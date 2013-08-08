<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251_1_options')
    exit($user->noRights());
     
$id = $fksdb->save($_POST['id'], 1);

$doc = $fksdb->fetch("SELECT * FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$doc)
    exit('<div class="ifehler">'.$trans->__('Dokument nicht gefunden.').'</div>');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())) {}
else exit($user->noRights());



echo '
<h1>'.$trans->__('Dokumenten-Einstellungen.').'</h1>

<div class="box">
    <form class="doc_options">
        <table>';
            if($doc->klasse)
            {         
                echo '
                <tr class="doc1_abs">
                    <td class="ftd">'.$trans->__('Dokumenten-Datum:').'</td>
                    <td>
                        <input type="text" name="datum" class="datepicker" value="'.($doc->datum > 86400?date('d.m.Y', $doc->datum):'').'" />
                    </td>
                </tr>';
            }
            
            $docautors = $fksdb->select("users", array(
                "id", "vorname", "nachname", "namenszusatz", "str", "email", "ort", "position", "eid"
            ), "(type = '0' OR type = '2') AND papierkorb = '0'");
            $muser = array();
            
            while($dautor = $fksdb->fetch($docautors))
            {
                $adding = '';
                if($dautor->namenszusatz) $adding = $trans->__('Nameszusatz:').' '.substr($dautor->namenszusatz, 0, 50).'...';
                elseif($dautor->str) $adding = $trans->__('Strasse:').' '.substr($dautor->str, 0, 50).'...';
                elseif($dautor->email) $adding = $trans->__('Email:').' '.substr($dautor->email, 0, 50).'...';
                elseif($dautor->ort) $adding = $trans->__('Ort:').' '.substr($dautor->ort, 0, 50).'...';
                elseif($dautor->plz) $adding = $trans->__('PLZ:').' ...'.$dautor->plz.'...';
                elseif($dautor->position) $adding = $trans->__('Position:').' '.substr($dautor->position, 0, 50).'...';
                elseif($dautor->eid) $adding = $trans->__('Alternative ID:').' '.substr($dautor->eid, 0, 50).'...';
                
                $muser[$dautor->id] = trim($dautor->vorname.' '.$dautor->nachname.' '.str_replace('()', '', ($adding && in_array($dautor->vorname.' '.$dautor->nachname, $muser)?'('.$adding.')':'')));
            }
            
            echo '
            <tr class="doc1_abs">
                <td class="ftd">'.$trans->__('Dokumenten-Autor:').'</td>
                <td>
                    <select name="author">';
                    foreach($muser as $k => $v)
                    {
                        if(!$v) continue;
                        echo '<option value="'.$k.'"'.($doc->author == $k || (!$doc->author && $doc->von == $k)?' selected':'').'>'.$v.'</option>';
                    }
                    echo '
                    <select> 
                </td>
            </tr>
            <tr class="doc1_abs">
                <td class="ftd">'.$trans->__('Lebensdauer:').'</td>
                <td>
                    '.$trans->__('Dieses Dokument ausschließlich für folgenden Zeitraum freischalten:').'<br />
                    <table class="zeitraum">
                        <tr>
                            <td><input type="checkbox" name="anfangC" class="vonbis" value="1"'.($doc->anfang?' checked':'').' /></td>
                            <td'.(!$doc->anfang?' class="notaktiv"':'').'>'.$trans->__('Von:').'</td>
                            <td><input type="text" name="anfang" class="datepicker" value="'.($doc->anfang?date('d.m.Y', $doc->anfang):'').'"'.(!$doc->anfang?' disabled':'').' /></td>
                            <td class="uhrzeit">
                                <select name="anfangA" class="uhrzeit"'.(!$doc->anfang?' disabled':'').'>
                                    '.$base->time_options(($doc->anfang?date('H', $doc->anfang):0)).'
                                </select> : 
                                <select name="anfangB" class="uhrzeit"'.(!$doc->anfang?' disabled':'').'>
                                    '.$base->time_options(($doc->anfang?date('i', $doc->anfang):0), true).'
                                </select> '.$trans->__('Uhr').'
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" name="bisC" class="vonbis" value="1"'.($doc->bis?' checked':'').' /></td>
                            <td'.(!$doc->bis?' class="notaktiv"':'').'>'.$trans->__('Bis:').'</td>
                            <td><input type="text" name="bis" class="datepicker" value="'.($doc->bis?date('d.m.Y', $doc->bis):'').'"'.(!$doc->bis?' disabled':'').' /></td>
                            <td class="uhrzeit">
                                <select name="bisA" class="uhrzeit"'.(!$doc->bis?' disabled':'').'>
                                    '.$base->time_options(($doc->bis?date('H', $doc->bis):23)).'
                                </select> : 
                                <select name="bisB" class="uhrzeit"'.(!$doc->bis?' disabled':'').'>
                                    '.$base->time_options(($doc->bis?date('i', $doc->bis):59), true).'
                                </select> '.$trans->__('Uhr').'
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>';
            
            echo '
            <tr class="doc1_abs">
                <td class="ftd">'.$trans->__('Suche:').'</td>
                <td>
                    <input type="checkbox" id="doc_options_no_search" name="no_search" value="1"'.($doc->no_search?' checked':'').' />
                    <label for="doc_options_no_search">'.$trans->__('Dieses Dokument von der Suche im Frontend ausschließen').'</label>
                </td>
            </tr>';
            
            $zq = $fksdb->query("SELECT id, name FROM ".SQLPRE."responsibilities WHERE papierkorb = '0' ORDER BY name");
            if($fksdb->count($zq))
            {
                $dzsb = $base->fixedUnserialize($doc->zsb);
                if(!is_array($dzsb)) $dzsb = array();
                
                echo '
                <tr class="doc1_abs">
                    <td>'.$trans->__('Zuständigkeit:').'</td>
                    <td>
                        <p>';
                            while($z = $fksdb->fetch($zq))
                            {
                                if($rechte['dok']['zsb'] && !in_array($z->id, $rechte['dok']['zsba']))
                                    continue;
                                    
                                echo '
                                <input type="checkbox" value="'.$z->id.'" name="zsb[]" id="r3_zsb'.$z->id.'"'.(in_array($z->id, $dzsb)?' checked':'').' />
                                <label for="r3_zsb'.$z->id.'">'.$z->name.'</label><br />';
                            }
                        echo '                            
                        </p>
                    </td>
                </tr>';
            }
            
            $ro = $base->fixedUnserialize($doc->rollen);
            $rolQ = $fksdb->query("SELECT id, titel FROM ".SQLPRE."roles WHERE papierkorb = '0' ORDER BY sort, id");
            if($fksdb->count($rolQ) > 1)
            {
                if(!is_array($ro))
                    $ro = array();
                
                echo '
                <tr class="doc1_rollen">
                    <td>'.$trans->__('Zugelassene Rollen:').'</td>
                    <td>
                        <p>
                            <input type="checkbox" id="drol_-1" name="role[]" value="-1"'.(in_array('-1', $ro)?' checked':'').' />
                            <label for="drol_-1"><em>'.$trans->__('kein Kunde / nicht angemeldet').'</em></label><br />';
                            
                            while($rol = $fksdb->fetch($rolQ))
                            {
                                echo '
                                <input type="checkbox" id="drol_'.$rol->id.'" name="role[]" value="'.$rol->id.'"'.(in_array($rol->id, $ro)?' checked':'').' />
                                <label for="drol_'.$rol->id.'">'.$rol->titel.'</label><br />';
                            }
                        echo '                            
                        </p>
                    </td>
                </tr>';
            }
        
        echo '
        </table>
        
        <p class="dokument_opt more_doks_info">
            '.($user->r('dok', 'del') && !$doc->papierkorb?'<a class="delete_document">'.$trans->__('Dokument löschen').'</a><br />':'').'
            '.($doc->gesperrt?
                '<a class="open_document">'.$trans->__('Dokument entsperren').'</a>'
                :
                '<a class="close_document">'.$trans->__('Dokument sperren').'</a>'
            ).'
            <br />
            
            '.(!$doc->vorlage?
                '<a class="document_draft">'.$trans->__('Dokument als Vorlage speichern').'</a>'
                :
                '<br /><strong>'.$trans->__('Dokument ist Vorlage: "%1"', false, array($doc->vorlage)).'</strong><br />
                <a class="document_draft">'.$trans->__('Vorlage umbennen').'</a><br />
                <a class="delete_document_draft">'.$trans->__('Vorlage entfernen').'</a>'
            ).'
        </p>
    </form>
</div>

<div class="box_save">
    <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
    <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
</div>';

exit();
?>
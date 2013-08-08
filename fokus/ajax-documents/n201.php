<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');

if(!$user->isAdmin() || $index != 'n201')
    exit($user->noRights());
    
$f = $rel; 
if($f && !$user->r('dok', 'publ'))
    exit($user->noRights());

$akt_limit = $fksdb->save($_REQUEST['limit']);
$real_doc_admin = $fksdb->save($_REQUEST['real_doc_admin']);        
$sortA = $fksdb->save($_REQUEST['sortA']);
$sortB = $fksdb->save($_REQUEST['sortB']);
$opt = explode('+', $fksdb->save($_REQUEST['opt']));
$dklassen = explode('+', $fksdb->save($_REQUEST['dklassen']));

$q = $fksdb->save($_REQUEST['q']);
$qA = explode(' ', $q);
for($x = 0; $x < count($qA); $x++)
    $qS .= ($x?" OR":"")." titel LIKE '%".$qA[$x]."%' OR id LIKE '".$qA[$x]."' ";
$sql .= ($q?" AND (".$qS.") ":""); 

for($x = 1; $x < count($dklassen); $x++)
    $qD .= ($x > 1?" OR":"")." klasse = '".$dklassen[$x]."' ";
$sql .= ($qD?" AND (".$qD.") ":(!$f?" AND klasse = '' ":""));  

$zellennr = 0;
echo '<tr id="headline">';
echo '<th id="ddd_id"'.($sortA == 'id'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="id">'. $trans->__('ID') .'</th>'; $zellennr ++;
if(in_array('1', $opt)) { echo '<th id="ddd_titel"'.($sortA == 'titel'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="titel">'. $trans->__('Name') .'</th>'; $zellennr ++; }
if(in_array('2', $opt)) { echo '<th id="ddd_timestamp_freigegeben"'.($sortA == 'status'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="status">'. $trans->__('Status') .'</th>'; $zellennr ++; }
if(in_array('3', $opt)) { echo '<th id="ddd_von"'.($sortA == 'von'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="von">'. $trans->__('Ursprünglicher Autor') .'/th>'; $zellennr ++; }
if(in_array('4', $opt)) { echo '<th id="ddd_von_edit"'.($sortA == 'von_edit'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="von_edit" style="min-width:90px">'. $trans->__('Letzter Autor') .'</th>'; $zellennr ++; }
if(in_array('5', $opt)) { echo '<th id="ddd_timestamp"'.($sortA == 'timestamp'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="timestamp">'. $trans->__('Erstellungsdatum') .'</th>'; $zellennr ++; }
if(in_array('6', $opt)) { echo '<th id="ddd_timestamp_edit"'.($sortA == 'timestamp_edit'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="timestamp_edit">'. $trans->__('Bearbeitungsdatum') .'</th>'; $zellennr ++; }
if(in_array('7', $opt)) { echo '<th id="ddd_timestamp_freigegeben2"'.($sortA == 'timestamp_freigegeben'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="timestamp_freigegeben">'. $trans->__('Freischaltdatum') .'</th>'; $zellennr ++; }
if(in_array('8', $opt)) { echo '<th id="ddd_seiten"'.($sortA == 'seiten'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="seiten">'. $trans->__('Strukturelemente') .'</th>'; $zellennr ++; }
if($f) { echo '<th'.($sortA == 'sprache'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="id">'. $trans->__('Sprache') .'</th>'; $zellennr ++; }
if($f) { echo '<th class="last'.($sortA == 'von_edit'?' sort '.($sortB == 'DESC'?'desc':'asc'):'').'" data-sort="von_edit">'. $trans->__('Freigabe erteilen') .'</th>'; $zellennr ++; }
echo '</tr>';    

$anzahl_spalten = $zellennr;
$real_count = 0;
$gesamt_count = 0;

$insgesamt = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."documents WHERE produkt = '' AND papierkorb = '0' ".$sql)); 

if($sortA == 'status')
    $sortA = "statusB ".$sortB.", statusA";
    
$per_oeffnen = ($user->r('per', 'edit') && (!$user->r('per', 'type') || $user->r('per', 'mitarbeiter')));

$documents = $fksdb->rows("SELECT zsb, id, von, von_edit, seiten, titel, timestamp, timestamp_edit, timestamp_freigegeben, statusA, statusB, anfang, bis, klasse FROM ".SQLPRE."documents WHERE produkt = '' AND papierkorb = '0' ".$sql."".($sortA?"ORDER BY ".$sortA." ".$sortB:"ORDER BY timestamp_edit DESC").(!$f?" LIMIT ".round($akt_limit * 1.5, 0):""));

$document_in = "";
foreach($documents as $did => $doc)
{
    $document_in .= (!$document_in?"":", ").$did; 
}

if($document_in)
{
    if(!$f) 
    {
        $spc = $fksdb->rows("SELECT id, edit, von, dokument FROM ".SQLPRE."document_versions WHERE language = '".$trans->getInputLanguage()."' AND dokument IN (".$document_in.") GROUP BY dokument ORDER BY id DESC", "", "dokument"); 
    }
    else
    { 
        $spc = $fksdb->rows("SELECT id, edit, language, von, dokument FROM ".SQLPRE."document_versions WHERE dokument IN (".$document_in.") AND ende = '1' AND aktiv = '0' GROUP BY dokument", "", "dokument");
    } 
    
    $document_relations = $fksdb->rows("SELECT id, element, slot, dokument FROM ".SQLPRE."document_relations WHERE dokument IN (".$document_in.") ORDER BY element, sort", "", "dokument");
}
 
foreach($documents as $row)
{
    $gesamt_count ++;
    
    $nicht_zustaendig = false;
    if($row->zsb && !$user->isSuperAdmin() && count($user->getAvaibleCompetence()))
    {    
        $dzsb = $base->fixedUnserialize($row->zsb);
        if(count($dzsb))
        {
            $nicht_zustaendig = true;
            
            foreach($dzsb as $zid)
            {
                if($user->isCompetent($zid))
                    $nicht_zustaendig = false;    
            }
        }
    }
    if($nicht_zustaendig)
        continue;
     
    if($row->anfang || $row->bis)
    {
        $dscheck = $base->find_check_document_statusB($row->id, $row->anfang, $row->bis, $row->statusB);
        if($dscheck >= 0)
            $row->statusB = $dscheck;
    }
    
    
    $dversions = $spc[$row->id];
    if(!is_array($dversions))
        $dversions = array();
    
    foreach($dversions as $dv)
    {
        $oeffnen = ($user->r('dok', 'edit') || ($user->r('dok', 'new') && $row->von == $user->getID())?true:false);
        if(!$f && !$real_doc_admin)
            $oeffnen = true;
        
        $ttipp = '';
        $countpages = 0;
        if($row->seiten && !$row->klasse)
        {
            $sdQ = $document_relations[$row->id]; 
            if(!is_array($sdQ))
                $sdQ = array();
            
            $connected = '';
            
            foreach($sdQ as $sd)
            {
                if($sd->slot)
                {
                    $slot_name = $base->getActiveTemplateConfig('slots', $sd->slot, 'name');
                    if(!$slot_name)
                        continue;
                    
                    $countpages ++;
                    $connected .= '<li'.($countpages == count($sdQ)?' class="last"':'').'><a data-type="slot" data-id="'.$sd->slot.'">Slot &quot;'.$slot_name.'&quot;</a></li>';
                }
                else
                {
                    $titel = $fksdb->data("SELECT titel FROM ".SQLPRE."elements WHERE id = '".$sd->element."' AND struktur = '".$base->getStructureID()."' LIMIT 1", "titel");
                    if(!$titel)
                        continue;
                    
                    $countpages ++;
                    $connected .= '<li'.($countpages == count($sdQ)?' class="last"':'').'><a data-type="ele" data-id="'.$sd->element.'">'.$titel.'</a></li>';
                }
            }
            
            if($connected && $countpages)
                $ttipp = '<ul>'.$connected.'</ul><p class="pfeil"></p>';
            
            $row->seiten = $countpages;
        }
        elseif($row->klasse)
        {
            $titelQ = $fksdb->query("SELECT titel, id FROM ".SQLPRE."elements WHERE klasse = '".$row->klasse."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0'");
            $ttipp = ($fksdb->count($titelQ)?'<ul>':''); 
            while($titel = $fksdb->fetch($titelQ))
            { 
                $countpages ++;
                $ttipp .= '<li'.($countpages == $fksdb->count($ttipp)?' class="last"':'').'><a data-type="ele" data-id="'.$titel->id.'">'.$titel->titel.'</a></li>';
            }
            $ttipp .= ($fksdb->count($titelQ)?'</ul><p class="pfeil"></p>':'');
            
            $row->seiten = $countpages;
        }
        
        $real_count ++;
        
        echo '<tr class="bg_'.$base->countup().' entry" data-id="'.$row->id.'" data-title="'.$row->titel.'">';
            echo '<td>'.($oeffnen?'<a class="inc_documents" id="n250" rel="'.$row->id.'">':'').'D'.str_pad($row->id, 5 ,'0', STR_PAD_LEFT).''.($oeffnen?'</a>':'').'</td>';
            if(in_array('1', $opt)) 
            { 
                echo '<td>'.($oeffnen?'<a class="inc_documents" id="n250" rel="'.$row->id.'">':'').''.($row->titel?$row->titel:$trans->__('- kein Titel -')).''.($oeffnen?'</a>':'').'</td>';
            }
            if(in_array('2', $opt)) 
            { 
                echo '<td class="xstatus">'.$base->document_status($row->statusA, $row->statusB).'</td>'; 
            }
            if(in_array('3', $opt)) 
            { 
                echo '<td>'.($per_oeffnen?'<a class="inc_users" id="n590" rel="'.$row->von.'">':'').$base->user($row->von, ' ', $trans->__('vorname'), $trans->__('nachname')).($per_oeffnen?'</a>':'').'</td>';
            }
            if(in_array('4', $opt)) 
            { 
                echo '<td>'.($per_oeffnen?'<a class="inc_users" id="n590" rel="'.$dv->von.'">':'').$base->user($dv->von, ' ', $trans->__('vorname'), $trans->__('nachname')).($per_oeffnen?'</a>':'').'</td>';
            }
            if(in_array('5', $opt)) 
            { 
                echo '<td>'.$base->is_online($row->timestamp, true).'</td>'; 
            }
            if(in_array('6', $opt)) 
            { 
                echo '<td>'.$base->is_online($row->timestamp_edit, true).'</td>'; 
            }
            if(in_array('7', $opt)) 
            { 
                echo '<td>'.($row->timestamp_freigegeben?$base->is_online($row->timestamp_freigegeben, true):'').'</td>'; 
            }
            if(in_array('8', $opt)) 
            { 
                echo '<td><div class="seiten_info lcolor">'.$row->seiten.' Seite'.($row->seiten != 1?'n':'').' zugeordnet'.$ttipp.'</div></td>'; 
            }
            if($f) 
            { 
                echo '<td><img src="'.$trans->getFlag($dv->language, 2).'" alt="" /> '.$trans->__(strtoupper($dv->language)).'</td>';
            }
            if($f) 
            { 
                echo '<td class="last"><a class="freigeben" rel="'.$row->id.'_'.$dv->id.'">'. $trans->__('Freigeben') .'</a></td>';
            }
        echo '</tr>';  
    } 
    
    if($real_count >= $akt_limit && !$f)
        break;
}

if(!$real_count)
{
    echo '
    <tr>
        <td colspan="'.$anzahl_spalten.'" class="calibri nothing_found">'. $trans->__('keine Dokumente vorhanden') .'</td>
    </tr>';
}
elseif($gesamt_count < $insgesamt && !$f)
{
    $verbleibend = $insgesamt - $gesamt_count;
    
    echo '
    <tr>
        <td colspan="'.$anzahl_spalten.'" class="calibri more_results">
            <a class="next">'. $trans->__('+ weitere Dokumente anzeigen') .' ('.($verbleibend < 15?$verbleibend:'15').')</a>
            '.($verbleibend > 15?'<a class="all">'. $trans->__('+ alle Dokumente anzeigen') .' ('.($verbleibend).')</a>':'').'
        </td>
    </tr>';        
}
?>
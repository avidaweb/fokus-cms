<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$fo = $base->fixedUnserialize($row->html);
if(!is_array($fo)) $fo = array();
$fa = $fo['f'];
if(!is_array($fa)) $fa = array();

$faB = array();
foreach($fa as $f_id => $f)
{
    $faB[$f['bid']][$f_id]['bid'] = $f['bid'];
    $faB[$f['bid']][$f_id]['name'] = $f['name'];
    $faB[$f['bid']][$f_id]['type'] = $f['type'];
    $faB[$f['bid']][$f_id]['links'] = $f['links'];
    $faB[$f['bid']][$f_id]['opt'] = $f['opt'];
}

if(!is_array($fo['aktion']))
    $fo['aktion'] = array();

if(in_array(2, $fo['aktion']))
{
    $doc_opt = $base->db_to_array($fo['zuordnung_dokument']);
    $rewrite_taget = ($doc_opt['rewrite_taget']?true:false);
}

echo '
<form id="formular_form">
<div class="box">
    <div class="formularkopf">
        <div class="bL">
            <h2>'. $trans->__('Beschriftung.') .'</h2>
            '. $trans->__('Hier können Sie Beschriftungen einfügen, z.B. &quot;Vorname, Nachname, Straße, etc.&quot;') .'
        </div>
        <div class="bR">
            <h2>'. $trans->__('Formularelemente.') .'</h2>
            '. $trans->__('In dieser Spalte können Sie ihre Formularelemente einfügen. Das können z.B. Textfelder, Auswahlboxen aber auch ergänzende Beschriftungen sein.') .'
        </div>
    </div>
    
    <div id="formular">'; 
        
        foreach($faB as $b_id => $block)
        {
            echo '
            <div class="block sblock">
                <input type="hidden" class="bid" value="'.$b_id.'" />
                
                <div class="bL">';
                    foreach($block as $f_id => $f)
                    {
                        if($f['links'])
                        {
                            $name = htmlspecialchars(str_replace('"', "'", $f['name']));
                            
                            echo '
                            <div class="feld string">
                                <em>'.$ftypen[$f['type']].'</em>
                                <a class="name">'.Strings::cut(strip_tags($f['name']), 20).'</a>
                                <a class="opt">'. $trans->__('Optionen') .'</a>
                                
                                <input type="hidden" name="f['.$f_id.'][bid]" value="'.$b_id.'" class="fbid" />
                                <input type="hidden" name="f['.$f_id.'][name]" value="'.$name.'" class="fname" />
                                <input type="hidden" name="f['.$f_id.'][type]" value="'.$f['type'].'" class="ftype" />
                                <input type="hidden" name="f['.$f_id.'][links]" value="'.$f['links'].'" class="flinks" />
                                <input type="hidden" name="f['.$f_id.'][opt]" value="'.$f['opt'].'" class="fopt" />
                            </div>';
                        }
                    }
                    echo '
                </div>
                <div class="bR">';
                    foreach($block as $f_id => $f)
                    {
                        if(!$f['links'])
                        {
                            $name = ($f['type'] == 'string'?htmlspecialchars(str_replace('"', "'", $f['name'])):$f['name']);
                            
                            echo '
                            <div class="feld '.$f['type'].'">
                                <em>'.$ftypen[$f['type']].'</em>
                                <a class="name">'.Strings::cut(strip_tags($f['name']), 100).'</a>
                                <a class="opt">'. $trans->__('Optionen') .'</a>
                                
                                <input type="hidden" name="f['.$f_id.'][bid]" value="'.$b_id.'" class="fbid" />
                                <input type="hidden" name="f['.$f_id.'][name]" value="'.$name.'" class="fname" />
                                <input type="hidden" name="f['.$f_id.'][type]" value="'.$f['type'].'" class="ftype" />
                                <input type="hidden" name="f['.$f_id.'][links]" value="'.$f['links'].'" class="flinks" />
                                <input type="hidden" name="f['.$f_id.'][opt]" value="'.$f['opt'].'" class="fopt" />
                            </div>';
                        }
                    }
                    echo '
                </div>
                <div class="anfasser"></div>
            </div>';    
        }
        
        echo '
    </div>
    
    <div class="formularkopf" id="newf_block">
        <div class="bL">
            <button class="beschrL">'. $trans->__('Beschriftung einfügen') .'</button>
        </div>
        <div class="bR">
            <button class="inputR">'. $trans->__('Formularelement einfügen') .'</button>
            <button class="beschrR">'. $trans->__('Beschriftung einfügen') .'</button>
        </div>
    </div>
</div>

<div class="box fmore">
    <h2>'. $trans->__('Formulareinstellungen.') .'</h2>
    
    <div class="fbox">
        <div class="A">
            <strong>'. $trans->__('Name des Formulars.') .'</strong>
            '. $trans->__('Der Name des Formulars wird im Bereich &quot;Kommunikationskanäle&quot; angezeigt.') .'
        </div>
        <div class="B">
            <input type="text" class="longer" name="name" value="'.($fo['name']?$fo['name']:'Formular '.date('d.m.Y', $dokument->timestamp)).'" />
        </div>
    </div>
    <div class="fbox">
        <div class="A">
            <strong>'. $trans->__('Beschriftung des Absenden-Buttons.') .'</strong>
            '. $trans->__('Der Absenden-Button dieses Formulares soll folgende Beschriftung haben.') .'
        </div>
        <div class="B">
            <input type="text" name="submit" value="'.($fo['submit']?$fo['submit']: $trans->__('Abschicken')).'" />
        </div>
    </div>
    <div class="fbox">
        <div class="A">
            <strong>'. $trans->__('Zielseite nach Absenden des Formulars.') .'</strong>
            '. $trans->__('Wenn das Formular erfolgreich abgesendet wurde, Benutzer auf folgende Seite weiterleiten.') .'
        </div>
        <div class="B rewrite_taget_no"'.($rewrite_taget?' style="display:none;"':'').'>
            <button class="ele_choose">Strukturelement ausw&auml;hlen</button>
            <p class="ele_choosen">'.$fksdb->data("SELECT titel FROM ".SQLPRE."elements WHERE id = '".$fo['ziel']."' LIMIT 1", "titel").'</p>
            <input type="hidden" name="ziel" value="'.$fo['ziel'].'" />
        </div>
        <div class="B rewrite_taget_yes"'.(!$rewrite_taget?' style="display:none;"':'').'>
            <p class="ele_choosen">'.$trans->__('Automatisch erzeugtes Dokument').'</p>
        </div>
    </div>
    <div class="fbox fbox2">
        <div class="A">
            <strong>'. $trans->__('Beschriftungsspalte anzeigen?') .'</strong>
            '. $trans->__('Soll die oben angezeigte Beschriftungsspalte angezeigt werden?') .'
        </div>
        <div class="B">
            <p>
                <input id="fstr_0" type="radio" name="strings" value="0"'.($fo['strings'] == 0?' checked="checked"':'').' />
                <label for="fstr_0">'. $trans->__('Ja, Beschriftungsspalte immer anzeigen') .'</label>
            </p>
            <p>
                <input id="fstr_1" type="radio" name="strings" value="1"'.($fo['strings'] == 1?' checked="checked"':'').' />
                <label for="fstr_1">'. $trans->__('Ja, Beschriftungsspalte anzeigen, wenn Beschriftungen eingetragen sind') .'</label>
            </p>
            <p>
                <input id="fstr_2" type="radio" name="strings" value="2"'.($fo['strings'] == 2?' checked="checked"':'').' />
                <label for="fstr_2">'. $trans->__('Nein, Beschriftungsspalte nicht anzeigen') .'</label>
            </p>
        </div>
    </div>
    <div class="fbox fbox2 fbox3">
        <div class="A">
            <strong>'. $trans->__('Aktion beim Absenden.') .'</strong>
            '. $trans->__('Was soll passieren, wenn der Benutzer das Formular ausf&uuml;llt und absendet?') .'
        </div>
        <div class="B">
            <table class="fwmA">
                <tr>
                    <td class="a">
                        <input id="fwm_0" type="radio" name="save" value="0"'.($fo['save'] == 0?' checked="checked"':'').' />
                    </td>
                    <td>
                        <label for="fwm_0">'. $trans->__('Formulardaten im Kommunikationskanal &quot;Formulare&quot; speichern und mich per Email informieren:') .'</label>
                        <input type="text" class="longer" name="saveEmail" value="'.($fo['saveEmail']?$fo['saveEmail']:$base->getOpt()->email).'" />
                    </td>
                </tr>
                <tr>
                    <td class="a">
                        <input id="fwm_1" type="radio" name="save" value="1"'.($fo['save'] == 1?' checked="checked"':'').' />
                    </td>
                    <td>
                        <label for="fwm_1">'. $trans->__('Formulardaten im Kommunikationskanal &quot;Formulare&quot; speichern und mich nicht informieren.') .'</label>
                    </td>
                </tr>
            </table>
            <table class="fwmB" id="form_feldzuordnungen">
                <tr class="C">
                    '.($user->r('per', 'new')?'
                    <td class="a">
                        <input id="fzo_1" type="checkbox" name="aktion[]" value="1"'.(in_array(1, $fo['aktion'])?' checked="checked"':'').' />
                    </td>
                    <td>
                        <label for="fzo_1">'. $trans->__('Auf Basis der Formulardaten automatisch einen <strong>neuen Benutzer</strong> erzeugen und in der Datenbank speichern.') .'</label>
                        <button class="fzo_1"'.(!in_array(1, $fo['aktion'])?' style="display:none;"':'').'>'. $trans->__('Feldzuordnungen &amp; Einstellungen') .'</button>
                        <input type="hidden" name="zuordnung_benutzer" class="zordnung" value="'.$fo['zuordnung_benutzer'].'" />
                    </td>
                    ':'
                    <td colspan="2">
                        '.(in_array(1, $fo['aktion'])?'<input type="hidden" name="aktion[]" value="1" />':'').'
                        <input type="hidden" name="zuordnung_benutzer" class="zordnung" value="'.$fo['zuordnung_benutzer'].'" />
                    </td>').'
                </tr>
                
                <tr>
                    <td class="a">
                        <input id="fzo_2" type="checkbox" name="aktion[]" value="2"'.(in_array(2, $fo['aktion'])?' checked="checked"':'').' />
                    </td>
                    <td>
                        <label for="fzo_2">'. $trans->__('Auf Basis der Formulardaten automatisch ein <strong>neues Dokument</strong> erzeugen und in der Datenbank speichern') .'</label>
                        <button class="fzo_2"'.(!in_array(2, $fo['aktion'])?' style="display:none;"':'').'>'. $trans->__('Feldzuordnungen &amp; Einstellungen') .'</button>
                        <input type="hidden" name="zuordnung_dokument" class="zordnung" value="'.$fo['zuordnung_dokument'].'" />
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>
</form>';
?>
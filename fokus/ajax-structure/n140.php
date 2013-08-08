<?php
if($index != 'n140')
    exit();
    
if(!$user->r('str', 'ele') && !$user->r('str', 'slots') && !$user->r('fks', 'opt'))
    exit($user->noRights());

if(!$base->getStructureID())
    exit('<div class="ifehler">'.$trans->__('Momentan ist keine Struktur zur Bearbeitung gewählt').'</div>');
  
if($v->slot && $v->sid)
{
    $element = $fksdb->fetch("SELECT * FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
    if(!$element)
        exit('<div class="ifehler">'.$trans->__('Ausgewähltes Element existiert nicht oder befindet sich im Papierkorb').'</div>');
        
    $slot = $base->getActiveTemplateConfig('slots', $v->slot);
    if(!is_array($slot)) 
        exit('<div class="ifehler">'.$trans->__('Der ausgewählte Slot ist im momentan aktiven Template nicht verfügbar').'</div>'); 
        
    $sdoks = $fksdb->rows("SELECT id, dokument, klasse FROM ".SQLPRE."document_relations WHERE slot = '".$v->slot."' AND element = '".$v->sid."' ORDER BY sort");
}
elseif($v->slot)
{  
    if(!$user->r('str', 'slots'))
        exit($user->noRights());
        
    $slot = $base->getActiveTemplateConfig('slots', $v->slot);
    if(!is_array($slot)) 
        exit('<div class="ifehler">'.$trans->__('Der ausgewählte Slot ist im momentan aktiven Template nicht verfügbar').'</div>'); 
}
elseif($v->error)
{
    if(!$user->r('fks', 'opt'))
        exit($user->noRights());
}
else
{  
    if(!$user->r('str', 'ele'))
        exit($user->noRights());
        
    $element = $fksdb->fetch("SELECT * FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
    if(!$element)
        exit('<div class="ifehler">'.$trans->__('Ausgewähltes Element existiert nicht, gehört zu einer nicht aktiven Struktur oder befindet sich im Papierkorb.').'</div>');
        
    $spr = $base->fixedUnserialize($element->sprachen);
    if(!is_array($spr)) $spr = array();
    
    $cf = $api->getCustomFields();
    if(!is_array($cf)) $cf = array();
    
    $katsQ = $fksdb->query("SELECT name FROM ".SQLPRE."categories");
    while($kat = $fksdb->fetch($katsQ))
        $kat_string .= '<option value="'.$kat->name.'" />';
        
    if($element->klasse)
    {
        $fk = $base->open_dklasse('../content/dklassen/'.$element->klasse.'.php');  
        $slug = $base->slug($fk['name']);  
        $dka = $base->db_to_array($base->getOpt()->dk);
        $dk = (object)$dka;
        if($dk->n_uebersicht[$slug])
            $no_uebersicht = true;
    }
    
    $user->lastUse('element', $element->id);
}

echo '
<h1>
    '.(!$v->slot?
        (!$v->error?
            (!$element->klasse?
                $trans->__('Strukturelement bearbeiten:').' '.Strings::cut($element->titel, 21)
                :
                Strings::cut($element->titel, 50)
            )
            :
            $trans->__('Fehlerseite bearbeiten:').' #'.$v->error
        )
        :
        (!$element?
            $trans->__('Slot bearbeiten:').' '.Strings::cut($slot['name'], 28)
            :
            $trans->__('Slot <em>%1</em> im Strukturelement <em>%2</em>.', false, array($slot['name'], $element->titel))
        )
    ).'
</h1>

<form id="strukturelement" class="language_dialog">
<input type="hidden" name="sid" value="'.intval($element->id).'" />
<input type="hidden" name="klasse" value="'.(!$element->klasse?'false':'true').'" />
<input type="hidden" name="slot" value="'.$v->slot.'" />
<input type="hidden" name="slot_dclass" value="'.$slot['dclass'].'" />
<input type="hidden" name="error" value="'.$v->error.'" />

<div class="box sprachbox">
    <table class="element_sprachen">
        <tr class="bezeichner">
            <td class="breit" colspan="3">'.$trans->__('Name für interne Nutzung:').'</td>
            <td class="inp">
                <input type="text" name="titel" value="'.$element->titel.'" required placeholder="'.$trans->__('Bezeichner des Strukturelements').'" />
            </td>
            <td class="more">
                <a href="'.DOMAIN.'/'.$element->id.'/'.($element->titel?$base->slug($element->titel):'na').'/" target="_blank">
                    (S'.str_pad($element->id, 5 ,'0', STR_PAD_LEFT).')
                </a>
            </td>
        </tr>
    </table>';
        
    $slan = ($base->getActiveLanguagesCount() > 1?false:true);
    foreach($base->getActiveLanguages() as $sp)
    {
        echo '
        <div class="sprache">
            <table class="element_sprachen '.($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'aktiv':'inaktiv').'">
                <tr class="main">
                    '.(!$slan?'
                    <td class="auswahl">
                        <input type="checkbox" name="aktiv" id="se_spracheA_'.$sp.'" value="1"'.($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp)?' checked="checked"':'').' />
                    </td>
                    <td class="flagge">
                        <label for="se_spracheA_'.$sp.'"><img width="22" src="'.$trans->getFlag($sp).'" alt="" /></label>
                    </td>
                    <td class="titel">
                        '.$trans->__(strtoupper($sp)).':
                    </td>':'
                    <td class="breit" colspan="3">
                        '.$trans->__('Öffentlicher Titel:').'
                    </td>').'
                    <td class="inp">
                        <input type="text" class="ntitle" id="ntitle_'.$sp.'" data-lan="'.$sp.'" name="sprache['.$sp.'][titel]" value="'.$spr[$sp]['titel'].'"'.($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').' />
                    </td>
                    <td class="more">
                        <a class="rbutton rollout"><span>'.$trans->__('Details').'</span></a>
                    </td>
                </tr>
                <tr class="firstrow">
                    <td class="breit" colspan="3">'.$trans->__('HTML-Titel:').'</td>
                    <td class="inp">
                        <input type="text" name="sprache['.$sp.'][htitel]" class="ht1" value="'.$spr[$sp]['htitel'].'"'.(!$spr[$sp]['htitel']?' style="display:none;"':'').($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').' />
                        <input type="text" disabled="disabled" value="'.$base->auto_title($spr[$sp]['titel'], $sp).'" class="ht2"'.($spr[$sp]['htitel']?' style="display:none;"':'').' />
                        <p class="auto">
                            <input type="checkbox" class="autotitle" id="autotitle_'.$sp.'" value="1"'.(!$spr[$sp]['htitel']?' checked="checked"':'').($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').' /> 
                            <label for="autotitle_'.$sp.'">'.$trans->__('HTML-Titel automatisch generieren').'</label>
                        </p>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td class="breit" colspan="3">'.$trans->__('HTML-Beschreibung:').'</td>
                    <td class="inp">
                        <textarea class="html_desc" name="sprache['.$sp.'][desc]"'.($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').'>'.$spr[$sp]['desc'].'</textarea>
                        <p class="zeichen">
                            <span>0</span> '.$trans->__('Zeichen').'
                        </p>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td class="breit" colspan="3">'.$trans->__('HTML-Schlüsselworte:').'</td>
                    <td class="inp">
                        <input type="text" list="tags_'.$sp.'" name="sprache['.$sp.'][tags]" value="'.$spr[$sp]['tags'].'"'.($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').' />
                        '.($kat_string?'
                        <datalist id="tags_'.$sp.'">
                            '.$kat_string.'
                        </datalist>':'').'
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td class="breit" colspan="3">'.$trans->__('URL:').'</td>
                    <td class="inp">
                        <input type="text" name="sprache['.$sp.'][url]" class="url1" value="'.$base->slug($spr[$sp]['url']).'"'.(!$spr[$sp]['url']?' style="display:none;"':'').($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').' />
                        <input type="text" disabled="disabled" value="'.$base->slug($spr[$sp]['titel']).'" class="url2"'.($spr[$sp]['url']?' style="display:none;"':'').' />
                        <p class="url">
                            <input type="checkbox" class="autourl" id="autourl_'.$sp.'" value="1"'.(!$spr[$sp]['url']?' checked="checked"':'').($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').' /> 
                            <label for="autourl_'.$sp.'">'.$trans->__('URL automatisch generieren').'</label>
                        </p>
                    </td>
                    <td></td>
                </tr>';
                
                if(!is_array($cf))
                    $cf = array();
                    
                foreach($cf as $k => $val)
                {
                    if($val['global'] || !$val['name'])
                        continue;
                    
                    if(count($val['restriction']))
                    {
                        if($val['restriction']['elements'] !== true && !in_array($element->id, $val['restriction']['elements']))
                            continue;
                    }
                        
                    echo '
                    <tr>
                        <td class="breit" colspan="3">'.($val['name']).'</td>
                        <td class="inp">';
                            if(!$val['type'] || $val['type'] == 'text' || $val['type'] == 'input')
                            {
                                echo '<input type="text" name="sprache['.$sp.']['.$k.']" value="'.$spr[$sp][$k].'"'.($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').' />';
                            }
                            elseif($val['type'] == 'textarea')
                            {
                                echo '<textarea name="sprache['.$sp.']['.$k.']"'.($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').'>'.$spr[$sp][$k].'</textarea>';
                            }
                            elseif($val['type'] == 'checkbox')
                            {
                                echo '<input type="checkbox" name="sprache['.$sp.']['.$k.']" value="fks_true"'.($spr[$sp][$k] == 'fks_true'?' checked="checked"':'').($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').' />';
                            }
                            elseif($val['type'] == 'select' && is_array($val['values']))
                            {
                                echo '<select name="sprache['.$sp.']['.$k.']"'.($spr[$sp]['titel'] || ($v->neu && $trans->getInputLanguage() == $sp) || $slan?'':' disabled').'>';
                                foreach($val['values'] as $x => $y)
                                    echo '<option value="'.$x.'"'.($spr[$sp][$k] == $x?' selected="selected"':'').'>'.$y.'</option>';
                                echo '                     
                                </select>';
                            }
                        echo '
                        </td>
                        <td></td>
                    </tr>';
                }
                
                echo '
                <tr>
                    <td class="gcsnippet" colspan="5">
                        <a class="rbutton rollout">'.$trans->__('Snippet-Vorschau <span>anzeigen</span>').'</a>
                        
                        <div class="gsnippet">
                            <p class="s_titel">'.Strings::cut(($spr[$sp]['htitel']?$spr[$sp]['htitel']:$base->auto_title($spr[$sp]['titel'], $sp)), 55).'</p>
                            <p class="s_url">
                                '.$domain.'/'.$element->id.'/<span>'.Strings::cut(($spr[$sp]['url']?$base->slug($spr[$sp]['url']):$base->slug($spr[$sp]['titel'])), 50).'</span>
                            </p>
                            <p class="s_desc">
                                '.date('d.m.Y').' - 
                                <span>'.Strings::cut($spr[$sp]['desc'], 150).'</span>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
        </div>';
    } 
    
echo ' 
</div>

<div class="box fehlerbox"'.($element->frei || $v->slot || $v->error?' style="display: none;"':'').'>
    <strong>'.$trans->__('Dieses Strukturelement wird nicht angezeigt').'</strong>
    '.$trans->__('Aufgrund der momentanen Freigabeeinstellungen ist dieses Strukturelement derzeit nicht Bestandteil Ihrer aktiven Webseite. Diese Einstellung können Sie jederzeit über die <em>Einstellungen</em> ändern oder hier das Strukturlement <a class="freigabe">direkt freigeben</a>.').'
</div>

<div class="movebox" id="struk_doks">
    <img src="images/moveboxH.png" alt="" class="schatten" />
    <div class="moved">
        <div class="dokumente"></div>
        <div class="buttons">
            <button class="insert">'.$trans->__('Dokument einfügen').'</button>
            '.($user->r('dok', 'new')?'<button class="insert-new shortcut-new">'.$trans->__('Neues Dokument anlegen &amp; einfügen').'</button>':'').'
        </div>
    </div>
    <img src="images/moveboxB.png" alt="" class="schatten" />
    
    <div class="loadme">
        <img src="images/loading_grey.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
    </div>
</div>

'.(!$v->slot && !$v->error?'
<div class="box">
    <a class="rbutton goaway" id="se_open_seopt">'.$trans->__('Einstellungen').'</a>
    '.(!$element->klasse && (!$eleopt || $user->r('str', 'dk'))?'<a class="rbutton goaway" id="se_open_dk">'.$trans->__('Dokumentenklassen').'</a>':'').'
    '.($element->klasse && !$no_uebersicht && $user->r('dok')?'<a class="rbutton goaway" id="se_open_duebersicht" data-id="'.$element->klasse.'.php">'.$trans->__('Dokumentenübersicht').'</a>':'').'
    '.(!$element->klasse && $suite->rm(4) && $user->r('fks', 'ghost')?'<a class="rbutton goaway" href="'.$domain.'/fokus/sub_ghost.php?index=go&sid='.$element->id.'" target="_blank">'.$trans->__('Direktbearbeitung').'</a>':'').'
    '.($user->r('str', 'slots')?'<a class="rbutton goaway" id="se_slots">'.$trans->__('Slots').'</a>':'').'
</div>

<div class="box_save">
    <input type="submit" value="'.$trans->__('speichern').'" class="bs2" data-close="false" />
    <input type="submit" value="'.$trans->__('speichern &amp; schließen').'" class="bs2" data-close="true" />
</div>
':'
'.($v->slot?'
<div class="box">
    '.$slot['desc'].'
</div>
':'').'
').'
</form>';
?>
<?php
if($user->r('str', 'ele') && $index == 'n150')
{
    $element = $fksdb->fetch("SELECT * FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
    if(!$element)
        exit('<div class="ifehler">'.$trans->__('Ausgewähltes Element existiert nicht oder befindet sich im Papierkorb').'</div>');
        
    $ro = $base->fixedUnserialize($element->rollen);
    if(!is_array($ro)) $ro = array();
    
    $rolQ = $fksdb->query("SELECT id, titel FROM ".SQLPRE."roles WHERE papierkorb = '0' ORDER BY sort, id"); 
	
	$cf = $base->fixedUnserialize($element->cf);
        
    echo '
    <h1>'.$trans->__('Strukturelement-Einstellungen.').'</h1>
    
    <div class="box">  
        <form id="strukturelement_optionen">              
        <table>
            <tr class="more">
                <td class="left">'.$trans->__('Freigabe:').'</td>
                <td>
                    <table class="freigabe">
                        <tr>
                            <td><input type="radio" name="frei" id="ele_freiB" value="1"'.($element->frei?' checked="checked"':'').' /></td>
                            <td><label for="ele_freiB">'.$trans->__('Element freigeben').'</label></td>
                            <td class="last">'.$trans->__('(sichtbar auf Webseite)').'</td>
                        </tr>
                        <tr>
                            <td><input type="radio" name="frei" id="ele_freiA" value="0"'.(!$element->frei?' checked="checked"':'').' /></td>
                            <td><label for="ele_freiA">'.$trans->__('Element sperren').'</label></td>
                            <td class="last">'.$trans->__('(<strong>nicht</strong> sichtbar auf Webseite)').'</td>
                        </tr>
                    </table>
                </td>
            </tr>'; 
            if(count($base->getActiveTemplateConfig('files')) && (!$eleopt || $user->r('str', 'template')))
            {
                $mtempslug = $base->slug($base->getActiveTemplateConfig('name'));
                
                echo '
                <tr class="more">
                    <td class="left">'.$trans->__('Template-Datei:').'</td>
                    <td>
                        <select name="templatedatei">
                            <option value=""'.(!$element->templatedatei?' selected="selected"':'').($rechte['str']['template'] && !$rechte['str']['td'][$mtempslug]['index'] && $element->templatedatei?' disabled':'').'>'.$trans->__('Standard').'</option>';
                            foreach($base->getActiveTemplateConfig('files') as $cn => $ck)
                            {
                                $disabled = false;
                                if($rechte['str']['template'] && !$rechte['str']['td'][$mtempslug][$ck] && $element->templatedatei != $ck)
                                    $disabled = true;
                                    
                                echo '<option'.($disabled?' disabled':'').' value="'.$ck.'"'.($element->templatedatei == $ck?' selected="selected"':'').'>'.(is_numeric($cn)?$ck:$cn).'</option>';
                            }
                            echo '
                        </select>
                    </td>
                </tr>';
            }
            if(count($base->getActiveTemplateConfig('mobile')) && (!$eleopt || $user->r('str', 'td')))
            {
                echo '
                <tr class="more">
                    <td class="left">'.$trans->__('Mobile Template-Datei:').'</td>
                    <td>
                        <select name="m_templatedatei">'; 
                            foreach($base->getActiveTemplateConfig('mobile') as $cn => $ck)
                                echo '<option value="'.$ck.'"'.($element->m_templatedatei == $ck?' selected="selected"':'').'>'.(is_numeric($cn)?$ck:$cn).'</option>';
                            echo '
                        </select>
                    </td>
                </tr>';
            }
            
            if(!$eleopt || $user->r('str', 'lebensdauer'))
            {
                echo '
                <tr class="more ldauer">
                    <td class="left">'.$trans->__('Lebensdauer:').'</td>
                    <td class="zeitraumtd">
                        '.$trans->__('Dieses Strukturelement nur für folgenden Zeitraum freischalten:').'<br />
                        <table class="zeitraum">
                            <tr>
                                <td><input type="checkbox" name="vonC" class="vonbis" value="1"'.($element->anfang?' checked="checked"':'').' /></td>
                                <td'.(!$element->anfang?' class="notaktiv"':'').'>'.$trans->__('Von:').'</td>
                                <td><input type="text" name="anfang" class="datepicker" value="'.($element->anfang?date('d.m.Y', $element->anfang):'').'"'.(!$element->anfang?' disabled="disabled"':'').' /></td>
                                <td class="time'.(!$element->anfang?' notaktiv':'').'">
                                    <select name="anfangH" class="uhrzeit"'.(!$element->anfang?' disabled="disabled"':'').'>
                                        '.$base->time_options(($element->anfang?date('H', $element->anfang):0)).'
                                    </select> : 
                                    <select name="anfangM" class="uhrzeit"'.(!$element->anfang?' disabled="disabled"':'').'>
                                        '.$base->time_options(($element->anfang?date('i', $element->anfang):0), true).'
                                    </select> '.$trans->__('Uhr').'
                                </td>
                            </tr>
                            <tr>
                                <td><input type="checkbox" name="bisC" class="vonbis" value="1"'.($element->bis?' checked="checked"':'').' /></td>
                                <td'.(!$element->bis?' class="notaktiv"':'').'>'.$trans->__('Bis:').'</td>
                                <td><input type="text" name="bis" class="datepicker" value="'.($element->bis?date('d.m.Y', $element->bis):'').'"'.(!$element->bis?' disabled="disabled"':'').' /></td>
                                <td class="time'.(!$element->bis?' notaktiv':'').'">
                                    <select name="bisH" class="uhrzeit"'.(!$element->bis?' disabled="disabled"':'').'>
                                        '.$base->time_options(($element->bis?date('H', $element->bis):23)).'
                                    </select> : 
                                    <select name="bisM" class="uhrzeit"'.(!$element->bis?' disabled="disabled"':'').'>
                                        '.$base->time_options(($element->bis?date('i', $element->bis):59), true).'
                                    </select> '.$trans->__('Uhr').'
                                </td>
                            </tr>
                        </table>
                    </td>
                </tr>';
            }
            
            $cfields = $api->getCustomFields();
            
			if(count($cfields))
			{
				foreach($cfields as $k => $v)
				{
					if(!$v['global'] || !$v['name'])
						continue;
                        
                    if(count($v['restriction']))
                    {
                        if($v['restriction']['elements'] !== true && !in_array($element->id, $v['restriction']['elements']))
                            continue;
                    }
						
					echo '
					<tr class="more cf">
						<td class="left">'.$v['name'].'</td>
						<td>';
							if(!$v['type'] || $v['type'] == 'text' || $v['type'] == 'input')
							{
								echo '<input type="text" name="cf['.$k.']" value="'.$cf[$k].'" />';
							}
							elseif($v['type'] == 'textarea')
							{
								echo '<textarea name="cf['.$k.']">'.$cf[$k].'</textarea>';
							}
							elseif($v['type'] == 'checkbox')
							{
								echo '<input type="checkbox" name="cf['.$k.']" value="fks_true"'.($cf[$k] == 'fks_true'?' checked="checked"':'').' />';
							}
							elseif($v['type'] == 'select' && is_array($v['values']))
							{
								echo '<select name="cf['.$k.']">';
								foreach($v['values'] as $x => $y)
									echo '<option value="'.$x.'"'.($cf[$k] == $x?' selected="selected"':'').'>'.$y.'</option>';
								echo '                     
								</select>';
							}
						echo '
						</td>
					</tr>';
				}
			}
			echo '
            <tr class="more">
                <td colspan="2" class="tdnavi">
                    <input type="checkbox" id="is_in_navi" name="in_navi" value="1"'.($element->no_navi?' checked="checked"':'').' />
                    <label for="is_in_navi">'.$trans->__('Strukturelement in Hauptnavigation verstecken').'</label>
                    
                    <div class="navi"'.(!$element->no_navi?' style="display:block;"':'').'>
                        <p>
                            <input type="checkbox" id="open_se_new_windows" name="neues_fenster" value="1"'.($element->neues_fenster?' checked="checked"':'').' />
                            <label for="open_se_new_windows">'.$trans->__('Strukturelement in neuem Fenster öffnen').'</label>
                        </p>
                    </div>                                    
                </td>
            </tr>
            <tr class="more">
                <td colspan="2">
                    <input type="checkbox" id="is_hidden" name="is_hidden" value="1"'.($element->is_hidden?' checked="checked"':'').' />
                    <label for="is_hidden">'.$trans->__('Strukturelement in Strukturverwaltung verstecken').'</label>                                    
                </td>
            </tr>';
            
            if(!$eleopt || $user->r('str', 'rollen'))
            {
                echo '
                <tr class="more"'.(!$fksdb->count($rolQ)?' style="display:none;"':'').'>
                    <td colspan="2" class="tdrollen">
                        <input type="checkbox" id="nurrollen" name="restriction" value="1"'.(count($ro)?' checked="checked"':'').' />
                        <label for="nurrollen">'.$trans->__('Strukturelement nur gewissen Rollen zugänglich machen').'</label>
                        
                        <div class="rollen"'.(count($ro)?' style="display:blocK;"':'').'>';
                            while($rol = $fksdb->fetch($rolQ))
                            {
                                echo '
                                <input type="checkbox" id="rol_'.$rol->id.'" name="ro[]" value="'.$rol->id.'"'.(in_array($rol->id, $ro) || $rol->id == 1?' checked':'').''.($rol->id == 1?' disabled':'').' />
                                <label for="rol_'.$rol->id.'">'.$rol->titel.'</label><br />';
                            }
                            echo '
                            <p>
                                <input type="radio" id="rfA" name="rollen_fehler" value="0"'.(!$element->rollen_fehler?' checked="checked"':'').' />
                                <label for="rfA">'.$trans->__('Benutzer ohne Berechtigung erhalten bei Aufruf eine definierbare Fehlerseite').'</label><br />
                                <input type="radio" id="rfB" name="rollen_fehler" value="1"'.($element->rollen_fehler?' checked="checked"':'').' />
                                <label for="rfB">'.$trans->__('Das Strukturelement wird für Benutzer ohne Berechtigung komplett entfernt').'</label>
                            </p>
                        </div>                                    
                    </td>
                </tr>';
            }
            
            if(!$eleopt || $user->r('str', 'seo'))
            {
                echo '
                <tr class="more">
                    <td colspan="2" class="noseo">
                        <input type="checkbox" id="noseo" name="noseo" value="1"'.($element->noseo?' checked="checked"':'').' />
                        <label for="noseo">'.$trans->__('Strukturelement f&uuml;r Suchmaschinen sperren').'</label>                              
                    </td>
                </tr>';
            }
            
            echo '
            <tr class="more">
                <td colspan="2" class="nositemap">
                    <input type="checkbox" id="nositemap" name="nositemap" value="1"'.($element->nositemap?' checked="checked"':'').' />
                    <label for="nositemap">'.$trans->__('Strukturelement nicht in Sitemap anzeigen').'</label>                              
                </td>
            </tr>
        </table>
        </form>
    </div>
    <div class="box_save" style="display:block;">
        <input type="submit" class="bs2" value="'.$trans->__('speichern &amp; schließen').'" />
    </div>';    
}
?>
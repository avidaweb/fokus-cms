<?php
if($user->r('str', 'ele') && $index == 'n124')
{
    $ids = Strings::explodeCheck(',', $v->elemente);
	if(!count($ids)) exit('<div class="ifehler">'.$trans->__('Keine Elemente zum Bearbeiten ausgewählt').'</div>');
	
	echo '
    <h1>
        '.(count($ids) != 1?
        $trans->__('%1 Elemente bearbeiten', false, array(count($ids)))
        :
        $trans->__('1 Element bearbeiten')).'
    </h1>
    
    <form id="mfa_bearbeiten">
    <div class="box">
        <h2 class="calibri">'.$trans->__('Welche Einstellungen und Inhalte möchten Sie bearbeiten?').'</h2>';
        
        if(!$eleopt || $user->r('str', 'template'))
        {
            $mtempslug = $base->slug($base->getActiveTemplateConfig('name'));
            
            echo '
            <div class="area">
                <p class="checkme">
                    <input type="checkbox" name="check_td" id="check_td" value="1" />
                    <label for="check_td">'.$trans->__('Template-Datei').'</label>
                </p>
                <div class="more">
                    <select name="templatedatei">
                        <option value=""'.($rechte['str']['template'] && !$rechte['str']['td'][$mtempslug]['index']?' disabled':'').'>'.$trans->__('Standard').'</option>';
                        foreach($base->getActiveTemplateConfig('files') as $cn => $ck)
                        {
                            $disabled = false;
                            if($rechte['str']['template'] && !$rechte['str']['td'][$mtempslug][$ck])
                                $disabled = true;
                                
                            echo '<option value="'.$ck.'"'.($disabled?' disabled':'').($rechte['str']['tda'][$mtempslug] == $ck?' selected':'').'>'.(is_numeric($cn)?$ck:$cn).'</option>';
                        }
                        echo '
                    </select>
                </div>
            </div>';
        }
        
        if(count($base->getActiveTemplateConfig('mobile')) && (!$eleopt || $user->r('str', 'template')))
        {
            echo '
            <div class="area">
                <p class="checkme">
                    <input type="checkbox" name="check_tdm" id="check_tdm" value="1" />
                    <label for="check_tdm">'.$trans->__('Mobile Template-Datei').'</label>
                </p>
                <div class="more">
                    <select name="m_templatedatei">';
                        foreach($base->getActiveTemplateConfig('mobile') as $cn => $ck)
                            echo '<option value="'.$ck.'">'.(is_numeric($cn)?$ck:$cn).'</option>';
                        echo '
                    </select>
                </div>
            </div>';
        }
        
        if(!$eleopt || $user->r('str', 'lebensdauer'))
        {
            echo '
            <div class="area">
                <p class="checkme">
                    <input type="checkbox" name="check_ld" id="check_ld" value="1" />
                    <label for="check_ld">'.$trans->__('Lebensdauer').'</label>
                </p>
                <div class="more">
                    <table class="zeitraum">
                        <tr>
                            <td><input type="checkbox" name="vonC" class="vonbis" value="1" /></td>
                            <td class="xstr notaktiv">'.$trans->__('Von:').'</td>
                            <td><input type="text" name="anfang" class="datepicker" value="" disabled="disabled" /></td>
                            <td class="xstr time notaktiv">
                                <select name="anfangH" class="uhrzeit" disabled="disabled">
                                    '.$base->time_options(0).'
                                </select> : 
                                <select name="anfangM" class="uhrzeit" disabled="disabled">
                                    '.$base->time_options(0, true).'
                                </select> '.$trans->__('Uhr').'
                            </td>
                        </tr>
                        <tr>
                            <td><input type="checkbox" name="bisC" class="vonbis" value="1" /></td>
                            <td class="notaktiv">'.$trans->__('Bis:').'</td>
                            <td><input type="text" name="bis" class="datepicker" value="" disabled="disabled" /></td>
                            <td class="xstr time notaktiv">
                                <select name="bisH" class="uhrzeit" disabled="disabled">
                                    '.$base->time_options(23).'
                                </select> : 
                                <select name="bisM" class="uhrzeit" disabled="disabled">
                                    '.$base->time_options(59, true).'
                                </select> '.$trans->__('Uhr').'
                            </td>
                        </tr>
                    </table>
                </div>
            </div>';
        }
        
        echo '
        <div class="area">
            <p class="checkme">
                <input type="checkbox" name="check_navi" id="check_navi" value="1" />
                <label for="check_navi">'.$trans->__('Anzeige in Hauptnavigation').'</label>
            </p>
            <div class="more auswahl">
                <span>
                    <input type="radio" name="no_navi" id="no_navi_0" value="0" checked="checked" />
                    <label for="no_navi_0">'.$trans->__('in Hauptnavigation anzeigen').'</label>
                </span>
                <span>
                    <input type="radio" name="no_navi" id="no_navi_1" value="1" />
                    <label for="no_navi_1">'.$trans->__('nicht in Hauptnavigation anzeigen').'</label>
                </span>
            </div>
        </div>
        
        <div class="area">
            <p class="checkme">
                <input type="checkbox" name="check_sv" id="check_sv" value="1" />
                <label for="check_sv">'.$trans->__('Anzeige in Strukturverwaltung').'</label>
            </p>
            <div class="more auswahl">
                <span>
                    <input type="radio" name="is_hidden" id="is_hidden_0" value="0" checked="checked" />
                    <label for="is_hidden_0">'.$trans->__('in Strukturverwaltung anzeigen').'</label>
                </span>
                <span>
                    <input type="radio" name="is_hidden" id="is_hidden_1" value="1" />
                    <label for="is_hidden_1">'.$trans->__('nicht in Strukturverwaltung anzeigen').'</label>
                </span>
            </div>
        </div>';
        
        if(!$eleopt || $user->r('str', 'seo'))
        {
            echo '
            <div class="area">
                <p class="checkme">
                    <input type="checkbox" name="check_noseo" id="check_noseo" value="1" />
                    <label for="check_noseo">'.$trans->__('Suchmaschinen-Sichtbarkeit').'</label>
                </p>
                <div class="more auswahl">
                    <span>
                        <input type="radio" name="noseo" id="noseo_0" value="0" checked="checked" />
                        <label for="noseo_0">'.$trans->__('für Suchmaschinen freigeben').'</label>
                    </span>
                    <span>
                        <input type="radio" name="noseo" id="noseo_1" value="1" />
                        <label for="noseo_1">'.$trans->__('für Suchmaschinen sperren').'</label>
                    </span>
                </div>
            </div>';
        }
    echo '
    </div>
    </form>
    
    <div class="box_save">
        <input type="button" class="bs1" value="'.$trans->__('abbrechen').'" />
        <input type="button" class="bs2" value="'.$trans->__('weiter').'" />
    </div>';
}
?>
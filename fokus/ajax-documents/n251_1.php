<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251')
    exit($user->noRights());
           
$cf = $api->getCustomFields();
if(!is_array($cf)) $cf = array();

$spr_active = $base->fixedUnserialize($doc->sprachen);
if(!is_array($spr_active)) $spr_active = array();

$spr = $base->fixedUnserialize($doc->sprachenfelder);
if(!is_array($spr)) $spr = array();
    
if($doc->anfang || $doc->bis)
{
    $dscheck = $base->find_check_document_statusB($doc->id, $doc->anfang, $doc->bis, $doc->statusB);
    if($dscheck >= 0)
        $doc->statusB = $dscheck;
}

if($doc->statusB && $doc->statusB < 3)
{
    echo '
    <div class="ifehler">
        <strong>'. $trans->__('Dieses Dokument ist zur Zeit offline') .'</strong>
        '.($doc->statusB == 1?$trans->__('Die Lebensdauer dieses Dokuments ist begrenzt ').($doc->anfang?$trans->__('vom ').date('d.m.Y', $doc->anfang).$trans->__(' ab ').date('H:i', $doc->anfang).$trans->__(' Uhr'):'').' '.($doc->bis?$trans->__('bis zum ').date('d.m.Y', $doc->bis).$trans->__(' um ').date('H:i', $doc->bis).$trans->__(' Uhr'):''):'').'.
        '.($doc->statusB == 2?$trans->__('Das Dokument wurde manuell gesperrt. In den <a class="options">Einstellungen</a> können Sie es wieder freigeben.'):'').'
    </div>';
}


$slan = ($base->getActiveLanguagesCount() > 1?false:true);
// languages
echo '
<form class="language_dialog'.(!$doc->klasse && $slan?' single':'').'" data-active="'.($dv_active->language?$dv_active->language:$standard_language).'">
    <table class="element_sprachen">
        <tr class="bezeichner">
            <td class="breit" colspan="3">'.$trans->__('Name für interne Nutzung:').'</td>
            <td class="inp">
                <input type="text" name="titel" value="'.$doc->titel.'" required placeholder="'.$trans->__('Bezeichner des Dokuments').'" />
            </td>
            <td class="more">
                (D'.str_pad($doc->id, 5 ,'0', STR_PAD_LEFT).')
            </td>
        </tr>
    </table>';
        
    foreach($base->getActiveLanguages() as $sp)
    {
        $is_active = (in_array($sp, $spr_active)?true:false);
        
        if(!$doc->klasse)
        {
            if($slan)
            {
                echo '<input type="hidden" name="active['.$sp.']" value="1" />';
                continue;
            }
            
            echo '
            <div class="sprache">
                <table class="element_sprachen '.($is_active?'aktiv':'inaktiv').'">
                    <tr class="main">
                        <td class="auswahl">
                            <input type="hidden" name="take_content['.$sp.']" value="" />
                            <input type="checkbox" class="activate_lang" name="active['.$sp.']" id="doc_se_spracheA_'.$sp.'" value="1"'.($is_active?' checked':'').' />
                        </td>
                        <td class="flagge">
                            <label for="doc_se_spracheA_'.$sp.'"><img width="22" src="'.$trans->getFlag($sp).'" alt="" /></label>
                        </td>
                        <td class="titel">
                            <label for="doc_se_spracheA_'.$sp.'">'.$trans->__(strtoupper($sp)).'</label>
                        </td>
                    </tr>
                </table>
            </div>';
            
            continue;
        }
        
        echo '
        <div class="sprache">
            <table class="element_sprachen '.($is_active || $slan?'aktiv':'inaktiv').'">
                <tr class="main">
                    '.(!$slan?'
                    <td class="auswahl">
                        <input type="hidden" name="take_content['.$sp.']" value="" />
                        <input type="checkbox" class="activate_lang" name="active['.$sp.']" id="doc_se_spracheA_'.$sp.'" value="1"'.($is_active?' checked':'').' />
                    </td>
                    <td class="flagge">
                        <label for="doc_se_spracheA_'.$sp.'"><img width="22" src="'.$trans->getFlag($sp).'" alt="" /></label>
                    </td>
                    <td class="titel">
                        <label for="doc_se_spracheA_'.$sp.'">'.$trans->__(strtoupper($sp)).':</label>
                    </td>':'
                    <td class="breit" colspan="3">
                        <input type="hidden" name="active['.$sp.']" value="1" />
                        '.$trans->__('Öffentlicher Titel:').'
                    </td>').'
                    <td class="inp">
                        <input type="text" class="ntitle" id="doc_ntitle_'.$sp.'" data-lan="'.$sp.'" name="sprache['.$sp.'][titel]" value="'.$spr[$sp]['titel'].'"'.($is_active || $slan?'':' disabled').' />
                    </td>
                    <td class="more">
                        <a class="rbutton rollout"><span>'.$trans->__('Details').'</span></a>
                    </td>
                </tr>
                <tr class="firstrow">
                    <td class="breit" colspan="3">'.$trans->__('HTML-Titel:').'</td>
                    <td class="inp">
                        <input type="text" name="sprache['.$sp.'][htitel]" class="ht1" value="'.$spr[$sp]['htitel'].'"'.(!$spr[$sp]['htitel']?' style="display:none;"':'').($is_active || $slan?'':' disabled').' />
                        <input type="text" disabled value="'.$base->auto_title($spr[$sp]['titel'], $sp).'" class="ht2"'.($spr[$sp]['htitel']?' style="display:none;"':'').' />
                        <p class="auto">
                            <input type="checkbox" class="autotitle" id="doc_autotitle_'.$sp.'" value="1"'.(!$spr[$sp]['htitel']?' checked':'').($is_active || $slan?'':' disabled').' /> 
                            <label for="doc_autotitle_'.$sp.'">'.$trans->__('HTML-Titel automatisch generieren').'</label>
                        </p>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td class="breit" colspan="3">'.$trans->__('HTML-Beschreibung:').'</td>
                    <td class="inp">
                        <textarea class="html_desc" name="sprache['.$sp.'][desc]"'.($is_active || $slan?'':' disabled').'>'.$spr[$sp]['desc'].'</textarea>
                        <p class="zeichen">
                            <span>0</span> '.$trans->__('Zeichen').'
                        </p>
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td class="breit" colspan="3">'.$trans->__('HTML-Schlüsselworte:').'</td>
                    <td class="inp">
                        <input type="text" name="sprache['.$sp.'][tags]" value="'.$spr[$sp]['tags'].'"'.($is_active || $slan?'':' disabled').' />
                    </td>
                    <td></td>
                </tr>
                <tr>
                    <td class="breit" colspan="3">'.$trans->__('URL:').'</td>
                    <td class="inp">
                        <input type="text" name="sprache['.$sp.'][url]" class="url1" value="'.$base->slug($spr[$sp]['url']).'"'.(!$spr[$sp]['url']?' style="display:none;"':'').($is_active || $slan?'':' disabled').' />
                        <input type="text" disabled value="'.$base->slug($spr[$sp]['titel']).'" class="url2"'.($spr[$sp]['url']?' style="display:none;"':'').' />
                        <p class="url">
                            <input type="checkbox" class="autourl" id="doc_autourl_'.$sp.'" value="1"'.(!$spr[$sp]['url']?' checked':'').($is_active || $slan?'':' disabled').' /> 
                            <label for="doc_autourl_'.$sp.'">'.$trans->__('URL automatisch generieren').'</label>
                        </p>
                    </td>
                    <td></td>
                </tr>';
                    
                foreach((array)$cf as $k => $cfval)
                {
                    if(!$cfval['name'])
                        continue;
    
                    if(count($cfval['restriction']))
                    {
                        if($cfval['restriction']['documents'] !== true && !in_array($doc->id, $cfval['restriction']['documents']))
                            continue;
                    }
                        
                    if($cfval['global'])
                    {
                        $has_global_cf = true;
                        continue;
                    }
                        
                    echo '
                    <tr>
                        <td class="breit" colspan="3">'.($cfval['name']).'</td>
                        <td class="inp">';
                            if(!$cfval['type'] || $cfval['type'] == 'text' || $cfval['type'] == 'input')
                            {
                                echo '<input type="text" name="sprache['.$sp.']['.$k.']" value="'.$spr[$sp][$k].'"'.($is_active || $slan?'':' disabled').' />';
                            }
                            elseif($cfval['type'] == 'textarea')
                            {
                                echo '<textarea name="sprache['.$sp.']['.$k.']"'.($is_active || $slan?'':' disabled').'>'.$spr[$sp][$k].'</textarea>';
                            }
                            elseif($cfval['type'] == 'checkbox')
                            {
                                echo '<input type="checkbox" name="sprache['.$sp.']['.$k.']" value="fks_true"'.($spr[$sp][$k] == 'fks_true'?' checked':'').($is_active || $slan?'':' disabled').' />';
                            }
                            elseif($cfval['type'] == 'select' && is_array($cfval['values']))
                            {
                                echo '<select name="sprache['.$sp.']['.$k.']"'.($is_active || $slan?'':' disabled').'>';
                                foreach($cfval['values'] as $x => $y)
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
</form>';


// options menu
echo '
<div class="options_menu">
    <a class="rbutton goaway options">'.$trans->__('Einstellungen').'</a>
    '.($doc->klasse && $has_global_cf?'<a class="rbutton goaway custom_fields">'.$trans->__('Benutzerdefinierte Felder').'</a>':'').'
    '.($doc->klasse && $user->r('dok', 'cats')?'<a class="rbutton goaway categories">'.$trans->__('Kategorien zuweisen').'</a>':'').'
    '.(is_array($base->getActiveTemplateConfig('classes')) && $cssk?'<a class="rbutton goaway format">'.$trans->__('Dokument formatieren').'</a>':'').'
</div>
           
<table class="info">
    '.($doc->klasse?'
    <tr>
        <td>Dokumentenklasse:</td>
        <td>'.$fk['name'].'</td>
    </tr>':'').'
    <tr>
        <td>Status:</td>
        <td>
            '.$base->document_status($doc->statusA, $doc->statusB).'
        </td>
    </tr>
    '.($dv->id?'
    <tr>
        <td>Zuletzt freigegeben von:</td>
        <td>'.$base->user($dv->von_freigegeben, ' ', 'vorname', 'nachname').', am '.date('d.m.Y', $dv->timestamp_freigegeben).' um '.date('H:i', $dv->timestamp_freigegeben).' Uhr</td>
    </tr>':'').'
</table>';    
?>
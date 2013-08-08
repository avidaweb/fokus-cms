<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n251S')
    exit($user->noRights());
    
$id = $fksdb->save($_POST['id'], 1);
$spalte = $fksdb->save($_POST['spalte'], 1);

$column = $fksdb->fetch("SELECT * FROM ".SQLPRE."columns WHERE dokument = '".$id."' AND id = '".$spalte."' LIMIT 1");
if(!$column)
    exit($trans->__('Keine Spalte gefunden.'));

$abst = explode('_', $column->padding);
$padding = $abst[0];
$margin = $abst[1];

$paddingA = array();
$marginA = array();
if($padding) $paddingA = explode('|', $padding);
if($margin) $marginA = explode('|', $margin);
        
echo '
<div class="extoL extoL2 shadow" id="extoL2S">
    <form method="post">
    <div>
        <table id="ecss_0">
            <tr class="kopf">
                <td class="f1"><input type="checkbox" name="hat_css_klasse" id="mcss_0" value="1"'.($column->css_klasse?' checked="checked"':'').(!$cssk?' disabled':'').' /></td>
                <td><label for="mcss_0">'. $trans->__('Hinterlegte Formatierungsstile hinzufügen') .'</label></td>
            </tr>
            <tr'.(!$column->css_klasse?' class="inaktiv"':'').'>
                <td></td>
                <td>
                    <div class="sclasses">';
                        $classes_count = 0;
                        foreach($base->getActiveTemplateConfig('classes') as $class => $op)
                        {
                            if($op['restriction'] && $op['restriction'] != 'none' && !Strings::strExists('column', $op['restriction'], false))
                                continue;
                            if(!$op['name'])
                                continue;
                                
                            $classes_count ++;
                             
                            $checked = (Strings::strExists($class.' ', ' '.$column->css_klasse.' ')?true:false);
                            $cslug = $base->slug($class);
                                
                            echo '
                            <p>
                                <input type="checkbox" name="classes[]" value="'.$class.'" id="ccclass_'.$cslug.'"'.($checked?' checked':'').' />
                                <label for="ccclass_'.$cslug.'">'.$op['name'].'</label>
                            </p>';
                        }
                            
                        if(!$classes_count)
                        {
                            echo '<em>'. $trans->__('Es wurden noch keine für Spalten relevanten Formatierungsstile hinterlegt.') .'</em>';
                        }
                    echo '
                    </div>
                </td>
            </tr>
        </table>
    </div>
    <div class="exto_block">
        <table id="ecss_1">
            <tr class="kopf">
                <td class="f1"><input type="checkbox" name="css" id="mcss_1" value="1"'.($column->css?' checked="checked"':'').(!$cssf?' disabled':'').' /></td>
                <td colspan="2"><label for="mcss_1">'. $trans->__('Eigene Definition für Formatierung verwenden') .'</label></td>
            </tr>
            <tr'.(!$column->css?' class="inaktiv"':'').'>
                <td></td>
                <td>
                    <input id="c_text_align" type="checkbox" name="alignC" value="1"'.(!$column->css?' disabled="disabled"':'').''.($column->align?' checked="checked"':'').' /> 
                    <label for="c_text_align">'. $trans->__('Text-Ausrichtung') .'</label>
                </td>
                <td>
                    <select name="align"'.(!$column->css || !$column->align?' disabled="disabled"':'').'>
                        <option value="1"'.($column->align == 1?' selected="selected"':'').'>'. $trans->__('Linksb&uuml;ndig') .'</option>
                        <option value="2"'.($column->align == 2?' selected="selected"':'').'>'. $trans->__('Rechtsb&uuml;ndig') .'</option>
                        <option value="3"'.($column->align == 3?' selected="selected"':'').'>'. $trans->__('Zentriert') .'</option>
                        <option value="4"'.($column->align == 4?' selected="selected"':'').'>'. $trans->__('Blocksatz') .'</option>
                    </select>
                </td>
            </tr>
            <tr'.(!$column->css?' class="inaktiv"':'').'>
                <td></td>
                <td>
                    <input id="tfa" type="checkbox" name="colorC" value="1"'.(!$column->css?' disabled="disabled"':'').''.($column->color?' checked="checked"':'').' /> 
                    <label for="tfa">'. $trans->__('Text-Farbe') .'</label>
                </td>
                <td><input type="hidden" name="color" value="#'.$column->color.'" /><div class="colorSelector" id="cS1" style="background-color:#'.$column->color.';"></div></td>
            </tr>
            <tr'.(!$column->css?' class="inaktiv"':'').'>
                <td></td>
                <td>
                    <input id="bgf" type="checkbox" name="bgcolorC" value="1"'.(!$column->css?' disabled="disabled"':'').''.($column->bgcolor?' checked="checked"':'').' /> 
                    <label for="bgf">'. $trans->__('Hintergrund-Farbe') .'</label>
                </td>
                <td><input type="hidden" name="bgcolor" value="#'.$column->bgcolor.'" /><div class="colorSelector" id="cS3" style="background-color:#'.$column->bgcolor.';"></div></td>
            </tr>
            <tr'.(!$column->css?' class="inaktiv"':'').'>
                <td></td>
                <td>
                    <input id="CSSRahmen" type="checkbox" name="border" value="1"'.(!$column->css?' disabled="disabled"':'').''.($column->border?' checked="checked"':'').' /> 
                    <label for="CSSRahmen">'. $trans->__('Rahmen anzeigen:') .'</label>
                </td>
                <td>
                    <select name="borderkat"'.(!$column->css || !$column->border?' disabled="disabled"':'').'>
                        <option value="1"'.($column->border == 1?' selected="selected"':'').'>'. $trans->__('durchgehend') .'</option>
                        <option value="2"'.($column->border == 2?' selected="selected"':'').'>'. $trans->__('gestrichelt') .'</option>
                        <option value="3"'.($column->border == 3?' selected="selected"':'').'>'. $trans->__('punktiert') .'</option>
                    </select>
                    <input type="hidden" name="bordercolor" value="#'.$column->bordercolor.'" /><div class="colorSelector" id="cS2" style="background-color:#'.$column->bordercolor.';"></div>
                </td>
            </tr>
            <tr'.(!$column->css?' class="inaktiv"':'').'>
                <td></td>
                <td class="abstaende">
                    <input id="CSSAbstand" type="checkbox" name="abstand" value="1"'.(!$column->css?' disabled="disabled"':'').''.($padding?' checked="checked"':'').' /> 
                    <label for="CSSAbstand">'. $trans->__('Innenabstand nutzen:') .'</label>
                </td>
                <td class="abstaende">
                    <p><input type="text" name="padding_0" class="sm" value="'.$paddingA[0].'"'.(!$column->css || !$padding?' disabled="disabled"':'').' /> '. $trans->__('Pixel oben') .'</p>
                    <p><input type="text" name="padding_1" class="sm" value="'.$paddingA[1].'"'.(!$column->css || !$padding?' disabled="disabled"':'').' /> '. $trans->__('Pixel unten') .'</p>
                    <p><input type="text" name="padding_2" class="sm" value="'.$paddingA[2].'"'.(!$column->css || !$padding?' disabled="disabled"':'').' /> '. $trans->__('Pixel rechts') .'</p>
                    <p><input type="text" name="padding_3" class="sm" value="'.$paddingA[3].'"'.(!$column->css || !$padding?' disabled="disabled"':'').' /> '. $trans->__('Pixel links') .'</p>
                </td>
            </tr>
            <tr'.(!$column->css?' class="inaktiv"':'').'>
                <td></td>
                <td class="abstaende">
                    <input id="CSSAbstand2" type="checkbox" name="abstandm" value="1"'.(!$column->css?' disabled="disabled"':'').''.($margin?' checked="checked"':'').' /> 
                    <label for="CSSAbstand2">'. $trans->__('Außenabstand nutzen:') .'</label>
                </td>
                <td class="abstaende">
                    <p><input type="text" name="margin_0" class="sm" value="'.$marginA[0].'"'.(!$column->css || !$margin?' disabled="disabled"':'').' /> '. $trans->__('Pixel oben') .'</p>
                    <p><input type="text" name="margin_1" class="sm" value="'.$marginA[1].'"'.(!$column->css || !$margin?' disabled="disabled"':'').' /> '. $trans->__('Pixel unten') .'</p>
                    <p><input type="text" name="margin_2" class="sm" value="'.$marginA[2].'"'.(!$column->css || !$margin?' disabled="disabled"':'').' /> '. $trans->__('Pixel rechts') .'</p>
                    <p><input type="text" name="margin_3" class="sm" value="'.$marginA[3].'"'.(!$column->css || !$margin?' disabled="disabled"':'').' /> '. $trans->__('Pixel links') .'</p>
                </td>
            </tr>
        </table>
    </div>
    <div class="exto_block">
        <button>'. $trans->__('speichern') .'</button>
        <button>'. $trans->__('abbrechen') .'</button>
    </div>
    </form>
</div>';
?>
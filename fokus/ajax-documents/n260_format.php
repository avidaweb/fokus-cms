<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin() || (!$cssf && !$cssk))
    exit($user->noRights());
    
$id = $fksdb->save($_POST['id'], 1);
$block = $fksdb->save($_POST['block']);
$ibid = $fksdb->save($_POST['ibid']);
$blockindex = $fksdb->save($_POST['blockindex']);
$dkdatei = $fksdb->save($_POST['dkdatei']);

$dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von, titel, timestamp FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if(!$dokument || !$dve)
    exit('no document');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());
    
if($dokument->klasse && !$ibid)
    exit('cant format');


if(!$dokument->klasse)
{
    $d = $fksdb->fetch("SELECT css, css_klasse, padding, color, font, bgcolor, border, bordercolor, align, margin, spalten FROM ".SQLPRE."blocks WHERE dokument = '".$id."' AND id = '".$block."' LIMIT 1");
}
else
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);
    
    $d = new stdClass();
    $d->css = $ki[$ibid]['html'][$blockindex]['css'];
    $d->css_klasse = $ki[$ibid]['html'][$blockindex]['css_klasse'];
    $d->padding = $ki[$ibid]['html'][$blockindex]['padding'];
    $d->color = $ki[$ibid]['html'][$blockindex]['color'];
    $d->font = $ki[$ibid]['html'][$blockindex]['font'];
    $d->bgcolor = $ki[$ibid]['html'][$blockindex]['bgcolor'];
    $d->border = $ki[$ibid]['html'][$blockindex]['border'];
    $d->bordercolor = $ki[$ibid]['html'][$blockindex]['bordercolor'];
    $d->align = $ki[$ibid]['html'][$blockindex]['align'];
    $d->margin = $ki[$ibid]['html'][$blockindex]['margin'];
    $d->spalten = $ki[$ibid]['html'][$blockindex]['spalten'];
}

if(!$d)
    exit('failed');
       
$abst = explode('_', $d->padding);
$padding = $abst[0];
$margin = $abst[1];

$paddingA = array();
$marginA = array();
if($padding) $paddingA = explode('|', $padding);
if($margin) $marginA = explode('|', $margin);
    
echo '
<h1>'. $trans->__('Inhaltselement formatieren.') .'</h1>

<div class="box">
    <div class="extoL extoL2" id="extoL2">
        <form method="post">
        <div class="exto_block">
            <table id="ecss_0">
                <tr class="kopf">
                    <td class="f1"><input type="checkbox" name="hat_css_klasse" id="mcss_0" value="1"'.($d->css_klasse?' checked="checked"':'').(!$cssk?' disabled':'').' /></td>
                    <td><label for="mcss_0" class="calibri">'. $trans->__('Hinterlegte Formatierungsstile hinzufügen') .'</label></td>
                </tr>
                <tr'.(!$d->css_klasse?' class="inaktiv"':'').'>
                    <td></td>
                    <td>
                        <div class="sclasses">';
                            $classes_count = 0;
                        
                            foreach($base->getActiveTemplateConfig('classes') as $class => $op)
                            {
                                if($op['restriction'] && $op['restriction'] != 'none' && !Strings::strExists('content', $op['restriction'], false))
                                    continue;
                                if(!$op['name'])
                                    continue;
                                    
                                $classes_count ++;
                                 
                                $checked = (Strings::strExists($class.' ', ' '.$d->css_klasse.' ')?true:false);
                                $cslug = $base->slug($class);
                                    
                                echo '
                                <p>
                                    <input type="checkbox" name="classes[]" value="'.$class.'" id="cclass_'.$cslug.'"'.($checked?' checked':'').' />
                                    <label for="cclass_'.$cslug.'">'.$op['name'].'</label>
                                </p>';
                            }
                            
                            if(!$classes_count)
                            {
                                echo '<em>'. $trans->__('Es wurden noch keine für Inhaltselemente relevanten Formatierungsstile hinterlegt.') .'</em>';
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
                    <td class="f1"><input type="checkbox" name="css" id="mcss_1" value="1"'.($d->css?' checked="checked"':'').(!$cssf?' disabled':'').' /></td>
                    <td colspan="2"><label for="mcss_1" class="calibri">'. $trans->__('Eigene Definition für Formatierung verwenden') .'</label></td>
                </tr>
                <tr'.(!$d->css?' class="inaktiv"':'').'>
                    <td></td>
                    <td class="a">
                        <input id="talignC" type="checkbox" name="alignC" value="1"'.(!$d->css?' disabled':'').($d->align?' checked':'').' /> 
                        <label for="talignC">'. $trans->__('Text-Ausrichtung') .'</label>
                    </td>
                    <td class="b">
                        <select name="align"'.(!$d->align?' disabled="disabled"':'').'>
                            <option value="1"'.($d->align == 1?' selected="selected"':'').'>'. $trans->__('Linksb&uuml;ndig') .'</option>
                            <option value="2"'.($d->align == 2?' selected="selected"':'').'>'. $trans->__('Rechtsb&uuml;ndig') .'</option>
                            <option value="3"'.($d->align == 3?' selected="selected"':'').'>'. $trans->__('Zentriert') .'</option>
                            <option value="4"'.($d->align == 4?' selected="selected"':'').'>'. $trans->__('Blocksatz') .'</option>
                        </select>
                    </td>
                </tr>
                <tr'.(!$d->css?' class="inaktiv"':'').'>
                    <td></td>
                    <td class="a">
                        <input id="fontC" type="checkbox" name="fontC" value="1"'.(!$d->css?' disabled':'').($d->font?' checked':'').' /> 
                        <label for="fontC">'. $trans->__('Text-Gr&ouml;&szlig;e') .'</label>
                    </td>
                    <td class="b">
                        <input type="text" name="font" class="sm" value="'.$d->font.'"'.(!$d->font?' disabled="disabled"':'').' /> Pixel
                    </td>
                </tr>
                <tr'.(!$d->css?' class="inaktiv"':'').'>
                    <td></td>
                    <td class="a">
                        <input id="css3spaltenC" type="checkbox" name="spaltenC" value="1"'.(!$d->css?' disabled="disabled"':'').''.($d->spalten?' checked="checked"':'').' /> 
                        <label for="css3spaltenC">'. $trans->__('CSS3-Spalten') .'</label>
                    </td>
                    <td class="b">
                        <select name="spalten"'.(!$d->spalten?' disabled="disabled"':'').'>';
                            for($z=1;$z<13;$z++)
                                echo '<option value="'.$z.'"'.($d->spalten == $z?' selected="selected"':'').'>'.$z.'</option>';
                        echo '
                        </select>
                    </td>
                </tr>
                <tr'.(!$d->css?' class="inaktiv"':'').'>
                    <td></td>
                    <td class="a">
                        <input id="tfa" type="checkbox" name="colorC" value="1"'.(!$d->css?' disabled="disabled"':'').''.($d->color?' checked="checked"':'').' /> 
                        <label for="tfa">'. $trans->__('Text-Farbe') .'</label>
                    </td>
                    <td class="b">
                        <input type="hidden" name="color" value="#'.$d->color.'" />
                        <div class="colorSelector" id="cS1" style="background-color:#'.$d->color.';"></div>
                    </td>
                </tr>
                <tr'.(!$d->css?' class="inaktiv"':'').'>
                    <td></td>
                    <td class="a">
                        <input id="bgf" type="checkbox" name="bgcolorC" value="1"'.(!$d->css?' disabled="disabled"':'').''.($d->bgcolor?' checked="checked"':'').' /> 
                        <label for="bgf">'. $trans->__('Hintergrund-Farbe') .'</label>
                    </td>
                    <td class="b">
                        <input type="hidden" name="bgcolor" value="#'.$d->bgcolor.'" />
                        <div class="colorSelector" id="cS3" style="background-color:#'.$d->bgcolor.';"></div>
                    </td>
                </tr>
                <tr'.(!$d->css?' class="inaktiv"':'').'>
                    <td></td>
                    <td class="a">
                        <input id="CSSshow_border" type="checkbox" name="border" value="1"'.(!$d->css?' disabled="disabled"':'').''.($d->border?' checked="checked"':'').' /> 
                        <label for="CSSshow_border">'. $trans->__('Rahmen anzeigen:') .'</label>
                    </td>
                    <td class="b">
                        <select class="borderkat" name="borderkat"'.(!$d->border?' disabled="disabled"':'').'>
                            <option value="1"'.($d->border == 1?' selected="selected"':'').'>'. $trans->__('durchgehend') .'</option>
                            <option value="2"'.($d->border == 2?' selected="selected"':'').'>'. $trans->__('gestrichelt') .'</option>
                            <option value="3"'.($d->border == 3?' selected="selected"':'').'>'. $trans->__('punktiert') .'</option>
                        </select>
                        <input type="hidden" name="bordercolor" value="#'.$d->bordercolor.'" /><div class="colorSelector" id="cS2" style="background-color:#'.$d->bordercolor.';"></div>
                    </td>
                </tr>
                <tr'.(!$d->css?' class="inaktiv"':'').'>
                    <td></td>
                    <td class="abstaende a">
                        <input id="CSSabstandnutzen" type="checkbox" name="abstand" value="1"'.(!$d->css?' disabled="disabled"':'').''.($padding?' checked="checked"':'').' /> 
                        <label for="CSSabstandnutzen">'. $trans->__('Innenabstand nutzen:') .'</label>
                    </td>
                    <td class="abstaende b">
                        <p><input type="text" name="padding_0" class="sm" value="'.$paddingA[0].'"'.(!$padding?' disabled':'').' /> Pixel oben</p>
                        <p><input type="text" name="padding_1" class="sm" value="'.$paddingA[1].'"'.(!$padding?' disabled':'').' /> Pixel unten</p>
                        <p><input type="text" name="padding_2" class="sm" value="'.$paddingA[2].'"'.(!$padding?' disabled':'').' /> Pixel rechts</p>
                        <p><input type="text" name="padding_3" class="sm" value="'.$paddingA[3].'"'.(!$padding?' disabled':'').' /> Pixel links</p>
                    </td>
                </tr>
                <tr'.(!$d->css?' class="inaktiv"':'').'>
                    <td></td>
                    <td class="abstaende a">
                        <input id="CSSabstandnutzen2" type="checkbox" name="abstandm" value="1"'.(!$d->css?' disabled="disabled"':'').''.($margin?' checked="checked"':'').' /> 
                        <label for="CSSabstandnutzen2">'. $trans->__('Außenabstand nutzen:') .'</label>
                    </td>
                    <td class="abstaende b">
                        <p><input type="text" name="margin_0" class="sm" value="'.$marginA[0].'"'.(!$margin?' disabled':'').' /> '. $trans->__('Pixel oben') .'</p>
                        <p><input type="text" name="margin_1" class="sm" value="'.$marginA[1].'"'.(!$margin?' disabled':'').' /> '. $trans->__('Pixel unten') .'</p>
                        <p><input type="text" name="margin_2" class="sm" value="'.$marginA[2].'"'.(!$margin?' disabled':'').' /> '. $trans->__('Pixel rechts') .'</p>
                        <p><input type="text" name="margin_3" class="sm" value="'.$marginA[3].'"'.(!$margin?' disabled':'').' /> '. $trans->__('Pixel links') .'</p>
                    </td>
                </tr>
            </table>
        </div>
        </form>
    </div>

</div>
            
<div class="box_save" style="display:block;">
    <input type="submit" value="'. $trans->__('verwerfen') .'" class="bs1" />
    <input type="submit" value="'. $trans->__('speichern') .'" class="bs2" />
</div>';
?>
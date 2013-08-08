<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n251S2')
    exit($user->noRights());
    
$id = $fksdb->save($_POST['id'], 1);
$spalte = $fksdb->save($_POST['spalte'], 1);
$f = $_POST['f'];
parse_str($f, $fa); 

$css_klasse = '';
if($fa['hat_css_klasse'] && is_array($fa['classes']))
{
    foreach($fa['classes'] as $cl)
        $css_klasse .= ' '.$cl.' ';
    $css_klasse = Strings::removeDoubleSpace($css_klasse);
}

$align = ($fa['alignC']?intval($fa['align']):0);

$border = ($fa['border']?$fa['borderkat']:0);
$bordercolor = ($fa['border']?$fa['bordercolor']:'');

$color = ($fa['colorC']?str_replace('#', '', $fa['color']):'');
$bgcolor = ($fa['bgcolorC']?str_replace('#', '', $fa['bgcolor']):'');
$bordercolor = str_replace('#', '', $bordercolor);

$padding = 0;
$margin = 0;

if($fa['padding_0'] || $fa['padding_1'] || $fa['padding_2'] || $fa['padding_3'])
    $padding = ($fa['padding_0']?$fa['padding_0']:0).'|'.($fa['padding_1']?$fa['padding_1']:0).'|'.($fa['padding_2']?$fa['padding_2']:0).'|'.($fa['padding_3']?$fa['padding_3']:0);

if($fa['margin_0'] || $fa['margin_1'] || $fa['margin_2'] || $fa['margin_3'])
    $margin = ($fa['margin_0']?$fa['margin_0']:0).'|'.($fa['margin_1']?$fa['margin_1']:0).'|'.($fa['margin_2']?$fa['margin_2']:0).'|'.($fa['margin_3']?$fa['margin_3']:0);
    
$abstand = $padding.'_'.$margin;

$upt = $fksdb->query("UPDATE ".SQLPRE."columns SET css = '".$fa['css']."', css_klasse = '".$css_klasse."', color = '".$color."', bgcolor = '".$bgcolor."', border = '".$border."', bordercolor = '".$bordercolor."', align = '".$align."', padding = '".$abstand."' WHERE id = '".$spalte."' AND dokument = '".$id."' LIMIT 1"); 

$d = $fksdb->fetch("SELECT dversion_edit, id FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");
?>
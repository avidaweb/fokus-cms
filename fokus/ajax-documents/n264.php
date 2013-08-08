<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n264')
    $user->noRights();

$id = $fksdb->save($_POST['id'], 1);
$block = $fksdb->save($_POST['block']);
$ibid = $fksdb->save($_POST['ibid']);
$blockindex = $fksdb->save($_POST['blockindex']);
$f = $_POST['f'];
parse_str($f, $fa);

$dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT klasse_inhalt, id FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

$css_klasse = '';
if($fa['hat_css_klasse'] && is_array($fa['classes']))
{
    foreach($fa['classes'] as $cl)
        $css_klasse .= ' '.$cl.' ';
    $css_klasse = Strings::removeDoubleSpace($css_klasse);
}

$border = ($fa['border']?$fa['borderkat']:0);
$color = ($fa['colorC']?str_replace('#', '', $fa['color']):'');
$bgcolor = ($fa['bgcolorC']?str_replace('#', '', $fa['bgcolor']):'');
$bordercolor = str_replace('#', '', $fa['bordercolor']);

$padding = 0;
$margin = 0;

if($fa['abstand'] && (isset($fa['padding_0']) || isset($fa['padding_1']) || isset($fa['padding_2']) || isset($fa['padding_3'])))
    $padding = ($fa['padding_0']?$fa['padding_0']:0).'|'.($fa['padding_1']?$fa['padding_1']:0).'|'.($fa['padding_2']?$fa['padding_2']:0).'|'.($fa['padding_3']?$fa['padding_3']:0);

if($fa['abstandm'] && (isset($fa['margin_0']) || isset($fa['margin_1']) || isset($fa['margin_2']) || isset($fa['margin_3'])))
    $margin = ($fa['margin_0']?$fa['margin_0']:0).'|'.($fa['margin_1']?$fa['margin_1']:0).'|'.($fa['margin_2']?$fa['margin_2']:0).'|'.($fa['margin_3']?$fa['margin_3']:0);

$abstand = $padding.'_'.$margin;

$font = ($fa['fontC']?$fa['font']:'');
$align = ($fa['alignC']?$fa['align']:0);
$spalten = ($fa['spaltenC']?$fa['spalten']:0);

if(!$dokument->klasse && !$dokument->produkt)
{
    $upt = $fksdb->query("UPDATE ".SQLPRE."blocks SET css = '".$fa['css']."', css_klasse = '".$css_klasse."', color = '".$color."', font = '".$font."', bgcolor = '".$bgcolor."', border = '".$border."', bordercolor = '".$bordercolor."', padding = '".$abstand."', spalten = '".$spalten."', align = '".$align."' WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
}
elseif($ibid)
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);

    $ki[$ibid]['html'][$blockindex]['css'] = $fa['css'];
    $ki[$ibid]['html'][$blockindex]['css_klasse'] = $css_klasse;
    $ki[$ibid]['html'][$blockindex]['padding'] = $abstand;
    $ki[$ibid]['html'][$blockindex]['color'] = $color;
    $ki[$ibid]['html'][$blockindex]['font'] = $font;
    $ki[$ibid]['html'][$blockindex]['bgcolor'] = $bgcolor;
    $ki[$ibid]['html'][$blockindex]['border'] = $border;
    $ki[$ibid]['html'][$blockindex]['bordercolor'] = $bordercolor;
    $ki[$ibid]['html'][$blockindex]['align'] = $align;
    $ki[$ibid]['html'][$blockindex]['margin'] = $fa['margin'];
    $ki[$ibid]['html'][$blockindex]['spalten'] = $spalten;

    $kis = serialize($ki);

    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$dve->id."' LIMIT 1");
}


$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$dokument->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($id);
?>
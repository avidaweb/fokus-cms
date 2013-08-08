<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n261qe')
    exit($user->noRights());

$id = $fksdb->save($_POST['id'], 1);
$block = $fksdb->save($_POST['block'], 1);
$ibid = $fksdb->save($_POST['ibid']);
$blockindex = $fksdb->save($_POST['blockindex']);
$html = rawurldecode($fksdb->save(Strings::tidyHTML(Strings::removeBadHTML(Strings::cleanString($_POST['html'])))));

$dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if(!$dokument || !$dve)
    exit();

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

if(!$dokument->klasse && !$dokument->produkt)
{
    $upd2 = $fksdb->query("UPDATE ".SQLPRE."blocks SET html = '".$html."' WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
}
else
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);
    if(!$ibid)
    {
        $ki[$block]['html'] = $html;
    }
    else
    {
        $ki[$ibid]['html'][$blockindex]['html'] = $html;
    }
    $kis = serialize($ki);

    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$dve->id."' LIMIT 1");
}

echo ($html?Strings::cut(strip_tags(htmlspecialchars_decode($html)), 180):'<span class="no_content">(Noch kein Inhalt)</span>');

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$dve->id."' LIMIT 1");
$base->create_dk_snippet($id);
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n263')
    exit($user->noRights());

$id = $fksdb->save($_POST['id'], 1);
$block = $fksdb->save($_POST['block']);
$ibid = $fksdb->save($_POST['ibid']);
$blockindex = $fksdb->save($_POST['blockindex']);
$nt = $fksdb->save($_POST['nt']);

$dokument = $fksdb->fetch("SELECT dversion_edit, id, klasse, produkt, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT * FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

if(!$dokument->klasse)
{
    $upt = $fksdb->query("UPDATE ".SQLPRE."blocks SET type = '".$nt."' WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
}
elseif($ibid)
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);
    $ki[$ibid]['html'][$blockindex]['type'] = $nt;
    $kis = serialize($ki);

    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$dokument->dversion_edit."' LIMIT 1");
}

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$dokument->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($id);
?>
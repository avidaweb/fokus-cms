<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n257')
    exit($user->noRights());

$aktiv = $fksdb->save($_GET['aktiv'], 1);
$id = $fksdb->save($_GET['id'], 1);
$sort = $_GET['sort'];
$sort = str_replace("block[]=", "", $sort);
$sort = explode('&', $sort);

$d = $fksdb->fetch("SELECT dversion_edit, id, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$d) exit();

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

for($x=0; $x<count($sort); $x++)
{
    $update = $fksdb->query("UPDATE ".SQLPRE."blocks SET sort = '".($x + 1)."' WHERE id = '".$fksdb->save($sort[$x])."' AND spalte = '".$aktiv."'");
}

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($d->id);
?>
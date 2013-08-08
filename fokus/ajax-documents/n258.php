<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n258')
    exit($user->noRights());

$id = $fksdb->save($_POST['id'], 1);
$a = $fksdb->save($_POST['a']);
$d = $fksdb->fetch("SELECT dversion_edit, id, anfang, bis, timestamp_freigegeben, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$d) exit();

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

if($a == 'del' && $user->r('dok', 'del'))
{
    $del = $fksdb->query("UPDATE ".SQLPRE."documents SET papierkorb = '1' WHERE id = '".$id."' LIMIT 1");
    $user->trash('dokument', $id);
}
elseif($a == 'sperr')
{
    $update = $fksdb->query("UPDATE ".SQLPRE."documents SET gesperrt = '1', statusB = '".$base->find_document_statusB(1, $d->anfang, $d->bis, $d->timestamp_freigegeben)."' WHERE id = '".$id."' LIMIT 1");
}
elseif($a == 'entsperr')
{
    $update = $fksdb->query("UPDATE ".SQLPRE."documents SET gesperrt = '0', statusB = '".$base->find_document_statusB(0, $d->anfang, $d->bis, $d->timestamp_freigegeben)."' WHERE id = '".$id."' LIMIT 1");
}
elseif($a == 'vorlage')
{
    $titel = $fksdb->save($_POST['titel']);
    $update = $fksdb->query("UPDATE ".SQLPRE."documents SET vorlage = '".$titel."' WHERE id = '".$id."' LIMIT 1");
}
elseif($a == 'del_vorlage')
{
    $update = $fksdb->query("UPDATE ".SQLPRE."documents SET vorlage = '' WHERE id = '".$id."' LIMIT 1");
}
?>
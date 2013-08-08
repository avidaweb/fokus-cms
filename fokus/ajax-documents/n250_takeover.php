<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok', 'acopy') || $index != 'n250_takeover')
    exit($user->noRights());

$id = $fksdb->save($_GET['id'], 1);
$d = $fksdb->fetch("SELECT dversion_edit FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");

$fksdb->update("document_versions", array(
    "von" => $user->getID()
), array(
    "id" => $d->dversion_edit,
    "dokument" => $id
), 1);
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'pn') || !$suite->rm(10) || $index != 'n642')
    exit($user->noRights());

$msg = $fksdb->save($_REQUEST['msg'], 1);

$pn = $fksdb->fetch("SELECT text FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."' AND id = '".$msg."' LIMIT 1");
$update = $fksdb->query("UPDATE ".SQLPRE."messages SET gelesen = '1' WHERE benutzer = '".$user->getID()."' AND id = '".$msg."' LIMIT 1");

exit(htmlspecialchars_decode($pn->text));
?>
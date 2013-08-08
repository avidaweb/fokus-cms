<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if($index != 'n661' || !$user->r('kom', 'pinnwandedit'))
    exit($user->noRights());

$t = base64_encode($_POST['t']);

$tbenutzer = $base->user($base->getOpt('notiz_von'), ' ', 'vorname', 'nachname');
if($base->getOpt('notiz_time') > time() - 60 && $base->getOpt('notiz_von') != $user->getID())
    exit($tbenutzer);

$upd = $fksdb->query("UPDATE ".SQLPRE."options SET notiz = '".$t."', notiz_von = '".$user->getID()."', notiz_time = '".time()."' WHERE id = '1' LIMIT 1");
?>
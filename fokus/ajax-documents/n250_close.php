<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n250_close')
    exit($user->noRights());

$id = $fksdb->save($_GET['id'], 1);

$fksdb->update("documents", array(
    "closed_by" => $user->getID(),
    "closed_to" => (time() + 15)
), array(
    "id" => $id
), 1);
?>
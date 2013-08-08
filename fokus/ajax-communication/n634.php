<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'kkanal') || $index != 'n634')
    exit($user->noRights());

$ids = (array)$_REQUEST['ids'];
$id = $fksdb->save($_REQUEST['id']);
$action = $fksdb->save($_REQUEST['action']);

if(count($ids))
{
    $ids = array_map("intval", $ids);
    $fksdb->query("UPDATE ".SQLPRE."comments SET frei = '".($action == 'open'?1:0)."' WHERE id IN (".implode(',', $ids).")");
    exit();
}

$c = $fksdb->fetch("SELECT id, frei FROM ".SQLPRE."comments WHERE id = '".$id."' LIMIT 1");
$fksdb->query("UPDATE ".SQLPRE."comments SET frei = '".($c->frei?0:1)."' WHERE id = '".$c->id."' LIMIT 1");
?>
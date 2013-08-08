<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'kkanal') || $index != 'n635')
    exit($user->noRights());

$ids = (array)$_REQUEST['ids'];
$id = $fksdb->save($_REQUEST['id']);
$type = $fksdb->save($_REQUEST['type']);

$where = "id = '".$id."' LIMIT 1";
if(count($ids))
{
    $ids = array_map("intval", $ids);
    $where = "id IN (".implode(',', $ids).")";
}

$fksdb->query("DELETE FROM ".SQLPRE."".($type == 'ds'?"records":"comments")." WHERE ".$where);
?>
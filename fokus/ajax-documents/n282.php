<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok', 'ezsb') || $index != 'n282')
    exit($user->noRights());

$a = $fksdb->save($_REQUEST['a']);
$name = $fksdb->save($_REQUEST['name']);
$id = $fksdb->save($_REQUEST['id'], 1);

if($a == 'new' && $name)
{
    $fksdb->insert("responsibilities", array(
        "name" => $name
    ));
}
elseif($a == 'del')
{
    $del = $fksdb->query("UPDATE ".SQLPRE."responsibilities SET papierkorb = '1' WHERE id = '".$id."' LIMIT 1");
    $user->trash('zsb', $id);
}
?>
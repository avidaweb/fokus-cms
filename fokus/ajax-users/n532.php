<?php
if($user->r('per', 'prolle') && $index == 'n532')
{
    $id = $fksdb->save($_GET['id']);
    
    $del = $fksdb->query("DELETE FROM ".SQLPRE."user_roles WHERE id = '".$id."' LIMIT 1");
}
?>
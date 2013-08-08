<?php
if($user->r('per', 'rollen') && $index == 'n542')
{
    $id = $fksdb->save($_POST['id']);
    if($id != 1)
    {
        $del = $fksdb->query("UPDATE ".SQLPRE."roles SET papierkorb = '1' WHERE id = '".$id."' LIMIT 1");
        $user->trash('rolle', $id);   
    }
}
?>
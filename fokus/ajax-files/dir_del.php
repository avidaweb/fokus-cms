<?php
if($index == 'dir_del' && $user->r('dat', 'dir'))
{
    $ordner = $fksdb->save($_REQUEST['ordner']);
    $dir = $fksdb->fetch("SELECT dir FROM ".SQLPRE."files WHERE id = '".$ordner."' LIMIT 1");
    
    $update = $fksdb->query("UPDATE ".SQLPRE."files SET dir = '".$dir->dir."' WHERE dir = '".$ordner."'");
    $dir = $fksdb->query("DELETE FROM ".SQLPRE."files WHERE id = '".$ordner."' LIMIT 1");
}
?>
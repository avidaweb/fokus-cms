<?php
if($index == 'dir_rename' && $user->r('dat', 'dir'))
{
    $ordner = $fksdb->save($_REQUEST['ordner']);
    $titel = $fksdb->save($_REQUEST['titel']);
    
    if($titel)
        $update = $fksdb->query("UPDATE ".SQLPRE."files SET titel = '".$titel."' WHERE id = '".$ordner."' LIMIT 1");
}
?>
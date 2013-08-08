<?php
if($index == 'dir_move_in' && $user->r('dat', 'edit'))
{
    $dir = $fksdb->save($_REQUEST['dir'], 1);
    $stacks = $fksdb->save($_REQUEST['stacks']);
    $stacks = trim(str_replace('bild_', '', $stacks));
    
    if(!$stacks)
        exit();
        
    $fksdb->query("UPDATE ".SQLPRE."files SET dir = '".$dir."' WHERE id IN (".$stacks.")");
}
?>
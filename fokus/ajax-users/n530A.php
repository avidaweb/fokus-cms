<?php
if($user->r('per') && $index == 'n530A')
{
    $f = $fksdb->save($_GET['firma']);
    $firma = $fksdb->fetch("SELECT telA, telB, telC FROM ".SQLPRE."companies WHERE id = '".$f."' LIMIT 1");
    echo $firma->telA.' '.$firma->telB; 
}
?>
<?php
if($user->r('str', 'ele') && $index == 'n140release')
{
    $up = $fksdb->query("UPDATE ".SQLPRE."elements SET frei = '1' WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' LIMIT 1");
}
?>
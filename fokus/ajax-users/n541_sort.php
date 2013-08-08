<?php
if($user->r('per', 'rollen') && $index == 'n541_sort')
{
    $sort = $_POST['sort'];
    parse_str($sort, $sorta);  
    
    foreach($sorta['rol'] as $o1 => $o2)
    {
        $countup ++;
        $update = $fksdb->query("UPDATE ".SQLPRE."roles SET sort = '".$countup."' WHERE id = '".$fksdb->save($o2)."' AND id != '1' LIMIT 1"); 
    }
}
?>
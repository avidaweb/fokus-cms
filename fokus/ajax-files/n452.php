<?php
if($index == 'n452' && $user->r('dat', 'edit')) 
{
    $id = $fksdb->save($_GET['id']);
    $v = $fksdb->save($_GET['v']);
    
    $stack = $fksdb->fetch("SELECT id FROM ".SQLPRE."files WHERE id = '".$id."' LIMIT 1");
    $file = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' AND id = '".$v."' LIMIT 1");
    
    if($file)
    {
        $updt = $fksdb->query("UPDATE ".SQLPRE."files SET last_type = '".$file->type."', last_timestamp = '".$base->getTime()."', last_ausrichtung = '".$file->ausrichtung."', last_grafik = '".$file->grafik."', last_autor = '".$file->autor."' WHERE id = '".$stack->id."' LIMIT 1");
                
        $updt2 = $fksdb->query("UPDATE ".SQLPRE."file_versions SET timestamp = '".$base->getTime()."' WHERE stack = '".$stack->id."' AND id = '".$v."' LIMIT 1");
    }
}
?>
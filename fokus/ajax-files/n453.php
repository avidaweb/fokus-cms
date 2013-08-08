<?php
if($index == 'n453' && $user->r('dat', 'del')) 
{
    $id = $fksdb->save($_GET['id']);
    $v = $fksdb->save($_GET['v']);
    
    $stack = $fksdb->fetch("SELECT * FROM ".SQLPRE."files WHERE id = '".$id."' LIMIT 1");
    $aktuell = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");
    $file = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' AND id = '".$v."' LIMIT 1");
    $gesamt = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."'"));
    
    if($file)
    {
        if($aktuell->id == $file->id)
        {
            if($gesamt == 1)
            {
                $del = $fksdb->query("UPDATE ".SQLPRE."files SET papierkorb = '1' WHERE id = '".$stack->id."' LIMIT 1");
                $user->trash('bild', $stack->id);
                echo 'all';
            }
            else
            {
                $next = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' AND id != '".$v."' ORDER BY timestamp DESC LIMIT 1");
    
                $updt = $fksdb->query("UPDATE ".SQLPRE."files SET last_type = '".$next->type."', last_timestamp = '".$next->timestamp."', last_ausrichtung = '".$next->ausrichtung."', last_grafik = '".$next->grafik."', last_autor = '".$next->autor."' WHERE id = '".$stack->id."' LIMIT 1");              
            }
        }
        
        $del = $fksdb->query("DELETE FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' AND id = '".$v."' LIMIT 1");
    }
}
?>
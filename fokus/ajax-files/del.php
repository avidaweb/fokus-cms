<?php
if($index == 'del' && $user->r('dat', 'del')) // Gesamten Stack löschen
{ 
    $id = $fksdb->save($_REQUEST['id']); 
    $up = $fksdb->fetch("SELECT id FROM ".SQLPRE."files WHERE id = '".$id."' LIMIT 1"); 
    
    if($up->id)
    {
        $del = $fksdb->query("UPDATE ".SQLPRE."files SET papierkorb = '1' WHERE id = '".$id."' LIMIT 1"); 
        $user->trash('bild', $id);
    }
} 
?>
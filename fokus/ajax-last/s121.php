<?php
if($index == 's121' && $user->r('suc', 'papierkorb')) // Wiederherstellen
{   
    $a = explode('_', $fksdb->save($_GET['atr']));
    $type = $a[0];
    $aid = $a[1];
    
    $del = $fksdb->query("DELETE FROM ".SQLPRE."recent_items WHERE type = '".$type."' AND aid = '".$aid."' AND papierkorb = '1'");
    
    if($type == 'dokument')
    {
        $upt = $fksdb->query("UPDATE ".SQLPRE."documents SET papierkorb = '0' WHERE id = '".$aid."' LIMIT 1");
    }
    elseif($type == 'bild')
    {
        $upt = $fksdb->query("UPDATE ".SQLPRE."files SET papierkorb = '0' WHERE id = '".$aid."' LIMIT 1");
    }  
    elseif($type == 'element')
    {
        $upt = $fksdb->query("UPDATE ".SQLPRE."elements SET papierkorb = '0' WHERE id = '".$aid."' LIMIT 1"); 
    }
    elseif($type == 'firma')
    {
        $upt = $fksdb->query("UPDATE ".SQLPRE."companies SET papierkorb = '0' WHERE id = '".$aid."' LIMIT 1"); 
    }
    elseif($type == 'personen')
    {
        $upt = $fksdb->query("UPDATE ".SQLPRE."users SET papierkorb = '0' WHERE id = '".$aid."' LIMIT 1"); 
    }
    elseif($type == 'rolle')
    {
        $upt = $fksdb->query("UPDATE ".SQLPRE."roles SET papierkorb = '0' WHERE id = '".$aid."' LIMIT 1"); 
    }
    elseif($type == 'struktur')
    {
        $upt = $fksdb->query("UPDATE ".SQLPRE."structures SET papierkorb = '0' WHERE id = '".$aid."' LIMIT 1"); 
    }
    elseif($type == 'zsb')
    {
        $upt = $fksdb->query("UPDATE ".SQLPRE."responsibilities SET papierkorb = '0' WHERE id = '".$aid."' LIMIT 1"); 
    }
}
?>
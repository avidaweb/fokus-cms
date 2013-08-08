<?php
if($index == 's481') // Pinnwand speichern
{
    $t = base64_encode($_POST['t']);
    
    $upd = $fksdb->query("UPDATE ".SQLPRE."users SET notiz = '".$t."' WHERE id = '".$user->getID()."' LIMIT 1");
}
?>
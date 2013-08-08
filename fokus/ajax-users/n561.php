<?php
if($user->r('per', 'del') && $index == 'n561')
{
    $id = $fksdb->save($_REQUEST['id']);
    
    $rC = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."user_roles WHERE benutzer = '".$id."' AND rolle = '1'"));
    if($rC && $user->getRole() != 1)
        exit($trans->__('Keine Rechte da Superadministrator'));
    
    $del = $fksdb->query("UPDATE ".SQLPRE."users SET papierkorb = '1' WHERE id = '".$id."' LIMIT 1");
    $user->trash('personen', $id);
}
?>
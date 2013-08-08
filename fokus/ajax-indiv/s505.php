<?php
if($index != 's505' || !$user->r('fks', 'customize'))
    exit($user->noRights());
    
parse_str($_POST['f'], $fa);

$fksdb->update("users", array(
    "indiv" => serialize($fa)
), array(
    "id" => $user->getID()
), 1);

exit();
?>
<?php
if($index != 's515' || !$user->isAdmin())
    exit($user->noRights());
    
parse_str($_POST['f'], $fa);
    
$fksdb->update("users", array(
    "anrede" => $fa['anrede'],
    "vorname" => $fa['vorname'],
    "nachname" => $fa['nachname'],
    "namenszusatz" => $fa['namenszusatz'],
    "email" => $fa['email']
), array(
    "id" => $user->getID()
), 1);
    
    
if(!$fa['new_pw'] || (empty($fa['pw']) && empty($fa['pw_t'])))
    exit();
    
$pw = ($fa['pw_klar']?$user->getPasswordHash($fa['pw_t']):$user->getPasswordHash($fa['pw']));

$fksdb->update("users", array(
    "pw" => $pw
), array(
    "id" => $user->getID()
), 1);

setcookie($user->getCookiesAliases('password'), $pw, (time() + 35 * 24 * 60 * 60), '/');
?>
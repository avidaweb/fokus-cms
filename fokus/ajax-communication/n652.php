<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'livetalk') || !$suite->rm(10) || $index != 'n652')
    exit($user->noRights());

$text = $fksdb->save($_POST['text']);

if(!$text)
    exit('no input');

$fksdb->insert("livetalk", array(
    "benutzer" => $user->getID(),
    "text" => $text,
    "timestamp" => time()
));
?>
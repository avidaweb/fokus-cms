<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');

$index = $fksdb->save($_REQUEST['index']);
$rel = $fksdb->save($_REQUEST['rel']);

if(!$suite->rm(4) || !$user->r('fks', 'ghost'))
    exit($user->noRights());

if($index == 'go')
{ 
    $user->setGhost(true);

    $sid = $fksdb->save($_REQUEST['sid'], 1);
    $did = $fksdb->save($_REQUEST['did'], 1);

    if($sid && $did)
        $base->go(DOMAIN.'/'.$sid.'/'.$did.'/reload/');
    elseif($sid)
        $base->go(DOMAIN.'/'.$sid.'/reload/');
    $base->go(DOMAIN);
}
<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');

$index = $fksdb->save($_REQUEST['index']);
$rel = $fksdb->save($_REQUEST['rel']);
$a = $fksdb->save($_REQUEST['a']);

$kat = $fksdb->save($_REQUEST['kat']);
    
$add_to_pic = array(0 => '', 1 => '_2', 2 => '_2', 3 => '_3');

if(!$user->r('dat'))
    exit($user->noRights());

ignore_user_abort(true);
set_time_limit(0);


$load_ajax = 'ajax-files/'.$index.'.php';
if(!file_exists($load_ajax))
    exit('no_file');
require($load_ajax);
?>
<?php 
define('IS_AJAX', true, true);
require('header.php');

$action = $fksdb->save($_GET['action']); 

$api->executeHook('ajax', $classes);
$api->executeHook('ajax_'.$action, $classes);

$fksdb->close();
unset($fksdb, $base, $suite, $api, $trans, $user);
exit();
?>
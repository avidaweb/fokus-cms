<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');

$index = $fksdb->save($_REQUEST['index']);


$load_ajax = 'ajax-editor/'.$index.'.php';
if(!file_exists($load_ajax))
    exit('no_file');
require($load_ajax);
?>
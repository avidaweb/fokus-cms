<?php
define('IS_BACKEND_DEEP', true, true);

require_once('../../inc/header.php');
require_once('../login.php');

require_once('widget-fks.php');

$widgets = $api->getWidgets();
$saved = array();

parse_str($_POST['f'], $f);
$prio = $f['prio'];
    
foreach($widgets as $wkey => $widget)
{
    $saved[$wkey]['prio'] = intval($prio[$wkey]);
}

$user->setData(array(
    "widgets" => $base->array_to_db($saved)
));
?>
<?php
define('IS_BACKEND_DEEP', true, true);

require_once('../../inc/header.php');
require_once('../login.php');
require_once('../../inc/classes.backend/class.widgets.php');

require_once('widget-fks.php');

$id = $fksdb->save($_POST['id']);

$widget = new Widgets($classes, $id);
exit($widget->output());
?>
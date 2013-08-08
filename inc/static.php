<?php
error_reporting(0);

require('classes.other/class.static.php');
$static = new StaticFiles($_GET['type'], $_GET['files']);
exit($static->outputFiles());
?>
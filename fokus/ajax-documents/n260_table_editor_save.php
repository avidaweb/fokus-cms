<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n260_table_editor_save')
    exit($user->noRights());

parse_str($_POST['f'], $f);
$v = $base->db_to_array($f['value']);

$x = $fksdb->save($_POST['x'], 1);
$y = $fksdb->save($_POST['y'], 1);
$text = $_POST['text'];

$v[$y][$x] = $text;

exit($base->array_to_db($v));
?>
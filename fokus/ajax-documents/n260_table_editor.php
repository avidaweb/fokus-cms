<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n260_table_editor')
    exit($user->noRights());

parse_str($_POST['f'], $f);
$v = $base->db_to_array($f['value']);

$x = $fksdb->save($_POST['x'], 1);
$y = $fksdb->save($_POST['y'], 1);

$e = $v[$y][$x];
if(get_magic_quotes_gpc())
    $e = stripslashes($e);

echo '
<h1>'. $trans->__('Tabellenzelle bearbeiten.') .'</h1>

<div class="box">
    <textarea>'.$e.'</textarea>
</div>

<div class="box_save" style="display:block;">
    <input type="submit" class="bs1" value="'. $trans->__('verwerfen') .'" />
    <input type="submit" class="bs2" value="'. $trans->__('speichern') .'" />
</div>';
?>
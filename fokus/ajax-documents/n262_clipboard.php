<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n262_clipboard' || !$suite->rm(4))
    exit($user->noRights());

$id = $fksdb->save($_POST['id'], 1);
$block = $fksdb->save($_POST['block']);

$user->clipboard('inhaltselement', $block, '', $id);
?>
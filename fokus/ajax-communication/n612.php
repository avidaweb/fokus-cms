<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'nledit') || !$suite->rm(5) || $index != 'n612')
    exit($user->noRights());

$dele = $fksdb->query("DELETE FROM ".SQLPRE."newsletters WHERE id = '".$rel."' LIMIT 1");
?>
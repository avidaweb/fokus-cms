<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n252a')
    exit($user->noRights());

require('n252.php');
?>
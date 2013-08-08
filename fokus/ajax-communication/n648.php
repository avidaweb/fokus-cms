<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'pn') || !$suite->rm(10) || $index != 'n648')
    exit($user->noRights());

exit($fksdb->count("SELECT id FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."' AND an = '".$user->getID()."' AND gelesen = '0'"));
?>
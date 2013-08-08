<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'pn') || !$suite->rm(10) || $index != 'n647')
    exit($user->noRights());

$limit = $fksdb->save($_REQUEST['limit']);
$b = $fksdb->save($_REQUEST['b']);

$pnQ = $fksdb->count("SELECT id FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."'".($b?" AND (an = '".$b."' OR von = '".$b."')":"")." GROUP BY vid");

if($pnQ > $limit)
    echo '<a>'.$trans->__('+ Ältere Nachrichten anzeigen').'</a>';
?>
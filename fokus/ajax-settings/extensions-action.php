<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('fks', 'extensions') || $index != 'extensions-action')
    exit($user->noRights());
    
$extensions = $api->getExtensions(false, true, false);
$db_ext = $base->getOpt('extensions');

$id = $fksdb->save($_POST['id']);
$action = $fksdb->save($_POST['action']);
$ext = $extensions[$id];

if(!$action || !$ext)
    exit();
    
if($action == 'activate' || $action == 'deactivate')
{
    $db_ext[$id] = array(
        'activated' => ($action == 'activate'?true:false),
        'changed' => $action,
        'version' => $ext['config']['version'].''
    );
    
    $base->setOpt('extensions', $db_ext, true);
}

exit();
?>
<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');
require_once('../inc/classes.backend/class.notifications.php');

if(!$_GET['task'])
{
    $api->executeHook('init_notifications', $classes);
    
    $dir = ROOT.'fokus/notifications';
    $handle = opendir($dir);
    while($file = readdir ($handle)) 
    {
        if(!file_exists($dir.'/'.$file) || !is_file($dir.'/'.$file))
            continue;
        
        include($dir.'/'.$file);
    } 
    closedir($handle); 
    
    
    $notifications = new Notifications($classes, ($fksdb->save($_GET['first'], 1)?true:false));
    $notifications->getNew();
    
    exit(json_encode($notifications->output()));
}
elseif($_GET['task'] == 'delete')
{
    $notifications = new Notifications($classes);
    $notifications->delete($_GET['delid']);
}
elseif($_GET['task'] == 'delete-all')
{
    $notifications = new Notifications($classes);
    $notifications->deleteAll();
}
?>
<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
$api->executeHook('init_widgets', $classes);
    
$dir = ROOT.'fokus/widgets';
$handle = opendir($dir);
while($file = readdir($handle)) 
{
    if(!file_exists($dir.'/'.$file) || !is_file($dir.'/'.$file))
        continue;
    
    include($dir.'/'.$file);
} 
closedir($handle); 
?>
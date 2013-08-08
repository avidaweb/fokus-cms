<?php
if(!$user->r('fks', 'opt') || $index != 'errors-clear')
    exit($user->noRights());

$filename = ROOT.'content/export/fehler.txt';
if (is_writable($filename)) 
{
    if($handle = fopen($filename, "w")) 
    {
        fwrite($handle, '');
        fclose($handle);
    }
}
?>
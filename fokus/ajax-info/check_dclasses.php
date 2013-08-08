<?php
if($index == 'check_dclasses')
{
    error_reporting(E_ALL ^ E_NOTICE);
    $ordner = "../content/dklassen";
    
    if(is_dir($ordner))
    {
        $handle = opendir($ordner);
        while ($file = readdir ($handle)) 
        {
            if($file != "." && $file != "..") 
            {
                $fk = $base->open_dklasse($ordner.'/'.$file);
            }
        }
    }
    
    exit('finish');
}   
?>
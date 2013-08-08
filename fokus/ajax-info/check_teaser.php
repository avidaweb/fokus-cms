<?php
if($index == 'check_teaser')
{
    error_reporting(E_ALL ^ E_NOTICE);
    $ordner = "../content/stklassen";
    
    if(is_dir($ordner))
    {
        $handle = opendir($ordner);
        while ($file = readdir ($handle)) 
        {
            if($file != "." && $file != "..") 
            {
                $fk = $base->open_stklasse($ordner.'/'.$file);
            }
        }
    }
    
    exit('finish');
}     
?>
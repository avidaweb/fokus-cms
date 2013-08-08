<?php
define('IS_BACKEND_DEEP', true, true);

require_once('../../inc/header.php');
require_once('../login.php');

require_once('widget-fks.php');

$sizes = array(1 => 185, 2 => 385, 3 => 585);
$pos = array(0 => 5, 1 => 205, 2 => 405);

$mx = array();
$my = array();

$widgets = $api->getWidgetsSorted();

$last = 0;

foreach($widgets as $wkey => $widget)
{
    $width = $sizes[$widget['width']];
    $height = $sizes[$widget['height']];
    
    $x = 0;
    $y = 0;
    
    while(true)
    {
        $break = true;
        
        if($widget['width'] + $x > 3)
        {
            $break = false;
        }
        else
        {   
            for($ty = $y; $ty < $y + $widget['height']; $ty ++)
            {
                for($tx = $x; $tx < $x + $widget['width']; $tx ++)
                {
                    if($mx[$ty][$tx])
                        $break = false;
                }
            }
        }
        
        if($break)
            break;
            
        if($x < 2)
        {
            $x ++;
        }
        else
        {
            $x = 0;
            $y ++;
        }
    }
    
    for($ty = $y; $ty < $y + $widget['height']; $ty ++)
    {
        for($tx = $x; $tx < $x + $widget['width']; $tx ++)
            $mx[$ty][$tx] = true;
    }
    
    $left = $pos[$x];
    $top = $y * 200 + 15;
    
    if($top + $height > $last)
        $last = $top + $height;
    
    echo '
    <div class="widget" data-id="'.$wkey.'" style="width: '.$width.'px; height: '.$height.'px; left: '.$left.'px; top: '.$top.'px;">
        <div class="loading"></div>
    </div>';    
}

if($last)
{
    echo '<span class="fpadding" style="top: '.$last.'px;"></span>';    
}
?>
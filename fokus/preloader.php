<?php
echo '
<script type="text/javascript">
    var bilder_preload = new Array();';

    $countpics = 0;
    function preload_images($dir, $counter = 0)
    {
        $handle = opendir($dir);
        $filetypes = array('jpg','gif','jpeg','png');
        while ($file = readdir ($handle)) 
        {
            $file_info = pathinfo($file); 
            $extension = strtolower($file_info["extension"]);
            if($file != "." && $file != ".." && !is_dir($dir.'/'.$file) && in_array($extension, $filetypes))  
            {
                echo '
                bilder_preload.push("'.$dir.'/'.$file.'")';
                
                $counter ++;
            }
        }
        
        return $counter;
    }  
     
    $countpics = preload_images('images', $countpics);
    $countpics = preload_images('images/icons', $countpics);
    $countpics = preload_images('images/flags', $countpics);
    $countpics = preload_images('images/blocksymbols', $countpics);
    $countpics = preload_images('images/colorpicker', $countpics);
    $countpics = preload_images('images/nr', $countpics);
    $countpics = preload_images('ckeditor/skins/kama', $countpics);
    $countpics = preload_images('ckeditor/skins/kama/images', $countpics);
    
echo '
</script>';
?>
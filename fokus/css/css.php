<?php
function remove_junk($str) 
{
    $str = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str);
    $str = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $str);
    return $str;
}

header("Content-type: text/css; charset=utf-8");
header("Expires: ".date("D, j M Y H:i:s", (time() + 86400 * 7))." GMT"); 
header("Pragma: cache"); 
header("Cache-Control: store, cache");  
        
$files = array();

$filestring = stripslashes($_GET['files']);

$filesT = explode('|', $filestring);

foreach($filesT as $f)
{
    $file = $f.'.css'; 
    
    if(file_exists($file))
        $files[] = $file;
}

if(!count($files))
    exit();

if(extension_loaded('zlib'))
   ob_start('ob_gzhandler');

ob_start("remove_junk");
foreach($files as $file)
    include($file);
ob_end_flush();
exit();
?>
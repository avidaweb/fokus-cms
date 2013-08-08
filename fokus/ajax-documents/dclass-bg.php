<?php
error_reporting(0);

function rgb2array($rgb) 
{
    return array(
        base_convert(substr($rgb, 0, 2), 16, 10),
        base_convert(substr($rgb, 2, 2), 16, 10),
        base_convert(substr($rgb, 4, 2), 16, 10),
    );
}

header("Content-Type: image/jpeg; charset=utf-8");

header("Last-Modified: ".gmdate("D, d M Y H:i:s", (time() - 30 * 24 * 60 * 60))." GMT"); 
header("Pragma: cache"); 
header("Cache-Control: store, cache");
header("Expires: ".date("D, j M Y H:i:s", (time() + 30 * 24 * 60 * 60))." GMT");

$color = htmlentities(substr($_GET['color'], 0, 6));
if(!$color) $color = '88d7ff';
$rgb = rgb2array($color);

$img = imagecreatetruecolor(7, 1);
$ncolor = imagecolorallocate($img, $rgb[0], $rgb[1], $rgb[2]);
imagefill($img, 0, 0, $ncolor);
imagejpeg($img);

exit();
?>
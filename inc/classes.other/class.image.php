<?php
Class Image
{
    private static $fksdb = null;

    private $id = 0, $width = 0, $height = 0, $version = 0, $name = '';
    private $quality = 80, $file = null, $file_version = null, $physical = '', $dimension = array();
    private $cache_filename = '', $cache_file = '';
    private $image = null, $output_image = null;

    public function __construct()
    {
        $this->setServerSettings();

        $this->defineConstants();
        $this->includeFiles();

        $this->getImageVars();
        $this->getQuality();
        $this->getFile();
        $this->getFileVersion();
        $this->getPhysical();
        $this->getDimension();

        $this->getCacheFile();

        $this->createImage();
        $this->createCroppedImage();
        $this->createThumbnailImage();

        $this->outputImage();
    }

    private function outputImage()
    {
        if($this->file_version->type == 'jpg')
        {
            imagejpeg($this->output_image);
            imagejpeg($this->output_image, $this->cache_file, $this->quality);
        }
        if($this->file_version->type == 'png')
        {
            imagepng($this->output_image);
            imagepng($this->output_image, $this->cache_file, (9 - round((($this->quality / 10 * 9) / 10), 0)));
        }
        if($this->file_version->type == 'gif')
        {
            imagegif($this->output_image);
            imagegif($this->output_image, $this->cache_file);
        }

        imagedestroy($this->output_image);
    }

    private function outputImageCache()
    {
        $this->setHeader($this->file_version->type);

        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->file_version->timestamp)." GMT");
        header("Pragma: cache");
        header("Cache-Control: store, cache");
        header("Expires: ".date("D, j M Y H:i:s", (time() + 86400 * 14))." GMT");

        header("Content-length: ".filesize($this->cache_file));
        readfile($this->cache_file);
        exit();
    }

    private function outputImageError($msg)
    {
        $this->setHeader('png');

        $width = ($this->width?$this->width:400);
        $height = ($this->height?$this->height:($this->width?$this->width:400));

        $im = imagecreatetruecolor($width, $height);

        $bg_color = imagecolorallocate($im, 251, 240, 240);
        imagefill($im, 0, 0, $bg_color);

        $text_color = imagecolorallocate($im, 111, 6, 6);
        imagestring($im, ceil($width / 100), 5, 5,  'Error: '.$msg, $text_color);

        $bordercolors = $text_color;
        $x = 0;
        $y = 0;
        $w = $width - 1;
        $h = $height - 1;
        imageline($im, $x,$y,$x,$y+$h,$bordercolors);
        imageline($im, $x,$y,$x+$w,$y,$bordercolors);
        imageline($im, $x+$w,$y,$x+$w,$y+$h,$bordercolors);
        imageline($im, $x,$y+$h,$x+$w,$y+$h,$bordercolors);

        imagepng($im);
        imagedestroy($im);

        exit();
    }


    private function createImage()
    {
        $this->setHeader($this->file_version->type);

        if($this->file_version->type == 'jpg')
            $this->image = imagecreatefromjpeg($this->physical);
        elseif($this->file_version->type == 'png')
            $this->image = imagecreatefrompng($this->physical);
        elseif($this->file_version->type == 'gif')
            $this->image = imagecreatefromgif($this->physical);
    }

    private function createCroppedImage()
    {
        if(!$this->width || !$this->height)
            return false;

        $divide = floatval($this->width / $this->height);
        $old_width = $this->dimension['width'];
        $old_height = $this->dimension['height'];

        if(($this->dimension['width'] / $this->dimension['height']) > $divide)
        {
            $old_height = $this->dimension['height'];
            $old_width = intval($this->dimension['height'] / $this->height * $this->width);
        }
        else if(($this->dimension['width'] / $this->dimension['height']) < $divide)
        {
            $old_width = $this->dimension['width'];
            $old_height = intval($this->dimension['width'] / $this->width * $this->height);
        }

        $image_x = 0;
        $image_y = 0;

        if($this->file->cropped == 0 || $this->file->cropped == 2 || $this->file->cropped == 7)
            $image_x = (($this->dimension['width'] - $old_width) * 0.5);
        elseif($this->file->cropped == 1 || $this->file->cropped == 4 || $this->file->cropped == 6)
            $image_x = 0;
        elseif($this->file->cropped == 3 || $this->file->cropped == 5 || $this->file->cropped == 8)
            $image_x = $this->dimension['width'] - $old_width;

        if($this->file->cropped == 0 || $this->file->cropped == 4 || $this->file->cropped == 5)
            $image_y = (($this->dimension['height'] - $old_height) * 0.5);
        elseif($this->file->cropped == 1 || $this->file->cropped == 2 || $this->file->cropped == 3)
            $image_y = 0;
        elseif($this->file->cropped == 6 || $this->file->cropped == 7 || $this->file->cropped == 8)
            $image_y = $this->dimension['height'] - $old_height;


        $canvas = imagecreatetruecolor($old_width, $old_height);
        $canvas = $this->setAlpha($canvas);

        imagecopyresampled($canvas, $this->image, 0, 0, $image_x, $image_y, $old_width, $old_height, $old_width, $old_height);
        imagedestroy($this->image);

        $this->output_image = imagecreatetruecolor($this->width, $this->height);
        $this->output_image = $this->setAlpha($this->output_image);

        imagecopyresampled($this->output_image, $canvas, 0, 0, 0, 0, $this->width, $this->height, $old_width, $old_height);
        imagedestroy($canvas);
    }

    private function createThumbnailImage()
    {
        if($this->width && $this->height)
            return false;

        $thumb_width = $this->width;
        $thumb_height = $this->height;

        if($this->width)
        {
            $factor = $this->dimension['width'] / $thumb_width;
            $thumb_height = intval($this->dimension['height'] / $factor);
        }
        elseif($this->height)
        {
            $factor = $this->dimension['height'] / $thumb_height;
            $thumb_width = intval($this->dimension['width'] / $factor);
        }

        $this->output_image = ImageCreateTrueColor($thumb_width, $thumb_height);
        $this->output_image = $this->setAlpha($this->output_image);

        imagecopyresampled($this->output_image, $this->image, 0, 0, 0, 0, $thumb_width, $thumb_height, $this->dimension['width'], $this->dimension['height']);
        imagedestroy($this->image);
    }

    private function setAlpha($image)
    {
        if($this->file_version->type == 'jpg')
            return $image;

        imagealphablending($image, false);
        imagesavealpha($image, true);

        return $image;
    }

    private function setHeader($type)
    {
        if($type == 'jpg' || $type == 'jpeg')
            header('Content-Type: image/jpeg;');
        elseif($type == 'png' || $type == 'pneg')
            header('Content-Type: image/png;');
        elseif($type == 'gif')
            header('Content-Type: image/gif;');
        else
            $this->outputImageError('no valid filetype');
    }

    private function setServerSettings()
    {
        ini_set('display_errors', '0');
        ini_set('memory_limit', '512M');
        error_reporting(0);
        set_time_limit(0);
    }

    private function defineConstants()
    {
        define('ROOT', '../');
        define('DEPENDENCE', true, true);
    }

    private function includeFiles()
    {
        require(ROOT.'fokus-config.php');
        require(ROOT.'inc/classes.database/database-select.php');

        self::$fksdb = $fksdb;
    }

    private function getImageVars()
    {
        $this->id = intval($_GET['id']);
        $this->width = intval($_GET['w']);
        $this->height = intval($_GET['h']);
        $this->name = strip_tags($_GET['s']);

        if(strpos($this->name, '~fids~') === false)
            return true;

        preg_match('#~fids~(.*)~fide~#isU', $this->name, $sp);
        $this->version = intval($sp[1]);
    }

    private function getQuality()
    {
        $quality = intval(self::$fksdb->data("SELECT thumb_quality FROM ".SQLPRE."options WHERE id = '1' LIMIT 1", "thumb_quality"));
        if(!$quality)
            return false;
        $this->quality = $quality;
    }

    private function getFile()
    {
        $this->file = self::$fksdb->fetch("SELECT kat, id, last_timestamp, cropped FROM ".SQLPRE."files WHERE id = '".intval($this->id)."' LIMIT 1");

        if(!$this->file)
            $this->outputImageError('invalid file');
    }

    private function getFileVersion()
    {
        $this->file_version = self::$fksdb->fetch("SELECT id, file, type, width, height, timestamp FROM ".SQLPRE."file_versions WHERE stack = '".$this->file->id."'".($this->version?" AND id = '".$this->version."'":"")." ORDER BY timestamp DESC LIMIT 1");

        if(!$this->file)
            $this->outputImageError('invalid file version');
    }

    private function getPhysical()
    {
        $this->physical = ROOT.'content/uploads/bilder/'.$this->file_version->file.'.'.$this->file_version->type;
    }

    private function getDimension()
    {
        list($w, $h) = getimagesize($this->physical);

        $this->dimension = array(
            'width' => $w,
            'height' => $h
        );
    }

    private function getCacheFile()
    {
        $this->cache_filename = md5($this->file->id.$this->file_version->id.$this->width.$this->height.$this->file_version->timestamp.$this->file->last_timestamp.$this->file->cropped.$this->quality.$this->file_version->file);
        $this->cache_file = ROOT.'content/uploads/bilder/cache_'.$this->cache_filename.'.'.$this->file_version->type;

        if(!$this->width && !$this->height)
            $this->cache_file = ROOT.'content/uploads/bilder/'.$this->file_version->file.'.'.$this->file_version->type;

        if(!file_exists($this->cache_file))
            return false;

        $this->outputImageCache();
    }
}
?>
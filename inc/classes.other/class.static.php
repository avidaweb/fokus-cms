<?php
Class StaticFiles
{
    private $files = '';
    private $type = '';
    private $last_modified = 0;

    public function __construct($type, $filestring)
    {
        if($type != 'css' && $type != 'js')
            return false;
        $this->type = $type;

        $this->files = $this->getFiles($filestring);
        $this->secureFiles();

        $this->getLastModified();
        $this->setContentType();
        $this->setCaching();
        $this->setGzip();
    }

    public function outputFiles()
    {
        if(!count($this->files))
            return false;

        ob_start();
        foreach((array)$this->files as $file)
        {
            if(!$file)
                continue;
            echo "/* File: ".$file." */\n";
            echo $this->cleanFile($this->correctPaths(file_get_contents($file), $file))."\n\n";
        }
        ob_end_flush();
    }

    private function getFiles($filestring)
    {
        if(!$filestring)
            return array();

        $filestring = gzuncompress(base64_decode(str_replace(array('-', '_'), array('/', '+'), $filestring)));
        return explode('|', $filestring);
    }

    private function secureFiles()
    {
        if(!count($this->files))
            exit();

        foreach($this->files as $k => $v)
        {
            $this->files[$k] = $this->secureUrl($v);
        }
    }

    private function secureUrl($url)
    {
        $parts = explode('.', $url);
        $ending = $parts[(count($parts) - 1)];

        if($this->type == 'css' && $ending == 'css')
            return $url;
        if($this->type == 'js' && $ending == 'js')
            return $url;
        return '';
    }

    private function correctPaths($content, $file)
    {
        if($this->type == 'js')
            return $content;

        $base = $this->getFileBase($file);

        $content = str_replace(array(
            "url(../",
            "url('../",
            'url("../',
            "url(images",
            "url('images",
            'url("images',
            "url(img",
            "url('img",
            'url("img'
        ), array(
            "url(".$base."/../",
            "url('".$base."/../",
            'url("'.$base.'/../',
            "url(".$base."/images",
            "url('".$base."/images",
            'url("'.$base.'/images',
            "url(".$base."/img",
            "url('".$base."/img",
            'url("'.$base.'/img'
        ), $content);

        return $content;
    }

    private function getFileBase($file)
    {
        $paths = explode('/', $file);
        array_pop($paths);
        return implode('/', $paths);
    }

    private function setContentType()
    {
        if($this->type == 'css')
            header("Content-type: text/css; charset=utf-8");
        elseif($this->type == 'js')
            header("Content-type: application/javascript; charset=utf-8");
    }

    private function getLastModified()
    {
        if(!count($this->files))
            return false;

        foreach((array)$this->files as $file)
        {
            if(!$file)
                continue;

            $last_modified = filemtime($file);
            if($last_modified > $this->last_modified)
                $this->last_modified = $last_modified;
        }

        if(!$this->last_modified)
            $this->last_modified = time() - (8 * 24 * 60 * 60);

        return true;
    }

    private function setCaching()
    {
        header("Last-Modified: ".gmdate("D, d M Y H:i:s", $this->last_modified)." GMT");
        header("Expires: ".date("D, j M Y H:i:s", (time() + 86400 * 30))." GMT");
        header("Pragma: cache");
        header("Cache-Control: store, cache");
    }

    private function setGzip()
    {
        if(extension_loaded('zlib'))
            ob_start('ob_gzhandler');
    }

    private function cleanFile($str)
    {
        $str = preg_replace('!/\*[^*]*\*+([^/][^*]*\*+)*/!', '', $str);
        $str = str_replace(array("\r\n", "\r", "\n", "\t", '  ', '    ', '    '), '', $str);
        $str = strip_tags($str);
        return $str;
    }
}
?>
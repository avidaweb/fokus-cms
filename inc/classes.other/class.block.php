<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Block 
{
    private $name, $short, $class, $html_tag, $width, $window, $content, $script_callback, $css_callback;
    
    function __construct($opt = array(), $window = '', $content = '')
    {
        $this->name = $opt['name'];
        $this->short = $opt['short'];
        $this->class = $opt['class'];
        $this->html_tag = $opt['html_tag'];
        $this->width = $opt['width'];
        $this->script_callback = ($opt['js_file']?$opt['js_file']:$opt['script_callback']);
        $this->css_callback = ($opt['css_file']?$opt['css_file']:$opt['css_callback']);
        
        if($window)
            $this->setWindow($window);
        if($content)
            $this->setContent($content);
    }
    
    public function getShort()
    {
        return $this->short;
    }
    
    public function getName()
    {
        return $this->name;
    }
    
    public function getCssClass()
    {
        return $this->class;
    }
    
    public function getHtmlTag()
    {
        return $this->html_tag;
    }
    
    public function getWidth()
    {
        return $this->width;
    }
    
    public function getScriptCallback()
    {
        return $this->script_callback;
    }
    
    public function getCssCallback()
    {
        return $this->css_callback;
    }
    
    public function setWindow($window)
    {
        $this->window = $window;
    }
    
    public function getWindow()
    {
        return $this->window;   
    }
    
    public function setContent($content)
    {
        $this->content = $content;
    }
    
    public function getContent()
    {
        return $this->content;   
    }
}
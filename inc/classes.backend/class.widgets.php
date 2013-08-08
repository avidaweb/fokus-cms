<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Widgets 
{
    private static $static, $base, $api, $fksdb, $user, $suite, $trans; 
    
    private $id = '';
    private $widget = null;    
    
    public function __construct($static, $id = '')
    {
        // static
        self::$static = $static;
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
        
        $this->id = $id;
        $this->start();
    }
    
    public function start()
    {
        $this->widget = self::$api->getWidget($this->id);
        if(!$this->widget)
            exit(self::$trans->__('Interner Fehler: Widget %1 wurde nicht gefunden.', false, array($this->id)));
        return false;
    }
    
    public function output()
    {
        $rtn = '
        <h5>'.$this->getHeadline().'</h5>
        <div class="body">';
        $rtn .= $this->getBody();
        $rtn .= '</div>';
        return $rtn;
    }
    
    private function getHeadline()
    {
        $title = $this->widget['title'];
        $click = $this->getClick();
        
        if(!$click)
            return $title;
            
        if(is_array($click))
            return '<a data-function="'.$click[0].'" data-attr="'.$click[1].'" class="function">'.$title.'</a>';
            
        if(filter_var($click, FILTER_VALIDATE_URL) !== FALSE)
            return '<a href="'.$click.'" target="_blank" class="url">'.$title.'</a>';  
        
        if(Strings::strExists('|!|', $click))
        {
            $clickdata = explode('|!|', $click);
            return '<a data-function="'.$clickdata[0].'" data-attr="'.$clickdata[1].'" class="function">'.$title.'</a>';
        }
        
        return '<a data-function="'.$click.'" class="function">'.$title.'</a>';
    }
    
    private function getBody()
    {
        if(!is_callable($this->widget['callback']))
            return ''; 
            
        return call_user_func_array($this->widget['callback'], array(self::$static));
    }
    
    private function getClick()
    { 
        if(is_callable($this->widget['click']))
            return call_user_func_array($this->widget['click'], array(self::$static));
            
        return $this->widget['click'];
    }
}
?>
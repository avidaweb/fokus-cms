<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Apps 
{
    private static $static, $base, $api, $fksdb, $user, $suite, $trans;    
    
    private $id = '', $app = array();
    
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
        
        if($id)
        {
            $this->id = $id;
            $this->app = self::$api->getApp($id);
        }
    }
    
    public function data($data, $app = null)
    {
        if(!$app)
            $app = $this->app;
        return $this->app[$data];
    }
    
    public function isAvaible($app = null) 
    {
        if(!$app)
            $app = $this->app;
        if(!$app)
            return false;
        return true;
    }
    
    public function getContent($app = null)
    {
        if(!$app)
            $app = $this->app;
        if(!$app)
            return self::$trans->__('Keine App gefunden');
            
        if(!$app['content_callback'])
            return self::$trans->__('App ist fehlerhaft (kein Content-Callback definiert)');
            
        if(!is_callable($app['content_callback']))
            return self::$trans->__('App ist fehlerhaft (Content-Callback falsch definiert)');
            
        $storage = self::$api->getStorageBase($app['slug']);
        $data = (count($storage)?(object)$storage:new stdClass());
            
        ob_start(); 
        if(is_string($app['content_callback']))
            $rtn = call_user_func($app['content_callback'], $data, self::$static);
        elseif(is_array($app['content_callback']))
            $rtn = call_user_func_array($app['content_callback'], array($data, self::$static));
        $rtn .= ob_get_contents();
        ob_end_clean(); 
            
        return $rtn;
    }
    
    public function checkRights($app = null)
    {
        if(!$app)
            $app = $this->app;
        if(!$app)
            return false;
            
        if(!$app['rights_callback'])
            return true;
            
        if(!is_callable($app['rights_callback']))
            return false;
            
        if(is_string($app['rights_callback']))
            return call_user_func($app['rights_callback'], self::$api->getUserRights(), self::$static);
        elseif(is_array($app['rights_callback']))
            return call_user_func_array($app['rights_callback'], array(self::$api->getUserRights(), self::$static)); 
            
        return false;
    }
    
    public function getMenu($menu)
    {
        $apps = self::$api->getAppsSorted();
        $rtn = '';
        
        foreach((array)$apps[$menu] as $aid => $app)
        {
            if(!$this->checkRights($app))
                continue;
            
            $title = ($app['menu_title']?$app['menu_title']:$app['title']);
            $rtn .= '<li><a class="app" data-id="'.$aid.'" data-width="'.$app['width'].'">'.self::$trans->__($title).'</a></li>';
        }
        
        return $rtn;
    }
}
?>
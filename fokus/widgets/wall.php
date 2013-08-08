<?php
class WidgetWall
{
    private static $base, $api, $fksdb, $user, $trans; 
    
    public function __construct($static, $first = false)
    {
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
        
        $this->init();
    }
    
    private function init()
    {
        if(!$this->hasRights())
            return false;    
            
        self::$api->initWidget('fks-wall', self::$trans->__('Öffentliche Pinnwand'), array($this, 'getWidget'), 3, 1, 'fks.openWall');
    }
    
    private function hasRights()
    {
        return self::$api->checkUserRights('communication', 'wall');
    }
    
    public function getWidget()
    {
        $note = trim(base64_decode(self::$base->getOpt('notiz')));
        
        if(!$note)
            return '<em class="empty">'.self::$trans->__('keine Einträge vorhanden').'</em>';
            
        return $note;
    }
}

$widget_wall = new WidgetWall($classes);
?>
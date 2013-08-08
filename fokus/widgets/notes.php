<?php
class WidgetNotes
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
            
        self::$api->initWidget('fks-notes', self::$trans->__('Persönliche Notizen'), array($this, 'getWidget'), 2, 1, 'fks.openNotes');
    }
    
    private function hasRights()
    {
        return self::$api->checkUserRights('fks', 'notes');
    }
    
    public function getWidget()
    {
        $note = trim(base64_decode(self::$user->data('notiz')));
        
        if(!$note)
            return '<em class="empty">'.self::$trans->__('keine Notizen vorhanden').'</em>';
            
        return $note;
    }
}

$widget_notes = new WidgetNotes($classes);
?>
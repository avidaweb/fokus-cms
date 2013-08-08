<?php
class WidgetComments
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
            
        self::$api->initWidget('fks-comments', self::$trans->__('Kommentare auf der Website'), array($this, 'getWidget'), 2, 2, 'fks.openComments');
    }
    
    private function hasRights()
    {
        return self::$api->checkUserRights('communication', 'channels');
    }
    
    public function getWidget()
    {
        $rtn = '<br />';
        
        $comments = self::$fksdb->query("SELECT name, timestamp, text FROM ".SQLPRE."comments ORDER BY timestamp DESC LIMIT 5");
        while($comment = self::$fksdb->fetch($comments))
        {
            $rtn .= '
            <p class="left col-1-4">
                <span title="'.$comment->name.'">'.Strings::cut($comment->name, 9, '.').'</span><br />
                <small>
                    '.date('d.m.Y', $comment->timestamp).'<br />
                    '.date('H:i', $comment->timestamp).' Uhr
                </small>
            </p>
            
            <p class="right col-3-4">
                <em>'.Strings::cut($comment->text, 120).'</em>
            </p>
            
            <span class="clr"></span>';
        }
        
        if(!self::$fksdb->count($comments))
            return '<em class="empty">'.self::$trans->__('keine Kommentare vorhanden').'</em>';
        
        return $rtn;
    }
}

$widget_comments = new WidgetComments($classes);
?>
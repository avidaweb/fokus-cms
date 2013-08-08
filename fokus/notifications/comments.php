<?php
class NotificationComments
{
    private static $base, $api, $fksdb, $user, $trans; 
    
    public function __construct($static)
    {
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
    }
    
    public function getMessage($classes, $lastcheck, $rights)
    {
        if(!$rights['communication']['channels'])
            return false;
            
        $comment = self::$fksdb->fetch("SELECT text, frei, name FROM ".SQLPRE."comments WHERE timestamp > ".$lastcheck." ORDER BY frei LIMIT 1");
        if(!$comment)
            return false;
            
        $author = ($comment->name?$comment->name:self::$trans->__('Ein Besucher'));
        
        if($comment->frei)
            return self::$trans->__('%1 hat einen Kommentar hinterlassen: %2', false, array($author, $comment->text));
        return self::$trans->__('%1 hat einen Kommentar hinterlassen, der noch nicht freigegeben ist.', false, array($author));
    }
}

$notification_comments = new NotificationComments($classes);
$api->initNotification('fks-comments', $trans->__('Neuer Kommentar'), array($notification_comments, 'getMessage'), 'fks.openComments');
?>
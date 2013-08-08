<?php
class NotificationMessages
{
    private static $base, $api, $fksdb, $user, $trans; 
    
    public function __construct($static, $first = false)
    {
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
    }
    
    public function getMessage($classes, $lastcheck, $rights)
    {
        if(!$rights['communication']['messages'])
            return false;
            
        $lastmessage = self::$fksdb->data("SELECT titel FROM ".SQLPRE."messages WHERE benutzer = '".self::$user->getID()."' AND an = '".self::$user->getID()."' AND gelesen = 0 AND timestamp > ".$lastcheck." LIMIT 1", "titel");
        if(!$lastmessage)
            return false;
        
        return self::$trans->__('Sie haben eine neue Nachricht erhalten: %1', false, array($lastmessage));
    }
}

$notification_messages = new NotificationMessages($classes);
$api->initNotification('fks-messages', $trans->__('Neue Nachricht'), array($notification_messages, 'getMessage'), 'fks.openMessages');
?>
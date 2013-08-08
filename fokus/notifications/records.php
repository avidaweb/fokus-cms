<?php
class NotificationRecords
{
    private static $base, $api, $fksdb, $user, $trans; 
    
    private $click = array();
    
    public function __construct($static, $first = false)
    {
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
    }
    
    public function getClick($classes, $lastcheck, $rights)
    {
        return $this->click;    
    }
    
    public function getMessage($classes, $lastcheck, $rights)
    {
        if(!$rights['communication']['channels'])
            return false;
            
        $record = self::$fksdb->data("SELECT vid FROM ".SQLPRE."records WHERE timestamp > ".$lastcheck." LIMIT 1", "vid");
        if(!$record) return false;
            
        $block = self::$fksdb->data("SELECT html FROM ".SQLPRE."blocks WHERE vid = '".$record."' ORDER BY id DESC LIMIT 1", "html");
        if(!$block) return false;
        
        $fo = self::$base->fixedUnserialize($block);
        if(!count($fo)) return false;
        $name = ($fo['name']?$fo['name']:self::$trans->__('Unbenanntes Formular'));
        
        $this->click = array('fks.openRecords', $record);
        
        return self::$trans->__('Es ist ein neuer Datensatz im Formular "%1" eingegangen.', false, array($name));
    }
}

$notification_records = new NotificationRecords($classes);
$api->initNotification('fks-records', $trans->__('Neuer Datensatz'), array($notification_records, 'getMessage'), array($notification_records, 'getClick'));
?>
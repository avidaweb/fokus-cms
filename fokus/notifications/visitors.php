<?php
class NotificationVisitors
{
    private static $static, $base, $api, $fksdb, $user, $trans; 
    
    public function __construct($static)
    {
        self::$static = $static;
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
    }
    
    public function getMessage($classes, $lastcheck, $rights)
    {
        if(!$rights['fks']['options'])
            return false;
            
        if($lastcheck >= mktime(0, 0, 0))
            return false;
            
        include_once(ROOT.'inc/classes.other/class.stats.php');
        $stats = new Stats(self::$static);
        
        $visitors_yesterday = $stats->getVisitorsYesterday();
        $pageviews_yesterday = $stats->getPageviewsYesterday();
        
        if(!$visitors_yesterday && !$pageviews_yesterday)
            return false;
            
        return self::$trans->__('Gestern, am %1, gab es %2 Besucher mit %3 Seitenaufrufen', false, array(
            date('d.m.Y', strtotime('yesterday')),
            $visitors_yesterday,
            $pageviews_yesterday
        ));
    }
}

$notification_visitors = new NotificationVisitors($classes);
$api->initNotification('fks-visitors', $trans->__('Besucher-Statistik'), array($notification_visitors, 'getMessage'), '');
?>
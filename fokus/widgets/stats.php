<?php
class WidgetStats
{
    private static $static, $base, $api, $fksdb, $user, $trans; 
    
    public function __construct($static, $first = false)
    {
        self::$static = $static;
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
            
        self::$api->initWidget('fks-stats', self::$trans->__('Besucher-Statistik'), array($this, 'getWidget'), 1, 2, '');
    }
    
    private function hasRights()
    {
        return self::$api->checkUserRights('fks', 'options');
    }
    
    public function getWidget()
    {
        include_once(ROOT.'inc/classes.other/class.stats.php');
        $stats = new Stats(self::$static);
        
        return '
        <p>
            <em>'.self::$trans->__('Heute:').'</em><br />
            '.self::$trans->__('%1 Besucher', false, array($stats->getVisitorsToday())).'<br />
            '.self::$trans->__('%1 Aufrufe', false, array($stats->getPageviewsToday())).'
        </p>
        <p>
            <em>'.self::$trans->__('Gestern:').'</em><br />
            '.self::$trans->__('%1 Besucher', false, array($stats->getVisitorsYesterday())).'<br />
            '.self::$trans->__('%1 Aufrufe', false, array($stats->getPageviewsYesterday())).'
        </p>
        <p>
            <em>'.self::$trans->__('Diese Woche:').'</em><br />
            '.self::$trans->__('%1 Besucher', false, array($stats->getVisitorsSince(
                date('Y-m-d', strtotime('last Monday'))
            ))).'<br />
            '.self::$trans->__('%1 Aufrufe', false, array($stats->getPageviewsSince(
                date('Y-m-d', strtotime('last Monday'))
            ))).'
        </p>
        <p>
            <em>'.self::$trans->__('Letzte 7 Tage:').'</em><br />
            '.self::$trans->__('%1 Besucher', false, array($stats->getVisitorsLastDays(7))).'<br />
            '.self::$trans->__('%1 Aufrufe', false, array($stats->getPageviewsLastDays(7))).'
        </p>
        <p>
            <em>'.self::$trans->__('Dieser Monat:').'</em><br />
            '.self::$trans->__('%1 Besucher', false, array($stats->getVisitorsSince(
                date('Y-m-d', strtotime('first day of this month'))
            ))).'<br />
            '.self::$trans->__('%1 Aufrufe', false, array($stats->getPageviewsSince(
                date('Y-m-d', strtotime('first day of this month'))
            ))).'
        </p>
        <p>
            <em>'.self::$trans->__('Letzte 30 Tage:').'</em><br />
            '.self::$trans->__('%1 Besucher', false, array($stats->getVisitorsLastDays(30))).'
        </p>';
    }
}

$widget_stats = new WidgetStats($classes);
?>
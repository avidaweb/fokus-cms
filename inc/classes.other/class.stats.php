<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Stats 
{
    private static $static, $base, $api, $fksdb, $user, $suite, $trans; 
    
    private $visitor, $daily_visitor, $ip;
    private $day;
    
    public function __construct($static)
    {
        // static
        self::$static = $static;
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
        
        $this->visitor = self::$api->getVisitor();
        $this->daily_visitor = self::$api->getDailyVisitor();
        $this->ip = self::$api->getVisitorIP();
        
        $this->day = date('Y-m-d');
    }
    
    public function saveVisit($element, $document = 0)
    {
        if($this->isRobot())
            return false;
            
        $referer = ($_SERVER['SERVER_NAME'] != self::$api->getReferer('host')?self::$api->getReferer():null);
        
        self::$fksdb->insert("stats", array(
            "hash" => $this->daily_visitor,
            "visitor" => $this->visitor,
            "user" => self::$user->getID(),
            "day" => $this->day,
            "time" => time(),
            "referer" => $referer,
            "element" => intval($element),
            "document" => intval($document)
        ));
        
        return true;
    }
    
    private function isRobot()
    {
        $agents = 'Google|msnbot|Rambler|Yahoo|AbachoBOT|accoona|AcioRobot|ASPSeek|CocoCrawler|Dumbot|FAST-WebCrawler|GeonaBot|Gigabot|Lycos|MSRBOT|Scooter|AltaVista|IDBot|eStyle|Scrubby';
        
        if(strpos($agents, $_SERVER['HTTP_USER_AGENT']) !== false)
            return true;
            
        return false;    
    }
    
    
    public function getVisitorsByDay($day)
    {
        return self::$fksdb->count("SELECT id FROM ".SQLPRE."stats WHERE day = '".$day."' GROUP BY hash");    
    }
    
    public function getVisitorsToday()
    {
        return $this->getVisitorsByDay($this->day);   
    }
    
    public function getVisitorsYesterday()
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        return $this->getVisitorsByDay($date);   
    }
    
    public function getVisitorsLastDays($days = 7)
    {
        $date = date('Y-m-d', strtotime('-'.$days.' days'));
        return self::$fksdb->count("SELECT id FROM ".SQLPRE."stats WHERE day >= '".$date."' AND day < '".$this->day."' GROUP BY hash");    
    }
    
    public function getVisitorsSince($date)
    {
        return self::$fksdb->count("SELECT id FROM ".SQLPRE."stats WHERE day >= '".$date."' GROUP BY hash");   
    }
    
    
    public function getPageviewsByDay($day)
    {
        return self::$fksdb->count("SELECT id FROM ".SQLPRE."stats WHERE day = '".$day."'");    
    }
    
    public function getPageviewsToday()
    {
        return $this->getPageviewsByDay($this->day);   
    }
    
    public function getPageviewsYesterday()
    {
        $date = date('Y-m-d', strtotime('yesterday'));
        return $this->getPageviewsByDay($date);   
    }
    
    public function getPageviewsLastDays($days = 7)
    {
        $date = date('Y-m-d', strtotime('-'.$days.' days'));
        return self::$fksdb->count("SELECT id FROM ".SQLPRE."stats WHERE day >= '".$date."' AND day < '".$this->day."'");    
    }
    
    public function getPageviewsSince($date)
    {
        return self::$fksdb->count("SELECT id FROM ".SQLPRE."stats WHERE day >= '".$date."'");   
    }
}
?>
<?php
class WidgetSession
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
            
        self::$api->initWidget('fks-session', self::$trans->__('Sitzungs-Daten'), array($this, 'getWidget'), 1, 1, 'fks.openSessionInfo');
    }
    
    private function hasRights()
    {
        return self::$api->checkUserRights('fks', 'session');
    }
    
    public function getWidget()
    {
        $logged_since = self::$user->getLoginTime();
        
        return '
        <p>
            <em>'.self::$trans->__('Momentan angemeldet als:').'</em><br />
            '.self::$user->data('vorname').' '.self::$user->data('nachname').'
        </p>
        <p>
            <em>'.self::$trans->__('Gewählte Rolle:').'</em><br />
            '.self::$fksdb->data("SELECT titel FROM ".SQLPRE."roles WHERE id = '".self::$user->getRole()."' LIMIT 1", "titel").'
        </p>
        <p>
            <em>'.self::$trans->__('Eingeloggt seit:').'</em><br />
            '.date('d.m.Y', $logged_since).', '.self::$trans->__('%1 Uhr', false, array(date('H:i', $logged_since))).'
        </p>';
    }
}

$widget_session = new WidgetSession($classes);
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Notifications 
{
    private static $static, $base, $api, $fksdb, $user, $suite, $trans; 
    
    private $lastchange = 0, $rights = array();
    private $notifications = array(), $output = array();
    private $first = false, $new = false;
    
    
    public function __construct($static, $first = false)
    {
        // static
        self::$static = $static;
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
        
        $this->first = $first;
    }
    
    public function getNew()
    {
        $this->lastchange = $this->getLastChange();
        $this->rights = $this->getRights();
        
        $this->notifications = $this->getNotifications();
        $this->output = $this->getOutput();
        $this->save();
        $this->setLastChange();
    }
    
    public function output()
    {
        return array(
            'changed' => ($this->first || $this->new?1:0),
            'result' => $this->outputResults()
        ); 
    }
    
    private function outputResults()
    {
        if(!$this->first && !$this->new)
            return '';
            
        $rtn = '';
            
        $qry = self::$fksdb->select("notifications", array("id", "title", "message", "click"), array(
            "user" => self::$user->getID()
        ), "id DESC");
        
        $count = self::$fksdb->count($qry);
        if(!$count)
            return '';
        
        if($count > 1)
        {
            $rtn = '
            <div class="empty">
                <a>'.self::$trans->__('Alle <strong>%1 Hinweise</strong> entfernen.', false, array($count)).'</a>
            </div>';
        }
        
        while($r = self::$fksdb->fetch($qry))
        {
            $rtn .= '
            <div class="note" data-id="'.$r->id.'">
                <h5>'.$this->getHeadline($r->title, $r->click).'</h5>
                <p>'.$r->message.'</p>
                <span title="'.self::$trans->__('Hinweis entfernen').'"></span>
            </div>';
        }
        
        return $rtn;
    }
    
    private function getHeadline($title, $click)
    {
        if(!$click)
            return $title;
            
        if(filter_var($click, FILTER_VALIDATE_URL) !== FALSE)
            return '<a href="'.$click.'" target="_blank" class="url">'.$title.'</a>';  
        
        if(Strings::strExists('|!|', $click))
        {
            $clickdata = explode('|!|', $click);
            return '<a data-function="'.$clickdata[0].'" data-info="'.$clickdata[1].'" class="function">'.$title.'</a>';
        }
        
        return '<a data-function="'.$click.'" class="function">'.$title.'</a>';
            
    }
    
    public function delete($id)
    {   
        self::$fksdb->query("DELETE FROM ".SQLPRE."notifications WHERE id = '".intval($id)."' AND user = '".self::$user->getID()."' LIMIT 1");
        return true;   
    }
    
    public function deleteAll()
    {
        self::$fksdb->query("DELETE FROM ".SQLPRE."notifications WHERE user = '".self::$user->getID()."'");
        return true;  
    }
    
    
    
    private function getLastChange()
    {
        return intval(self::$user->data('last_notifications'));
    }
    
    private function setLastChange()
    {
        self::$fksdb->update("users", array(
            "last_notifications" => time()
        ), array(
            "id" => self::$user->getID()
        ), 1);
        
        return true;   
    }
    
    private function getRights()
    {
        return self::$user->getRights(true);
    }
    
    private function getNotifications()
    {
        return self::$api->getNotifications();
    }
    
    private function getOutput()
    {
        $output = array();
        if(!count($this->notifications))
            return $output;
            
        foreach($this->notifications as $unique_slug => $a)
        {
            if(!is_callable($a['callback']))
                continue; 
                
            $message = call_user_func_array($a['callback'], array(self::$static, $this->lastchange, $this->rights));
            if(!is_string($message) || $message === false || !trim($message))
                continue;
                
            $click = (is_callable($a['click'])?call_user_func_array($a['click'], array(self::$static, $this->lastchange, $this->rights)):$a['click']);
                
            $output[$unique_slug] = array(
                'title' => $a['title'],
                'message' => $message,
                'click' => $click
            );    
        }
        
        return $output;
    }
    
    private function save()
    {
        if(!count($this->output))
            return false;
            
        $this->new = true;
        
        foreach($this->output as $unique_slug => $a)
        {
            $this->insertNotification($a['title'], $a['message'], $a['click']);
        }
        
        return true;
    }
    
    public function insertNotification($title, $message = '', $click = null, $user = 0)
    {
        return self::$api->insertNotification($title, $message, $click, $user);
    }
}
?>
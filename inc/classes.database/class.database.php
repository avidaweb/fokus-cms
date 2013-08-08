<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Database
{
    protected $connection;
    protected $result;
    protected $query_count;
    protected $insertedID;
    protected $error;
    
       
    function __construct($host = '', $dbuser = '', $password = '', $database = '')
    {
        if($host && $database)
            $this->connect($host, $dbuser, $password, $database);
    }

    
    public function data($mixed, $key)
    {
        $obj = $this->fetch($mixed);
        
        return ($obj?$obj->$key:null);
    }
    
    public function rows($mixed, $value = '', $index = '')
    {
        if(is_resource($mixed))
            $result = $mixed;
        elseif(!empty($mixed))
            $result = $this->query($mixed);
        elseif(is_resource($this->result))
            $result = $this->result;
        else
            return false;
        
        $rows = array();
        while($me = $this->fetch($result))
        {
            if($index)
                $rows[$me->$index][] = (!$value?$me:$me->$value);
            elseif($me->id)
                $rows[$me->id] = (!$value?$me:$me->$value);
            else
                $rows[] = (!$value?$me:$me->$value);
        }
        return $rows;
    }
    
    public function fetchSelect($table, $data, $where, $order = "", $limit = 0)
    {
        $query = $this->select($table, $data, $where, $order, $limit);
        if($query === false)
            return false;
        return $this->fetch($query);    
    }
    
    public function copy($original_data, $table, $replace = array())
    {
        $original = array();
        
        if(is_array($original_data))
            $original = $original_data;
        if(is_resource($original_data))
            $original = $this->fetchArray($original_data);
        if(!count($original))
            return false;
            
        if(count($replace))
            $original = array_merge($original, $replace);
            
        unset($original['id']);
            
        return $this->insert($table, $original);
    }
    
    
    public function getInsertedID()
    {
        return intval($this->insertedID);
    }
    
    public function getError()
    {
        return $this->error;
    }
    
    
    public function setTablePreset($table)
    {
        if(!defined('SQLPRE'))
            return $table;
        
        if(strpos($table, SQLPRE) === false)
            $table = SQLPRE.$table;
        return $table;
    }
    
    
    public function getQueryCount()
    {
        return intval($this->query_count);
    }
    
    
    public function getDatabaseSize($divide = 1000, $round = 0, $ending = 'KB')
    {
        $dbsize = 0;
        $result = $this->query("SHOW TABLE STATUS");
        while($row = $this->fetchArray($result)) 
            $dbsize += $row["Data_length"] + $row["Index_length"];
            
        if($divide)
            $dbsize = round(($dbsize / $divide), $round);
            
        if($ending)
            $dbsize = $dbsize.$ending;
            
        return $dbsize;
    }
    
    
    public function save($string, $type = 0)
    {
        if(get_magic_quotes_gpc())
            $string = stripslashes($string);
        
        $string = strip_tags(htmlspecialchars(trim($string)));
    	$string = str_replace("\\", "&#92;", $string);
    	$string = str_replace("'", "&rsquo;", $string);
        if($type == 1)
            $string = intval($string);
        return $string;
    }


    protected function setError($e, $query = '', $file = '', $line = '')
    {
        $this->error = $e;
        if($handle = @fopen(ROOT.'content/export/fehler.txt', 'a'))
        {
            fwrite($handle, $e."\nQuery: ".$query."\nDate: ".date('d.m.Y')." - ".date('H:i')."\n\n");
            fclose($handle);
        }

        if(!defined('DEBUG')) return true;
        if(DEBUG !== true) return true;

        $error = '
        <strong>Es ist ein Datenbank-Fehler aufgetreten:</strong><br /><br />
        <code>'.$e.'</code><br /><br />
        Query:<br />
        <code>'.$query.'</code><br /><br />
        Datei:<br />
        <code>'.$file.'</code><br /><br />
        Zeile:<br />
        <code>'.$line.'</code><br /><br /><em>Diese Meldung wird angezeigt, weil die Fehlerausgabe in der fokus-config.php aktiviert wurde.</em>';

        exit('<div style="border:6px solid red; background: black; color:white; margin: 20px 10px; padding: 15px; font-family: monospace;">'.$error.'</div>');
    }

    protected function errorConnection()
    {
        $this->outputError('<strong>Es konnte keine Verbindung mit dem Datenbank-Server aufgebaut werden.</strong><br />Entweder ist der Datenbankserver momentan nicht erreichbar oder die in der fokus-config.php hinterlegten Datenbank-Informationen sind ungültig.');
    }
    
    protected function errorDatabase()
    {
        $this->outputError('<strong>Es konnte keine Verbindung mit der Datenbank aufgebaut werden.</strong><br />Entweder ist der Datenbankserver momentan nicht erreichbar oder die in der fokus-config.php hinterlegte Datenbank ist ungültig.');
    }


    protected function outputError($msg = '')
    {
        exit('<!DOCTYPE html><html lang="de"><head><meta charset="utf-8"/><title>Datenbank-Fehler: CMS fokus</title><link href="'.DOMAIN.'/fokus/css/reset.css" rel="stylesheet" type="text/css" /><link href="'.DOMAIN.'/fokus/css/layout.css" rel="stylesheet" type="text/css" /><script src="'.DOMAIN.'/inc/libraries/js/jquery.min.js" type="text/javascript"></script><script src="'.DOMAIN.'/inc/libraries/js/jquery-ui.custom.min.js" type="text/javascript"></script><script src="'.DOMAIN.'/fokus/js/login.js" type="text/javascript"></script></head><body><div id="main"><table class="fenster" style="width:800px;"><tr><td class="A1"></td><td class="A2"></td><td class="A3"></td></tr><tr><td class="B1"></td><td class="B2"><div class="inhalt" id="install"><h1>Datenbank-Fehler.</h1><div class="box"><div class="warnung">'.$msg.'</div></div></div></td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr></div></body></html>');
    }
}
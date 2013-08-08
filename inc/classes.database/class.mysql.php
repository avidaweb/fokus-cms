<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class MySQL_Database extends Database
{    
    public function connect($host, $dbuser, $password, $database, $return_codes = false)
    {
        $this->close();
        
        $this->connection = mysql_connect($host, $dbuser, $password);
        if(!$this->connection)
        {
            if($return_codes)
                return 1;
            $this->errorConnection();
        }
        
        $db = mysql_select_db($database, $this->connection);
        if(!$db)
        {
            if($return_codes)
                return 2;
            $this->errorDatabase();
        }
        
        if(defined('DBWRAPPER') || defined('IS_INSTALLATION'))
            mysql_set_charset('utf8', $this->connection);
        
        if($return_codes)
            return 0;
            
        return true;
    }
    
    public function close()
    {
        $this->result = null;
        $this->query_count = 0;
        $this->insertedID = null;
        $this->error = null;
        
        if($this->connection)
        {
            mysql_close($this->connection);
            $this->connection = null;
            return true;
        }
        return false;
    }
    
    
    public function query($query)
    {
        $this->insertedID = null;
        
        $this->result = mysql_query($query, $this->connection);
        $insertedID = mysql_insert_id($this->connection);
        if($insertedID)
            $this->insertedID = $insertedID;
        
        $this->query_count ++;
        
        $this->error = mysql_error($this->connection);
        if($this->error)
        {
            if($handle = @fopen(ROOT.'content/export/fehler.txt', "a")) 
            {
                fwrite($handle, $this->error."\nQuery: ".$query."\nDate: ".date('d.m.Y')." - ".date('H:i')."\n\n");
                fclose($handle);
            }
            
            return false;
        }
        
        return $this->result;
    }
    
    public function multiQuery($query)
    {
        $queries = explode(';', $query);
        
        $counter = 0;
        $errors = '';
        
        foreach($queries as $q)
        {
            $q = trim($q);
            if(!empty($q))
            {
                $counter ++;
                
                if(!$this->query($q))
                    $errors .= 'error in line '.$counter.'<br /><strong>'.nl2br($this->getError()).'</strong><br />';    
            }
        }
        
        if(!$errors)
            return '';
        else
            return $errors;
    }
    
    
    public function count($mixed)
    {
        $count = 0;
        
        if(is_resource($mixed))
        {
            $count = mysql_num_rows($mixed);
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            $count = mysql_num_rows($result);
        }
        elseif(is_resource($this->result))
        {
            $count = mysql_num_rows($this->result);
        }
        
        return intval($count);
    }
    
    public function affected()
    {
        return intval(mysql_affected_rows($this->connection));    
    }
    
    public function fetch($mixed)
    {
        if(is_resource($mixed))
        {
            return mysql_fetch_object($mixed);
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            return mysql_fetch_object($result);
        }
        elseif(is_resource($this->result))
        {
            return mysql_fetch_object($this->result);
        }
        
        return false;
    }
    
    public function fetchAssoc($mixed)
    {
        if(is_resource($mixed))
        {
            return mysql_fetch_assoc($mixed);
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            return mysql_fetch_assoc($result);
        }
        elseif(is_resource($this->result))
        {
            return mysql_fetch_assoc($this->result);
        }
        
        return false;
    }
    
    public function fetchArray($mixed, $result_type = MYSQL_ASSOC)
    {
        if(is_resource($mixed))
        {
            return mysql_fetch_array($mixed, $result_type);
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            return mysql_fetch_array($result, $result_type);
        }
        elseif(is_resource($this->result))
        {
            return mysql_fetch_array($this->result, $result_type);
        }
        
        return false;
    }
    
    
    public function insert($table, $data)
    {
        if(!$table || !is_array($data))
            return false;
            
        $statement = "";
        foreach($data as $k => $v)
        {
            if(is_int($v))
                $statement .= ($statement?",":"")." ".$k." = ".$v."";
            else
                $statement .= ($statement?",":"")." ".$k." = '".mysql_real_escape_string($v)."'";
        }
        
        if(!$statement)
            return false;
            
        $table = $this->setTablePreset($table);
        
        $query = "INSERT INTO ".$table." SET ".$statement.";";
        
        if($this->query($query))
            return true;
        return false;
    }
    
    public function update($table, $data, $where, $limit = 0)
    {
        if(!$table || !is_array($data) || !$where)
            return false;
            
        $statement = "";
        foreach($data as $k => $v)
        {
            if(is_int($v))
                $statement .= ($statement?",":"")." ".$k." = ".$v."";
            else
                $statement .= ($statement?",":"")." ".$k." = '".mysql_real_escape_string($v)."'";
        }
        
        if(!$statement)
            return false;
            
        $where_statement = "";
        if(is_array($where))
        {
            foreach($where as $k => $v)
            {
                if(is_int($v))
                    $where_statement .= ($where_statement?" AND ":"")." ".$k." = ".$v."";
                else
                    $where_statement .= ($where_statement?" AND ":"")." ".$k." = '".mysql_real_escape_string($v)."'";
            }
        }
        else
        {
            $where_statement = $where;
        }
            
        $table = $this->setTablePreset($table);
        
        $query = "UPDATE ".$table." SET ".$statement." WHERE ".$where_statement.($limit?" LIMIT ".$limit:"").";";
        
        if($this->query($query))
            return true;
        return false;
    }
    
    public function select($table, $data, $where, $order = "", $limit = 0)
    {
        if(!$table)
            return false;
        $table = $this->setTablePreset($table);
           
        $select = (!is_array($data)?$data:implode(',', $data));
        if(!$select)
            return false;
            
        $where_statement = "";
        if(is_array($where))
        {
            foreach($where as $k => $v)
            {
                if(is_int($v))
                    $where_statement .= ($where_statement?" AND ":"")." ".$k." = ".intval($v)."";
                elseif(is_array($v))
                    $where_statement .= ($where_statement?" AND ":"")." ".$k." = ('".trim(implode("','", $v), ',')."')";
                else
                    $where_statement .= ($where_statement?" AND ":"")." ".$k." = '".mysql_real_escape_string($v)."'";
            }
        }
        else
        {
            $where_statement = $where;
        }
            
        $query = "SELECT ".$select." FROM ".$table.($where_statement?" WHERE ".$where_statement:"").($order?" ORDER BY ".$order:"").($limit?" LIMIT ".$limit:"").";";
        return $this->query($query);
    }
}
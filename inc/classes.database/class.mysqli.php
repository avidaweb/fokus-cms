<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class MySQLi_Database extends Database
{    
    public function connect($host, $dbuser, $password, $database, $return_codes = false)
    {
        $this->close();
        
        $this->connection = new mysqli($host, $dbuser, $password, $database);
        if(mysqli_connect_errno())
        {
            if($return_codes)
                return 1;
            $this->errorConnection();
        }
        
        $this->connection->set_charset('utf8');
        
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
            $this->connection->close();
            return true;
        }
        return false;
    }
    
    
    public function query($query)
    {
        $this->insertedID = null;
        
        $this->result = $this->connection->query($query);
        $insertedID = $this->connection->insert_id;
        if($insertedID)
            $this->insertedID = $insertedID;
        
        $this->query_count ++;
        
        $this->error = $this->connection->error;
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
        if($this->connection->multi_query($query))
        {
            do { $this->connection->use_result(); } 
            while($this->connection->next_result());
        }
        
        $this->error = $this->connection->error;
        if($this->error)
        {
            if($handle = @fopen(ROOT.'content/export/fehler.txt', "a")) 
            {
                fwrite($handle, $this->error."\nQuery: ".$query."\nDate: ".date('d.m.Y')." - ".date('H:i')."\n\n");
                fclose($handle);
            }
            
            return false;
        }
        
        return true;
    }
    
    
    public function count($mixed)
    {
        $count = 0;
        
        if($mixed instanceof mysqli_result)
        {
            $count = $mixed->num_rows;
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            $count = $result->num_rows;
        }
        elseif($this->result instanceof mysqli_result)
        {
            $count = $this->result->num_rows;
        }
        
        return intval($count);
    }
    
    public function affected()
    {
        return intval($this->connection->affected_rows);    
    }
    
    public function fetch($mixed)
    {
        if($mixed instanceof mysqli_result)
        {
            return $mixed->fetch_object();
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            if(!$result) return false;
            return $result->fetch_object();
        }
        elseif($this->result instanceof mysqli_result)
        {
            return $this->result->fetch_object();
        }
        
        return false;
    }
    
    public function fetchAssoc($mixed)
    {
        if($mixed instanceof mysqli_result)
        {
            return $mixed->fetch_assoc();
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            return $result->fetch_assoc();
        }
        elseif($this->result instanceof mysqli_result)
        {
            return $this->result->fetch_assoc();
        }
        
        return false;
    }
    
    public function fetchArray($mixed, $result_type = MYSQL_ASSOC)
    {
        if($mixed instanceof mysqli_result)
        {
            return $mixed->fetch_array($result_type);
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            return $result->fetch_array($result_type);
        }
        elseif($this->result instanceof mysqli_result)
        {
            return $this->result->fetch_array($result_type);
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
                $statement .= ($statement?",":"")." ".$k." = '".$this->connection->real_escape_string($v)."'";
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
                $statement .= ($statement?",":"")." ".$k." = '".$this->connection->real_escape_string($v)."'";
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
                    $where_statement .= ($where_statement?" AND ":"")." ".$k." = '".$this->connection->real_escape_string($v)."'";
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
                    $where_statement .= ($where_statement?" AND ":"")." ".$k." = '".$this->connection->real_escape_string($v)."'";
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
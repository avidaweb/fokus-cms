<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class PDO_Database extends Database
{
    private $affected = 0;

    public function connect($host, $dbuser, $password, $database, $return_codes = false)
    {
        $this->close();

        $server = 'mysql:dbname='.$database.';host='.$host.';';

        try
        {
            $this->connection = new PDO($server, $dbuser, $password, array(
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8',
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
            ));
        }
        catch(PDOException $e)
        {
            if($return_codes)
                return 1;
            $this->errorConnection();
        }
        
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
        $this->connection = null;

        return true;
    }


    public function exec($query)
    {
        $this->insertedID = null;

        try
        {
            $this->affected = $this->connection->exec($query);
        }
        catch(PDOException $e)
        {
            $this->setError($e->getMessage(), $query, $e->getFile(), $e->getLine());
            return false;
        }

        $insertedID = $this->connection->lastInsertId();
        if($insertedID)
            $this->insertedID = $insertedID;

        $this->query_count ++;

        return true;
    }

    private function getDataType($data)
    {
        if(is_null($data))
            return PDO::PARAM_STR;
        elseif(is_bool($data))
            return PDO::PARAM_BOOL;
        elseif(is_int($data))
            return PDO::PARAM_INT;
        else
            return PDO::PARAM_STR;
    }

    public function prepared($query, $data = array())
    {
        $this->insertedID = null;

        try
        {
            $stmt = $this->connection->prepare($query);
        }
        catch(PDOException $e)
        {
            $this->setError($e->getMessage(), $query, $e->getFile(), $e->getLine());
            return false;
        }

        $dataloop = 0;
        foreach($data as $val)
        {
            $dataloop ++;
            $stmt->bindValue($dataloop, $val, $this->getDataType($val));
        }

        try
        {
            $stmt->execute();
            $this->result = $stmt;
        }
        catch(PDOException $e)
        {
            $this->setError($e->getMessage(), $query, $e->getFile(), $e->getLine());
            return false;
        }

        $insertedID = $this->connection->lastInsertId();
        if($insertedID)
            $this->insertedID = $insertedID;

        $this->query_count ++;

        return true;
    }

    public function query($query)
    {
        if(strncmp($query, 'INSERT', strlen('INSERT')) === 0 || strncmp($query, 'UPDATE', strlen('UPDATE')) === 0 || strncmp($query, 'REPLACE', strlen('REPLACE')) === 0 || strncmp($query, 'DELETE', strlen('DELETE')) === 0)
            return $this->exec($query);

        try
        {
            $this->result = $this->connection->query($query);
        }
        catch(PDOException $e)
        {
            $this->setError($e->getMessage(), $query, $e->getFile(), $e->getLine());
            return false;
        }
        
        $this->query_count ++;
        
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
            if(empty($q))
                continue;

            $counter ++;

            if(!$this->query($q))
                $errors .= 'error in line '.$counter.'<br /><strong>'.nl2br($this->getError()).'</strong><br />';
        }

        if(!$errors)
            return '';
        else
            return $errors;
    }
    
    
    public function count($mixed)
    {
        $count = 0;
        
        if($mixed instanceof PDOStatement)
        {
            $count = $mixed->rowCount();
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            $count = $result->rowCount();
        }
        elseif($this->result instanceof PDOStatement)
        {
            $count = $this->result->rowCount();
        }
        
        return intval($count);
    }
    
    public function affected()
    {
        return intval($this->affected);
    }
    
    public function fetch($mixed)
    {
        if($mixed instanceof PDOStatement)
        {
            return $mixed->fetchObject();
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            if(!$result) return false;
            return $result->fetchObject();
        }
        elseif($this->result instanceof PDOStatement)
        {
            return $this->result->fetchObject();
        }
        
        return false;
    }
    
    public function fetchAssoc($mixed)
    {
        if($mixed instanceof PDOStatement)
        {
            return $mixed->fetch(PDO::FETCH_ASSOC);
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            return $result->fetch(PDO::FETCH_ASSOC);
        }
        elseif($this->result instanceof PDOStatement)
        {
            return $this->result->fetch(PDO::FETCH_ASSOC);
        }
        
        return false;
    }
    
    public function fetchArray($mixed, $result_type = MYSQL_ASSOC)
    {
        if($result_type == MYSQL_ASSOC)
            $result_type = PDO::FETCH_ASSOC;
        elseif($result_type == MYSQL_BOTH)
            $result_type = PDO::FETCH_BOTH;
        elseif($result_type == MYSQL_NUM)
            $result_type = PDO::FETCH_NUM;

        if($mixed instanceof PDOStatement)
        {
            return $mixed->fetch($result_type);
        }
        elseif(!empty($mixed))
        {
            $result = $this->query($mixed);
            return $result->fetch($result_type);
        }
        elseif($this->result instanceof PDOStatement)
        {
            return $this->result->fetch($result_type);
        }
        
        return false;
    }
    
    
    public function insert($table, $data)
    {
        if(!$table || !is_array($data))
            return false;
            
        $statement = "";
        foreach($data as $k => $v)
            $statement .= ($statement?",":"")." ".$k." = ?";
        
        if(!$statement)
            return false;
            
        $table = $this->setTablePreset($table);
        
        $query = "INSERT INTO ".$table." SET ".$statement.";";
        
        if($this->prepared($query, $data))
            return true;
        return false;
    }
    
    public function update($table, $data, $where, $limit = 0)
    {
        if(!$table || !is_array($data) || !$where)
            return false;
            
        $statement = "";
        foreach($data as $k => $v)
            $statement .= ($statement?",":"")." ".$k." = ?";
        
        if(!$statement)
            return false;
            
        $where_statement = "";
        if(is_array($where))
        {
            $vars = array_merge(array_values($data), array_values($where));

            foreach($where as $k => $v)
                $where_statement .= ($where_statement?",":"")." ".$k." = ?";

        }
        else
        {
            $vars = $data;

            $where_statement = $where;
        }
            
        $table = $this->setTablePreset($table);
        
        $query = "UPDATE ".$table." SET ".$statement." WHERE ".$where_statement.($limit?" LIMIT ".$limit:"").";";
        
        if($this->prepared($query, $vars))
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
            $vars = $where;

            foreach($where as $k => $v)
                $where_statement .= ($where_statement?",":"")." ".$k." = ?";
        }
        else
        {
            $vars = array();

            $where_statement = $where;
        }
            
        $query = "SELECT ".$select." FROM ".$table.($where_statement?" WHERE ".$where_statement:"").($order?" ORDER BY ".$order:"").($limit?" LIMIT ".$limit:"").";";

        if($this->prepared($query, $vars))
            return $this->result;
        return false;
    }
}
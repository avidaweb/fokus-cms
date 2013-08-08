<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');
    
require_once(ROOT.'inc/classes.database/class.database.php');

$dbwrapper = 'mysql';
if(defined('DBWRAPPER'))
    $dbwrapper = DBWRAPPER;

if($dbwrapper == 'mysql')
{
    require_once(ROOT.'inc/classes.database/class.mysql.php');
    $fksdb = new MySQL_Database($dbserver, $dbuser, $dbpw, $db);
}
elseif($dbwrapper == 'mysqli')
{
    require_once(ROOT.'inc/classes.database/class.mysqli.php');
    $fksdb = new MySQLi_Database($dbserver, $dbuser, $dbpw, $db);
}
elseif($dbwrapper == 'pdo')
{
    require_once(ROOT.'inc/classes.database/class.pdo.php');
    $fksdb = new PDO_Database($dbserver, $dbuser, $dbpw, $db);
}
else
{
    exit('wrong database wrapper definied (fokus-config.php)');
}

unset($dbwrapper);
?>
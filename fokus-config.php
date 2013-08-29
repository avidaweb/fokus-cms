<?php 
error_reporting(0);

define('INSTALLED', true);
define('DEBUG', true);

$dbserver =	'localhost';
$dbuser = '';
$dbpw =	'';
$db = 'fokus';

$domain = 'http://localhost/Seiten/fokus'; 

$standard_language = 'de';

$cookies = array('login' => 'dc873e7ede57f460b2bdc8a449e7b944', 'pw' => 'a68f5d8c412b45b4d63f2e9037895df6', 'ablauf' => 'b361d9e5cb697c609656cdebb975e95b', 'rolle' => '6ecceff8a8311a4093fc35a8eabe8b13');

$salts = array('login' => 'sdfs873sdfsd4894234h5b', 'password' => '432j65kj24324bsa2354', 'password_b' => 'fgd8324hb5h34');

define('DBWRAPPER', 'pdo');
define('SQLPRE', 'fks_');
define('FOKUSKEY', 'MCTMB-NNNNL-GGYY9-NLMLL-NNMAH-4XRCV-JKHKE-VOECM');
?>

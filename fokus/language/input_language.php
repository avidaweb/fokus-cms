<?php
define('IS_BACKEND', true, true);
define('IS_BACKEND_DEEP', true, true);

require('../../inc/header.php');
require_once('../login.php');

$input = $fksdb->save($_GET['input']);

setcookie('input_language', $input, $base->getTime() + 9999999, '/');

$sprachenT = $base->getActiveLanguages();
$sprachen = $base->getActiveLanguages();

while($aname = current($sprachenT)) 
{
    if($aname == $input) 
    {
        $key = key($sprachenT);
        break;
    }
    next($sprachenT);
}

$sprachen[$base->getTime()] = $sprachen[$key];
unset($sprachen[$key]);
krsort($sprachen);

$new_sprachen = serialize($sprachen);
$updates = $fksdb->query("UPDATE ".SQLPRE."options SET sprachen = '".$new_sprachen."' WHERE id = '1' LIMIT 1"); 
?>
<?php
define('IS_GHOST', true, true);

require('../../header.php');

if(!$user->isGhost())
    exit('Keine Berechtigung');

$v = $base->vars();

if($v['task'] == 'close')
{
    $user->setGhost(false);
}
?>
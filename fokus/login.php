<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin() || !$user->isLogged())
{
    if($index)
    {
        $base->go(BACKEND_DIR.'index.php');
    }
    else
    { 
        exit('new-log-in');
    }
}
if(!$user->getRole())
{
    if($index)
    {
        $base->go(BACKEND_DIR.'enter-role-assignment.php');
    }
    else
    { 
        exit('Keine Rolle gewählt');
    }
}


$rechte = $user->getRights();
?>
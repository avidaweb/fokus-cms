<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('fks', 'extensions') || $index != 'extensions')
    exit($user->noRights());
    
echo '
<h1>'.$trans->__('Erweiterungen verwalten.').'</h1>

<div id="extension-dashboard"></div>';
?>
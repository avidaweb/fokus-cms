<?php
define('IS_BACKEND_DEEP', true, true);

require_once('../../inc/header.php');
require_once('../login.php');

echo '
<div class="widgets">
    <div class="loading"></div>
</div>
<div class="menu">
    <div class="inner">
        <a class="close">'.$trans->__('Dashboard schließen').'</a>
        
        <p class="personal">
            <a class="sort">'.$trans->__('Widgets sortieren').'</a>
            '.($api->checkUserRights('fks', 'customize')?'<a class="customize">'.$trans->__('Workflow optimieren').'</a>':'').'
            <a class="profile">'.$trans->__('Persönliche Einstellungen').'</a>
        </p>
        
        '.($api->checkUserRights('fks', 'cleaner') || $api->checkUserRights('fks', 'options') || $api->checkUserRights('fks', 'extensions')?'
        <p class="system">
            '.($api->checkUserRights('fks', 'extensions') && count($api->getExtensions(false, false))?'<a class="extensions">'.$trans->__('Erweiterungen').'</a>':'').'
            '.($api->checkUserRights('fks', 'cleaner')?'<a class="clean">'.$trans->__('Systemreinigung').'</a>':'').'
            '.($api->checkUserRights('fks', 'options')?'<a class="options">'.$trans->__('Systemeinstellungen').'</a>':'').'
        </p>
        ':'').'
        
        <p class="website">
            <a class="frontend" href="'.DOMAIN.'" target="_blank">'.$trans->__('Website öffnen').'</a>
            '.($api->checkUserRights('fks', 'ghost')?'<a href="'.DOMAIN.'/fokus/sub_ghost.php?index=go" target="_blank">'.$trans->__('Website Direktbearbeitung').'</a>':'').'
            '.($api->checkUserRights('fks', 'foresight')?'<a class="foresight">'.$trans->__('Website Vorschau').'</a>':'').'
        </p>
        
        '.(FKS_OPEN?'
        <p>
            <a href="http://community.fokus-cms.de/" target="_blank">'.$trans->__('Support &amp; Feedback').'</a>
        </p>
        ':'').'
        
        <a class="logout" href="'.DOMAIN.'/fokus/enter.php?logout=true">'.$trans->__('%1 abmelden', false, array($api->getUserData('first_name'))).'</a>
    </div>
</div>';
?>
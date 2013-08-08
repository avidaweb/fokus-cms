<?php
define('IS_BACKEND_DEEP', true, true);

require_once('../../inc/header.php');
require_once('../login.php');

require_once('widget-fks.php');

$widgets = $api->getWidgetsSorted();

echo '
<form id="widget-sort">
    <h1>'.$trans->__('Widgets sortieren.').'</h1>
    
    <div class="box">
        <h2 class="calibri">'.$trans->__('Wichtigkeit der Widgets bestimmen.').'</h2>
    
        <p class="introtext">
            '.$trans->__('Je wichtiger Sie ein Widget einstufen, desto weiter oben wird es im Dashboard angezeigt. Wenn Lücken auf Grund unterschiedlicher Größen entstehen, füllt fokus diese automatisch mit passenden Widgets auf.').'
        </p>';
    
        foreach($widgets as $wkey => $widget)
        {
            $prio = $user->getWidget($wkey, 'prio');
            
            echo '
            <div class="widget-prio">
                <h6>'.$widget['title'].'</h6>
                
                <div class="prio">
                    <span>'.$trans->__('unwichtig').'</span>
                    <div class="slider">
                        <input type="hidden" name="prio['.$wkey.']" value="'.($prio?$prio:50).'" />
                    </div>
                    <span>'.$trans->__('wichtig').'</span>
                </div>
            </div>';  
        }
        
        echo '
        <div class="standard">
            <a>'.$trans->__('Standardeinstellungen wiederherstellen').'</a>
        </div>
    </div>
</form>';
?>
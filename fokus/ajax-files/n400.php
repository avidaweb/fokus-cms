<?php
if($index == 'n400' && $user->r('dat'))
{
    echo '
    <h1>'.$trans->__('Bilder &amp; Dateien.').'</h1>
    
    <div class="box">
        
        <div id="bilderC">
            <ul id="bilderN">
                <li'.(!$user->r('dat', 'bilder')?' style="display:none;"':'').'><a href="#bilder">'.$trans->__('Bilder').'</a></li>
                <li style="display:none;"><a href="#screen">'.$trans->__('Screenshots').'</a></li>
                <li'.(!$user->r('dat', 'dateien')?' style="display:none;"':'').'><a href="#docdateien">'.$trans->__('Dateien').'</a></li>
                <li style="display:none;"><a href="#multi">'.$trans->__('Multimedia-Dateien').'</a></li>
            </ul>
            
            <div id="bilder" class="bilderM ui-tabs-hide"><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></div>
            <div id="screen" class="bilderM ui-tabs-hide"></div>
            <div id="docdateien" class="bilderM ui-tabs-hide"></div>
            <div id="multi" class="bilderM ui-tabs-hide"></div>
        </div>
        
    </div>';
} 
?>
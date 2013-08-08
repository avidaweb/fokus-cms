<?php
if($user->r('per', 'rollen') && $index == 'n540')
{
    echo '
    <div id="rollenuebersicht">
        <h1>'.$trans->__('Rollen &amp; Rechte.').'</h1>
        
        <div class="box">
            '.$trans->__('In diesem Bereich können Sie verschiedene Rollen inkl. der entsprechenden Funktions- und Zugriffsrechte vergeben. Hier wenden Sie sich bei Fragen an den Systemadministrator oder ihren Support-Partner.').'
        </div>
        
        <div class="box">
            <h2>'.$trans->__('Vorhandene Rollen.').'</h2>
            
            <div class="rollen">
                <table id="rolle">
                    <tr><td><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>
                </table>
            </div>
            
            <div id="neubutton"><button class="inc_users" id="n550" rel="0">'.$trans->__('Neue Rolle anlegen').'</button></div>
        </div>
    </div>';
}
?>
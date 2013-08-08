<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'pn') || !$suite->rm(10) || $index != 'n640')
    exit($user->noRights());

echo '
<h1>'.$trans->__('Persönliche Nachrichten.').'</h1>

<div class="box" id="pn">
    <div id="pnL">
        <div id="neue_pn">
            <img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
        </div>

        <h3 class="calibri">'.$trans->__('Empfänger suchen.').'</h3>
        <input id="suche_empf" type="text" />

        <form id="last_empf_form" method="post">
        <div id="last_empf">
            <img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
        </div>
        </form>
    </div>
    <div id="pnR">
        <img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
    </div>

    <div id="pn_mehr"></div>
</div>';
?>
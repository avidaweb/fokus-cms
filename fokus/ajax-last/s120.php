<?php
if($index == 's120')
{    
    if(!$user->r('suc', 'zuve'))
        exit($user->noRights());
    
    echo '
    <h1>'.$trans->__('Zuletzt verwendete Inhalte.').'</h1>
    <input type="hidden" name="papierkorb" value="" />
    <input type="hidden" name="suche" value="" />
    
    <div class="box" id="last_use">
        <div class="loading"><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /><br /></div>
    	<table></table>
    </div>';
}
?>
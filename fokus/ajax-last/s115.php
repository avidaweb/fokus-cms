<?php
if($index == 's115')
{    
    if(!$user->r('suc', 'papierkorb'))
        exit($user->noRights());       
    
    echo '
    <h1>'.$trans->__('Papierkorb.').'</h1>
    <input type="hidden" name="papierkorb" value="true" />
    <input type="hidden" name="suche" value="" />
    
    <div class="box" id="last_use">
        <div class="loading"><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /><br /></div>
    	<table></table>
    </div>';
}
?>
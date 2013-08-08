<?php
if($index == 's110')
{        
    if(!$user->r('suc', 'suche'))
        exit($user->noRights());
            
    echo '
    <h1>'.$trans->__('Suche.').'</h1>
    <input type="hidden" name="papierkorb" value="" />
    <input type="hidden" name="suche" value="true" />
    
    <div class="box" id="search_box">
        <label for="whole_q">'.$trans->__('Geben Sie einen Suchbegriff oder eine ID ein:').'</label>
        <input type="search" name="q" id="whole_q" />
    </div>
    
    <div class="box" id="last_use" style="display:none;">
        <div class="loading"><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /><br /></div>
    	<table></table>
    </div>';
}
?>
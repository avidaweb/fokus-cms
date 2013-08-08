<?php
define('IS_FORESIGHT', true, true);

require('../../header.php');

if(!$user->isAdmin() || !$user->isForesight())
    exit('Keine Berechtigung');
    

$foresight_time = $user->getForesight('time');
    
$h = date('H', $foresight_time);
$m = date('i', $foresight_time);

echo '
<form action="'.DOMAIN.'/fokus/sub_foresight.php?index=s451" method="post">
<div class="fks_wrapper">
    <div class="fks_left">
        foresight. Websitevorschau f&uuml;r folgendes Datum:
    </div>
    <div class="fks_right">
        <input type="hidden" name="url" value="'.$fksdb->save($_GET['url']).'" />
        
        <div class="fks_rest">
            <span class="fks_B">um</span>
            
            <select name="uhr1">';
                for($z = 0; $z <= 24; $z++)
                    echo '<option value="'.$z.'"'.($h == $z?' selected="selected"':'').'>'.str_pad($z, 2 ,'0', STR_PAD_LEFT).'</option>';
            echo '
            </select> : 
            <select name="uhr2">';
                for($z = 0; $z <= 60; $z++)
                    echo '<option value="'.$z.'"'.($m == $z?' selected="selected"':'').'>'.str_pad($z, 2 ,'0', STR_PAD_LEFT).'</option>';
            echo '
            </select>
            
            <span class="fks_C">Uhr.</span>
            
            <input type="submit" value="beenden" name="stop" class="submit" />
            <input type="submit" value="aktualisieren" name="go" class="submit" />
        </div>
        
        <div class="fks_datumO">
            <input type="text" class="fks_datum" name="datum" value="'.date('d.m.Y', $foresight_time).'" />
        </div>
    </div>
</div>
</form>';

?>
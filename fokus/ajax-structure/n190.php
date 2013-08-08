<?php
if($index != 'n190')
    exit();
    
if(!$user->r('str', 'kat') && !$v->just_select)
    exit($user->noRights());
    
if(!$user->r('dok', 'cats') && $v->just_select)
    exit($user->noRights());
    
echo '
<h1>'.(!$v->just_select?$trans->__('Kategorien verwalten.'):$trans->__('Kategorien auswählen.')).'</h1>

<div class="movebox" id="kategorien">
    <canvas width="600" height="1"></canvas>

    <div class="loadme">
        <img src="images/loading_grey.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
    </div>
    
    <img src="images/moveboxH.png" alt="" class="schatten" />
    <div class="moved baum" data-kat="0">
    
    </div>
    <img src="images/moveboxB.png" alt="" class="schatten" />
</div>';

if($v->just_select)
{
    echo ' 
    <div class="box_save" style="display:none;">
        <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
        <input type="button" value="'.$trans->__('übernehmen').'" class="bs2" />
    </div>';
}
else
{
    echo '<div class="box"></div>';    
}
?>
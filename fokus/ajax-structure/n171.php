<?php
if($user->r('str', 'menue') && $index == 'n171')
{
    $struk = $fksdb->fetch("SELECT id FROM ".SQLPRE."structures WHERE id = '".$base->getStructureID()."' LIMIT 1");
    if(!$struk)
        exit('<div class="ifehler">'.$trans->__('Momentan ist keine Struktur zur Bearbeitung gewählt').'</div>');
        
    $menue = $base->getActiveTemplateConfig('menus', $v->menue);
    if(!is_array($menue))
        exit($trans->__('Gewähltes Menü existiert nicht'));
    
    echo '
    <h1>'.$trans->__('Menü bearbeiten:').' '.Strings::cut($menue['name'], 30).'.</h1>
    <input type="hidden" name="menue" value="'.$v->menue.'" />
    
    <div class="movebox" id="menues">
        <canvas width="600" height="1"></canvas>
    
        <div class="loadme">
            <img src="images/loading_grey.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
        </div>
        
        <img src="images/moveboxH.png" alt="" class="schatten" />
        <div class="moved baum" data-kat="0"></div>
        <img src="images/moveboxB.png" alt="" class="schatten" />
    </div>
    
    <div class="box"></div>';
}
?>
<?php
if($user->r('str', 'menue') && $index == 'n170')
{
    echo '<h1>'.$trans->__('Menüs bearbeiten').'</h1>';
    
    if(!is_array($base->getActiveTemplateConfig('menus')))
        exit('<div class="ifehler">'.$trans->__('Keine Menüs in diesem Template vorhanden').'</div>');
    
    echo '
    <div class="box">
        <div id="vmenues" class="boxedtable">';
            foreach($base->getActiveTemplateConfig('menus') as $id => $c)
            {
                echo '
                <p>
                    <a data-id="'.$id.'">'.$c['name'].'</a>
                    <span>'.$c['desc'].'</span>
                </p>';
            }
        echo '
        </div>
    </div>';
}
?>
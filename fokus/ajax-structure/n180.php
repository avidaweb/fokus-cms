<?php
if(!$user->r('str', 'slots') || $index != 'n180')
    exit($user->noRights());
    
echo '<h1>'.$trans->__('Slots bearbeiten').'</h1>';

if(!is_array($base->getActiveTemplateConfig('slots')))
    exit('<div class="ifehler">'.$trans->__('Keine Slots in diesem Template vorhanden').'</div>');

echo '
<div class="box">
    <div id="vslots" class="boxedtable">';
        foreach($base->getActiveTemplateConfig('slots') as $id => $c)
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
?>
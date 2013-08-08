<?php
if(!$user->r('str', 'slots') || !$user->r('str', 'ele') || $index != 'n160')
    exit($user->noRights());
    
$element = $fksdb->fetch("SELECT * FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
if(!$element)
    exit('<div class="ifehler">'.$trans->__('Ausgewähltes Element existiert nicht, gehört zu einer nicht aktiven Struktur oder befindet sich im Papierkorb.').'</div>');
    
$slots = $base->db_to_array($element->slots);
if(!is_array($slots)) $slots = array();
    
echo '<h1>'.$trans->__('Slots überschreiben.').'</h1>';

if(!is_array($base->getActiveTemplateConfig('slots')))
    
echo '<pre>'.print_r($slots, true).'</pre>';
echo '
<div class="box">
    <h3 class="calibri">
        '.$trans->__('Für welche Slots im Strukturelement <strong>%1</strong> möchten Sie individuelle Inhalte definieren?', false, array($element->titel)).'
    </h3>

    <div id="vslots" class="boxedtable">';
        foreach($base->getActiveTemplateConfig('slots') as $id => $c)
        {
            echo '
            <p'.(!$slots[$id]?' class="inactive""':'').'>
                <em>
                    <input type="checkbox" value="1" id="element_slot_'.$id.'" data-id="'.$id.'"'.($slots[$id]?' checked':'').' />
                </em>
                
                <label'.($slots[$id]?' style="display:none;"':'').'>'.$c['name'].'</label>                
                <a data-id="'.$id.'"'.(!$slots[$id]?' style="display:none;"':'').'>'.$c['name'].'</a>
                
                <span>'.$c['desc'].'</span>
            </p>';
        }
    echo '
    </div>
</div>';
?>
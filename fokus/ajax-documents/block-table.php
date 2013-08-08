<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$la = $base->db_to_array($row->html);
if(!is_array($la)) $la = array();
$c = (object)$la;

echo '
<form method="post" id="ctabelle">
    <input type="hidden" name="x" value="'.$c->x.'" />
    <input type="hidden" name="y" value="'.$c->y.'" />
    <input type="hidden" name="value" value="'.$c->value.'" />
    
    <div class="box">
        <div id="tabellentabs">
            <ul id="tabellentabs_navi">
                <li><a href="#ctabelle_layout">'. $trans->__('Aufbau') .'</a></li>
                <li><a href="#ctabelle_inhalt">'. $trans->__('Inhalt') .'</a></li>
            </ul>
            
            <div id="ctabelle_layout" data-kat="layout" class="ctab"></div>
            <div id="ctabelle_inhalt" data-kat="inhalt" class="ctab"></div>
        </div>
    </div>
</form>';
?>
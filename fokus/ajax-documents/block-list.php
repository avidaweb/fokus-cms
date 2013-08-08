<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$la = $base->fixedUnserialize($row->html);
if(!is_array($la)) $la = array();

$kindof = intval($la['kindof']);
unset($la['kindof']);

echo '
<form method="post" id="listen_form">
<div class="box">';
    foreach($la as $l)
    {
        echo '
        <div class="liste">
            <div class="area"><textarea name="liste[]" id="liste_'.Strings::createID().'">'.str_replace('&quot;', '"', htmlspecialchars_decode(Strings::cleanString($l))).'</textarea></div>
            <p class="move"></p>
            <a class="del">'. $trans->__('Listenpunkt entfernen') .'</a>
        </div>';
    }
    if(!count($la))
    {
        echo '
        <div class="liste">
            <div class="area"><textarea name="liste[]" id="liste_'.Strings::createID().'"></textarea></div>
            <p class="move"></p>
            <a class="del">'. $trans->__('Listenpunkt entfernen') .'</a>
        </div>';
    }
    
    echo '
    <button class="new">'. $trans->__('Neuen Listenpunkt hinzufügen') .'</button>
    
    <div class="type">
        <strong class="calibri">'. $trans->__('Art der Liste') .'</strong>
        
        <span>
            <input type="radio" name="kindof" value="0" id="listtype0"'.($kindof == 0?' checked':'').' />
            <label for="listtype0">'. $trans->__('Aufzählungsliste (&lt;ul&gt;)') .'</label>
        </span>
        <span>
            <input type="radio" name="kindof" value="1" id="listtype1"'.($kindof == 1?' checked':'').' />
            <label for="listtype1">'. $trans->__('Nummerierte Liste (&lt;ol&gt;)') .'</label>
        </span>
    </div>
</div>
</form>';
?>
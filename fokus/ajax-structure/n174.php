<?php
if($user->r('str', 'menue') && $index == 'n174')
{
    $k = $fksdb->fetch("SELECT id FROM ".SQLPRE."menus WHERE id = '".$v->id."' AND struktur = '".$base->getStructureID()."' LIMIT 1");
    if(!$k)
        exit();
        
    $spr = array();
    parse_str($_POST['spr'], $la);
    if(!is_array($la['spr']))
        $la['spr'] = array();
    foreach($la['spr'] as $l => $w)
    {
        if($fksdb->save($l) && $fksdb->save($w))
            $spr[$fksdb->save($l)] = $fksdb->save($w);
    }
    
    $updt = $fksdb->query("UPDATE ".SQLPRE."menus SET url = '".$v->url."', sprachen = '".serialize($spr)."', ziel = '".$v->ziel."', power = '".$v->power."', klasse = '".$v->klasse."' WHERE id = '".$k->id."' LIMIT 1");
} 
?>
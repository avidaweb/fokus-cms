<?php
if($user->r('str', 'ele') && $index == 'n145')
{
    parse_str($_POST['f'], $spt);
    if(!is_array($spt)) exit();
    
    $sp = array();
    foreach($spt['sprache'] as $s1 => $wert)
    {
        $lan = $fksdb->save($s1);
        
        if(is_array($wert) && $lan)
        {
            $sp[$lan]['titel'] = $fksdb->save($wert['titel']);
            $sp[$lan]['htitel'] = $fksdb->save($wert['htitel']);
            $sp[$lan]['desc'] = $fksdb->save($wert['desc']);
            $sp[$lan]['tags'] = $fksdb->save($wert['tags']);
            $sp[$lan]['url'] = $fksdb->save($wert['url']);
            
            $cfields = $api->getCustomFields();
            
            if(is_array($cfields))
            {
                foreach($cfields as $k => $z)
                    $sp[$lan][$k] = $fksdb->save($wert[$k]);
            }
        }
    }
    
    $upt = $fksdb->query("UPDATE ".SQLPRE."elements SET titel = '".$fksdb->save($spt['titel'])."', sprachen = '".serialize($sp)."' WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1"); 
}
?>
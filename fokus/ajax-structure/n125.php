<?php
if($user->r('str', 'ele') && $index == 'n125')
{
    parse_str($_POST['f'], $fa);
    if(!is_array($fa)) exit();
    
    $ids = Strings::explodeCheck(',', $v->elemente);
	if(!count($ids)) exit();
	
	$sql_ids = "";
	foreach($ids as $i)
		$sql_ids .= ($sql_ids?" OR ":"")." id = '".$i."' ";
    
    $templatedatei = $fksdb->save($fa['templatedatei']);
    $m_templatedatei = $fksdb->save($fa['m_templatedatei']);
    
    // Lebensdauer Start
    $von = explode('.', $fksdb->save($fa['anfang'])); 
    $bis = explode('.', $fksdb->save($fa['bis'])); 
    $vonH = $fksdb->save($fa['anfangH']); 
    $vonM = $fksdb->save($fa['anfangM']); 
    $bisH = $fksdb->save($fa['bisH']); 
    $bisM = $fksdb->save($fa['bisM']); 
    
    $vonA = (count($von) > 2?mktime($vonH, $vonM, 0, $von[1], $von[0], $von[2]):0);
    $bisA = (count($bis) > 2?mktime($bisH, $bisM, 0, $bis[1], $bis[0], $bis[2]):0);
    $anfang = ($fa['vonC'] && $vonA > 86400?$vonA:0);
    $bis = ($fa['bisC'] && $bisA > 86400?$bisA:0);
    /// Lebensdauer Ende
    
    $noseo = $fksdb->save($fa['noseo']);
    $no_navi = $fksdb->save($fa['no_navi']);
    $is_hidden = $fksdb->save($fa['is_hidden']);
    
    
    // Updaten
    $sql_add = "";
    if($fa['check_td'])
        $sql_add .= ($sql_add?", ":"")." templatedatei = '".$templatedatei."'";
    if($fa['check_tdm'])
        $sql_add .= ($sql_add?", ":"")." m_templatedatei = '".$m_templatedatei."'";
    if($fa['check_navi'])
        $sql_add .= ($sql_add?", ":"")." no_navi = '".$no_navi."'";
    if($fa['check_sv'])
        $sql_add .= ($sql_add?", ":"")." is_hidden = '".$is_hidden."'";
    if($fa['check_noseo'])
        $sql_add .= ($sql_add?", ":"")." noseo = '".$noseo."'";
    if($fa['check_ld'])
    {
        $sql_add .= ($sql_add?", ":"")." anfang = '".$anfang."'";
        $sql_add .= ($sql_add?", ":"")." bis = '".$bis."'";
    }
        
    if($sql_add)
    {
        $updt = $fksdb->query("UPDATE ".SQLPRE."elements SET ".$sql_add." WHERE struktur = '".$base->getStructureID()."' AND (".$sql_ids.")");
    }
}
?>
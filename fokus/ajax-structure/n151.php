<?php
if($user->r('str', 'ele') && $index == 'n151')
{
    parse_str($_POST['f'], $fa);
    if(!is_array($fa)) exit();

    $element = $fksdb->fetch("SELECT id, anfang, bis, rollen, rollen_fehler, noseo, templatedatei, m_templatedatei FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
    if(!$element)
        exit();  
    
    $frei = $fksdb->save($fa['frei']);
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

    if($fa['restriction'])
    {
        $ro = serialize($fa['ro']);
        $rollen_fehler = $fksdb->save($fa['rollen_fehler']);
    }
    else
    {
        $ro = '';
        $rollen_fehler = 0;
    }
    
    $noseo = $fksdb->save($fa['noseo']);
    $nositemap = $fksdb->save($fa['nositemap']);
    $is_hidden = $fksdb->save($fa['is_hidden']);
    
    $no_navi = $fksdb->save($fa['in_navi']);
    $neues_fenster = $fksdb->save($fa['neues_fenster']);
	
	$cfa = array();
	if(count($fa['cf']))
	{
		foreach($fa['cf'] as $cfk => $cfv)
			$cfa[$fksdb->save($cfk)] = $fksdb->save($cfv);
	}
    
    // Standard wenn Rechte nicht da sind
    if($eleopt && !$user->r('str', 'lebensdauer'))
    {
        $anfang = $element->anfang;
        $bis = $element->bis;
    }
    if($eleopt && !$user->r('str', 'rollen'))
    {
        $ro = $element->rollen;
        $rollen_fehler = $element->rollen_fehler;
    }
    if($eleopt && !$user->r('str', 'seo'))
    {
        $noseo = $element->noseo;
    }
    if($eleopt && !$user->r('str', 'template'))
    {
        $templatedatei = $element->templatedatei;
        $m_templatedatei = $element->m_templatedatei;
    }
    
    $upt = $fksdb->query("UPDATE ".SQLPRE."elements SET rollen = '".$ro."', templatedatei = '".$templatedatei."', m_templatedatei = '".$m_templatedatei."', anfang = '".$anfang."', bis = '".$bis."', rollen_fehler = '".$rollen_fehler."', noseo = '".$noseo."', nositemap = '".$nositemap."', frei = '".$frei."', no_navi = '".$no_navi."', is_hidden = '".$is_hidden."', neues_fenster = '".$neues_fenster."', cf = '".serialize($cfa)."' WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");  
}
?>
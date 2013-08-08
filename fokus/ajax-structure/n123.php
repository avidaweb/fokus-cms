<?php
if($user->r('str', 'ele') && $index == 'n123')
{
    $ids = Strings::explodeCheck(',', $v->elemente);
	if(!count($ids) && intval($v->elemente)) $ids[] = intval($v->elemente);
	if(!count($ids)) exit();
	
	$sql_ids = "";
	foreach($ids as $i)
		$sql_ids .= ($sql_ids?" OR ":"")." id = '".$i."' ";
		
    if($v->task == 'del')
    {
		foreach($ids as $i)
		{
			$alq = $fksdb->fetch("SELECT element, id FROM ".SQLPRE."elements WHERE id = '".$i."' AND struktur = '".$base->getStructureID()."' LIMIT 1");
			$kq = $fksdb->query("UPDATE ".SQLPRE."elements SET element = '".$alq->element."' WHERE element = '".$alq->id."' AND struktur = '".$base->getStructureID()."'");
		}
        $del = $fksdb->query("DELETE FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' AND (".$sql_ids.")");
	}
    elseif($v->task == 'close')
    {
		$updt = $fksdb->query("UPDATE ".SQLPRE."elements SET frei = '0' WHERE struktur = '".$base->getStructureID()."' AND (".$sql_ids.")");
	}
    elseif($v->task == 'free')
    {
		$updt = $fksdb->query("UPDATE ".SQLPRE."elements SET frei = '1' WHERE struktur = '".$base->getStructureID()."' AND (".$sql_ids.")");
	}
    elseif($v->task == 'clone')
    {
		foreach($ids as $i)
		{
			$exQ = $fksdb->query("SELECT * FROM ".SQLPRE."elements WHERE id = '".$i."' AND struktur = '".$base->getStructureID()."' LIMIT 1");
            $ex = $fksdb->fetchArray($exQ);
			
            $fksdb->copy($ex, "elements", array(
            	"struktur" => $base->getStructureID()
            ));
			$new_id = $fksdb->getInsertedID();
			
			$sds = $fksdb->query("SELECT * FROM ".SQLPRE."document_relations WHERE element = '".$ex['id']."' ORDER BY sort");
			while($sd = $fksdb->fetchArray($sds))
            {
                $fksdb->copy($sd, "document_relations", array(
                    "element" => $new_id,
                    "timestamp" => time()
                ));
			}
			
			// Nochmal durchshaken
			$kcount = 0;
			$kq = $fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE element = '".$ex['element']."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' ORDER BY sort, id ASC");
			while($kx = $fksdb->fetch($kq))
			{
				 $krr = $fksdb->query("UPDATE ".SQLPRE."elements SET sort = '".$kcount."' WHERE id = '".$kx->id."' LIMIT 1");
				 $kcount ++;
			}
		}
	}
}
?>
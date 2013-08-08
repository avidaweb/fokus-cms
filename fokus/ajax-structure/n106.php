<?php
if($user->r('str', 'struk') && $index == 'n106')
{
    if($v->titel)
    {
        $fksdb->insert("structures", array(
        	"titel" => $v->titel
        ));
        $id = $fksdb->getInsertedID();  
        
        if($v->clone && $id)
        {
            $id_converter = array();
            
            $ergebnis = $fksdb->query("SELECT * FROM ".SQLPRE."elements WHERE struktur = '".$v->clone."' AND papierkorb = '0' ORDER BY element ASC");
            while($row = $fksdb->fetchArray($ergebnis))
            {
                $nelement = ($row['element'] > 0?$id_converter[$row['element']]:0);	 
                
                $fksdb->copy($row, "elements", array(
                    "struktur" => $id,
                    "element" => $nelement
                ));		
                			 
                $element_id = $fksdb->getInsertedID(); 
                $id_converter[$row['id']] = $element_id;
                
                $blQ = $fksdb->query("SELECT * FROM ".SQLPRE."document_relations WHERE element = '".$row['id']."'");
                while($bl = $fksdb->fetchArray($blQ))
                {
                    $fksdb->copy($bl, "document_relations", array(
                        "element" => $element_id
                    ));	
                }
            }            
        }
    }
}
?>
<?php
if($user->r('str', 'ele') && (!$eleopt || $user->r('str', 'dk')) && $index == 'n156')
{
    $element = $fksdb->fetch("SELECT id, klasse, frei, templatedatei, m_templatedatei FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
    if(!$element || $element->klasse)
        exit();
        
    $iid = $element->id;
        
    parse_str($_POST['f'], $dk);
    if(!is_array($dk)) exit();
    
    if($dk['dk_type0'])
        $dk['dk_type'] = 1;
    if($dk['dk_type1'])
        $dk['dk_type'] = 2;
    if($dk['dk_type0'] && $dk['dk_type1'])
        $dk['dk_type'] = 3;
        
    if($dk['dklasse']) $dklasse = serialize($dk);
    else $dklasse = '';
    
    $upt = $fksdb->query("UPDATE ".SQLPRE."elements SET dklasse = '".$dklasse."' WHERE id = '".$iid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");  
    
    if(($dk['dk_type'] == 1 || $dk['dk_type'] == 3) && $dklasse)
    {
        for($x = 0; $x < count($dk['dk_klassen']); $x++)
            $not_del .= (!$x?" AND (":"")." klasse != '".$dk['dk_klassen'][$x]."' ".($x+1 == count($dk['dk_klassen'])?")":" OR ");
    }
    if(($dk['dk_type'] == 2 || $dk['dk_type'] == 3) && $dklasse)
    {
        for($x = 0; $x < count($dk['dk_klassen']); $x++)
            $not_del2 .= (!$x?" AND (":"")." klasse != '".$dk['dk_klassen'][$x]."' ".($x+1 == count($dk['dk_klassen'])?")":" OR ");
    }
    $del = $fksdb->query("DELETE FROM ".SQLPRE."document_relations WHERE element = '".$iid."' AND klasse != '' ".$not_del);
    $del2 = $fksdb->query("DELETE FROM ".SQLPRE."elements WHERE element = '".$iid."' AND klasse != '' ".$not_del2);
            
    if($dklasse && $iid)
    { 
        for($x = 0; $x < count($dk['dk_klassen']); $x++)
        {
            $fk = $base->open_dklasse('../content/dklassen/'.$dk['dk_klassen'][$x].'.php');
            if(!$fk['name'])
                continue;
                
            $dktitel = $trans->__('Dokumentenklasse &quot;%1&quot;', false, array($fk['name']));
        
            if($dk['dk_type'] == 1 || $dk['dk_type'] == 3)
            {
                $lastsd = $fksdb->fetch("SELECT id FROM ".SQLPRE."document_relations WHERE element = '".$iid."' ORDER BY sort DESC");
                $sort = $lastsd->sort + 1;
                
                $check = $fksdb->query("SELECT id FROM ".SQLPRE."document_relations WHERE element = '".$iid."' AND klasse = '".$dk['dk_klassen'][$x]."' LIMIT 1");
                if(!$fksdb->count($check))
                {
                    $fksdb->insert("document_relations", array(
                    	"element" => $iid,
                    	"dokument" => 0,
                    	"klasse" => $dk['dk_klassen'][$x],
                    	"timestamp" => $base->getTime(),
                    	"sort" => $sort
                    ));
                }
                
                $seiten = $fksdb->query("SELECT id FROM ".SQLPRE."document_relations WHERE klasse = '".$dk['dk_klassen'][$x]."'");
                $upt2 = $fksdb->query("UPDATE ".SQLPRE."documents SET seiten = '".$fksdb->count($seiten)."' WHERE klasse = '".$dk['dk_klassen'][$x]."'");   
            }
            if($dk['dk_type'] == 2 || $dk['dk_type'] == 3)
            {
                $lastsd = $fksdb->fetch("SELECT id FROM ".SQLPRE."elements WHERE element = '".$iid."' ORDER BY sort DESC");
                $sort = $lastsd->sort + 1;
                
                $check = $fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE element = '".$iid."' AND klasse = '".$dk['dk_klassen'][$x]."' LIMIT 1");
                if(!$fksdb->count($check))
                {
                    $fksdb->insert("elements", array(
                    	"struktur" => $base->getStructureID(),
                    	"element" => $iid,
                    	"templatedatei" => $element->templatedatei,
                    	"m_templatedatei" => $element->m_templatedatei,
                    	"sort" => $sort,
                    	"klasse" => $dk['dk_klassen'][$x],
                    	"titel" => $dktitel,
                    	"frei" => $element->frei 
                    ));
                    $e_id = $fksdb->getInsertedID();
                    
                    $check2 = $fksdb->query("SELECT id FROM ".SQLPRE."document_relations WHERE element = '".$e_id."' AND klasse = '-1' LIMIT 1");
                    if(!$fksdb->count($check2))
                    {
                        $fksdb->insert("document_relations", array(
                        	"element" => $e_id,
                        	"dokument" => 0,
                        	"klasse" => -1,
                        	"timestamp" => $base->getTime(),
                        	"sort" => 0
                        ));
                    }
                    echo $e_id;
                }       
                else
                {
                    $self_ele = $fksdb->fetch($check);
                    $updt = $fksdb->query("UPDATE ".SQLPRE."elements SET titel = '".$dktitel."' WHERE element = '".$iid."' AND id = '".$self_ele->id."' LIMIT 1");
                    
                    $check2 = $fksdb->query("SELECT id FROM ".SQLPRE."document_relations WHERE element = '".$self_ele->id."' AND klasse = '-1' LIMIT 1");
                    if(!$fksdb->count($check2))
                    { 
                        $fksdb->insert("document_relations", array(
                        	"element" => $self_ele->id,
                        	"dokument" => 0,
                        	"klasse" => -1,
                        	"timestamp" => $base->getTime(),
                        	"sort" => 0
                        ));
                    }
                    echo $self_ele->id;
                }
            } 
        }
    }
}
?>
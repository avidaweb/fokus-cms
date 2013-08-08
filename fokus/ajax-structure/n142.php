<?php
if(($user->r('str', 'ele') || $user->r('str', 'slots') || $user->r('fks', 'opt')) && $index == 'n142')
{
    if($v->slot)
    {
        if(!$user->r('str', 'slots'))
            exit($user->noRights());
            
        if($v->sid)
        {
            $element = $fksdb->fetch("SELECT id FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
            if(!$element) 
                exit();    
        }
            
        if($v->task == 'add')
        {   
            $dok = $fksdb->fetch("SELECT id FROM ".SQLPRE."documents WHERE id = '".$v->dok."' AND papierkorb = '0' LIMIT 1");
            if(!$dok) exit();
            
            $last_sort = $fksdb->fetch("SELECT sort FROM ".SQLPRE."document_relations WHERE slot = '".$v->slot."'".($v->sid?" AND element = '".$v->sid."'":"")." ORDER BY sort DESC LIMIT 1");
            $sort = $last_sort->sort + 1;
            
            $fksdb->insert("document_relations", array(
            	"slot" => $v->slot,
            	"dokument" => $dok->id,
            	"timestamp" => $base->getTime(),
            	"sort" => $sort,
                "element" => ($v->sid?$v->sid:0)
            ));
        }
        elseif($v->task == 'delete')
        {   
            $del = $fksdb->query("DELETE FROM ".SQLPRE."document_relations WHERE id = '".$v->sd."' AND slot = '".$v->slot."'".($v->sid?" AND element = '".$v->sid."'":"")." LIMIT 1");
        }
        elseif($v->task == 'sort')
        {   
            $no = explode(',', $v->neworder);
            $xcount = 0;
            
            foreach($no as $sdid)
            {
                $up = $fksdb->query("UPDATE ".SQLPRE."document_relations SET sort = '".$xcount."' WHERE id = '".$sdid."' AND slot = '".$v->slot."'".($v->sid?" AND element = '".$v->sid."'":"")." LIMIT 1");
                $xcount ++;
            }
        }
    }
    elseif($v->error)
    {
        if(!$user->r('fks', 'opt'))
            exit($user->noRights());
            
        if($v->task == 'add')
        {   
            $dok = $fksdb->fetch("SELECT id FROM ".SQLPRE."documents WHERE id = '".$v->dok."' AND papierkorb = '0' LIMIT 1");
            if(!$dok) exit();
            
            $last_sort = $fksdb->fetch("SELECT sort FROM ".SQLPRE."document_relations WHERE error_page = '".$v->error."' ORDER BY sort DESC LIMIT 1");
            $sort = $last_sort->sort + 1;
            
            $fksdb->insert("document_relations", array(
            	"error_page" => $v->error,
            	"dokument" => $dok->id,
            	"timestamp" => $base->getTime(),
            	"sort" => $sort
            ));
        }
        elseif($v->task == 'delete')
        {   
            $del = $fksdb->query("DELETE FROM ".SQLPRE."document_relations WHERE id = '".$v->sd."' AND error_page = '".$v->error."' LIMIT 1");
        }
        elseif($v->task == 'sort')
        {   
            $no = explode(',', $v->neworder);
            $xcount = 0;
            
            foreach($no as $sdid)
            {
                $up = $fksdb->query("UPDATE ".SQLPRE."document_relations SET sort = '".$xcount."' WHERE id = '".$sdid."' AND error_page = '".$v->error."' LIMIT 1");
                $xcount ++;
            }
        }
    }
    else
    {
        if(!$user->r('str', 'ele'))
            exit($user->noRights());
            
        $element = $fksdb->fetch("SELECT id FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
        if(!$element) exit();
        
        if($v->task == 'add')
        {   
            $dok = $fksdb->fetch("SELECT id FROM ".SQLPRE."documents WHERE id = '".$v->dok."' AND papierkorb = '0' LIMIT 1");
            if(!$dok) exit();
            
            $last_sort = $fksdb->fetch("SELECT sort FROM ".SQLPRE."document_relations WHERE element = '".$element->id."' ORDER BY sort DESC LIMIT 1");
            $sort = $last_sort->sort + 1;
            
            $fksdb->insert("document_relations", array(
            	"element" => $element->id,
            	"dokument" => $dok->id,
            	"timestamp" => $base->getTime(),
            	"sort" => $sort
            ));
        }
        elseif($v->task == 'delete')
        {   
            $del = $fksdb->query("DELETE FROM ".SQLPRE."document_relations WHERE id = '".$v->sd."' AND element = '".$element->id."' LIMIT 1");
        }
        elseif($v->task == 'sort')
        {   
            $no = explode(',', $v->neworder);
            $xcount = 0;
            
            foreach($no as $sdid)
            {
                $up = $fksdb->query("UPDATE ".SQLPRE."document_relations SET sort = '".$xcount."' WHERE id = '".$sdid."' AND element = '".$element->id."' LIMIT 1");
                $xcount ++;
            }
        }
    }
}
?>
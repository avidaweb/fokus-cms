<?php
if($user->r('str', 'ele') && $index == 'n122')
{
    $k = $fksdb->fetch("SELECT id, element, sort FROM ".SQLPRE."elements WHERE id = '".$v->id."' LIMIT 1");
    
    if($v->task == 'new')
    {
        $sort = 999999;
        $kat = 0;

        if(!$v->type)
        {
            $kat = 0;
            $sort = 999999;
        }
        elseif($v->type == 'sibling')
        {
            $kat = $k->element;
            $sort = $k->sort + 1;
        } 
        elseif($v->type == 'child')
        {
            $kat = $v->id;
            $sort = 999999;
        } 

        $parent = new stdClass();
        $parent->id = 0;
        $parent->templatedatei = '';
        $parent->m_templatedatei = '';

        if($kat)
            $parent = $fksdb->fetch("SELECT id, templatedatei, m_templatedatei FROM ".SQLPRE."elements WHERE id = '".$kat."' LIMIT 1");
            
        $templatedatei = $parent->templatedatei;
        if($rechte['str']['template'])
        {
            $tslug = $base->slug($base->getActiveTemplateConfig('name'));
            $templatedatei = $rechte['str']['tda'][$tslug];    
        }
        
        $fksdb->insert("elements", array(
        	"struktur" => $base->getStructureID(),
        	"element" => $parent->id,
        	"autor" => $user->getID(),
        	"templatedatei" => $templatedatei,
        	"m_templatedatei" => $parent->m_templatedatei,
        	"sort" => $sort
        ));
        echo $fksdb->getInsertedID();
        
        if($user->getIndiv()->struktur_status)
            $fupdt = $fksdb->query("UPDATE ".SQLPRE."elements SET frei = '1' WHERE id = '".$fksdb->getInsertedID()."' LIMIT 1");
        
        // Nochmal durchshaken
        $kcount = 0;
        $kq = $fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE element = '".$kat."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' ORDER BY sort, id DESC");
        while($kx = $fksdb->fetch($kq))
        {
             $krr = $fksdb->query("UPDATE ".SQLPRE."elements SET sort = '".$kcount."' WHERE id = '".$kx->id."' LIMIT 1");
             $kcount ++;
        }
    }
    elseif($v->task == 'remove' && $k->id)
    {
        $kq = $fksdb->query("UPDATE ".SQLPRE."elements SET element = '".$k->element."' WHERE element = '".$k->id."' AND klasse = ''");
        $kqd = $fksdb->query("DELETE FROM ".SQLPRE."elements WHERE element = '".$k->id."' AND klasse != ''");
        $del = $fksdb->query("DELETE FROM ".SQLPRE."elements WHERE id = '".$k->id."' LIMIT 1");
        
        if($k->element)
            echo $k->element;
    }
    elseif($v->task == 'move_higher' && $k->id && $k->element != 0)
    {
        $kparent = $fksdb->fetch("SELECT element FROM ".SQLPRE."elements WHERE id = '".$k->element."' LIMIT 1");
        $kq = $fksdb->query("UPDATE ".SQLPRE."elements SET element = '".$kparent->element."' WHERE id = '".$k->id."' LIMIT 1");
        echo $k->id;
    }
    elseif($v->task == 'move_another' && $k->id && $v->id != $v->to && $v->to != 0)
    {
        $t = $fksdb->fetch("SELECT * FROM ".SQLPRE."elements WHERE id = '".$v->to."' LIMIT 1");
        if(!$t)
            exit('wrong to');
        
        function checkRevers($base, $fksdb, $id, $to, $kreal)
        {
            $kq = $fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE element = '".$id."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0'");
            while($kx = $fksdb->fetch($kq))
            {
                if($kx->id == $to)
                {
                    $kq = $fksdb->query("UPDATE ".SQLPRE."elements SET element = '".$kreal->element."' WHERE element = '".$kreal->id."'");
                    break;
                }
                else
                {   
                    checkRevers($base, $fksdb, $kx->id, $to, $kreal);
                }
            }
        }
        checkRevers($base, $fksdb, $k->id, $t->id, $k);
        
        $kqr = $fksdb->query("UPDATE ".SQLPRE."elements SET element = '".$t->id."' WHERE id = '".$k->id."' AND struktur = '".$base->getStructureID()."' LIMIT 1"); 
        echo $k->id;
    }
    elseif($v->task == 'sort')
    {
        $sort = explode('|', $v->nsort);
        $xo = 0;
        
        foreach($sort as $s)
        {
            $kqr = $fksdb->query("UPDATE ".SQLPRE."elements SET sort = '".$xo."' WHERE id = '".$s."' AND struktur = '".$base->getStructureID()."' AND element = '".$v->id."' LIMIT 1"); 
            $xo ++;
        }
    }
}
?>
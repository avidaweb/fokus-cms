<?php
if($user->r('str', 'menue') && $index == 'n173')
{
    $k = $fksdb->fetch("SELECT * FROM ".SQLPRE."menus WHERE id = '".$v->id."' AND menue = '".$v->menue."' AND struktur = '".$base->getStructureID()."' LIMIT 1");
    $menue = $base->getActiveTemplateConfig('menus', $v->menue);
    if(!is_array($menue))
        exit();
    
    if($v->task == 'new')
    {
        if(!$v->type)
        {
            $kat = 0;
            $sort = 999999;
        }
        elseif($v->type == 'sibling')
        {
            $kat = $k->mid;
            $sort = $k->sort + 1;
        } 
        elseif($v->type == 'child')
        {
            $kat = $v->id;
            $sort = 999999;
        } 
        
        $fksdb->insert("menus", array(
        	"struktur" => $base->getStructureID(),
        	"mid" => $kat,
        	"sort" => $sort,
        	"menue" => $v->menue
        ));
        echo $fksdb->getInsertedID();
        
        // Nochmal durchshaken
        $kcount = 0;
        $kq = $fksdb->query("SELECT id FROM ".SQLPRE."menus WHERE mid = '".$kat."' AND struktur = '".$base->getStructureID()."' AND menue = '".$v->menue."' ORDER BY sort, id DESC");
        while($kx = $fksdb->fetch($kq))
        {
             $krr = $fksdb->query("UPDATE ".SQLPRE."menus SET sort = '".$kcount."' WHERE id = '".$kx->id."' LIMIT 1");
             $kcount ++;
        }
    }
    elseif($v->task == 'remove' && $k->id)
    {
        $kq = $fksdb->query("UPDATE ".SQLPRE."menus SET mid = '".$k->mid."' WHERE mid = '".$k->id."'");
        $del = $fksdb->query("DELETE FROM ".SQLPRE."menus WHERE id = '".$k->id."' AND menue = '".$v->menue."' LIMIT 1");
        
        if($k->mid)
            echo $k->mid;
    }
    elseif($v->task == 'move_higher' && $k->id && $k->mid != 0)
    {
        $kparent = $fksdb->fetch("SELECT mid FROM ".SQLPRE."menus WHERE id = '".$k->mid."' LIMIT 1");
        $kq = $fksdb->query("UPDATE ".SQLPRE."menus SET mid = '".$kparent->mid."' WHERE id = '".$k->id."' LIMIT 1");
        echo $k->id;
    }
    elseif($v->task == 'move_another' && $k->id && $v->id != $v->to && $v->to != 0)
    {
        $t = $fksdb->fetch("SELECT * FROM ".SQLPRE."menus WHERE id = '".$v->to."' AND menue = '".$v->menue."' LIMIT 1");
        if(!$t)
            exit('wrong to');
        
        function checkRevers($base, $fksdb, $id, $to, $kreal)
        {
            $kq = $fksdb->query("SELECT id FROM ".SQLPRE."menus WHERE mid = '".$id."' AND struktur = '".$base->getStructureID()."' AND menue = '".$v->menue."'");
            while($kx = $fksdb->fetch($kq))
            {
                if($kx->id == $to)
                {
                    $kq = $fksdb->query("UPDATE ".SQLPRE."menus SET mid = '".$kreal->mid."' WHERE mid = '".$kreal->id."' AND menue = '".$v->menue."'");
                    break;
                }
                else
                {   
                    checkRevers($base, $fksdb, $kx->id, $to, $kreal);
                }
            }
        }
        checkRevers($base, $fksdb, $k->id, $t->id, $k);
        
        $kqr = $fksdb->query("UPDATE ".SQLPRE."menus SET mid = '".$t->id."' WHERE id = '".$k->id."' AND struktur = '".$base->getStructureID()."' LIMIT 1"); 
        echo $k->id;
    }
    elseif($v->task == 'sort')
    {
        $sort = explode('|', $v->nsort);
        $xo = 0;
        
        foreach($sort as $s)
        {
            $kqr = $fksdb->query("UPDATE ".SQLPRE."menus SET sort = '".$xo."' WHERE id = '".$s."' AND struktur = '".$base->getStructureID()."' AND mid = '".$v->id."' LIMIT 1"); 
            $xo ++;
        }
    }
}
?>
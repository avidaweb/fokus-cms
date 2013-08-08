<?php
if($index != 'n192')
    exit();
    
if(!$user->r('str', 'kat'))
    exit($user->noRights());
    
$va = $base->vars('POST');
$v = (object)$va;

$k = $fksdb->fetch("SELECT id, kat FROM ".SQLPRE."categories WHERE id = '".$v->id."' LIMIT 1");

if($v->task == 'new')
{
    if(!$v->type)
    {
        $kat = 0;
        $sort = 999999;
    }
    elseif($v->type == 'sibling')
    {
        $sib = $fksdb->fetch("SELECT * FROM ".SQLPRE."categories WHERE id = '".$v->id."' LIMIT 1");
        $kat = $sib->kat;
        $sort = $sib->sort + 1;
    } 
    elseif($v->type == 'child')
    {
        $kat = $v->id;
        $sort = 999999;
    } 
    
    $fksdb->insert("categories", array(
    	"name" => $trans->__('Neue Kategorie'),
    	"sort" => $sort,
    	"timestamp" => $base->getTime(),
    	"kat" => $kat
    ));
    echo $fksdb->getInsertedID();
    
    // Nochmal durchshaken
    $kcount = 0;
    $kq = $fksdb->query("SELECT id FROM ".SQLPRE."categories WHERE kat = '".$kat."' ORDER BY sort, id DESC");
    while($kx = $fksdb->fetch($kq))
    {
         $krr = $fksdb->query("UPDATE ".SQLPRE."categories SET sort = '".$kcount."' WHERE id = '".$kx->id."' LIMIT 1");
         $kcount ++;
    }
}
elseif($v->task == 'remove' && $k->id)
{
    $kq = $fksdb->query("UPDATE ".SQLPRE."categories SET kat = '".$k->kat."' WHERE kat = '".$k->id."'");
    $del = $fksdb->query("DELETE FROM ".SQLPRE."categories WHERE id = '".$k->id."' LIMIT 1");
    
    if($k->kat)
        echo $k->kat;
}
elseif($v->task == 'rename' && $k->id)
{
    $kq = $fksdb->query("UPDATE ".SQLPRE."categories SET name = '".$v->val."' WHERE id = '".$k->id."' LIMIT 1");
}
elseif($v->task == 'move_higher' && $k->id && $k->kat != 0)
{
    $kparent = $fksdb->fetch("SELECT kat FROM ".SQLPRE."categories WHERE id = '".$k->kat."' LIMIT 1");
    $kq = $fksdb->query("UPDATE ".SQLPRE."categories SET kat = '".$kparent->kat."' WHERE id = '".$k->id."' LIMIT 1");
    echo $k->id;
}
elseif($v->task == 'move_another' && $k->id && $v->id != $v->to && $v->to != 0)
{
    $t = $fksdb->fetch("SELECT * FROM ".SQLPRE."categories WHERE id = '".$v->to."' LIMIT 1");
    if(!$t)
        exit('wrong to');
    
    function checkRevers($base, $fksdb, $id, $to, $kreal)
    {
        $kq = $fksdb->query("SELECT id FROM ".SQLPRE."categories WHERE kat = '".$id."'");
        while($kx = $fksdb->fetch($kq))
        {
            if($kx->id == $to)
            {
                $kq = $fksdb->query("UPDATE ".SQLPRE."categories SET kat = '".$kreal->kat."' WHERE kat = '".$kreal->id."'");
                break;
            }
            else
            {   
                checkRevers($base, $fksdb, $kx->id, $to, $kreal);
            }
        }
    }
    checkRevers($base, $fksdb, $k->id, $t->id, $k);
    
    $kqr = $fksdb->query("UPDATE ".SQLPRE."categories SET kat = '".$t->id."' WHERE id = '".$k->id."' LIMIT 1"); 
    echo $k->id;
}
elseif($v->task == 'sort')
{
    $sort = explode('|', $v->nsort);
    $xo = 0;
    
    foreach($sort as $s)
    {
        $kqr = $fksdb->query("UPDATE ".SQLPRE."categories SET sort = '".$xo."' WHERE id = '".$s."' AND kat = '".$v->id."' LIMIT 1"); 
        $xo ++;
    }
}
?>
<?php
if($user->r('per', 'rollen') && $index == 'n550F')
{
    $r = $fksdb->fetch("SELECT fehler FROM ".SQLPRE."roles WHERE id = '".$rel."' LIMIT 1");
    
    if($rel == 1)
        exit();
    
    $fehler = $base->fixedUnserialize($r->fehler);
    if(!is_array($fehler)) $fehler = array();
    
    $new = $fksdb->save($_POST['newe']);
    if($new)
    {
        $fehler[] = $new;   
        $updt = $fksdb->query("UPDATE ".SQLPRE."roles SET fehler = '".serialize($fehler)."' WHERE id = '".$rel."' LIMIT 1");     
    }
    
    $del = $fksdb->save($_POST['del']);
    if($del)
    {
        foreach($fehler as $k => $f)
        {  
            if($f == $del)
                unset($fehler[$k]);
        }
        $updt = $fksdb->query("UPDATE ".SQLPRE."roles SET fehler = '".serialize($fehler)."' WHERE id = '".$rel."' LIMIT 1");     
    }
    
    foreach($fehler as $f)
    {
        $dok = $fksdb->fetch("SELECT id, titel FROM ".SQLPRE."documents WHERE id = '".$f."' AND papierkorb = '0' LIMIT 1");
        if(!$dok)
            continue;
        
        echo '
        <div class="struk_dok" id="Rsd_'.$dok->id.'"> 
            <span>'.$dok->titel.'</span>
            <div class="options">
                <div class="add">
                    <a class="doc_delete" rel="'.$dok->id.'">'.$trans->__('Dokument entfernen').'</a>
                </div>
            </div>
        </div>';
    }

    echo '
    <button class="bl">'.$trans->__('Dokument einfügen').'</button>';
} 
?>
<?php
if($index == 's130' && $user->r('suc', 'zuve'))
{
    $time = $fksdb->save($_GET['time']);
    $last = $fksdb->fetch("SELECT id FROM ".SQLPRE."recent_items WHERE benutzer = '".$user->getID()."' ORDER BY timestamp DESC LIMIT 1"); 
    
    if($last->id != $time || !$time)
    {
        $len = 20;
        
        $all = $fksdb->query("SELECT * FROM ".SQLPRE."recent_items WHERE papierkorb = '0' AND benutzer = '".$user->getID()."' ORDER BY timestamp DESC LIMIT 4"); 
        while($lu = $fksdb->fetch($all))
        {
            echo '<li class="last" data-id="'.$lu->id.'">';
            
            if($lu->type == 'dokument')
            {
                $dok = $fksdb->fetch("SELECT titel, id FROM ".SQLPRE."documents WHERE id = '".$lu->aid."' LIMIT 1");
                $titel = Strings::cut($dok->titel, $len);
                $pid = 'D'.str_pad($i->aid, 5 ,'0', STR_PAD_LEFT);
                echo '<a class="inc_documents" id="n250" rel="'.$dok->id.'" title="'.$trans->__('Dokument:').' '.$dok->titel.'">'.(!$titel?$pid:$titel).'</a>';
            }
            elseif($lu->type == 'bild')
            {
                $bild = $fksdb->fetch("SELECT titel, kat, id FROM ".SQLPRE."files WHERE id = '".$lu->aid."' LIMIT 1");
                $titel = Strings::cut($bild->titel, $len);
                $pid = 'B'.str_pad($i->aid, 5 ,'0', STR_PAD_LEFT);
                echo '<a class="inc_files" id="n400" rel="'.$bild->kat.'_'.$bild->id.'" title="'.$trans->__('Datei:').' '.$bild->titel.'">'.(!$titel?$pid:$titel).'</a>';
            }  
            elseif($lu->type == 'element')
            {
                $e = $fksdb->fetch("SELECT titel, id FROM ".SQLPRE."elements WHERE id = '".$lu->aid."' LIMIT 1"); 
                $titel = Strings::cut($e->titel, $len);
                $pid = 'E'.str_pad($i->aid, 5 ,'0', STR_PAD_LEFT);
                echo '<a class="selement" data-id="'.$e->id.'" title="'.$trans->__('Strukturelement:').' '.$e->titel.'">'.(!$titel?$pid:$titel).'</a>';
            }
            elseif($lu->type == 'firma')
            {
                $e = $fksdb->fetch("SELECT name, id FROM ".SQLPRE."companies WHERE id = '".$lu->aid."' LIMIT 1"); 
                $titel = Strings::cut($e->name, $len);
                $pid = 'F'.str_pad($i->aid, 5 ,'0', STR_PAD_LEFT);
                echo '<a class="inc_users" id="n570" rel="'.$e->id.'" title="'.$trans->__('Firma:').' '.$e->name.'">'.(!$titel?$pid:$titel).'</a>';
            }
            elseif($lu->type == 'personen')
            {
                $e = $fksdb->fetch("SELECT id, type, vorname, nachname FROM ".SQLPRE."users WHERE id = '".$lu->aid."' LIMIT 1"); 
                $titel = Strings::cut($e->vorname.' '.$e->nachname, $len);
                $pid = ($e->type==1?'K':'M').str_pad($i->aid, 5 ,'0', STR_PAD_LEFT);
                echo '<a class="inc_users" id="n53'.($e->type==1?'0':'5').'" rel="'.$e->id.'" title="'.$trans->__('Person:').' '.$e->vorname.' '.$e->nachname.'">'.(!$titel?$pid:$titel).'</a>';
            }
            elseif($lu->type == 'rolle')
            {
                $e = $fksdb->fetch("SELECT id, titel FROM ".SQLPRE."roles WHERE id = '".$lu->aid."' LIMIT 1"); 
                $titel = Strings::cut($e->titel, $len);
                $pid = 'R'.str_pad($i->aid, 5 ,'0', STR_PAD_LEFT);
                echo '<a class="inc_users" id="n550" rel="'.$e->id.'" title="'.$trans->__('Rolle:').' '.$e->titel.'">'.(!$titel?$pid:$titel).'</a>';
            }
            elseif($lu->type == 'struktur')
            {
                $e = $fksdb->fetch("SELECT id, titel FROM ".SQLPRE."structures WHERE id = '".$lu->aid."' LIMIT 1"); 
                $titel = Strings::cut($e->titel, $len);
                $pid = 'S'.str_pad($i->aid, 5 ,'0', STR_PAD_LEFT);
                echo '<a id="n100" class="inc_structure" rel="100" title="'.$trans->__('Struktur:').' '.$e->titel.'">'.(!$titel?$pid:$titel).'</a>';
            }
            elseif($lu->type == 'zsb')
            {
                $e = $fksdb->fetch("SELECT id, name FROM ".SQLPRE."responsibilities WHERE id = '".$lu->aid."' LIMIT 1"); 
                $titel = Strings::cut($e->name, $len);
                $pid = 'Z'.str_pad($i->aid, 5 ,'0', STR_PAD_LEFT);
                echo '<a id="n280" class="inc_documents" title="'.$trans->__('Zuständigkeit:').' '.$e->name.'">'.(!$titel?$pid:$titel).'</a>';
            }
            
            echo '</li>';
        }
    }
}
?>
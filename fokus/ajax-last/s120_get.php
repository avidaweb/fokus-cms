<?php
if($index == 's120_get' && $user->r('suc'))
{  
    $echte_spalten = $fksdb->save($_POST['realspalten'], 1);
    $akt_limit = $fksdb->save($_REQUEST['limit']);
    $real_count = 0;
    $ia = array();
        
    $papierkorb = $fksdb->save($_REQUEST['papierkorb']);
    $suche = $fksdb->save($_REQUEST['suche']);
    
    if($papierkorb)
        $all = $fksdb->query("SELECT * FROM ".SQLPRE."recent_items WHERE papierkorb = '1' ORDER BY timestamp DESC");  
    elseif(!$suche)
        $all = $fksdb->query("SELECT * FROM ".SQLPRE."recent_items WHERE papierkorb = '0' AND benutzer = '".$user->getID()."' ORDER BY timestamp DESC"); 
       
    if(!$suche)
    {
        while($xx = $fksdb->fetch($all))
        {
            $ia[] = $xx;
        }
    }
    else
    {
        $q = $fksdb->save($_REQUEST['q']);
        if(!$q)
        {
            echo '
        	<tr>
        		<td colspan="'.$echte_spalten.'" class="calibri nothing_found">'.$trans->__('Suchbegriff eingeben').'</td>
        	</tr>';
            exit();
         }
        
        $qA = Strings::explodeCheck(' ', $q); 
        for($x = 0; $x < count($qA); $x++)
        {
            $qS_1 .= ($x?" AND":"")." (titel LIKE '%".$qA[$x]."%' OR id LIKE '".$qA[$x]."') ";
            $qS_2 .= ($x?" AND":"")." (titel LIKE '%".$qA[$x]."%' OR beschr LIKE '%".$qA[$x]."%' OR id LIKE '".$qA[$x]."') ";
            $qS_3 .= ($x?" AND":"")." (titel LIKE '%".$qA[$x]."%'".(!is_numeric($qA[$x])?" OR sprachen LIKE '%".$qA[$x]."%'":"")." OR id LIKE '".$qA[$x]."') ";
            $qS_4 .= ($x?" AND":"")." (name LIKE '%".$qA[$x]."%' OR id LIKE '".$qA[$x]."') ";
            $qS_5 .= ($x?" AND":"")." (vorname LIKE '%".$qA[$x]."%' OR nachname LIKE '%".$qA[$x]."%' OR email LIKE '%".$qA[$x]."%' OR id LIKE '".$qA[$x]."') ";
        }
        $sql_1 = ($q?" AND (".$qS_1.") ":""); 
        $sql_2 = ($q?" AND (".$qS_2.") ":""); 
        $sql_3 = ($q?" AND (".$qS_3.") ":""); 
        $sql_4 = ($q?" AND (".$qS_4.") ":""); 
        $sql_5 = ($q?" AND (".$qS_5.") ":""); 
        
        $gloscount = 0;
        
        $all = $fksdb->query("SELECT id FROM ".SQLPRE."documents WHERE papierkorb = '0' ".$sql_1." ORDER BY id"); 
        while($xx = $fksdb->fetch($all))
        {
            $gloscount ++;
            $uid = $xx->id * 100000 + $gloscount;
            $xx->type = 'dokument';
            $xx->aid = $xx->id;
            $ia[$uid] = $xx;
        }
        
        $all = $fksdb->query("SELECT id FROM ".SQLPRE."files WHERE papierkorb = '0' ".$sql_2." ORDER BY id"); 
        while($xx = $fksdb->fetch($all))
        {
            $gloscount ++;
            $uid = $xx->id * 100000 + $gloscount;
            $xx->type = 'bild';
            $xx->aid = $xx->id;
            $ia[$uid] = $xx;
        }
        
        $all = $fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE papierkorb = '0' ".$sql_3." AND struktur = '".$base->getStructureID()."' ORDER BY id"); 
        while($xx = $fksdb->fetch($all))
        {
            $gloscount ++;
            $uid = $xx->id * 100000 + $gloscount;
            $xx->type = 'element';
            $xx->aid = $xx->id;
            $ia[$uid] = $xx;
        }
        
        $all = $fksdb->query("SELECT id FROM ".SQLPRE."companies WHERE papierkorb = '0' ".$sql_4." ORDER BY id"); 
        while($xx = $fksdb->fetch($all))
        {
            $gloscount ++;
            $uid = $xx->id * 100000 + $gloscount;
            $xx->type = 'firma';
            $xx->aid = $xx->id;
            $ia[$uid] = $xx;
        }
        
        $all = $fksdb->query("SELECT id FROM ".SQLPRE."users WHERE papierkorb = '0' ".$sql_5." ORDER BY id"); 
        while($xx = $fksdb->fetch($all))
        {
            $gloscount ++;
            $uid = $xx->id * 100000 + $gloscount;
            $xx->type = 'personen';
            $xx->aid = $xx->id;
            $ia[$uid] = $xx;
        }
        
        $all = $fksdb->query("SELECT id FROM ".SQLPRE."roles WHERE papierkorb = '0' ".$sql_2." ORDER BY id"); 
        while($xx = $fksdb->fetch($all))
        {
            $gloscount ++;
            $uid = $xx->id * 100000 + $gloscount;
            $xx->type = 'rolle';
            $xx->aid = $xx->id;
            $ia[$uid] = $xx;
        }
        
        $all = $fksdb->query("SELECT id FROM ".SQLPRE."structures WHERE papierkorb = '0' ".$sql_1." ORDER BY id"); 
        while($xx = $fksdb->fetch($all))
        {
            $gloscount ++;
            $uid = $xx->id * 100000 + $gloscount;
            $xx->type = 'struktur';
            $xx->aid = $xx->id;
            $ia[$uid] = $xx;
        }
        
        $all = $fksdb->query("SELECT id FROM ".SQLPRE."responsibilities WHERE papierkorb = '0' ".$sql_4." ORDER BY id"); 
        while($xx = $fksdb->fetch($all))
        {
            $gloscount ++;
            $uid = $xx->id * 100000 + $gloscount;
            $xx->type = 'zsb';
            $xx->aid = $xx->id;
            $ia[$uid] = $xx;
        }
        
        asort($ia);
    }
    
    foreach($ia as $i)
    {
        $itype = $i->type;
        $nid = '<small>#'.str_pad($i->aid, 5 ,'0', STR_PAD_LEFT).'</small>';
        
        echo '
        <tr class="'.($itype == 'bild'?'bild':'text').'">';
        
        if($itype == 'dokument')
        {
            if(!$user->r('dok', 'edit'))
                continue;
            
            $dok = $fksdb->fetch("SELECT titel, id FROM ".SQLPRE."documents WHERE id = '".$i->aid."' LIMIT 1");
            
            echo '
            <td class="type">'.$trans->__('Dokument').'</td>
            <td class="goto"><a class="inc_documents" id="n250" rel="'.$dok->id.'">'.$nid.' '.Strings::cut($dok->titel, 45).'</a></td>';
        }
        elseif($itype == 'bild')
        {
            if(!$user->r('dat'))
                continue;
            
            $stack = $fksdb->fetch("SELECT id, titel, kat, last_type, isdir FROM ".SQLPRE."files WHERE id = '".$i->aid."' LIMIT 1");
            
            if($stack->kat == 2)
            {
                $bild = '<img src="images/icons/32'.$base->getFileTypeThumbnail($stack->last_type).'.jpg" alt=" " height="32" />';
                $type = $trans->__('Datei');
            }
            else
            {
                $bild = '<img src="../inc/img.php?id='.$i->aid.'&w=32&h=32" alt=" " height="32" width="32" />';
                $type = $trans->__('Bild');
            }
            
            if($stack->isdir)
            {
                $bild = '<img src="images/folder.png" alt=" " height="32" />';
                $type = $trans->__('Ordner');
            } 
            
            echo '
            <td class="type">'.$type.'</td>
            <td class="goto">
                <a class="inc_files" id="n400" rel="'.$stack->kat.'_'.$stack->id.'">
                    '.$bild.'
                    <span>'.$nid.' '.Strings::cut($stack->titel, 35).'</span>
                </a>
            </td>';
        }
        elseif($itype == 'element')
        {
            if(!$user->r('str', 'ele'))
                continue;
            
            $e = $fksdb->fetch("SELECT titel, id FROM ".SQLPRE."elements WHERE id = '".$i->aid."' LIMIT 1"); 
            
            echo '
            <td class="type">'.$trans->__('Strukturelement').'</td>
            <td class="goto"><a class="selement" data-id="'.$e->id.'">'.$nid.' '.Strings::cut($e->titel, 45).'</a></td>';
        }
        elseif($itype == 'firma')
        {
            if(!$user->r('per', 'firma'))
                continue;
            
            $e = $fksdb->fetch("SELECT name, id FROM ".SQLPRE."companies WHERE id = '".$i->aid."' LIMIT 1"); 
            
            echo '
            <td class="type">'.$trans->__('Firma').'</td>
            <td class="goto"><a class="inc_users" id="n570" rel="'.$e->id.'">'.$nid.' '.Strings::cut($e->name, 45).'</a></td>';
        }
        elseif($itype == 'personen')
        {
            if(!$user->r('per', 'edit'))
                continue;
            
            $e = $fksdb->fetch("SELECT id, type, email, vorname, nachname FROM ".SQLPRE."users WHERE id = '".$i->aid."' LIMIT 1"); 
            $ausg = trim($e->vorname.' '.$e->nachname);
            if(!$ausg) $ausg = $e->email;
            
            echo '
            <td class="type">'.$trans->__('Person').'</td>
            <td class="goto"><a class="inc_users" id="n53'.($e->type==1?'0':'5').'" rel="'.$e->id.'">'.$nid.' '.Strings::cut($ausg, 45).'</a></td>';
        }
        elseif($itype == 'rolle')
        {
            if(!$user->r('per', 'rollen'))
                continue;
            
            $e = $fksdb->fetch("SELECT id, titel FROM ".SQLPRE."roles WHERE id = '".$i->aid."' LIMIT 1"); 
            
            echo '
            <td class="type">'.$trans->__('Rolle').'</td>
            <td class="goto"><a class="inc_users" id="n550" rel="'.$e->id.'">'.$nid.' '.Strings::cut($e->titel, 45).'</a></td>';
        }
        elseif($itype == 'struktur')
        {
            if(!$user->r('str', 'struk'))
                continue;
            
            $e = $fksdb->fetch("SELECT id, titel FROM ".SQLPRE."structures WHERE id = '".$i->aid."' LIMIT 1"); 
            
            echo '
            <td class="type">'.$trans->__('Struktur').'</td>
            <td class="goto"><a id="n100" class="inc_structure" rel="100">'.$nid.' '.Strings::cut($e->titel, 45).'</a></td>';
        }
        elseif($itype == 'zsb')
        {
            if(!$user->r('dok', 'ezsb'))
                continue;
            
            $e = $fksdb->fetch("SELECT id, name FROM ".SQLPRE."responsibilities WHERE id = '".$i->aid."' LIMIT 1"); 
            
            echo '
            <td class="type">'.$trans->__('Zuständigkeit').'</td>
            <td class="goto"><a id="n280" class="inc_documents">'.$nid.' '.Strings::cut($e->name, 45).'</a></td>';
        }
        else
        {
            continue;
        }
        
        echo '
            '.(!$suche?'
            <td class="time">
                '.($papierkorb?
                    $trans->__('gelöscht am %1, %2 Uhr', false, array(
                        date('d.m.Y', $i->timestamp),
                        date('H:i', $i->timestamp)
                    ))
                    :
                    $trans->__('geöffnet am %1, %2 Uhr', false, array(
                        date('d.m.Y', $i->timestamp),
                        date('H:i', $i->timestamp)
                    ))
                ).'
            </td>
            ':'').'
            '.($papierkorb?'
            <td class="reset">
                <a data-id="'.$itype.'_'.$i->aid.'">'.$trans->__('wiederherstellen').'</a>
            </td>
            ':'').'
        </tr>';    
        
        $real_count ++;
        
        if($real_count >= $akt_limit)
            break;
    }
    
    $insgesamt = count($ia);
    
    if(!$real_count)
    {
    	echo '
    	<tr>
    		<td colspan="'.$echte_spalten.'" class="calibri nothing_found">'.$trans->__('keine Elemente vorhanden').'</td>
    	</tr>';
    }
    elseif($real_count < $insgesamt)
    {
    	$verbleibend = $insgesamt - $real_count;
    	
    	echo '
    	<tr>
    		<td colspan="'.$echte_spalten.'" class="calibri more_results">
    			<a class="next">'.$trans->__('+ weitere Elemente anzeigen').' ('.($verbleibend < 15?$verbleibend:'15').')</a>
    			'.($verbleibend > 15?'<a class="all">'.$trans->__('+ alle Elemente anzeigen').' ('.($verbleibend).')</a>':'').'
    		</td>
    	</tr>';        
    }
}
?>
<?php
if($user->r('per', 'prolle') && $index == 'n531')
{
    $benutzer = $fksdb->save($_GET['benutzer']);
    
    $rA = $fksdb->query("SELECT id FROM ".SQLPRE."user_roles WHERE benutzer = '".$benutzer."'");
        
    $rQ = $fksdb->query("SELECT id, titel, beschr FROM ".SQLPRE."roles ORDER BY sort, id");
    while($r = $fksdb->fetch($rQ))
    { 
        $rC = $fksdb->query("SELECT id FROM ".SQLPRE."user_roles WHERE rolle = '".$r->id."' AND benutzer = '".$benutzer."'");
        if($fksdb->count($rC))
        {
            $rCa = $fksdb->fetch($rC);
            $cc++;
            
            echo '
            <tr'.($cc == $fksdb->count($rA)?' class="last"':'').'>
                <td><strong>'.$r->titel.'</strong></td>
                <td>'.$r->beschr.'</td>
                <td><a class="del" rel="'.$rCa->id.'">'.$trans->__('Rollenzuordnung entfernen').'</a></td> 
            </tr>';   
        }
    } 
    if(!$cc)
        echo '<tr class="last"><td>'.$trans->__('Noch keine Rolle zugeordnet').'</td></tr>';
}
?>
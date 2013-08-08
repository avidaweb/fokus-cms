<?php
if($user->r('per', 'rollen') && $index == 'n541')
{
    $sort = 0;
    
    $rQ = $fksdb->query("SELECT id, titel, beschr, sort FROM ".SQLPRE."roles WHERE papierkorb = '0' ORDER BY sort, id");
    while($r = $fksdb->fetch($rQ))
    {
        if($r->sort != $sort)
            $update = $fksdb->query("UPDATE ".SQLPRE."roles SET sort = '".$sort."' WHERE id = '".$r->id."' AND id != '1' LIMIT 1");
        $sort ++;
        
        if($r->id != 1 || $user->getRole() == 1)
        {
            $cc++;
            
            echo '
            <tr'.($r->id != 1?' class="csort"':'').' id="rol_'.$r->id.'">
                <td class="fir"><strong>'.$r->titel.'</strong></td>
                <td class="desc">'.$r->beschr.'</td>
                <td class="ezeilig">'.($r->id != 1?'<a class="inc_users" id="n550" rel="'.$r->id.'">'.$trans->__('bearbeiten').'</a>':'').'</td> 
                <td class="ezeilig">'.($r->id != 1?'<a class="del" rel="'.$r->id.'">'.$trans->__('löschen').'</a>':'').'</td> 
                <td class="schieber">'.($r->id != 1?'<img src="images/button_verschieben.png" alt="Schieber" />':'').'</td>
            </tr>';   
        }
    }
}
?>
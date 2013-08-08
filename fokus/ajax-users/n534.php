<?php
if($index == 'n534')
{
    if(!$user->r('per', 'prolle'))
        exit($user->noRights());
        
    $more = $fksdb->save($_POST['more']);
    
    echo '
    <h1>'.($more?$trans->__('Weitere Optionen.'):$trans->__('Rollenzuordnung.')).'</h1>
    
    <div class="box" id="rollenuebersicht">        
        <h2 class="calibri">'.$trans->__('Rolle zuordnen.').'</h2>
        <div class="rollen">
            <table id="rolle">';
            $rQ = $fksdb->query("SELECT id, titel, beschr FROM ".SQLPRE."roles WHERE papierkorb = '0' ORDER BY sort, id");
            while($r = $fksdb->fetch($rQ))
            {
                if($r->id != 1 || $user->getRole() == 1)
                {
                    $cc++;
                    
                    echo '
                    <tr'.($cc == $fksdb->count($rQ)?' class="last"':'').'>
                        <td><strong>'.$r->titel.'</strong></td>
                        <td>'.$r->beschr.'</td>
                        <td><a rel="'.$r->id.'">'.$trans->__('verwenden').'</a></td> 
                    </tr>';   
                }
            }
            echo '
            </table>
        </div>
    </div>';
}
?>
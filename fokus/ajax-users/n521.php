<?php
if($user->r('per', 'firma') && $index == 'n521')
{
    $q = $fksdb->save($_REQUEST['q']);
    $sortA = $fksdb->save($_REQUEST['sortA']);
    $sortB = $fksdb->save($_REQUEST['sortB']);
    $opt = explode('+', $fksdb->save($_REQUEST['opt']));
    $sql .= ($q?" AND (name LIKE '%".$q."%' OR branche LIKE '%".$q."%') ":""); 
    
    $zellennr = 0;
    echo '<tr id="headline">';
        for($x=1; $x<15; $x++)
        {
            if(in_array($x, $opt)) 
            { 
                echo '<td'.($zellennr == count($opt)-2?' class="last"':'').' id="'.$optionenTF[$x].'">'.$trans->__($optionenF[$x]).'</td>'; 
                $zellennr ++; 
            }
        }
    echo '</tr>'; 
    
    $ergebnis = $fksdb->query("SELECT * FROM ".SQLPRE."companies WHERE papierkorb = '0' ".$sql."".($sortA?"ORDER BY ".$sortA." ".$sortB:""));
    while($row = $fksdb->fetch($ergebnis))
    {
        $zellennr = 0;
        
        echo '<tr>';
            if(in_array('1', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'><a class="inc_users" id="n570" rel="'.$row->id.'">'.$row->name.'</a></td>'; 
                $zellennr ++; 
            }
            if(in_array('2', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->str.' '.$row->hn.'</td>'; 
                $zellennr ++; 
            }
            if(in_array('3', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->plz.'</td>'; 
                $zellennr ++; 
            }
            if(in_array('4', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->ort.'</td>'; 
                $zellennr ++; 
            }
            if(in_array('5', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->land.'</td>'; 
                $zellennr ++; 
            }
            if(in_array('6', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->tel.'</td>'; 
                $zellennr ++; 
            }
            if(in_array('7', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->fax.'</td>'; 
                $zellennr ++; 
            }
            if(in_array('8', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->email.'</td>'; 
                $zellennr ++; 
            }
            if(in_array('9', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->branche.'</td>'; 
                $zellennr ++; 
            }
            if(in_array('10', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->status.'</td>'; 
                $zellennr ++; 
            }
            if(in_array('11', $opt)) 
            { 
                echo '<td'.($zellennr == 0?' class="first"':($zellennr == count($opt)-2?' class="last"':'')).'>'.$row->tags.'</td>'; 
                $zellennr ++; 
            }
        echo '</tr>';
    }
}
?>
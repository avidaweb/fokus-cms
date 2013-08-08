<?php
if($user->r('per') && $index == 'n511')
{
    if(($rel == 1 && !$tkunde) || ($rel == 2 && !$tmitarbeiter))
        exit($user->noRights());
        
    $sortA = $fksdb->save($_REQUEST['sortA']);
    $sortB = $fksdb->save($_REQUEST['sortB']);
    $opt = Strings::explodeCheck('+', $fksdb->save($_REQUEST['opt']));
    $rollen = Strings::explodeCheck('+', $fksdb->save($_REQUEST['rollen'])); 
    $q = $fksdb->save($_REQUEST['q']);
    $akt_limit = $fksdb->save($_REQUEST['limit']);
    
    $qA = explode(' ', $q);
    for($x = 0; $x < count($qA); $x++)
        $qS .= ($x?" OR":"")." (id LIKE '".$qA[$x]."' OR nachname LIKE '%".$qA[$x]."%' OR vorname LIKE '%".$qA[$x]."%' OR ort LIKE '%".$qA[$x]."%' OR email LIKE '%".$qA[$x]."%') ";
    $sql .= ($q?" AND (".$qS.") ":"");
    
    $zellennr = 0;
    $real_count = 0;
    $gesamt_count = 0;
    
    $insgesamt = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."users WHERE ".($rel?"(type = '0' OR type = '".$rel."')":"id != '0'")." AND papierkorb = '0' ".$sql)); 
    
    echo '
    <tr id="headline">
        <th id="ppp_id" '.($sortA == 'id'?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="id"></th>';
        for($x=1; $x<16; $x++)
        {
            if(in_array($x, $opt)) 
            { 
                echo '
                <th id="ppp_'.$optionenT[$x].'" '.($sortA == $optionenT[$x]?' class="sort '.($sortB == 'DESC'?'desc':'asc').'"':'').' data-sort="'.$optionenT[$x].'">'.$trans->__($optionen[$x]).'</th>'; 
                $zellennr ++;
            }
        }
    echo '
    </tr>'; 
    
    $anzahl_spalten = $zellennr + 1;  
    
    $rollen_sql = "";
    if(is_array($rollen))
    {
        foreach($rollen as $ro)
            $rollen_sql .= ($rollen_sql?"OR ":"")." rolle = '".$ro."' ";
    }
    
    $oeffnen = $user->r('per', 'edit');
    
    $ergebnis = $fksdb->query("SELECT * FROM ".SQLPRE."users WHERE ".($rel?"(type = '0' OR type = '".$rel."')":"id != '0'")." AND papierkorb = '0' ".$sql."".($sortA?"ORDER BY ".$sortA." ".$sortB:"")); 
    while($row = $fksdb->fetch($ergebnis))
    {
        $gesamt_count ++;
        
        if($rollen_sql)
        {
            $has_rolle = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."user_roles WHERE benutzer = '".$row->id."' AND (".$rollen_sql.") LIMIT 1"));
            if(!$has_rolle)
                continue;
        }
        
        $real_count ++;
        
        $firma = $fksdb->fetch("SELECT name FROM ".SQLPRE."companies WHERE id = '".$row->firma."' LIMIT 1");
        
        echo '<tr class="bg_'.$base->countup().' entry">';
        echo '<td>'.($oeffnen?'<a class="inc_users" id="n53'.($row->type==1?'0':'5').'" rel="'.$row->id.'">':'').($rel==1?'K':'M').''.str_pad($row->id, 5 ,'0', STR_PAD_LEFT).($oeffnen?'</a>':'').'</td>'; 
        if(in_array('1', $opt)) { echo '<td>'.($oeffnen?'<a class="inc_users" id="n53'.($row->type==1?'0':'5').'" rel="'.$row->id.'">':'').$row->vorname.($oeffnen?'</a>':'').'</td>'; }
        if(in_array('2', $opt)) { echo '<td>'.($oeffnen?'<a class="inc_users" id="n53'.($row->type==1?'0':'5').'" rel="'.$row->id.'">':'').$row->nachname.($oeffnen?'</a>':'').'</td>'; }
        if(in_array('3', $opt)) { echo '<td>'.$row->str.' '.$row->hn.'</td>'; }
        if(in_array('4', $opt)) { echo '<td>'.$row->plz.'</td>'; }
        if(in_array('5', $opt)) { echo '<td>'.$row->ort.'</td>'; }
        if(in_array('6', $opt)) { echo '<td>'.$row->land.'</td>'; }
        if(in_array('7', $opt)) { echo '<td>'.$firma->name.'</td>'; }
        if(in_array('8', $opt)) { echo '<td>'.$row->position.'</td>'; }
        if(in_array('9', $opt)) { echo '<td>'.$row->tel_g.'</td>'; }
        if(in_array('10', $opt)) { echo '<td>'.$row->tel_p.'</td>'; }
        if(in_array('11', $opt)) { echo '<td>'.$row->mobil.'</td>'; }
        if(in_array('12', $opt)) { echo '<td>'.$row->fax.'</td>'; }
        if(in_array('13', $opt)) { echo '<td>'.$row->email.'</td>'; }
        if(in_array('14', $opt)) { echo '<td>'.$row->tags.'</td>'; }
        if(in_array('15', $opt)) { echo '<td>'.$base->is_online($row->online).'</td>'; }
        echo '</tr>';
        
        if($real_count >= $akt_limit)
            break;
    }
    
    if(!$real_count)
    {
        echo '
        <tr>
            <td colspan="'.$anzahl_spalten.'" class="calibri nothing_found">'.$trans->__('keine Personen vorhanden.').'</td>
        </tr>';
    }
    elseif($gesamt_count < $insgesamt)
    {
        $verbleibend = $insgesamt - $gesamt_count;
        
        echo '
        <tr>
            <td colspan="'.$anzahl_spalten.'" class="calibri more_results">
                <a class="next">'.$trans->__('+ weitere Personen anzeigen').' ('.($verbleibend < 15?$verbleibend:'15').')</a>
                '.($verbleibend > 15?'<a class="all">'.$trans->__('+ alle Personen anzeigen').' ('.($verbleibend).')</a>':'').'
            </td>
        </tr>';        
    }
}
?>
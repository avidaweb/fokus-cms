<?php
if($index == 'n403' && $user->r('dat'))
{
    $dt = explode('|', $fksdb->save($_GET['dt']));
    for($x=0; $x<count($dt)-1; $x++) $dts .= ($x==0?"AND (":" OR ")."".($dt[$x] == "*"?"last_type != ''":"last_type = '".$dt[$x]."'")."".($x==count($dt)-2?")":"");
    
    $q = $fksdb->save($_GET['q']);
    $qs = ($q?"AND titel LIKE '%".$q."%'":"");
    
    $sort1 = $fksdb->save($_GET['sort1']);
    $sort2 = $fksdb->save($_GET['sort2']);
    
    $dir = $fksdb->save($_GET['dir']);
    
    $stack = $fksdb->save($_GET['stack']);
    $stacksql = ($stack?"AND id = '".$stack."'":"");
    
    $ergebnis = $fksdb->query("SELECT id, titel, isdir, dir, roles FROM ".SQLPRE."files WHERE dir = '".$dir."' AND papierkorb = '0' AND kat = '".$kat."' ".$dts." ".$stacksql." ".$qs." ".($sort1?" ORDER BY isdir DESC, ".$sort1." ".$sort2:""));
    while($row = $fksdb->fetch($ergebnis))
    {
        if(!$row->isdir) // Ist eine Datei
        {
            $d = $fksdb->fetch("SELECT id, type, timestamp FROM ".SQLPRE."file_versions WHERE stack = '".$row->id."' ORDER BY timestamp DESC LIMIT 1");
            $d2 = $fksdb->query("SELECT id FROM ".SQLPRE."file_versions WHERE stack = '".$row->id."'");
            $d3 = $fksdb->query("SELECT id FROM ".SQLPRE."file_versions WHERE stack = '".$row->id."' AND timestamp <= '".$d->timestamp."'");
            
            $roles = $base->db_to_array($row->roles);
                
            echo '
            <div class="one one_'.$base->countup().'" id="datei_'.$row->id.'">
                <div class="thumb"><img src="images/icons/32'.$base->getFileTypeThumbnail($d->type).'.jpg" alt="'.$d->type.'" width="32" /></div>
                <div class="info">
                    <a>'.$row->titel.'</a>
                    
                    <p>
                        '.(!$stack?
                            $trans->__('Neueste Version vom %1', false, array(date('d.m.Y', $d->timestamp))).' / '
                            .($fksdb->count($d2) == 1?
                                $trans->__('1 Version')
                                :
                                $trans->__('%1 Versionen', false, array($fksdb->count($d2)))
                            )
                            :
                            $trans->__('Version vom %1 / %2. Version', false, array(
                                date('d.m.Y', $d->timestamp),
                                $fksdb->count($d3)
                            ))
                        ).'
                        /
                        '.(count($roles)?
                            '<span class="va_no">
                                '.$trans->__('Nur für bestimmte Rollen freigegeben').'
                                ('.count($roles).')
                            </span>'
                            :
                            '<span class="va_yes">
                                '.$trans->__('Öffentlich freigegeben').'
                            </span>'
                        ).'
                    </p>
                </div>
            </div>';
        }
        else
        {
            echo '
            <div class="one one_'.$base->countup().'" id="datei_'.$row->id.'">
                <div class="thumb"><img src="images/icons/32'.$base->getFileTypeThumbnail($d->type).'.jpg" alt="'.$d->type.'" width="32" /></div>
                <div class="info">
                    '.$trans->__('Ordner:').' 
                    '.$row->titel.'
                </div>
            </div>';
        }
    }  
    
    if(!$fksdb->count($ergebnis))
        echo '<div class="calibri not_found">'.$trans->__('Keine Dateien gefunden').'</div>';  
}
?>
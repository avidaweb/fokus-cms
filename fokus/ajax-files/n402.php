<?php
if($index == 'n402' && $user->r('dat'))
{
    $dir = $fksdb->save($_GET['dir']);
    
    echo '
    <div id="dir_struktur">
        <a rel="0">'.$trans->__('Hauptverzeichnis').'</a>';
        
        function ordner_struktur($fksdb, $parent, $output)
        {         
            if($parent != 0)
            {
                $ergebnis = $fksdb->query("SELECT titel, id, dir FROM ".SQLPRE."files WHERE isdir = '1' AND id = '".$parent."' LIMIT 1");
                while($row = $fksdb->fetch($ergebnis))
                {
                    $output = ' &raquo; <a rel="'.$row->id.'">'.$row->titel.'</a> '.$output;
                    
                    if($row->dir)
                        $output = ordner_struktur($fksdb, $row->dir, $output);
                }
            }
            
            return $output;
        }
        echo ordner_struktur($fksdb, $dir, '');
        
        echo '
    </div>
    
    <div id="real_pics">';  
    
    $dt = explode('|', $fksdb->save($_GET['dt']));
    for($x=0; $x<count($dt)-1; $x++) $dts .= ($x==0?"AND (isdir = '1' OR ":" OR ")."last_type = '".$dt[$x]."'".($x==count($dt)-2?")":"");
    
    $ar = explode('|', $fksdb->save($_GET['ar']));
    $ars = (count($ar) > 1 && count($ar) < 3?($ar[0] == "h"?"AND last_ausrichtung > 1":"AND last_ausrichtung < 1"):"");
    
    $q = $fksdb->save($_GET['q']);
    $qs = ($q?"AND titel LIKE '%".$q."%'":"");
    
    $sort1 = $fksdb->save($_GET['sort1']);
    $sort2 = $fksdb->save($_GET['sort2']);
    
    $stack = $fksdb->save($_GET['stack'], 1);
    $stacksql = ($stack?"AND id = '".$stack."'":"");
    
    $laden = $fksdb->save($_GET['laden']);
    $alle = $fksdb->query("SELECT id FROM ".SQLPRE."files WHERE dir = '".$dir."' AND kat = '".$kat."' ".$dts." ".$stacksql." ".$ars." ".$qs."");
    $verbleibend = $fksdb->count($alle) - $laden;
    $verbleibend = ($verbleibend < 0?0:$verbleibend);
    
    $ergebnis = $fksdb->query("SELECT id, kat, dir, isdir, titel FROM ".SQLPRE."files WHERE dir = '".$dir."' AND papierkorb = '0' AND kat = '".$kat."' ".$dts." ".$stacksql." ".$ars." ".$qs." ".($sort1?" ORDER BY isdir DESC, ".$sort1." ".$sort2:"")." LIMIT ".$laden); 
    while($row = $fksdb->fetch($ergebnis))
    {
        if(!$row->isdir) // Ist eine Datei
        {
            $d = $fksdb->fetch("SELECT id, file, type, width, height FROM ".SQLPRE."file_versions WHERE stack = '".$row->id."' ORDER BY timestamp DESC LIMIT 1");
            
            $bild = DOMAIN.'/img/'.$row->id.'-200-0-'.$base->slug($row->titel).'.'.$d->type.'?version='.$d->id;
            $imagesize = (intval(ini_get('allow_url_fopen')) == 0?array():getimagesize($bild));
            
            $notfound = false;
            if(!file_exists(ROOT.'content/uploads/bilder/'.$d->file.'.'.$d->type) && !is_array($imagesize))
            {
                $notfound = true;
                $bild = DOMAIN.'/fokus/images/warning.png';
            }
                    
            if($d->width > 800)
            {
                $twidth = 800;
                $thumb_800 = DOMAIN.'/img/'.$row->id.'-800-0-'.$base->slug($row->titel).'.'.$d->type.'?version='.$d->id;
            }
            else
            {
                $twidth = $d->width;
                $thumb_800 = DOMAIN.'/img/'.$row->id.'-0-0-'.$base->slug($row->titel).'.'.$d->type.'?version='.$d->id;
            }
            
            echo '
            <div class="one'.($notfound?' notfound':'').'" id="bild_'.$row->id.'">
                <div class="thumb">
                    <img class="mainpic" src="'.$bild.'" alt="'.$row->titel.'" />
                    
                    <input type="hidden" class="dateibig" value="'.$thumb_800.'" />
                    <input type="hidden" class="dateiwidth" value="'.(46 + $twidth).'" />
                </div>
                <div class="titel">'.$row->titel.'</div>
            </div>';
        }
        else // Ist ein Ordner
        { 
            $kinder = $fksdb->count("SELECT id FROM ".SQLPRE."files WHERE dir = '".$row->id."' AND isdir = '1' AND kat = '".$kat."' LIMIT 3"); 
            
            echo '
            <div class="one ordner" id="bild_'.$row->id.'">
                <div class="thumb">
                    <img class="mainpic" src="images/folder'.$add_to_pic[$kinder].'.png" alt="'.$trans->__('Ordner').'" />
                    
                    <input type="hidden" class="isdir" value="'.$row->id.'" />
                </div>
                <div class="titel">'.$row->titel.'</div>
            </div>';
        } 
    }  
    
    if(!$fksdb->count($ergebnis))
        echo '<div class="calibri not_found">'.$trans->__('Keine Bilder gefunden').'</div>';
    
    echo '</div>';
    
    echo ($verbleibend > 0?'
    <div class="more">
        '.($verbleibend > 30?'<a class="moreA">'.$trans->__('Mehr Bilder anzeigen').' (+30)</a>':'').'
        <a class="moreB">'.$trans->__('Alle Bilder anzeigen').' (+'.$verbleibend.')</a>
    </div>':''); 
}
?>
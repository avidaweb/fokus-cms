<?php
if($index == 'link_elements')
{
    $q = $fksdb->save($_POST['q']);
    
    function elemente($fksdb, $base, $trans, $parent, $ebene, $q = '')
    {   
        $type = $fksdb->save($_POST['type']);
        $href = rawurldecode($fksdb->save($_POST['href']));
        
        if($type == 'i')
        {
            preg_match('~{s-(.*)}~Uis', $href, $intern); 
            preg_match('~{s-(.*)_(.*)}~Uis', $href, $dok_intern); 
        } 
        
        $ergebnis = $fksdb->query("SELECT titel, id, klasse, element FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' ".(!$q?"AND element = '".$parent."'":"AND (titel LIKE '%".$q."%')")." AND produkt = '' AND papierkorb = '0' ORDER BY sort");
        while($row = $fksdb->fetch($ergebnis))
        {
            if(!$row->klasse)
            {
                $precheck = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' AND element = '".$row->id."' AND papierkorb = '0' AND produkt = ''"));
                
                echo '
                <div class="elemente'.($ebene > 0?' nelemente':'').'"'.($type == 'i' && $intern[1] == $row->id && !$dok_intern[2]?' id="open_me"':'').'>
                    <div class="own">
                        <div class="kopp">
                            <input type="radio" name="int_link" id="fck_int_link_'.$row->id.'" data-element="'.$row->id.'" data-document="0" value="'.$row->id.'"'.($type == 'i' && $intern[1] == $row->id && !$dok_intern[2]?' checked="checked"':'').' />
                            <label for="fck_int_link_'.$row->id.'">'.$row->titel.'</label>
                            '.($precheck && !$q?'<span></span>':'').'
                        </div>
                    </div>';
                    
                    if(!$q)
                    {
                        $newebene = $ebene + 1;
                        elemente($fksdb, $base, $trans, $row->id, $newebene, $q);
                    }
                    
                echo '</div>';
            }
            else
            {
                $dQ = $fksdb->query("SELECT id, titel FROM ".SQLPRE."documents WHERE klasse = '".$row->klasse."' AND timestamp_freigegeben != '' AND papierkorb = '0' ORDER BY id DESC");
                while($d = $fksdb->fetch($dQ))
                {
                    echo '
                    <div class="elemente delement'.($ebene > 0?' nelemente':'').'"'.($type == 'i' && $dok_intern[1] == $row->element && $dok_intern[2] == $d->id?' id="open_me"':'').'>
                        <div class="own">
                            <div class="kopp">
                                <input type="radio" name="int_link" id="fck_int_link_'.$row->element.'_'.$d->id.'" data-element="'.$row->element.'" data-document="'.$d->id.'" value="'.$row->element.'_'.$d->id.'"'.($type == 'i' && $dok_intern[1] == $row->element && $dok_intern[2] == $d->id?' checked="checked"':'').' />
                                <label for="fck_int_link_'.$row->element.'_'.$d->id.'">'.$d->titel.'</label>
                            </div>
                        </div>
                    </div>';
                }
            }
        }
            
        if($q)
        {
            $dQ = $fksdb->query("SELECT id, titel, klasse FROM ".SQLPRE."documents WHERE titel LIKE '%".$q."%' AND timestamp_freigegeben != '' AND klasse != '' AND papierkorb = '0' ORDER BY id DESC");
            while($d = $fksdb->fetch($dQ))
            {
                $kele = $fksdb->fetch("SELECT id, element FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' AND klasse = '".$d->klasse."' AND produkt = '' AND papierkorb = '0' ORDER BY sort LIMIT 1");
                
                if(!$kele)
                    continue;
                
                echo '
                <div class="elemente delement">
                    <div class="own">
                        <div class="kopp">
                            <input type="radio" name="int_link" id="fck_int_link_'.$kele->element.'_'.$d->id.'" data-element="'.$kele->element.'" data-document="'.$d->id.'" value="'.$kele->element.'_'.$d->id.'"'.($type == 'i' && $intern[1] == $kele->element && $intern[2] == $d->id?' checked="checked"':'').' />
                            <label for="fck_int_link_'.$kele->element.'_'.$d->id.'">'.$d->titel.'</label>
                        </div>
                    </div>
                </div>';
            }
        }
    }
    
    echo elemente($fksdb, $base, $trans, 0, 0, $q);
}  
?>
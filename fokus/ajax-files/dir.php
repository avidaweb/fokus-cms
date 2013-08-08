<?php
if($index == 'dir' && $user->r('dat', 'dir'))
{
    $ordner = $fksdb->save($_REQUEST['ordner']);
    
    echo '
    <div class="box choue">
        '.$trans->__('Bitte wählen Sie einen Ordner.').'
    </div>
    <div class="box ordner">
                
        <div class="own o_null'.($ordner == 0?' aktiv':'').'">
            <p class="titel">
                <span><a rel="0">'.$trans->__('Hauptverzeichnis').'</a></span>
            </p>
            <p class="options">
                <a class="new" rel="0">'.$trans->__('Neuer Kind-Ordner').'</a>
            </p>
        </div>
        <div class="ebene_-1 o">
            <input type="hidden" value=".ebene_0" />';
            
            $dirs = $fksdb->rows("SELECT titel, id, dir FROM ".SQLPRE."files WHERE kat = '".$kat."' AND isdir = '1' ORDER BY sort", "", "dir");
    
            function directory_loop($dirs, $ordner, $trans, $parent, $ebene)
            {          
                $cdirs = $dirs[$parent];
                if(!is_array($cdirs))
                    return false;
                
                foreach($cdirs as $row)
                {
                    echo '
                    <div class="ebene_'.$ebene.' o" id="o_'.$row->id.'">
                        <input type="hidden" value=".ebene_'.($ebene + 1).'" />
                        
                        <div class="own o_'.$ebene.''.($ordner == $row->id?' aktiv':'').'">
                            <p class="titel">
                                <span>
                                    <a rel="'.$row->id.'">'.$row->titel.'</a>
                                    <input type="text" value="'.$row->titel.'" />
                                    <strong class="lcolor">'.$trans->__('Speichern').'</strong>
                                </span>
                            </p>
                            
                            <p class="options">
                                <a class="umb" rel="'.$row->id.'">'.$trans->__('Umbenennen').'</a>
                                <span> | </span>
                                <a class="del" rel="'.$row->id.'">'.$trans->__('Entfernen').'</a>
                                <span> | </span>
                                <a class="new" rel="'.$row->id.'">'.$trans->__('Neuer Kind-Ordner').'</a>
                            </p>
                        </div>';
                        
                        $newebene = $ebene + 1;
                        directory_loop($dirs, $ordner, $trans, $row->id, $newebene);
                        
                    echo '
                    </div>';
                }
            }
            
            directory_loop($dirs, $ordner, $trans, 0, 0);
        
        echo '
        </div>
    </div>
    
    <div class="box_save">
        <input type="button" value="'.$trans->__('weiter').'" class="bs2" />
    </div>';
        
}
?>
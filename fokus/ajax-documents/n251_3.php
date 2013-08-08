<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251')
    exit($user->noRights());

if(!isset($id))
    $id = 0;
    
echo '
<div class="movebox" id="dokumenteninhalt">                      
    <img src="images/moveboxH.png" alt="" class="schatten" />

    <div class="loadme">
        <img src="images/loading_grey.gif" alt="'. $trans->__('Bitte warten.. Inhalt wird geladen..') .'" class="ladebalken" />
    </div>

    <div class="moved">';
    
        if(!$doc->klasse && !$doc->produkt)
        {
            $get_me = array();
            
            $ergebnis2 = $fksdb->query("SELECT id FROM ".SQLPRE."columns WHERE dokument = '".intval($id)."' AND dversion = '".$dve->id."' ORDER BY sort");
            while($doc2 = $fksdb->fetch($ergebnis2))
                $get_me[] = $doc2;
                
            if($fksdb->count($ergebnis2) > 1)
            {
                echo '
                <div class="spaltenwahl calibri">
                    <div class="choose">';
                        foreach($get_me as $k => $sp)
                        {
                            $sptitle = $trans->__('Spalte %1', true, array($k + 1));
                            
                            echo '<a data-id="'.$sp->id.'" data-titel="'.$sptitle.'" title="'. $trans->__('%1 auswählen', false, array($sptitle)) .'"></a>';
                        }
                    echo '
                    </div>
                    <div class="sp_choosen">
                        <p>
                            <span></span>
                            '. $trans->__('ausgewählt') .'
                        </p>
                    </div>
                </div>';
            }
            
            foreach($get_me as $k => $sp)
            {
                echo '
                <div class="spalte spalte_'.$sp->id.'">
                    <input type="hidden" value="'.$sp->id.'" />
                </div>';
            }
        }
        else
        {
            echo '
            <input type="hidden" value="1" id="is_dklasse" />
            <div class="spalte">
                <input type="hidden" value="-50" />
            </div>';
        }
        
        echo '
        <input type="hidden" id="spaltennr" value="'.$dve->spaltennr.'" />
        '.(Strings::strExists(':inhaltsbereich(', $fk['content'], false)?'<input type="hidden" id="is_inhaltsbereich" value="true" />':'');

    echo '
    </div>
    <img src="images/moveboxB.png" alt="" class="schatten" />
</div>';   
?>
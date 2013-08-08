<?php
if($user->r('str', 'menue') && $index == 'n172')
{
    $open = $fksdb->save($_GET['open']);
        
    $menue = $base->getActiveTemplateConfig('menus', $v->menue);
    if(!is_array($menue))
        exit();
    
    $elemente = array();
    $elementeQ = $fksdb->query("SELECT menue, mid, sprachen, id FROM ".SQLPRE."menus WHERE struktur = '".$base->getStructureID()."' AND menue = '".$v->menue."' ORDER BY sort");
    while($e = $fksdb->fetch($elementeQ))
    {
        $elemente[$e->mid][] = $e;
    } 
    
    function kats($base, $fksdb, $trans, $eltern, $ebene, $open, $elemente)
    {
        $current = $elemente[$eltern]; 
        if(!is_array($current)) $current = array();  
        
        foreach($current as $k)
        { 
            $has_childs = count($elemente[$k->id]);
            
            $spr = $base->fixedUnserialize($k->sprachen);
            if(!is_array($spr)) $spr = array();
            
            $titel = $spr[$trans->getInputLanguage()];
            if(!$titel)
            {
                $alternativ = '...';
                foreach($spr as $sp => $spv)
                {
                    $alternativ = '('.strtoupper($sp).') '.$spv;
                    break;
                }
            }
            
            echo '
            <div class="zweig zweig_'.$ebene.'" data-kat="'.$k->id.'"'.($k->id == $open?' id="open_me"':'').'>
                <div class="row">
                    <div class="white'.($has_childs?' childs':'').(!$titel?' no_lan':'').'" data-kat="'.$k->id.'">
                        <a class="name" data-id="'.$k->id.'" data-titel="'.$k->titel.'" data-element="'.$k->mid.'">
                            '.($titel?$titel:$alternativ).'
                        </a>
                    </div>
                    <div class="more">
                        '.($has_childs?'
                        <a class="klappen">
                            <strong>'.$trans->__('aufklappen').'</strong>
                            <span>('.$has_childs.')</span>
                        </a>':'').'
                        <div class="opt'.(!$has_childs?' optwmargin':'').'">
                            <a>'.$trans->__('Optionen').'</a>
                            <div class="optarea" data-kat="'.$k->id.'">
                                <p>
                                    <a class="add_child">'.$trans->__('Unter-Menüpunkt hinzufügen').'</a>
                                    <a class="add_sibling">'.$trans->__('Nachbar-Menüpunkt hinzufügen').'</a>
                                </p>
                                <p>
                                    '.($ebene > 0?'<a class="move_higher">'.$trans->__('Menüpunkt eine Ebene höher verschieben').'</a>':'').'
                                    <a class="move_another">'.$trans->__('Menüpunkt einem anderen unterordnen').'</a>
                                </p>
                                <p>
                                    <a class="edit">'.$trans->__('Menüpunkt bearbeiten').'</a>
                                    <a class="delete">'.$trans->__('Menüpunkt löschen').'</a>
                                </p>
                            </div>
                        </div>
                        <div class="move"></div>
                    </div>
                </div>';
            
            if($has_childs)
            {
                $ebene ++;
                kats($base, $fksdb, $trans, $k->id, $ebene, $open, $elemente);
                $ebene --;
            }
            
            echo '
            </div>';        
        }
    }
    kats($base, $fksdb, $trans, 0, 0, $open, $elemente);
    
    echo '
    <div class="tbuttons">
        <button class="new shortcut-new">'.$trans->__('Neuen Menüpunkt hinzufügen').'</button>
    </div>';
}
?>
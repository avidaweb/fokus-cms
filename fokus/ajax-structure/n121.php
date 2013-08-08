<?php
if($user->r('str', 'ele') && $index == 'n121')
{
    $open = Strings::explodeCheck(',', $_GET['open']);
    $just_select = $fksdb->save($_GET['just_select']); 
	$showall = $fksdb->save($_REQUEST['show_all']);
    
    $elemente = array();
    $elementeQ = $fksdb->query("SELECT id, titel, element, frei, klasse, anfang, bis, rollen, sprachen, is_hidden FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' AND papierkorb = '0'".(!$showall?" AND is_hidden = '0'":"")." ORDER BY sort");
    while($e = $fksdb->fetch($elementeQ))
    {
        $elemente[$e->element][] = $e;
    }
    
    function kats($base, $fksdb, $trans, $eltern, $ebene, $open, $elemente)
    {
        $current = $elemente[$eltern]; 
        if(!is_array($current)) $current = array();
        
        foreach($current as $k)
        {
            $has_childs = count($elemente[$k->id]);
            $frei = (!$k->frei || ($k->anfang > time()) || ($k->bis > 86400 && $k->bis < time())?false:true);
            $rollen = ($frei && strlen($k->rollen) > 3?true:false);
			
			$spr = $base->fixedUnserialize($k->sprachen);
			$no_lan = (!$spr[$trans->getInputLanguage()]?true:false);
            
            echo '
            <div class="zweig zweig_'.$ebene.($k->klasse?' is_klasse':'').'" data-kat="'.$k->id.'">
                <div class="row">
                    <div class="white'.($has_childs?' childs':'').($no_lan?' no_lan':'').($k->is_hidden?' p_is_hidden':'').'" data-kat="'.$k->id.'">
                        <a class="name'.(!$frei?' '.(!$k->frei?'gesperrt':'lebensdauer'):'').($rollen?' rollen':'').'" data-id="'.$k->id.'" data-titel="'.$k->titel.'" data-element="'.$k->element.'">
                            '.$k->titel.'
                        </a>
						'.($fksdb->save($_REQUEST['mfa'])?'
						<span class="select">
							<input type="checkbox" name="ma[]" value="'.$k->id.'" id="mfaa_'.$k->id.'" />
							<label for="mfaa_'.$k->id.'">'.$k->titel.'</label>
						</span>':'').'
                    </div>
                    <div class="more">
                        '.($has_childs?'
                        <a class="klappen'.(in_array($k->id, $open)?' reopen':'').'" data-kat="'.$k->id.'">
                            <strong>'.$trans->__('aufklappen').'</strong>
                            <span>('.$has_childs.')</span>
                        </a>':'').'
                        <div class="opt'.(!$has_childs?' optwmargin':'').'">
                            <a>'.$trans->__('Optionen').'</a>
                            <div class="optarea" data-kat="'.$k->id.'">
                                <p>
                                    '.(!$k->klasse?'<a class="add_child">'.$trans->__('Unter-Element hinzufügen').'</a>':'').'
                                    <a class="add_sibling">'.$trans->__('Nachbar-Element hinzufügen').'</a>
                                </p>
                                '.(!$k->klasse?'
                                <p>
                                    '.($ebene > 0?'<a class="move_higher">'.$trans->__('Element eine Ebene höher verschieben').'</a>':'').'
                                    <a class="move_another">'.$trans->__('Element einem anderen unterordnen').'</a>
                                </p>
                                ':'').'
                                <p>
                                    <a class="edit">'.$trans->__('Element bearbeiten').'</a>
                                    '.(!$k->klasse?'<a class="delete">'.$trans->__('Element löschen').'</a>':'').'
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
        <button class="new shortcut-new">'.$trans->__('Neues Element hinzufügen').'</button>
    </div>';
}
?>
<?php
if($index != 'n191')
    exit();
    
if(!$user->r('str', 'kat') && !$v->just_select)
    exit($user->noRights());
    
if(!$user->r('dok', 'cats') && $v->just_select)
    exit($user->noRights());
    
$open = $fksdb->save($_GET['open']);
$just_select = $fksdb->save($_GET['just_select']);

function kats($base, $fksdb, $trans, $eltern, $ebene, $open, $just_select)
{
    $kq = $fksdb->query("SELECT id, name, kat FROM ".SQLPRE."categories WHERE kat = '".$eltern."' ORDER BY sort");
    while($k = $fksdb->fetch($kq))
    {
        $has_childs = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."categories WHERE kat = '".$k->id."'"));
        
        echo '
        <div class="zweig zweig_'.$ebene.'" data-kat="'.$k->id.'"'.($k->id == $open?' id="open_me"':'').'>
            <div class="row">
                <div class="white'.($has_childs?' childs':'').'" data-kat="'.$k->id.'">
                    '.(!$just_select?'
                    <a class="name">
                        '.$k->name.'
                    </a>':'
                    <p class="just_select">
                        <input type="checkbox" class="select" id="kat_select_'.$k->id.'" value="'.$k->id.'" data-name="'.$k->name.'" />
                        <label for="kat_select_'.$k->id.'">'.$k->name.'</label>
                    </p>').'
                    <p class="renameit">
                        <input type="text" value="'.$k->name.'" />
                        <a class="save">'.$trans->__('speichern').'</a>
                    </p>
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
                                <a class="add_child">'.$trans->__('Unterkategorie hinzufügen').'</a>
                                <a class="add_sibling">'.$trans->__('Nachbar-Kategorie hinzufügen').'</a>
                            </p>
                            <p>
                                '.($ebene > 0?'<a class="move_higher">'.$trans->__('Kategorie eine Ebene höher verschieben').'</a>':'').'
                                <a class="move_another">'.$trans->__('Kategorie einer anderen unterordnen').'</a>
                            </p>
                            <p>
                                <a class="rename">'.$trans->__('Kategorie umbennen').'</a>
                                <a class="delete">'.$trans->__('Kategorie löschen').'</a>
                            </p>
                        </div>
                    </div>
                    <div class="move"></div>
                </div>
            </div>';
        
        if($has_childs)
        {
            $ebene ++;
            kats($base, $fksdb, $trans, $k->id, $ebene, $open, $just_select);
            $ebene --;
        }
        
        echo '
        </div>';        
    }
}
kats($base, $fksdb, $trans, 0, 0, $open, $just_select);

echo '
<div class="tbuttons">
    <button class="new shortcut-new">'.$trans->__('Neue Kategorie hinzufügen').'</button>
</div>';
?>
<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok', 'cats') || $index != 'n251_1_categories')
    exit($user->noRights());
     
$id = $fksdb->save($_POST['id'], 1);

$doc = $fksdb->fetch("SELECT id, von, klasse, kats FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$doc || !$doc->klasse)
    exit('<div class="ifehler">'.$trans->__('Dokument nicht gefunden.').'</div>');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())) {}
else exit($user->noRights());

echo '
<h1>'.$trans->__('Dokument mit Kategorien verknüpfen.').'</h1>

<div class="box">
    <p class="introtext">
        '.$trans->__('Sie können einem Dokument beliebig viele Kategorien zuweisen, um beispielsweise die Ausgabe innerhalb eines Teasers einzuschränken.').'
    </p>
</div>

<div class="box">';
    $katQ = $fksdb->query("SELECT id, name FROM ".SQLPRE."categories ORDER BY kat, sort");
    if($fksdb->count($katQ))
    {
        $kats = $base->fixedUnserialize($doc->kats);
        if(!is_array($kats)) $kats = array();
        
        echo '
        <form class="doc_categories">
            <div class="boxedarea">
                <ul>';
                    while($z = $fksdb->fetch($katQ))
                    {                                            
                        if(!in_array($z->id, $kats))
                            continue;
                            
                        echo '
                        <li class="in_kat_'.$z->id.'">
                            <span>'.$z->name.'</span>
                            <a></a>
                            <input type="hidden" name="kat[]" value="'.$z->id.'" />
                        </li>';
                    }
                echo '                            
                </ul>
                <a class="add">'. $trans->__('Kategorien hinzufügen') .'</a>
            </div>
        </form>';
    }
    else
    {
        echo $trans->__('Noch keine Kategorien vorhanden, die ausgewählt werden könnten.');
    }
echo '
</div>

<div class="box_save">
    <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
    <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
</div>';

exit();
?>
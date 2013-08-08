<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n260_load_relation')
    exit($user->noRights());
    
$related = $fksdb->save($_POST['related']);
$limit = $fksdb->save($_POST['limit'], 1);

$sqlstring = "";
$sort = explode(',', $fksdb->save($_POST['sort']));
foreach($sort as $s)
{
    if(intval($s) > 0)
        $sqlstring .= ($sqlstring?" OR ":"")." id = '".intval($s)."' ";
}

$ka = array();
$kq = $fksdb->query("SELECT id, titel FROM ".SQLPRE."documents WHERE klasse = '".$related."' AND papierkorb = '0' ".($sqlstring?"AND (".$sqlstring.")":"AND id = '-1'")); 

while($ko = $fksdb->fetch($kq))
    $ka[$ko->id] = $ko;
    
$related_count = 0;

foreach($sort as $s)
{
    $k = $ka[$s];
    
    if(!$k)
        continue;
        
    if($related_count >= $limit && $limit)
        continue;
        
    echo '
    <div class="zweig zweig_0" data-kat="'.$k->id.'">
        <div class="row">
            <div class="white">
                <a class="name"'.(strlen($k->titel) > 60?' title="'.$k->titel.'"':'').'>
                    '.($k->titel?$k->titel:$k->id).'
                </a>
            </div>
            <div class="more">
                <a class="del">'.$trans->__('entfernen').'</a>
                <div class="move"></div>
            </div>
        </div>
    </div>';
    
    $related_count ++;
}

if($related_count < $limit || !$limit)
    echo '<button class="new">'.$trans->__('Dokumente hinzufügen').'</button>';
?>
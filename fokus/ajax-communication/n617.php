<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'nledit') || !$suite->rm(5) || !$index == 'n617')
    exit($user->noRights());
        
$newF = array();
parse_str($_POST['sort'], $s); 

if(count($s['Ksd']))
{
    foreach($s['Ksd'] as $ss)
        $newF[] = $ss;
}

$data = array(
    "doks" => $base->array_to_db($newF),
    "titel" => $fksdb->save($_POST['titel']),
    "template" => $fksdb->save($_POST['template'])
);

if($rel)
{
    $fksdb->update("newsletters", $data, array(
        "id" => $rel
    ), 1);
}
else
{
    $fksdb->insert("newsletters", $data);
    
    exit($fksdb->getInsertedID());
}
?>
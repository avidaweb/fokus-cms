<?php
if(!$user->r('per', 'rollen') || $index != 'n551')
    exit($user->noRights());

parse_str($_POST['values'], $v);

$id = $fksdb->save($v['r_id'], 1);

if($id == 1)
    exit();

if($id)
{
    $fksdb->update("roles", array(
        "titel" => $fksdb->save($v['titel']),
        "beschr" => $fksdb->save($v['beschr']),
        "frontend" => $fksdb->save($v['frontend'])
    ), array(
        "id" => $id
    ), 1);
}
else
{
    $fksdb->insert("roles", array(
        "titel" => $fksdb->save($v['titel']),
        "beschr" => $fksdb->save($v['beschr']),
        "frontend" => $fksdb->save($v['frontend']),
        "sort" => 999999
    ));
    $id = $fksdb->getInsertedID();
}

// Rechte emfpangen :)
$r = $v['r'];
if(!is_array($r))
    $r = array();

$rechte = array();

if($r['is_str'])
    $rechte['str'] = $r['str'];
if($r['is_dok'])
    $rechte['dok'] = $r['dok'];
if($r['is_dat'])
    $rechte['dat'] = $r['dat'];
if($r['is_per'])
    $rechte['per'] = $r['per'];
if($r['is_kom'])
    $rechte['kom'] = $r['kom'];
if($r['is_suc'])
    $rechte['suc'] = $r['suc'];
if($r['is_fks'])
    $rechte['fks'] = $r['fks'];
if($r['is_api'])
    $rechte['api'] = $r['api'];

$fksdb->update("roles", array(
    "rechte" => $base->array_to_db($rechte)
), array(
    "id" => $id
), 1);

// Fehlerseiten
$newF = array();
parse_str($_POST['sort'], $s);

if(!is_array($s['Rsd']))
    $s['Rsd'] = array();

foreach($s['Rsd'] as $ss)
    $newF[] = $ss;

$fksdb->update("roles", array(
    "fehler" => serialize($newF)
), array(
    "id" => $id
), 1);
?>
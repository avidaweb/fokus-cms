<?php
if(!$user->r('str', 'slots') || !$user->r('str', 'ele') || $index != 'n161')
    exit($user->noRights());
    
$element = $fksdb->fetch("SELECT id, slots FROM ".SQLPRE."elements WHERE id = '".$v->element."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
if(!$element)
    exit();
    
$slots = $base->db_to_array($element->slots);
$slots[$v->slot] = ($v->open == 1?true:false);

$fksdb->update("elements", array(
    "slots" => $base->array_to_db($slots)
), array(
    "id" => $element->id
), 1);
?>
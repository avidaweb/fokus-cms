<?php
if((!$user->r('str', 'ele') && !$user->r('str', 'slots') && !$user->r('fks', 'opt')) || $index != 'n141')
    exit($user->noRights());
   

if($v->slot && $v->sid)
{
    $element = $fksdb->fetch("SELECT id, klasse FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' LIMIT 1");
    if(!$element)
        exit('<div class="ifehler">'.$trans->__('Ausgewähltes Element existiert nicht oder befindet sich im Papierkorb').'</div>');
        
    $sdoks = $fksdb->rows("SELECT id, dokument, klasse FROM ".SQLPRE."document_relations WHERE slot = '".$v->slot."' AND element = '".$v->sid."' ORDER BY sort");
}
elseif($v->slot)
{
    $sdoks = $fksdb->rows("SELECT id, dokument, klasse FROM ".SQLPRE."document_relations WHERE slot = '".$v->slot."' AND element = '0' ORDER BY sort");
}
elseif($v->error)
{
    $sdoks = $fksdb->rows("SELECT id, dokument, klasse FROM ".SQLPRE."document_relations WHERE error_page = '".$v->error."' ORDER BY sort");
}
else
{
    $element = $fksdb->fetch("SELECT id, klasse FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' LIMIT 1");
    if(!$element)
        exit('<div class="ifehler">'.$trans->__('Ausgewähltes Element existiert nicht oder befindet sich im Papierkorb').'</div>');
    
    $sdoks = $fksdb->rows("SELECT id, dokument, klasse FROM ".SQLPRE."document_relations WHERE element = '".$element->id."' AND slot = '' ORDER BY sort");
}
if(!count($sdoks))
    exit('');
    
require(ROOT.'inc/classes.backend/class.document.preview.php');
    
$dsql = "";
foreach($sdoks as $sd)
    $dsql .= ($dsql?" OR ":"")." id = '".$sd->dokument."' ";

$doks = $fksdb->rows("SELECT id, titel, statusA, statusB, dversion_edit, klasse, dk1, dkt1, dk2, dkt2, dk3, dkt3, dk4, dkt4 FROM ".SQLPRE."documents WHERE papierkorb = '0' AND (".$dsql.")");   

foreach($sdoks as $did)
{
    $preview = new DocumentPreview($classes);
    echo $preview->getStructurePreview($did, $doks, $element, $dk);      
}
?>
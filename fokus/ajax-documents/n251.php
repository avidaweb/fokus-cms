<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251')
    exit($user->noRights());
    
$a = $fksdb->save($_GET['a']); 
$id = $fksdb->save($_GET['id'], 1);

$doc = $fksdb->fetch("SELECT * FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$doc)
    exit('<div class="ifehler">'.$trans->__('Dokument nicht gefunden.').'</div>');
    
$dve = $fksdb->fetch("SELECT id, language, spaltennr FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND id = '".$doc->dversion_edit."' LIMIT 1");
$dv = $fksdb->fetch("SELECT id, von_freigegeben, timestamp_freigegeben FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND language = '".$dve->language."' AND aktiv = '1' LIMIT 1");

$dname = 'Dokument';

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())) {}
else exit($user->noRights());

if($doc->klasse)
{
    $fk = $base->open_dklasse('../content/dklassen/'.$doc->klasse.'.php');
    $slug = $base->slug($fk['name']);
    
    $dka = $base->db_to_array($base->getOpt('dk'));
    $dks = (object)$dka;
}

if($a < 1 || $a > 5)
    exit('wrong_page');
    
require('n251_'.$a.'.php');
exit();
?>
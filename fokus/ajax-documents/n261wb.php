<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n261wb')
    exit($user->noRights());

$id = $fksdb->save($_POST['id'], 1);
parse_str($_POST['html'], $wb);

if(!is_array($wb))
    exit();

$dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT klasse_inhalt, id FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

if($dokument->klasse)
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);

    foreach($wb as $wk => $wv)
    {
        if(!is_array($wv))
            $ki[$wk]['html'] = $fksdb->save($wv);
        else
            $ki[$wk]['html'] = $base->array_to_db($wv);
    }

    $kis = serialize($ki);

    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$dve->id."' LIMIT 1");
}

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$dve->id."' LIMIT 1");
$base->create_dk_snippet($id);
?>
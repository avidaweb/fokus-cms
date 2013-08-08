<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'save_form')
    exit($user->noRights());

$block = $fksdb->save($_POST['block']);
$ibid = $fksdb->save($_POST['ibid']);
$blockindex = $fksdb->save($_POST['blockindex']);
$id = $fksdb->save($_POST['id'], 1);
$f = ($_POST['f']);
parse_str($f, $fa);

$dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if(!$dokument || !$dve)
    exit('no document');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

if($_POST['liste'])
{
    $copycat = $fa;
    $fa = array();
    foreach($copycat['liste'] as $l)
        $fa[] = str_replace('"', '&quot;', $fksdb->save(Strings::removeBadHTML(Strings::cleanString($l))));

    $fa['kindof'] = intval($copycat['kindof']);
}

if($_POST['a2db'])
    $sfa = $base->array_to_db($fa);
else
    $sfa = serialize($fa);

if(!$dokument->klasse)
{
    $upt = $fksdb->query("UPDATE ".SQLPRE."blocks SET html = '".$sfa."' WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
}
else
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);

    if(!$ibid)
        $ki[$block]['html'] = $sfa;
    else
        $ki[$ibid]['html'][$blockindex]['html'] = $sfa;

    $kis = serialize($ki);

    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$dve->id."' LIMIT 1");
}

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$dokument->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($id);
?>
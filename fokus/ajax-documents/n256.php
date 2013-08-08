<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n256')
    exit($user->noRights());

$aktiv = $fksdb->save($_GET['aktiv']);
$id = $fksdb->save($_GET['id'], 1);
$block = $fksdb->save($_GET['block'], 1);
$copy = $fksdb->save($_GET['copy'], 1);
$last = $fksdb->save($_GET['last']);
$extb = $fksdb->save($_GET['extb']);

$d = $fksdb->fetch("SELECT dversion_edit, id, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$d) exit();

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

if(!$last)
{
    $sort = $fksdb->save($_GET['sort']);
    $rest = substr($sort, 0, strpos($sort, "b[]="));
    $anzahl = preg_match_all('/block/i', $rest, $arrResult) + 1;
}
else
{
    $anzahl = 9999999;
}

if(!$copy)
{
    $fksdb->insert("blocks", array(
        "vid" => Strings::createID(),
        "dokument" => $id,
        "dversion" => $d->dversion_edit,
        "spalte" => $aktiv,
        "type" => $block,
        "sort" => $anzahl,
        "extb" => $extb
    ));
}
else
{
    $bl = $fksdb->fetchArray("SELECT * FROM ".SQLPRE."blocks WHERE id = '".$copy."' LIMIT 1");

    if($bl)
    {
        $fksdb->copy($bl, "blocks", array(
            "vid" => Strings::createID(),
            "dokument" => $id,
            "dversion" => $d->dversion_edit,
            "spalte" => $aktiv,
            "sort" => $anzahl
        ));
    }
}

if(!$last)
{
    $ergebnis = $fksdb->query("SELECT id FROM ".SQLPRE."blocks WHERE dokument = '".$id."' AND dversion = '".$d->dversion_edit."' AND spalte = '".$aktiv."' AND sort >= '".$anzahl."' AND id != '".$fksdb->getInsertedID()."' ORDER BY sort");
    while($row = $fksdb->fetch($ergebnis))
    {
        $anzahl ++;
        $update = $fksdb->query("UPDATE ".SQLPRE."blocks SET sort = '".$anzahl."' WHERE id = '".$row->id."'");
    }
}
else
{
    $anzahl = 0;
    $ergebnis = $fksdb->query("SELECT id FROM ".SQLPRE."blocks WHERE dokument = '".$id."' AND dversion = '".$d->dversion_edit."' AND spalte = '".$aktiv."' ORDER BY sort");
    while($row = $fksdb->fetch($ergebnis))
    {
        $anzahl ++;
        $update = $fksdb->query("UPDATE ".SQLPRE."blocks SET sort = '".$anzahl."' WHERE id = '".$row->id."'");
    }
}

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($d->id);
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok', 'edit') || $index != 'n203')
    exit($user->noRights());

$id = $fksdb->save($_GET['id']);
$dva = $fksdb->save($_GET['dv']);

$d = $fksdb->fetch("SELECT id, dversion_edit, gesperrt, anfang, bis, timestamp_freigegeben FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, language FROM ".SQLPRE."document_versions WHERE id = '".$d->dversion_edit."' AND dokument = '".$id."' LIMIT 1");
if($dva == -1)
    $dv = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE language = '".$dve->language."' AND dokument = '".$id."' AND aktiv = '1' LIMIT 1");
else
    $dv = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$dva."' AND dokument = '".$id."' LIMIT 1");
$doc = $d;

$del = $fksdb->query("DELETE FROM ".SQLPRE."columns WHERE dokument = '".$doc->id."' AND dversion = '".$dve->id."'");
$del = $fksdb->query("DELETE FROM ".SQLPRE."blocks WHERE dokument = '".$doc->id."' AND dversion = '".$dve->id."'");

// DKlassen Update
$dve_update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$dv->klasse_inhalt."' WHERE id = '".$d->dversion_edit."' AND dokument = '".$id."' LIMIT 1");

$ergebnis = $fksdb->query("SELECT * FROM ".SQLPRE."columns WHERE dokument = '".$doc->id."' AND dversion = '".$dv->id."'");
while($row = $fksdb->fetchArray($ergebnis))
{
    $fksdb->copy($row, "columns", array(
        "dokument" => $d->id,
        "dversion" => $dve->id
    ));
    $spalten_id = $fksdb->getInsertedID();

    $blQ = $fksdb->query("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$doc->id."' AND spalte = '".$row['id']."'");
    while($bl = $fksdb->fetchArray($blQ))
    {
        $fksdb->copy($bl, "blocks", array(
            "dokument" => $d->id,
            "dversion" => $dve->id,
            "spalte" => $spalten_id
        ));
    }
}

$base->create_dk_snippet($d->id);

if($dva == -1) // Aenderungen an Dokument rueckgaengig machen
{
    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '0', ende = '0' WHERE id = '".$dve->id."' LIMIT 1");
    $updta2 = $fksdb->query("UPDATE ".SQLPRE."documents SET statusA = '2', statusB = '".$base->find_document_statusB($d->gesperrt, $d->anfang, $d->bis, $d->timestamp_freigegeben)."' WHERE id = '".$id."' LIMIT 1");
}
else // Version aus Zeitsprung wiederherstellen
{
    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$dve->id."' LIMIT 1");
    $updta2 = $fksdb->query("UPDATE ".SQLPRE."documents SET statusA = '0', statusB = '".$base->find_document_statusB($d->gesperrt, $d->anfang, $d->bis, $d->timestamp_freigegeben)."' WHERE id = '".$id."' LIMIT 1");
}
?>
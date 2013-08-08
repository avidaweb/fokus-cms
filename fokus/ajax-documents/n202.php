<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok', 'publ') || $index != 'n202')
    exit($user->noRights());

$v = explode('_', $fksdb->save($_GET['v']));
$id = intval($v[0]);
$vid2 = intval($v[1]);

$d = $fksdb->fetch("SELECT id, gesperrt, anfang, bis FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dv = $fksdb->fetch("SELECT id, language, klasse_inhalt, spaltennr FROM ".SQLPRE."document_versions WHERE id = '".$vid2."' AND dokument = '".$id."' LIMIT 1");
if(!$d || !$dv) exit();

if($fksdb->save($_GET['a']))
{
    $updt = $fksdb->query("UPDATE ".SQLPRE."document_versions SET ende = '1' WHERE dokument = '".$d->id."' AND id = '".$dv->id."' LIMIT 1");
    $updt2 = $fksdb->query("UPDATE ".SQLPRE."documents SET von_edit = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->id."' LIMIT 1");
}

$updt = $fksdb->query("UPDATE ".SQLPRE."document_versions SET aktiv = '0' WHERE dokument = '".$d->id."' AND language = '".$dv->language."'");
$updt = $fksdb->query("UPDATE ".SQLPRE."document_versions SET aktiv = '1', ende = '0', timestamp_freigegeben = '".$base->getTime()."', von_freigegeben = '".$user->getID()."' WHERE dokument = '".$d->id."' AND id = '".$dv->id."' LIMIT 1");
$updt = $fksdb->query("UPDATE ".SQLPRE."documents SET freigegeben = '".$user->getID()."', timestamp_freigegeben = '".$base->getTime()."', statusA = '2', statusB = '".$base->find_document_statusB($d->gesperrt, $d->anfang, $d->bis)."' WHERE id = '".$d->id."' LIMIT 1");

$fksdb->insert("document_versions", array(
    "dokument" => $d->id,
    "von" => $user->getID(),
    "timestamp" => $base->getTime(),
    "language" => $dv->language,
    "klasse_inhalt" => $dv->klasse_inhalt,
    "spaltennr" => $dv->spaltennr
));
$vid = $fksdb->getInsertedID();

$updt = $fksdb->query("UPDATE ".SQLPRE."documents SET dversion_edit = '".$vid."' WHERE id = '".$d->id."' LIMIT 1");

$ergebnis = $fksdb->query("SELECT * FROM ".SQLPRE."columns WHERE dokument = '".$d->id."' AND dversion = '".$dv->id."'");
while($row = $fksdb->fetchArray($ergebnis))
{
    $fksdb->copy($row, "columns", array(
        "dokument" => $d->id,
        "dversion" => $vid
    ));
    $spalten_id = $fksdb->getInsertedID();

    $blQ = $fksdb->query("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$d->id."' AND spalte = '".$row['id']."'");
    while($bl = $fksdb->fetchArray($blQ))
    {
        $fksdb->copy($bl, "blocks", array(
            "dokument" => $d->id,
            "dversion" => $vid,
            "spalte" => $spalten_id
        ));
    }
}
?>
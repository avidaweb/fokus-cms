<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n253')
    exit($user->noRights());

$task = $fksdb->save($_POST['task']);
$id = $fksdb->save($_POST['id'], 1);

$d = $fksdb->fetch("SELECT dversion_edit, id, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$d)
    exit('no document');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

$ergebnisA = $fksdb->query("SELECT id FROM ".SQLPRE."columns WHERE dokument = '".$id."' AND dversion = '".$d->dversion_edit."'");
$spaltenanzahl = $fksdb->count($ergebnisA);

function spalten_berechnen($fksdb, $d, $dazu = 0, $not_spalte = 0)
{
    $ergebnis = $fksdb->query("SELECT id, size FROM ".SQLPRE."columns WHERE dokument = '".$d->id."' AND dversion = '".$d->dversion_edit."' AND id != '".$not_spalte."' AND closed = '0'");
    while($row = $fksdb->fetch($ergebnis))
    {
        $nwidth_each = $row->size - $dazu;
        if($nwidth_each < 5)
            $nwidth_each = 5;

        if($dazu != 0)
            $update = $fksdb->query("UPDATE ".SQLPRE."columns SET size = '".$nwidth_each."' WHERE id = '".$row->id."' LIMIT 1");
    }
}

function spalten_update($fksdb, $d, $spalte = 0)
{
    $gesamt = 0;

    $ergebnis = $fksdb->query("SELECT id, size FROM ".SQLPRE."columns WHERE closed = '0' AND dokument = '".$d->id."' AND dversion = '".$d->dversion_edit."'");
    while($row = $fksdb->fetch($ergebnis))
        $gesamt += $row->size;

    $max = 100;
    $ergebnis = $fksdb->query("SELECT id, size FROM ".SQLPRE."columns WHERE closed = '1' AND dokument = '".$d->id."' AND dversion = '".$d->dversion_edit."'");
    while($row = $fksdb->fetch($ergebnis))
        $max -= $row->size;

    if($gesamt > $max)
    {
        $diff = $gesamt - $max;
        if($diff >= 0.05)
        {
            $ergebnis = $fksdb->query("SELECT id, size FROM ".SQLPRE."columns WHERE dokument = '".$d->id."' AND dversion = '".$d->dversion_edit."' AND id != '".$spalte."' AND closed = '0'");

            if($fksdb->count($ergebnis))
            {
                $re_verlust = $diff / $fksdb->count($ergebnis);
                while($row = $fksdb->fetch($ergebnis))
                {
                    $nwidth_diff = round($row->size - $re_verlust, 1);
                    if($fksdb->count($ergebnis) == 1 && !$spalte)
                        $nwidth_diff = $max;
                    $update = $fksdb->query("UPDATE ".SQLPRE."columns SET size = '".$nwidth_diff."' WHERE id = '".$row->id."' LIMIT 1");
                }
            }
        }
    }
    if($gesamt < $max)
    {
        $diff = $max - $gesamt;
        if($diff >= 0.05)
        {
            $ergebnis = $fksdb->query("SELECT id, size FROM ".SQLPRE."columns WHERE dokument = '".$d->id."' AND dversion = '".$d->dversion_edit."' AND id != '".$spalte."' AND closed = '0'");

            if($fksdb->count($ergebnis))
            {
                $re_verlust = round($diff / $fksdb->count($ergebnis));
                while($row = $fksdb->fetch($ergebnis))
                {
                    $nwidth_diff = $row->size + $re_verlust;
                    if($fksdb->count($ergebnis) == 1 && !$spalte)
                        $nwidth_diff = $max;
                    $update = $fksdb->query("UPDATE ".SQLPRE."columns SET size = '".$nwidth_diff."' WHERE id = '".$row->id."' LIMIT 1");
                }
            }
        }
    }
}

if($task == 1)
{
    $pos = $fksdb->save($_POST['pos']) + 1;

    $nwidthG = 0;
    $ergebnis = $fksdb->query("SELECT id, size FROM ".SQLPRE."columns WHERE dokument = '".$id."' AND dversion = '".$d->dversion_edit."' AND closed = '0'");
    while($spal = $fksdb->fetch($ergebnis))
        $nwidthG += $spal->size;
    $nwidth = ($nwidthG / ($fksdb->count($ergebnis)+1));

    $fksdb->insert("columns", array(
        "dokument" => $id,
        "dversion" => $d->dversion_edit,
        "size" => $nwidth,
        "sort" => $pos
    ));

    $ins_id = $fksdb->getInsertedID();
    $fksdb->update("columns", array(
        "vid" => $ins_id
    ), array(
        "id" => $ins_id
    ), 1);


    $ergebnis2 = $fksdb->query("SELECT id, sort FROM ".SQLPRE."columns WHERE dokument = '".$id."' AND dversion = '".$d->dversion_edit."' AND id != '".$ins_id."' AND sort >= '".$pos."'");
    while($row2 = $fksdb->fetch($ergebnis2))
    {
        $nsort = $row2->sort + 1;
        $update = $fksdb->query("UPDATE ".SQLPRE."columns SET sort = '".$nsort."' WHERE id = '".$row2->id."' LIMIT 1");
    }

    if($fksdb->count($ergebnis) - 1 == 0) $verlust_each = 50;
    else $verlust_each = round($nwidth / ($fksdb->count($ergebnis) - 1), 0);

    spalten_berechnen($fksdb, $d, $verlust_each, $ins_id);
    spalten_update($fksdb, $d);
}
else if($task == 2)
{
    $loesch = $fksdb->save($_POST['loesch']);
    $altw = $fksdb->fetch("SELECT id, size FROM ".SQLPRE."columns WHERE id = '".$loesch."' LIMIT 1");

    $delete = $fksdb->query("DELETE FROM ".SQLPRE."columns WHERE id = '".$loesch."' AND dokument = '".$id."' AND dversion = '".$d->dversion_edit."' LIMIT 1");

    $ergebnis = $fksdb->query("SELECT id FROM ".SQLPRE."columns WHERE dokument = '".$id."' AND dversion = '".$d->dversion_edit."'");
    $verlust_each = round($altw->size / $fksdb->count($ergebnis), 0);

    spalten_berechnen($fksdb, $d, -$verlust_each, $altw->id);
    spalten_update($fksdb, $d);
}
else if($task == 3)
{
    $width = $_POST['width'];
    $ges_width = 0;
    $last_width = 0;
    $count = 0;


    $sQ = $fksdb->query("SELECT id, closed, size FROM ".SQLPRE."columns WHERE dokument = '".$id."' AND dversion = '".$d->dversion_edit."' ORDER BY sort");
    while($s = $fksdb->fetch($sQ))
    {
        if($s->closed)
            $cwidth = $s->size;
        elseif($count < count($width))
            $cwidth = $width[$count] - $last_width;
        else
            $cwidth = 100 - $ges_width;

        $update = $fksdb->query("UPDATE ".SQLPRE."columns SET size = '".$cwidth."' WHERE id = '".$s->id."' LIMIT 1");

        $ges_width += $cwidth;
        $last_width = $width[$count];
        $count ++;
    }
    spalten_update($fksdb, $d);
}
else if($task == 4 && $spaltenanzahl > 1)
{
    $spalte = $fksdb->save($_POST['spalte']);
    $altw = $fksdb->fetch("SELECT id, size FROM ".SQLPRE."columns WHERE id = '".$spalte."' LIMIT 1");

    $update = $fksdb->query("UPDATE ".SQLPRE."columns SET size = '".($altw->size + 1)."' WHERE id = '".$spalte."' AND closed = '0' LIMIT 1");
    spalten_update($fksdb, $d, $spalte);
}
else if($task == 5 && $spaltenanzahl > 1)
{
    $spalte = $fksdb->save($_POST['spalte']);
    $altw = $fksdb->fetch("SELECT id, size FROM ".SQLPRE."columns WHERE id = '".$spalte."' LIMIT 1");

    $update = $fksdb->query("UPDATE ".SQLPRE."columns SET size = '".($altw->size - 1)."' WHERE id = '".$spalte."' AND closed = '0' LIMIT 1");
    spalten_update($fksdb, $d, $spalte);
}
else if($task == 6)
{
    $spalte = $fksdb->save($_POST['spalte']);
    $width = str_replace(',', '.', $fksdb->save($_POST['width']));

    $update = $fksdb->query("UPDATE ".SQLPRE."columns SET size = '".$width."' WHERE id = '".$spalte."' AND closed = '0' LIMIT 1");
    spalten_update($fksdb, $d, $spalte);
}
else if($task == 7)
{
    $max = 100;
    $ergebnis = $fksdb->query("SELECT id, size FROM ".SQLPRE."columns WHERE closed = '1' AND dokument = '".$id."' AND dversion = '".$d->dversion_edit."'");
    while($row = $fksdb->fetch($ergebnis))
        $max -= $row->size;

    $nsize = ($max / ($spaltenanzahl - $fksdb->count($ergebnis)));

    $update = $fksdb->query("UPDATE ".SQLPRE."columns SET size = '".$nsize."' WHERE closed = '0' AND dokument = '".$id."' AND dversion = '".$d->dversion_edit."'");
}
else if($task == 8)
{
    $spalte = $fksdb->save($_POST['spalte'], 1);
    $column = $fksdb->fetch("SELECT id, closed FROM ".SQLPRE."columns WHERE dokument = '".$id."' AND id = '".$spalte."' LIMIT 1");

    $update = $fksdb->query("UPDATE ".SQLPRE."columns SET closed = '".($column->closed?"0":"1")."' WHERE dokument = '".$id."' AND id = '".$column->id."' LIMIT 1");
}
else if($task == 9)
{
    $ids = $_POST['ids'];
    $xo = 0;

    foreach($ids as $cid)
    {
        $fksdb->update("columns", array(
            "sort" => $xo
        ), array(
            "id" => intval($cid),
            "dokument" => $id,
            "dversion" => $d->dversion_edit
        ), 1);

        $xo ++;
    }
}

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($d->id);
?>
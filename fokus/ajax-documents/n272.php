<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n272')
    exit($user->noRights());

$block = $fksdb->save($_REQUEST['block']);
$ibid = $fksdb->save($_REQUEST['ibid']);
$blockindex = $fksdb->save($_REQUEST['blockindex']);
$id = $fksdb->save($_REQUEST['id'], 1);
$dir = $fksdb->save($_REQUEST['dir']);
$pid = $fksdb->save($_REQUEST['pid']);
$pids = explode('_', $pid);

$dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

if(!$dokument->klasse && !$dokument->produkt)
{
    $b = $fksdb->fetch("SELECT html FROM ".SQLPRE."blocks WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
    $html = $base->fixedUnserialize($b->html);

    if($dir)
    {
        $p = $fksdb->fetch("SELECT titel, id FROM ".SQLPRE."files WHERE id = '".$dir."' AND papierkorb = '0' AND isdir = '1' LIMIT 1");
        if($p->id)
            $html[] = array("id" => $p->id, "name" => $p->titel, "dir" => true);
    }
    else
    {
        foreach($pids as $pid)
        {
            $p = $fksdb->fetch("SELECT titel, id, beschr FROM ".SQLPRE."files WHERE id = '".$pid."' AND papierkorb = '0' LIMIT 1");
            if($p->id)
                $html[] = array("id" => $p->id, "name" => $p->titel, "desc" => $p->beschr);
        }
    }

    $upt = $fksdb->query("UPDATE ".SQLPRE."blocks SET html = '".serialize($html)."' WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
}
else
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);

    if(!$ibid)
        $html = $base->fixedUnserialize($ki[$block]['html']);
    else
        $html = $base->fixedUnserialize($ki[$ibid]['html'][$blockindex]['html']);

    if($dir)
    {
        $p = $fksdb->fetch("SELECT titel, id FROM ".SQLPRE."files WHERE id = '".$dir."' AND papierkorb = '0' AND isdir = '1' LIMIT 1");
        if($p->id)
            $html[] = array("id" => $p->id, "name" => $p->titel, "dir" => true);
    }
    else
    {
        foreach($pids as $pid)
        {
            $p = $fksdb->fetch("SELECT titel, id, beschr FROM ".SQLPRE."files WHERE id = '".$pid."' AND papierkorb = '0' LIMIT 1");
            if($p->id)
                $html[] = array("id" => $p->id, "name" => $p->titel, "desc" => $p->beschr);
        }
    }

    if(!$ibid)
        $ki[$block]['html'] = serialize($html);
    else
        $ki[$ibid]['html'][$blockindex]['html'] = serialize($html);

    $kis = serialize($ki);

    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$dve->id."' LIMIT 1");
}

$d = $fksdb->fetch("SELECT dversion_edit FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($id);
?>
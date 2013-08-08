<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n268') // Mehrfachauswahl bei den Inhaltselementen
    exit($user->noRights());

$id = $fksdb->save($_POST['id'], 1);
$a = $fksdb->save($_POST['a']);
parse_str($_POST['form'], $f);

$d = $fksdb->fetch("SELECT dversion_edit, id, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

$last = $f['block'][(count($f['block']) - 1)];
$blast = $fksdb->fetch("SELECT sort FROM ".SQLPRE."blocks WHERE id = '".intval($last)."' AND dokument = '".$id."' LIMIT 1");

foreach($f['block'] as $b)
{
    $b = $fksdb->save($b, 1);

    if($a == 'mfa_del')
    {
        $del = $fksdb->query("DELETE FROM ".SQLPRE."blocks WHERE id = '".$b."' AND dokument = '".$id."' LIMIT 1");
    }
    elseif($a == 'mfa_sperr')
    {

    }
    elseif($a == 'mfa_copy')
    {
        $bl = $fksdb->fetchArray("SELECT * FROM ".SQLPRE."blocks WHERE id = '".$b."' AND dokument = '".$id."' LIMIT 1");

        if($bl)
        {
            $fksdb->copy($bl, "blocks", array(
                "vid" => Strings::createID(),
                "dokument" => $d->id,
                "sort" => $blast->sort
            ));
        }
    }
    elseif($a == 'mfa_ablage' && $suite->rm(4))
    {
        $user->clipboard('inhaltselement', $b, '', $id);
    }
}

if($a == 'mfa_copy')
{
    $nsort = 0;
    $bQ = $fksdb->query("SELECT id FROM ".SQLPRE."blocks WHERE dversion = '".$d->dversion_edit."' AND dokument = '".$id."' ORDER BY sort, id");
    while($b = $fksdb->fetch($bQ))
    {
        $nsort ++;
        $updt = $fksdb->query("UPDATE ".SQLPRE."blocks SET sort = '".$nsort."' WHERE id = '".$b->id."' AND dokument = '".$id."' LIMIT 1");
    }
}

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($id);
?>
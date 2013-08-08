<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n257_del')
    exit($user->noRights());

$aktiv = $fksdb->save($_GET['aktiv']);
$id = $fksdb->save($_GET['id'], 1);
$ib = explode('!', $fksdb->save($_GET['ib']));
$ibid = $ib[0];

$d = $fksdb->fetch("SELECT id, klasse, dversion_edit, produkt, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$d->dversion_edit."' LIMIT 1");
if(!$d) exit();

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

if(is_array($ib) && $d->klasse)
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);

    $copy = $ki[$ibid]['html'];
    $ki[$ibid]['html'] = array();

    $c = 0;
    foreach($copy as $k => $v)
    {
        if(is_array($v) && $ib[1] != $k)
        {
            $ki[$ibid]['html'][$c] = $v;
            $c++;
        }
    }

    $html = serialize($ki);
    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$html."', edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");
    $base->create_dk_snippet($d->id);
}
?>
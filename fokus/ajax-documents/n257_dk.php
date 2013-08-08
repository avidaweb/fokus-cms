<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n257_dk')
    exit($user->noRights());

$aktiv = $fksdb->save($_GET['aktiv']);
$id = $fksdb->save($_GET['id'], 1);
$ibid = $fksdb->save($_GET['ibid']);

$d = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$d) exit();

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

$sort = $_GET['sort'];
parse_str($sort, $teast);
$sort = array();
foreach($teast as $k => $v)
    $sort[] = str_replace("block_", "", $k);

$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$d->dversion_edit."' LIMIT 1");

if(($d->klasse || $d->produkt) && !Strings::strExists('&b[]', $_GET['sort']))
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);
    $make_copy = $ki[$ibid]['html'];
    $new_order = array();

    if(is_array($make_copy))
    {
        foreach($make_copy as $k => $v)
            $new_order[$v['id']] = $k;

        for($x=0; $x<count($sort); $x++)
        {
            $get = $new_order[$fksdb->save($sort[$x])];
            if(is_array($make_copy[$get]))
                $ki[$ibid]['html'][$x] = $make_copy[$get];
        }

        $html = serialize($ki);
        $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$html."', edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");

        $base->create_dk_snippet($d->id);
    }
}
?>
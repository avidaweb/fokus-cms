<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n261')
    exit($user->noRights());

$id = $fksdb->save($_POST['id'], 1);
$block = $fksdb->save($_POST['block']);
$ibid = $fksdb->save($_POST['ibid']);
$blockindex = $fksdb->save($_POST['blockindex']);
$type = $fksdb->save($_POST['type'], 1);
$all = $_POST['all'];
$sm = $_POST['sm'];

$dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");
if(!$dokument) exit();

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

if(!$dokument->klasse)
{
    $blockinfo = $fksdb->fetch("SELECT type FROM ".SQLPRE."blocks WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
    $type = $blockinfo->type;
}

if($type < 24)
    $html = $fksdb->save(Strings::removeBadHTML(Strings::cleanString($_POST['html'])));
else
    $html = $fksdb->save(Strings::cleanString($_POST['html']));
$html = rawurldecode($html);

parse_str($all, $p);

if($sm)
{
    parse_str($sm, $sa);
    $html = serialize($sa);
}

$bildw = null;
$bildh = null;
$bildwt = null;
$bildp = null;
$bildid = null;
$bildt = null;
$bild_extern = null;
$bild_link = null;

if($p['bild'] == 1)
{
    $bildw = $fksdb->save($p['bildw']);
    $bildh = $fksdb->save($p['bildh']);
    $bildwt = $fksdb->save($p['bildwt']);
    $bildp = $fksdb->save($p['bildp']);
    $bildid = $fksdb->save($p['bildid']);
    $bildt = $fksdb->save($p['bildt']);
    $bild_extern = '';
}
elseif($p['bild'] == 2)
{
    $bildw = $fksdb->save($p['bildw2']);
    $bildh = $fksdb->save($p['bildh2']);
    $bildwt = $fksdb->save($p['bildwt2']);
    $bildp = $fksdb->save($p['bildp2']);
    $bildt = $fksdb->save($p['bildt']);
    $bildid = 0;
    $bild_extern = $fksdb->save($p['bild_extern']);
}

if($bildt)
{
    $link_opt = array();
    $link_opt['href'] = $fksdb->save($p['link_href']);
    $link_opt['ziel'] = $fksdb->save($p['link_ziel']);
    $link_opt['power'] = $fksdb->save($p['link_power']);
    $link_opt['titel'] = $fksdb->save($p['link_titel']);
    $link_opt['klasse'] = $fksdb->save($p['link_klasse']);

    $bild_link = $base->array_to_db($link_opt);
}

if(!$dokument->klasse)
{
    $upd2 = $fksdb->query("UPDATE ".SQLPRE."blocks SET html = '".$html."', bild = '".$p['bild']."', bildid = '".$bildid."', bildw = '".$bildw."', bildh = '".$bildh."', bildwt = '".$bildwt."', bildt = '".$bild_link."', bildp = '".$bildp."', bild_extern = '".$bild_extern."' WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
    echo $fksdb->getError();
}
else
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);

    if(!$ibid)
    {
        $ki[$block]['html'] = $html;
        $ki[$block]['bild'] = $p['bild'];
        $ki[$block]['bildid'] = $bildid;
        $ki[$block]['bildw'] = $bildw;
        $ki[$block]['bildh'] = $bildh;
        $ki[$block]['bildwt'] = $bildwt;
        $ki[$block]['bildp'] = $bildp;
        $ki[$block]['bildt'] = $bild_link;
        $ki[$block]['bild_extern'] = $bild_extern;
    }
    else
    {
        $ki[$ibid]['html'][$blockindex]['html'] = $html;
        $ki[$ibid]['html'][$blockindex]['bild'] = $p['bild'];
        $ki[$ibid]['html'][$blockindex]['bildid'] = $bildid;
        $ki[$ibid]['html'][$blockindex]['bildw'] = $bildw;
        $ki[$ibid]['html'][$blockindex]['bildh'] = $bildh;
        $ki[$ibid]['html'][$blockindex]['bildwt'] = $bildwt;
        $ki[$ibid]['html'][$blockindex]['bildp'] = $bildp;
        $ki[$ibid]['html'][$blockindex]['bildt'] = $bild_link;
        $ki[$ibid]['html'][$blockindex]['bild_extern'] = $bild_extern;
    }

    $kis = serialize($ki);

    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$dve->id."' LIMIT 1");
}

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$dve->id."' LIMIT 1");
$base->create_dk_snippet($dokument->id);
?>
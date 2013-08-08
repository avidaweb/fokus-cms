<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n256_dk')
    exit($user->noRights());

$id = $fksdb->save($_GET['id'], 1);
$block = $fksdb->save($_GET['block'], 1);
$copy = $fksdb->save($_GET['copy']);
$ibid = $fksdb->save($_GET['ibid']);
$last = $fksdb->save($_GET['last']);
$extb = $fksdb->save($_GET['extb']);

$d = $fksdb->fetch("SELECT id, klasse, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$d->dversion_edit."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

if(!$d || !$dve || !$d->klasse)
    exit();

$ki = $base->fixedUnserialize($dve->klasse_inhalt);


if(!$last)
{
    $sort = $fksdb->save($_GET['sort']);
    $rest = substr($sort, 0, strpos($sort, ";b[]="));
    $anzahl = preg_match_all('/block/i', $rest, $arrResult);
}
else
{
    $anzahl = count($ki[$ibid]['html']);
}


$new_block = array(
    'id' => Strings::createID(),
    'type' => $block
);

if($extb)
    $new_block['extb'] = $extb;

if($copy)
{
    $bl = $fksdb->fetch("SELECT * FROM ".SQLPRE."blocks WHERE id = '".$copy."' LIMIT 1");

    $new_block['type'] = $bl->type;
    $new_block['bild'] = $bl->bild;
    $new_block['bildid'] = $bl->bildid;
    $new_block['bildw'] = $bl->bildw;
    $new_block['bildh'] = $bl->bildh;
    $new_block['bildwt'] = $bl->bildwt;
    $new_block['bildp'] = $bl->bildp;
    $new_block['bild_extern'] = $bl->bild_extern;
    $new_block['teaser'] = $bl->teaser;
    $new_block['html'] = $bl->html;
    $new_block['extb'] = $bl->extb;
    $new_block['extb_content'] = $bl->extb_content;
}

if(is_array($ki[$ibid]['html']))
{
    if($anzahl >= count($ki[$ibid]['html'])) // Letzer Position
    {
        $ki[$ibid]['html'][$anzahl] = $new_block;
    }
    else // Irgendwo mittendrin
    {
        $c = 0;
        foreach($ki[$ibid]['html'] as $k => $v)
        {
            if($anzahl == $c)
                $c++;

            $ki[$ibid]['html'][$c] = $v;

            if($anzahl == $c - 1)
            {
                $ki[$ibid]['html'][$anzahl] = $new_block;
            }
            $c++;
        }
    }
}
else // Erstes Element
{
    $ki[$ibid]['html'][0] = $new_block;
}

$html = serialize($ki);
$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$html."', edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($d->id);
?>
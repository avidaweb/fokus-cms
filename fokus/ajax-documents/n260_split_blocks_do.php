<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n260_split_blocks_do')
    exit($user->noRights());

$id = $fksdb->save($_POST['id'], 1);
$block = $fksdb->save($_POST['block']);
$ibid = $fksdb->save($_POST['ibid']);
$blockindex = $fksdb->save($_POST['blockindex']);

$bl = $fksdb->fetch("SELECT id, sort, spalte FROM ".SQLPRE."blocks WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
$zindex = $bl->sort;

$dokument = $fksdb->fetch("SELECT dversion_edit, id, klasse, produkt, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

$html = str_replace(array("\r\n", "\n", "\r"), '', $_POST['text']);

$abschnitte = explode('<br /><br />', $html);
if(!count($abschnitte))
    exit('Keine Aufteilung vorhanden');

$reverse_typen = array(
    'text' => 15,
    'h1' => 10,
    'h2' => 11,
    'h3' => 12,
    'h4' => 13,
    'zitat' => 18,
    'list' => 20
);

$counter = 0;
$final = array();

parse_str($_POST['f'], $f);
$box = $f['box'];

foreach($abschnitte as $a)
{
    $clear = strip_tags(str_replace('<br />', ' ', $a));

    if(!$clear)
        continue;

    $slug = $box[$counter];
    $final[$slug] .= ($final[$slug]?'<br /><br />':'').$a;

    $counter ++;
}

if(count($final))
{
    // Alten Block entfernen
    if(!$dokument->klasse)
    {
        $del = $fksdb->query("DELETE FROM ".SQLPRE."blocks WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
    }

    $ibid_new = array();
    $no_sql = "";

    // Neue Blöcke nach und nach durchgehen
    foreach($final as $k => $v)
    {
        $kk = explode('_', $k);
        $type = $reverse_typen[$kk[0]];

        $ins_html = '';

        if($type != 20) // text elements
        {
            $ins_html = rawurldecode($fksdb->save(Strings::removeBadHTML(Strings::cleanString($v))));
        }
        else // list elements
        {
            $items = explode('<br />', $v);
            $item = array();

            if(!count($items))
                $items[] = $v;

            foreach($items as $i)
            {
                $i = (trim(trim(trim(trim(($i)), '-'), '•')));
                $item[] = str_replace('"', '&quot;', $fksdb->save(Strings::removeBadHTML(Strings::cleanString($i))));
            }

            $ins_html = serialize($item);
        }

        if(!$type || !$ins_html)
            continue;

        if(!$dokument->klasse) // Falls normaler Block
        {
            $fksdb->insert("blocks", array(
                "vid" => Strings::createID(),
                "dokument" => $id,
                "dversion" => $dokument->dversion_edit,
                "spalte" => $bl->spalte,
                "type" => $type,
                "sort" => $zindex,
                "html" => $ins_html
            ));
            $no_sql .= " AND id != '".$fksdb->getInsertedID()."' ";

            $zindex ++;
        }
        elseif($ibid)
        {
            $ibid_new[] = array(
                'id' => Strings::createID(),
                'type' => $type,
                'html' => $ins_html
            );
        }
    }

    // Die alten Bloecke neu sortieren
    if(!$dokument->klasse && !$dokument->produkt)
    {
        $dahinterQ = $fksdb->query("SELECT id FROM ".SQLPRE."blocks WHERE spalte = '".$bl->spalte."' AND dokument = '".$id."' AND sort > '".$bl->sort."' ".$no_sql);
        while($dah = $fksdb->fetch($dahinterQ))
        {
            $updt = $fksdb->query("UPDATE ".SQLPRE."blocks SET sort = '".$zindex."' WHERE id = '".$dah->id."' AND dokument = '".$id."' LIMIT 1");
            $zindex ++;
        }
    }
    elseif($ibid && count($ibid_new))
    {
        $ki = $base->fixedUnserialize($dve->klasse_inhalt);

        $copy = $ki[$ibid]['html'];
        $ki[$ibid]['html'] = array();

        $c = 0;
        foreach($copy as $k => $v)
        {
            if(is_array($v))
            {
                if($blockindex == $k)
                {
                    foreach($ibid_new as $ibn)
                    {
                        $ki[$ibid]['html'][$c] = $ibn;
                        $c++;
                    }
                }
                else
                {
                    $ki[$ibid]['html'][$c] = $v;
                    $c++;
                }
            }
        }

        $kis = serialize($ki);
        $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$dve->id."' LIMIT 1");
    }
}

$update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$dokument->dversion_edit."' LIMIT 1");
$base->create_dk_snippet($id);
?>
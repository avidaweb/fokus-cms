<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok', 'new') || $index != 'n211')
    $user->noRights();

$all = $_POST['all'];
parse_str($all, $p);

$sp = array($trans->getInputLanguage());
$spmore[$trans->getInputLanguage()]['titel'] = $fksdb->save($p['titel']);

$zsb = '';
$selement = 0;
$slot = '';
$error = '';

if($p['zsb']) $zsb = serialize($p['r_zsb']);
if($p['selement']) $selement = $fksdb->save($p['selement']);
if($p['slot']) $slot = $fksdb->save($p['slot']);
if($p['error']) $error = $fksdb->save($p['error']);

$fksdb->insert("documents", array(
    "titel" => $fksdb->save($p['titel']),
    "von" => $user->getID(),
    "von_edit" => $user->getID(),
    "author" => $user->getID(),
    "timestamp_edit" => $base->getTime(),
    "timestamp" => $base->getTime(),
    "datum" => $base->getTime(),
    "klasse" => $fksdb->save($p['klasse']),
    "sprachen" => serialize($sp),
    "sprachenfelder" => serialize($spmore),
    "zsb" => $zsb,
    "statusA" => 0,
    "statusB" => 3
));
$id = $fksdb->getInsertedID();

$fksdb->insert("document_versions", array(
    "dokument" => $id,
    "von" => $user->getID(),
    "timestamp" => $base->getTime(),
    "language" => $trans->getInputLanguage(),
    "edit" => 1
));
$vid = $fksdb->getInsertedID();

$updt = $fksdb->query("UPDATE ".SQLPRE."documents SET dversion_edit = '".$vid."' WHERE id = '".$id."' LIMIT 1");

echo $id.'____'.$p['titel'];

if(!$p['vorlage'])
{
    $fksdb->insert("columns", array(
        "dokument" => $id,
        "dversion" => $vid,
        "size" => 100,
        "sort" => 1
    ));
    $ins_column_id = $fksdb->getInsertedID();

    $fksdb->update("columns", array(
        "vid" => $ins_column_id
    ), array(
        "id" => $ins_column_id
    ), 1);
}
else // Vorlage verwenden
{
    $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1' WHERE id = '".$vid."' LIMIT 1");

    if(!$p['type'] && $p['ch_vorlage']) // Aus Vorlage
    {
        $doc = $fksdb->fetch("SELECT id, zsb, css_klasse, kats, rollen, cf, no_search, author FROM ".SQLPRE."documents WHERE id = '".$fksdb->save($p['ch_vorlage'])."' LIMIT 1");
        $dve = $fksdb->fetch("SELECT id FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."' AND language = '".$trans->getInputLanguage()."' AND aktiv = '1' LIMIT 1");

        $fksdb->update("documents", array(
            "zsb" => $doc->zsb,
            "css_klasse" => $doc->css_klasse,
            "kats" => $doc->kats,
            "rollen" => $doc->rollen,
            "cf" => $doc->cf,
            "no_search" => $doc->no_search,
            "author" => $doc->author
        ), array(
            "id" => $id
        ), 1);

        $ergebnis = $fksdb->query("SELECT * FROM ".SQLPRE."columns WHERE dokument = '".$doc->id."' AND dversion = '".$dve->id."'");
        while($row = $fksdb->fetchArray($ergebnis))
        {
            $fksdb->copy($row, "columns", array(
                "dokument" => $id,
                "dversion" => $vid
            ));
            $spalten_id = $fksdb->getInsertedID();

            $fksdb->update("columns", array(
                "vid" => $spalten_id
            ), array(
                "id" => $spalten_id
            ), 1);

            if($p['aufteilung1'])
            {
                $blQ = $fksdb->query("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$doc->id."' AND spalte = '".$row['id']."'");
                while($bl = $fksdb->fetchArray($blQ))
                {
                    $nextblock = array(
                        "vid" => Strings::createID(),
                        "dokument" => $id,
                        "dversion" => $vid,
                        "spalte" => $spalten_id
                    );

                    if($p['aufteilung1'] != 2)
                    {
                        $nextblock = array_merge($nextblock, array(
                            "html" => "",
                            "teaser" => "",
                            "bild" => "",
                            "bildid" => "",
                            "bildw" => "",
                            "bildh" => "",
                            "bildwt" => "",
                            "bildp" => "",
                            "bildt" => "",
                            "bild_extern" => "",
                            "extb_content" => ""
                        ));
                    }

                    $fksdb->copy($bl, "blocks", $nextblock);
                }
            }
        }
    }
    elseif($p['type'] && $p['gew_dok']) // Aus bestehendem Dokument
    {
        $doc = $fksdb->fetch("SELECT id, zsb, css_klasse, kats, rollen, cf, no_search, author FROM ".SQLPRE."documents WHERE id = '".$fksdb->save($p['gew_dok'])."' LIMIT 1");
        $dve = $fksdb->fetch("SELECT id FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."' AND language = '".$trans->getInputLanguage()."' AND aktiv = '1' LIMIT 1");

        $fksdb->update("documents", array(
            "zsb" => $doc->zsb,
            "css_klasse" => $doc->css_klasse,
            "kats" => $doc->kats,
            "rollen" => $doc->rollen,
            "cf" => $doc->cf,
            "no_search" => $doc->no_search,
            "author" => $doc->author
        ), array(
            "id" => $id
        ), 1);

        $ergebnis = $fksdb->query("SELECT * FROM ".SQLPRE."columns WHERE dokument = '".$doc->id."' AND dversion = '".$dve->id."'");
        while($row = $fksdb->fetchArray($ergebnis))
        {
            $fksdb->copy($row, "columns", array(
                "dokument" => $id,
                "dversion" => $vid
            ));
            $spalten_id = $fksdb->getInsertedID();

            $fksdb->update("columns", array(
                "vid" => $spalten_id
            ), array(
                "id" => $spalten_id
            ), 1);

            if($p['aufteilung2'])
            {
                $blQ = $fksdb->query("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$doc->id."' AND spalte = '".$row['id']."'");
                while($bl = $fksdb->fetchArray($blQ))
                {
                    $nextblock = array(
                        "vid" => Strings::createID(),
                        "dokument" => $id,
                        "dversion" => $vid,
                        "spalte" => $spalten_id
                    );

                    if($p['aufteilung2'] != 2)
                    {
                        $nextblock = array_merge($nextblock, array(
                            "html" => "",
                            "teaser" => "",
                            "bild" => "",
                            "bildid" => "",
                            "bildw" => "",
                            "bildh" => "",
                            "bildwt" => "",
                            "bildp" => "",
                            "bildt" => "",
                            "bild_extern" => "",
                            "extb_content" => ""
                        ));
                    }

                    $fksdb->copy($bl, "blocks", $nextblock);
                }
            }
        }
    }
}

if($selement || $slot || $error)
{
    $i_element = ($selement?$selement:0);
    $i_slot = ($slot?$slot:'');
    $i_error = ($error?$error:'');

    $lastsd = $fksdb->fetch("SELECT sort FROM ".SQLPRE."document_relations WHERE element = '".$i_element."' AND slot = '".$i_slot."' AND error_page = '".$i_error."' ORDER BY sort DESC LIMIT 1");
    $sort = $lastsd->sort + 1;

    $fksdb->insert("document_relations", array(
        "element" => $i_element,
        "slot" => $i_slot,
        "error_page" => $i_error,
        "dokument" => $id,
        "timestamp" => $base->getTime(),
        "sort" => $sort
    ));

    $seiten = $fksdb->query("SELECT id FROM ".SQLPRE."document_relations WHERE dokument = '".$id."'");
    $upt2 = $fksdb->query("UPDATE ".SQLPRE."documents SET seiten = '".$fksdb->count($seiten)."' WHERE id = '".$id."' LIMIT 1");

    $countup = 0;
    $seitenss = $fksdb->query("SELECT id, sort FROM ".SQLPRE."document_relations WHERE element = '".$i_element."' AND slot = '".$i_slot."' AND error_page = '".$i_error."' ORDER BY sort");
    while($s = $fksdb->fetch($seitenss))
    {
        $countup ++;
        $update = $fksdb->query("UPDATE ".SQLPRE."document_relations SET sort = '".$countup."' WHERE id = '".$s->id."'");
    }
}
?>
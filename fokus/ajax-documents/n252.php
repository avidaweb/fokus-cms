<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || ($index != 'n252' && $index != 'n252a'))
    exit($user->noRights());
    
$id = $fksdb->save($_POST['id'], 1);
    
$doc = $fksdb->fetch("SELECT id, dversion_edit, gesperrt, timestamp_freigegeben, von, titel FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$doc) exit();
$dv_active = $fksdb->query("SELECT id, language FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."' AND id = '".$doc->dversion_edit."' LIMIT 1");
    
if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())) {}
    else exit($user->noRights());

parse_str($_POST['f'], $f);
$titel = ($f['titel']?$fksdb->save($f['titel']):$doc->titel);

$nsp = array();
foreach((array)$f['active'] as $s => $is)
{
    if(!$s || !$is)
        continue;
    $nsp[] = $fksdb->save($s);
}
    
if(!is_array($f['take_content']))
    $f['take_content'] = array();
    
    
$cfields = $api->getCustomFields();
$sp = array();
foreach((array)$f['sprache'] as $s1 => $arr)
{
    $lang = $fksdb->save($s1);
    
    if(!is_array($arr) || !$lang)
        continue;
        
    $sp[$lang]['titel'] = $fksdb->save($arr['titel']);
    $sp[$lang]['htitel'] = $fksdb->save($arr['htitel']);
    $sp[$lang]['desc'] = $fksdb->save($arr['desc']);
    $sp[$lang]['tags'] = $fksdb->save($arr['tags']);
    $sp[$lang]['url'] = $fksdb->save($arr['url']);
    
    if(is_array($cfields))
    {
        foreach($cfields as $k => $z)
            $sp[$lang][$k] = $fksdb->save($arr[$k]);
    }
}


$active = '';
if($index == 'n252a') $active = $fksdb->save($_POST['lan']);
if(!$active) $active = $dv_active->language;
if(!$active) $active = $standard_language;

foreach($nsp as $tsp)
{
    $copylan = $f['take_content'][$tsp];
    $dv_test = $fksdb->query("SELECT id FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND language = '".$tsp."' LIMIT 1");
    
    if($copylan && $copylan != $tsp && in_array($copylan, $nsp))
    {
        $dv_object = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND language LIKE '".$copylan."' ORDER BY id DESC LIMIT 1");
        
        $fksdb->insert("document_versions", array(
        	"dokument" => $id,
        	"timestamp" => $base->getTime(),
        	"timestamp_edit" => $base->getTime(),
        	"von" => $user->getID(),
        	"language" => $tsp,
        	"edit" => 1,
        	"klasse_inhalt" => $dv_object->klasse_inhalt
        ));
        $vid = $fksdb->getInsertedID();
        
        $ergebnis = $fksdb->query("SELECT * FROM ".SQLPRE."columns WHERE dokument = '".$doc->id."' AND dversion = '".$dv_object->id."'");
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
            
            $blQ = $fksdb->query("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$doc->id."' AND spalte = '".$row['id']."'");
            while($bl = $fksdb->fetchArray($blQ))
            {
                $fksdb->copy($bl, "blocks", array(
                    "vid" => Strings::createID(),
                    "dokument" => $id,
                    "dversion" => $vid,
                    "spalte" => $spalten_id
                ));
            }
        }
    }
    elseif(!$fksdb->count($dv_test))
    {
        $fksdb->insert("document_versions", array(
        	"dokument" => $id,
        	"timestamp" => $base->getTime(),
        	"von" => $user->getID(),
        	"language" => $tsp,
        	"edit" => 1,
        	"klasse_inhalt" => ""
        ));
        $vid = $fksdb->getInsertedID();
        
        $fksdb->insert("columns", array(
        	"dokument" => $id,
        	"dversion" => $vid,
        	"size" => 100
        ));
        $spalten_id = $fksdb->getInsertedID(); 
    
        $fksdb->update("columns", array(
            "vid" => $spalten_id
        ), array(
            "id" => $spalten_id
        ), 1);
    }
}

$dv = $fksdb->query("SELECT id FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND language = '".$active."' LIMIT 1");
if(!$fksdb->count($dv))
{
    $dv_object = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND id = '".$doc->dversion_edit."' LIMIT 1");
    
    $fksdb->insert("document_versions", array(
    	"dokument" => $id,
    	"timestamp" => $base->getTime(),
    	"von" => $user->getID(),
    	"language" => $active,
    	"edit" => 1,
    	"klasse_inhalt" => ""
    ));
    $vid = $fksdb->getInsertedID();
}

$dve = $fksdb->fetch("SELECT id FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND language = '".$active."' ORDER BY id DESC LIMIT 1");

if($index == 'n252')
{
    $fksdb->update("documents", array(
        "titel" => $titel,
        "dversion_edit" => $dve->id,
        "sprachen" => serialize($nsp),
        "sprachenfelder" => serialize($sp)
    ), array(
        "id" => $id
    ), 1);
}
else
{
    $fksdb->update("documents", array(
        "dversion_edit" => $dve->id,
    ), array(
        "id" => $id
    ), 1);
}

exit();
?>
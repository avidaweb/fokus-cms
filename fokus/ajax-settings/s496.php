<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

ignore_user_abort(true);
set_time_limit(0);

if(!$user->r('fks', 'pure'))
    exit($user->noRights());
    
if($index != 's496' )
    exit('wrong page');
    
$todo = $fksdb->save($_POST['todo']);
$nr = $fksdb->save($_POST['nr']);
    
parse_str($_POST['f'], $f);
if(!is_array($f) || !count($f) || !$todo || !$nr || $nr > $todo)
    exit('<li class="ifehler">no correct form submit</li>');
    
$progress = array('trash', 'docs_versions', 'docs_unused', 'pics_versions', 'pics_unused', 'pics_cache', 'livetalk', 'last_used', 'user_inactive', 'optimize');
$avaible = array('no_task');

foreach($progress as $pr)
{
    if($f[$pr])
        $avaible[] = $pr;
}

$task = $avaible[$nr];

if(!$task)
    exit('<li>error: no task on nr '.$nr.': '.$base->debug($avaible, false).'</li>');
  

if($task == 'trash')
{
    $affected = 0;
    
    $docs = $fksdb->query("SELECT id FROM ".SQLPRE."documents WHERE papierkorb = '1'");
    while($doc = $fksdb->fetch($docs))
    {
        $fksdb->query("DELETE FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."'");
        $affected += $fksdb->affected();   
        
        $fksdb->query("DELETE FROM ".SQLPRE."blocks WHERE dokument = '".$doc->id."'");
        $affected += $fksdb->affected();   
        
        $fksdb->query("DELETE FROM ".SQLPRE."columns WHERE dokument = '".$doc->id."'");
        $affected += $fksdb->affected();   
        
        $fksdb->query("DELETE FROM ".SQLPRE."document_relations WHERE dokument = '".$doc->id."'");
        $affected += $fksdb->affected();    
        
        $fksdb->query("DELETE FROM ".SQLPRE."feeds WHERE dokument = '".$doc->id."'");
        $affected += $fksdb->affected();     
        
        $fksdb->query("DELETE FROM ".SQLPRE."recent_items WHERE aid = '".$doc->id."' AND type = 'dokument'");
        $affected += $fksdb->affected();   
    }
    $fksdb->query("DELETE FROM ".SQLPRE."documents WHERE papierkorb = '1'");
    $affected += $fksdb->affected();
    
    /** ************* */
    
    $elements = $fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE papierkorb = '1'");
    while($element = $fksdb->fetch($elements))
    {
        $fksdb->query("DELETE FROM ".SQLPRE."document_relations WHERE element = '".$element->id."' AND element != '0'");
        $affected += $fksdb->affected();       
        
        $fksdb->query("DELETE FROM ".SQLPRE."feeds WHERE element = '".$element->id."'");
        $affected += $fksdb->affected(); 
    }
    $fksdb->query("DELETE FROM ".SQLPRE."elements WHERE papierkorb = '1'");
    $affected += $fksdb->affected();
    
    /** ************* */
    
    $users = $fksdb->query("SELECT id FROM ".SQLPRE."users WHERE papierkorb = '1'");
    while($user = $fksdb->fetch($users))
    {
        $fksdb->query("DELETE FROM ".SQLPRE."user_roles WHERE benutzer = '".$user->id."'");
        $affected += $fksdb->affected(); 
        
        $fksdb->query("DELETE FROM ".SQLPRE."livetalk WHERE benutzer = '".$user->id."'");
        $affected += $fksdb->affected(); 
        
        $fksdb->query("DELETE FROM ".SQLPRE."messages WHERE benutzer = '".$user->id."'");
        $affected += $fksdb->affected();
    }
    $fksdb->query("DELETE FROM ".SQLPRE."users WHERE papierkorb = '1'");
    $affected += $fksdb->affected();
    
    /** ************* */
    
    $roles = $fksdb->query("SELECT id FROM ".SQLPRE."roles WHERE papierkorb = '1'");
    while($role = $fksdb->fetch($roles))
    {
        $fksdb->query("DELETE FROM ".SQLPRE."user_roles WHERE rolle = '".$role->id."'");
        $affected += $fksdb->affected(); 
    }
    $fksdb->query("DELETE FROM ".SQLPRE."roles WHERE papierkorb = '1'");
    $affected += $fksdb->affected();
    
    /** ************* */
    
    $files = $fksdb->query("SELECT id FROM ".SQLPRE."files WHERE papierkorb = '1'");
    while($file = $fksdb->fetch($files))
    {
        $fksdb->query("DELETE FROM ".SQLPRE."file_versions WHERE stack = '".$file->id."'");
        $affected += $fksdb->affected(); 
    }
    $fksdb->query("DELETE FROM ".SQLPRE."files WHERE papierkorb = '1'");
    $affected += $fksdb->affected();
    
    /** ************* */
    
    $structures = $fksdb->query("SELECT id FROM ".SQLPRE."structures WHERE papierkorb = '1'");
    while($structure = $fksdb->fetch($structures))
    {
        $elements = $fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE struktur = '".$structure->id."'");
        while($element = $fksdb->fetch($elements))
        {
            $fksdb->query("DELETE FROM ".SQLPRE."document_relations WHERE element = '".$element->id."' AND element != '0'");
            $affected += $fksdb->affected();       
            
            $fksdb->query("DELETE FROM ".SQLPRE."feeds WHERE element = '".$element->id."'");
            $affected += $fksdb->affected(); 
        }
        $fksdb->query("DELETE FROM ".SQLPRE."elements WHERE papierkorb = '1'");
        $affected += $fksdb->affected();
    }
    $fksdb->query("DELETE FROM ".SQLPRE."structures WHERE papierkorb = '1'");
    $affected += $fksdb->affected();
    
    /** ************* */
    
    $fksdb->query("DELETE FROM ".SQLPRE."companies WHERE papierkorb = '1'");
    $affected += $fksdb->affected();
    
    $fksdb->query("DELETE FROM ".SQLPRE."responsibilities WHERE papierkorb = '1'");
    $affected += $fksdb->affected();
    
         
    /** finally delete trash database */
    $fksdb->query("DELETE FROM ".SQLPRE."recent_items WHERE papierkorb = '1'");
    
    echo '<li>'.$trans->__('<strong>%1</strong> Elemente aus dem Papierkorb wurden vollständig gelöscht.', false, array($affected)).'</li>';    
}
elseif($task == 'docs_unused')
{
    $affected = 0;
    
    $elements = array();
    $newsletter = array();
    $roles = array();
    
    $relations = $fksdb->rows("SELECT id, dokument FROM ".SQLPRE."document_relations", "dokument");
    
    if($f['docs_unused_dclass'])
        $elements = $fksdb->rows("SELECT id, klasse FROM ".SQLPRE."elements WHERE klasse != '' AND papierkorb = '0'", "klasse");
     
    if($f['docs_unused_task'] == 2)  
    {
        $nl = $fksdb->query("SELECT doks FROM ".SQLPRE."newsletters");
        while($nletter = $fksdb->fetch($nl))
        {
            $nl_docs = $base->db_to_array($nletter->doks);
            if(!count($nl_docs) || !is_array($nl_docs)) continue;
            
            foreach($nl_docs as $docid)
                $newsletter[] = $docid;
        }
    }
    
    $nl = $fksdb->query("SELECT fehler FROM ".SQLPRE."roles WHERE papierkorb = '0'");
    while($nletter = $fksdb->fetch($nl))
    {
        $nl_docs = $base->fixedUnserialize($nletter->fehler);
        if(!count($nl_docs) || !is_array($nl_docs)) continue;
        
        foreach($nl_docs as $docid)
            $roles[] = $docid;
    }
    
    $del_list = array();
    
    $docs = $fksdb->query("SELECT id, klasse FROM ".SQLPRE."documents");
    while($doc = $fksdb->fetch($docs))
    {
        if(!$f['docs_unused_dclass'] && $doc->klasse)
            continue;
            
        if(in_array($doc->id, $relations))
            continue;
            
        if(in_array($doc->id, $roles))
            continue;
            
        if($f['docs_unused_dclass'] && $doc->klasse && in_array($doc->klasse, $elements))
            continue;
            
        if($f['docs_unused_task'] == 2 && in_array($doc->id, $newsletter))
            continue;
            
        $del_list[] = $doc->id;
    }
    
    if(count($del_list))
    {        
        $del_string = trim(implode(',', $del_list), ',');
        
        $fksdb->query("DELETE FROM ".SQLPRE."document_versions WHERE dokument IN (".$del_string.")");
        $fksdb->query("DELETE FROM ".SQLPRE."blocks WHERE dokument IN (".$del_string.")");
        $fksdb->query("DELETE FROM ".SQLPRE."columns WHERE dokument IN (".$del_string.")");
        $fksdb->query("DELETE FROM ".SQLPRE."document_relations WHERE dokument IN (".$del_string.")");
        $fksdb->query("DELETE FROM ".SQLPRE."feeds WHERE dokument IN (".$del_string.")");
        $fksdb->query("DELETE FROM ".SQLPRE."recent_items WHERE aid IN (".$del_string.") AND type = 'dokument'");
        
        $fksdb->query("DELETE FROM ".SQLPRE."documents WHERE id IN (".$del_string.")");
        $affected = $fksdb->affected();
    }
    
    echo '<li>'.$trans->__('<strong>%1</strong> nicht mehr verwendete Dokumente wurden entfernt.', false, array($affected)).'</li>';  
}
elseif($task == 'docs_versions')
{
    $del_list = array();
    
    $affected = 0;
    $affected_docs = 0;
    
    $docs = $fksdb->query("SELECT id FROM ".SQLPRE."documents");
    while($doc = $fksdb->fetch($docs))
    {
        $v = array();
        
        $dversions = $fksdb->query("SELECT id, language FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."' AND timestamp_freigegeben != '0' AND aktiv = '0' ORDER BY timestamp DESC");
        while($dversion = $fksdb->fetch($dversions))
        {
            $v[$dversion->language] = ($v[$dversion->language] <= 1?2:($v[$dversion->language] + 1));
            
            if($f['docs_versions_task'] == 1)
            {
                if($v[$dversion->language] > 3)
                {
                    $del_list[] = $dversion->id;
                    $affected_docs ++;
                }
            }
            elseif($f['docs_versions_task'] == 2)
            {
                if($v[$dversion->language] > 10)
                {
                    $del_list[] = $dversion->id;
                    $affected_docs ++;
                }
            }
            elseif($f['docs_versions_task'] == 3)
            {
                if($v[$dversion->language] % 2 == 0)
                {
                    $del_list[] = $dversion->id;
                    $affected_docs ++;
                }
            }
        }
    }
    
    if(count($del_list))
    {        
        $del_string = trim(implode(',', $del_list), ',');
        
        $fksdb->query("DELETE FROM ".SQLPRE."blocks WHERE dversion IN (".$del_string.")");
        $fksdb->query("DELETE FROM ".SQLPRE."columns WHERE dversion IN (".$del_string.")");
        
        $fksdb->query("DELETE FROM ".SQLPRE."document_versions WHERE id IN (".$del_string.")");
        $affected = $fksdb->affected();
    }
    
    echo '<li>'.$trans->__('<strong>%1</strong> Versionen von %2 unterschiedlichen Dokumenten wurden entfernt.', false, array($affected, $affected_docs)).'</li>';  
}
elseif($task == 'pics_versions')
{
    $affected = 0;
    $entrys = array();
    
    $files = $fksdb->query("SELECT id FROM ".SQLPRE."files WHERE kat = '0'");
    while($file = $fksdb->fetch($files))
    {
        $fversions = $fksdb->query("SELECT id FROM ".SQLPRE."file_versions WHERE stack = '".$file->id."' ORDER BY timestamp DESC LIMIT 1, 9999");
        while($fversion = $fksdb->fetch($fversions))
            $entrys[] = $fversion->id;
    }
    
    if(count($entrys))
    {
        $fksdb->query("DELETE FROM ".SQLPRE."file_versions WHERE id IN (".trim(implode(",", $entrys), ',').")");
        $affected += $fksdb->affected();
    }
    
    echo '<li>'.$trans->__('<strong>%1</strong> alte Bild-Versionen wurden entfernt.', false, array($affected)).'</li>';  
}
elseif($task == 'pics_unused')
{
    $files = array();
    
    $filesQ = $fksdb->query("SELECT file, type FROM ".SQLPRE."file_versions WHERE ausrichtung != '0'");
    while($file = $fksdb->fetch($filesQ))
        $files[] = strtolower(trim($file->file.'.'.$file->type));
    
    $deleted = 0;
    
    $ordner = ROOT.'content/uploads/bilder';
    $handle = opendir($ordner);
    while($file = readdir($handle)) 
    {
        if($file == "." || $file == "..") 
            continue;
            
        $file = strtolower(trim(trim($file, '/')));
            
        if(in_array($file, $files))
            continue;
            
        if(Strings::strExists('cache_', $file))
            continue;
            
        if(unlink($ordner.'/'.$file))
            $deleted ++;
    }
    
    echo '<li>'.$trans->__('<strong>%1</strong> nicht mehr benötigte Bild-Dateien wurden vom Server entfernt.', false, array($deleted)).'</li>';        
}
elseif($task == 'pics_cache')
{
    $deleted = 0;
    
    $ordner = ROOT.'content/uploads/bilder';
    $handle = opendir($ordner);
    while($file = readdir($handle)) 
    {
        if($file == "." || $file == "..") 
            continue;
            
        $file = strtolower(trim(trim($file, '/')));
            
        if(!Strings::strExists('cache_', $file))
            continue;
            
        if(unlink($ordner.'/'.$file))
            $deleted ++;
    }
    
    echo '<li>'.$trans->__('<strong>%1</strong> Cache-Dateien (Thumbnails) wurden entfernt.', false, array($deleted)).'</li>';  
}
elseif($task == 'livetalk')
{
    $fksdb->query("DELETE FROM ".SQLPRE."livetalk");
    $affected += $fksdb->affected();
    
    echo '<li>'.$trans->__('<strong>%1</strong> Livetalk-Nachrichten wurden gelöscht.', false, array($affected)).'</li>';  
}
elseif($task == 'last_used')
{
    $fksdb->query("DELETE FROM ".SQLPRE."recent_items WHERE papierkorb = '0'");
    $affected += $fksdb->affected();
    
    echo '<li>'.$trans->__('<strong>%1</strong> "Zuletzt verwendet"-Einträge wurden gelöscht.', false, array($affected)).'</li>'; 
}
elseif($task == 'user_inactive')
{
    $affected = 0;
    
    $users = $fksdb->query("SELECT id FROM ".SQLPRE."users WHERE status = '1'");
    while($user = $fksdb->fetch($users))
    {
        $fksdb->query("DELETE FROM ".SQLPRE."user_roles WHERE benutzer = '".$user->id."'");        
        $fksdb->query("DELETE FROM ".SQLPRE."livetalk WHERE benutzer = '".$user->id."'");        
        $fksdb->query("DELETE FROM ".SQLPRE."messages WHERE benutzer = '".$user->id."'");
    }
    $fksdb->query("DELETE FROM ".SQLPRE."users WHERE status = '1'");
    $affected = $fksdb->affected();
    
    echo '<li>'.$trans->__('<strong>%1</strong> inaktive Benutzer wurden entfernt.', false, array($affected)).'</li>'; 
}
elseif($task == 'optimize')
{
    $alltables = $fksdb->query("SHOW TABLES");
    $optimized = 0;

    while($table = $fksdb->fetchAssoc($alltables))
    {
        foreach($table as $db => $tablename)
        {
            $fksdb->query("OPTIMIZE TABLE ".$tablename);
            $optimized ++;
        }
    }
    
    echo '<li>'.$trans->__('<strong>%1</strong> Datenbank-Tabellen wurden optimiert', false, array($optimized)).'</li>';
}


if($todo == $nr)
{
    echo '
    <li><br /></li>
    <li>'.$trans->__('Datenbankgröße nach Bereinigung:').' '.$fksdb->getDatabaseSize().'</li>
    <li>'.$trans->__('Alle Vorgänge wurden <strong>erfolgreich abgeschlossen</strong>. Sie können dieses Fenster nun schließen.').'</li>';
}
   
exit();
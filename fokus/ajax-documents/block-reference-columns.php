<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');

if(!$user->isAdmin())
    exit();
 
if(!$block_reference)
{  
    parse_str($_POST['f'], $f); 
    $c = (object)$f;
}

$doc = $fksdb->fetchSelect("documents", array("id", "dversion_edit"), array(
    "id" => $c->document
), "", 1); 
if(!$doc && !$block_reference) exit($trans->__('Es wurde kein Dokument geladen.'));
    
$dversion_edit = $fksdb->fetchSelect("document_versions", array("language"), array(
    "id" => $doc->dversion_edit
), "", 1);

$dversion = $fksdb->fetchSelect("document_versions", array("id"), array(
    "dokument" => $doc->id,
    "language" => $dversion_edit->language,
    "aktiv" => 1
), "id DESC", 1); 

    
$columns = $fksdb->select("columns", array("vid"), array(
    "dokument" => $doc->id,
    "dversion" => $dversion->id
), "sort ASC");

if(!$fksdb->count($columns) && !$block_reference) exit($trans->__('Es wurden keine Spalten in diesem Dokument gefunden.'));
    
$column_count = 0;
    
while($column = $fksdb->fetch($columns))
{
    echo '
    <div class="column item">
        <input type="radio" name="column" value="'.$column->vid.'" id="c_column_'.$column->vid.'"'.($c->column == $column->vid || $fksdb->count($columns) == 1?' checked':'').' />
        <label for="c_column_'.$column->vid.'">Spalte '.($column_count + 1).'</label>
    </div>'; 
    
    $column_count ++;   
}
?>
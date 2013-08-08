<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');

if(!$user->isAdmin())
    exit();

$c = $base->db_to_array($row->html);
if(!is_array($c)) $c = array();
$c = (object)$c;

if($c->document)
{
    $doc = $fksdb->fetchSelect("documents", array("id", "titel"), array(
        "id" => $c->document
    ), "", 1);
}

$block_reference = true;

echo '
<form id="dreference" class="dreference">
<div class="box step_document">
    <h2 class="calibri">'.$trans->__('Quell-Dokument auswählen.').'</h2>
    <p>'.$trans->__('Dieses Referenzelement bezieht seine Inhalte aus folgendem Dokument:').'</p>
    
    <p class="choosen">
        '.(!$c->document?'<em>'.$trans->__('Noch kein Dokument ausgewählt').'</em>':$doc->titel).'
    </p>
    <br />
    <input type="hidden" name="document" value="'.intval($c->document).'" />
    <button class="choose">'.$trans->__('Dokument auswählen').'</button>
</div>

<div class="box step_column"'.(!$c->document?' style="display:none;"':'').'>
    <h2 class="calibri">'.$trans->__('Spalte auswählen.').'</h2>
    
    <div class="columns">';
        if($c->document)
        {
            include('block-reference-columns.php');       
        }
    echo '
    </div>
</div>

<div class="box step_content"'.(!$c->document || !$c->column?' style="display:none;"':'').'>
    <h2 class="calibri">'.$trans->__('Inhalte auswählen.').'</h2>
    
    <div class="contents">';
        if($c->document && $c->column)
        {
            include('block-reference-blocks.php');       
        }
    echo '
    </div>
</div>
</form>';
?>
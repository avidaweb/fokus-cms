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


$column = $fksdb->fetchSelect("columns", array("id", "vid"), array(
    "dokument" => $doc->id,
    "dversion" => $dversion->id,
    "vid" => $c->column
), "", 1);
if(!$column && !$block_reference) exit($trans->__('Es wurde keine Spalte geladen.'));
    
$blocks = $fksdb->select("blocks", array("id", "vid", "html", "type", "teaser", "bildid", "bildw", "bildwt"), array(
    "dokument" => $doc->id,
    "dversion" => $dversion->id,
    "spalte" => $column->id
), "sort ASC");
if(!$fksdb->count($blocks) && !$block_reference) exit($trans->__('Es wurden keine Inhaltselemente in dieser Spalte gefunden.'));


echo '
<div class="show_hide">
    <p>
        <input type="radio" name="show" id="c_blocks_show_0" value="0"'.(!$c->show?' checked':'').' />
        <label for="c_blocks_show_0">'.$trans->__('Ich möchte Inhalte markieren, die <strong>nicht</strong> angezeigt werden sollen.').'</label>
    </p>
    <p>
        <input type="radio" name="show" id="c_blocks_show_1" value="1"'.($c->show?' checked':'').' />
        <label for="c_blocks_show_1">'.$trans->__('Ich möchte Inhalte markieren, die angezeigt werden sollen.').'</label>
    </p>
</div>';
    
while($block = $fksdb->fetch($blocks))
{
    echo '
    <div class="block item">
        <div class="p l">
            '.$base->getBlockByID($block->type, 'de').'
        </div>
        <div class="p m">
            '.$base->block_preview($block->html, $block->type, $block->teaser, array('id' => $block->bildid, 'w' => $block->bildw, 'wt' => $block->bildwt)).' 
        </div>
        <div class="p r">
            <input type="checkbox" name="block[]" id="c_block_'.$block->vid.'" value="'.$block->vid.'"'.(in_array($block->vid, (array)$c->block)?' checked':'').' />
            <label for="c_block_'.$block->vid.'">
                <span class="is_hide"'.($c->show?' style="display:none;"':'').'>'.$trans->__('nicht anzeigen').'</span>
                <span class="is_show"'.(!$c->show?' style="display:none;"':'').'>'.$trans->__('anzeigen').'</span>
            </label>
        </div>
    </div>';  
}
?>
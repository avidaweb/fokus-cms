<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n266')
    exit($user->noRights());

$id = $fksdb->save($_GET['id'], 1);
$element = $fksdb->save($_GET['element'], 1);
$a = $fksdb->save($_GET['a']);
$st = $fksdb->save($_GET['st'], 1);

$block = $fksdb->fetch("SELECT teaser FROM ".SQLPRE."blocks WHERE id = '".$id."' LIMIT 1");
$teaser = $base->fixedUnserialize($block->teaser);

$eQ = $fksdb->query("SELECT id, titel FROM ".SQLPRE."elements WHERE element = '".$element."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' ORDER BY sort");
while($e = $fksdb->fetch($eQ))
{
    echo '
    <div class="stt">
        <strong>'.Strings::cut($e->titel, 60).'</strong>
        <p>
            <input type="checkbox" id="teaser_hide_'.$e->id.'" name="ns['.$e->id.']" value="1"'.($teaser['ns'][$e->id]?' checked="checked"':'').' />
            <label for="teaser_hide_'.$e->id.'">'.($st?$trans->__('Anzeigen'):$trans->__('Nicht anzeigen')).'</label>
        </p>
    </div>';
}
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n265sm')
    exit($user->noRights());

$id = $fksdb->save($_GET['id'], 1);
$block = $fksdb->save($_GET['block']);
$recht = $fksdb->save($_GET['r']);
$hide = $fksdb->save($_GET['hide']);

$doc = $fksdb->fetch("SELECT dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, language FROM ".SQLPRE."document_versions WHERE id = '".$doc->dversion_edit."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())) {}
else exit($user->noRights());

$b = $fksdb->fetch("SELECT html FROM ".SQLPRE."blocks WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
$sm = $base->fixedUnserialize($b->html);
if(!is_array($sm)) $sm = array();
if(!is_array($sm['el'])) $sm['el'] = array();

function level($fksdb, $base, $trans, $dve, $recht, $hide, $sm, $parents, $parentnositemap)
{
    $elQ = $fksdb->query("SELECT id, titel, klasse, produkt, url, rollen, sprachen, nositemap FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".$base->getTime()."' AND (bis = '0' OR bis >= '".$base->getTime()."')) AND (klasse != '' OR sprachen LIKE '%\"".$dve->language."\"%') AND element = '".$parents."' ORDER BY sort ASC");
    if($fksdb->count($elQ) && $parents)
        echo '<ul>';

    while($el = $fksdb->fetch($elQ))
    {
        $sp = $base->fixedUnserialize($el->sprachen);
        $ro = $base->fixedUnserialize($el->rollen);
        if(!is_array($ro)) $ro = array();

        $ttitel = $sp[$dve->language]['titel'];
        if($el->klasse)
        {
            $fk = $base->open_dklasse('../content/dklassen/'.$el->klasse.'.php');
            $ttitel = '<em>'. $trans->__('Dokumentenklasse') .'</em> &quot;'.Strings::cut($fk['name'], 25).'&quot;';
        }

        if($parentnositemap)
            $el->nositemap = 1;

        echo '
        <li'.($el->nositemap?' class="nositemap"':'').'>
            <span class="C">
                <span class="A'.(count($ro) >= 1 && !in_array($recht, $ro)?' F':'').'">'.$ttitel.'</span>
                <span class="B">
                    <input type="checkbox"'.($el->nositemap?' disabled':'').' name="el[]" value="'.$el->id.'" id="s_el_'.$el->id.'"'.(in_array($el->id, $sm['el']) || $el->nositemap?' checked':'').' />
                    <label for="s_el_'.$el->id.'">'. $trans->__('Nicht anzeigen') .'</label>
                </span>
            </span>';
            echo level($fksdb, $base, $trans, $dve, $recht, $hide, $sm, $el->id, $el->nositemap);
        echo '
        </li>';
    }

    if($fksdb->count($elQ) && $parents)
        echo '</ul>';
}

echo '
<ul class="sitemap">';
    echo level($fksdb, $base, $trans, $dve, $recht, $hide, $sm, 0, 0);
echo '</ul>';
?>
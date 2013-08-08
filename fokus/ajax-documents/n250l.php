<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n250l')
    exit($user->noRights());

$id = $fksdb->save($_GET['id'], 1);

$docs = $fksdb->query("SELECT sprachen, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
while($doc = $fksdb->fetch($docs))
{
    $dve = $fksdb->fetch("SELECT language FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND id = '".$doc->dversion_edit."' LIMIT 1");

    $spr = $base->fixedUnserialize($doc->sprachen);
    if(!is_array($spr)) $spr = array();

    if($base->getActiveLanguagesCount() <= 1)
        continue;

    foreach($base->getActiveLanguages() as $sp)
    {
        if(!in_array($sp, $spr))
            continue;

        echo '
        <a rel="'.$sp.'"'.($sp != $dve->language?' class="aoben"':'').'>
            <img src="'.$trans->getFlag($sp, ($sp != $dve->language?'2':'1')).'" alt="" />
        </a>';
    }
}
?>
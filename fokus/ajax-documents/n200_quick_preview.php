<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if($index != 'n200_quick_preview')
    exit();

$id = $fksdb->save($_POST['id']);
$td = $fksdb->save($_POST['td']);
$bereich = $fksdb->save($_POST['bereich']);
$justframe = $fksdb->save($_POST['justframe']);

// Newsletter
$newsletter = $_POST['newsletter'];
if($newsletter)
{
    $newF = array();
    parse_str($newsletter, $s);

    if(count($s['Ksd']))
    {
        foreach($s['Ksd'] as $ss)
            $newF[] = $ss;
    }

    $imp = implode('-', $newF);
}

$newsletter2 = $fksdb->save($_POST['newsletter2']);
if($newsletter2)
{
    $newsletter = true;
    $imp = $newsletter2;
}

if(!$newsletter && !$user->r('dok'))
    exit($user->noRights());
if($newsletter && !$user->r('kom'))
    exit($user->noRights());
//

$dok = $fksdb->fetch("SELECT id, dversion_edit, titel FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT language FROM ".SQLPRE."document_versions WHERE dokument = '".$dok->id."' AND id = '".$dok->dversion_edit."' LIMIT 1");

if(!$td) $td = 'index';

$frameurl = '../index.php?titel='.urlencode($dok->titel).($newsletter?'&newsletter='.$imp:'').'&language='.$dve->language.'&td='.$td.'&bereich='.$bereich.'&vorschau='.$dok->id.'&dk='.($row->klasse?$row->id:0).'&produkt='.($row->produkt?$row->id:0);

if($justframe)
{
    echo $frameurl;
}
else
{
    echo '
    <div id="topbar">';
        if(count($base->getActiveTemplateConfig('files')))
        {
            echo '
            <p>
                <select name="td">
                    <option value="index">Standard Template-Datei (index.php)</option>';
                        foreach($base->getActiveTemplateConfig('files') as $cn => $ck)
                            echo '<option value="'.$ck.'">'.(is_numeric($cn)?$ck:($cn).' ('.$ck.')').'</option>';
                echo '
                </select>
            </p>';
        }
        if(count($base->getActiveTemplateConfig('slots')))
        {
            echo '
            <p>
                <select name="bereich">
                    <option value="">Im Inhaltsbereich anzeigen</option>';
                        foreach($base->getActiveTemplateConfig('slots') as $cn => $ck)
                            echo '<option value="'.$cn.'">Im Slot '.($ck['name']).' ('.$cn.') anzeigen</option>';
                echo '
                </select>
            </p>';
        }

        echo '
        <a class="close">'. $trans->__('schließen') .'</a>
        <a class="close newtab" href="'.$frameurl.'" target="fkspreview">'. $trans->__('im eigenen Fenster anzeigen') .'</a>
    </div>
    <iframe frameborder="0" src="'.$frameurl.'"></iframe>';
}
?>
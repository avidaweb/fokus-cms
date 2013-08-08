<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('fks', 'pure'))
    exit($user->noRights());
    
if($index != 's495' )
    exit('wrong page');
    
parse_str($_POST['f'], $f);
if(!is_array($f) || !count($f))
    exit('<div class="ifehler">no correct form submit</div>');
    
$progress = array('trash', 'docs_versions', 'docs_unused', 'pics_versions', 'pics_unused', 'pics_cache', 'livetalk', 'last_used', 'user_inactive', 'optimize');

$todo = 0;
foreach($progress as $pr)
{
    if($f[$pr])
        $todo ++;
}
    
echo '
<h1>'.$trans->__('fokus wird aufgeräumt...').'</h1>

<div class="box infobox calibri">
    '.$trans->__('Bitte haben Sie einen Moment Geduld: fokus wird nun aufgeräumt. Sie sollten dieses Fenster erst schließen, wenn alle Operationen vollständig abgeschlossen sind.').'
</div>

<div class="box pure">
    <div class="progress" data-min="1" data-max="'.$todo.'"></div>
    
    <h2 class="calibri">'.$trans->__('Fortschritt.').'</h2>
    <ul class="info">
        <li>'.$trans->__('Datenbankgröße vor Bereinigung:').' '.$fksdb->getDatabaseSize().'</li>
        <li><br /></li>
    </ul>
</div>

<div class="box_save" style="display: block;">
    <input type="button" value="'.$trans->__('schließen').'" class="bs1" /> 
</div>';
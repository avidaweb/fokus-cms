<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');
require_once(ROOT.'inc/classes.backend/class.apps.php');

$id = $fksdb->save($_POST['id']);
$save = $fksdb->save($_POST['save']);
$app = new Apps($classes, $id);

if(!$app->isAvaible())
    exit('<div class="ifehler">'.$trans->__('Die aufgerufene fokus App &quot;'.$id.'&quot; wurde nicht gefunden').'</div>');
    
if(!$app->checkRights())
    exit($user->noRights($app->data('title')));
    
if($save)
{
    parse_str($_POST['f'], $form);
    if(!count($form))
        exit();

    $hinputs = (array)$form['fks-app-hidden-inputs'];
    if(!count($hinputs))
        exit();

    $allready_ins = array();

    foreach($hinputs as $iname)
    {
        if(Strings::strExists('[', $iname))
        {
            $iname_arr = explode('[', $iname);
            $iname = $iname_arr[0];
        }

        if(in_array($iname, $allready_ins))
            continue;

        $value = null;
        if(isset($form[$iname]))
            $value = $form[$iname];

        $api->setStorage($iname, $value, $id);
        $allready_ins[] = $iname;
    }
        
    exit();    
}
    
echo '
<h1>'.$app->data('title').'</h1>

'.($app->data('autosave')?'<form class="autosave">':'').'

<div class="hidden-inputs"></div>

<div class="box fks-app" id="app-'.$id.'" data-js="'.trim($app->data('js')).'" data-css="'.trim($app->data('css')).'" data-width="'.$app->data('width').'">';
    echo $app->getContent();
echo '
</div>

'.($app->data('autosave')?'</form>':'').'';

if($app->data('autosave'))
{
    echo '
    <div class="box_save" style="display:block;">
        <input type="submit" value="'.$trans->__('abbrechen').'" class="bs1" /> 
        <input type="submit" value="'.$trans->__('speichern').'" class="bs2" />
    </div>';
}
?>
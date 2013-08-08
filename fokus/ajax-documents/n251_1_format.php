<?php 
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251_1_format')
    exit($user->noRights());
     
$id = $fksdb->save($_POST['id'], 1);

$doc = $fksdb->fetch("SELECT id, von, css_klasse FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$doc)
    exit('<div class="ifehler">'.$trans->__('Dokument nicht gefunden.').'</div>');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())) {}
else exit($user->noRights());

if(!is_array($base->getActiveTemplateConfig('classes')) || !$cssk)
    exit('<div class="ifehler">'.$trans->__('Keine Formatierungen hinterlegt oder keine Zugriffsrechte').'</div>');

echo '
<h1>'.$trans->__('Dokument formatieren.').'</h1>

<div class="box">
    <p class="introtext">
        '.$trans->__('Im Folgenden können Sie dem Dokument beliebig viele vordefinierte Formatierungsstile zuweisen. Konkret handelt es sich dabei um vom Entwickler festgelegte CSS-Klassen.').'
    </p>
</div>

<div class="box">';
    $fstile = '';
    foreach($base->getActiveTemplateConfig('classes') as $class => $op)
    {
        if($op['restriction'] && $op['restriction'] != 'none' && !Strings::strExists('document', $op['restriction'], false))
            continue;
        if(!$op['name'])
            continue;
         
        $checked = (Strings::strExists($class.' ', ' '.$doc->css_klasse.' ')?true:false);
        $cslug = $base->slug($class);
            
        $fstile .= '
        <p>
            <input type="checkbox" name="classes[]" value="'.$class.'" id="ccsdlass_'.$cslug.'"'.($checked?' checked':'').' />
            <label for="ccsdlass_'.$cslug.'">'.$op['name'].'</label>
        </p>';
    } 
    
    if($fstile)
    {
        echo '
        <form class="css_document">
            '.$fstile.'
        </form>';
    }
    else
    {
        echo $trans->__('Es wurden im System keine CSS-Klassen hinterlegt, die einem Dokument zugeordnet werden könnten');    
    }
echo '
</div>

<div class="box_save">
    <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
    <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
</div>';

exit();
?>
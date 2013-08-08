<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if($index != 'n660' || (!$user->r('kom', 'pinnwand') && !$user->r('kom', 'pinnwandedit')))
    exit($user->noRights());

$tbenutzer = $base->user($base->getOpt('notiz_von'), ' ', 'vorname', 'nachname');
$noedit = ($base->getOpt('notiz_time') > time() - 90 && $base->getOpt('notiz_von') != $user->getID()?true:false);

if(!$user->r('kom', 'pinnwandedit'))
    $noedit = true;

echo '
<h1>'.$trans->__('Pinnwand.').'</h1>

<div id="pinnwand">
    <div class="box info"'.($base->getOpt('notiz_von')?'':' style="display:none;"').'>
        '.($noedit?$trans->__('Die Pinnwand wird momentan von %1 bearbeitet und ist somit für andere Benutzer gesperrt.', false, array($tbenutzer))
        :$trans->__('Die Pinnwand wurde zuletzt von %1 am %2 um %3 Uhr bearbeitet.', false, array($tbenutzer, date('d.m.y', $base->getOpt('notiz_time')), date('H:i', $base->getOpt('notiz_time'))))).'
    </div>
    <div class="box">
        <textarea'.($noedit?' disabled="disabled"':'').'>'.base64_decode($base->getOpt('notiz')).'</textarea>
    </div>
    '.(!$noedit?'
    <div class="box_save">
        <input type="button" class="bs1" value="'.$trans->__('schließen').'" />
        <input type="button" class="bs2" value="'.$trans->__('speichern').'" />
    </div>':'').'
</div>';
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok', 'ezsb') || $index != 'n280')
    exit($user->noRights());

echo '
<div class="zsbkats">
    <h1>'. $trans->__('Zuständigkeiten verwalten.') .'</h1>

    <div class="box">
        '. $trans->__('In diesem Bereich können Sie verschiedene Zuständigkeitsbereiche anlegen. Diese Zuständigkeitsbereiche können Sie im Bereich &quot;Rollen & Rechte&quot; den entsprechenden Benutzern zuweisen, damit diese Dokumente für diese Bereiche anlegen können und / oder Dokumente in diesem Bereich freigeben können.') .'

        <div class="zsbT">
            <table id="zsb"><tr><td><img src="images/loading_white.gif" alt="'. $trans->__('Bitte warten.. Inhalt wird geladen..') .'" class="ladebalken" /></td></tr></table>
        </div>

        <table class="zsb_add">
            <tr>
                <td>'. $trans->__('Name des neuen Zuständigkeitsbereiches:') .'</td>
                <td><input type="text" class="name_zsb" /></td>
                <td><button class="sub_zsb">'. $trans->__('Zuständigkeitsbereich anlegen') .'</td>
            </tr>
        </table>
    </div>
</div>';
?>
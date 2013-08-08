<?php
if(!$user->r('fks', 'opt') || $index != 'errors')
    exit($user->noRights());

$fehlerm = file_get_contents(ROOT.'content/export/fehler.txt');
        
echo '
<h1>'.$trans->__('Systemfehler.').'</h1>

<div class="box">
    <p>
        '.$trans->__('In diesem Bereich werden Systemfehler gespeichert. Falls etwas nicht korrekt funktionieren sollte, können Sie ihrem fokus Partner oder dem fokus Entwicklerteam folgende Fehlermeldungen übermitteln.').'
    </p>
    '.(!$fehlerm?'
    <p>
        <strong>'.$trans->__('Es wurden keine Systemfehler gefunden :)').'</strong>
    </p>':'
    <textarea>'.$fehlerm.'</textarea>
    <a>'.$trans->__('Liste leeren').'</a>').'
</div>';
?>
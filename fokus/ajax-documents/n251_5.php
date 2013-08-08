<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251')
    exit($user->noRights());
    
echo '
<p>
    '. $trans->__('Mit der Funktion &quot;Zeitsprung&quot; können Sie die &auml;lteren Versionen ihres Dokumentes einsehen und bei Bedarf wiederverwenden. Dazu können Sie einfach durch die Versionen &quot;blättern&quot; und anschließend die gewünschte Version &quot;zur Bearbeitung laden&quot;. In diesem Fall werden alle Einstellungen, Inhalte und Bilder aus dieser Version geladen, damit Sie diese mit oder ohne Änderungen neu zur Freigabe einreichen können. Die geladene Version bleibt davon unberührt.') .'
</p>

<div id="zeitsprung"></div>';
?>
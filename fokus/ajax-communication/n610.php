<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if((!$user->r('kom', 'nledit') && !$user->r('kom', 'nlsend')) || !$suite->rm(5) || $index != 'n610')
    exit($user->noRights());

echo '
<h1>'.$trans->__('Newsletter.').'</h1>

<div class="box newsletter_neu">
    '.($user->r('kom', 'nledit')?'<a class="inc_communication" id="n615" rel="0">'.$trans->__('neuen Newsletter anlegen').'</a>':'').'
</div>
<div class="box newsletterov">
    <h2 class="calibri">'.$trans->__('Bereits angelegte Newsletter.').'</h2>

    <div id="v_newsletter"><img src="images/loading.gif" alt="loading..." class="ladebalken" /></div>
</div>';
?>
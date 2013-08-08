<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'livetalk') || !$suite->rm(10) || $index != 'n650')
    exit($user->noRights());

$lvcount = $fksdb->count("SELECT id FROM ".SQLPRE."livetalk LIMIT 10");

echo '
<h1>'.$trans->__('Livetalk.').'</h1>

<div class="box livetalk" id="livetalk">
    <p class="alt">
        '.($lvcount >= 10?'<a>'.$trans->__('+ ältere Updates anzeigen').'</a>':'').'
    </p>
</div>

<div class="box livetalk" id="new_livetalk">
    <div class="LL">
        <img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
    </div>
    <div class="LR">
        <textarea></textarea>
        <button>'.$trans->__('Update schreiben').'</button>
    </div>
</div>';
?>
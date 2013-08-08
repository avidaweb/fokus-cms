<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n260_gmaps_pos')
    exit($user->noRights());

echo '
<h1>'. $trans->__('Breiten- &amp; Längengrad ermitteln.') .'</h1>

<div class="box">
    <p>
        '. $trans->__('Straße, Ort, Land eingeben:') .'<br />
        <input type="text" name="addr" />
        <button>'. $trans->__('ermitteln') .'</button>
    </p>
    <p class="erg"></p>
</div>

<div class="box_save">
    <input type="submit" class="bs1" value="'. $trans->__('verwerfen') .'" />
    <input type="submit" class="bs2" value="'. $trans->__('übernehmen') .'" />
</div>';
?>
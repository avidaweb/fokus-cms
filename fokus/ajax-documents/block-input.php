<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

echo '
<div class="box">
    <div id="editor">
        <input class="nur_wert" type="text" id="text" value="'.$row->html.'" />
    </div>
</div>';
?>
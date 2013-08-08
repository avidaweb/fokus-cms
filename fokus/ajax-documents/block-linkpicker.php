<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$c = $base->db_to_array($row->html);
if(!is_array($c)) $c = array();
$c = (object)$c;

echo '
<form id="urllinkpicker" class="linkpicker">
    <input type="hidden" name="href" value="'.$c->href.'" />
    <input type="hidden" name="email" value="'.$c->email.'" />
    <input type="hidden" name="element" value="'.$c->element.'" />
    <input type="hidden" name="document" value="'.$c->document.'" />
    <input type="hidden" name="file" value="'.$c->file.'" />

    <input type="hidden" name="text" value="'.$c->text.'" />
    <input type="hidden" name="title" value="'.$c->title.'" />
    <input type="hidden" name="classes" value="'.$c->classes.'" />
    <input type="hidden" name="target" value="'.$c->target.'" />
    <input type="hidden" name="power" value="'.$c->power.'" />
</form>';
?>;
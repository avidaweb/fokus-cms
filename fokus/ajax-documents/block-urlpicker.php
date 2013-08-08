<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$c = $base->db_to_array($row->html);
if(!is_array($c)) $c = array();
$c = (object)$c;

echo '
<form id="urllinkpicker" class="urlpicker">
    <input type="hidden" name="href" value="'.$c->href.'" />
    <input type="hidden" name="email" value="'.$c->email.'" />
    <input type="hidden" name="element" value="'.$c->element.'" />
    <input type="hidden" name="document" value="'.$c->document.'" />
    <input type="hidden" name="file" value="'.$c->file.'" />

    <input type="hidden" name="text" value="" />
    <input type="hidden" name="title" value="" />
    <input type="hidden" name="classes" value="" />
    <input type="hidden" name="target" value="" />
    <input type="hidden" name="power" value="" />
</form>';
?>
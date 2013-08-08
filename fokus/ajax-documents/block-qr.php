<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$c = $base->fixedUnserialize($row->html);
if(!is_array($c)) $c = array();
$c = (object)$c;

echo '
<form id="qrcode" class="dcomments">
<div class="box">
    <h2 class="calibri">'. $trans->__('QR-Code generieren.') .'</h2>
    <table>
        <tr>
            <td class="a">'. $trans->__('Ziel-URL des QR-Codes') .'</td>
            <td class="b">
                <input type="text" name="url" value="'.$c->url.'" />
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Gr&ouml;&szlig;e des QR-Codes') .'</td>
            <td class="b">
                <input type="text" name="size" value="'.($c->size?$c->size:150).'" class="size" /> '. $trans->__('Pixel') .'
            </td>
        </tr>
    </table>
</div>
</form>';
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$c = $base->fixedUnserialize($row->html);
if(!is_array($c)) $c = array();
$c = (object)$c;

if(!$c->saved)
{
    $c->label_name_text = $trans->__('Name:');
    $c->label_password_text = $trans->__('Passwort:');
    $c->label_name_position = $trans->__('before');
    $c->label_password_position = $trans->__('before');
    $c->submit_text = $trans->__('Anmelden');
    $c->submit_logout_text = $trans->__('Abmelden');
}        

echo '
<form id="dlogin" class="dcomments">
<div class="box">
    <input type="hidden" name="saved" value="true" />
    <h2 class="calibri">'. $trans->__('Anmelde-Vorgang') .'</h2>
    <table class="firstones">
        <tr>
            <td class="a">'. $trans->__('Aktion nach erfolgreichem Anmelden') .'</td>
            <td class="b">
                <p>
                    <input type="radio" class="checkbox" name="success_go" id="succes_go_a" value="0"'.(!$c->success_go?' checked="checked"':'').' />
                    <label for="succes_go_a">'. $trans->__('Eine Text-Meldung ausgeben') .'</label>
                </p>
                <p>
                    <input type="radio" class="checkbox" name="success_go" id="succes_go_b" value="1"'.($c->success_go?' checked="checked"':'').' />
                    <label for="succes_go_b">'. $trans->__('Auf anderes Strukturelement weiterleiten') .'</label>
                </p>
            </td>
        </tr>
        <tr class="success_go_a"'.($c->success_go?' style="display:none;"':'').'>
            <td class="a">'. $trans->__('Text nach Anmelden') .'</td>
            <td class="b">
                <textarea name="success_text">'.$c->success_text.'</textarea>
            </td>
        </tr>
        <tr class="success_go_b"'.(!$c->success_go?' style="display:none;"':'').'>
            <td class="a">'. $trans->__('Weiterleitung nach Anmelden') .'</td>
            <td class="b">
                <button class="ele_choose">Strukturelement ausw&auml;hlen</button>
                <p class="ele_choosen">'.($c->success_forwarding?$fksdb->data("SELECT titel FROM ".SQLPRE."elements WHERE id = '".$c->success_forwarding."' LIMIT 1", "titel"):'<em>Kein Element gew&auml;hlt</em>').'</p>
                <input type="hidden" name="success_forwarding" value="'.($c->success_go?$c->success_forwarding:'').'" />
            </td>
        </tr>
    </table>
    
    <h2 class="calibri">'. $trans->__('Abmelde-Vorgang') .'</h2>
    <table class="firstones">
        <tr>
            <td class="a">'. $trans->__('Aktion nach erfolgreichem Abmelden') .'</td>
            <td class="b">
                <p>
                    <input type="radio" class="checkbox" name="success_logout_go" id="succes_logout_go_a" value="0"'.(!$c->success_logout_go?' checked="checked"':'').' />
                    <label for="succes_logout_go_a">'. $trans->__('Eine Text-Meldung ausgeben') .'</label>
                </p>
                <p>
                    <input type="radio" class="checkbox" name="success_logout_go" id="succes_logout_go_b" value="1"'.($c->success_logout_go?' checked="checked"':'').' />
                    <label for="succes_logout_go_b">'. $trans->__('Auf anderes Strukturelement weiterleiten') .'</label>
                </p>
            </td>
        </tr>
        <tr class="success_logout_go_a"'.($c->success_logout_go?' style="display:none;"':'').'>
            <td class="a">'. $trans->__('Text nach erfolgreichem Abmelden') .'</td>
            <td class="b">
                <textarea name="success_logout_text">'.$c->success_logout_text.'</textarea>
            </td>
        </tr>
        <tr class="success_logout_go_b"'.(!$c->success_logout_go?' style="display:none;"':'').'>
            <td class="a">'. $trans->__('Weiterleitung nach Abmelden') .'</td>
            <td class="b">
                <button class="ele_choose">'. $trans->__('Strukturelement auswählen') .'</button>
                <p class="ele_choosen">'.($c->success_logout_forwarding?$fksdb->data("SELECT titel FROM ".SQLPRE."elements WHERE id = '".$c->success_logout_forwarding."' LIMIT 1", "titel"):'<em>Kein Element gew&auml;hlt</em>').'</p>
                <input type="hidden" name="success_logout_forwarding" value="'.($c->success_logout_go?$c->success_logout_forwarding:'').'" />
            </td>
        </tr>
    </table>
</div>
<div class="box">
    <h2 class="calibri">'. $trans->__('Eingabefeld &quot;Name&quot;') .'</h2>
    <table>
        <tr>
            <td class="a">'. $trans->__('Beschriftung des Eingabefeldes') .'</td>
            <td class="b">
                <input type="text" name="label_name_text" value="'.$c->label_name_text.'" />
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Position des Eingabefeldes') .'</td>
            <td class="b">
                <select name="label_name_position">
                    <option value="before"'.($c->label_name_position == 'before'?' selected="selected"':'').'>'. $trans->__('Hinter der Beschriftung') .'</option>
                    <option value="after"'.($c->label_name_position == 'after'?' selected="selected"':'').'>'. $trans->__('Vor der Beschriftung') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Zeilenumbruch') .'</td>
            <td class="b">
                <input type="checkbox" class="checkbox" value="true" name="label_name_br" id="label_name_br"'.($c->label_name_br?' checked="checked"':'').' />
                <label for="label_name_br"> '. $trans->__('Zeilenumbruch zwischen Eingabefeld und Beschriftung') .'
            </td>
        </tr>
    </table>
</div>
<div class="box">
    <h2 class="calibri">'. $trans->__('Eingabefeld &quot;Passwort&quot;') .'</h2>
    <table>
        <tr>
            <td class="a">'. $trans->__('Beschriftung des Eingabefeldes') .'</td>
            <td class="b">
                <input type="text" name="label_password_text" value="'.$c->label_password_text.'" />
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Position des Eingabefeldes') .'</td>
            <td class="b">
                <select name="label_password_position">
                    <option value="before"'.($c->label_password_position == 'before'?' selected="selected"':'').'>'. $trans->__('Hinter der Beschriftung') .'</option>
                    <option value="after"'.($c->label_password_position == 'after'?' selected="selected"':'').'>'. $trans->__('Vor der Beschriftung') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Zeilenumbruch') .'</td>
            <td class="b">
                <input type="checkbox" class="checkbox" value="true" name="label_password_br" id="label_password_br"'.($c->label_password_br?' checked="checked"':'').' />
                <label for="label_password_br">'. $trans->__(' Zeilenumbruch zwischen Eingabefeld und Beschriftung') .'
            </td>
        </tr>
    </table>
</div>
<div class="box">
    <h2 class="calibri">'. $trans->__('Anmelde- und Abmelde-Button') .'</h2>
    <table>
        <tr>
            <td class="a">'. $trans->__('Beschriftung des Anmeldebuttons') .'</td>
            <td class="b">
                <input type="text" name="submit_text" value="'.$c->submit_text.'" />
            </td>
        </tr>                    
        <tr>
            <td class="a">'. $trans->__('Beschriftung des Abmeldebuttons') .'</td>
            <td class="b">
                <input type="text" name="submit_logout_text" value="'.$c->submit_logout_text.'" />
            </td>
        </tr>
    </table>
</div>
</form>';
?>
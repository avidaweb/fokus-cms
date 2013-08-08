<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'nlsend') || !$suite->rm(5) || $index != 'n620')
    exit($user->noRights());

$k = $fksdb->fetch("SELECT * FROM ".SQLPRE."newsletters WHERE id = '".$rel."' LIMIT 1");
if(!$k) exit('Dieser Newsletter existiert nicht mehr');

$doks = $base->db_to_array($k->doks);
for($x = 0; $x < count($doks); $x++)
    $dok_string .= ($x?'-':'').$doks[$x];

echo '
<form class="nele">
<h1>'.$trans->__('Newsletter ansehen und versenden.').'</h1>
<input type="hidden" name="kid" value="'.$k->id.'" />
<input type="hidden" name="doks" value="'.$dok_string.'" />
<input type="hidden" name="template" value="'.$k->template.'" />

<div class="box">
    <table class="struk_dok_table">
        <tr>
            <td class="left"><strong>'.$trans->__('Name des Newsletters:').'</strong></td>
            <td>'.$k->titel.'</td>
        </tr>
    </table>
</div>
<div class="box nlpreview">
    <a>'.$trans->__('Vorschau öffnen').'</a>
</div>
<div class="box">
    <table class="struk_dok_table">
        <tr>
            <td class="left"><strong>'.$trans->__('Betreff des Newsletters:').'</strong></td>
            <td><input type="text" name="betreff" class="betreff" value="'.$k->titel.'" /></td>
        </tr>
        <tr>
            <td class="left"><strong>'.$trans->__('Absender des Newsletters:').'</strong></td>
            <td><input type="text" name="absender" class="absender" value="'.$base->getOpt()->email.'" /></td>
        </tr>
        <tr>
            <td class="left"><strong>'.$trans->__('E-Mail-Adressen der Empfänger:').'</strong></td>
            <td><textarea name="newsletter_empf" class="newsletter_empf"></textarea></td>
        </tr>
    </table>
    <a class="add_empf">'.$trans->__('Empfängeradressen aus Benutzerverwaltung hinzufügen').'</a>
</div>
<div class="box">
    <table class="struk_dok_table">
        <tr class="deakt">
            <td class="left bigleft">
                <input type="checkbox" name="cc" value="1" id="is_cc" />
                <label for="is_cc"><strong>'.$trans->__('Folgende E-Mail-Adresse in Kopie setzen:').'</strong></label>
            </td>
            <td class="gocopy">
                <input type="text" name="cc_email" class="cmail" value="'.$base->getOpt()->email.'" disabled="disabled" />
                <p>
                    <input type="radio" name="cc_type" value="1" id="cc_type_a" disabled="disabled" checked="checked" />
                    <label for="cc_type_a">'.$trans->__('Einmalig in Kopie setzen').'</label>
                </p>
                <p>
                    <input type="radio" name="cc_type" value="2" id="cc_type_b" disabled="disabled" />
                    <label for="cc_type_b">'.$trans->__('Bei jeder Email in Kopie (CC) setzen').'</label>
                </p>
                <p>
                    <input type="radio" name="cc_type" value="3" id="cc_type_c" disabled="disabled" />
                    <label for="cc_type_c">'.$trans->__('Bei jeder Email in Blindkopie (BCC) setzen').'</label>
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="box sendnl">
    <div class="a">
        <p>'.$trans->__('Hier können Sie einen Test-Newsletter senden.').'</p>
        <p class="ce">
            <strong>'.$trans->__('Email:').'</strong>
            <input type="text" name="testmail" class="testmail" value="'.$base->getOpt()->email.'" />
        </p>
        <p><button>'.$trans->__('Test-Email senden').'</button></p>
    </div>

    <span class="oder">'.$trans->__('oder').'</span>

    <div class="b">
        <p>'.$trans->__('Hier können Sie Ihren Newsletter endgültig versenden.').'</p>
        <p class="ce2">
            <strong>'.$trans->__('Anzahl: <span class="anz_empf">0</span> Empfänger').'</strong>
        </p>
        <p><button>'.$trans->__('Newsletter jetzt versenden').'</button></p>
    </div>
</div>
</form>
<div class="box" id="send_status">
    <h2 class="calibri">'.$trans->__('Emails werden verschickt...').'</h2>
    <div id="progresssbar"></div>
    <div class="info">
        '.$trans->__('Es wurden bereits <span class="ges_empf">0</span> von <span class="anz_empf">0</span> Emails verschickt. Pro Sekunde werden zwei Emails verschickt.').'
    </div>
</div>';
?>
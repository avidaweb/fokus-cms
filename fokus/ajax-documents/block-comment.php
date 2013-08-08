<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$c = $base->fixedUnserialize($row->html);
if(!is_array($c)) $c = array();
$c = (object)$c;

echo '
<form id="dcomments" class="dcomments">
<div class="box">
    <h2 class="calibri">'. $trans->__('Allgemeine Einstellungen') .'</h2>
    <table>
        <tr>
            <td class="a">'. $trans->__('Zuordnung der Kommmentare') .'</td>
            <td class="b">
                <select name="type">
                    <option value="0"'.($c->type == 0?' selected="selected"':'').'>'. $trans->__('Dem Strukturelement zuordnen') .'</option>
                    <option value="1"'.($c->type == 1?' selected="selected"':'').'>'. $trans->__('Dem Dokument zuordnen') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Kommentare automatisch freischalten') .'</td>
            <td class="b">
                <select name="frei">
                    <option value="0"'.($c->frei == 0?' selected="selected"':'').'>'. $trans->__('Keinen Kommentar automatisch freischalten') .'</option>
                    <option value="1"'.($c->frei == 1?' selected="selected"':'').'>'. $trans->__('Seriöse Kommentare automatisch freischalten') .'</option>
                    <option value="2"'.($c->frei == 2?' selected="selected"':'').'>'. $trans->__('Jeden Kommentar automatisch freischalten') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Email-Benachrichtigung') .'</td>
            <td class="b">
                <input type="checkbox" name="pn" value="1"'.($c->pn?' checked':'').' id="komment_pn" />
                <label for="komment_pn">'. $trans->__('Bei neuen Kommentaren per Email informieren') .'</label>
                
                <p class="pn"'.($c->pn?' style="display:block;"':'').'>
                    <input type="email" name="pn_email" value="'.($c->pn_email?$c->pn_email:$base->getOpt()->email).'" />
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="box">
    <h2 class="calibri">'. $trans->__('Layout der Kommentare') .'</h2>
    <table>
        <tr>
            <td class="a">'. $trans->__('Position des Eingabe-Formulares') .'</td>
            <td class="b">
                <select name="position">
                    <option value="0"'.($c->position == 0?' selected="selected"':'').'>'. $trans->__('Formular &uuml;ber den Kommentaren') .'</option>
                    <option value="1"'.($c->position == 1?' selected="selected"':'').'>'. $trans->__('Formular unter den Kommentaren') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Reihenfolge der Ausgabe') .'</td>
            <td class="b">
                <select name="chrono">
                    <option value="0"'.($c->chrono == 0?' selected="selected"':'').'>'. $trans->__('Von Alt nach Neu') .'</option>
                    <option value="1"'.($c->chrono == 1?' selected="selected"':'').'>'. $trans->__('Von Neu nach Alt') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Eingabe-Formular verstecken') .'</td>
            <td class="b">
                <input type="checkbox" name="hide" value="1"'.($c->hide?' checked':'').' id="komment_hide" />
                <label for="komment_hide">'. $trans->__('Kein weiteres Kommentieren ermöglichen') .'</label>
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Vorauswahl bei angemeldeten Benutzern') .'</td>
            <td class="b">
                <select name="loggedusers">
                    <option value="0"'.($c->loggedusers == 0?' selected="selected"':'').'>'. $trans->__('Name, Email &amp; URL werden automatisch eingetragen') .'</option>
                    <option value="1"'.($c->loggedusers == 1?' selected="selected"':'').'>'. $trans->__('Name, Email &amp; URL müssen händisch eingetragen werden') .'</option>
                </select>
            </td>
        </tr>
        <tr class="loggedusers_force"'.($c->loggedusers == 1?' style="display:block;"':'').'>
            <td class="a">'. $trans->__('Vorauswahl ist nicht überschreibar') .'</td>
            <td class="b">
                <input type="checkbox" name="loggedusers_force" value="1"'.($c->loggedusers_force?' checked':'').' id="komment_loggedusers_force" />
                <label for="komment_loggedusers_force">'. $trans->__('Vorausgefüllte Werte sind nicht editierbar') .'</label>
            </td>
        </tr>
    </table>
</div>
<div class="box">
    <h2 class="calibri">'. $trans->__('Feld-Einstellungen') .'</h2>
    <fieldset class="feld'.($c->name?' faktiv':'').'">
        <legend>
            <input type="checkbox" value="1" name="name" id="c_name_s"'.($c->name?' checked="checked"':'').' />
            <label for="c_name_s">'. $trans->__('Name') .'</label>
        </legend>
        <p>
            <span>
                <input type="checkbox" value="1" name="name_p" id="c_name_p"'.($c->name_p?' checked="checked"':'').(!$c->name?' disabled="disabled"':'').' />
                <label for="c_name_p">'. $trans->__('Pflichtfeld') .'</label>
            </span>
            <span>
                <input type="checkbox" value="1" name="name_h" id="c_name_h"'.($c->name_h?' checked="checked"':'').(!$c->name?' disabled="disabled"':'').' />
                <label for="c_name_h">'. $trans->__('Nur für Administratoren sichtbar') .'</label>
            </span>
        </p>
    </fieldset>
    <fieldset class="feld'.($c->email?' faktiv':'').'">
        <legend>
            <input type="checkbox" value="1" name="email" id="c_email_s"'.($c->email?' checked="checked"':'').' />
            <label for="c_email_s">'. $trans->__('Email') .'</label>
        </legend>
        <p>
            <span>
                <input type="checkbox" value="1" name="email_p" id="c_email_p"'.($c->email_p?' checked="checked"':'').(!$c->email?' disabled="disabled"':'').' />
                <label for="c_email_p">'. $trans->__('Pflichtfeld') .'</label>
            </span>
            <span>
                <input type="checkbox" value="1" name="email_h" id="c_email_h"'.($c->email_h?' checked="checked"':'').(!$c->email?' disabled="disabled"':'').' />
                <label for="c_email_h">'. $trans->__('Nur für Administratoren sichtbar') .'</label>
            </span>
        </p>
    </fieldset>
    <fieldset class="feld'.($c->web?' faktiv':'').'">
        <legend>
            <input type="checkbox" value="1" name="web" id="c_web_s"'.($c->web?' checked="checked"':'').' />
            <label for="c_web_s">'. $trans->__('Webseite') .'</label>
        </legend>
        <p>
            <span>
                <input type="checkbox" value="1" name="web_p" id="c_web_p"'.($c->web_p?' checked="checked"':'').(!$c->web?' disabled="disabled"':'').' />
                <label for="c_web_p">'. $trans->__('Pflichtfeld') .'</label>
            </span>
            <span>
                <input type="checkbox" value="1" name="web_h" id="c_web_h"'.($c->web_h?' checked="checked"':'').(!$c->web?' disabled="disabled"':'').' />
                <label for="c_web_h">'. $trans->__('Nur für Administratoren sichtbar') .'</label>
            </span>
            <span>
                <input type="checkbox" value="1" name="web_df" id="c_web_df"'.($c->web_df?' checked="checked"':'').(!$c->web?' disabled="disabled"':'').' />
                <label for="c_web_df">'. $trans->__('Link-Power vererben (rel=follow)') .'</label>
            </span>
        </p>
    </fieldset>
    <fieldset class="feld'.($c->text?' faktiv':'').'">
        <legend>
            <input type="checkbox" value="1" name="text" id="c_text_s"'.($c->text?' checked="checked"':'').' />
            <label for="c_text_s">'. $trans->__('Kommentar') .'</label>
        </legend>
        <p>
            <span>
                <input type="checkbox" value="1" name="text_p" id="c_text_p"'.($c->text_p?' checked="checked"':'').(!$c->text?' disabled="disabled"':'').' />
                <label for="c_text_p">'. $trans->__('Pflichtfeld') .'</label>
            </span>
            <span>
                <input type="checkbox" value="1" name="text_h" id="c_text_h"'.($c->text_h?' checked="checked"':'').(!$c->text?' disabled="disabled"':'').' />
                <label for="c_text_h">'. $trans->__('Nur für Administratoren sichtbar') .'</label>
            </span>
        </p>
    </fieldset>
</div>
</form>';
?>
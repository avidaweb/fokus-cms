<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(($index != 'n530' && $index != 'n535'))
    exit($user->noRights());

if(!$user->r('per'))
    exit($user->noRights());

$user_query = $fksdb->query("SELECT * FROM ".SQLPRE."users WHERE id = '".intval($rel)."' LIMIT 1");
$u = $fksdb->fetch($user_query);

$firma = $fksdb->fetch("SELECT * FROM ".SQLPRE."companies WHERE id = '".$u->firma."'");

$err = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND papierkorb = '0'"));

if(!$fksdb->count($user_query))
{
    if(!$user->r('per', 'new'))
        exit($user->noRights());

    $neuer_benutzer = true;

    $rel = explode('_', $rel);

    echo '
    <input type="hidden" value="'.$rel[1].'" id="type" />
    <h1>'.($rel[1]==1?$trans->__('Neuen Kunden anlegen.'):$trans->__('Neuen Mitarbeiter anlegen.')).'</h1>';
}
else
{
    if(!$user->r('per', 'edit'))
        exit($user->noRights());

    $user->lastUse('personen', $u->id);

    echo '
    <input type="hidden" value="'.$u->id.'" id="id" />
    <input type="hidden" value="'.$u->type.'" id="type" />
    <h1>'.$trans->__('%1 bearbeiten.', false, array($u->vorname.' '.$u->nachname)).'</h1>';

    $rel[1] = $u->type;

    // Super-Admin-Check
    $rC = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."user_roles WHERE benutzer = '".$u->id."' AND rolle = '1'"));
    if($rC && $user->getRole() != 1 && $user->getID() != $u->id)
    {
        echo '
        <div class="box fehlerbox">
            <strong>'.$trans->__('Sie haben keinen Zugriff auf diesen Benutzer').'</strong>
            '.$trans->__('Sie versuchen den Mitarbeiter-Account eines Superadministrators zu bearbeiten. Da Sie selber nicht über die Rechte des Superadministrators verfügen, ist dieser Schritt leider nicht möglich. Bitte schließen Sie das Fenster.').'
        </div>
        <div class="box_save" style="display:block;">
            <input type="button" value="'.$trans->__('schließen').'" class="bs1" />
        </div>';

        exit();
    }
}

if(($rel[1] == 1 && !$tkunde) || ($rel[1] == 2 && !$tmitarbeiter) || ($rel[1] == 0 && !$tmitarbeiter && !$tkunde))
    exit($user->noRights());

if($user->getID() == $u->id)
{
    echo '
    <div class="box fehlerbox">
        <strong>'.$trans->__('Sie bearbeiten Ihren eigenen Account').'</strong>
        '.$trans->__('Sie nehmen gerade Änderungen an Ihrem eigenen Mitarbeiter-Account vor.').'
    </div>';
}

echo '
<form>
<div class="box" id="per_edit">
    <table>
        <tr>
            <td class="ftd">'.($rel[1]==1?$trans->__('Kundenummer'):$trans->__('Mitarbeiter-ID')).'</td>
            <td><input type="text" value="'.($fksdb->count($user_query)?($u->type==1?'K':'M').''.str_pad($u->id, 5 ,'0', STR_PAD_LEFT):$trans->__('wird automatisch vergeben')).'" disabled="disabled" /></td>
        </tr>
        <tr>
            <td class="ftd">'.($rel[1]==1?$trans->__('Externe Kundenummer'):$trans->__('Externe Mitarbeiter-ID')).'</td>
            <td><input type="text" id="eid" value="'.$u->eid.'" /></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Anrede').'</td>
            <td>
                <select id="anrede">';
                $anreden = array('', 'Herr', 'Frau', 'Dr.', 'Dipl.-Ing.', 'Professor');
                foreach($anreden as $ll)
                    echo '<option value="'.$ll.'"'.($ll == $u->anrede?' selected="selected"':'').'>'.$trans->__($ll).'</option>';
                echo '
                </select>
            </td>
        </tr>
        <tr>
            <td class="ftd">'.$trans->__('Vorname').'</td>
            <td><input type="text" name="vorname" id="vorname" value="'.$u->vorname.'"'.($rel[1] != 1?' required':'').' /></td>
        </tr>
        <tr>
            <td class="ftd">'.$trans->__('Nachname').'</td>
            <td><input type="text" name="nachname" id="nachname" value="'.$u->nachname.'"'.($rel[1] != 1?' required':'').' /></td>
        </tr>
        <tr>
            <td class="ftd">'.$trans->__('Namenszusatz').'</td>
            <td><input type="text" id="namenszusatz" value="'.$u->namenszusatz.'" /></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Straße').'</td>
            <td><input type="text" id="str" value="'.$u->str.'" style="width:380px" /><input type="text" id="hn" value="'.$u->hn.'" style="width:50px" /></td>
        </tr>
        <tr>
            <td class="ftd">'.$trans->__('PLZ &amp; Ort').'</td>
            <td><input type="number" id="plz" value="'.$u->plz.'" style="width:70px" /><input type="text" id="ort" value="'.$u->ort.'" style="width:360px" /></td>
        </tr>
        <tr>
            <td class="ftd">'.$trans->__('Land').'</td>
            <td>
                <select id="land">';
                foreach($trans->getCountries() as $ll)
                    echo '<option value="'.$ll.'"'.($ll==$u->land || (!$u->land && $ll == 'Deutschland')?' selected="selected"':'').'>'.$ll.'</option>';
                echo '
                </select>
            </td>
        </tr>
    </table>';

    $firmaQ = $fksdb->query("SELECT id, name FROM ".SQLPRE."companies ORDER BY name");
    if($fksdb->count($firmaQ))
    {
        echo '
        <table>
            <tr>
                <td class="ftd">'.$trans->__('Firma / Unternehmen').'</td>
                <td>
                    <input type="radio" style="width:auto;" value="0" id="firma_no" name="firma"'.(!$u->firma?' checked="checked"':'').' />
                    <label for="firma_no">'.$trans->__('Der Mitarbeiter ist keinem Unternehmen zugeordnet.').'</label><br /><br />

                    <input type="radio" style="width:auto;" value="0" id="firma_yes" name="firma"'.($u->firma?' checked="checked"':'').(!$fksdb->count($firmaQ)?' disabled="disabled"':'').' />
                    <label for="firma_yes">'.$trans->__('Der Mitarbeiter gehört zu folgendem Unternehmen:').'</label>

                    <select id="firma" style="width:170px"'.(!$u->firma || !$fksdb->count($firmaQ)?' disabled="disabled"':'').'>
                        <option value="0">'.$trans->__('Bitte wählen').'</option>';
                        while($firmaS = $fksdb->fetch($firmaQ))
                            echo '<option value="'.$firmaS->id.'"'.($u->firma == $firmaS->id?' selected="selected"':'').'>'.$firmaS->name.'</option>';
                    echo '
                    </select>
                </td>
            </tr>
            <tr>
                <td class="ftd">'.$trans->__('Position').'</td>
                <td><input type="text" id="position" value="'.$u->position.'"'.(!$u->firma || !$fksdb->count($firmaQ)?' disabled="disabled"':'').' /></td>
            </tr>
        </table>';
    }

    echo '
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Telefon geschäftlich').'</td>
            <td>
                <table>
                    <tr>
                        <td>
                            <input type="radio" name="dw1" id="dw1A" style="width:auto"'.($u->tel_g || !$u->tel_g_d?' checked="checked"':'').' />
                            <label for="dw1A">'.$trans->__('Gesamte Nummer').'</label>
                        </td>
                        <td colspan="2" style="text-align:right;"><input type="tel" id="tel_g" value="'.$u->tel_g.'"'.(!$u->tel_g && $u->tel_g_d?' disabled="disabled"':'').' style="width:230px" /></td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" name="dw1" id="dw1B" style="width:auto"'.($u->tel_g_d?' checked="checked"':'').(!$fksdb->count($firmaQ)?' disabled="disabled"':'').' />
                            <label for="dw1B">'.$trans->__('nur Durchwahl eingeben').'</label>
                        </td>
                        <td style="text-align:right;"><span class="durchwahl">'.$firma->telA.' '.$firma->telB.'</span> - </td>
                        <td style="text-align:right;"><input type="text" id="tel_g_d" value="'.$u->tel_g_d.'"'.(!$u->tel_g_d || !$fksdb->count($firmaQ)?' disabled="disabled"':'').' style="width:120px" /></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="ftd">'.$trans->__('Fax geschäftlich').'</td>
            <td>
                <table>
                    <tr>
                        <td>
                            <input type="radio" name="dw2" id="dw2A" style="width:auto"'.($u->fax || !$u->fax_d?' checked="checked"':'').' />
                            <label for="dw2A">'.$trans->__('Gesamte Nummer').'</label>
                        </td>
                        <td colspan="2" style="text-align:right;"><input type="tel" id="fax" value="'.$u->fax.'"'.(!$u->fax && $u->fax_d?' disabled="disabled"':'').' style="width:230px" /></td>
                    </tr>
                    <tr>
                        <td>
                            <input type="radio" name="dw2" id="dw2B" style="width:auto"'.($u->fax_d?' checked="checked"':'').(!$fksdb->count($firmaQ)?' disabled="disabled"':'').' />
                            <label for="dw2B">'.$trans->__('nur Durchwahl eingeben').'</label>
                        </td>
                        <td style="text-align:right;"><span class="durchwahl">'.$firma->telA.' '.$firma->telB.'</span> - </td>
                        <td style="text-align:right;"><input type="text" id="fax_d" value="'.$u->fax_d.'"'.(!$u->fax_d || !$fksdb->count($firmaQ)?' disabled="disabled"':'').' style="width:120px" /></td>
                    </tr>
                </table>
            </td>
        </tr>
        <tr>
            <td class="ftd">'.$trans->__('Telefon mobil').'</td>
            <td><input type="tel" id="mobil" value="'.$u->mobil.'" style="width:230px" /></td>
        </tr>
        <tr>
            <td class="ftd">'.$trans->__('Telefon privat').'</td>
            <td><input type="tel" id="tel_p" value="'.$u->tel_p.'" style="width:230px" /></td>
        </tr>
        <tr>
            <td class="ftd">'.$trans->__('E-Mail-Adresse').'</td>
            <td><input type="email" name="email" id="email" value="'.$u->email.'"'.($rel[1] != 1?' required':'').' /></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Stichwort &amp; Notizen').'</td>
            <td><textarea type="text" id="tags" rows="2">'.$u->tags.'</textarea></td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Passwort').'</td>
            <td>
                '.($fksdb->count($user_query)?'
                <input type="checkbox" id="new_passwort">
                <label for="new_passwort">'.$trans->__('Neues Passwort vergeben?').'</label>

                <div class="new_pw">
                    <input type="password" id="pw" value="" class="pw" autocomplete="off" placeholder="'.$trans->__('Neues Passwort eintragen').'" />
                    <input type="text" id="pw_t" value="" class="pw_t" autocomplete="off" placeholder="'.$trans->__('Neues Passwort eintragen').'" style="display:none;" />

                    <p class="new_pw_klar">
                        <input type="checkbox" id="pwt_klartext" value="1" class="auto" />
                        <label for="pwt_klartext">'.$trans->__('Passwort im Klartext eingeben?').'</label>
                    </p>
                </div>':'
                <input type="password" class="pw" placeholder="'.$trans->__('Passwort eintragen').'" autocomplete="off" name="password" id="pw" value=""'.($rel[1] != 1?' required':'').' />
                <input type="text" id="pw_t" value="" class="pw_t" autocomplete="off" placeholder="'.$trans->__('Passwort eintragen').'" style="display:none;" />

                <p class="new_pw_klar">
                    <input type="checkbox" id="pwt_klartext" value="1" class="auto" />
                    <label for="pwt_klartext">'.$trans->__('Passwort im Klartext eingeben?').'</label>
                </p>
                ').'
            </td>
        </tr>
    </table>
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Lebensdauer').'</td>
            <td>
                '.$trans->__('Diesen Benutzer ausschließlich für folgenden Zeitraum freischalten:').'<br />
                <table class="zeitraum">
                    <tr>
                        <td><input type="checkbox" id="vonC" class="vonbis" value="1"'.($u->von?' checked="checked"':'').' /></td>
                        <td'.(!$u->von?' class="notaktiv"':'').'>'.$trans->__('Von:').'</td>
                        <td>
                            <input type="text" id="von" class="datepicker" value="'.($u->von?date('d.m.Y', $u->von):'').'"'.(!$u->von?' disabled="disabled"':'').' />
                        </td>
                        <td class="time'.(!$u->von?' notaktiv':'').'">
                            <select id="von_h" class="zeit"'.(!$u->von?' disabled="disabled"':'').'>
                                '.$base->time_options(($u->von?date('H', $u->von):0)).'
                            </select> :
                            <select id="von_m" class="zeit"'.(!$u->von?' disabled="disabled"':'').'>
                                '.$base->time_options(($u->von?date('i', $u->von):0), true).'
                            </select> Uhr
                        </td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" style="width:auto;" id="bisC" class="vonbis" value="1"'.($u->bis?' checked="checked"':'').' /></td>
                        <td'.(!$u->bis?' class="notaktiv"':'').'>'.$trans->__('Bis:').'</td>
                        <td>
                            <input type="text" id="bis" class="datepicker" value="'.($u->bis?date('d.m.Y', $u->bis):'').'"'.(!$u->bis?' disabled="disabled"':'').' />
                        </td>
                        <td class="time'.(!$u->bis?' notaktiv':'').'">
                            <select id="bis_h" class="zeit"'.(!$u->bis?' disabled="disabled"':'').'>
                                '.$base->time_options(($u->bis?date('H', $u->bis):23)).'
                            </select> :
                            <select id="bis_m" class="zeit"'.(!$u->bis?' disabled="disabled"':'').'>
                                '.$base->time_options(($u->bis?date('i', $u->bis):59), true).'
                            </select> Uhr
                        </td>
                    </tr>
                </table>

                '.($fksdb->count($user_query) && $user->r('per', 'del') && !$u->papierkorb?'
                <p class="llinie"></p>
                <input type="checkbox" style="width:auto;" id="c_del_user">
                <label for="c_del_user">'.$trans->__('Diesen Benutzer löschen?').'</label>
                <div id="del_user"><button>'.($rel[1]==1?$trans->__('Kunden entfernen'):$trans->__('Mitarbeiter entfernen')).'</button></div>
                ':'').'
            </td>
        </tr>
    </table>
    '.($user->r('per', 'prolle')?'
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Rollenzuordnung').'</td>
            <td>
                <div class="rollen">
                    '.($u->id?'
                    <table id="trollen">
                        <tr><td><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>
                    </table>
                </div>
                <button id="neue_rolle">'.$trans->__('Rolle zuordnen').'</button>':'
                &nbsp;&nbsp;&nbsp;&nbsp;'.$trans->__('Vor dem Zuordnen einer Rolle muss die Person gespeichert werden')).'
            </td>
        </tr>
    </table>':'').'
    '.($fksdb->count($user_query)?'
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Benutzergruppen:').'</td>
            <td>
                <table class="bgruppen">
                    <tr>
                        <td><input'.(!$tmitarbeiter?' disabled':'').' type="checkbox" style="width:auto;" value="1" id="cmitarbeiter"'.(!$u->type || $u->type == 2?' checked="checked"':'').' /></td>
                        <td><label for="cmitarbeiter">'.$trans->__('Mitarbeiter').'</label></td>
                    </tr>
                    <tr>
                        <td><input'.(!$tkunde?' disabled':'').' type="checkbox" style="width:auto;" value="1" id="ckunde"'.(!$u->type || $u->type == 1?' checked="checked"':'').' /></td>
                        <td><label for="ckunde">'.$trans->__('Kunden').'</label></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>':'').'
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Benutzer-Status:').'</td>
            <td>
                <select id="status">
                    <option value="0"'.($u->status == 0?' selected="selected"':'').'>'.$trans->__('aktiv').'</option>
                    <option value="1"'.($u->status == 1?' selected="selected"':'').'>'.$trans->__('inaktiv').'</option>
                    <option value="2"'.($u->status == 2?' selected="selected"':'').'>'.$trans->__('gesperrt').'</option>
                </select>
            </td>
        </tr>
    </table>';

    echo '
    <table>
        <tr>
            <td class="ftd">'.$trans->__('Benutzer-Avatar:').'</td>
            <td>
                <input type="hidden" name="avatar" value="'.$u->avatar.'" />

                <button class="avatar_select">Bild auswählen</button>
                '.($user->r('dat', 'new')?'<button class="avatar_new">Bild hochladen</button>':'').'
                '.($user->r('dat', 'edit')?'<button class="avatar_edit" data-file="'.$u->avatar.'"'.(!$u->avatar?' style="display:none;"':'').'>'.$trans->__('Bild bearbeiten').'</button>':'').'
            </td>
            <td class="goright">
                <img src="'.$api->getImageUrl($u->avatar, 0, 100).'" alt=" " height="100" class="avatar'.(!$u->avatar?' hidden':'').'" />
            </td>
        </tr>
    </table>';

    $ufields = $api->getUserFields();
    $ufields_output = '';
    if(count($ufields))
    {
        $cf = $base->db_to_array($u->cf);

        foreach($ufields as $k => $v)
        {
            if(!$v['name'])
                continue;

            $ufields_output .= '
            <tr>
                <td class="ftd">'.$v['name'].'</td>
                <td>';
                    if(!$v['type'] || $v['type'] == 'text' || $v['type'] == 'input')
                    {
                        $ufields_output .= '<input type="text" name="cf['.$k.']" value="'.$cf[$k].'" />';
                    }
                    elseif($v['type'] == 'textarea')
                    {
                        $ufields_output .= '<textarea name="cf['.$k.']">'.$cf[$k].'</textarea>';
                    }
                    elseif($v['type'] == 'checkbox')
                    {
                        $ufields_output .= '<input type="checkbox" name="cf['.$k.']" value="fks_true"'.($cf[$k] == 'fks_true'?' checked="checked"':'').' />';
                    }
                    elseif($v['type'] == 'select' && is_array($v['values']))
                    {
                        $ufields_output .= '<select name="cf['.$k.']">';
                        foreach($v['values'] as $x => $y)
                            $ufields_output .= '<option value="'.$x.'"'.($cf[$k] == $x?' selected="selected"':'').'>'.$y.'</option>';
                        $ufields_output .= '
                        </select>';
                    }
                $ufields_output .= '
                </td>
            </tr>';
        }

        if($ufields_output)
        {
            echo '
            <table>
                '.$ufields_output.'
            </table>';
        }
    }

    echo '
    <table>
        <tr>
            <td class="ftd">'.$trans->__('fokus individualisieren:').'</td>
            <td>
                <input type="checkbox" id="pindiv" value="true"'.($base->getOpt()->pindiv == $u->id?' checked':'').' />
                <label for="pindiv">'.$trans->__('Diesen Benutzer als Preset für &quot;fokus individualisieren&quot; und die Positionierung von Widgets verwenden.').'</label>
            </td>
        </tr>
    </table>';

    if(!$fksdb->count($user_query))
    {
        echo '
        <table>
            <tr>
                <td class="ftd">'.$trans->__('Email-Benachrichtigung:').'</td>
                <td>
                    <input type="checkbox" id="sendemailbe" value="true" />
                    <label for="sendemailbe">'.$trans->__('Der angelegten Person die Zugangsdaten per Email senden.').'</label>
                </td>
            </tr>
        </table>';
    }
    else
    {
        echo '
        <table id="person_user_info">
            <tr>
                <td class="ftd">'.$trans->__('Benutzer-Info:').'</td>
                <td>';
                    if($u->registriert_von == 'form')
                    {
                        $trans->__('Person wurde am <strong>%1</strong> um <strong>%2 Uhr</strong> durch ein <strong>Formular</strong> auf der Webseite angelegt', true, array(date('d.m.Y', $u->registriert), date('H:i', $u->registriert)));
                    }
                    elseif($u->registriert_von == 'system')
                    {
                        $trans->__('Person wurde am <strong>%1</strong> um <strong>%2 Uhr</strong> im Zuge der <strong>CMS-Installation</strong> angelegt', true, array(date('d.m.Y', $u->registriert), date('H:i', $u->registriert)));
                    }
                    elseif(intval($u->registriert_von))
                    {
                        $trans->__('Person wurde am <strong>%1</strong> um <strong>%2 Uhr</strong> durch den Mitarbeiter <strong>%3</strong> angelegt', true, array(date('d.m.Y', $u->registriert), date('H:i', $u->registriert), $base->user($u->registriert_von, ' ', 'vorname', 'nachname')));
                    }
                    else
                    {
                        $trans->__('Person wurde am <strong>%1</strong> um <strong>%2 Uhr</strong> angelegt', true, array(date('d.m.Y', $u->registriert), date('H:i', $u->registriert)));
                    }
                echo '
                </td>
            </tr>
        </table>';
    }

echo '
</div>
<div class="box_save">
    <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" />
    <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
</div>
</form>';
?>
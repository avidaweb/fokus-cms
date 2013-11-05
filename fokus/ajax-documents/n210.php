<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if($index != 'n210')
    exit();

if(!$user->r('dok', 'new'))
    exit($user->noRights());

$vorlage = $fksdb->save($_REQUEST['vorlage']);

if($rel)
{
    if(is_numeric($rel))
    {
        $selement = $fksdb->fetch("SELECT id, titel FROM ".SQLPRE."elements WHERE id = '".$rel."' LIMIT 1");
    }
    elseif(Strings::strExists('fks_dk_', $rel))
    {
        $dk = str_replace('fks_dk_', '', $rel);
    }
    elseif(Strings::strExists('fks_slot_sid_', $rel))
    {
        $temp = explode('_xx_', str_replace('fks_slot_sid_', '', $rel));
        $slot = $temp[0];
        $selement = $fksdb->fetch("SELECT id, titel FROM ".SQLPRE."elements WHERE id = '".$temp[1]."' LIMIT 1");
    }
    elseif(Strings::strExists('fks_slot_sid_dk_', $rel))
    {
        $temp = explode('_xx_xx_', str_replace('fks_slot_sid_dk_', '', $rel));
        $slot = $temp[0];
        $dk = $temp[1];
        $selement = $fksdb->fetch("SELECT id, titel FROM ".SQLPRE."elements WHERE id = '".$temp[2]."' LIMIT 1");
    }
    elseif(Strings::strExists('fks_slot_dk_', $rel))
    {
        $temp = explode('_xx_xx_', str_replace('fks_slot_dk_', '', $rel));
        $slot = $temp[0];
        $dk = $temp[1];
    }
    elseif(Strings::strExists('fks_error_', $rel))
    {
        $error = str_replace('fks_error_', '', $rel);
    }
    else
    {
        $slot = $rel;
    }
}

echo '
<div id="neues_dokument_anlegen">
    <h1>
        '.(!$vorlage?
            $trans->__('Neues Dokument anlegen.')
            :
            $trans->__('Als Vorlage speichern.')
        ).'
    </h1>

    <form method="post">

    '.($selement && $slot?'
    <div class="box">
        <input type="hidden" name="selement" value="'.$selement->id.'" />
        <input type="hidden" name="slot" value="'.$slot.'" />
        <input type="hidden" name="do_not_open" value="true" />

        <em>'. $trans->__('Dieses Dokument wird automatisch dem Strukturelement <strong>%1</strong> und dem Slot <strong>%2</strong> zugeordnet.', false, array($selement->titel, ($base->getActiveTemplateConfig('slots', $slot, 'name')))) .'</em>
    </div>':'').'
    '.($selement && !$slot?'
    <div class="box">
        <input type="hidden" name="selement" value="'.$selement->id.'" />
        <em>'. $trans->__('Dieses Dokument wird automatisch dem Strukturelement <strong>%1</strong> zugeordnet.', false, array($selement->titel)) .'</em>
    </div>':'').'
    '.($slot && !$selement?'
    <div class="box">
        <input type="hidden" name="slot" value="'.$slot.'" />
        <em>'. $trans->__('Dieses Dokument wird automatisch dem Slot <strong></strong> zugeordnet.', false, array($base->getActiveTemplateConfig('slots', $slot, 'name'))) .'</em>
    </div>':'').'
    '.($error?'
    <div class="box">
        <input type="hidden" name="error" value="'.$error.'" />
        <em>'. $trans->__('Dieses Dokument wird automatisch der Fehlerseite <strong>%1</strong> zugeordnet.', false, array($error)) .'</em>
    </div>':'').'

    <div class="box">
        <table id="doc_neu">';

            $ivalue = '';
            if($vorlage)
                $ivalue = $fksdb->data("SELECT vorlage FROM ".SQLPRE."documents WHERE id = '".$vorlage."' LIMIT 1", "vorlage");
            elseif($selement && $slot)
                $ivalue = trim($selement->titel.' - '.$base->getActiveTemplateConfig('slots', $slot, 'name'), ' - ');
            elseif($selement)
                $ivalue = $selement->titel;
            elseif($slot)
                $ivalue = $base->getActiveTemplateConfig('slots', $slot, 'name');
            elseif($error)
                $ivalue = 'Fehlerseite: '.$error;

            if(!$vorlage)
            {
                $vorlagenanz = $fksdb->query("SELECT id FROM ".SQLPRE."documents WHERE vorlage != '' AND papierkorb = '0' LIMIT 1");

                $dkklassen_inc = array();
                $ordner = "../content/dklassen";

                if(is_dir($ordner))
                {
                    $handle = opendir($ordner);
                    while ($file = readdir ($handle))
                    {
                        if($file != "." && $file != "..")
                        {
                            $fk = $base->open_dklasse($ordner.'/'.$file);

                            $dkklassen_inc[$file] = $fk;
                        }
                    }
                }

                $hide_title = false;
                if(count($dkklassen_inc))
                {
                    $dka = $base->db_to_array($base->getOpt()->dk);
                    $dks = (object)$dka;

                    if(!$dk && $rechte['dok']['dk'] && $rechte['dok']['dklassea'])
                        $dknew = $rechte['dok']['dklassea'];

                    echo '
                    <tr>
                        <td class="l1">'. $trans->__('Dokumenten-Klasse:') .'</td>
                        <td>
                            <select name="klasse" class="klasse">
                                <option'.($rechte['dok']['dk'] && !$rechte['dok']['dklasse'][0]?' disabled':'').' value="" data-notitel="false">'. $trans->__('Standard-Dokument') .'</option>';

                                foreach($dkklassen_inc as $k => $v)
                                {
                                    $filename = str_replace('.php', '', $k);
                                    $slug = $base->slug($v['name']);

                                    $disabled = false;
                                    if($rechte['dok']['dk'] && !$rechte['dok']['dklasse'][$slug])
                                        $disabled = true;

                                    if(($k == $dk || $slug == $dknew) && $dks->auto_titel[$slug])
                                        $hide_title = true;

                                    echo '<option'.($disabled?' disabled':'').' value="'.$filename.'"'.($k == $dk || $slug == $dknew?' selected="selected"':'').' data-notitel="'.($dks->auto_titel[$slug]?'true':'false').'">'.$v['name'].'</option>';
                                }
                            echo '
                            </select>
                        </td>
                    </tr>';
                }

                echo '
                <tr class="dokument_title">
                    <td class="l1"'.($hide_title?' style="display:none;"':'').'>
                       '.(!$vorlage?$trans->__('Name des Dokuments'):$trans->__('Name der Vorlage')).':
                    </td>
                    <td'.($hide_title?' style="display:none;"':'').'>
                        <input type="text" required="required" name="titel" class="titel"'.($ivalue?' value="'.$ivalue.'"':'').' />
                    </td>
                </tr>';

                $zq = $fksdb->query("SELECT * FROM ".SQLPRE."responsibilities WHERE papierkorb = '0' ORDER BY name");
                if($fksdb->count($zq))
                {
                    echo '
                    <tr id="neu_zsb">
                        <td class="l1">'. $trans->__('Zuständigkeiten:') .'</td>
                        <td>
                            <div class="vbox">
                                <input type="checkbox" name="zsb" value="1" id="nd_zsb"'.($rechte['dok']['zsb']?' checked':'').' />
                                <label for="nd_zsb">'. $trans->__('Nur für bestimmte Zuständigkeitsbereiche') .'</label>
                            </div>
                            <div class="vbox vbox2"'.($rechte['dok']['zsb']?' style="display:block;"':'').'>';
                                while($z = $fksdb->fetch($zq))
                                {
                                    if($rechte['dok']['zsb'] && !in_array($z->id, $rechte['dok']['zsba']))
                                        continue;

                                    echo '
                                    <input type="checkbox" value="'.$z->id.'" name="r_zsb[]" id="rn_zsb'.$z->id.'" />
                                    <label for="rn_zsb'.$z->id.'">'.$z->name.'</label><br />';
                                }
                            echo '
                            </div>
                        </td>
                    </tr>';
                }

                echo '
                <tr id="vorlage_verwenden"'.($dk || $dknew?' style="display:none;"':'').'>
                    <td class="l1">'. $trans->__('Vorlage verwenden:') .'</td>
                    <td>
                        <div class="vbox">
                            <input type="checkbox" name="vorlage" value="1" id="nd_vorlage" /> <label for="nd_vorlage">'. $trans->__('Vorlage verwenden') .'</label>
                        </div>
                        <div class="vbox vbox2 vbox2O">
                            <input type="radio" name="type" name="type" id="nd_type1" value="0"'.($fksdb->count($vorlagenanz) == 0?' disabled="disabled"':'').' /> <label for="nd_type1">Vorlage ausw&auml;hlen:</label>
                            <p>
                                <select name="ch_vorlage">
                                    <option value="0">'. $trans->__('Bitte wählen') .'</option>';
                                    $vdocsQ = $fksdb->query("SELECT id, vorlage FROM ".SQLPRE."documents WHERE vorlage != '' AND papierkorb = '0' ORDER BY vorlage");
                                    while($vdocs = $fksdb->fetch($vdocsQ))
                                        echo '<option value="'.$vdocs->id.'">'.$vdocs->vorlage.'</option>';
                                echo '
                                </select><br />
                                <input type="radio" name="aufteilung1" id="nd_aufteilung1_1" value="0" checked="checked" /> <label for="nd_aufteilung1_1">nur Aufteilung &uuml;bernehmen</label><br />
                                <input type="radio" name="aufteilung1" id="nd_aufteilung1_2" value="1" /> <label for="nd_aufteilung1_2">nur Aufteilung und Elemente &uuml;bernehmen</label><br />
                                <input type="radio" name="aufteilung1" id="nd_aufteilung1_3" value="2" /> <label for="nd_aufteilung1_3">Aufteilung, Elemente und Inhalt &uuml;bernehmen</label>
                            </p>
                        </div>
                        <div class="vbox vbox2">
                            <input type="radio" name="type" id="nd_type2" value="1" /> <label for="nd_type2">Anderes Dokument als Vorlage verwenden:</label>
                            <p>
                                <button id="nd_choose">'. $trans->__('Dokument ausw&auml;hlen') .'</button><br />
                                <span id="gew_dok"></span><input type="hidden" name="gew_dok" />
                                <input type="radio" name="aufteilung2" id="nd_aufteilung2_1" value="0" checked="checked" /> <label for="nd_aufteilung2_1">'. $trans->__('nur Aufteilung übernehmen') .'</label><br />
                                <input type="radio" name="aufteilung2" id="nd_aufteilung2_2" value="1" /> <label for="nd_aufteilung2_2">'. $trans->__('nur Aufteilung und Elemente übernehmen') .'</label><br />
                                <input type="radio" name="aufteilung2" id="nd_aufteilung2_3" value="2" /> <label for="nd_aufteilung2_3">'. $trans->__('Aufteilung, Elemente und Inhalt übernehmen') .'</label>
                            </p>
                        </div>
                    </td>
                </tr>';
            }
            else
            {
                echo '
                <tr class="dokument_title">
                    <td class="l1">'.(!$vorlage?$trans->__('Name des Dokuments'):$trans->__('Name der Vorlage')).':</td>
                    <td><input type="text" required="required" name="titel" class="titel"'.($ivalue?' value="'.$ivalue.'"':'').' /></td>
                </tr>';
            }
        echo '
        </table>
    </div>
    </form>
    <div class="box_save"'.($selement || $slot || $vorlage || $error?' style="display:block;"':'').'>
        <input type="button" value="'. $trans->__('verwerfen') .'" class="bs1" />
        <input type="button" value="'. $trans->__('anlegen & fortfahren') .'" class="bs2" />
    </div>
</div>';
?>
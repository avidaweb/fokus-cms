<?php
if($index != 'link' || !$user->isAdmin())
    exit($user->noRights());

$picture = $fksdb->save($_REQUEST['picture']);

$ext = $fksdb->save($_REQUEST['ext']);
$url_only = $fksdb->save($_REQUEST['url_only']);

$text = stripslashes(strip_tags(htmlspecialchars($_REQUEST['text'])));
$href = rawurldecode($fksdb->save($_REQUEST['href']));
$ziel = $fksdb->save($_REQUEST['ziel']);
$power = $fksdb->save($_REQUEST['power']);
$titel = $fksdb->save($_REQUEST['titel']);
$klasse = $fksdb->save($_REQUEST['klasse']);

$text = ($text == 'null'?'':$text);
$href = ($href == 'null'?'':$href);
$titel = ($titel == 'null'?'':$titel);
$klasse = ($klasse == 'null'?'':' '.$klasse.' ');

$menue = $fksdb->save($_REQUEST['menue'], 1);
if($menue)
{
    $mo = $fksdb->fetch("SELECT * FROM ".SQLPRE."menus WHERE struktur = '".$base->getStructureID()."' AND id = '".$menue."' LIMIT 1");
    if(!$mo)
        exit('Gewählter Menüpunkt existiert nicht');

    $href = $mo->url;
    $spr = $base->fixedUnserialize($mo->sprachen);
    $titel = $mo->titel;
    $klasse = ' '.trim($mo->klasse).' ';
    $ziel = $mo->ziel;
    $power = $mo->power;
}

$type = (Strings::strExists('{s-', $href)?'i':'e');
$type = (Strings::strExists('{d-', $href)?'d':$type);
$type = (Strings::strExists('mailto:', $href)?'m':$type);
if($type == 'i')
{
    preg_match('~{s-(.*)}~Uis', $href, $intern);
    preg_match('~{s-(.*)_(.*)}~Uis', $href, $dok_intern);
}
elseif($type == 'd')
{
    preg_match('~{d-(.*)}~Uis', $href, $dateien);
}
elseif($type == 'm')
{
    $href = str_replace('mailto:', '', $href);
}


echo '
<h1>'.$trans->__('Link einfügen.').'</h1>

<div class="box" id="fck_link">
    <input type="hidden" id="hiddentype" value="'.$type.'" />
    <input type="hidden" name="menue" value="'.$menue.'" />
    <input type="hidden" name="nhref" value="'.$href.'" />

    <div class="bereich linktext"'.($url_only?' style="display:none;"':'').'>
        <form>
        <table>
            <tr class="dkop">
                <td class="radio"></td>
                <td class="beschr" colspan="2"><strong>'.$trans->__('Eigenschaften des Links').'</strong></td>
            </tr>
            '.(!$picture && !$menue?'
            <tr>
                <td></td>
                <td class="beschr">'.$trans->__('Link-Text:').'</td>
                <td class="inp"><input type="text" id="fck_text" value="'.$text.'" /> '.$trans->__('(Pflicht)').'</td>
            </tr>
            ':'');

            if($menue)
            {
                $clangs = 0;

                foreach($base->getActiveLanguages() as $l)
                {
                    echo '
                    <tr>
                        <td></td>
                        <td class="beschr">
                            <img src="'.$trans->getFlag($l, 2).'" alt="" />
                            '.$trans->__(strtoupper($l)).'
                        </td>
                        <td class="inp">
                            <input type="text" name="spr['.$l.']" value="'.$spr[$l].'"'.(!$clangs && !$spr[$l]?' autofocus':'').' />
                        </td>
                    </tr>';

                    if(!$spr[$l])
                        $clangs ++;
                }
            }

            echo '
            <tr>
                <td></td>
                <td class="beschr">'.$trans->__('Link-Ziel:').'</td>
                <td class="inp">
                    <select id="fck_ziel">
                        <option value="0"'.($ziel == 0?' selected="selected"':'').'>'.$trans->__('gleiches Fenster (self)').'</option>
                        <option value="1"'.($ziel == 1?' selected="selected"':'').'>'.$trans->__('neues Fenster (blank)').'</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2">
                    <a id="fck_opt_more">'.$trans->__('Link-Optionen einblenden').'</a>
                </td>
            </tr>
            <tr class="fck_opt_more">
                <td></td>
                <td class="beschr">'.$trans->__('Link-Power:').'</td>
                <td class="inp">
                    <select id="fck_power">
                        <option value="0"'.($power == 0?' selected="selected"':'').'>'.$trans->__('Popularit&auml;t vererben (follow)').'</option>
                        <option value="1"'.($power == 1?' selected="selected"':'').'>'.$trans->__('Popularit&auml;t nicht vererben (nofollow)').'</option>
                    </select>
                </td>
            </tr>
            '.(!$menue?'
            <tr class="fck_opt_more">
                <td></td>
                <td class="beschr">'.$trans->__('Link-Titel:').'</td>
                <td class="inp"><input type="text" id="fck_titel" value="'.$titel.'" /> '.$trans->__('(optional)').'</td>
            </tr>':'');

            if(is_array($base->getActiveTemplateConfig('classes')))
            {
                $class_erlaubt = ($menue?'menu':'link');
                $classes_text = '';

                foreach($base->getActiveTemplateConfig('classes') as $class => $op)
                {
                    if($op['restriction'] && $op['restriction'] != 'none' && !Strings::strExists($class_erlaubt, $op['restriction'], false))
                        continue;
                    if(!$op['name'])
                        continue;

                    $checked = (Strings::strExists($class.' ', ' '.$klasse.' ')?true:false);
                    $cslug = $base->slug($class);

                    $classes_text .= '
                    <p>
                        <input type="checkbox" name="classes[]" class="classes" value="'.$class.'" id="ccllass_'.$cslug.'"'.($checked?' checked':'').' />
                        <label for="ccllass_'.$cslug.'">'.$op['name'].'</label>
                    </p>';
                }

                if($classes_text)
                {
                    echo '
                    <tr class="fck_opt_more extclass">
                        <td></td>
                        <td class="beschr">'.$trans->__('Link-Klasse:').'</td>
                        <td class="inp extoL2">
                            <div class="sclasses">
                                '.$classes_text.'
                            </div>
                        </td>
                    </tr>';
                }
            }
        echo '
        </table>
        </form>
    </div>

    <div class="bereich extern'.($type != 'e'?' versteckt':'').'">
        <table>
            <tr class="dkop">
                <td class="radio"><input type="radio" name="linktype" id="linktype_0" value="0"'.($type == 'e'?' checked="checked"':'').' /></td>
                <td class="beschr"><label for="linktype_0"><strong>'.$trans->__('Externer Link').'</strong></label></td>
                <td class="inp">'.$trans->__('(z.B. auf eine andere Webseite)').'</td>
            </tr>
            <tr class="tr_e">
                <td></td>
                <td class="beschr">'.$trans->__('Webseite / URL:').'</td>
                <td class="inp"><input type="text" id="fck_href" value="'.($href && $type == 'e'?$href:'http://').'"'.(!$menue?' autofocus':'').' /></td>
            </tr>
        </table>
    </div>

    <div class="bereich intern'.($type != 'i'?' versteckt':'').'">
        <table>
            <tr class="dkop">
                <td class="radio"><input type="radio" name="linktype" id="linktype_1" value="1"'.($type == 'i'?' checked="checked"':'').' /></td>
                <td class="beschr"><label for="linktype_1"><strong>'.$trans->__('Interner Link').'</strong></label></td>
                <td class="inp">'.$trans->__('(z.B. auf eine Seite dieser Webseite)').'</td>
            </tr>
            <tr class="tr_i">
                <td></td>
                <td class="both" colspan="2">
                    <div class="searche">
                        <label for="searchelement">'.$trans->__('Strukturelement suchen:').'</label>
                        <input type="text" id="searchelement" />
                    </div>

                    <div class="elover">
                        <img src="images/loading_white.gif" alt="loading" class="ladebalken" />
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <div class="bereich mail'.($type != 'm'?' versteckt':'').'">
        <table>
            <tr class="dkop">
                <td class="radio"><input type="radio" name="linktype" id="linktype_3" value="3"'.($type == 'm'?' checked="checked"':'').' /></td>
                <td class="beschr"><label for="linktype_3"><strong>'.$trans->__('Email-Verlinkung').'</strong></label></td>
                <td class="inp">'.$trans->__('(z.B. für externes Email-Programm)').'</td>
            </tr>
            <tr class="tr_m">
                <td></td>
                <td class="beschr">'.$trans->__('E-Mail-Adresse:').'</td>
                <td class="inp"><input type="text" id="fck_mail" value="'.($href && $type == 'm'?$href:'').'" /></td>
            </tr>
        </table>
    </div>

    <div class="bereich datei'.($type != 'd'?' versteckt':'').'">
        <table>
            <tr class="dkop">
                <td class="radio"><input type="radio" name="linktype" id="linktype_2" value="2"'.($type == 'd'?' checked="checked"':'').' /></td>
                <td class="beschr"><label for="linktype_2"><strong>'.$trans->__('Download-Datei').'</strong></label></td>
                <td class="inp">'.$trans->__('(z.B. auf ein PDF-Dokument)').'</td>
            </tr>
            <tr class="tr_d">
                <td></td>
                <td class="both" colspan="2">
                    <h4>'.$trans->__('Bitte wählen Sie eine Datei aus.').'</h4>

                    <div class="elover">';
                        $dQ = $fksdb->query("SELECT id, titel, last_type FROM ".SQLPRE."files WHERE isdir = '0' AND kat = '2' AND papierkorb = '0' ORDER BY last_timestamp DESC");
                        while($d = $fksdb->fetch($dQ))
                        {
                            echo '
                            <div class="elemente">
                                <div class="own">
                                    <div class="kopp">
                                        <input type="radio" name="int_file" id="fck_int_file_'.$d->id.'" value="'.$d->id.'"'.($type == 'd' && $dateien[1] == $d->id?' checked="checked"':'').' />
                                        <label for="fck_int_file_'.$d->id.'">'.$d->titel.' <em>('.$trans->__('Format:').' '.$d->last_type.')</em></label>
                                    </div>
                                </div>
                            </div>';
                        }
                        if(!$fksdb->count($dQ))
                            echo $trans->__('Im System existieren keine Dateien.');
                    echo '
                    </div>
                </td>
            </tr>
        </table>
    </div>
</div>

<div class="box_save" style="display:block;">
    <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" />
    <input type="button" value="'.$trans->__('einfügen').'" class="bs2" />
</div>';
?>
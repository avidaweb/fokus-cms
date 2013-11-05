<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n269') /// FORMULAR GENERATOR
    exit($user->noRights());

$a = $fksdb->save($_POST['a']);

if($a == 0) // Formularelement einfügen
{
    $type = $fksdb->save($_POST['type']);
    echo '<input type="hidden" id="neuesformelement_type" value="'.($type == 'beschrL'?'L':'R').'" />';

    if($type == 'inputR')
    {
        echo '
        <h1>'. $trans->__('Neues Element in Formular einf&uuml;gen.') .'</h1>

        <div class="neu_feld">
            <div class="box">
                <p>
                    '. $trans->__('Name des Formularelements:') .'
                    <input type="text" class="name" />
                </p>
            </div>

            <div class="box" style="display: none;">
                <table>
                    <tr>
                        <td><input type="text" class="ex_tf" /></td>
                        <td class="r"><button class="text">'. $trans->__('Textfeld (einzeilig) einfügen') .'</button></td>
                    </tr>
                    <tr>
                        <td><textarea class="ex_tf"></textarea></td>
                        <td class="r"><button class="textarea">'. $trans->__('Textbox (mehrzeilig) einfügen') .'</button></td>
                    </tr>
                    <tr>
                        <td><input type="checkbox" /><input type="checkbox" checked="checked" /></td>
                        <td class="r"><button class="checkbox">'. $trans->__('Auswahlbox einfügen') .'</button></td>
                    </tr>
                    <tr>
                        <td><input type="radio" name="example_radio" /><input type="radio" name="example_radio" checked="checked" /></td>
                        <td class="r"><button class="radio">'. $trans->__('Alternativ-Auswahl einfügen') .'</button></td>
                    </tr>
                    <tr>
                        <td><select><option>'. $trans->__('Beispieloption 1') .'</option><option>'. $trans->__('Beispieloption 2') .'</option></select></td>
                        <td class="r"><button class="select">'. $trans->__('Auswahlfeld einfügen') .'</button></td>
                    </tr>
                    <tr>
                        <td><input type="password" class="ex_tf" value="Ein Passwort" /></td>
                        <td class="r"><button class="password">'. $trans->__('Passwort-Feld einfügen') .'</button></td>
                    </tr>
                    <tr>
                        <td><input type="file" /></td>
                        <td class="r"><button class="img">'. $trans->__('Bild-Upload einfügen') .'</button></td>
                    </tr>
                </table>
            </div>
        </div>';
    }
    else
    {
        echo '
        <h1>'. $trans->__('Neues Textfeld in Formular einf&uuml;gen.') .'</h1>

        <div class="box">
            <textarea class="text"></textarea>
        </div>

        <div class="box_save" style="display:block;">
            <button class="string">'. $trans->__('Weiter') .'</button>
        </div>';
    }
}
elseif($a == 1) // Formularelement anlegen und HTML zurückliefern
{
    $type = $fksdb->save($_POST['type']);
    $ort = $fksdb->save($_POST['ort']);
    $name = $fksdb->save($_POST['name']);
    if(!$type) exit();

    $b_id = Strings::createID();
    $f_id = Strings::createID();

    $klen = 100;
    if($type == 'string')
    {
        $klen = ($ort == 'L'?20:100);
        $name = Strings::cleanString($name);
    }

    $input = '
    <div class="feld '.$type.'">
        <em>'.$ftypen[$type].'</em>
        <a class="name">'.Strings::cut(strip_tags(htmlspecialchars_decode($name)), $klen).'</a>
        <a class="opt">'. $trans->__('Optionen') .'</a>

        <input type="hidden" name="f['.$f_id.'][bid]" value="'.$b_id.'" class="fbid" />
        <input type="hidden" name="f['.$f_id.'][name]" value="'.str_replace('"', "'", $name).'" class="fname" />
        <input type="hidden" name="f['.$f_id.'][type]" value="'.$type.'" class="ftype" />
        <input type="hidden" name="f['.$f_id.'][links]" value="'.($ort == 'L'?1:0).'" class="flinks" />
        <input type="hidden" name="f['.$f_id.'][opt]" value="" class="fopt" />
    </div>';

    echo '
    <div class="block sblock">
        <input type="hidden" class="bid" value="'.$b_id.'" />
        <div class="bL">
            '.($ort == 'L'?$input:'').'
        </div>
        <div class="bR">
            '.($ort == 'R'?$input:'').'
        </div>
        <div class="anfasser"></div>
    </div>';
}
elseif($a == 2) // Formularelement anlegen und HTML zurückliefern
{
    $b_id = Strings::createID();

    echo '
    <div class="block sblock">
        <input type="hidden" class="bid" value="'.$b_id.'" />
        <div class="bL"></div>
        <div class="bR"></div>
        <div class="anfasser"></div>
    </div>';
}
elseif($a == 3) // Formularelement Optionen
{
    $fid = $fksdb->save($_POST['fid']);
    $name = $fksdb->save($_POST['name']);
    $type = $fksdb->save($_POST['type']);
    $opt = $base->db_to_array($_POST['opt']);

    echo '<h1>'. $trans->__('Optionen für &quot;%1&quot;', false, array(Strings::cut($name, 22))) .'</h1>';

    echo '
    <form id="feld_optionen">
    <div class="box">
        <table id="fele_edit">
            <tr>';
                if($type == 'string')
                {
                    echo '
                    <td colspan="2">
                        <textarea class="text" name="fname">'.$name.'</textarea>
                    </td>';
                }
                else
                {
                    echo '
                    <td class="a">
                        '. $trans->__('Name des Formularelementes') .'
                    </td>
                    <td>
                        <input type="text" name="fname" class="fname" value="'.$name.'" />
                    </td>';
                }
                echo '
            </tr>
            <tr>
                <td class="a">Zeilenumbruch einf&uuml;gen</td>
                <td>
                    <select name="br">
                        <option value="0"'.($opt['br'] == 0?' selected="selected"':'').'>'. $trans->__('Kein Zeilenumbruch') .'</option>
                        <option value="1"'.($opt['br'] == 1?' selected="selected"':'').'>'. $trans->__('Zeilenumbruch vor dem Element') .'</option>
                        <option value="2"'.($opt['br'] == 2?' selected="selected"':'').'>'. $trans->__('Zeilenumbruch nach dem Element') .'</option>
                        <option value="3"'.($opt['br'] == 3?' selected="selected"':'').'>'. $trans->__('Zeilenumbruch vor und nach dem Element') .'</option>
                    </select>
                </td>
            </tr>
            <tr'.($type != 'checkbox'?' style="display:none;"':'').'>
                <td class="a">'. $trans->__('Standardmäßig ausgewählt?') .'</td>
                <td>
                    <input type="checkbox" name="checked" value="1"'.($opt['checked']?' checked="checked"':'').' />
                </td>
            </tr>
            <tr'.($type != 'img'?' style="display:none;"':'').'>
                <td class="a">'. $trans->__('Upload Ordner wählen') .'</td>
                <td class="upload_dir">
                    <button class="choose_dir">'. $trans->__('Ordner wählen') .'</button>
                    <p class="current_dir">
                        '.($opt['dir']?
                            $fksdb->data("SELECT titel FROM ".SQLPRE."files WHERE id = '".$opt['dir']."' AND isdir = '1' LIMIT 1", "titel")
                            :
                            $trans->__('Hauptverzeichnis')
                        ).'
                    </p>
                    <input type="hidden" name="dir" value="'.intval($opt['dir']).'" />
                </td>
            </tr>
            <tr'.($type == 'string' || $type == 'radio' || $type == 'select' || $type == 'img'?' style="display:none;"':'').'>
                <td class="a">'. $trans->__('Ist dieses Feld ein Pflichtfeld?') .'</td>
                <td>
                    <table class="pflicht">
                        <tr>
                            <td class="g"><input type="radio" name="pflicht" value="0"'.(!$opt['pflicht']?' checked="checked"':'').' id="pflichtf_A" /><td>
                            <td><label for="pflichtf_A">'. $trans->__('Nein, dieses Feld muss nicht ausgef&uuml;llt werden.') .'</label></td>
                        </tr>
                        <tr>
                            <td class="g"><input type="radio" name="pflicht" value="1"'.($opt['pflicht']?' checked="checked"':'').' id="pflichtf_B" /><td>
                            <td><label for="pflichtf_B">'. $trans->__('Ja, dieses Feld ist ein Pflichtfeld. Folgende Bedingung:') .'</label></td>
                        </tr>
                        '.($type != 'radio' && $type != 'select'?'
                        <tr class="pflicht_optional"'.(!$opt['pflicht']?' style="display:none;"':'').'>
                            <td class="g"><td>
                            <td>
                                <select name="pflicht_laenge">
                                    '.($type == 'text' || $type == 'textarea' || $type == 'password'?'
                                    <option value="min_1"'.($opt['pflicht_laenge'] == 'min_1'?' selected="selected"':'').'>'. $trans->__('Mindestens 1 Zeichen') .'</option>
                                    <option value="min_2"'.($opt['pflicht_laenge'] == 'min_2'?' selected="selected"':'').'>'. $trans->__('Mindestens 3 Zeichen') .'</option>
                                    <option value="min_3"'.($opt['pflicht_laenge'] == 'min_3'?' selected="selected"':'').'>'. $trans->__('Mindestens 5 Zeichen') .'</option>
                                    <option value="min_4"'.($opt['pflicht_laenge'] == 'min_4'?' selected="selected"':'').'>'. $trans->__('Mindestens 15 Zeichen') .'</option>
                                    <option value="min_5"'.($opt['pflicht_laenge'] == 'min_5'?' selected="selected"':'').'>'. $trans->__('Mindestens 200 Zeichen') .'</option>
                                    ':'').'
                                    '.($type == 'checkbox'?'
                                    <option value="is_checked"'.($opt['pflicht_laenge'] == 'is_checked'?' selected="selected"':'').'>'. $trans->__('Checkbox muss angew&auml;hlt sein') .'</option>
                                    <option value="not_checked"'.($opt['pflicht_laenge'] == 'not_checked'?' selected="selected"':'').'>'. $trans->__('Checkbox darf nicht angew&auml;hlt sein') .'</option>
                                    ':'').'
                                </select>
                            </td>
                        </tr>':'').'
                    </table>
                </td>
            </tr>
            <tr'.($type == 'text' || $type == 'textarea' || $type == 'password'?'':' style="display:none;"').'>
                <td class="a">'. $trans->__('Welche Zeichen sind zugelassen?') .'</td>
                <td>
                    <select name="pflicht_zeichen">
                        <option value=""'.(!$opt['pflicht_zeichen']?' selected="selected"':'').'>'. $trans->__('Keine Beschr&auml;nkung') .'</option>
                        <option value="email"'.($opt['pflicht_zeichen'] == 'email'?' selected="selected"':'').'>'. $trans->__('Valide E-Mail-Adresse') .'</option>
                        <option value="url"'.($opt['pflicht_zeichen'] == 'url'?' selected="selected"':'').'>'. $trans->__('Valide URL') .'</option>
                        <option value="int"'.($opt['pflicht_zeichen'] == 'int'?' selected="selected"':'').'>'. $trans->__('Nur Ziffern') .'</option>
                        <option value="alpha"'.($opt['pflicht_zeichen'] == 'alpha'?' selected="selected"':'').'>'. $trans->__('Nur Buchstaben') .'</option>
                        <option value="k_sonder"'.($opt['pflicht_zeichen'] == 'k_sonder'?' selected="selected"':'').'>'. $trans->__('Keine Sonderzeichen') .'</option>
                    </select>
                </td>
            </tr>
            <tr'.($type == 'text' || $type == 'textarea' || $type == 'password' || $type == 'select'?'':' style="display:none;"').'>
                <td class="a">'. $trans->__('Breite des Feldes?') .'</td>
                <td>
                    <select name="width">
                        <option value=""'.(!$opt['width']?' selected="selected"':'').'>'. $trans->__('Automatische Breite') .'</option>
                        <option value="20"'.($opt['width'] == '20'?' selected="selected"':'').'>'. $trans->__('sehr klein (20 Pixel)') .'</option>
                        <option value="60"'.($opt['width'] == '60'?' selected="selected"':'').'>'. $trans->__('klein (60 Pixel)') .'</option>
                        <option value="140"'.($opt['width'] == '140'?' selected="selected"':'').'>'. $trans->__('mittel (140 Pixel)') .'</option>
                        <option value="210"'.($opt['width'] == '210'?' selected="selected"':'').'>'. $trans->__('groß (210 Pixel)') .'</option>
                        <option value="340"'.($opt['width'] == '340'?' selected="selected"':'').'>'. $trans->__('sehr groß (340 Pixel)') .'</option>
                        <option value="460"'.($opt['width'] == '460'?' selected="selected"':'').'>'. $trans->__('sehr groß (460 Pixel)') .'</option>
                        <option value="580"'.($opt['width'] == '580'?' selected="selected"':'').'>'. $trans->__('sehr groß (580 Pixel)') .'</option>
                        <option value="1000"'.($opt['width'] == '1000'?' selected="selected"':'').'>'. $trans->__('volle Breite') .'</option>
                    </select>
                </td>
            </tr>
            <tr'.($type == 'textarea'?'':' style="display:none;"').'>
                <td class="a">'. $trans->__('H&ouml;he des Feldes?') .'</td>
                <td>
                    <select name="height">
                        <option value=""'.(!$opt['height']?' selected="selected"':'').'>'. $trans->__('Automatische H&ouml;he') .'</option>';
                        for($x=1; $x < 100; $x++)
                            echo '<option value="'.$x.'"'.($opt['height'] == $x?' selected="selected"':'').'>'.$x.' Zeile'.($x > 1?'n':'').' (ca. '.($x * 16).' Pixel)</option>';
                        echo '
                    </select>
                </td>
            </tr>
            <tr'.($type == 'radio' || $type == 'select'?'':' style="display:none;"').'>
                <td class="a">'. $trans->__('Auswahlm&ouml;glichkeiten bearbeiten') .'</td>
                <td class="auswahl">
                    <p class="ignore">
                        '. $trans->__('Auswahl <strong></strong>, Beschriftung:') .'
                        <input type="text" name="auswahl[]" value="" />
                        <a class="del"><img src="images/delete.png" alt="Entfernen" title="'. $trans->__('Auswahl entfernen') .'" /></a>
                        <span class="schieber"><img src="images/button_verschieben.png" alt="'. $trans->__('Schieber') .'" /></span>
                    </p>
                    <p>
                        '. $trans->__('Auswahl <strong>1</strong>, Beschriftung:') .'
                        <input type="text" name="auswahl[]" value="'.$opt['auswahl'][0].'" />
                    </p>';
                    for($x = 1; $x < count($opt['auswahl']); $x++)
                    {
                        echo '
                        <p>
                            '. $trans->__('Auswahl <strong>%1</strong>, Beschriftung:', false, array(($x + 1))) .'
                            <input type="text" name="auswahl[]" value="'.$opt['auswahl'][$x].'" />
                            <a class="del"><img src="images/delete.png" alt="Entfernen" title="'. $trans->__('Auswahl entfernen') .'" /></a>
                            <span class="schieber"><img src="images/formular_anfasser.png" alt="'. $trans->__('Schieber') .'" /></span>
                        </p>';
                    }
                    echo '
                    <a class="add">'. $trans->__('weitere Auswahlm&ouml;glichkeit hinzuf&uuml;gen') .'</a>
                </td>
            </tr>
            <tr'.($type == 'radio'?'':' style="display:none;"').'>
                <td class="a">'. $trans->__('Darstellung der Felder') .'</td>
                <td>
                    <select name="radio_string">
                        <option value="0"'.($opt['radio_string'] == 0?' selected="selected"':'').'>'. $trans->__('Beschriftung vor der Auswahlbox') .'</option>
                        <option value="1"'.($opt['radio_string'] == 1?' selected="selected"':'').'>'. $trans->__('Beschriftung nach der Auswahlbox') .'</option>
                    </select>
                    <br /><br />
                    <select name="radio_flat">
                        <option value="0"'.($opt['radio_flat'] == 0?' selected="selected"':'').'>'. $trans->__('Zeilenumbruch nach jeder Auswahlbox') .'</option>
                        <option value="1"'.($opt['radio_flat'] == 1?' selected="selected"':'').'>'. $trans->__('Alle Auswahlboxen in einer Reihe') .'</option>
                    </select>
                </td>
            </tr>

            <tr class="delete">
                <td class="a">'. $trans->__('Dieses Feld entfernen?') .'</td>
                <td>
                    <button id="del_feld">'. $trans->__('Feld l&ouml;schen') .'</button>
                </td>
            </tr>
        </table>
    </div>
    </form>';

    echo '
    <div class="box_save" style="display:block;">
        <input type="button" value="'. $trans->__('verwerfen') .'" class="bs1" />
        <input type="button" value="'. $trans->__('Speichern') .'" class="bs2" />
    </div>';
}
elseif($a == 4) // Serialize Optionen verarbeiten
{
    parse_str($_POST['feld_opt'], $feld_opt);

    if(is_array($feld_opt['auswahl']))
    {
        $counter_auswahl = 0;
        $new_auswahl = array();
        foreach($feld_opt['auswahl'] as $a)
        {
            $a = $fksdb->save($a);

            if(!empty($a))
            {
                $new_auswahl[$counter_auswahl] = $a;
                $counter_auswahl ++;
            }
        }
    }

    $opt = array(
        'pflicht' => $feld_opt['pflicht'],
        'pflicht_laenge' => $feld_opt['pflicht_laenge'],
        'pflicht_zeichen' => $feld_opt['pflicht_zeichen'],
        'br' => $feld_opt['br'],
        'width' => $feld_opt['width'],
        'height' => $feld_opt['height'],
        'checked' => $feld_opt['checked'],
        'auswahl' => $new_auswahl,
        'radio_string' => $feld_opt['radio_string'],
        'radio_flat' => $feld_opt['radio_flat'],
        'dir' => $feld_opt['dir']
    );

    echo $base->array_to_db($opt).'{stop]'.Strings::cut(htmlspecialchars_decode($_POST['feld_name']), 100);
}
elseif($a == 5) // Feldzuordnungen
{
    $ztype = $fksdb->save($_POST['ztype']);
    $ztypen = array('fzo_1' => 'benutzer', 'fzo_2' => 'dokument', 'fzo_3' => 'produkt');
    $type = $ztypen[$ztype];

    $form = $_POST['f'];
    parse_str($form, $fo);
    $fa = $fo['f'];
    if(!is_array($fa)) $fa = array();

    $o = $base->db_to_array($_POST['opt']);

    if($type == 'benutzer')
    {
        $possible_fields = array(
            'email' => 'E-Mail-Adresse',
            'vorname' => 'Vorname',
            'nachname' => 'Nachname',
            'anrede' => 'Anrede',
            'namenszusatz' => 'Namenszusatz',
            'str' => 'Stra&szlig;e',
            'hn' => 'Hausnummer',
            'plz' => 'PLZ',
            'ort' => 'Ort',
            'land' => 'Land',
            'tel_p' => 'Telefon Privat',
            'tel_g' => 'Telefon Gesch&auml;ftlich',
            'mobil' => 'Mobiltelefon',
            'fax' => 'Fax',
            'tags' => 'Notizen',
            'pw' => 'Passwort'
        );
    }
    else
    {
        $ordner = '../content/'.($type == 'dokument'?'d':'p').'klassen/';
        $datei = $o['klasse'];

        $possible_fields = array();
        $possible_images = array();

        $fk = $base->open_dklasse($ordner.$datei.'.php');

        if($fk['related'])
        {
            $fk = $base->open_dklasse($ordner.$fk['related']);
            $tmp_inhalt = $fk['content'];

            $fk = $base->open_dklasse($ordner.$datei.'.php');
            $fk['content'] = $tmp_inhalt;
        }

        $result = preg_match_all('@:(.*)\):@iU', $fk['content'], $subpattern);
        $btypen = array();

        foreach($subpattern[1] as $s1 => $s2)
        {
            $b = explode('(', $s2);

            if(in_array($b[0], $base->getBlocks('dclass')))
            {
                $block_type = array_search($b[0], $base->getBlocks('dclass'));
                $atr = $base->get_attributes($b[1]);

                $btypen[$block_type] += 1;

                if($atr['name'])
                {
                    $bid = $base->slug($atr['name']);
                    $bname = $atr['name'];
                }
                else
                {
                    $bid = $block_type.'_'.$btypen[$block_type];
                    $bname = $base->getBlockByID($block_type, 'de');
                }


                if($block_type < 30)
                    $possible_fields[$bid] = $bname;
                elseif($block_type == 30)
                    $possible_images[$bid] = $bname;
                elseif($block_type == 1005)
                    $possible_fields[$bid] = $bname;
            }
        }
    }

    echo '
    <h1>'. $trans->__('Feldzuordnungen &amp; Einstellungen.') .'</h1>

    <form id="feld_zuordnungen">
    <div class="box intro">
        <strong>';

        if($type == 'benutzer')
            echo $trans->__('Sie möchten mit diesem Formular automatisch Benutzer erzeugen');
        elseif($type == 'dokument')
            echo $trans->__('Sie möchten mit diesem Formular automatisch Dokumente erzeugen');
        else
            echo $trans->__('Sie möchten mit diesem Formular automatisch Produkte erzeugen');

        echo '
        </strong>
    </div>
    <div class="box">
        <table class="zuordnung">
            <tr>
                <td colspan="3" class="head1"><h2 class="calibri">'. $trans->__('Formularfelder.') .'</h2></td>
                <td><h2 class="calibri">Felder '.($type == 'benutzer'?$trans->__('des Benutzerkontos'):($type == 'dokument'?$trans->__('der Dokumentenklasse'):$trans->__('der Produktklasse'))).'.</h2></td>
            </tr>';
            foreach($fa as $f_id => $f)
            {
                if($f['type'] == 'string' || !$f['name'])
                    continue;

                echo '
                <tr>
                    <td class="feldname">'. $trans->__('Feldname:') .'</td>
                    <td class="feldwert">'.$f['name'].'</td>
                    <td><img src="images/pfeil_'.($o['feld'][$f_id]?'blau':'weiss').'.png" alt="" /></td>
                    <td>
                        <select name="feld['.$f_id.']">
                            <option value="">'. $trans->__('keine Zuordnung') .'</option>';

                            if($f['type'] == 'img')
                            {
                                foreach($possible_images as $pk => $pv)
                                    echo '<option value="'.$pk.'"'.($o['feld'][$f_id] == $pk?' selected="selected"':'').'>'.$pv.'</option>';
                            }
                            else
                            {
                                foreach($possible_fields as $pk => $pv)
                                    echo '<option value="'.$pk.'"'.($o['feld'][$f_id] == $pk?' selected="selected"':'').'>'.$pv.'</option>';
                            }
                        echo '
                        </select>
                    </td>
                </tr>';
            }
            echo '
            <tr class="abstand"><td colspan="4"></td></tr>';

            for($x = 0; $x < count($o['notiz']) || $x < 1; $x++)
            {
                echo '
                <tr class="notiz">
                    <td class="feldname" colspan="2">
                        Text: <input type="text" name="notiz[]" value="'.$o['notiz'][$x].'" />
                    </td>
                    <td><img src="images/pfeil_'.($o['notiz'][$x]?'blau':'weiss').'.png" alt="" /></td>
                    <td>
                        <select name="notiz_feld[]">
                            <option value="">'. $trans->__('keine Zuordnung') .'</option>';
                            foreach($possible_fields as $pk => $pv)
                            {
                                echo '<option value="'.$pk.'"'.($o['notiz_feld'][$x] == $pk?' selected="selected"':'').'>'.$pv.'</option>';
                            }
                        echo '
                        </select>
                    </td>
                </tr>';
            }
            echo '
            <tr class="last">
                <td colspan="3"></td>
                <td><a id="add_notiz">'. $trans->__('Weiteres Textfeld hinzufügen') .'</a></td>
            </tr>';

            if($type == 'dokument')
            {
                $meta_values = array(
                    'title' => 'Titel',
                    'meta_title' => 'HTML-Titel',
                    'meta_descr' => 'HTML-Beschreibung',
                    'meta_keywords' => 'HTML-Schlüsselworte'
                );

                echo '
                <tr class="abstand"><td colspan="4"></td></tr>

                <tr>
                    <td colspan="3" class="head1"><h2 class="calibri">'. $trans->__('Formularfelder.') .'</h2></td>
                    <td><h2 class="calibri">'. $trans->__('Felder des Dokuments.') .'</h2></td>
                </tr>';
                foreach($meta_values as $mkey => $mval)
                {
                    echo '
                    <tr>
                        <td colspan="2">
                            <select name="feld_meta['.$mkey.']">
                                <option value="">'. $trans->__('keine Zuordnung') .'</option>';
                                foreach($fa as $f_id => $f)
                                {
                                    if(!$f['name'] || $f['type'] == 'string' || $f['type'] == 'img')
                                        continue;

                                    echo '<option value="'.$f_id.'"'.($o['feld_meta'][$mkey] == $f_id?' selected="selected"':'').'>'.$f['name'].'</option>';
                                }
                            echo '
                            </select>
                        </td>
                        <td><img src="images/pfeil_'.($o['feld_meta'][$mkey]?'blau':'weiss').'.png" alt="" /></td>
                        <td>'.$mval.'</td>
                    </tr>';
                }
            }

        echo '
        </table>
    </div>

    <div class="box">
        <h2 class="calibri">'. $trans->__('Einstellungen.') .'</h2>';

        if($type == 'benutzer')
        {
            echo '
            <div class="unterbox">
                <div class="ubL">
                    '. $trans->__('Mit diesem Formular generierte Benutzer werden folgende Rollen zugeordnet:') .'
                </div>
                <div class="ubR">';
                    if(!is_array($o['rollen']))
                        $o['rollen'] = array();

                    $rolQ = $fksdb->query("SELECT id, titel FROM ".SQLPRE."roles WHERE id != '1' AND papierkorb = '0' ORDER BY sort, id");
                    while($rol = $fksdb->fetch($rolQ))
                    {
                        echo '
                        <input type="checkbox" id="role_'.$rol->id.'" name="rollen[]" value="'.$rol->id.'"'.(in_array($rol->id, $o['rollen'])?' checked="checked"':'').' />
                        <label for="role_'.$rol->id.'">'.$rol->titel.'</label><br />';
                    }
                echo '
                </div>
            </div>
            <div class="unterbox">
                <div class="ubL">
                    '. $trans->__('Mit diesem Formular generierte Benutzer erhalten folgende Benutzergruppe:') .'
                </div>
                <div class="ubR">
                    <input type="radio" id="begrp_1" name="type" value="1"'.($o['type'] == 1 || !isset($o['type'])?' checked="checked"':'').' />
                    <label for="begrp_1">Kunde</label><br />
                    <input type="radio" id="begrp_2" name="type" value="2"'.($o['type'] == 2?' checked="checked"':'').' />
                    <label for="begrp_2">Mitarbeiter</label><br />
                    <input type="radio" id="begrp_3" name="type" value="3"'.($o['type'] == 3?' checked="checked"':'').' />
                    <label for="begrp_3">Kunde &amp; Mitarbeiter</label><br />
                </div>
            </div>
            <div class="unterbox">
                <div class="ubL">
                    '. $trans->__('Mit diesem Formular generierte Benutzer erhalten folgenden Benutzerstatus:') .'
                </div>
                <div class="ubR">
                    <select name="status" class="status">
                        <option value="0"'.($o['status'] == 0?' selected="selected"':'').'>'. $trans->__('aktiv - keine Aktivierung erforderlich') .'</option>
                        <option value="1"'.($o['status'] == 1?' selected="selected"':'').'>'. $trans->__('inaktiv - Aktivierung erforderlich') .'</option>
                        <option value="2"'.($o['status'] == 2?' selected="selected"':'').'>'. $trans->__('gesperrt - Aktivierung nur durch Administrator möglich') .'</option>
                    </select>
                </div>
            </div>
            <div class="unterbox">
                <div class="ubL">
                    '. $trans->__('Benutzern wird nach Absenden des Formulars automatisch eine Email (mit Aktivierungslink) gesendet (Double-Opt-In).') .'
                </div>
                <div class="ubR">
                    <input type="radio" name="mail" value="0"'.(!$o['mail']?' checked="checked"':'').' id="akmail0" />
                    <label for="akmail0">'. $trans->__('Nein, Email nicht zusenden.') .'</label><br />
                    <input type="radio" name="mail" value="1"'.($o['mail']?' checked="checked"':'').' id="akmail1" />
                    <label for="akmail1">'. $trans->__('Ja, Email zusenden.') .'</label>
                </div>
            </div>
            <div class="unterbox akt_wl"'.($o['status'] != 1?' style="display:none;"':'').'>
                <div class="ubL">
                    '. $trans->__('Nach erfolgreicher Aktivierung werden Benutzer auf das folgende Strukturelement weitergeleitet.') .'
                </div>
                <div class="ubR">
                    <button class="ele_choose">'. $trans->__('Strukturelement ausw&auml;hlen') .'</button>
                    <p class="ele_choosen">'.$fksdb->data("SELECT titel FROM ".SQLPRE."elements WHERE id = '".$o['akt_ok']."' LIMIT 1", "titel").'</p>
                    <input type="hidden" name="akt_ok" value="'.$o['akt_ok'].'" />
                </div>
            </div>
            <div class="unterbox akt_wl"'.($o['status'] != 1?' style="display:none;"':'').'>
                <div class="ubL">
                    '. $trans->__('Nach fehlgeschlagener Aktivierung werden Benutzer auf das folgende Strukturelement weitergeleitet.') .'
                </div>
                <div class="ubR">
                    <button class="ele_choose">'. $trans->__('Strukturelement auswählen') .'</button>
                    <p class="ele_choosen">'.$fksdb->data("SELECT titel FROM ".SQLPRE."elements WHERE id = '".$o['akt_fehler']."' LIMIT 1", "titel").'</p>
                    <input type="hidden" name="akt_fehler" value="'.$o['akt_fehler'].'" />
                </div>
            </div>
            <div class="unterbox textbox"'.(!$o['mail']?' style="display:none;"':'').'>
                <p class="betreff">
                    Email-Betreff:
                    <input type="text" name="betreff" value="'.$o['betreff'].'" />
                </p>
                <textarea name="mailtext" id="zmailtext">'.$o['mailtext'].'</textarea>

                <div class="zusatz">
                    <h2 class="calibri">'. $trans->__('Platzhalter für<br />Emailtext und Betreff:') .'</h2>
                    <div class="zR">
                        <p class="akt_link"'.($o['status'] != 1?' style="display:none;"':'').'>
                            <strong>'. $trans->__('Link zum Aktivieren des Benutzerkontos:') .'</strong>
                            <a rel="[aktivierungslink]" title="'. $trans->__('Aktivierungslink') .'">[aktivierungslink]</a>
                        <p>
                        <p class="all">';
                            foreach($fa as $f_id => $f)
                            {
                                if($f['type'] == 'string' || !$f['name'])
                                    continue;

                                echo '<a rel="['.$base->slug($f['name']).']" title="'.$f['name'].'">['.$base->slug($f['name']).']</a>';
                            }
                        echo '
                        </p>
                        <p class="erkl">
                            '. $trans->__('<strong>Beispiel:</strong> Der Platzhalter [vorname] greift auf das Feld &quot;Vorname&quot; des Benutzerkontos zu. Trägt der Benutzer in dem Formular den Namen &quot;Mario&quot; in dieses Feld ein, wird dieser Name in die Email eingetragen. Wichtig: Der Platzhalter muss den korrekten Namen des Benutzerkonto-Feldes (oben, rechte Spalten) verwenden; nicht den Namen des Formularfeldes (oben, linke Spalte).<br />
                            Die Platzhalter können ebenfalls im <strong>Email-Betreff</strong> genutzt werden.') .'
                        </p>
                    </div>
                </div>
            </div>';
        }
        else
        {
            $dpname = ($type == 'dokument'?$trans->__('Dokument'):$trans->__('Produkt'));

            echo '
            <input type="hidden" name="klasse" value="'.$o['klasse'].'" />

            <div class="unterbox">
                <div class="ubL">
                    '. $trans->__('Mit diesem Formular generierte %1e erhalten folgenden Status:', false, array($dpname)) ,'
                </div>
                <div class="ubR">
                    <select name="status" class="status">
                        <option value="0"'.($o['status'] == 0?' selected="selected"':'').'>'. $trans->__('%1 nur speichern', false, array($dpname)) .'</option>
                        <option value="1"'.($o['status'] == 1?' selected="selected"':'').'>'. $trans->__('%1 zur Freigabe vorlegen', false, array($dpname)) .'</option>
                        <option value="2"'.($o['status'] == 2?' selected="selected"':'').'>'. $trans->__('%1 unmittelbar freigeben', false, array($dpname)) .'</option>
                    </select>
                </div>
            </div>
            <div class="unterbox rewrite_taget"'.($o['status'] != 2?' style="display:none;"':'').'>
                <div class="ubL">
                    '. $trans->__('Nach Absenden des Formulares auf das neu erstellte Dokument weiterleiten, falls automatisch ein Strukturelement erstellt wird?') .'
                </div>
                <div class="ubR">
                    <input type="checkbox" name="rewrite_taget" id="rewrite_taget_cb" value="1"'.($o['rewrite_taget']?' checked':'').' />
                    <label for="rewrite_taget_cb">'. $trans->__('Ja, weiterleiten') .'</label>
                </div>
            </div>';
        }
    echo '
    </div>
    </form>

    <div class="box_save" style="display:block;">
        <input type="button" value="'. $trans->__('verwerfen') .'" class="bs1" /> <input type="button" value="'. $trans->__('Speichern') .'" class="bs2" />
    </div>';
}
elseif($a == 6) // Feldzuordnungen verarbeiten
{
    parse_str($_POST['opt'], $opt);

    if(is_array($opt['notiz']) && is_array($opt['notiz_feld']))
    {
        $counter_auswahl = 0;
        $new_notiz = array();
        $new_notiz_feld = array();

        for($x = 0; $x < count($opt['notiz']); $x++)
        {
            $a = $fksdb->save($opt['notiz'][$x]);
            $b = $fksdb->save($opt['notiz_feld'][$x]);

            if(!empty($a) && !empty($b))
            {
                $new_notiz[$counter_auswahl] = $a;
                $new_notiz_feld[$counter_auswahl] = $b;
                $counter_auswahl ++;
            }
        }
    }

    $optS = array(
        'klasse' => $opt['klasse'],
        'feld' => $opt['feld'],
        'feld_meta' => $opt['feld_meta'],
        'notiz' => $new_notiz,
        'notiz_feld' => $new_notiz_feld,
        'rollen' => $opt['rollen'],
        'type' => $opt['type'],
        'status' => $opt['status'],
        'mail' => $opt['mail'],
        'betreff' => $opt['betreff'],
        'mailtext' => $opt['mailtext'],
        'akt_ok' => $opt['akt_ok'],
        'akt_fehler' => $opt['akt_fehler'],
        'rewrite_taget' => $opt['rewrite_taget']
    );

    echo $base->array_to_db($optS);
}
elseif($a == 7) // Feldzuordnungen Dokumentenklasse wählen im Vorraus
{
    $ztype = $fksdb->save($_POST['ztype']);
    $ztypen = array('fzo_1' => 'benutzer', 'fzo_2' => 'dokument', 'fzo_3' => 'produkt');
    $type = $ztypen[$ztype];

    $o = $base->db_to_array($_POST['opt']);

    $dkklassen_inc = array();
    $ordner = '../content/'.($type == 'dokument'?'d':'p').'klassen';

    if(is_dir($ordner))
    {
        $handle = opendir($ordner);
        while($file = readdir($handle))
        {
            if($file != "." && $file != "..")
            {
                $fk = $base->open_dklasse($ordner.'/'.$file);

                $dkklassen_inc[$file] = $fk;
            }
        }
    }

    echo '<h1>'.($type == 'dokument'?'Dokumenten':'Produkt').'klasse w&auml;hlen.</h1>';

    if(count($dkklassen_inc))
    {
        echo '
        <input type="hidden" name="alte_klasse" value="'.$o['klasse'].'" />';

        if($o['klasse'])
        {
            echo '
            <div class="box fehlerbox">
               '.$trans->__('Es ist bereits eine Dokumentenklasse ausgewählt. Bitte beachten Sie, dass sämtliche Zuordnungen verloren gehen, wenn Sie diese ändern.').'
            </div>';
        }

        echo '
        <div class="box">
            <select name="klasse" class="klasse">';
                foreach($dkklassen_inc as $k => $v)
                {
                    $filename = str_replace('.php', '', $k);
                    echo '<option value="'.$filename.'"'.($o['klasse'] == $filename?' selected="selected"':'').'>'.($v['name']?($v['name']):$k).'</option>';
                }
            echo '
            </select>
        </div>';
    }
    else
    {
        echo '
        <div class="box fehlerbox">
            '. $trans->__('Es existieren keine %1klassen. Um ein Formular mit einem %2 zu verkn&uuml;pfen, sind diese allerdings erforderlich.', false, array(($type == 'dokument'?'Dokumenten':'Produkt'), $type == 'dokument'?'Dokument':'Produkt')) .'
        </div>';
    }

    echo '
    <div class="box_save" style="display:block;">
        <input type="button" value="'. $trans->__('verwerfen') .'" class="bs1" /> '.(count($dkklassen_inc)?'<input type="button" value="'. $trans->__('Speichern') .'" class="bs2" />':'').'
    </div>';
}
elseif($a == 8) // Feldzuordnungen verarbeiten
{
    $optS = array(
        'klasse' => $fksdb->save($_POST['klasse'])
    );

    echo $base->array_to_db($optS);
}
elseif($a == 99)
{
    $block = $fksdb->save($_POST['block']);
    $ibid = $fksdb->save($_POST['ibid']);
    $blockindex = $fksdb->save($_POST['blockindex']);
    $id = $fksdb->save($_POST['id']);
    $f = ($_POST['f']);
    parse_str($f, $fa);

    $dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
    $dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

    if(!$dokument->klasse && !$dokument->produkt)
    {
        $upt = $fksdb->query("UPDATE ".SQLPRE."blocks SET html = '".serialize($fa)."' WHERE id = '".$block."' AND dokument = '".$id."' LIMIT 1");
        echo $fksdb->getError();
    }
    else
    {
        $ki = $base->fixedUnserialize($dve->klasse_inhalt);

        if(!$ibid)
            $ki[$block]['html'] = serialize($fa);
        else
            $ki[$ibid]['html'][$blockindex]['html'] = serialize($fa);

        $kis = serialize($ki);

        $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$dve->id."' LIMIT 1");
    }

    $d = $fksdb->fetch("SELECT dversion_edit FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
    $update = $fksdb->query("UPDATE ".SQLPRE."document_versions SET edit = '1', ende = '0', von = '".$user->getID()."', timestamp_edit = '".$base->getTime()."' WHERE id = '".$d->dversion_edit."' LIMIT 1");
    $base->create_dk_snippet($id);
}
?>
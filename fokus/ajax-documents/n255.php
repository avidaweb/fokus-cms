<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n255')
    exit($user->noRights());

function blocksymbols($typ)
{
    return (file_exists('images/blocksymbols/'.$typ.'.gif')?'images/blocksymbols/'.$typ.'.gif':'images/blocksymbols/default.gif');
}

$allowed_quick_edit = false; // quick edit temporaly disabled

$aktiv = $fksdb->save($_GET['aktiv']);
$id = $fksdb->save($_GET['id']);
$spaltenr = $fksdb->save($_GET['spaltenr']);

$d = $fksdb->fetch("SELECT * FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT * FROM ".SQLPRE."document_versions WHERE id = '".$d->dversion_edit."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

$updt = $fksdb->query("UPDATE ".SQLPRE."document_versions SET spaltennr = '".$spaltenr."' WHERE id = '".$dve->id."' LIMIT 1");

echo '
<input type="hidden" value="'.$aktiv.'" id="onlyfillmeab" />';

if(!$d->klasse && !$d->produkt)
{
    echo '<form method="post" id="block_form">';

    $ergebnis2 = $fksdb->query("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$id."' AND dversion = '".$dve->id."' AND spalte = '".$aktiv."' ORDER BY sort");
    while($row2 = $fksdb->fetch($ergebnis2))
    {
        $nname = '';
        if($row2->extb)
        {
            $bar = $api->getBlock($row2->extb);
            if($bar)
                $nname = ($bar->getShort()?$bar->getShort():$bar->getName());
        }
        else
        {
            $nname = $base->getBlockByID($row2->type, 'de');
        }

        if(!$nname)
            continue;

        echo '
        <div class="block" id="block_'.$row2->id.'">
            <div class="c">
                <div class="blockO">
                    <input type="checkbox" name="block[]" value="'.$row2->id.'" />

                    <img src="'.blocksymbols($row2->type).'" alt=" " />
                    <a class="edit titel">'.$nname.'</a>

                    <a class="edit bearbeiten">'. $trans->__('bearbeiten') .'</a>
                    '.($row2->type < 20 && $row2->type > 7 && $allowed_quick_edit?'
                    <span class="trenner"></span>
                    <a class="quickedit" rel="'.$row2->id.'">'. $trans->__('Nur Text Ändern</a>'):'').'
                </div>
                <div'.($row2->type <= 30 || $row2->type == 40 || $row2->type == 64 || $row2->type == 1050?'':' style="display:none"').' class="blockU blockU1'.($row2->type < 20 && $row2->type > 7?' blockUE':'').'">
                    '.$base->block_preview($row2->html, $row2->type, $row2->teaser, array('id' => $row2->bildid, 'w' => $row2->bildw, 'wt' => $row2->bildwt, 'extern' => $row2->bild_extern)).'
                </div>
                '.($row2->type < 20 && $row2->type > 7?'
                <div class="blockU blockU2">
                    <div class="qeditor">
                        <textarea class="text_'.$row2->type.'">'.rawurldecode(htmlspecialchars_decode(Strings::cleanString($row2->html))).'</textarea>
                        <button class="verw">'. $trans->__('Verwerfen') .'</button> <button class="save">'. $trans->__('Speichern') .'</button>
                    </div>
                </div>':'').'
            </div>
            <div class="drag"></div>
        </div>';
    }
    if(!$fksdb->count($ergebnis2))
    {
        echo '
        <div class="leer">
            '. $trans->__('In dieser Spalte befinden sich noch keine Inhalts-Elemente. Sie können neue Elemente jederzeit per Drag&Drop aus dem rechten Fenster in diese Spalte ziehen.') .'
        </div>';
    }

    echo '</form>';
}
else
{
    $ordner = '../content/dklassen/';
    $datei = $d->klasse;

    echo '<input type="hidden" name="dk_datei" value="'.$datei.'" />';

    $ki = $base->fixedUnserialize($dve->klasse_inhalt);

    $gruppen = array();
    $gruppe_offen = array();
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

    $count_notizen = 0;

    foreach($subpattern[1] as $s1 => $s2)
    {
        $b = explode('(', $s2);

        if(in_array($b[0], $base->getBlocks('dclass')))
        {
            $atr = $base->get_attributes($b[1]);
            $block_type = array_search($b[0], $base->getBlocks('dclass'));
            $btypen[$block_type] += 1;

            if($atr['name']) $bid = $base->slug($atr['name']);
            else $bid = $block_type.'_'.$btypen[$block_type];

            if($atr['gruppe'])
            {
                $gruppen[$atr['gruppe']][] = array(
                    'gruppe' => $atr['gruppe'],
                    'name' => $atr['name'],
                    'type' => $block_type,
                    'bid' => $bid,
                    'atr' => $atr
                );

                if($atr['gruppe-offen'])
                    $gruppe_offen[$atr['gruppe']] = ($atr['gruppe-offen'] == 'false'?false:true);
            }
            else
            {
                $gruppen[$bid] = array(
                    'gruppe' => 'none',
                    'name' => $atr['name'],
                    'type' => $block_type,
                    'bid' => $bid,
                    'atr' => $atr,
                    'inhaltsbereich' => false
                );
            }
        }
        elseif($b[0] == 'inhaltsbereich')
        {
            $atr = $base->get_attributes($b[1]);
            $btypen['inhaltsbereich'] += 1;

            if($atr['name']) $bid = $base->slug($atr['name']);
            else $bid = 'inhaltsbereich'.$btypen['inhaltsbereich'];

            $bloecke = $ki[$bid]['html'];
            if(!is_array($bloecke))
                $bloecke = array();

            $inhalthtml = '
            <fieldset class="inhaltsbereich">
                '.($atr['name']?'<legend>'.$atr['name'].'</legend>':'').'
                <input type="hidden" class="ib_id" value="'.$bid.'" />';

                foreach($bloecke as $k => $v)
                {
                    $nhtml = $v['html'];
                    $nteaser = $v['teaser'];
                    $ntype = $v['type'];
                    $nid = $v['id'];
                    $ext_block = $v['extb'];

                    $ex_name = $base->getBlockByID($ntype, 'de');

                    if($ext_block)
                    {
                        $ex = $api->getBlock($ext_block);
                        if(!$ex)
                            continue;

                        $ex_name = ($ex->getShort()?$ex->getShort():$ex->getName());
                        if(!$ex_name)
                            continue;
                    }

                    $inhalthtml .= '
                    <div class="block" id="block_'.$nid.'_'.$ntype.'" data-ext_block="'.$ext_block.'">
                        <input type="hidden" class="block_index" value="'.$k.'" />
                        <div class="c">
                            <div class="blockO">
                                <img src="'.blocksymbols($ntype).'" alt=" " />
                                <a class="edit titel">'.$ex_name.'</a>
                                <a class="edit bearbeiten">'. $trans->__('bearbeiten') .'</a>
                                <span class="trenner"></span>
                                <a class="del" rel="'.$bid.'!'.$k.'">'. $trans->__('entfernen') .'</a>
                            </div>
                            <div'.($ntype <= 30 || $ntype == 40 || $ntype == 64 || $ntype == 1050?'':' style="display:none"').' class="blockU blockU1'.($ntype < 20 && $ntype > 7?' blockUE':'').'">
                                '.$base->block_preview($nhtml, $ntype, $nteaser, array('id' => $v['bildid'], 'w' => $v['bildw'], 'wt' => $v['bildwt'], 'extern' => $v['bild_extern'])).'
                            </div>
                        </div>
                        <div class="drag"></div>
                    </div>';
                }

            $inhalthtml .= '
            </fieldset>';

            $gruppen[$bid] = array(
                'gruppe' => 'none',
                'name' => $atr['name'],
                'type' => 0,
                'bid' => $bid,
                'atr' => $atr,
                'inhaltsbereich' => true,
                'inhalthtml' => $inhalthtml
            );
        }
        elseif($b[0] == 'notiz')
        {
            $atr = $base->get_attributes($b[1]);
            $count_notizen ++;

            $bid = 'notiz_'.$count_notizen;
            $notiz = '<h3 class="calibri notiz notiz_'.($atr['size']?$atr['size']:'medium').'">'.($atr['text']?$atr['text']:'Abschnitt '.$count_notizen).'</h3>';

            $gruppen[$bid] = array(
                'gruppe' => 'none',
                'notiz' => $notiz
            );
        }
    }

    // Elemente wieder auflösen
    foreach($gruppen as $gname => $g)
    {
        // Falls keine Gruppe
        if($g['gruppe'] == 'none')
        {
            $bid = $g['bid'];
            $block_type = $g['type'];
            $atr = $g['atr'];
            $html = '';

            if($g['inhaltsbereich']) // Falls Inhaltsbereich
            {
                echo $g['inhalthtml'];
            }
            elseif($g['notiz']) // Falls Notiz
            {
                echo $g['notiz'];
            }
            elseif($block_type == 1 || $block_type == 4 || $block_type == 5 || $block_type == 1005) // Falls Element direkt dargestellt werden soll
            {
                $feld = '';
                if($block_type == 1)
                {
                    $feld = '<input type="text" id="bid_'.$bid.'" name="'.$bid.'" value="'.$ki[$bid]['html'].'"'.($atr['feldbreite']?' style="width:'.$atr['feldbreite'].'px;"':'').' />';
                }
                elseif($block_type == 4)
                {
                    $werte = explode('|', $atr['werte']);

                    $feld = '<select id="bid_'.$bid.'" name="'.$bid.'">';
                    foreach($werte as $w)
                        $feld .= '<option value="'.$w.'"'.($ki[$bid]['html'] == $w?' selected="selected"':'').'>'.$w.'</option>';
                    $feld .= '</select>';
                }
                elseif($block_type == 5)
                {
                    $werte = explode('|', $atr['werte']);
                    $realvalues = $base->db_to_array($ki[$bid]['html']);

                    $feld = '';
                    foreach($werte as $k => $w)
                    {
                        $feld .= '
                        <span class="checkbox">
                            <input type="checkbox" id="bid_'.$bid.'_'.$k.'" data-id="'.$bid.'" data-nr="'.$k.'" name="'.$bid.'[]" value="'.$w.'"'.(in_array($w, $realvalues)?' checked':'').' />
                            <label for="bid_'.$bid.'_'.$k.'">'.$w.'</label>
                        </span>';
                    }
                }
                elseif($block_type == 1005)
                {
                    $feld = '
                    <input type="text" id="bid_'.$bid.'" name="'.$bid.'" value="'.$ki[$bid]['html'].'" class="datepicker" />
                    <em>(Format: '.date('d.m.Y').')</em>';
                }

                echo '
                <div class="block nodrag wertblock">
                    <form>
                    <table class="wert">
                        <tr class="firstrow">
                            <td class="a">
                                <img src="'.blocksymbols($block_type).'" alt=" " />
                                <strong>'.($atr['name']?$atr['name']:$base->getBlockByID($block_type, 'de')).'</strong>
                            </td>
                            <td class="felder">
                                '.$feld.'
                            </td>
                        </tr>
                    </table>
                    </form>
                    <div class="speichern">
                        <a class="save">'. $trans->__('Speichern') .'</a> | <a class="verw">'. $trans->__('Verwerfen') .'</a>
                    </div>
                </div>';
            }
            else // Falls normales Element
            {
                echo '
                <div class="block nodrag" id="block_'.$bid.'_'.$block_type.'" data-name="'.$atr['name'].'" data-ext_block="'.$atr['block'].'">
                    <div class="c">
                        <div class="blockO">
                            <img src="'.blocksymbols($block_type).'" alt=" " />
                            <a class="edit titel">'.($atr['name']?$atr['name']:$base->getBlockByID($block_type, 'de')).'</a>
                            <a class="edit bearbeiten">'. $trans->__('bearbeiten') .'</a>
                            '.($block_type < 20 && $block_type > 7 && $allowed_quick_edit?'
                            <span class="trenner"></span>
                            <a class="quickedit" rel="'.$bid.'">'. $trans->__('Nur Text Ändern</a>'):'').'
                        </div>
                        <div'.($block_type <= 30 || $block_type == 40 || $block_type == 64 || $block_type == 1050?'':' style="display:none"').' class="blockU blockU1'.($block_type < 20 && $block_type > 7?' blockUE':'').'">
                            '.$base->block_preview($ki[$bid]['html'], $block_type, $ki[$bid]['teaser'], array('id' => $ki[$bid]['bildid'], 'w' => $ki[$bid]['bildw'], 'wt' => $ki[$bid]['bildwt'], 'extern' => $ki[$bid]['bild_extern']), $atr['name']).'
                        </div>
                        '.($block_type < 20 && $block_type > 7?'
                        <div class="blockU blockU2">
                            <div class="qeditor">
                                <textarea class="text_'.$block_type.'">'.rawurldecode(htmlspecialchars_decode(Strings::cleanString($ki[$bid]['html']))).'</textarea>
                                <button class="save">'. $trans->__('Speichern') .'</button> <button class="verw">'. $trans->__('Verwerfen') .'</button> &nbsp;
                            </div>
                        </div>':'').'
                    </div>
                    <div class="drag"></div>
                </div>';
            }
        }
        else // Falls Gruppe
        {
            $openable = (isset($gruppe_offen[$gname])?true:false);
            $is_open = false;
            if($openable) $is_open = $gruppe_offen[$gname];

            echo '
            <div class="block nodrag wertblock wertgruppe'.($openable?' openable':'').'">
                '.($openable?'<a class="rbutton '.($is_open?'rollin':'rollout').'">Gruppe <span>'.($is_open?'schließen':'öffnen').'</span></a>':'').'
                <form>
                <table class="wert">
                    <tr class="firstrow">
                        <td class="a" colspan="2">
                            <img src="'.blocksymbols($g['type']).'" alt=" " />
                            <strong>'.$gname.'</strong>
                        </td>
                    </tr>
                    <tr class="show_werte"><td colspan="2"></td></tr>';
                    foreach($g as $b)
                    {
                        $atr = $b['atr'];

                        $feld = '';
                        if($b['type'] == 1)
                        {
                            $feld = '<input type="text" id="bid_'.$b['bid'].'" name="'.$b['bid'].'" value="'.$ki[$b['bid']]['html'].'"'.($atr['feldbreite']?' style="width:'.$atr['feldbreite'].'px;"':'').' />';
                        }
                        elseif($b['type'] == 4)
                        {
                            $werte = explode('|', $atr['werte']);

                            $feld = '<select id="bid_'.$b['bid'].'" name="'.$b['bid'].'">';
                            foreach($werte as $w)
                                $feld .= '<option value="'.$w.'"'.($ki[$b['bid']]['html'] == $w?' selected="selected"':'').'>'.$w.'</option>';
                            $feld .= '</select>';
                        }
                        elseif($b['type'] == 5)
                        {
                            $werte = explode('|', $atr['werte']);
                            $realvalues = $base->db_to_array($ki[$b['bid']]['html']);

                            $feld = '';
                            foreach($werte as $k => $w)
                            {
                                $feld .= '
                                <span class="checkbox">
                                    <input type="checkbox" id="bid_'.$b['bid'].'_'.$k.'" data-id="'.$b['bid'].'" data-nr="'.$k.'" name="'.$b['bid'].'[]" value="'.$w.'"'.(in_array($w, $realvalues)?' checked':'').' />
                                    <label for="bid_'.$b['bid'].'_'.$k.'">'.$w.'</label>
                                </span>';
                            }
                        }
                        elseif($b['type'] == 1005)
                        {
                            $feld = '
                            <input type="text" id="bid_'.$b['bid'].'" name="'.$b['bid'].'" value="'.$ki[$b['bid']]['html'].'" class="datepicker" />
                            <em>(Format: '.date('d.m.Y').')</em>';
                        }

                        echo '
                        <tr class="show_werte">
                            <td class="bezeichner">
                                <label for="bid_'.$b['bid'].'">'.($atr['name']?$atr['name']:$base->getBlockByID($b['type'], 'de')).'</label>
                            </td>
                            <td class="felder">
                                '.$feld.'
                            </td>
                        </tr>';
                    }
                echo '
                </table>
                </form>
                <div class="speichern">
                    <a class="save">'. $trans->__('Speichern') .'</a> | <a class="verw">'. $trans->__('Verwerfen') .'</a>
                </div>
            </div>';
        }
    }
}
?>
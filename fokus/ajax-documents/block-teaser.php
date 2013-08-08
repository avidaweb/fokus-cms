<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$teaser = $base->fixedUnserialize($row->teaser);
$e = $fksdb->fetch("SELECT titel, id FROM ".SQLPRE."elements WHERE id = '".$teaser['element']."' LIMIT 1");

echo '
<form method="post" id="teaser_form">
<div class="box">
    <h2 class="calibri">'. $trans->__('Strukturelement ausw&auml;hlen.') .'</h2>
    <div class="teaseA">
        <div class="teaseAL">
            '. $trans->__('Die Kind-Elemente des folgenden Strukturelementes werden angeteasert.') .'
        </div>
        <strong id="aktueller_teaser">'.($e?$e->titel:'<em>Noch kein Strukturelement ausgew&auml;hlt</em>').'</strong>
        <div class="teaseAR">
            <button>'. $trans->__('Strukturelement auswählen') .'</button>
            <input type="hidden" id="t_strele" name="element" value="'.$e->id.'" />
            <input type="hidden" id="teasertype" value="2" />
        </div>
    </div>
</div>

<div class="box"'.(!$e?' style="display:none;"':'').'>
    <h2 class="calibri">'. $trans->__('Art der Darstellung wählen.') .'</h2>';
    
    $teaser_bloecke = array($trans->__('1. Überschrift im Dokument (h1)'), $trans->__('2. Unterüberschrift im Dokument (h2)'), $trans->__('3. Textblock im Dokument'));
    
    $teaserklassen = array();
    $ordner = '../content/stklassen';
    $handle = opendir($ordner);
    while ($file = readdir ($handle)) 
    {
        if($file != "." && $file != "..") 
        {
            $stk = $base->open_stklasse($ordner.'/'.$file);
            
            if($stk['name'])
            {
                $teaserklassen[] = array(
                    'name' => $stk['name'],
                    'file' => str_replace('.php', '', $file)
                );
            }
        }
    }
    
    echo '
    <table class="teaseA2">
        <tr>
            <td><input type="radio" name="auflistung" id="t_aufl_A" value="0"'.(!$teaser['auflistung']?' checked="checked"':'').' /></td>
            <td>
                <label for="t_aufl_A">
                    <strong>'. $trans->__('Einfache Auflistung.') .'</strong>
                    '. $trans->__('Simple Auflistung der Titel der angeteaserten Strukturelemente.') .'
                </label>
            </td>
        </tr>
        <tr>
            <td><input type="radio" name="auflistung" id="t_aufl_B" value="1"'.($teaser['auflistung'] == 1?' checked="checked"':'').' /></td>
            <td>
                <label for="t_aufl_B">
                    <strong>'. $trans->__('Umfangreiche Auflistung.') .'</strong>
                    '. $trans->__('Auflistung der angeteaserten Strukturelemente mit ausgewählten Inhaltselementen.') .'
                </label>
            </td>
        </tr>
        <tr'.(!count($teaserklassen)?' style="display:none;"':'').'>
            <td><input type="radio" name="auflistung" id="t_aufl_C" value="2"'.($teaser['auflistung'] == 2?' checked="checked"':'').' /></td>
            <td>
                <label for="t_aufl_C">
                    <strong>'. $trans->__('Teaserklassen Auflistung.') .'</strong>
                    '. $trans->__('Auflistung der angeteaserten Strukturelement gemäß einer vorhandenen Teaserklasse.') .'
                </label>
            </td>
        </tr>
    </table>
    
    <div class="teaser_inhalt" id="struk_teaser_more2"'.($teaser['auflistung'] == 2 && count($teaserklassen)?' style="display:block;"':'').'>
        <strong class="stk">'. $trans->__('Strukturteaser-Klasse wählen:') .'</strong>
        
        <select name="stklasse">';
            foreach($teaserklassen as $tv)
            {                                                   
                echo '<option value="'.$tv['file'].'"'.($teaser['stklasse'] == $tv['file']?' selected="selected"':'').'>'.$tv['name'].'</option>';
            }
        echo '
        </select>
    </div>
    
    <div class="teaser_inhalt" id="struk_teaser_more"'.($teaser['auflistung'] == 1?' style="display:block;"':'').'>';
        for($g = 0; $g < 3; $g++)
        {
            $sas = $teaser['sas'][$g];
            
            echo '
            <div class="t_block">
                <div class="t_blockL">
                    <div class="t_blockLA">
                        <strong>'.$teaser_bloecke[$g].'</strong>
                    </div>
                    <div class="t_blockLB">
                        <input type="checkbox" id="teaser_hide_'.$g.'" name="h['.$g.']" value="1"'.($teaser['h'][$g]?' checked="checked"':'').' />
                        <label for="teaser_hide_'.$g.'">'. $trans->__('Nicht anzeigen') .'</label>
                    </div>
                </div>
                <div class="t_blockR"'.($teaser['h'][$g]?' style="display:none;"':'').'>
                    '.($g > 1?'
                    <div class="t_optAT">
                        <input type="checkbox" id="teaser_hide_pic_'.$g.'" name="hp['.$g.']" value="1"'.($teaser['hp'][$g]?' checked="checked"':'').' />
                        <label for="teaser_hide_pic_'.$g.'">'. $trans->__('Bild im Textblock nicht anzeigen') .'</label>
                    </div>':'').'
                    <div class="t_optA">
                        <input type="checkbox" id="teaser_onlyfirst_'.$g.'" name="of['.$g.']" value="1"'.($teaser['of'][$g]?' checked="checked"':'').' />
                        <label for="teaser_onlyfirst_'.$g.'">'. $trans->__('Ausschlie&szlig;lich das erste Element darstellen') .'</label>
                    </div>
                    <div class="t_optB">
                        <div class="t_optB0">
                            <input type="radio" class="teaser_auszug" id="teaser_auszugC_'.$g.'" name="a['.$g.']" value="0"'.(!$teaser['a'][$g]?' checked="checked"':'').' />
                            <label for="teaser_auszugC_'.$g.'">'. $trans->__('Text nicht mit Strukturelement verlinken') .'</label>
                        </div>
                        <div class="t_optB1">
                            <input type="radio" class="teaser_auszug" id="teaser_auszugA_'.$g.'" name="a['.$g.']" value="1"'.($teaser['a'][$g] == 1?' checked="checked"':'').' />
                            <label for="teaser_auszugA_'.$g.'">'. $trans->__('Kompletten Text mit Strukturelement verlinken') .'</label>
                        </div>
                        <div class="t_optB2">
                            <input type="radio" class="teaser_auszug" id="teaser_auszugB_'.$g.'" name="a['.$g.']" value="2"'.($teaser['a'][$g] == 2?' checked="checked"':'').' />
                            <label for="teaser_auszugB_'.$g.'">'. $trans->__('Text automatisch kürzen und mit Link erg&auml;nzen') .'</label>
                            
                            <table'.($teaser['a'][$g] == 2?' style="display:table;"':'').'>
                                <tr>
                                    <td>'. $trans->__('Kürzen nach:') .'</td>
                                    <td>
                                        <input type="text" name="z['.$g.']" value="'.$teaser['z'][$g].'" class="smt" />
                                        <select name="z2['.$g.']">
                                            <option value="0"'.(!$teaser['z2'][$g]?' selected="selected"':'').'>'. $trans->__('Worten') .'</option>
                                            <option value="1"'.($teaser['z2'][$g] == 1?' selected="selected"':'').'>'. $trans->__('Zeichen') .'</option>
                                            <option value="2"'.($teaser['z2'][$g] == 2?' selected="selected"':'').'>'. $trans->__('Sätze') .'</option>
                                        </select>
                                    </td>
                                </tr>
                                <tr>
                                    <td>Erg&auml;nzen mit:</td>
                                    <td>
                                        <input type="text" name="z3['.$g.']" value="'.($teaser['z3'][$g]?$teaser['z3'][$g]:$trans->__('... mehr lesen')).'" />
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                    <div class="t_optC">
                        <label for="teaser_show_as'.$g.'">'. $trans->__('Anzeigen als:') .'</label>
                        <select id="teaser_show_as'.$g.'" name="sas['.$g.']">
                            <option value="1"'.($sas == 1?' selected="selected"':'').'>'. $trans->__('Überschrift (h1)') .'</option>
                            <option value="2"'.($sas == 2 || (!$sas && $g == 0)?' selected="selected"':'').'>'. $trans->__('Unterüberschrift (h2)') .'</option>
                            <option value="3"'.($sas == 3 || (!$sas && $g == 1)?' selected="selected"':'').'>'. $trans->__('Abschnitts&uuml;berschrift (h3)') .'</option>
                            <option value="4"'.($sas == 4 || (!$sas && $g == 2)?' selected="selected"':'').'>'. $trans->__('Textblock') .'</option>
                        </select>
                    </div>
                </div>
            </div>';
        }    
    echo '
    </div>
</div>

<div class="box" id="struk_teaser_opt"'.(!$e?' style="display:none;"':'').'>
    <h2 class="calibri">'. $trans->__('Sortierung &amp; Limitierung einstellen.') .'</h2>

    <table>
        <tr>
            <td class="a">
                '. $trans->__('Sortier-Reihenfolge bei der Ausgabe:') .'
            </td>
            <td class="b">
                <select name="sort">
                    <option value=""'.(!$teaser['sort']?' selected="selected"':'').'>'. $trans->__('Standard-Sortierung') .'</option>
                    <option value="datum DESC"'.($teaser['sort'] == 'datum DESC'?' selected="selected"':'').'>'. $trans->__('Dokumenten-Datum (neueste zuerst)') .'</option>
                    <option value="datum ASC"'.($teaser['sort'] == 'datum ASC'?' selected="selected"':'').'>'. $trans->__('Dokumenten-Datum (älteste zuerst)') .'</option>
                    <option value="timestamp DESC"'.($teaser['sort'] == 'timestamp DESC'?' selected="selected"':'').'>'. $trans->__('Erstelldatum (neueste zuerst)') .'</option>
                    <option value="timestamp ASC"'.($teaser['sort'] == 'timestamp ASC'?' selected="selected"':'').'>'. $trans->__('Erstelldatum (&auml;lteste zuerst)') .'</option>
                    <option value="titel ASC"'.($teaser['sort'] == 'titel ASC'?' selected="selected"':'').'>'. $trans->__('Name (A-Z)') .'</option>
                    <option value="titel DESC"'.($teaser['sort'] == 'titel DESC'?' selected="selected"':'').'>'. $trans->__('Name (Z-A)') .'</option>
                    <option value="RAND()"'.($teaser['sort'] == 'RAND()'?' selected="selected"':'').'>'. $trans->__('Zufall') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">
                '. $trans->__('Automatischer Seitenumbruch:') .'
            </td>
            <td class="b">
                <select name="umbruch">
                    <option value="">'. $trans->__('Alle Elemente auf einer Seite anzeigen') .'/option>';
                    $x = 0;
                    while($x < 1000)
                    {
                        $x += ($x<20?1:($x<50?5:($x<100?10:($x<300?50:100))));
                        echo '<option value="'.$x.'"'.($teaser['umbruch'] == $x?' selected="selected"':'').'>Pro Seite '.$x.' Element'.($x!=1?'e':'').' anzeigen</option>';
                    }
                echo '
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">
                '. $trans->__('Ausgabe limitieren:') .'
            </td>
            <td class="b dk_filterO">
                <p>
                    <input type="checkbox" name="vonA" value="1"'.($teaser['vonA']?' checked="checked"':'').' class="vonbis" /> 
                    <span>'. $trans->__('von Nr.') .'</span>
                    <input type="text"'.(!$teaser['vonA']?' disabled="disabled"':'').' name="von" value="'.$teaser['von'].'" />
                </p>
                <p>
                    <input type="checkbox" name="bisA" value="1"'.($teaser['bisA']?' checked="checked"':'').' class="vonbis" /> 
                    <span>'. $trans->__('bis Nr.') .'</span>
                    <input type="text" name="bis"'.(!$teaser['bisA']?' disabled="disabled"':'').' value="'.$teaser['bis'].'" />
                </p>
            </td>
        </tr>
    </table>
</div>';

$katQ = $fksdb->query("SELECT id, kat, name FROM ".SQLPRE."categories ORDER BY sort");
if($fksdb->count($katQ))
{
    $ka = array();
    while($kx = $fksdb->fetch($katQ))
        $ka[$kx->kat][] = $kx;
    
    $kats = $teaser['kat'];
    if(!is_array($kats)) $kats = array();
        
    function kats($base, $ka, $kats, $eltern, $ebene)
    {
        $current = $ka[$eltern]; 
        if(!is_array($current)) $current = array();
        
        foreach($current as $k)
        {
            $has_childs = count($ka[$k->id]);
            
            echo '
            <p style="margin-left:'.($ebene * 16).'px;">
                <input type="checkbox" value="'.$k->id.'" name="kat[]" id="ktkat'.$k->id.'"'.(in_array($k->id, $kats)?' checked="checked"':'').' />
                <label for="ktkat'.$k->id.'">'.$k->name.'</label>
            </p>';
            
            if($has_childs)
            {
                $ebene ++;
                kats($base, $ka, $kats, $k->id, $ebene);
                $ebene --;
            }
        }
    }
    
    echo '
    <div class="box" id="struk_teaser_kats"'.(!$e?' style="display:none;"':'').'>
        <h2 class="calibri">'. $trans->__('Kategorie-Filter.') .'</h2>
    
        <a class="rbutton rollout">'. $trans->__('Kategorien <span>anzeigen</span>') .'</a>
        <div class="kats">';
            kats($base, $ka, $kats, 0, 0);
        echo '
        </div>
    </div>';
}

echo '
<div class="box" id="struk_teaser_inhalt"'.(!$e?' style="display:none;"':'').'>
    <h2 class="calibri">'. $trans->__('Einzelne Strukturelemente ein/ausblenden?') .'</h2>
    
    <div class="select_type">
        <p>
            <input type="radio" id="t_select_type_0" value="0" name="st"'.(!$teaser['st']?' checked':'').' />
            <label for="t_select_type_0">'. $trans->__('Ausgewählte Strukturelemente <strong>nicht</strong> im Teaser verwenden') .'</label>
        </p>
        <p>
            <input type="radio" id="t_select_type_1" value="1" name="st"'.($teaser['st']?' checked':'').' />
            <label for="t_select_type_1">'. $trans->__('Ausschließlich ausgewählte Strukturelemente im Teaser verwenden') .'</label>
        </p>
    </div>
    
    <div class="elemente"></div>
</div>';

if(!$dokument->klasse)
{
    echo '
    <div class="box" id="teaser_rss"'.(!$e?' style="display:none;"':'').'>
        <h2 class="calibri">'. $trans->__('RSS-Feed generieren.') .'</h2>
        
        <p class="is_rss">
            <input id="i_is_rss" type="checkbox" name="rss" value="1"'.($teaser['rss']?' checked':'').' />
            <label for="i_is_rss">'. $trans->__('Auf Basis des Teasers einen RSS-Feed generieren.') .'</label>
        </p>
        
        <div class="extra"'.(!$teaser['rss']?' style="display:none;"':'').'>
            <table>
                <tr>
                    <td class="a">
                        '. $trans->__('Titel des gesamten Feeds:') .'
                    </td>
                    <td class="b">
                        <input type="text" name="rss_titel" value="'.($teaser['rss_titel']?$teaser['rss_titel']:$dokument->titel).'" />
                    </td>
                </tr>
                <tr>
                    <td class="a">
                        '. $trans->__('Beschreibung des gesamten Feeds:') .'
                    </td>
                    <td class="b">
                        <input type="text" name="rss_desc" value="'.$teaser['rss_desc'].'" />
                    </td>
                </tr>
                <tr>
                    <td class="a">
                        '. $trans->__('Autor des gesamten Feeds:') .'
                    </td>
                    <td class="b">
                        <input type="text" name="rss_autor" value="'.($teaser['rss_autor']?$teaser['rss_autor']:$base->getOpt()->vorname.' '.$base->getOpt()->nachname).'" />
                    </td>
                </tr>
                <tr>
                    <td class="a">
                        '. $trans->__('Integration des Feeds:') .'
                    </td>
                    <td class="b">
                        <select name="rss_home">
                            <option value="0"'.($teaser['rss_home'] == 0?' selected':'').'>'. $trans->__('Feed in ausgewähltes Strukturelement und Kindelemente integrieren') .'</option>
                            <option value="1"'.($teaser['rss_home'] == 1?' selected':'').'>'. $trans->__('Feed in jedem Strukturelement der gesamten Webseite integrieren') .'</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">
                        '. $trans->__('Anzahl der Strukturelemente:') .'
                    </td>
                    <td class="b">
                        <select name="rss_anzahl">';
                            $x = 0;
                            $teaser['rss_anzahl'] = ($teaser['rss_anzahl']?$teaser['rss_anzahl']:10);
                            while($x < 1000)
                            {
                                $x += ($x<20?1:($x<50?5:($x<100?10:($x<300?50:100))));
                                echo '<option value="'.$x.'"'.($teaser['rss_anzahl'] == $x?' selected="selected"':'').'>Die '.$x.' neuesten Element'.($x!=1?'e':'').' im RSS-Feed anzeigen</option>';
                            }
                        echo '
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </div>';
}

echo '
</form>';
?>
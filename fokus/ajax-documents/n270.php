<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n270')
    exit($user->noRights());

if(!isset($rel))
    $rel = 0;

$ausrichtung = $fksdb->save($_REQUEST['ausrichtung']);

echo '
<h1>'.($rel == 1?'Bild':'Video').' w&auml;hlen.</h1>

'.($ausrichtung?'
<div class="box">
    '. $trans->__('Ausrichtung:') .'
    <select id="ch_ausrichtung">
        <option value="0">'. $trans->__('Keine') .'</option>
        <option value="1">'. $trans->__('Links') .'</option>
        <option value="2">'. $trans->__('Rechts') .'</option>
    </select>
</div>':'').'

<div class="box" id="bild_waehlen">
    <div class="bwL">
        <fieldset>
            <legend>'. $trans->__('Suchen.') .'</legend>

            <input type="text" id="bw_qT" />
        </fieldset>

        <fieldset>
            <legend>Filter.</legend>

            <div class="dropdown">
                <span class="foben">Dateitypen.</span>
                <p><input type="checkbox" id="bw_dateityp_1" checked="checked" /> <label for="bw_dateityp_1">.jpg</label></p>
                <p><input type="checkbox" id="bw_dateityp_2" checked="checked" /> <label for="bw_dateityp_2">.gif</label></p>
                <p><input type="checkbox" id="bw_dateityp_3" checked="checked" /> <label for="bw_dateityp_3">.png</label></p>
            </div>

            <div class="dropdown">
                <span class="foben">Ausrichtung.</span>
                <p><input type="checkbox" id="bw_ausrichtung_1" checked="checked" /> <label for="bw_ausrichtung_1">'. $trans->__('horizontal') .'</label></p>
                <p><input type="checkbox" id="bw_ausrichtung_2" checked="checked" /> <label for="bw_ausrichtung_2">'. $trans->__('vertikal') .'</label></p>
            </div>

            <div class="dropdown">
                <span class="foben">Kategorie.</span>
                <p><input type="checkbox" id="bw_kat_1" checked="checked" /> <label for="bw_kat_1">'. $trans->__('Foto') .'</label></p>
                <p><input type="checkbox" id="bw_kat_2" checked="checked" /> <label for="bw_kat_2">'. $trans->__('Grafik') .'</label></p>
            </div>
        </fieldset>

        <fieldset>
            <legend>'. $trans->__('Sortierung.') .'</legend>

            <div class="dropdown2">
                <p><input type="radio" name="sort1" id="bw_sort1_1" /> <label for="bw_sort1_1">'. $trans->__('Dateiname') .'</label></p>
                <p><input type="radio" name="sort1" id="bw_sort1_2" checked="checked" /> <label for="bw_sort1_2">'. $trans->__('Upload') .'</label></p>
                <p><input type="radio" name="sort1" id="bw_sort1_3" /> <label for="bw_sort1_3">'. $trans->__('Aktualisierung') .'</label></p>
                <p><input type="radio" name="sort1" id="bw_sort1_4" /> <label for="bw_sort1_4">'. $trans->__('Autor') .'</label></p>
            </div>

            <hr />

            <div class="dropdown2">
                <p><input type="radio" name="sort2" id="bw_sort2_1" /> <label for="bw_sort2_1">'. $trans->__('Aufsteigend') .'</label></p>
                <p><input type="radio" name="sort2" id="bw_sort2_2" checked="checked" /> <label for="bw_sort2_2">'. $trans->__('Absteigend') .'</label></p>
            </div>
        </fieldset>

        <button id="pics2gal"></button>
    </div>
    <div class="s_ergebnis bwR" id="s_bilder"><img src="images/loading_white.gif" alt="'. $trans->__('Bitte warten.. Inhalt wird geladen..') .'" class="ladebalken" /></div>
</div>

<div class="box_save">
    <input type="button" value="'. $trans->__('verwerfen') .'" class="bs1" /> <input type="button" value="'. $trans->__('speichern') .'" class="bs2" />
</div>';
?>
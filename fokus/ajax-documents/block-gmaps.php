<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$c = $base->fixedUnserialize($row->html);
if(!is_array($c)) $c = array();
$c = (object)$c;

$alternativtext = ($c->nojs?$c->nojs: $trans->__('Google Maps Karte kann nur bei aktiviertem Javascript angezeigt werden'));
$zoom = ($c->zoom?$c->zoom:7);
$breite = ($c->width?$c->width:100);
$hoehe = ($c->height?$c->height:300);

echo '
<form id="googlemaps" class="dcomments">
<div class="box">
    <h2 class="calibri">'. $trans->__('Position der Karte.') .'</h2>
    <table>
        <tr>
            <td class="a">'. $trans->__('Breitengrad') .'</td>
            <td class="b">
                <input type="text" class="latlong" name="lat" value="'.$c->lat.'" />
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Längengrad') .'</td>
            <td class="b">
                <input type="text" class="latlong" name="long" value="'.$c->long.'" />
            </td>
        </tr>
        <tr>
            <td colspan="2">
                <a class="rbutton goaway">'. $trans->__('Breiten- und Längengrad aus Adresse ermitteln') .'</a>
            </td>
        </tr>
    </table>
</div>
<div class="box">
    <h2 class="calibri">'. $trans->__('Eigenschaften der Karte.') .'</h2>
    <table>
        <tr>
            <td class="a">'. $trans->__('Zoomstufe') .'</td>
            <td class="b zoomlegend">
                <span class="b">'. $trans->__('(fern)') .'</span>
                <div class="zoomer"></div>
                <span>(nah)</span>
                <input type="hidden" name="zoom" value="'.$zoom.'" />
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Katentyp') .'</td>
            <td class="b">
                <select name="typ">
                    <option value="0"'.($c->typ == 0?' selected':'').'>'. $trans->__('Karte') .'</option>
                    <option value="1"'.($c->typ == 1?' selected':'').'>'. $trans->__('Satellit') .'</option>
                    <option value="2"'.($c->typ == 2?' selected':'').'>'. $trans->__('Hybrid (Satellit mit Straßen, Orten, ...)').'</option>
                    <option value="3"'.($c->typ == 3?' selected':'').'>'. $trans->__('Gelände') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Markierung') .'</td>
            <td class="b">
                <input type="checkbox" name="marker" id="gmaps_marker" value="1"'.($c->marker?' checked':'').' /> 
                <label for="gmaps_marker">'. $trans->__('Der Karte an der angegebenden Position eine Markierung hinzufügen') .'</label>
                
                <p'.(!$c->marker?' style="display:none;"':'').'>
                    '. $trans->__('Beschriftung der Markierung:') .'
                    <input name="marker_text" class="markertext" value="'.$c->marker_text.'" type="text" />
                </p>
            </td>
        </tr>
    </table>
</div>
<div class="box">
    <h2 class="calibri">'. $trans->__('Ausgabe der Karte.') .'</h2>
    <table class="groessen">
        <tr>
            <td class="a">'. $trans->__('Breite') .'</td>
            <td class="b">
                <input type="text" class="ausgabe" name="width" value="'.$breite.'" /> 
                <select name="width_typ" class="wt">
                    <option value="0"'.($c->width_typ == 0?' selected':'').'>'. $trans->__('Prozent (empfohlen)') .'</option>
                    <option value="1"'.($c->width_typ == 1?' selected':'').'>'. $trans->__('Pixel') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Höhe') .'</td>
            <td class="b">
                <input type="text" class="ausgabe" name="height" value="'.$hoehe.'" /> 
                Pixel
            </td>
        </tr>
        <tr>
            <td class="a">'. $trans->__('Alternativtext') .'</td>
            <td class="b">
                <textarea name="nojs">'.$alternativtext.'</textarea>
            </td>
        </tr>
    </table>
</div>
</form>';
?>
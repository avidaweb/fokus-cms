<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$sm = $base->fixedUnserialize($row->html);
if(!is_array($sm)) $sm = array(); 

echo '
<form id="sitemap_form">
<div class="box">
    <p>
        <input type="radio" id="isitemap" name="rollen" value="0"'.(!$sm['rollen']?' checked="checked"':'').' />
        <label for="isitemap">'. $trans->__('Sitemap-Punkte gem&auml;&szlig; Benutzer-Berechtigung anzeigen') .'</label>
    </p>
    <p>
        <input type="radio" id="isitemap2" name="rollen" value="1"'.($sm['rollen']?' checked="checked"':'').' />
        <label for="isitemap2">'. $trans->__('Alle Sitemap-Punkte unabhängig von den Benutzer-Berechtigungen anzeigen') .'</label>
    </p>
    <div class="simulate">
        '. $trans->__('Untere Sitemap-Vorschau für folgende Benutzergruppe anzeigen:') .'
        <select id="sm_simulate">
            <option value="0">'. $trans->__('Besucher / nicht angemeldet') .'</option>';
            $rolQ = $fksdb->query("SELECT id, titel FROM ".SQLPRE."roles WHERE papierkorb = '0' ORDER BY id");
            while($rol = $fksdb->fetch($rolQ))
                echo '<option value="'.$rol->id.'">'.$rol->titel.'</option>';
            echo '
        </select>
    </div>
</div>

<div class="box" id="n_sitemap">
    <img src="images/loading.gif" alt="'. $trans->__('Bitte warten.. Inhalt wird geladen..') .'" class="ladebalken" />
</div>
</form>';
?>
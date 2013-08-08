<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');

$index = $fksdb->save($_REQUEST['index']);
$rel = $fksdb->save($_REQUEST['rel']);

if(!$suite->rm(2))
    exit('Modul nicht aktiviert');
    
if(!$user->r('fks', 'foresight'))
    exit('Keine Rechte vorhanden');

if($index == 's450')
{    
    echo '
    <h1>foresight. Ihre Website in der Zukunft ansehen.</h1>
    
    <form id="foresight" target="_blank" action="sub_foresight.php?index=s451" method="post">
    <div class="box">
        <div class="overview">
            <span class="A">Website zu folgendem Datum ansehen:</span>
            <input type="text" class="datum datepicker" name="datum" value="'.date('d.m.Y', ($base->getZeroHour() + 86400)).'" />
            
            <span class="B">um</span>
            
            <select name="uhr1">';
                for($z = 0; $z <= 24; $z++)
                    echo '<option value="'.$z.'">'.str_pad($z, 2 ,'0', STR_PAD_LEFT).'</option>';
            echo '
            </select> : 
            <select name="uhr2">';
                for($z = 0; $z <= 60; $z++)
                    echo '<option value="'.$z.'">'.str_pad($z, 2 ,'0', STR_PAD_LEFT).'</option>';
            echo '
            </select>
            
            <span class="C">Uhr.</span>
        </div>
        <div class="overview">
            <table>
                <tr>
                    <td><input id="fks_type_0" type="radio" name="type" value="1" checked="checked" /></td>
                    <td><label for="fks_type_0">Nur freigegebene Dokumente als Vorschau anzeigen.</label></td>
                </tr>
                <tr>
                    <td><input id="fks_type_1" type="radio" name="type" value="2" /></td>
                    <td><label for="fks_type_1">Auch meine zur Freigabe vorgelegten Dokumente als Vorschau anzeigen.</label></td>
                </tr>
                <tr>
                    <td><input id="fks_type_2" type="radio" name="type" value="3" /></td>
                    <td><label for="fks_type_2">Auch alle zur Freigabe vorgelegten Dokumente als Vorschau anzeigen.</label></td>
                </tr>
            </table>
        </div>
        <div class="overview">
            <table>
                <tr>
                    <td><input id="fks_struk_0" type="radio" name="struk" value="2" checked="checked" /></td>
                    <td><label for="fks_struk_0">Struktur verwenden, die &quot;aktuell zur Bearbeitung&quot; ausgew&auml;hlt ist.</label></td>
                </tr>
                <tr>
                    <td><input id="fks_struk_1" type="radio" name="struk" value="1" /></td>
                    <td><label for="fks_struk_1">Struktur verwenden, die &quot;aktuell online&quot; ist.</label></td>
                </tr>
            </table>
        </div>
    </div>
    <div class="box_save">
        <input type="submit" value="weiter" name="go" class="bs2" />
    </div>
    </form>';    
}
elseif($index == 's451')
{
    $url = $fksdb->save($_POST['url']);
    
    if($_POST['go'])
    {
        if(!$_POST['datum']) $datum = date('d.m.Y');
        $datum = explode('.', $_POST['datum']);
        $zeit = mktime(intval($_POST['uhr1']), intval($_POST['uhr2']), 0, $datum[1], $datum[0], $datum[2]);
        
        $type = $fksdb->save($_POST['type'], 1);
        if(!$type) $tpye = $user->getForesight('type');
        
        $struk = $fksdb->save($_POST['struk'], 1);
        if(!$struk) $struk = $user->getForesight('structure');
        
        $user->setForesight(true, $fksdb->save($zeit).'!'.$type.'!'.$struk);
    }
    elseif($_POST['stop'])
    {
        $user->setForesight(false);
    }
    
    $base->go(($url?$url:$domain.'/'));
}
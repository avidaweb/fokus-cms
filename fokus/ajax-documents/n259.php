<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || !$suite->rm(8) || $index != 'n259')
    exit($user->noRights());

$id = $fksdb->save($_GET['id'], 1);
$dv = $fksdb->save($_GET['dv'], 1);

$d = $fksdb->fetch("SELECT titel, id, dversion_edit, klasse, produkt, von FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, language, ende, edit FROM ".SQLPRE."document_versions WHERE id = '".$d->dversion_edit."' AND dokument = '".$id."' LIMIT 1");
$dv = $fksdb->fetch("SELECT * FROM ".SQLPRE."document_versions WHERE ".(!$dv?"aktiv = '1'":"id = '".$dv."'")." AND dokument = '".$id."' AND language = '".$dve->language."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $d->von == $user->getID())) {}
else exit($user->noRights());

$dva = $fksdb->query("SELECT * FROM ".SQLPRE."document_versions WHERE id != '".$dve->id."' AND language = '".$dve->language."' AND dokument = '".$id."' ORDER BY id DESC");
$dvv = $fksdb->query("SELECT id FROM ".SQLPRE."document_versions WHERE id != '".$dve->id."' AND id < '".$dv->id."' AND language = '".$dve->language."' AND dokument = '".$id."'");
$dvo = $fksdb->query("SELECT id FROM ".SQLPRE."document_versions WHERE id != '".$dve->id."' AND id < '".$dve->id."' AND language = '".$dve->language."' AND dokument = '".$id."'");

$dv_vor = $fksdb->fetch("SELECT id FROM ".SQLPRE."document_versions WHERE id != '".$dve->id."' AND id < '".$dv->id."' AND language = '".$dve->language."' AND dokument = '".$id."' ORDER BY id DESC LIMIT 1");
$dv_zurueck = $fksdb->fetch("SELECT id FROM ".SQLPRE."document_versions WHERE id != '".$dve->id."' AND id > '".$dv->id."' AND language = '".$dve->language."' AND dokument = '".$id."' ORDER BY id ASC LIMIT 1");

$allev = $fksdb->count($dva);
$aktuellev = $fksdb->count($dvv) + 1;
$onlinev = $fksdb->count($dvo);

///////////////////////////////////////////////////
require_once('../inc/classes.view/class.fks.php');
$fks = new Page(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans,
    'user' => $user,
    'api' => $api
), array(
    'title' => $d->titel,
    'language' => $dve->language,
    'preview' => $d->id,
    'dversion_preview' => $dv->id,
    'dclass' => ($d->klasse?$d->id:0)
));

require_once('../inc/classes.view/class.content.php');
$content = new Content(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans,
    'user' => $user,
    'api' => $api,
    'fks' => $fks
));

require_once('../inc/classes.blocks/_basic.php');

$content->setGalerie(array('img_width' => 150, 'img_height' => 150));
$content->setForm(array('view' => 'flat'));

$inhalt = $content->get(array(
    'id' => 'vcontent',
    'document_class' => '',
    'document_width' => '590',
    'column_class' => 'vspalte',
    'column_padding' => array(8, 20, 8, 0),
    'column_padding_last' => array(8, 0, 8)
));

echo '
<div class="zp">
    <div class="zpL">
        '.$inhalt.'
    </div>
    <div class="zpR">
        <a class="vor loadit" rel="'.$dv_vor->id.'" title="Eine &auml;ltere Version laden"'.($aktuellev == $allev?' ':($aktuellev == 1?' style="display:none;"':'')).'></a>
        <a class="zurueck loadit" rel="'.$dv_zurueck->id.'" title="Eine neuere Version laden"'.($aktuellev == $allev?' style="display:none;"':($aktuellev == 1?' ':'')).'></a>
    </div>
</div>

<div class="short">
    <h2>Version '.$aktuellev.' vom '.date('d.m.Y', $dv->timestamp_freigegeben).' um '.date('H:i', $dv->timestamp_freigegeben).' Uhr</h2>
    <p>
        Erstellt von '.$base->user($dv->von, ' ', 'vorname', 'nachname').'<br />
        Freigegeben von '.$base->user($dv->von_freigegeben, ' ', 'vorname', 'nachname').'

        '.(!$dv->aktiv?'<span><strong>'. $trans->__('HINWEIS: Diese Version ist momentan nicht online.</strong> Version %1 ist momentan online!</span>' , false, array($onlinev)):'').'
    </p>
</div>
'.(!$dv->aktiv || $dve->edit?'
<div class="laden">
    '.($dve->ende?'
    <span><strong>'. $trans->__('Sie können derzeit keine alte Version laden, da das Dokument zur Freigabe vorgelegt worden ist</strong></span>'):'
    <span>
        <strong>'. $trans->__('Möchten Sie Version %1 wieder zur Bearbeitung laden?', false, array($aktuellev)) .'</strong>
        <a class="tooltipp">'. $trans->__('(was passiert dann?)<span>Wenn Sie diese Version zur Bearbeitung laden, werden alle Inhalte dieses Dokumentes wieder auf den gewählten Versionsstand zum entsprechenden Datum zurückgesetzt. Sie können diesen Stand dann weiter bearbeiten oder unverändert freigeben bzw. zur Freigabe vorlegen.') .'</span></a>
    </span>
    <button>'. $trans->__('Version %1 zur Bearbeitung laden', false, array($aktuellev)) .'</button>
    <input type="hidden" id="zp_alteversion" value="'.$dv->id.'" />
    <input type="hidden" id="zp_gebwarnung" value="'.($dve->edit?'true':'false').'" />').'
</div>':'').'

<div class="historie">
    <h2>'. $trans->__('Übersicht & Historie') .'</h2>

    <table>';
        $counter  = 0;
        while($da = $fksdb->fetch($dva))
        {
            $version = $allev - $counter;

            echo '
            <tr class="bg_'.$base->countup().''.($da->id == $dv->id?' aktiv':'').'">
                <td><a class="loadit" rel="'.$da->id.'"><strong>'. $trans->__('Version %1', false, array($version)) .'</strong></a></td>
                <td>'. $trans->__('vom %1', false, array(date('d.m.Y', $da->timestamp_freigegeben))) .'</td>
                <td>'. $trans->__('um %1 Uhr', false, array(date('H:i', $da->timestamp_freigegeben))) .'</td>
                <td>Erstellt von '.$base->user($da->von, ' ', 'vorname', 'nachname').'</td>
                <td>Freigegeben von '.$base->user($da->von_freigegeben, ' ', 'vorname', 'nachname').'</td>
                <td class="last">'.($da->id == $dv->id?$trans->__('zur Ansicht ausgewählt'):'<a class="loadit" rel="'.$da->id.'">'. $trans->__('Diese Version ansehen') .'</a>').'</td>
            </tr>';

            $counter ++;
        }
    echo '
    </table>
</div>';
?>
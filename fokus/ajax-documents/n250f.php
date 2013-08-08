<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n250f')
    exit($user->noRights());

$id = $fksdb->save($_GET['id'], 1);
$a = $fksdb->save($_GET['a']);

$d = $fksdb->fetch("SELECT dversion_edit, von_edit, id, produkt, gesperrt, anfang, bis, timestamp_freigegeben FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT ende, language, edit, id FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND id = '".$d->dversion_edit."' LIMIT 1");
$dveA = $fksdb->query("SELECT id FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND id != '".$d->dversion_edit."' AND language = '".$dve->language."'");

if(!$a)
{
    if($dve->edit)
    {
        echo '
        <p>'. $trans->__('Dieses Dokument wurde verändert:') .'</p>

        <div class="fopt'.($dve->ende?' fende':'').'">
            '.($dve->ende?$trans->__('Dokument ist zur Freigabe vorgelegt'):$trans->__('Freigabeoptionen anzeigen').'
            <div class="fmore shadow">
                <a class="clink">'. $trans->__('Freigabeoptionen schließen') .'</a>
                '.($user->r('dok', 'publ')?'<a class="flink2" rel="'.$d->id.'_'.$dve->id.'"><span></span><strong>'. $trans->__('Dokument direkt freigeben') .'</strong></a>':'').'
                '.(!$d->produkt?'<a class="flink"><span></span><strong>'. $trans->__('Dokument zur Freigabe vorlegen') .'</strong></a>':'').'
                '.(!$d->produkt?'<a class="wlink">'. $trans->__('Dokument an Mitarbeiter weitergeben') .'</a>':'').'
                '.($fksdb->count($dveA)?'<a class="vlink">'. $trans->__('Alle Änderungen an diesem Dokument verwerfen') .'</a>':'').'
            </div>').'
        </div>';
    }
}
elseif($a == 1)
{
    $fksdb->update("document_versions", array(
        "ende" => 1
    ), array(
        "id" => $d->dversion_edit,
        "dokument" => $id
    ), 1);

    $fksdb->update("documents", array(
        "von_edit" => $user->getID(),
        "timestamp_edit" => time(),
        "statusA" => 1,
        "statusB" => $base->find_document_statusB($d->gesperrt, $d->anfang, $d->bis, $d->timestamp_freigegeben)
    ), array(
        "id" => $id
    ), 1);
}
?>
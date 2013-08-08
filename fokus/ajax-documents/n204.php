<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok', 'edit') || $index != 'n204')
    exit($user->noRights());

$id = $fksdb->save($_REQUEST['id']);
$a = $fksdb->save($_REQUEST['a']);
$b = $fksdb->save($_REQUEST['b']);

if($a == 'get')
{
    echo '
    <h1>Dokument weitergeben.</h1>

    <div class="box">
        <p>
            <span class="dokweitergeben">'. $trans->__('Benutzer wählen:') .'</span>

            <select id="bweiter">';
                $pQ = $fksdb->query("SELECT id, eid, vorname, nachname, email, plz, ort FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND status = '0' AND papierkorb = '0' ORDER BY vorname, nachname");
                while($p = $fksdb->fetch($pQ))
                {
                    $ident_a = trim(($p->eid?$p->eid.', ':'').($p->email?$p->email.', ':'').($p->plz?$p->plz.($p->ort?', ':''):'').($p->ort?$p->ort:''), ', ');
                    $ident = trim($p->vorname.' '.$p->nachname.($ident_a?' ('.$ident_a.')':''));

                    if($ident == '()' || !$ident)
                        continue;

                    echo '<option value="'.$p->id.'">'.$ident.'</option>';
                }
            echo '
            </select>
        </p>

        <p>
            <span class="dokweitergeben">'. $trans->__('Nachricht schreiben') .'</span>
            <textarea></textarea>
        </p>
    </div>

    <div class="box box_save" style="display:block;">
        <button class="bs1">'. $trans->__('Abbrechen') .'</button>
        <button class="bs2">'. $trans->__('Weitergeben') .'</button>
    </div>';
}
elseif($a == 'set')
{
    $d = $fksdb->fetch("SELECT id, dversion_edit, titel FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
    $dve = $fksdb->fetch("SELECT id, von FROM ".SQLPRE."document_versions WHERE id = '".$d->dversion_edit."' AND dokument = '".$id."' LIMIT 1");

    $to_user = $fksdb->fetch("SELECT id, email, indiv FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND id = '".$b."' LIMIT 1");

    if($to_user && $dve->von == $user->getID() && $b != $user->getID())
    {
        $fksdb->query("UPDATE ".SQLPRE."document_versions SET von = '".$b."' WHERE id = '".$dve->id."' AND dokument = '".$id."' LIMIT 1");
        $fksdb->query("UPDATE ".SQLPRE."documents SET closed_by = '".$b."' WHERE id = '".$d->id."' LIMIT 1");

        $text = $fksdb->save($_REQUEST['text']);
        $title = $trans->__('Dokument %1 zur Bearbeitung vorgelegt', false, array($d->titel));

        $api->insertNotification($trans->__('Dokument zugewiesen'), $trans->__('%1 hat Ihnen das Dokument %2 zugewiesen.', false, array(
            $api->getUserData('first_name'),
            $d->titel
        )), array('fks.openDocument', $d->id), $b);

        // An Absender (sich selber - Kopie)
        $fksdb->insert("messages", array(
            "benutzer" => $user->getID(),
            "gelesen" => 1,
            "vid" => Strings::createID(),
            "von" => $user->getID(),
            "an" => $b,
            "timestamp" => $base->getTime(),
            "text" => $text,
            "titel" => $title
        ));

        // An Empfänger
        $fksdb->insert("messages", array(
            "benutzer" => $b,
            "gelesen" => 0,
            "vid" => Strings::createID(),
            "von" => $user->getID(),
            "an" => $b,
            "timestamp" => $base->getTime(),
            "text" => $text,
            "titel" => $title
        ));


        // Bei Bedarf Email senden
        $indiv = $base->fixedUnserialize($to_user->indiv);
        if(!is_array($indiv))
            $indiv = array();

        if(!$indiv['no_email_get_document'] && $to_user->email)
        {
            $email_title = $trans->__('Dokument %1 weitergegeben auf %2', false, array($d->titel, str_replace('http://', '', DOMAIN)));

            $email_text = $trans->__('Das Dokument %1 wurde an Sie zur Bearbeitung vorgelegt.', false, array($d->titel)).'

'.$trans->__('Unter folgender URL können Sie das Dokument bearbeiten:').' 
'.DOMAIN.'/fokus/

'.($text?$trans->__('Bemerkung:').'
'.$text.'
':'').'
'.$trans->__('Im Reiter Workflow-Optimierung unter fokus individualisieren können Sie ihre Kommunikations- und Benachrichtigungs-Einstellungen bearbeiten');

            $base->email($to_user->email, $email_title, $email_text);
        }
    }
}
?>
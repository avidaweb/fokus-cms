<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'pn') || !$suite->rm(10) || $index != 'n646')
    exit($user->noRights());

$titel = $fksdb->save($_REQUEST['titel']);
$text = rawurldecode($fksdb->save(Strings::removeBadHTML(Strings::cleanString($_POST['text']))));

$empfB = substr($fksdb->save($_REQUEST['empf']), 0, (strlen($fksdb->save($_REQUEST['empf'])) - 1));
$empfA = explode(';', $empfB);
$empf = (count($empfA)?$empfA:array($empfB));

if(!count($empf) || !$text || !$titel)
    exit('no input');

$vid = Strings::createID();

foreach($empf as $e)
{
    $pnQ = $fksdb->fetch("SELECT id, email, indiv FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND id = '".intval($e)."' LIMIT 1");
    if(!$pnQ || $e == $user->getID())
        continue;

    // An Absender (sich selber - Kopie)
    $fksdb->insert("messages", array(
        "benutzer" => $user->getID(),
        "gelesen" => 1,
        "vid" => $vid,
        "von" => $user->getID(),
        "an" => $e,
        "timestamp" => $base->getTime(),
        "text" => $text,
        "titel" => $titel
    ));

    // An Empfänger
    $fksdb->insert("messages", array(
        "benutzer" => $e,
        "gelesen" => 0,
        "vid" => $vid,
        "von" => $user->getID(),
        "an" => $e,
        "timestamp" => $base->getTime(),
        "text" => $text,
        "titel" => $titel
    ));


    // Bei Bedarf Email senden
    $indiv = $base->fixedUnserialize($pnQ->indiv);

    if(!$indiv['no_email_pn'] && $pnQ->email)
    {
        $email_title = $titel.($titel?' - ':'').$trans->__('Private Nachricht auf %1', false, array(str_replace('http://', '', DOMAIN)));

        $email_text = $text.'

'.$trans->__('Private Nachricht beantworten:').' '.DOMAIN.'/fokus/

'.$trans->__('Im Reiter Workflow-Optimierung unter fokus individualisieren können Sie ihre Kommunikations- und Benachrichtigungs-Einstellungen bearbeiten');

        $base->email($pnQ->email, $email_title, $email_text);
    }
}
?>
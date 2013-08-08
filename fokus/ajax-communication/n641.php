<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'pn') || !$suite->rm(10) || $index != 'n641')
    exit($user->noRights());

$limit = $fksdb->save($_REQUEST['limit']);
$b = $fksdb->save($_REQUEST['b']);
if($b) $bo = $fksdb->fetch("SELECT id, vorname, nachname FROM ".SQLPRE."users WHERE id = '".$b."' LIMIT 1");

if($b)
{
    echo '
    <a id="new_msg" rel="'.$bo->id.'">
        <span>'.$trans->__('%1 eine neue Nachricht schreiben', false, array($bo->vorname.' '.$bo->nachname)).'</span>
        <img src="images/small-mail.png" alt="Mail" />
    </a>';
}

$modusA = array(
    $trans->__('Heute, %1', false, array(date('d.m.Y', $base->getTime()))),
    $trans->__('Gestern, %1', false, array(date('d.m.Y', ($base->getTime() - 86400)))),
    $trans->__('Letzte 7 Tage'),
    $trans->__('Letzte 30 Tage'),
    $trans->__('Letzte 90 Tage'),
    $trans->__('Älter')
);
$modus = -1;

$count_pns = 0;

$pnQ = $fksdb->query("SELECT * FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."'".($b?" AND (an = '".$bo->id."' OR von = '".$bo->id."')":"")." GROUP BY vid ORDER BY timestamp DESC LIMIT ".$limit);
while($pn = $fksdb->fetch($pnQ))
{
    $modus_alt = $modus;

    if($modus == -1)
        $modus = 0;
    if($modus == 0 && $pn->timestamp < $base->getZeroHour())
        $modus = 1;
    if($modus == 1 && $pn->timestamp < $base->getZeroHour() - 86400)
        $modus = 2;
    if($modus == 2 && $pn->timestamp < $base->getZeroHour() - (86400 * 6))
        $modus = 3;
    if($modus == 3 && $pn->timestamp < $base->getZeroHour() - (86400 * 30))
        $modus = 4;
    if($modus == 4 && $pn->timestamp < $base->getZeroHour() - (86400 * 90))
        $modus = 5;

     if($modus_alt != $modus)
        echo '<h2 class="calibri">'.$modusA[$modus].'</h2>';

     $gesamt = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."' AND von = '".$user->getID()."' AND vid = '".$pn->vid."'"));
     if($pn->von == $user->getID() && $gesamt > 1 && !$b)
        $nachricht_an = $trans->__('%1 Empfänger', false, array($gesamt));
     else
        $nachricht_an = $base->user(($pn->von == $user->getID()?$pn->an:$pn->von), ' ', 'vorname', 'nachname');

     $count_pns ++;

     echo '
     <div class="pnO">
        <div class="pnA">
            <img src="images/mail'.($pn->von == $user->getID()?'_out':(!$pn->gelesen?'_new':'')).'.png" alt="mails" />
        </div>
        <div class="pnB">
            '.($pn->von == $user->getID()?$trans->__('Nachricht an'):$trans->__('Nachricht von')).'
            <span>'.$nachricht_an.'</span>
            '.$base->is_online($pn->timestamp, true).'
        </div>
        <div class="pnC">
            <a class="titel calibri'.($pn->gelesen || $pn->von == $user->getID()?'':' unread').'" rel="'.$pn->id.'">'.$pn->titel.'</a>
            <p class="vorschau">
                '.Strings::cut(strip_tags(htmlspecialchars_decode($pn->text)), 200).'
            </p>
            <p class="more">
                <a class="show_msg" rel="'.$pn->id.'">'.$trans->__('Nachricht anzeigen').'</a>
                '.(!$pn->gelesen && $pn->an == $user->getID()?'
                <span class="s_gelesen"> | </span>
                <a class="gelesen" rel="'.$pn->id.'">'.$trans->__('Als &quot;gelesen&quot; markieren').'</a>':'').'
                '.($pn->von == $user->getID()?'':' | <a class="answer" rel="'.$pn->id.'|'.$pn->von.'">'.$trans->__('Antwort schreiben').'</a>').'
            </p>
        </div>
     </div>';
}

if(!$count_pns)
    echo '<h2 class="calibri">'.$trans->__('Keine Nachrichten vorhanden.').'</h2>';
?>
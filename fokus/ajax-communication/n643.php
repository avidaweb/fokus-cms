<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'pn') || !$suite->rm(10) || $index != 'n643')
    exit($user->noRights());

$aktiv = $fksdb->save($_REQUEST['aktiv']);

$pnA = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."' AND an = '".$user->getID()."'"));
$pnB = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."' AND an = '".$user->getID()."' AND gelesen = '0'"));

echo '
<a rel="0" class="'.($pnB?' unread':'').''.(!$aktiv?' aktiv':'').'">
    <span>'.$trans->__('Posteingang').'</span>
    <strong><span>'.$pnA.'</span>'.($pnB?' ('.$pnB.')':'').'</strong>
</a>';

$pnQ = $fksdb->query("SELECT von FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."' AND an = '".$user->getID()."' GROUP BY von ORDER BY timestamp DESC LIMIT 10");
while($pn = $fksdb->fetch($pnQ))
{
    $anz = $fksdb->count("SELECT id FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."' AND von = '".$pn->von."'");
    $anz_unr = $fksdb->count("SELECT id FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."' AND von = '".$pn->von."' AND gelesen = '0'");

    echo '
    <a rel="'.$pn->von.'" class="'.($anz_unr?' unread':'').''.($aktiv == $pn->von?' aktiv':'').'">
        <span>'.$base->user($pn->von, ' ', 'vorname', 'nachname').'</span>
        <strong><span>'.$anz.'</span>'.($anz_unr?' ('.$anz_unr.')':'').'</strong>
    </a>';
}
?>
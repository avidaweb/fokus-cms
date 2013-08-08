<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'livetalk') || !$suite->rm(10) || $index != 'n651')
    exit($user->noRights());

$last = $fksdb->save($_GET['last'], 1);
$limit = $fksdb->save($_GET['limit'], 1);
$output = '';

$talkQ = $fksdb->rows("SELECT id, benutzer, timestamp, text FROM ".SQLPRE."livetalk WHERE id > '".$last."' ORDER BY id DESC LIMIT ".$limit);
$users = array();

foreach($talkQ as $talk)
    $users[$talk->benutzer] = intval($talk->benutzer);

$lusers = array();
if(count($users))
    $lusers = $fksdb->rows("SELECT id, vorname, nachname FROM ".SQLPRE."users WHERE id IN (".implode(',', $users).")");

foreach($talkQ as $talk)
{
    $u = $lusers[$talk->benutzer];

    $output = '
    <div class="talk" id="talk_'.$talk->id.'">
        <input type="hidden" class="userhidden" value="'.$talk->benutzer.'" />
        <input type="hidden" class="tstamp" value="'.$talk->timestamp.'" />
        <div class="LL">
            '.($talk->benutzer == $user->getID()?'<div>':'<a rel="'.$talk->benutzer.'" id="n590" class="inc_users">').'
                '.$u->vorname.' '.$u->nachname.'
            '.($talk->benutzer == $user->getID()?'</div>':'</a>').'
            <span>// '.$base->is_online($talk->timestamp, true).'</span>
        </div>
        <div class="LR">
            '.Strings::url2link($talk->text).'
        </div>
    </div>
    <div class="sperre"></div>'.$output;
}

exit($output);
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok', 'ezsb') || $index != 'n281')
    exit($user->noRights());

$d = 0;
$zq = $fksdb->query("SELECT id, name FROM ".SQLPRE."responsibilities WHERE papierkorb = '0' ORDER BY name");
while($z = $fksdb->fetch($zq))
{
    echo '
    <tr'.(!$d?' class="first"':'').'>
        <td class="f">'.$z->name.'</td>
        <td><a rel="'.$z->id.'">'. $trans->__('Zuständigkeitsbereich löschen') .'</a></td>
    </tr>';
    $d ++;
}

if(!$fksdb->count($zq))
    echo '<tr class="first"><td class="f">'. $trans->__('Es wurden noch keine Zuständigkeiten angelegt') .'</td></tr>';
?>
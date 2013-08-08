<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'kkanal') || $index != 'n630')
    exit($user->noRights());

$ckom = $fksdb->query("SELECT id FROM ".SQLPRE."comments LIMIT 1");
$csuch = $fksdb->query("SELECT id FROM ".SQLPRE."searches LIMIT 1");
$ef = 0;

echo '
<h1>'.$trans->__('Kommunikationskanäle.').'</h1>

<div class="box" id="kkanaele">
    <div class="kanal">
        <h2 class="calibri">'.$trans->__('<span>Kanal:</span> Formulare.').'</h2>
        <p>'.$trans->__('Übersicht aller Daten, die über Formulare auf Ihrer Webseite gesendet wurden.').'</p>
        <table class="forms">';
            $kkQ = $fksdb->query("SELECT vid FROM ".SQLPRE."blocks WHERE type = '52' GROUP BY vid ORDER BY id DESC");
            while($kkL = $fksdb->fetch($kkQ))
            {
                $kk = $fksdb->fetch("SELECT html, vid FROM ".SQLPRE."blocks WHERE type = '52' AND vid = '".$kkL->vid."' ORDER BY id DESC LIMIT 1");
                $fo = $base->fixedUnserialize($kk->html);

                $fQ = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."records WHERE vid = '".$kk->vid."' LIMIT 1"));
                if(!$fQ)
                    $ef ++;

                echo '
                <tr'.(!$fQ?' class="is_empty"':'').'>
                    <td class="a">
                        <a id="n631" class="inc_communication" rel="formular_'.$kk->vid.'">'.($fo['name']?$fo['name']:$trans->__('Unbenanntes Formular')).'</a>
                    </td>
                    <td class="b">
                        <a id="n631" class="inc_communication" rel="formular_'.$kk->vid.'">'.$trans->__('öffnen').'</a>
                    </td>
                </tr>';
            }

            if($ef)
            {
                echo '
                <tr class="empty">
                    <td colspan="2">
                        <a>
                            '.($ef != 1?
                            $trans->__('%1 leere Formulare ebenfalls anzeigen', false, array($ef))
                            :
                            $trans->__('1 leeres Formular ebenfalls anzeigen')
                            ).'
                        </a>
                    </td>
                </tr>';
            }
        echo '
        </table>
        '.(!$fksdb->count($kkQ)?'<span class="hinweis">'.$trans->__('Noch keine Daten in diesem Kommunikationskanal vorhanden').'</span>':'
        <a class="rbutton rollout">'.$trans->__('Kommunikationskanal <span>öffnen</span>').'</a>').'
    </div>

    <div class="kanal">
        <h2 class="calibri">'.$trans->__('<span>Kanal:</span> Kommentare.').'</h2>
        <p>'.$trans->__('Übersicht aller Daten, die über Kommente auf Ihrer Webseite gesendet wurden.').'</p>
        '.($fksdb->count($ckom)?'<a class="rbutton goaway" data-task="kommentare">'.$trans->__('Kommunikationskanal öffnen').'</a>':'
        <span class="hinweis">'.$trans->__('Noch keine Daten in diesem Kommunikationskanal vorhanden').'</span>').'
    </div>

    <div class="kanal">
        <h2 class="calibri">'.$trans->__('<span>Kanal:</span> Suche.').'</h2>
        <p>'.$trans->__('Übersicht aller Daten, die über die Suche auf Ihrer Webseite gesendet wurden.').'</p>
        '.($fksdb->count($csuch)?'<a class="rbutton goaway" data-task="suche">'.$trans->__('Kommunikationskanal öffnen').'</a>':'
        <span class="hinweis">'.$trans->__('Noch keine Daten in diesem Kommunikationskanal vorhanden').'</span>').'
    </div>
</div>';
?>
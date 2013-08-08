<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if($index != 'n200')
    exit();

$f = $rel;

if($f && (!$user->r('dok', 'publ') || !$user->r('dok', 'publ_all')))
    exit($user->noRights());

$eid = $fksdb->save($_REQUEST['eid']);
$nobox = $fksdb->save($_REQUEST['nobox']);

$optionen = array(1 => $trans->__("Dateiname"), 2 => $trans->__("Status"), 3 => $trans->__("Ersteller / Ursprünglicher Autor"), 4 => $trans->__("Letzter Autor / letzter Bearbeiter"),  5 => $trans->__("Erstellungsdatum"),  6 => $trans->__("Letztes Bearbeitungsdatum"),  7 => $trans->__("Letztes Freischaltdatum"),  8 => $trans->__("Zugeordnete Strukturelemente"));
$pre_checked = array(1, 2, 4, 6, 8);

$dka = $base->db_to_array($base->getOpt()->dk);
$dk = (object)$dka;

$ordner = "../content/dklassen";
$handle = opendir($ordner);
while ($file = readdir ($handle))
{
    if($file != "." && $file != "..")
    {
        $fk = $base->open_dklasse($ordner.'/'.$file);
        $filename = str_replace('.php', '', $file);

        $dklassen[$fk['group']][] = array(
            'file' => $filename,
            'name' => $fk['name']
        );
    }
}

echo (!$nobox?'
<h1>'.(!$f?$trans->__('Dokumente verwalten'):$trans->__('Dokumente freigeben')).'.</h1>

<input type="hidden" id="is_freigabe" value="'.$f.'" />
<input type="hidden" id="akt_limit" value="15" />

<div class="box">':'').'
    <div id="dokumente">
        <div class="kopf">
            <div class="kopfL">
                '.($user->r('dok', 'new')?'<button id="n210" class="inc_documents">Neues Dokument anlegen<a rel="0"></a></button>':'').'
            </div>
            <div class="kopfR">
                '. $trans->__('Dokumente suchen:') .' <input type="search" name="suche" />
            </div>
        </div>

        <div class="menue documentmenu">';

            if(count($dklassen))
            {
                echo '
                <div class="li">
                    <a class="rbutton rollout">'. $trans->__('Dokumentenklassen <span>einblenden</span>') .' </a>
                    <div class="opt opt1">
                        <div class="ch">
                            <input type="radio" name="showdk" value="0" id="showdk_0" checked />
                            <label for="showdk_0">'. $trans->__('Nur freie Dokumente anzeigen') .'</label>
                        </div>
                        <div class="ch">
                            <input type="radio" name="showdk" value="1" id="showdk_1" />
                            <label for="showdk_1">'. $trans->__('Nur Dokumentenklassen anzeigen') .'</label>
                        </div>

                        <div class="showdk">';
                            foreach($dklassen as $gruppe => $dkg)
                            {
                                echo '
                                <a'.(!$cdkg?' class="firstg"':'').'>'.($gruppe?$trans->__('Gruppe &quot;').($gruppe).'&quot;':$trans->__('Ohne Gruppe')).'</a>
                                <div>';
                                    foreach($dkg as $dk)
                                    {
                                        echo '
                                        <p>
                                            <input type="checkbox" value="'.$dk['file'].'" id="'.$eid.'dkg_'.$gruppe.'_'.$dk['file'].'" />
                                            <label for="'.$eid.'dkg_'.$gruppe.'_'.$dk['file'].'">'.($dk['name']?($dk['name']):$dk['file']).'</label>
                                        </p>';
                                    }
                                echo '
                                </div>';

                                $cdkg ++;
                            }
                        echo '
                        </div>
                    </div>
                </div>';
            }

            echo '
            <div class="li">
                <a class="rbutton rollout">'. $trans->__('Mögliche Felder <span>einblenden</span>') .'</a>
                <div class="opt opt2">
                    <table>';
                        foreach($optionen as $oA => $oB)
                        {
                            echo '
                            <tr>
                                <td><input type="checkbox" id="'.$eid.'dsel_'.$oA.'" value="'.$oA.'"'.(in_array($oA, $pre_checked)?' checked="checked"':'').' /></td>
                                <td><label for="'.$eid.'dsel_'.$oA.'">'.$oB.'</label></td>
                            </tr>';
                        }
                        echo '
                    </table>
                </div>
            </div>
        </div>

        <table id="docs_auflistung">
            <tr><td><img src="images/loading.gif" alt="'. $trans->__('Bitte warten.. Inhalt wird geladen..') .'" class="ladebalken" /></td></tr>
        </table>
    </div>
'.(!$nobox?'
</div>':'');
?>
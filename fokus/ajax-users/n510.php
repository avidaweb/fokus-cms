<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(($index != 'n510' && $index != 'n515') || !$user->isAdmin())
    exit($user->noRights());

if(($rel == 1 && !$tkunde) || ($rel == 2 && !$tmitarbeiter) || !$user->r('per'))
    exit($user->noRights());

echo '
<input type="hidden" id="type" value="'.$rel.'" />
<h1>'.($rel == 1?$trans->__('Kunden verwalten.'):$trans->__('Mitarbeiter verwalten.')).'</h1>

<div class="box">
    <div id="personen">
        <div class="kopf">
            <div class="kopfL">
                '.($user->r('per', 'new')?'
                    <button id="n53'.($row->type == 1?'0':'5').'" class="inc_users">
                        '.($rel == 1?$trans->__('Neuen Kunden anlegen'):$trans->__('Neuen Mitarbeiter anlegen')).'
                        <a rel="n_'.$rel.'"></a>
                    </button>
                ':'').'
            </div>
            <div class="kopfR">
                '.$trans->__('Personen suchen:').'
                <input type="search" name="suche" />
            </div>
        </div>

        <div class="menue">
            <div class="li">
                <a class="rbutton rollout">'.$trans->__('Rollen <span>einblenden</span>').'</a>
                <div class="opt opt2">
                    <table>
                        <tr>
                            <td><input type="checkbox" id="pro_0" value="0" checked class="first" /></td>
                            <td><label for="pro_0"><em>'.$trans->__('Alle Rollen').'</em></label></td>
                        </tr>';
                        $roQ = $fksdb->query("SELECT id, titel FROM ".SQLPRE."roles WHERE papierkorb = '0' ORDER BY sort");
                        while($ro = $fksdb->fetch($roQ))
                        {
                            echo '
                            <tr>
                                <td><input type="checkbox" id="pro_'.$ro->id.'" value="'.$ro->id.'" /></td>
                                <td><label for="pro_'.$ro->id.'">'.$ro->titel.'</label></td>
                            </tr>';
                        }
                        echo '
                    </table>
                </div>
            </div>

            <div class="li">
                <a class="rbutton rollout">'.$trans->__('Mögliche Felder <span>einblenden</span>').'</a>
                <div class="opt opt1">
                    <table>';
                        foreach($optionen as $oA => $oB)
                        {
                            echo '
                            <tr>
                                <td><input type="checkbox" id="pcb_'.$oA.'" value="'.$oA.'"'.(in_array($oA, $pre_checked)?' checked':'').' /></td>
                                <td><label for="pcb_'.$oA.'">'.$oB.'</label></td>
                            </tr>';
                        }
                        echo '
                    </table>
                </div>
            </div>
        </div>

        <table id="pers_auflistung">
            <tr><td><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>
        </table>
    </div>
</div>';
?>
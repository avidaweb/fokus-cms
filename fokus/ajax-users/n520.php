<?php
if($user->r('per', 'firma') && $index == 'n520')
{
    echo '
    <h1>'.$trans->__('Firmen verwalten.').'</h1>
    
    <div class="box">
        <div id="firmen">
            <div class="kopf">
                <h2>'.$trans->__('Alle Firmen in der Übersicht').'</h2>
                '.$trans->__('Folgend erhalten Sie eine Auflistung aller Firmen.').'<br />
            </div>    
            
            <div class="menue">
                <div class="li">
                    <a class="more_opt">'.$trans->__('Optionen einblenden').'</a>
                    <div class="opt">
                        <strong>'.$trans->__('Folgende Attribute werden im Ergebnis angezeigt:').'</strong>
                        <table>';
                            foreach($optionenF as $oA => $oB)
                            {
                                echo '
                                <tr>
                                    <td><input type="checkbox" value="'.$oA.'"'.(in_array($oA, $pre_checked)?' checked="checked"':'').' /></td>
                                    <td>'.$oB.'</td>
                                </tr>';
                            }
                            echo '
                            <tr>
                                <td></td>
                                <td><button class="aktualisieren">'.$trans->__('aktualisieren').'</button></td>
                            </tr>
                        </table>
                    </div>
                </div>
                <div class="re">
                    <p><button class="inc_users" id="n570">'.$trans->__('Neue Firma anlegen').'</button></p>
                    '.$trans->__('Firma suchen:').' <input type="text" id="suche" />
                </div>
            </div>    
            
            <table id="firmen_auflistung">
                <tr><td><img src="images/loading.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>
            </table>
            
            <div class="menue">
                <div class="li">&nbsp;</div>
                <div class="re"><br />
                    <p>'.$trans->__('Firma suchen:').' <input type="text" id="suche" /></p>
                    <button class="inc_users" id="n570">'.$trans->__('Neue Firma anlegen').'</button>
                </div>
            </div>
        </div>
    </div>';
}
?>
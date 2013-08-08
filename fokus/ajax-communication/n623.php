<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'nlsend') || !$suite->rm(5) || $index != 'n623')
    exit($user->noRights());

echo '
<div class="benutzerauswahl">
    <h1>'.$trans->__('Empfänger zum Newsletter hinzufügen').'</h1>

    <form class="auswahl">
        <div class="box">
            <input type="hidden" name="empf" value="'.$fksdb->save($_POST['cempf']).'" />

            <div class="a">';
                $rolQ = $fksdb->query("SELECT id, titel FROM ".SQLPRE."roles WHERE papierkorb = '0' ORDER BY sort, id");
                while($rol = $fksdb->fetch($rolQ))
                {
                    echo '
                    <p>
                        <input type="checkbox" id="roln_'.$rol->id.'" name="ro[]" value="'.$rol->id.'" />
                        <label for="roln_'.$rol->id.'">'.$rol->titel.'</label>
                    </p>';
                }
            echo '
            </div>
            <div class="b">
                <input type="text" name="q" />
            </div>
        </div>

        <div class="box">
            <button class="alle">'.$trans->__('Alle auswählen').'</button>
            <button class="none">'.$trans->__('Auswahl aufheben').'</button>

            <div class="fstati">
                <input type="checkbox" id="fstati_aus" name="fstati" value="1" />
                <label for="fstati_aus">'.$trans->__('Inaktive und gesperrte Personen verstecken').'</label>
            </div>
        </div>
    </form>

    <form class="ergebnis">
        <div class="box">
            <table>
                <tr><td><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>
            </table>
        </div>
    </form>
</div>

<div class="box_save">
    <button class="bs1">'.$trans->__('abbrechen').'</button>
    <button class="bs2">'.$trans->__('hinzufügen').'</button>
</div>';
?>
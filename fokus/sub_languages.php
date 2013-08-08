<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');

$index = $fksdb->save($_REQUEST['index']);
$rel = $fksdb->save($_REQUEST['rel']);


if($index == 's310')
{    
    echo '
    <h1>Alle Sprachen.</h1>
    
    <div class="box">
        <div class="csb">
            Sie k&ouml;nnen '.($suite->getLimitOfLanguages() == -1?'beliebig viele Sprachen':'ingesamt '.$suite->getLimitOfLanguages().' Sprache'.($suite->getLimitOfLanguages() != 1?'n':'')).' mit dieser Lizenz verwenden und verwalten.
            <br />Sie haben aktuell '.$base->getActiveLanguagesCount().' Sprache'.($base->getActiveLanguagesCount() != 1?'n':'').' gew&auml;hlt.
            
            <table id="sprachenuebersicht">';            
            foreach($base->getActiveLanguages() as $sp)
            {
                echo '
                <tr>
                    <td><input type="radio" name="csp" id="csp_'.$sp.'" value="'.$sp.'"'.($trans->getInputLanguage() == $sp?' checked="checked"':'').' /> <a id="s350" class="'.$sp.'"></a></td>
                    <td><label for="csp_'.$sp.'"><img src="'.$trans->getFlag($sp, 2).'" alt="" /></label></td>
                    <td><label for="csp_'.$sp.'"><strong>'.$trans->__(strtoupper($sp)).'</strong></label></td>
                </tr>';
            }  
            echo '
            </table>
            
            '.($user->r('fks', 'opt')?'Zum Verwalten und hinzuf&uuml;gen von Sprachen &ouml;ffnen Sie bitte die <a id="s420" class="sub_settings" rel="4">fokus Einstellungen</a>.':'').'
        </div>
    </div>';
}
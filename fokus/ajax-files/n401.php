<?php
if($index == 'n401' && $user->r('dat'))
{
    $dir = $fksdb->save($_GET['dir']);
    $ordner = $fksdb->fetch("SELECT titel FROM ".SQLPRE."files WHERE id = '".$dir."' AND isdir = '1' LIMIT 1");
    
    echo '
    <div class="left">
        '.($user->r('dat', 'new')?'
        <fieldset>
            <legend>'.$trans->__('Upload.').'</legend>
            <button class="button shortcut-new" id="bilder_hochladen_'.($a > 2?'2':'1').'" name="'.($a-1).'">
                '.($a > 2?
                    $trans->__('Dateien hochladen')
                    :
                    $trans->__('Bilder hochladen')
                ).'
            </button>
        </fieldset>':'').'
        
        '.($a <= 2?'
        <fieldset class="dir">
            <legend>'.$trans->__('Ordner.').'</legend>
            <p>'.(!$dir?$trans->__('Hauptverzeichnis'):$ordner->titel).'</p>
            '.($user->r('dat', 'dir')?'<button>'.$trans->__('Ordner verwalten').'</button>':'').'
        </fieldset>':'').'
        
        <fieldset>
            <legend>'.$trans->__('Filter.').'</legend>
            
            <div class="dropdown">
                <span class="foben">'.$trans->__('Dateitypen.').'</span>
                '.($a == 1 || $a == 2?'
                <p><input type="checkbox" id="dateityp_1" checked="checked" /> <label for="dateityp_1">.jpg</label></p>
                <p><input type="checkbox" id="dateityp_2" checked="checked" /> <label for="dateityp_2">.gif</label></p>
                <p><input type="checkbox" id="dateityp_3" checked="checked" /> <label for="dateityp_3">.png</label></p>
                ':'').'
                '.($a == 3?'
                <p><input type="checkbox" id="dateityp_1" checked="checked" /> <label for="dateityp_1">.zip</label></p>
                <p><input type="checkbox" id="dateityp_2" checked="checked" /> <label for="dateityp_2">.rar</label></p>
                <p><input type="checkbox" id="dateityp_3" checked="checked" /> <label for="dateityp_3">.pdf</label></p>
                <p><input type="checkbox" id="dateityp_4" checked="checked" /> <label for="dateityp_4">.xls / .xlsx</label></p>
                <p><input type="checkbox" id="dateityp_5" checked="checked" /> <label for="dateityp_5">.doc / .docx</label></p>
                <p><input type="checkbox" id="dateityp_6" checked="checked" /> <label for="dateityp_6">.???</label></p>
                ':'').'
            </div>
            
            '.($a == 1?'
            <div class="dropdown">
                <span class="foben">'.$trans->__('Ausrichtung.').'</span>
                <p><input type="checkbox" id="ausrichtung_1" checked="checked" /> <label for="ausrichtung_1">'.$trans->__('horizontal').'</label></p>
                <p><input type="checkbox" id="ausrichtung_2" checked="checked" /> <label for="ausrichtung_2">'.$trans->__('vertikal').'</label></p>
            </div>
            ':'').'
        </fieldset>
        
        <fieldset>
            <legend>'.$trans->__('Sortierung.').'</legend>
            
            <div class="dropdown2">
                <p><input type="radio" name="sort1" id="sort1_1" /> <label for="sort1_1">'.$trans->__('Dateiname').'</label></p>
                <p><input type="radio" name="sort1" id="sort1_2" checked="checked" /> <label for="sort1_2">'.$trans->__('Upload').'</label></p>
                <p><input type="radio" name="sort1" id="sort1_3" /> <label for="sort1_3">'.$trans->__('Aktualisierung').'</label></p>
                <p><input type="radio" name="sort1" id="sort1_4" /> <label for="sort1_4">'.$trans->__('Autor').'</label></p>
            </div>
            
            <hr />
            
            <div class="dropdown2">
                <p><input type="radio" name="sort2" id="sort2_1" /> <label for="sort2_1">'.$trans->__('Aufsteigend').'</label></p>
                <p><input type="radio" name="sort2" id="sort2_2" checked="checked" /> <label for="sort2_2">'.$trans->__('Absteigend').'</label></p>
            </div>
        </fieldset>
    </div>
    <div class="mitte">
        '.($a == 1 || $a == 2 || $a == 4?'
        <table class="container">
            <tr class="oben">
                <td class="obenL"></td>
                <td class="obenM">
                    <div class="bsuche">
                        <span>
                            <input type="text" placeholder="'.$trans->__('Suche').'" />
                        </span>
                        <button style="display:none;">'.$trans->__('Zurück').'</button>
                    </div>
                    <div class="mauswahl">
                        <input type="checkbox" id="mauswahl1" /> 
                        <label for="mauswahl1">'.$trans->__('Mehrfachauswahl').'</label>
                    </div>
                    <div class="bgr">
                        '.$trans->__('klein').'
                        <div class="wslider" id="wslider1"></div>
                        '.$trans->__('groß').'
                    </div>
                </td>
                <td class="obenR"></td>
            </tr>
            <tr class="tr_loading haupt" id="hauptLoading">
                <td class="hauptL"></td>
                <td class="center"><img src="images/loading_grey.gif" alt="LOADING..." /></td>
                <td class="hauptR"></td>
            </tr>
            <tr class="haupt">
                <td class="hauptL"></td>
                <td class="board"></td>
                <td class="hauptR"></td>
            </tr>
            <tr class="unten">
                <td class="untenL"></td>
                <td class="untenM">
                    <div class="bsuche">
                        <span>
                            <input type="text" placeholder="'.$trans->__('Suche').'" />
                        </span>
                        <button style="display:none;">'.$trans->__('Zurück').'</button>
                    </div>
                    <div class="mauswahl">
                        <input type="checkbox" id="mauswahl2" /> 
                        <label for="mauswahl2">'.$trans->__('Mehrfachauswahl').'</label>
                    </div>
                    <div class="bgr">
                        '.$trans->__('klein').'
                        <div class="wslider" id="wslider2"></div>
                        '.$trans->__('groß').'
                    </div>
                </td>
                <td class="untenR"></td>
            </tr>
        </table>':'').'
        
        '.($a == 3?'
        <div id="dateien">
        
        </div>':'').'
    </div>
    <div class="right">
        '.($a == 3?'
        <fieldset id="backbuttond">
            <legend>'.$trans->__('Übersicht.').'</legend>
            <div>
                <button class="button">'.$trans->__('Zurück zur Übersicht').'</button>
            </div>
        </fieldset>
        
        <fieldset>
            <legend>'.$trans->__('Suche.').'</legend>
            <div class="bsuche">
                <input type="text" />
            </div>
        </fieldset>':'').'
        
        <fieldset>
            <legend>'.$trans->__('Vorschau.').'</legend>
            <div id="vorschau">
                <em>
                    '.($a > 2?
                        $trans->__('Ein oder mehrere Dateien durch Anklicken auswählen')
                        :
                        $trans->__('Ein oder mehrere Bilder durch Anklicken auswählen')
                    ).'
                </em>
            </div>
        </fieldset>
    </div>';
}
?>
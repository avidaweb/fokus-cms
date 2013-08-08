<?php
if($user->r('str', 'ele') && (!$eleopt || $user->r('str', 'dk')) && $index == 'n155')
{
    $element = $fksdb->fetch("SELECT id, dklasse FROM ".SQLPRE."elements WHERE id = '".$v->sid."' AND struktur = '".$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
    if(!$element)
        exit('<div class="ifehler">'.$trans->__('Ausgewähltes Element existiert nicht oder befindet sich im Papierkorb').'</div>');
        
    
    $dk = $base->fixedUnserialize($element->dklasse);
    if(!is_array($dk)) $dk = array();
        
    echo '
    <h1>'.$trans->__('Dokumentenklassen zuordnen.').'</h1>
    
    <div class="box">  
        <form id="strukturelement_dk">
        <table>
            <tr class="dkmore">
                <td class="dkmore_cbtd"><input type="radio" name="dklasse" id="dklasseA" value="0"'.(!$element->dklasse?' checked="checked"':'').' /></td>
                <td><label for="dklasseA">'.$trans->__('Dokumente werden nur manuell zugewiesen.').'</label></td>
            </tr>
            <tr class="dkmore dkmore2">
                <td class="dkmore_cbtd"><input type="radio" name="dklasse" id="dklasseB" value="1"'.($element->dklasse?' checked="checked"':'').' /></td>
                <td>
                    <label for="dklasseB">'.$trans->__('Zusätzlich Dokumente aus Dokumentenklassen automatisiert zuweisen.').'</label>
                    
                    <table id="struk_dok_table2B"'.($element->dklasse?' style="display:table;"':'').'>
                        <tr id="dk_more_optA">
                            <td><input type="checkbox" name="dk_type0" class="dk_type" id="dk_typeA" value="1"'.($dk['dk_type'] == 1 || $dk['dk_type'] == 3?' checked="checked"':'').' /></td>
                            <td><label for="dk_typeA">'.$trans->__('Folgende Dokumente im Strukturelement einfügen.').'</label></td>
                        </tr>
                        <tr id="dk_more_optB">
                            <td><input type="checkbox" name="dk_type1" class="dk_type" id="dk_typeB" value="1"'.($dk['dk_type'] == 2 || $dk['dk_type'] == 3?' checked="checked"':'').' /></td>
                            <td class="dkbo"><label for="dk_typeB">'.$trans->__('Folgende Dokumente als Strukturelemente behandeln und unterordnen.').'</label></td>
                        </tr>
                        <tr id="dk_more_opt"'.(!$dk['dk_type']?' style="display:none;"':'').'>
                            <td></td>
                            <td>
                                <table>';
                                    $ordner = "../content/dklassen";
                                    $handle = opendir($ordner);
                                    while ($file = readdir ($handle)) 
                                    {
                                        if($file != "." && $file != "..") 
                                        {
                                            $fk = $base->open_dklasse($ordner.'/'.$file);
                                            $filename = str_replace('.php', '', $file);
                                            
                                            if(!$fk['name'])
                                                continue;
                                                                                                
                                            echo '
                                            <tr>
                                                <td class="lfi">'.(!$cdk?'<strong>'.$trans->__('Klassen zuweisen:').'</strong>':'').'</td>
                                                <td>
                                                    <input type="checkbox" name="dk_klassen[]" value="'.$filename.'" id="dk_klassen_'.$filename.'"'.(is_array($dk)?(in_array($filename, $dk['dk_klassen'])?' checked="checked"':''):'').' />
                                                    <label for="dk_klassen_'.$filename.'">'.$fk['name'].'</label>
                                                </td>
                                            </tr>';
                                            $cdk ++;
                                        }
                                    }
                                    
                                    /*
                                    echo '
                                    <tr>
                                        <td class="lfi"><strong>'.$trans->__('Sortierung:').'</strong></td>
                                        <td>
                                            <select name="dk_sort">
                                                <option value="datum DESC"'.($dk['dk_sort'] == 'datum DESC'?' selected="selected"':'').'>
                                                    '.$trans->__('Dokumenten-Datum (neueste zuerst)').'
                                                </option>
                                                <option value="datum ASC"'.($dk['dk_sort'] == 'datum ASC'?' selected="selected"':'').'>
                                                    '.$trans->__('Dokumenten-Datum (älteste zuerst)').'
                                                </option>
                                                <option value="timestamp DESC"'.($dk['dk_sort'] == 'timestamp DESC'?' selected="selected"':'').'>
                                                    '.$trans->__('Erstelldatum (neueste zuerst)').'
                                                </option>
                                                <option value="timestamp ASC"'.($dk['dk_sort'] == 'timestamp ASC'?' selected="selected"':'').'>
                                                    '.$trans->__('Erstelldatum (älteste zuerst)').'
                                                </option>
                                                <option value="titel ASC"'.($dk['dk_sort'] == 'titel ASC'?' selected="selected"':'').'>
                                                    '.$trans->__('Name (A-Z)').'
                                                </option>
                                                <option value="titel DESC"'.($dk['dk_sort'] == 'titel DESC'?' selected="selected"':'').'>
                                                    '.$trans->__('Name (Z-A)').'
                                                </option>
                                                <option value="RAND()"'.($dk['dk_sort'] == 'RAND()'?' selected="selected"':'').'>
                                                    '.$trans->__('Zufall').'
                                                </option>
                                            </select>
                                        </td>
                                    </tr>';
                                    */
                                    
                                echo '
                                </table>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
        </form>
    </div>
    <div class="box_save" style="display:block;">
        <input type="submit" class="bs2" value="'.$trans->__('speichern &amp; schließen').'" />
    </div>';    
}
?>
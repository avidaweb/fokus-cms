<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('fks', 'pure'))
    exit($user->noRights());
    
if($index != 's490')
    exit('wrong page');
    

/** count documents (start) */    
$count = array();
$count['docs'] = $fksdb->count("SELECT id FROM ".SQLPRE."documents");
$count['dversions'] = $fksdb->count("SELECT id FROM ".SQLPRE."document_versions WHERE timestamp_freigegeben != '0'"); 
$count['version_percent'] = round(($count['dversions'] / $count['docs']), 1);

$docs = $fksdb->query("SELECT id FROM ".SQLPRE."documents");
while($doc = $fksdb->fetch($docs))
{
    $langs = array();
    
    $dvcQ = $fksdb->query("SELECT id, language FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."' AND timestamp_freigegeben != '0'");
    while($dvc = $fksdb->fetch($dvcQ))
        $langs[$dvc->language] ++;
        
    if(!count($langs))
        continue;
    
    foreach($langs as $max)
    {
        $count['loose_3'] += ($max > 3?($max - 3):0);  
        $count['loose_10'] += ($max > 10?($max - 10):0);    
        $count['loose_second'] += floor(($max / 2));
    }         
}

$count['loose_3_percent'] = round(($count['loose_3'] / $count['dversions'] * 100), 1);
$count['loose_10_percent'] = round(($count['loose_10'] / $count['dversions'] * 100), 1);
$count['loose_second_percent'] = round(($count['loose_second'] / $count['dversions'] * 100), 1);
/** count documents (end) */  
 

        
echo '
<h1>'.$trans->__('Systemreinigung.').'</h1>

<div class="box infobox calibri">
    '.$trans->__('Bitte wählen Sie unten aus, welche Aktionen Sie ausführen lassen möchten. Die Ausführung startet erst mit dem Klick auf "Aktionen jetzt ausführen".').' 
</div>

<form>
<div class="box pure">
    <article>
        <h2 class="calibri">'.$trans->__('Dokumente.').'</h2>
        
        <div class="tab">
            <p>
                <input type="checkbox" name="docs_unused" id="pure_docs_unused" value="1" />
                <label for="pure_docs_unused">'.$trans->__('Dokumente aus dem System entfernen, die nicht verwendet werden').'</label>
            </p>
            
            <aside>
                <p>
                    <input type="radio" name="docs_unused_task" id="pure_docs_unused_task_1" value="1" checked />
                    <label for="pure_docs_unused_task_1">'.$trans->__('Dokumente löschen, die keinem Strukturelement oder Slot zugeordnet sind').'</label>
                </p>
                <p>
                    <input type="radio" name="docs_unused_task" id="pure_docs_unused_task_2" value="2" />
                    <label for="pure_docs_unused_task_2">'.$trans->__('Dokumente löschen, die keinem Strukturelement, Slot oder Newsletter zugeordnet sind').'</label>
                </p>
                
                <p class="padding_top">
                    <input type="checkbox" name="docs_unused_dclass" id="pure_docs_unused_dclass" value="1" />
                    <label for="pure_docs_unused_dclass">'.$trans->__('Dokumente in Dokumentenklassen einschließen').'</label>
                </p>
            </aside>
        </div>
        
        <div class="tab">
            <p>
                <input type="checkbox" name="docs_versions" id="pure_docs_versions" value="1" />
                <label for="pure_docs_versions">'.$trans->__('Alte Versionen von Dokumenten entfernen').'</label>
            </p>
            
            <aside>
                <ul class="info">
                    <li>'.$trans->__('Es existieren aktuell <strong>%1 Dokumente</strong> im System.', false, array($count['docs'])).'</li>
                    <li>'.$trans->__('Durchschnittlich hat ein Dokument <strong>%1 Versionen</strong>.', false, array($count['version_percent'])).'</li>
                    <li>'.$trans->__('Insgesamt sind <strong>%1 Versionen</strong> im System vorhanden.', false, array($count['dversions'])).'</li>
                </ul>
                
                <div>'.$trans->__('Folgende Aktion für die Versionen eines Dokumentes durchführen:').'</div>
                
                <p class="padding_top">
                    <input type="radio" name="docs_versions_task" id="pure_docs_versions_task_1" value="1" checked />
                    <label for="pure_docs_versions_task_1">
                        '.$trans->__('Nur die neuesten 3 Versionen aufheben (ca %1% / %2 der Versionen entfallen)', false, array(
                            $count['loose_3_percent'],
                            $count['loose_3']
                        )).'
                    </label>
                </p>
                <p>
                    <input type="radio" name="docs_versions_task" id="pure_docs_versions_task_2" value="2" />
                    <label for="pure_docs_versions_task_2">
                        '.$trans->__('Nur die neuesten 10 Versionen aufheben (ca %1% / %2 der Versionen entfallen)', false, array(
                            $count['loose_10_percent'],
                            $count['loose_10']
                        )).'
                    </label>
                </p>
                <p>
                    <input type="radio" name="docs_versions_task" id="pure_docs_versions_task_3" value="3" />
                    <label for="pure_docs_versions_task_3">
                        '.$trans->__('Jede zweite Version aus dem System entfernen (ca %1% / %2 der Versionen entfallen)', false, array(
                            $count['loose_second_percent'],
                            $count['loose_second']
                        )).'
                    </label>
                </p>
            </aside>
        </div>
    </article>
    
    <article>
        <h2 class="calibri">'.$trans->__('Bilder.').'</h2>
        
        <p>
            <input type="checkbox" name="pics_versions" id="pure_pics_versions" value="1" />
            <label for="pure_pics_versions">'.$trans->__('Alte Versionen von Bildern entfernen').'</label>
        </p>
        <p>
            <input type="checkbox" name="pics_unused" id="pure_pics_unused" value="1" />
            <label for="pure_pics_unused">'.$trans->__('Bild-Dateien vom Server löschen, die nicht mehr verwendet werden').'</label>
        </p>
        <p>
            <input type="checkbox" name="pics_cache" id="pure_pics_cache" value="1" />
            <label for="pure_pics_cache">'.$trans->__('Bilder-Cache leeren (Thumbnails)').'</label>
        </p>
    </article>
    
    <article>
        <h2 class="calibri">'.$trans->__('Allgemein.').'</h2>
        
        <p>
            <input type="checkbox" name="optimize" id="pure_optimize" value="1" />
            <label for="pure_optimize">'.$trans->__('Datenbank via SQL-Befehl optimieren').'</label>
        </p>
        <p>
            <input type="checkbox" name="trash" id="pure_trash" value="1" />
            <label for="pure_trash">'.$trans->__('Papierkorb leeren').'</label>
        </p>
        <p>
            <input type="checkbox" name="livetalk" id="pure_livetalk" value="1" />
            <label for="pure_livetalk">'.$trans->__('Livetalk leeren').'</label>
        </p>
        <p>
            <input type="checkbox" name="last_used" id="pure_last_used" value="1" />
            <label for="pure_last_used">'.$trans->__('"Zuletzt verwendet" leeren').'</label>
        </p>
        <p>
            <input type="checkbox" name="user_inactive" id="pure_user_inactive" value="1" />
            <label for="pure_user_inactive">'.$trans->__('Benutzer mit dem Status "inaktiv" aus dem System entfernen').'</label>
        </p>
    </article>
</div>
</form>

<div class="box_save">
    <input type="button" value="'.$trans->__('abbrechen').'" class="bs1" /> 
    <input type="button" value="'.$trans->__('gewählte Aktionen jetzt ausführen').'" class="bs2" />
</div>';
?>
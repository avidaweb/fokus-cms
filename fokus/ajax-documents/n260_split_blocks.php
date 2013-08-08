<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin() || $index != 'n260_split_blocks' || !$user->r('dok'))
    exit($user->noRights());
    
$id = $fksdb->save($_POST['id'], 1);
$block = $fksdb->save($_POST['block']);
$ibid = $fksdb->save($_POST['ibid']);
$blockindex = $fksdb->save($_POST['blockindex']);
$dkdatei = $fksdb->save($_POST['dkdatei']);

$dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von, titel, timestamp FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

$html = $_POST['html']; 
$html = str_replace(array("\r\n", "\n", "\r"), '', $html);

$abschnitte = explode('<br /><br />', $html);
if(!count($abschnitte))
    $abschnitte = array();
    
$box = array();
foreach($abschnitte as $a)
{
    $clear = strip_tags(str_replace('<br />', ' ', $a));
    
    if(!$clear)
        continue;
    
    $box[] = array(
        'count' => str_word_count($clear),
        'snippet' => Strings::cut($clear, 350),
        'list_allowed' => (Strings::strExists('<br />', $a)?true:false)
    );
}

echo '
<h1>'.$trans->__('Element aufteilen.').'</h1>';

if(count($box) < 2)
{
    echo '
    <div class="box">
        <h2 class="calibri">'.$trans->__('Aufteilung in mehrere Inhaltselemente war nicht möglich!').'</h2>
        <p>
            '.$trans->__('Bitte verwenden Sie einen doppelten Zeilenumbruch zwischen zwei Textbausteinen, um einen Textblock auf mehrere Inhaltselemente aufzuteilen.').'
        <br /><br />
        
        <strong>'. $trans->__('Beispiel:') .'</strong><br />
        <textarea disabled class="helper">'. $trans->__('Erster Textbaustein
        
Zweiter Textbaustein') .'</textarea>
        </p>
    </div>
            
    <div class="box_save" style="display:block;">
        <input type="submit" value="'.$trans->__('zurück').'" class="bs1" /> 
    </div>';
}
else
{
    echo '
    <div class="box">
    <form>';
    
        $box_count = 1;
    
        foreach($box as $a)
        {
            echo '
            <div class="abox" data-nr="'.$box_count.'">
                <div class="lb">
                    '.$a['snippet'].'
                    <span>
                        ('.$trans->__('%1 Worte', false, array($a['count'])).')
                    </span>
                </div>
                <div class="rb">
                    <select name="box[]">
                        <option value="text_1">'.$trans->__('TEXTBLOCK').' A</option>
                        <option value="text_2">'.$trans->__('TEXTBLOCK').' B</option>
                        <option value="h1_1">H1 - '.$trans->__('Überschrift').' A</option>
                        <option value="h2_1">H2 - '.$trans->__('Unterüberschrift').' A</option>
                        <option value="h3_1">H3 - '.$trans->__('Abschnittsüberschrift').' A</option>
                        <option value="h4_1">H4 - '.$trans->__('Zwischenüberschrift').' A</option>
                        <option value="zitat_1">'.$trans->__('ZITAT').' A</option>
                        '.($a['list_allowed']?'<option value="list_1">'.$trans->__('LISTE').' A</option>':'').'
                    </select>
                </div>
                
                '.(count($box) > $box_count?'
                <div class="hr">
                    <span></span>
                </div>
                ':'').'
            </div>';
            
            $box_count ++;
        }
         
    echo '
    </form>
    </div>
    
    <div class="box">
        <strong>'.$trans->__('Hinweis:').'</strong> 
        '.$trans->__('Textbausteine, die durch einen einfachen Zeilenumbruch getrennt sind, können gleichzeitig in mehrere Listenelemente aufgeteilt werden.').'
    </div>
       
    <div class="box_save" style="display:block;">
        <input type="submit" value="'.$trans->__('verwerfen').'" class="bs1" /> 
        <input type="submit" value="'.$trans->__('speichern').'" class="bs2" />
    </div>';
}
?>
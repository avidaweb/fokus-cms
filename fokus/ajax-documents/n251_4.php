<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251')
    exit($user->noRights());

$static = $classes;
    
require_once(ROOT.'inc/classes.view/class.fks.php');
require_once(ROOT.'inc/classes.view/class.content.php');
require_once(ROOT.'inc/classes.view/class.navigation.php');
require_once(ROOT.'inc/classes.blocks/_basic.php');

$fks = new Page($static, array(
    'title' => $doc->titel,
    'language' => $dve->language,
    'preview' => $doc->id,
    'dclass' => ($doc->klasse?$doc->id:0)
));
$api->setStatic('fks', $fks);
$static['fks'] = $fks;

$navigation = new Navigation($static);
$api->setStatic('navigation', $navigation);

$content = new Content($static);
$api->setStatic('content', $content);

$content->setGalerie(array('img_width' => 150, 'img_height' => 150));
$content->setForm(array('view' => 'flat'));

$p = array(
    'id' => 'vcontent',
    'document_class' => '',
    'document_width' => '570',
    'document_border_width' => array(1),
    'column_class' => 'vspalte',
    'column_padding' => array(8, 20, 8, 0),
    'column_padding_last' => array(8, 0, 8)
);
$inhalt = $content->get($p); 

$inhalt = preg_replace('&<script(.*)</script>&isU', '', $inhalt);

$inhalt = str_replace('>', '> ', $inhalt);
$nur_text = strip_tags($inhalt);
$html_only = $nur_text;
$nur_text = preg_replace('/[^0-9a-zA-ZäüöÄÜÖß ]/', '', $nur_text); 
$nur_text = trim(Strings::removeDoubleSpace($nur_text));

$old_words = explode(' ', $nur_text);
$words = array();

foreach($old_words as $word)
{
    if(strlen(trim($word)) > 1)
        $words[] = $word;
}

$wortzahl = count($words);
$letter_count = strlen($nur_text);

$counter = array(
    'h' => substr_count($inhalt, '</h1>') + substr_count($inhalt, '</h2>') + substr_count($inhalt, '</h3>') + substr_count($inhalt, '</h4>'),
    'p' => substr_count($inhalt, '</p>'),
    'img' => substr_count($inhalt, '<img '),
    'quote' => substr_count($inhalt, '</blockquote>'),
    'ul' => substr_count($inhalt, '</ul>') + substr_count($inhalt, '</ol>')
);

// filter words
$extract_words = array("die", "der", "und", "in", "zu", "den", "das", "nicht", "von", "sie", "ist", "des", "sich", "mit", "dem", "dass", "er", "es", "ein", "ich", "auf", "so", "eine", "auch", "als", "an", "nach", "wie", "im", "für", "man", "aber", "aus", "durch", "wenn", "nur", "war", "noch", "werden", "bei", "hat", "wir", "was", "wird", "sein", "einen", "welche", "sind", "oder", "zur", "um", "haben", "einer", "mir", "über", "uumlber", "ihm", "diese", "einem", "ihr", "uns", "da", "zum", "kann", "doch", "vor", "dieser", "mich", "ihn", "du", "hatte", "seine", "mehr", "am", "denn", "nun", "unter", "sehr", "selbst", "schon", "hier", "bis", "habe", "ihre", "dann", "ihnen", "seiner", "alle", "wieder", "meine", "gegen", "vom", "ganz", "wo", "muss", "ohne", "eines", "können", "sei", "amp", "für", "fuumlr");
//

$vorkommen = array();
$wdfs = array();
for($x=0; $x<$wortzahl; $x++)
{
    $dwort = $words[$x];

    if(!empty($dwort) && !ctype_digit($dwort) && !in_array(strtolower($dwort), $extract_words))
        $vorkommen[$dwort] += 1; 
} 

foreach($vorkommen as $word => $wcount)
{
    $wdf = round(log(($wcount + 1), 2) / log($wortzahl, 2), 2); 
    $wdfs[$word] = $wdf;
}

arsort($wdfs);

echo '
<div id="vorschau">
    <div class="vorschauL">
        <h2>Vorschau.</h2>
        <span class="xinfo">'. $trans->__('Dokument &quot;%1&quot;.', false, array($doc->titel)) .'</span>
        
        '.$inhalt.' 
    </div>
    <div class="vorschauR">
        <h2>Auswertung.</h2>
        <span class="xinfo">'. $trans->__('und Suchmaschinenoptimierung.') .'</span>
        
        <div class="cont">
            <table>
                <tr>
                    <td>'. $trans->__('Anzahl Wörter:') .'</td>
                    <td>'.$wortzahl.'</td>
                </tr>
                <tr>
                    <td>'. $trans->__('Anzahl Sätze:') .'</td>
                    <td>'.Strings::countSentences($html_only).'</td>
                </tr>
                <tr>
                    <td>'. $trans->__('Anzahl Zeichen:') .'</td>
                    <td>'.$letter_count.'</td>
                </tr>
                <tr>
                    <td colspan="2"><br /></td>
                </tr>
                <tr>
                    <td>'. $trans->__('Anzahl Überschriften:') .'</td>
                    <td>'.$counter['h'].'</td>
                </tr>
                <tr>
                    <td>'. $trans->__('Anzahl Text-Abschnitte:') .'</td>
                    <td>'.$counter['p'].'</td>
                </tr>
                <tr>
                    <td>'. $trans->__('Anzahl Listen:') .'</td>
                    <td>'.$counter['ul'].'</td>
                </tr>
                <tr>
                    <td>'. $trans->__('Anzahl Zitate:') .'</td>
                    <td>'.$counter['quote'].'</td>
                </tr>
                <tr>
                    <td>'. $trans->__('Anzahl Bilder:') .'</td>
                    <td>'.$counter['img'].'</td>
                </tr>
            </table>
        </div>
        
        <div class="cont">
            <strong>'. $trans->__('ZUSATZINFO:') .'</strong><br /><br />
            
            '. $trans->__('Folgend die <strong>10 wichtigsten Worte</strong> in<br />diesem Dokument:') .'<br /><br />
            <table>';
            $sf = array('auml', 'uuml', 'ouml', 'Auml', 'Uuml', 'Ouml', 'szlig');
            $ef = array('ä', 'ü', 'ö', 'Ä', 'Ü', 'Ö', 'ß');

            $countv = 0;
            foreach($wdfs as $word => $wdf)
            {
                $v2 = intval($vorkommen[$word]);
                
                $countv ++;
                $prozent = round($v2 / $wortzahl * 100, 1);
                $word = str_replace($sf, $ef, $word);
                
                echo '
                <tr>
                    <td><strong>'.$countv.'.</strong> '.Strings::cutWords($word, 18).'</td>
                    <td>('.$v2.' mal / '.$prozent.'% / '.$wdf.' WDF)</td>
                </tr>';
                
                if($countv == 10)
                    break;
            }
            echo '
            </table>
        </div>
    </div>
</div>';  
?>
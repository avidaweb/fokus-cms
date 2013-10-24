<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');

if(!$user->r('dok') || $index != 'n260')
    exit($user->noRights());
       
$id = $fksdb->save($_GET['id'], 1);
$block = $fksdb->save($_GET['block']);
$ibid = $fksdb->save($_GET['ibid']);
$blockindex = $fksdb->save($_GET['blockindex']);
$dkdatei = $fksdb->save($_GET['dkdatei']);
$realname = $fksdb->save($_GET['realname']);
$ext_block = $fksdb->save($_GET['ext_block']);

$dclass_block = array();
$row = new stdClass();

$dokument = $fksdb->fetch("SELECT id, klasse, produkt, dversion_edit, von, titel, timestamp FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
$dve = $fksdb->fetch("SELECT * FROM ".SQLPRE."document_versions WHERE id = '".$dokument->dversion_edit."' LIMIT 1");

if(!$dokument || !$dve)
    exit('no document');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $dokument->von == $user->getID())) {}
else exit($user->noRights());

if(!$dokument->klasse && !$dokument->produkt)
{
    $row = $fksdb->fetch("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$id."' AND id = '".$block."' LIMIT 1");
}
else
{
    $ki = $base->fixedUnserialize($dve->klasse_inhalt);
    $type = $fksdb->save($_GET['type']);
    
    if(!$ibid)
    {
        $row->type = $type;
        $row->id = $block;
        $row->html = $ki[$block]['html'];
        $row->bild = $ki[$block]['bild'];	
        $row->bildid = $ki[$block]['bildid'];	
        $row->bildw = $ki[$block]['bildw'];	
        $row->bildh = $ki[$block]['bildh'];	
        $row->bildwt = $ki[$block]['bildwt'];	
        $row->bildp = $ki[$block]['bildp'];	
        $row->bildt = $ki[$block]['bildt'];	
        $row->bild_extern = $ki[$block]['bild_extern'];	
        $row->teaser = $ki[$block]['teaser'];
        
        $row->extb = $ext_block;
        $row->extb_content = $ki[$block]['extb_content'];
        
        $dclass_content = $base->open_dklasse(ROOT.'content/dklassen/'.$dkdatei.'.php');
        $dclass_blocks = $base->getClassBlocks($dclass_content);
        $dclass_block = $dclass_blocks[$block];
    }
    else
    {
        $row->type = $type;
        $row->id = $block;
        $row->html = $ki[$ibid]['html'][$blockindex]['html'];
        $row->bild = $ki[$ibid]['html'][$blockindex]['bild'];	
        $row->bildid = $ki[$ibid]['html'][$blockindex]['bildid'];	
        $row->bildw = $ki[$ibid]['html'][$blockindex]['bildw'];	
        $row->bildh = $ki[$ibid]['html'][$blockindex]['bildh'];	
        $row->bildwt = $ki[$ibid]['html'][$blockindex]['bildwt'];	
        $row->bildp = $ki[$ibid]['html'][$blockindex]['bildp'];	
        $row->bildt = $ki[$ibid]['html'][$blockindex]['bildt'];	
        $row->bild_extern = $ki[$ibid]['html'][$blockindex]['bild_extern'];	
        $row->teaser = $ki[$ibid]['html'][$blockindex]['teaser'];
        $row->extb = $ki[$ibid]['html'][$blockindex]['extb'];
        $row->extb_content = $ki[$ibid]['html'][$blockindex]['extb_content'];
    }
}


$ex = null;
if($row->type == 100)
    $ex = $api->getBlock($row->extb);

echo '
<div class="box boxfirst">
    '.($ex?'<span>'. $trans->__('Erweiterung: ') .'<strong>'.$ex->getName().'</strong></span>':'
    <span>Element: &nbsp;&nbsp;&nbsp;&nbsp; <strong>'.$base->getBlockByID($row->type, 'de').'</strong></span>');
    
    if($ibid || !$dokument->klasse)
    {
        echo '
        <a class="more_opt">'. $trans->__('Optionen') .'</a>
    
        <div id="exto" class="shadow">
            <div class="extoL" id="extoL1">
                <h4>Elementtyp &auml;ndern</h4>
                <div class="exto_block">
                    <table>
                        <tr>
                            <td class="kopf">'. $trans->__('Text-Elemente') .'</td>
                        </tr>
                        <tr>
                            <td>';
                                for($x = 10; $x<20; $x++)
                                {
                                    if($base->getBlockByID($x, 'de'))
                                        echo '<a id="nb_'.$x.'"'.($row->type == $x?' class="aktiv"':'').'>'.$base->getBlockByID($x, 'de').'</a><br />';
                                }
                            echo '
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="exto_block">
                    <button>'. $trans->__('speichern') .'</button>
                    <button>'. $trans->__('abbrechen') .'</button>
                </div>
            </div>
            
            <div class="extoR">
                <a class="overandout first">'. $trans->__('Optionen schließen') .'</a>
                '.(!$ibid && $row->type < 20?'<a class="expand" id="e_opt_type">'. $trans->__('Elementtyp ändern') .'</a>':'').'
                '.($row->type == 15?'<a id="e_opt_aufteilen">'. $trans->__('Element aufteilen') .'</a>':'').'
                '.($cssf || $cssk?'<a id="e_opt_format">'. $trans->__('Formatierung & Anzeige') .'</a>':'').'
                '.(!$ibid && $suite->rm(4)?'<a id="e_opt_copy">'. $trans->__('In Zwischenablage kopieren') .'</a>':'').'
                <a id="e_opt_del">'. $trans->__('Element entfernen') .'</a>
            </div>
        </div>';
    }
echo '
</div>

<input type="hidden" id="block_id" value="'.$row->id.'" />
<input type="hidden" id="block_ibid" value="'.$ibid.'" />
<input type="hidden" id="blockindex" value="'.$blockindex.'" />
<input type="hidden" id="block_extb" value="'.$row->extb.'" />';


$include_blocks = array(
    1 => 'input',
    15 => 'image',
    30 => 'image',
    20 => 'list',
    22 => 'table',
    36 => 'video',
    40 => 'gallery',
    64 => 'teaser',
    66 => 'reference',
    67 => 'sitemap',
    52 => 'form',
    69 => 'comment',
    73 => 'login',
    44 => 'gmaps',
    47 => 'qr',
    1015 => 'linkpicker',
    1016 => 'urlpicker',
    1050 => 'relation',
    100 => 'extension'
);


// Normaler WYSIWYG-Editor
if($row->type > 7 && $row->type < 30 && $row->type != 20 && $row->type != 22)
{
    require_once('block-editor.php');
}

if(array_key_exists($row->type, $include_blocks))
{
    require_once('block-'.$include_blocks[$row->type].'.php');
}

echo '
<div class="box_save">
    <input type="button" value="'. $trans->__('verwerfen') .'" class="bs1" />
    <input type="button" value="'. $trans->__('speichern') .'" class="bs2" />
</div>';
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'kkanal') || $index != 'n631')
    exit($user->noRights());

if(!isset($rel))
    exit();

$ra = explode('_', $rel);
$vid = $ra[1];
$type = $ra[0];

$name = '';
$fa = array();

if($type == 'formular')
{
    $kk = $fksdb->fetch("SELECT html, vid FROM ".SQLPRE."blocks WHERE type = '52' AND vid = '".$vid."' ORDER BY id DESC LIMIT 1");
    $fo = $base->fixedUnserialize($kk->html);

    $name = ($fo['name']?$fo['name']:$trans->__('Unbenanntes Formular'));

    $fa = $fo['f'];
    if(!is_array($fa)) $fa = array();
}
elseif($type == 'suche')
{
    $name = $trans->__('Suche');
}
elseif($type == 'comments')
{
    $name = $trans->__('Kommentare');
}

echo '
<form action="inc_communication.php?index=n633" method="post">
<h1>'.Strings::cut($name, 90).'</h1>

<input type="hidden" name="type" value="'.$type.'" />
<input type="hidden" name="vid" value="'.$vid.'" />

<div class="box" id="form_fields">
    <div id="form_head">
        <div class="li">';
            if($type == 'formular')
            {
                echo '
                <a class="more_opt" rel="1">'.$trans->__('Mögliche Felder einblenden').'</a>
                <div class="opt">
                    <table>';
                        foreach($fa as $f_id => $f)
                        {
                            if($f['type'] == 'string')
                                continue;

                            echo '
                            <tr>
                                <td><input type="checkbox" id="dsel_'.$f_id.'" name="felder[]" value="'.$f_id.'" checked="checked" /></td>
                                <td><label for="dsel_'.$f_id.'">'.$f['name'].'</label></td>
                            </tr>';
                        }
                        echo '
                    </table>
                </div>';
            }
            if($type == 'comments')
            {
                echo '
                <div class="opt multiopt">
                    <strong>markierte Datensätze:</strong>
                    <a class="del">löschen</a> |
                    <a class="close">sperren</a> |
                    <a class="open">freigeben</a>
                </div>';
            }
        echo '
        </div>
        <div class="re">
            <label for="form_search">'.$trans->__('Kommunikationskanal durchsuchen:').'</label>
            <input type="text" id="form_search" name="search" />
        </div>
    </div>

    <table id="form_auflistung">
        <tr><td><img src="images/loading_white.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" /></td></tr>
    </table>

    '.($type == 'formular'?'
    <p class="export">
        <input type="submit" value="'.$trans->__('XML-Export').'" name="xml" />
        <input type="submit" value="'.$trans->__('CSV-Export').'" name="csv" />
    </p>
    ':'').'
</div>
</form>';
?>
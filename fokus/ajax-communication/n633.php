<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'kkanal') || $index != 'n633')
    exit($user->noRights());

$vid = $fksdb->save($_REQUEST['vid']);
$type = $fksdb->save($_REQUEST['type']);
$csv = $fksdb->save($_REQUEST['csv']);
$json = $fksdb->save($_REQUEST['json']);
$export = ($csv?'csv':($json?'json':'xml'));
$opt = $_REQUEST['felder'];
if(!is_array($opt))
    $opt = array();
$q = $fksdb->save($_REQUEST['search']);

if($type == 'formular')
{
    $kk = $fksdb->fetch("SELECT html, vid FROM ".SQLPRE."blocks WHERE type = '52' AND vid = '".$vid."' ORDER BY id DESC LIMIT 1");
    $fo = $base->fixedUnserialize($kk->html);

    $name = ($fo['name']?$fo['name']:$trans->__('Unbenanntes Formular'));

    $fa = $fo['f'];
    if(!is_array($fa)) $fa = array();

    if($export == 'xml')
        $result = '<?xml version="1.0" encoding="UTF-8"?>
<'.$base->slug($name).'>
';
    if($export == 'csv')
        $result = '';
    if($export == 'json')
        $result = '{
';

    $fQ = $fksdb->query("SELECT * FROM ".SQLPRE."records WHERE vid = '".$vid."' ORDER BY id DESC");
    while($fi = $fksdb->fetch($fQ))
    {
        $data = $base->db_to_array($fi->felder);
        $data_counter ++;

        $rtn = '';
        $search_result = false;

        if($export == 'xml')
            $rtn .= '    <data>
';
        if($export == 'json')
            $rtn .= '    [
';

        foreach($fa as $f_id => $f)
        {
            if($f['type'] == 'string' || !in_array($f_id, $opt))
                continue;

            $val = $data[$f_id]['value'];

            if($f['type'] == 'img')
            {
                $valA = $base->db_to_array($val);
                $val = ($valA['status'] == 'ok'?$valA['url']:'');
            }

            if($export == 'csv')
            {
                $val = utf8_decode($val);
                $val = nl2br($val);
                $val = html_entity_decode($val, ENT_COMPAT, 'UTF-8');
                $val = strip_tags($val);
                $val = preg_replace("(\r\n|\n|\r)", " ", $val);
                $val = str_replace('"', '', $val);
                $val = str_replace(';', ',', $val);
                $val = preg_replace ('#\s+#' , ' ' , $val);
                $val = '"'.trim($val).'"';
            }
            elseif($export == 'xml')
            {
                $val = html_entity_decode($val, ENT_COMPAT, 'UTF-8');
                $val = strip_tags($val);
                $val = str_replace('<', '', $val);
                $val = str_replace('>', '', $val);
                $val = htmlentities($val, ENT_COMPAT, 'UTF-8');
                $val = trim($val);
            }
            elseif($export == 'json')
            {
                $val = utf8_decode($val);
                $val = nl2br($val);
                $val = html_entity_decode($val, ENT_COMPAT, 'UTF-8');
                $val = strip_tags($val);
                $val = preg_replace("(\r\n|\n|\r)", " ", $val);
                $val = str_replace('"', '', $val);
                $val = preg_replace ('#\s+#' , ' ' , $val);
                $val = trim($val);
                if(!is_numeric($val))
                    $val = '"'.$val.'"';
            }

            if($q && $val && (Strings::strExists($q, $val, false) || Strings::strExists($val, $q, false)))
                $search_result = true;

            if($export == 'xml')
                $rtn .= '        <'.$base->slug($f['name']).'>'.$val.'</'.$base->slug($f['name']).'>
';
            if($export == 'csv')
                $rtn .= $val.';';
        }
        $rtn .= ($export == 'xml'?'    </data>':'').'
';

        if(!$q || $search_result)
        {
            $result .= $rtn;
        }
    }

    $result .= ($export == 'xml'?'</'.$base->slug($name).'>':'');
}

if(!$result)
    exit();

$datei = "../content/export/export.txt";
$handle = fopen($datei, "w");
fwrite($handle, $result);

$mime = ($export == 'csv'?'text/comma-separated-values':'text/xml');
$len = filesize($datei);

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");

header('Content-type: '.$mime);
header('Content-Disposition: attachment; filename="'.$base->slug($name).'.'.$export.'"');
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".$len);
@readfile($datei);

exit();
?>
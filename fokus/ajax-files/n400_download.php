<?php
if($index != 'n400_download' || !$user->r('dat'))
    exit($user->noRights());
    
ignore_user_abort(true);
set_time_limit(0);
ini_set('memory_limit', '1024M');

$id = $fksdb->save($_GET['id']);
$ids = Strings::explodeCheck('|', $_GET['ids']);
$dir = $fksdb->save($_GET['dir']);

if($dir)
{
    $ids = array();
    $dirs = $fksdb->query("SELECT id FROM ".SQLPRE."files WHERE dir = '".$dir."' AND papierkorb = '0' AND isdir = '0'");
    while($mdir = $fksdb->fetch($dirs))
        $ids[] = $mdir->id;
}

function get_p_mime($datei, $d, $stack)
{
    $mime = '';
    if(function_exists('finfo_open'))
    {
        $finfo = @finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
        $mime = @finfo_file($finfo, $datei);
        @finfo_close($finfo);
    }
    elseif(function_exists('mime_content_type'))
    {
        $mime = @mime_content_type($d->file.'.'.$d->type);
    }
    
    if(!$mime && $stack->kat == 0)
    {
        if($d->type == 'gif')
            $mime = 'image/gif';
        elseif($d->type == 'png')
            $mime = 'image/png';
        else
            $mime = 'image/jpg';
    }
    return $mime;
}

if($id)
{
    $d = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE id = '".$id."' LIMIT 1");
    $stack = $fksdb->fetch("SELECT kat, titel FROM ".SQLPRE."files WHERE id = '".$d->stack."' LIMIT 1");
    
    $datei = '../content/uploads/'.$base->getFilePath($stack->kat).'/'.$d->file.'.'.$d->type;

    $len = filesize($datei);
    $mime = get_p_mime($datei, $d, $stack);
    
    $filename = $base->slug($stack->titel).'.'.$d->type; 
}
elseif(count($ids))
{
    $datei = '../content/export/download.zip';
    $mime = 'application/zip';
    $real_files = 0;
    
    $handl = fopen($datei, 'w');
    fclose($handl);
    
    $zip = new ZipArchive;
    $res = $zip->open($datei, ZipArchive::CREATE); 
    foreach($ids as $did)
    {
        $stack = $fksdb->fetch("SELECT kat, titel, id FROM ".SQLPRE."files WHERE id = '".$did."' LIMIT 1");
        $d = $fksdb->fetch("SELECT file, type FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");
        $myfile = '../content/uploads/'.$base->getFilePath($stack->kat).'/'.$d->file.'.'.$d->type;
        
        if(file_exists($myfile))
        {
            $real_files ++;
            $zip->addFile($myfile, $base->slug($stack->titel).'.'.$d->type);
        }
    }
    $zip->close();
    
    $len = filesize($datei);
    $filename = 'bilder-'.$real_files.'.zip';
}
    
header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");		

if($mime)
    header('Content-type: '.$mime);
header('Content-Disposition: attachment; filename="'.$filename.'"');
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".$len);
if(readfile($datei) === false)
    exit('error: download incomplete');
exit();	
?>
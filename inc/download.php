<?php
define('IS_BACKEND', true, true);
require('header.php');

$id = $fksdb->save($_GET['id']);

$stack = $fksdb->fetch("SELECT id, downloads, kat, titel, roles FROM ".SQLPRE."files WHERE id = '".$id."' LIMIT 1");
$file = $fksdb->fetch("SELECT id, downloads, file, type FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC, id DESC LIMIT 1");

if($stack && $file)
{
    $stack_downloads = $stack->downloads + 1;
    $file_downloads = $file->downloads + 1;

    $upd = $fksdb->query("UPDATE ".SQLPRE."files SET downloads = '".$stack_downloads."' WHERE id = '".$stack->id."' LIMIT 1");
    $upd = $fksdb->query("UPDATE ".SQLPRE."file_versions SET downloads = '".$file_downloads."' WHERE id = '".$file->id."' LIMIT 1");
}

$datei = '../content/uploads/'.$base->getFilePath($stack->kat).'/'.$file->file.'.'.$file->type;

if(!$stack || !$file || !file_exists($datei))
{
    header(($_ENV['SERVER_PROTOCOL']?$_ENV['SERVER_PROTOCOL']:$_SERVER['SERVER_PROTOCOL'])." 404 Not Found", true, 404);
    $base->go(DOMAIN.'/error/404/');
}

$roles = $base->db_to_array($stack->roles);
if(count($roles) && !$user->isSuperAdmin())
{
    $is_in_role = false;
    $a_roles = $user->getAvailableRoles();
    foreach($a_roles as $gr)
    {
        if(in_array($gr, $roles))
            $is_in_role = true;
    }
    
    if(!$user->isLogged() && in_array('-1', $roles))
        $is_in_role = true;
    
    if(!$is_in_role)
    {
        header(($_ENV['SERVER_PROTOCOL']?$_ENV['SERVER_PROTOCOL']:$_SERVER['SERVER_PROTOCOL'])." 403 Forbidden", true, 403);
        $base->go(DOMAIN.'/error/403/');
    }
}


$len = filesize($datei);
if(function_exists('finfo_open'))
{
    $finfo = @finfo_open(FILEINFO_MIME_TYPE); // return mime type ala mimetype extension
    $mime = @finfo_file($finfo, dirname(__FILE__)."/".$datei);
    @finfo_close($finfo);
}
elseif(function_exists('mime_content_type'))
{
    $mime = @mime_content_type($file->file.'.'.$file->type);
}

header("Pragma: public");
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: public");
header("Content-Description: File Transfer");		

header('Content-type: '.$mime);
header('Content-Disposition: attachment; filename="'.$base->slug($stack->titel).'.'.$file->type.'"');
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".$len);
@readfile($datei);
exit();	
?>
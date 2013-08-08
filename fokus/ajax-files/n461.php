<?php
if($index == 'n461' && $suite->rm(1) && $user->r('dat', 'edit')) 
{
    $stackid = $fksdb->save($_REQUEST['stackid']);
    $fileid = $fksdb->save($_REQUEST['fileid']);
    
    $w = $fksdb->save($_REQUEST['w']);
    $h = $fksdb->save($_REQUEST['h']);
    $x = $fksdb->save($_REQUEST['x']);
    $y = $fksdb->save($_REQUEST['y']);
    $s = $fksdb->save($_REQUEST['s']);
    $c = $fksdb->save($_REQUEST['c']);
    $b = $fksdb->save($_REQUEST['b']);
    $cropped = $fksdb->save($_REQUEST['cropped'], 1);
    $titel = $fksdb->save($_REQUEST['titel']);
    $desc = $fksdb->save($_REQUEST['desc']);
    
    parse_str($_POST['cf_form'], $cf_form);
    if(!is_array($cf_form)) exit();
    
    $cfa = array();
	if(count($cf_form['cf']))
	{
		foreach($cf_form['cf'] as $cfk => $cfv)
			$cfa[$fksdb->save($cfk)] = $fksdb->save($cfv);
	}
    $cf_save = $base->array_to_db($cfa);
    
    $stack = $fksdb->fetch("SELECT id FROM ".SQLPRE."files WHERE id = '".$stackid."' LIMIT 1");
    $file = $fksdb->fetch("SELECT id, width, height, type, file FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ".($fileid?"AND id = '".$fileid."'":"ORDER BY timestamp DESC")." LIMIT 1");
    
    if(!$stack || !$file)
        exit();
    
    if(!$w)
        $w = $file->width;
    if(!$h)
        $h = $file->height;
    
    $id = Strings::createID();
    $kat = 0;
    $typ = $file->type;
    
    $uploaddir = '../content/uploads/'.$base->getFilePath($kat).'/';
    $datei = $id.'.'.$typ;
    $alte_datei = $uploaddir.$file->file.'.'.$file->type;
    
    $image_p = imagecreatetruecolor($w, $h);
    imagealphablending($image_p, false);
    imagesavealpha($image_p, true);
        
	if($typ == 'png') $image = imagecreatefrompng($alte_datei);
    else if($typ == 'gif') $image = imagecreatefromgif($alte_datei);
    else $image = imagecreatefromjpeg($alte_datei);
    
    if($s == 1)
    {
        // Grayscale
        imagefilter($image, IMG_FILTER_GRAYSCALE);
    }
    
    imagecopy($image_p, $image, 0, 0, $x, $y, $w, $h);
        
    imagedestroy($image);
    
    $bild_t = $uploaddir.$datei;	
	if($typ == 'png') imagepng($image_p, $bild_t, 0); 
    else if($typ == 'gif') imagegif($image_p, $bild_t); 
    else imagejpeg($image_p, $bild_t, 100);
    imagedestroy($image_p);	
    
    
    $fksdb->update("files", array(
        "last_type" => $typ,
        "last_timestamp" => time(), 
        "last_ausrichtung" => ($w / $h), 
        "last_autor" => $user->getID(), 
        "titel" => $titel, 
        "beschr" => $desc,
        "cf" => $cf_save,
        "cropped" => $cropped
    ), array(
        "id" => $stack->id
    ), 1);
        
    $fksdb->insert("file_versions", array(
    	"stack" => $stack->id,
    	"file" => $id,
    	"type" => $typ,
    	"timestamp" => $base->getTime(),
    	"ausrichtung" => ($w / $h),
    	"grafik" => 0,
    	"width" => $w,
    	"height" => $h,
    	"autor" => $user->getID()
    ));
}
?>
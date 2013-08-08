<?php
ignore_user_abort(true);
set_time_limit(0);

if($index = 'n400_upload' && ($user->r('dat', 'new') || $user->r('dat', 'edit') || $user->r('dat', 'ver'))) 
{
    $base->setContentType('application/json');
        
    $status = array(
        'status' => 'error'
    );
    
    if($a == 'upload_1')
    {
        $addto = $fksdb->save($_REQUEST['addto']);
        $uploaddir = ROOT.'content/uploads/bilder/';
        
        $loopfile = $_FILES['files'];
        
        if(!count($loopfile))
            exit(json_encode($status));
            
        $stati = array(); 
            
        $id = Strings::createID();
        $typ = $base->filetype($loopfile['name'][0]); 
        $real_name = $loopfile['name'][0];
        
        if(Strings::strExists('php', $typ))
            exit();
        
        $stack = $fksdb->save($_REQUEST['stack']);
        $ordner = $fksdb->save($_REQUEST['ordner']);
        $datei = $id.'.'.$typ;

        $name = '';
        $nameA = explode('.', $loopfile['name'][0]);
        for($n = 0; $n < count($nameA) - 1; $n++)
            $name .= $nameA[$n].' ';
        $name = $base->slug($name);
        
        if(move_uploaded_file($loopfile['tmp_name'][0], $uploaddir.$datei)) 
        {    
        	list($OriginalBreite, $OriginalHoehe) = getimagesize($uploaddir.$datei);	
            
            if(!$stack)
            {
                $fksdb->insert("files", array(
                	"kat" => 0,
                	"dir" => $ordner,
                	"titel" => $name,
                	"last_type" => $typ,
                	"timestamp" => $base->getTime(),
                	"last_timestamp" => $base->getTime(),
                	"last_ausrichtung" => ($OriginalBreite / $OriginalHoehe),
                	"last_grafik" => 0,
                	"last_autor" => $user->getID()
                ));
                $stack_id = $fksdb->getInsertedID();
            }
            else
            {
                $stack_id = $stack;
                $updt = $fksdb->query("UPDATE ".SQLPRE."files SET last_type = '".$typ."', last_timestamp = '".$base->getTime()."', last_ausrichtung = '".($OriginalBreite / $OriginalHoehe)."', last_grafik = '0', last_autor = '".$user->getID()."' WHERE id = '".$stack_id."' LIMIT 1");
            }
            
            $fksdb->insert("file_versions", array(
            	"stack" => $stack_id,
            	"file" => $id,
            	"type" => $typ,
            	"timestamp" => $base->getTime(),
            	"ausrichtung" => ($OriginalBreite / $OriginalHoehe),
            	"grafik" => 0,
            	"width" => $OriginalBreite,
            	"height" => $OriginalHoehe,
            	"autor" => $user->getID() 
            ));
            $file_id = $fksdb->getInsertedID();
            
            $stati[] = array(
                'status' => 'finished',
                'id' => $stack_id,
                'file_id' => $file_id,
                'name' => $name,
                'original_name' => $real_name,
                'show_name' => '<span>'.$name.'</span><em>.'.$typ.'</em>',
                'file' => $uploaddir.$datei,
                'width' => $OriginalBreite,
                'height' => $OriginalHoehe,
                'url' => DOMAIN.'/img/'.$stack_id.'-0-0-'.$base->slug($name).'.'.$typ,
                'thumbnail_url' => DOMAIN.'/img/'.$stack_id.'-80-60-'.$base->slug($name).'.'.$typ,
                'thumbnail_url_160' => DOMAIN.'/img/'.$stack_id.'-160-0-'.$base->slug($name).'.'.$typ,
                'thumbnail_url_200' => DOMAIN.'/img/'.$stack_id.'-200-0-'.$base->slug($name).'.'.$typ,
                'thumbnail_url_h100' => DOMAIN.'/img/'.$stack_id.'-0-100-'.$base->slug($name).'.'.$typ
            );
        }
        else
        {
            $stati[] = array(
                'status' => 'broken',
                'name' => $loopfile['name'][0],
                'should_go_to' => $uploaddir.$datei
            );
        }
        
        $status = array(
            'status' => 'ok',
            'files' => $stati
        );                                
        
        exit(json_encode($status));
    }
    elseif($a == 'upload_2')
    {
        $loopfile = $_FILES['files'];
        
        if(!count($loopfile))
            exit(json_encode($status));
            
        $id = Strings::createID();
        $typ = $base->filetype($loopfile['name'][0]); 
        $real_name = $loopfile['name'][0];
        
        if(Strings::strExists('php', $typ))
            exit(json_encode($status));
        
        $uploaddir = ROOT.'content/uploads/dokumente/';
        
        $stack = $fksdb->save($_REQUEST['stack']);
        $ordner = $fksdb->save($_REQUEST['ordner']);
        $datei = $id.'.'.$typ;
        
        $nameA = explode('.', $loopfile['name'][0]);
        for($n = 0; $n < count($nameA) - 1; $n++)
            $name .= $nameA[$n].' ';
        $name = $base->slug($name);
        
        if(move_uploaded_file($loopfile['tmp_name'][0], $uploaddir.$datei)) 
        {   
            if(!$stack)
            {         
                $fksdb->insert("files", array(
                	"kat" => 2,
                	"dir" => $ordner,
                	"titel" => $name,
                	"last_type" => $typ,
                	"timestamp" => $base->getTime(),
                	"last_timestamp" => $base->getTime(),
                	"last_autor" => $user->getID()
                ));
                $stack_id = $fksdb->getInsertedID();
            }
            else
            {
                $stack_id = $stack;
                $updt = $fksdb->query("UPDATE ".SQLPRE."files SET last_type = '".$typ."', last_timestamp = '".$base->getTime()."', last_autor = '".$user->getID()."' WHERE id = '".$stack_id."' LIMIT 1");
            }
            
            $fksdb->insert("file_versions", array(
            	"stack" => $stack_id,
            	"file" => $id,
            	"type" => $typ,
            	"timestamp" => $base->getTime(),
            	"autor" => $user->getID() 
            ));
            $file_id = $fksdb->getInsertedID();
            
            $stati[] = array(
                'status' => 'finished',
                'id' => $stack_id,
                'file_id' => $file_id,
                'name' => $name,
                'original_name' => $real_name,
                'show_name' => $name.'<em>.'.$typ.'</em>',
                'file' => $uploaddir.$datei,
                'url' => DOMAIN.'/img/'.$stack_id.'/'.$base->slug($name).'.'.$typ,
                'thumbnail_url' => BACKEND_DIR.'images/icons/64'.$base->getFileTypeThumbnail($typ).'.jpg'
            );
        }
        else
        {
            $stati[] = array(
                'status' => 'broken',
                'name' => $loopfile['name'][0],
                'should_go_to' => $uploaddir.$datei
            );
        }
        
        $status = array(
            'status' => 'ok',
            'files' => $stati
        );                                
        
        exit(json_encode($status));
    }
}
?>
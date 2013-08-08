<?php
if($user->r('per', 'firma') && $index == 'n580')
{
    $edit = $fksdb->save($_POST['edit']);
    $w = $_POST['v'];  
    parse_str($w, $v); 
    
    if(!$edit)
    {
        $fksdb->insert("companies", array(
        	"name" => $v['name'],
        	"str" => $v['str'],
        	"hn" => $v['hn'],
        	"plz" => $v['plz'],
        	"ort" => $v['ort'],
        	"land" => $v['land'],
        	"telA" => $v['telA'],
        	"telB" => $v['telB'],
        	"telC" => $v['telC'],
        	"fax" => $v['fax'],
        	"email" => $v['email'],
        	"branche" => $v['branche'],
        	"status" => $v['status'],
        	"tags" => $v['tags']
        ));   
    }
    else
    {
        $update = $fksdb->query("UPDATE ".SQLPRE."companies SET name = '".$v['name']."', str = '".$v['str']."', hn = '".$v['hn']."', plz = '".$v['plz']."', ort = '".$v['ort']."', land = '".$v['land']."', telA = '".$v['telA']."', telB = '".$v['telB']."', telC = '".$v['telC']."', fax = '".$v['fax']."', email = '".$v['email']."', branche = '".$v['branche']."', status = '".$v['status']."', tags = '".$v['tags']."' WHERE id = '".$v['id']."' LIMIT 1");    
    }
} 
?>
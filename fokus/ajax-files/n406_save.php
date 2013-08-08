<?php
if($index == 'n406_save' && $user->r('dat'))
{
    parse_str($_POST['f'], $f);
    
    $roles = $f['roles'];
    if(!is_array($roles))
        exit();
        
    foreach($roles as $sid => $val)
    {
        if(!is_array($val))
            continue; 
        
        $fksdb->update("files", array(
            "roles" => $base->array_to_db($val)
        ), "id = '".$sid."'", 1);       
    }
}
?>
<?php
if($user->r('kom') && $suite->rm(5) && $index == 'n622') // Newsletter senden - Email-Anzahl Check
{
    $count = 0;
    $empfT = explode(',', $_POST['empf']);
    foreach($empfT as $e)
    {
        $e = trim($e);
        if($base->is_valid_email($e))
            $count ++;
    }
    echo $count;
}
?>
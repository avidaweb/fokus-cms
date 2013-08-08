<?php
if($user->r('per', 'prolle') && $index == 'n534A')
{
    $benutzer = $fksdb->save($_POST['benutzer']);
    $rolle = $fksdb->save($_POST['rolle']);
    
    if($rolle != 1 || $user->getRole() == 1)
    {
        $fksdb->insert("user_roles", array(
        	"benutzer" => $benutzer,
        	"rolle" => $rolle
        ));
    }
}
?>
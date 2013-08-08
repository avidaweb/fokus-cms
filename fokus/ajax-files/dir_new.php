<?php
if($index == 'dir_new' && $user->r('dat', 'dir'))
{
    $nr = $fksdb->save($_REQUEST['nr']);
    $ordner = $fksdb->save($_REQUEST['ordner']);
    $titel = $trans->__('Neuer Ordner').($nr?' ('.($nr + 1).')':'');
    
    $fksdb->insert("files", array(
    	"kat" => $kat,
    	"dir" => $ordner,
    	"isdir" => 1,
    	"titel" => $titel,
    	"timestamp" => $base->getTime(),
    	"last_timestamp" => $base->getTime(),
    	"last_autor" => $user->getID()
    ));
    
    exit($fksdb->getInsertedID());
}
?>
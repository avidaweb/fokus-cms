<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');

$index = $fksdb->save($_REQUEST['index']);
$rel = $fksdb->save($_REQUEST['rel']);
    
$ftypen = array('text' => 'Textfeld', 'textarea' => 'Textbox', 'checkbox' => 'Auswahlbox', 'radio' => 'Alternativ-Auswahl', 'select' => 'Auswahlfeld', 'password' => 'Passwort', 'img' => 'Bild-Upload', 'string' => 'Beschriftung');


$cssk = ($rechte['dok']['css'] && !$rechte['dok']['cssk']?false:true);
$cssf = ($rechte['dok']['css'] && !$rechte['dok']['cssf']?false:true);

$load_ajax = 'ajax-documents/'.$index.'.php';
if(!file_exists($load_ajax))
    exit('no_file');
require($load_ajax);
?>
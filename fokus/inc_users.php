<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');

$index = $fksdb->save($_REQUEST['index']);
$rel = $fksdb->save($_REQUEST['rel']);

$optionen = array(1 => "Vorname", 2 => "Nachname", 3 => "Straße", 4 => "PLZ", 5 => "Ort",  6 => "Land",  7 => "Firma",  8 => "Position",  9 => "Telefon geschäftlich",  10 => "Telefon privat",  11 => "Telefon mobil",  12 => "Fax",  13 => "E-Mail-Adresse",  14 => "Stichworte",  15 => "Online");
$optionenT = array(1 => "vorname", 2 => "nachname", 3 => "str", 4 => "plz", 5 => "ort",  6 => "land",  7 => "firma",  8 => "position",  9 => "tel_g",  10 => "tel_p",  11 => "mobil",  12 => "fax",  13 => "email",  14 => "tags",  15 => "online");
$pre_checked = array(1, 2, 3, 4, 5, 15);

$optionenF = array(1 => "Name", 2 => "Straße", 3 => "PLZ", 4 => "Ort",  5 => "Land",  6 => "Telefon",  7 => "Fax",  8 => "E-Mail-Adresse",  9 => "Branche",  10 => "Status",  11 => "Stichworte");
$optionenTF = array(1 => "name", 2 => "str", 3 => "plz", 4 => "ort",  5 => "land",  6 => "tel",  7 => "fax",  8 => "email",  9 => "branche",  10 => "status",  11 => "tags");
$pre_checkedF = array(1, 3, 4, 6, 9);

$tmitarbeiter = (!$rechte['per']['type'] || $rechte['per']['mitarbeiter']?true:false);
$tkunde = (!$rechte['per']['type'] || $rechte['per']['kunden']?true:false);


$load_ajax = 'ajax-users/'.$index.'.php';
if(!file_exists($load_ajax))
    exit('no_file');
require($load_ajax);
?>
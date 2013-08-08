<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n260_gmaps_pos_get')
    exit($user->noRights());

$addr = urlencode($fksdb->save($_POST['address']));

$rtn = file_get_contents('http://maps.google.com/maps/api/geocode/json?address='.$addr.'&language=de&sensor=false');
if(!$rtn) exit('error');
$js = json_decode($rtn, true);
if(!$js['status'] == 'OK') exit();

$loc = $js['results'][0]['geometry']['location'];
if(!is_array($loc)) exit();

exit($loc['lat'].'||'.$loc['lng']);
?>
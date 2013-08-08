<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n260_split_check')
    exit($user->noRights());

$html = str_replace(array("\r\n", "\n", "\r"), '', $_POST['html']);

$paras = explode('<br /><br />', $html);

if(count($paras) > 3)
    exit('true');
exit('false');
?>
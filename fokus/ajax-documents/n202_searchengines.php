<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok', 'publ') || $index != 'n202_searchengines')
    exit($user->noRights());

$v = explode('_', $fksdb->save($_GET['v']));
$id = intval($v[0]);
$vid2 = intval($v[1]);

$pinged = $fksdb->save($_COOKIE['sitemap_ping'], 1);

if($pinged >= time())
    exit();

$dv = $fksdb->fetch("SELECT language FROM ".SQLPRE."document_versions WHERE id = '".$vid2."' AND dokument = '".$id."' LIMIT 1");

$ping_url = urlencode($domain.'/'.($dv->language != $standard_language?$dv->language.'/':'').'sitemap.xml');
$google = (@file_get_contents('http://www.google.com/webmasters/sitemaps/ping?sitemap='.$ping_url) === false?false:true);
$bing = (@file_get_contents('http://www.bing.com/webmaster/ping.aspx?siteMap='.$ping_url) === false?false:true);

setcookie('sitemap_ping', (time() + 3600), (time() + 3600), '/');
?>
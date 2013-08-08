<?php
define('IS_FEED', true);

require('header.php');

$v = $base->vars('GET');

$feed = $fksdb->fetch("SELECT id, dokument, block FROM ".SQLPRE."feeds WHERE id = '".intval($v['id'])."' LIMIT 1");
if(!$feed) exit('Dieser Feed existiert nicht / feed don\'t exist #1');

$blocks = $fksdb->query("SELECT id, teaser, dversion FROM ".SQLPRE."blocks WHERE vid = '".$feed->block."' AND dokument = '".$feed->dokument."'"); 
while($block = $fksdb->fetch($blocks))
{ 
    $dv = $fksdb->fetch("SELECT id, language FROM ".SQLPRE."document_versions WHERE dokument = '".$feed->dokument."' AND id = '".$block->dversion."' AND aktiv = '1' LIMIT 1");
    if($dv)
        break;
}
if(!$block || !$dv) exit('Dieser Feed existiert nicht / feed don\'t exist #2');


require_once('classes.view/class.fks.php');
$fks = new Page(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans,
    'user' => $user,
    'api' => $api
), array(
    'id' => 0,
    'language' => $dv->language,
    'paging' => 0,
    'feed' => true
));

require_once('classes.view/class.content.php');
$content = new Content(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans,
    'user' => $user,
    'api' => $api,
    'fks' => $fks
));

include_once('classes.blocks/_basic.php');
include_once('classes.blocks/64_teaser.php');


$teaser = $base->fixedUnserialize($block->teaser);
if(!$teaser['rss']) exit('Dieser Feed existiert nicht / feed don\'t exist #3');

$teaser['auflistung'] = 0;
$teaser['data'] = true;

$bl = new stdClass();
$bl->teaser = serialize($teaser);
    
$block = new Block_64(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans,
    'user' => $user,
    'api' => $api,
    'fks' => $fks,
    'content' => $content
), array(
    'block' => $bl
));

$output = $block->get();

$rtn = '';
$smallest_time = time();
 
if(count($output['items']))
{
    foreach($output['items'] as $i)
    {
        $d = $api->getDocument($i['document_id']);
        if(!$d)
            continue;
            
        $author = $api->getUsers(array($d['author']));
        
        if($smallest_time > $d['created'])
            $smallest_time = $d['created'];
            
        $rtn .= '
        <item>
            <title>'.htmlspecialchars(($d['meta']['title']?$d['meta']['title']:$d['title'])).'</title>
            <description>'.htmlspecialchars($d['meta']['metadescription']).'</description>
            <link>'.$i['url'].'</link>
            <author>'.htmlspecialchars($author[0]['email'].' ('.$author[0]['first_name'].' '.$author[0]['last_name'].')').'</author>
            <guid>'.$i['url'].'</guid>
            <pubDate>'.date('r', $d['created']).'</pubDate>
        </item>';    
    }
}


echo '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0">
 
    <channel>
        <title>'.htmlspecialchars($teaser['rss_titel']).'</title>
        <link>'.DOMAIN.'</link>
        <description>'.htmlspecialchars($teaser['rss_desc']).'</description>
        <language>'.$dv->language.'</language>
        <copyright>'.htmlspecialchars($teaser['rss_autor']).'</copyright>
        <pubDate>'.date('r', $smallest_time).'</pubDate>';
        
        echo $rtn;
        
    echo '
    </channel>
</rss>';

?>
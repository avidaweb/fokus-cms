<?php
define('IS_SITEMAP', true, true);

require('header.php');

if($_GET['lan'])
    $lan = $fksdb->save($_GET['lan']);
else
    $lan = $trans->getStandardLanguage();
     
$firstele = $fksdb->fetch("SELECT id FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' AND element = '0' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".$base->getTime()."' AND (bis = '0' OR bis >= '".$base->getTime()."'))", "sort ASC LIMIT 1");

function levelSitemap($output, $parents, $depth, $lan, $firstele, $fksdb, $base, $trans)
{           
    $elQ = $fksdb->query("SELECT id, titel, klasse, url, sprachen, rollen, element FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' AND nositemap = '0' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".$base->getTime()."' AND (bis = '0' OR bis >= '".$base->getTime()."')) AND (klasse != '' OR sprachen LIKE '%\"".$lan."\"%') AND element = '".$parents."' ORDER BY sort ASC");  
        
    while($el = $fksdb->fetch($elQ))
    {
        if(!$el->klasse)
        {
            $sp = $base->fixedUnserialize($el->sprachen);
            $url = ($el->url?$el->url:DOMAIN.'/'.($lan != $trans->getStandardLanguage()?$lan.'/':'').$el->id.'/'.$base->auto_slug($sp[$lan]).'/');
                
            if($firstele->id == $el->id)
                $url = DOMAIN.'/';
            
            $edit = 0;
            $pics = '';
            $has_teaser = false;
            
            $sdQ = $fksdb->query("SELECT dokument FROM ".SQLPRE."document_relations WHERE element = '".$el->id."'"); 
            while($sd = $fksdb->fetch($sdQ))
            {
                $dQ = $fksdb->query("SELECT timestamp_freigegeben FROM ".SQLPRE."documents WHERE id = '".$sd->dokument."' AND papierkorb = '0' LIMIT 1");
                while($d = $fksdb->fetch($dQ))
                {
                    if($d->timestamp_freigegeben > $edit)
                        $edit = $d->timestamp_freigegeben;
                        
                    $manyteaser = $fksdb->query("SELECT id FROM ".SQLPRE."blocks WHERE dokument = '".$sd->dokument."' AND type = '64' GROUP BY vid ORDER BY dversion DESC LIMIT 1");
                    if($manyteaser)
                        $has_teaser = true;
                                                
                    $bQ = $fksdb->query("SELECT bild, bildid FROM ".SQLPRE."blocks WHERE dokument = '".$sd->dokument."' AND type = '30' GROUP BY vid ORDER BY dversion DESC");
                    while($b = $fksdb->fetch($bQ))
                    {
                        if($b->bild)
                        {
                            $stack = $fksdb->fetch("SELECT titel, beschr FROM ".SQLPRE."files WHERE id = '".$b->bildid."' AND papierkorb = '0' LIMIT 1");
                            if($stack)
                            {
                                $pic = $fksdb->fetch("SELECT file, type FROM ".SQLPRE."file_versions WHERE stack = '".$b->bildid."' ORDER BY id DESC LIMIT 1");
                                
                                $titel = explode('.', $stack->titel);
                                $ende = $base->slug($titel[0]).'.'.$pic->type;
                                
                                $pics .= '
                                <image:image>
                                    <image:loc>'.DOMAIN.'/img/'.$b->bildid.'-0-0-'.$ende.'</image:loc>
                                    '.($stack->titel?'<image:title>'.$stack->titel.'</image:title>':'').'
                                    '.($stack->beschr?'<image:caption>'.$stack->beschr.'</image:caption>':'').'
                                </image:image>'; 
                            }
                        }
                    }
                }
            }
            
            $output .= '
            <url>
                <loc>'.$url.'</loc>
                '.($edit?'<lastmod>'.date(DATE_W3C, $edit).'</lastmod>':'').'
                <changefreq>'.($has_teaser?'daily':'weekly').'</changefreq>
                <priority>'.($depth < 9?round(1 - ($depth / 10), 1):'0.1').'</priority>
                '.$pics.'
            </url>';
        }
        else
        { 
            $dQ = $fksdb->query("SELECT id, timestamp_freigegeben, sprachenfelder FROM ".SQLPRE."documents WHERE klasse = '".$el->klasse."' AND papierkorb = '0' AND timestamp_freigegeben != ''");
            while($d = $fksdb->fetch($dQ))
            {
                $dspr = $base->fixedUnserialize($d->sprachenfelder);
                $url = DOMAIN.'/'.($lan != $trans->getStandardLanguage()?$lan.'/':'').$el->element.'/'.$d->id.'/'.$base->auto_slug($dspr[$lan]).'/';
            
                $output .= '
                <url>
                    <loc>'.$url.'</loc>
                    <lastmod>'.date(DATE_W3C, $d->timestamp_freigegeben).'</lastmod>
                    <changefreq>weekly</changefreq>
                    <priority>'.($depth < 9?round(1 - ($depth / 10), 1):'0.1').'</priority>
                </url>';
            }
        }
        
        $depth ++; 
        $output = levelSitemap($output, $el->id, $depth, $lan, $firstele, $fksdb, $base, $trans);
        $depth --;
    }
    
    return $output;
}

$ldv = $fksdb->fetch("SELECT timestamp_freigegeben FROM ".SQLPRE."document_versions WHERE language = '".$lan."' AND aktiv = '1' ORDER BY timestamp_freigegeben DESC LIMIT 1"); 

$sfile = ROOT.'content/export/sitemap_'.$lan.'.txt';
if($ldv->timestamp_freigegeben < $base->getOpt()->last_sitemap)
    $contents = file_get_contents($sfile);
    
$base->setHeaderCaching($ldv->timestamp_freigegeben);
    
if($ldv->timestamp_freigegeben < $base->getOpt()->last_sitemap && file_exists($sfile) && strlen($contents) > 5 && !$_GET['fresh'])
{
    exit($contents);
}
else
{
    $output = levelSitemap('', 0, 0, $lan, $firstele, $fksdb, $base, $trans);
    
    $gesamt_ausgabe = '<?xml version="1.0" encoding="UTF-8"?>
    <urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">'.$output.'</urlset>';
    
    echo $gesamt_ausgabe;
    
    $handle = fopen($sfile, 'w'); 
    
    if($handle && is_writable($sfile))
    { 
        fwrite($handle, $gesamt_ausgabe);
        
        $upd = $fksdb->query("UPDATE ".SQLPRE."options SET last_sitemap = '".$base->getTime()."' WHERE id = '1' LIMIT 1");
        
        if($base->getOpt()->last_sitemap < $base->getTime() - 3600)
        {
            $ping_url = DOMAIN.'/'.($lan != $trans->getStandardLanguage()?$lan.'/':'').'sitemap.xml';
            $google = @file_get_contents('http://www.google.com/webmasters/sitemaps/ping?sitemap='.$ping_url);
            $bing = @file_get_contents('http://www.bing.com/webmaster/ping.aspx?siteMap='.$ping_url);
        }
    }
}

$fksdb->close();
?>
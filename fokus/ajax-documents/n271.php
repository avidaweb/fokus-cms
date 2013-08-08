<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n271')
    exit($user->noRights());

$dir = $fksdb->save($_GET['dir'], 1);

echo '
<div class="dirbr">
    <a rel="0">'. $trans->__('Hauptverzeichnis') .'</a>';

    function ordner_struktur($fksdb, $parent, $output)
    {
        if($parent != 0)
        {
            $ergebnis = $fksdb->query("SELECT titel, id, dir FROM ".SQLPRE."files WHERE isdir = '1' AND id = '".$parent."' LIMIT 1");
            while($row = $fksdb->fetch($ergebnis))
            {
                $output = ' &raquo; <a rel="'.$row->id.'">'.$row->titel.'</a> '.$output;

                if($row->dir)
                    $output = ordner_struktur($fksdb, $row->dir, $output);
            }
        }

        return $output;
    }
    echo ordner_struktur($fksdb, $dir, '');

    echo '
</div>';

if($dir && $fksdb->save($_GET['mfa']))
{
    echo '
    <div class="choose_dir">
        <a>'. $trans->__('Den gewählten Ordner samt aktuellen und zukünftigen Kindelementen in die Galerie übernehmen') .'</a>
    </div>';
}

$dt = explode('|', $fksdb->save($_GET['dt']));
for($x=0; $x<count($dt)-1; $x++) $dts .= ($x==0?"AND ( ":" OR ")."last_type = '".$dt[$x]."'".($x==count($dt)-2?")":"");

$ar = explode('|', $fksdb->save($_GET['ar']));
$ars = (count($ar) > 1 && count($ar) < 3?($ar[0] == "h"?"AND last_ausrichtung > 1":"AND last_ausrichtung < 1"):"");

$q = $fksdb->save($_GET['qT']);
$qs = ($q?"AND (titel LIKE '%".$q."%' OR beschr LIKE '%".$q."%') ":"");

$sort1 = $fksdb->save($_GET['sort1']);
$sort2 = $fksdb->save($_GET['sort2']);

$laden = $fksdb->save($_GET['laden']);
$alle = $fksdb->query("SELECT id FROM ".SQLPRE."files WHERE dir = '".$dir."' AND papierkorb = '0' AND kat = '".$kat."' ".$dts." ".$stacksql." ".$ars." ".$qs."");
$verbleibend = $fksdb->count($alle) - $laden;
$verbleibend = ($verbleibend < 0?0:$verbleibend);

$dirs = $fksdb->query("SELECT id, kat, dir, isdir, titel FROM ".SQLPRE."files WHERE dir = '".$dir."' AND papierkorb = '0' AND kat = '0' AND isdir = '1' ".$qs." ".($sort1?" ORDER BY ".$sort1." ".$sort2:"")." LIMIT ".$laden);
if($fksdb->count($dirs)) echo '<div class="dirs">';
while($d = $fksdb->fetch($dirs))
{
    echo '
    <a rel="'.$d->id.'" title="'.$d->titel.'" class="dir">
        <img src="images/folder.png" alt="'.$d->titel.'" width="70" />
        <span>'.$d->titel.'</span>
    </a>';
}
if($fksdb->count($dirs)) echo '</div>';

echo '
<div class="tpics">';
    $stacks = $fksdb->query("SELECT id, kat, dir, isdir, titel FROM ".SQLPRE."files WHERE dir = '".$dir."' AND papierkorb = '0' AND kat = '0' AND isdir = '0' ".$dts." ".$ars." ".$qs." ".($sort1?" ORDER BY ".$sort1." ".$sort2:"")." LIMIT ".$laden);
    while($s = $fksdb->fetch($stacks))
    {
        $d = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$s->id."' ORDER BY timestamp DESC LIMIT 1");

        $bild0 = '../inc/img.php?id='.$s->id.'&amp;w=75&amp;h=52';
        $bild = '../inc/img.php?id='.$s->id.'&amp;w=115&amp;h=92';

        $thumb = array(
            '100' => DOMAIN.'/img/'.$s->id.'-100-0-'.$base->slug($s->titel).'.'.$d->type,
            '160' => DOMAIN.'/img/'.$s->id.'-160-0-'.$base->slug($s->titel).'.'.$d->type,
            '200' => DOMAIN.'/img/'.$s->id.'-200-0-'.$base->slug($s->titel).'.'.$d->type,
            '100h' => DOMAIN.'/img/'.$s->id.'-0-100-'.$base->slug($s->titel).'.'.$d->type
        );

        $no_preview = false;

        echo '
        <div class="pre" style="background-image:url('.$bild0.');" alt="'.$s->titel.'">
            '.(!$no_preview?'<img src="'.$bild.'" alt="'.$s->titel.'" />':'').'
            <a rel="'.$s->id.'" title="'.$s->titel.'" class="'.$d->width.'_'.$d->height.'" data-id="'.$s->id.'" data-title="'.$s->titel.'" data-width="'.$d->width.'" data-height="'.$d->height.'" data-thumb100h="'.$thumb['100h'].'" data-thumb100="'.$thumb['100'].'" data-thumb160="'.$thumb['160'].'" data-thumb200="'.$thumb['200'].'"></a>
            <p>'.$s->titel.'</p>
        </div>';
    }
echo '
</div>';

echo ($verbleibend > 0?'
<div class="more">
    '.($verbleibend > 10?'<a class="moreA"><strong>'. $trans->__('Mehr Bilder</strong> anzeigen (+10)') .'</a>':'').'
    <a class="moreB"><strong>'. $trans->__('Alle Bilder') .'</strong>'. $trans->__(' anzeigen (+%1)', false, array($verbleibend)) .'</a>
</div>':'');
?>
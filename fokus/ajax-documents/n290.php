<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n290')
    exit($user->noRights());

if(!isset($rel))
    $rel = '';

if(!Strings::strExists('.php', $rel))
    $rel = $rel.'.php';

$choose = $fksdb->save($_POST['choose']);

$ordner = '../content/dklassen/';
$fk = $base->open_dklasse($ordner.$rel);

if(!$fk['name'])
    exit('<div class="fehlerbox">'. $trans->__('Die gewählte Dokumentenklasse wurde nicht gefunden.') .'</div>');

$slug = $base->slug($fk['name']);

$dka = $base->db_to_array($base->getOpt('dk'));
$dk = (object)$dka;

if($dk->n_uebersicht[$slug])
    exit('<div class="fehlerbox">'. $trans->__('Die gewählte Dokumentenklasse darf laut Systemeinstellungen nicht mit eigener Dokumentenübersicht angezeigt werden.') .'</div>');

if($rechte['dok']['dk'] && !$rechte['dok']['dklasse'][$slug])
    exit('<div class="fehlerbox">'. $trans->__('Auf diese Dokumentenklasse hat die Ihnen zugewiesene Mitarbeiter-Rolle keinen Zugriff.') .'</div>');

if($fk['related'])
{
    $fk = $base->open_dklasse($ordner.$fk['related']);
    $tmp_inhalt = $fk['content'];

    $fk = $base->open_dklasse($ordner.$rel);
    $fk['content'] = $tmp_inhalt;
}

$bg_color = ($dk->color[$slug]?$dk->color[$slug]:'88d7ff');

$result = preg_match_all('@:(.*)\):@iU', $fk['content'], $subpattern);
$countatr = 0;
$dks = array();
$breiten = array();
$laengen = array();

foreach($subpattern[1] as $s1 => $s2)
{
    $b = explode('(', $s2);

    if(in_array($b[0], $base->getBlocks('dclass')))
    {
        $atr = $base->get_attributes($b[1]);
        $bid = $base->slug($atr['name']);

        if(!$atr['name'] || !$dk->show[$slug][$bid])
            continue;

        $countatr ++;
        $dks[$bid] = $atr['name'];
        $breiten[] = intval(($dk->breite_uebersicht[$slug][$bid]?$dk->breite_uebersicht[$slug][$bid]:100));
        $laengen[] = intval(($dk->laenge_uebersicht[$slug][$bid]?$dk->laenge_uebersicht[$slug][$bid]:0));
    }

    if($countatr >= 10)
        break;
}

$countbreiten = 0;
$countlaengen = 0;

$gbreite = 0;
$sbreite = '';
foreach($breiten as $bb)
{
    $gbreite += ($bb + 35);
    $sbreite .= (!$countbreiten?'':'|').$bb;
    $countbreiten ++;
}

$slaenge = '';
foreach($laengen as $ll)
{
    $slaenge .= (!$countlaengen?'':'|').$ll;
    $countlaengen ++;
}

echo '
<h1>
    <span class="color-circle" style="background: #'.$bg_color.';"></span>
    '. $trans->__('Dokumentenübersicht &quot;%1&quot;', false, array($fk['name'])) .'
</h1>

<div class="box" id="dkoverview">
    <div class="headline">
        <div class="a">
            <button class="new_doc shortcut-new">'. $trans->__('Neues Dokument anlegen') .'</button>
        </div>
        <div class="b">
            '. $trans->__('Dokumente suchen:') .'
            <input type="search" name="q" />
        </div>
    </div>';


    $katQ = $fksdb->query("SELECT id, kat, name FROM ".SQLPRE."categories ORDER BY sort");
    if($fksdb->count($katQ))
    {
        $ka = array();
        while($kx = $fksdb->fetch($katQ))
            $ka[$kx->kat][] = $kx;

        function kats($base, $ka, $eltern, $ebene)
        {
            $current = $ka[$eltern];
            if(!is_array($current)) $current = array();

            foreach($current as $k)
            {
                $has_childs = count($ka[$k->id]);

                echo '
                <p style="margin-left:'.($ebene * 16).'px;">
                    <input type="checkbox" value="'.$k->id.'" name="kat[]" id="ktdkat'.$k->id.'" />
                    <label for="ktdkat'.$k->id.'">'.$k->name.'</label>
                </p>';

                if($has_childs)
                {
                    $ebene ++;
                    kats($base, $ka, $k->id, $ebene);
                    $ebene --;
                }
            }
        }

        echo '
        <div class="menue documentmenu">
            <div class="li">
                <a class="rbutton rollout">'. $trans->__('Kategorien <span>anzeigen</span>') .'</a>
                <div class="opt opt2">
                    <form class="category_selection">';
                        kats($base, $ka, 0, 0);
                    echo '
                    </form>
                </div>
            </div>
        </div>';
    }

    echo '
    <input type="hidden" name="anz_spalten" value="'.count($dks).'" />
    <input type="hidden" name="datei" value="'.$rel.'" />
    <input type="hidden" name="slug" value="'.$slug.'" />
    <input type="hidden" name="klasse" value="'.str_replace('.php', '', $rel).'" />
    <input type="hidden" name="breiten" value="'.$sbreite.'" />
    <input type="hidden" name="gbreite" value="'.$gbreite.'" />
    <input type="hidden" name="laengen" value="'.$slaenge.'" />
    <input type="hidden" name="choose" value="'.($choose?'true':'').'" />

    <table class="overview">
        <tr class="head">
            '.($choose?'<th></th>':'').'
            <th data-sort="id" class="sort desc" style="width:20px">ID</th>
            '.(!$dk->n_titel_uebersicht[$slug]?'<th data-sort="titel">'. $trans->__('Bezeichner') .'</th>':'');
            $cupdk = 0;
            foreach($dks as $bid => $v)
            {
                $cupdk ++;
                $width = ($dk->breite_uebersicht[$slug][$bid]?$dk->breite_uebersicht[$slug][$bid]:100);
                echo '<th data-sort="dk'.$cupdk.'" style="width:'.$width.'px">'.$v.'</th>';
            }
            echo '
            <th data-sort="status">'. $trans->__('Status') .'</th>
            <th data-sort="timestamp_edit">'. $trans->__('Änderungsdatum') .'</th>
            <th data-sort="von_edit" style="min-width:90px">'. $trans->__('Autor') .'</th>
        </tr>
    </table>
</div>

'.($choose?'
<div class="box_save boxedarea">
    <ul></ul>
    <button class="takeit">'. $trans->__('Auswahl übernehmen') .'</button>
</div>':'').'';
?>
<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n291')
    exit($user->noRights());

$slug = $fksdb->save($_POST['slug']);
$klasse = $fksdb->save($_POST['klasse']);
$spalten = $fksdb->save($_POST['spalten']);
$sort = $fksdb->save($_POST['sort']);
$sort2 = $fksdb->save($_POST['sort2']);
$choose = $fksdb->save($_POST['choose']);
$akt_limit = $fksdb->save($_REQUEST['limit']);
$q = explode(' ', $fksdb->save($_POST['q']));
$breiten = explode('|', $fksdb->save($_POST['breiten']));
$laengen = explode('|', $fksdb->save($_POST['laengen']));
$echte_spalten = $fksdb->save($_POST['realspalten'], 1);

parse_str($_POST['cats'], $catsA);
$cats = (!is_array($catsA['kat'])?array():$catsA['kat']);

if($rechte['dok']['dk'] && !$rechte['dok']['dklasse'][$slug])
    exit();

$dka = $base->db_to_array($base->getOpt('dk'));
$dk = (object)$dka;

$real_count = 0;
$total_count = 0;

function dk_content_by_type($base, $dk, $content, $type, $breite = 0, $laenge = 0)
{
    if($type == 30)
    {
        if(!$content)
            return '';

        $ca = explode('|||', $content);

        if($laenge > 0)
        {
            if($ca[1] && $ca[2])
                $laenge = round(($laenge / 2), 0);

            $ca[1] = Strings::cut($ca[1], $laenge);
            $ca[2] = Strings::cut($ca[2], $laenge);
        }

        $beschr = $ca[1].($ca[1] && $ca[2]?' / ':'').$ca[2];

        $content = '
        <img src="'.DOMAIN.'/img/'.$ca[0].'-'.$breite.'-'.$breite.'-'.$base->slug($ca[1]).'.'.$ca[3].'" alt=" " /><br />
        <span class="rimg">'.$beschr.'</span>';
    }
    else
    {
        if($laenge > 0)
            $content = Strings::cut($content, $laenge);
    }

    return $content;
}

$add_sql = "";
foreach($q as $s)
{
    if($s)
    {
        $s = htmlentities($s, ENT_QUOTES, "UTF-8");
        $add_sql .= " AND (id LIKE '".$s."' OR titel LIKE '%".$s."%' OR sprachenfelder LIKE '%".$s."%' OR dk1 LIKE '%".$s."%' OR dk2 LIKE '%".$s."%' OR dk3 LIKE '%".$s."%' OR dk4 LIKE '%".$s."%') ";
    }
}

if($sort == 'status')
    $sort = "statusB ".$sort2.", statusA";

$docq = $fksdb->query("SELECT id, titel, anfang, bis, statusA, statusB, dk1, dk2, dk3, dk4, dk5, dk6, dk7, dk8, dk9, dk10, dkt1, dkt2, dkt3, dkt4, dkt5, dkt6, dkt7, dkt8, dkt9, dkt10, dversion_edit, timestamp_edit, von_edit, author, von, kats FROM ".SQLPRE."documents WHERE papierkorb = '0' AND klasse = '".$klasse."' ".$add_sql.($sort?" ORDER BY ".$sort." ".$sort2:""));
while($doc = $fksdb->fetch($docq))
{
    $total_count ++;

    $dcats = $base->fixedUnserialize($doc->kats);
    if(!is_array($dcats))
        $dcats = array();

    if(count($cats))
    {
        $in_cats = false;

        foreach($dcats as $cat)
        {
            if(in_array($cat, $cats))
                $in_cats = true;
        }

        if(!$in_cats)
            continue;
    }

    if($doc->anfang || $doc->bis)
    {
        $dscheck = $base->find_check_document_statusB($doc->id, $doc->anfang, $doc->bis, $doc->statusB);
        if($dscheck >= 0)
            $doc->statusB = $dscheck;
    }

    $dk1 = '';
    $dk2 = '';
    $dk3 = '';
    $dk4 = '';
    $dk5 = '';
    $dk6 = '';
    $dk7 = '';
    $dk8 = '';
    $dk9 = '';
    $dk10 = '';

    if($spalten >= 1) $dk1 = dk_content_by_type($base, $dk, $doc->dk1, $doc->dkt1, $breiten[0], $laengen[0]);
    if($spalten >= 2) $dk2 = dk_content_by_type($base, $dk, $doc->dk2, $doc->dkt2, $breiten[1], $laengen[1]);
    if($spalten >= 3) $dk3 = dk_content_by_type($base, $dk, $doc->dk3, $doc->dkt3, $breiten[2], $laengen[2]);
    if($spalten >= 4) $dk4 = dk_content_by_type($base, $dk, $doc->dk4, $doc->dkt4, $breiten[3], $laengen[3]);
    if($spalten >= 5) $dk5 = dk_content_by_type($base, $dk, $doc->dk5, $doc->dkt5, $breiten[4], $laengen[4]);
    if($spalten >= 6) $dk6 = dk_content_by_type($base, $dk, $doc->dk6, $doc->dkt6, $breiten[5], $laengen[5]);
    if($spalten >= 7) $dk7 = dk_content_by_type($base, $dk, $doc->dk7, $doc->dkt7, $breiten[6], $laengen[6]);
    if($spalten >= 8) $dk8 = dk_content_by_type($base, $dk, $doc->dk8, $doc->dkt8, $breiten[7], $laengen[7]);
    if($spalten >= 9) $dk9 = dk_content_by_type($base, $dk, $doc->dk9, $doc->dkt9, $breiten[8], $laengen[8]);
    if($spalten >= 10) $dk10 = dk_content_by_type($base, $dk, $doc->dk10, $doc->dkt10, $breiten[9], $laengen[9]);

    $oeffnen = ($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())?true:false);

    echo '
    <tr class="serp">
        '.($choose?'<td><a class="choose" data-id="'.$doc->id.'" data-titel="'.($doc->titel?$doc->titel:$doc->id).'">auswählen</a></td>':'').'
        <td class="id">'.($oeffnen?'<a class="inc_documents" id="n250" rel="'.$doc->id.'">':'').'D'.str_pad($doc->id, 5 ,'0', STR_PAD_LEFT).($oeffnen?'</a>':'').'</td>
        '.(!$dk->n_titel_uebersicht[$slug]?'<td>'.($oeffnen?'<a class="inc_documents" id="n250" rel="'.$doc->id.'">':'').$doc->titel.($oeffnen?'</a>':'').'</td>':'').'
        '.($spalten >= 1?'<td>'.$dk1.'</td>':'').'
        '.($spalten >= 2?'<td>'.$dk2.'</td>':'').'
        '.($spalten >= 3?'<td>'.$dk3.'</td>':'').'
        '.($spalten >= 4?'<td>'.$dk4.'</td>':'').'
        '.($spalten >= 5?'<td>'.$dk5.'</td>':'').'
        '.($spalten >= 6?'<td>'.$dk6.'</td>':'').'
        '.($spalten >= 7?'<td>'.$dk7.'</td>':'').'
        '.($spalten >= 8?'<td>'.$dk8.'</td>':'').'
        '.($spalten >= 9?'<td>'.$dk9.'</td>':'').'
        '.($spalten >= 10?'<td>'.$dk10.'</td>':'').'
        <td class="xstatus">'.$base->document_status($doc->statusA, $doc->statusB).'</td>
        <td>'.$base->is_online($doc->timestamp_edit, true).'</td>
        <td><a class="inc_users" id="n590" rel="'.$doc->author.'">'.$base->user($doc->author, ' ', 'vorname', 'nachname').'</a></td>
    </tr>';

    $real_count ++;
    if($real_count >= $akt_limit)
        break;
}

$insgesamt = $fksdb->count($docq);

if(!$real_count)
{
    echo '
    <tr>
        <td colspan="'.$echte_spalten.'" class="calibri nothing_found">keine Dokumente vorhanden</td>
    </tr>';
}
elseif($total_count < $insgesamt)
{
    $verbleibend = $insgesamt - $total_count;

    echo '
    <tr>
        <td colspan="'.$echte_spalten.'" class="calibri more_results">
            <a class="next">'. $trans->__('+ weitere Dokumente anzeigen %1', false, array(($verbleibend < 15?$verbleibend:'15'))) .'</a>
            '.($verbleibend > 15?'<a class="all">'. $trans->__('+ alle Dokumente anzeigen (%1)', false, array(($verbleibend))) .'</a>':'').'
        </td>
    </tr>';
}
?>
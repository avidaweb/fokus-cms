<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'kkanal') || $index != 'n632')
    exit($user->noRights());

$vid = $fksdb->save($_REQUEST['vid']);
$type = $fksdb->save($_REQUEST['type']);

$opt = explode('+', $fksdb->save($_REQUEST['opt']));

$q = $fksdb->save($_REQUEST['q']);
$data_counter = 0;

if($type == 'formular')
{
    $kk = $fksdb->fetch("SELECT html, vid FROM ".SQLPRE."blocks WHERE type = '52' AND vid = '".$vid."' ORDER BY id DESC LIMIT 1");
    $fo = $base->fixedUnserialize($kk->html);

    $name = ($fo['name']?$fo['name']:$trans->__('Unbenanntes Formular'));

    $fa = $fo['f'];
    if(!is_array($fa)) $fa = array();

    echo '
    <tr id="headline">';
        $counter = 0;
        foreach($fa as $f_id => $f)
        {
            if($f['type'] == 'string' || !in_array($f_id, $opt))
                continue;

            echo '<td id="kk_'.$f_id.'"'.(!$counter?' class="first"':'').'>'.$f['name'].'</td>';
            $counter ++;
        }
        echo '
        <td id="kk_d_id" class="optional">'.$trans->__('Datensatz-ID').'</td>
        <td id="kk_d_number" class="optional">'.$trans->__('Nummer').'</td>
        <td id="kk_d_timestamp" class="optional">'.$trans->__('Erfassungsdatum').'</td>
        <td id="kk_d_opt" class="optional last">'.$trans->__('Optionen').'</td>
    </tr>';

    $counterup = 0;

    $fQ = $fksdb->query("SELECT id, timestamp, felder FROM ".SQLPRE."records WHERE vid = '".$vid."' ORDER BY id DESC");
    while($fi = $fksdb->fetch($fQ))
    {
        $data = $base->db_to_array($fi->felder);
        $data_counter ++;

        $rtn = '';
        $counterup ++;
        $search_result = false;

        $rtn .= '
        <tr'.($data_counter == $fksdb->count($fQ)?' class="last"':'').'>';
            $counter = 0;
            foreach($fa as $f_id => $f)
            {
                if($f['type'] == 'string' || !in_array($f_id, $opt))
                    continue;

                $val = $data[$f_id]['value'];

                if($f['type'] == 'img')
                {
                    $valA = $base->db_to_array($val);
                    $val = ($valA['status'] == 'ok'?'<img src="'.$valA['thumbnail_url'].'" alt=" " />':'');
                }

                if($q && $f['type'] != 'img')
                {
                    if(Strings::strExists($q, $val, false) || Strings::strExists($val, $q, false))
                    {
                        $search_result = true;
                        $val = '<strong>'.$val.'</strong>';
                    }
                }

                $rtn .= '<td'.(!$counter?' class="first"':'').'>'.$val.'</td>';
                $counter ++;
            }
            $rtn .= '
            <td class="first optional">'.str_pad($fi->id, 6 ,'0', STR_PAD_LEFT).'</td>
            <td class="optional">'.str_pad($counterup, strlen($fksdb->count($fQ)) ,'0', STR_PAD_LEFT).'</td>
            <td class="optional">'.date('d.m.Y', $fi->timestamp).' - '.date('H:i', $fi->timestamp).'</td>
            <td class="last optional">
                <a title="'.$trans->__('Datensatz entfernen?').'" class="delete" data-id="'.$fi->id.'">
                    <img src="images/delete.png" alt="'.$trans->__('Entfernen').'" height="14" />
                </a>
            </td>
        </tr>';

        if(!$q || $search_result)
        {
            echo $rtn;
        }
    }
}
elseif($type == 'suche')
{
    echo '
    <tr id="headline">
        <td class="first">'.$trans->__('Suchphrase').'</td>
        <td>'.$trans->__('Ergebnisse').'</td>
        <td>'.$trans->__('IP-Adresse').'</td>

        <td id="kk_d_id" class="optional">'.$trans->__('Datensatz-ID').'</td>
        <td id="kk_d_timestamp" class="optional last">'.$trans->__('Erfassungsdatum').'</td>
    </tr>';

    $fQ = $fksdb->query("SELECT q, results, ip, id, timestamp FROM ".SQLPRE."searches".($q?" WHERE q LIKE '%".$q."%'":"")." ORDER BY id DESC");
    while($fi = $fksdb->fetch($fQ))
    {
        $data_counter ++;

        echo '
        <tr'.($data_counter == $fksdb->count($fQ)?' class="last"':'').'>
            <td class="first">'.$fi->q.'</td>
            <td>'.$fi->results.'</td>
            <td>'.$fi->ip.'</td>

            <td class="first optional">'.str_pad($fi->id, 6 ,'0', STR_PAD_LEFT).'</td>
            <td class="last optional">'.date('d.m.Y', $fi->timestamp).' - '.date('H:i', $fi->timestamp).'</td>
        </tr>';
    }
}
elseif($type == 'comments')
{
    echo '
    <tr id="headline">
        <td class="first"></td>
        <td>'.$trans->__('Name').'</td>
        <td>'.$trans->__('Email').'</td>
        <td>'.$trans->__('Web').'</td>
        <td>'.$trans->__('Kommentar').'</td>
        <td>'.$trans->__('Status').'</td>
        <td>'.$trans->__('Zuordnung').'</td>

        <td id="kk_d_opt" class="optional" colspan="2">'.$trans->__('Optionen').'</td>
        <td id="kk_d_s" class="optional">'.$trans->__('Strukturelement').'</td>
        <td id="kk_d_timestamp" class="optional last">'.$trans->__('Erfassungsdatum').'</td>
    </tr>';

    $fQ = $fksdb->query("SELECT id, element, name, email, web, text, frei, type, dk, timestamp FROM ".SQLPRE."comments".($q?" WHERE (name LIKE '%".$q."%' OR email LIKE '".$q."%' OR web LIKE '".$q."%')":"")." ORDER BY id DESC");
    while($fi = $fksdb->fetch($fQ))
    {
        $data_counter ++;

        if(!$fi->dk)
            $ele = $fksdb->fetch("SELECT titel FROM ".SQLPRE."elements WHERE id = '".$fi->element."' LIMIT 1");
        else
            $ele = $fksdb->fetch("SELECT titel FROM ".SQLPRE."documents WHERE id = '".$fi->dk."' LIMIT 1");

        echo '
        <tr'.($data_counter == $fksdb->count($fQ)?' class="last"':'').'>
            <td class="first"><input type="checkbox" name="multi" value="'.$fi->id.'" /></td>
            <td>'.$fi->name.'</td>
            <td>'.$fi->email.'</td>
            <td>'.$fi->web.'</td>
            <td>'.$fi->text.'</td>
            <td>'.($fi->frei?'Freigeschaltet':'Gesperrt').'</td>
            <td>'.($fi->type?'Dokument':'Strukturelement').'</td>

            <td class="first optional">
                <a class="cfreisperr" rel="'.$fi->id.'">
                    '.($fi->frei?$trans->__('Sperren'):'<strong>'.$trans->__('Freischalten').'</strong>').'
                </a>
            </td>
            <td class="optional">
                <a class="cdel" rel="'.$fi->id.'">'.$trans->__('Entfernen').'</a>
            </td>
            <td class="optional">
                <a href="'.$domain.'/'.$fi->element.($fi->dk?'/'.$fi->dk:'').'/'.$base->slug($ele->titel).'/" target="_blank">'.$ele->titel.'</a>
            </td>
            <td class="last optional">'.date('d.m.Y', $fi->timestamp).' - '.date('H:i', $fi->timestamp).'</td>
        </tr>';
    }
}
?>
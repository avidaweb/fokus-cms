<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'pn') || !$suite->rm(10) || $index != 'n644')
    exit($user->noRights());

$empf = $fksdb->save($_REQUEST['empf']);
$checked = explode('_', substr($fksdb->save($_REQUEST['checked']), 0, -1));
$checkcount = 0;

if(count($checked) && $_REQUEST['checked'])
{
    foreach($checked as $c)
    {
        $csql .= (!$checkcount?"":" OR ")." id = '".intval($c)."' ";
        $checkcount ++;
    }
    $csql = "(".$csql.") OR ";
}
else
    $checked = array();

if($empf || count($checked))
    $pnQ = $fksdb->query("SELECT id FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND id != '".$user->getID()."' AND (".$csql.($empf?"(vorname LIKE '".$empf."%' OR nachname LIKE '".$empf."%')":"id = '-1'").") LIMIT 5");
else
    $pnQ = $fksdb->query("SELECT von FROM ".SQLPRE."messages WHERE benutzer = '".$user->getID()."' AND an = '".$user->getID()."' AND id != '".$user->getID()."' GROUP BY von ORDER BY timestamp DESC LIMIT 5");

while($pn = $fksdb->fetch($pnQ))
{
    $von = ($empf || count($checked)?$pn->id:$pn->von);

    echo '
    <p>
        <input type="checkbox" name="empf[]" id="empf_'.$von.'" value="'.$von.'"'.(in_array($von, $checked)?' checked="checked"':'').' />
        <label class="lcolor" for="empf_'.$von.'">'.$base->user($von, ' ', 'vorname', 'nachname').'</label>
    </p>';
}

if(!count($checked) && !$fksdb->count($pnQ))
    echo '<p>'.$trans->__('Keine Benutzer gefunden.').'</p>';

echo '
<p class="bp">
    <button>'.$trans->__('auswählen').'</button>
</p>';
?>
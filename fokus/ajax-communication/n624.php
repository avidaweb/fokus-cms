<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'nlsend') || !$suite->rm(5) || $index != 'n624')
    exit($user->noRights());

echo '
<tr class="head">
    <td></td>
    <td>'.$trans->__('Vorname').'</td>
    <td>'.$trans->__('Nachname').'</td>
    <td>'.$trans->__('Email').'</td>
    <td>'.$trans->__('Ort').'</td>
    <td>'.$trans->__('Land').'</td>
    <td>'.$trans->__('Rollen').'</td>
</tr>';

parse_str($_POST['f'], $ca);
$c = (object)$ca;

$empf = array();
$empfT = explode(',', $c->empf);
foreach($empfT as $e)
{
    $e = trim($e);
    if($base->is_valid_email($e))
        $empf[] = $e;
}

$rollen = array();
$rolQ = $fksdb->query("SELECT id, titel FROM ".SQLPRE."roles WHERE papierkorb = '0' ORDER BY sort, id");
while($rol = $fksdb->fetch($rolQ))
    $rollen[$rol->id] = $rol->titel;

if(!is_array($c->ro))
    $c->ro = array();

if($c->q)
    $add_sql = " AND (vorname LIKE '".$c->q."%' OR nachname LIKE '".$c->q."%' OR email LIKE '%".$c->q."%')";

$pQ = $fksdb->query("SELECT id, vorname, nachname, ort, land, email FROM ".SQLPRE."users WHERE papierkorb = '0'".($c->fstati?" AND status = '0'":"")." ".$add_sql." ORDER BY nachname, vorname");
while($p = $fksdb->fetch($pQ))
{
    $c_rol = '';
    $has_role = false;

    $rolQ = $fksdb->query("SELECT rolle FROM ".SQLPRE."user_roles WHERE benutzer = '".$p->id."'");
    while($rol = $fksdb->fetch($rolQ))
    {
        $c_rol .= (!$c_rol?'':', ').$rollen[$rol->rolle];

        if(in_array($rol->rolle, $c->ro))
            $has_role = true;
    }

    if(!$has_role && count($c->ro))
        continue;

    echo '
    <tr>
        <td><input type="checkbox" value="'.$p->email.'"'.(in_array($p->email, $empf) || !$p->email?' class="no" disabled="disabled"':' class="yes"').' /></td>
        <td>'.$p->vorname.'</td>
        <td>'.$p->nachname.'</td>
        <td>'.($p->email?$p->email:'<em>'.$trans->__('keine Email').'</em>').'</td>
        <td>'.$p->ort.'</td>
        <td>'.$p->land.'</td>
        <td>'.$c_rol.'</td>
    </tr>';
}
?>
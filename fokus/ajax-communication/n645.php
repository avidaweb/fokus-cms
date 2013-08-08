<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'pn') || !$suite->rm(10) || $index != 'n645')
    exit($user->noRights());
        
$rel = $_REQUEST['rel'];
if(!$rel)
    exit('no relation');

if(Strings::strExists('answer', $rel))
{
    $nrel = explode('|', $rel);
    $rel = $nrel[2];
    $answer = intval($nrel[1]);
    $bo = $fksdb->fetch("SELECT titel FROM ".SQLPRE."messages WHERE id = '".$answer."' AND benutzer = '".$user->getID()."' LIMIT 1");
}

parse_str($rel, $empfB);
if(is_array($empfB['empf']) && count($empfB['empf']))
{
    foreach($empfB['empf'] as $e)
    {
        $input_hidden .= $e.';';

        $ausgabe .= '
        <span class="tempf">
            <a class="del" rel="'.$e.'"></a>
            <a class="inc_users" id="n590" rel="'.$e.'">
                '.$base->user($e, ' ', 'vorname', 'nachname').'
            </a>
        </span>';
    }
}
else
{
    $e = $rel;
    $input_hidden = $e.';';

    $ausgabe = '
    <span class="tempf">
        <a class="del" rel="'.$e.'"></a>
        <a class="inc_users" id="n590" rel="'.$e.'">
            '.$base->user($e, ' ', 'vorname', 'nachname').'
        </a>
    </span>';
}

echo '
<h1>'.$trans->__('Neue Nachricht schreiben.').'</h1>

<div class="box" id="neu_pn">
    <strong>'.$trans->__('Titel / Betreff:').'</strong>
    <input type="text" id="msg_titel" value="'.$bo->titel.'" />

    <strong>'.$trans->__('Nachrichtentext:').'</strong>
    <textarea id="msg_text"></textarea>

    <strong>'.$trans->__('Empfänger der Nachricht:').'</strong>
    <input type="hidden" value="'.$input_hidden.'" id="msg_empf" />
    <div class="msg_empf">'.$ausgabe.'</div>
</div>

<div class="box_save" style="display:block;">
    <input type="button" value="verwerfen" class="bs1" /> <input type="button" value="Nachricht absenden" class="bs2" />
</div>';
?>
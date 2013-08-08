<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'nledit') || !$suite->rm(5) || $index != 'n615')
    exit($user->noRights());

if($rel)
{
    $k = $fksdb->fetch("SELECT id, titel, template FROM ".SQLPRE."newsletters WHERE id = '".$rel."' LIMIT 1");
    if($k) $edit = true;
}

echo '
<h1>'.($edit?$trans->__('Newsletter bearbeiten.'):$trans->__('Newsletter anlegen.')).'</h1>
<input type="hidden" name="kid" value="'.$k->id.'" />

<form class="nele">
<div class="box">
    <table class="struk_dok_table">
        <tr>
            <td class="left"><strong>'.$trans->__('Name des Newsletters:').'</strong></td>
            <td>
                <input type="text" name="titel" class="titel" value="'.$k->titel.'"'.(!$edit?' autofocus':'').' />
                <span>'.$trans->__('(Wird nur für den internen Gebrauch verwendet)').'</span>
            </td>
        </tr>
        <tr>
            <td class="left"><strong>'.$trans->__('Template-Datei:').'</strong></td>
            <td>
                <select name="template">';
                    if(count($base->getActiveTemplateConfig('newsletter')))
                    {
                        foreach($base->getActiveTemplateConfig('newsletter') as $cn => $ck)
                            echo '<option value="'.$ck.'"'.($k->template == $ck?' selected="selected"':'').'>'.(is_numeric($cn)?$ck:$cn.' ('.$ck.')').'</option>';
                    }
                    else
                    {
                        echo '<option value=""'.(!$k->template?' selected="selected"':'').'>'.$trans->__('Standard (index.php)').'</option>';
                        if(count($base->getActiveTemplateConfig('files')))
                        {
                            foreach($base->getActiveTemplateConfig('files') as $cn => $ck)
                                echo '<option value="'.$ck.'"'.($k->template == $ck?' selected="selected"':'').'>'.(is_numeric($cn)?$ck:$cn.' ('.$ck.')').'</option>';
                        }
                    }
                    echo '
                </select>
            </td>
        </tr> 
    </table>
</div>
<div class="movebox"'.(!$edit?' style="display:none;"':'').'>
    <div class="loadme">
        <img src="images/loading_grey.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
    </div>
    
    <img src="images/moveboxH.png" alt="" class="schatten" />
    <div class="moved baum" data-kat="0" id="Kstruk_doks"></div>
    <img src="images/moveboxB.png" alt="" class="schatten" />
</div>
<div class="box v_struk nlpreview"'.(!$edit?' style="display:none;"':'').'>
    <a>'.$trans->__('Vorschau öffnen').'</a>
</div>
<div class="box_save">
    <button class="bs2">'.$trans->__('speichern').'</button>
</div>
</form>';
?>
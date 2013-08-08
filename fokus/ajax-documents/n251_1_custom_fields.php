<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251_1_custom_fields')
    exit($user->noRights());
     
$id = $fksdb->save($_POST['id'], 1);

$doc = $fksdb->fetch("SELECT id, von, klasse, cf FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$doc || !$doc->klasse)
    exit('<div class="ifehler">'.$trans->__('Dokument nicht gefunden.').'</div>');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())) {}
else exit($user->noRights());

$cf = $api->getCustomFields();
if(!is_array($cf)) $cf = array();

$cfi = $base->fixedUnserialize($doc->cf);
if(!is_array($cfi)) $cfi = array();

echo '
<h1>'.$trans->__('Benutzerdefinierte Felder für dieses Dokument bearbeiten.').'</h1>

<div class="box">
    <form class="doc_custom_fields">
    <table>';
        foreach($cf as $k => $v)
        {
            if(!$v['global'] || !$v['name'])
                continue;
                
            if(count($v['restriction']))
            {
                if($v['restriction']['documents'] !== true && !in_array($doc->id, $v['restriction']['documents']))
                    continue;
            }
                
            echo '
            <tr>
                <td class="ftd">'.$v['name'].'</td>
                <td>';
                    echo '<input type="hidden" name="cf_used[]" value="'.$k.'" />';

                    if(!$v['type'] || $v['type'] == 'text' || $v['type'] == 'input')
                    {
                        echo '<input type="text" name="cf['.$k.']" value="'.$cfi[$k].'" />';
                    }
                    elseif($v['type'] == 'textarea')
                    {
                        echo '<textarea name="cf['.$k.']">'.$cfi[$k].'</textarea>';
                    }
                    elseif($v['type'] == 'checkbox')
                    {
                        echo '<input type="checkbox" name="cf['.$k.']" value="fks_true"'.($cfi[$k] == 'fks_true'?' checked':'').' />';
                    }
                    elseif($v['type'] == 'select' && is_array($v['values']))
                    {
                        echo '<select name="cf['.$k.']">';
                        foreach($v['values'] as $x => $y)
                            echo '<option value="'.$x.'"'.($cfi[$k] == $x?' selected':'').'>'.$y.'</option>';
                        echo '                     
                        </select>';
                    }
                echo '
                </td>
            </tr>';
        }
    echo '
    </table>
    </form>';
echo '
</div>

<div class="box_save">
    <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
    <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
</div>';

exit();
?>
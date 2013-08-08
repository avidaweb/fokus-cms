<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$bilder = $base->fixedUnserialize($row->html);
if(!is_array($bilder)) $bilder = array();

$add_to_pic = array(0 => '', 1 => '_2', 2 => '_2', 3 => '_3');
$used_pics = array();

echo '
<form method="post" id="dgalerie_form">
<div class="box">
    <div id="dgalerie">';
        
        foreach($bilder as $b1 => $b2)
        {
            if(in_array($b2['id'], $used_pics))
                continue;
            $used_pics[] = $b2['id'];
            
            $stack = $fksdb->fetch("SELECT id, kat, dir, isdir, titel FROM ".SQLPRE."files WHERE id = '".$b2['id']."' AND papierkorb = '0' LIMIT 1"); 
            $pic = $fksdb->fetch("SELECT id, file, type FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");
            
            if($stack->isdir)
            {
                $kinder = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."files WHERE dir = '".$row->id."' AND isdir = '1'")); 
                $bild = $domain.'/fokus/images/folder'.$add_to_pic[$kinder].'.png';
            }
            else
            {
                $bild = DOMAIN.'/img/'.$stack->id.'-120-0-'.$base->slug($stack->titel).'.'.$pic->type;
            }
        
            echo '
            <table class="bild">
                <tr>
                    <td rowspan="3" class="td1"><img src="'.$bild.'" alt="N/A" /></td>
                    <td class="td2">'.(!$stack->isdir?'Bildname':'Ordner').':</td>
                    <td class="td3" colspan="2">
                        '.(!$stack->isdir?'<input type="text" name="p['.$b1.'][name]" value="'.$b2['name'].'" />':
                        '<input type="text" disabled value="'.$stack->titel.'" />').'
                    </td>
                    <td rowspan="3" class="td4"><a class="bild_del" rel="'.$b2['id'].'">'. $trans->__('entfernen') .'</a></td>
                    <td rowspan="3" class="mover"></td>
                </tr>
                <tr>
                    <td class="td2">'.(!$stack->isdir?'Bildbeschreibung':'Unterordner').':</td>
                    <td class="td3">
                        '.(!$stack->isdir?'<input type="text" name="p['.$b1.'][desc]" value="'.$b2['desc'].'" />':
                        $kinder.'<input type="hidden" name="p['.$b1.'][isdir]" value="1" />').'
                    </td>
                    <td><input type="hidden" name="p['.$b1.'][id]" value="'.$b2['id'].'" /></td>
                </tr>
                <tr>
                    <td class="td2">Vorschau verstecken?</td>
                    <td class="td3"><input class="hidev" type="checkbox" name="p['.$b1.'][hidev]" value="1"'.($b2['hidev']?' checked="checked"':'').' /></td>
                    <td></td>
                </tr>
            </table>';                       
        }
        echo '
        <button class="choose">'. $trans->__('Bilder auswählen') .'</button>
        '.($user->r('dat', 'new')?'<button class="upload">'. $trans->__('Bilder hochladen') .'</button>':'').'
    </div>
</div>
</form>';
?>
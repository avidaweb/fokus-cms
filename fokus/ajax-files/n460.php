<?php
if($index != 'n460' || !$suite->rm(1) || !$user->r('dat', 'edit')) 
    exit($user->noRights());
    

$file = $fksdb->save($_POST['file'], 1);
$file_version = $fksdb->save($_POST['file_version'], 1);

$stack = $fksdb->fetch("SELECT * FROM ".SQLPRE."files WHERE id = '".$file."' LIMIT 1");
$file = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ".($file_version?"AND id = '".$file_version."'":"ORDER BY timestamp DESC")." LIMIT 1");

if(!$stack || !$file)
    exit($trans->__('Das ausgewählte Bild existiert nicht mehr.'));
        
$vh = $file->width / 640;
$original = '../content/uploads/'.$base->getFilePath($stack->kat).'/'.$file->file.'.'.$file->type;

$bild_url = '../img/'.$stack->id.'-640-0-~fids~'.$file->id.'~fide~'.Strings::createID().'.'.$file->type;
    
echo '
<h1>'.$trans->__('Bild &quot;%1&quot; bearbeiten.', false, array($stack->titel)).'</h1>

<div class="box" id="picedit">

    <input type="hidden" id="pic_vh" value="'.$vh.'" />
    <input type="hidden" id="pic_x" value="0" />
    <input type="hidden" id="pic_y" value="0" />
    <input type="hidden" id="pic_w" value="" />
    <input type="hidden" id="pic_h" value="" />
    <input type="hidden" id="pic_c" value="100" />
    <input type="hidden" id="pic_b" value="50" />

    <div class="area">
        <div class="aL">
            <img src="'.$bild_url.'" alt="'.$stack->titel.'" id="cropbox" />
            
            <p class="desc">
                <button id="go_zuschnitt">'.$trans->__('Bild zuschneiden').'</button>
                <span>
                    '.$trans->__('Bild zuschneiden? So gehts: Ziehen Sie einfach mit der Maus<br />einen &quot;Rahmen&quot; über den gewünschten Bildausschnitt.').'
                </span>
            </p>
        </div>
        <div class="aR">
            <table>
                <tr>
                    <td>'.$trans->__('Größe').'</td>
                    <td>'.$base->filetype($original).'</td>
                </tr>
                '.($stack->kat < 2?'
                <tr>
                    <td>'.$trans->__('Abmessungen').'</td>
                    <td>'.$file->width.' x '.$file->height.' Pixel</td>
                </tr>
                ':'').'
                <tr>
                    <td>'.$trans->__('Dateityp').'</td>
                    <td>'.$base->filetype($original).'</td>
                </tr>
                <tr>
                    <td>'.$trans->__('Autor').'</td>
                    <td>'.$base->user($file->autor, ' ', 'vorname', 'nachname').'</td>
                </tr>
            </table>
            
            <span class="trenn"></span>
            
            <table class="saettigung">
                <tr>
                    <td><h3 class="calibri">'.$trans->__('Schwarzweiß').'</h3></td>
                </tr>
                <tr>
                    <td>
                        <input type="checkbox" value="1" id="pic_s" /> 
                        <label for="pic_s">'.$trans->__('Nur Graustufen verwenden').'</label>
                    </td>
                </tr>
            </table>
            
            <span class="trenn"></span>
            
            <table class="cropped">
                <tr>
                    <td><h3 class="calibri">'.$trans->__('Vorschaubereich').'</h3></td>
                </tr>
                <tr>
                    <td>
                        <div class="croparea">
                            <span class="crop'.($stack->cropped == 1?' active':'').'" data-crop="1" title="'.$trans->__('oben links').'"></span>
                            <span class="crop'.($stack->cropped == 2?' active':'').'" data-crop="2" title="'.$trans->__('oben zentriert').'"></span>
                            <span class="crop'.($stack->cropped == 3?' active':'').'" data-crop="3" title="'.$trans->__('oben rechts').'"></span>
                            <span class="crop'.($stack->cropped == 4?' active':'').'" data-crop="4" title="'.$trans->__('zentriert links').'"></span>
                            <span class="crop'.($stack->cropped == 0?' active':'').'" data-crop="0" title="'.$trans->__('zentriert').'"></span>
                            <span class="crop'.($stack->cropped == 5?' active':'').'" data-crop="5" title="'.$trans->__('zentriert rechts').'"></span>
                            <span class="crop'.($stack->cropped == 6?' active':'').'" data-crop="6" title="'.$trans->__('unten links').'"></span>
                            <span class="crop'.($stack->cropped == 7?' active':'').'" data-crop="7" title="'.$trans->__('unten zentriert').'"></span>
                            <span class="crop'.($stack->cropped == 8?' active':'').'" data-crop="8" title="'.$trans->__('unten rechts').'"></span>
                        </div>
                    </td>
                </tr>
            </table>
            
            <span class="trenn"></span>
            
            <table class="zuschnitt" id="table_zuschnitt">
                <tr>
                    <td colspan="3"><h3 class="calibri">'.$trans->__('Zuschnitt').'</h3></td>
                </tr>
                <tr>
                    <td>'.$trans->__('Breite:').'</td>
                    <td><input type="text" disabled="disabled" value="" id="pic_breite" /></td>
                    <td>'.$trans->__('Pixel').'</td>
                </tr>
                <tr>
                    <td>'.$trans->__('Höhe:').'</td>
                    <td><input type="text" disabled="disabled" value="" id="pic_hoehe" /></td>
                    <td>'.$trans->__('Pixel').'</td>
                </tr>
            </table>
        </div>
    </div>
</div>

<div class="box pic_edit_data">
    <p>
        <label for="pic_titel">'.$trans->__('Titel').'</label>
        <input type="text" id="pic_titel" class="inp" value="'.$stack->titel.'" autofocus />
    </p>
    
    <p>
        <label for="pic_desc">'.$trans->__('Beschreibung').'</label>
        <textarea id="pic_desc" class="inp">'.$stack->beschr.'</textarea>
    </p>';
    
    // get custom fields
    $ufields = $api->getFileFields();
    $ufields_output = '';
    if(count($ufields))
    {
        $cf = $base->db_to_array($stack->cf);
        
    	foreach($ufields as $k => $v)
    	{
    		if(!$v['name'])
    			continue;
                
            $ufields_output .= '
            <p>
                <label>'.$v['name'].'</label>';
                
			    if(!$v['type'] || $v['type'] == 'text' || $v['type'] == 'input')
				{
					$ufields_output .= '<input type="text" name="cf['.$k.']" value="'.$cf[$k].'" />';
				}
				elseif($v['type'] == 'textarea')
				{
					$ufields_output .= '<textarea name="cf['.$k.']">'.$cf[$k].'</textarea>';
				}
				elseif($v['type'] == 'checkbox')
				{
					$ufields_output .= '<input type="checkbox" name="cf['.$k.']" value="fks_true"'.($cf[$k] == 'fks_true'?' checked':'').' />';
				}
				elseif($v['type'] == 'select' && is_array($v['values']))
				{
					$ufields_output .= '<select name="cf['.$k.']">';
					foreach($v['values'] as $x => $y)
						$ufields_output .= '<option value="'.$x.'"'.($cf[$k] == $x?' selected':'').'>'.$y.'</option>';
					$ufields_output .= '                     
					</select>';
				}
                
			$ufields_output .= '
            </p>';
        }
       
        if($ufields_output)
        {
            echo '
            <form class="ufields">
                '.$ufields_output.'
            </form>';
        }
    } 
    
echo '
</div>

<div class="box_save" style="display:block;">
    <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
    <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
</div>';
?>
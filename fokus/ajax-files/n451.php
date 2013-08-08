<?php
if($suite->rm(8) && $user->r('dat', 'edit') && $index == 'n451') // Zeitsprung! jump jump jump!
{
    $id = $fksdb->save($_GET['id']);
    $v = $fksdb->save($_GET['v']);
    
    $stack = $fksdb->fetch("SELECT * FROM ".SQLPRE."files WHERE id = '".$id."' LIMIT 1");
    $aktuell = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");
    $file = ($v?$fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' AND id = '".$v."' LIMIT 1"):$aktuell);
    
    $dva = $fksdb->query("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC");
    $dvv = $fksdb->query("SELECT id FROM ".SQLPRE."file_versions WHERE timestamp < '".$file->timestamp."' AND stack = '".$stack->id."'");
    $dvo = $fksdb->query("SELECT id FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."'");
    
    $dv_vor = $fksdb->fetch("SELECT id FROM ".SQLPRE."file_versions WHERE timestamp < '".$file->timestamp."' AND stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");
    $dv_zurueck = $fksdb->fetch("SELECT id FROM ".SQLPRE."file_versions WHERE timestamp > '".$file->timestamp."' AND stack = '".$stack->id."' ORDER BY timestamp ASC LIMIT 1");
    
    $allev = $fksdb->count($dva);
    $aktuellev = $fksdb->count($dvv) + 1;
    $onlinev = $fksdb->count($dvo);
        
    $original = '../content/uploads/'.$base->getFilePath($stack->kat).'/'.$file->file.'.'.$file->type;
    
    if($file->width < 640)
    {
        $margin_right = 640 - $file->width;
        $nwidthL = 670 - $margin_right;
        $nwidthP = 640 - $margin_right;
    }
    
    $internimage = DOMAIN.'/img/'.$stack->id.'-'.($file->width > 640?'640':'0').'-0-~fids~'.$file->id.'~fide~'.Strings::createID().'.'.$file->type;
    
    echo '
    <input type="hidden" id="zdateistack" value="'.$stack->id.'" />
    
    <div class="zp">
        <div class="zpL"'.($nwidthL?' style="width:'.$nwidthL.'px; margin-left:'.$margin_right.'px;"':'').'>
            <div id="pcontent"'.($nwidthP?' style="width:'.$nwidthP.'px;"':'').'>
                <img src="'.$internimage.'" alt="'.$stack->titel.'" />
            </div>
        </div>
        <div class="zpR">
            
            <table>
                <tr>
                    <td colspan="2"><h3 class="calibri">'.Strings::cutWords($stack->titel, 36).'</h3></td>
                </tr>
                <tr>
                    <td colspan="2" class="bschr calibri">'.$stack->beschr.'</td>
                </tr>
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
            
            <p>
                '.($user->r('dat', 'ver')?'
                <button class="new_version" data-id="'.$stack->id.'" data-images="1">
                    '.$trans->__('Neue Version hochladen').'
                </button>
                ':'').'
                
                '.($suite->rm(1)?'<button id="n460" class="inc_files" data-file="'.$stack->id.'" data-file_version="'.$file->id.'">'.$trans->__('Version bearbeiten').'</button>':'').'
                '.($user->r('dat', 'del')?'<button id="zs_del_auswahl">'.$trans->__('Version löschen').'</button>':'').'
            </p>
            
            <a class="vor loadit" rel="'.$dv_vor->id.'" title="'.$trans->__('Eine ältere Version laden').'"'.($aktuellev == 1?' style="display:none;"':'').'></a>
            <a class="zurueck loadit" rel="'.$dv_zurueck->id.'" title="'.$trans->__('Eine neuere Version laden').'"'.($aktuellev == $allev?' style="display:none;"':($aktuellev == 1?' style="padding-top:55px;"':'')).'></a>
        </div>
    </div>
    
    <div class="short">
        <h2>
            '.$trans->__('Version %1 vom %2', false, array(
                $aktuellev,
                date('d.m.Y', $file->timestamp)
            )).'
        </h2>
        <p>
            '.$trans->__('Erstellt von %1', false, array($base->user($file->autor, ' ', 'vorname', 'nachname'))).'
            
            '.($file->id != $aktuell->id?
            '<span>'.$trans->__('<strong>HINWEIS: Diese Version ist momentan nicht online.</strong> Version %1 ist momentan online!', false, array($onlinev)).'</span>'
            :'').'
        </p>
    </div>
    '.($file->id != $aktuell->id?'
    <div class="laden">
        <span>
            <strong>'.$trans->__('Möchten Sie Version %1 wieder zur Bearbeitung laden?', false, array($aktuellev)).'</strong> 
        </span>
        <button>'.$trans->__('Version %1 wiederherstellen', false, array($aktuellev)).'</button>
    </div>':'').'
    <input type="hidden" id="zp_alteversion" value="'.$file->id.'" />
    
    <div class="historie">
        <h2>'.$trans->__('Übersicht &amp; Historie').'</h2>
        
        <table>';
            while($da = $fksdb->fetch($dva))
            {
                $version = $allev - $counter;
                
                echo '
                <tr class="bg_'.$base->countup().''.($da->id == $file->id?' aktiv':'').'">
                    <td>
                        <a class="loadit" rel="'.$da->id.'">
                            <strong>'.$trans->__('Version %1', false, array($version)).'</strong>
                        </a>
                    </td>
                    <td>
                        '.$trans->__('vom %1', false, array(date('d.m.Y', $da->timestamp))).'
                    </td>
                    <td>
                        '.$trans->__('Erstellt von %1', false, array($base->user($da->autor, ' ', 'vorname', 'nachname'))).'
                    </td>
                    <td class="last">
                        '.($da->id == $file->id?
                            $trans->__('zur Ansicht ausgewählt')
                            :
                            '<a class="loadit" rel="'.$da->id.'">'.$trans->__('Diese Version ansehen').'</a>'
                        ).'
                    </td>
                </tr>';
                    
                $counter ++;   
            }
        echo '
        </table>
    </div>';
}
?>
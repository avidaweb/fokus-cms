<?php
if($index == 'n400_preview' && $user->r('dat'))
{
    $id = $fksdb->save($_GET['id']);
    $count = $fksdb->save($_GET['count']);
    
    $ergebnis = $fksdb->query("SELECT * FROM ".SQLPRE."files WHERE id = '".$id."' LIMIT 1");
    while($stack = $fksdb->fetch($ergebnis))
    {
        if(!$stack->isdir)
        {
            if($stack->kat == 0 || $stack->kat == 2)
                $user->lastUse('bild', $stack->id);
            
            $row = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");     
            $ver = $fksdb->query("SELECT id FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."'");
            
            $nopic = false;
        
            if($stack->kat == 2)
            {
                $thumb = 'images/icons/32'.$base->getFileTypeThumbnail($row->type).'.jpg';
                
                $roles = $base->db_to_array($stack->roles);
            }
            else
            {
                $check_typ = array('jpg', 'jpeg', 'png', 'pneg', 'gif');
                if(!in_array(strtolower($row->type), $check_typ))
                    continue;
                
                $thumb = DOMAIN.'/img/'.$stack->id.'-220-0-'.$base->slug($stack->titel).'.'.$row->type.'?version='.$row->id;
                $imagesize = (intval(ini_get('allow_url_fopen')) == 0?array():getimagesize($thumb));
                
                if(!file_exists(ROOT.'content/uploads/bilder/'.$row->file.'.'.$row->type) && !is_array($imagesize))
                {
                    $nopic = true;
                    $thumb = DOMAIN.'/fokus/images/warning.png';
                }
            }
            
            if($row->width > 800)
            {
                $twidth = 800;
                $thumb_800 = DOMAIN.'/img/'.$stack->id.'-800-0-'.$base->slug($stack->titel).'.'.$row->type.'?version='.$row->id;
            }
            else
            {
                $twidth = $row->width;
                $thumb_800 = DOMAIN.'/img/'.$stack->id.'-0-0-'.$base->slug($stack->titel).'.'.$row->type.'?version='.$row->id;
            }
            
            $titel = explode('.', $stack->titel);
            $ende = $base->slug($titel[0]).'.'.$stack->last_type;
            
            echo ($count == 1 && $stack->kat != 2 && !$nopic?'
            <a class="bigimg">
                <img src="'.$thumb.'" alt="'.$stack->titel.'" width="220" />
            </a>':'').'
            
            '.($nopic?'
            <div class="ifehler">
                '.$trans->__('Die zugehörige Bilddatei wurde auf dem Server nicht gefunden. Bitte laden Sie eine neue Version hoch oder entfernen Sie das Bild.').'
            </div>':'').'
            
            <input type="hidden" id="dateiid" value="'.$row->id.'" />
            <input type="hidden" id="dateistack" value="'.$row->stack.'" />
            <input type="hidden" id="dateibig" value="'.$thumb_800.'" />
            <input type="hidden" id="dateiwidth" value="'.(46 + $twidth).'" />
            
            <table>
                <tr>
                    <td colspan="2">
                        <h3 class="calibri">
                            '.($count == 1?
                                Strings::cutWords($stack->titel, 36)
                                :
                                ($stack->kat == 0?
                                    $trans->__('%1 Bilder gewählt', false, array($count))
                                    :
                                    $trans->__('%1 Dateien gewählt', false, array($count))
                                ).''
                            ).'
                        </h3>
                    </td>
                </tr>
                '.($count == 1?'
                <tr>
                    <td colspan="2" class="bschr calibri">
                        '.$stack->beschr.'
                    </td>
                </tr>
                <tr>
                    <td>'.$trans->__('ID').'</td>
                    <td>'.$stack->id.'</td>
                </tr>
                '.($stack->kat == 2?'
                <tr>
                    <td>'.$trans->__('Freigabe').'</td>
                    <td>
                        '.(count($roles)?
                            '<span class="va_no">
                                '.$trans->__('Eingeschränkt').'
                                ('.count($roles).')
                            </span>'
                            :
                            '<span class="va_yes">
                                '.$trans->__('Öffentlich').'
                            </span>'
                        ).'
                    </td>
                </tr>
                <tr>
                    <td>'.$trans->__('Downloads').'</td>
                    <td>'.$stack->downloads.'</td>
                </tr>
                ':'').'
                '.($stack->kat == 2 && $fksdb->count($ver) > 1?'
                <tr>
                    <td>'.$trans->__('Downloads').'<br /><em>'.$trans->__('(letzte Version)').'</em></td>
                    <td>'.$row->downloads.'</td>
                </tr>
                ':'').'
                '.($stack->kat < 2?'
                <tr>
                    <td>'.$trans->__('Abmessungen').'</td>
                    <td>'.$row->width.' x '.$row->height.' '.$trans->__('Pixel').'</td>
                </tr>
                ':'').'
                <tr>
                    <td>'.$trans->__('Dateityp').'</td>
                    <td>'.$row->type.'</td>
                </tr>
                <tr>
                    <td>'.$trans->__('Autor').'</td>
                    <td>'.$base->user($row->autor, ' ', 'vorname', 'nachname').'</td>
                </tr>
                <tr>
                    <td>'.$trans->__('Version-Nr').'</td>
                    <td>'.($fksdb->count($ver)).'</td>
                </tr>
                '.($stack->kat < 2?'
                <tr>
                    <td>'.$trans->__('Datei-Pfad').'</td>
                    <td><a href="'.$domain.'/img/'.$stack->id.'-0-0-'.$ende.'" target="_blank">'.$trans->__('öffnen').'</a></td>
                </tr>
                ':'').'
                ':'').'
            </table>
            
            <span class="trenn"></span>';
            
            if($stack->kat == 0)
            {
                echo '
                '.($count == 1?'
                    '.($suite->rm(8) && $user->r('dat', 'edit')?
                    '<button id="n450" class="inc_files"'.($fksdb->count($ver) < 2?' style="display:none;"':'').'>
                        '.$trans->__('Alle Versionen ansehen').'
                        <a rel="'.$stack->id.'"></a>
                    </button>':'').'
                    
                    '.($user->r('dat', 'ver')?'
                    <button class="new_version" data-id="'.$stack->id.'" data-images="1">
                        '.$trans->__('Neue Version hochladen').'
                    </button>
                    ':'').'
                    
                    '.($user->r('dat', 'edit')?'
                    <button id="n460" class="inc_files" data-file="'.$stack->id.'" data-file_version="'.$row->id.'">
                        '.$trans->__('Bild bearbeiten').'
                    </button>':'').'
                    
                    '.($user->r('dat', 'edit')?
                    '<button id="move_in_dir" data-id="'.$stack->id.'">
                        '.$trans->__('Bild verschieben').'
                    </button>':'').'
                    
                    <span class="trenn"></span>  
                             
                    <button id="download">
                        '.$trans->__('Bild herunterladen').'
                    </button>
                ':'
                    '.($user->r('dat', 'edit')?
                    '<button id="move_in_dir">
                        '.$trans->__('Bilder-Auswahl verschieben').'
                    </button>':'').'
                    
                    '.(class_exists('ZipArchive')?'
                    <button id="download">
                        '.$trans->__('Bilder-Auswahl herunterladen').'
                    </button>':'').'
                ').
                ($user->r('dat', 'del')?'
                <button id="del_auswahl">
                    '.($count == 1?
                        $trans->__('Bild löschen')
                        :
                        $trans->__('Bilder-Auswahl löschen')
                    ).' 
                </button>':'');
            }
            else
            {
                echo '
                '.($count == 1?'
                '.($user->r('dat', 'ver')?'
                <button class="new_version" data-id="'.$stack->id.'" data-images="0">
                    '.$trans->__('Neue Version hochladen').'
                </button>
                <span class="trenn"></span>':'').'
                <button id="download">'.$trans->__('Datei herunterladen').'</button>
                ':'').'
                '.($user->r('dat', 'del')?'
                <button id="del_auswahl">
                    '.($count == 1?
                        $trans->__('Datei löschen')
                        :
                        $trans->__('Datei-Auswahl löschen')
                    ).' 
                </button>':'').'
                
                '.($user->r('dat', 'edit')?'
                <button id="set_rights">
                    '.($count == 1?
                        $trans->__('Datei-Rechte setzen')
                        :
                        $trans->__('Dateien-Rechte setzen')
                    ).' 
                </button>':'');
            }
        }
        else
        {
            $unter_files = $fksdb->query("SELECT id FROM ".SQLPRE."files WHERE dir = '".$stack->id."' AND isdir = '0'");
            $unter_ordner = $fksdb->query("SELECT id FROM ".SQLPRE."files WHERE dir = '".$stack->id."' AND isdir = '1'");
    
            echo '
            <a class="bigimg">
                <img src="images/folder'.$add_to_pic[$fksdb->count($unter_ordner)].'.png" alt="'.$stack->titel.'" width="180" />
            </a>
            
            <input type="hidden" id="dateistack" value="'.$stack->id.'" />
            <input type="hidden" id="dateistack_titel" value="'.$stack->titel.'" />
            <input type="hidden" id="v_isdir" value="1" />
            
            <table>
                <tr>
                    <td colspan="2"><h3 class="calibri">'.Strings::cutWords($stack->titel, 36).'</h3></td>
                </tr>
                <tr>
                    <td>'.$trans->__('Anzahl Dateien').'</td>
                    <td>'.$fksdb->count($unter_files).'</td>
                </tr>
                <tr>
                    <td>'.$trans->__('Anzahl Unterordner').'</td>
                    <td>'.$fksdb->count($unter_ordner).'</td>
                </tr>
                <tr>
                    <td>'.$trans->__('Autor').'</td>
                    <td>'.$base->user($stack->last_autor, ' ', 'vorname', 'nachname').'</td>
                </tr>
                <tr>
                    <td>'.$trans->__('Datum').'</td>
                    <td>'.date('d.m.Y', $stack->last_timestamp).'</td>
                </tr>
            </table>
            
            <button id="ordner_oeffnen">'.$trans->__('Ordner öffnen').'</button>
            <button id="download" data-dir="'.$stack->id.'">'.$trans->__('Ordner herunterladen').'</button>';
        }
    }
}
?>
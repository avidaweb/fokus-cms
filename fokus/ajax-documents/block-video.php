<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

function video_optionen($trans, $row, $s, $uindiv)
{
    if(!$row->bild)
    { 
        $row->bildw = 100;
        $row->bildh = 0;
        $row->bildwt = 1;
        $row->bildp = 3;
    }
    
    if(!$row->bildh)
        $row->bildh = 300;
    
    $rtn = '
    <table>
        <tr>
            <td>'. $trans->__('Breite des Videos') .'</td>
            <td class="t2">
                <input type="text" name="bildw'.$s.'" class="bild_w" value="'.$row->bildw.'" /> 
                <span>/</span>
                
                <select name="bildwt'.$s.'" class="bildwts">
                    <option value="0"'.($row->bildwt == 0?' selected="selected"':'').'>Pixel</option>
                    <option value="1"'.($row->bildwt == 1?' selected="selected"':'').'>Prozent</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>'. $trans->__('Höhe des Videos') .'</td>
            <td class="t2">
                <input type="text" name="bildh'.$s.'" class="bild_hT" value="'.$row->bildh.'" />
                Pixel
            </td>
        </tr>
        <tr>
            <td>'. $trans->__('Wo soll das Video sein?') .'</td>
            <td class="t2">
                <select name="bildp'.$s.'">
                    <option value="0"'.($row->bildp == 0?' selected="selected"':'').'>'. $trans->__('Links') .'</option>
                    <option value="1"'.($row->bildp == 1?' selected="selected"':'').'>'. $trans->__('Rechts') .'</option>
                    <option value="2"'.($row->bildp == 2?' selected="selected"':'').'>'. $trans->__('Zentriert') .'</option>
                    <option value="3"'.($row->bildp == 3?' selected="selected"':'').'>'. $trans->__('Bündig') .'</option>
                </select>
            </td>
        </tr> 
    </table>';
    return $rtn;
}

echo '
<form method="post" id="add_pic_form">
<div class="box text_bild">
    <div class="tbL">
        <strong>'. $trans->__('Video anzeigen') .'</strong><br />
        <span class="bildgr"></span>
    </div>
    <div class="tbR">
        
        <div class="tbRa">
            <div class="tbRa">
                <input type="hidden" name="bild" id="textbild_extern" value="2" checked /> 
                <label>'. $trans->__('Video aus externer Quelle einbinden') .'</label>
                
                <div class="choosebild choosebild2">
                    <p class="bauswahl">
                        URL: <input type="text" name="bild_extern" id="bild_extern" value="'.$row->bild_extern.'" /> 
                        <br /><br />'. $trans->__('Momentan werden folgende Portale unterstützt:') .'<br />
                        
                        <a href="http://www.youtube.com" target="_blank" rel="nofollow">'. $trans->__('Youtube') .'</a>,
                        <a href="http://vimeo.com/" target="_blank" rel="nofollow">'. $trans->__('Vimeo') .'</a>,
                        <a href="http://www.clipfish.de/" target="_blank" rel="nofollow">'. $trans->__('Clipfish') .'</a>,
                        <a href="http://www.dailymotion.com" target="_blank" rel="nofollow">'. $trans->__('Dailymotion') .'</a> &amp;
                        <a href="http://www.myvideo.de" target="_blank" rel="nofollow">'. $trans->__('MyVideo') .'</a>
                    </p>
                    '.video_optionen($trans, $row, 2, $user->getIndiv()).'
                </div>
            </div>
        </div>
    </div>
</div>
</form>';
?>
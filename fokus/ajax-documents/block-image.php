<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

function bild_optionen($trans, $user, $row, $s, $attr)
{
    if(!$row->bild)
    { 
        $indiv = $user->getIndiv();
        
        if($row->type != 15)
        {
            $row->bildw = ($indiv->saved?$indiv->bild_w:100);
            $row->bildh = ($indiv->saved?$indiv->bild_h:0);
            $row->bildwt = ($indiv->saved?$indiv->bild_wt:1);
            $row->bildp = ($indiv->saved?$indiv->bild_p:3);
        }
        else
        { 
            $row->bildw = ($indiv->saved?$indiv->bildt_w:100);
            $row->bildh = ($indiv->saved?$indiv->bildt_h:0);
            $row->bildwt = ($indiv->saved?$indiv->bildt_wt:1);
            $row->bildp = ($indiv->saved?$indiv->bildt_p:2);
        }
    }
    
    
    $auto = array(
        'width' => false,
        'height' => false,
        'dimension' => false,
        'align' => false
    );
    
    if(count($attr))
    {
        if(isset($attr['width']))
        {
            $row->bildw = intval($attr['width']);
            $auto['width'] = true;
            $auto['height'] = true;
        }
        if(isset($attr['height']))
        {
            $row->bildh = intval($attr['height']);
            $auto['height'] = true;
            $auto['width'] = true;
        }
        if(isset($attr['dimension']))
        {
            $row->bildwt = ($attr['dimension'] == 'original'?2:($attr['dimension'] == 'percent'?1:0));
            $auto['dimension'] = true;
        }
        if(isset($attr['align']))
        {
            $row->bildp = ($attr['align'] == 'center'?2:($attr['align'] == 'right'?1:($attr['align'] == 'block'?3:0)));
            $auto['align'] = true;
        }
    }
    
    $rtn = '
    <table>
        '.($auto['width'] || $auto['height'] || $auto['dimension'] || $auto['align']?' 
        <tr>
            <td colspan="2">
                <em>'. $trans->__('Eigenschaften aus Dokumentenklasse werden verwendet') .'</em>
            </td>
        </tr>
        ':'').'
        <tr>
            <td>Größe des Bildes</td>
            <td class="t2">
                <input type="text" name="bildw'.$s.'" class="bild_w" value="'.$row->bildw.'"'.($row->bildwt >= 2?' style="display:none;"':'').''.($auto['width']?' disabled':'').' /> 
                <input type="text" name="bildh'.$s.'" class="bild_h" value="'.$row->bildh.'"'.($row->bildwt >= 1?' style="display:none;"':'').''.($auto['height']?' disabled':'').' />
                <span'.($row->bildwt >= 2?' style="display:none;"':'').'>/</span>
                
                <select name="bildwt'.$s.'" class="bildwts"'.($auto['dimension']?' disabled':'').'>
                    <option value="0"'.($row->bildwt == 0?' selected':'').'>'. $trans->__('Pixel') .'</option>
                    <option value="1"'.($row->bildwt == 1?' selected':'').'>'. $trans->__('Prozent') .'</option>
                    <option value="2"'.($row->bildwt == 2?' selected':'').'>'. $trans->__('Originalgröße') .'</option>
                </select>
            </td>
        </tr>
        <tr>
            <td>Wo soll das Bild sein?</td>
            <td class="t2">
                <select name="bildp'.$s.'"'.($auto['align']?' disabled':'').'>
                    '.($row->type == 15?'
                    <option value="0"'.($row->bildp == 0?' selected':'').'>'. $trans->__('Links im Text') .'</option>
                    <option value="1"'.($row->bildp == 1?' selected':'').'>'. $trans->__('Rechts im Text') .'</option>
                    <option value="2"'.($row->bildp == 2?' selected':'').'>'. $trans->__('Über dem Text') .'</option>
                    ':'
                    <option value="0"'.($row->bildp == 0?' selected':'').'>'. $trans->__('Links') .'</option>
                    <option value="1"'.($row->bildp == 1?' selected':'').'>'. $trans->__('Rechts') .'</option>
                    <option value="2"'.($row->bildp == 2?' selected':'').'>'. $trans->__('Zentriert') .'</option>
                    <option value="3"'.($row->bildp == 3?' selected':'').'>'. $trans->__('Bündig') .'</option>
                    ').'
                </select>
            </td>
        </tr> 
    </table>';
    
    return $rtn;
}

$stack = $fksdb->fetch("SELECT * FROM ".SQLPRE."files WHERE id = '".$row->bildid."' LIMIT 1");
$file = $fksdb->fetch("SELECT * FROM ".SQLPRE."file_versions WHERE stack = '".$row->bildid."' ORDER BY timestamp DESC LIMIT 1");

if($stack->papierkorb)
{
    echo '
    <div class="box fehlerbox">
        <strong>'. $trans->__('Das gewählte Bild wurde mittlerweile in den Papierkorb verschoben') .'</strong>
    </div>';
}

$bildlink = $base->db_to_array($row->bildt);

$internpic = ($stack->id?DOMAIN.'/img/'.$stack->id.'-200-0-'.$base->slug($stack->titel).'.'.$file->type:'');

echo '
<form method="post" id="add_pic_form">
<div class="box text_bild">
    <div class="tbL">
        <'.($row->type == 15?'h2 class="calibri"':'strong').'>
            Bild anzeigen
        </'.($row->type == 15?'h2':'strong').'>

        <img id="preview_picture"'.(!$row->bild?' style="display:none;"':'').' src="'.($row->bild_extern?$row->bild_extern:$internpic).'" alt="Kein Bild geladen" />

        <br />
        <span class="bildgr"></span>
    </div>
    <div class="tbR">
        '.($row->type == 15?'
        <div class="tbRa">
            <input type="radio" name="bild" id="textbild_no" value="0"'.(!$row->bild?' checked="checked"':'').' />
            <label for="textbild_no">'. $trans->__('kein Bild anzeigen') .'</label>
        </div>':'').'

        <div class="tbR'.($row->type == 15?'b':'a').'">
            <input type="radio" name="bild" id="textbild_yes" value="1"'.($row->bild == 1 || $row->type != 15?' checked="checked"':'').' />
            <label for="textbild_yes">'. $trans->__('Bild aus Bildverwaltung wählen') .'</label>

            <div class="choosebild choosebild1"'.($row->bild != 1 && ($row->bild == 2 || $row->type == 15)?' style="display:none;"':'').'>
                <p class="bauswahl">
                    <button id="getoldpic" class="'.($row->type == 36?'2':'1').'">'. $trans->__('Bild auswählen') .'</button>
                    '.($user->r('dat', 'new')?'
                    <button id="getnewpic">'. $trans->__('Bild hochladen') .'</button>
                    ':'').'
                    '.($user->r('dat', 'edit')?'
                    <button class="edit_current_pic" data-file="'.$row->bildid.'" data-file_version="0"'.($row->bild != 1 || !$row->bildid?' style="display:none;"':'').'>
                        '.$trans->__('Bild bearbeiten').'
                    </button>':'').'

                    <input type="hidden" id="ins_bild_id" value="'.$row->bildid.'" name="bildid" />
                    <span id="ins_bild_titel">'.($row->bildid?$stack->titel:'').'</span>
                </p>
                '.bild_optionen($trans, $user, $row, '', $dclass_block['attr']).'
            </div>
            '.($row->type != 15?'
            <div class="tbRb">
                <input type="radio" name="bild" id="textbild_extern" value="2"'.($row->bild == 2?' checked="checked"':'').' />
                <label for="textbild_extern">'. $trans->__('Bild aus externe Quelle wählen') .'</label>

                <div class="choosebild choosebild2"'.($row->bild != 2?' style="display:none;"':'').'>
                    <p class="bauswahl">
                        URL: <input type="text" name="bild_extern" id="bild_extern" value="'.$row->bild_extern.'" />
                    </p>
                    '.bild_optionen($trans, $user, $row, 2, $dclass_block['attr']).'
                </div>
            </div>':'').'
            <div class="bild_verlinken"'.(!$row->bild && $row->type < 30?' style="display:none;"':'').'>
                <input type="checkbox" id="piclinkit" name="bildt" value="1"'.($bildlink['href']?' checked="checked"':'').' />
                <label for="piclinkit">'. $trans->__('Bild verlinken ') .'</label>

                <p'.(!$bildlink['href']?' style="display:none;"':'').'>
                    <button id="linkoptionen">'. $trans->__('Linkoptionen') .'</button>
                    <input type="hidden" name="link_href" value="'.$bildlink['href'].'" />
                    <input type="hidden" name="link_ziel" value="'.$bildlink['ziel'].'" />
                    <input type="hidden" name="link_power" value="'.$bildlink['power'].'" />
                    <input type="hidden" name="link_titel" value="'.$bildlink['titel'].'" />
                    <input type="hidden" name="link_klasse" value="'.$bildlink['klasse'].'" />
                </p>
            </div>
        </div>
    </div>
</div>
</form>';
?>
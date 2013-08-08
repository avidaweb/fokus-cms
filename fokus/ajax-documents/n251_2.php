<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251')
    exit($user->noRights());
    
$sizeable = 0;
$closed = 0;

if(!isset($id))
    $id = 0;

$sortierung = 0;
$ergebnis2 = $fksdb->query("SELECT id, closed FROM ".SQLPRE."columns WHERE dokument = '".intval($id)."' AND dversion = '".$dve->id."' ORDER BY sort");
while($doc2 = $fksdb->fetch($ergebnis2))
{
    $sortierung ++;
    $update = $fksdb->query("UPDATE ".SQLPRE."columns SET sort = '".$sortierung."' WHERE id = '".$doc2->id."'");
    
    if($fksdb->count($ergebnis2) == 1)
        $update = $fksdb->query("UPDATE ".SQLPRE."columns SET size = '100' WHERE id = '".$doc2->id."'");
        
    if(!$doc2->closed)
        $sizeable ++;
    else
        $closed ++;
}

$ergebnis2 = $fksdb->query("SELECT id, size, closed FROM ".SQLPRE."columns WHERE dokument = '".$id."' AND dversion = '".$dve->id."' ORDER BY sort");
$anzahl = $fksdb->count($ergebnis2);

$magic_number = 27;

echo '
<div id="spalten">';

if($anzahl)
{
    echo '
    <div class="add">
        '.($anzahl != $closed?'<div class="neu" title="'.$trans->__('Neue Spalte anlegen').'"></div>':'').'
    </div>';
    
    $first_neu_width = round($magic_number / $anzahl);
}

$first_neu_width = 0;
$countup = 0;
$last_one = 0;
$gesamt = 0;

while($doc2 = $fksdb->fetch($ergebnis2))
{
    $size = floor((860 * $doc2->size / 100));
    $real_size = $size - $first_neu_width - $magic_number - 2;
    
    if($ueberschuss)
    {
        $real_size -= $ueberschuss;
        $ueberschuss = 0;
    }
    if($real_size < 1)
    {
        $ueberschuss = 1 - $real_size;
        $real_size = 1;
    }
        
    $countup ++;
    
    $breite = number_format($doc2->size, 1, ',', '');
    
    echo '
    '.($countup != 1?'<input type="hidden" class="hspalte" value="'.$last_one.'" />':'').'
    
    <div class="spalte" data-id="'.$doc2->id.'" style="width:'.$real_size.'px;">
        <p class="move" title="'.$trans->__('Spalte verschieben').'"'.($anzahl == 1?' style="display:none;"':'').'></p>
        
        <strong title="'.$trans->__('Hier klicken, um Größe der Spalte zu verändern').'">'.$breite.'%</strong>
        <input type="text" value="'.$breite.'" data-id="'.$doc2->id.'" />
        
        <div class="plusminus"'.($sizeable < 2?' style="display:none;"':'').'>
            <a class="minus" title="'.$trans->__('Spalte um einen Prozentpunkt vergrößern').'" data-id="'.$doc2->id.'"'.($doc2->closed?' style="visibility:hidden;"':'').'>-</a>
            <a class="plus" title="'.$trans->__('Spalte um einen Prozentpunkt verkleinern').'" data-id="'.$doc2->id.'"'.($doc2->closed?' style="visibility:hidden;"':'').'>+</a>
        </div>
        
        <a class="close '.($doc2->closed?'closed':'open').'" data-id="'.$doc2->id.'" title="'.($doc2->closed?'Fixierung der Spalte aufheben':'Spalte fixieren').'"'.($anzahl == 1?' style="display:none;"':'').'></a>
        
        <p class="column-options">
            <a>Optionen</a>
            <span title="">
                <a data-id="'.$doc2->id.'" class="format" title="">'.$trans->__('Spalte formatieren').'</a>
                <a data-id="'.$doc2->id.'" class="delete" title=""'.($doc2->closed || $anzahl == 1?' style="display:none;"':'').'>'.$trans->__('Spalte entfernen').'</a>
            </span>
        </p>
        
        '.($doc2->closed?'<div class="closedinfo">'. $trans->__('Spalte ist gesperrt') .'</div>':'').'
    </div>
    
    <div class="add">
        '.($anzahl != $closed?'<div class="neu" title="'.$trans->__('Neue Spalte anlegen').'"></div>':'').'
    </div>';
    
    $gesamt += $doc2->size;
    $last_one = $doc2->size;
}

if(!$anzahl)
    echo '<div class="neu" id="firstneu" title="'.$trans->__('Neue Spalte anlegen').'"></div>';
    
echo '
</div>

<div id="spalten_slider"'.($sizeable < 2?' style="display:none;"':'').'></div>

<button id="all_same_size"'.($sizeable < 2?' style="display:none;"':'').'>'. $trans->__('Allen Spalten auf die selbe Breite skalieren') .'</button>

<p id="new_spalten_size">'. $trans->__('Diese Spalte bekommt <span>0</span>%</p>');

if($anzahl && $anzahl == $closed)
{
    echo '
    <div class="ifehler">
        <strong>'. $trans->__('Alle vorhandenen Spalten sind fixiert.') .'</strong>
        '. $trans->__('Da alle existierenden Spalten als über das Schloss-Symbol fixiert wurden,<br /> können keine neuen Spalten angelegt werden oder bestehende Spaltengrößen verändert werden.') .'
    </div>';
}
?>
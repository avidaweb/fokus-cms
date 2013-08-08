<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n265')
    exit($user->noRights());

function elemente($base, $fksdb, $parent, $ebene)
{
    $ergebnis = $fksdb->query("SELECT titel, id FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' AND element = '".$parent."' AND klasse = '' AND papierkorb = '0' ORDER BY sort");
    while($row = $fksdb->fetch($ergebnis))
    {
        echo '
        <div class="ebene_'.$ebene.' elemente" id="'.$row->titel.'_'.$row->id.'">
            <div class="own el_'.$ebene.'">
                <div class="kopp"><span>'.$row->titel.'</span></div>
            </div>';

            $newebene = $ebene + 1;
            elemente($base, $fksdb, $row->id, $newebene);

        echo '</div>';
    }
}

echo '
<h1>'. $trans->__('Strukturelement auswählen.') .'</h1>

<div class="box slayout">
    <div class="ebene_-1 elemente">';
        elemente($base, $fksdb, 0, 0);
    echo '</div>
</div>

<div class="box_save">
    <input type="button" value="'. $trans->__('verwerfen') .'" class="bs1" /> <input type="button" value="'. $trans->__('speichern') .'" class="bs2" />
</div>';
?>
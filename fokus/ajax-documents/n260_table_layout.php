<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n260_table_layout')
    exit($user->noRights());

parse_str($_POST['f'], $f);

echo '
<div class="auswahl">';
    for($y = 1; $y <= 50; $y ++)
    {
        for($x = 1; $x <= 25; $x ++)
        {
            echo '<span title="'.$x.'x'.$y.'" data-x="'.$x.'" data-y="'.$y.'"'.($f['x'] >= $x && $f['y'] >= $y?' class="aktiv"':'').'></span>';
            if($x == 25)
                echo '<p></p>';
        }
    }
echo '
</div>

<div class="info">
    <h3 class="spalten">'. $trans->__('<span>%1</span> Spalten', false, array(intval($f['x']))) .'</h3>
    <h3 class="zeilen">'. $trans->__('<span>%1</span> Zeilen', false, array(intval($f['y']))) .'</h3>

    <p>
        '. $trans->__('Klicken Sie auf eines der Felder, um die Anzahl der Spalten und Zeilen zu bestimmen. Im nächsten Schritt bearbeiten Sie die Inhalte ihrer Tabelle.') .'
    </p>
</div>';
?>
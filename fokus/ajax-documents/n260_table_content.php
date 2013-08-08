<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n260_table_content')
    exit($user->noRights());

parse_str($_POST['f'], $f);
$ox = intval($f['x']);
$oy = intval($f['y']);
$v = $base->db_to_array($f['value']);

echo '
<table>';
    for($y = 1; $y <= $oy; $y ++)
    {
        echo '<tr>';
        for($x = 1; $x <= $ox; $x ++)
        {
            echo '
            <td data-x="'.$x.'" data-y="'.$y.'">
                '.($v[$y][$x]?Strings::cut(rawurldecode(strip_tags(htmlspecialchars_decode(Strings::cleanString($v[$y][$x])))), 100):'<span>(kein Inhalt)</span>').'
            </td>';
        }
        echo '</tr>';
    }
echo '
</table>';
?>
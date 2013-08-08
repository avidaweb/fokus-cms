<?php
define('IS_BACKEND_DEEP', true, true);

require_once('../../inc/header.php');
require_once('../login.php');

$i = $user->getIndiv();
$workspace_count = max(1, $i->workspaces);

echo '
<div class="menu">
    <a>'.$trans->__('Dashboard & Apps').'</a>
</div>

<div class="workspace">';

    for($w = 0; $w < $workspace_count; $w ++)
        echo '<a data-nr="'.$w.'">'.$trans->__('Arbeitsbereich %1', false, array(($w + 1))).'</a>';

echo '
</div>';
?>
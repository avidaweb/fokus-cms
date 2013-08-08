<?php
define('IS_GHOST', true, true);

require('../../header.php');

if(!$user->isAdmin() || !$user->isGhost())
    exit('Keine Berechtigung');

echo '
<form action="'.DOMAIN.'/fokus/sub_ghost.php?index=s451" method="post">
<div class="fks_wrapper">
    <div class="fks_left" id="fks_ghost_topSpace">
        
    </div>
    <div id="fks_ghost_bottomSpace"></div>
    <div class="fks_right">
        <p class="first">
            <button class="close" title="Schließt die Website-Direktbearbeitung">schließen</button>
        </p>
        <p>
            <button class="save" data-task="save" title="Alle Dokumente, an denen Inhalte bearbeitet wurden, werden gespeichert und als bearbeitet markiert">
                <span class="jsave"></span>
                speichern
            </button>
            <button class="save" data-task="wait" title="Alle Dokumente, an denen Inhalte bearbeitet wurden, werden gespeichert und zur Freigabe vorgelegt">
                <span class="jwait"></span>
                speichern &amp; zur Freigabe vorlegen
            </button>
            '.($user->r('dok', 'publ')?'
            <button class="save" data-task="free" title="Alle Dokumente, an denen Inhalte bearbeitet wurden, werden gespeichert und direkt freigegeben">
                <span class="jfree"></span>
                speichern &amp; freigeben
            </button>':'').'
        </p>
    </div>
</div>
</form>';

?>
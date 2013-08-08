<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

echo '
<div class="box">
    <div id="editor">
        <textarea id="text" name="editor_text" class="text_'.$row->type.'">'.rawurldecode(Strings::cleanString($row->html)).'</textarea>
    </div>
    
    '.(($ibid || !$dokument->klasse) && ($cssf || $cssk)?'
    <div class="set_view">
        <a>'.$trans->__('Darstellung anpassen').'</a>
    </div>
    ':'').'
    
    '.(($ibid || !$dokument->klasse) && $row->type == 15?'
    <div class="lots_of_p">
        '.$trans->__('Sie verwenden viele Absätze in diesem Textblock. Möchten Sie das <a>Inhaltselement jetzt aufteilen</a>?').'
    </div>
    ':'').'
</div>';
?>
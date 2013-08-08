<?php
if($index == 's480') // Pinnwand
{    
    echo '
    <h1>'.$trans->__('Persönliche Notizen.').'</h1>
    
    <div id="pnotizen">
        <div class="box">
            <textarea>'.base64_decode($user->data('notiz')).'</textarea>
        </div>
        <div class="box_save">
            <input type="button" class="bs1" value="'.$trans->__('schließen').'" />
            <input type="button" class="bs2" value="'.$trans->__('speichern').'" />
        </div>
    </div>';
}
?>
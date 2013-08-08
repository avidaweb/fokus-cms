<?php
if($index == 'n406' && $user->r('dat'))
{
    $stacks = $fksdb->save($_REQUEST['stacks']);
    $stacks = trim(str_replace('datei_', '', $stacks));
    
    if(!$stacks)
        exit(); 
        
    $stacksQ = $fksdb->rows("SELECT id, titel, roles FROM ".SQLPRE."files WHERE id IN (".$stacks.")");
    if(!count($stacksQ))
        exit('<div class="ifehler">'.$trans->__('Keine Datei ausgewählt.').'</div>');
        
    $roles = $fksdb->rows("SELECT id, titel FROM ".SQLPRE."roles WHERE papierkorb = '0' ORDER BY sort, id");
    
    echo '
    <h1>'.$trans->__('Zugriffsrechte setzen.').'</h1>
    
    <div class="box">
    <form>';
    
        foreach($stacksQ as $stack)
        {
            $fr = $base->db_to_array($stack->roles); 
                
            echo '
            <article class="extoL2">
                <h2 class="calibri">'.$stack->titel.'</h2>
                
                <div class="sclasses">
                    <p>
                        <input type="checkbox" id="drol_-1'.$stack->id.'" name="roles['.$stack->id.'][]" value="-1"'.(in_array('-1', $fr)?' checked="checked"':'').' />
                        <label for="drol_-1'.$stack->id.'"><em>'.$trans->__('kein Kunde / nicht angemeldet').'</em></label>
                    </p>';
                        
                    foreach($roles as $r)
                    {
                        echo '
                        <p>
                            <input type="checkbox" id="drol_'.$r->id.$stack->id.'" name="roles['.$stack->id.'][]" value="'.$r->id.'"'.(in_array($r->id, $fr)?' checked="checked"':'').' />
                            <label for="drol_'.$r->id.$stack->id.'">'.$r->titel.'</label>
                        </p>';
                    }
                echo '
                </div>
            </article>';
        }
    
    echo '
    </form>
    </div>
    
    <div class="box_save" style="display:block;">
        <input type="submit" class="bs1" value="'.$trans->__('verwerfen').'" />
        <input type="submit" class="bs2" value="'.$trans->__('speichern').'" />
    </div>';
}
?>
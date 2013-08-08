<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

if(!$ex)
{
    echo '
    <div class="box_fehler">
        <strong>'. $trans->__('Diese Erweiterung existiert nicht mehr.') .'</strong>
    </div>';
}
else
{
    $cfunc = $ex->getWindow();
    $cdata = $base->db_to_array($row->extb_content); 
    $cdata = (object)$cdata;

    echo '
    <form id="extension_form">
        <div class="box" id="extension_block" data-width="'.$ex->getWidth().'" data-jscallback="'.$ex->getScriptCallback().'" data-csscallback="'.$ex->getCssCallback().'">
            <div id="block-'.$row->extb.'">';

                echo (is_callable($cfunc)?call_user_func($cfunc, $cdata, $classes):'').'
            </div>
        </div>
    </form>';      
}
?>
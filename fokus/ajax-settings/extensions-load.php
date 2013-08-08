<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('fks', 'extensions') || $index != 'extensions-load')
    exit($user->noRights());
    
$extensions = $api->getExtensions(false, true);
$active_count = 0;

foreach($extensions as $ext)
{ 
    if($ext['activated'])
        $active_count ++;   
}

echo '
<div class="box" id="extension-info">
    '.$trans->__('Es befinden sich momentan %1 Erweiterungen im System, wovon %2 aktiviert wurden.', false, array(count($extensions), $active_count)).'
</div>

<div class="box">';

    foreach($extensions as $ext)
    {
        $author = '';
        if(is_array($ext['config']['author']))
        {
            $author = trim($ext['config']['author']['name']);
            if($author && $ext['config']['author']['url'])
                $author = '<a href="'.trim($ext['config']['author']['url']).'" target="_blank">'.$author.'</a>';
        }
        
        $meta = '<strong>'.($ext['activated']?'<a class="action" data-action="deactivate" data-id="'.$ext['id'].'">'.$trans->__('deaktivieren').'</a>':'<a class="action" data-action="activate" data-id="'.$ext['id'].'">'.$trans->__('aktivieren').'</a>').'</strong> | ';
        
        $meta .= ($ext['config']['version']?' <span>'.$trans->__('Version:').'</span> '.$ext['config']['version'].' | ':'');
        $meta .= ($author?' <span>'.$trans->__('Autor:').'</span> '.$author.' | ':'');
        $meta .= ($ext['config']['license']?' <span>'.$trans->__('Lizenz:').'</span> '.$ext['config']['license'].' | ':'');
        
        $name = $ext['config']['name'];
        if($ext['config']['url'])
            $name = '<a href="'.trim($ext['config']['url']).'" target="_blank">'.$name.'</a>';
        
        echo '
        <div class="ext '.($ext['activated']?'activated':'deactivated').'">
            <div class="check">
                <input type="checkbox" id="ext_cb_'.$ext['id'].'" name="ext['.$ext['id'].']" value="1" />
            </div>
            
            <div class="r">
                '.(file_exists($api->getExtensionDir($ext['dir'], '/', false).'screenshot.jpg')?'
                <img src="'.$api->getExtensionDir($ext['dir']).'screenshot.jpg'.'" alt="'.$ext['config']['name'].'" />
                ':'').'
            
                <h2 class="calibri">'.$name.'</h2>
                
                <p class="descr">'.$ext['config']['description'].'</p>
                <div class="meta">'.trim($meta, ' | ').'</div>
            </div>
        </div>';   
    }
    
echo '
</div>';
?>
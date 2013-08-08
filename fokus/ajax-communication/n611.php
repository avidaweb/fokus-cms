<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'nledit') && !$user->r('kom', 'nlsend'))
    exit($user->noRights());
    
if(!$suite->rm(5) || $index != 'n611') 
    exit($user->noRights());
    
$kkQ = $fksdb->query("SELECT id, titel, last_send, send FROM ".SQLPRE."newsletters ORDER BY id DESC");
while($n = $fksdb->fetch($kkQ))
{
    if($n->send == 0) 
        $ls = $trans->__('noch nicht gesendet');
    elseif($n->send == 1) 
        $ls = $trans->__('<strong>1</strong> mal gesendet am %1', false, array(date('d.m.Y', $n->last_send)));
    else 
        $ls = $trans->__('<strong>%1</strong> mal gesendet, zuletzt am %2', false, array($n->send, date('d.m.Y', $n->last_send)));
    
    echo '
    <div class="spalte">
        <p class="mail"><img src="images/mail.png" alt="" height="22" /></p>
        <p class="titel">'.($user->r('kom', 'nlsend')?'<a class="inc_communication" id="n620" rel="'.$n->id.'">'.$n->titel.'</a>':$n->titel).'</p>
        <p class="last">'.$ls.'</p>
        '.($user->r('kom', 'nledit')?'<p class="edit"><a class="inc_communication" id="n615" rel="'.$n->id.'">'.$trans->__('bearbeiten').'</a></p>':'').'
        '.($user->r('kom', 'nlsend')?'<p class="send"><a class="inc_communication" id="n620" rel="'.$n->id.'">'.$trans->__('ansehen und versenden').'</a></p>':'').'
        '.($user->r('kom', 'nledit')?'<p class="del"><a rel="'.$n->id.'" title="'.$trans->__('Newsletter entfernen').'"><img src="images/delete.png" alt="" height="22" /></a></p>':'').'
    </div>';                
}
?>
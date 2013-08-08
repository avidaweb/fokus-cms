<?php
if($index == 's440' && $user->r('fks', 'sitzung'))
{    
    $logged_since = $user->getLoginTime();
    
    $dauer_std = floor(($base->getTime() - $logged_since) / 3600);
    $dauer_min = floor((($base->getTime() - $logged_since) / 60) - ($dauer_std * 60)); 
    
    $browser = get_browser(null, true); 
    
    echo '
    <h1>'.$trans->__('Session-Info.').'</h1>
    
    <div class="box">
    	<table id="sessioninfo">
    		<tr>
    			<td>'.$trans->__('Du bist momentan angemeldet als:').'</td>
    			<td class="right">'.$user->data('vorname').' '.$user->data('nachname').'</td>
    		</tr>
    		<tr>
    			<td>'.$trans->__('Gewählte Rolle:').'</td>
    			<td class="right">'.$fksdb->data("SELECT titel FROM ".SQLPRE."roles WHERE id = '".$user->getRole()."' LIMIT 1", "titel").'</td>
    		</tr>
    		<tr>
    			<td>'.$trans->__('Email:').'</td>
    			<td class="right"><a href="mailto:'.$user->data('email').'">'.$user->data('email').'</a></td>
    		</tr>
    		<tr>
    			<td>'.$trans->__('Registriert seit:').'</td>
    			<td class="right">'.date('d.m.Y', $user->data('registriert')).', '.date('H:i', $user->data('registriert')).' Uhr</td>
    		</tr>
    		<tr>
    			<td>'.$trans->__('Eingeloggt seit:').'</td>
    			<td class="right">'.date('d.m.Y', $logged_since).', '.$trans->__('%1 Uhr', false, array(date('H:i', $logged_since))).'</td>
    		</tr>
    		<tr>
    			<td>'.$trans->__('Verweildauer in Stunden:').'</td>
    			<td class="right">
                    '.($dauer_std == 1?'1 Stunde':$trans->__('%1 Stunden', false, array($dauer_std))).' 
                    &amp; 
                    '.($dauer_min == 1?'1 Minute':$trans->__('%1 Minuten', false, array($dauer_min))).'
                </td>
    		</tr>
    		<tr>
    			<td>'.$trans->__('IP-Adresse:').'</td>
    			<td class="right">'.$api->getVisitorIP().'</td>
    		</tr>
            '.($browser['browser']?'
    		<tr>
    			<td>'.$trans->__('Browser:').'</td>
    			<td class="right">'.$browser['browser'].'</td>
    		</tr>
            ':'').'
            '.($browser['platform'] && $browser['platform'] != 'unknown'?'
    		<tr>
    			<td>'.$trans->__('System:').'</td>
    			<td class="right">'.$browser['platform'].'</td>
    		</tr>
            ':'').'
            '.($user->data('bis') >= time()?'
    		<tr>
    			<td colspan="2">
                    <strong>
                        '.$trans->__('Account läuft am %1 um %2 Uhr ab', false, array(
                            date('d.m.Y', $user->data('bis')),
                            date('H:i', $user->data('bis'))
                        )).'
                    </strong>
                </td>
    		</tr>
            ':'').'
    	</table>
    </div>';
}
?>
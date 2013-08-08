<?php
if($index == 'n590' && $user->isLogged())
{
    $p = $fksdb->fetch("SELECT id, firma, vorname, nachname, str, hn, plz, ort, land, tel_p, tel_g, fax, email FROM ".SQLPRE."users WHERE id = '".$rel."' AND papierkorb = '0' LIMIT 1");
    $f = $fksdb->fetch("SELECT id, name FROM ".SQLPRE."companies WHERE id = '".$p->firma."' AND papierkorb = '0' LIMIT 1");
    
    if(!$p)
        exit('<h1>'.$trans->__('Benutzer nicht gefunden.').'</h1>');
        
    echo '
    <h1>'.$trans->__('Benutzerprofil.').'</h1>
    
    <div class="box">
        <p>
            '.($p->vorname || $p->nachname?$p->vorname.' '.$p->nachname.'<br />':'').'
            '.($p->str || $p->hn?$p->str.' '.$p->hn.'<br />':'').'
            '.($p->plz || $p->ort?$p->plz.' '.$p->ort.'<br />':'').'
            '.($p->land?$p->land.'<br />':'').'
            
            '.($f?'<br />'.$trans->__('Firma:').' '.$f->name.'<br />':'').'
            <br />
            
            '.($p->tel_p?$trans->__('Tel. privat:').' '.$p->tel_p.'<br />':'').'
            '.($p->tel_g?$trans->__('Tel. geschftl.:').' '.$p->tel_g.'<br />':'').'
            '.($p->fax?$trans->__('Fax:').' '.$p->fax.'<br />':'').'
            '.($p->email?$trans->__('Email:').' <a href="mailto:'.$p->email.'">'.$p->email.'</a>':'').'
        </p>
        '.(($user->r('per', 'edit') && $tmitarbeiter) || ($user->r('kom', 'pn') && $suite->rm(10))?'
        <p class="linkz">
            '.($user->r('per', 'edit') && $tmitarbeiter?'<a id="n535" class="inc_users" rel="'.$p->id.'">'.$trans->__('Benutzerprofil öffnen').'</a>':'').'
            '.($user->r('kom', 'pn') && $suite->rm(10)?'<a id="n645" class="inc_communication" rel="'.$p->id.'">'.$trans->__('Eine Nachricht schreiben').'</a>':'').'
        </p>
        ':'').'
    </div>';
} 
?>
<?php
if($_POST['fks_login_submit'])
{
    $v = self::$base->vars('POST');
    $pc = (object)$v;
    
    // Vorüberprüfungen
    $namen = str_replace(',', ' ', $pc->fks_login_name);
	$namen = Strings::removeDoubleSpace($namen);
	$namen = explode(' ', $namen, 2);
    
	$ergebnis = self::$fksdb->query("SELECT id, vorname, nachname FROM ".SQLPRE."users WHERE papierkorb = '0' AND ((email = '".$namen[0]."') OR (vorname = '".$namen[0]."' AND nachname = '".$namen[1]."') OR (vorname = '".$namen[1]."' AND nachname = '".$namen[0]."'))"); 
    
    $aktuell_first_try = self::$fksdb->save($_COOKIE['login_first_try']); 
    $aktuell_login_versuch = self::$fksdb->save($_COOKIE['login_versuch']) + 1;

    if(!self::$api->checkAjaxHash())
        $login_error[] = self::$trans->__('Formular wurde nicht korrekt abgeschickt (Fehlercode: 101). Sie sollten die Seite aktualisieren.');
    if($this->isLogin())
        $login_error[] = self::$trans->__('Sie sind bereits angemeldet');
    elseif(!$pc->fks_login_name)
        $login_error[] = self::$trans->__('Benutzername muss eingegeben werden');  
    elseif(!$pc->fks_login_password)
        $login_error[] = self::$trans->__('Passwort muss eingegeben werden'); 
    elseif(!self::$fksdb->count($ergebnis))
        $login_error[] = self::$trans->__('Anmeldedaten nicht korrekt');
        
    if(!count($login_error)) 
    {
        $row = self::$fksdb->fetch($ergebnis);
        $id = $row->id;
        
        $login = self::$user->getLoginHash($id);
        $pw = self::$user->getPasswordHash($pc->fks_login_password); 
        
        $ergebnis = self::$fksdb->query("SELECT login, pw, id, von, bis, status, vorname, type FROM ".SQLPRE."users WHERE papierkorb = '0' AND login = '".$login."' AND login != '' AND pw != '' AND pw != '".md5('')."' LIMIT 1");
        if(!self::$fksdb->count($ergebnis))
            $login_error[] = 'Anmeldedaten nicht korrekt';
        while($row = self::$fksdb->fetch($ergebnis))
        {            
            $ref = parse_url($_SERVER['HTTP_REFERER']);
            $self = $_SERVER['HTTP_HOST'];
            
            // Spezifische Validierung //
            if($row->von > self::$base->getTime() || ($row->bis && $row->bis < self::$base->getTime()))
                $login_error[] = 'Die Lebensdauer dieses Accounts ist abgelaufen';
            elseif($row->status > 0)
                $login_error[] = 'Dieser Account ist '.($row->status == 2?'gesperrt':'inaktiv');
            elseif($row->pw != $pw)
                $login_error[] = 'Anmeldedaten nicht korrekt';
            elseif($_SERVER['HTTP_REFERER'] && !Strings::strExists($self, $ref['host']) && !Strings::strExists($ref['host'], $self))
                $login_error[] = 'Der Login darf von keiner externen Webseite erfolgen';
            elseif((self::$base->getTime() - $aktuell_first_try) / $aktuell_login_versuch < 10 && $aktuell_login_versuch > 12)
                $login_error[] = 'Zu viele Login-Versuche: Bitte versuchen Sie es in wenigen Minuten erneut';
    
            if(!count($login_error))
            {
                setcookie('login_versuch', 0, self::$base->getTime() - 600, '/'); 
                
                $rolle = self::$fksdb->fetch("SELECT id, rolle FROM ".SQLPRE."user_roles WHERE benutzer = '".$row->id."' ORDER BY id LIMIT 1");
                
                self::$user->doLogin($row->login, $row->pw, $rolle->rolle);
                
                // Weiterleiten
                if($pc->success_forwarding)
                    self::$base->go($this->getRoot().$pc->success_forwarding.'/na/');
                else
                    self::$base->go($this->getRoot().$this->element->id.'/'.(!$this->dclass?'':$dk.'/').self::$base->auto_slug($this->esprachen[$this->language]).'&login_ok=true/');
            }
        }
    }
            
    if(count($login_error))
    {
        setcookie('login_first_try', ($aktuell_first_try > self::$base->getTime() - 600?($aktuell_first_try + 30):self::$base->getTime()), self::$base->getTime() + 600, '/'); 
        setcookie('login_versuch', $aktuell_login_versuch + 1, self::$base->getTime() + 600, '/'); 
    }
    
    $this->login_error = $login_error;
}
if($_POST['fks_logout_submit'])
{
    $v = self::$base->vars('POST');
    $pc = (object)$v;
    
    self::$user->doLogout();
    
    // Weiterleiten
    if($pc->success_logout_forwarding)
        self::$base->go($this->getRoot().$pc->success_logout_forwarding.'/na/');
    else
        self::$base->go($this->getDomain().$this->element->id.'/'.(!$this->dclass?'':$dk.'/').self::$base->auto_slug($this->esprachen[$this->language]).'&logout_ok=true/');
}
?>
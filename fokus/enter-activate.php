<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');

if($user->isAdmin())
{
    $base->go('fokus.php');
}

$code = $fksdb->save($_GET['code']);

if($code)
{
    $cuser = $fksdb->fetch("SELECT * FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND papierkorb = '0' AND reset_hash LIKE '".$code."' AND status = '0' LIMIT 1");      
    if(!$cuser)
        $error[] = $trans->__('Dieser Aktivierungscode konnte keinem registrierten Mitarbeiter zugeordnet werden');
    if($cuser->reset_time < time() && !count($error))
        $error[] = $trans->__('Dieser Aktivierungscode ist bereits abgelaufen. Bitte fordern Sie eine neue Email an.');
        
    if(!count($error))
    {
        $reset_ok = true;
        $reset_mail = $cuser->email;
        
        $plain_password = substr(Strings::createID(), 2, 12);
        $new_password = $user->getPasswordHash($plain_password);
        
        $new_login = $user->getLoginHash($cuser->id);
        
        $fksdb->update("users", array(
            "reset_time" => 0, 
            "reset_hash" => '',
            "login" => $new_login,
            "pw" => $new_password
        ), "id = '".$cuser->id."'", 1);
        
        
        $message = $trans->__('Guten Tag %1,
        
auf der Webseite %2 wurde für Ihren Mitarbeiter-Account ein neues Passwort generiert:
%3


Unter der Adresse %4 können Sie sich nun mit den folgenden Daten anmelden.
Benutzername: %5
Passwort: %3

Hinweis: Mit dieser Email erhalten Sie das letzte Mal Ihre hinterlegten Zugangsdaten, bevor diese verschlüsselt in der Datenbank gespeichert werden.', false, array(
            trim($cuser->anrede.' '.$cuser->vorname.' '.$cuser->nachname),
            str_replace('http://', '', DOMAIN),
            $plain_password,
            DOMAIN.'/fokus/',
            $cuser->email
        ));
        
        $subject = $trans->__('Passwort zurückgesetzt:').' '.str_replace('http://', '', DOMAIN);
        
        $base->email($cuser->email, $subject, $message, $base->getOpt('email'));
    }
}
else
{
    $error[] = $trans->__('Es wurde kein gültiger Code zum Zurücksetzen übermittelt');
}

echo '
<!DOCTYPE html> 
<html lang="de"> 
<head> 
    <meta charset="utf-8"/> 
    <meta name="robots" content="noindex, nofollow">

	<title>'.$base->getServerName().' | '.$trans->__('fokus. das cms.').'</title>
    
    <script>
    document.createElement("header");
    document.createElement("section");
    document.createElement("article");
    document.createElement("aside");
    document.createElement("footer");
    document.createElement("nav");
    document.createElement("hgroup");
    document.createElement("details");
    document.createElement("figcaption");
    document.createElement("figure");
    document.createElement("menu");
    </script>
    
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon" /> 
    
    <link type="text/css" rel="stylesheet" media="screen" href="'.BACKEND_DIR.'css/css.php?files=reset|jquery_ui|layout|login&v='.FKS_VERSION.'" />
    
    <script src="'.DOMAIN.'/inc/libraries/js/jquery.min.js?v='.FKS_VERSION.'"></script>
    <script src="'.DOMAIN.'/inc/libraries/js/jquery-ui.custom.min.js?v='.FKS_VERSION.'"></script>
    <!--[if lte IE 8]><script src="'.DOMAIN.'/inc/libraries/js/modernizr.js?v='.FKS_VERSION.'"></script><![endif]-->
    <script src="js/login.js?v='.FKS_VERSION.'" type="text/javascript"></script>
        
</head>

<body>
    <div id="main">
    
        <table class="fenster" id="user-reset"><tr><td class="A1"></td><td class="A2"></td><td class="A3"></td></tr><tr><td class="B1"></td><td class="B2">
        <form action="enter-reset.php" method="post">
        <div class="inhalt">
            <h1>'.$trans->__('Passwort zurücksetzen.').'</h1>
            
            '.$base->error($error);
            
            if($reset_ok)
            {
                echo '
                <div class="box" id="anmeldung">
                    <p class="info">
                        '.$trans->__('Ihr Passwort wurde erfolgreich zurückgesetzt. Wir haben Ihnen eine Email an %1 geschickt, in der Sie ihre neuen Zugangsdaten finden.<br /><br /><a href="enter.php">Zur Anmeldung</a>', false, array($reset_mail)).'
                    </p>
                </div>';
            }
            
        echo '
        </div>
        </form>
        </td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr><tr><td colspan="3" class="D"></td></tr>
        </table>
    </div>
</body>
</html>';
<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');

if($user->isAdmin())
{
    $base->go('fokus.php');
}

if($_POST['reset'])
{
    $email = $fksdb->save($_POST['email']);

    if(!$api->checkAjaxHash())
        $error[] = $trans->__('Formular wurde nicht korrekt abgeschickt (Fehlercode: 101). Sie sollten die Seite aktualisieren.');
    
    $ref = parse_url($_SERVER['HTTP_REFERER']);
    $self = $_SERVER['HTTP_HOST'];
    if($_SERVER['HTTP_REFERER'] && $self && !Strings::strExists($self, $ref['host']) && !Strings::strExists($ref['host'], $self))
        $error[] = $trans->__('Das Formular darf von keiner externen Webseite abgeschickt werden');
    
    if(!$email)
        $error[] = $trans->__('E-Mail-Adresse ist ein Pflichtfeld');
        
    $cuser = $fksdb->fetch("SELECT * FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND papierkorb = '0' AND email LIKE '".$email."' AND status = '0' LIMIT 1");      
    if(!$cuser && !count($error))
        $error[] = $trans->__('Zur eingegebenen E-Mail-Adresse wurde kein gültiger Benutzer gefunden');
    if($cuser->reset_time > time() && !count($error))
        $error[] = $trans->__('Diesem Benutzer wurde bereits innerhalb der letzten 48 Stunden eine Email zum Zurücksetzen des Passworts zugesandt');
        
    if(!count($error))
    {
        $reset_ok = true;
        $reset_mail = $cuser->email;
        
        $reset_hash = sha1(md5($cuser->email.Strings::createID().DOMAIN).$cuser->id.time()).md5(time().Strings::createID());
        $reset_time = time() + (48 * 60 * 60);
        
        $fksdb->update("users", array(
            "reset_time" => $reset_time, 
            "reset_hash" => $reset_hash
        ), "id = '".$cuser->id."'", 1);
        
        
        $message = $trans->__('Guten Tag %1,
        
auf der Webseite %2 wurde für Ihren Mitarbeiter-Account ein neues Passwort angefordert.

Sie haben bis zum %3 um %4 Uhr Zeit, den folgenden Link aufzurufen und damit ihr Passwort zurückzusetzen:
%5


(Falls Sie kein neues Passwort angefordert haben, können Sie diese Email einfach ignorieren)', false, array(
            trim($cuser->anrede.' '.$cuser->vorname.' '.$cuser->nachname),
            str_replace('http://', '', DOMAIN),
            date('d.m.Y', $reset_time),
            date('H:i', $reset_time),
            DOMAIN.'/fokus/enter-activate.php?code='.$reset_hash
        ));
        
        $subject = $trans->__('Passwort zurücksetzen:').' '.str_replace('http://', '', DOMAIN);
        
        $base->email($cuser->email, $subject, $message, $base->getOpt('email'));
    }
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
            
            '.$base->error($error).'
            
            <div class="box" id="anmeldung">';
            
                if($reset_ok)
                {
                    echo '
                    <p class="info">
                        '.$trans->__('Wir haben Ihnen eine Email an %1 geschickt, mit der Sie Ihr Passwort zurücksetzen können. Bitte überprüfen Sie nun Ihren Posteingang. Bitte beachten Sie, dass Sie nur 48 Stunden Zeit haben, um Ihr Passwort zurückzusetzen. Danach verfällt der in der Email übermittelte Link.', false, array($reset_mail)).'
                    </p>';
                }
                else
                {
                    echo '
                    <table>
                        <tr>
                            <td>'.$trans->__('E-Mail-Adresse:').'</td>
                            <td>
                                <input type="email" name="email" value="'.$email.'" required tabindex="1" autofocus /> 
                            </td>
                        </tr>
                    </table>';
                }
            
            echo '
            </div>
            <div class="box_save">
                '.$api->getHashInput().'
            
                <input type="submit" class="bs2" value="'.$trans->__('Passwort zurücksetzen').'" name="reset" tabindex="2" />
            </div>
        </div>
        </form>
        </td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr><tr><td colspan="3" class="D"></td></tr>
        </table>
    </div>
</body>
</html>';
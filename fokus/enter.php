<?php
define('IS_BACKEND', true, true);
define('UPDATE_ALLOWED', true);

require_once('../inc/header.php');

$error = array();
$multi_user = false;
$name = '';
$mname = '';
$namen = array();

if($_REQUEST['logout'])
{    
    $user->doLogout();
}

if($user->isAdmin())
{
    $base->go('fokus.php');
}

if($_POST['login'])
{
    if(!$api->checkAjaxHash() && !$_POST['ajaxcheck'])
        $error[] = $trans->__('Formular wurde nicht korrekt abgeschickt (Fehlercode: 101). Sie sollten die Seite aktualisieren.');
    
    $name = $fksdb->save($_POST['name']);
    $mname = $fksdb->save($_POST['mname']);
    $pw2 = $fksdb->save($_POST['pw']);
    
    if(!$mname)
    {
    	$namen = str_replace(',', ' ', $name);
    	$namen = Strings::removeDoubleSpace($namen);
    	$namen = explode(' ', $namen, 2);
        
    	$user_query = $fksdb->query("SELECT id, vorname, nachname FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND papierkorb = '0' AND ((email = '".$namen[0]."') OR (vorname = '".$namen[0]."' AND nachname = '".$namen[1]."') OR (vorname = '".$namen[1]."' AND nachname = '".$namen[0]."'))"); 
    }
    else
    {
        $multi_user = $base->db_to_array($fksdb->save($_POST['mehrere']));
        
    	$user_query = $fksdb->query("SELECT id, vorname, nachname FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND papierkorb = '0' AND id = '".$mname."' LIMIT 1"); 
    }
        
    if(!$fksdb->count($user_query))
    {
        $error[] = $trans->__('Anmeldedaten nicht korrekt');
    }
    elseif($fksdb->count($user_query) > 1 && $name)
    {
        if($_POST['ajaxcheck'])
        {
            echo 'ok';
            exit();
        }
        
        $error[] = $trans->__('Es wurden mehrere mögliche Benutzer gefunden. <br />Bitte identifizieren Sie Ihren Account:');
        $multi_user = array();
        
        while($cur_user = $fksdb->fetch($user_query))
        {
            $pera = $fksdb->fetch("SELECT namenszusatz, plz, ort, position, email, eid, str FROM ".SQLPRE."users WHERE id = '".$cur_user->id."' AND papierkorb = '0' LIMIT 1"); 
            
            $adding = '';
            if($pera->namenszusatz) $adding = $trans->__('Nameszusatz:').' '.substr($pera->namenszusatz, 0, 5).'...';
            elseif($pera->str) $adding = $trans->__('Strasse:').' '.substr($pera->str, 0, 5).'...';
            elseif($pera->email) $adding = $trans->__('Email:').' '.substr($pera->email, 0, 5).'...';
            elseif($pera->ort) $adding = $trans->__('Ort:').' '.substr($pera->ort, 0, 5).'...';
            elseif($pera->plz) $adding = $trans->__('PLZ:').' ...'.substr($pera->plz, -2, 2).'...';
            elseif($pera->position) $adding = $trans->__('Position:').' '.substr($pera->position, 0, 5).'...';
            elseif($pera->eid) $adding = $trans->__('Alternative ID:').' '.substr($pera->eid, 0, 5).'...';
            
            $multi_user[$cur_user->id] = $cur_user->vorname.' '.$cur_user->nachname.' '.($adding?'('.$adding.')':'');
        }
    }
    else 
    {
        if($base->getOpt('login_captcha'))
        {       
            $captcha = $fksdb->save($_POST['captcha'], 1);
            
            $cp1 = $fksdb->save($_POST['cp1'], 1);       
            $cp2 = $fksdb->save($_POST['cp2'], 1);       
            $cpt = $fksdb->save($_POST['cpt']);
            
            $z1 = $base->calc_captcha($cp1, 1);
            $z2 = $base->calc_captcha($cp2, 1); 
            
            $zres = ($cpt == 'plus'?($z1 * $z2):($z1 + $z2));
            
            if($captcha != $zres)
            {
                $wrong_captcha = true;
                $error[] = $trans->__('Die Sicherheitsfrage wurde falsch beantwortet');
            }
        }
        
        $cur_user = $fksdb->fetch($user_query);
        $id = $cur_user->id;
        
        $login = $user->getLoginHash($id);
        $pw = $user->getPasswordHash($pw2); 
        
        if(!$name && !$multi_user)
            $error[] = $trans->__('Benutzername muss eingegeben werden');  
        if(!$pw2)
            $error[] = $trans->__('Passwort muss eingegeben werden');
        
        
        $user_query = $fksdb->query("SELECT login, pw, id, von, bis, status, vorname FROM ".SQLPRE."users WHERE (type = '0' OR type = '2') AND papierkorb = '0' AND login = '".$login."' AND login != '' AND pw != '' AND pw != '".md5('')."' LIMIT 1");
        
        if(!$fksdb->count($user_query))
            $error[] = $trans->__('Anmeldedaten nicht korrekt');
            
        while($cur_user = $fksdb->fetch($user_query))
        {
            $roles = $fksdb->query("SELECT id, rolle FROM ".SQLPRE."user_roles WHERE benutzer = '".$cur_user->id."' GROUP BY rolle");
            $role = $fksdb->fetch($roles);
            
            $ref = parse_url($_SERVER['HTTP_REFERER']);
            $self = $_SERVER['HTTP_HOST'];
            
            $first_login_action = $fksdb->save($_COOKIE['login_first_try']); 
            $current_login_action = $fksdb->save($_COOKIE['login_versuch']) + 1;
            
            if($cur_user->von > time() || ($cur_user->bis && $cur_user->bis < time()))
                $error[] = $trans->__('Die Lebensdauer dieses Accounts ist abgelaufen');
            elseif($cur_user->status > 0)
                $error[] = $trans->__('Dieser Account ist').' '.($cur_user->status == 2?$trans->__('gesperrt'):$trans->__('inaktiv'));
            elseif($cur_user->pw != $pw)
                $error[] = $trans->__('Anmeldedaten nicht korrekt');
            elseif(!$fksdb->count($roles))
                $error[] = $trans->__('Dem Benutzer ist keine Rolle zugewiesen');
            elseif($_SERVER['HTTP_REFERER'] && $self && !Strings::strExists($self, $ref['host']) && !Strings::strExists($ref['host'], $self))
                $error[] = $trans->__('Der Login darf von keiner externen Webseite erfolgen');
            elseif((time() - $first_login_action) / $current_login_action < 10 && $current_login_action > 10)
                $error[] = $trans->__('Zu viele Login-Versuche: Bitte versuchen Sie es in wenigen Minuten erneut');
                
            if(!count($error) && !$_POST['ajaxcheck'])
            {
                setcookie('login_versuch', 0, time() - 600, '/'); 
                    
                $user->doLogin($cur_user->login, $cur_user->pw, ($fksdb->count($roles) == 1?$role->rolle:0));
                    
                $fksdb->insert("livetalk", array(
                	"benutzer" => $cur_user->id,
                	"text" => '<em>'.$cur_user->vorname.' '.$trans->__('hat sich angemeldet').'</em>',
                	"timestamp" => time()
                ));
                
                $base->go('fokus.php');
            }
            
            if(count($error))
            {
                $add_time = ($wrong_captcha?10:30);
                setcookie('login_first_try', ($first_login_action > time() - 600?($first_login_action + $add_time):time()), time() + 600, '/'); 
                setcookie('login_versuch', $current_login_action + 1, time() + 600, '/'); 
            }
        }
    }
        
    if($_POST['ajaxcheck'])
    {  
        if(!count($error))
        {
            echo 'ok';    
        }
        else
        {
            echo $base->error($error);
        }
        exit();
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
    <script src="js/login.js?v='.FKS_VERSION.'" type="text/javascript"></script>';
    
    include(BACKEND_DIR.'preloader.php');
    
echo '
</head>

<body>
    <div id="main">
    
        <table class="fenster" id="login_window"><tr><td class="A1"></td><td class="A2"></td><td class="A3"></td></tr><tr><td class="B1"></td><td class="B2">
        <form action="enter.php" method="post">
            '.$api->getHashInput().'
        
            <div class="inhalt">
                <h1>'.$trans->__('Anmeldung.').'</h1>
                '.$base->error($error).'
                <div class="box" id="anmeldung">
                    <table>
                        <tr>
                            <td>'.$trans->__('Benutzername:').'</td>
                            <td>';
                                if(!is_array($multi_user))
                                {
                                    echo '
                                    <input id="name-name" type="text" name="name" value="'.($name?$name:'').'"'.($name?'':' placeholder="'.$trans->__('z.B. &quot;Maik Müller&quot;').'"').' class="normal" required tabindex="1" autofocus />';
                                }
                                else
                                {
                                    echo '
                                    <input type="hidden" name="mehrere" value="'.$base->array_to_db($multi_user).'" />
                                    
                                    <select name="mname" required tabindex="1">';
                                    foreach($multi_user as $k => $v)
                                        echo '<option value="'.$k.'"'.($mname == $k?' selected="selected"':'').'>'.$v.'</option>';
                                    echo '
                                    <select>';
                                }
                            echo '
                            </td>
                        </tr>
                        <tr>
                            <td>'.$trans->__('Passwort:').'</td>
                            <td>
                                <input id="password-password" type="password" name="pw" value="" placeholder="'.$trans->__('z.B. &quot;Pflaumenbaum879&quot;').'" autocomplete="off" class="normal" required tabindex="2" /><br />
                                
                                <a class="reset" href="enter-reset.php" tabindex="99">'.$trans->__('Passwort vergessen?').'</a>
                            </td>
                        </tr>';
                        if($base->getOpt('login_captcha'))
                        {
                            $z1 = rand(1, 9);
                            $z2 = rand(1, 9);
                            $op = (rand(0, 1) == 1?'plus':'mal');
                            
                            $login_security = array(
                                'numbers' => array(
                                    1 => 'acht',
                                    2 => 'drei',
                                    3 => 'sieben',
                                    4 => 'eins',
                                    5 => 'fuenf',
                                    6 => 'zwei',
                                    7 => 'sechs',
                                    8 => 'neun',
                                    9 => 'vier'
                                ) 
                            );  
                            
                            echo '
                            <tr>
                                <td>
                                    <img src="images/nr/'.$login_security['numbers'][$z1].'.png" alt="" />  
                                    <img src="images/nr/'.$op.'.png" alt="" />  
                                    <img src="images/nr/'.$login_security['numbers'][$z2].'.png" alt="" />  
                                    =
                                </td>
                                <td>
                                    <input type="number" value="" tabindex="3" autocomplete="off" required name="captcha" placeholder="'.$trans->__('Ergebnis der Sicherheitsfrage').'" />
                                    <input type="hidden" name="cp1" value="'.$base->calc_captcha($z1).'" />
                                    <input type="hidden" name="cp2" value="'.$base->calc_captcha($z2).'" />
                                    <input type="hidden" name="cpt" value="'.$op.'" />
                                </td>
                            </tr>';
                        }
                    echo '
                    </table>
                    
                </div>
                <div class="box_save">
                    <input type="submit" class="bs2" value="'.$trans->__('anmelden').'" name="loginbutton" tabindex="4" />
                    <input type="hidden" name="login" value="true" />
                </div>
            </div>
        </form>
        </td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr><tr><td colspan="3" class="D"></td></tr>
        </table>
    </div>
    
    <div id="preload" class="calibri">
        '.$trans->__('<span class="a">0</span> von <span class="b">%1</span> fokus Dateien wurden erfolgreich vorgeladen...', false, array($countpics)).'
    </div>
    <div id="loadedimages"></div>
</body>
</html>';
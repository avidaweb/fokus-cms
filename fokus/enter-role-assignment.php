<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');

if($user->getRole())
{
    $base->go('fokus.php');
}

if($_POST['go'])
{
    $rolle = $fksdb->save($_POST['rolle']);
    
    if($rolle)
    {
        setcookie($user->getCookiesAliases('role'), $rolle, (time() + 60 * 60 * 24 * 30), '/');  
        
        $base->go('fokus.php');
    }
}
    
$rollenoutput = '';
$rdurchlauf = 0;
$rrollencount = 0;

$ergebnis = $fksdb->query("SELECT rolle FROM ".SQLPRE."user_roles WHERE benutzer = '".$user->getID()."' GROUP BY rolle");
while($row = $fksdb->fetch($ergebnis))
{
    $rname = $fksdb->fetch("SELECT titel FROM ".SQLPRE."roles WHERE id = '".$row->rolle."' AND papierkorb = '0' LIMIT 1");
    
    if($rname)
    {
        $rollenoutput .= '
        <tr>
            <td><input type="radio" name="rolle" value="'.$row->rolle.'" id="r'.$row->rolle.'"'.(!$rdurchlauf?' checked="checked"':'').' /></td>
            <td><label for="r'.$row->rolle.'">'.$trans->__('Anmelden als').' <em>&quot;'.$rname->titel.'&quot;</em> '.'</label></td>
        </tr>';
        
        $rrollencount ++;
    }
    
    $rdurchlauf ++;
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
        <form action="enter-role-assignment.php" method="post">
        <div class="inhalt">
            <h1>'.$trans->__('Rolle wählen.').'</h1>
            '.(!$rrollencount?'
            <div class="box fehlerbox">
                <strong>'.$trans->__('Ihnen wurde leider noch keine Rolle zugewiesen.').'</strong>
                '.$trans->__('Eine Anmeldung im fokus Backend ist ohne zugewiesene Rolle leider nicht möglich. Bitte kontaktieren Sie ihren Administrator.').'
            </div>':'
            <div class="box" id="rolle">
                '.$base->error($fehler).'
                <table>
                    '.$rollenoutput.'
                </table>
            </div>
            <div class="box_save" style="display:block;">
                <input type="submit" class="bs2" value="weiter" name="go" />
            </div>').'
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
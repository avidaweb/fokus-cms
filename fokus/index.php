<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');

if($user->isLogged() && $user->isAdmin())
    $base->go(DOMAIN.'/fokus/fokus.php');

echo '
<!DOCTYPE html> 
<html lang="de"> 
<head> 
    <meta charset="utf-8"/> 
    <meta name="robots" content="noindex, follow">

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
    
        <div id="index-intro">
            <div class="wrap"> 
                <h1>'.$trans->__('Wohin möchten Sie?').'</h1>
                
                <p class="backend">
                    <a href="enter.php">'.$trans->__('Jetzt anmelden').'</a>
                </p>
                <p class="vid-train">
                    <a href="http://www.fokus-cms.de/22/video-trainings-fuer-einsteiger/" target="_blank">'.$trans->__('Video-Trainings ansehen').'</a>
                </p>
                <p class="support">
                    <a href="http://community.fokus-cms.de/" target="_blank">'.$trans->__('Zur fokus community').'</a>
                </p>
                <p class="frontend">
                    <a href="'.DOMAIN.'">'.$trans->__('Zurück zur Website').'</a>
                </p>
            </div>
        </div>
        
    </div>
    
    <div id="preload" class="calibri">
        '.$trans->__('<span class="a">0</span> von <span class="b">%1</span> fokus Dateien wurden erfolgreich vorgeladen...', false, array($countpics)).'
    </div>
    <div id="loadedimages"></div>
    
    <noscript><div id="no-js">'.$trans->__('Javascript muss aktiviert sein :)').'</div></noscript>
</body>
</html>';
?>
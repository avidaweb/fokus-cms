<?php
define('IS_BACKEND', true, true);
define('UPDATE_ALLOWED', true);

$index = true;
require_once('../inc/header.php');
require_once('login.php');
  
$ba = $base->fixedUnserialize($user->data('widgets'));
 
echo '
<!DOCTYPE html> 
<html lang="de"> 
<head> 
    <meta charset="utf-8"/> 
	<meta name="author" content="fokus-cms.de" />
    <meta name="robots" content="noindex, nofollow" />

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
    
    <link type="text/css" rel="stylesheet" media="screen" href="'.BACKEND_DIR.'css/css.php?files=reset|jquery_ui|jquery.fileupload-ui|colorpicker|jquery.Jcrop|layout|widgets|inc_structure|inc_documents|inc_files|inc_users|inc_communication|sub&v='.FKS_VERSION.'" />
    
    <script>
        var domain = \''.DOMAIN.'\';
        var ajax_url = \''.$api->getAjaxUrl().'\';
        
        var debug = '.(defined('DEBUG')?'true':'false').';';
        
        $navi_breite = 0 + ($user->r('str')?+160:+0)+($user->r('dok')?+160:+0)+($user->r('dat')?+160:+0)+($user->r('per')?+160:+0)+($user->r('kom')?+160:+0)+($user->r('suc')?+160:+0);
        echo '
        var navi_breite = '.$navi_breite.';
        var neues_fenster_task = '.($user->getIndiv()->fenster_neu?$user->getIndiv()->fenster_neu:0).';
        var subnavi_click = '.($user->getIndiv()->subnavi?$user->getIndiv()->subnavi:0).';
        var show_changelog = '.(floatval($base->getOpt()->changelog) > 1?'true':'false').';
    </script>
    
    <script src="'.DOMAIN.'/inc/libraries/js/jquery.min.js?v='.FKS_VERSION.'"></script>
    <script src="'.DOMAIN.'/inc/libraries/js/jquery-ui.custom.min.js?v='.FKS_VERSION.'"></script>
    <!--[if lte IE 8]><script src="'.DOMAIN.'/inc/libraries/js/modernizr.js?v='.FKS_VERSION.'"></script><![endif]-->

    <script src="js/functions.js?v='.FKS_VERSION.'"></script>
    <script src="js/element.js?v='.FKS_VERSION.'"></script>
    <script src="js/jcanvas.min.js?v='.FKS_VERSION.'"></script>
    <script src="js/jquery.scrollTo-min.js?v='.FKS_VERSION.'"></script>
    <script src="js/colorpicker.js?v='.FKS_VERSION.'"></script>
    <script src="js/jquery.hotkeys.js?v='.FKS_VERSION.'"></script>
    <script src="js/jquery.Jcrop.min.js?v='.FKS_VERSION.'"></script>
    
    <script src="js/tmpl.min.js?v='.FKS_VERSION.'"></script>
    <script src="js/jquery.iframe-transport.js?v='.FKS_VERSION.'"></script>
    <script src="js/jquery.fileupload.js?v='.FKS_VERSION.'"></script>
    <script src="js/jquery.fileupload-fp.js?v='.FKS_VERSION.'"></script>
    <script src="js/jquery.fileupload-ui.js?v='.FKS_VERSION.'"></script>
    <script src="js/locale.js?v='.FKS_VERSION.'"></script>
    
    <script src="ckeditor/ckeditor.js?v='.FKS_VERSION.'"></script>
    <script src="ckeditor/adapters/jquery.js?v='.FKS_VERSION.'"></script>
    
    <script src="js/core.js?v='.FKS_VERSION.'"></script>
    <script src="js/notifications.js?v='.FKS_VERSION.'"></script>
    <script src="js/widgets.js?v='.FKS_VERSION.'"></script>
    <script src="js/fks.js?v='.FKS_VERSION.'"></script>
</head>

<body> 
    <input type="hidden" id="input_language" value="'.$trans->getInputLanguage().'" />
    
    <section id="main"></section>
    
    <aside id="notifications"></aside>
    <aside id="widget-menu"></aside>
    <aside id="widget-dashboard">
        <div class="dashboard"></div>
    </aside>
    
    <table class="fenster" id="sfrage"><tr><td class="A1"></td><td class="A2"></td><td class="A3"></td></tr><tr><td class="B1"></td><td class="BB2 inhalt">
    <div>'.$trans->__('Sind Sie sicher?').'</div>
    <button>'.$trans->__('Nein, zurück').'</button><button>'.$trans->__('Ja, weiter').'</button> 
    </td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr><tr><td colspan="3" class="D"></td></tr></table>
    
    <div id="big_vorschau"></div>
    
    <noscript><div id="no-js">'.$trans->__('Javascript muss aktiviert sein :)').'</div></noscript>
    <div id="fks-offline"><p>'.$trans->__('Das CMS fokus hat erkannt, dass Sie momentan über keine aktive Internetverbindung verfügen. Damit ungespeicherte Änderungen nicht verloren gehen, wurden Ihre Eingabemöglichkeiten beschränkt. Sobald Sie wieder online sind, wird dieser Hinweis automatisch ausgeblendet und Sie können die Arbeit fortsetzen.').'</p></div>
    <div id="old-browser">
        <strong>'.$trans->__('Sie verwenden einen alten Browser').'</strong><br />
        '.$trans->__('Um das CMS fokus ohne Einschränkungen verwenden zu können empfehlen wir die Verwendung der neuesten Versionen von <a href="https://www.google.com/chrome?hl=de" target="_blank">Google Chrome</a>, <a href="http://www.mozilla.org/de/firefox/new/" target="_blank">Mozilla Firefox</a> oder dem <a href="http://windows.microsoft.com/de-DE/internet-explorer/products/ie/home" target="_blank">Internet Explorer</a>.').'
    </div>
</body>
</html>';


// refresh document status
$docq = $fksdb->query("SELECT id, anfang, bis, statusB FROM ".SQLPRE."documents WHERE (statusB = '1' AND (anfang <= '".$base->getTime()."' AND (bis = '0' OR bis >= '".$base->getTime()."'))) OR (statusB = '0' AND (anfang > '".$base->getTime()."' OR (bis > '1000' AND bis < '".$base->getTime()."')))");
while($doc = $fksdb->fetch($docq))
{  
    $dscheck = $base->find_check_document_statusB($doc->id, $doc->anfang, $doc->bis, $doc->statusB);
}
?>
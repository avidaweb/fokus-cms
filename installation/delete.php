<?php
define('IS_INSTALLATION', true, true);

error_reporting(0);

echo '
<!DOCTYPE html> 
<html lang="de"> 
<head> 
    <meta charset="utf-8"/> 

	<title>fokus. die installation.</title>
    
    <link href="../fokus/css/reset.css" rel="stylesheet" type="text/css" media="screen, handheld, tv, projection" />
    <link href="../fokus/css/smoothness/jquery-ui.custom.css" rel="stylesheet" type="text/css" media="screen, handheld, tv, projection" />
    <link href="../fokus/css/layout.css" rel="stylesheet" type="text/css" media="screen, handheld, tv, projection" />
    <link href="installation.css" rel="stylesheet" type="text/css" media="screen, handheld, tv, projection" />
    
    <script src="../inc/libraries/js/jquery.min.js" type="text/javascript"></script>
    <script src="../inc/libraries/js/jquery-ui.custom.min.js" type="text/javascript"></script>
    <script src="installation.js" type="text/javascript"></script>
</head>

<body>
    <div id="main">
    
        <table class="fenster" style="margin-top: 60px;"><tr><td class="A1"></td><td class="A2"></td><td class="A3"></td></tr><tr><td class="B1"></td><td class="B2">
        <div class="inhalt" id="install">
            <h1>Installation.</h1>
            <div class="box">
                <div class="warnung">
                    <strong>'. $trans->__('Fokus wurde erfolgreich installiert und kann nun verwendet werden!') .'</strong><br />
                    <strong>'. $trans->__('Wichtig:</strong>Bitte l&ouml;schen Sie umgehend den Ordner /installation/ im fokus Hauptverzeichnis. Ist dies geschehen klicken Sie auf <em>Weiter</em>, um das System zu nutzen. Bevor Sie den Ordner nicht entfernt haben, ist die Benutzung von fokus aus Sicherheitsgr&uuml;nden nicht m&ouml;glich.') .'<br /><br />
                    <form action="../fokus/" method="get"><div><input type="submit" value="'. $trans->__('Weiter') .'" /></div></form>
                </div>
            </div>
        </div>
        </td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr><tr><td colspan="3" class="D"></td></tr>
    
    </div>
</body>
</html>';
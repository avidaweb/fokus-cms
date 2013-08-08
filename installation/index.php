<?php
define('IS_INSTALLATION', true, true);
error_reporting(0);

require('../inc/header.php');

if(defined('INSTALLED'))
    $base->go('delete.php');

$phpversion = floatval(phpversion());

$mod_rewrite = true;
if(function_exists('apache_get_modules')) 
{
    $modules = apache_get_modules();
    $mod_rewrite = in_array('mod_rewrite', $modules);
} 

if(!file_exists('../fokus-config.php'))
    fopen('../fokus-config.php', 'w');
if(!file_exists('../.htaccess'))
    fopen('../.htaccess', 'w');

echo '<!DOCTYPE html> 
<html lang="de"> 
<head> 
    <meta charset="utf-8"/> 
	<meta name="author" content="fokus-cms.de" />
    <meta name="robots" content="noindex, nofollow" />

	<title>'. $trans->__('fokus. die installation.') .'</title>
    
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
    
        <table class="fenster"><tr><td class="A1"></td><td class="A2"></td><td class="A3"></td></tr><tr><td class="B1"></td><td class="B2">
        <form method="post" id="i_form">
        <div class="inhalt" id="install">
            <h1>Installation.</h1>
            '.$base->error($fehler);

            echo '
            <noscript>
                <div class="box">
                    <div class="warnung">
                        <strong>'. $trans->__('Javascript muss aktiviert sein!') .'</strong>
                        '. $trans->__('Um die fokus Installation sowie das Backend nutzen zu können, müssen Sie in ihrem Browser Javascript aktivieren.') .'
                    </div>
                </div>
            </noscript>';
            
            if($phpversion < 5.3)
            {
                echo '
                <div class="box">
                    <div class="warnung">
                        <strong>'. $trans->__('CMS fokus kann wahrscheinlich nicht installiert werden!</strong>
                        Das CMS fokus ben&ouml;tigt <em>PHP</em> ab der Version <em>5.3</em>. Sie verwenden derzeit die Version <em>%1</em>. Bitte f&uuml;hren Sie ein Update aus oder kontaktieren Ihren CMS fokus - Partner.', false, array($phpversion)) .'
                    </div>
                </div>';
            }
            
            if((!file_exists('../fokus-config.php') || !is_writable('../fokus-config.php')))
            {
                echo '
                <div class="box">
                    <div class="warnung">
                        <strong>'. $trans->__('CMS fokus kann wahrscheinlich nicht installiert werden!</strong>
                        Die Datei <em>fokus-config.php</em> im Hauptverzeichnis ').(!file_exists('../fokus-config.php')?$trans->__(' existiert nicht'):$trans->__('benötigt die CHMOD-Rechte <em>0666</em> und besitzt momentan die Rechte <em>%1</em>.')).$trans->__('Bitte legen Sie die Datei jetzt an und geben ihr die nötigen Rechte. Anschließend klicken Sie auf <em>Aktualisieren</em>.', false, array($base->file_perms('../fokus-config.php', true))) .'<br /><br />
                        <button>'. $trans->__('Aktualisieren') .'</button>
                    </div>
                </div>';
            }
            
            if((!file_exists('../.htaccess') || !is_writable('../.htaccess')))
            {
                echo '
                <div class="box">
                    <div class="warnung">
                        <strong>'. $trans->__('Es kann zu Problemen kommen') .'</strong>
                        '. $trans->__('Die Datei <em>.htaccess</em> im Hauptverzeichnis ').(!file_exists('../.htaccess')?$trans->__(' existiert nicht'):$trans->__('benötigt die CHMOD-Rechte <em>0666</em> und besitzt momentan die Rechte <em>%1</em>', false, array($base->file_perms('../.htaccess', true)))).$trans->__('Bitte legen Sie die Datei jetzt an und geben ihr die nötigen Rechte. Anschließend klicken Sie auf <em>Aktualisieren</em>.') .'<br /><br />
                        <button>'. $trans->__('Aktualisieren') .'</button>
                    </div>
                </div>';
            }
            
            if(!$mod_rewrite)
            {
                echo '
                <div class="box">
                    <div class="warnung">
                        <strong>'. $trans->__('Es kann zu Problemen kommen') .'</strong>
                        '. $trans->__('Es scheint so als w&auml;re das Server-Modul <em>ModRewrite</em> auf Ihrem Server nicht aktiviert. Dieses ist aber notwendig, um das CMS fokus ohne Probleme zu verwenden. Bitte schalten Sie das Modul jetzt in Ihren <em>Servereinstellungen</em> frei oder kontaktieren Ihren CMS fokus Partner. Anschlie&szlig;end klicken Sie auf <em>Aktualisieren</em>.') .'<br /><br />
                        <button>'. $trans->__('Aktualisieren') .'</button>
                    </div>
                </div>';
            }
            
            if(!is_writable('../content/uploads/') || !is_writable('../content/uploads/bilder/') || !is_writable('../content/uploads/dokumente/'))
            {
                echo '
                <div class="box">
                    <div class="warnung">
                        <strong>'. $trans->__('Es kann zu Problemen kommen') .'</strong>
                        '. $trans->__('Das Verzeichnis <em>/content/uploads/</em> oder eines seiner Kind-Ordner ist nicht beschreibar. Sie ben&ouml;tigen die CHMOD-Rechte <em>0777</em>.
                        Bitte geben Sie den Verzeichnissen jetzt die nötigen Rechte und klicken Sie anschließend auf <em>Aktualisieren</em>.') .'<br /><br />
                        <button>'. $trans->__('Aktualisieren') .'</button>
                    </div>
                </div>';
            }
            
            if(!is_writable('../content/export/'))
            {
                echo '
                <div class="box">
                    <div class="warnung">
                        <strong>'. $trans->__('Es kann zu Problemen kommen') .'</strong>
                        '. $trans->__('Der Ordner <em>/content/export/</em> ist nicht beschreibar. Er ben&ouml;tigt die CHMOD-Rechte <em>0777</em> und besitzt momentan die Rechte <em>%1</em>.
                        Bitte geben Sie dem Verzeichnis jetzt die n&ouml;tigen Rechte und klicken Sie anschlie&szlig;end auf <em>Aktualisieren</em>.', false, array($base->file_perms('../content/export/', true))) .'<br /><br />
                        <button>'. $trans->__('Aktualisieren') .'</button>
                    </div>
                </div>';
            }
            
            echo '
            <div class="box">
                <fieldset id="step1">
                    <legend>'. $trans->__('Datenbank') .'</legend>
                    
                    <table class="daten">
                        <tr>
                            <td class="first">'. $trans->__('Server / Host') .'</td>
                            <td><input type="text" name="host" value="localhost" /></td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Benutzer') .'</td>
                            <td><input type="text" name="user" value="" /></td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Passwort') .'</td>
                            <td><input type="text" name="pw" value="" /></td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Datenbankname') .'</td>
                            <td><input type="text" name="db" value="" /></td>
                        </tr>
                        <tr>
                            <td colspan="2"><br /><button>'. $trans->__('weiter') .'</button></td>
                        </tr>
                    </table>
                </fieldset>
                
                <fieldset id="step2">
                    <legend>Benutzer</legend>
                    
                    <table class="daten">
                        <tr>
                            <td class="first">'. $trans->__('Vorname') .'</td>
                            <td><input type="text" name="vorname" value="" /></td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Nachname') .'</td>
                            <td><input type="text" name="nachname" value="" /></td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Email-Adresse') .'</td>
                            <td><input type="email" name="email" value="" /></td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Passwort') .'</td>
                            <td><input type="password" name="upw" value="" /></td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Daten senden?') .'</td>
                            <td>
                                <label>
                                    <input type="checkbox" name="password_email" value="1" />
                                    <span>'. $trans->__('Zugangsdaten nach Installation per Email zusenden?') .'</span>
                                </label>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><br /><button>'. $trans->__('weiter') .'</button></td>
                        </tr>
                    </table>
                </fieldset>
                
                <fieldset id="step3">
                    <legend>'. $trans->__('Optionale Einstellungen') .'</legend>
                    
                    <table class="daten">
                        <tr>
                            <td class="first">'. $trans->__('Basis-URL') .'</td>';
                            
                            $pfad = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['SCRIPT_NAME'];
                            $pf = explode('/', $pfad);
                            $pfa = '';
                            for($x=0; $x < count($pf)-2; $x++)
                                $pfa .= $pf[$x].'/';
                                
                            $last = substr($pfa, -1, 1);
                            $pfa = ($last == '/'?substr($pfa, 0, (strlen($pfa)-1)):$pfa);
                            
                            // DB Prefix
                            $dbprefix = 'f'.substr(Strings::createID(), 0, 4).'_';
                            
                            echo '
                            <td><input type="text" name="url" value="'.$pfa.'" /></td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Datenbank-Prefix') .'</td>
                            <td>
                                <input type="text" name="pre" value="'.$dbprefix.'" /><br />
                                <small>'. $trans->__('(wird aus Sicherheitsgründen zufällig generiert)') .'</small>
                            </td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Standard-Sprache') .'</td>
                            <td>
                                <select name="lan">
                                    <option value="de">'. $trans->__('Deutsch') .'</option>
                                    <option value="en">'. $trans->__('Englisch') .'</option>
                                    <option value="fr">'. $trans->__('Französisch') .'</option>
                                    <option value="es">'. $trans->__('Spanisch') .'</option>
                                    <option value="it">'. $trans->__('Italienisch') .'</option>
                                    <option value="ru">'. $trans->__('Russisch') .'</option>
                                    <option value="cn">'. $trans->__('Chinesisch') .'</option>
                                    <option value="jp">'. $trans->__('Japanisch') .'</option>
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <td class="first">'. $trans->__('Template') .'</td>
                            <td>
                                <select name="template">';
                                $ordner = "../content/templates";
                                $handle = opendir($ordner);
                                while ($file = readdir ($handle)) 
                                {
                                    if($file != "." && $file != "..") 
                                    {
                                        if(is_dir($ordner."/".$file)) 
                                        {
                                            $inc_template = $base->open_template_config($ordner."/".$file."/config.php");
                                            
                                            if($inc_template['name'])
                                                echo '<option value="'.$file.'"'.($file == 'default'?' selected="selected"':'').'>'.$inc_template['name'].'</option>';
                                        }
                                    }
                                }
                                closedir($handle);
                                echo '
                                </select>
                            </td>
                        </tr>';
                        
                        $rw_base = rtrim(stripslashes(dirname(dirname($_SERVER['SCRIPT_NAME']))).'/', '/');
                        if(!$rw_base) $rw_base = '/';
                        echo '
                        <tr>
                            <td class="first">'. $trans->__('RewriteBase') .'</td>
                            <td>
                                <input type="text" name="rewritebase" value="'.$rw_base.'" />
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2"><br /><button>'. $trans->__('weiter') .'</button></td>
                        </tr>
                    </table>
                </fieldset>
                
                <fieldset id="step4">
                    <legend>'. $trans->__('Installation') .'</legend>
                    
                    <button>'. $trans->__('fokus installieren') .'</button>
                </fieldset>
            </div>
        </div>
        </form>
        </td><td class="B3"></td></tr><tr><td class="C1"></td><td class="C2"></td><td class="C3"></td></tr><tr><td colspan="3" class="D"></td></tr></table>
    
    </div>
</body>
</html>';
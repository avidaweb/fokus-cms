<?php
define('IS_INSTALLATION', true, true);
error_reporting(0);

require('../inc/header.php');

$a = $fksdb->save($_REQUEST['a']);
$fa = $_REQUEST['fa'];  
parse_str($fa, $f); 

if($a == 1)
{ 
    $code = $fksdb->connect($fksdb->save($f['host']), $fksdb->save($f['user']), $fksdb->save($f['pw']), $fksdb->save($f['db']), true);
    
    if($code > 0)
        exit('fehler'.$code);
    else
        echo 'ok';
}
if($a == 2)
{ 
    if(!$fksdb->save($f['vorname'])) exit($trans->__('Sie müssen ihren Vornamen angeben'));
    if(!$fksdb->save($f['nachname'])) exit($trans->__('Sie müssen ihren Nachnamen angeben'));
    if(!$fksdb->save($f['email'])) exit($trans->__('Sie müssen ihre E-Mail-Adresse angeben'));
    if(!$base->is_valid_email($fksdb->save($f['email']))) exit($trans->__('Sie müssen eine korrekte E-Mail-Adresse angeben'));
    if(strlen($fksdb->save($f['upw'])) < 5) exit($trans->__('Das Passwort muss mindestens 5 Zeichen lang sein'));
    
    echo 'ok';
}
if($a == 3)
{ 
    $last = substr($fksdb->save($f['url']), -1, 1);
    
    if(!$fksdb->save($f['url'])) exit($trans->__('Sie müssen die Basis-URL angeben'));
    if($last == '/') exit($trans->__('Die Basis-URL darf nicht mit / enden'));
    if(!$fksdb->save($f['lan'])) exit($trans->__('Sie müssen die Standardsprache angeben'));
    if(!$fksdb->save($f['rewritebase'])) exit($trans->__('Sie müssen einen RewriteBase-Pfad angeben'));
    
    $htaccess_add = $base->generate_htaccess($fksdb->save($f['rewritebase']), $fksdb->save($f['url']));
    
    $filename = '../.htaccess';
    $htaccess = file_get_contents($filename);
            
    if(Strings::strExists('# START FKS', $htaccess, false))
        $htaccess = stripslashes(preg_replace('~# START FKS(.*)# END FKS~isU', preg_quote ($htaccess_add), $htaccess, 1)); 
    else
        $htaccess = $htaccess."
".$htaccess_add;

    if(!$handle = fopen($filename, "w"))
        exit('<div class="warnung">'.$trans->__('Kann die Datei .htaccess nicht öffnen').'</div>');

    if(!is_writable($filename))
        exit('<div class="warnung">'.$trans->__('Die Datei .htaccess ist nicht schreibbar').'</div>');
    
    if(!fwrite($handle, $htaccess))
        exit('<div class="warnung">'.$trans->__('Kann in die Datei .htaccess nicht schreiben').'</div>');
    
    echo 'ok';
}
if($a == 4)
{ 
    $pre = $fksdb->save($f['pre']);
    define('SQLPRE', $pre);
    
    $login_salt = Strings::createID();
    $passwort_salt = Strings::createID();
    $passwort_b_salt = substr(Strings::createID(), 2, 15);

    $database_wrapper = 'mysql';
    if(function_exists('mysqli_connect'))
        $database_wrapper = 'mysqli';
    //if(defined('PDO::ATTR_DRIVER_NAME'))
        //$database_wrapper = 'pdo';

    $config = '<?php 
error_reporting(0);

define(\'INSTALLED\', TRUE);

$dbserver =	\''.$fksdb->save($f['host']).'\';
$dbuser = \''.$fksdb->save($f['user']).'\';
$dbpw =	\''.$fksdb->save($f['pw']).'\';
$db = \''.$fksdb->save($f['db']).'\';

$domain = \''.$fksdb->save($f['url']).'\'; 

$standard_language = \''.$fksdb->save($f['lan']).'\';

$cookies = array(\'login\' => \''.Strings::createID().'\', \'pw\' => \''.Strings::createID().'\', \'ablauf\' => \''.Strings::createID().'\', \'rolle\' => \''.Strings::createID().'\');

$salts = array(\'login\' => \''.$login_salt.'\', \'password\' => \''.$passwort_salt.'\', \'password_b\' => \''.$passwort_b_salt.'\');

define(\'DBWRAPPER\', \''.$database_wrapper.'\');
define(\'SQLPRE\', \''.$pre.'\');
define(\'FOKUSKEY\', \''.$fksdb->save($f['key']).'\');
?>';

    $filename = '../fokus-config.php';

    if(!$handle = fopen($filename, "w"))
        exit('<div class="warnung">Kann die Datei fokus-config.php nicht öffnen</div>');

    if(!is_writable($filename))
        exit('<div class="warnung">Die Datei fokus-config.php ist nicht schreibbar</div>');

    if(!fwrite($handle, $config))
        exit('<div class="warnung">Kann in die Datei fokus-config.php nicht schreiben</div>');

    $ok .= 'fokus-config.php wurde erstellt...';
    fclose($handle);

    
    $code = $fksdb->connect($fksdb->save($f['host']), $fksdb->save($f['user']), $fksdb->save($f['pw']), $fksdb->save($f['db']), true);
    if($code > 0)
        exit('Datenbank-Fehler #'.$code);
    
    $fksdb->query("SET @@global.sql_mode= '';");
    $fksdb->query("SET sql_mode= '';");

    $query = "
    DROP TABLE IF EXISTS `".$pre."clipboard`;
    CREATE TABLE  `".$pre."clipboard` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
    `benutzer` INT( 11 ) NOT NULL DEFAULT '0' ,
    `timestamp` INT( 11 ) NOT NULL DEFAULT '0' ,
    `type` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
    `type2` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
    `aid` INT( 11 ) NOT NULL DEFAULT '0' ,
    `aid2` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
    `aid3` VARCHAR( 255 ) NOT NULL DEFAULT '',
      PRIMARY KEY (`id`),
      INDEX (  `benutzer`  ),
      INDEX (  `type` )
    ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
    

    DROP TABLE IF EXISTS `".$pre."blocks`;
    CREATE TABLE `".$pre."blocks` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `vid` varchar(255) NOT NULL DEFAULT '',
      `dokument` int(11) NOT NULL DEFAULT '0',
      `dversion` int(11) NOT NULL DEFAULT '0',
      `spalte` int(11) NOT NULL DEFAULT '0',
      `type` int(3) NOT NULL DEFAULT '0',
      `sort` int(11) NOT NULL DEFAULT '0',
      `html` longtext NOT NULL,
      `css` int(1) NOT NULL DEFAULT '0',
      `css_klasse` text NOT NULL,
      `color` varchar(25) NOT NULL DEFAULT '',
      `font` varchar(25) NOT NULL DEFAULT '',
      `bgcolor` varchar(25) NOT NULL DEFAULT '',
      `border` int(1) NOT NULL DEFAULT '0',
      `bordercolor` varchar(25) NOT NULL DEFAULT '',
      `padding` varchar(255) NOT NULL DEFAULT '',
      `margin` varchar(25) NOT NULL DEFAULT '',
      `align` int(1) NOT NULL DEFAULT '0',
      `spalten` int(2) NOT NULL DEFAULT '0',
      `bild` int(1) NOT NULL DEFAULT '0',
      `bildid` int(11) NOT NULL DEFAULT '0',
      `bildw` int(6) NOT NULL DEFAULT '0',
      `bildh` int(6) NOT NULL DEFAULT '0',
      `bildwt` int(1) NOT NULL DEFAULT '0',
      `bildp` int(1) NOT NULL DEFAULT '0',
      `bildt` longtext NOT NULL,
      `bild_extern` text NOT NULL,
      `teaser` longtext NOT NULL,
      `extb` text NOT NULL,
      `extb_content` longtext NOT NULL,
      PRIMARY KEY (`id`),
      INDEX (  `vid` ),
      INDEX (  `dokument` ),
      INDEX (  `dversion` ),
      INDEX (  `spalte` )
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."comments`;
    CREATE TABLE  `".$pre."comments` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
    `vid` VARCHAR( 128 ) NOT NULL DEFAULT '' ,
    `timestamp` INT( 11 ) NOT NULL DEFAULT '0' ,
    `ip` VARCHAR( 35 ) NOT NULL DEFAULT '' ,
    `benutzer` INT( 11 ) NOT NULL DEFAULT '0' ,
    `type` INT( 1 ) NOT NULL DEFAULT '0' ,
    `dokument` INT( 11 ) NOT NULL DEFAULT '0' ,
    `element` INT( 11 ) NOT NULL DEFAULT '0' ,
    `dk` INT( 11 ) NOT NULL DEFAULT '0' ,
    `frei` INT( 1 ) NOT NULL DEFAULT '0' ,
    `name` VARCHAR( 100 ) NOT NULL DEFAULT '' ,
    `email` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
    `web` VARCHAR( 500 ) NOT NULL DEFAULT '' ,
    `text` MEDIUMtext NOT NULL ,
      PRIMARY KEY (`id`),
    INDEX (  `vid` ),
    INDEX (  `frei` )
    ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."documents`;
    CREATE TABLE `".$pre."documents` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `dversion_edit` int(11) NOT NULL DEFAULT '0',
      `titel` varchar(255) NOT NULL DEFAULT '',
      `statusA` int(1) NOT NULL DEFAULT '0',
      `statusB` int(1) NOT NULL DEFAULT '0',
      `von` int(11) NOT NULL DEFAULT '0',
      `author` int(11) NOT NULL DEFAULT '0',
      `freigegeben` int(11) NOT NULL DEFAULT '0',
      `timestamp` int(11) NOT NULL DEFAULT '0',
      `von_edit` int(11) NOT NULL DEFAULT '0',
      `timestamp_edit` int(11) NOT NULL DEFAULT '0',
      `timestamp_freigegeben` int(11) NOT NULL DEFAULT '0',
      `datum` int(11) NOT NULL DEFAULT '0',
      `closed_to` int(11) NOT NULL DEFAULT '0',
      `closed_by` int(11) NOT NULL DEFAULT '0',
      `language` varchar(10) NOT NULL DEFAULT '',
      `seiten` int(10) NOT NULL DEFAULT '0',
      `anfang` int(11) NOT NULL DEFAULT '0',
      `bis` int(11) NOT NULL DEFAULT '0',
      `gesperrt` int(1) NOT NULL DEFAULT '0',
      `no_search` int(1) NOT NULL DEFAULT '0',
      `vorlage` varchar(255) NOT NULL DEFAULT '',
      `klasse` varchar(255) NOT NULL DEFAULT '',
      `produkt` varchar(255) NOT NULL DEFAULT '',
      `sprachen` text NOT NULL,
      `sprachenfelder` longtext NOT NULL,
      `zsb` longtext NOT NULL,
      `cf` longtext NOT NULL,
      `papierkorb` int(11) NOT NULL DEFAULT '0',
      `css_klasse` varchar(255) NOT NULL DEFAULT '',
      `kats` text NOT NULL,
      `rollen` text NOT NULL,
      `dk1` MEDIUMTEXT NOT NULL,
      `dkt1` INT( 3 ) NOT NULL DEFAULT '0',
      `dk2` MEDIUMTEXT NOT NULL,
      `dkt2` INT( 3 ) NOT NULL DEFAULT '0',
      `dk3` MEDIUMTEXT NOT NULL,
      `dkt3` INT( 3 ) NOT NULL DEFAULT '0',
      `dk4` MEDIUMTEXT NOT NULL,
      `dkt4` INT( 3 ) NOT NULL DEFAULT '0',
      `dk5` MEDIUMTEXT NOT NULL,
      `dkt5` INT( 3 ) NOT NULL DEFAULT '0',
      `dk6` MEDIUMTEXT NOT NULL,
      `dkt6` INT( 3 ) NOT NULL DEFAULT '0',
      `dk7` MEDIUMTEXT NOT NULL,
      `dkt7` INT( 3 ) NOT NULL DEFAULT '0',
      `dk8` MEDIUMTEXT NOT NULL,
      `dkt8` INT( 3 ) NOT NULL DEFAULT '0',
      `dk9` MEDIUMTEXT NOT NULL,
      `dkt9` INT( 3 ) NOT NULL DEFAULT '0',
      `dk10` MEDIUMTEXT NOT NULL,
      `dkt10` INT( 3 ) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `statusB` ),
      INDEX (  `gesperrt` ),
      INDEX (  `klasse` )
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."document_versions`;
    CREATE TABLE `".$pre."document_versions` (
      `id` BIGINT(15) unsigned NOT NULL AUTO_INCREMENT,
      `dokument` int(11) NOT NULL DEFAULT '0',
      `edit` int(1) NOT NULL DEFAULT '0',
      `ende` int(1) NOT NULL DEFAULT '0',
      `aktiv` int(1) NOT NULL DEFAULT '0',
      `timestamp` int(11) NOT NULL DEFAULT '0',
      `timestamp_freigegeben` int(11) NOT NULL DEFAULT '0',
      `timestamp_edit` int(11) NOT NULL DEFAULT '0',
      `klasse_inhalt` longtext NOT NULL,
      `von` int(11) NOT NULL DEFAULT '0',
      `von_freigegeben` int(11) NOT NULL DEFAULT '0',
      `language` varchar(10) NOT NULL DEFAULT '',
      `spaltennr` int(2) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `dokument` ),
      INDEX (  `edit` ),
      INDEX (  `ende` ),
      INDEX (  `aktiv` )
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."options`;
    CREATE TABLE `".$pre."options` (
      `id` int(1) NOT NULL,
      `version` float(8,2) NOT NULL DEFAULT '0.00',
      `changelog` float(8,2) NOT NULL DEFAULT '0.00',
      `firma` text NOT NULL,
      `vorname` text NOT NULL,
      `nachname` text NOT NULL,
      `str` text NOT NULL,
      `hn` text NOT NULL,
      `plz` text NOT NULL,
      `ort` text NOT NULL,
      `tel` text NOT NULL,
      `fax` text NOT NULL,
      `email` text NOT NULL,
      `web` text NOT NULL,
      `logout` int(11) NOT NULL DEFAULT '0',
      `sprachen` longtext NOT NULL,
      `extensions` longtext NOT NULL,
      `template` varchar(255) NOT NULL DEFAULT '',
      `next_check` int(11) NOT NULL DEFAULT '0',
      `vor_titel` text NOT NULL,
      `nach_titel` text NOT NULL,
      `last_sitemap` int(11) NOT NULL DEFAULT '0',
      `noseo` int(1) NOT NULL DEFAULT '0',
      `www` int(1) NOT NULL DEFAULT '0',
      `gzip` int(1) NOT NULL DEFAULT '0',
      `merge_css` int(1) NOT NULL DEFAULT '0',
      `merge_js` int(1) NOT NULL DEFAULT '0',
      `cf` longtext NOT NULL,
      `q_template` varchar(255) NOT NULL DEFAULT '',
      `q_template_mobile` varchar(255) NOT NULL DEFAULT '',
      `thumb_quality` int(3) NOT NULL DEFAULT '0',
      `rewritebase` varchar(255) NOT NULL DEFAULT '',
      `dk` longtext NOT NULL,
      `error_pages` longtext NOT NULL,
      `notiz` longtext NOT NULL,
      `notiz_time` int(11) NOT NULL DEFAULT '0',
      `notiz_von` int(11) NOT NULL DEFAULT '0',
      `login_captcha` int(1) NOT NULL DEFAULT '0',
      `login_captcha_rand` int(5) NOT NULL DEFAULT '0',
      `pindiv` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;

    
    DROP TABLE IF EXISTS `".$pre."elements`;
    CREATE TABLE `".$pre."elements` (
      `id` BIGINT(15) unsigned NOT NULL AUTO_INCREMENT,
      `struktur` int(11) NOT NULL DEFAULT '0',
      `element` int(11) NOT NULL DEFAULT '0',
      `sprachen` longtext NOT NULL,
      `cf` text NOT NULL,
      `slots` longtext NOT NULL,
      `frei` int(1) NOT NULL DEFAULT '0',
      `is_hidden` int(1) NOT NULL DEFAULT '0',
      `titel` varchar(255) NOT NULL DEFAULT '',
      `autor` int(1) NOT NULL DEFAULT '0',
      `sort` int(11) NOT NULL DEFAULT '0',
      `no_navi` int(1) NOT NULL DEFAULT '0',
      `trennlinie` int(1) NOT NULL DEFAULT '0',
      `templatedatei` text NOT NULL,
      `m_templatedatei` text NOT NULL,
      `neues_fenster` int(1) NOT NULL DEFAULT '0',
      `url` text NOT NULL,
      `dklasse` longtext NOT NULL,
      `klasse` varchar(255) NOT NULL DEFAULT '',
      `pklasse` longtext NOT NULL,
      `produkt` text NOT NULL,
      `rollen` longtext NOT NULL,
      `rollen_fehler` int(1) NOT NULL DEFAULT '0',
      `anfang` int(11) NOT NULL DEFAULT '0',
      `bis` int(11) NOT NULL DEFAULT '0',
      `noseo` int(1) NOT NULL DEFAULT '0',
      `nositemap` int(1) NOT NULL DEFAULT '0',
      `closed_to` int(11) NOT NULL DEFAULT '0',
      `closed_by` int(11) NOT NULL DEFAULT '0',
      `papierkorb` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `struktur` ),
      INDEX (  `element` ),
      INDEX (  `frei` )
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    DROP TABLE IF EXISTS `".$pre."file_versions`;
    CREATE TABLE  `".$pre."file_versions` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
    `stack` INT( 11 ) NOT NULL DEFAULT '0' ,
    `file` text NOT NULL ,
    `type` VARCHAR( 25 ) NOT NULL DEFAULT '' ,
    `timestamp` INT( 11 ) NOT NULL DEFAULT '0' ,
    `autor` int(11) NOT NULL DEFAULT '0' ,
    `width` INT( 5 ) NOT NULL DEFAULT '0' ,
    `height` INT( 5 ) NOT NULL DEFAULT '0' ,
    `ausrichtung` FLOAT NOT NULL DEFAULT '0' ,
    `grafik` INT( 1 ) NOT NULL DEFAULT '0' ,
    `downloads` INT( 11 ) NOT NULL DEFAULT '0' ,
    `mm_file` text NOT NULL ,
    `mm_type` VARCHAR( 25 ) NOT NULL DEFAULT '' ,
     PRIMARY KEY (`id`),
     INDEX (  `stack` )
    ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."companies`;
    CREATE TABLE `".$pre."companies` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `name` text NOT NULL,
      `str` text NOT NULL,
      `hn` text NOT NULL,
      `plz` text NOT NULL,
      `ort` text NOT NULL,
      `land` text NOT NULL,
      `telA` varchar(255) NOT NULL DEFAULT '',
      `telB` varchar(255) NOT NULL DEFAULT '',
      `telC` varchar(255) NOT NULL DEFAULT '',
      `fax` varchar(255) NOT NULL DEFAULT '',
      `email` text NOT NULL,
      `branche` text NOT NULL,
      `status` text NOT NULL,
      `tags` mediumtext NOT NULL,
      `papierkorb` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."records`;
    CREATE TABLE IF NOT EXISTS `".$pre."records` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `vid` varchar(255) NOT NULL DEFAULT '',
      `timestamp` int(11) NOT NULL DEFAULT '0',
      `ip` varchar(32) NOT NULL DEFAULT '',
      `benutzer` int(11) NOT NULL DEFAULT '0',
      `felder` longtext NOT NULL,
      PRIMARY KEY (`id`),
      INDEX (`vid`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."categories`;
    CREATE TABLE IF NOT EXISTS `".$pre."categories` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `name` varchar(255) NOT NULL DEFAULT '',
      `timestamp` int(11) NOT NULL DEFAULT '0',
      `kat` int(11) NOT NULL DEFAULT '0',
      `sort` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (`kat`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."recent_items`;
    CREATE TABLE  `".$pre."recent_items` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
    `benutzer` int(11) NOT NULL DEFAULT '0' ,
    `timestamp` int(11) NOT NULL DEFAULT '0' ,
    `aid` int(11) NOT NULL DEFAULT '0' ,
    `type` VARCHAR( 100 ) NOT NULL DEFAULT '',
    `papierkorb` int(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `benutzer`  ),
      INDEX (  `type` ),
      INDEX (  `papierkorb` )
    ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."livetalk`;
    CREATE TABLE  `".$pre."livetalk` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
    `benutzer` int(11) NOT NULL DEFAULT '0' ,
    `text` text NOT NULL ,
    `timestamp` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `timestamp` ) 
    ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."menus`;
    CREATE TABLE  `".$pre."menus` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `struktur` int(11) NOT NULL DEFAULT '0',
      `menue` varchar(225) NOT NULL DEFAULT '',
      `mid` int(11) NOT NULL DEFAULT '0',
      `sort` int(6) NOT NULL DEFAULT '0',
      `url` text NOT NULL,
      `sprachen` text NOT NULL,
      `ziel` int(1) NOT NULL DEFAULT '0',
      `power` int(1) NOT NULL DEFAULT '0',
      `titel` text NOT NULL,
      `klasse` text NOT NULL,
      PRIMARY KEY (`id`),
      INDEX (  `struktur` ),
      INDEX (  `menue` ) 
    ) ENGINE= MYISAM DEFAULT CHARSET=utf8;

    
    DROP TABLE IF EXISTS `".$pre."newsletters`;
    CREATE TABLE  `".$pre."newsletters` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT PRIMARY KEY ,
    `titel` text NOT NULL ,
    `timestamp` INT( 11 ) NOT NULL DEFAULT '0' ,
    `send` INT( 6 ) NOT NULL DEFAULT '0' ,
    `last_send` INT( 11 ) NOT NULL DEFAULT '0' ,
    `template` text NOT NULL ,
    `doks` LONGtext NOT NULL
    ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."notifications`;
    CREATE TABLE  `".$pre."notifications` (
    `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
    `user` INT( 11 ) NOT NULL DEFAULT '0' ,
    `time` INT( 11 ) NOT NULL DEFAULT '0' ,
    `title` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
    `click` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
    `message` text NOT NULL DEFAULT '' ,
      PRIMARY KEY (`id`),
      INDEX (  `user`  )
    ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."users`;
    CREATE TABLE `".$pre."users` (
      `id` BIGINT(15) unsigned NOT NULL AUTO_INCREMENT,
      `eid` varchar(255) NOT NULL DEFAULT '',
      `pw` text NOT NULL,
      `login` text NOT NULL,
      `email` text NOT NULL,
      `status` int(1) NOT NULL DEFAULT '0',
      `code` varchar(64) NOT NULL DEFAULT '',
      `avatar` BIGINT(15) NOT NULL DEFAULT '0',
      `anrede` varchar(255) NOT NULL DEFAULT '',
      `vorname` text NOT NULL,
      `nachname` text NOT NULL,
      `namenszusatz` text NOT NULL,
      `str` text NOT NULL,
      `hn` varchar(25) NOT NULL DEFAULT '',
      `plz` varchar(25) NOT NULL DEFAULT '',
      `ort` text NOT NULL,
      `land` text NOT NULL,
      `tel_p` text NOT NULL,
      `tel_g` text NOT NULL,
      `mobil` text NOT NULL,
      `fax` text NOT NULL,
      `tel_p_d` varchar(100) NOT NULL DEFAULT '',
      `tel_g_d` varchar(100) NOT NULL DEFAULT '',
      `fax_d` varchar(100) NOT NULL DEFAULT '',
      `firma` int(11) NOT NULL DEFAULT '0',
      `position` text NOT NULL,
      `tags` mediumtext NOT NULL,
      `type` int(1) NOT NULL DEFAULT '0',
      `registriert` int(11) NOT NULL DEFAULT '0',
      `registriert_von` varchar(11) NOT NULL DEFAULT '',
      `online` int(11) NOT NULL DEFAULT '0',
      `von` int(11) NOT NULL DEFAULT '0',
      `bis` int(11) NOT NULL DEFAULT '0',
      `widgets` longtext NOT NULL,
      `indiv` longtext NOT NULL,
      `cf` longtext NOT NULL,
      `notiz` longtext NOT NULL,
      `reset_hash` text NOT NULL,
      `reset_time` int(11) NOT NULL DEFAULT '0',
      `last_notifications` int(11) NOT NULL DEFAULT '0',
      `nachricht` text NOT NULL,
      `papierkorb` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `status` ) 
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."messages`;
    CREATE TABLE `".$pre."messages` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
    `vid` varchar(32) NOT NULL DEFAULT '',
    `benutzer` int(11) NOT NULL DEFAULT '0',
    `von` int(11) NOT NULL DEFAULT '0',
    `an` int(11) NOT NULL DEFAULT '0',
    `timestamp` int(11) NOT NULL DEFAULT '0',
    `text` mediumtext NOT NULL,
    `titel` text NOT NULL,
    `gelesen` int(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `vid` ),
      INDEX (  `benutzer`  ),
      INDEX (  `timestamp` )
    ) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."roles`;
    CREATE TABLE `".$pre."roles` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `titel` text NOT NULL,
      `beschr` text NOT NULL,
      `rechte` longtext NOT NULL,
      `frontend` int(1) NOT NULL DEFAULT '0',
      `fehler` longtext NOT NULL,
      `papierkorb` int(11) NOT NULL DEFAULT '0',
      `sort` int(5) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."user_roles`;
    CREATE TABLE `".$pre."user_roles` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `benutzer` int(11) NOT NULL DEFAULT '0',
      `rolle` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `benutzer`),
      INDEX (  `rolle` )
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."feeds`;
    CREATE TABLE `".$pre."feeds` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `block` varchar(64) NOT NULL,
      `dokument` int(11) NOT NULL,
      `home` int(1) NOT NULL,
      `element` int(11) NOT NULL,
      `titel` text NOT NULL,
      PRIMARY KEY (`id`),
      INDEX (  `element` )
    ) ENGINE=MYISAM  DEFAULT CHARSET=utf8 ;
    
    
    DROP TABLE IF EXISTS `".$pre."columns`;
    CREATE TABLE `".$pre."columns` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `vid` int(11) NOT NULL DEFAULT '0',
      `dokument` int(11) NOT NULL DEFAULT '0',
      `dversion` int(11) NOT NULL DEFAULT '0',
      `size` double NOT NULL,
      `sort` int(11) NOT NULL DEFAULT '0',
      `closed` int(1) NOT NULL DEFAULT '0',
      `css` int(1) NOT NULL DEFAULT '0',
      `css_klasse` text NOT NULL,
      `color` varchar(25) NOT NULL DEFAULT '',
      `bgcolor` varchar(25) NOT NULL DEFAULT '',
      `border` int(1) NOT NULL DEFAULT '0',
      `bordercolor` varchar(25) NOT NULL DEFAULT '',
      `padding` varchar(255) NOT NULL DEFAULT '',
      `align` int(1) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `vid` ),
      INDEX (  `dokument` ),
      INDEX (  `dversion` ) 
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."files`;
    CREATE TABLE `".$pre."files` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
    `kat` INT( 1 ) NOT NULL DEFAULT '0' ,
    `isdir` INT( 1 ) NOT NULL DEFAULT '0' ,
    `dir` INT( 11 ) NOT NULL DEFAULT '0' ,
    `titel` text NOT NULL ,
    `beschr` mediumtext NOT NULL ,
    `timestamp` INT( 11 ) NOT NULL DEFAULT '0' ,
    `sort` INT( 5 ) NOT NULL DEFAULT '0' ,
    `cropped` INT( 1 ) NOT NULL DEFAULT '0' ,
    `downloads` INT( 11 ) NOT NULL DEFAULT '0' ,
    `roles` longtext NOT NULL,
    `cf` longtext NOT NULL,
    `last_type` VARCHAR( 25 ) NOT NULL DEFAULT '' ,
    `last_timestamp` INT( 11 ) NOT NULL DEFAULT '0',
    `last_ausrichtung` FLOAT NOT NULL DEFAULT '0' ,
    `last_grafik` INT( 1 ) NOT NULL DEFAULT '0' ,
    `last_autor` int(11) NOT NULL DEFAULT '0',
      `papierkorb` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
    INDEX (  `kat`),
    INDEX (  `isdir` )
    ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."structures`;
    CREATE TABLE `".$pre."structures` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `titel` varchar(255) NOT NULL DEFAULT '',
      `a1` int(1) NOT NULL DEFAULT '0',
      `a2` int(1) NOT NULL DEFAULT '0',
      `snavi` longtext NOT NULL,
      `papierkorb` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`),
      INDEX (  `a1`  ),
      INDEX (  `a2` )
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."document_relations`;
    CREATE TABLE `".$pre."document_relations` (
      `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
      `element` int(11) NOT NULL DEFAULT '0',
      `slot` varchar(255) NOT NULL DEFAULT '',
      `error_page` text NOT NULL,
      `dokument` int(11) NOT NULL DEFAULT '0',
      `timestamp` int(11) NOT NULL DEFAULT '0',
      `sort` int(11) NOT NULL DEFAULT '0',
      `klasse` varchar(255) NOT NULL DEFAULT '',
      PRIMARY KEY (`id`),
      INDEX (  `element` ),
      INDEX (  `slot`  ),
      INDEX (  `dokument` )
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."searches`;
    CREATE TABLE  `".$pre."searches` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
    `q` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
    `results` INT( 4 ) NOT NULL DEFAULT '0' ,
    `timestamp` INT( 11 ) NOT NULL DEFAULT '0' ,
    `ip` VARCHAR( 35 ) NOT NULL DEFAULT '',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."stats`;
    CREATE TABLE `".$pre."stats` (
    `id` BIGINT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
    `hash` VARCHAR(512) NULL DEFAULT '',
    `visitor` VARCHAR(512) NULL DEFAULT '',
    `user` BIGINT(15) NULL DEFAULT NULL,
    `day` DATE NULL DEFAULT NULL,
    `time` BIGINT(15) NULL DEFAULT NULL,
    `element` BIGINT(15) NULL DEFAULT NULL,
    `document` BIGINT(15) NULL DEFAULT NULL,
    `referer` TEXT NULL,
    PRIMARY KEY (`id`),
    INDEX `hash` (`hash`(255)),
    INDEX `day` (`day`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."storage`;
    CREATE TABLE `".$pre."storage` (
    `id` BIGINT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(512) NOT NULL DEFAULT '',
    `base` VARCHAR(512) NOT NULL DEFAULT '',
    `value` LONGTEXT NOT NULL,
    `serialized` INT(1) NOT NULL DEFAULT '0',
    PRIMARY KEY (`id`),
    INDEX `name` (`name`(512)),
    INDEX `base` (`base`(512))
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
    
    DROP TABLE IF EXISTS `".$pre."responsibilities`;
    CREATE TABLE  `".$pre."responsibilities` (
    `id` BIGINT(15) NOT NULL AUTO_INCREMENT,
    `name` text NOT NULL,
    `papierkorb` int(11) NOT NULL DEFAULT '0',
      PRIMARY KEY (`id`)
    ) ENGINE=MyISAM DEFAULT CHARSET=utf8;";
    
    $sqlq = $fksdb->multiQuery($query);
    if(!$sqlq) $ok .= $trans->__('Datenbanktabellen wurden angelegt... ');
    else 
    { 
        $fehler = true;
        $ft .= $trans->__('Fehler bei Datenbanktabellen: %1<br />', false, array($sqlq));
    }
        
    $insert = $fksdb->insert($pre."options", array(
        "id" => 1,
        "version" => FKS_VERSION,
        "vorname" => $fksdb->save($f['vorname']),
        "nachname" => $fksdb->save($f['nachname']),
        "email" => $fksdb->save($f['email']),
        "logout" => 86400,
        "sprachen" => "a:1:{i:0;s:2:\"".$fksdb->save($f['lan'])."\";}",
        "template" => $fksdb->save($f['template']),
        "next_check" => (time() + 600),
        "thumb_quality" => 80,
        "rewritebase" => $fksdb->save($f['rewritebase']),
        "pindiv" => 1,
        "login_captcha_rand" => rand(1, 9999)
    ));
    if($insert) $ok .= $trans->__('Einstellungen wurden gesetzt... ');
    else 
    { 
        $fehler = true;
        $ft .= 'Fehler bei Einstellungen: '.$fksdb->getError().'<br />';
    }
    
    $login = $user->getLoginHash(1, $login_salt);
    $pw = $user->getPasswordHash($fksdb->save($f['upw']), $passwort_salt, $passwort_b_salt);
    
    $insert = $fksdb->insert($pre."users", array(
    	"pw" => $pw,
    	"login" => $login,
    	"vorname" => $fksdb->save($f['vorname']),
    	"nachname" => $fksdb->save($f['nachname']),
    	"email" => $fksdb->save($f['email']),
    	"registriert" => time(),
    	"registriert_von" => "system",
    	"type" => 2,
    	"status" => 0,
    	"papierkorb" => 0 
    ));
    if($insert) $ok .= $trans->__('Benutzer-Account wurde angelegt... ');
    else 
    { 
        $fehler = true;
        $ft .= $trans->__('Fehler bei Benutzer-Account: %1<br />', false, array($fksdb->getError()));
    }
    
    $insert = $fksdb->insert($pre."structures", array(
    	"titel" => $trans->__("Website-Struktur %1", false, array(date('Y'))),
    	"a1" => 1,
    	"a2" => 1,
    	"papierkorb" => 0
    ));    
    if($insert) $ok .= $trans->__('Struktur wurde gesetzt... ');
    else 
    { 
        $fehler = true;
        $ft .= $trans->__('Fehler bei Struktur: %1<br />', false, array($fksdb->getError()));
    }
    
    $insert = $fksdb->insert($pre."roles", array(
    	"titel" => $trans->__("Super Administrator"),
    	"frontend" => 1
    ));
    if($insert) $ok .= $trans->__('Rolle wurde angelegt... ');
    else 
    { 
        $fehler = true;
        $ft .= $trans->__('Fehler bei Rolle: %1<br />', false, array($fksdb->getError()));
    }
    
    $insert = $fksdb->insert($pre."user_roles", array(
    	"benutzer" => 1,
    	"rolle" => 1
    ));
    if($insert) $ok .= $trans->__('Benutzer-Account wurde der Rolle zugewiesen... ');
    else 
    { 
        $fehler = true;
        $ft .= $trans->__('Fehler bei Benutzer-Account-Zuordnung: %1<br />', false, array($fksdb->getError()));
    }
    
    if(!$fehler)
    {
        // send email
        $subject = $trans->__('CMS fokus wurde erfolgreich installiert');
        
        $emailmessage = 'Hallo '.$fksdb->save($f['vorname']).' '.$fksdb->save($f['nachname']).',
        
das CMS fokus wurde erfolgreich unter '.$fksdb->save($f['url']).($fksdb->save($f['key'])?' mit dem Lizenzschlüssel '.$fksdb->save($f['key']):'').' installiert.

FRONTEND
URL: '.$fksdb->save($f['url']).'/

BACKEND
URL: '.$fksdb->save($f['url']).'/fokus/
Benutzername: '.$fksdb->save($f['vorname']).' '.$fksdb->save($f['nachname']).' -oder- '.$fksdb->save($f['email']).'
Passwort: '.$fksdb->save($f['upw']).'

Hinweis: Mit dieser Email erhalten Sie das letzte Mal Ihre hinterlegten Zugangsdaten, bevor diese verschlüsselt in der Datenbank gespeichert werden.

Wir wünschen Ihnen viel Erfolg bei der Umsetzung Ihrer Webseite mit dem CMS fokus!
www.fokus-cms.de';

        if($f['password_email'] == 1)
            $base->email($f['email'], $subject, $emailmessage, $f['email']);
        
        
        // insert notification
        $api->insertNotification($trans->__('Installation abgeschlossen'), $trans->__('Das CMS fokus wurde erfolgreich installiert. Viel Spaß!'), '', 1);
    }
    else
    {
        @$handle = fopen('../fokus-config.php', "w"); 
        @fwrite($handle, '');
        @fclose($handle);
    }
    
    echo '
    <div class="ok" style="font-size:10px;"><em>'.$ok.'</em></div>
    
    '.($fehler?'
    <div class="warnung">
        <strong>'. $trans->__('Bei der fokus-Installation trat ein Fehler auf: Bitte kontaktieren Sie den technischen Support unter <a href="mailto:technik.fokus@avida-websolutions.de">technik.fokus@avida-websolutions.de</a> und übermitteln ihm folgende Fehlernachricht:</strong><br /><em>%1</em>', false, array($ft)) .'
    </div>':'
    <div class="ok">
        <strong>'. $trans->__('Das CMS fokus wurde erfolgreich installiert und kann nun verwendet werden!') .'</strong><br />
        <strong>'. $trans->__('Wichtig:</strong>Bitte löschen Sie umgehend den Ordner /installation/ im Fokus-Hauptverzeichnis. Ist dies geschehen klicken Sie auf <em>Weiter</em>, um das System zu nutzen. Bevor Sie den Ordner nicht entfernt haben, ist die Benutzung von Fokus aus Sicherheitsgründen nicht möglich.') .'<br /><br />
        <form action="../fokus/" method="get"><div><button>'. $trans->__('Weiter') .'</button></div></form>
    </div>');
}
?>
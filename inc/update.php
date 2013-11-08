<?php
$fv = floatval(FKS_VERSION);
$fv_db = floatval($this->getOpt('version'));
$query = '';

if($fv > 0 && $fv_db > 0 && $fv_db < $fv)
{ 
    $uv = 2011.01;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        DROP TABLE IF EXISTS `".SQLPRE."comment`;
        CREATE TABLE  `".SQLPRE."comment` (
        `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
        `vid` VARCHAR( 128 ) NOT NULL ,
        `timestamp` INT( 11 ) NOT NULL ,
        `ip` VARCHAR( 35 ) NOT NULL ,
        `benutzer` INT( 11 ) NOT NULL ,
        `type` INT( 1 ) NOT NULL ,
        `dokument` INT( 11 ) NOT NULL ,
        `element` INT( 11 ) NOT NULL ,
        `dk` INT( 11 ) NOT NULL ,
        `frei` INT( 1 ) NOT NULL ,
        `name` VARCHAR( 100 ) NOT NULL ,
        `email` VARCHAR( 255 ) NOT NULL ,
        `web` VARCHAR( 500 ) NOT NULL ,
        `text` MEDIUMTEXT NOT NULL ,
          PRIMARY KEY (`id`),
        INDEX (  `vid` ),
        INDEX (  `frei` )
        ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
    
        DROP TABLE IF EXISTS `".SQLPRE."suche`;
        CREATE TABLE  `".SQLPRE."suche` (
        `id` INT( 11 ) NOT NULL AUTO_INCREMENT,
        `q` VARCHAR( 255 ) NOT NULL ,
        `results` INT( 4 ) NOT NULL ,
        `timestamp` INT( 11 ) NOT NULL ,
        `ip` VARCHAR( 35 ) NOT NULL,
          PRIMARY KEY (`id`)
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
    
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';";
    } 
    
    $uv = 2011.02;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE  `".SQLPRE."einstellungen` ADD  `thumb_quality` INT( 3 ) NOT NULL;
    
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."', thumb_quality = '80';";
    } 
    
    $uv = 2011.03;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE  `".SQLPRE."einstellungen` ADD  `rewritebase` VARCHAR( 255 ) NOT NULL;
    
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."', rewritebase = '/';";
    }  
    
    $uv = 2011.05;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE  `".SQLPRE."dokument` ADD `rollen` TEXT NOT NULL;
    
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';";
    } 
    
    $uv = 2011.06;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE  `".SQLPRE."personen` ADD  `registriert_von` VARCHAR( 11 ) NOT NULL AFTER  `registriert`;
    
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';";
        
        // Listen-Elemente aendern
        $listQ = self::$fksdb->query("SELECT id, html FROM ".SQLPRE."block WHERE type = '20'");
        while($list = self::$fksdb->fetch($listQ))
        {
            $lhtml = $base->fixedUnserialize($list->html);
            if(!is_array($lhtml))
            {
                $newhtml = htmlspecialchars_decode($list->html);
                $elemente = explode('<br />', $newhtml); 
                if(!count($elemente))
                    $elemente = array();
                    
                for($xx = 0; $xx < count($elemente); $xx++)
                    $elemente[$xx] = htmlspecialchars($elemente[$xx]);
                    
                $seri = serialize($elemente);
                $listupdate = self::$fksdb->query("UPDATE ".SQLPRE."block SET html = '".$seri."' WHERE id = '".$list->id."' AND type = '20' LIMIT 1");
            }
        }
    }    
    
    $uv = 2011.07;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."personen ADD `indiv` LONGTEXT NOT NULL AFTER  `widgets`;
    
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';";
    }     
    
    $uv = 2011.08;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."dokument ADD  `dk1` MEDIUMTEXT NOT NULL , ADD  `dk2` MEDIUMTEXT NOT NULL ,ADD  `dk3` MEDIUMTEXT NOT NULL , ADD  `dk4` MEDIUMTEXT NOT NULL, ADD  `dkt1` INT( 3 ) NOT NULL DEFAULT '0' , ADD  `dkt2` INT( 3 ) NOT NULL DEFAULT '0' , ADD `dkt3` INT( 3 ) NOT NULL DEFAULT '0', ADD `dkt4` INT( 3 ) NOT NULL DEFAULT '0';
        ALTER TABLE ".SQLPRE."einstellungen ADD `dk` LONGTEXT NOT NULL;
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';";
    }   
    
    $uv = 2011.09;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."dokument ADD  `dk5` MEDIUMTEXT NOT NULL , ADD  `dk6` MEDIUMTEXT NOT NULL ,ADD  `dk7` MEDIUMTEXT NOT NULL , ADD  `dk8` MEDIUMTEXT NOT NULL, ADD  `dk9` MEDIUMTEXT NOT NULL , ADD  `dk10` MEDIUMTEXT NOT NULL, ADD `dkt5` INT( 3 ) NOT NULL DEFAULT '0' , ADD  `dkt6` INT( 3 ) NOT NULL DEFAULT '0' , ADD `dkt7` INT( 3 ) NOT NULL DEFAULT '0', ADD `dkt8` INT( 3 ) NOT NULL DEFAULT '0' , ADD `dkt9` INT( 3 ) NOT NULL DEFAULT '0', ADD `dkt10` INT( 3 ) NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';"; 
    }     
    
    $uv = 2011.10;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."dokument ADD `statusA` INT( 1 ) NOT NULL DEFAULT '0', ADD `statusB` INT( 1 ) NOT NULL DEFAULT '0';
        ALTER TABLE ".SQLPRE."dokument ADD INDEX (`statusB`);
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';"; 
    }     
    
    $uv = 2011.11;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."kats ADD `kat` INT( 11 ) NOT NULL DEFAULT '0', ADD `sort` INT( 11 ) NOT NULL DEFAULT '0';
        ALTER TABLE ".SQLPRE."kats ADD INDEX (  `kat` );
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';"; 
    }    
    
    $uv = 2011.12;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."element ADD `is_hidden` INT( 1 ) NOT NULL DEFAULT '0', ADD `cf` TEXT NOT NULL;
        
        DROP TABLE IF EXISTS `".SQLPRE."menue`;
        CREATE TABLE  `".SQLPRE."menue` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
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
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';"; 
    } 
    
    $uv = 2011.13;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."element ADD `nositemap` INT( 1 ) NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';"; 
    } 
    
    $uv = 2011.14;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."stack ADD `downloads` INT( 11 ) NOT NULL DEFAULT '0';
        ALTER TABLE ".SQLPRE."file ADD `downloads` INT( 11 ) NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';"; 
    }
    
    $uv = 2011.15;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        CREATE TABLE `".SQLPRE."rss` (
          `id` int(11) NOT NULL AUTO_INCREMENT,
          `block` varchar(64) NOT NULL,
          `dokument` int(11) NOT NULL,
          `home` int(1) NOT NULL,
          `element` int(11) NOT NULL,
          `titel` text NOT NULL,
          PRIMARY KEY (`id`),
          INDEX (  `element` )
        ) ENGINE=MYISAM  DEFAULT CHARSET=utf8 ;
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';";
        
        
        // Status bei bestehenden Dokumenten neu setzen
        $docq = self::$fksdb->query("SELECT id, timestamp_freigegeben, bis, anfang, gesperrt, dversion_edit FROM ".SQLPRE."dokument");
        while($doc = self::$fksdb->fetch($docq))
        {
            $dva = self::$fksdb->fetch("SELECT id FROM ".SQLPRE."dversion WHERE dokument = '".$doc->id."' AND aktiv = '1' LIMIT 1");
            $dve = self::$fksdb->fetch("SELECT ende, language, edit, id FROM ".SQLPRE."dversion WHERE dokument = '".$doc->id."' AND id = '".$doc->dversion_edit."' LIMIT 1");
            if($dva->id && $doc->timestamp_freigegeben && !$dve->ende && !$dve->edit)
                $statusA = 2;
            elseif($dve->edit && $dve->ende)
                $statusA = 1;
            else
                $statusA = 0;
                
            $updt_first = self::$fksdb->query("UPDATE ".SQLPRE."dokument SET statusA = '".$statusA."', statusB = '".$this->find_document_statusB($doc->gesperrt, $doc->anfang, $doc->bis, $doc->timestamp_freigegeben)."' WHERE id = '".$doc->id."' LIMIT 1");     
        } 
    } 
    
    $uv = 2011.16;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."dokument ADD `cf` longtext NOT NULL;
        ALTER TABLE ".SQLPRE."personen ADD `cf` longtext NOT NULL;
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';"; 
    } 
    
    $uv = 2011.17;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."einstellungen ADD `notiz` longtext NOT NULL, ADD `notiz_time` INT( 11 ) NOT NULL DEFAULT '0', ADD `notiz_von` INT( 11 ) NOT NULL DEFAULT '0', ADD `login_captcha` INT( 1 ) NOT NULL DEFAULT '0', ADD `login_captcha_rand` INT( 5 ) NOT NULL DEFAULT '0';
        ALTER TABLE ".SQLPRE."personen ADD `notiz` longtext NOT NULL;
        UPDATE ".SQLPRE."einstellungen SET login_captcha_rand = '".rand(1, 9999)."';
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."';"; 
    }  
    
    $uv = 2011.18;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."einstellungen ADD `changelog` float NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."', changelog = '".$uv."';"; 
    }   
    
    $uv = 2011.19;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."block ADD `extb` text NOT NULL, ADD `extb_content` longtext NOT NULL;
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."', changelog = '".$uv."';"; 
    }  
    
    $uv = 2011.20;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."einstellungen ADD `pindiv` INT( 11 ) NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."', changelog = '".$uv."';"; 
    }   
    
    $uv = 2012.01;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."', changelog = '".$uv."';"; 
    }   
    
    $uv = 2012.02;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."einstellungen ADD `q_template_mobile` varchar(255) NOT NULL DEFAULT '';
        ALTER TABLE ".SQLPRE."dokument ADD `no_search` int(1) NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."', changelog = '".$uv."';"; 
    }     
    
    $uv = 2012.03;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."', changelog = '".$uv."';"; 
    }     
    
    $uv = 2012.10;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        UPDATE ".SQLPRE."einstellungen SET version = '".$uv."', changelog = '".$uv."';"; 
    }     
    
    $uv = 2012.11;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        RENAME TABLE    ".SQLPRE."ablage TO ".SQLPRE."clipboard,
                        ".SQLPRE."block TO ".SQLPRE."blocks,
                        ".SQLPRE."comment TO ".SQLPRE."comments,
                        ".SQLPRE."dokument TO ".SQLPRE."documents,
                        ".SQLPRE."dversion TO ".SQLPRE."document_versions,
                        ".SQLPRE."einstellungen TO ".SQLPRE."options,
                        ".SQLPRE."element TO ".SQLPRE."elements,
                        ".SQLPRE."file TO ".SQLPRE."file_versions,
                        ".SQLPRE."firma TO ".SQLPRE."companies,
                        ".SQLPRE."form TO ".SQLPRE."records,
                        ".SQLPRE."kats TO ".SQLPRE."categories,
                        ".SQLPRE."last_use TO ".SQLPRE."recent_items,
                        ".SQLPRE."menue TO ".SQLPRE."menus,
                        ".SQLPRE."newsletter TO ".SQLPRE."newsletters,
                        ".SQLPRE."personen TO ".SQLPRE."users,
                        ".SQLPRE."pn TO ".SQLPRE."messages,
                        ".SQLPRE."rolle TO ".SQLPRE."roles,
                        ".SQLPRE."rolle_person TO ".SQLPRE."user_roles,
                        ".SQLPRE."rss TO ".SQLPRE."feeds,
                        ".SQLPRE."spalte TO ".SQLPRE."columns,
                        ".SQLPRE."stack TO ".SQLPRE."files,
                        ".SQLPRE."struktur TO ".SQLPRE."structures,
                        ".SQLPRE."struk_dok TO ".SQLPRE."document_relations,
                        ".SQLPRE."suche TO ".SQLPRE."searches,
                        ".SQLPRE."zsb TO ".SQLPRE."responsibilities;
        
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    }     
    
    $uv = 2012.12;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."columns ADD `closed` int(1) NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    }     
    
    $uv = 2012.13;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."options ADD `gzip` int(1) NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    }    
    
    $uv = 2012.14;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."files ADD `roles` longtext NOT NULL;
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    }    
    
    $uv = 2012.15;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."users ADD `reset_hash` text NOT NULL, ADD `reset_time` int(11) NOT NULL DEFAULT '0';
        ALTER TABLE ".SQLPRE."options ADD `error_pages` longtext NOT NULL;
        ALTER TABLE ".SQLPRE."document_relations ADD `error_page` text NOT NULL;
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
        
        $this->refreshHtaccess();
    }    
    
    $uv = 2012.20;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    }   
    
    $uv = 2012.21;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."elements ADD `slots` longtext NOT NULL;
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    }   
    
    $uv = 2012.22;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."columns ADD `vid` int(11) NOT NULL DEFAULT '0';
        UPDATE ".SQLPRE."columns SET vid = id;
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    } 
    
    $uv = 2012.30;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."documents ADD `author` int(11) NOT NULL DEFAULT '0';
        UPDATE ".SQLPRE."documents SET author = von;
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    } 
    
    $uv = 2012.31;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."files ADD `cf` longtext NOT NULL;
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    } 
    
    $uv = 2012.32;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        DROP TABLE IF EXISTS `".SQLPRE."notifications`;
        CREATE TABLE  `".SQLPRE."notifications` (
        `id` BIGINT( 20 ) NOT NULL AUTO_INCREMENT,
        `user` INT( 11 ) NOT NULL DEFAULT '0' ,
        `time` INT( 11 ) NOT NULL DEFAULT '0' ,
        `title` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
        `click` VARCHAR( 255 ) NOT NULL DEFAULT '' ,
        `message` text NOT NULL DEFAULT '' ,
          PRIMARY KEY (`id`),
          INDEX (  `user`  )
        ) ENGINE = MYISAM DEFAULT CHARSET=utf8;
        
        ALTER TABLE ".SQLPRE."users ADD `last_notifications` int(11) NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    }
    
    $uv = 2012.33;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        UPDATE ".SQLPRE."users SET widgets = '';
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    } 
    
    $uv = 2012.34;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        DROP TABLE IF EXISTS `".SQLPRE."stats`;
        CREATE TABLE `".SQLPRE."stats` (
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
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    } 
    
    $uv = 2012.35;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        ALTER TABLE ".SQLPRE."files ADD `cropped` INT(1) NOT NULL DEFAULT '0';
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
    } 
    
    $uv = 2012.36;
    if($fv_db < $uv && $fv >= $uv)    
    {
        $query .= "
        DROP TABLE IF EXISTS `".SQLPRE."storage`;
        CREATE TABLE `".SQLPRE."storage` (
        `id` BIGINT(15) UNSIGNED NOT NULL AUTO_INCREMENT,
        `name` VARCHAR(512) NOT NULL DEFAULT '',
        `base` VARCHAR(512) NOT NULL DEFAULT '',
        `value` LONGTEXT NOT NULL,
        `serialized` INT(1) NOT NULL DEFAULT '0',
        PRIMARY KEY (`id`),
        INDEX `name` (`name`(512)),
        INDEX `base` (`base`(512))
        ) ENGINE=MyISAM DEFAULT CHARSET=utf8;
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';"; 
        
        $this->refreshHtaccess();
    }

    $uv = 2012.37;
    if($fv_db < $uv && $fv >= $uv)
    {
        $query .= "
        ALTER TABLE ".SQLPRE."options ADD `extensions` longtext NOT NULL;
            
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';";
    }

    $uv = 2012.38;
    if($fv_db < $uv && $fv >= $uv)
    {
        $query .= "
        ALTER TABLE ".SQLPRE."users ADD `avatar` BIGINT(15) NOT NULL DEFAULT '0';

        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';";
    }

    $uv = 2012.39;
    if($fv_db < $uv && $fv >= $uv)
    {
        $query .= "
        ALTER TABLE ".SQLPRE."options ADD `merge_css` INT(1) NOT NULL DEFAULT '0', ADD `merge_js` INT(1) NOT NULL DEFAULT '0';

        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';";

        $this->refreshHtaccess();
    }

    $uv = 2013.10;
    if($fv_db < $uv && $fv >= $uv)
    {
        $query .= "
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';";
    }

    $uv = 2013.11;
    if($fv_db < $uv && $fv >= $uv)
    {
        $query .= "
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';";
    }

    $uv = 2013.20;
    if($fv_db < $uv && $fv >= $uv)
    {
        $query .= "
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';";
    }

    $uv = 2013.30;
    if($fv_db < $uv && $fv >= $uv)
    {
        $query .= "
        UPDATE ".SQLPRE."options SET version = '".$uv."', changelog = '".$uv."';";
    }
    
    
    if($query)
    {
        $sql_update = self::$fksdb->multiQuery($query);
        if($sql_update === false)
        {
            $error_msg = self::$trans->__('Falls das System nicht mehr korrekt funktioniert oder Probleme auftreten, kontaktieren Sie bitte ihren fokus Partner und senden Sie ihm folgende Fehlermeldung:<br /><em>%1</em>', false, array(self::$fksdb->getError()));
            $error_title = self::$trans->__('Beim Update Ihrer fokus Version trat ein Fehler auf');
            
            $this->printFrontendError($error_msg, $error_title);
        }
        
        $this->initOpt();
    }
}
?>
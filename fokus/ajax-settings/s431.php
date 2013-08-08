<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

ignore_user_abort(true);
set_time_limit(0);

if(!$user->r('fks', 'opt'))
    exit($user->noRights());
    
if($index == 's431')
{
    $a = $fksdb->save($_REQUEST['a']);
    
    if($a == 'allgemein')
    {
        $email = $fksdb->save($_POST['email']);
        $key = $fksdb->save($_POST['key']);
        
        $update = $fksdb->query("UPDATE ".SQLPRE."options SET email = '".$email."' WHERE id = '1'");
        
        if($key && $key != FOKUSKEY)
        {
            include('../fokus-config.php');
            
            if(!is_array($salts))
                $salts = array(); 
    
            $database_wrapper = (defined('DBWRAPPER')?DBWRAPPER:'mysql');
            
            $config = '<?php 
error_reporting(0);

define(\'INSTALLED\', TRUE);

$dbserver =	\''.$dbserver.'\';
$dbuser = \''.$dbuser.'\';
$dbpw =	\''.$dbpw.'\';
$db = \''.$db.'\';

$domain = \''.$domain.'\'; 

$standard_language = \''.$standard_language.'\';

$cookies = array(\'login\' => \''.$cookies['login'].'\', \'pw\' => \''.$cookies['pw'].'\', \'ablauf\' => \''.$cookies['ablauf'].'\', \'rolle\' => \''.$cookies['rolle'].'\');

$salts = array(\'login\' => \''.$salts['login'].'\', \'password\' => \''.$salts['password'].'\', \'password_b\' => \''.$salts['password_b'].'\');

define(\'DBWRAPPER\', \''.$database_wrapper.'\');
define(\'SQLPRE\', \''.SQLPRE.'\');
define(\'FOKUSKEY\', \''.$key.'\');
?>';

            $filename = '../fokus-config.php';
            if (!$handle = fopen($filename, "w")) 
                exit($trans->__('Kann die Datei fokus-config.php nicht öffnen'));
            if (!is_writable($filename)) 
                exit($trans->__('Die Datei fokus-config.php ist nicht schreibbar'));
            if (!fwrite($handle, $config)) 
                exit($trans->__('Kann in die Datei fokus-config.php nicht schreiben'));
        
            fclose($handle);    
        }
    }
    elseif($a == 'system')
    {
        $h = $fksdb->save($_POST['e_h'], 1);
        $m = $fksdb->save($_POST['e_m'], 1);
        $logout = $h * 3600 + $m * 60;
        $login_captcha = $fksdb->save($_POST['login_captcha']);
        $logcp = ($login_captcha == 'true'?1:0);
        
        $noseo = $fksdb->save($_POST['noseo']);
        $www = $fksdb->save($_POST['www']);
        $q_template = $fksdb->save($_POST['q_template']);
        $q_template_mobile = $fksdb->save($_POST['q_template_mobile']);
        $thumb_quality = $fksdb->save($_POST['thumb_quality'], 1);
        $rewritebase = $fksdb->save($_POST['rewritebase']);
        $gzip = $fksdb->save($_POST['gzip'], 1);
        $merge_css = $fksdb->save($_POST['merge_css'], 1);
        $merge_js = $fksdb->save($_POST['merge_js'], 1);
        
        $atitel = $_POST['atitel'];
        parse_str($atitel, $at); 
        
        foreach($at['vor_titel'] as $a => $k)
            $at['vor_titel'][$a] = htmlspecialchars($k);
        
        foreach($at['nach_titel'] as $a => $k)
            $at['nach_titel'][$a] = htmlspecialchars($k);
        
        foreach((array)$at['cf'] as $a => $k)
        {
            foreach($k as $d => $p)
                $at['cf'][$a][$d] = stripslashes($p);
        }

        $fksdb->update("options", array(
            "logout" => $logout,
            "login_captcha" => $logcp,
            "noseo" => $noseo,
            "www" => $www,
            "q_template" => $q_template,
            "q_template_mobile" => $q_template_mobile,
            "vor_titel" => serialize($at['vor_titel']),
            "nach_titel" => serialize($at['nach_titel']),
            "cf" => $base->array_to_db($at['cf']),
            "thumb_quality" => $thumb_quality,
            "gzip" => $gzip,
            "merge_css" => $merge_css,
            "merge_js" => $merge_js
        ), array(
            "id" => 1
        ), 1);
        
        if($rewritebase && $rewritebase != $base->getOpt('rewritebase'))
        {
            $htaccess_add = $base->generate_htaccess($rewritebase);

            $filename = '../.htaccess';
            $htaccess = file_get_contents($filename);
            
            if(Strings::strExists('# START FKS', $htaccess, false))
                $htaccess = stripslashes(preg_replace('~# START FKS(.*)# END FKS~isU', preg_quote ($htaccess_add), $htaccess, 1)); 
            else
                $htaccess = $htaccess."
".$htaccess_add;
        
            if (!$handle = fopen($filename, "w")) 
                exit($trans->__('Kann die Datei .htaccess nicht öffnen'));
            if (!is_writable($filename)) 
                exit($trans->__('Die Datei .htaccess ist nicht schreibbar'));
            if (!fwrite($handle, $htaccess)) 
                exit($trans->__('Kann in die Datei .htaccess nicht schreiben'));
        
            fclose($handle);

            $fksdb->update("options", array(
                "rewritebase" => $rewritebase
            ), array(
                "id" => 1
            ), 1);
        }
    }
    elseif($a == 'templates')
    {
        $t = $fksdb->save($_POST['t']);

        $fksdb->update("options", array(
            "template" => $t
        ), array(
            "id" => 1
        ), 1);
    }
    elseif($a == 'sprachen')
    {
        $sprachenA = $_POST['sprachen'];
        parse_str($sprachenA, $sp);
        
        $max = ($suite->getLimitOfLanguages() == -1?99999:$suite->getLimitOfLanguages());
        
        $xc = 0;
        for($x = 0; $x < 999; $x ++)
        {
            if(!empty($sp['land'][$x]))
            {
                $sprache[] = strtolower($fksdb->save($sp['land'][$x]));
                
                $xc ++;
                if($xc >= $max)
                    break;
            }
        }
        
        $update = $fksdb->query("UPDATE ".SQLPRE."options SET sprachen = '".serialize($sprache)."' WHERE id = '1'");
    }
    elseif($a == 'dk')
    {
        parse_str($_POST['dka'], $dk);
        $dkseri = $base->array_to_db($dk);
        
        $update = $fksdb->query("UPDATE ".SQLPRE."options SET dk = '".$dkseri."' WHERE id = '1'");
        $base->initOpt();
        
        // Bestehende Dokumentenklassen neu kalkulieren        
        $docq = $fksdb->query("SELECT id FROM ".SQLPRE."documents WHERE klasse != ''");
        while($doc = $fksdb->fetch($docq))
        {
            $base->create_dk_snippet($doc->id, true);        
        }
    }
    elseif($a == 'backup' && $suite->rm(12))
    {
        $tabellen = array();
        $result = $fksdb->query("SHOW TABLES FROM ".$db);
        while ($row = mysql_fetch_row($result)) {
            $tabellen[] = str_replace(SQLPRE, '', trim($row[0]));
        } 
        if(!count($tabellen))
            exit('ende');
         
        $count = $fksdb->save($_POST['count']);
        $email = $fksdb->save($_POST['email']);
        $file = '../content/export/db_dump.fks';
        
        if($count >= count($tabellen))
        {
            echo 'ende';
            
            $subject = 'SQL-Dump: '.DOMAIN;
            $message = $trans->__('SQL-Dump %1 vom %2 um %3 Uhr durch den Benutzer %4', false, array(
                DOMAIN, 
                date('d.m.Y'), 
                date('H:i'), 
                $user->data('vorname').' '.$user->data('nachname')
            ));
            
            $id = Strings::createID();
            $dateiname_mail = 'fks-sql-dump-'.date('d-m-y').'.fks';
            $dateiinhalt = fread(fopen($file, "r"), filesize($file));
            
            $kopf = "From: ".$base->getOpt()->email." <".$base->getOpt()->email.">\n";
            $kopf .= "MIME-Version: 1.0\n";
            $kopf .= "Content-Type: multipart/mixed; boundary=$id\n\n";
            $kopf .= "This is a multi-part message in MIME format\n";
            $kopf .= "--".$id."\n";
            $kopf .= "Content-Type: text/plain\n";
            $kopf .= "Content-Transfer-Encoding: 8bit\n\n";
            $kopf .= $message; // Inhalt der E-Mail (Body)
            $kopf .= "\n--".$id."";
            $kopf .= "\nContent-Type: application/json; name=".$dateiname_mail."\n";
            $kopf .= "Content-Transfer-Encoding: base64\n";
            $kopf .= "Content-Disposition: attachment; filename=".$dateiname_mail."\n\n";
            $kopf .= chunk_split(base64_encode($dateiinhalt));
    $kopf .= "\n--".$id."--";
            
            if(mail($email, $subject, "", $kopf))
                unlink($file);
        }
        else
        {
            if($handle = fopen($file, (!$count?"w":"a"))) 
            {
                $table = $tabellen[$count];
                
                $content = '';
                if(!$count)
                {
                    $content = '{
"backup_ok": true,
"backup_tables": '.count($tabellen).',
"backup_time": '.time().',
"backup": {';    
                }
                
                
                $content .= (!$count?'':', ').'"'.$table.'": ['; 
                
                $inner_count = 0;
                $result = $fksdb->query("SELECT * FROM ".SQLPRE.$table) OR $content = "# ".$table." nicht vorhanden\n #".$fksdb->getError(); 
                while($row = @mysql_fetch_row($result)) 
                { 
                    $insert = (!$inner_count?'':', ').'{'; 

                    for($j=0; $j<mysql_num_fields($result); $j++) 
                    { 
                        $meta = mysql_fetch_field($result, $j);
                        $insert .= ($j == 0?'':', ').'"'.$meta->name.'": ';
                        
                        if(!isset($row[$j])) $insert .= '""'; 
                        else if($row[$j] != "") $insert .= '"'.base64_encode(gzcompress($row[$j], 1)).'"'; 
                        else $insert .= '""'; 
                    } 
                    
                    $insert .= '}'; 
                    $content .= $insert; 
                    
                    $inner_count ++;
                } 
                
                $content .= ']';
                $content .= ($count + 1 == count($tabellen)?'}}':'');
                
                fwrite($handle, $content);
                fclose($handle);
                
                echo ($count + 1).'|||'.count($tabellen).'|||'.$tabellen[$count];
            }
            else
            {
                echo 'fehler';
            }
        }
    }
    elseif($a == 'import' && $suite->rm(12))
    {
        @ini_set('memory_limit', '200M');
        
        parse_str($_POST['f'], $f);
           
        $pw2 = $fksdb->save($f['best_pw']);
        $pw = $user->getPasswordHash($pw2);
        if(!$pw2)   
            exit($trans->__('Es wurde kein Bestätigungs-Passwort eingegeben'));
        if($pw != $user->data('pw'))   
            exit($trans->__('Bestätigungs-Passwort wurde nicht korrekt eingegeben')); 
            
        $datei = '../content/export/db_import_tmp.fks'; 
        if(!file_exists($datei))
            exit($trans->__('fokus-Dump wurde nicht korrekt hochgeladen'));
            
        $dinhalt = file_get_contents($datei);
        if(!$dinhalt)
            exit($trans->__('fokus-Dump konnte ich eingelesen werden')); 
            
        $json = json_decode($dinhalt, true);
        if(!is_array($json))
            exit($trans->__('Bei der hochgeladenen Datei handelt es sich um kein valides fokus-Dump-Format'));
            
        if(!$json['backup_ok'] || !is_array($json['backup']))
            exit($trans->__('Bei der hochgeladenen Datei handelt es sich um keinen validen fokus-Dump'));
            
        $bu = $json['backup'];
        
        foreach($bu as $table => $val)
        {
            $fksdb->query("TRUNCATE TABLE ".SQLPRE.$table);
            
            foreach($val as $r)
            {
                $data = array();
                foreach($r as $k => $w)
                {
                    $value = ($w?gzuncompress(base64_decode($w)):'');
                    $data[$k] = $value;
                }
                
                $fksdb->insert($table, $data);
            }
        }
            
        unlink($datei);  
        exit('ok');
    }
    elseif($a == 'import_dump' && $suite->rm(12))
    {
        @ini_set('upload_max_filesize', '128M');
        @ini_set('memory_limit', '200M');
        
        $datei = '../content/export/db_import_tmp.fks';
        $file_old = $fksdb->save($_POST['file']);
        
        if(!file_exists($file_old))
            exit('uploaded file '.$file_old.' not found');
        
        if(!copy($file_old, $datei))
            exit('error');
            
        if(file_exists($datei))
            exit('ok');
            
        exit('error');
    } 
    elseif($a == 'error_pages')
    {
        parse_str($_POST['fa'], $f);
        $fs = $base->array_to_db($f);
        
        $fksdb->query("UPDATE ".SQLPRE."options SET error_pages = '".$fs."' WHERE id = '1' LIMIT 1");
    }
    
}     
?>
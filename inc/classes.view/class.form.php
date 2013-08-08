<?php
function validate_field($base, $opt, $wert, $type)
{ 
    if($opt['pflicht'])
    { 
        if($type == 'text' || $type == 'textarea' || $type == 'password')
        { 
            if($opt['pflicht_laenge'] == 'min_1' && strlen($wert) < 1)
                return 'Dieses Feld ist ein Pflichtfeld';
            elseif($opt['pflicht_laenge'] == 'min_2' && strlen($wert) < 3)
                return 'Dieses Feld ist ein Pflichtfeld (min. 3 Zeichen)';
            elseif($opt['pflicht_laenge'] == 'min_3' && strlen($wert) < 5)
                return 'Dieses Feld ist ein Pflichtfeld (min. 5 Zeichen)';
            elseif($opt['pflicht_laenge'] == 'min_4' && strlen($wert) < 15)
                return 'Dieses Feld ist ein Pflichtfeld (min. 15 Zeichen)';
            elseif($opt['pflicht_laenge'] == 'min_5' && strlen($wert) < 200)
                return 'Dieses Feld ist ein Pflichtfeld (min. 200 Zeichen)';
        }
        elseif($type == 'checkbox')
        {
            if($opt['pflicht_laenge'] == 'is_checked' && $wert != 'Ja')
                return 'Feld muss ausgew&auml;hlt sein';
            elseif($opt['pflicht_laenge'] == 'not_checked' && $wert == 'Ja')
                return 'Feld darf nicht ausgew&auml;hlt sein';
        }
        elseif($type == 'radio' || $type == 'select')
        {
            if(!$wert)
                return 'Kein Eintrag gew&auml;hlt';
        }
    }
    
    if($opt['pflicht_zeichen'] && $wert)
    {
        if($opt['pflicht_zeichen'] == 'email' && !$base->is_valid_email($wert))
        {
            return 'Keine valide E-Mail-Adresse';
        }
        elseif($opt['pflicht_zeichen'] == 'url')
        {
            if(!Strings::strExists('http', $wert, false))
                $wert = 'http://'.$wert;
            
            if(!$base->is_valid_url($wert))
                return 'Keine valide URL';
        }
        elseif($opt['pflicht_zeichen'] == 'int')
        {
            $neuerText = preg_replace("/[^0-9 ]/", "", $wert);
            if($neuerText != $wert) 
                return 'Nur Ziffern erlaubt';
        }
        elseif($opt['pflicht_zeichen'] == 'alpha')
        {
            $neuerText = preg_replace("/[^a-zA-ZäöüÄÖÜß ]/", "", $wert);
            if($neuerText != $wert) 
                return 'Nur Buchstaben erlaubt';
        }
        elseif($opt['pflicht_zeichen'] == 'k_sonder')
        {
            $neuerText = preg_replace("/[^a-zA-Z0-9äöüÄÖÜß ]/", "", $wert);
            if($neuerText != $wert) 
                return 'Keine Sonderzeichen erlaubt';
        }
    }
    
    return '';
}

if($_POST['fokus_form_send'])
{
    $v = self::$base->vars('POST');
    
    $vid = $v['fokus_form_id'];
    
    $kkQ = self::$fksdb->query("SELECT html FROM ".SQLPRE."blocks WHERE type = '52' AND vid = '".$vid."' ORDER BY id DESC LIMIT 1");
    while($kk = self::$fksdb->fetch($kkQ))
    {
        $fo = self::$base->fixedUnserialize($kk->html);
        
        if(!is_array($fo)) $fo = array();
        $fa = $fo['f'];
        if(!is_array($fa)) $fa = array();
        
        if(!is_array($fo['aktion'])) $fo['aktion'] = array();
        if(in_array(1, $fo['aktion']))
            $zo = self::$base->db_to_array($fo['zuordnung_benutzer']);
        
        $feldnamen_in_use = array();
        $form_error = array();
        $ergebnis = array();
        
        $faB = array(); 
        foreach($fa as $f_id => $f)
        {
            if($f['type'] == 'string')
                continue;
                
            $opt = self::$base->db_to_array($f['opt']);
            
            $urlname = self::$base->slug($f['name']);
            while(in_array($urlname, $feldnamen_in_use))
                $urlname .= 'Z';
            $feldnamen_in_use[] = $urlname;
            
            $wert = $v[$urlname];
            
            if($f['type'] == 'radio' || $f['type'] == 'select')
            {
                $auswahl = $opt['auswahl'];
                if(!is_array($auswahl))
                    $auswahl = array();    
                    
                $wert = $auswahl[$wert]; 
            }
            elseif($f['type'] == 'checkbox')
            {
                $wert = ($wert == 'true'?'Ja':'Nein');
            }
            elseif($f['type'] == 'img')
            {
                require_once(ROOT.'inc/classes.other/class.upload.php');
            
                $upload = new Upload(self::$static, $urlname, array(
                    'cat' => 0,
                    'dir' => $opt['dir']
                ));
                
                $upload_status = $upload->getStatus();
                unset($upload);
                
                $wert = '';
                if($upload_status['status'] == 'error')
                {
                    $error_message = array(
                        'wrong filetype' => 'Die hochgeladene Datei gehört nicht zu den gültigen Dateitypen (jpg, png, gif)',
                        'upload failed' => 'Der Upload ist fehlgeschlagen (eventuell ist die hochgeladene Datei zu groß)'
                    );
                    
                    $form_error[$urlname] = '<span>'.$f['name'].'</span>: '.$error_message[$upload_status['error']];
                }
                elseif($upload_status['status'] == 'ok')
                {
                    $wert = self::$base->array_to_db($upload_status);
                }
            }
            
            $val = validate_field(self::$base, $opt, $wert, $f['type']);
            if($val) // Fehler
            {
                $form_error[$urlname] = '<span>'.$f['name'].'</span>: '.$val;
            }
            else
            {
                $ergebnis[$f_id] = array('value' => $wert, 'name' => $f['name'], 'field' => $urlname, 'type' => $f['type']); 
                
                // duplicate email check
                if(in_array(1, $fo['aktion']) && is_array($zo))
                {
                    if($zo['feld'][$f_id] == 'email')
                    {
                        $mail_exists = self::$fksdb->fetch("SELECT id FROM ".SQLPRE."users WHERE email LIKE '".$wert."' LIMIT 1");
                        if($mail_exists)
                            $form_error[$urlname] = '<span>'.$f['name'].'</span>: Mit dieser Emailadresse ist bereits ein Benutzer registriert';
                    }
                }
            } 
        }   
        
        $db_wert = self::$base->array_to_db($ergebnis);
        
        ////////// Globale Validierung ////////////
        // Hash Check
        if(!self::$api->checkAjaxHash())
            $form_error[0] = 'Formular wurde nicht korrekt abgeschickt (Fehlercode: 101). Sie sollten die Seite aktualisieren.';

        // Zeitfenster überprüfen
        if(self::$api->execute_filter('fks_form_validate_time', ($v['fokus_form_send'] > time() - 2?true:false), array('submited' => $v['fokus_form_send'])))
            $form_error[0] = 'Es trat ein unbekannter Fehler auf: Bitte versuchen Sie es erneut (Fehlercode: 102)';
            
        // Honeypot passives Captcha
        if(self::$api->execute_filter('fks_form_validate_honeypot', (!empty($v[$v['fokus_form_check']])?true:false), array('honeypot' => $v[$v['fokus_form_check']])))
            $form_error[0] = 'Es trat ein unbekannter Fehler auf: Bitte versuchen Sie es erneut (Fehlercode: 103)';
            
        // Referer mit Host vergleichen
        $ref = parse_url($_SERVER['HTTP_REFERER']);
        $self = $_SERVER['HTTP_HOST'];
        if(self::$api->execute_filter('fks_form_validate_referer', ($_SERVER['HTTP_REFERER'] && !Strings::strExists($self, $ref['host']) && !Strings::strExists($ref['host'], $self)?true:false), array('referer' => $_SERVER['HTTP_REFERER'], 'self' => $self)))
            $form_error[1] = 'Anfragen auf das Formular dürfen nur direkt über die Webseite gestellt werden';
            
        // Doppelte Einträge vermeiden
        $check_last_ip = self::$fksdb->count(self::$fksdb->query("SELECT id FROM ".SQLPRE."records WHERE vid = '".$vid."' AND ip = '".self::$api->getVisitorIP()."' AND timestamp > '".(time() - 30)."' LIMIT 1")); 
        if(self::$api->execute_filter('fks_form_validate_duplicate_30', ($check_last_ip?true:false), array()))
            $form_error[2] = 'Sie haben innerhalb der letzten 30 Sekunden bereits einen Eintrag in diesem Formular vorgenommen';
            
        $check_last_ip = self::$fksdb->count(self::$fksdb->query("SELECT id FROM ".SQLPRE."records WHERE vid = '".$vid."' AND ip = '".self::$api->getVisitorIP()."' AND felder = '".$db_wert."' AND timestamp > '".(time() - 600)."' LIMIT 1")); 
        if(self::$api->execute_filter('fks_form_validate_duplicate_600', ($check_last_ip?true:false), array()))
            $form_error[2] = 'Sie haben innerhalb der letzten f&uuml;nf Minuten bereits einen Eintrag mit den selben Werten in diesem Formular vorgenommen';
        /////
        
        if(count($form_error))
        {
            $form_error['fks_form_id'] = $vid;
        }
        
        $this->form_error = $form_error;
        
        if(!count($form_error))
        {
            // In Datenbank eintragen
            self::$fksdb->insert("records", array(
            	"vid" => $vid,
            	"timestamp" => time(),
            	"ip" => self::$api->getVisitorIP(),
            	"benutzer" => self::$user->getID(),
            	"felder" => $db_wert 
            ));
            
            // Email an Admin
            if(!$fo['save'] && $fo['saveEmail'])
            {
                $msg = 'Es ist ein neuer Datensatz im Formular "'.$fo['name'].'" eingegangen.
                
Webseite: '.$this->getURL().'
Zeitpunkt: '.date('d.m.Y').' - '.date('H:i').' Uhr
IP-Adresse: '.self::$api->getVisitorIP().'

';
                foreach($ergebnis as $a)
                {
                    if($a['type'] == 'img')
                    {
                        $valA = self::$base->db_to_array($a['value']);
                        $a['value'] = ($valA['status'] == 'ok'?$valA['url']:'');
                    }
                    
                    $msg .= $a['name'].':
'.$a['value'].'

';
                }
                
                $betreff = $fo['name'].': Neuer Datensatz ('.$this->getDomain().')';
                
                self::$base->email($fo['saveEmail'], $betreff, $msg, self::$base->getOpt()->email);
            }
            
            // Neuen Benutzer anlegen
            if(in_array(1, $fo['aktion']))
            {
                $p_sql = "";
                $p_array = array();
                $mailtext = $zo['mailtext'];
                $mailbetreff = $zo['betreff'];
                $email = '';
                
                foreach($ergebnis as $k => $a)
                {
                    if($a['type'] == 'img')
                        continue;
                    
                    if($zo['feld'][$k])
                    {
                        $zname = $zo['feld'][$k];
                        $zwert = $a['value'];
                        
                        if($zname == 'pw')
                            $zwert = self::$user->getPasswordHash($zwert);
                        if($zname == 'email')
                            $email = $zwert;
                        
                        $p_array[$zname] .= ($p_array[$zname]?', ':'').$zwert;
                    }
                    
                    if($zo['mail'])
                    {
                        $mailbetreff = str_replace('['.$a['field'].']', ($a['value']), $mailbetreff);
                        $mailtext = str_replace('['.$a['field'].']', ($a['value']), $mailtext);
                    }
                }
                
                for($x = 0; $x < count($zo['notiz']); $x++)
                {
                    $zname = self::$fksdb->save($zo['notiz_feld'][$x]);
                    $zwert = self::$fksdb->save($zo['notiz'][$x]);
                        
                    if($zname == 'pw')
                        $zwert = self::$user->getPasswordHash($zwert);
                    
                    $p_array[$zname] .= ($p_array[$zname]?', ':'').$zwert;
                }
                    
                if($zo['type'] > 2)
                    $zo['type'] = 0;
                    
                $user_add = array(
                	"status" => $zo['status'],
                	"type" => $zo['type'],
                	"registriert" => time(),
                	"registriert_von" => "form"
                );
                
                foreach($p_array as $k => $a)
                    $user_add[$k] = $a;
                
                // Benutzer anlegen  
                self::$fksdb->insert("users", $user_add);
                $pid = self::$fksdb->getInsertedID();
                    
                $acode = ($zo['status'] == 1?Strings::createID():0);
                
                // Login Variable updaten
                $login = self::$user->getLoginHash($pid);
                $update = self::$fksdb->query("UPDATE ".SQLPRE."users SET login = '".$login."', code = '".$acode."' WHERE id = '".$pid."' LIMIT 1");
                
                // Benutzer Rollen zuordnen
                foreach($zo['rollen'] as $ro)
                {
                    if($ro != 1)
                    {
                        self::$fksdb->insert("user_roles", array(
                        	"benutzer" => $pid,
                        	"rolle" => $ro
                        ));
                    }
                }
                
                if($zo['mail'] && $mailtext && $email)
                {
                    if($zo['status'] == 1)
                    {
                        $activate_link = $this->getDomain().'activate/'.$vid.'/'.$acode.'/';
                        $mailtext = str_replace('[aktivierungslink]', $activate_link, $mailtext);
                    }
                    
                    $betreff = ($mailbetreff?$mailbetreff:$fo['name']);
                    $mailtext = html_entity_decode(htmlspecialchars_decode(strip_tags(Strings::br2nl($mailtext))), ENT_QUOTES, 'UTF-8');
                    
                    self::$base->email($email, $betreff, $mailtext, self::$base->getOpt()->email);
                }
            }
            
            // Neues Dokument oder Produkt anlegen
            for($lauf = 2; $lauf <= 3; $lauf ++)
            {
                if(in_array($lauf, $fo['aktion']))
                {
                    $type = ($lauf == 2?'d':'p');    
                    
                    $lan = $this->getLanguage(true);
                    $sp = array($lan);
                    $spmore[$lan]['titel'] = $fo['name'];
                    $int_title = $fo['name'];
                    
                    $zo = self::$base->db_to_array($fo['zuordnung_'.($type == 'd'?'dokument':'produkt')]);
                    
                    if(count($zo['feld_meta']))
                    {
                        $meta_values = array(
                            'title' => 'titel',
                            'meta_title' => 'htitel',
                            'meta_descr' => 'desc',
                            'meta_keywords' => 'tags'
                        );
                        
                        foreach($meta_values as $mkey => $mval)
                        {  
                            $rev_key = $zo['feld_meta'][$mkey];
                            if(!$rev_key)
                                continue;
                                
                            $rev_values = $ergebnis[$rev_key]['value'];
                            if(!$rev_values)
                                continue;
                                
                            $spmore[$lan][$mval] = $rev_values;
                            
                            if($rev_values && $mkey == 'title')
                                $int_title = $rev_values;
                        }
                    }
                    
                    self::$fksdb->insert("documents", array(
                    	"titel" => $int_title,
                    	"von" => self::$user->getID(),
                    	"author" => self::$user->getID(),
                    	"von_edit" => self::$user->getID(),
                    	"timestamp_edit" => self::$base->getTime(),
                    	"timestamp" => self::$base->getTime(),
                    	"datum" => self::$base->getTime(),
                    	"klasse" => $zo['klasse'],
                    	"sprachen" => serialize($sp),
                    	"sprachenfelder" => serialize($spmore),
                    	"zsb" => $zsb,
                        "statusB" => 0,
                        "statusA" => intval($zo['status'])
                    ));
                    $id = self::$fksdb->getInsertedID();
                    
                    self::$fksdb->insert("document_versions", array(
                    	"dokument" => $id,
                    	"von" => self::$user->getID(),
                    	"timestamp" => self::$base->getTime(),
                    	"language" => $lan,
                    	"edit" => 1
                    ));
                    $vid = self::$fksdb->getInsertedID();
                    
                    $updt = self::$fksdb->query("UPDATE ".SQLPRE."documents SET dversion_edit = '".$vid."' WHERE id = '".$id."' LIMIT 1");
                    
                    $p_array = array();
                    $p_img = array();
                    $ki = array();
                    
                    foreach($ergebnis as $k => $a)
                    {
                        if(!$zo['feld'][$k])
                            continue;
                            
                        $zname = $zo['feld'][$k];
                        
                        if($a['type'] == 'img')
                        {
                            $valA = self::$base->db_to_array($a['value']);
                            $p_img[$zname] = intval(($valA['status'] == 'ok'?$valA['id']:0));
                        }
                        else
                        {
                            $zwert = $a['value'];
                            $p_array[$zname] .= ($p_array[$zname]?', ':'').$zwert;
                        }
                    }
                    
                    for($x = 0; $x < count($zo['notiz']); $x++)
                    {
                        $zname = self::$fksdb->save($zo['notiz_feld'][$x]);
                        $zwert = self::$fksdb->save($zo['notiz'][$x]);
                        $p_array[$zname] .= ($p_array[$zname]?', ':'').$zwert;
                    }
                    
                    foreach($p_array as $k => $a)
                    { 
                        $html = rawurldecode(self::$fksdb->save(Strings::tidyHTML(Strings::removeBadHTML(Strings::cleanString(nl2br($a))))));
                        $ki[$k]['html'] = $html;
                    }     
                    
                    foreach($p_img as $k => $a)
                    { 
                        if(!$a)
                            continue;
                            
                        $ki[$k]['bild'] = 1;
                        $ki[$k]['bildwt'] = 2;
                        $ki[$k]['bildid'] = intval($a);
                    }                       
                    
                    $kis = serialize($ki);
                    $update = self::$fksdb->query("UPDATE ".SQLPRE."document_versions SET klasse_inhalt = '".$kis."' WHERE id = '".$vid."' LIMIT 1");
                    
                    // create snippet
                    self::$base->create_dk_snippet($id);
                    
                    if($zo['status'] == 1)
                        $update = self::$fksdb->query("UPDATE ".SQLPRE."document_versions SET ende = '1', timestamp_edit = '".self::$base->getTime()."' WHERE id = '".$vid."' LIMIT 1");
                    if($zo['status'] == 2)
                    {
                        $update = self::$fksdb->query("UPDATE ".SQLPRE."document_versions SET ende = '0', aktiv = '1', timestamp_edit = '".self::$base->getTime()."', timestamp_freigegeben = '".self::$base->getTime()."', von_freigegeben = '".self::$user->getID()."' WHERE id = '".$vid."' LIMIT 1");
                        
                        self::$fksdb->insert("document_versions", array(
                        	"dokument" => $id,
                        	"von" => self::$user->getID(),
                        	"timestamp" => self::$base->getTime(),
                        	"language" => $lan,
                        	"edit" => 0,
                        	"klasse_inhalt" => $kis
                        ));
                        $vid2 = self::$fksdb->getInsertedID();
                        
                        $updt = self::$fksdb->query("UPDATE ".SQLPRE."documents SET dversion_edit = '".$vid2."', statusA = '2' WHERE id = '".$id."' LIMIT 1");
                        
                        if($zo['rewrite_taget'] && $zo['klasse'])
                        {
                            $related_class = str_replace('.php', '', $zo['klasse']);
                            $search_element = self::$fksdb->data("SELECT element FROM ".SQLPRE."elements WHERE klasse = '".$related_class."' ORDER BY element, sort LIMIT 1", "element");
                            
                            if($search_element)
                                self::$base->go($this->buildURL($search_element, $id));
                        }
                    }
                }
            }
            
            // Weiterleiten
            if($fo['ziel'])
            {
                $wtl = self::$fksdb->fetch("SELECT id, sprachen FROM ".SQLPRE."elements WHERE id = '".$fo['ziel']."' LIMIT 1");
                $spr = self::$base->fixedUnserialize($wtl->sprachen);
                
                self::$base->go($this->getDomain().$wtl->id.'/'.self::$base->auto_slug($spr[$this->language]).'/');
            }
            elseif($this->id == $this->home_element->id)
            {
                self::$base->go($this->getRoot().'?form_ok=true#fks_form_'.$v['fokus_form_id']);
            }
            elseif(!$this->dclass)
            {
                self::$base->go($this->getDomain().$this->element->id.'/'.self::$base->auto_slug($this->esprachen[$this->language]).'&form_ok=true/#fks_form_'.$v['fokus_form_id']);
            }
            else 
            {
                self::$base->go($this->getDomain().$this->element->id.'/'.$dk.'/'.self::$base->auto_slug($this->esprachen[$this->language]).'&form_ok=true/#fks_form_'.$v['fokus_form_id']);
            }
        }
    }
}

if($_GET['acode'] && $_GET['fid'])
{
    $acode = self::$fksdb->save($_GET['acode']);
    $fid = self::$fksdb->save($_GET['fid']);
    
    $person = self::$fksdb->fetch("SELECT id FROM ".SQLPRE."users WHERE code = '".$acode."' AND registriert > '".(time() - 604800)."' AND status != '2' LIMIT 1"); 
    $f = self::$fksdb->fetch("SELECT html FROM ".SQLPRE."blocks WHERE type = '52' AND vid = '".$fid."' ORDER BY id DESC LIMIT 1"); 
    $fo = self::$base->fixedUnserialize($f->html);
    $zo = self::$base->db_to_array($fo['zuordnung_benutzer']);
    
    if($person && $f)
    {
        $updt = self::$fksdb->query("UPDATE ".SQLPRE."users SET code = '', status = '0' WHERE id = '".$person->id."' LIMIT 1");
        
        self::$api->doLogin($person->id);
        
        if($zo['akt_ok'])
        {
            $wtl = self::$fksdb->fetch("SELECT id, sprachen FROM ".SQLPRE."elements WHERE id = '".$zo['akt_ok']."' LIMIT 1");
            $spr = self::$base->fixedUnserialize($wtl->sprachen);
            
            self::$base->go($this->getDomain().$wtl->id.'/'.self::$base->auto_slug($spr[$this->language]).'/');
        }
    }
    else
    {
        if($zo['akt_fehler'])
        {
            $wtl = self::$fksdb->fetch("SELECT id, sprachen FROM ".SQLPRE."elements WHERE id = '".$zo['akt_fehler']."' LIMIT 1");
            $spr = self::$base->fixedUnserialize($wtl->sprachen);
            
            self::$base->go($this->getDomain().$wtl->id.'/'.self::$base->auto_slug($spr[$this->language]).'/');
        }
    }
    
    self::$base->go($this->getDomain());
}
?>
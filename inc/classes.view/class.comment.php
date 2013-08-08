<?php
if($_POST['fks_comment_send'])
{
    $v = self::$base->vars('POST');
    $pc = (object)$v;
    $vid = $pc->fks_comment_id; 
    
    $kkQ = self::$fksdb->query("SELECT html FROM ".SQLPRE."blocks WHERE type = '69' AND vid = '".$vid."' ORDER BY id DESC LIMIT 1");
    while($kk = self::$fksdb->fetch($kkQ))
    {
        $c = self::$base->fixedUnserialize($kk->html);
        if(!is_array($c)) $c = array();
        $c = (object)$c;
        
        $comment_error = array(); 
        
        $comment_opt = array(
            'use_name' => ($c->name?true:false),
            'use_email' => ($c->email?true:false),
            'use_web' => ($c->web?true:false),
            'use_comment' => ($c->text?true:false),
            'hide_name' => ($c->name_h?true:false),
            'hide_email' => ($c->email_h?true:false),
            'hide_web' => ($c->web_h?true:false),
            'hide_comment' => ($c->text_h?true:false),
            'web_nofollow' => (!$c->web_df?true:false),
            'insert_userdata' => (!$c->loggedusers?true:false),
            'lock_userdata' => (!$c->loggedusers && $c->loggedusers_force?true:false)
        );

        $web = '';
        if($pc->web)
        {
            $web = $pc->web;
            if(!Strings::strExists('http', $web, false))
                $web = 'http://'.$web;
        }
        
        // Felder Validierung /////////////
        if($c->name && $c->name_p && strlen($pc->name) < 3)
            $comment_error['name'] = self::$trans->__('&quot;Name&quot; ist ein Pflichtfeld');
        elseif(strlen($pc->name) > 100)
            $comment_error['name'] = self::$trans->__('&quot;Name&quot; ist zu lang (max. 100 Zeichen)');
        if($c->email && $c->email_p && !$pc->email)
            $comment_error['email'] = self::$trans->__('&quot;Email&quot; ist ein Pflichtfeld');
        if($c->email && $pc->email && !self::$base->is_valid_email($pc->email))
            $comment_error['email'] = self::$trans->__('Die eingetragende E-Mail-Adresse ist nicht gültig');
        if(strlen($pc->email) > 250)
            $comment_error['email'] = self::$trans->__('&quot;Email&quot; ist zu lang (max. 250 Zeichen)');
        if($c->web && $c->web_p && !$pc->web)
            $comment_error['web'] = self::$trans->__('&quot;Webseite&quot; ist ein Pflichtfeld');
        if($c->web && $pc->web && !self::$base->is_valid_url($web))
            $comment_error['web'] = self::$trans->__('Die eingetragende Webseite ist nicht gültig');
        if($c->text && $c->text_p && strlen($pc->text) < 10)
            $comment_error['text'] = self::$trans->__('&quot;Kommentar&quot; ist ein Pflichtfeld (min. 10 Zeichen)');
        elseif($c->text && $pc->text && $pc->text != strip_tags($pc->text))
            $comment_error['text'] = self::$trans->__('HTML-Formatierungen sind nicht erlaubt');
            
        // Vertrauenswürdigkeit berechnen
        if($c->frei == 1 && !self::$user->isAdmin())
        {
            $vertrauen = true;
            if(Strings::strExists('http', $pc->text, false))
                $vertrauen = false;
            if(strlen($pc->text) < 100)
                $vertrauen = false;
            if(substr_count($web, '/') > 3)
                $vertrauen = false;
            if(Strings::strExists('.ru', $web, false))
                $vertrauen = false;
                
            if($vertrauen)
            {
                include_once(ROOT.'inc/badwords.php');
            
                foreach($bad_word_list as $b)
                {
                    if(Strings::strExists($pc->text, $b, false) || Strings::strExists($web, $b, false) || Strings::strExists($pc->email, $b, false) || Strings::strExists($pc->name, $b, false))
                    {
                        $vertrauen = false;
                        break;
                    }
                }
            }
        }
        elseif(self::$user->isAdmin())
        {
            $vertrauen = true;
        }

        
        ////////// Globale Validierung ////////////
        // Zeitfenster überprüfen
        if(!self::$api->checkAjaxHash())
            $comment_error[0] = 'Formular wurde nicht korrekt abgeschickt (Fehlercode: 101). Sie sollten die Seite aktualisieren.';

        if($v['fks_comment_send'] > self::$base->getTime() - 3 && !count($comment_error))
            $comment_error[0] = self::$trans->__('Es trat ein unbekannter Fehler auf: Bitte versuchen Sie es erneut (Fehlercode: 102)');
            
        // Honeypot passives Captcha
        if(!empty($v['fks_url']) && !count($comment_error))
            $comment_error[0] = self::$trans->__('Es trat ein unbekannter Fehler auf: Bitte versuchen Sie es erneut (Fehlercode: 103)');
            
        // Referer mit Host vergleichen
        $ref = parse_url($_SERVER['HTTP_REFERER']);
        $self = $_SERVER['HTTP_HOST'];
        if($_SERVER['HTTP_REFERER'] && !Strings::strExists($self, $ref['host']) && !Strings::strExists($ref['host'], $self))
            $comment_error[1] = self::$trans->__('Anfragen auf das Formular dürfen nur direkt über die Webseite gestellt werden');
            
        // Doppelte Einträge vermeiden
        $check_last_ip = self::$fksdb->count(self::$fksdb->query("SELECT id FROM ".SQLPRE."comments WHERE vid = '".$vid."' AND ip = '".self::$api->getVisitorIP()."' AND timestamp > '".(time() - 30)."' LIMIT 1")); 
        if($check_last_ip) 
            $comment_error[2] = self::$trans->__('Sie haben innerhalb der letzten 30 Sekunden bereits einen Kommentar vorgenommen');
            
        $check_last_ip = self::$fksdb->count(self::$fksdb->query("SELECT id FROM ".SQLPRE."comments WHERE ip = '".self::$api->getVisitorIP()."' AND text LIKE '%".$pc->text."%' AND timestamp > '".(time() - 600)."' LIMIT 1")); 
        if($check_last_ip) 
            $comment_error[2] = self::$trans->__('Sie haben innerhalb der letzten fünf Minuten bereits einen Kommentar mit dem selben Text vorgenommen');
        /////
        
        if(count($comment_error))
        {
            $comment_error['fks_form_id'] = $vid;
        }
        
        $this->comment_error = $comment_error; 
        
        if(!count($comment_error))
        {
            $name = '';
            $email = '';
            $text = '';

            if($c->name)
                $name = $pc->name;
            if($c->email)
                $email = $pc->email;
            if($c->text)
                $text = $pc->text;
                
            $frei = ($c->frei == 0?0:($c->frei == 1 && !$vertrauen?0:1));
            
            // In Datenbank eintragen
            self::$fksdb->insert("comments", array(
            	"vid" => $vid,
            	"timestamp" => time(),
            	"ip" => self::$api->getVisitorIP(),
            	"benutzer" => self::$user->getID(),
            	"type" => $c->type,
            	"dokument" => $pc->fks_comment_document,
            	"element" => $this->element->id,
            	"dk" => $this->getDclassDocumentID(),
            	"frei" => $frei,
            	"name" => $name,
            	"email" => $email,
            	"web" => $web,
            	"text" => $text
            ));
            
            // Email bei Bedarf versenden
            if($c->pn && $c->pn_email)
            {
                $mbetreff = 'Neuer Kommentar auf '.str_replace('http://', '', $this->getDomain(''));
                $mmsg = 'Es ist ein neuer Kommentar auf '.str_replace('http://', '', $this->getDomain('')).' eingegangen.
                
'.($c->name?'Name: '.$name.'
':'').($c->email?'Email: '.$email.'
':'').($c->web?'Webseite: '.$web.'
':'').($c->text?'
Nachricht: 
'.$text.'
':'').'
Status: '.($frei?'Freigeschaltet':'Gesperrt').'

Kommentare verwalten: '.$this->getDomain().'fokus/';

                self::$base->email($c->pn_email, $mbetreff, $mmsg, self::$base->getOpt('email'));    
            }
            
            // Hook: comment_added
            $hatr = array(
                'vid' => $vid,
                'user' => self::$user->getID(),
                'ip' => self::$api->getVisitorIP(),
                'element' => $this->element->id,
                'document' => $pc->fks_comment_document,
                'dclass_document' => $this->getDclassDocumentID(),
                'open' => ($frei?true:false),
                'name' => $name, 
                'email' => $email, 
                'comment' => $text, 
                'web' => $web,
                'timestamp' => time(),
                'opt' => $comment_opt
            );   
            self::$api->executeHook('comment_added', $hatr);
            unset($hatr);
            
            // Weiterleiten
            if($this->id == $this->home_element->id && !$this->dclass)
            {
                self::$base->go($this->getRoot().'?comment_ok=true');
            }
            elseif(!$this->dclass)
            {
                self::$base->go($this->getDomain().$this->element->id.'/'.self::$base->auto_slug($this->element_languages[$this->language]).'&comment_ok=true/');
            }
            else 
            {
                self::$base->go($this->getDomain().$this->element->id.'/'.$this->getDclassDocumentID().'/'.self::$base->auto_slug($this->element_languages[$this->language]).'&comment_ok=true/');
            }
        }
    }
}
?>
<?php
class User
{
    private static $base, $fksdb, $suite, $trans;
    
    private $cookies = array(), $cookie_alias = array();
    private $salts = array();
    
    private $logged = false, $admin = false;
    private $user = null;
    private $user_role = 0, $available_roles = array();
    private $competence = array(), $competence_avaible = array(), $rights = array();
    private $widgets = array();
    
    public function User($p)
    {
        self::$fksdb = $p['fksdb'];
        self::$base = $p['base'];
        self::$suite = $p['suite'];
        self::$trans = $p['trans'];
        
        $this->setCookieAliases($p['cookie_alias']);
        $this->cookies = $this->getCookies(); 
        
        $this->setSalts($p['salts']);
        
        if($this->cookies['stated'])
        {
            $logged = $this->doLogin($this->cookies['login'], $this->cookies['password'], $this->cookies['role']);
            
            if($logged)
                $this->getForesight();
        }
    }
    
    
    private function setSalts($salts)
    {
        if(!is_array($salts))
            $this->salts = array('login' => '', 'password' => '', 'password_b' => '');   
        else
            $this->salts = array('login' => $salts['login'], 'password' => $salts['password'], 'password_b' => $salts['password_b']);   
    }
    
    private function getSalt($salt)
    {
        return $this->salts[$salt];    
    }
    
    private function getCookies()
    { 
        if($_COOKIE[$this->cookie_alias['expiration']] < time())
        { 
        	setcookie($_COOKIE[$this->cookie_alias['login']], 0, time() - 3600, '/');
            setcookie($_COOKIE[$this->cookie_alias['password']], 0, time() - 3600, '/');
            setcookie($_COOKIE[$this->cookie_alias['role']], 0, time() - 3600, '/');
            
            return false;
        }     
        else
        {
            return array(
                'expiration' => self::$fksdb->save($_COOKIE[$this->cookie_alias['expiration']], 1),
                'role' => self::$fksdb->save($_COOKIE[$this->cookie_alias['role']], 1),
                'password' => self::$fksdb->save($_COOKIE[$this->cookie_alias['password']]),
                'login' => self::$fksdb->save($_COOKIE[$this->cookie_alias['login']]),
                'stated' => ($_COOKIE[$this->cookie_alias['login']] && $_COOKIE[$this->cookie_alias['password']]?true:false)
            );
        }
    }
    
    private function setCookieAliases($aliases)
    {
        if(is_array($aliases))
        {
            $this->cookie_alias = array(
                'expiration' => $aliases['ablauf'],
                'role' => $aliases['rolle'],
                'password' => $aliases['pw'],
                'login' => $aliases['login']
            );
        }
        else
        {
            $this->cookie_alias = array(
                'expiration' => md5('expiration_'.DOMAIN),
                'role' => md5('role_'.DOMAIN),
                'password' => md5('password_'.DOMAIN),
                'login' => md5('login_'.DOMAIN)
            );
        }
    }
    
    public function getCookiesAliases($cookie = '')
    { 
        if(!$cookie)
            return $this->cookie_alias;
        else
            return $this->cookie_alias[$cookie];
    }
    
    private function renewCookies($logout = false, $login = '', $password = '', $role_id = 0)
    {
        $cookie_lifetime = (!$logout?((60 * 60 * 24 * 30) + time()):(time() - 864000));
        
        setcookie($this->cookie_alias['login'], $login, $cookie_lifetime, '/');
        setcookie($this->cookie_alias['password'], $password, $cookie_lifetime, '/');
        setcookie($this->cookie_alias['role'], $role_id, $cookie_lifetime, '/');
    	setcookie($this->cookie_alias['expiration'], (time() + self::$base->getOpt('logout')), $cookie_lifetime, '/');
        
        $this->cookies = array(
            'expiration' => self::$fksdb->save((time() + self::$base->getOpt('logout')), 1),
            'role' => self::$fksdb->save($role_id, 1),
            'password' => self::$fksdb->save($password),
            'login' => self::$fksdb->save($login),
            'stated' => ($login && $password?true:false)
        );
        
        if($logout)
        {
            $this->cookies = array();
            $this->admin = false;
            $this->logged = false;
        }
        
        return true;
    }
    
    
    public function doLogin($login, $password, $role_id = 0)
    {
        $pquery = self::$fksdb->query("SELECT * FROM ".SQLPRE."users WHERE login = '".$login."' AND pw = '".$password."' AND status = '0' AND pw != '".md5('')."' AND login != '' AND pw != '' AND papierkorb = '0' LIMIT 1");
        if(!self::$fksdb->count($pquery))
            return false;
            
        $user = self::$fksdb->fetch($pquery); 
        
        if($user->von > time() || ($user->bis && $user->bis < time() && $user->bis != mktime(0, 0, 0, date('m'), date('d'), date('Y'))))
        {
            $this->doLogout();
            return false;
        }
        
        $atm_online = self::$fksdb->count("SELECT id FROM ".SQLPRE."users WHERE id != '".$user->id."' AND status = '0' AND papierkorb = '0' AND online >= '".(time() - 15)."' AND (type = '0' OR type = '2')");
        if($atm_online >= self::$suite->getLimitOfUsers() && self::$suite->getLimitOfUsers() != -1)
        {
            $this->doLogout();
            return false;
        }
        
        
        $this->user = $user;
        $this->logged = true;
        
            
        $all_roles = array();
        $all_rolesQ = self::$fksdb->query("SELECT rolle FROM ".SQLPRE."user_roles WHERE benutzer = '".$user->id."'");
        while($ar = self::$fksdb->fetch($all_rolesQ))
            $all_roles[] = $ar->rolle;
            
        if($role_id && !in_array($role_id, $all_roles))
            $role_id = $all_roles[0];
            
        $this->user_role = $role_id;
        $this->available_roles = $all_roles;
        
        if(!$user->type || $user->type == 2)
            $this->admin = true;
        
        $competence = array();
        if($role_id && $role_id != 1)
        {
            $rights = self::$base->db_to_array(self::$fksdb->data("SELECT rechte FROM ".SQLPRE."roles WHERE id = '".$role_id."' AND papierkorb = '0' LIMIT 1", "rechte")); 
           
            if(is_array($rights))
            {
                $this->rights = $rights;
                
                if(is_array($rights['dok']['zsba']))
                {
                    foreach($rights['dok']['zsba'] as $zid)
                        $competence[] = $zid;
                }
            }
        }   
        $this->competence = $competence;
        $this->competence_avaible = self::$fksdb->rows("SELECT id FROM ".SQLPRE."responsibilities WHERE papierkorb = '0'", "id");
        
        $this->widgets = self::$base->db_to_array($user->widgets);
        
             
        $this->renewCookies(false, $login, $password, $role_id);
    	$this->setLoginTime();
        
        return true;
    }
    
    public function doLogout()
    {
        if(!$this->isLogged())
            return false;
        
        $this->renewCookies(true);
        return true;
    }
    
    
    public function getLoginHash($login, $salt = '')
    {
        if(!$salt)
            $salt = $this->getSalt('login');
        
        return md5(strtolower($login).$salt);
    }
    
    public function getPasswordHash($password, $salt = '', $salt_b = '')
    {
        if(!$salt)
            $salt = $this->getSalt('password');
        if(!$salt_b)
            $salt_b = $this->getSalt('password_b');
            
        if(!$salt && !$salt_b)
            $password = strtolower($password);
        
        $output = md5($password.$salt);
        
        if($salt_b)
        {
            if(function_exists('hash') && in_array('sha512', hash_algos()))
                $output = hash('sha512', $salt_b.$output);
            else
                $output = sha1($salt_b.$output);
        }
            
        return $output;
    }
    
    
    public function setLoginTime()
    {
        if(!isset($_COOKIE['logintime']))
    	    setcookie('logintime', time(), (time() + 3600), '/');
        else
            setcookie('logintime', self::$fksdb->save($_COOKIE['logintime'], 1), (time() + 3600), '/');
            
        $update = self::$fksdb->query("UPDATE ".SQLPRE."users SET online = '".time()."' WHERE id = '".$this->user->id."' LIMIT 1");
    	
        return true;
    }
    
    public function getLoginTime()
    {
        if(!$this->user)
            return 0;
            
        return intval(self::$fksdb->save($_COOKIE['logintime'], 1));
    }
    
    
    public function data($field = '')
    {
        if(!$this->user)
            return ($field?false:new StdClass());
            
        $userdata = $this->user;
        return ($field?$userdata->$field:$userdata);
    }
    
    public function setData($data = array())
    {
        if(!$this->user || !count($data))
            return false;
            
        self::$fksdb->update("users", $data, array(
            "id" => $this->user->id
        ), 1);
        
        return true;
    }
    
    public function getWidgets($widget = '')
    {
        if(!$widget)
            return $this->widgets;
        return $this->widgets[$widget];
    }
    
    public function getWidget($widget, $data = '')
    {
        $widget = $this->getWidgets($widget);
        
        if($data)
            return $widget[$data];
        return $widget;
    }
    
    public function isLogged()
    {
        if($this->user && $this->logged)
            return true;
        return false;
    }
    
    public function isAdmin()
    {  
        if($this->isLogged() && $this->admin)
            return true;
        return false;
    }
    
    public function isSuperAdmin()
    {
        if($this->isAdmin() && $this->getRole() == 1)
            return true;
        return false;
    }
    
    
    public function getID()
    {
        if(!$this->user)
            return 0;
            
        return $this->user->id;    
    }
    
    public function getRole()
    {
        if(!$this->user)
            return 0;
            
        return intval($this->user_role);
    }
    
    public function getAvailableRoles()
    {
        if(!$this->user && !is_array($this->available_roles))
            return array();
            
        return $this->available_roles;
    }
    
    public function getIndiv()
    {
        if(!$this->user)
            return new StdClass(); 
            
        $indiv = $this->user->indiv;
        if(!$indiv)
            return new StdClass();
            
        $indivA = self::$base->fixedUnserialize($indiv);
         
        return (object)$indivA;    
    }
    
    public function getRights($translate = false)
    {   
        if(!$this->user)
            return array();
            
        if(!is_array($this->rights))
            return array();
            
        if($translate)
            return $this->getTranslatedRights();
            
        return $this->rights;
    }
    
    private function getTranslatedRights()
    {
        $sa = $this->isSuperAdmin();
        
        $rights = array(
            'structures' => array(
                'edit_elements' => (!$sa?$this->rights['str']['ele']:1),
                'restrict_edit_elements' => (!$sa?$this->rights['str']['opt']:0),
                'lifetime' => (!$sa?$this->rights['str']['lebensdauer']:1),
                'roles' => (!$sa?$this->rights['str']['rollen']:1),
                'seo' => (!$sa?$this->rights['str']['seo']:1),
                'automation' => (!$sa?$this->rights['str']['dk']:1),
                'restrict_templates' => (!$sa?$this->rights['str']['template']:0),
                'standard_template' => (!$sa?$this->rights['str']['tda']:null),
                'availaible_templates' => (!$sa?$this->rights['str']['td']:null),
                'structures' => (!$sa?$this->rights['str']['struk']:1),
                'menus' => (!$sa?$this->rights['str']['menue']:1),
                'slots' => (!$sa?$this->rights['str']['slots']:1),
                'categories' => (!$sa?$this->rights['str']['kat']:1)
            ),
            'documents' => array(
                'edit_document' => (!$sa?$this->rights['dok']['edit']:1),
                'create_document' => (!$sa?$this->rights['dok']['new']:1),
                'delete_document' => (!$sa?$this->rights['dok']['del']:1),
                'publish_document' => (!$sa?$this->rights['dok']['publ']:1),
                'publish_management' => (!$sa?$this->rights['dok']['publ_all']:1),
                'set_category' => (!$sa?$this->rights['dok']['cats']:1),
                'take_document' => (!$sa?$this->rights['dok']['acopy']:1),
                'restrict_responsibility' => (!$sa?$this->rights['dok']['zsb']:0),
                'responsibility_area' => (!$sa?$this->rights['dok']['zsba']:0),
                'restrict_classes' => (!$sa?$this->rights['dok']['dk']:0),
                'standard_class' => (!$sa?$this->rights['dok']['dklassea']:null),
                'availaible_classes' => (!$sa?$this->rights['dok']['dklasse']:null),
                'restrict_formatting' => (!$sa?$this->rights['dok']['css']:0),
                'free_formatting' => (!$sa?$this->rights['dok']['cssf']:1),
                'css_formatting' => (!$sa?$this->rights['dok']['cssk']:1),
                'responsibilities' => (!$sa?$this->rights['dok']['ezsb']:1)
            ),
            'files' => array(
                'images' => (!$sa?$this->rights['dat']['bilder']:1),
                'files' => (!$sa?$this->rights['dat']['dateien']:1),
                'add_file' => (!$sa?$this->rights['dat']['new']:1),
                'edit_file' => (!$sa?$this->rights['dat']['edit']:1),
                'delete_file' => (!$sa?$this->rights['dat']['del']:1),
                'update_file' => (!$sa?$this->rights['dat']['ver']:1),
                'directories' => (!$sa?$this->rights['dat']['dir']:1)
            ),
            'users' => array(
                'edit_user' => (!$sa?$this->rights['per']['edit']:1),
                'create_user' => (!$sa?$this->rights['per']['new']:1),
                'delete_user' => (!$sa?$this->rights['per']['del']:1),
                'assign_role' => (!$sa?$this->rights['per']['prolle']:1),
                'restrict_type' => (!$sa?$this->rights['per']['type']:0),
                'customer' => (!$sa?$this->rights['per']['kunden']:1),
                'employee' => (!$sa?$this->rights['per']['mitarbeiter']:1),
                'companies' => (!$sa?$this->rights['per']['firma']:1),
                'roles' => (!$sa?$this->rights['per']['rollen']:1)
            ),
            'communication' => array(
                'edit_newsletter' => (!$sa?$this->rights['kom']['nledit']:1),
                'send_newsletter' => (!$sa?$this->rights['kom']['nlsend']:1),
                'channels' => (!$sa?$this->rights['kom']['kkanal']:1),
                'messages' => (!$sa?$this->rights['kom']['pn']:1),
                'livetalk' => (!$sa?$this->rights['kom']['livetalk']:1),
                'wall' => (!$sa?$this->rights['kom']['pinnwand']:1),
                'edit_wall' => (!$sa?$this->rights['kom']['pinnwandedit']:1)
            ),
            'items' => array(
                'search' => (!$sa?$this->rights['suc']['suche']:1),
                'trash' => (!$sa?$this->rights['suc']['papierkorb']:1),
                'recent' => (!$sa?$this->rights['suc']['zuve']:1)
            ),
            'fks' => array(
                'ghost' => (!$sa?$this->rights['fks']['ghost']:1),
                'foresight' => (!$sa?$this->rights['fks']['foresight']:1),
                'customize' => (!$sa?$this->rights['fks']['indiv']:1),
                'cleaner' => (!$sa?$this->rights['fks']['pure']:1),
                'session' => (!$sa?$this->rights['fks']['sitzung']:1),
                'notes' => (!$sa?$this->rights['fks']['notiz']:1),
                'extensions' => (!$sa?$this->rights['fks']['extensions']:1),
                'options' => (!$sa?$this->rights['fks']['opt']:1)
            ),
            'api' => $this->rights['api']
        );
        
        return $rights;
    }
    
    public function getCompetence()
    {   
        if(!$this->user)
            return array();
            
        if(!is_array($this->competence))
            return array();
            
        return $this->competence;
    }
    
    public function getAvaibleCompetence()
    {   
        if(!is_array($this->competence_avaible))
            return array();
            
        return $this->competence_avaible;
    }
    
    public function isCompetent($cid)
    {   
        if(!$this->user)
            return false;
            
        if($this->isSuperAdmin())
            return true;
            
        if(in_array($cid, $this->competence))
            return true;
            
        if(!in_array($cid, $this->competence_avaible))
            return true;
            
        return false;
    }
    
    public function r($cat, $area = '')
    {
        if(!$this->isLogged())
            return false;
        
        if($this->isSuperAdmin())
            return true; 
            
        $rights = $this->getRights();
        
        if(is_array($rights[$cat]))
        {
            if($area)
            {
                if($rights[$cat][$area])
                    return true;
            }
            else
            {
                return true;
            }
        }
        
        return false;
    }
    
    
    public function setGhost($on = true)
    {
        if($on)
        {
            setcookie('ghost', 'true', (time() + 60 * 60 * 24 * 30), '/');
            return true;
        }
        else
        {
            setcookie('ghost', '', (time() - 60 * 60 * 24 * 30), '/');
            return false;
        }
    }
    
    public function isGhost()
    {
        if($this->isAdmin() && self::$fksdb->save($_COOKIE['ghost']) && self::$suite->rm(4) && $this->r('fks', 'ghost'))
            return true;
            
        return false;    
    }
    
    public function setForesight($on = true, $cookie_value = '')
    {
        if($on)
        {
            setcookie('foresight', $cookie_value, (time() + 60 * 60 * 24 * 30), '/');
            return true;
        }
        else
        {
            setcookie('foresight', '', (time() - 60 * 60 * 24 * 30), '/');
            return false;
        }
    }
    
    public function isForesight()
    {
        if($this->isAdmin() && self::$fksdb->save($_COOKIE['foresight']) && self::$suite->rm(2) && $this->r('fks', 'foresight'))
            return true;
            
        return false;    
    }
    
    public function getForesight($data = '')
    {
        if(!$this->isForesight())
            return ($data?0:array());
    
        $fvalues = explode('!', self::$fksdb->save($_COOKIE['foresight']));
        
        if(defined('IS_INDEX') && $fvalues[0] != time())
            self::$base->setTime($fvalues[0]);
            
        $result = array(
            'time' => $fvalues[0],
            'type' => $fvalues[1],
            'structure' => $fvalues[2]
        );
            
        if($data)
            return intval($result[$data]);
        else
            return $result;
    }
    
    
    
    public function lastUse($type, $aid = 0)
    {        
        $del = self::$fksdb->query("DELETE FROM ".SQLPRE."recent_items WHERE benutzer = '".$this->getID()."' AND type = '".$type."' AND aid = '".$aid."' AND papierkorb = '0'");
        
        self::$fksdb->insert("recent_items", array(
        	"benutzer" => $this->getID(),
        	"timestamp" => time(),
        	"aid" => $aid,
        	"type" => $type
        ));
    }
    
    public function trash($type, $aid = 0)
    {        
        $del = self::$fksdb->query("DELETE FROM ".SQLPRE."recent_items WHERE type = '".$type."' AND aid = '".$aid."'");
        
        self::$fksdb->insert("recent_items", array(
        	"benutzer" => $this->getID(),
        	"timestamp" => time(),
        	"aid" => $aid,
        	"type" => $type,
        	"papierkorb" => 1
        ));  
    }
    
    public function clipboard($type, $aid = 0, $type2 = '', $aid2 = '', $aid3 = '')
    {         
        $del = self::$fksdb->query("DELETE FROM ".SQLPRE."clipboard WHERE benutzer = '".$this->getID()."' AND type = '".$type."' AND aid = '".$aid."'");
        
        self::$fksdb->insert("clipboard", array(
            "benutzer" => $this->getID(),
            "timestamp" => time(),
            "type" => $type,
            "type2" => $type2,
            "aid" => $aid,
            "aid2" => $aid2,
            "aid3" => $aid3
        ));
    }
    
    public function noRights($area = '')
    {
        $myr = self::$fksdb->data("SELECT titel FROM ".SQLPRE."roles WHERE id = '".$this->getRole()."' LIMIT 1", "titel");
        
        return '<div class="ifehler rfehler"><strong>Kein Zugriff möglich</strong>Ihre zugewiesene Rolle <em>'.$myr.'</em> weißt nicht die nötigen Rechte auf, um auf '.(!$area?'diesen Bereich':'den Bereich '.$area).' zugreifen zu können.</div>'; 
    }
}
?>
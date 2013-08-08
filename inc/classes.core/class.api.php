<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class API 
{
    public static $static, $fksdb, $base, $user, $suite, $trans;
    
    private $hooks = array(), $filter = array(), $shortcodes = array();
    private $blocks = array(), $notifications = array(), $widgets = array(), $apps = array(), $rights = array();
    private $custom_fields = array(), $user_fields = array(), $file_fields = array();
    private $js_files = array(), $css_files = array();
    private $data = array();
    private $custom_pages = array();
    
    public function __construct($static)
    {
        $static['api'] = $this;
        self::$static = $static;
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$user = $static['user'];
        self::$trans = $static['trans']; 
    }
    
    public function setStatic($static_key, $static_var)
    {
        self::$static[$static_key] = $static_var;
    }

    public function getStatic()
    {
        return self::$static;
    }
    // common functions
    
    public function getDomain($ending = '/')
    {
        $domain = DOMAIN;
        $tdomain = $domain;
        
        if(self::$base->getOpt('www') == 1 && !Strings::strExists('www.', $_SERVER['SERVER_NAME'], false) && !Strings::strExists('//www.', $domain, false)) // mit www
        { 
            if(Strings::strExists('http://', $domain))
            {
                $pre = 'http://';
                $rest = substr($domain, 7);
            }
            elseif(Strings::strExists('https://', $domain))
            {
                $pre = 'https://';
                $rest = substr($domain, 8);
            }
            
            if($pre && $rest)
                $tdomain = $pre.'www.'.$rest; 
        }
        elseif(self::$base->getOpt('www') == 2 && Strings::strExists('www.', $_SERVER['SERVER_NAME'], false)) // ohne www
        {
            $tdomain = str_replace('//www.', '//', $domain); 
        }
        
        return $this->execute_filter('get_domain', $tdomain.$ending);
    }
    
    public function writeDomain($ending = '/')
    {
        echo $this->getDomain($ending);    
    }

    public function getLanguages()
    {
        $langs = self::$trans->getLanguages();
        $new_langs = array();

        foreach($langs as $code)
        {
            $new_langs[strtolower($code)] = array(
                'code' => $code,
                'url' => $this->getDomain().($code != self::$trans->getStandardLanguage()?$code.'/':''),
                'flag' => array(
                    16 => self::$trans->getFlag($code, 2, true),
                    32 => self::$trans->getFlag($code, 1, true)
                )
            );
        }

        return $new_langs;
    }

    public function getActiveLanguages()
    {
        $langs = self::$base->getActiveLanguages();
        $old_langs = $this->getLanguages();
        $new_langs = array();

        foreach($old_langs as $k => $v)
        {
            if(!in_array($k, $langs))
                continue;
            $new_langs[$k] = $v;
        }

        return $new_langs;
    }

    public function getActiveLanguagesCount()
    {
        return self::$base->getActiveLanguagesCount();
    }
    
    public function getElementUrl($element_id, $language = '')
    {
        return $this->execute_filter('get_element_url', $this->getElementUrlIntern($element_id, $language));
    }
    
    private function getElementUrlIntern($element_id, $language = '')
    {
        $real_language = ($language?$language:self::$trans->getStandardLanguage());
        
        if($language == self::$trans->getStandardLanguage())
            $language = '';
            
        $el = self::$fksdb->fetch("SELECT sprachen, id FROM ".SQLPRE."elements WHERE id = '".intval($element_id)."' AND struktur = '".self::$base->getStructureID()."' AND papierkorb = '0' LIMIT 1");
        $spr = self::$base->fixedUnserialize($el->sprachen);
        
        if(!$el)
            return $this->getDomain().($language?$language.'/':'');
            
        return $this->getDomain().($language?$language.'/':'').$el->id.'/'.self::$base->auto_slug($spr[$real_language]).'/';
    }
    
    public function getDclassUrl($element_id, $document_id, $language = '')
    {
        return $this->execute_filter('get_dclass_url', $this->getDclassUrlIntern($element_id, $document_id, $language));
    }
    
    private function getDclassUrlIntern($element_id, $document_id, $language = '')
    {
        $real_language = ($language?$language:self::$trans->getStandardLanguage());
        
        if($language == self::$trans->getStandardLanguage())
            $language = '';
            
        $dtemp = self::$fksdb->fetch("SELECT sprachenfelder, id FROM ".SQLPRE."documents WHERE id = '".intval($document_id)."' AND klasse != '' AND papierkorb = '0' LIMIT 1");
        $spr = self::$base->fixedUnserialize($dtemp->sprachenfelder);
        
        if(!$dtemp)
            return $this->getDomain().($language?$language.'/':'');
            
        return $this->getDomain().($language?$language.'/':'').$element_id.'/'.$dtemp->id.'/'.self::$base->auto_slug($spr[$real_language]).'/';
    }
    
    public function includeFile($file)
    {
        extract(self::$static);
        include($file);           
    }
    
    public function includeFileOnce($file)
    {
        extract(self::$static);
        include_once($file);           
    }
    
    public function getExtensions($only_activated = true, $load_config = true, $load_index = false)
    {
        ob_start();
        
        $extensions = array();
        $db_ext = self::$base->getOpt('extensions');
        
        $handle = opendir(EXTENSIONS_DIR);
        while($dir = readdir($handle)) 
        {   
            $id = trim($dir, '/');
            
            if($only_activated && !$db_ext[$id]['activated'] && !$db_ext[$id]['changed'])
                continue;
                
            $extension_dir = EXTENSIONS_DIR.$dir;
            if(!is_dir($extension_dir) || !file_exists($extension_dir.'/index.php') || !file_exists($extension_dir.'/config.php'))
                continue;
              
            $ext = array(
                'id' => $id,
                'dir' => $dir,
                'path' => $extension_dir.'/',
                'index_file' => $extension_dir.'/index.php',
                'config_file' => $extension_dir.'/config.php',
                'activated' => ($db_ext[$id]['activated']?true:false)
            );
            
            if($load_index)
            {
                extract(self::$static);
                include_once($extension_dir.'/index.php');
            }
             
            if($load_config)
            {  
                $extension = array();
                include($extension_dir.'/config.php');
                
                if($extension['name'])
                    $ext['config'] = $extension;
            }
            
            if($load_index && $load_config)
            {
                // activate - deactive
                $changed = $db_ext[$id]['changed'];
                if($changed)
                { 
                    $version = $extension['version'].'';
                    $callback = $extension['actions'][$changed];
                    
                    if($callback && is_callable($callback))
                    {   
                        if(is_string($callback))
                            call_user_func($callback, self::$static, $version);
                        elseif(is_array($callback))
                            call_user_func_array($callback, array(self::$static, $version));
                    }     
                        
                    $db_ext[$id]['changed'] = ''; 
                    self::$base->setOpt('extensions', $db_ext, true);    
                }
                
                // update
                $old_version = $db_ext[$id]['version'].'';
                $new_version = $extension['version'].'';
                $callback = $extension['actions']['update']; 
                
                if(version_compare($old_version, $new_version) == -1 && is_callable($callback))
                {
                    if(is_string($callback))
                        call_user_func($callback, self::$static, $old_version, $new_version);
                    elseif(is_array($callback))
                        call_user_func_array($callback, array(self::$static, $old_version, $new_version)); 
                        
                    $db_ext[$id]['version'] = $new_version; 
                    self::$base->setOpt('extensions', $db_ext, true);    
                }
            }
            
            if($only_activated && !$db_ext[$id]['activated'])
                continue;
                
            $extensions[$id] = $ext;
        } 
        closedir($handle); 
        ob_end_clean();
        
        return $extensions;   
    }
    
    public function getExtensionsDir($ending = '/', $withDomain = true)
    {
        $dir = ($withDomain?$this->getDomain():ROOT).'content/extensions'.$ending;
        return $this->execute_filter('get_extensions_dir', $dir);
    }
    
    public function writeExtensionsDir($ending = '/', $withDomain = true)
    {
        echo $this->getExtensionsDir($ending, $withDomain);
    }
    
    public function getExtensionDir($extension, $ending = '/', $withDomain = true)
    {
        $dir = ($withDomain?$this->getDomain():ROOT).'content/extensions/'.$extension.$ending;
        return $this->execute_filter('get_extension_dir', $dir);
    }
    
    public function writeExtensionDir($extension, $ending = '/', $withDomain = true)
    {
        echo $this->getExtensionDir($extension, $ending, $withDomain);
    }
    
    public function getTemplateDir($ending = '/', $withDomain = true)
    {
        $dir = ($withDomain?$this->getDomain():ROOT).'content/templates/'.self::$base->getActiveTemplate().$ending;
        return $this->execute_filter('get_template_dir', $dir);
    }
    
    public function writeTemplateDir($ending = '/', $withDomain = true)
    {
        echo $this->getTemplateDir($ending, $withDomain);
    }
    
    public function getTemplateConfig()
    {
        $load = $this->getTemplateDir('/', false).'config.php';
        return $this->execute_filter('get_template_config', $load);
    }
    
    public function writeTemplateConfig()
    {
        echo $this->getTemplateConfig();
    }
    
    public function includeTemplateFile($file = '')
    {
        if(!$file)
            return false;
        
        $ifile = $this->getTemplateDir('/', false).$file;
        if(file_exists($ifile))
            return $ifile;
        return false;
    }
    
    public function isGhost()
    {
        return (self::$user->isGhost()?true:false);
    }

    public function isForesight()
    {
        return (self::$user->isForesight()?true:false);
    }
    
    public function isMobile()
    {
        $useragent = $_SERVER['HTTP_USER_AGENT'];
        if(preg_match('/android|avantgo|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|symbian|treo|up\.(browser|link)|vodafone|wap|windows (ce|phone)|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|e\-|e\/|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(di|rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|xda(\-|2|g)|yas\-|your|zeto|zte\-/i', substr($useragent,0,4)))
            return $this->execute_filter('is_mobile', true);
        return $this->execute_filter('is_mobile', false);
    }


    public function getAjaxUrl($action = '')
    {
        return DOMAIN.'/ajax/'.($action?$action.'/':'');
    }
    
    public function getAjaxHash()
    {
        return $this->execute_filter('get_ajax_hash', sha1(date('Y').md5(self::$user->getLoginHash('hash').DOMAIN).$this->getVisitorCuttedIP()));
    }

    public function checkAjaxHash($hash = '')
    {
        if(!$hash)
            $hash = $_REQUEST[$this->getHashID()];

        return ($hash == $this->getAjaxHash()?true:false);
    }

    public function getHashID()
    {
        return $this->execute_filter('get_hash_id', md5(date('Y').DOMAIN.sha1(DOMAIN.$this->getVisitorCuttedIP())));
    }

    public function getHashInput()
    {
        return '<input type="hidden" name="'.$this->getHashID().'" value="'.$this->getAjaxHash().'" />';
    }
    
    public function getGlobalCustomField($name, $langugage = '')
    {
        $cf = self::$base->db_to_array(self::$base->getOpt('cf'));
        $t = (!$langugage?$cf[self::$trans->getStandardLanguage()][$name]:$cf[$langugage][$name]);  
        
        if(self::$base->getActiveTemplateConfig('global_custom_fields', $name, 'type') == 'checkbox')
            return $this->execute_filter('get_global_custom_field', ($t == 'fks_true'?true:false));
            
        return $this->execute_filter('get_global_custom_field', htmlspecialchars_decode($t));
    }
    
    public function writeGlobalCustomField($name, $langugage = '')
    {
        echo $this->getGlobalCustomField($name, $langugage);
    }
    
    public function getVisitorIP()
    {
        if (!isset($_SERVER['HTTP_X_FORWARDED_FOR'])) 
            $ip = $_SERVER['REMOTE_ADDR'];
        else 
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
            
        return $this->execute_filter('get_visitor_ip', $ip);
    }

    public function getVisitorCuttedIP($limit = 2)
    {
        return implode('.', array_slice(explode('.', $this->getVisitorIP()), 0, $limit));
    }
    
    public function getVisitor()
    {
        return $this->execute_filter('get_visitor', sha1($this->getVisitorCuttedIP().$_SERVER['HTTP_USER_AGENT']));
    }

    public function getDailyVisitor()
    {
        return $this->execute_filter('get_daily_visitor', md5($this->getVisitorCuttedIP().date('Y-m-d')));
    }
    
    public function writeVisitorIP()
    {
        echo $this->getVisitorIP();    
    }
    
    public function getReferer($part = '')
    {
        $ref = $_SERVER['HTTP_REFERER'];
        
        if($part && $ref)
        {
            $nref = parse_url($ref);
            
            if($part == 'scheme')
                $ref = $nref['scheme'];
            else if($part == 'host')
                $ref = $nref['host'];
            else if($part == 'path')
                $ref = $nref['path'];
        }
        
        return $this->execute_filter('get_referer', $ref, array(
            'part' => $part
        ));
    }
    
    public function writeReferer($part = '')
    {
        echo $this->getReferer($part);    
    }
    
    public function getUserID()
    {
        return self::$user->getID();
    }
    
    public function writeUserID()
    {
        echo $this->getUserID();    
    }
    
    public function getUserData($data, $user_id = 0)
    {
        if(!$data)
            return false;
            
        if($user_id)
        {
            $user = $this->getUsers(array(
                'id' => $user_id
            )); 
            
            if(!$user[0])
                return false;
                
            if($data == 'fields')
                return self::$base->db_to_array($user[0][$data]); 
                
            return $user[0][$data];  
        }
            
        $convert = array(
            'id' => 'id',
            'type' => 'type',
            'status' => 'status',
            'activation_code' => 'code',
            'salutation' => 'anrede',
            'first_name' => 'vorname',
            'last_name' => 'nachname',
            'suffix' => 'namenszusatz',
            'street' => 'str',
            'house_number' => 'hn',
            'zip' => 'plz',
            'city' => 'ort',
            'country' => 'land',
            'privat_phone' => 'tel_p',
            'business_phone' => 'tel_g',
            'mobile_phone' => 'mobil',
            'email' => 'email',
            'company' => 'firma',
            'position' => 'position',
            'tags' => 'tags',
            'registered_on' => 'registriert',
            'registered_by' => 'registriert_von',
            'last_online' => 'online',
            'activated_of' => 'von',
            'activated_until' => 'bis',
            'message' => 'nachricht',
            'avatar' => 'avatar',
            'fields' => 'cf'
        );
        
        $cdata = $convert[$data];
        if(!$cdata)
            return false;
        
        $value = self::$user->data($cdata);
        
        if($data == 'fields')
            $value = self::$base->db_to_array($value);
            
        return $value;
    }
    
    public function getUserRights()
    {
        return self::$user->getRights(true);
    }
    
    public function checkUserRights($area, $right)
    {
        if(!self::$user->isLogged())
            return false;
            
        if(self::$user->isSuperAdmin())
            return true;
        
        $rights = $this->getUserRights();
        
        return ($rights[$area][$right]?true:false);
    }
    
    public function isLogin()
    {
        return (self::$user->isLogged()?true:false);
    }
    
    public function isLogged()
    {
        return (self::$user->isLogged()?true:false);
    }

    public function isAdmin()
    {
        return (self::$user->isLogged() && self::$user->isAdmin()?true:false);
    }

    public function isSuperAdmin()
    {
        return (self::$user->isLogged() && self::$user->isSuperAdmin()?true:false);
    }
    
    
    public function data($id, $value = '')
    {
        if(!$id)
            return false;
            
        if(!$value)
            return $this->data[$id];
            
        $this->data[$id] = $value;
        return true;
    }
    
    public function getStorage($name, $base = '')
    {
        $name = self::$fksdb->save($name);
        $base = self::$fksdb->save($base);
        
        $storage = self::$fksdb->fetch("SELECT value, serialized FROM ".SQLPRE."storage WHERE name = '".$name."' AND base = '".$base."' LIMIT 1");
        if(!$storage)
            return false;
            
        if($storage->serialized)
            return self::$base->db_to_array($storage->value);
            
        return $storage->value;
    }
    
    public function getStorageBase($base)
    {
        $base = self::$fksdb->save($base);
        $query = self::$fksdb->query("SELECT name, value, serialized FROM ".SQLPRE."storage WHERE base = '".$base."'");
        $data = array();
        
        while($storage = self::$fksdb->fetch($query))
        {
            if($storage->serialized)
                $data[$storage->name] = self::$base->db_to_array($storage->value);
            else
               $data[$storage->name] = $storage->value; 
        }
        
        return $data;
    }
    
    public function setStorage($name, $value, $base = '')
    {
        if(!$name)
            return false;
        
        $serialized = 0;
        if(is_array($value) || is_object($value))
        {
            $value = self::$base->array_to_db($value);
            $serialized = 1;
        }
        
        $name = self::$fksdb->save($name);
        $value = self::$fksdb->save($value);
        $base = self::$fksdb->save($base);
        
        $storage = self::$fksdb->fetch("SELECT id FROM ".SQLPRE."storage WHERE name = '".$name."' AND base = '".$base."' LIMIT 1");
        
        if(!$storage)
        {
            self::$fksdb->insert("storage", array(
                "name" => $name,
                "base" => $base,
                "value" => $value,
                "serialized" => $serialized 
            ));   
        }
        else
        {
            self::$fksdb->update("storage", array(
                "value" => $value,
                "serialized" => $serialized 
            ), array(
                "id" => $storage->id
            ), 1);   
        }
        
        return true;
    }
    
    
    // content blocks
    public function initBlock($unique_id, $block)
    {
        $this->blocks[$unique_id] = $block;
        return true;    
    }
    
    public function getBlocks()
    {
        return $this->blocks;    
    }
    
    public function getBlock($unique_id)
    {
        return $this->blocks[$unique_id];
    }
    
    
    // apps
    public function initApp($unique_slug, $title, $content_callback, $options = array())
    {
        if(!$unique_slug)
            return false;
            
        $standard = array(
            'rights_callback' => '',
            'menu_parent' => '',
            'menu_position' => 50,
            'menu_title' => '',
            'window_width' => 680,
            'autosave' => true,
            'js_file' => '',
            'css_file' => ''
        );
        foreach($standard as $k => $v)
            $options[$k] = (isset($options[$k])?$options[$k]:$v);
            
            
        $this->apps[$unique_slug] = array(
            'slug' => $unique_slug,
            'title' => $title,
            'content_callback' => $content_callback,
            'rights_callback' => $options['rights_callback'],
            'menu_parent' => $options['menu_parent'],
            'menu_position' => $options['menu_position'],
            'menu_title' => $options['menu_title'],
            'width' => $options['window_width'],
            'autosave' => $options['autosave'],
            'js' => $options['js_file'],
            'css' => $options['css_file']
        );
        
        return true;
    }
    
    public function getApps()
    {
        return $this->apps;
    }
    
    public function getAppsSorted()
    {
        $apps = array();
        $rtn_apps = array();
        $c = 0;
        
        foreach($this->apps as $wkey => $w)
        {
            if(!$w['menu_parent'])
                continue;
            
            $c += 0.0001;
            $prio = intval($w['menu_position']);
            $key = intval($prio).''.$c;
            
            $apps[$key] = $w; 
        }
        
        krsort($apps);
        
        foreach($apps as $w)
            $rtn_apps[$w['menu_parent']][$w['slug']] = $w;
        
        return $rtn_apps;
    }
    
    public function getApp($unique_slug)
    {
        return $this->apps[$unique_slug];    
    }
    
    
    // widgets
    public function initWidget($unique_slug, $title, $callback, $width = 2, $height = 1, $click = '')
    {
        if(!$unique_slug)
            return false;
            
        $width = ($width > 3?3:($width < 1?1:$width));
        $height = ($height > 3?3:($height < 1?1:$height)); 
            
        $this->widgets[$unique_slug] = array(
            'slug' => $unique_slug,
            'title' => $title,
            'callback' => $callback,
            'width' => $width,
            'height' => $height,
            'click' => $click
        );
        return true;
    }
    
    public function getWidgets()
    {
        return $this->widgets;
    }
    
    public function getWidgetsSorted()
    {
        $widgets = array();
        $rtn_widgets = array();
        $c = 1;

        $t_widgets = $this->widgets;
        ksort($t_widgets);
        
        foreach($t_widgets as $wkey => $w)
        {
            $c -= 0.00001;
            $prio = self::$user->getWidget($wkey, 'prio');
            $key = intval($prio).''.$c;
            
            $widgets[$key] = $w; 
        }
        
        krsort($widgets);
        
        foreach($widgets as $w)
            $rtn_widgets[$w['slug']] = $w;

        unset($t_widgets, $widgets);
        
        return $rtn_widgets;
    }
    
    public function getWidget($unique_slug)
    {
        return $this->widgets[$unique_slug];    
    }
    
    
    // notifications
    public function initNotification($unique_slug, $title, $callback, $click = '')
    {
        if(!$unique_slug)
            return false;
            
        $this->notifications[$unique_slug] = array(
            'slug' => $unique_slug,
            'title' => $title,
            'callback' => $callback,
            'click' => $click
        );
        return true;
    }
    
    public function getNotifications()
    {
        return $this->notifications;
    }
    
    public function getNotification($unique_slug)
    {
        return $this->notifications[$unique_slug];    
    }
    
    public function insertNotification($title, $message = '', $click = null, $user_id = 0)
    {
        if(!$user_id)
            $user_id = self::$user->getID();
            
        if(is_array($click))
            $click = implode('|!|', $click);
        
        self::$fksdb->insert("notifications", array(
            "user" => $user_id,
            "time" => time(),
            "title" => trim($title),
            "message" => trim($message),
            "click" => $click
        ));
        
        return true;
    }


    // custom rights
    public function initRight($unique_slug, $title)
    {
        if(!$unique_slug)
            return false;

        $this->rights[$unique_slug] = array(
            'slug' => $unique_slug,
            'title' => $title
        );
        return true;
    }

    public function getRights()
    {
        return $this->rights;
    }

    public function getRight($unique_slug)
    {
        return $this->rights[$unique_slug];
    }
    
    
    // custom urls
    public function initCustomPage($unique_slug, $content_callback, $fks_callback = '', $element_id = 0, $explode_vars = '-', $explode_limit = 0)
    {
        $this->custom_pages[$unique_slug] = array(
            'slug' => $unique_slug,
            'content_callback' => $content_callback,
            'fks_callback' => $fks_callback,
            'element_id' => intval($element_id),
            'explode_vars' => $explode_vars,
            'explode_limit' => intval($explode_limit)
        );
        return true;    
    }
    
    public function getCustomPages()
    {
        return $this->custom_pages;    
    }
    
    public function getCustomPage($unique_slug)
    {
        return $this->custom_pages[$unique_slug];
    }


    // css and javascript files
    public function addCssFile($file, $unique_slug = '', $pos = 50, $media = '')
    {
        if(!$unique_slug)
            $unique_slug = $file;

        $this->css_files[$unique_slug] = array(
            'slug' => $unique_slug,
            'file' => $file,
            'pos' => intval($pos),
            'media' => $media
        );
        return true;
    }

    public function addCssFiles($files = array(), $pos = 50)
    {
        foreach($files as $file)
        {
            $this->css_files[$file] = array(
                'slug' => $file,
                'file' => $file,
                'pos' => intval($pos),
                'media' => ''
            );
        }
        return true;
    }

    public function getCssFiles()
    {
        $files = $this->css_files;
        if(!count($files))
            return array();

        $ordered = array();
        $countup = 0;

        foreach($files as $slug => $file)
        {
            $countup += 0.0001;
            $key = $file['pos'].$countup;
            $ordered[$key] = $file;
        }
        ksort($ordered);

        $rtn = array();
        foreach($ordered as $file)
        {
            $rtn[$file['slug']] = $file;
        }
        return $rtn;
    }

    public function getCssFile($unique_slug)
    {
        return $this->css_files[$unique_slug];
    }

    public function getCssFilesMergedUrl()
    {
        $files = $this->getCssFiles();
        $filestring = '';
        foreach($files as $file)
            $filestring .= (!$filestring?'':'|').$file['file'];

        $hash = str_replace(array('/', '+'), array('-', '_'), base64_encode(gzcompress($filestring)));
        return DOMAIN.'/static/css/'.$hash.'/';
    }

    public function addJsFile($file, $unique_slug = '', $pos = 50, $header = true, $defer = false)
    {
        if(!$unique_slug)
            $unique_slug = $file;

        $this->js_files[$unique_slug] = array(
            'slug' => $unique_slug,
            'file' => $file,
            'pos' => intval($pos),
            'header' => ($header?true:false),
            'defer' => $defer
        );
        return true;
    }

    public function addJsFiles($files = array(), $pos = 50, $header = true)
    {
        foreach($files as $file)
        {
            $this->js_files[$file] = array(
                'slug' => $file,
                'file' => $file,
                'pos' => intval($pos),
                'header' => ($header?true:false),
                'defer' => ''
            );
        }
        return true;
    }

    public function getJsFiles($position = 'all')
    {
        $files = $this->js_files;
        if(!count($files))
            return array();

        $ordered = array();
        $countup = 0;

        foreach($files as $slug => $file)
        {
            $countup += 0.0001;
            $key = $file['pos'].$countup;
            $ordered[$key] = $file;
        }
        ksort($ordered);

        $files = array();
        foreach($ordered as $file)
        {
            if($position != 'all' && (($position == 'header' && !$file['header']) || ($position == 'footer' && $file['header'])))
                continue;

            $files[$file['slug']] = $file;
        }
        return $files;
    }

    public function getJsFilesMergedUrl($position = 'all')
    {
        $files = $this->getJsFiles($position);
        $filestring = '';
        foreach($files as $file)
            $filestring .= (!$filestring?'':'|').$file['file'];

        $hash = str_replace(array('/', '+'), array('-', '_'), base64_encode(gzcompress($filestring)));
        return DOMAIN.'/static/js/'.$hash.'/';
    }

    public function getJsFile($unique_slug)
    {
        return $this->js_files[$unique_slug];
    }

    
    // get- functions
    public function getCommentCount($element = 0, $dclass_document = 0, $document = 0)
    {
        $sql_add = "";
        if($element)
            $sql_add .= " AND element = '".$element."' ";
        if($document)
            $sql_add .= " AND dokument = '".$document."' ";
        if($dclass_document)
            $sql_add .= " AND dk = '".$dclass_document."' ";
            
        $count = intval(self::$fksdb->count("SELECT id FROM ".SQLPRE."comments WHERE vid != '' ".$sql_add));
        
        return $this->execute_filter('get_comment_count', $count, array(
            'element_id' => $element,
            'dclass_document_id' => $dclass_document,
            'document_id' => $document
        ));
    }
    
    public function getComments($terms = array())
    {
        $sql_add = "";
        if(isset($terms['id']))
            $sql_add .= " AND id = '".$terms['id']."' ";
        if(isset($terms['user']))
            $sql_add .= " AND benutzer = '".$terms['user']."' ";
        if(isset($terms['element']))
            $sql_add .= " AND element = '".$terms['element']."' ";
        if(isset($terms['document']))
            $sql_add .= " AND dokument = '".$terms['document']."' ";
        if(isset($terms['dclass_document']))
            $sql_add .= " AND dk = '".$terms['dclass_document']."' ";
        if(isset($terms['open']))
            $sql_add .= " AND frei = '".($terms['open']?'1':'0')."' ";
        if(isset($terms['name']))
            $sql_add .= " AND name LIKE '".$terms['name']."' ";
        if(isset($terms['email']))
            $sql_add .= " AND email LIKE '".$terms['email']."' ";
        if(isset($terms['url']))
            $sql_add .= " AND web LIKE '".$terms['url']."' ";
        if(isset($terms['text']))
            $sql_add .= " AND text LIKE '".$terms['text']."' ";
        if(isset($terms['in_name']))
            $sql_add .= " AND name LIKE '%".$terms['in_name']."%' ";
        if(isset($terms['in_email']))
            $sql_add .= " AND email LIKE '%".$terms['in_email']."%' ";
        if(isset($terms['in_url']))
            $sql_add .= " AND web LIKE '%".$terms['in_url']."%' ";
        if(isset($terms['in_text']))
            $sql_add .= " AND text LIKE '%".$terms['in_text']."%' ";
            
        $rtn = array();
        
        $comments = self::$fksdb->query("SELECT * FROM ".SQLPRE."comments WHERE vid != '' ".$sql_add);
        while($d = self::$fksdb->fetch($comments))
        {
            $rtn[] = array(
                'id' => $d->id,
                'vid' => $d->vid,
                'time' => $d->timestamp,
                'element' => $d->element,
                'document' => $d->dokument,
                'dclass_document' => $d->dk,
                'user' => $d->benutzer,
                'ip' => $d->ip,
                'open' => ($d->frei?true:false),
                'name' => $d->name,
                'email' => $d->email,
                'url' => $d->web,
                'text' => $d->text
            );
        }
        
        return $this->execute_filter('get_comments', $rtn, $terms);
    }
    
    public function getDocuments($terms = array())
    {
        $sql_add = "";
        if(isset($terms['id']))
            $sql_add .= " AND id = '".$terms['id']."' ";
        if(isset($terms['author']))
            $sql_add .= " AND von = '".$terms['author']."' ";
        if(isset($terms['last_author']))
            $sql_add .= " AND von_edit = '".$terms['last_author']."' ";
        if(isset($terms['publisher']))
            $sql_add .= " AND freigegeben = '".$terms['publisher']."' ";
        if(isset($terms['title']))
            $sql_add .= " AND titel LIKE '".$terms['title']."' ";
        if(isset($terms['in_title']))
            $sql_add .= " AND titel LIKE '%".$terms['in_title']."%' ";
        if(isset($terms['released']))
            $sql_add .= "AND timestamp_freigegeben ".($terms['released']?'!=':'=')." '0' ";
        if(isset($terms['open']))
            $sql_add .= " AND gesperrt = '".($terms['open']?'0':'1')."' ";
        if(isset($terms['is_online']))
            $sql_add .= "AND (gesperrt = 0 AND timestamp_freigegeben != '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."'))) ";
        if(isset($terms['dclass']))
            $sql_add .= " AND klasse LIKE '".$terms['dclass']."' ";
        if(isset($terms['in_dclass']))
            $sql_add .= " AND klasse LIKE '%".$terms['in_dclass']."%' ";
        if(isset($terms['language']))
            $sql_add .= " AND sprachen LIKE '%".$terms['language']."%' ";
            
        $rtn = array();
        
        $cf = $this->getCustomFields();
        
        $docs = self::$fksdb->query("SELECT * FROM ".SQLPRE."documents WHERE papierkorb = '0' ".$sql_add);
        while($d = self::$fksdb->fetch($docs))
        {
            $categories = self::$base->fixedUnserialize($d->kats);
            if(isset($terms['category']) && !in_array($terms['category'], $categories))
                continue;
            
            $rtn[] = array(
                'id' => $d->id,
                'title' => $d->titel,
                'author' => $d->von,
                'last_author' => $d->von_edit,
                'publisher' => $d->freigegeben,
                'created' => $d->timestamp,
                'edited' => $d->timestamp_edit,
                'published' => $d->timestamp_freigegeben,
                'user_date' => $d->datum,
                'associated_elements' => $d->seiten,
                'online' => (!$d->gesperrt && $d->timestamp_freigegeben && ($d->anfang <= self::$base->getTime() && (!$d->bis || $d->bis >= self::$base->getTime()))?true:false),
                'released' => ($d->timestamp_freigegeben?true:false),
                'open' => ($d->gesperrt?false:true),
                'dclass' => $d->klasse,
                'languages' => self::$base->fixedUnserialize($d->sprachen),
                'meta' => $this->getElementMeta($d, $cf, true),
                'customfields' => $this->getElementCustomFields($d, $cf),
                'classes' => $d->css_klasse,
                'categories' => $categories,
                'searchable' => ($d->no_search?false:true)
            );
        }
        
        return $this->execute_filter('get_documents', $rtn, $terms);
    }
    
    public function getDocument($id, $data = '')
    {
        $docs = $this->getDocuments(array(
            'id' => $id
        ));
        if(!count($docs))
            return false;

        if($data)
            return $docs[0][$data];
            
        return $docs[0];   
    }
    
    
    public function getUsers($terms = array())
    {
        $sql_add = "";
        if(isset($terms['id']))
            $sql_add .= " AND id = '".$terms['id']."' ";
        if(isset($terms['type']))
            $sql_add .= " AND type = '".($terms['type'] == 'customer'?"1":($terms['type'] == 'employee'?"2":"0"))."' ";
        if(isset($terms['status']))
            $sql_add .= " AND status = '".($terms['status'] == 'active'?"0":($terms['status'] == 'inactive'?"1":"2"))."' ";
        if(isset($terms['salutation']))
            $sql_add .= " AND anrede LIKE '".$terms['salutation']."' ";
        if(isset($terms['first_name']))
            $sql_add .= " AND vorname LIKE '".$terms['first_name']."' ";
        if(isset($terms['in_first_name']))
            $sql_add .= " AND vorname LIKE '%".$terms['in_first_name']."%' ";
        if(isset($terms['last_name']))
            $sql_add .= " AND nachname LIKE '".$terms['last_name']."' ";
        if(isset($terms['in_last_name']))
            $sql_add .= " AND nachname LIKE '%".$terms['in_last_name']."%' ";
        if(isset($terms['street']))
            $sql_add .= " AND str LIKE '".$terms['street']."' ";
        if(isset($terms['house_number']))
            $sql_add .= " AND hn = '".$terms['house_number']."' ";
        if(isset($terms['zip']))
            $sql_add .= " AND plz = '".$terms['zip']."' ";
        if(isset($terms['city']))
            $sql_add .= " AND ort LIKE '".$terms['city']."' ";
        if(isset($terms['country']))
            $sql_add .= " AND land LIKE '".$terms['country']."' ";
        if(isset($terms['email']))
            $sql_add .= " AND email LIKE '".$terms['email']."' ";
        if(isset($terms['in_email']))
            $sql_add .= " AND email LIKE '%".$terms['in_email']."%' ";
        if(isset($terms['tags']))
            $sql_add .= " AND tags LIKE '".$terms['tags']."' ";
        if(isset($terms['in_tags']))
            $sql_add .= " AND tags LIKE '%".$terms['in_tags']."%' ";
        if(isset($terms['registered_before']))
            $sql_add .= " AND registriert < '".$terms['registered_before']."' ";
        if(isset($terms['registered_after']))
            $sql_add .= " AND registriert >= '".$terms['registered_after']."' ";
        if(isset($terms['registered_by']))
            $sql_add .= " AND registriert_von = '".$terms['registered_by']."' ";
        if(isset($terms['last_online']))
            $sql_add .= " AND online >= '".(time() - $terms['last_online'])."' ";
        if(isset($terms['has_avatar']))
            $sql_add .= " AND avatar != '1' ";
            
        $rtn = array();
        
        $users = self::$fksdb->query("SELECT * FROM ".SQLPRE."users WHERE papierkorb = '0' ".$sql_add);
        while($cuser = self::$fksdb->fetch($users))
        {
            $rtn[] = array(
                'id' => $cuser->id,
                'type' => ($cuser->type == 1?'customer':($cuser->type == 2?'employee':'both')),
                'status' => ($cuser->status == 1?'inactive':($cuser->status == 2?'closed':'active')),
                'activation_code' => $cuser->code,
                'salutation' => $cuser->anrede,
                'first_name' => $cuser->vorname,
                'last_name' => $cuser->nachname,
                'suffix' => $cuser->namenszusatz,
                'street' => $cuser->str,
                'house_number' => $cuser->hn,
                'zip' => $cuser->plz,
                'city' => $cuser->ort,
                'country' => $cuser->land,
                'privat_phone' => $cuser->tel_p,
                'business_phone' => $cuser->tel_g,
                'mobile_phone' => $cuser->mobil,
                'email' => $cuser->email,
                'company' => $cuser->firma,
                'position' => $cuser->position,
                'tags' => $cuser->tags,
                'registered_on' => $cuser->registriert,
                'registered_by' => $cuser->registriert_von,
                'last_online' => $cuser->online,
                'activated_of' => $cuser->von,
                'activated_until' => $cuser->bis,
                'message' => $cuser->nachricht,
                'avatar' => $cuser->avatar,
                'fields' => self::$base->db_to_array($cuser->cf)
            );
        }
        
        return $this->execute_filter('get_users', $rtn, $terms);
    }
    
    public function getFiles($terms = array())
    {
        $sql_add = "";
        if(isset($terms['cat']))
            $sql_add .= " AND kat = '".($terms['cat'] == 'file'?2:0)."' ";
        if(isset($terms['id']))
            $sql_add .= " AND id = '".$terms['id']."' ";
        if(isset($terms['is_dir']))
            $sql_add .= " AND isdir = '1' ";
        if(isset($terms['in_dir']))
            $sql_add .= " AND dir = '".$terms['in_dir']."' ";
        if(isset($terms['title']))
            $sql_add .= " AND titel LIKE '".$terms['title']."' ";
        if(isset($terms['in_title']))
            $sql_add .= " AND titel LIKE '%".$terms['in_title']."%' ";
        if(isset($terms['desc']))
            $sql_add .= " AND beschr LIKE '".$terms['desc']."' ";
        if(isset($terms['in_desc']))
            $sql_add .= " AND beschr LIKE '%".$terms['in_desc']."%' ";
        if(isset($terms['file_type']))
            $sql_add .= " AND last_type = '".$terms['file_type']."' ";
        if(isset($terms['orientation']))
            $sql_add .= " AND last_ausrichtung ".($terms['orientation'] == 'r'?"=":($terms['orientation'] == 'h'?">":"<"))." '1' ";
        if(isset($terms['user_id']))
            $sql_add .= " AND last_autor = '".$terms['user_id']."' ";
         
        $rtn = array();
            
        $stacks = self::$fksdb->query("SELECT id, kat, titel, last_type, isdir, dir, beschr, timestamp, last_timestamp, last_autor, last_ausrichtung, downloads FROM ".SQLPRE."files WHERE papierkorb = '0' ".$sql_add);
        while($stack = self::$fksdb->fetch($stacks))
        {
            $versions = self::$fksdb->count("SELECT id FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."'");
            $file = self::$fksdb->fetch("SELECT id, file, type, width, height FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");
            
            if($stack->kat == 2)
            {
                $physical = 'content/uploads/dokumente/'.$file->file.'.'.$file->type;
                $virtual = DOMAIN.'/files/'.$stack->id.'/'.self::$base->slug($stack->titel).'.'.$stack->last_type;
            }
            else
            {
                $vtitel = explode('.', $stack->titel);
                $ende = self::$base->slug($vtitel[0]).'.'.$stack->last_type;
            
                $physical = 'content/uploads/bilder/'.$file->file.'.'.$file->type;
                $virtual = DOMAIN.'/img/'.$stack->id.'-0-0-'.$ende;
            }
            
            $rtn[] = array(
                'id' => $stack->id,
                'file_id' => $file->id,
                'cat' => ($stack->kat == 2?'file':'image'),
                'is_dir' => ($stack->isdir?true:false),
                'in_dir' => $stack->dir,
                'title' => $stack->titel,
                'desc' => $stack->beschr,
                'time_created' => $stack->timestamp,
                'time_updated' => $stack->last_timestamp,
                'user_id' => $stack->last_autor,
                'file_type' => $stack->last_type,
                'orientation' => ($stack->last_ausrichtung == 1?'r':($stack->last_ausrichtung > 1?'h':'v')),
                'downloads' => $stack->downloads,
                'versions' => $versions,
                'url' => $virtual,
                'path_relative' => HOME_DIR.$physical,
                'path_absolute' => DOMAIN.'/'.$physical,
                'width' => $file->width,
                'height' => $file->height
            );
        }
        
        return $this->execute_filter('get_files', $rtn, $terms);
    }
    
    public function getImageUrl($id, $width, $height, $end = '')
    {
        return $this->execute_filter('get_image_url', $this->getImageUrlIntern($id, $width, $height, $end), array(
            'id' => $id,
            'width' => $width,
            'height' => $height,
            'end' => $end
        ));
    }
    
    private function getImageUrlIntern($id, $width, $height, $end = '')
    {
        if($end)
            return DOMAIN.'/img/'.$id.'-'.$width.'-'.$height.'-'.$end;
            
        $img = self::$fksdb->fetch("SELECT id, titel, last_type FROM ".SQLPRE."files WHERE id = '".$id."' AND kat = '0' AND papierkorb = '0' LIMIT 1");
        if(!$img)
            return false;
            
        $vtitel = explode('.', $img->titel);
        $end = self::$base->slug($vtitel[0]).'.'.$img->last_type;
            
        return DOMAIN.'/img/'.$img->id.'-'.$width.'-'.$height.'-'.$end;
    }
    
    public function getStructures($terms = array())
    {
        $sql_add = "";
        if(isset($terms['id']))
            $sql_add .= "AND id = '".$terms['id']."' ";
        if(isset($terms['name']))
            $sql_add .= "AND titel LIKE '".$terms['name']."' ";
        if(isset($terms['in_name']))
            $sql_add .= "AND titel LIKE '%".$terms['in_name']."%' ";
        if(isset($terms['online']))
            $sql_add .= "AND a1 = '".($terms['online']?'1':'0')."' ";
        if(isset($terms['editable']))
            $sql_add .= "AND a2 = '".($terms['editable']?'1':'0')."' ";
            
        $rtn = array();
        
        $structures = self::$fksdb->query("SELECT * FROM ".SQLPRE."structures WHERE papierkorb = '0' ".$sql_add);
        while($structure = self::$fksdb->fetch($structures))
        {
            $rtn[] = array(
                'id' => $structure->id,
                'name' => $structure->titel,
                'online' => ($structure->a1?true:false),
                'editable' => ($structure->a2?true:false)
            );
        }
        
        return $this->execute_filter('get_structures', $rtn);
    }
    
    public function getElements($terms = array(), $tree = true)
    {
        if(!isset($terms['structure']))
            $terms['structure'] = self::$base->getStructureID();
        
        $sql_add = "";
        if(isset($terms['id']))
            $sql_add .= "AND id = '".$terms['id']."' ";
        if(isset($terms['name']))
            $sql_add .= "AND titel LIKE '".$terms['name']."' ";
        if(isset($terms['in_name']))
            $sql_add .= "AND titel LIKE '%".$terms['in_name']."%' ";
        if(isset($terms['parent']))
            $sql_add .= "AND element = '".$terms['parent']."' ";
        if(isset($terms['id_or_parent']))
            $sql_add .= "AND (id = '".$terms['id_or_parent']." OR element = '".$terms['id_or_parent']."') ";
        if(isset($terms['structure']))
            $sql_add .= "AND struktur = '".$terms['structure']."' ";
        if(isset($terms['open']))
            $sql_add .= "AND frei = '".($terms['open']?'1':'0')."' ";
        if(isset($terms['is_online']))
            $sql_add .= "AND (frei = '1' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."'))) ";
        if(isset($terms['file']))
            $sql_add .= "AND templatedatei LIKE '".$terms['file']."' ";
        if(isset($terms['dclass']))
            $sql_add .= "AND klasse LIKE '".str_replace('.php', '', $terms['dclass'])."' ";
        if(isset($terms['noindex']))
            $sql_add .= "AND noseo = '".($terms['noindex']?'1':'0')."' ";
        
        $elements = self::$fksdb->rows("SELECT * FROM ".SQLPRE."elements WHERE papierkorb = '0' ".$sql_add." ORDER BY element, sort", "", ($tree?"element":""));
        $cf = $this->getCustomFields();
        
        if($tree)
            return $this->execute_filter('get_elements_tree', $this->getElementsLoop($elements, 0, $cf), $terms);
            
        $rtn = array();
        foreach($elements as $element)
        {
            $rtn[$element->id] = array(
                'id' => $element->id,
                'structure' => $element->struktur,
                'parent' => $element->element,
                'name' => $element->titel,
                'meta' => $this->getElementMeta($element, $cf),
                'open' => ($element->frei?true:false),
                'online' => ($element->frei && ($element->anfang <= self::$base->getTime() && (!$element->bis || $element->bis >= self::$base->getTime()))?true:false),
                'order' => $element->sort,
                'dclass' => $element->klasse,
                'customfields' => $this->getElementCustomFields($element, $cf),
                'file' => ($element->templatedatei?$element->templatedatei:'index.php'),
                'file_mobile' => $element->m_templatedatei,
                'noindex' => ($element->noseo?true:false),
                'in_sitemap' => ($element->nositemap?false:true)
            ); 
        }
        
        return $this->execute_filter('get_elements', $rtn, $terms);
    }
    
    private function getElementsLoop($elements, $parent = 0, $cf)
    {
        $rtn = array();
        
        if(!is_array($elements[$parent]))
            return array();
        
        foreach($elements[$parent] as $element)
        {
            $rtn[$element->id] = array(
                'id' => $element->id,
                'structure' => $element->struktur,
                'parent' => $element->element,
                'name' => $element->titel,
                'meta' => $this->getElementMeta($element, $cf),
                'open' => ($element->frei?true:false),
                'online' => ($element->frei && ($element->anfang <= self::$base->getTime() && (!$element->bis || $element->bis >= self::$base->getTime()))?true:false),
                'order' => $element->sort,
                'dclass' => $element->klasse,
                'customfields' => $this->getElementCustomFields($element, $cf),
                'file' => ($element->templatedatei?$element->templatedatei:'index.php'),
                'file_mobile' => $element->m_templatedatei,
                'noindex' => ($element->noseo?true:false),
                'in_sitemap' => ($element->nositemap?false:true),
                'children' => $this->getElementsLoop($elements, $element->id, $cf)
            );    
        }
        
        return $rtn;
    }
    
    private function getElementMeta($element, $cf, $doc = false)
    {
        if(!$doc) $m = self::$base->fixedUnserialize($element->sprachen);
        else $m = self::$base->fixedUnserialize($element->sprachenfelder);
        
        $nm = array();
        
        if(!is_array($m))
            return $nm;
        
        foreach($m as $l => $v)
        {
            if(!is_array($v))
                continue;
            
            $nm[$l] = array(
                'title' => $v['titel'],
                'metatitle' => $v['htitel'],
                'metadescription' => $v['desc'],
                'metakeywords' => $v['tags'],
            );
            
            if(isset($v['titel'])) unset($v['titel']);
            if(isset($v['htitel'])) unset($v['htitel']);
            if(isset($v['desc'])) unset($v['desc']);
            if(isset($v['tags'])) unset($v['tags']);
            if(isset($v['url'])) unset($v['url']);
            
            if(!count($v))
                continue;
            
            foreach($v as $i => $w)
            {
                if(!array_key_exists($i, $cf))
                    continue;
                
                if($cf[$i]['type'] == 'checkbox')
                    $w = ($w == 'fks_true'?true:false);
                    
                $nm[$l]['customfields'][$i] = $w;
            }     
        }
        
        return $nm;
    }
    
    private function getElementCustomFields($element, $cf)
    {
        $c = self::$base->fixedUnserialize($element->cf);
        if(!is_array($c) || !count($c))
            return array();   
            
        $nm = array(); 
        foreach($c as $i => $w)
        {
            if(!array_key_exists($i, $cf))
                continue;
                
            if($cf[$i]['type'] == 'checkbox')
                $w = ($w == 'fks_true'?true:false);
                
            $nm[$i] = $w;
        }  
        
        return $nm;  
    }
    
    public function getCategories($terms = array(), $tree = true)
    {
        $sql_add = "";
        if(isset($terms['id']))
            $sql_add .= "AND id = '".$terms['id']."' ";
        if(isset($terms['name']))
            $sql_add .= "AND name LIKE '".$terms['name']."' ";
        if(isset($terms['in_name']))
            $sql_add .= "AND name LIKE '%".$terms['in_name']."%' ";
        if(isset($terms['parent']))
            $sql_add .= "AND kat = '".$terms['parent']."' ";
        if(isset($terms['id_or_parent']))
            $sql_add .= "AND (id = '".$terms['id_or_parent']." OR kat = '".$terms['id_or_parent']."') ";
        
        $cats = self::$fksdb->rows("SELECT * FROM ".SQLPRE."categories WHERE id != '0' ".$sql_add." ORDER BY kat, sort", "", ($tree?"kat":""));
        
        if($tree)
            return $this->getCategoriesLoop($cats, 0);
            
        $rtn = array();
        foreach($cats as $cat)
        {
            $rtn[$cat->id] = array(
                'id' => $cat->id,
                'parent' => $cat->kat,
                'name' => $cat->name,
                'timestamp' => $cat->timestamp,
                'order' => $cat->sort
            ); 
        }
        return $rtn;
    }
    
    private function getCategoriesLoop($cats, $parent = 0)
    {
        $rtn = array();
        
        if(!is_array($cats[$parent]))
            return array();
        
        foreach($cats[$parent] as $cat)
        {
            $rtn[$cat->id] = array(
                'id' => $cat->id,
                'parent' => $cat->kat,
                'name' => $cat->name,
                'timestamp' => $cat->timestamp,
                'order' => $cat->sort,
                'children' => $this->getCategoriesLoop($cats, $cat->id)
            );    
        }
        
        return $rtn;
    }


    // get content
    public function getContentByDocument($doc_id, $p = array(), $language = '')
    {
        $static = $this->getStatic();

        if(!is_object($static['fks']) || !is_object($static['content']))
        {
            require_once(ROOT.'inc/classes.view/class.fks.php');
            require_once(ROOT.'inc/classes.view/class.content.php');
            require_once(ROOT.'inc/classes.view/class.navigation.php');
            require_once(ROOT.'inc/classes.blocks/_basic.php');

            if(!$language)
                $language = self::$trans->getStandardLanguage();

            $document = $this->getDocument($doc_id);
            if(!$document)
                return false;

            $version = self::$fksdb->fetch("SELECT id FROM ".SQLPRE."document_versions WHERE dokument = '".$document['id']."' AND language = '".$language."' AND aktiv = 1 ORDER BY timestamp_freigegeben DESC LIMIT 1");
            if(!$version)
                return false;

            $static['fks'] = new Page($static, array(
                'title' => $document['meta'][$language]['title'],
                'language' => $language,
                'preview' => $document['id'],
                'dversion_preview' => $version->id,
                'dclass' => ($document['dclass']?$document['id']:0)
            ));
            $static['content'] = new Content($static);
        }

        $p['document_id'] = $doc_id;
        return $static['content']->get($p);
    }

    public function getDclassDocumentValues($doc_id, $language = '')
    {
        return $this->getContentByDocument($doc_id, array('dclass_values' => true), $language);
    }
    
    
    // hooks, filter and shortcodes
    public function executeHook($name, $arg = array(), $must_return = false)
    {
        if(!$name)
            return false; 
           
        $hooks = $this->hooks;
        $rtn = '';
        
        if(is_array($hooks[$name]))
        {
            if(!is_array($arg))
                $arg = array(); 
                
            if($must_return)
                ob_start();  
            
            foreach($hooks[$name] as $nr => $ar)
            {
                if(!is_callable($ar['func']))
                    continue;
                    
                $narg = $arg;
                if(is_array($ar['arg']))
                    $narg = array_merge($arg, $ar['arg']);
                    
                if(is_string($ar['func']))
                    $rtn .= call_user_func($ar['func'], $narg, self::$static);
                elseif(is_array($ar['func']))
                    $rtn .= call_user_func_array($ar['func'], array($narg, self::$static));
            }
            
            if($must_return)
            {
                $rtn .= ob_get_contents();
                ob_end_clean();
            }
        }
        
        return $rtn;
    }
    public function execute_hook($name, $arg = array(), $must_return = false)
    {
        return $this->executeHook($name, $arg, $must_return);
    }
    
    public function addHook($name, $func, $arg = array())
    {
        if(!$name || !$func)
            return false;
            
        $this->hooks[$name][] = array(
            'func' => $func,
            'arg' => $arg
        );
        
        return true;
    }
    public function add_hook($name, $func, $arg = array())
    {
        return $this->addHook($name, $func, $arg);
    }
    
    public function executeFilter($name, $content, $arg = array())
    {
        if(!$name || !$content)
            return false; 
           
        $filter = $this->filter;
        
        if(is_array($filter[$name]))
        {
            if(!is_array($arg))
                $arg = array();   
            
            foreach($filter[$name] as $nr => $ar)
            {
                if(!is_callable($ar['func']))
                    continue;
                    
                $narg = $arg;
                if(is_array($ar['arg']))
                    $narg = array_merge($arg, $ar['arg']);
                    
                if(is_string($ar['func']))
                    $content = call_user_func($ar['func'], $content, $narg, self::$static);
                elseif(is_array($ar['func']))
                    $content = call_user_func_array($ar['func'], array($content, $narg, self::$static));   
            }
        }
        
        return $content;
    }
    public function execute_filter($name, $content, $arg = array())
    {
        return $this->executeFilter($name, $content, $arg);
    }
    
    public function addFilter($name, $func, $arg = array())
    {
        if(!$name || !$func)
            return false;
            
        $this->filter[$name][] = array(
            'func' => $func,
            'arg' => $arg
        );
    }
    public function add_filter($name, $func, $arg = array())
    {
        return $this->addFilter($name, $func, $arg);
    }
    
    public function executeShortcode($content = '')
    {
        $shortcodes = $this->shortcodes;
        
        if(is_array($shortcodes))
        {                
            $codenames = array_keys($shortcodes);
            $codenames_esc = array_map('preg_quote', $codenames);
            $regex_codes = implode('|',  $codenames_esc);
            
            $content = preg_replace_callback('/'.'(.?)\[('.$regex_codes.')\b(.*?)(?:(\/))?\](?:(.+?)\[\/\2\])?(.?)'.'/s', array(get_class($this), 'execute_shortcode_func'), $content);
        }
        
        return $content;
    }
    public function execute_shortcode($content = '')
    {
        return $this->executeShortcode($content);
    }
    
    private function execute_shortcode_func($code) 
    {  
        $shortcodes = $this->shortcodes; 
    
        // Verschachtelung verhindern
    	if ($code[1] == '[' && $code[6] == ']') 
    		return substr($code[0], 1, -1);
    	
        // Attribute laden
        $attribute = array();
        $code[3] = str_replace('&quot;', '"', $code[3]);
    	$code[3] = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $code[3]);
    	if(preg_match_all('/(\w+)\s*=\s*"([^"]*)"(?:\s|$)|(\w+)\s*=\s*\'([^\']*)\'(?:\s|$)|(\w+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/', $code[3], $ergebnisse, PREG_SET_ORDER)) 
        {
    		foreach($ergebnisse as $e) 
            {
    			if (!empty($e[1]))
    				$attribute[strtolower($e[1])] = stripcslashes($e[2]);
    			elseif (!empty($e[3]))
    				$attribute[strtolower($e[3])] = stripcslashes($e[4]);
    			elseif (!empty($e[5]))
    				$attribute[strtolower($e[5])] = stripcslashes($e[6]);
    			elseif (isset($e[7]) and strlen($e[7]))
    				$attribute[] = stripcslashes($e[7]);
    			elseif (isset($e[8]))
    				$attribute[] = stripcslashes($e[8]);
    		}
    	} 
        else 
        {
    		$attribute = ltrim($code[3]);
    	}
        // Atribute geladen
    
        // Shortcode verarbeiten
        if(is_callable($shortcodes[$code[2]]))
        {
            return $code[1].call_user_func($shortcodes[$code[2]], $attribute, $code[5], self::$static).$code[6];
        }
    }
    
    public function addShortcode($name, $func)
    {
        if(!$name || !is_callable($func))
            return false;
            
        $this->shortcodes[$name] = $func;
        
        return true;
    }
    public function add_shortcode($name, $func)
    {
        return $this->addShortcode($name, $func);
    }
    
    public function hasShortcodes()
    {
        return (count($this->shortcodes)?true:false);
    }
    public function has_shortcodes()
    {
        return $this->hasShortcodes();
    }
    
    
    
    public function doLogin($user_id, $password = '', $role = -1)
    {
        $login = self::$user->getLoginHash($user_id);
        $pw = self::$user->getPasswordHash($password); 
        
        $status = array();
        $error = array();
        
        $t_user = self::$fksdb->fetch("SELECT login, pw, id, von, bis, status, vorname, type FROM ".SQLPRE."users WHERE papierkorb = '0' AND login = '".$login."' AND login != '' AND pw != '' AND pw != '".md5('')."' LIMIT 1");
        
        if(!$t_user)
            $error[] = 'wrong_username'; 
        if($t_user->pw != $pw && $password)
            $error[] = 'wrong_password';           
        if($t_user->von > time() || ($t_user->bis && $t_user->bis < time()))
            $error[] = 'lifetime_expired';
        if($t_user->status == 1)
            $error[] = 'closed';
        if($t_user->status == 2)
            $error[] = 'inactive';

        if(!count($error))
        {
            $status['status'] = 'succeeded';
            
            if($role == -1)
                $role = intval(self::$fksdb->data("SELECT rolle FROM ".SQLPRE."user_roles WHERE benutzer = '".$t_user->id."' ORDER BY id LIMIT 1", "rolle"));
            $status['role'] = $role;
            
            if(self::$user->isLogged())
            {
                $status['logged_out'] = self::$user->getID;
                self::$user->doLogout();
            }
                
            $status['is_logged'] = self::$user->doLogin($login, $t_user->pw, $role);
        }
        else
        {
            $status = array(
                'status' => 'failed',
                'errors' => $error
            );
        }
            
        return $status; 
    }
    
    
    public function setCustomField($slug, $name, $global = false, $type = 'input', $values = array())
    {
        if(!$name || !$slug)
            return false;
            
        $this->custom_fields[$slug] = array(
            'name' => $name,
            'type' => $type,
            'global' => $global,
            'values' => $values
        );
        
        return true;    
    }
    
    public function getCustomFields($source = 'both')
    {
        $custom_fields_template = self::$base->getActiveTemplateConfig('custom_fields');
        if(!is_array($custom_fields_template))
            $custom_fields_template = array();
        $custom_fields_api = $this->custom_fields;
        
        if($source == 'template')
            return $custom_fields_template;
        if($source == 'api')
            return $custom_fields_api; 
        
        return array_merge($custom_fields_template, $custom_fields_api);
    }
    
    public function setUserField($slug, $name, $type = 'input', $values = array())
    {
        if(!$name || !$slug)
            return false;
            
        $this->user_fields[$slug] = array(
            'name' => $name,
            'type' => $type,
            'values' => $values
        );    
        
        return true;
    }
    
    public function getUserFields($source = 'both')
    {
        $user_fields_template = self::$base->getActiveTemplateConfig('user_fields');
        if(!is_array($user_fields_template))
            $user_fields_template = array();
        $user_fields_api = $this->user_fields;
        
        if($source == 'template')
            return $user_fields_template;
        if($source == 'api')
            return $user_fields_api; 
        
        return array_merge($user_fields_template, $user_fields_api);
    }
    
    public function getUserField($field_slug, $user_id = 0)
    {
        $ufields = $this->getUserFields();
        
		if(!is_array($ufields[$field_slug]))
			return '';
		
        if(!$user_id)
        {
            if(!self::$user->isLogged())
                return '';
            else
                $cf_data = self::$user->data('cf');
        }
        else
        {
            $cf_data = self::$fksdb->data("SELECT cf FROM ".SQLPRE."users WHERE id = '".$user_id."' LIMIT 1", "cf");
        }
        
        if(!$cf_data)
            return '';
        
        $cf = self::$base->db_to_array($cf_data);
		$value = $cf[$field_slug];
        
        if($ufields[$field_slug]['type'] == 'checkbox')
            return ($value == 'fks_true'?true:false);
        
        return htmlspecialchars_decode($value);        
    }
    
    public function setFileField($slug, $name, $type = 'input', $values = array())
    {
        if(!$name || !$slug)
            return false;
            
        $this->file_fields[$slug] = array(
            'name' => $name,
            'type' => $type,
            'values' => $values
        );    
        
        return true;
    }
    
    public function getFileFields($source = 'both')
    {
        $file_fields_template = self::$base->getActiveTemplateConfig('file_fields');
        if(!is_array($file_fields_template))
            $file_fields_template = array();
        $file_fields_api = $this->file_fields;
        
        if($source == 'template')
            return $file_fields_template;
        if($source == 'api')
            return $file_fields_api; 
        
        return array_merge($file_fields_template, $file_fields_api);
    }
    
    public function getFileField($field_slug, $file_id)
    {
        $ufields = $this->getFileFields();
        
		if(!is_array($ufields[$field_slug]))
			return '';
		
        $cf_data = self::$fksdb->data("SELECT cf FROM ".SQLPRE."files WHERE id = '".$file_id."' LIMIT 1", "cf");
        
        if(!$cf_data)
            return '';
        
        $cf = self::$base->db_to_array($cf_data);
		$value = $cf[$field_slug];
        
        if($ufields[$field_slug]['type'] == 'checkbox')
            return ($value == 'fks_true'?true:false);
        
        return htmlspecialchars_decode($value);        
    }
    
    
    public function getImage2Data($image)
    {
        return self::$base->image2data($image);    
    }
    
    public function writeImage2Data($image)
    {
        echo self::$base->image2data($image);    
    }
}
?>
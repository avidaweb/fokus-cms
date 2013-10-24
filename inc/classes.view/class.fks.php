<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Page
{
    private static $static, $base, $api, $fksdb, $user, $suite, $trans; 
    
    private $id = 0, $title = '', $language = '', $paging = 0, $dclass = 0, $search = '';
    private $preview = false, $dversion_preview = false, $area = '', $mark_word = '', $templatefile = '';
    private $error = 0, $feed = false;
    private $custom_page = null, $custom_values = array();
    
    private $element, $element_languages, $element_roles, $element_errors;
    private $dclass_element, $dclass_document;
    private $structure, $home_element, $elements_tree;
    private $form_error, $comment_error, $login_error;
    private $is_home, $is_html5 = true;
    private $template_texts;
    
        
    function __construct($static, $dynamic = array())
    {         
        // static
        self::$static = $static;
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
        
        // dynamic
        $this->id = intval($dynamic['id']);
        $this->title = $dynamic['title'];
        $this->language = ($dynamic['language']?$dynamic['language']:self::$trans->getStandardLanguage());
        $this->paging = intval($dynamic['paging']);
        $this->dclass = intval($dynamic['dclass']);
        $this->search = urldecode($dynamic['search']);
        $this->preview = $dynamic['preview'];
        $this->dversion_preview = $dynamic['dversion_preview'];
        $this->area = $dynamic['area'];
        $this->mark_word = $dynamic['mark_word'];
        $this->templatefile = $dynamic['templatefile'];
        $this->error = intval($dynamic['error']);
        $this->feed = $dynamic['feed'];
        
        
        // init
        $this->initCustomPage($dynamic['cp'], $dynamic['cp_vars']);
        $this->initErrors(); 
        $this->initPreview();
        $this->initStructure();
        $this->initHomeElement(); 
        $this->initSearchQuery();   
        $this->initWWW();  
        $this->initElement(); 
        $this->initElementsTree();
        $this->initDclass(); 
        $this->initRoles();
        $this->initURL(); 
        $this->initForms();
        $this->initTemplateTexts();
        $this->initCustomPageHook();
        $this->initStats();
    }
    
    
    private function initCustomPage($cp = '', $cp_vars = '')
    {
        if(!$cp)
            return false; 
        
        $custom_page = self::$api->getCustomPage($cp);
        if(!$custom_page)
        {
            $this->error = 404;
            return false;
        } 
        
        $custom_page['original_var'] = $cp_vars;
        if(!$custom_page['explode_vars'] || !$cp_vars)
            $custom_page['vars'] = array($cp_vars);
        else
            $custom_page['vars'] = explode($custom_page['explode_vars'], $cp_vars, ($custom_page['explode_limit']?$custom_page['explode_limit']:9999));
            
        if($custom_page['element_id'])
            $this->id = intval($custom_page['element_id']);
        
        $this->custom_page = $custom_page; 
        return true;
    }
    
    private function initCustomPageHook()
    {
        if(!$this->isCustomPage())
            return false;
        
        $custom_page = $this->getCustomPage();
           
        if($custom_page['fks_callback'] && is_callable($custom_page['fks_callback']))
        {
            $hatr = array(
                'fks' => $this,
                'api' => self::$api,
                'fksdb' => self::$fksdb
            );
                
            ob_start();  
            
            if(is_string($custom_page['fks_callback']))
                call_user_func($custom_page['fks_callback'], $custom_page['slug'], $custom_page['vars'], $this, $hatr);
            elseif(is_array($custom_page['fks_callback']))
                call_user_func_array($custom_page['fks_callback'], array($custom_page['slug'], $custom_page['vars'], $this, $hatr)); 
                
            ob_end_clean();  
        } 
        
        return true;     
    }
    
    public function isCustomPage()
    {
        return ($this->custom_page != null?true:false);
    }
    
    public function getCustomPage($data = '')
    {
        if($data)
            return $this->custom_page[$data];
        
        return $this->custom_page;
    }
    
    private function initErrors()
    {
        if(!$this->error || $this->preview || $this->feed)
            return false;
            
        $http_codes = array(
            403 => "403 Forbidden",
            404 => "404 Not Found",
            500 => "500 Internal Server Error",
            503 => "503 Service Unavailable"
        );
        
        if(!in_array($this->error, $http_codes))
            $this->error = 404;
            
        header(($_ENV['SERVER_PROTOCOL']?$_ENV['SERVER_PROTOCOL']:$_SERVER['SERVER_PROTOCOL'])." ".$http_codes[$this->error], true, $this->error);
    }
    
    private function initPreview()
    {
        if(!$this->preview)
            return false;
            
        $idO = self::$fksdb->fetch("SELECT element FROM ".SQLPRE."document_relations WHERE dokument = '".$this->preview."' AND element > '0' ORDER BY sort LIMIT 1"); 
        $this->id = $idO->element; 
        
        if(!$idO->element)
        {
            $idO = self::$fksdb->fetch("SELECT id FROM ".SQLPRE."elements WHERE frei = '1' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) ORDER BY sort LIMIT 1");
            $this->id = $idO->id; 
        } 
    
        if(!self::$user->isAdmin())
            self::$base->go(self::$api->getDomain());
    }
    
    private function initStructure()
    {
        $this->structure = self::$fksdb->fetch("SELECT id, titel, a1, a2, snavi FROM ".SQLPRE."structures WHERE a".(self::$user->isForesight() && self::$user->getForesight('structure') == 2?"2":"1")." = '1' AND papierkorb = '0' LIMIT 1"); 
    }
    
    private function initElement()
    {
        $this->element = self::$fksdb->fetch("SELECT * FROM ".SQLPRE."elements WHERE id = '".$this->id."' AND frei = '1' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) LIMIT 1");
        
        if(((!$this->element || $this->element->papierkorb) || $this->element->klasse || $this->element->struktur != $this->structure->id) && !$this->preview && !$this->feed && !$this->isCustomPage())
            self::$base->go($this->getDomain().$this->getLanguage(false, '/'));
            
        $this->element_languages = self::$base->fixedUnserialize($this->element->sprachen);
        if(!is_array($this->element_languages)) 
            $this->element_languages = array();
            
        if($this->templatefile)
            $this->element->templatedatei = $this->templatefile;
            
        $this->element->slots = self::$base->db_to_array($this->element->slots);
        if(!is_array($this->element->slots))
            $this->element->slots = array();

        self::$api->execute_hook('init_element', self::$static, true);
    }
    
    private function initHomeElement()
    {
        $this->home_element = self::$fksdb->fetch("SELECT * FROM ".SQLPRE."elements WHERE struktur = '".$this->structure->id."' AND element = '0' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) ORDER BY sort ASC LIMIT 1"); 
        
        if($this->id && $this->id == $this->home_element->id && !$this->preview && !$this->feed && !$this->dclass && !$this->paging && !$_GET['login_ok'])
			self::$base->go(self::$api->getDomain().$this->getLanguage(false, '/'), true);
        
        $this->is_home = false;
        if(!$this->id) 
        {
            $this->is_home = true;
            $this->id = $this->home_element->id;
        }
        
        if(!$this->home_element && !$this->preview && !$this->feed)
        {
            self::$base->printFrontendError(self::$trans->__('Das CMS fokus wurde unter %1 frisch installiert. Sie können nun damit beginnen, Ihre neue Website mit Inhalten zu befüllen.', false, array(DOMAIN)), self::$trans->__('Frische Installation.'));
        }
    }
    
    private function initElementsTree()
    {
        $this->elements_tree = array($this->element);
        
        $this->initElementsTreeLoop($this->element->element);
    }
    
    private function initElementsTreeLoop($element_id)
    {
        if(!$element_id)
            return false;
            
        $parent = self::$fksdb->fetch("SELECT * FROM ".SQLPRE."elements WHERE id = '".$element_id."' AND frei = '1' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) LIMIT 1");
        
        if($parent)
        {
            $this->elements_tree[] = $parent;
            $this->initElementsTreeLoop($parent->element);
        }
    }
    
    private function initDclass()
    {
        if(!$this->dclass)
            return false;
            
        $this->dclass_document = self::$fksdb->fetch("SELECT sprachenfelder, id, klasse, cf, kats FROM ".SQLPRE."documents WHERE id = '".$this->dclass."' AND papierkorb = '0' LIMIT 1");
        
        $this->element_languages = self::$base->fixedUnserialize($this->dclass_document->sprachenfelder);
        if(!is_array($this->element_languages)) 
            $this->element_languages = array();
        
        $this->dclass_element = self::$fksdb->fetch("SELECT * FROM ".SQLPRE."elements WHERE element = '".$this->id."' AND frei = '1' AND papierkorb = '0' AND klasse = '".$this->dclass_document->klasse."' AND klasse != '' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) LIMIT 1");
        
        $this->dclass_document->categories = self::$base->fixedUnserialize($this->dclass_document->kats);
        
        if(!$this->dclass_element && !$this->preview && !$this->feed)
            self::$base->go($this->getDomain().$this->getLanguage(false, '/'));
    }
    
    private function initRoles()
    {
        if(!is_array($this->elements_tree) || $this->preview || $this->feed)
            return false;
            
        foreach($this->elements_tree as $vv)
        {
            $this->element_roles = self::$base->fixedUnserialize($vv->rollen);
            if(!is_array($this->element_roles)) 
                $this->element_roles = array();
            
            if(count($this->element_roles) && !self::$user->isSuperAdmin() && !$this->is_home)
            {
                $is_in_role = false;
                $a_roles = self::$user->getAvailableRoles();
                foreach($a_roles as $gr)
                {
                    if(in_array($gr, $this->element_roles))
                        $is_in_role = true;
                }
                
                if(!$is_in_role)
                {
                    if($vv->rollen_fehler)
                    {   
                        self::$base->go(DOMAIN.'/error/403/');
                    }
                    elseif(!$vv->rollen_fehler)
                    {   
                        $rfehler = "";
                        foreach($this->element_roles as $er)
                        {
                            if($er > 1)
                                $rfehler .= (!$rfehler?" AND (":" OR ")." id = '".$er."' ";
                        }
                        $rfehler .= (!$rfehler?"":") ");
                        
                        $rQ = self::$fksdb->fetch("SELECT fehler FROM ".SQLPRE."roles WHERE papierkorb = '0' ".$rfehler." AND fehler != '' ORDER BY sort LIMIT 1");
                        $this->element_errors = self::$base->fixedUnserialize($rQ->fehler);
                    }
                }
            }
        }
        
        $this->element_roles = self::$base->fixedUnserialize($this->element->rollen);
        if(!is_array($this->element_roles)) 
            $this->element_roles = array();
    }
    
    private function initSearchQuery()
    {
        if($_REQUEST['fks_search'] && !$this->preview && !$this->feed)
            self::$base->go($this->getRoot().'q/'.self::$fksdb->save(urlencode($_REQUEST['fks_search'])).'/');
    }
    
    private function initWWW()
    {
        if(!$this->preview && !$this->feed && self::$base->getOpt('www')) // WWW-Redirect
        {
            $has_www = (Strings::strExists('www.', $_SERVER['SERVER_NAME'], false) || Strings::strExists('www.', $_SERVER['HTTP_HOST'], false)?true:false);
            
            if(self::$base->getOpt('www') == 1) // with www
            { 
                if(!$has_www) 
                { 
                    if(!Strings::strExists('//www.', self::$api->getDomain(), false))
                    {
                        $pre = '';
                        $end = '';

                        if(Strings::strExists('http://', self::$api->getDomain()))
                        {
                            $pre = 'http://';
                            $end = substr(self::$api->getDomain(), 7);
                        }
                        elseif(Strings::strExists('https://', self::$api->getDomain()))
                        {
                            $pre = 'https://';
                            $end = substr(self::$api->getDomain(), 8);
                        }
                        
                        if($pre && $end)
                        {
                            $tmp_domain = $pre.'www.'.$end; 
                            
                            if($this->is_home) 
                                self::$base->go($tmp_domain.'/', true);
                            elseif(!$this->dclass) 
                                self::$base->go($tmp_domain.'/'.$this->id.'/nbb/', true);
                            else 
                                self::$base->go($tmp_domain.'/'.$this->id.'/'.$this->dclass.'/nbc/', true);
                        }
                    }
                    else
                    { 
                        if($this->is_home) 
                            self::$base->go(self::$api->getDomain(), true);
                        elseif(!$this->dclass) 
                            self::$base->go(self::$api->getDomain().$this->id.'/nab/', true);
                        else 
                            self::$base->go(self::$api->getDomain().$this->id.'/'.$this->dclass.'/nac/', true);
                    }
                }
            }
            elseif(self::$base->getOpt('www') == 2) // without www
            {
                if($has_www)
                {
                    $tmp_domain = str_replace('//www.', '//', self::$api->getDomain()); 
                    
                    if($this->is_home) 
                        self::$base->go($tmp_domain.'/', true);
                    elseif(!$this->dclass) 
                        self::$base->go($tmp_domain.'/'.$this->id.'/nab/', true);
                    else 
                        self::$base->go($tmp_domain.'/'.$this->id.'/'.$this->dclass.'/nac/', true);
                }
            }
        }
    }
    
    private function initURL()
    {
        $t_normal = self::$base->slug($this->element_languages[$this->language]['titel']);
        $t_url = self::$base->slug($this->element_languages[$this->language]['url']);
        
        if($this->title == $t_url || (!$t_url && $this->title == $t_normal) || (!$t_url && $this->title == 'na') || $this->preview || $this->feed || $this->is_home)
            return true;
            
		self::$base->go($this->getDomain().$this->element->id.'/'.($this->dclass?$this->dclass.'/':'').self::$base->auto_slug($this->element_languages[$this->language]).'/'.($this->paging?$this->paging.'/':''), true);
        
        return false;
    }
    
    private function initForms()
    {
        if($this->preview || $this->feed)
            return false;
        
        if($_POST['fokus_form_send'] || $_GET['acode'])
            include(ROOT.'inc/classes.view/class.form.php');
            
        if($_POST['fks_comment_send'])
            include(ROOT.'inc/classes.view/class.comment.php');
        
        if($_POST['fks_login_submit'] || $_POST['fks_logout_submit'])
            include(ROOT.'inc/classes.view/class.login.php'); 
    }
    
    private function initTemplateTexts()
    {
        $tf = $this->getTemplateLanguageFile();
        $fks_text = array();
        
        if($tf && file_exists($tf))
        {
            ob_start();
            include($tf);
            ob_end_clean();
        }
        
        $this->template_texts = $fks_text;
    }
    
    private function initStats()
    {
        if($this->preview || $this->feed || $this->error)
            return false;
     
        include_once(ROOT.'inc/classes.other/class.stats.php');
        
        $stats = new Stats(self::$static);
        $stats->saveVisit($this->element->id, $this->dclass_document->id);
    }
    
    
    private function setCustomValue($value, $data = '')
    {
        if(!$value)
            return false;
            
        $this->custom_values[$value] = $data;
        return true;
    }
    
    private function getCustomValue($value)
    {
        return $this->custom_values[$value];
    }
    
    
    public function getError()
    {
        return $this->error;
    }
    
    
    public function getID()
    {
        return intval($this->id);
    }
    
    public function getStructure($data = '')
    {
        if($data)
            return $this->structure->$data;
        return $this->structure;    
    }
    
    public function getStructureID()
    {
        return intval($this->getStructure('id'));   
    }
    
    public function getElement($data = '')
    {
        if($data)
            return $this->element->$data;
        return $this->element;    
    }
    
    public function isOverwrittenSlot($slot)
    {
        if(!count($this->element->slots))
            return false;
            
        if($this->element->slots[$slot])
            return true;
            
        return false;    
    }
    
    public function isDclass()
    {
        return ($this->dclass && $this->dclass_document && $this->dclass_element?true:false);
    }
    
    public function getDclassElementID()
    {
        if(!$this->isDclass())
            return 0;
            
        return intval($this->dclass_element->id);
    }
    
    public function getDclassDocumentID()
    {
        if(!$this->isDclass())
            return 0;
            
        return intval($this->dclass_document->id);
    }
    
    public function getHomeElement($data = '')
    {
        if($data)
            return $this->home_element->$data;
        return $this->home_element;    
    }
    
    public function isPreview()
    {
        if($this->preview || $this->templatefile || $this->dversion_preview)
            return true;
        return false;
    }
    
    public function getPreview()
    {
        return intval($this->preview);
    }   
    
    public function getPreviewTemplatefile()
    {
        if(!$this->isPreview())
            return '';
        return $this->templatefile;
    }
    
    public function isBackendPreview()
    {
        if($this->preview && !$this->templatefile)
            return true;
        return false;    
    }
    
    public function isDocumentPreview()
    {
        if($this->dversion_preview)
            return true;
        return false;    
    }
    
    public function getDocumentPreview()
    {
        return intval($this->dversion_preview);    
    }
    
    public function getPreviewArea()
    {
        return $this->area;    
    }
    
    public function getErrorDocuments($data = -1)
    {
        if($data > -1)
        {
            if(!is_array($this->element_errors))
                return 0;
            return intval($this->element_errors[$data]);  
        }
        else
        {
            if(!is_array($this->element_errors))
                return array();
            return $this->element_errors;  
        }
    }
    
    public function getFormErrors($data = '')
    {
        if($data)
        {
            if(!is_array($this->form_error))
                return '';
            return $this->form_error[$data];  
        }
        else
        {
            if(!is_array($this->form_error))
                return array();
            return $this->form_error;  
        }
    }
    
    public function deleteFormError($data)
    {
        if(!is_array($this->form_error))
            return false;
        
        unset($this->form_error[$data]);
        return true;
    }
    
    public function getCommentErrors($data = '')
    {
        if($data)
        {
            if(!is_array($this->comment_error))
                return '';
            return $this->comment_error[$data];  
        }
        else
        {
            if(!is_array($this->comment_error))
                return array();
            return $this->comment_error;  
        }
    }
    
    public function deleteCommentError($data)
    {
        if(!is_array($this->comment_error))
            return false;
        
        unset($this->comment_error[$data]);
        return true;
    }
    
    public function getMarkedWord()
    {
        return $this->mark_word;
    }
    
    
    public function getDomain($ending = '/')
    {
        return self::$api->getDomain($ending);
    }
    
    public function writeDomain($ending = '/')
    {
        echo $this->getDomain($ending);
    }
    
    public function getTemplateDir($ending = '/', $withDomain = true)
    {
        return self::$api->getTemplateDir($ending, $withDomain);
    }
    
    public function writeTemplateDir($ending = '/', $withDomain = true)
    {
        echo $this->getTemplateDir($ending = '/', $withDomain = true);
    }
    
    public function getTemplateFile($ignoreMobile = false)
    {
        if($this->getError())
        {
            $ep = self::$base->db_to_array(self::$base->getOpt('error_pages'));
            
            if(self::isMobile() && !$ignoreMobile && $ep['template_mobile'])
                return $ep['template_mobile'];
                
            if($ep['template'])
                return $ep['template'];
        }   
        
        if($this->element->m_templatedatei && self::isMobile() && count(self::$base->getActiveTemplateConfig('mobile')) && !$ignoreMobile)
            return (!$this->dclass?$this->element->m_templatedatei:$this->dclass_element->m_templatedatei);
        
        return (!$this->dclass?$this->element->templatedatei:$this->dclass_element->templatedatei);
    }
    
    public function writeTemplateFile($ignoreMobile = false)
    {
        echo $this->getTemplateFile($ignoreMobile);
    }
    
    public function getTemplateConfig()
    {
        return self::$api->getTemplateConfig();
    }
    
    public function writeTemplateConfig()
    {
        echo $this->getTemplateConfig();
    }
    
    public function getTemplate()
    {        
        $dir = $this->getTemplateDir('/', false);
        $load = $dir.$this->getTemplateFile(); 
        
        if(!file_exists($load) && self::isMobile())
            $load = $dir.$this->getTemplateFile(true);
            
        if(!file_exists($load) || $this->getTemplateFile() == '')
            $load = $dir.'index.php';
            
        if($this->getSearchString())
        {
            if(!self::$api->isMobile())
            {
                $load = $dir.self::$base->getOpt()->q_template;
                if(!file_exists($load) || !self::$base->getOpt()->q_template)
                    $load = $dir.'index.php';
            }
            else
            {
                $load = $dir.self::$base->getOpt()->q_template_mobile;  
                if(!file_exists($load) || !self::$base->getOpt()->q_template_mobile)
                    $load = $dir.'index.php';
            }
        }
        
        if(!file_exists($load))
        {
            $error_title = self::$trans->__('Kein gültiges Template gefunden');
            $error_msg = self::$trans->__('Die Webseite konnte nicht korrekt ausgegeben werden. Es wurde im entsprechenden Verzeichnis kein gültiges Template gefunden oder die gewählte Templatedatei ist beschädigt.');
            
            self::$base->printFrontendError($error_msg, $error_title);
        }
        
        return self::$api->execute_filter('get_template', $load);
    }
    
    public function writeTemplate()
    {
        echo $this->getTemplate();
    }
    
    public function includeTemplateFile($file = 'index.php')
    {
        $ifile = $this->getTemplateDir('/', false).$file;
        if(file_exists($ifile))
            return $ifile;
        return false;
    }
    
    public function getTemplateLanguageFile()
    {
        $load = $this->getTemplateDir('/', false).'language.php';
        if(!file_exists($load))
            $load = '';
        return $load;
    }
    
    public function writeTemplateLanguageFile()
    {
        echo $this->getTemplateLanguageFile();
    }
    
    public function getLanguage($noFilter = false, $ending = '')
    { 
        if(!$noFilter && self::$trans->getStandardLanguage() == $this->language)
            return self::$api->execute_filter('get_language', '');
        return self::$api->execute_filter('get_language', $this->language.$ending);
    }
    
    public function writeLanguage($noFilter = false, $ending = '')
    {
        echo $this->getLanguage($noFilter, $ending);
    }

    public function getLanguageCode()
    {
        return self::$api->execute_filter('get_language_code', $this->language);
    }

    public function getLanguageLink($language = '', $to_root = false, $not_when_first = true, $page = -1)
    { 
        if(!$language)
            $language = $this->language;
        
        if(self::$trans->getStandardLanguage() == $language)
            $way = $this->getDomain();
        else
            $way = $this->getDomain().$language.'/';
            
        if($this->isCustomPage() && !$to_root)
            return self::$api->execute_filter('get_language_link', $way.'c/'.$this->getCustomPage('slug').'/'.($this->getCustomPage('original_var')?$this->getCustomPage('original_var').'/':''));
            
        if($this->home_element->id == $this->element->id && $not_when_first && !$this->dclass && !$this->getPage())
            $to_root = true; 
        
        if($to_root)
            return self::$api->execute_filter('get_language_link', $way);
            
        $check = self::$fksdb->query("SELECT id, sprachen FROM ".SQLPRE."elements WHERE id = '".$this->element->id."' AND papierkorb = '0' AND sprachen LIKE '%\"".$language."\"%' LIMIT 1");
        if(self::$fksdb->count($check))
        {
            $spele = self::$fksdb->fetch($check);
            $sptitel = self::$base->fixedUnserialize($spele->sprachen);
            
            if($page == -1)
                $page = $this->paging;
            
            if($this->dclass)
            {
                $sptitel = $this->element_languages;
                
                return self::$api->execute_filter('get_language_link', $way.$this->element->id.'/'.$this->dclass.'/'.self::$base->auto_slug($sptitel[$language]).'/'.($page?$page.'/':''));
            }
            else
            {
                return self::$api->execute_filter('get_language_link', $way.$this->element->id.'/'.self::$base->auto_slug($sptitel[$language]).'/'.($page?$page.'/':''));
            }
        } 
        
        return self::$api->execute_filter('get_language_link', $way);
    }
    
    public function writeLanguageLink($language = '', $to_root = false, $not_when_first = true)
    {
        echo $this->getLanguageLink($language, $to_root, $not_when_first);
    }
    
    public function getRoot()
    {
        return self::$api->execute_filter('get_root', $this->getLanguageLink($language = '', true));
    }
    
    public function writeRoot()
    {
        echo $this->getRoot();
    }
    
    public function getURL()
    {
        return self::$api->execute_filter('get_url', $this->getLanguageLink());
    }
    
    public function writeURL()
    {
        echo $this->getURL();
    }
    
    public function getPage()
    {
        return self::$api->execute_filter('get_page', $this->paging);
    }
    
    public function writePage()
    {
        echo $this->getPage();
    }
    
    public function getPageURL($page)
    {
        return self::$api->execute_filter('get_page_url', $this->getLanguageLink('', false, false, $page));
    }
    
    public function writePageURL($page)
    {
        echo $this->getPageURL($page);
    }
    
    public function getPageBeforeURL()
    {
        $page = ($this->paging > 0?($this->paging - 1):0);
        return self::$api->execute_filter('get_page_before_url', $this->getLanguageLink('', false, false, $page));
    }
    
    public function writePageBeforeURL()
    {
        echo $this->getPageBeforeURL();
    }
    
    public function getPageAfterURL()
    {
        $page = $this->paging + 1;
        return self::$api->execute_filter('get_page_after_url', $this->getLanguageLink('', false, false, $page));
    }
    
    public function writePageAfterURL()
    {
        echo $this->getPageAfterURL();
    }
    
    public function getTitle()
    {
        if($this->getCustomValue('title'))
            return self::$api->execute_filter('get_title', $this->getCustomValue('title'));
        
        $spr = $this->element_languages;
        
        $titel = $spr[$this->language]['titel']; 
        if(!$titel) $titel = $this->element->titel;
        
        if($this->isSearch())
            $titel = '&quot;'.$this->getSearchString().'&quot;';
        if($this->getError())
            $titel = 'error: '.$this->getError();
            
        return self::$api->execute_filter('get_title', $titel);   
    }
    
    public function writeTitle()
    {
        echo $this->getTitle();    
    }
    
    public function setTitle($value)
    {
        $this->setCustomValue('title', $value);
    }
    
    public function getMetaTitle()
    {
        $element = $this->element;
        $spr = $this->element_languages;
        
        $titel = $spr[$this->language]['htitel'];
        if(!$titel) $titel = self::$base->auto_title($spr[$this->language]['titel'], $this->language);
        if(!$titel) $titel = $spr[$this->language]['titel']; 
        if(!$titel) $titel = $element->titel; 
        
        if($this->isSearch())
            $titel = self::$base->auto_title('&quot;'.$this->getSearchString().'&quot;', $this->language);
        if($this->getError())
            $titel = self::$base->auto_title($this->getError(), $this->language);
            
        if($this->getCustomValue('metatitle'))
        {
            if(!$this->getCustomValue('metatitle_append'))
                return self::$api->execute_filter('get_meta_title', $this->getCustomValue('metatitle'));
            return self::$api->execute_filter('get_meta_title', self::$base->auto_title($this->getCustomValue('metatitle'), $this->language));
        }
            
        return self::$api->execute_filter('get_meta_title', $titel);   
    }
    
    public function writeMetaTitle()
    {
        echo $this->getMetaTitle();    
    }
    
    public function setMetaTitle($value, $append = false)
    {
    	$this->setCustomValue('metatitle', $value);
    	$this->setCustomValue('metatitle_append', $append);
    }
    
    public function getMetaDescription()
    {   
        if($this->getCustomValue('metadescription'))
            return self::$api->execute_filter('get_meta_description', $this->getCustomValue('metadescription'));
    
        $spr = $this->element_languages;
        
        return self::$api->execute_filter('get_meta_description', $spr[$this->language]['desc']);
    }
    
    public function writeMetaDescription()
    {
        echo $this->getMetaDescription();    
    }
    
    public function setMetaDescription($value)
    {
    	$this->setCustomValue('metadescription', $value);
    }
    
    public function getMetaKeywords()
    {   
        if($this->getCustomValue('metakeywords'))
            return self::$api->execute_filter('get_meta_keywords', $this->getCustomValue('metakeywords'));
    
        $spr = $this->element_languages;
        
        return self::$api->execute_filter('get_meta_keywords', $spr[$this->language]['tags']);
    }
    
    public function writeMetaKeywords()
    {
        echo $this->getMetaKeywords();    
    }
    
    public function setMetaKeywords($value)
    {
    	$this->setCustomValue('metakeywords', $value);
    }
        
    public function getAutor()
    {
        $element = $this->element;
        
        $docs = array();
        $sdQ = self::$fksdb->query("SELECT dokument FROM ".SQLPRE."document_relations WHERE element = '".$element->id."' ORDER BY sort"); 
        while($sd = self::$fksdb->fetch($sdQ))
        {
            $doc = self::$fksdb->fetch("SELECT von_edit FROM ".SQLPRE."documents WHERE id = '".$sd->dokument."' AND gesperrt = '0' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) LIMIT 1");
            
            if(!in_array($doc->von_edit, $docs))
                $autoren = ', '.self::$base->user($doc->von_edit, ' ', 'vorname', 'nachname');
            $docs[] = $doc->von_edit;
        }
        
        $autoren = trim(substr($autoren, 2));
        return self::$api->execute_filter('get_author', $autoren);   
    }
    public function getAuthor() { return $this->getAutor(); }
    
    public function writeAutor()
    {
        echo $this->getAutor();    
    }
    public function writeAuthor() { $this->writeAutor(); }
        
    private function getNoRobotsIntern()
    {
        if(self::$base->getOpt('noseo') || $this->isSearch() || $this->preview || $this->getError())
            return true;
            
        if($this->dclass)
            return $this->dclass_element->noseo;
            
        if(!$this->element)
            return false;
            
        return ($this->element->noseo?true:false);
    }
    
    public function getNoRobots()
    {
        return self::$api->execute_filter('get_no_robots', $this->getNoRobotsIntern());
    }
    
    public function setHTML5($html5 = true)
    {
        $this->is_html5 = $html5;
    }
    
    public function isHTML5()
    {
        return self::$api->execute_filter('is_html5', $this->is_html5);
    }
    
    public function isSearch()
    {
        return self::$api->execute_filter('is_search', ($this->getSearchString()?true:false));
    }
    
    public function getSearchString()
    {
        return self::$api->execute_filter('get_search_string', $this->search);
    }
    
    public function writeSearchString()
    {
        echo $this->getSearchString();    
    }
    
    public function getSearchURL($query = '')
    {
        return self::$api->execute_filter('get_search_url', $this->getRoot().'q/'.($query?urlencode($query).'/':''));
    }
    
    public function writeSearchURL($query = '')
    {
        echo $this->getSearchURL($query);    
    }
    
    public function getSearchForm($p = array())
    {
        /// PARAMETER STANDARDWERTE ///
        $standard = array(
            'class' => '',
            'label_text' => '',
            'label_position' => 'before',
            'label_br' => false,
            'html_tag' => 'p',
            'submit_text' => 'Suchen',
            'submit_class' => 'fks_submit',
            'input_id' => '',
            'input_class' => 'fks_search',
            'input_placeholder' => '',
            'input_no_html5' => false
        );
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////
        
        $form = '
        <form method="post" action="'.$this->getURL().'" class="fks_search_form">
        '.($p['html_tag']?'<'.$p['html_tag'].''.($p['class']?' class="'.$p['class'].'"':'').'>':'').'
            '.($p['label_text'] && $p['label_position'] == 'before'?'<label for="'.$p['input_id'].'">'.$p['label_text'].'</label>'.($p['label_br']?'<br />':''):'').'
            <input type="'.(self::isHTML5() && !$p['input_no_html5']?'search':'text').'"'.(self::isHTML5()?' required':'').' name="fks_search"'.($p['input_placeholder']?' placeholder="'.$p['input_placeholder'].'"':'').''.($p['input_id']?' id="'.$p['input_id'].'"':'').''.($p['input_class']?' class="'.$p['input_class'].'"':'').' value="'.$this->getSearchString().'" />
            '.($p['label_text'] && $p['label_position'] == 'after'?($p['label_br']?'<br />':'').'<label for="'.$p['input_id'].'">'.$p['label_text'].'</label>':'').'
            <input type="submit" name="fks_search_submit"'.($p['submit_class']?' class="'.$p['submit_class'].'"':'').' value="'.$p['submit_text'].'" />
        '.($p['html_tag']?'</'.$p['html_tag'].'>':'').'
        </form>';    
        
        return self::$api->execute_filter('get_search_form', $form, $p);
    }
    
    public function writeSearchForm($p = array())
    {
        echo $this->getSearchForm($p);    
    }
    
    public function getLoginForm($p = array())
    {
        /// PARAMETER STANDARDWERTE ///
        $standard = array(
            'id' => '',
            'class' => 'fks_login',
            'html_tag' => 'div',
            'container_html_tag' => 'p',
            'container_class' => '',
            'container_name_class' => '',
            'container_password_class' => '',
            'container_submit_class' => '',
            'container_submit_logout_class' => '',
            'label_name_text' => 'Name:',
            'label_password_text' => 'Passwort:',
            'label_name_position' => 'before',
            'label_password_position' => 'before',
            'label_name_br' => false,
            'label_password_br' => false,
            'label_name_class' => '',
            'label_password_class' => '',
            'input_name_class' => 'fks_login_name',
            'input_password_class' => 'fks_login_password',
            'submit_text' => 'Anmelden',
            'submit_class' => 'fks_submit',
            'submit_logout_text' => 'Abmelden',
            'submit_logout_class' => 'fks_submit fks_submit_logout',
            'error_class' => 'fks_login_error',
            'success_forwarding' => 0,
            'success_class' => 'fks_login_success',
            'success_text' => '',
            'success_logout_text' => '',
            'success_logout_forwarding' => 0
        );
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////
        
        
        if(count($this->login_error))
        {
            foreach($this->login_error as $li)
                $form_error_li = '<li>'.$li.'</li>';
            $form_error = '<ul'.($p['error_class']?' class="'.$p['error_class'].'"':'').'>'.$form_error_li.'</ul>';   
            
            $tmp_name = self::$fksdb->save($_POST['fks_login_name']);
        }
        
        if($_GET['login_ok'] && $p['success_text'])
        {
            $form_ok = '<div'.($p['success_class']?' class="'.$p['success_class'].'"':'').'>'.nl2br($p['success_text']).'</div>';
        }
        if($_GET['logout_ok'] && $p['success_logout_text'])
        {
            $form_ok = '<div'.($p['success_class']?' class="'.$p['success_class'].'"':'').'>'.nl2br($p['success_logout_text']).'</div>';
        }
        
        $form = '
        <form method="post" action="'.$this->getURL().'#fks_login_form" class="fks_login_form" id="fks_login_form">
            '.$form_error.'
            '.$form_ok.'

            '.self::$api->getHashInput().'
            
            '.($p['html_tag']?'<'.$p['html_tag'].''.($p['class']?' class="'.$p['class'].'"':'').''.($p['id']?' id="'.$p['id'].'"':'').'>':'').'
            
                '.(!self::isLogin()?'
                    '.($p['success_forwarding']?'<input type="hidden" name="success_forwarding" value="'.intval($p['success_forwarding']).'" />':'').'
                
                    '.($p['container_html_tag']?'<'.$p['container_html_tag'].' class="'.trim($p['container_class'].' '.$p['container_name_class']).'">':'').'
                        '.($p['label_name_text'] && $p['label_name_position'] == 'before'?'<label for="fks_login_name">'.$p['label_name_text'].'</label>'.($p['label_name_br']?'<br />':''):'').'
                        <input type="text" required="required" name="fks_login_name" id="fks_login_name"'.($p['input_name_class']?' class="'.$p['input_name_class'].'"':'').' value="'.$tmp_name.'" />
                        '.($p['label_name_text'] && $p['label_name_position'] == 'after'?($p['label_name_br']?'<br />':'').'<label for="fks_login_name">'.$p['label_name_text'].'</label>':'').'
                    '.($p['container_html_tag']?'</'.$p['container_html_tag'].'>':'').'
                    
                    '.($p['container_html_tag']?'<'.$p['container_html_tag'].' class="'.trim($p['container_class'].' '.$p['container_password_class']).'">':'').'
                        '.($p['label_password_text'] && $p['label_password_position'] == 'before'?'<label for="fks_login_password">'.$p['label_password_text'].'</label>'.($p['label_password_br']?'<br />':''):'').'
                        <input type="password" required="required" name="fks_login_password" id="fks_login_password"'.($p['input_password_class']?' class="'.$p['input_password_class'].'"':'').' value="" />
                        '.($p['label_password_text'] && $p['label_password_position'] == 'after'?($p['label_password_br']?'<br />':'').'<label for="fks_login_password">'.$p['label_password_text'].'</label>':'').'
                    '.($p['container_html_tag']?'</'.$p['container_html_tag'].'>':'').'
                        
                    '.($p['container_html_tag']?'<'.$p['container_html_tag'].' class="'.trim($p['container_class'].' '.$p['container_submit_class']).'">':'').'
                        <input type="submit" name="fks_login_submit"'.($p['submit_class']?' class="'.$p['submit_class'].'"':'').' value="'.$p['submit_text'].'" />
                    '.($p['container_html_tag']?'</'.$p['container_html_tag'].'>':'').'
                    
                ':'
                    '.($p['success_logout_forwarding']?'<input type="hidden" name="success_logout_forwarding" value="'.intval($p['success_logout_forwarding']).'" />':'').'
                    
                    '.($p['container_html_tag']?'<'.$p['container_html_tag'].' class="'.trim($p['container_class'].' '.$p['container_submit_logout_class']).'">':'').'
                        <input type="submit" name="fks_logout_submit"'.($p['submit_logout_class']?' class="'.$p['submit_logout_class'].'"':'').' value="'.$p['submit_logout_text'].'" />
                    '.($p['container_html_tag']?'</'.$p['container_html_tag'].'>':'').'
                ').'
                
                '.($p['html_tag']?'</'.$p['html_tag'].'>':'').'            
        </form>';    
        
        return self::$api->execute_filter('get_login_form', $form, $p);
    }
    
    public function writeLoginForm($p = array())
    {
        echo $this->getLoginForm($p);    
    }

    public function getFeeds()
    {
        $dsql = "";
        $pid = (!$this->dclass?$this->id:$this->dclass_element->id);
        $sdQ = self::$fksdb->query("SELECT dokument FROM ".SQLPRE."document_relations WHERE element = '".$pid."' AND klasse = ''");
        while($se = self::$fksdb->fetch($sdQ))
            $dsql .= " OR dokument = '".$se->dokument."' ";

        $rss = array();
        $feeds = self::$fksdb->query("SELECT block, dokument, id, titel FROM ".SQLPRE."feeds WHERE home = '1' OR element = '".$pid."' OR element = '".$this->element->element."' ".$dsql);
        while($feed = self::$fksdb->fetch($feeds))
        {
            $dv = self::$fksdb->fetch("SELECT id FROM ".SQLPRE."document_versions WHERE dokument = '".$feed->dokument."' AND language = '".$this->getLanguage(true)."' AND aktiv = '1' ORDER BY id DESC LIMIT 1");
            $block_check = self::$fksdb->count(self::$fksdb->query("SELECT id FROM ".SQLPRE."blocks WHERE vid = '".$feed->block."' AND dokument = '".$feed->dokument."' AND dversion = '".$dv->id."' LIMIT 1"));

            if(!$block_check)
                continue;

            $rss[] = array(
                'id' => $feed->id,
                'title' => trim($feed->titel),
                'url' => $this->getDomain().'feed/'.$feed->id.'/'.self::$base->slug($feed->titel).'/',
                'document' => $feed->dokument
            );
        }

        return $rss;
    }

    public function getFeedLinks()
    {
        $feeds = $this->getFeeds();
        if(!count($feeds))
            return '';

        $rtn = '';
        foreach($feeds as $feed)
            $rtn .= '<link rel="alternate" type="application/rss+xml" title="'.$feed['title'].'" href="'.$feed['url'].'" />'."\n";
        return $rtn;
    }

    public function writeFeedLinks()
    {
        echo $this->getFeedLinks();
    }
    
    public function getHeader($p = array())
    {
        /// STANDARDS ///
        $standard = array(
            'jquery' => true,
            'jquery_ui' => false
        );
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////

        if($p['html5'])
            self::setHTML5(true);

        $p['fks'] = $this;
        $p['title'] = $this->getTitle();
        $p['meta_title'] = $this->getMetaTitle();
        $p['meta_description'] = $this->getMetaDescription();
        $p['meta_keywords'] = $this->getMetaKeywords();
        $p['author'] = $this->getAutor();
        $p['noindex'] = ($this->getNoRobots()?true:false);
        $p['url'] = $this->getURL();
        $p['language'] = $this->getLanguage(true);

        $header = ''.self::$api->execute_hook('before_header', $p, true);


        if($p['jquery'])
            self::$api->addJsFile(DOMAIN.'/inc/libraries/js/jquery.min.js?v='.FKS_VERSION, 'jquery', 5);

        if($p['jquery_ui'])
        {
            self::$api->addJsFile(DOMAIN.'/inc/libraries/js/jquery-ui.custom.min.js?v='.FKS_VERSION, 'jquery-ui', 7);
            self::$api->addCssFile(DOMAIN.'/inc/libraries/css/jquery-ui.css?v='.FKS_VERSION, 'jquery-ui', 7);
        }

        if(self::$user->isGhost())
        {
            $header .= '
            <script>
                var ghost_ordner = "'.$this->getDomain().'";
                var ghost_url = "'.$this->getURL().'";
                var ghost_border = "'.(self::$base->getActiveTemplateConfig('ghost', 'border')?self::$base->getActiveTemplateConfig('ghost', 'border'):'1px dotted #555;').'";
            </script>';

            if(!$p['jquery'])
                self::$api->addJsFile(DOMAIN.'/inc/libraries/js/jquery.min.js?v='.FKS_VERSION, 'jquery', 5);
            self::$api->addJsFile(DOMAIN.'/fokus/ckeditor/ckeditor.js?v='.FKS_VERSION, 'ckeditor', 10);
            self::$api->addJsFile(DOMAIN.'/fokus/ckeditor/adapters/jquery.js?v='.FKS_VERSION, 'ckeditor-jquery', 10);

            self::$api->addJsFile(DOMAIN.'/inc/frontend/ghost/ghost.js?v='.FKS_VERSION, 'fks-ghost', 15);
            self::$api->addCssFile(DOMAIN.'/inc/frontend/ghost/ghost.css?v='.FKS_VERSION, 'fks-ghost', 15);
        }

        if(self::$user->isForesight())
        {
            $header .= '
            <script>
                var foresight_ordner = "'.$this->getDomain().'";
                var foresight_url = "'.$this->getURL().'";
            </script>';

            if(!$p['jquery'])
                self::$api->addJsFile(DOMAIN.'/inc/libraries/js/jquery.min.js?v='.FKS_VERSION, 'jquery', 5);
            if(!$p['jquery_ui'])
            {
                self::$api->addJsFile(DOMAIN.'/inc/libraries/js/jquery-ui.custom.min.js?v='.FKS_VERSION, 'jquery-ui', 7);
                self::$api->addCssFile(DOMAIN.'/inc/libraries/css/jquery-ui.css?v='.FKS_VERSION, 'jquery-ui', 7);
            }

            self::$api->addJsFile(DOMAIN.'/inc/frontend/foresight/foresight.js?v='.FKS_VERSION, 'fks-foresight', 15);
            self::$api->addCssFile(DOMAIN.'/inc/frontend/foresight/foresight.css?v='.FKS_VERSION, 'fks-foresight', 15);
        }

        
        $header .= '
        '.(!$this->is_html5?'<meta http-equiv="content-type" content="text/html; charset=utf-8" />':'<meta charset="utf-8"/>').'

        <title>'.$this->getMetaTitle().'</title>

        '.($this->getMetaDescription()?'<meta name="description" content="'.$this->getMetaDescription().'" />':'').'
        '.($this->getMetaKeywords()?'<meta name="keywords" content="'.$this->getMetaKeywords().'" />':'').'

        <meta name="robots" content="'.($this->getNoRobots()?'no':'').'index,'.(self::$base->getOpt('noseo')?'no':'').'follow" />

        '.(!$this->is_html5?'<meta http-equiv="content-language" content="'.$this->getLanguage(true).'" />':'').'

        <link rel="canonical" href="'.$this->getURL().'" />
        '.$this->getFeedLinks().'

        '.($this->is_html5?'
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
        </script>':'').'
        ';

        if(self::$base->getOpt('merge_css') && count(self::$api->getCssFiles()) && !self::$api->isGhost() && !self::$api->isForesight())
        {
            $header .= '<link href="'.self::$api->getCssFilesMergedUrl().'" rel="stylesheet" type="text/css" />'."\n";
        }
        else
        {
            $css_files = self::$api->getCssFiles();
            foreach($css_files as $file)
                $header .= '<link href="'.$file['file'].'" rel="stylesheet" type="text/css"'.($file['media']?' media="'.$file['media'].'"':'').' />'."\n";
        }

        if(self::$base->getOpt('merge_js') && count(self::$api->getJsFiles('header')) && !self::$api->isGhost() && !self::$api->isForesight())
        {
            $header .= '<script src="'.self::$api->getJsFilesMergedUrl('header').'"></script>'."\n";
        }
        else
        {
            $js_files = self::$api->getJsFiles('header');
            foreach($js_files as $file)
                $header .= '<script src="'.$file['file'].'"'.($file['defer']?' defer':'').'></script>'."\n";
        }
    
        $header = self::$api->execute_filter('get_header', $header, $p);
        $header = self::$api->execute_filter('after_header', $header, $p);
        $header .= self::$api->execute_hook('after_header', $p, true);
            
        return $header;   
    }
    
    public function writeHeader($p = array())
    {
        echo $this->getHeader($p);    
    }

    public function getFooter($p = array())
    {
        /// STANDARDS ///
        $standard = array(
            'jquery' => false,
            'jquery_ui' => false
        );
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////

        $footer = self::$api->execute_hook('before_footer', self::$static);


        if($p['jquery'])
            self::$api->addJsFile(DOMAIN.'/inc/libraries/js/jquery.min.js?v='.FKS_VERSION, 'jquery', 5, false);
        if($p['jquery_ui'])
            self::$api->addJsFile(DOMAIN.'/inc/libraries/js/jquery-ui.custom.min.js?v='.FKS_VERSION, 'jquery-ui', 7, false);

        if(self::$base->getOpt('merge_js') && count(self::$api->getJsFiles('footer')) && !self::$api->isGhost() && !self::$api->isForesight())
        {
            $footer .= '<script src="'.self::$api->getJsFilesMergedUrl('footer').'"></script>'."\n";
        }
        else
        {
            $js_files = self::$api->getJsFiles('footer');
            foreach($js_files as $file)
                $footer .= '<script src="'.$file['file'].'"'.($file['defer']?' defer':'').'></script>'."\n";
        }

        $footer = self::$api->execute_filter('get_footer', $footer, self::$static);
        $footer = self::$api->execute_filter('after_footer', $footer, self::$static);
        $footer .= self::$api->execute_hook('get_footer', self::$static, true);
        $footer .= self::$api->execute_hook('after_footer', self::$static, true);

        return $footer;
    }

    public function writeFooter($p = array())
    {
        echo $this->getFooter($p);
    }
    
    public function isGhost()
    {
        return self::$api->isGhost();
    }

    public function getGhostUrl()
    {
        $sid = $this->getElementID();
        $did = $this->getDclassDocumentID();

        return DOMAIN.'/fokus/sub_ghost.php?index=go'.($sid?'&sid='.$sid:'').($did?'&did='.$did:'');
    }

    public function writeGhostUrl()
    {
        echo $this->getGhostUrl();
    }

    public function getGhostLink($linktext = 'fokus Ghost')
    {
        if(!self::$user->isAdmin())
            return '';

        if(!self::$suite->rm(4) || !self::$user->r('fks', 'ghost'))
            return '';

        if(self::$api->isGhost())
            return '';

        return '<a href="'.$this->getGhostUrl().'" class="fks_ghost_link" rel="nofollow">'.$linktext.'</a>';
    }

    public function writeGhostLink($linktext = 'fokus Ghost')
    {
        echo $this->getGhostLink($linktext);
    }
    
    public function isMobile()
    {
        return self::$api->isMobile();
    }
    
    public function getTemplateTexts()
    {
        if(!is_array($this->template_texts))
            return array();
            
        return $this->template_texts;
    }
    
    public function getText($id, $use_id_if_not_avaible = true)
    {
        $text = $this->getTemplateTexts();
        
        $t = $text[$id][$this->language];
        
        if(!$t)
            $t = $text[$id][self::$trans->getStandardLanguage()];
            
        if(!$t && $use_id_if_not_avaible)
            $t = $id;
        
        return self::$api->execute_filter('get_text', $t, array(
            'id' => $id,
            'text' => $text
        ));
    }
    
    public function writeText($id, $use_id_if_not_avaible = true)
    {
        echo $this->getText($id, $use_id_if_not_avaible);
    }
    
    public function getCustomField($name)
    {
        return self::$api->execute_filter('get_custom_field', $this->getCustomFieldIntern($name), array(
            'name' => $name
        ));    
    }
    
    private function getCustomFieldIntern($name)
    {
        $cfields = self::$api->getCustomFields();
        
		if(!is_array($cfields[$name]))
			return '';
		
		if($cfields[$name]['global'])
		{
            if(!$this->dclass)
                $cf = self::$base->fixedUnserialize($this->element->cf);
            else
                $cf = self::$base->fixedUnserialize($this->dclass_document->cf);
			$wert = $cf[$name];
		}
		else
		{
			$wert = $this->element_languages[$this->language][$name];
		}
        
        if($cfields[$name]['type'] == 'checkbox')
            return ($wert == 'fks_true'?true:false);
        
        $t = htmlspecialchars_decode($wert);        
        return $t;
    }
    
    public function writeCustomField($name)
    {
        echo $this->getCustomField($name);
    }
    
    public function getGlobalCustomField($name, $langugage = '')
    {
        if(!$langugage)
            $langugage = $this->language;
        
        return self::$api->getGlobalCustomField($name, $langugage);
    }
    
    public function writeGlobalCustomField($name, $langugage = '')
    {
        echo $this->getGlobalCustomField($name, $langugage);
    }
    
    public function getBreadcrumb($p = array())
    {
        /// PARAMETER STANDARDWERTE ///
        $standard = array(
            'id' => '',
            'class' => 'fks_breadcrumb',
            'view' => 'list',
            'home_text' => 'Home',
            'home_link' => $this->getRoot(),
            'last_class' => 'last',
            'first_class' => 'first',
            'microdata' => true
        );
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////
        
        /*
        'after_li' => '',
        'after_first_li' => '',
        'after_last_li' => '',
        'before_li' => '',
        'before_first_li' => '',
        'before_last_li' => '',
        'after_a' => '',
        'after_first_a' => '',
        'after_last_a' => '',
        'before_a' => '',
        'before_first_a' => '',
        'before_last_a' => ''
        */
        
        $home_link = ($p['home_link'] == '/'?$this->getDomain():$p['home_link']);
        $md = ($p['microdata'] && $p['view'] == 'list'?true:false);
        
        if($this->dclass)
        { 
            $d = clone $this->element; 
            if($d->klasse)
                $d = self::$fksdb->fetch("SELECT id, titel, sprachen, element, dklasse FROM ".SQLPRE."elements WHERE id = '".$d->element."' AND papierkorb = '0' LIMIT 1");
            $bc[] = $d; 
            
            $e = $this->element;
            if($e->klasse)
                $e = self::$fksdb->fetch("SELECT id, titel, sprachen, element, dklasse FROM ".SQLPRE."elements WHERE id = '".$e->element."' AND papierkorb = '0' LIMIT 1");
            unset($e->dklasse); 
            $bc[] = $e;   
        }
        else 
        {
            $e = $this->element; 
            unset($e->dklasse); 
            $bc[] = $e;   
        }
        
        while($e->element != 0)
        {
            $e = self::$fksdb->fetch("SELECT id, titel, sprachen, element FROM ".SQLPRE."elements WHERE id = '".$e->element."' AND papierkorb = '0' LIMIT 1");
            $bc[] = $e;    
        } 
        
        $rtn = ($p['view'] == 'list'?'<ul':'<div').''.($p['id']?' id="'.$p['id'].'"':'').''.($p['class']?' class="'.$p['class'].'"':'').''.($md?' itemscope itemtype="http://data-vocabulary.org/Breadcrumb"':'').'>';
        
        $ali = (isset($p['after_first_li'])?$p['after_first_li']:$p['after_li']);
        $bli = (isset($p['before_first_li'])?$p['before_first_li']:$p['before_li']);
        $aa = (isset($p['after_first_a'])?$p['after_first_a']:$p['after_a']);
        $ba = (isset($p['before_first_a'])?$p['before_first_a']:$p['before_a']);
        
        $add_class = ($p['first_class']?' class="'.$p['first_class'].'"':'');
        if($p['home_text'])
            $rtn .= ($p['view'] == 'list'?$bli.'<li'.$add_class.'>':'').$ba.'<a'.($p['view'] == 'flat'?$add_class:'').' href="'.$home_link.'"'.($md?' itemprop="url"':'').'>'.($md?'<span itemprop="title">':'').''.$p['home_text'].''.($md?'</span>':'').'</a>'.$aa.($p['view'] == 'list'?'</li>'.$ali:'');
        
        for($x = count($bc) - 1; $x >= 0; $x--)
        { 
            $add_class = ($x + 1 == count($bc) && !$p['home_text'] && $p['first_class']?' class="'.$p['first_class'].'"':'');
            $add_class = (!$x && $p['last_class']?' class="'.$p['last_class'].'"':$add_class);
            $spr = self::$base->fixedUnserialize($bc[$x]->sprachen);
            
            $ali = ($x + 1 == count($bc) && !$p['home_text'] && isset($p['after_first_li'])?$p['after_first_li']:(!$x && isset($p['after_last_li'])?$p['after_last_li']:$p['after_li']));
            $bli = ($x + 1 == count($bc) && !$p['home_text'] && isset($p['before_first_li'])?$p['before_first_li']:(!$x && isset($p['before_last_li'])?$p['before_last_li']:$p['before_li']));
            $aa = ($x + 1 == count($bc) && !$p['home_text'] && isset($p['after_first_a'])?$p['after_first_a']:(!$x && isset($p['after_last_a'])?$p['after_last_a']:$p['after_a']));
            $ba = ($x + 1 == count($bc) && !$p['home_text'] && isset($p['before_first_a'])?$p['before_first_a']:(!$x && isset($p['before_last_a'])?$p['before_last_a']:$p['before_a']));
            
            if($bc[$x]->dklasse)
            {
                $d = self::$fksdb->fetch("SELECT titel, id, sprachenfelder FROM ".SQLPRE."documents WHERE id = '".$this->dclass."' AND papierkorb = '0' LIMIT 1");
                $dspr = self::$base->fixedUnserialize($d->sprachenfelder);
                
                $rtn .= ($p['view'] == 'list'?$bli.'<li'.$add_class.''.($md?' itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"':'').'>':'').$ba.'<a'.($p['view'] == 'flat'?$add_class:'').' href="'.$this->getRoot().$bc[$x]->id.'/'.$d->id.'/'.self::$base->auto_slug($dspr[$this->language]).'/"'.($md?' itemprop="url"':'').'>'.($md?'<span itemprop="title">':'').''.$dspr[$this->language]['titel'].''.($md?'</span>':'').'</a>'.$aa.($p['view'] == 'list'?'</li>'.$ali:'');
            }
            else
            {
                if(!self::isSearch())
                {
                    $rtn .= ($p['view'] == 'list'?$bli.'<li'.$add_class.''.($md?' itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"':'').'>':'').$ba.'<a'.($p['view'] == 'flat'?$add_class:'').' href="'.$this->getRoot().$bc[$x]->id.'/'.self::$base->auto_slug($spr[$this->language]).'/"'.($md?' itemprop="url"':'').'>'.($md?'<span itemprop="title">':'').''.$spr[$this->language]['titel'].''.($md?'</span>':'').'</a>'.$aa.($p['view'] == 'list'?'</li>'.$ali:'');
                }
                else
                { 
                    $rtn .= ($p['view'] == 'list'?$bli.'<li'.$add_class.''.($md?' itemprop="child" itemscope itemtype="http://data-vocabulary.org/Breadcrumb"':'').'>':'').$ba.'<a'.($p['view'] == 'flat'?$add_class:'').' href="'.$this->getRoot().'q/'.urlencode($this->getSearchString()).'/"'.($md?' itemprop="url"':'').'>'.($md?'<span itemprop="title">':'').'&quot;'.$this->getSearchString().'&quot;'.($md?'</span>':'').'</a>'.$aa.($p['view'] == 'list'?'</li>'.$ali:'');
                }
            } 
            
            $titel = ($this->dclass?self::$base->auto_title(self::$fksdb->data("SELECT titel FROM ".SQLPRE."documents WHERE id = '".$this->dclass."' AND papierkorb = '0' LIMIT 1", "titel"), $this->language):$titel);
        }
        
        $rtn .= ($p['view'] == 'list'?'</ul>':'</div>');
        
        return self::$api->execute_filter('get_breadcrumb', $rtn, $p);
    }
    
    public function writeBreadcrumb($p = array())
    {
        echo $this->getBreadcrumb($p);    
    }
    
    public function getJumpBack($p = array())
    {
        /// PARAMETER STANDARDWERTE ///
        $standard = array(
            'id' => '',
            'class' => '',
            'element' => '',
            'link_text' => '',
            'no_wrapper' => false,
            'link' => 'all', // all, a
            'view' => 'flat' // flat, a
        );
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////
        
        /*
        'before' => ''
        'after' => ''
        */
        
        $rtn = '';
        
        if(!$p['element'])
        {
            if(!$this->dclass)
                $e = $this->element;
            else
                $e = $this->dclass_element;
        }
        else
            $e = self::$fksdb->fetch("SELECT id, element FROM ".SQLPRE."elements WHERE id = '".$p['element']."' AND papierkorb = '0' LIMIT 1");
        
        if($e->element == 0)
            return self::$api->execute_filter('get_jump_back', '', $p);
        
        $e = self::$fksdb->fetch("SELECT id, titel, sprachen, klasse FROM ".SQLPRE."elements WHERE id = '".$e->element."' AND papierkorb = '0' LIMIT 1");   
        $spr = self::$base->fixedUnserialize($e->sprachen);
        
        if(!$p['no_wrapper'])
            $rtn = ($p['view'] == 'flat'?'<div':'<span').''.($p['id']?' id="'.$p['id'].'"':'').($p['class']?' class="'.$p['class'].'"':'').'>';
        
        $link = '<a href="'.$this->getDomain().$e->id.'/'.self::$base->auto_slug($spr[$this->language]).'/"'.($p['no_wrapper']?($p['id']?' id="'.$p['id'].'"':'').($p['class']?' class="'.$p['class'].'"':''):'').'>';
        $a_text = (!$p['link_text']?$spr[$this->language]['titel']:($p['link_text']));
        
        $rtn .= 
        ($p['link'] == 'all'?$link:'')
            .($p['before']).
        ($p['link'] == 'a'?$link:'')
            .$a_text.
        ($p['link'] == 'a'?'</a>':'')
            .($p['after']).
        ($p['link'] == 'all'?'</a>':'');
           
        if(!$p['no_wrapper'])
            $rtn .= ($p['view'] == 'flat'?'</div>':'</span>');
        
        return self::$api->execute_filter('get_jump_back', $rtn, $p);
    }
    
    public function writeJumpBack($p = array())
    {
        echo $this->getJumpBack($p);    
    }
    
    public function isHome()
    {
        return self::$api->execute_filter('is_home', ($this->is_home?true:false));
    }
    
    public function getElementID()
    {
        return self::$api->execute_filter('get_element_id', $this->id);
    }
    
    public function writeElementID()
    {
        echo $this->getElementID();    
    }
    
    public function getParentElementID()
    {
        $parent = self::$fksdb->fetch("SELECT id FROM ".SQLPRE."elements WHERE id = '".$this->element->element."' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) LIMIT 1");
        return self::$api->execute_filter('get_parent_element_id', ($parent?$parent->id:0));
    }
    
    public function writeParentElementID()
    {
        echo $this->getParentElementID();    
    }
    
    public function getElementTree($field = 'id', $count = -1, $order = 'ASC')
    {        
        $search_field = array('structure', 'parent', 'free', 'title', 'file');
        $replace_field = array('struktur', 'element', 'frei', 'titel', 'templatedatei');
        $field = str_replace($search_field, $replace_field, strtolower($field));
        
        $new_elem = array();
        if($order == 'ASC')
        {
            for($x = count($this->elements_tree) - 1; $x >= 0; $x--)
                $new_elem[] = $this->elements_tree[$x]->$field;
        }
        else
        {
            for($x = 0; $x < count($this->elements_tree); $x++)
                $new_elem[] = $this->elements_tree[$x]->$field;
        }
        
        $new_elem = self::$api->execute_filter('get_element_tree', $new_elem, array(
            'field' => $field,
            'count' => $count,
            'order' => $order
        ));
            
        if($count >= 0)
            return $new_elem[$count];
        else
            return $new_elem;
    }
    
    public function writeElementTree($field = 'id', $element = -1, $order = 'ASC')
    {
        echo $this->getElementTree($field, $element, $order);    
    }
    
    public function getUserID()
    {
        return self::$api->getUserID();
    }
    
    public function writeUserID()
    {
        echo $this->getUserID();    
    }
    
    public function isLogin()
    {
        return self::$api->isLogin();
    }
    
    public function isAdmin()
    {
        return self::$api->isAdmin();
    }
    
    public function buildURL($element_id, $document_id = 0, $titles = array())
    {
        if(!count($titles))
        {
            if($document_id)
                $language_data = self::$fksdb->data("SELECT sprachenfelder FROM ".SQLPRE."documents WHERE id = '".$document_id."' LIMIT 1", "sprachenfelder");
            else
                $language_data = self::$fksdb->data("SELECT sprachen FROM ".SQLPRE."elements WHERE id = '".$element_id."' LIMIT 1", "sprachen");
                
            if($language_data)
                $titles = self::$base->fixedUnserialize($language_data);
        }
        
        return self::$api->execute_filter('build_url', $this->getRoot().$element_id.'/'.($document_id?$document_id.'/':'').self::$base->auto_slug($titles[$this->getLanguage(true)]).'/', array(
            'element_id' => $element_id,
            'document_id' => $document_id
        ));
    }
    
    public function getCategories()
    {
        return self::$api->execute_filter('get_categories', $this->getCategoriesIntern());
    }
    
    private function getCategoriesIntern()
    {
        $rtn = array();
        
        if(!$this->isDclass() || !$this->dclass_document)
            return array();
            
        if(!count($this->dclass_document->categories))
            return array();
            
        $avaible_cats = self::$api->getCategories(array(), false);
        if(!count($avaible_cats))
            return array();
        
        foreach($avaible_cats as $cat)
        {
            if(!in_array($cat['id'], $this->dclass_document->categories))
                continue;
        
            $rtn[$cat['id']] = $cat;
        }
        
        return $rtn;
    }
}
?>
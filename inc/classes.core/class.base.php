<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Base
{
    private static $fksdb, $trans;
    
    private $blocks = array(), $dclass_methods = array();
    
    private $timestamp = 0, $zerohour = 0;
    private $opt = null;
    private $structure_id = 0;
    private $active_template = '';
    
    private $active_languages = array();
    
    private $count_up_var = 1, $user_cache = array();
    
    private $block_alternatives = array(), $attr_alternatives = array();
    
    function __construct($p)
    {
        self::$fksdb = $p['fksdb'];
        self::$trans = $p['trans'];
        
        $this->setContentType();
        $this->setHeaderCaching();
        
        $this->initBlocks();
        $this->initDclassMethods();
        
        $this->initOpt();
        $this->initActiveTemplate();
        $this->initActiveLanguages();
        
        $this->setTime(time());
        $this->setStructureID();
        $this->initErrorHandling();
        
        $this->initAlternatives();
        
        $this->initGZIP();
        
        $this->update();
    }
    
    public function setContentType($type = '')
    {
        if(!$type)
        {
            if(defined('IS_FEED'))
                $type = "text/xml";
            elseif(defined('IS_SITEMAP'))
                $type = "text/xml";
            else
                $type = "text/html";
        }
        
        mb_internal_encoding("UTF-8");
        header("Content-Type: ".$type."; charset=utf-8");
    }
    
    public function setHeaderCaching($last_modified = 0, $expires = 0)
    {
        if(defined('IS_BACKEND') || defined('IS_INSTALLATION'))
        {
            header("Expires: Mon, 26 Jul 1990 05:00:00 GMT");
            header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");
            header("Cache-Control: no-store, no-cache, must-revalidate");
            header("Cache-Control: post-check=0, pre-check=0", false);
            header("Pragma: no-cache");
        }
        else
        {
            if($last_modified)
                header("Last-Modified: ".gmdate("D, d M Y H:i:s", $last_modified)." GMT");
            if($expires)
                header("Expires: ".gmdate("D, d M Y H:i:s", $expires)." GMT");
        }
    }
    
    public function initGZIP()
    {
        if(!extension_loaded('zlib'))
            return false;
            
        if(!$this->getOpt('gzip'))
            return false;
            
        if(!defined('IS_INDEX') && !defined('IS_SITEMAP') && !defined('IS_BACKEND'))
            return false;
            
        ob_start("ob_gzhandler");
        
        return true;
    }
    
    public function setTime($timestamp)
    {
        $timestamp = intval($timestamp);
        
        $this->timestamp = $timestamp;
        $this->zerohour = mktime(0, 0, 0, date('m', $timestamp), date('d', $timestamp), date('Y', $timestamp));
        
        return $timestamp;
    }
    
    public function getTime()
    {
        return intval($this->timestamp);
    }
    
    public function getZeroHour()
    {
        return $this->zerohour;
    }
    
    
    private function initActiveTemplate()
    {
        $this->active_template = $this->getOpt('template');
        
        if(!file_exists(ROOT.'content/templates/'.$this->active_template.'/config.php'))
        {
            $dir = ROOT.'content/templates';
            $handle = opendir($dir);
            while ($file = readdir ($handle)) 
            {
                if(!file_exists($dir.'/'.$file.'/config.php'))
                    continue;
                    
                $this->active_template = str_replace('/', '', $file);
                break;
            } 
            closedir($handle); 
        }
            
        define('TEMPLATE_DIR', ROOT.'content/templates/'.$this->active_template.'/', true);
    }
    
    public function getActiveTemplate()
    {
        return $this->active_template;
    }
    
    public function getActiveTemplateConfig($data = '', $dataB = '', $dataC = '', $dataD = '')
    {
        $config = $this->open_template_config(ROOT.'content/templates/'.$this->getActiveTemplate().'/config.php');
        
        if(is_array($config))
        {
            if(!$data)
            {
                return $config;
            }
            else
            {
                if($dataB)
                {
                    if($dataC)
                    {
                        if($dataD)
                        {
                            return $config[$data][$dataB][$dataC][$dataD];
                        }
                        
                        return $config[$data][$dataB][$dataC];
                    }
                    
                    return $config[$data][$dataB];
                }
                
                return $config[$data];
            }
        }
        
        return array();
    }
    
    
    public function initOpt()
    {
        if(!self::$fksdb)
            return false;
            
        $this->opt = self::$fksdb->fetch("SELECT * FROM ".SQLPRE."options WHERE id = '1' LIMIT 1");
        
        // workaround for old table names
        if(!$this->opt)
            $this->opt = self::$fksdb->fetch("SELECT * FROM ".SQLPRE."einstellungen WHERE id = '1' LIMIT 1");
            
        $this->opt->extensions = $this->db_to_array($this->opt->extensions);
    }
    
    public function getOpt($field = '')
    {
        $opt = $this->opt;
        
        if($field)
            return $opt->$field;
        else
            return $opt;
    }
    
    public function setOpt($field, $data, $a2db = false)
    {
        if($a2db)
            $data = $this->array_to_db($data);
            
        self::$fksdb->update("options", array($field => $data), array("id" => 1), 1);      
        $this->initOpt();
        
        return true;
    }
    
    public function getServerName()
    {
        return trim(str_replace(array('http://', 'https://', 'www.'), '', $_SERVER['SERVER_NAME']));
    }
    
    private function initActiveLanguages()
    {
        $languages = $this->fixedUnserialize($this->getOpt('sprachen'));
        
        if(!is_array($languages) || !count($languages)) 
            $languages = array(self::$trans->getStandardLanguage());
            
        krsort($languages);
        
        $this->active_languages = $languages;
    }
    
    public function getActiveLanguages()
    {
        if(!is_array($this->active_languages))
            return array(self::$trans->getStandardLanguage());
            
        return $this->active_languages;
    }
    
    public function getActiveLanguagesCount()
    {
        return intval(count($this->getActiveLanguages()));
    }
    
    
    private function setStructureID()
    {
        if(!self::$fksdb)
            return false;
            
        $this->structure_id = intval(self::$fksdb->data("SELECT id FROM ".SQLPRE."structures WHERE a".(IS_BACKEND?"2":"1")." = '1' AND papierkorb = '0' LIMIT 1", "id"));
        
        return ($this->structure_id == 0?false:true);    
    }
    
    public function getStructureID()
    {
        return intval($this->structure_id);
    }
    
    
    private function initErrorHandling()
    {
        if(defined('DEBUG') && DEBUG === true)
        {
            ini_set('display_errors', '1');
            error_reporting(E_ALL ^ E_NOTICE);
        }
        else
        {
            ini_set('display_errors', '0');
            error_reporting(0);
        }

        $newerrorhandler = set_error_handler(array($this, 'ErrorHandler'), E_ALL ^ E_NOTICE);
    }

    public function ErrorHandler($code, $text, $file, $line)
    {
        $error = "";
        switch ($code)
        {
            case E_USER_ERROR:
                $error .= "Schwerer Fehler: [".$code."] ".$text."\n";
                $error .= "Fataler Fehler in Zeile ".$line." in der Datei ".$file.", PHP " . PHP_VERSION . " (" . PHP_OS . ")";
                break;

            case E_USER_WARNING:
                $error .= "Warnung: [".$code."] ".$text;
                break;

            default:
                $error .= "Fehler: [".$code."] ".$text."\n";
                $error .= "Zeile ".$line." in Datei ".$file.", PHP " . PHP_VERSION . " (" . PHP_OS . ")";
                break;
        }

        if(!$error)
            return false;

        $this->txt_error($error);
        return true;
    }
    
    
    public function getFilePath($category)
    {
        $paths = array('bilder', 'screenshots', 'dokumente', 'multimedia'); 
        return $paths[$category];   
    }

    public function getFileTypeThumbnail($endung)
    {
        $filetype2image = array('mdb' => 'access', 'mdb' => 'access', 'mde' => 'access', 'pdf' => 'adobe', 'zip' => 'archiv', 'rar' => 'archiv', 'jpg' => 'bilddatei', 'jpeg' => 'bilddatei', 'png' => 'bilddatei', 'gif' => 'bilddatei', 'bmp' => 'bilddatei', 'xla' => 'excel', 'xlax' => 'excel', 'xlc' => 'excel', 'xlcx' => 'excel', 'xlk' => 'excel', 'xls' => 'excel', 'xlsm' => 'excel', 'xlsx' => 'excel', 'xlt' => 'excel', 'avi' => 'movieclip', 'wmv' => 'movieclip', 'wma' => 'movieclip', 'flv' => 'movieclip', '3gp' => 'movieclip', 'mov' => 'movieclip', 'mkv' => 'movieclip', 'mp4' => 'movieclip', 'mpg' => 'movieclip', 'ogg' => 'movieclip', 'mpp' => 'msproject', 'mpt' => 'msproject', 'mpx' => 'msproject', 'pps' => 'powerpoint', 'ppt' => 'powerpoint', 'ppsx' => 'powerpoint', 'pptx' => 'powerpoint', 'ppz' => 'powerpoint', 'pub' => 'publisher', 'doc' => 'word', 'docx' => 'word', 'docm' => 'word', 'dot' => 'word');
        
        $endung = strtolower($endung);
    
        if($filetype2image[$endung]) 
            return $filetype2image[$endung];
        else 
            return 'datei_universell';
    }
    
    public function getMemoryLimit()
    {
        return (int)(ini_get('memory_limit'));
    }
    
    public function getUploadLimit()
    {
        $max_upload = (int)(ini_get('upload_max_filesize'));
        $max_post = (int)(ini_get('post_max_size'));
        $max_memory = (int)(ini_get('memory_limit'));
        return min($max_upload, $max_post, $max_memory);
    }
    
    public function getUploadSizeLimit()
    {
        $memory_limit = $this->getMemoryLimit();
        $factor = 1.7;
        
        $resize_width = 800;
        $resize_height = 1700;
        
        return intval(sqrt(floatval((($memory_limit / $factor * 1024 * 1024) - ($resize_width * $resize_height)) / 4)));
    }
    
    public function getUploadTimeLimit()
    {
        $max_time_exe = (int)(ini_get('max_execution_time'));
        $max_time_input = (int)(ini_get('max_input_time'));
        
        if(!$max_time_input)
            return $max_time_exe;
            
        return min($max_time_exe, $max_time_input);  
    }
    
    
    public function go($url, $permanently = false)
    {
        if($permanently)
        {
            header('HTTP/1.1 301 Moved Permanently');
            header('Location: '.$url);
			header('Connection: close');
        }
        else
        {
            header('Location: '.$url);
        }
        
        exit();    
    }
    
    
    private function update()
    {
        if(defined('UPDATE_ALLOWED'))
            require(ROOT.'inc/update.php');     
    }
    
    
    public function printFrontendError($error = '', $title = 'CMS fokus')
    {
        echo '
        <!DOCTYPE html> 
        <html lang="de">
            <head>
                <meta charset="utf-8"/> 
                <title>'.$title.'</title>
                <link href="'.DOMAIN.'/fokus/css/reset.css" rel="stylesheet" type="text/css" media="screen, handheld, tv, projection" />
                <link href="'.DOMAIN.'/fokus/css/smoothness/jquery-ui.custom.css" rel="stylesheet" type="text/css" media="screen, handheld, tv, projection" />
                <link href="'.DOMAIN.'/fokus/css/layout.css" rel="stylesheet" type="text/css" media="screen, handheld, tv, projection" />

                <script src="'.DOMAIN.'/inc/libraries/js/jquery.min.js" type="text/javascript"></script>
                <script src="'.DOMAIN.'/inc/libraries/js/jquery-ui.custom.min.js" type="text/javascript"></script>
                <script src="'.DOMAIN.'/fokus/js/login.js" type="text/javascript"></script>
            </head>
            <body>
                <div id="main">
                    <table class="fenster">
                        <tr>
                            <td class="A1"></td>
                            <td class="A2"></td>
                            <td class="A3"></td>
                        </tr>
                        <tr>
                            <td class="B1"></td>
                            <td class="B2">
                                <div class="inhalt" id="install">
                                    <h1>'.$title.'</h1>
                                    <div class="box">
                                        <div class="warnung">
                                            '.$error.'
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="B3"></td>
                        </tr>
                        <tr>
                            <td class="C1"></td>
                            <td class="C2"></td>
                            <td class="C3"></td>
                        </tr>
                    </table>
                </div>
            </body>
        </html>';
        
        exit();
    }
    
    
    public function filetype($file)
    {
        $path_parts = pathinfo($file);
        $type = strtolower($path_parts['extension']);
        
        if($type == 'jpeg')
            $type = 'jpg';
            
        return $type;
    }
    
    public function filesize($URL)
    {
    	if(file_exists($URL))
        	$Groesse = filesize($URL);
    	
    	if($Groesse < 1){
            return "N/A";
        }
        else if($Groesse < 1000){
            return number_format($Groesse, 0, ",", ".")." Bytes";
        }
        else if($Groesse < 1048576){
            return number_format($Groesse/1024, 0, ",", ".")." kb";
        }
        else{
            return number_format($Groesse/1048576, 0, ",", ".")." mb";
        }
    }
    
    public function vars($type = 'REQUEST')
    {
        if($type == 'REQUEST')
            $ay = $_REQUEST;
        elseif($type == 'POST')
            $ay = $_POST;
        elseif($type == 'GET')
            $ay = $_GET;
            
        if(!self::$fksdb)
            return $ay;
            
        $rtn = array();
        foreach($ay as $a1 => $a2)
            $rtn[self::$fksdb->save($a1)] = self::$fksdb->save($a2);
            
        return $rtn;   
    }
    
    public function txt_error($error)
    {
        if(!$error)
            return false;
            
        $error .= "\n".date('d.m.Y')." - ".date('H:i')."\n\n";
    
        if(!$handle = @fopen(ROOT.'content/export/fehler.txt', 'a')) 
            return false;
            
        fwrite($handle, $error);
        fclose($handle);
    }
     
    public function calc_captcha($nr, $decode = 0)
    {
        $cz = $this->getOpt('login_captcha_rand');
        $rand = intval((date('Y') - date('d') + date('m')) * $cz);
        
        if(!$decode)
        {
            $res = $rand + $nr;
            $res -= $cz;
        }
        else
        {
            $res = $nr + $cz;
            $res = $res - $rand;
        }
        return $res;
    }
            
    public function block_preview_dk_content_by_type($content, $type)
    {
        if($type == 30)
        {
            if(!$content) return '';
            $ca = explode('|||', $content);
            $content = $ca[1].($ca[1] && $ca[2]?' / ':'').$ca[2];
        }
        return Strings::cut($content, 25);  
    }
    
    public function block_preview($html, $type, $teaserh = '', $bild = array(), $dklasse = '')
    {
        if($type < 30 && $type != 20 && $type != 22)
        {
            return ($html?Strings::cut(strip_tags(htmlspecialchars_decode($html)), 300):'<span class="no_content">(noch kein Inhalt)</span>');
        }
        elseif($type == 20)
        {
            $seri = $this->fixedUnserialize($html);
            unset($seri['kindof']);
            
            if(!is_array($seri) || !count($seri))
                return '<span class="no_content">(noch kein Inhalt)</span>';
                
            foreach($seri as $s)
                $rtn .= '&bull; '.strip_tags(htmlspecialchars_decode($s)).' ';
                
            return Strings::cut($rtn, 180);
        }
        elseif($type == 22)
        {
            $seri = $this->db_to_array($html);
            if(!is_array($seri))
                return '<span class="no_content">(noch kein Inhalt)</span>';
                
            $ox = intval($seri['x']);
            $oy = intval($seri['y']);
            $v = $this->db_to_array($seri['value']);
            
            $oxm = ($ox > 5?5:$ox);
            $oym = ($oy > 3?3:$oy);
            
            $rtn = '
            <table class="rtable">
                <tr>
                    <th colspan="'.$oxm.'">Insgesamt '.$ox.' Spalte'.($ox != 1?'n':'').' und '.$oy.' Zeile'.($oy != 1?'n':'').'</th>
                </tr>';
                for($y = 1; $y <= $oym; $y ++)
                {
                    $rtn .= '<tr>';
                    for($x = 1; $x <= $oxm; $x ++)
                    {
                        $rtn .=  '
                        <td>
                            '.($v[$y][$x]?Strings::cut(strip_tags(htmlspecialchars_decode($v[$y][$x])), 20):'').'
                        </td>';
                    }
                    $rtn .= '</tr>';
                }
            $rtn .= '
            </table>';
                
            return $rtn;
        }
        elseif($type == 30)
        {
            if(trim($bild['extern']))
            {
                $path = parse_url(trim($bild['extern']));
                return self::$trans->__('Bild aus externer Quelle:').' <em>'.$path['host'].'</em>';
            }
            
            $bq = self::$fksdb->fetch("SELECT id, file, type, stack FROM ".SQLPRE."file_versions WHERE stack = '".$bild['id']."' ORDER BY timestamp DESC LIMIT 1");
            if(!$bq)
                return '<span class="no_content">(noch kein Bild ausgewählt)</span>';
            $file = DOMAIN.'/img/'.$bq->stack.'-0-60-na.'.$bq->type;
            
            return '<img src="'.$file.'" height="60" alt=" " />';
        }
        elseif($type == 40)
        {
            $bilder = $this->fixedUnserialize($html);
            if(!is_array($bilder)) 
                return '<span class="no_content">(keine Bilder in dieser Galerie)</span>';
            $cgc = 0;
            
            $rtn = '
            <span class="galery">';
                foreach($bilder as $b1 => $b2)
                {   
                    if($b2['dir'])
                    {
                        $file = 'images/folder.png'; 
                    }
                    else
                    {
                        $bq = self::$fksdb->fetch("SELECT id, file, type, stack FROM ".SQLPRE."file_versions WHERE stack = '".$b2['id']."' ORDER BY timestamp DESC LIMIT 1");
                        $file = DOMAIN.'/img/'.$bq->stack.'-0-50-na.'.$bq->type;
                    }
                    
                    if($file)
                        $rtn .= '<img src="'.$file.'" height="50" alt=" " />';
                        
                    $cgc ++;
                    if($cgc >= 4)
                        break;
                }
                $rtn .= '
                <span>'.count($bilder).' Element'.(count($bilder) != 1?'e':'').' in dieser Galerie</span>
            </span>';
            
            return $rtn;
        }
        elseif($type == 64)
        {
            $teaser = $this->fixedUnserialize($teaserh);
            return ($teaser['element']?'Strukturelement: '.self::$fksdb->data("SELECT titel FROM ".SQLPRE."elements WHERE id = '".$teaser['element']."' LIMIT 1", "titel"):'<span class="no_content">(noch kein Strukturelement gewählt)</span>');
        }
        elseif($type == 1050)
        {
            $dka = $this->db_to_array($this->getOpt()->dk);
            $dk = (object)$dka;
        
            $seri = $this->fixedUnserialize($html);
            $doks = explode(',', $seri['sort']);
            if(!is_array($doks)) $doks = array();
            
            $sqlstring = "";
            foreach($doks as $s)
            {
                if(intval($s) > 0)
                    $sqlstring .= ($sqlstring?" OR ":"")." id = '".$s."' ";
            }
            
            $ka = array();
            $kq = self::$fksdb->query("SELECT id, titel, dk1, dk2, dk3, dk4, dk5, dk6, dk7, dk8, dk9, dk10, dkt1, dkt2, dkt3, dkt4, dkt5, dkt6, dkt7, dkt8, dkt9, dkt10 FROM ".SQLPRE."documents WHERE klasse = '".$seri['related']."' ".($sqlstring?"AND (".$sqlstring.")":"AND id = '-1'")); 
            
            while($ko = self::$fksdb->fetch($kq))
                $ka[$ko->id] = $ko;
                
            $getdklasse = $this->open_dklasse('../content/dklassen/'.$seri['related'].'.php'); 
            $slug = $this->slug($getdklasse['name']);
            
            $rtn = '<table>';
            foreach($doks as $did)
            {
                $k = $ka[$did];
                if(!$k)
                    continue;
                    
                $rtn .= '<tr>';
                $zipzap = 0; 
                    
                if(!is_array($dk->show[$slug]) || !is_array($dk->show_relation[$slug]))
                {
                    $rtn .= '<td>'.$k->titel.'</td>';
                }
                else
                {
                    if(!$dk->n_titel_uebersicht[$slug])
                        $rtn .= '<td>'.$k->titel.'</td>'; 
                    
                    foreach($dk->show[$slug] as $bid => $vid)
                    { 
                        $zipzap ++;
                        if($dk->show_relation[$slug][$bid])
                        {
                            $d1 = 'dk'.$zipzap;
                            $d2 = 'dkt'.$zipzap;
                            $rtn .= '<td>'.$this->block_preview_dk_content_by_type($k->$d1, $k->$d2).'</td>';
                        }
                    }
                }
                $rtn .= '</tr>';
            }
            $rtn .= '</table>';
            
            return $rtn;
        }
    }
    
    public function error($list, $message = 'auto')
    {
        if(!count($list))
            return '';
            
        $string = '';
        foreach($list as $l)
            $string .= '<li>'.$l.'</li>';
            
        return '
        <div class="box fehlerbox">
            <strong>
                '.($message == 'auto'?
                    (count($list) == 1?
                        self::$trans->__('Es trat folgender Fehler auf:')
                        :
                        self::$trans->__('Es traten mehrere Fehler auf:')
                    )
                    :
                    $message
                ).'
            </strong>
            <ul class="fehler">'.$string.'</ul>
        </div>';
    }
    
    public function countup()
    {
    	if($this->count_up_var == 2)
    		$this->count_up_var = 1;
    	else
    		$this->count_up_var = 2;
    	return $this->count_up_var;
    }
    
    public function user($id, $seperator, $f1, $f2 = '', $f3 = '', $f4 = '', $f5 = '', $f6 = '', $f7 = '', $f8 = '')
    {
        if(array_key_exists($id, $this->user_cache))
        {
            $b = $this->user_cache[$id];
        }
        else
        {
            $b = self::$fksdb->fetch("SELECT * FROM ".SQLPRE."users WHERE id = '".$id."' LIMIT 1"); 
            $this->user_cache[$id] = $b;
        }
        
        if($b)
        {
            return 
            ($f1 && !empty($b->$f1)?$b->$f1:'').
            ($f2 && !empty($b->$f2)?$seperator.$b->$f2:'').
            ($f3 && !empty($b->$f3)?$seperator.$b->$f3:'').
            ($f4 && !empty($b->$f4)?$seperator.$b->$f4:'').
            ($f5 && !empty($b->$f5)?$seperator.$b->$f5:'').
            ($f6 && !empty($b->$f6)?$seperator.$b->$f6:'').
            ($f7 && !empty($b->$f7)?$seperator.$b->$f7:'').
            ($f8 && !empty($b->$f8)?$seperator.$b->$f8:'');
        }
        else
        { 
            return 'N/A';
        }
    }
    
    public function is_online($time, $livetalk = false)
    {        
        if($time + 60 > time() && !$livetalk)
    		$online = 'gerade online';
    	else if($time + 3600 > time() && !$livetalk)
    		$online = 'vor '.(round((time() - $time) / 60, 0)).' Minuten';
    	else if($time >= $this->getZeroHour())
    		$online = 'heute, um '.date('H:i', $time).' Uhr';
    	else if($time >= $this->getZeroHour() - 86400)
    		$online = 'gestern, um '.date('H:i', $time).' Uhr';
    	else if(!$time)
            $online = 'noch nie';
    	else
    		$online = date('d.m.Y', $time);
            
        return $online;
    }
    
    public function doc_edit($time)
    {
        if($time + 60 > time())
    		$online = 'gerade eben';
    	else if($time + 3600 > time())
    		$online = 'vor '.(round((time() - $time) / 60, 0)).' Minuten';
    	else if($time >= $this->getZeroHour())
    		$online = 'vor '.(round((time() - $time) / 60 / 60, 0)).' Stunden';
    	else if($time >= $this->getZeroHour() - 86400)
    		$online = 'um '.date('H:i', $time).' Uhr';
    	else if(!$time)
            $online = 'noch nie';
    	else
    		$online = 'vor '.(round((time() - $time) / 60 / 60 / 24, 0)).' Tagen';
            
        return $online;
    }
    
    public function slug($str)
    {
        $find = array("ä", "ü", "ö", "Ä", "Ü", "Ö", "ß");
        $replace = array("ae", "ue", "oe", "ae", "ue", "oe", "ss");
        
        $str = htmlspecialchars_decode($str);
        $str = trim($str);
        $str = mb_strtolower($str, 'UTF-8');
        $str = str_replace($find, $replace, $str);
        $str = preg_replace('/[^a-z0-9-]/', '-', $str);
        $str = preg_replace('/-+/', "-", $str);
        $str = trim($str, "-");
        return $str;
    }
    
    public function auto_slug($spr)
    {
        $rtn = ($spr['url']?$spr['url']:($spr['titel']?$spr['titel']:'na'));
        return $this->slug($rtn);
    }
    
    public function auto_title($titel, $lan)
    {
        $vor_titel = $this->fixedUnserialize($this->getOpt()->vor_titel);
        $nach_titel = $this->fixedUnserialize($this->getOpt()->nach_titel);
        
        return trim($vor_titel[$lan].' '.$titel.' '.$nach_titel[$lan]);
    }
    
    public function file_perms($file, $octal = false)
    {
        if(!file_exists($file)) return false;
    
        $perms = fileperms($file);
    
        $cut = $octal ? 2 : 3;
    
        return substr(decoct($perms), $cut);
    }
    
    public function is_valid_email($email)
    {
        if(function_exists('filter_var')) 
        {
            return filter_var($email, FILTER_VALIDATE_EMAIL);
        } 
        else 
        {
            if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) 
            {
                return false;
            }
            
            $email_array = explode("@", $email);
            $local_array = explode(".", $email_array[0]);
            for ($i = 0; $i < sizeof($local_array); $i++) 
            {
                if (!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$", $local_array[$i])) 
                {
                    return false;
                }
            }    
            if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) 
            { 
                $domain_array = explode(".", $email_array[1]);
                if (sizeof($domain_array) < 2) 
                {
                        return false; 
                }
                for ($i = 0; $i < sizeof($domain_array); $i++) 
                {
                    if (!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|([A-Za-z0-9]+))$", $domain_array[$i])) 
                    {
                        return false;
                    }
                }
            }
            return true;
        }
        
    	return true;
    }
    
    public function is_valid_url($url)
    {
        if(!Strings::strExists('http', $url))
            $url = 'http://'.$url;
            
        if(function_exists('filter_var')) 
        {
            return filter_var($url, FILTER_VALIDATE_URL);
        } 
        else 
        {
            return preg_match('|^http(s)?://[a-z0-9-]+(.[a-z0-9-]+)*(:[0-9]+)?(/.*)?$|i', $url);
        }
        
    	return true;
    }
    
    public function get_attributes($atr_string)
    {        
        $atr = array();
        $atrA = explode(';', $atr_string);
        if(!is_array($atrA)) $atrA[] = $atr_string;
        if(!is_array($atrA)) $atrA = array();
        foreach($atrA as $a)
        {
            $aT = explode('=', $a);
            
            if($aT[0])
            {
                $aT[0] = $this->getAttributeAlternative($aT[0]);
                $atr[$aT[0]] = $aT[1];
            }
        } 
        return $atr;
    }
    
    public function array_to_db($array)
    {
        if(is_array($array))
            return base64_encode(gzcompress(serialize($array))); 
        return '';
    }
    
    public function db_to_array($string)
    { 
        if($string)
            return unserialize(gzuncompress(base64_decode($string)));
        return array();
    }

    public function fixedUnserialize($serialized)
    {
        $unserialized = @unserialize($serialized);
        if(is_array($unserialized))
            return $unserialized;

        $tmp = preg_replace('/^a:\d+:\{/', '', $serialized);
        return $this->repairSerializedArray_R($tmp);
    }

    private function repairSerializedArray_R(&$broken)
    {
        $data       = array();
        $index      = null;
        $len        = strlen($broken);
        $i          = 0;

        while(strlen($broken))
        {
            $i++;
            if ($i > $len)
            {
                break;
            }

            if (substr($broken, 0, 1) == '}') // end of array
            {
                $broken = substr($broken, 1);
                return $data;
            }
            else
            {
                $bite = substr($broken, 0, 2);
                switch($bite)
                {
                    case 's:': // key or value
                        $re = '/^s:\d+:"([^\"]*)";/';
                        if (preg_match($re, $broken, $m))
                        {
                            if ($index === null)
                            {
                                $index = $m[1];
                            }
                            else
                            {
                                $data[$index] = $m[1];
                                $index = null;
                            }
                            $broken = preg_replace($re, '', $broken);
                        }
                        break;

                    case 'i:': // key or value
                        $re = '/^i:(\d+);/';
                        if (preg_match($re, $broken, $m))
                        {
                            if ($index === null)
                            {
                                $index = (int) $m[1];
                            }
                            else
                            {
                                $data[$index] = (int) $m[1];
                                $index = null;
                            }
                            $broken = preg_replace($re, '', $broken);
                        }
                        break;

                    case 'b:': // value only
                        $re = '/^b:[01];/';
                        if (preg_match($re, $broken, $m))
                        {
                            $data[$index] = (bool) $m[1];
                            $index = null;
                            $broken = preg_replace($re, '', $broken);
                        }
                        break;

                    case 'a:': // value only
                        $re = '/^a:\d+:\{/';
                        if (preg_match($re, $broken, $m))
                        {
                            $broken         = preg_replace('/^a:\d+:\{/', '', $broken);
                            $data[$index]   = $this->repairSerializedArray_R($broken);
                            $index = null;
                        }
                        break;

                    case 'N;': // value only
                        $broken = substr($broken, 2);
                        $data[$index]   = null;
                        $index = null;
                        break;
                }
            }
        }

        return $data;
    }
    
    
    private function initAlternatives()
    {
        $block_translateA = array();
        $block_translateB = array();
        $block_translateA[] = ':wert(';                     $block_translateB[] = ':input(';
        $block_translateA[] = ':auswahl(';                  $block_translateB[] = ':select(';
        $block_translateA[] = ':ueberschrift(';             $block_translateB[] = ':h1(';
        $block_translateA[] = ':unterueberschrift(';        $block_translateB[] = ':h2(';
        $block_translateA[] = ':abschnittsueberschrift(';   $block_translateB[] = ':h3(';
        $block_translateA[] = ':zwischenueberschrift(';     $block_translateB[] = ':h4(';
        $block_translateA[] = ':textblock(';                $block_translateB[] = ':p(';
        $block_translateA[] = ':zitat(';                    $block_translateB[] = ':quote(';
        $block_translateA[] = ':liste(';                    $block_translateB[] = ':ul(';
        $block_translateA[] = ':liste(';                    $block_translateB[] = ':list(';
        $block_translateA[] = ':bild(';                     $block_translateB[] = ':img(';
        $block_translateA[] = ':galerie(';                  $block_translateB[] = ':gallery(';
        $block_translateA[] = ':strukturteaser(';           $block_translateB[] = ':teaser(';
        $block_translateA[] = ':kommentare(';               $block_translateB[] = ':comments(';
        $block_translateA[] = ':suche(';                    $block_translateB[] = ':search(';
        $block_translateA[] = ':datumswert(';               $block_translateB[] = ':datepicker(';
        $block_translateA[] = ':datum(';                    $block_translateB[] = ':date(';
        $block_translateA[] = ':zurueck(';                  $block_translateB[] = ':back(';
        $block_translateA[] = ':autor(';                    $block_translateB[] = ':author(';
        $block_translateA[] = ':kopie(';                    $block_translateB[] = ':copy(';
        $block_translateA[] = ':inhaltsbereich(';           $block_translateB[] = ':content(';
        $block_translateA[] = ':notiz(';                    $block_translateB[] = ':note(';
        
        
        $atr_translateA = array();
        $atr_translateB = array();
        $atr_translateA[] = 'galerie_class';                $atr_translateB[] = 'gallery_class';
        $atr_translateA[] = 'zeichen';                      $atr_translateB[] = 'limit_chars';
        $atr_translateA[] = 'weiterlesen';                  $atr_translateB[] = 'more';
        $atr_translateA[] = 'laendercode';                  $atr_translateB[] = 'country_code';
        $atr_translateA[] = 'gruppe';                       $atr_translateB[] = 'group';
        $atr_translateA[] = 'feldbreite';                   $atr_translateB[] = 'input_width';
        $atr_translateA[] = 'werte';                        $atr_translateB[] = 'values';
        
        $this->block_alternatives = array($block_translateA, $block_translateB);
        $this->attr_alternatives = array($atr_translateA, $atr_translateB); 
    }
    
    private function getBlockAlternative($string)
    {
        return str_replace($this->block_alternatives[1], $this->block_alternatives[0], $string);        
    }
    
    private function getAttributeAlternative($string)
    {
        return str_replace($this->attr_alternatives[1], $this->attr_alternatives[0], $string);        
    }
    
    
    public function open_template_config($file)
    {
        if(!Strings::strExists('.php', $file, false))
            $file .= '.php';
            
        $file = str_replace('.php.php', '.php', $file);
        
        if(!file_exists($file))
            return array();
        
        $template = array();
        ob_start();
        include($file);
        ob_end_clean();
             
        if(!$template['files'])
            $template['files'] = $template['dateien']; 
        if(!$template['mobile'])
            $template['mobile'] = $template['mobile_dateien']; 
        if(!$template['newsletter'])
            $template['newsletter'] = $template['newsletter_dateien']; 
        if(!$template['menus'])
            $template['menus'] = $template['menue']; 
        if(!$template['custom_fields'])
            $template['custom_fields'] = $template['feld']; 
        if(!$template['global_custom_fields'])
            $template['global_custom_fields'] = $template['feld_global']; 
            
        if(!$template['classes'] && count($template['css_klassen']))
        {
            foreach($template['css_klassen'] as $cssA => $cssB)
            {
                if($cssB)
                    $template['classes'][$cssB] = array('name' => $cssA, 'restriction' => 'none');        
            }
        }
            
        return $template;
    }
    
    public function open_dklasse($file)
    {
        if(!Strings::strExists('.php', $file, false))
            $file .= '.php';
            
        $file = str_replace('.php.php', '.php', $file);
        if(!file_exists($file))
            $file = str_replace('/dklassen/', '/document-classes/', $file);
        if(!file_exists($file))
            return array();
        
        $fk = array();
        $dclass = array();
        
        ob_start();
        include($file); 
        ob_end_clean();
        
        if(!count($fk) && count($dclass))
            $fk = $dclass;
            
        if(!$fk['content'])
            $fk['content'] = $fk['inhalt'];
            
        if(is_array($fk['relation']))
        {
            foreach($fk['relation'] as $relk => $relv)
            {
                if(!$fk['relation'][$relk]['dclass'])
                    $fk['relation'][$relk]['dclass'] = $fk['relation'][$relk]['dklasse'];
                if(!$fk['relation'][$relk]['content'])
                    $fk['relation'][$relk]['content'] = $fk['relation'][$relk]['inhalt'];
            
                if($fk['relation'][$relk]['content'])
                    $fk['relation'][$relk]['content'] = $this->getBlockAlternative($fk['relation'][$relk]['content']);
            }
        }
        
        if($fk['related'])
        {
            $fk_t = $this->open_dklasse(ROOT.'content/dklassen/'.$fk['related']);
            $tmp_content = $fk_t['content'];
            
            $fk['content'] = $tmp_content;
        }
        
        if($fk['content'])
            $fk['content'] = $this->getBlockAlternative($fk['content']);
        
        return $fk;
    }
    
    public function open_stklasse($file)
    {
        if(!Strings::strExists('.php', $file, false))
            $file .= '.php';
            
        $file = str_replace('.php.php', '.php', $file);
        if(!file_exists($file))
            $file = str_replace('/stklassen/', '/teaser-classes/', $file);
        if(!file_exists($file))
            return array();
        
        $stk = array();
        $teaser = array();
        
        ob_start();
        include($file); 
        ob_end_clean();
        
        if(!count($stk) && count($teaser))
            $stk = $teaser;
            
        if(!$stk['content'])
            $stk['content'] = $stk['inhalt'];
        
        if($stk['content'])
            $stk['content'] = $this->getBlockAlternative($stk['content']);
            
        if(is_array($stk))
            return $stk;
        return array();
    }
    
    public function email($to, $subject, $message, $by = '')
    {
        if(Strings::strExists(';', $to))
        {
            $recipents = explode(';', $to);
            foreach($recipents as $rec)
                $this->email(trim($rec), $subject, $message, $by);
            return true;
        }

        if(!$by)
            $by = $this->getOpt('email');

        $message = html_entity_decode(strip_tags(htmlspecialchars_decode($message)));
        $eheader = 'FROM: '.$by . "\r\n" . 'MIME-Version: 1.0' . "\r\n" . 'Content-type: text/plain; charset=UTF-8' . "\r\n";
        $email = mb_send_mail($to, $subject, $message, $eheader);
        
        if(!$email)
        {
            $error = "Email konnte nicht verschickt verschickt werden!\nAn:".$to."\nBetreff:".$subject;
            $this->txt_error($error);
            
            return false;
        }
        
        return true;
    }
    
    public function generate_htaccess($rewritebase = '', $domain = '')
    {
        if(!$rewritebase)
            $rewritebase = $this->getOpt('rewritebase');
            
        if(!$domain)
            $domain = DOMAIN;
        
        $htaccess_add = '# START FKS
RewriteEngine on
RewriteBase '.$rewritebase.'

RewriteCond %{REQUEST_URI} ^/[^\.]+[^/]$
RewriteRule ^(.*)$ http://%{HTTP_HOST}/$1/ [R=301,L]

RewriteRule ^test-mod-rewrite/$ inc/test.php

RewriteRule ^([a-z]{2})/$ index.php?lan=$1

RewriteRule ^([a-z]{2})/([0-9]+)/([0-9]+)/([^/]+)/([0-9]+)/$ index.php?lan=$1&id=$2&dk=$3&titel=$4&seite=$5
RewriteRule ^([a-z]{2})/([0-9]+)/([0-9]+)/([^/]+)/$ index.php?lan=$1&id=$2&dk=$3&titel=$4

RewriteRule ^([a-z]{2})/([0-9]+)/([^/]+)/([0-9]+)/$ index.php?lan=$1&id=$2&titel=$3&seite=$4
RewriteRule ^([a-z]{2})/([0-9]+)/([^/]+)/$ index.php?lan=$1&id=$2&titel=$3


RewriteRule ^([0-9]+)/([0-9]+)/([^/]+)/([0-9]+)/$ index.php?id=$1&dk=$2&titel=$3&seite=$4
RewriteRule ^([0-9]+)/([0-9]+)/([^/]+)/$ index.php?id=$1&dk=$2&titel=$3

RewriteRule ^([0-9]+)/([^/]+)/([0-9]+)/$ index.php?id=$1&titel=$2&seite=$3
RewriteRule ^([0-9]+)/([^/]+)/$ index.php?id=$1&titel=$2


RewriteRule ^([a-z]{2})/c/([^/]+)/$ index.php?lan=$1&cp=$2
RewriteRule ^([a-z]{2})/c/([^/]+)/([^/]+)/$ index.php?lan=$1&cp=$2&cp_vars=$3
RewriteRule ^c/([^/]+)/$ index.php?cp=$1
RewriteRule ^c/([^/]+)/([^/]+)/$ index.php?cp=$1&cp_vars=$2

RewriteRule ^([a-z]{2})/q/([^/]+)/$ index.php?lan=$1&fks_q=$2
RewriteRule ^q/([^/]+)/$ index.php?fks_q=$1

RewriteRule ^ajax/([^/]+)/$ inc/ajax.php?action=$1

RewriteRule ^static/([a-z]+)/([^/]+)/$ inc/static.php?type=$1&files=$2

RewriteRule ^feed/([0-9]+)/([^/]+)/$ inc/feed.php?id=$1&titel=$2

RewriteRule ^activate/([^/]+)/([^/]+)/$ index.php?fid=$1&acode=$2

RewriteRule ^img/([0-9]+).([0-9]+).([0-9]+).([^/]+)$ inc/img.php?id=$1&w=$2&h=$3&s=$4&titel=$5
RewriteRule ^files/([0-9]+)/([^/]+)$ inc/download.php?id=$1

RewriteRule ^sitemap.xml$ inc/sitemap.php
RewriteRule ^([a-z]{2})/sitemap.xml$ inc/sitemap.php?lan=$1

RewriteRule ^robots.txt$ inc/robots.txt


RewriteRule ^error/([0-9]+)/$ index.php?error=$1
ErrorDocument 403 '.$domain.'/error/403/
ErrorDocument 404 '.$domain.'/error/404/
ErrorDocument 500 '.$domain.'/error/500/
ErrorDocument 503 '.$domain.'/error/503/


<Files fokus-config.php>
    Order Deny,Allow
    Deny from all
</Files>

AddDefaultCharset utf-8
# END FKS'; 
    
        return $htaccess_add;   
    }
    
    public function refreshHtaccess()
    {
        $filename = ROOT.'.htaccess';
        $htaccess = file_get_contents($filename);
        
        $htaccess_add = $this->generate_htaccess();
                
        if(Strings::strExists('# START FKS', $htaccess, false))
            $htaccess = stripslashes(preg_replace('~# START FKS(.*)# END FKS~isU', preg_quote ($htaccess_add), $htaccess, 1)); 
        else
            $htaccess = $htaccess."
".$htaccess_add;
    
        if(!$handle = fopen($filename, "w")) 
            return false;
        if(!is_writable($filename)) 
            return false;
            
        fwrite($handle, $htaccess);
        fclose($handle);
        
        return true;
    }
    
    public function time_options($current = 0, $minutes = false)
    {
        $rtn = '';
        for($x = 0; $x < ($minutes?60:24); $x++)
            $rtn .= '<option value="'.$x.'"'.($current == $x?' selected="selected"':'').'>'.str_pad($x, 2 ,'0', STR_PAD_LEFT).'</option>';
        return $rtn;
    }
    
    
    public function getMimeType($file)
    {
        $mime = '';
        
        if(function_exists('finfo_open'))
        {
            $finfo = @finfo_open(FILEINFO_MIME_TYPE); 
            $mime = @finfo_file($finfo, $file);
            @finfo_close($finfo);
        }
        elseif(function_exists('mime_content_type'))
        {
            $mime = @mime_content_type($file);
        }   
        
        if(!$mime)
        {
            if(Strings::strExists('jpg', substr($file, -4), false) || Strings::strExists('jpeg', substr($file, -5), false))
                $mime = 'image/jpeg';
            elseif(Strings::strExists('png', substr($file, -4), false))
                $mime = 'image/png'; 
            elseif(Strings::strExists('gif', substr($file, -4), false))
                $mime = 'image/gif'; 
        }
        
        return $mime; 
    }
    
    public function image2data($image)
    {
        $imageData = base64_encode(file_get_contents($image));
        return 'data: '.$this->getMimeType($image).';base64,'.$imageData;
    }
    
    
    public function create_dk_snippet_content_by_type($content, $type, $bildid = 0, $zeichen = 0)
    {
        // Falls normaler Text
        if($type < 30 && $type != 20) 
        {
            $n_content = strip_tags(htmlspecialchars_decode($content)); 
        }
        
        // Falls Liste
        if($type == 20)
        {
            $elemente = $this->fixedUnserialize($content);
            $rtn = '';
            if(count($elemente))
            {
                foreach($elemente as $e)
                    $rtn .= '&bull; '.strip_tags(htmlspecialchars_decode($e)).' '; 
            }
            $n_content = $rtn;
        }
        
        // Falls Bild
        if($type == 30 && $bildid)
        {
            $stack = self::$fksdb->fetch("SELECT titel, beschr, id, last_type FROM ".SQLPRE."files WHERE id = '".$bildid."' AND papierkorb = '0' LIMIT 1");
            if($stack->id)
                $n_content = $stack->id.'|||'.$stack->titel.'|||'.$stack->beschr.'|||'.$stack->last_type;
        }
        
        // Falls Zeichenbeschraenkung
        if($zeichen > 0)
        {
            $n_content = Strings::cut($n_content, $zeichen, '');
        }
        
        return $n_content;
    }
    
    public function create_dk_snippet($dokument, $alle = false)
    {        
        if(!$alle)
            $updt_first = self::$fksdb->query("UPDATE ".SQLPRE."documents SET statusA = '0', timestamp_edit = '".time()."' WHERE id = '".$dokument."' LIMIT 1");
        
        $d = self::$fksdb->fetch("SELECT id, klasse, dversion_edit FROM ".SQLPRE."documents WHERE id = '".$dokument."' AND klasse != '' LIMIT 1");
        if(!$d->id)
            return false;
        
        $dka = $this->db_to_array($this->getOpt()->dk); 
        $dk = (object)$dka; 
            
        $dve = self::$fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE id = '".$d->dversion_edit."' AND dokument = '".$d->id."' LIMIT 1"); 
        $ki = $this->fixedUnserialize($dve->klasse_inhalt);
        
        if(!$dve->klasse_inhalt)
            return false;
        
        $ordner = ROOT.'content/dklassen/';
        $fk = $this->open_dklasse($ordner.$d->klasse.'.php');
        
        if(!$fk['name'])
            return false;
                        
        $slug = $this->slug($fk['name']);
        
        $result = preg_match_all('@:(.*)\):@iU', $fk['content'], $subpattern);  
        $countatr = 0;
        
        $dks = array();
        
        $auto_titel = array();
        $atitel = '';
            
        foreach($subpattern[1] as $s1 => $s2)
        {
            $countatr ++;
                
            $b = explode('(', $s2);  
            
            if(in_array($b[0], $this->getBlocks('dclass')))
            { 
                $atr = $this->get_attributes($b[1]);     
                if(!$atr['name'])
                    continue;                            
                           
                $block_type = array_search($b[0], $this->getBlocks('dclass'));  
                $bid = $this->slug($atr['name']); 
                
                if($dk->auto_titel[$slug])
                {
                    if($dk->at_1[$slug] == $bid)
                        $auto_titel[1] = $this->create_dk_snippet_content_by_type($ki[$bid]['html'], $block_type, $ki[$bid]['bildid'], $dk->atz_1[$slug]); 
                    if($dk->at_2[$slug] == $bid)
                        $auto_titel[2] = $this->create_dk_snippet_content_by_type($ki[$bid]['html'], $block_type, $ki[$bid]['bildid'], $dk->atz_2[$slug]); 
                    if($dk->at_3[$slug] == $bid)
                        $auto_titel[3] = $this->create_dk_snippet_content_by_type($ki[$bid]['html'], $block_type, $ki[$bid]['bildid'], $dk->atz_3[$slug]);       
                }
                
                if($block_type > 30 || !$dk->show[$slug][$bid])
                    continue;  
                 
                if($countatr <= 10)
                { 
                    $dks[] = array(
                        'type' => $block_type,
                        'content' => $this->create_dk_snippet_content_by_type($ki[$bid]['html'], $block_type, $ki[$bid]['bildid'])
                    );
                }
            }
        }  
                
        if($dk->auto_titel[$slug])
        {
            foreach($auto_titel as $at)
            {
                $atitel .= ($atitel?' - ':'').$at;
            }
            
            $upt = self::$fksdb->query("UPDATE ".SQLPRE."documents SET titel = '".$atitel."' WHERE id = '".$d->id."' LIMIT 1");
        }
        
        $upt = self::$fksdb->query("UPDATE ".SQLPRE."documents SET dkt1 = '".$dks[0]['type']."', dk1 = '".$dks[0]['content']."', dkt2 = '".$dks[1]['type']."', dk2 = '".$dks[1]['content']."', dkt3 = '".$dks[2]['type']."', dk3 = '".$dks[2]['content']."', dkt4 = '".$dks[3]['type']."', dk4 = '".$dks[3]['content']."', dkt5 = '".$dks[4]['type']."', dk5 = '".$dks[4]['content']."', dkt6 = '".$dks[5]['type']."', dk6 = '".$dks[5]['content']."', dkt7 = '".$dks[6]['type']."', dk7 = '".$dks[6]['content']."', dkt8 = '".$dks[7]['type']."', dk8 = '".$dks[7]['content']."', dkt9 = '".$dks[8]['type']."', dk9 = '".$dks[8]['content']."', dkt10 = '".$dks[9]['type']."', dk10 = '".$dks[9]['content']."' WHERE id = '".$d->id."' LIMIT 1");
        
        return true;
    }
       
    public function document_status($a, $b, $noText = false)
    {
        $alt = '';
        if($b == 0)
        {
            if($a == 0)
                $alt .= 'in Bearbeitung';
            elseif($a == 1)
                $alt .= 'zur Freigabe';
            elseif($a == 2)
                $alt .= 'freigegeben';
        }
        elseif($b == 1)
        {
            $alt .= 'Offline (Lebensdauer)';
        }
        elseif($b == 2)
        {
            $alt .= 'Offline (gesperrt)';
        }
        elseif($b == 3)
        {
            $alt .= 'Offline (keine Freigabe)';
        }
        
        $rtn = '<span class="mystatus statusA'.$a.' statusB'.$b.'"'.($noText?' title="'.$alt.'"':'').'></span>';
        if(!$noText)
            $rtn .= $alt;
        
        return $rtn;
    }
    
    public function find_document_statusB($closed, $from, $to, $version_frei = 1)
    {
        if($closed > 0)
        {
            return 2;
        }
        if($from > time() || ($to > 1000 && $to < time()))
        {
            return 1;
        }
        if(!$version_frei)
        {
            return 3;
        }
        
        return 0;
    }
    
    public function find_check_document_statusB($id, $from, $to, $status)
    {
        if($status == 0)
        {
            if($from > time() || ($to > 1000 && $to < time()))
            {
                $updt = self::$fksdb->query("UPDATE ".SQLPRE."documents SET statusB = '1' WHERE id = '".$id."' LIMIT 1");
                return 1;
            }    
        }
        elseif($status == 1)
        {
            if($from <= time() && ($to <= 1000 || $to >= time()))
            {
                $updt = self::$fksdb->query("UPDATE ".SQLPRE."documents SET statusB = '0' WHERE id = '".$id."' LIMIT 1");
                return 0;
            } 
        }
        
        return -1;
    }
    
    public function debug($mixed, $print = true)
    {
        $debug = '<pre>'.print_r($mixed, true).'<pre>';
        
        if($print)
            exit($debug);
        return $debug;
    }
    
    
    private function initBlocks()
    {
        $this->blocks = array(
            1 => array(
                'de' => 'Wert',
                'en' => 'input',
                'dclass' => 'wert'
            ), 
            4 => array(
                'de' => 'Auswahl',
                'en' => 'select',
                'dclass' => 'auswahl'
            ), 
            5 => array(
                'de' => 'Checkbox',
                'en' => 'checkbox',
                'dclass' => 'checkbox'
            ), 
            8 => array(
                'de' => 'Text',
                'en' => 'text',
                'dclass' => 'text'
            ), 
            10 => array(
                'de' => 'Überschrift (H1)',
                'en' => 'h1',
                'dclass' => 'ueberschrift'
            ), 
            11 => array(
                'de' => 'Unterüberschrift (H2)',
                'en' => 'h2',
                'dclass' => 'unterueberschrift'
            ), 
            12 => array(
                'de' => 'Abschnittsüberschrift (H3)',
                'en' => 'h3',
                'dclass' => 'abschnittsueberschrift'
            ), 
            13 => array(
                'de' => 'Zwischenüberschrift (H4)',
                'en' => 'h4',
                'dclass' => 'zwischenueberschrift'
            ), 
            15 => array(
                'de' => 'Textblock',
                'en' => 'p',
                'dclass' => 'textblock'
            ), 
            18 => array(
                'de' => 'Zitat',
                'en' => 'quote',
                'dclass' => 'zitat'
            ), 
            20 => array(
                'de' => 'Liste',
                'en' => 'list',
                'dclass' => 'liste'
            ), 
            22 => array(
                'de' => 'Tabelle',
                'en' => 'table',
                'dclass' => 'table'
            ), 
            24 => array(
                'de' => 'HTML-Block',
                'en' => 'html',
                'dclass' => 'html'
            ), 
            30 => array(
                'de' => 'Bild',
                'en' => 'img',
                'dclass' => 'bild'
            ), 
            36 => array(
                'de' => 'Video',
                'en' => 'video',
                'dclass' => 'video'
            ), 
            40 => array(
                'de' => 'Galerie',
                'en' => 'gallery',
                'dclass' => 'galerie'
            ), 
            44 => array(
                'de' => 'Google Maps',
                'en' => 'gmaps',
                'dclass' => 'gmaps'
            ), 
            47 => array(
                'de' => 'QR-Code',
                'en' => 'qr',
                'dclass' => 'qr'
            ), 
            52 => array(
                'de' => 'Formular',
                'en' => 'form'
            ), 
            64 => array(
                'de' => 'Teaser',
                'en' => 'teaser',
                'dclass' => 'strukturteaser'
            ), 
            66 => array(
                'de' => 'Referenz',
                'en' => 'reference',
                'dclass' => 'reference'
            ), 
            67 => array(
                'de' => 'Sitemap',
                'en' => 'sitemap',
                'dclass' => 'sitemap'
            ), 
            69 => array(
                'de' => 'Kommentare',
                'en' => 'comments'
            ), 
            71 => array(
                'de' => 'Suche',
                'en' => 'search',
                'dclass' => 'suche'
            ), 
            73 => array(
                'de' => 'Login',
                'en' => 'login',
                'dclass' => 'login'
            ), 
            100 => array(
                'de' => 'Erweiterung',
                'en' => 'extension',
                'dclass' => 'extension'
            ),
            1005 => array(
                'de' => 'Datumswert',
                'en' => 'datepicker',
                'dclass' => 'datumswert'
            ),
            1015 => array(
                'de' => 'Link-Auswahl',
                'en' => 'linkpicker',
                'dclass' => 'linkpicker'
            ),
            1016 => array(
                'de' => 'URL-Auswahl',
                'en' => 'urlpicker',
                'dclass' => 'urlpicker'
            ),
            1050 => array(
                'de' => 'Relationsplatzhalter',
                'en' => 'relation',
                'dclass' => 'relation'
            )
        );    
    }
    
    public function getBlockByID($id, $sort = '')
    {
        if($sort)
            return $this->blocks[$id][$sort];
        
        return $this->blocks[$id];
    }
    
    public function getBlocks($sort = '')
    {
        if(!$sort)
        {
            return $this->blocks;
        }
        else
        {
            $output = array();
            
            foreach($this->blocks as $number => $entrys)
                $output[$number] = $entrys[$sort];
                
            return $output;
        }
    }
     
    private function initDclassMethods()
    {   
        $this->dclass_methods = array(
            'datum', 
            'link', 
            'zurueck', 
            'autor', 
            'kopie', 
            'url', 
            'id',
            'categories',
            'commentcount',
            'callback'
        );
    }
    
    public function getDclassMethods()
    {
        return $this->dclass_methods;    
    }
    
    
    public function getClassBlocks($dclass = array())
    {
        $useless = preg_match_all('@:(.*)\):@iU', $dclass['content'], $blocks); 
        $btypen = array();
         
        $result = array();
        
        foreach($blocks[1] as $s1 => $s2)
        {
            $b = explode('(', $s2);  
            $f = $b[0];
            $a = $b[1];
            
            if(in_array($f, $this->getBlocks('dclass')))
            { 
                $block_type = array_search($f, $this->getBlocks('dclass'));
                $btypen[$block_type] += 1;
                
                $attr = $this->get_attributes($a);

                if($attr['name'])
                    $bid = $this->slug($attr['name']);
                else
                    $bid = $block_type.'_'.$btypen[$block_type];
                    
                $result[$bid] = array(
                    'type' => $block_type,
                    'attr' => $attr
                );
            }
        }
        
        return $result;
    }
}
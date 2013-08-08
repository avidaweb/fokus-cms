<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Translation 
{
    private $translation, $fokus_language;
    private $standard_language;
    private $input_language;
    
    private $countries, $avaible_languages;
    
    function __construct($p = array())
    {
        $this->translation = array();
        
        $this->setFokusLanguage($p['fokus_language']);
        $this->setStandardLanguage($p['standard_language']);
        $this->setInputLanguage($p['input_language']);
        
        $this->initCountries();
        $this->initLanguages();
    }
    
    
    public function setFokusLanguage($fokus_language)
    {
        if(!$fokus_language)
            $fokus_language = 'de';
        $this->fokus_language = $fokus_language;
        
        $this->getTranslations();
    }
    
    public function setStandardLanguage($standard_language)
    {
        if(!$standard_language)
            $standard_language = 'de';
        $this->standard_language = $standard_language;
    }
    
    public function setInputLanguage($input_language)
    {
        if(!$input_language)
            $input_language = $this->getStandardLanguage();
        $this->input_language = $input_language;
    }
    
    public function getFokusLanguage()
    {
        return $this->fokus_language;
    }
    
    public function getFokusLanguageCode()
    {
        return substr($this->fokus_language, 0, 2);
    }
    
    public function getStandardLanguage()
    {
        return $this->standard_language;
    }
    
    public function getInputLanguage()
    {
        return $this->input_language;
    }
    
    private function getTranslations($language = '')
    {
        $lan = ($language?$language:$this->getFokusLanguage());
        
        $file = ROOT.'inc/translations/'.$lan.'.php'; 
        if(file_exists($file))
            include_once($file);
            
        if(!is_array($translation))
            $translation = array();
            
        $this->translation[$lan] = $translation;
    }
    
    
    public function getText($key, $vars = array(), $language = '')
    {   
        $lan = ($language?$language:$this->getFokusLanguage());
        
        if(!is_array($this->translation[$lan]))
            $this->getTranslations($lan);
        
        $output = ($this->translation[$lan][$key]?$this->translation[$lan][$key]:$key);
        
        if(count($vars))
        {
            $search = array();
            $replace = array();
            $count = 1;
            
            foreach($vars as $v)
            {
                $search[] = '%'.$count;
                $replace[] = $v;    
                $count ++;       
            }
        
            $output = str_replace($search, $replace, $output);
        }
        
        return $output;
    }
    
    public function writeText($key, $vars = array())
    {   
        echo $this->getText($key, $vars);
    }
    
    public function __($key, $echo = false, $vars = array())
    {
        if(!$echo)
            return $this->getText($key, $vars);
        else
            $this->writeText($key, $vars);
    }
    
    public function addTranslation($language, $key, $translation)
    {
        $this->translation[$language][$key] = $translation; 
        return true;
    }
    
    
    private function initCountries()
    {
        $this->countries = array("Afghanistan", "Ägypten", "Albanien", "Algerien", "Amerikanisch-Samoa", "Andorra", "Angola", "Anguilla", "Antarktis", "Antigua und Barbuda", "Äquatorialguinea", "Argentinien", "Armenien", "Aruba", "Australien", "Äthiopien", "Azerbaijan", "Bahamas", "Bahrain", "Bangladesh", "Barbados", "Belgien", "Belize", "Benin", "Bermudas", "Bhutan", "Bolivien", "Bosnien-Herzegovina", "Botswana", "Bouvet Island", "Brasilien", "British Indian Ocean Territory", "Brunei Darussalam", "Bulgarien", "Burkina Faso", "Burundi", "Canada", "Chile", "China", "Christmas Island", "Cocos (Keeling) Islands", "Cook Islands", "Costa Rica", "Demokratische Republik Kongo", "Deutschland", "Djibouti", "Dominica", "Dominikanische Republik", "Dänemark", "Ecuador", "El Salvador", "Elfenbeinküste (Cote D'Ivoire)", "Eritrea", "Estland", "Falkland-Inseln (Malvinas)", "Faröer-Inseln", "Fiji", "Finnland", "France, Metropolitan", "Frankreich", "Französisch Guiana", "Französisch Polynesien", "Französisches Südl.Territorium", "Gabon", "Gambia", "Georgien", "Ghana", "Gibraltar", "Grenada", "Griechenland", "Großbritannien", "Grönland", "Guadeloupe", "Guam", "Guatemala", "Guinea", "Guinea-Bissau", "Guyana", "Haiti", "Heard und Mc Donald Islands", "Honduras", "Indien", "Indonesien", "Irak", "Iran (Islamische Republik)", "Irland", "Island", "Israel", "Italien", "Jamaica", "Japan", "Jemen", "Jordanien", "Jugoslawien", "Kamerun", "Kap Verde", "Kasachstan", "Katar", "Kayman Islands", "Kenya", "Kirgisien", "Kiribati", "Kolumbien", "Komoren", "Kong Hong", "Kongo", "Korea", "Korea, Volksrepublik", "Kuba", "Kuwait", "Königreich Kambodscha", "Laos", "Lesotho", "Lettland", "Libanon", "Liberia", "Libyen", "Liechtenstein", "Littauen", "Luxemburg", "Macao", "Madagaskar", "Malawi", "Malaysien", "Malediven", "Mali", "Malta", "Marokko", "Marshall-Inseln", "Martinique", "Mauretanien", "Mauritius", "Mayotte", "Mazedonien, ehem. Jugoslawische Republik", "Mexico", "Micronesien", "Moldavien", "Monaco", "Mongolei", "Montserrat", "Mozambique", "Myanmar", "Namibia", "Nauru", "Nepal", "Neu Kaledonien", "Neuseeland", "Nicaragua", "Niederlande", "Niederländische Antillen", "Niger", "Nigeria", "Niue", "Norfolk Island", "Norwegen", "Nördliche Marianneninseln", "Oman", "Österreich (Austria)", "Ost-Timor", "Pakistan", "Palau", "Panama", "Papua Neuguinea", "Paraguay", "Peru", "Philippinen", "Pitcairn", "Polen", "Portugal", "Puerto Rico", "Reunion", "Ruanda", "Rumänien", "Russische Föderation", "Saint Kitts und Nevis", "Saint Lucia", "Saint Vincent und Grenadines", "Salomonen", "Sambia", "Samoa", "San Marino", "Sao Tome und Principe", "Saudi Arabien", "Schweden", "Schweiz", "Senegal", "Seychellen", "Sierra Leone", "Singapur", "Slovenien", "Slowakei", "Somalia", "Spanien", "Sri Lanka", "St. Helena", "St. Pierre und Miquelon", "Sudan", "Surinam", "Svalbard und Jan Mayen Islands", "Swaziland", "Syrien, Arabische Republik", "Südafrika", "Südgeorgien und Südliche Sandwich-Inseln", "Tadschikistan", "Taiwan", "Tansania, United Republic of", "Thailand", "Togo", "Tokelau", "Tonga", "Trinidad und Tobago", "Tschad", "Tschechische Republik", "Tschechoslowakei (ehemalige)", "Tunesien", "Turk und Caicos-Inseln", "Turkmenistan", "Tuvalu", "Türkei", "Uganda", "Ukraine", "Ungarn", "Uruguay", "Usbekistan", "Vanuatu", "Vatikanstaat", "Venezuela", "Vereinigte Arabische Emirate", "Vereinigte Staaten", "Vereinigte Staaten, Minor Outlying Islands", "Vietnam", "Virgin Islands (Britisch)", "Virgin Islands (U.S.)", "Wallis und Futuna Islands", "Weißrußland (Belarus)", "Westsahara", "Zentralafrikanische Republik", "Zimbabwe", "Zypern");    
    }
    
    public function getCountries()
    {
        return $this->countries;    
    }
    
    private function initLanguages()
    {
        $this->avaible_languages = array('BE', 'BR', 'BG', 'CA', 'CN', 'CO', 'CZ', 'EG', 'EN', 'FR', 'DE', 'GR', 'HU', 'IN', 'IT', 'JP', 'NL', 'NO', 'PL', 'PT', 'RU', 'ES', 'SE', 'CH', 'TR', 'UA', 'US');
        
        sort($this->avaible_languages);
        
        return $this->avaible_languages;
    }
    
    public function getLanguages()
    {
        return $this->avaible_languages;
    }
    
    public function getFlag($country_code, $size = 1, $domain = false)
    {
        $thumb = str_replace(' ', '-', $this->getText(strtoupper($country_code), array(), 'en'));
        return ($domain?DOMAIN.'/fokus/':BACKEND_DIR).'images/flags/'.$thumb.'-Flag_00'.$size.'.png';
    }
}
?>
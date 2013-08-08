<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Navigation
{    
    private static $base, $api, $fksdb, $user, $suite, $trans, $fks; 
    
    private $output = '', $counter = 0;
    
    function __construct($static = array())
    {
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
        self::$fks = $static['fks'];
    }
        
    private function level($struktur, $parents, $ebene, $p)
    {        
        if(!$parents && !$ebene)
            $this->output = ''; 
            
        $menue = $p['menue']; 
            
        if($menue)
        { 
			$elQ = self::$fksdb->query("SELECT id, mid, url, sprachen, ziel, power, klasse FROM ".SQLPRE."menus WHERE struktur = '".$struktur."' AND menue = '".$menue."' AND mid = '".$parents."' AND sprachen LIKE '%\"".self::$fks->getLanguage(true)."\"%' ORDER BY sort"); 
        } 
        else 
		{
			$elQ = self::$fksdb->query("SELECT id, titel, klasse, url, neues_fenster, sprachen, rollen, rollen_fehler, noseo, element FROM ".SQLPRE."elements WHERE struktur = '".$struktur."' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) AND (".($p['dclasses']?"klasse != '' OR":"")." sprachen LIKE '%\"".self::$fks->getLanguage(true)."\"%') ".$sn_sql." AND element = '".$parents."' AND no_navi = '0' ORDER BY sort ".$p['order']); 
        }
		
        if(self::$fksdb->count($elQ)) 
        {
            $extra_ul_class = 'parent-'.$parents.' depth-'.$ebene.' '.($menue?'menu':'navigation');
                
            if($p['view'] != 'flat')
                $this->output .= '<ul '.($ebene > 0?'class':'id').'="'.$p['id'].($ebene > 0?'_'.$ebene.' '.$extra_ul_class:'').'"'.(!$ebene?' class="'.trim($p['class'].' '.$extra_ul_class).'"':'').'>';
            elseif($p['view'] == 'flat' && (!$parents && !$ebene))
                $this->output .= '<'.($p['html_tag']?$p['html_tag']:'div').($p['id']?' id="'.$p['id'].'"':'').' class="'.trim($p['class'].' '.$extra_ul_class).'"'.'>';
        }
        
        $count_ebene = 0;
            
        while($el = self::$fksdb->fetch($elQ))
        {
            $extra_class = '';
            $dclass_dok = false;
            
			if($menue)
			{
				$mq = $el;
				unset($el);
				
				$msp = self::$base->fixedUnserialize($mq->sprachen);
				
				$href = $mq->url;
				$type = (Strings::strExists('{s-', $href)?'i':'e');
				$type = (Strings::strExists('{d-', $href)?'d':$type);
				$type = (Strings::strExists('mailto:', $href)?'m':$type);
				if($type == 'i')
				{
					preg_match('~{s-(.*)}~Uis', $href, $intern); 
					preg_match('~{s-(.*)_(.*)}~Uis', $href, $dok_intern);  
					
					$el = self::$fksdb->fetch("SELECT id, titel, klasse, url, neues_fenster, sprachen, rollen, rollen_fehler, noseo, element FROM ".SQLPRE."elements WHERE id = '".$intern[1]."' AND struktur = '".$struktur."' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) LIMIT 1"); 
					if(!$el) continue;
					
					if(!$dok_intern[2])
					{
						$sp = self::$base->fixedUnserialize($el->sprachen);
						$href = (self::$fks->getHomeElement('id') == $el->id?self::$fks->getDomain().self::$fks->getLanguage(false, '/'):self::$fks->getDomain().self::$fks->getLanguage(false, '/').$el->id.'/'.self::$base->auto_slug($sp[self::$fks->getLanguage(true)]).'/');
                        
                        $extra_class .= ' intern intern-'.$el->id;
                        $dclass_dok = false;
					}
					else
					{
						$dQ = self::$fksdb->fetch("SELECT id, sprachenfelder FROM ".SQLPRE."documents WHERE id = '".$dok_intern[2]."' AND papierkorb = '0' LIMIT 1");
						if(!$dQ) continue;
                        $dsp = self::$base->fixedUnserialize($dQ->sprachenfelder);
						
						$href = self::$fks->getDomain().self::$fks->getLanguage(false, '/').$el->id.'/'.$dQ->id.'/'.self::$base->auto_slug($dsp[self::$fks->getLanguage(true)]).'/';
                        
                        $extra_class .= ' intern intern-'.$el->id.' dclass dclass-'.$dQ->id;
                        $dclass_dok = true;
					}
				}
				elseif($type == 'd')
				{
					preg_match('~{d-(.*)}~Uis', $href, $dateien); 
					
					$dQ = self::$fksdb->fetch("SELECT id, titel, last_type FROM ".SQLPRE."files WHERE id = '".$dateien[1]."' AND papierkorb = '0' LIMIT 1");
					if(!$dQ) continue;
						
					$href = self::$fks->getDomain().'files/'.$dQ->id.'/'.self::$base->slug($dQ->titel).'.'.$dQ->last_type;
                    
                    $extra_class .= ' file file-'.$dQ->id.' file-type-'.$dQ->last_type;
				}
                elseif($type == 'e')
                {
                    $extra_class .= ' extern';    
                }
                elseif($type == 'm')
                {
                    $extra_class .= ' email';    
                }
			}
		
			if($el)
			{
				$r = self::$base->fixedUnserialize($el->rollen);
				if(!is_array($r)) $r = array();
				if(count($r) && self::$user->getRole() != 1 && !in_array(self::$user->getRole(), $r) && $el->rollen_fehler == 1)
					continue;
            
				$sp = self::$base->fixedUnserialize($el->sprachen);
			}
            
            $cf = array();
            $cf['bl'] = $p['before_li'];
            $cf['ba'] = $p['before_a'];
            $cf['bt'] = $p['before_t'];
            $cf['al'] = $p['after_li'];
            $cf['aa'] = $p['after_a'];
            $cf['at'] = $p['after_t']; 
            
            if($count_ebene == 0 && !$this->counter)
            {
                $cf['bl'] = (isset($p['before_first_li'])?$p['before_first_li']:$cf['bl']);
                $cf['ba'] = (isset($p['before_first_a'])?$p['before_first_a']:$cf['ba']);
                $cf['bt'] = (isset($p['before_first_t'])?$p['before_first_t']:$cf['bt']);
                $cf['al'] = (isset($p['after_first_li'])?$p['after_first_li']:$cf['al']);
                $cf['aa'] = (isset($p['after_first_a'])?$p['after_first_a']:$cf['aa']);
                $cf['at'] = (isset($p['after_first_t'])?$p['after_first_t']:$cf['at']);
            }
            elseif($count_ebene + 1 == self::$fksdb->count($elQ) && !$ebene)
            { 
                $cf['bl'] = (isset($p['before_last_li'])?$p['before_last_li']:$cf['bl']);
                $cf['ba'] = (isset($p['before_last_a'])?$p['before_last_a']:$cf['ba']);
                $cf['bt'] = (isset($p['before_last_t'])?$p['before_last_t']:$cf['bt']);
                $cf['al'] = (isset($p['after_last_li'])?$p['after_last_li']:$cf['al']);
                $cf['aa'] = (isset($p['after_last_a'])?$p['after_last_a']:$cf['aa']);
                $cf['at'] = (isset($p['after_last_t'])?$p['after_last_t']:$cf['at']);
            } 
            
            if(is_array($p['felder']))
            {
                foreach($p['felder'] as $f => $v)
                {
                    $i = $v['before'].$sp[self::$fks->getLanguage(true)][$f].$v['after']; 
                    $pos = $v['position']; 
                    
                    if($pos == 'before_li') $cf['bl'] .= $i; 
                    elseif($pos == 'before_a') $cf['ba'] .= $i;    
                    elseif($pos == 'before_text') $cf['bt'] .= $i;    
                    elseif($pos == 'after_li') $cf['al'] = $i.$cf['al'];     
                    elseif($pos == 'after_a') $cf['aa'] = $i.$cf['aa'];     
                    elseif($pos == 'after_text') $cf['at'] = $i.$cf['at'];         
                }
            }
            
            $count_ebene ++;
            
			if($el)
			{
				$elC = self::$fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE struktur = '".$struktur."' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) AND element = '".$el->id."' AND id = '".self::$fks->getElementID()."' LIMIT 1"); 
			}
            
            $add_class = '';
            if(self::$fks->getElementID() == $el->id || self::$fksdb->count($elC) || $el->trennlinie || $count_ebene == 1 || $count_ebene == self::$fksdb->count($elQ) || $mq->klasse)
            {
                $add_class = trim(
                ($count_ebene == 1?' '.$p['first_class']:'').
                ($count_ebene == self::$fksdb->count($elQ)?' '.$p['last_class']:'').
                (self::$fks->getElementID() == $el->id && ((!$dclass_dok && !self::$fks->getDclassDocumentID()) || (self::$fks->getDclassDocumentID() == $dQ->id && $dclass_dok))?' '.$p['active_class']:'').
                (self::$fksdb->count($elC)?' '.$p['active_class_child']:'').
                ($mq->klasse?' '.$mq->klasse:'')
                );
            } 
            
			if($menue)
			{
				$url = $href;
			}
			else
			{
				$url = (self::$fks->getHomeElement('id') == $el->id?self::$fks->getDomain().self::$fks->getLanguage(false, '/'):self::$fks->getDomain().self::$fks->getLanguage(false, '/').$el->id.'/'.self::$base->auto_slug($sp[self::$fks->getLanguage(true)]).'/');
			}
            
            $is_klasse = false;
            $real_loop = false;
               
			if($el->klasse)
			{ 
                if($p['dclasses'] && !$menue)
                {
    				$is_klasse = true; 
    			
    				$dQ = self::$fksdb->query("SELECT id, titel, sprachenfelder FROM ".SQLPRE."documents WHERE klasse = '".$el->klasse."' AND papierkorb = '0' AND timestamp_freigegeben != '0' AND gesperrt = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) ORDER BY timestamp DESC");
    				while($d = self::$fksdb->fetch($dQ))
    				{ 
    					$dsp = self::$base->fixedUnserialize($d->sprachenfelder);
                        $real_loop = true;
    		
    					$url = self::$fks->getDomain().self::$fks->getLanguage(false, '/').$el->element.'/'.$d->id.'/'.self::$base->auto_slug($dsp[self::$fks->getLanguage(true)]).'/';
                        
                        $extra_class = ' intern intern-'.$el->id.' dclass dclass-'.$dQ->id;
    					$add_class = ' class="'.(self::$fks->getElementID() == $el->element && $this->dclass == $d->id?trim($extra_class.' '.$p['active_class']):trim($extra_class)).'"';
    					
    					if($p['view'] == 'list')
    					{ 
    						$this->output .= '
    						'.$cf['bl'].'
    						<li'.$add_class.'>
    							'.$cf['ba'].'
    							<a href="'.$url.'"'.($el->neues_fenster?' target="_blank"':'').'>
    								'.$cf['bt'].$dsp[self::$fks->getLanguage(true)]['titel'].$cf['at'].'
    							</a>
    							'.$cf['aa'].'
    						</li>
    						'.$cf['al'];  
    					}
    					elseif($p['view'] == 'flat')
    					{ 
    						$this->output .= '
    						'.$cf['ba'].'
    						<a'.$add_class.' href="'.$url.'"'.($el->neues_fenster?' target="_blank"':'').'>
    							'.$cf['bt'].$dsp[self::$fks->getLanguage(true)]['titel'].$cf['at'].'
    						</a>
    						'.$cf['aa'];  
    					}
    				}
                }
			}
			else
			{
				$titel = ($menue?$msp[self::$fks->getLanguage(true)]:$sp[self::$fks->getLanguage(true)]['titel']); 
				$nf = ($menue?$mq->ziel:$el->neues_fenster); 
				$power = ($menue?$mq->power:$el->noseo); 
                $real_loop = true;
                
                if(!$menue)
                    $extra_class = ' intern intern-'.$el->id;
                $extra_class .= ' target-'.($nf?'blank':'self').' power-'.($power?'nofollow':'follow').' depth-'.$ebene.' item-'.($count_ebene);    
                
                $add_class = ' class="'.trim($add_class.' '.$extra_class).'"';
				
				if($p['view'] == 'list')
				{
					$this->output .= '
					'.$cf['bl'].'
					<li'.$add_class.'>
						'.$cf['ba'].'
						<a href="'.$url.'"'.($nf?' target="_blank"':'').($power?' rel="nofollow"':'').'>
							'.$cf['bt'].$titel.$cf['at'].'
						</a>
						'.$cf['aa'];
				}  
				elseif($p['view'] == 'flat')
				{
					$this->output .= '
					'.$cf['ba'].'
					<a'.$add_class.' href="'.$url.'"'.($nf?' target="_blank"':'').($power?' rel="nofollow"':'').'>
						'.$cf['bt'].$titel.$cf['at'].'
					</a>
					'.$cf['aa'];  
				}
			}
			
			$this->counter ++;
			
			if($menue)
				$el = $mq;
            
                        
            $ebene ++;
            if(!$p['max_ebene'] || $ebene < $p['max_ebene'])
                $this->level($struktur, $el->id, $ebene, $p);
            $ebene --;
            
			if($p['view'] == 'list' && !$is_klasse && $real_loop)
				$this->output .= '</li>'.$cf['al'];    
        }
        
        if(self::$fksdb->count($elQ)) 
        {
            if($p['view'] == 'list')
                $this->output .= '</ul>';
            elseif($p['view'] == 'flat' && (!$parents && !$ebene))
                $this->output .= '</'.($p['html_tag']?$p['html_tag']:'div').'>';
        }
        
        return $this->output; 
    }
    
    function get($p = array())
    {
        /// PARAMETER STANDARDWERTE ///
        $standard = array(
            'id' => '',
            'class' => '',
            'menue' => '',
            'max_ebene' => 0,
            'sub_navigation' => false,
            'parent' => 0,
            'view' => 'list',
            'html_tag' => '',
            'active_class' => 'active',
            'active_class_child' =>  'active_child',
            'first_class' => 'first',
            'last_class' => 'last',
            'order' => 'ASC',
            'dclasses' => false
        );
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////
        
        /*
        before_a
        before_li
        before_text
        after_a
        after_li
        after_text
         
        before_first_a
        before_first_li
        before_first_text
        after_first_a
        after_first_li
        after_first_text 
        
        before_last_a
        before_last_li
        before_last_text
        after_last_a
        after_last_li
        after_last_text 
        */
        
        if(!$p['menue'])
            $p['menue'] = $p['menu'];
        if(!$p['max_ebene'])
            $p['max_ebene'] = $p['depth'];
        if(!$p['felder'])
            $p['felder'] = $p['custom_fields'];
        
        $this->output = '';
        $this->counter = 0;
        
		if($p['menue'])
			$parents = 0;
		else
			$parents = ($p['sub_navigation']?self::$fks->getID():($p['parent']?$p['parent']:0)); 
			
        $this->output = $this->level(self::$fks->getStructure('id'), $parents, 0, $p);
        
        if(!$p['menue'] && $p['sub_navigation'] && $p['max_ebene'] && !$this->output)
        {
            $new_ele = self::$fks->getElement();
            for($d=0; $d<$p['max_ebene']; $d++)
            {
                if($new_ele->element)
                {
                    $new_ele = self::$fksdb->fetch("SELECT element, id FROM ".SQLPRE."elements WHERE id='".$new_ele->element."' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) LIMIT 1");
                    $this->output = $this->level(self::$fks->getStructure('id'), $new_ele->id, 0, $p);
                }
            }
        }
            
        return $this->output;
    }
    
    public function write($p = array())
    {  
        echo $this->get($p);    
    }


    public function getLanguageSwitcher($to_root = false, $flag_size = 32, $hide_when_single = true)
    {
        if($hide_when_single && self::$api->getActiveLanguagesCount() < 2)
            return '';

        $rtn = '';
        $langs = self::$api->getActiveLanguages();

        foreach($langs as $code => $lang)
        {
            $url = ($to_root?$lang['url']:self::$fks->getLanguageLink($code));
            $flag = '<img src="'.($flag_size <= 16?$lang['flag'][16]:$lang['flag'][32]).'" width="'.$flag_size.'" height="'.$flag_size.'" alt="'.$lang['code'].'" />';

            $rtn .= '<li><a rel="alternate" hreflang="'.$code.'" href="'.$url.'" class="fks-language-switcher-link fks-language-switcher-link-'.$code.' fks-language-switcher-link-'.(self::$fks->getLanguage(true) == $code?'active':'inactive').'">'.$flag.'</a></li>';
        }

        return '<ul class="fks-language-switcher">'.$rtn.'</ul>';
    }

    public function writeLanguageSwitcher($to_root = false, $flag_size = 32, $hide_when_single = true)
    {
        echo $this->getLanguageSwitcher($to_root, $flag_size, $hide_when_single);
    }
}
?>
<?php
class Block_67 extends BlockBasic
{
    private $elements = array(), $structures = array();
    
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $sm = self::$base->fixedUnserialize($this->html);
        if(!is_array($sm)) $sm = array(); 
        
        $no_ele = $sm['el'];
        if(!is_array($no_ele)) 
            $no_ele = array(); 
            
        $elementsQ = self::$fksdb->query("SELECT id, titel, klasse, url, sprachen, rollen, element FROM ".SQLPRE."elements WHERE struktur = '".self::$fks->getStructureID()."' AND nositemap = '0' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) AND (klasse != '' OR sprachen LIKE '%\"".self::$fks->getLanguage(true)."\"%') ORDER BY sort ASC");
        while($e = self::$fksdb->fetch($elementsQ))
            $this->elements[$e->element][] = $e;
            
        $output = $this->loop('', 0, $no_ele, $sm['rollen']);
        
        if(!$output && count($this->elements))
        {
            foreach($this->elements as $eid => $ev)
            {
                if(!$eid)
                    continue;
                    
                $output = $this->loop($output, $eid, $no_ele, $sm['rollen'], true);
            }
        }
        
        $this->html = $this->executeCallback($output);
        
        return '<ul'.$this->add_css.' class="'.trim('fks_sitemap '.$this->add_class).'"'.($atr['id']?' id="'.$atr['id'].'"':'').'>'.$this->html.'</ul>';
    }

    public function getHookAttributes()
    {
        $self = array(
            'elements' => $this->structures,
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
    
    private function loop($rtn, $parents, $no_ele, $roles, $flat = false)
    {                    
        $current = $this->elements[$parents]; 
        if(!is_array($current)) $current = array();
            
        $count_items = 0;
            
        foreach($current as $el)
        {
            if(in_array($el->id, $no_ele))
                continue;
            
            $r = self::$base->fixedUnserialize($el->rollen);
            if(!is_array($r)) $r = array();
            if(!$roles && count($r) >= 1 && !in_array(self::$user->getRole(), $r))
                continue;
            
            if(count($current) && $parents && !$count_items) 
                $rtn .= '<ul>';
                
            $count_items ++;
            
            if(!$el->klasse)
            {
                $sp = self::$base->fixedUnserialize($el->sprachen);
                $url = ($el->url?$el->url:self::$fks->getDomain().self::$fks->getLanguage(false, '/').$el->id.'/'.self::$base->auto_slug($sp[self::$fks->getLanguage(true)]).'/');

                $this->structures[$parents][] = array(
                    'element_id' => $el->id
                );
                
                $rtn .= '<li><a href="'.$url.'"'.($el->neues_fenster?' target="_blank"':'').'>'.$sp[self::$fks->getLanguage(true)]['titel'].'</a>';  
                if(!$flat)
                    $rtn = $this->loop($rtn, $el->id, $no_ele, $roles);
                $rtn .= '</li>';  
            }
            else
            {
                $dQ = self::$fksdb->query("SELECT id, titel, sprachenfelder FROM ".SQLPRE."documents WHERE klasse = '".$el->klasse."' AND papierkorb = '0' AND timestamp_freigegeben != '0' AND gesperrt = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) ORDER BY timestamp DESC");
				while($d = self::$fksdb->fetch($dQ))
				{
					$dsp = self::$base->fixedUnserialize($d->sprachenfelder);
					$url = self::$fks->getDomain().self::$fks->getLanguage(false, '/').$el->element.'/'.$d->id.'/'.self::$base->auto_slug($dsp[self::$fks->getLanguage(true)]).'/';

                    $this->structures[$parents][] = array(
                        'element_id' => $el->id,
                        'document_id' => $d->id
                    );
                      
                    $rtn .= '<li><a href="'.$url.'"'.($el->neues_fenster?' target="_blank"':'').'>'.$dsp[self::$fks->getLanguage(true)]['titel'].'</a></li>'; 
                } 
            }
        }
        
        if(count($current) && $parents && $count_items) 
            $rtn .= '</ul>';
        
        return $rtn; 
    }
}   
?>
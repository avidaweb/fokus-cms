<?php
class Block_1050 extends BlockBasic
{
    private $element = 0, $documents = array();

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $seri = self::$base->fixedUnserialize($this->html);
        $doks = explode(',', $seri['sort']);
        if(!is_array($doks))
            return ''; 
            
        $related = str_replace('.php', '', $seri['related']);
        $owndk = str_replace('.php', '', $seri['owndk']);
        
        $sqlstring = "";
        foreach($doks as $s)
        {
            if(intval($s) > 0)
                $sqlstring .= ($sqlstring?" OR ":"")." id = '".$s."' ";
        }
        
        $ka = self::$fksdb->rows("SELECT id, titel, klasse, sprachenfelder FROM ".SQLPRE."documents WHERE klasse = '".$related."' AND papierkorb = '0' ".(!$this->preview?" AND gesperrt = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."'))":"")." ".($sqlstring?"AND (".$sqlstring.")":"AND id = '-1'")); 
        
        $fk = self::$base->open_dklasse(ROOT.'content/dklassen/'.$owndk.'.php'); 
        $relatedarray = $fk['relation'][$this->attr['name']]; 
        if(!is_array($relatedarray))
            return '';
            
        $result = preg_match_all('@:(.*)\):@iU', $relatedarray['content'], $subpattern);  
        
        $loop = 0;
        $html = ''; 
        
        if($relatedarray['element'] == 'search')
            $search_element = self::$fksdb->data("SELECT element FROM ".SQLPRE."elements WHERE klasse = '".$related."' ORDER BY element, sort LIMIT 1", "element");
            
        foreach($doks as $did)
        {
            $doc = $ka[$did];
            if(!$doc)
                continue;

            $this->documents[] = $doc->id;
                
            $dspr = self::$base->fixedUnserialize($doc->sprachenfelder);
            
            $dv = self::$fksdb->fetch("SELECT klasse_inhalt FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."' AND language = '".self::$fks->getLanguage(true)."' AND aktiv = '1' LIMIT 1");
            
            if(!$dv)
                continue;
            
            $ki = self::$base->fixedUnserialize($dv->klasse_inhalt);
            $this->block_types = array();
            
            $loop ++; 
        
            $this->dclass_elements = array();
            $attr2['d-id'] = $doc->id;
            $attr2['d-titel'] = $doc->titel; 
            $attr2['is_teaser'] = true; 
            if(is_int($relatedarray['element']))
            {
                $this->element = intval($relatedarray['element']);

                $attr2['s-id'] = intval($relatedarray['element']);
                $attr2['no_doc'] = false;
            }
            if($relatedarray['element'] == 'search')
            {
                $this->element = intval($search_element);

                $attr2['s-id'] = intval($search_element);
                $attr2['no_doc'] = false;
            }
            else
            {
                $attr2['no_doc'] = true;
            }
            $this->dclass_elements[0] = $attr2; 
            
            foreach($subpattern[1] as $s1 => $s2)
            {
                $x = explode('(', $s2);   
                
                if(in_array($x[0], self::$base->getBlocks('dclass')) || in_array($x[0], self::$base->getDclassMethods()))
                { 
                    $block_type = (in_array($x[0], self::$base->getBlocks('dclass'))?array_search($x[0], self::$base->getBlocks('dclass')):array_search($x[0], self::$base->getDclassMethods())); 
                    
                    $this->block_types[$block_type] += 1; 
                    
                    $attr = self::$base->get_attributes($x[1]);
        
                    if($attr['name'])
                        $bid = self::$base->slug($attr['name']);
                    else
                        $bid = $block_type.'_'.$this->block_types[$block_type];
                        
                    $attr['is_teaser'] = true; 
                    
                    $b = null;
                    $b->id = $bid;
                    $b->type = $block_type;
                    
                    if(in_array($x[0], self::$base->getBlocks('dclass')))
                    {   
                        $b->html = $ki[$bid]['html'];
                        $b->bild = $ki[$bid]['bild'];	
                        $b->bildid = $ki[$bid]['bildid'];	
                        $b->bildw = $ki[$bid]['bildw'];	
                        $b->bildh = $ki[$bid]['bildh'];	
                        $b->bildwt = $ki[$bid]['bildwt'];	
                        $b->bildp = $ki[$bid]['bildp'];	
                        $b->bildt = $ki[$bid]['bildt'];	
                        $b->bild_extern = $ki[$bid]['bild_extern'];	
                        $b->teaser = $ki[$bid]['teaser'];
                    }
                
                    $this->dclass_elements[$bid] = array($attr, $block_type, $b); 
                }
            } 
             
            $html .= preg_replace('@:([A-Za-z]+)\((.*)\):@iUe', '$this->getTeaserLoop(\'\1\', \'\2\')', $relatedarray['content']);  
        }

        if($html)
            $html = $this->executeCallback($html);
        $this->html = $html;

        return $relatedarray['before'].$this->html.$relatedarray['after'];
    }

    public function getHookAttributes()
    {
        $self = array(
            'element' => $this->element,
            'documents' => $this->documents,
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
}   
?>
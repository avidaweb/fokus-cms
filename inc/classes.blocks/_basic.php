<?php
class BlockBasic
{
    protected static $static, $base, $api, $fksdb, $user, $suite, $trans, $fks, $content; 
    
    protected $block = null, $column_width = 0, $html_before = '', $html_after = '', $attr = array(), $document = null;
    protected $html = '';
    protected $add_css = '', $add_class = '', $classes = array();
    protected $dclass_elements = array(), $dclass_values = array(), $block_types = array();
    
    function __construct($static = array(), $dynamic = array())
    {
        self::$static = $static;        
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
        self::$fks = $static['fks'];
        self::$content = $static['content'];
        
        $this->block = $dynamic['block'];
        $this->column_width = $dynamic['column_width'];
        $this->html_before = $dynamic['html_before'];
        $this->html_after = $dynamic['html_after'];
        $this->attr = $dynamic['attr'];
        $this->document = $dynamic['document'];
        
        // get html
        $this->html = $this->block->html;
        
        $this->initStyles();
    }
    
    
    public function get()
    {
        return '';    
    }
    
    
    protected function getHookStandardAttributes()
    {
        return array(
            'attributes' => $this->attr,
            'trans' => self::$trans,
            'api' => self::$api,
            'fks' => self::$fks,
            'content' => self::$content,
            'id' => $this->block->id,
            'type' => self::$base->getBlockByID($this->block->type, 'en'), 
            'class' => $this->add_class,
            'classes' => $this->classes,
            'html_before' => $this->html_before,
            'html_after' => $this->html_after, 
            'column_width' => $this->column_width,
            'document' => array(
                'id' => $this->document->id,
                'dclass' => $this->document->klasse,
                'slot' => $this->document->slot
            )
        );    
    }
    
    public function getHookAttributes()
    {
        return $this->getHookStandardAttributes();
    }
    
    
    protected function executeCallback($content  = '')
    {
        $cb = $this->attr['callback'];
        
        if(!$content)
            $content = $this->html;
            
        if(!$cb)
            return $content;
            
        if(Strings::strExists('|', $cb))
        {
            $cba = explode('|', $cb, 2);
            
            if(!is_callable($cba))
                return $content; 
            
            return call_user_func_array($cba, array($content, $this->getHookAttributes()));
        }
        else
        {
            if(!is_callable($cb))
                return $content;

            return call_user_func($cb, $content, $this->getHookAttributes());
        }
        
        return $content;
    }
    
    
    protected function initStyles()
    {
        if($this->block->css_klasse)
        {
            $this->add_class = trim($this->block->css_klasse);
            $this->classes = Strings::explodeCheck(' ', $this->add_class);
        }
        
        if($this->block->css)
        {
            $border = array('', 'solid', 'dashed', 'dotted');
            $align = array('inherit', 'left', 'right', 'center', 'justify');
            
            if($this->block->padding)
            {
                $abst = explode('_', $this->block->padding);
                $paddingZ = $abst[0];
                $marginZ = $abst[1];

                if($paddingZ)
                {
                    $paddingA = explode('|', $paddingZ);
                    $padding[0] = $paddingA[0]; 
                    $padding[1] = $paddingA[2]; 
                    $padding[2] = $paddingA[1]; 
                    $padding[3] = $paddingA[3]; 
                }
                if($marginZ)
                {
                    $marginA = explode('|', $marginZ);
                    $margin[0] = $marginA[0]; 
                    $margin[1] = $marginA[2]; 
                    $margin[2] = $marginA[1]; 
                    $margin[3] = $marginA[3]; 
                }
            }
            
            $this->add_css = ' style="'.
            ($this->block->color?'color:#'.$this->block->color.' !important; ':''). 
            ($this->block->font?'font-size:'.$this->block->font.'px !important; ':''). 
            ($this->block->bgcolor?'background-color:#'.$this->block->bgcolor.' !important; ':'').   
            ($this->block->border?'border:1px '.$border[$this->block->border].' #'.$this->block->bordercolor.' !important; ':'').   
            (is_array($padding)?'padding:'.$padding[0].'px '.$padding[1].'px '.$padding[2].'px '.$padding[3].'px !important; ':'').   
            (is_array($margin)?'margin:'.$margin[0].'px '.$margin[1].'px '.$margin[2].'px '.$margin[3].'px !important; ':'').  
            ($this->block->align?'text-align:'.$align[$this->block->align].' !important; ':''). 
            ($this->block->spalten > 1?'-moz-column-count: '.$this->block->spalten.'; -webkit-column-count: '.$this->block->spalten.'; column-count: '.$this->block->spalten.'; ':''). 
            '"'; 
        } 
        
        if($this->attr['class'])
            $this->add_class = trim($this->add_class.' '.$this->attr['class']); 
    }
    
    protected function initAttributes($html)
    {      
        if($this->attr['zeichen'])
        {
            $html = Strings::cut($html, $this->attr['zeichen'], '...', true);
        }
        if($this->attr['weiterlesen'] && $this->attr['s-id'])
        {
            if(!$this->attr['no_doc'])
                $html = $html.' <a href="'.$this->getURL($this->attr['s-id'], $this->attr['d-id']).'">'.$this->attr['weiterlesen'].'</a>';
            else
                $html = $html.' <a href="'.$this->getURL($this->attr['s-id']).'">'.$this->attr['weiterlesen'].'</a>';
        }
        if($this->attr['before'])
        {
            $html = $this->attr['before'].$html;
        }
        if($this->attr['after'])
        {
            $html = $html.$this->attr['after'];
        }
        if($this->attr['link'] == 'true' && $this->attr['s-id'] && $this->block->type < 30)
        {
            if(!$this->attr['no_doc'])
                $html = '<a href="'.$this->getURL($this->attr['s-id'], $this->attr['d-id']).'">'.$html.'</a>';
            else
                $html = '<a href="'.$this->getURL($this->attr['s-id']).'">'.$html.'</a>';
        }
        
        return $html;
    }
    
    
    protected function tidyText($html)
    {
        return $this->trimBr($this->tidyCode(htmlspecialchars_decode($html))); 
    }
    
    protected function tidyCode($html)
    {
        $search[] = '&nbsp;';                           $replace[] = ' ';
        $search[] = '<div>';                            $replace[] = '';
        $search[] = '</div>';                           $replace[] = '<br />';
        $search[] = ' & ';                              $replace[] = ' &amp; ';
        $search[] = '<b>';                              $replace[] = '<strong>';
        $search[] = '</b>';                             $replace[] = '</strong>';
        $search[] = '<br>';                             $replace[] = '<br />';
        $search[] = '<i>';                              $replace[] = '<em>';
        $search[] = '</i>';                             $replace[] = '</em>';
        
        return str_replace($search, $replace, $html);   
    }
    
    protected function trimBr($html)
    {
        return trim(preg_replace("
          ~
            (
              \s* | <br\s*/>
            )*$
         ~x", "", $html));
    }
    
    
    protected function buildInternLinks($html)
    {
        if(Strings::strExists('d-', $html))
        {
            $html = preg_replace('~{d-([0-9]+)}~Uise', '$this->setDownloadLink(\'\\1\')', $html);
            $html = preg_replace('~&#37;7Bd-([0-9]+)&#37;~Uise', '$this->setDownloadLink(\'\\1\')', $html);
        }
        
        if(Strings::strExists('s-', $html))
        {
            $html = preg_replace('~{s-([0-9]+)_([0-9]+)}~Uise', '$this->getURL(\'\\1\', \'\\2\')', $html);
            $html = preg_replace('~%Bs-([0-9]+)_([0-9]+)%7D~Uise', '$this->getURL(\'\\1\', \'\\2\')', $html);
            $html = preg_replace('~&#37;7Bs-([0-9]+)_([0-9]+)&#37;7D~Uise', '$this->getURL(\'\\1\', \'\\2\')', $html);
            
            $html = preg_replace('~{s-([0-9]+)}~Uise', '$this->getURL(\'\\1\')', $html);
            $html = preg_replace('~%7Bs-([0-9]+)%7D~Uise', '$this->getURL(\'\\1\')', $html);
            $html = preg_replace('~&#37;7Bs-([0-9]+)&#37;7D~Uise', '$this->getURL(\'\\1\')', $html);
        }
        
        return $this->repareLinks($html);    
    }
    
    
    protected function getURL($id, $dok = 0)
    { 
        if(!$dok)
        {
            if($id != self::$fks->getHomeElement('id'))
            {
                return self::$api->getElementUrl($id, self::$fks->getLanguage(true));
            }
            else
            {
                return self::$fks->getRoot();
            }
        }
        else
        {
            return self::$api->getDclassUrl($id, $dok, self::$fks->getLanguage(true));
        }
    }
    
    protected function setDownloadLink($id)
    {
        $dQ = self::$fksdb->fetch("SELECT id, titel, last_type FROM ".SQLPRE."files WHERE id = '".$id."' AND papierkorb = '0' LIMIT 1");
        if(!$dQ) return '';
            
        return self::$fks->getDomain().'files/'.$dQ->id.'/'.self::$base->slug($dQ->titel).'.'.$dQ->last_type;
    }
    
    private function repareLinks($html)
    {
        $html = preg_replace_callback('!href="(.*)"!', array($this, 'repareLinksLoop'), $html);
        return $html;    
    }
    
    private function repareLinksLoop($hit)
    {
        $result = $hit[1];
        if(!Strings::strExists('://', $result) && !Strings::strExists('http', $result) && !Strings::strExists('mailto', $result))
            $result = 'http://'.$result;
        
        return 'href="'.$result.'"';
    }
    
    
    protected function getGhostMeta($tag)
    {
        if(self::$fks->isGhost() && !$this->attr['is_teaser'])
            return ' data-type="'.$tag.'" data-id="'.$this->block->id.'" data-dkname="'.self::$base->slug($this->attr['name']).'" data-blockindex="'.$this->attr['blockindex'].'" data-ibid="'.$this->attr['ibid'].'" rel="editable"';
        return '';
    }
    
    
    protected function getImageUrl($id, $w, $h, $title, $ending, $percent = 0, $column = 0)
    { 
        if($percent)
        { 
            $h = 0;
            $w = round($w * $column / 100, 0); 
        }
        
        $title = explode('.', $title);
        $end = self::$base->slug($title[0]).'.'.$ending;
        return self::$fks->getDomain().'img/'.$id.'-'.(is_numeric($w)?$w:0).'-'.(is_numeric($h)?$h:0).'-'.$end;        
    }
    
    
    protected function getTeaserLoop($tf, $ta)
    {       
        if(!in_array($tf, self::$base->getBlocks('dclass')) && !in_array($tf, self::$base->getDclassMethods()))
            return '';
        
        $block_type = (in_array($tf, self::$base->getBlocks('dclass'))?array_search($tf, self::$base->getBlocks('dclass')):array_search($tf, self::$base->getDclassMethods())); 
                            
        $attr = self::$base->get_attributes($ta);
        
        $attr['s-id'] = $this->dclass_elements[0]['s-id'];
        $attr['d-id'] = $this->dclass_elements[0]['d-id'];
        $attr['d-titel'] = $this->dclass_elements[0]['d-titel']; 
        $attr['no_doc'] = $this->dclass_elements[0]['no_doc']; 
        $attr['is_teaser'] = $this->dclass_elements[0]['is_teaser']; 
        $attr['is_element'] = $this->dclass_elements[0]['is_element']; 
        
        if(in_array($tf, self::$base->getBlocks('dclass'))) 
        {  
            $match = 0;
            $match_type = 0;
            foreach($this->dclass_elements as $d1 => $d2)
            {
                if($attr['name'] && $attr['name'] == $d2[0]['name'])
                { 
                    $match = $d1; 
                    $match_type = 1;
                    break;
                }
                elseif((!$attr['name'] || $attr['is_element']) && $block_type == $d2[1])
                {
                    $match = $d1;
                    break;
                }
            }
        
            if($match)
            {  
                if(!$match_type)
                    unset($this->dclass_elements[$match]);
                
                $b = $d2[2];
                $b->type = $block_type;
                
                if(!empty($b->html) || $b->bild)
                {
                    if($b->type < 24)
                    {
                        if($attr['html'] != 'true')
                            $b->html = htmlspecialchars(strip_tags(htmlspecialchars_decode($b->html)));
                        else
                            $b->html = htmlspecialchars(strip_tags(htmlspecialchars_decode($b->html), '<strong><em><a><br><br />'));
                    }
                    
                    $this->addDclassValue($b, $attr);
                    
                    return self::$content->parseHTML($b, 0, '', '', $attr);
                }
            }
            
            return '';
        }
        elseif(in_array($tf, self::$base->getDclassMethods())) 
        {   
            return self::$content->getDclassMethod($tf, $attr); 
        }
        
        return '';
    }
    
    protected function addDclassValue($b, $attr)
    {
        if($b->type >= 20 && $b->type != 1005)
            return false;
            
        $this->dclass_values[$b->id] = array(
            'name' => $attr['name'],
            'id' => $b->id,
            'type' => $b->type,
            'content' => $b->html
        );
    }
}   
?>
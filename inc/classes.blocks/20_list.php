<?php
class Block_20 extends BlockBasic
{
    private $kindof = 0, $elements = array();
    
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $this->html = $this->getElements();
        
        $tag = ($this->attr['tag']?
            $this->attr['tag']
            :
            ($this->kindof == 1?
                'ol'
                :
                'ul'
            )
        );
        
        if(!$this->html)
            return '';   
        
        $this->html = $this->executeCallback($this->html);
        
        $output = '<'.$tag.($this->add_class?' class="'.$this->add_class.'"':'').$this->add_css.''.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>'.$this->html_before.$this->html.$this->html_after.'</'.$tag.'>';
        
        return $output;  
    }

    public function getHookAttributes()
    {
        $self = array(
            'elements' => $this->elements,
            'listtype' => ($this->kindof == 1?'ol':'ul'),
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
    
    private function getElements()
    {
        $elements = self::$base->fixedUnserialize($this->html);
        if(!is_array($elements))
            return '';
            
        $this->kindof = intval($elements['kindof']);
        unset($elements['kindof']);

        $output = '';
        
        if(!count($elements))
            return '';

        foreach($elements as $e)
        {
            $e = $this->buildInternLinks($this->tidyCode(htmlspecialchars_decode($e)));

            if(!$e)
                continue;

            $this->elements[] = $e;
            $output .= '<li>'.$e.'</li>';
        }
        
        return $output;
    }
}   
?>
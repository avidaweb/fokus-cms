<?php
class Block_24 extends BlockBasic
{
    private $text = '';

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $this->text = $this->html;

        $this->html = htmlspecialchars_decode($this->html); 
        
        $this->html = $this->initAttributes($this->html); 
        $this->html = $this->executeCallback();
        
        $tag = ($this->attr['tag']?$this->attr['tag']:'div');     
            
        $output = '<'.$tag.' class="'.trim('fks_html '.$this->add_class).'"'.$this->add_css.''.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>'.$this->html_before.$this->html.$this->html_after.'</'.$tag.'>';
        
        return $output;  
    }

    public function getHookAttributes()
    {
        $self = array(
            'text' => $this->text,
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
}   
?>
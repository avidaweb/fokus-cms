<?php
class Block_5 extends BlockBasic
{
    private $values = array();
    
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $this->values = self::$base->db_to_array($this->html);
        
        $implode = ($this->attr['implode']?$this->attr['implode']:', ');
        $this->html = (count($this->values)?implode($implode, $this->values):'');
        
        $this->html = $this->initAttributes($this->html);    
        $this->html = $this->executeCallback();
            
        $output = $this->html_before.$this->html.$this->html_after;
        
        return $output;  
    }

    public function getHookAttributes()
    {
        $self = array(
            'values' => $this->values,
            'values_count' => count($this->values),
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
}   
?>
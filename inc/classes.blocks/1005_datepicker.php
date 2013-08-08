<?php
class Block_1005 extends BlockBasic
{
    private $time = 0;

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $this->html = $this->tidyText($this->html);   
        
        $this->html = $this->initAttributes($this->html); 
        
        setlocale(LC_TIME, ($this->attr['laendercode']?$this->attr['laendercode']:'de_DE'));
        $format = ($this->attr['format']?$this->attr['format']:'%d.%m.%Y'); 
        
        $values = explode('.', $this->html);
        if(is_array($values))
        {
            $this->time = mktime(0, 0, 0, $values[1], $values[0], $values[2]);
            
            if($this->attr['format'] == 'timestamp')
                $this->html = $this->time;
            else
                $this->html = strftime($format, $this->time);
        } 
        
        $this->html = $this->executeCallback();

        return $this->html_before.$this->html.$this->html_after;
    }

    public function getHookAttributes()
    {
        $self = array(
            'timestamp' => $this->time,
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
}   
?>
<?php
class Block_100 extends BlockBasic
{
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $ext = self::$api->getBlock($this->block->extb);
        if(!$ext)
            return '';
            
        if(!$ext->getName())
            return '';
            
        $econ = self::$base->db_to_array($this->block->extb_content);
        if(!is_array($econ)) $econ = array(); 
        $data = (object)$econ;
        
        $html = '';
        $cfunc = $ext->getContent();
        if(is_callable($cfunc))
        {
            if(is_array($cfunc))
            {
                $html = call_user_func_array($cfunc, array($data, $this->getHookAttributes()));
            }
            else
            {
                $html = call_user_func($cfunc, $data, $this->getHookAttributes());
            }
        }
        
        $html = $this->executeCallback($html);
        
        $html_tag = ($ext->getHtmlTag()?$ext->getHtmlTag():'div');
        
        return '<'.$html_tag.$this->add_css.' class="'.trim($ext->getCssClass.' '.$this->add_class).'"'.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>'.$html.'</'.$html_tag.'>';
    }
}   
?>
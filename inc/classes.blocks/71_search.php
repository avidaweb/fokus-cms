<?php
class Block_71 extends BlockBasic
{
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $html = self::$fks->getSearchForm();
        
        $html = $this->executeCallback($html);
        
        return '<div'.$this->add_css.' class="'.trim('fks_search_content '.$this->add_class).'"'.($atr['id']?' id="'.$atr['id'].'"':'').'>'.$html.'</div>';     
    }
}   
?>
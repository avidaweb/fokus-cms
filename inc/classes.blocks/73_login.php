<?php
class Block_73 extends BlockBasic
{
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $c = self::$base->fixedUnserialize($this->html);
        if(!is_array($c)) $c = array(); 
        $c = (object)$c; 
          
        $html = self::$fks->getLoginForm(array(
            'label_name_text' => $c->label_name_text,
            'label_password_text' => $c->label_password_text,
            'label_name_position' => $c->label_name_position,
            'label_password_position' => $c->label_password_position,
            'label_name_br' => ($c->label_name_br?true:false),
            'label_password_br' => ($c->label_password_br?true:false),
            'submit_text' => $c->submit_text,
            'submit_logout_text' => $c->submit_logout_text,
            'success_forwarding' => $c->success_forwarding,
            'success_text' => $c->success_text,
            'success_logout_text' => $c->success_logout_text,
            'success_logout_forwarding' => $c->success_logout_forwarding
        ));
        
        $html = $this->executeCallback($html);
        
        return '<div'.$this->add_css.' class="'.trim('fks_login_content '.$this->add_class).'"'.($atr['id']?' id="'.$atr['id'].'"':'').'>'.$html.'</div>'; 
    }
}   
?>
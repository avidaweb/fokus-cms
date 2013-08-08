<?php
class Block_22 extends BlockBasic
{
    private $elements = array();

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $this->html = $this->getElements();
           
        if(!$this->html)
            return '';

        $this->html = $this->executeCallback($this->html);
        
        $output = '<table'.($this->add_class?' class="'.$this->add_class.'"':'').$this->add_css.''.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>'.$this->html_before.$this->html.$this->html_after.'</table>';
        
        return $output;  
    }

    public function getHookAttributes()
    {
        $self = array(
            'elements' => $this->elements,
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
    
    private function getElements()
    {
        $elements = self::$base->db_to_array($this->html); 
        $output = '';
        
        $ox = intval($elements['x']);
        $oy = intval($elements['y']);
        $v = self::$base->db_to_array($elements['value']);
        
        if(count($v))
        {
            for($y = 1; $y <= $oy; $y ++)
            {
                $output .= '<tr class="r'.$y.'">';
                for($x = 1; $x <= $ox; $x ++)
                {
                    $e = $v[$y][$x];
                    if(get_magic_quotes_gpc())
                        $e = stripslashes($e);
                    $e = $this->buildInternLinks($this->tidyCode(htmlspecialchars_decode($e)));

                    $this->elements[$y][$x] = $e;
                    $output .= '<td class="d'.$x.'">'.$e.'</td>';
                }
                $output .= '</tr>';
            }  
        }
        
        return $output;
    }
}   
?>
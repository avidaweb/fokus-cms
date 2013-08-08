<?php
class Block_47 extends BlockBasic
{
    private $url = '', $link_url = '';

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $sm = self::$base->fixedUnserialize($this->html);
        if(!is_array($sm))
            return '';

        $this->link_url = $sm['url'];
        $this->url = 'https://chart.googleapis.com/chart?cht=qr&amp;chl='.$sm['url'].'/&amp;chs='.$sm['size'].'x'.$sm['size'].'&amp;chld=M|0';
        
        $html = '
        <a href="'.$sm['url'].'">
            <img src="'.$this->url.'" alt="'.$sm['url'].'" />
        </a>';
        
        $this->html = $this->executeCallback($html);
        
        return '<div'.$this->add_css.' class="'.trim('fks_qr_code '.$this->add_class).'"'.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>'.$this->html_before.$this->html.$this->html_after.'</div>';
    }

    public function getHookAttributes()
    {
        $self = array(
            'url' => $this->url,
            'link_url' => $this->link_url,
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
}   
?>
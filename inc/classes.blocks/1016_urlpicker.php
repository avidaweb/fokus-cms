<?php
class Block_1016 extends BlockBasic
{
    private $url = '';

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $c = (object)self::$base->db_to_array($this->html);
        if(!count($c))
            return '';

        $this->html = $this->buildUrl($c);
        $this->url = $this->html;

        $this->html = $this->executeCallback();
            
        return $this->html_before.$this->html.$this->html_after;
    }

    public function getHookAttributes()
    {
        $self = array(
            'url' => $this->url,
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }

    protected function buildUrl($c)
    {
        if($c->element)
            return $this->getURL($c->element, $c->document);

        if($c->file)
            return $this->setDownloadLink($c->file);

        if($c->email)
            return 'mailto:'.$c->email;

        return $c->href;
    }
}   
?>
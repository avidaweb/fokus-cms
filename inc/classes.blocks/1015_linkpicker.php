<?php
class Block_1015 extends BlockBasic
{
    private $url = '', $text = '';

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $c = (object)self::$base->db_to_array($this->html);
        if(!count($c))
            return '';

        $this->html = $this->buildLink($c);
        $this->html = $this->executeCallback();
        
        return $this->html;
    }

    public function getHookAttributes()
    {
        $self = array(
            'url' => $this->url,
            'text' => $this->text,
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }

    protected function buildLink($c)
    {
        $url = $this->buildUrl($c);
        $classes = trim($c->classes.' '.$this->attr['class']);
        $class = ($classes?' class="'.$classes.'"':'');
        $id = ($this->attr['id']?' id="'.$this->attr['id'].'"':'');
        $target = ($c->target == 'blank'?' target="_blank"':'');
        $power = ($c->power == 'nofollow'?' rel="nofollow"':'');
        $title = ($c->title?' title="'.$c->title.'"':'');

        $this->url = $url;
        $this->text = $c->text;

        return '<a href="'.$url.'"'.$class.$id.$target.$power.$title.'>'.$this->html_before.$c->text.$this->html_after.'</a>';
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
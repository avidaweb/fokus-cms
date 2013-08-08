<?php
class Block_30 extends BlockBasic
{
    protected $file_id = 0, $file_version = 0, $url = '', $link_url = '', $align = '', $width = 0, $height = 0;
    
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $tag = 'div';
        if($this->attr['tag']) 
            $tag = $this->attr['tag'];  
        
        $this->html = $this->initAttributes($this->html); 
        
        $stack = self::$fksdb->fetch("SELECT id, kat, titel FROM ".SQLPRE."files WHERE id = '".$this->block->bildid."' AND papierkorb = '0' LIMIT 1");
        if(!$stack && !$this->block->bild_extern)
            return '';
            
        $file = self::$fksdb->fetch("SELECT id, file, type FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");
        if(!$file && !$this->block->bild_extern)
            return '';

        $this->file_id = $stack->id;
        $this->file_version = $file->id;
        
        if($this->attr['align'])
            $this->block->bildp = ($this->attr['align'] == 'center'?2:($this->attr['align'] == 'right'?1:($this->attr['align'] == 'block'?3:0)));
        $this->align = $this->attr['align'];
        
        if($this->attr['dimension'])
            $this->block->bildwt = ($this->attr['dimension'] == 'original'?2:($this->attr['dimension'] == 'percent'?1:0));
        
        $was_original = false;
        if($this->block->bildwt == 2)
        {
            $this->block->bildwt = 0;
            $this->block->bildw = $file->width;
            $this->block->bildh = $file->height;
            
            $was_original = true;
        }
        
        if($this->attr['width'])
        {
            $this->block->bildw = $this->attr['width'];
            if(!$this->attr['height'])
                $this->block->bildh = 0;
        }
        if($this->attr['height'])
        {
            $this->block->bildh = $this->attr['height'];
            if(!$this->attr['width'])
                $this->block->bildw = 0;
        }
        
        $this->width = $this->block->bildw;
        $this->height = $this->block->bildh;
                    
        $bstyle = ($was_original || !$this->block->bildw?'':'width: '.$this->block->bildw.($this->block->bildwt == 0?'px':'%').';');
        $bstyle .= ($this->block->bildp == 0?' float:left;':'');
        $bstyle .= ($this->block->bildp == 1?' float:right;':'');
        $bstyle .= ($this->block->bildp == 2?' margin:0px auto; display:block;':'');
        $this->add_class .= ($this->block->bildp == 0?' align_left':'');
        $this->add_class .= ($this->block->bildp == 1?' align_right':'');
        $this->add_class .= ($this->block->bildp == 2?' align_center':'');
        
        $this->url = 
        (!$this->block->bild_extern?
            $this->getImageUrl($stack->id, $this->block->bildw, $this->block->bildh, $stack->titel, $file->type, $this->block->bildwt, $this->column_width)
        :
            $this->block->bild_extern
        );
        
        $filelink = self::$base->db_to_array($this->block->bildt);
        $linkstart = '';
        $linkend = '';
        
        if($this->block->bildt && $filelink['href'])
        {
            $this->link_url = $this->buildInternLinks($filelink['href']);
            
            $linkstart = '<a href="'.$this->link_url.'"'.($filelink['ziel'] == 1?' target="_blank"':'').($filelink['power'] == 1?' rel="nofollow"':'').($filelink['titel']?' title="'.$filelink['titel'].'"':'').($filelink['klasse']?' class="'.$filelink['klasse'].'"':'').'>';
            $linkend = '</a>';
        }
        elseif($this->attr['link'] == 'true')
        {
            $lclass = ($this->attr['link_class']?' class="'.$this->attr['link_class'].'"':'');
            $arel = ($this->attr['rel']?' rel="'.$this->attr['rel'].'"':'');
                
            if($this->attr['d-id'] && !$this->attr['no_doc'])
                $this->link_url = $this->getURL($this->attr['s-id'], $this->attr['d-id']);
            elseif($this->attr['s-id'])
                $this->link_url = $this->getURL($this->attr['s-id']);
            elseif($this->block->bild_extern)
                $this->link_url = $this->block->bild_extern;
            else
                $this->link_url = $this->getImageUrl($stack->id, 0, 0, $stack->titel, $file->type, $this->block->bildwt, $this->column_width);

            $linkstart = '<a'.$lclass.$arel.' href="'.$this->link_url.'">';
            $linkend = '</a>';
        }
        
        $html = '
        '.(!$this->attr['no_wrapper']?'<'.$tag.''.$this->add_css.' class="'.trim('fks_picture '.$this->add_class).'"'.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>':'').'
            '.$linkstart.'
            <img src="'.$this->url.'" alt="'.($this->attr['alt']?$this->attr['alt']:$stack->titel).'"'.($this->attr['title']?' title="'.$this->attr['title'].'"':'').' style="'.trim($bstyle).'"'.($this->attr['img_class']?' class="'.trim($this->attr['img_class']).'"':'').' />
            '.$linkend.'
        '.(!$this->attr['no_wrapper']?'</'.$tag.'>':'');


        $this->html = $this->executeCallback($html);
        
        return $this->html;
    }
    
    public function getHookAttributes()
    {
        $new = array(
            'html' => $this->html,
            'file_id' => $this->file_id,
            'file_version' => $this->file_version,
            'url' => $this->url,
            'link_url' => $this->link_url,
            'align' => $this->align,
            'width' => $this->width,
            'height' => $this->height,
            'attr' => $this->attr
        );
        
        return array_merge($new, $this->getHookStandardAttributes());    
    }
}   
?>
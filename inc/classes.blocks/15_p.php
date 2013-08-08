<?php
class Block_15 extends BlockBasic
{
    private $text = '', $image = 0;

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $this->text = $this->html;

        $this->html = $this->tidyText($this->html);
        $this->html = $this->buildInternLinks($this->html); 
        
        $this->html = $this->initAttributes($this->html); 
        
        $tag = ($this->attr['tag']?$this->attr['tag']:'p');  
        
        $html = $this->getImage().$this->html;
        $html = $this->executeCallback($html);   
            
        $output = '<'.$tag.$this->getGhostMeta($tag).($this->add_class?' class="'.$this->add_class.'"':'').$this->add_css.''.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>'.$this->html_before.$html.$this->html_after.'</'.$tag.'>';
        
        return $output;  
    }

    public function getHookAttributes()
    {
        $self = array(
            'text' => $this->text,
            'html' => $this->html,
            'image' => $this->image
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
    
    private function getImage()
    {
        if($this->block->bild != 1 || self::$fks->isGhost())
            return '';
            
        $stack = self::$fksdb->fetch("SELECT id, kat, titel FROM ".SQLPRE."files WHERE id = '".$this->block->bildid."' AND papierkorb = '0' LIMIT 1");
        if(!$stack)
            return '';
            
        $file = self::$fksdb->fetch("SELECT file, type FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");
        if(!$file)
            return '';

        $this->image = $stack->id;
    
        $was_original = false;
        if($this->block->bildwt == 2)
        {
            $this->block->bildwt = 0;
            $this->block->bildw = $file->width;
            $this->block->bildh = $file->height;
            
            $was_original = true;
        }
        
        $bstyle = ($was_original?'':'width: '.$this->block->bildw.($this->block->bildwt == 0?'px':'%').';');
        $bstyle .= ($this->block->bildp == 0?' float:left;':'');
        $bstyle .= ($this->block->bildp == 1?' float:right;':'');
        $bstyle .= ($this->block->bildp == 2?' display:block;':'');
        $add_img_class = ($this->block->bildp == 0?' class="align_left"':'');
        $add_img_class = ($this->block->bildp == 1?' class="align_right"':$add_img_class);
        $add_img_class = ($this->block->bildp == 2?' class="align_center"':$add_img_class);
        
        $filelink = self::$base->db_to_array($this->block->bildt);
        if($this->block->bildt && $filelink['href'])
        {
            $fileurl = $this->buildInternLinks($filelink['href']);
            
            $linkstart = '<a href="'.$fileurl.'"'.($filelink['ziel'] == 1?' target="_blank"':'').($filelink['power'] == 1?' rel="nofollow"':'').($filelink['titel']?' title="'.$filelink['titel'].'"':'').($filelink['klasse']?' class="'.$filelink['klasse'].'"':'').'>';
            $linkend = '</a>';
        }
        
        $image = $this->getImageUrl($stack->id, ($this->block->bildw?$this->block->bildw:0), ($this->block->bildh?$this->block->bildh:0), $stack->titel, $file->type, $this->block->bildwt, $this->column_width);
        
        return $linkstart.'<img src="'.$image.'" alt="'.$stack->titel.'" style="'.$bstyle.'"'.$add_img_class.' />'.$linkend;   
            
    }
}   
?>
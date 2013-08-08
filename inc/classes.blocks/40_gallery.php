<?php
class Block_40 extends BlockBasic
{
    private $ids = array(), $meta = array(), $images = array(), $options = array();
    
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $p = self::$content->getGalleryOptions($this->classes);
        $this->options = $p;
        
        $tag = 'div';
        if($this->attr['tag']) 
            $tag = $this->attr['tag']; 
        
        $images = self::$base->fixedUnserialize($this->block->html);
        if(!is_array($images)) 
        {
            $images = array();
        }
        else
        {
            if($this->attr['before'])
                $p['html_before'] = $this->attr['before'].$p['html_before'];
            if($this->attr['after'])
                $p['html_after'] .= $this->attr['after'];
        }
        
        if(count($this->attr))
        {
            if(isset($this->attr['show_title'])) $p['show_title'] = ($this->attr['show_title'] == 'true'?true:false); 
            if(isset($this->attr['show_desc'])) $p['show_desc'] = ($this->attr['show_desc'] == 'true'?true:false);  
            if(isset($this->attr['img_width'])) $p['img_width'] = $this->attr['img_width']; 
            if(isset($this->attr['img_height'])) $p['img_height'] = $this->attr['img_height'];
            if(isset($this->attr['img_link_width'])) $p['img_link_width'] = $this->attr['img_link_width'];  
            if(isset($this->attr['img_link_height'])) $p['img_link_height'] = $this->attr['img_link_height'];  
            if(isset($this->attr['img_in_a_rows'])) $p['img_in_a_rows'] = $this->attr['img_in_a_rows']; 
            if(isset($this->attr['limit'])) $p['limit'] = $this->attr['limit']; 
            if(isset($this->attr['link'])) $p['link'] = $this->attr['link'];  
            if(isset($this->attr['link_class'])) $p['link_class'] = $this->attr['link_class']; 
            if(isset($this->attr['galerie_class'])) $p['galerie_class'] = $this->attr['galerie_class'];   
            if(isset($this->attr['container_class'])) $p['container_class'] = $this->attr['container_class']; 
            if(isset($this->attr['img_class'])) $p['img_class'] = $this->attr['img_class']; 
            if(isset($this->attr['view'])) $p['view'] = $this->attr['view']; 
            if(isset($this->attr['html_before'])) $p['html_before'] = $this->attr['html_before']; 
            if(isset($this->attr['html_after'])) $p['html_after'] = $this->attr['html_after']; 
        }
        
        $percent = 0;
        if(Strings::strExists('%', $p['img_width'], false))
            $percent = 1;
        
        $percent_link = 0;
        if(Strings::strExists('%', $p['img_link_width'], false))
            $percent_link = 1;
         
        $p['img_width'] = intval($p['img_width']);
        $p['img_height'] = intval($p['img_height']);
        $p['img_link_width'] = intval($p['img_link_width']);
        $p['img_link_height'] = intval($p['img_link_height']);
        
        $count_pictures = 0;
        $output = '';
        $sql_stack = "";
    
        foreach($images as $b1 => $b2)
        {
            $dirsql = "";
            if($b2['dir'])
            {
                $dirsql = $this->getDirectory($b2['id']);
                $nid = "(id = '-99' ".$dirsql.")";    
            }
            else
            {
                $nid = "id = '".$b2['id']."'";
                
                $this->ids[] = $b2['id'];
                $this->meta[] = array(
                    'name' => $b2['name'],
                    'desc' => $b2['desc'],
                    'hidev' => $b2['hidev']
                );
            }
            
            $sql_stack .= (!$sql_stack?"":" OR ")."(".$nid.") ";
        }
        
        if(!$sql_stack)
            return '';
            
        $stackQ = self::$fksdb->rows("SELECT id, kat, titel FROM ".SQLPRE."files WHERE ".$sql_stack." AND papierkorb = '0'");
        
        foreach($this->ids as $k => $id)
        {
            $stack = $stackQ[$id];
            if(!$stack)
                continue;
                
            $meta = $this->meta[$k];
            
            if($p['limit'] > 0 && $count_pictures >= $p['limit'])
                break;
                
            $imageS = self::$fksdb->fetch("SELECT file, type, width, height FROM ".SQLPRE."file_versions WHERE stack = '".$stack->id."' ORDER BY timestamp DESC LIMIT 1");
            if(!$imageS)
                continue;
                
            $count_pictures ++;
            
            $img_link_width = ($p['img_link_width'] > $imageS->width?$imageS->width:$p['img_link_width']);
            $img_link_height = ($p['img_link_height'] > $imageS->height?$imageS->height:$p['img_link_height']);
            
            $image = $this->getImageUrl($stack->id, $p['img_width'], $p['img_height'], $stack->titel, $imageS->type, $percent, $this->column_width);
            $image_path = $this->getImageUrl($stack->id, $img_link_width, $img_link_height, $stack->titel, $imageS->type, $percent_link);
            
            $link = '<a href="'.$image_path.'" rel="lightbox[galerie_'.$this->block->id.']" title="'.$meta['name'].($meta['name'] && $meta['desc']?' - ':'').$meta['desc'].'"'.($p['link_class']?' class="'.$p['link_class'].'"':'').'>';
            
            if($percent)
            {
                $p['img_width'] = round($p['img_width'] * $this->column_width / 100, 0); 
                $p['img_height'] = 0;
            }
                
            $imagesrc = '<img'.($p['img_width']?' width="'.$p['img_width'].'"':'').($p['img_height']?' height="'.$p['img_height'].'"':'').' src="'.$image.'" alt="'.$meta['desc'].'" class="'.$p['img_class'].'" />';
            
            $item = '';
            $hatr = array(
                'image_id' => $stack->id,
                'image_url' => $image_path,
                'image_type' => $imageS->type,
                'image_width' => $imageS->width,
                'image_height' => $imageS->height,
                'thumbnail' => $imagesrc,
                'thumbnail_url' => $image,
                'thumbnail_width' => $p['img_width'],
                'thumbnail_height' => $p['img_height'],
                'thumbnail_hidden' => ($meta['hidev']?true:false),
                'title' => $meta['name'],
                'description' => $meta['desc'],
                'count' => $count_pictures
            );
            $this->images[] = $hatr;
            $hatr['attributes'] = $p;
            
            // Hook: before_gallery_item
            $item .= self::$api->execute_hook('before_gallery_item', $hatr, true);
            
            $item .= '
            <'.($p['view'] == 'flat'?'div':'li').' class="'.$p['container_class'].'"'.($meta['hidev']?' style="display:none;"':'').'>
                '.($p['link'] == 'all'?$link:'').'
                    '.($p['link'] == 'img'?$link:'').''.$imagesrc.''.($p['link'] == 'img'?'</a>':'').'
                    '.($p['link'] == 'text'?'</a>':'').'
                        '.($p['link'] == 'title'?$link:'').''.($p['show_title']?'<strong>'.$meta['name'].'</strong>':'').''.($p['link'] == 'title'?'</a>':'').' 
                        '.($p['link'] == 'desc'?$link:'').''.($p['show_desc']?'<span>'.$meta['desc'].'</span>':'').''.($p['link'] == 'desc'?'</a>':'').'
                        '.($p['link'] != 'all' && $p['link'] != 'img' && $p['link'] != 'text' && $p['link'] != 'title' && $p['link'] != 'desc' && $p['link'] != 'none' && $p['link'] != 'no' && $p['link'] != ''?$link.$p['link'].'</a>':'').'
                    '.($p['link'] == 'text'?'</a>':'').'
                '.($p['link'] == 'all'?'</a>':'').'
            </'.($p['view'] == 'flat'?'div':'li').'>';
            
            // Hook: after_gallery_item
            $item = self::$api->execute_filter('gallery_item', $item, $hatr);
            $item .= self::$api->execute_hook('after_gallery_item', $hatr, true);
            
            $output .= $item;
        }
        
        $this->html = $this->executeCallback($output);
        
        $html = $p['html_before'].'<'.($p['view'] == 'flat'?$tag:'ul').''.$this->add_css.' class="'.trim($p['galerie_class'].' '.$this->add_class).'"'.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>'.$this->html.'</'.($p['view'] == 'flat'?$tag:'ul').'>'.$p['html_after'];
        return $html;
    }

    public function getHookAttributes()
    {
        $new = array(
            'html' => $this->html,
            'image_ids' => $this->ids,
            'images' => $this->images,
            'options' => $this->options
        );

        return array_merge($new, $this->getHookStandardAttributes());
    }
    
    private function getDirectory($did, $dirsql = "")
    {
        $stackQ = self::$fksdb->query("SELECT id, isdir, titel, beschr FROM ".SQLPRE."files WHERE dir = '".$did."' AND papierkorb = '0' ORDER BY isdir");
        while($stack = self::$fksdb->fetch($stackQ))
        { 
            if($stack->isdir)
            {
                $dirsql .= $this->getDirectory($stack->id, $dirsql);
            }
            else 
            {
                $dirsql .= " OR id = '".$stack->id."' ";
                
                $this->ids[] = $stack->id;
                $this->meta[] = array(
                    'name' => $stack->titel,
                    'desc' => $stack->beschr,
                    'hidev' => false
                );
            }    
        }
        
        return $dirsql;
    }
}   
?>
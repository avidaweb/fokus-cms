<?php
class Block_36 extends BlockBasic
{
    protected $hoster = '', $hoster_id = '', $url = '', $align = '', $width = 0, $height = 0;

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
        
        if($this->attr['align'])
            $this->block->bildp = ($this->attr['align'] == 'center'?2:($this->attr['align'] == 'right'?1:($this->attr['align'] == 'block'?3:0)));
        $this->align = $this->attr['align'];
        
        if($this->attr['dimension'])
            $this->block->bildwt = ($this->attr['dimension'] == 'percent'?1:0);
        
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
            
        $style = '';
        $style .= ($this->block->bildp == 0?' float:left;':'');
        $style .= ($this->block->bildp == 1?' float:right;':'');
        $style .= ($this->block->bildp == 2?' margin:0px auto; display:block;':'');
        
        $video = '';
        
        if($this->block->bildwt)
        {
            if(!$this->column_width)
            {
                $generated_height = 350;
            }
            else
            { 
                $video_px = $this->column_width * $this->block->bildw / 100; 
                $generated_height = round($video_px * 0.7, 0); 
            }  
        }

        $this->width = $this->block->bildw;
        $this->height = $generated_height;
        
        if($this->block->bild_extern)
        {
            if(Strings::strExists('youtube.', $this->block->bild_extern))
                $video = $this->getYoutube($style, $generated_height);
            elseif(Strings::strExists('vimeo.', $this->block->bild_extern))
                $video = $this->getVimeo($style, $generated_height);
            elseif(Strings::strExists('myvideo.', $this->block->bild_extern))
                $video = $this->getMyVideo($style, $generated_height);
            elseif(Strings::strExists('dailymotion.', $this->block->bild_extern))
                $video = $this->getDalymotion($style, $generated_height);
            elseif(Strings::strExists('clipfish.', $this->block->bild_extern))
                $video = $this->getClipfish($style, $generated_height);
        }

        $this->html = $this->executeCallback($video);

        return '<'.$tag.''.$this->add_css.' class="'.trim('fks_video '.$this->add_class).'"'.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>'.$this->html.'</'.$tag.'>';
    }

    public function getHookAttributes()
    {
        $new = array(
            'html' => $this->html,
            'hoster' => $this->hoster,
            'hoster_id' => $this->hoster_id,
            'url' => $this->url,
            'align' => $this->align,
            'width' => $this->width,
            'height' => $this->height
        );

        return array_merge($new, $this->getHookStandardAttributes());
    }
    
    
    private function getYoutube($style = '', $generated_height = 0)
    {
        $url_string = parse_url($this->block->bild_extern, PHP_URL_QUERY);
        parse_str($url_string, $args);
        $vid_id = $args['v'];

        $this->hoster = 'youtube';
        $this->hoster_id = $vid_id;
        $this->url = 'http://www.youtube.com/embed/'.$vid_id;
        
        return '<iframe class="fks_youtube_player" style="width:'.$this->block->bildw.($this->block->bildwt?'%':'px').';height:'.(!$this->block->bildh?$generated_height:$this->block->bildh).'px;'.$style.'" src="http://www.youtube.com/embed/'.$vid_id.'?wmode=transparent&amp;rel=0&amp;hd=1"></iframe>';
    }
    
    private function getVimeo($style = '', $generated_height = 0)
    {
        $result = preg_match('~vimeo\.com\/([0-9]{1,10})~is', $this->block->bild_extern, $vimeo_id);

        $this->hoster = 'vimeo';
        $this->hoster_id = $vimeo_id[1];
        $this->url = 'http://player.vimeo.com/video/'.$vimeo_id[1];
                
        return '<iframe style="'.$style.'" src="http://player.vimeo.com/video/'.$vimeo_id[1].'" width="'.$this->block->bildw.($this->block->bildwt?'%':'').'" height="'.(!$this->block->bildh?$generated_height:$this->block->bildh).'"></iframe>';
    }
    
    private function getMyVideo($style = '', $generated_height = 0)
    {
        $result = preg_match('~myvideo\.de\/watch\/([0-9]*)\/~isU', $this->block->bild_extern, $myvideo_id);

        $this->hoster = 'myvideo';
        $this->hoster_id = $myvideo_id[1];
        $this->url = 'http://www.myvideo.de/movie/'.$myvideo_id[1];
                
        return '<object style="width:'.$this->block->bildw.($this->block->bildwt?'%':'px').';height:'.(!$this->block->bildh?$generated_height:$this->block->bildh).'px;" width="'.$this->block->bildw.($this->block->bildwt?'%':'').'" height="'.(!$this->block->bildh?$generated_height:$this->block->bildh).'"><param name="movie" value="http://www.myvideo.de/movie/'.$myvideo_id[1].'"></param><param name="AllowFullscreen" value="true"></param><param name="AllowScriptAccess" value="always"></param><embed src="http://www.myvideo.de/movie/'.$myvideo_id[1].'" width="'.$this->block->bildw.($this->block->bildwt?'%':'').'" height="'.(!$this->block->bildh?$generated_height:$this->block->bildh).'" type="application/x-shockwave-flash" allowscriptaccess="always" allowfullscreen="true"></embed></object>';
    }
    
    private function getDalymotion($style = '', $generated_height = 0)
    {
        $result = preg_match('~\/video\/([a-z0-9]*)_~isU', $this->block->bild_extern, $dm_id);

        $this->hoster = 'dalymotion';
        $this->hoster_id = $dm_id[1];
        $this->url = 'http://www.dailymotion.com/embed/video/'.$dm_id[1];
                
        return '<iframe width="'.$this->block->bildw.($this->block->bildwt?'%':'').'" height="'.(!$this->block->bildh?$generated_height:$this->block->bildh).'" src="http://www.dailymotion.com/embed/video/'.$dm_id[1].'"></iframe>';
    }
    
    private function getClipfish($style = '', $generated_height = 0)
    {
        $result = preg_match('~\/video\/([0-9]*)\/~isU', $this->block->bild_extern, $vid);

        $this->hoster = 'clipfish';
        $this->hoster_id = $vid[1];
        $this->url = 'http://www.clipfish.de/cfng/flash/clipfish_player_3.swf?as=0&amp;vid='.$vid[1];
                
        return '<object codebase="http://fpdownload.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=8,0,0,0" width="'.$this->block->bildw.($this->block->bildwt?'%':'').'" height="'.(!$this->block->bildh?$generated_height:$this->block->bildh).'"> <param name="allowScriptAccess" value="always" /> <param name="movie" value="http://www.clipfish.de/cfng/flash/clipfish_player_3.swf?as=0&amp;vid='.$vid[1].'&amp;r=1&amp;area=e&amp;c=ffffff" /> <param name="bgcolor" value="#ffffff" /> <param name="allowFullScreen" value="true" /> <embed src="http://www.clipfish.de/cfng/flash/clipfish_player_3.swf?as=0&amp;vid='.$vid[1].'&amp;r=1&amp;area=e&amp;c=990000" quality="high" bgcolor="#ffffff" width="'.$this->block->bildw.($this->block->bildwt?'%':'').'" height="'.(!$this->block->bildh?$generated_height:$this->block->bildh).'" name="player" align="middle" allowFullScreen="true" allowScriptAccess="always" type="application/x-shockwave-flash" pluginspage="http://www.macromedia.com/go/getflashplayer"></embed></object>';
    }
}   
?>
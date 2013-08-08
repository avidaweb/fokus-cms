<?php
class Block_52 extends BlockBasic
{
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $fo = self::$base->fixedUnserialize($this->html);
        if(!is_array($fo))
            return '';
            
        $p = self::$content->getFormOptions($this->classes);

        if(!is_numeric($this->block->id))
            $this->block->vid = $this->block->id;
        
        if($_POST['fokus_form_send'])
            $v = self::$base->vars('POST');
            
        if(!is_array($fo)) $fo = array();
        $fa = $fo['f'];
        if(!is_array($fa)) $fa = array();
        
        $labels_avaible = false;
        
        $faB = array();
        foreach($fa as $f_id => $f)
        {
            $faB[$f['bid']][$f_id]['bid'] = $f['bid'];
            $faB[$f['bid']][$f_id]['name'] = $f['name'];
            $faB[$f['bid']][$f_id]['type'] = $f['type'];
            $faB[$f['bid']][$f_id]['links'] = $f['links'];
            $faB[$f['bid']][$f_id]['opt'] = self::$base->db_to_array($f['opt']);
            
            if($f['links'])
                $labels_avaible = true;
        }   
        
        $event = '';
        if(count(self::$fks->getFormErrors()))
        {
            if(self::$fks->getFormErrors('fks_form_id') == $this->block->vid)
            {
                self::$fks->deleteFormError('fks_form_id');
                
                $event = '<ul class="fks_form_error" id="fks_form_'.$this->block->vid.'">';
                foreach(self::$fks->getFormErrors() as $e)
                {
                    $event .= '<li>'.$e.'</li>';
                }
                $event .= '</ul>';
            }
        }
        elseif($_GET['form_ok'])
        {
            $event = '<div class="fks_form_ok" id="fks_form_'.$this->block->vid.'">'.$p['success_text'].'</div>';
        }
        
        $slugs_used = array();
        $captcha_namen = array('fks_url', 'fks_link', 'fks_web', 'url', 'link', 'web', 'website', 'name', 'email', 'mail', 'vorname', 'nachname', 'twitter');
        $html = '';
        
        $input_file_used = false;
        
        if($p['view'] == 'flat')
            $html_tags = array('table' => 'div', 'tr' => 'p', 'td' => 'span');
        else
            $html_tags = array('table' => 'table', 'tr' => 'tr', 'td' => 'td');
                    
        foreach($faB as $b_id => $block)
        {
            $html .= '
            <'.$html_tags['tr'].'>';
                if(!$fo['strings'] || ($fo['strings'] == 1 && $labels_avaible))
                {
                    $linke_leiste = true;
                    
                    $html .= '
                    <'.$html_tags['td'].' class="form_left">';
                        foreach($block as $f_id => $f)
                        {
                            if($f['links'])
                            {
                                $html .= ($f['opt']['br'] == 1 || $f['opt']['br'] == 3?'<br />':'').
                                '<label>'.$f['name'].'</label>'
                                .($f['opt']['br'] == 2 || $f['opt']['br'] == 3?'<br />':'');
                            }
                        }
                    $html .= '
                    </'.$html_tags['td'].'>';
                }
                $html .= '
                <'.$html_tags['td'].' class="form_right">';
                    foreach($block as $f_id => $f)
                    { 
                        if(!$f['links'])
                        {
                            $html .= ($f['opt']['br'] == 1 || $f['opt']['br'] == 3?'<br />':'');
                            
                            if($f['type'] == 'string')
                            {
                                $html .= ' '.$f['name'].' ';
                            }
                            else
                            {
                                $urlname = self::$base->slug($f['name']);
                                
                                $style = '';
                                $add_style = '';
                                if($f['opt']['width'] || $f['opt']['height'])
                                {  
                                    if($f['opt']['width'])
                                        $add_style .= 'width:'.($f['opt']['width'] == 1000?'100%':$f['opt']['width'].'px').';'; 
                                    if($f['opt']['height'])
                                        $add_style .= 'height:'.($f['opt']['height'] * 16).'px;';
                                    $style = ' style="'.$add_style.'"';  
                                }
                                
                                while(in_array($urlname, $slugs_used))
                                    $urlname .= 'Z';
                                $slugs_used[] = $urlname;
                                
                                $is_error = (self::$fks->getFormErrors($urlname)?' class="fks_error"':'');
                                
                                $required = (self::$fks->isHTML5() && $f['opt']['pflicht']?true:false);
                                
                                if($f['type'] == 'text')
                                {
                                    $itype = 'text';
                                    if(self::$fks->isHTML5())
                                    { 
                                        if($f['opt']['pflicht_zeichen'] == 'email')
                                            $itype = 'email';
                                        if($f['opt']['pflicht_zeichen'] == 'url')
                                            $itype = 'url';
                                        if($f['opt']['pflicht_zeichen'] == 'int')
                                            $itype = 'number';
                                    }
                                    
                                    $html .= '<input'.$is_error.$style.' type="'.$itype.'"'.($required?' required':'').' name="'.$urlname.'" value="'.($v[$urlname]?$v[$urlname]:'').'" />';
                                }
                                elseif($f['type'] == 'textarea')
                                { 
                                    $html .= '<textarea'.$is_error.$style.' name="'.$urlname.'"'.($required?' required':'').'>'.($v[$urlname]?$v[$urlname]:'').'</textarea>';
                                }
                                elseif($f['type'] == 'checkbox')
                                {
                                    $html .= '<input'.$is_error.' type="checkbox" name="'.$urlname.'" value="true"'.(($f['opt']['checked'] && !$v) || $v[$urlname]?' checked="checked"':'').' />';
                                }
                                elseif($f['type'] == 'radio')
                                {
                                    $options = $f['opt']['auswahl'];
                                    if(!is_array($options))
                                        $options = array();
                                    
                                    $selections = 0;
                                    foreach($options as $a)
                                    {
                                        $label = '<label for="'.$urlname.'_'.$selections.'">'.$a.'</label>';
                                        
                                        $html .= ($f['opt']['radio_string'] == 0?$label.' ':'');
                                        $html .= '<input'.$is_error.' type="radio" id="'.$urlname.'_'.$selections.'" name="'.$urlname.'" value="'.$selections.'"'.($v[$urlname] == $selections || (!$v[$urlname] && !$selections)?' checked="checked"':'').' />';
                                        $html .= ($f['opt']['radio_string'] == 1?' '.$label:'');
                                        
                                        $selections ++;
                                        
                                        $html .= (!$f['opt']['radio_flat'] == 1 && $selections != count($options)?'<br />':'');
                                    }
                                }
                                elseif($f['type'] == 'select')
                                {
                                    $html .= '<select'.$is_error.$style.' name="'.$urlname.'"'.($required?' required':'').'>';
                                    
                                    $options = $f['opt']['auswahl'];
                                    if(!is_array($options))
                                        $options = array();
                                    
                                    $selections = 0;
                                    foreach($options as $a)
                                    {
                                        $html .= '<option value="'.$selections.'"'.($v[$urlname] == $selections || (!$v[$urlname] && !$selections)?' selected="selected"':'').'>'.$a.'</option>';
                                        $selections ++;
                                    }
                                    
                                    $html .= '</select>';                        
                                }
                                elseif($f['type'] == 'password')
                                {
                                    $html .= '<input'.$is_error.$style.' type="password"'.($required?' required':'').' name="'.$urlname.'" value="" />';                       
                                }
                                elseif($f['type'] == 'img')
                                {
                                    $html .= '<input'.$is_error.$style.' type="file" name="'.$urlname.'" accept="image/*"  />';     
                                    $input_file_used = true;                  
                                }
                            }
                            
                            $html .= ($f['opt']['br'] == 2 || $f['opt']['br'] == 3?'<br />':'');
                        }
                    }
                $html .= '
                </'.$html_tags['td'].'>
            </'.$html_tags['tr'].'>';    
        } 
        
        foreach($captcha_namen as $cn)
        {
            if(!in_array($cn, $slugs_used))
            {
                $cname = $cn;
                break;
            }
        }
        
        $html .= '
        <'.$html_tags['tr'].' class="form_submit">
            <'.$html_tags['td'].''.($linke_leiste && $p['view'] != 'flat'?' colspan="2"':'').'>
                <input type="submit" name="submit" value="'.($fo['submit']?$fo['submit']:'Abschicken').'"'.(self::$fks->isPreview()?' disabled':'').' />
                '.(self::$fks->isPreview()?'<p><small>'.self::$trans->__('Das Abschicken von Formularen ist in der Vorschau-Ansicht nicht möglich.').'</small></p>':'').'
            </'.$html_tags['td'].'>
        </'.$html_tags['tr'].'>';
        
        $output = 
        $event.($_GET['form_ok'] && $p['hide_after_submit']?'':'
        <form action="'.self::$fks->getURL().'#fks_form_'.$this->block->vid.'" method="post" '.$this->add_css.' class="'.trim('fks_form '.$this->add_class).'"'.(!$event?' id="fks_form_'.$this->block->vid.'"':'').($input_file_used?' enctype="multipart/form-data"':'').'>
            <'.$html_tags['table'].'>
                '.$html.'
            </'.$html_tags['table'].'>
            
            <input type="hidden" name="fokus_form_id" value="'.$this->block->vid.'" />
            <input type="hidden" name="fokus_form_send" value="'.time().'" />
            '.self::$api->getHashInput().'
            
            <input type="text" style="visibility:hidden; width:1px; height:1px;" name="'.$cname.'" value="" class="fks_check" />
            <input type="hidden" name="fokus_form_check" value="'.$cname.'" />
        </form>'); 
        
        $output = $this->executeCallback($output);
        
        return $output; 
    }
}   
?>
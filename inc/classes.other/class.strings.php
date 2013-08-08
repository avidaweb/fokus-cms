<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Strings 
{
    public static function cleanString($in)
    { 
        $find[] = 'â€œ';  // left side double smart quote
        $find[] = 'â€';  // right side double smart quote
        $find[] = 'â€˜';  // left side single smart quote
        $find[] = 'â€™';  // right side single smart quote
        $find[] = 'â€¦';  // elipsis
        $find[] = 'â€”';  // em dash
        $find[] = 'â€“';  // en dash
        $find[] = '%';  // en dash
        $find[] = '&nbsp;';  // 
        $find[] = '&amp;nbsp;';  // 
        
        $replace[] = '"';
        $replace[] = '"';
        $replace[] = "'";
        $replace[] = "'";
        $replace[] = "...";
        $replace[] = "-";
        $replace[] = "-";
        $replace[] = "&#37;";
        $replace[] = ' ';
        $replace[] = ' ';
        
        return str_replace($find, $replace, $in);
    } 
    
    public static function tidyHTML($html)
    {
        $search[] = '&nbsp;>';                          $replace[] = ' ';
        $search[] = '<div>';                            $replace[] = '';
        $search[] = '</div>';                           $replace[] = '<br />';
        $search[] = ' & ';                              $replace[] = ' &amp; ';
        $search[] = '<b>';                              $replace[] = '<strong>';
        $search[] = '</b>';                             $replace[] = '</strong>';
        $search[] = '<br>';                             $replace[] = '<br />';
        $search[] = '<i>';                              $replace[] = '<em>';
        $search[] = '</i>';                             $replace[] = '</em>';
        
        return str_replace($search, $replace, $html);   
    }
    
    public static function explodeCheck($delimiter, $content)
    {
        $res = array();
        $tmp = explode($delimiter, $content);
        foreach($tmp as $t)
        {
            $t = trim(strip_tags($t));
            if(!$t)
                continue;
                
            $res[] = $t;
        }
        return $res;
    }
    
    public static function removeBadHTML($s)
    {
        $s = str_replace('<span style="display: none;">&nbsp;</span>', ' ', $s);
        $s = str_replace('</p>', '<br />', $s);
        $s = strip_tags($s, '<strong><em><b><i><u><a><sup><sub><br><br /><span>');   
        $s = Strings::tidyHTML($s);
        return $s;
    }
    
    public static function removeDoubleSpace($string) 
    {
      return preg_replace('/\s{2,}/sm',' ',$string, PREG_SET_ORDER);  
    }
    
    public static function createID()
    {
        return md5(uniqid(mt_rand(), true));
    }
    
    public static function br2nl($str) 
    {
        return preg_replace("=<br(>|([\s/][^>]*)>)\r?\n?=i", "\n", $str);
    }
    
    public static function url2link($text)
    {
        $text = preg_replace("/([a-zA-Z]+:\/\/[a-z0-9\_\.\-]+"."[a-z]{2,6}[a-zA-Z0-9\/\*\-\_\?\&\%\=\,\+\.]+)/"," <a href=\"$1\" target=\"_blank\">$1</a>", $text);
        $text = preg_replace("/[^a-z]+[^:\/\/](www\."."[^\.]+[\w][\.|\/][a-zA-Z0-9\/\*\-\_\?\&\%\=\,\+\.]+)/",' <a href="\" target="\">$1</a>', $text);
        $text = preg_replace("/([\s|\,\>])([a-zA-Z][a-zA-Z0-9\_\.\-]*[a-z" . "A-Z]*\@[a-zA-Z][a-zA-Z0-9\_\.\-]*[a-zA-Z]{2,6})" . "([A-Za-z0-9\!\?\@\#\$\%\^\&\*\(\)\_\-\=\+]*)" . "([\s|\.|\,\<])/i", "$1<a href=\"mailto:$2$3\">$2</a>$4", $text);
     
        return $text;
    }
    
    public static function strExists($search, $string, $case_sensitive = true)
    {
        if(!$search && !$string)
            return true;
        if(!$search || !$string)
            return false;
            
        if(!$case_sensitive)
        {
            $search = strtolower($search);
            $string = strtolower($string);
        }
        
        return (strpos($string, $search) !== false?true:false);
    }
    
    public static function cutWords($string, $after = 50)
    {
        return wordwrap($string, $after, "<br />", 1);
    }
    
    public static function cutWordsByLength($str, $length = 0, $append = '...')
    {
        return implode(' ', array_slice(explode(' ', self::removeDoubleSpace($str)), 0, $length)).$append;
    }
    

    public static function truncate($str, $length = 100, $ending = '...', $in_the_word = true, $html = false)
    {
        if($html) 
        {
            if(strlen(preg_replace('/<.*?>/', '', $str)) <= $length) 
            {
                return $str;
            }
           
            preg_match_all('/(<.+?>)?([^<>]*)/s', $str, $lines, PREG_SET_ORDER);
   
            $total_length = strlen($ending);
            $open_tags = array();
            $truncate = '';
           
            foreach ($lines as $line_matchings) 
            {
                if (!empty($line_matchings[1])) 
                {
                    if (preg_match('/^<(\s*.+?\/\s*|\s*(img|br|input|hr|area|base|basefont|col|frame|isindex|link|meta|param)(\s.+?)?)>$/is', $line_matchings[1])) 
                    {
                    } 
                    else if (preg_match('/^<\s*\/([^\s]+?)\s*>$/s', $line_matchings[1], $tag_matchings)) 
                    {
                        $pos = array_search($tag_matchings[1], $open_tags);
                        if ($pos !== false) 
                        {
                            unset($open_tags[$pos]);
                        }
                    
                    } else if (preg_match('/^<\s*([^\s>!]+).*?>$/s', $line_matchings[1], $tag_matchings)) 
                    {
                        array_unshift($open_tags, strtolower($tag_matchings[1]));
                    }
                    
                    $truncate .= $line_matchings[1];
                }
               
                
                $content_length = strlen(preg_replace('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', ' ', $line_matchings[2]));
                if ($total_length+$content_length> $length) 
                {
                    
                    $left = $length - $total_length;
                    $entities_length = 0;
                    
                    if(preg_match_all('/&[0-9a-z]{2,8};|&#[0-9]{1,7};|&#x[0-9a-f]{1,6};/i', $line_matchings[2], $entities, PREG_OFFSET_CAPTURE)) 
                    {
                        foreach ($entities[0] as $entity) 
                        {
                            if($entity[1]+1-$entities_length <= $left) 
                            {
                                $left--;
                                $entities_length += strlen($entity[0]);
                            } 
                            else 
                            {
                                break;
                            }
                        }
                    }
                    $truncate .= substr($line_matchings[2], 0, $left + $entities_length);
                    break;
                } 
                else 
                {
                    $truncate .= $line_matchings[2];
                    $total_length += $content_length;
                }
               
                if($total_length>= $length) 
                {
                    break;
                }
            }
        } 
        else 
        {
            if(strlen($str) <= $length) 
            {
                return $str;
            } 
            else 
            {
                $truncate = substr($str, 0, $length - strlen($ending));
            }
        }
       
        if(!$in_the_word) 
        {
            $spacepos = strrpos($truncate, ' ');
            if (isset($spacepos)) 
            {
                $truncate = substr($truncate, 0, $spacepos);
            }
        }
       
        $truncate .= $ending;
       
        if($html) 
        {
            foreach ($open_tags as $tag) 
            {
                $truncate .= '</' . $tag . '>';
            }
        }
       
        return $truncate;
       
    }
    
    public static function cut($text, $len, $addmore = '...', $validateHTML = false)
    {
    	if(mb_strlen($text) <= $len)
            return $text;

        $replace = array('&uuml;', '&auml;', '&ouml;', '&Uuml;', '&Auml;', '&Ouml;', '&szlig;', '&quot;');
        $replace_back = array('ü', 'ä', 'ö', 'Ü', 'Ä', 'Ö', 'ß', '"');

        $text = str_replace($replace, $replace_back, $text);
        $text = mb_substr($text, 0, $len).$addmore;
        $text = str_replace($replace_back, $replace, $text);

        if($validateHTML)
        {
            $check = array('strong', 'em', 'b', 'i', 'a', 'sup');
            foreach($check as $c)
            {
                $c1 = intval(preg_match_all('/<'.$c.'/i', $text, $arrResult));
                $c2 = intval(preg_match_all('/<\/'.$c.'/i', $text, $arrResult));
                $diff = $c1 - $c2;
                if($diff > 0)
                {
                    for($x = 0; $x < $diff; $x++)
                        $text .= '</'.$c.'>';
                }
            }
        }
    	
    	return $text;
    }
    
    public static function cutSentences($str, $length = 0)
    {
        $cap = 1;
        $ret = '';
        
        for($x = 0; $x < strlen($str); $x++)
        {
            $letter = substr($str, $x, 1);
            
            if($letter == "." || $letter == "!" || $letter == "?")
                $cap ++;
            
            $ret .= $letter;
            
            if($cap > $length)
                break;
        }
        
        return $ret;
    }
    
    public static function countSentences($str)
    {
        $cap = 0;
        $capped = true;
        
        for($x = 0; $x < strlen($str); $x++)
        {
            $letter = substr($str, $x, 1); 
            
            if($letter == "." || $letter == "!" || $letter == "?")
            { 
                if(!$capped)
                    $cap ++;
                    
                $capped = true;
            }
            else
            {
                $capped = false;
            }
        }
        
        return $cap;
    }
}
?>
<?php
class Block_64 extends BlockBasic
{
    private $t = null, $element = null, $kids = array(), $dclasses = array();
    private $sort_element = '', $sort_dclass = '', $sort_order = 'ASC', $sort_count = 0;
    private $chunked = false, $pages = 0;
    private $tclass = array();
    private $css_class = '';
    private $result = array();
    
    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        $teaser = self::$base->fixedUnserialize($this->block->teaser);
        if(!is_array($teaser))
            return '';
        $this->t = (object)$teaser;

        if($this->attr['dclass_values'])
            $this->t->data = true;
        
        $this->element = self::$fksdb->fetch("SELECT id FROM ".SQLPRE."elements WHERE id = '".$this->t->element."' AND struktur = '".self::$fks->getStructureID()."' AND papierkorb = '0' LIMIT 1"); 
        if(!$this->element && !$this->t->data)
            return ''; 
            
        if(!is_array($this->t->include)) $this->t->include = array();
        if(!is_array($this->t->exclude)) $this->t->exclude = array();
            
        $this->kids = $this->getKids();
        if(!count($this->kids) && !$this->t->data)
            return ''; 
            
        $this->setSort(); 
        
        if($this->t->auflistung == 0)
            $result = $this->getSimple();
        elseif($this->t->auflistung == 1)
            $result = $this->getExtended();
        elseif($this->t->auflistung == 2)
            $result = $this->getClass();
        else
            return ''; 
            
        $this->t->total_number = count($result);
            
        $result = $this->sort($result); 
        $result = $this->limit($result);
        $result = $this->chunkPages($result);

        $this->result = $result;
        
        if($this->t->data)
            return $this->outputData($result);
            
        return $this->output($result);
    }
    
    
    private function outputData($result = array())
    {
        return array(
            'items' => array_values($result),
            'item_number' => count($result),
            'item_number_total' => $this->t->total_number,
            'pagination' => $this->getPagingData()
        );
    }
    
    private function output($result = array())
    {
        $output = '';
        
        if($this->t->auflistung == 0)
            $output .= '<ul>';
        if($this->tclass['before'])
            $output .= $this->tclass['before'];
            
        if(count($result))
            $output .= implode('', $result);
            
        if($this->t->auflistung == 0)
            $output .= '</ul>';    
        if($this->tclass['after'])
            $output .= $this->tclass['after'];
            
        $output .= $this->getPaging();

        $first = '<div'.$this->add_css.' class="'.trim('fks_teaser '.$this->css_class.' '.$this->add_class).'">';
        $last = '</div>';
        
        $this->html = $this->executeCallback($output);
        
        return $first.$this->html.$last;
    }

    public function getHookAttributes()
    {
        $self = array(
            'html' => $this->html,
            'items' => array_values($this->result),
            'item_number' => count($this->result),
            'item_number_total' => $this->t->total_number
        );

        return array_merge($self, $this->getPagingData(), $this->getHookStandardAttributes());
    }
    
    
    private function getClass()
    {
        $result = array(); 
        
        $tclass = self::$base->open_stklasse(ROOT.'content/stklassen/'.$this->t->stklasse.'.php');
        if(!is_array($tclass) || !$tclass['content'])
            return '';
            
        $this->tclass = $tclass;

        $this->css_class = 'fks_teaser_class fks_teaser_class_'.str_replace('.php', '', $this->t->stklasse);
        
        foreach($this->kids as $k)
        {
            if(!$k->klasse)
            {
                $btypen = array();
                
                $this->dclass_elements = array();
                $this->dclass_elements[0] = array(
                    's-id' => $k->id,
                    'no_doc' => true,
                    'is_teaser' => true,
                    'is_element' => true
                );
                
                $sdQ = self::$fksdb->query("SELECT dokument FROM ".SQLPRE."document_relations WHERE element = '".$k->id."' ORDER BY sort");
                while($sd = self::$fksdb->fetch($sdQ))
                {
                    $dv = self::$fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE dokument = '".$sd->dokument."' AND language = '".self::$fks->getLanguage(true)."' AND aktiv = '1' LIMIT 1");
                    if(!$dv)
                        continue;
                        
                    if($dv->klasse_inhalt)
                    {
                        $doc = $this->getKidsDocuments('', $sd->dokument);
                        $dclass = $this->dclasses[$doc->klasse]; 
                        if(!is_array($dclass['blocks'])) continue;
                        
                        $ki = self::$base->fixedUnserialize($dv->klasse_inhalt);
                        
                        foreach($dclass['blocks'] as $bid => $v)
                        {
                            $v['attr']['is_teaser'] = true; 
                            $b = $this->getExtendedClass($bid, $v['type'], $ki); 
                            $this->dclass_elements[$b->id] = array($v['attr'], $b->type, $b); 
                        } 
                        
                        continue;
                    }
                        
                    $bQ = self::$fksdb->query("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$sd->dokument."' AND dversion = '".$dv->id."' AND (type = '10' OR type = '11' OR type = '15') ORDER BY spalte, sort");
                    while($b = self::$fksdb->fetch($bQ))
                    {   
                        $btypen[$b->type] += 1;
                        $b->id = $b->type.'_'.$btypen[$b->type];
                        $this->dclass_elements[$b->id] = array(array(), $b->type, $b);                  
                    }
                }
                   
                $key = $this->getSortElement($k);
                $this->dclass_values = array();
                $output = preg_replace('@:([A-Za-z]+)\((.*)\):@iUe', '$this->getTeaserLoop(\'\1\', \'\2\')', $tclass['content']);
                
                if($this->t->data)
                {
                    $result[$key] = array(
                        'element_id' => $k->id,
                        'is_dclass' => false,
                        'teaser_class' => $tclass,
                        'values' => $this->dclass_values,
                        'output' => $output
                    );
                }
                else
                {
                    $result[$key] = $output; 
                }
            }
            else
            {
                if(!count($k->documents))
                    continue;
                    
                foreach($k->documents as $d_id => $doc)
                {
                    $btypen = array();
                
                    $this->dclass_elements = array();
                    $this->dclass_elements[0] = array(
                        's-id' => $this->element->id,
                        'd-id' => $doc->id,
                        'd-titel' => $doc->titel,
                        'no_doc' => false,
                        'is_teaser' => true 
                    ); 
                    
                    $dclass = $this->dclasses[$doc->klasse]; 
                    if(!is_array($dclass['blocks'])) continue;
                    
                    $dv = self::$fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."' AND language = '".self::$fks->getLanguage(true)."' AND aktiv = '1' LIMIT 1");
                    if(!$dv) continue;  
                    $ki = self::$base->fixedUnserialize($dv->klasse_inhalt);
                        
                    foreach($dclass['blocks'] as $bid => $v)
                    {
                        $v['attr']['is_teaser'] = true; 
                        $b = $this->getExtendedClass($bid, $v['type'], $ki); 
                        $this->dclass_elements[$b->id] = array($v['attr'], $b->type, $b); 
                    }
                    
                    $key = $this->getSortDclass($doc);
                    $this->dclass_values = array();
                    $output = preg_replace('@:([A-Za-z]+)\((.*)\):@iUe', '$this->getTeaserLoop(\'\1\', \'\2\')', $tclass['content']);
                
                    if($this->t->data)
                    {
                    
                        $result[$key] = array(
                            'element_id' => $k->id,
                            'document_id' => $d_id,
                            'is_dclass' => true,
                            'teaser_class' => $tclass,
                            'values' => $this->dclass_values,
                            'output' => $output
                        );
                    }
                    else
                    {
                        $result[$key] = $output;
                    } 
                }
            }
        }
        
        return $result;    
    }
    
    private function getSimple()
    {
        $result = array();

        $this->css_class = 'fks_teaser_simple';
        
        foreach($this->kids as $k)
        {
            if(!$k->klasse)
            {
                $spr = self::$base->fixedUnserialize($k->sprachen);
                if(!$spr[self::$fks->getLanguage(true)]['titel'])
                    continue;
                    
                $key = $this->getSortElement($k);
                
                if($this->t->data)
                {
                    $result[$key] = array(
                        'element_id' => $k->id,
                        'url' => self::$fks->buildURL($k->id, 0, $spr),
                        'title' => $spr[self::$fks->getLanguage(true)]['titel'],
                        'output' => '<li><a href="'.self::$fks->buildURL($k->id, 0, $spr).'">'.$spr[self::$fks->getLanguage(true)]['titel'].'</a></li>'
                    );
                }
                else
                {
                    $result[$key] = '<li><a href="'.self::$fks->buildURL($k->id, 0, $spr).'">'.$spr[self::$fks->getLanguage(true)]['titel'].'</a></li>';
                }
            }
            else
            {
                if(!count($k->documents))
                    continue;
                    
                foreach($k->documents as $d_id => $doc)
                {
                    $spr = self::$base->fixedUnserialize($doc->sprachenfelder);
                    if(!$spr[self::$fks->getLanguage(true)]['titel'])
                        continue;
                        
                    $key = $this->getSortDclass($doc);
                    
                    if($this->t->data)
                    {
                        $result[$key] = array(
                            'element_id' => $this->element->id,
                            'document_id' => $d_id,
                            'url' => self::$fks->buildURL($this->element->id, $d_id, $spr),
                            'title' => $spr[self::$fks->getLanguage(true)]['titel'],
                            'output' => '<li><a href="'.self::$fks->buildURL($this->element->id, $d_id, $spr).'">'.$spr[self::$fks->getLanguage(true)]['titel'].'</a></li>'
                        );
                    }
                    else
                    {
                         $result[$key] = '<li><a href="'.self::$fks->buildURL($this->element->id, $d_id, $spr).'">'.$spr[self::$fks->getLanguage(true)]['titel'].'</a></li>';
                    }
                }
            }
        }
        
        return $result;
    }
    
    
    private function getExtended()
    {
        $result = array();

        $this->css_class = 'fks_teaser_extended';
        
        $type2int = array(10 => 0, 11 => 1, 15 => 2);
        $t = $this->t;
        
        foreach($this->kids as $k)
        {
            $type_ban = array();
            
            if(!$k->klasse)
            {
                $output = '';
                
                $sdQ = self::$fksdb->query("SELECT dokument FROM ".SQLPRE."document_relations WHERE element = '".$k->id."' ORDER BY sort");
                while($sd = self::$fksdb->fetch($sdQ))
                {
                    $dv = self::$fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE dokument = '".$sd->dokument."' AND language = '".self::$fks->getLanguage(true)."' AND aktiv = '1' LIMIT 1");
                    if(!$dv)
                        continue;
                        
                    if($dv->klasse_inhalt)
                    {
                        $doc = $this->getKidsDocuments('', $sd->dokument);
                        $dclass = $this->dclasses[$doc->klasse]; 
                        if(!is_array($dclass['blocks'])) continue;
                        
                        $ki = self::$base->fixedUnserialize($dv->klasse_inhalt);
                        
                        foreach($dclass['blocks'] as $bid => $v)
                        {
                            if($v['type'] != 10 && $v['type'] != 11 && $v['type'] != 15)
                                continue;
                                
                            $type = $type2int[$v['type']];  
                            
                            if($t->h[$type] || ($t->of[$type] && in_array($type, $type_ban)))
                                continue;
                            $type_ban[] = $type;
                            
                            $v['attr']['is_teaser'] = true; 
                               
                            $b = $this->getExtendedClass($bid, $v['type'], $ki); 
                            $b = $this->getExtendedBlock($t, $type, $b, $k);  
                            
                            $output .= self::$content->parseHTML($b, $this->column_width, $b->html_before, $b->html_after, $v['attr'], $this->document);
                        } 
                        
                        continue;
                    }
                        
                    $bQ = self::$fksdb->query("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$sd->dokument."' AND dversion = '".$dv->id."' AND (type = '10' OR type = '11' OR type = '15') ORDER BY spalte, sort");
                    while($b = self::$fksdb->fetch($bQ))
                    {   
                        $type = $type2int[$b->type];
                        
                        if($t->h[$type] || ($t->of[$type] && in_array($type, $type_ban)))
                            continue;
                        $type_ban[] = $type;
                            
                        $b = $this->getExtendedBlock($t, $type, $b, $k);   
                        
                        $output .= self::$content->parseHTML($b, $this->column_width, $b->html_before, $b->html_after, array(), $this->document);                   
                    }
                }
                   
                $key = $this->getSortElement($k);
                $result[$key] = $output; 
            }
            else
            {
                if(!count($k->documents))
                    continue;
                    
                foreach($k->documents as $d_id => $doc)
                {
                    $type_ban = array();
                    $output = '';
                    
                    $dclass = $this->dclasses[$doc->klasse]; 
                    if(!is_array($dclass['blocks'])) continue;
                    
                    $dv = self::$fksdb->fetch("SELECT id, klasse_inhalt FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."' AND language = '".self::$fks->getLanguage(true)."' AND aktiv = '1' LIMIT 1");
                    if(!$dv) continue;  
                    $ki = self::$base->fixedUnserialize($dv->klasse_inhalt);
                        
                    foreach($dclass['blocks'] as $bid => $v)
                    {
                        if($v['type'] != 10 && $v['type'] != 11 && $v['type'] != 15)
                            continue;
                            
                        $type = $type2int[$v['type']];  
                        
                        if($t->h[$type] || ($t->of[$type] && in_array($type, $type_ban)))
                            continue;
                        $type_ban[] = $type;
                        
                        $v['attr']['is_teaser'] = true; 
                           
                        $b = $this->getExtendedClass($bid, $v['type'], $ki); 
                        $b = $this->getExtendedBlock($t, $type, $b, $k, $doc);  
                        
                        $output .= self::$content->parseHTML($b, $this->column_width, $b->html_before, $b->html_after, $v['attr'], $this->document);  
                    }
                    
                    $key = $this->getSortDclass($doc);
                    $result[$key] = $output;
                }
            }
        }
        
        return $result;        
    }
    
    private function getExtendedClass($bid, $type, $ki)
    {         
        $b = new stdClass();
        $b->id = $bid;
        $b->type = $type;
        $b->html = $ki[$bid]['html']; 
        $b->bild = $ki[$bid]['bild'];	
        $b->bildid = $ki[$bid]['bildid'];	
        $b->bildw = $ki[$bid]['bildw'];	
        $b->bildh = $ki[$bid]['bildh'];	
        $b->bildwt = $ki[$bid]['bildwt'];	
        $b->bildp = $ki[$bid]['bildp'];	
        $b->bildt = $ki[$bid]['bildt'];	
        $b->bild_extern = $ki[$bid]['bild_extern'];	
        $b->teaser = $ki[$bid]['teaser'];  
        
        return $b;  
    }
    
    private function getExtendedBlock($t, $type, $b, $k, $doc = null)
    {
        $b->html = htmlspecialchars(strip_tags(htmlspecialchars_decode($b->html), '<strong><em>'));
        $b->html_before = '';
        $b->html_after = '';
        
        if($t->hp[$type])
            $b->bild = 0;
            
        $element_link_id = ($doc?$this->element->id:$k->id);
            
        if($t->a[$type])
        {
            $titles = (!$doc?self::$base->fixedUnserialize($k->sprachen):self::$base->fixedUnserialize($doc->sprachenfelder));
            
            if($t->a[$type] == 1)
            {
                $b->html_before = '<a href="'.self::$fks->buildURL($element_link_id, (!$doc?0:$doc->id), $titles).'">';
                $b->html_after = '</a>';
            }
            elseif($t->a[$type] == 2)
            {
                $b->html_after = ' <a href="'.self::$fks->buildURL($element_link_id, (!$doc?0:$doc->id), $titles).'">'.$t->z3[$type].'</a>';
            }
        }
        
        
        if($t->sas[$type])
        {
            $convert2type = array(0 => 10, 1 => 10, 2 => 11, 3 => 12, 4 => 15);
            $b->type = $convert2type[$t->sas[$type]];
        }
        
        
        if($t->a[$type] != 2)
            return $b;
            

        if($t->z2[$type] == 1)
        {
            $b->html = htmlspecialchars(Strings::truncate(htmlspecialchars_decode($b->html), $t->z[$type], '...', ($t->z[$type] < 50?true:false), true));
        }
        elseif($t->z2[$type] == 2)
        {
            $b->html = Strings::cutSentences($b->html, $t->z[$type]);
        }
        else
        {
            $b->html = Strings::cutWordsByLength(htmlspecialchars_decode($b->html), $t->z[$type]);
        }
        
        return $b;
    }
     
    
    
    private function getKids()
    {
        $kids = array();
        $ns = $this->t->ns;
        if(!is_array($ns))
            $ns = array(); 
        
        $kidsQ = self::$fksdb->query("SELECT id, titel, sprachen, sort, klasse FROM ".SQLPRE."elements WHERE element = '".$this->element->id."' AND struktur = '".self::$fks->getStructureID()."' AND papierkorb = '0'");   
        while($kid = self::$fksdb->fetch($kidsQ))
        { 
            if(count($this->t->include) && (!in_array($kid->id, $this->t->include) && !in_array($kid->klasse, $this->t->include) && !in_array($kid->klasse.'.php', $this->t->include)))
                continue;
                
            if(count($this->t->exclude) && (in_array($kid->id, $this->t->exclude) || in_array($kid->klasse, $this->t->exclude) || in_array($kid->klasse.'.php', $this->t->exclude)))
                continue;
            
            $listed = (array_key_exists($kid->id, $ns)?true:false);
            if((!$this->t->st && !$listed) || ($this->t->st && $listed))
            {
                $kid->documents = $this->getKidsDocuments($kid->klasse);
                
                $kids[$kid->id] = $kid;    
            }    
        }
        
        return $kids;
    }
    
    private function getKidsDocuments($dclass, $d_id = 0)
    { 
        if(!$dclass && !$d_id)
            return array(); 
        
        $docs = array();
        $kat = $this->t->kat;
        if(!is_array($kat))
            $kat = array();
        
        $docsQ = self::$fksdb->query("SELECT id, klasse, titel, kats, sprachenfelder, timestamp, datum FROM ".SQLPRE."documents WHERE ".($d_id?"id = '".$d_id."'":"klasse = '".$dclass."'")." AND gesperrt = '0' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."'))");
        while($doc = self::$fksdb->fetch($docsQ))
        { 
            if(count($this->t->include) && (!in_array($doc->id, $this->t->include) && !in_array($doc->klasse, $this->t->include) && !in_array($doc->klasse.'.php', $this->t->include)))
                continue;
                
            if(count($this->t->exclude) && (in_array($doc->id, $this->t->exclude) || in_array($doc->klasse, $this->t->exclude) || in_array($doc->klasse.'.php', $this->t->exclude)))
                continue;
                
            $dkats = self::$base->fixedUnserialize($doc->kats);
            if(!is_array($dkats)) $dkats = array();
            $has_cat = (count($kat)?false:true);
            foreach($kat as $k)
            {
                if(in_array($k, $dkats))
                {
                    $has_cat = true;
                    break;
                }
            }
            if(!$has_cat)
                continue;
                
            if(!in_array($doc->klasse, $this->dclasses))
            {
                $dclass_temp = self::$base->open_dklasse(ROOT.'content/dklassen/'.$doc->klasse.'.php');
                $dclass_temp['blocks'] = self::$base->getClassBlocks($dclass_temp);
                $this->dclasses[$doc->klasse] = $dclass_temp;
            }
                 
            $docs[$doc->id] = $doc;
        }
        
        if($d_id)
            return $docs[$d_id];
        
        return $docs;
    }
    
    
    private function getPaging()
    {
        if(!$this->chunked || $this->pages < 2)
            return '';

        $this->css_class .= 'fks_teaser_pages';
            
        $p = self::$fks->getPage();
    
        if($this->pages < 7)
        {
            $stepA = 1;
            $stepB = 2;
        }
        else
        {
            $stepA = 2;
            $stepB = 4;
        }
        
        $start = ($p - $stepA > 0?($p - $stepA):0);
        $end = ($start + $stepB >= $this->pages - 1?($this->pages - 1):($start + $stepB));
        
        $pagination = '
        <div class="fks_pages">
            <span class="page">'.self::$trans->__('Seite %1 von %2', false, array(intval(($p + 1)), $this->pages)).'</span>
            '.($p?' <a class="first_page" href="'.self::$fks->getPageURL(0).'" rel="prev">'.self::$trans->__('Erste Seite').'</a> ':'').'
            '.($p?' <a class="page_before" href="'.self::$fks->getPageBeforeURL().'" rel="prev">&laquo;</a> ':'');
            
            for($x = $start; $x <= $end; $x++)
            {
                $pagination .= ' <a class="page_nr page_nr_'.$x.' '.($p == $x?'active_page':($p < $x?'rel_next':'rel_prev')).'" href="'.self::$fks->getPageURL($x).'"'.($p != $x?' rel="'.($p < $x?'next':'prev').'"':'').'>'.($x + 1).'</a> ';
            }
            
            $pagination .= ($p != $this->pages - 1?' <a class="page_after" href="'.self::$fks->getPageAfterURL().'" rel="next">&raquo;</a> ':'').'
            '.($p != $this->pages - 1?' <a class="last_page" href="'.self::$fks->getPageURL(($this->pages - 1)).'" rel="next">'.self::$trans->__('Letzte Seite').'</a> ':'').'
        </div>';
        
        return $pagination;
    }
    
    private function getPagingData()
    {
        if(!$this->chunked)
            return array('chunked' => false);
            
        $p = self::$fks->getPage();
        $urls = array();
    
        for($x = 0; $x < $this->pages; $x++)
            $urls[] = self::$fks->getPageURL($x); 
        
        return array(
            'chunked' => true,
            'pages' => $this->pages,
            'current_page' => intval(($p + 1)),
            'first_page' => 0,
            'last_page' => ($this->pages - 1),
            'page_before' => ($p > 0?($p - 1):$p),
            'page_after' => ($p != $this->pages - 1?($p + 1):$p),
            'urls' => $urls
        );
    }
    
    private function limit($result = array())
    {
        if(!$this->t->vonA && !$this->t->bisA)  
            return $result;
            
        $new = array();
        $loop = 0;
        
        foreach($result as $key => $value)
        {
            $loop ++;
            
            if($this->t->vonA && intval($this->t->von) > $loop)
                continue;
                
            if($this->t->bisA && intval($this->t->bis) < $loop)
                continue;
                
            $new[$key] = $value;
        }
        
        return $new;
    }
    
    
    private function chunkPages($result = array())
    {
        $break = intval($this->t->umbruch); 
        
        if($break <= 0)
            return $result;   
            
        $chunked = array_chunk($result, $break, true); 
        
        $this->chunked = true;
        $this->pages = intval(count($chunked));
        
        $result = $chunked[self::$fks->getPage()]; 
         
        return $result;
    }
    
    
    private function sort($result = array())
    {
        if($this->sort_order == 'SHUFFLE')
            shuffle($result);
        elseif($this->sort_order == 'DESC')
            krsort($result);
        else
            ksort($result);
            
        return $result;   
    }
    
    private function getSort($value = '')
    {
        $this->sort_count += 0.0001;
        return $value . ' ' . $this->sort_count;    
    }
    
    private function getSortElement($element)
    {
        if($this->sort_element == 'id')
        {
            $value = $element->id;
        }
        elseif($this->sort_element == 'alpha')
        {
            $titles = self::$base->fixedUnserialize($element->sprachen);
            $curtitle = $titles[self::$fks->getLanguage(true)]['titel'];
            
            $value = ($curtitle?$curtitle:$element->titel);
        }
        else
        {
            $value = $element->sort;
        }
            
        return $this->getSort($value).''; 
    }
    
    private function getSortDclass($document)
    {
        if($this->sort_dclass == 'time')
        {
            $value = $document->timestamp;
        }
        elseif($this->sort_dclass == 'alpha')
        {
            $titles = self::$base->fixedUnserialize($element->sprachenfelder);
            $curtitle = $titles[self::$fks->getLanguage(true)]['titel'];
            
            $value = ($curtitle?$curtitle:$document->titel);
        }
        else
        {
            $value = $document->datum; 
        }
            
        return $this->getSort($value).'';
    }
    
    private function setSort()
    {
        $this->sort_order = (Strings::strExists('DESC', $this->t->sort, false)?'DESC':'ASC');
        
        if(Strings::strExists('datum', $this->t->sort))
        {
            $this->sort_element = 'sort';
            $this->sort_dclass = 'date';
        }
        elseif(Strings::strExists('timestamp', $this->t->sort))
        {
            $this->sort_element = 'id';
            $this->sort_dclass = 'time';
        }
        elseif(Strings::strExists('titel', $this->t->sort))
        {
            $this->sort_element = 'alpha';
            $this->sort_dclass = 'alpha';
        }
        elseif(Strings::strExists('RAND', $this->t->sort))
        {
            $this->sort_element = 'rand';
            $this->sort_dclass = 'rand';
            $this->sort_order = 'SHUFFLE';
        }
        else
        {
            $this->sort_element = 'sort';
            $this->sort_dclass = 'date';
        }
    }
}   
?>
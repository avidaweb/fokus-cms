<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Content
{
    private static $static, $base, $api, $fksdb, $user, $suite, $trans, $fks;
    
    private $matchE = 0, $matchT = 0;
    private $gallery_options = array(), $form_options = array();
    private $block_types = array(), $dclass_values = array();
    
    function __construct($static = array())
    {
        self::$static = $static;
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
        self::$fks = $static['fks'];
        
        $this->setGallery();
        $this->setForm();
    }
    
    public function setGallery($p = array(), $select_class = 0)
    { 
        /// standard ///
        $standard = array(
            'show_title' => false,
            'show_desc' => false,
            'img_width' => 150,
            'img_height' => 150,
            'img_link_width' => 0,
            'img_link_height' => 0,
            'img_in_a_rows' => 0,
            'limit' => 0,
            'link' => 'none',
            'link_class' => '',
            'galerie_class' =>  'fks_gallery',
            'container_class' => 'fks_gallery_pic',
            'img_class' => '',
            'view' => 'flat',
            'html_before' => '',
            'html_after' => ''
        );
        
        if(!$p['galerie_class'])
            $p['galerie_class'] = $p['gallery_class'];
        
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////
        $this->gallery_options[$select_class] = $p;
    }
    
    public function setGalerie($p = array(), $select_class = 0)
    {
        $this->setGallery($p, $select_class);    
    }
    
    public function setForm($p = array(), $select_class = 0)
    { 
        /// standard ///
        $standard = array(
            'view' => 'table', // flat
            'hide_after_submit' => false,
            'success_text' => 'Formular erfolgreich abgeschickt'
        );
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////
        $this->form_options[$select_class] = $p;
    }
    
    
    public function getTeaser($p = array(), $return_data = false)
    {
        /// standard ///
        $standard = array(
            'type' => 'simple', // simple, extended, class 
            'element' => self::$fks->getElementID(), 
            'class' => '', 
            'categories' => array(), 
            'limit_from' => 0, 
            'limit_to' => 0, 
            'items_per_page' => 10, 
            'sort' => 'date', // date, id, alphabetic, random
            'order' => 'desc',
            'include' => array(),
            'exclude' => array()
        );
        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v);
        ////
        
        $sort = array(
            'date' => 'datum',
            'id' => 'timestamp',
            'alphabetic' => 'titel',
            'random' => 'RAND'
        );
        
        $teaser = array(
            'data' => ($return_data?true:false),
            'auflistung' => ($p['type'] == 'class' || $p['class']?2:($p['type'] == 'extended'?1:0)),
            'element' => intval($p['element']),
            'stklasse' => $p['class'],
            'kat' => $p['categories'],
            'vonA' => (intval($p['limit_from'])?true:false),
            'von' => intval($p['limit_from']),
            'bisA' => (intval($p['limit_to'])?true:false),
            'bis' => intval($p['limit_to']),
            'umbruch' => $p['items_per_page'],
            'sort' => $sort[$p['sort']].' '.$p['order'],
            'include' => $p['include'],
            'exclude' => $p['exclude']
        );
        
        
        include_once(ROOT.'inc/classes.blocks/_basic.php');
        include_once(ROOT.'inc/classes.blocks/64_teaser.php');
        
        $bl = new stdClass();
        $bl->teaser = serialize($teaser);
            
        $block = new Block_64(array(
            'fksdb' => self::$fksdb,
            'base' => self::$base,
            'suite' => self::$suite,
            'trans' => self::$trans,
            'user' => self::$user,
            'api' => self::$api,
            'fks' => self::$fks,
            'content' => $this
        ), array(
            'block' => $bl
        ));
        
        $output = $block->get();
        
        return $output;
    }
    
    public function writeTeaser($p = array())
    {
        echo $this->getTeaser($p);
    }
    
    
    public function parseHTML($bl, $column_width, $html_before = '', $html_after = '', $attr = array(), $doc = null)
    {
        $block_slug = self::$base->getBlockByID($bl->type, 'en');
        
        if(!$block_slug)
            return false;
            
        include_once(ROOT.'inc/classes.blocks/'.$bl->type.'_'.$block_slug.'.php');
            
        $class = 'Block_'.$bl->type;
        if(!class_exists($class))
            return false;
            
        $block = new $class(array(
            'fksdb' => self::$fksdb,
            'base' => self::$base,
            'suite' => self::$suite,
            'trans' => self::$trans,
            'user' => self::$user,
            'api' => self::$api,
            'fks' => self::$fks,
            'content' => $this
        ), array(
            'block' => $bl,
            'column_width' => $column_width,
            'html_before' => $html_before,
            'html_after' => $html_after,
            'attr' => $attr,
            'document' => $doc
        ));

        $output = $block->get();

        if($attr['dclass_values'])
        {
            $rtn = $block->getHookAttributes();
            unset($block, $rtn['fks'], $rtn['content'], $rtn['api'], $rtn['trans']);
            return $rtn;
        }
        
        // Hook: before_block
        $hatr = $block->getHookAttributes();
        $before_block = self::$api->execute_hook('before_block', $hatr, true);
        $output = ($before_block?$before_block:'').$output;
        
        // Hook: after_block
        $output = self::$api->execute_filter('after_block', $output, $hatr);
        $output .= self::$api->execute_hook('after_block', $hatr, true);
        
        unset($hatr, $block);
        
        return $output;
    }
    
    public function getGalleryOptions($classes)
    {  
        $p = $this->gallery_options[0];
        
        if(!count($classes))
            return $p;
            
        foreach($classes as $cc)
        {
            if(!is_array($this->gallery_options[$cc]))
                continue;
                
            return $this->gallery_options[$cc];
        }
        
        return $p;
    }
     
    public function getFormOptions($classes)
    {   
        $p = $this->form_options[0];
        
        if(count($classes))
            return $p;
            
        foreach($classes as $cc)
        {
            if(!is_array($this->form_options[$cc]))
                continue;
                
            return $this->form_options[$cc];
        }
        
        return $p;
    }
    
    
    private function calcCSS($t, $r, $d, $l)
    { 
        if($t != -1 && $r == -1 && $d == -1 && $l == -1)
        {
            $r = $t; $d = $t; $l = $t; 
        }
        else if($t != -1 && $r != -1 && $d == -1 && $l == -1)
        {
            $d = $t; $l = $r;  
        }
        else if($t != -1 && $r != -1 && $d != -1 && $l == -1)
        {
            $l = $r; 
        }
        
        $r = ($r == -1?1:$r);
        $d = ($d == -1?1:$d);
        $l = ($l == -1?1:$l);
        
        $all = array($t, $r, $d, $l);
        return $all;     
    }
    
    
    private function getDocumentRelations($c)
    {
        $query_pre = "SELECT dokument, id, klasse FROM ".SQLPRE."document_relations";

        if($c->document_id)
            return self::$fksdb->query($query_pre." LIMIT 1");

        if($c->slot && !self::$fks->getPreviewArea()) // Dokumente des Elements bei einem Slot
        {
            if(!self::$fks->isOverwrittenSlot($c->slot))
                return self::$fksdb->query($query_pre." WHERE slot = '".$c->slot."' AND element = '0' ORDER BY sort");
            
            return self::$fksdb->query($query_pre." WHERE slot = '".$c->slot."' AND element = '".intval(self::$fks->getElementID())."' ORDER BY sort");
        }
        
        if(self::$fks->isPreview()) // Dokumente des Elements im Normalzustand oder Dokumentenklasse
        {
            $query = self::$fksdb->query($query_pre." WHERE element = '".intval(self::$fks->getElementID())."'".(self::$fks->isPreview()?" AND klasse = ''":"")." AND slot = '' LIMIT 1"); 
        
            if(!self::$fksdb->count($query))
                return self::$fksdb->query($query_pre."".(self::$fks->isPreview()?" WHERE klasse = ''":"")." LIMIT 1");
                
            return $query;
        }
        
        if($c->newsletter) // Verschicken des Newsletters
            return self::$fksdb->query($query_pre." LIMIT ".count($c->newsletter));
        
        if(self::$fks->getError())
            return self::$fksdb->query($query_pre." WHERE error_page = '".self::$fks->getError()."' ORDER BY sort");
            
        if(!self::$fks->isDclass() && !count(self::$fks->getErrorDocuments())) // Dokumente des Elements im Normalzustand       
            return self::$fksdb->query($query_pre." WHERE element = '".intval(self::$fks->getElementID())."' AND slot = '' ORDER BY sort");
            
        if(!count(self::$fks->getErrorDocuments())) // Für Dokumentenklassen
            return self::$fksdb->query($query_pre." WHERE element = '".intval(self::$fks->getDclassElementID())."' AND slot = '' ORDER BY sort"); 
        
        return self::$fksdb->query($query_pre." LIMIT ".(count(self::$fks->getErrorDocuments())?count(self::$fks->getErrorDocuments()):"1")); 
    }
    
    private function getDocuments($c, $doc_rel)
    {
        $query_pre = "SELECT id, titel, dversion_edit, klasse, css_klasse, rollen, zsb, closed_to, closed_by FROM ".SQLPRE."documents";
        $query_sub = "AND papierkorb = '0' AND gesperrt = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."'))";

        if($c->document_id)
            return self::$fksdb->query($query_pre." WHERE id = '".$c->document_id."'");

        if($c->slot && !self::$fks->getPreviewArea())
            return self::$fksdb->query($query_pre." WHERE id = '".$doc_rel->dokument."' ".$query_sub.""); 
            
        if($c->newsletter) 
        {
            $n = $c->newsletter[($c->doc_rel_count - 1)];
            if($n) return self::$fksdb->query($query_pre." WHERE id = '".$n."' ".$query_sub."");
        }
        
        if(count(self::$fks->getErrorDocuments()))
            return self::$fksdb->query($query_pre." WHERE id = '".self::$fks->getErrorDocuments($c->error_counter)."' ".$query_sub." LIMIT 1"); 
        
        if(!self::$fks->isDclass() && !$doc_rel->klasse)
        {  
            if($doc_rel->klasse)
                return self::$fksdb->query($query_pre." WHERE klasse = '".$doc_rel->klasse."'".(self::$fks->isPreview()?" AND id = '".self::$fks->getPreview()."'":" ".$query_sub."")); 
                
            return self::$fksdb->query($query_pre." WHERE id = '".(self::$fks->isPreview()?self::$fks->getPreview():$doc_rel->dokument)."'".(self::$fks->isPreview()?" AND id = '".self::$fks->getPreview()."'":" ".$query_sub."")." LIMIT 1");
              
        }
        
        if(self::$fks->isDclass())
        {  
            if($doc_rel->klasse == -1 || self::$fks->isPreview())
                return self::$fksdb->query($query_pre." WHERE id = '".self::$fks->getDclassDocumentID()."' ".$query_sub." LIMIT 1");  
             
            return self::$fksdb->query($query_pre." WHERE id = '".$doc_rel->dokument."' ".$query_sub." LIMIT 1");                                        
        } 
        
        if($doc_rel->klasse)
            return self::$fksdb->query($query_pre." WHERE klasse = '".$doc_rel->klasse."' ".$query_sub.""); 
            
        if(self::$fks->isPreview())
            return self::$fksdb->query($query_pre." WHERE id = '".self::$fks->getPreview()."' LIMIT 1"); 
                    
        return self::$fksdb->query($query_pre." WHERE id = '".self::$fks->getDclassDocumentID()."' ".$query_sub." LIMIT 1"); 
    }
        
        
    function get($p = array())
    {
        /// PARAMETER STANDARDWERTE ///
        $standard = array(
            'id' => '',
            'class' => '',
            'slot' => '',
            'html_tag' => 'div',
            'document_html_tag' => 'div',
            'document_class' => 'document',
            'document_class_first' => '',
            'document_class_last' => '',
            'document_width ' => 0,
            'document_border_width' => array(0),
            'column_class' => 'column',
            'column_padding' => array(8, 20, 8, 0),
            'column_border_width' => array(0),
            'responsive' => false,
            'document_id' => 0,
            'dclass_values' => false
        );
        
        /*        
        'document_border_width_first' => array(0),
        'document_border_width_last' => array(0),
        'column_padding_first' =>  array(0),
        'column_padding_last' => array(8, 0, 8),
        'column_border_width_first' => array(0),
        'column_border_width_last' => array(0),
        'column_margin' => array(0),
        'column_margin_first' => array(0),
        'column_margin_last' => array(0)
        'no_wrapper',
        'no_document_wrapper'
        'no_column_wrapper'
        */

        if(!isset($p['document_width']) && !isset($p['width']) && !$p['responsive'])
            $p['responsive'] = true;
        
        if(!isset($p['column_padding']) && !isset($p['column_padding_last']) && !$p['responsive'])
            $p['column_padding_last'] = array(8, 0, 8);

        if(!isset($p['document_width']) && isset($p['width']))
            $p['document_width'] = $p['width'];

        foreach($standard as $k => $v)
            $p[$k] = (isset($p[$k])?$p[$k]:$v); 
            
        ////
        
        $css_calc = array('document_border_width', 'document_border_width_first', 'document_border_width_last', 'column_padding', 'column_padding_first', 'column_padding_last', 'column_margin', 'column_margin_first', 'column_margin_last', 'column_border_width', 'column_border_width_first', 'column_border_width_last');
        foreach($css_calc as $cc)
        {
            if(is_array($p[$cc]))
            {
                $tmp = array();
                for($x = 0; $x < 4; $x++)
                    $tmp[$x] = (isset($p[$cc][$x])?$p[$cc][$x]:-1);
                $p[$cc] = $this->calcCSS($tmp[0], $tmp[1], $tmp[2], $tmp[3]);
            }
        } 
        
        $c = (object)$p; 
        
        if(self::$fks->getPreviewArea() == $c->slot && $this->preview)
        {
            $c->slot = self::$fks->getPreviewArea(); 
        }
        
        $c->newsletter = array();
        if(self::$fksdb->save($_REQUEST['newsletter']))
            $c->newsletter = explode('-', self::$fksdb->save($_REQUEST['newsletter']));
            
        $write = '';

        if($c->dclass_values)
            $this->dclass_values = array();
            
        // Hook: before_content
        $hatr = array(
            'id' => $c->id, 
            'slot' => $c->slot, 
            'html_tag' => $c->html_tag, 
            'class' => $c->class
        );   
        $write .= self::$api->execute_hook('before_content', $hatr, true);
        unset($hatr);
        
        if(!$c->no_wrapper)
            $write .= '<'.$c->html_tag.''.($c->id?' id="'.$c->id.'"':'').' class="fks_content '.$c->class.'">'; 
        
        if((!self::$fks->isCustomPage() && !self::$fks->getSearchString()) || $c->slot)
        {
            $c->error_counter = 0;
            $c->doc_rel_count = 0;
            
            $doc_rel_query = $this->getDocumentRelations($c);
            
            while($doc_rel = self::$fksdb->fetch($doc_rel_query))
            {  
                $c->doc_rel_count ++;
                $count_documents = 0;
                
                $doc_query = $this->getDocuments($c, $doc_rel);
                while($doc = self::$fksdb->fetch($doc_query))
                {   
                    $doc_roles = self::$base->fixedUnserialize($doc->rollen);
                    if(!is_array($doc_roles)) $doc_roles = array();
                    
                    if(count($doc_roles) && !self::$fks->isPreview())
                    {
                        $is_in_role = false;
                        $a_roles = self::$user->getAvailableRoles();
                        foreach($a_roles as $gr)
                        {
                            if(in_array($gr, $doc_roles))
                                $is_in_role = true;
                        }
                            
                        if(in_array('-1', $doc_roles) && !self::$user->isLogged())
                            $is_in_role = true;
                        
                        if(!$is_in_role)
                            continue;
                    }
                    
                    if($c->slot)
                        $doc->slot = $c->slot;
                    
                    $count_documents ++; 
                    
                    $dv = self::$fksdb->fetch("SELECT * FROM ".SQLPRE."document_versions WHERE dokument = '".$doc->id."' AND ".(self::$fks->isPreview() || self::$fks->isGhost()?"id = '".(!self::$fks->isDocumentPreview()?$doc->dversion_edit:self::$fks->getDocumentPreview())."'":"language = '".self::$fks->getLanguage(true)."' AND aktiv = '1'")." ORDER BY timestamp_freigegeben DESC LIMIT 1");  
                      
                    if(self::$user->isForesight() && self::$user->getForesight('type') > 1)
                    {
                        $same_sql = "dokument = '".$doc->id."' AND language = '".self::$fks->getLanguage(true)."' AND timestamp_edit >= '".$dv->timestamp_edit."' AND ende = '1' AND aktiv = '0'";
                        
                        if(self::$user->getForesight('type') == 2)
                            $dvO = self::$fksdb->query("SELECT * FROM ".SQLPRE."document_versions WHERE ".$same_sql." AND von = '".self::$user->getID()."' LIMIT 1"); 
                        if(self::$user->getForesight('type') == 3)
                            $dvO = self::$fksdb->query("SELECT * FROM ".SQLPRE."document_versions WHERE ".$same_sql." LIMIT 1"); 
                        if(self::$fksdb->count($dvO))
                            $dv = self::$fksdb->fetch($dvO);
                    }
                    
                    $doc_class = $c->document_class;
                    if($count_documents == 1 && !empty($c->document_class_first))
                        $doc_class .= ' '.$c->document_class_first;
                    if($count_documents == self::$fksdb->count($doc_rel_query) && !empty($c->document_class_last))
                        $doc_class .= ' '.$c->document_class_last;
                    if($doc->css_klasse)
                        $doc_class .= ' '.$doc->css_klasse;
                        
                    $dbw = $c->document_border_width;
                    if($count_documents == 1 && isset($c->document_border_width_first))
                        $dbw = $c->document_border_width_first;
                    if($count_documents == self::$fksdb->count($doc_rel_query) && !empty($c->document_border_width_last))
                        $dbw = $c->document_border_width_last;
            
                    $document_width_real = $c->document_width - $dbw[1] - $dbw[3];
                    
                    $document_style = 'overflow:auto; ';
                    $document_style .= ($c->document_width?'width:'.$document_width_real.'px; ':'');
                    $document_style .= (is_array($dbw)?($dbw[0] == 0 && $dbw[1] == 0 && $dbw[2] == 0 && $dbw[3] == 0?'border-width:0px; ':'border-width:'.$dbw[0].'px '.$dbw[1].'px '.$dbw[2].'px '.$dbw[3].'px; '):''); 
                    
                    if($c->responsive)
                        $document_style .= 'box-sizing:border-box; -moz-box-sizing:border-box; -webkit-box-sizing:border-box; -ms-box-sizing:border-box; ';
                    
            
                    // activate live edit
                    $isGhost = false;
                    if(self::$fks->isGhost())
                    {
                        $not_responsible = false;
                        if($doc->zsb && !self::$user->isSuperAdmin() && count(self::$user->getAvaibleCompetence()))
                        {
                            $dzsb = self::$base->fixedUnserialize($doc->zsb);
                            if(count($dzsb))
                            {
                                $not_responsible = true;
                                
                                foreach($dzsb as $zid)
                                {
                                    if(self::$user->isCompetent($zid))
                                        $not_responsible = false;    
                                }
                            }
                        } 
                        
                        if(!$c->newsletter && ($doc->closed_to <= time() || $doc->closed_by == self::$user->getID()) && (!$dv->edit || $dv->von == self::$user->getID()) && !$not_responsible && self::$user->r('dok', 'edit') && self::$user->r('fks', 'ghost'))
                        {
                            $isGhost = true;
                        }
                    }
                    
                    $document_content = '';
                            
                    if(!$c->no_document_wrapper)
                    {
                        $document_content .= '<'.$c->document_html_tag.($isGhost?' data-did="'.$doc->id.'" data-dvid="'.$dv->id.'" data-klasse="'.$doc->klasse.'" data-slot="'.$c->slot.'"':'').' class="'.trim($doc_class.($isGhost?' ghost_editable':'')).'" style="'.trim($document_style).'" id="doc_'.$doc->id.'">';
                    }
                    
                    if((!$doc_rel->klasse && !$doc->klasse) || ($c->slot && !$doc->klasse) || ($c->newsletter && !$doc->klasse))
                    {  
                        $sum_width = 0;  
                        $loop = 0;
                        
                        $column_query = self::$fksdb->query("SELECT size, id, css, css_klasse, color, bgcolor, border, bordercolor, align, padding FROM ".SQLPRE."columns WHERE dokument = '".$doc->id."' AND dversion = '".$dv->id."' ORDER BY sort"); 
                        $columns_count = self::$fksdb->count($column_query);
                        
                        $columns = array();
                        $columns_pull_off = 0;
                        
                        // get columns from db
                        while($column = self::$fksdb->fetch($column_query))
                        {
                            $loop ++;  
                            
                            if($loop == 1 && is_array($c->column_border_width_first))
                                $border_width = $c->column_border_width_first;
                            else if($loop == $columns_count && is_array($c->column_border_width_last))
                                $border_width = $c->column_border_width_last;
                            else
                                $border_width = $c->column_border_width; 
                            
                            if($loop == 1 && is_array($c->column_padding_first))
                                $padding = $c->column_padding_first;
                            elseif($loop == $columns_count && is_array($c->column_padding_last))
                                $padding = $c->column_padding_last;
                            else
                                $padding = $c->column_padding; 
                            
                            if($loop == 1 && is_array($c->column_margin_first))
                                $margin = $c->column_margin_first;
                            elseif($loop == $columns_count && is_array($c->column_margin_last))
                                $margin = $c->column_margin_last;
                            else
                                $margin = $c->column_margin; 
                                
                            if($column->css)
                            {
                                if($column->border)
                                {
                                    $border_width[0] = 1; 
                                    $border_width[1] = 1; 
                                    $border_width[2] = 1; 
                                    $border_width[3] = 1; 
                                }
                                if($column->padding)
                                {
                                    $abst = explode('_', $column->padding);
                                    $paddingZ = $abst[0];
                                    $marginZ = $abst[1];
            
                                    if($paddingZ)
                                    {
                                        $paddingA = explode('|', $paddingZ);
                                        $padding[0] = $paddingA[0]; 
                                        $padding[1] = $paddingA[2]; 
                                        $padding[2] = $paddingA[1]; 
                                        $padding[3] = $paddingA[3]; 
                                    }
                                    if($marginZ)
                                    {
                                        $marginA = explode('|', $marginZ);
                                        $margin[0] = $marginA[0]; 
                                        $margin[1] = $marginA[2]; 
                                        $margin[2] = $marginA[1]; 
                                        $margin[3] = $marginA[3]; 
                                    }
                                }
                            }
                            
                            // math column pull off
                            $columns_pull_off += $border_width[1] + $border_width[3] + $padding[1] + $padding[3] + $margin[1] + $margin[3];
                             
                            // temporary save column  
                            $columns[] = array(
                                'c' => $column,
                                'border' => $border_width,
                                'padding' => $padding,
                                'margin' => $margin
                            );
                        }
                        
                        $loop = 0; 
                        
                        // output loop
                        foreach($columns as $spa)
                        {
                            $loop ++;
                            
                            $sp_class = '';
                            $add_css = '';
                            
                            $sp = $spa['c'];
                            $border_width = $spa['border'];
                            $padding = $spa['padding'];
                            $margin = $spa['margin'];
                                
                            if($sp->css_klasse)
                                $sp_class = $sp->css_klasse;
                                
                            if($sp->css)
                            {
                                $bordertyp = array('', 'solid', 'dashed', 'dotted');
                                $talign = array('inherit', 'left', 'right', 'center', 'justify');
                                
                                $add_css = ($sp->color?'color:#'.$sp->color.'; ':''). 
                                ($sp->bgcolor?'background-color:#'.$sp->bgcolor.'; ':'').   
                                ($sp->border?'border:1px '.$bordertyp[$sp->border].' #'.$sp->bordercolor.'; ':'').     
                                ($sp->align?'text-align:'.$talign[$sp->align].'; ':''); 
                            } 
                            
                            if(!$c->responsive && $c->document_width)
                            {
                                $column_pixel_size = ($document_width_real / 100) * $sp->size; 
                                $column_pixel_size = ($loop % 2 == 0?floor($column_pixel_size):ceil($column_pixel_size));
                                
                                $pull_of = ($columns_pull_off / $columns_count);
                                $pull_of = ($loop % 2 == 0?floor($pull_of):ceil($pull_of));
                                
                                $column_width_real = $column_pixel_size - $pull_of;
                                $sum_width += $column_pixel_size;
                                
                                if($columns_count == $loop && $document_width_real > $sum_width)
                                    $column_width_real += ($document_width_real - $sum_width - 1); 
                                    
                                if($columns_count == $loop && $sum_width > $document_width_real)
                                {
                                    $diff = $sum_width - $document_width_real;
                                    $column_width_real -= $diff;
                                }
                            }
                            
                            $column_style = 'float:left; ';
                            
                            if(!$c->responsive)
                                $column_style .= ($c->document_width?'width:'.$column_width_real.'px; ':'');
                            else
                                $column_style .= 'width:'.$sp->size.'%; box-sizing:border-box; -moz-box-sizing:border-box; -webkit-box-sizing:border-box; -ms-box-sizing:border-box; ';
                            
                            $column_style .= (is_array($border_width)?'border-width:'.$border_width[0].'px '.$border_width[1].'px '.$border_width[2].'px '.$border_width[3].'px; ':'');
                            $column_style .= (is_array($padding)?'padding:'.$padding[0].'px '.$padding[1].'px '.$padding[2].'px '.$padding[3].'px; ':'');
                            $column_style .= (is_array($margin)?'margin:'.$margin[0].'px '.$margin[1].'px '.$margin[2].'px '.$margin[3].'px; ':'');
                            $column_style .= $add_css;
                             
                            $final_class = trim($c->column_class.' '.$sp_class);
                                 
                            if(!$c->no_column_wrapper)                     
                                $document_content .= '<div'.($final_class?' class="'.$final_class.'"':'').' style="'.trim($column_style).'">';
                            
                            $blQ = self::$fksdb->query("SELECT * FROM ".SQLPRE."blocks WHERE dokument = '".$doc->id."' AND spalte = '".$sp->id."' AND dversion = '".$dv->id."' ORDER BY sort");
                            while($bl = self::$fksdb->fetch($blQ))
                            {
                                $document_content .= $this->parseHTML($bl, $column_width_real, '', '', array(), $doc);
                            }
                            
                            if(!$c->no_column_wrapper) 
                                $document_content .= '</div>';
                        } 
                    }
                    else
                    {                    
                        $ki = $dv->klasse_inhalt; 
                        $this->block_types = array(); 
                        $fk = self::$base->open_dklasse((self::$fks->isBackendPreview()?'../':'').'content/dklassen/'.$doc->klasse.'.php'); 
                        
                        $klassen_width = ($c->document_width?$document_width_real:0);
                        
                        $class_result = preg_replace('@:(.*)\):@iUe', '$this->getDclass(\'\1\', $ki, $doc, $klassen_width, $c)', $fk['content']);
                        $document_content .= $class_result;
                    }
                    
                    if(!$c->no_document_wrapper)
                        $document_content .= '</'.$c->document_html_tag.'>';
                    elseif($columns_count > 1)
                        $document_content .= '<div style="clear:both;" class="fks_clearer"></div>';
                        
                    // Hook: after_document
                    $hatr = array(
                        'id' => $doc->id, 
                        'dversion' => $dv->id, 
                        'dclass' => $doc->klasse, 
                        'column_count' => $columns_count, 
                        'slot' => $c->slot
                    );   
                    $document_content = self::$api->execute_filter('after_document', $document_content, $hatr);
                    $document_content .= self::$api->execute_hook('after_document', $hatr, true);
                    unset($hatr);
                    
                    $write .= $document_content;
                }

                if(count(self::$fks->getErrorDocuments()))
                    $c->error_counter ++;
            }
        }
        elseif(self::$fks->isCustomPage())
        {
            $write .= $this->getCustomPageContent();
        }
        elseif(self::$fks->getSearchString())
        {
            $write .= $this->getSearchContent();
        }
        
        if(!$c->no_wrapper)
            $write .= '</'.$c->html_tag.'>';
         
        // Shortcodes feuern
        if(self::$api->has_shortcodes())
            $write = self::$api->execute_shortcode($write);   
            
        // Hook: after_content
        $hatr = array(
            'id' => $c->id, 
            'slot' => $c->slot, 
            'html_tag' => $c->html_tag, 
            'class' => $c->class
        );   
        $write = self::$api->execute_filter('after_content', $write, $hatr);
        $write .= self::$api->execute_hook('after_content', $hatr, true);
        unset($hatr);

        if($c->dclass_values)
            return $this->dclass_values;

        return $write;
    }
    
    function write($p = array())
    { 
        echo $this->get($p); 
    }
    
    
    private function getSearchContent()
    {
        $ranking = array();
        $snippet = array();
        $elements = array();
        
        $q = preg_replace("/[^a-zA-Z0-9äöüÄÖÜß ]/", " ", self::$fks->getSearchString());
        $q = trim(Strings::removeDoubleSpace($q));
        $qa = explode(' ', $q);
        $qsql = '';
        
        $count_serps = 0;
        $count_dvs = 0;
        
        $contents = array();
        
        for($x = 0; $x < count($qa); $x++)
        {
            $qsql .= (!$x?" AND (":"OR ")."html LIKE '%".$qa[$x]."%' ".($x + 1 == count($qa)?") ":"");
            $dqsql .= (!$x?" AND (":"OR ")."klasse_inhalt LIKE '%".$qa[$x]."%' ".($x + 1 == count($qa)?") ":"");
        }                
            
        $dvQ = self::$fksdb->query("SELECT id FROM ".SQLPRE."document_versions WHERE aktiv = '1' AND language = '".self::$fks->getLanguage(true)."' AND klasse_inhalt = ''");
        while($dv = self::$fksdb->fetch($dvQ))
        {
            $dvsql .= (!$count_dvs?" AND (":"OR ")."dversion = '".$dv->id."' ".($count_dvs + 1 == self::$fksdb->count($dvQ)?") ":""); 
            $count_dvs ++;
        }
       
        $blQ = self::$fksdb->query("SELECT dokument, html, dversion, type FROM ".SQLPRE."blocks WHERE (type >= '7' AND type < '20') ".$qsql." ".$dvsql."");
        while($bl = self::$fksdb->fetch($blQ))
        {   
            $contents[] = array( 
                'k' => 'block',
                'dokument' => $bl->dokument,
                'html' => $pure_text = strip_tags(htmlspecialchars_decode($bl->html)),
                'dversion' => $bl->dversion,
                'type' => $bl->type
            );
        }
        
        $dvQ = self::$fksdb->query("SELECT id, klasse_inhalt, dokument FROM ".SQLPRE."document_versions WHERE aktiv = '1' AND language = '".self::$fks->getLanguage(true)."' AND klasse_inhalt != '' ".$dqsql);
        while($dv = self::$fksdb->fetch($dvQ))
        {
            $j = self::$base->fixedUnserialize($dv->klasse_inhalt);
            foreach($j as $did => $dc)
            { 
                $test_array = self::$base->db_to_array($dc['html']);
                $test_array_b = self::$base->fixedUnserialize($dc['html']);
                if(!is_array($test_array)) $test_array = array();
                if(!is_array($test_array_b)) $test_array_b = array(); 
                
                if(is_string($dc['html']) && !count($test_array) && !count($test_array_b))
                {
                    $html = strip_tags(htmlspecialchars_decode($dc['html']));
                    
                    $innit = false;
                    for($x = 0; $x < count($qa); $x++)
                    {
                        if(Strings::strExists($qa[$x], $html, false))
                            $innit = true;
                    }
                    
                    if($innit)
                    {
                        $dok = self::$fksdb->fetch("SELECT id, klasse, sprachenfelder FROM ".SQLPRE."documents WHERE id = '".$dv->dokument."' AND papierkorb = '0' AND no_search = '0' LIMIT 1");
                        
                        if($dok)
                        {
                            $contents[] = array( 
                                'k' => 'dv',
                                'dokument' => $dv->dokument,
                                'html' => $html,
                                'dversion' => $dv->id,
                                'type' => 16,
                                'dok' => $dok
                            ); 
                        }
                    }
                }
            }
        }
        
        function getElementsForTreeSearch($eid, $fksdb, $base, $fks, $me)
        {
            $parent = $fksdb->fetch("SELECT id, rollen, element FROM ".SQLPRE."elements WHERE id = '".$eid."' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".$base->getTime()."' AND (bis = '0' OR bis >= '".$base->getTime()."')) AND struktur = '".$fks->getStructure('id')."' LIMIT 1");
            if($parent)
            {
                $me->tmp_elements_search[] = $parent;
                getElementsForTreeSearch($parent->element, $fksdb, $base, $fks, $me);
            }
        }
        
        $gramma_remove = array('!', '.', ',', '?', ';', ':', '-', '"', '(', ')', '&', '/', '_');
        
        foreach($contents as $i => $bl)
        {
            $dok = $bl['dok'];
            $bl = (object)$bl; 
            
            $check_dok = self::$fksdb->count("SELECT id FROM ".SQLPRE."documents WHERE id = '".$bl->dokument."' AND papierkorb = '0' AND no_search = '0' LIMIT 1");
            if(!$check_dok)
                continue;
            
            $element = array();
            $doc_rel_query = self::$fksdb->query("SELECT element FROM ".SQLPRE."document_relations WHERE dokument = '".$bl->dokument."' AND slot = '' ORDER BY element, sort"); 
            while($doc_rel = self::$fksdb->fetch($doc_rel_query))
            {
                $ele = self::$fksdb->fetch("SELECT id, sprachen, rollen, element FROM ".SQLPRE."elements WHERE id = '".$doc_rel->element."' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) AND struktur = '".self::$fks->getStructure('id')."' LIMIT 1");
                if(!$ele)
                    continue; 
                    
                $spr = self::$base->fixedUnserialize($ele->sprachen);
                
                $this->tmp_elements_search = array();
                $this->tmp_elements_search[] = $ele;  
                
                getElementsForTreeSearch($ele->id, self::$fksdb, self::$base, self::$fks, $this);
                
                $rollen_f = false; 
                foreach($this->tmp_elements_search as $vv)
                {
                    $r = self::$base->fixedUnserialize($vv->rollen);
                    if(!is_array($r)) $r = array();
                    if(count($r) && !in_array(self::$user->getRole(), $r) && self::$user->getRole() != 1)
                    {
                        $rollen_f = true;
                        break;
                    }
                }
                
                if($spr[self::$fks->getLanguage(true)]['titel'] && !$rollen_f)
                {
                    $element[] = array(
                        'id' => $ele->id,
                        'titel' => $spr[self::$fks->getLanguage(true)]
                    );   
                }
            }
            
            if($bl->k == 'dv' && $dok->klasse)
            {
                $ele = self::$fksdb->fetch("SELECT element FROM ".SQLPRE."elements WHERE klasse = '".$dok->klasse."' AND frei = '1' AND papierkorb = '0' AND (anfang <= '".self::$base->getTime()."' AND (bis = '0' OR bis >= '".self::$base->getTime()."')) AND struktur = '".self::$fks->getStructure('id')."' LIMIT 1"); 
                if($ele)
                {
                    $spr = self::$base->fixedUnserialize($dok->sprachenfelder);
                
                    if($spr[self::$fks->getLanguage(true)]['titel'])
                    {
                        $element[] = array(
                            'id' => $ele->element,
                            'd' => $dok->id,
                            'klasse' => $dok->klasse,
                            'titel' => $spr[self::$fks->getLanguage(true)]
                        );   
                    }
                }
            }
            
            if(!count($element))
                continue;
                
            $count_serps ++;
            $svalue = 0;
            
            $search_html = ' '.strtolower(str_replace($gramma_remove, ' ', strip_tags($bl->html))).' ';
            $search_titel = ' '.strtolower(str_replace($gramma_remove, ' ', $spr[self::$fks->getLanguage(true)]['titel'])).' ';
            
            foreach($qa as $s)
            {
                $s = strtolower($s);
                
                if(Strings::strExists($s, $search_titel, false))
                { 
                    $full = substr_count(($search_titel), ' '.$s.' ');
                    $half = (substr_count(($search_titel), $s.' ') + substr_count(($search_titel), ' '.$s)) - $full;
                    $teils = substr_count(($search_titel), $s) - $full - $half;
                    
                    if($full < 0) $full = 0;
                    if($half < 0) $half = 0;
                    if($teils < 0) $teils = 0;
                    
                    $full = ($full > 2?2:$full);
                    $half = ($half > 2?2:$half);
                    $teils = ($teils > 2?2:$teils);
                    
                    $svalue += ($full * 50000) + ($half * 24000) + ($teils * 5000);
                }
                
                if(Strings::strExists($s, $search_html, false))
                { 
                    $full = substr_count(($search_html), ' '.$s.' ');
                    $half = (substr_count(($search_html), $s.' ') + substr_count(($search_html), ' '.$s)) - $full;
                    $teils = substr_count(($search_html), $s) - $full - $half;
                    
                    if($full < 0) $full = 0;
                    if($half < 0) $half = 0;
                    if($teils < 0) $teils = 0;
                    
                    $full = ($full > 3?3:$full);
                    $half = ($half > 3?3:$half);
                    $teils = ($teils > 3?3:$teils);
                    
                    $svalue += ($full * 130000) + ($half * 60000) + ($teils * 20000);  
                }
            }
            
            if($svalue < 100)
                continue;
            
            $svalue = ((30 - $bl->type) / 10) * $svalue;
            $svalue += $count_serps;
            
            // Snippet berechnen
            $snippet_length = 80;
            $pure_text = $bl->html;
            $string_length = strlen($pure_text);
            
            $pos = 0;
            $trys = 0;
            while(!$pos && $trys < 15)
            {
                $pos = strpos($pure_text, $qa[$trys]);  
                $trys ++;
            } 
            
            $start = $pos - $snippet_length;
            $end = $pos + $snippet_length; 
            
            if($pos < $snippet_length)
            {
                $start = 0;
                $end = $snippet_length * 2; 
            }
            elseif($end > $string_length)
            {
                $end = $string_length;
                $start = $end - ($snippet_length * 2);
                if($start < 0)
                    $start = 0;
            }  
            
            if($start != 0)
            {
                if($start <= 20)
                {
                    $start = 0; 
                }
                else
                {
                    $sub = substr($pure_text, ($start - 20), 20);
                    $subpos = strpos($sub, ' ');
                    $start = $start - (19 - $subpos);
                }
            }
            if($end != $string_length)
            {
                if($end + 20 >= $string_length)
                {
                    $end = $string_length; 
                }
                else
                {
                    $sub = substr($pure_text, $end, 20);
                    $subpos = strpos($sub, ' ');
                    $end = $end + $subpos;
                }
            }
            
            $length = $end - $start;
            
            $snip = substr($pure_text, $start, $length);
            $snip = str_replace('#', '', $snip);
            
            foreach($qa as $s)
                $snip = preg_replace('#('.$s.')#isU', '<strong>\1</strong>', $snip); 
            // Snippet ende
            
            $ranking[$bl->dokument] += $svalue;
            if($dok_count[$bl->dokument] < 2)
                $snippet[$bl->dokument] .= ($dok_count[$bl->dokument]?'... ':'').$snip;
            $elements[$bl->dokument] = $element; 
            
            $dok_count[$bl->dokument] += 1;  
        }
        
        arsort($ranking);
        
        self::$fksdb->insert("searches", array(
        	"q" => self::$fks->getSearchString(),
        	"results" => count($ranking),
        	"timestamp" => self::$base->getTime(),
        	"ip" => self::$api->getVisitorIP()
        ));
        
        foreach($ranking as $dok => $value)
        {
            $snip = $snippet[$dok];
            $ele = $elements[$dok];
            $ele_already = array(); 
            
            $serp = '
            <div class="fks_search_result">
                <p class="fks_search_snippet">
                    &quot;'.trim($snip).'&quot;
                </p>
                <ul class="fks_search_links">';
                    $serp_links = '';
                    $links = array();
                    foreach($ele as $k => $v)
                    {
                        if(in_array($v['id'], $ele_already))
                            continue;
                            
                        $ele_already[] = $v['id'];
                        
                        $serp_links .= '
                        <li>
                            '.(!$v['d']?'
                            <a href="'.self::$fks->getRoot().$v['id'].'/'.self::$base->auto_slug($v['titel']).'/">'.$v['titel']['titel'].'</a>
                            ':'
                            <a href="'.self::$fks->getRoot().$v['id'].'/'.$v['d'].'/'.self::$base->auto_slug($v['titel']).'/">'.$v['titel']['titel'].'</a>
                            ').'
                        </li>';
                        
                        // Hook: after_serp_link
                        $hatr = array(
                            'snippet' => trim($snip),
                            'q' => self::$fks->getSearchString(),
                            'results' => count($ranking),
                            'dclass' => $v['klasse'],
                            'title' => $v['titel']['titel'],
                            'eid' => $v['id'],
                            'did' => $v['d']
                        );
                        $serp_links = self::$api->execute_filter('after_serp_link', $serp_links, $hatr);
                        
                        $links[] = array('dclass' => $v['klasse'], 'title' => $v['titel']['titel'], 'eid' => $v['id'], 'did' => $v['d']);
                    }
                    $serp .= $serp_links;
                $serp .= '
                </ul>
            </div>';   
            
            // Hook: after_serp
            $hatr = array(
                'snippet' => trim($snip), 
                'q' => self::$fks->getSearchString(), 
                'results' => count($ranking), 
                'links' => $links
            );   
            $serp = self::$api->execute_filter('after_serp', $serp, $hatr);
            
            $write .= $serp;   
            
            $write .= self::$api->execute_hook('after_serp', $hatr, true);   
            unset($hatr);              
        }    
        
        return $write;
    }
    
    private function getCustomPageContent()
    {
        $cp = self::$fks->getCustomPage();
        $cp_callback = $cp['content_callback'];
        
        if(!$cp_callback || !is_callable($cp_callback))
            return '';
        
        $hatr = array(
            'fks' => self::$fks,
            'content' => $this,
            'api' => self::$api,
            'fksdb' => self::$fksdb
        );
        
        ob_start();  
        
        $rtn = '';
        if(is_string($cp_callback))
            $rtn .= call_user_func($cp_callback, $cp['slug'], $cp['vars'], $hatr);
        elseif(is_array($cp_callback))
            $rtn .= call_user_func_array($cp_callback, array($cp['slug'], $cp['vars'], $hatr)); 
            
        $rtn .= ob_get_contents();
        ob_end_clean(); 
        
        return $rtn;   
    }
    
    
    private function getDclass($s, $ki, $doc, $column_width = 0, $c = null)
    {
        $ki = self::$base->fixedUnserialize($ki);
        
        $s = explode('(', $s);
        $f = $s[0];
        $attr = self::$base->get_attributes($s[1]);

        $rtn = '';
        $placeholder = '';

        $attr['dclass_values'] = false;
        if(!is_null($c))
            $attr['dclass_values'] = $c->dclass_values;
          
        if(in_array($f, self::$base->getBlocks('dclass')) || in_array($f, self::$base->getDclassMethods()))
        {    
            $block_type = (in_array($f, self::$base->getBlocks('dclass'))?array_search($f, self::$base->getBlocks('dclass')):array_search($f, self::$base->getDclassMethods())); 
            $this->block_types[$block_type] += 1;
            
            if(in_array($f, self::$base->getBlocks('dclass')))
            {
                if($attr['name']) $bid = self::$base->slug($attr['name']);
                else $bid = $block_type.'_'.$this->block_types[$block_type];
                $placeholder = $attr['name'];

                if($attr['hide'] != 'true')
                { 
                    $b = new stdClass();
                    $b->id = $bid;
                    $b->dk = true;
                    $b->type = $block_type;
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
                    $b->extb = $attr['block'];
                    $b->extb_content = $ki[$bid]['extb_content'];
                    
                    if(!empty($b->html) || $b->bild || $b->teaser || $b->extb)
                        $rtn = $this->parseHTML($b, $column_width, '', '', $attr, $doc);
                }
            }
            elseif(in_array($f, self::$base->getDclassMethods())) // Falls es sich um eine extra Funktion handelt
            {
                $placeholder = $f;

                $attr['d-id'] = $doc->id;
                $rtn = $this->getDclassMethod($f, $attr);
            }
        }
        elseif($f == 'inhaltsbereich')
        {                           
            $this->block_types['inhaltsbereich'] += 1;
            
            if($attr['name']) $bid = self::$base->slug($attr['name']);
            else $bid = 'inhaltsbereich'.$this->block_types['inhaltsbereich'];
            $placeholder = $attr['name'];
            
            $bloecke = $ki[$bid]['html'];
            if(!is_array($bloecke))
                $bloecke = array();

            $rtn = '';
                
            foreach($bloecke as $k => $v)
            {
                $b = new stdClass();
                $b->id = $v['id'];
                $b->dk = true;
                $b->dk_content = true;
                $b->type = $v['type'];
                $b->html = $v['html']; 
                $b->bild = $v['bild'];	
                $b->bildid = $v['bildid'];	
                $b->bildw = $v['bildw'];	
                $b->bildh = $v['bildh'];	
                $b->bildwt = $v['bildwt'];	
                $b->bildp = $v['bildp'];	
                $b->bildt = $v['bildt'];	
                $b->bild_extern = $v['bild_extern'];	
                $b->teaser = $v['teaser'];
                $b->extb = $v['extb'];
                $b->extb_content = $v['extb_content'];
            
                $b->css = $v['css'];
                $b->css_klasse = $v['css_klasse'];
                $b->padding = $v['padding'];
                $b->color = $v['color'];
                $b->font = $v['font'];
                $b->bgcolor = $v['bgcolor'];
                $b->border = $v['border'];
                $b->bordercolor = $v['bordercolor'];
                $b->align = $v['align'];
                $b->margin = $v['margin'];
                $b->spalten = $v['spalten'];
                
                if($b->html || $b->bild || $b->teaser || $b->extb)
                {
                    $new_atr = array(
                        'ibid' => $bid,
                        'blockindex' => $k,
                        'dclass_values' => $attr['dclass_values']
                    );

                    $output = $this->parseHTML($b, $column_width, '', '', $new_atr, $doc);
                    $rtn .= $output;

                    if($attr['dclass_values'] && $placeholder)
                        $this->dclass_values[$placeholder][] = $output;
                }
            }

            return $rtn;
        }

        if($attr['dclass_values'] && $placeholder)
            $this->dclass_values[$placeholder][] = $rtn;
        
        return $rtn;
    }
    
    public function getDclassMethod($tf, $attr)
    {
        $rtn = '';
        $callback_opt = array();
        
        if($attr['d-id'])
        {
            $doc = self::$fksdb->fetch("SELECT datum, id, titel, sprachenfelder, von_edit, kats, von, author, klasse FROM ".SQLPRE."documents WHERE id = '".$attr['d-id']."' LIMIT 1");
            $dspr = self::$base->fixedUnserialize($doc->sprachenfelder);
            $doc->categories = self::$base->fixedUnserialize($doc->kats);
            
            $callback_opt['document'] = array(
                'id' => $doc->id,
                'dclass' => $doc->klasse,
                'date' => $doc->datum,
                'title' => $doc->titel,
                'public_title' => $dspr[self::$fks->getLanguage(true)]['titel'],
                'author' => $doc->von,
                'last_author' => $doc->von_edit,
                'categories' => $doc->categories
            );
        }
        if($attr['s-id'])
        {
            $el = self::$fksdb->fetch("SELECT sprachen, id, element, titel FROM ".SQLPRE."elements WHERE id = '".$attr['s-id']."' LIMIT 1");
            $spr = self::$base->fixedUnserialize($el->sprachen);
            
            $callback_opt['element'] = array(
                'id' => $el->id,
                'parent' => $el->element,
                'title' => $el->titel,
                'public_title' => $spr[self::$fks->getLanguage(true)]['titel']
            );
        }
            
        
        if($tf == 'id')
        {
            $rtn = intval($doc->id);
        }
        if($tf == 'datum')
        {
            setlocale(LC_TIME, ($attr['laendercode']?$attr['laendercode']:'de_DE'));
            
            $format = ($attr['format']?$attr['format']:'%d.%m.%Y'); 
            
            if($attr['format'] == 'timestamp')
                $rtn = $doc->datum;
            else
                $rtn = strftime($format, $doc->datum); 
                
            $callback_opt['timestamp'] = $doc->datum;
        }
        if($tf == 'autor')
        {  
            if($attr['type'] == 'last')
                $author_id = $doc->von_edit;
            elseif($attr['type'] == 'first')
                $author_id = $doc->von;
            else
                $author_id = $doc->author;
                
            if(!$author_id)
                $author_id = $doc->von;
            
            $per = self::$fksdb->fetch("SELECT id, anrede, vorname, nachname, str, hn, plz, ort, email, tel_p, tel_g, land, fax, mobil, tags FROM ".SQLPRE."users WHERE id = '".$author_id."' LIMIT 1"); 
            
            $per_search = array('ID', 'Vorname', 'Vorname', 'Nachname', 'Nachname', 'Strasse', 'Hausnummer', 'PLZ', 'Ort', 'Email', 'Telefon Privat', 'Telefon Geschäft', 'Land', 'Fax', 'Mobil', 'Notizen');
            $per_search_en = array('ID', 'firstname', 'first_name', 'lastname', 'last_name', 'street', 'housenumber', 'zip', 'city', 'email', 'privatphone', 'businessphone', 'country', 'fax', 'mobilephone', 'notes');
            $per_replace = array($per->id, $per->vorname, $per->vorname, $per->nachname, $per->nachname, $per->str, $per->hn, $per->plz, $per->ort, $per->email, $per->tel_p, $per->tel_g, $per->land, $per->fax, $per->mobil, $per->tags);
            
            $rtn = str_ireplace($per_search, $per_replace, $attr['info']);
            $rtn = str_ireplace($per_search_en, $per_replace, $rtn);
            
            $callback_opt['user'] = array(
                'id' => $per->id,
                'first_name' => $per->vorname,
                'last_name' => $per->nachname,
                'street' => $per->str,
                'housenumber' => $per->hn,
                'zip' => $per->plz,
                'city' => $per->ort,
                'email' => $per->email,
                'notes' => $per->tags
            );
        }
        if($tf == 'link')
        {  
            if(!$attr['no_doc'])
            { 
                $rtn = '
                <a href="'.self::$fks->getRoot().$attr['s-id'].'/'.$doc->id.'/'.self::$base->auto_slug($dspr[self::$fks->getLanguage(true)]).'/"'.($attr['class']?' class="'.$attr['class'].'"':'').'>
                    '.($attr['text']?$attr['text']:$dspr[self::$fks->getLanguage(true)]['titel']).'
                </a>';  
            }
            else
            {
                $rtn = '
                <a href="'.self::$fks->getRoot().$el->id.'/'.self::$base->auto_slug($spr[self::$fks->getLanguage(true)]).'/"'.($attr['class']?' class="'.$attr['class'].'"':'').'>
                    '.($attr['text']?$attr['text']:$spr[self::$fks->getLanguage(true)]['titel']).'
                </a>';  
            }
        }
        if($tf == 'url')
        {  
            if(!$attr['no_doc'])
            { 
                $rtn = self::$fks->getRoot().$attr['s-id'].'/'.$doc->id.'/'.self::$base->auto_slug($dspr[self::$fks->getLanguage(true)]).'/';
            }
            else
            {
                
                $rtn = self::$fks->getRoot().$el->id.'/'.self::$base->auto_slug($spr[self::$fks->getLanguage(true)]).'/';  
            }
        }
        if($tf == 'zurueck')
        {
            $possible_attr = array('id', 'class', 'element', 'link_text', 'text', 'link', 'view', 'before', 'after', 'no_wrapper', ''); 
                
            $p = array();
            foreach($possible_attr as $pa)
            {
                if($attr[$pa]) 
                    $p[$pa] = $attr[$pa];
            }
            
            if($p['text'] && !$p['link_text'])
                $p['link_text'] = $p['text'];
            
            $rtn = self::$fks->getJumpBack($p);
        }
        if($tf == 'categories')
        {
            $rtna = array();
            $ccount = 0;
                
            if(!count($doc->categories)) return array();
                
            $avaible_cats = self::$api->getCategories(array(), false);
            if(!count($avaible_cats)) return array();
            
            foreach($avaible_cats as $cat)
            {
                if(!in_array($cat['id'], $doc->categories))
                    continue;
            
                $rtna[$cat['id']] = $cat['name'];
                
                if($attr['callback'])
                    $callback_opt['categories'][$cat['id']] = $cat['name'];
                
                $ccount ++;
                if($attr['limit'] && $attr['limit'] >= $ccount)
                    break;
            }
            
            if(!count($rtna))
                $rtn = '';
            else 
                $rtn = implode(($attr['implode']?$attr['implode']:', '), $rtna); 
        }
        if($tf == 'commentcount')
        {
            $commentcount = self::$api->getCommentCount($attr['s-id'], $attr['d-id']);
            
            if($attr['callback'])
            {
                $callback_opt['count'] = $commentcount;
                $callback_opt['comments'] = self::$api->getComments(array(
                    'element' => $attr['s-id'],
                    'dclass_document' => $attr['d-id']
                ));
            }
            
            $rtn = $commentcount;
        }
        if($tf == 'callback')
        {
            if(!$attr['function'])
                return '';
                
            $fire = (!$attr['class']?$attr['function']:array($attr['class'], $attr['function']));
            unset($attr['function'], $attr['class']);
            
            if(!is_callable($fire))
                return '';
                
            $add = array(
                'fks' => self::$fks,
                'content' => $this,
                'api' => self::$api
            );
            $nattr = array_merge($attr, $callback_opt, $add);
            
            $rtn = call_user_func_array($fire, array($nattr, self::$static));
        }
        
        
        if($attr['callback'])
        {
            $fire_cb = explode('|', $attr['callback']);
            if(count($fire_cb) < 2) 
                $fire_cb = $attr['callback'];
                
            $callback_opt['attributes'] = $attr;
            
            if(!is_callable($fire_cb))
                return $rtn;
                
            $rtn = call_user_func_array($fire_cb, array($rtn, $callback_opt, self::$static));
        }
        
        return $rtn;
    }
}
?>
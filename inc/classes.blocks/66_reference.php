<?php
class Block_66 extends BlockBasic
{
    private $document_id = 0;

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    { 
        $blocks = $this->getBlocks();  
        if(!$blocks)
            return ''; 
             
        $this->html = $this->executeCallback($blocks);
        
        $tag = ($this->attr['tag']?$this->attr['tag']:'div');     
            
        $output = '<'.$tag.' class="'.trim('fks_reference '.$this->add_class).'"'.$this->add_css.''.($this->attr['id']?' id="'.$this->attr['id'].'"':'').'>'.$this->html_before.$this->html.$this->html_after.'</'.$tag.'>';
        
        return $output;  
    }

    public function getHookAttributes()
    {
        $self = array(
            'document_id' => $this->document_id,
            'html' => $this->html
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
    
    private function getBlocks()
    {
        $c = (object)self::$base->db_to_array($this->html);
        if(!count($c))
            return '';
        $output = '';
        
        $doc = self::$fksdb->fetchSelect("documents", array("id"), array(
            "id" => $c->document
        ), "", 1); 
        if(!$doc)
            return '';

        $this->document_id = $doc->id;
        
        $dversion = self::$fksdb->fetchSelect("document_versions", array("id"), array(
            "dokument" => $doc->id,
            "language" => self::$fks->getLanguage(true),
            "aktiv" => 1
        ), "id DESC", 1); 
        
            
        $column = self::$fksdb->fetchSelect("columns", array("id", "vid"), array(
            "dokument" => $doc->id,
            "dversion" => $dversion->id,
            "vid" => $c->column
        ), "", 1);
        if(!$column)
            return '';
            
        $blocks = self::$fksdb->select("blocks", "*", array(
            "dokument" => $doc->id,
            "dversion" => $dversion->id,
            "spalte" => $column->id
        ), "sort ASC");
        if(!self::$fksdb->count($blocks))
            return '';
        
        
        while($block = self::$fksdb->fetch($blocks))
        {
            if(!$c->show && in_array($block->vid, $c->block))
                continue;
                
            if($c->show && !in_array($block->vid, $c->block))
                continue;
                
            $output .= self::$content->parseHTML($block, $this->column_width, '', '');
        }
        
        
        return $output;
    }
}   
?>
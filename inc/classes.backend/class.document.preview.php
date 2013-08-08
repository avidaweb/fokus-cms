<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class DocumentPreview
{  
    private static $static, $base, $api, $fksdb, $user, $suite, $trans; 
    
    public function __construct($static)
    {
        // static
        self::$static = $static;
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
    }
    
    public function getNewsletterPreview($id, $docs, $dk)
    {
        $d = $docs[$id];
        
        $sd = new StdClass();
        $sd->id = $d->id; 
        $sd->klasse = $d->klasse; 
        
        return $this->getPreview($sd, $d, $dk, null, true);    
    }
    
    public function getStructurePreview($sd, $docs, $element, $dk)
    {
        $d = $docs[$sd->dokument];
        
        return $this->getPreview($sd, $d, $dk, $element);
    }
    
    private function getPreview($sd, $d, $dk, $element = null, $newsletter = false)
    {        
        $rtn = '';
        $column_content = '';
    
        if(!$d->klasse)
        {
            $spalten = self::$fksdb->query("SELECT id FROM ".SQLPRE."columns WHERE dokument = '".$d->id."' AND dversion = '".$d->dversion_edit."' ORDER BY sort, id");
            $spa = self::$fksdb->count($spalten);
            $spa = ($spa?$spa:1);
            $abzug = ($spa - 1) * 3;
            $spwidth = floor((520 / $spa) - $abzug);
            $addwlast = (520 - ($spwidth * $spa + $abzug));
            $spc = 0;
        }
        
        $rtn .= '
        <article data-sd="'.$sd->id.'" class="document-preview"'.($newsletter?' id="Ksd_'.$d->id.'"':'').'>
            <div class="c">
                <div class="head">';
                    if($sd->klasse)
                    {
                        if($sd->klasse == -1)
                        {
                            $rtn .= '<span class="bezeichner">'.self::$trans->__('Automatisierte Dokumentenklasse').'</span>';
                        }
                        else
                        {
                            $fk = self::$base->open_dklasse(ROOT.'content/dklassen/'.$sd->klasse.'.php'); 
                            
                            if($dk->n_uebersicht[self::$base->slug($fk['name'])])
                            {
                                $rtn .= '
                                <span class="bezeichner">
                                    '.self::$trans->__('Dokumentenklasse &quot;%1&quot;', false, array(Strings::cut($fk['name'], 25))).'
                                </span>';
                            }
                            else
                            {
                                $rtn .= '
                                <a data-id="'.$sd->klasse.'.php" class="titel duebersicht" title="'.self::$trans->__('Dokumentenübersicht der Dokumentenklasse &quot;%1&quot; öffnen', false, array($fk['name'])).'">
                                    '.self::$trans->__('Dokumentenklasse &quot;%1&quot;', false, array(Strings::cut($fk['name'], 25))).'
                                </a>';
                            }
                        }
                    }
                    else
                    {
                        $rtn .= '
                        <span class="status">'.self::$base->document_status($d->statusA, $d->statusB, true).'</span>
                        <a data-id="'.$d->id.'" class="titel" title="'.self::$trans->__('Dokument &quot;%1&quot; öffnen', false, array($d->titel)).'">
                            '.Strings::cut($d->titel, 35).'
                        </a>
                        <a data-id="'.$sd->id.'" class="del" title="'.self::$trans->__('Zuordnung entfernen?').'">X</a>';
                    }
                $rtn .= '
                </div>';
                
                // column content
                if($sd->klasse)
                {
                    $column_content .= '
                    <div class="spalte">
                        <div class="inh morespa">';
                        
                        if($sd->klasse == -1)
                        {
                            $and = self::$fksdb->count("SELECT id FROM ".SQLPRE."documents WHERE klasse = '".$element->klasse."' AND papierkorb = '0'");
                            $column_content .= ($and == 1?self::$trans->__('Inhalt aus 1 Dokument'):self::$trans->__('Inhalt aus %1 Dokumenten', false, array($and)));
                        }
                        else
                        {
                            $and = self::$fksdb->query("SELECT id FROM ".SQLPRE."documents WHERE klasse = '".$sd->klasse."' AND papierkorb = '0'");
                            $column_content .= ($and == 1?self::$trans->__('1 Dokument automatisch zugeordnet'):self::$trans->__('%1 Dokumente automatisch zugeordnet', false, array($and)));
                        }
                        
                        $column_content .= '
                        </div>
                    </div>';
                }
                elseif($d->klasse)
                {
                    if($d->dkt1 || $d->dkt2 || $d->dkt3 || $d->dkt4)
                    {
                        $column_content .= '
                        <div class="spalte">
                            <div class="inh dklasse">
                                '.$this->getDclassContent($d->dk1, $d->dkt1).'
                                '.$this->getDclassContent($d->dk2, $d->dkt2).'
                                '.$this->getDclassContent($d->dk3, $d->dkt3).'
                                '.$this->getDclassContent($d->dk4, $d->dkt4).'
                            </div>
                        </div>';
                    }
                    else
                    {
                        $fk = self::$base->open_dklasse(ROOT.'content/dklassen/'.$d->klasse.'.php');
                        
                        $column_content .= '
                        <div class="spalte">
                            <div class="inh morespa">
                                '.self::$trans->__('Dokumentenklasse <em>%1</em>', false, array(Strings::cut($fk['name'], 20))).'
                            </div>
                        </div>';
                    }
                }
                elseif($spa > 3)
                {
                    $column_content .= '
                    <div class="spalte">
                        <div class="inh morespa">
                            '.self::$trans->__('Dokument mit %1 Spalten', false, array($spa)).'
                        </div>
                    </div>';
                }
                else
                {                    
                    while($spalte = self::$fksdb->fetch($spalten))
                    {
                        $spc ++;
                                                
                        $bloecke = self::$fksdb->query("SELECT id, html, type, bildid FROM ".SQLPRE."blocks WHERE dokument = '".$d->id."' AND dversion = '".$d->dversion_edit."' AND spalte = '".$spalte->id."' AND ((type >= '10' AND type < '20' AND html != '') OR (type = '30' AND bildid != '0') OR type = '40') ORDER BY sort LIMIT 3");
                        $block_content = '';
                        while($block = self::$fksdb->fetch($bloecke))
                            $block_content .= $this->getBlockContent($block);
                            
                        if($spa == 1 && !$block_content)
                            continue;
                        
                        $column_content .= '
                        <div class="spalte"'.($spa > 1?' style="'.($spc != $spa?'float:left;margin-right:3px;width:'.$spwidth.'px;':'float:right;width:'.($addwlast + $spwidth).'px;').'"':'').'>
                            <div class="inh">  
                                '.$block_content.'
                            </div>
                        </div>';
                    }
                }
                
                if($column_content)
                {
                    $rtn .= '
                    <div class="spalten">
                        '.$column_content.'
                    </div>';
                }
                
            $rtn .= '
            </div>
            <div class="drag"></div>
        </article>';
        
        return $rtn;
    }
    
    private function getDclassContent($content, $type)
    {
        $ba = array();
        $block = (object)$ba;
        $block->type = $type;
        
        if($type == 30)
        {
            $ca = explode('|||', $content);
            $block->bildid = $ca[0];
        }
        else
        {
            $block->html = $content;
        }
        
        return $this->getBlockContent($block);
    }
    
    private function getBlockContent($block)
    {
        if(!$block->type)
            return '';
        
        $rtn = '<p class="type_'.$block->type.'">';
        
        if($block->type < 20)
            $rtn .= $this->getBlockText($block);
        elseif($block->type == 30)
            $rtn .= $this->getBlockImage($block);
        elseif($block->type == 40)
            $rtn .= $this->getBlockGallery($block);
        
        $rtn .= '</p>';
        
        return $rtn;
    }
    
    private function getBlockText($block)
    {
        return Strings::cut(rawurldecode(strip_tags(htmlspecialchars_decode(Strings::cleanString($block->html)))), 100);
    }
    
    private function getBlockImage($block)
    {
        $image = self::$fksdb->fetch("SELECT id, file, type, stack FROM ".SQLPRE."file_versions WHERE stack = '".$block->bildid."' ORDER BY timestamp DESC LIMIT 1");
        $file = DOMAIN.'/img/'.$image->stack.'-0-50-na.'.$image->type;
        if(!$image)
            return '';
            
        return '<img src="'.$file.'" height="50" alt=" " />';
    }
    
    private function getBlockGallery($block)
    {
        $pictures = self::$base->fixedUnserialize($block->html);
        if(!is_array($pictures)) 
            return '';
            
        $cgc = 0;
        $rtn = '';
        
        foreach($pictures as $b1 => $b2)
        {
            $image = self::$fksdb->fetch("SELECT id, file, type, stack FROM ".SQLPRE."file_versions WHERE stack = '".$b2['id']."' ORDER BY timestamp DESC LIMIT 1");
            $file = DOMAIN.'/img/'.$image->stack.'-0-40-na.'.$image->type;
            if(!$image)
                continue;
                
            $rtn .= '<img src="'.$file.'" height="40" alt=" " />';
                
            $cgc ++;
            if($cgc >= 3)
                break;
        }
        
        return $rtn;
    }
}
?>
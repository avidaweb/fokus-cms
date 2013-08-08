<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

class Upload
{  
    private static $static, $base, $api, $fksdb, $user, $suite, $trans; 
    
    private $post_name = '';
    private $cat = 0, $dir = 0, $append_to = 0;
    private $status = array();
    
    public function __construct($static, $name, $opt = array())
    {
        // static
        self::$static = $static;
        self::$fksdb = $static['fksdb'];
        self::$base = $static['base'];
        self::$suite = $static['suite'];
        self::$trans = $static['trans'];
        self::$user = $static['user'];
        self::$api = $static['api'];
        
        $this->post_name = $name;
        
        $this->cat = intval($opt['cat']);
        $this->dir = intval($opt['dir']);
        $this->append_to = intval($opt['append_to']);
        
        if($this->cat == 0)
            $this->uploadImage();
    }
    
    public function getStatus()
    {
        return $this->status;
    }
    
    private function isFile()
    {
        if(!$_FILES[$this->post_name]['name'])
        {
            $this->status = array(
                'status' => 'no_file'
            );
            
            return false;
        }
        
        return true;
    }
    
    private function uploadImage()
    {
        if(!$this->isFile())
            return false;
        
        $id = Strings::createID();
        $real_name = $_FILES[$this->post_name]['name'];
        
        $filetype = self::$base->filetype($real_name); 
        $file = $id.'.'.$filetype;
        
        $name = $this->getName($real_name);
        
        $uploaddir = ROOT.'content/uploads/bilder/';
        $uploadpath = $uploaddir.$file;
        
        $possible_filetypes = array('jpg', 'jpeg', 'png', 'pneg', 'gif');
        if(!in_array($filetype, $possible_filetypes))
        {
            $this->status = array(
                'status' => 'error',
                'error' => 'wrong filetype',
                'name' => $real_name,
                'should_go_to' => $uploadpath
            );
            
            return false;
        }
        
        if(move_uploaded_file($_FILES[$this->post_name]['tmp_name'], $uploadpath)) 
        {    
        	list($width, $height) = getimagesize($uploadpath);	
            
            if(!$this->append_to)
            {
                self::$fksdb->insert("files", array(
                	"kat" => 0,
                	"dir" => $this->dir,
                	"titel" => $name,
                	"last_type" => $filetype,
                	"timestamp" => self::$base->getTime(),
                	"last_timestamp" => self::$base->getTime(),
                	"last_ausrichtung" => ($width / $height),
                	"last_grafik" => 0,
                	"last_autor" => self::$user->getID()
                ));
                $stack_id = self::$fksdb->getInsertedID();
            }
            else
            {
                $stack_id = $this->append_to;
                
                self::$base->$fksdb->update("files", array(
                    "last_type" => $filetype,
                    "last_timestamp" => self::$base->getTime(),
                    "last_ausrichtung" => ($width / $height),
                    "last_autor" => self::$user->getID()
                ), array(
                    "id" => $stack_id
                ), 1);
            }
            
            self::$fksdb->insert("file_versions", array(
            	"stack" => $stack_id,
            	"file" => $id,
            	"type" => $filetype,
            	"timestamp" => self::$base->getTime(),
            	"ausrichtung" => ($width / $height),
            	"grafik" => 0,
            	"width" => $width,
            	"height" => $height,
            	"autor" => self::$user->getID() 
            ));
            $file_id = self::$fksdb->getInsertedID();
            
            $this->status = array(
                'status' => 'ok',
                'id' => $stack_id,
                'file_id' => $file_id,
                'name' => $name,
                'original_name' => $real_name,
                'show_name' => '<span>'.$name.'</span><em>.'.$filetype.'</em>',
                'file' => $uploadpath,
                'width' => $width,
                'height' => $height,
                'url' => DOMAIN.'/img/'.$stack_id.'-0-0-'.self::$base->slug($name).'.'.$filetype,
                'thumbnail_url' => DOMAIN.'/img/'.$stack_id.'-80-60-'.self::$base->slug($name).'.'.$filetype,
                'thumbnail_url_160' => DOMAIN.'/img/'.$stack_id.'-160-0-'.self::$base->slug($name).'.'.$filetype
            );
            
            return true;
        }
        
        $this->status = array(
            'status' => 'error',
            'error' => 'upload failed',
            'name' => $real_name,
            'should_go_to' => $uploadpath
        );
        
        return false;
    }
    
    private function getName($real_name)
    {
        $nameA = explode('.', $real_name);
        for($n = 0; $n < count($nameA) - 1; $n++)
            $name .= $nameA[$n].' ';
        return self::$base->slug($name);
    }
}
?>
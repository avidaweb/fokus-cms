<?php
class Suite
{
    private $assignment = array();
    private $the_key = '';
    
    function __construct()
    {
        $this->assignment = array(
            1	 => 'a',
            2	 => 'b',
            3	 => 'c',
            4	 => 'd',
            5	 => 'e',
            6	 => 'f',
            7	 => 'g',
            8	 => 'h',
            9	 => 'i',
            10	 => 'j',
            11	 => 'k',
            12	 => 'l',
            13	 => 'm',
            14	 => 'n',
            15	 => 'o',
            16	 => 'p',
            17	 => 'q',
            18	 => 'r',
            19	 => 's',
            20	 => 't',
            21	 => 'u',
            22	 => 'v',
            23	 => 'w',
            24	 => 'x',
            25	 => 'y',
            26	 => 'z',
            27	 => '1',
            28	 => '2',
            29	 => '3',
            30	 => '4',
            31	 => '5',
            32	 => '6',
            33	 => '7',
            34	 => '8',
            35	 => '9'
        ); 
        
        $this->the_key = $this->key(FOKUSKEY);

        $is_open = (!defined('FOKUSKEY')?true:(strlen(FOKUSKEY) == 47?false:true));
        define('FKS_OPEN', $is_open);
    }
    
    private function letterReverse($letter)
    {
        $key = array_search(strtolower($letter), $this->assignment) - 10;
         
        if($key > 26)
            $key = $key - 26;
        if($key < 1)
            $key = $key + 26;;
        return $this->assignment[$key];
    } 
    
    private function moduleReverse($letter)
    {
        $b = array_search(strtolower($letter), $this->assignment);
        
        if($b >= 12 && $b <= 14)
            return 1;
        elseif($b >= 15 && $b <= 17)
            return 2;
        elseif($b >= 18 && $b <= 20)
            return 3;
        elseif($b >= 21 && $b <= 23)
            return 4;
        elseif($b >= 24 && $b <= 26)
            return 5;
        else
            return 0;
    }
    
    private function key($key)
    {
        $w = array();
        $w = (object)$w;
        
        $block = explode('-', $key);
        
        // Block 1
        $day = array_search(strtolower($block[0][0]), $this->assignment); 
        $month = array_search(strtolower($block[0][1]), $this->assignment); 
        $year = array_search(strtolower($block[0][2]), $this->assignment).array_search(strtolower($block[0][3]), $this->assignment); 
        $index = array_search(strtolower($block[0][4]), $this->assignment);
        $w->date = $day.'.'.$month.'.'.$year;
        $w->index = $index;
        
        // Block 2
        $cc = 0;
        $blocknr = 2;
        for($d = 1; $d <= 5; $d++)
        {
            $w->modul[$d] = $this->moduleReverse($block[($blocknr - 1)][$cc]);
            $cc ++;
        }
        
        // Block 4
        $cc = 0;
        $blocknr = 4;
        for($d = 6; $d <= 10; $d++)
        {
            $w->modul[$d] = $this->moduleReverse($block[($blocknr - 1)][$cc]);
            $cc ++;
        }
        
        // Block 5
        $cc = 0;
        $blocknr = 5;
        for($d = 11; $d <= 15; $d++)
        {
            $w->modul[$d] = $this->moduleReverse($block[($blocknr - 1)][$cc]);
            $cc ++;
        }
        
        // Block 7
        $cc = 0;
        $blocknr = 7;
        for($d = 16; $d <= 20; $d++)
        {
            $w->modul[$d] = $this->moduleReverse($block[($blocknr - 1)][$cc]);
            $cc ++;
        }
        
        // Block 8
        $w->kunde = $this->letterReverse($block[7][0]).$this->letterReverse($block[7][1]).$this->letterReverse($block[7][2]).$this->letterReverse($block[7][3]).$this->letterReverse($block[7][4]);
        
        return $w; 
    }
    
    public function rm($modul, $need = 1)
    {
        if(!FOKUSKEY || FKS_OPEN)
            return 1;
        
        $mykey = $this->the_key;
        return ($mykey->modul[$modul] >= $need?$mykey->modul[$modul]:0);
    }
    
    public function getLimitOfLanguages()
    {
        $m2lan = array(
            0 => 3,
            1 => 3,
            2 => 10,
            3 => -1,
            4 => -1,
            5 => -1
        );
        
        return intval($m2lan[$this->rm(3)]);
    }
    
    public function getLimitOfUsers()
    {
        $m2us = array(
            0 => 3,
            1 => 3,
            2 => 10,
            3 => -1,
            4 => -1,
            5 => -1
        );
        
        return intval($m2us[$this->rm(9)]);
    }
}
?>
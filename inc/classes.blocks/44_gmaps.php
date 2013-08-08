<?php
class Block_44 extends BlockBasic
{
    private $lat = 0, $long = 0, $marker = '', $type = '';

    function __construct($static = array(), $dynamic = array())
    {
        parent::__construct($static, $dynamic);
    }
    
    
    public function get()
    {
        if(self::$fks->isDocumentPreview())
            return '<div class="ifehler">'.self::$trans->__('Google Maps kann im Vorschau-Modus nicht angezeigt werden').'</div>';
        
        $tag = 'div';
        if($this->attr['tag']) 
            $tag = $this->attr['tag']; 
        
        $c = self::$base->fixedUnserialize($this->html);
        if(!is_array($c)) return '';
        $c = (object)$c;
        
        $gen_id = 'gmaps'.Strings::createID();    
        
        $mtypes = array('ROADMAP', 'SATELLITE', 'HYBRID', 'TERRAIN');
        
        $this->html = '
        <script src="http://maps.google.com/maps/api/js?sensor=false"></script>
        <script>
            function f'.$gen_id.'() { 
                var latlng = new google.maps.LatLng('.floatval($c->lat).', '.floatval($c->long).');
                var myOptions = {
                    zoom: '.intval($c->zoom).',
                    center: latlng,
                    mapTypeId: google.maps.MapTypeId.'.$mtypes[$c->typ].'
                };
                var map = new google.maps.Map(document.getElementById("'.$gen_id.'"), myOptions);
                
                '.($c->marker?'
                var marker = new google.maps.Marker({
                    position: latlng, 
                    map: map, 
                    title: "'.htmlentities(strip_tags(nl2br($c->marker_text))).'"
                });':'').'
            }
            
            $(function(){
                f'.$gen_id.'();
            })
        </script>';

        $this->lat = floatval($c->lat);
        $this->long = floatval($c->long);
        $this->marker = ($c->marker?htmlentities(strip_tags(nl2br($c->marker_text))):'');
        $this->type = $mtypes[$c->typ];

        $this->html = $this->executeCallback($this->html);
                
        $html = $this->html_before.'<'.$tag.' id="'.$gen_id.'"'.$this->add_css.' class="'.trim('fks_gmaps '.$this->add_class).'" style="width:'.intval($c->width).($c->width_typ == 0?'%':'px').';height:'.intval($c->height).'px;">'.$c->nojs.'</'.$tag.'>'.$this->html.$this->html_after;
        
        return $html;  
    }

    public function getHookAttributes()
    {
        $self = array(
            'html' => $this->html,
            'lat' => $this->lat,
            'long' => $this->long,
            'marker' => $this->marker,
            'type' => $this->type
        );

        return array_merge($self, $this->getHookStandardAttributes());
    }
}   
?>
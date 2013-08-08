<?php
class ThreeColors
{
    private static $api;
    private $template_name, $template_id;

    public function __construct($api)
    {
        self::$api = $api;

        $this->template_name = 'Three Colors';
        $this->template_id = 'three-colors';

        $this->createApps();
        $this->createWidgets();
    }

    private function createApps()
    {
        self::$api->initApp($this->template_id, 'Template-Einstellungen: '.$this->template_name, array($this, 'getAppContent'), array(
            'rights_callback' => array($this, 'checkAppRights'),
            'menu_parent' => 'structures',
            'menu_position' => 80,
            'menu_title' => 'Einstellungen "'.$this->template_name.'"',
            'window_width' => 560,
            'autosave' => true,
            'js_file' => self::$api->getTemplateDir().'settings/script.js',
            'css_file' => self::$api->getTemplateDir().'settings/style.css'
        ));
    }

    public function checkAppRights($data, $static)
    {
        return self::$api->isSuperAdmin();
    }

    public function getAppContent($data, $static)
    {
        extract($static);

        include(self::$api->getTemplateConfig());

        $rtn = '
        <h2>Globale Einstellungen</h2>
        <p>
            <label for="'.$this->template_id.'-color">Standard-Hintergrundfarbe:</label>
            <span>
                <select name="color" id="'.$this->template_id.'-color">';
                    foreach($template['custom_fields']['color']['values'] as $ccode => $cname)
                        $rtn .= '<option value="'.$ccode.'"'.($ccode == $data->color?' selected':'').'>'.$cname.'</option>';
                $rtn .= '
                </select>
            </span>
            <small class="clr"></small>
        </p>
        <p>
            <label>Logo-Grafik:</label>
            <span>
                <input type="hidden" name="logo" value="'.$data->logo.'" />

                <button class="logo_select">Logo auswählen</button>
                '.(self::$api->checkUserRights('files', 'add_file')?'<button class="logo_new">Logo hochladen</button>':'').'
                '.(self::$api->checkUserRights('files', 'edit_file')?'<button class="logo_edit" data-file="'.$data->logo.'"'.(!$data->logo?' style="display:none;"':'').'>Logo bearbeiten</button>':'').'

                <img src="'.self::$api->getImageUrl($data->logo, 200, 0).'" alt=" " width="200" class="logo'.(!$data->logo?' hidden':'').'" />
                <br /><em>Ideales Format: 200 Pixel Breite</em>
            </span>
            <small class="clr"></small>
        </p>
        <br /><br />

        <h2>Sprachspezifische Einstellungen</h2>';

        foreach(self::$api->getActiveLanguages() as $code => $l)
        {
            $rtn .= '
            <h3>
                <img src="'.$l['flag'][16].'" alt="" />
                '.$trans->__($l['code']).'
            </h3>
            <p>
                <label for="'.$this->template_id.'-'.$code.'-area-a">Name Bereich A:</label>
                <span>
                    <input id="'.$this->template_id.'-'.$code.'-area-a" type="text" name="area['.$code.'][a]" value="'.$data->area[$code]['a'].'" />
                </span>
                <small class="clr"></small>
            </p>
            <p>
                <label for="'.$this->template_id.'-'.$code.'-area-b">Name Bereich B:</label>
                <span>
                    <input id="'.$this->template_id.'-'.$code.'-area-b" type="text" name="area['.$code.'][b]" value="'.$data->area[$code]['b'].'" />
                </span>
                <small class="clr"></small>
            </p>
            <p>
                <label for="'.$this->template_id.'-'.$code.'-area-c">Name Bereich C:</label>
                <span>
                    <input id="'.$this->template_id.'-'.$code.'-area-c" type="text" name="area['.$code.'][c]" value="'.$data->area[$code]['c'].'" />
                </span>
                <small class="clr"></small>
            </p>
            <br />';
        }

        return $rtn;
    }

    private function createWidgets()
    {
        self::$api->initWidget($this->template_id, $this->template_name, array($this, 'getWidget'), 1, 1, array('fks.openApp', $this->template_id));
    }

    public function getWidget()
    {
        return 'Hier kannst du die Einstellungen deines Templates <strong>'.$this->template_name.'</strong> bearbeiten.';
    }
}
?>
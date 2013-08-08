<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('fks', 'opt'))
    exit($user->noRights());
    
if($index == 's420')
{    
    $handle = opendir("../content/dklassen");
    while($file = readdir ($handle)) 
    {
        if($file != "." && $file != "..") 
        {
            $has_dklassen = true;
            break;
        }
    }
    
    echo '
    <h1>'.$trans->__('Systemeinstellungen.').'</h1>
    
    <div class="box fehlerbox warning_no_seo"'.(!$base->getOpt('noseo')?' style="display:none;"':'').'>
        <strong>'.$trans->__('Website für Suchmaschinen gesperrt').'</strong>
        '.$trans->__('Die gesamte Website ist momentan für Suchmaschinen gesperrt. Das bedeutet, dass Ihre Inhalte nicht in Suchmaschinen wie Google oder Bing erscheinen. Im Reiter <em>System</em> können Sie die Suchmaschinen-Sichtbarkeit umstellen.').'
    </div>
    
    <div class="box">
        <div id="einstellungen">
            <ul id="etabs">
                <li><a href="#e_set_allgemein">'.$trans->__('Allgemein').'</a></li>
                <li><a href="#e_set_system">'.$trans->__('System').'</a></li>
                <li><a href="#e_set_templates">'.$trans->__('Templates').'</a></li>
                <li><a href="#e_set_sprachen">'.$trans->__('Sprachen').'</a></li>
                <li'.(!$has_dklassen?' style="display:none;"':'').'><a href="#e_set_dk">'.$trans->__('Dokumentenklassen').'</a></li>
                <li'.(!$suite->rm(12)?' style="display:none;"':'').'><a href="#e_set_backup">'.$trans->__('Backup').'</a></li>
                <li><a href="#e_set_fehler">'.$trans->__('Fehlerseiten').'</a></li>
            </ul>
            
            <div id="e_set_allgemein" class="etab"></div>
            <div id="e_set_system" class="etab"></div>
            <div id="e_set_templates" class="etab"></div>
            <div id="e_set_sprachen" class="etab"></div>
            <div id="e_set_dk" class="etab"></div>
            <div id="e_set_backup" class="etab"></div>
            <div id="e_set_fehler" class="etab"></div>
        </div>
    </div>
    
    <div class="box_save">
        <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
        <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
    </div>';
}     
?>
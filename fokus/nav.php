<?php
define('IS_BACKEND', true, true);

require_once('../inc/header.php');
require_once('login.php');
   
   
$only_navi = $fksdb->save($_REQUEST['only_navi']); 

$dka = $base->db_to_array($base->getOpt('dk')); 
$dk = (object)$dka; 

$dkklassen = array();
$dkklassen_color = array();
$ordner = "../content/dklassen";

if(is_dir($ordner))
{
    $handle = opendir($ordner);
    while ($file = readdir ($handle)) 
    {
        if(!$file || $file == "." || $file == "..") 
            continue;
            
        $has_dklassen = true;
        
        $fk = $base->open_dklasse($ordner.'/'.$file);
        $slug = $base->slug($fk['name']); 
        
        if(!$fk['name'] || $dk->n_uebersicht[$slug])
            continue;
            
        $dkklassen[$file] = $fk['name'];
        $dkklassen_color[$file] = $dk->color[$slug];
    }
}

$naviindiv = array(
    'struktur' => array(
        'n120' => 'id="n120" class="inc_structure" rel="0"',
        'n100' => 'id="n100" class="inc_structure" rel="0"',
        'n170' => 'id="n170" class="inc_structure" rel="0"',
        'n180' => 'id="n180" class="inc_structure" rel="0"',
        'n190' => 'id="n190" class="inc_structure" rel="0"'
    ),
    'dokumente' => array(
        'n200' => 'id="n200" class="inc_documents" rel="0"',
        'n210' => 'id="n210" class="inc_documents" rel="0"',
        'n220' => 'id="n200" class="inc_documents" rel="1"',
        'n280' => 'id="n280" class="inc_documents" rel="0"'
    ),
    'dateien' => array(
        'n400' => 'id="n400" class="inc_files" rel="0"',
        'n402' => 'id="n400" class="inc_files" rel="2"'
    ),
    'personen' => array(
        'n510' => 'id="n510" class="inc_users" rel="1"',
        'n515' => 'id="n515" class="inc_users" rel="2"',
        'n520' => 'id="n520" class="inc_users" rel="1"',
        'n540' => 'id="n540" class="inc_users" rel="0"'
    ),
    'kommunikation' => array(
        'n610' => 'id="n610" class="inc_communication" rel="0"',
        'n630' => 'id="n630" class="inc_communication" rel="0"',
        'n640' => 'id="n640" class="inc_communication" rel="0"',
        'n650' => 'id="n650" class="inc_communication" rel="0"',
        'n660' => 'id="n660" class="inc_communication" rel="0"'
    ),
    'suche' => array(
        's110' => 'id="s110" class="sub_last" rel="0"',
        's120' => 'id="s115" class="sub_last" rel="0"',
        's121' => 'id="s120" class="sub_last" rel="0"'
    )
);

$add_dk = '';
if($user->r('dok') && count($dkklassen))
{
    foreach($dkklassen as $file => $dkname)
    {
        $dkslug = $base->slug($dkname);
        if($rechte['dok']['dk'] && !$rechte['dok']['dklasse'][$dkslug])
            continue;
            
        $bg_image = ($dkklassen_color[$file]?' style="background-image: url('.DOMAIN.'/fokus/ajax-documents/dclass-bg.php?color='.$dkklassen_color[$file].');"':'');
        
        $add_dk .= '
        <li class="dk">
            <a id="n290" class="inc_documents" rel="'.$file.'"'.$bg_image.'>
                '.$dkname.'
            </a>
        </li>';
    }
}

/** APPS - start */
include_once(ROOT.'inc/classes.backend/class.apps.php');
$apps = new Apps($classes);
/** APPS - end */

echo '
<nav id="navigationO">
    <a class="frontend" href="'.DOMAIN.'" target="_blank" title="'.$trans->__('Zur Website wechseln').'"></a>

    <ul id="nav" class="calibri">
        '.($user->r('str')?'
        <li id="n100" class="nchild">
            <a '.(array_key_exists($user->getIndiv()->open_struktur, $naviindiv['struktur'])?$naviindiv['struktur'][$user->getIndiv()->open_struktur]:'class="none"').'>
                '.$trans->__('Struktur.').'
            </a>
            <ul class="smooth">
                '.($user->r('str', 'ele')?'<li><a id="n120" class="inc_structure" rel="0">'.$trans->__('Aktuelle Struktur bearbeiten').'</a></li>':'').'
                '.($user->r('str', 'struk')?'<li><a id="n100" class="inc_structure" rel="0">'.$trans->__('Strukturauswahl öffnen').'</a></li>':'').'
                '.($user->r('str', 'menue') && count($base->getActiveTemplateConfig('menus'))?'<li><a id="n170" class="inc_structure" rel="0">'.$trans->__('Menüs bearbeiten').'</a></li>':'').'
                '.($user->r('str', 'slots') && count($base->getActiveTemplateConfig('slots'))?'<li><a id="n180" class="inc_structure" rel="0">'.$trans->__('Slots bearbeiten').'</a></li>':'').'
                '.($user->r('str', 'kat') && $has_dklassen?'<li><a id="n190" class="inc_structure" rel="0">'.$trans->__('Kategorien verwalten').'</a></li>':'').'
                
                '.$apps->getMenu('structures').'
            </ul>
        </li>':'').'
        '.($user->r('dok')?'
        <li id="n200" class="nchild">
            <a '.(array_key_exists($user->getIndiv()->open_dokumente, $naviindiv['dokumente'])?$naviindiv['dokumente'][$user->getIndiv()->open_dokumente]:'class="none"').'>
                '.$trans->__('Dokumente.').'
            </a>
            <ul class="smooth">
                '.($user->r('dok') && (!$rechte['dok']['dk'] || $rechte['dok']['dklasse'][0])?'<li><a id="n200" class="inc_documents" rel="0">'.$trans->__('Dokumentenverwaltung &ouml;ffnen').'</a></li>':'').'
                '.$add_dk.'
                '.($user->r('dok', 'new')?'<li><a id="n210" class="inc_documents" rel="0">'.$trans->__('Neues Dokument anlegen').'</a></li>':'').'
                '.($user->r('dok', 'publ') && $user->r('dok', 'publ_all')?'<li><a id="n200" class="inc_documents" rel="1">'.$trans->__('Dokumentenfreigabe').'</a></li>':'').'
                '.($user->r('dok', 'ezsb')?'<li><a id="n280" class="inc_documents" rel="0">'.$trans->__('Zuständigkeiten verwalten').'</a></li>':'').'
                
                '.$apps->getMenu('documents').'
            </ul>
        </li>':'').'
        '.($user->r('dat') && ($user->r('dat', 'bilder') || $user->r('dat', 'dateien'))?' 
        <li id="n400" class="nchild">
            <a '.(array_key_exists($user->getIndiv()->open_dateien, $naviindiv['dateien'])?$naviindiv['dateien'][$user->getIndiv()->open_dateien]:'class="none"').'>
                '.$trans->__('Dateien.').'
            </a>
            <ul class="smooth">
                '.($user->r('dat', 'bilder')?'<li><a id="n400" class="inc_files" rel="0">'.$trans->__('Bilder verwalten').'</a></li>':'').'
                '.($user->r('dat', 'dateien')?'<li><a id="n400" class="inc_files" rel="2">'.$trans->__('Dateien verwalten').'</a></li>':'').'
                
                '.$apps->getMenu('files').'
            </ul>
        </li>':'').'
        '.($user->r('per')?' 
        <li id="n500" class="nchild">
            <a '.(array_key_exists($user->getIndiv()->open_personen, $naviindiv['personen'])?$naviindiv['personen'][$user->getIndiv()->open_personen]:'class="none"').'>
                '.$trans->__('Personen.').'
            </a>
            <ul class="smooth">
                '.((($user->r('per', 'edit') || $user->r('per', 'new')) && (!$user->r('per', 'type') || $user->r('per', 'kunden')))?'<li><a id="n510" class="inc_users" rel="1">'.$trans->__('Kunden verwalten').'</a></li>':'').'
                '.((($user->r('per', 'edit') || $user->r('per', 'new')) && (!$user->r('per', 'type') || $user->r('per', 'mitarbeiter')))?'<li><a id="n515" class="inc_users" rel="2">'.$trans->__('Mitarbeiter verwalten').'</a></li>':'').'
                '.($user->r('per', 'firma')?'<li><a id="n520" class="inc_users" rel="1">'.$trans->__('Firmen verwalten').'</a></li>':'').'
                '.($user->r('per', 'rollen')?'<li><a id="n540" class="inc_users" rel="0">'.$trans->__('Rollen &amp; Rechte verwalten').'</a></li>':'').'
                
                '.$apps->getMenu('users').'
            </ul>
        </li>':'').'
        '.($user->r('kom')?' 
        <li id="n600" class="nchild">
            <a '.(array_key_exists($user->getIndiv()->open_kommunikation, $naviindiv['kommunikation'])?$naviindiv['kommunikation'][$user->getIndiv()->open_kommunikation]:'class="none"').'>
                '.$trans->__('Kommunikation.').'
            </a>
            <ul class="smooth">
                '.(($user->r('kom', 'nledit') || $user->r('kom', 'nlsend')) && $suite->rm(5)?'<li><a id="n610" class="inc_communication" rel="0">'.$trans->__('Newsletter verwalten').'</a></li>':'').'
                '.($user->r('kom', 'kkanal')?'<li><a id="n630" class="inc_communication" rel="0">'.$trans->__('Kommunikationkan&auml;le auswerten').'</a></li>':'').'
                '.($user->r('kom', 'pn') && $suite->rm(10)?'<li><a id="n640" class="inc_communication" rel="0">'.$trans->__('Nachrichten verwalten').'</a></li>':'').'
                '.($user->r('kom', 'livetalk') && $suite->rm(10)?'<li><a id="n650" class="inc_communication" rel="0">'.$trans->__('Livetalk').'</a></li>':'').'
                '.($user->r('kom', 'pinnwand')?'<li><a id="n660" class="inc_communication" rel="0">'.$trans->__('Pinnwand').'</a></li>':'').'
                
                '.$apps->getMenu('communication').'
            </ul>
        </li>':'').'
        '.($user->r('suc')?' 
        <li id="s100" class="nchild">
            <a '.(array_key_exists($user->getIndiv()->open_suche, $naviindiv['suche'])?$naviindiv['suche'][$user->getIndiv()->open_suche]:'class="none"').'>
                '.$trans->__('Suche.').'
            </a>
            <ul class="smooth" data-isopen="false">
                '.($user->r('suc', 'suche')?'<li><a id="s110" class="sub_last" rel="0">'.$trans->__('fokus durchsuchen').'</a></li>':'').'
                '.($user->r('suc', 'papierkorb')?'<li><a id="s115" class="sub_last" rel="0">'.$trans->__('Papierkorb öffnen').'</a></li>':'').'
                '.($user->r('suc', 'zuve')?'<li class="dlast"><a id="s120" class="sub_last" rel="0">'.$trans->__('Zuletzt verwendet').'</a></li>':'').'
                
                '.$apps->getMenu('items').'
            </ul>
        </li>':'').'';
        
        /*
        <li id="s400" class="nchild">
            <a '.(array_key_exists($user->getIndiv()->open_fokus, $naviindiv['fokus'])?$naviindiv['fokus'][$user->getIndiv()->open_fokus]:$naviindiv['fokus']['web']).'>
                '.$trans->__('fokus.').'
            </a>
            <ul class="smooth">
                <li><a href="'.$domain.'" target="_blank" rel="extern">'.$trans->__('Website öffnen').'</a></li> 
                '.($user->r('fks', 'ghost') && $suite->rm(4)?'<li><a href="'.$domain.'/fokus/sub_ghost.php?index=go" target="_blank" rel="extern">'.$trans->__('Website-Direktbearbeitung').'</a></li>':'').'
                '.($user->r('fks', 'foresight') && $suite->rm(2)?'<li><a id="s450" class="sub_foresight" rel="0">'.$trans->__('Website-Vorschau').'</a></li>':'').'
                '.($user->r('fks', 'indiv')?'<li><a id="s500" class="sub_indiv" rel="0">'.$trans->__('fokus individualisieren').'</a></li>':'').'
                '.($user->r('fks', 'pure')?'<li><a id="s490" class="sub_settings" rel="0">'.$trans->__('fokus aufräumen').'</a></li>':'').'
                '.($user->r('fks', 'opt')?'<li><a id="s420" class="sub_settings" rel="0">'.$trans->__('Systemeinstellungen').'</a></li>':'').' 
                '.($user->r('fks', 'sitzung')?'<li><a id="s440" class="sub_info" rel="0">'.$trans->__('Sitzungs-Information').'</a></li>':'').' 
                '.($user->r('fks', 'notiz')?'<li><a id="s480" class="sub_last" rel="0">'.$trans->__('Persönliche Notizen').'</a></li>':'').'
                '.(FKS_OPEN?'<li><a href="http://community.fokus-cms.de/" target="_blank" rel="extern">'.$trans->__('Support &amp; Feedback').'</a></li>':'').'  
                <li><a href="enter.php?logout=1" rel="extern">'.$base->user($user->getID(), ' ', 'vorname', 'nachname').' '.$trans->__('abmelden').'</a></li>
            </ul>
        </li> 
        */
        
        echo '
    </ul>
</nav>

'.(!$only_navi?'

<footer id="footer">
    <div id="taskleiste" class="smooth"></div>
</footer>':'');

?>
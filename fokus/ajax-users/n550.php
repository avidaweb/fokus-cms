<?php
if(!$user->r('per', 'rollen') || $index != 'n550')
    exit($user->noRights());
    
$r = $fksdb->fetch("SELECT * FROM ".SQLPRE."roles WHERE id = '".$rel."' LIMIT 1");

if($rel == 1 || (!$r && $rel))
    exit();

$ro = $base->db_to_array($r->rechte);
if(!is_array($ro)) $ro = array();

$fehler = $base->fixedUnserialize($r->fehler);
if(!is_array($fehler)) $fehler = array();

if($rel)
    $user->lastUse('rolle', $r->id);

$custom_rights = $api->getRights();
    
$is_str = (is_array($ro['str'])?true:false);
$is_dok = (is_array($ro['dok'])?true:false);
$is_dat = (is_array($ro['dat'])?true:false);
$is_per = (is_array($ro['per'])?true:false);
$is_kom = (is_array($ro['kom'])?true:false);
$is_suc = (is_array($ro['suc'])?true:false);
$is_fks = (is_array($ro['fks'])?true:false);
$is_api = (is_array($ro['api'])?true:false);

echo '
<h1>'.($rel?$trans->__('Rolle bearbeiten.'):$trans->__('Neue Rolle anlegen.')).'</h1>

<form id="rolle_edit">
<div class="box">
    <input type="hidden" id="r_id" name="r_id" value="'.$r->id.'" />
    
    <h3 class="calibri">'.$trans->__('Eigenschaften.').'</h3>
    <div class="area last_area">
        <p>
            <h4 class="calibri">'.$trans->__('Name der Rolle').'</h4>
            <input type="text" required value="'.$r->titel.'" name="titel" class="titel" />
        </p>
        <p class="trenner">
            <h4 class="calibri">'.$trans->__('Kurzbeschreibung der Rolle').'</h4>
            <input type="text" value="'.$r->beschr.'" name="beschr" class="beschr" />
        </p>
    </div>
    
    <h3 class="calibri">'.$trans->__('Backend.').'</h3>
    <div class="area">
        <h2 class="calibri">
            <input type="checkbox" name="r[is_str]" value="1" id="r_str"'.($is_str?' checked':'').' />
            <label for="r_str">'.$trans->__('Struktur').'</label>
        </h2>
        <article>
            <p>
                <input type="checkbox" name="r[str][ele]" value="1" id="r_str_ele"'.($ro['str']['ele']?' checked':'').' />
                <label for="r_str_ele">'.$trans->__('Darf Strukturelemente verwalten').'</label>
            </p>
            <div class="mopt lmore'.(!$ro['str']['ele']?' not_active':'').'">
                <input type="checkbox" name="r[str][opt]" value="1" id="r_str_opt"'.($ro['str']['opt'] && $ro['str']['ele']?' checked':'').''.(!$ro['str']['ele']?' disabled':'').' />
                <label for="r_str_opt">'.$trans->__('Rechte auf <strong>Strukturelemente</strong> gezielt einschränken').'</label>
                
                <div class="more"'.($ro['str']['opt'] && $ro['str']['ele']?' style="display:block;"':'').'>
                    <span class="desc">'.$trans->__('Oben gesetzte Rechte gelten für folgende Aktionen und Bereiche:').'</span>
                    
                    <p>
                        <input type="checkbox" name="r[str][lebensdauer]" value="1" id="r_str_lebensdauer"'.($ro['str']['lebensdauer']?' checked':'').' />
                        <label for="r_str_lebensdauer">'.$trans->__('Darf die Lebensdauer eines Strukturelements einschränken').'</label>
                    </p>
                    <p>
                        <input type="checkbox" name="r[str][rollen]" value="1" id="r_str_rollen"'.($ro['str']['rollen']?' checked':'').' />
                        <label for="r_str_rollen">'.$trans->__('Darf ein Strukturelement nur gewissen Rollen zugänglich machen').'</label>
                    </p>
                    <p>
                        <input type="checkbox" name="r[str][seo]" value="1" id="r_str_seo"'.($ro['str']['seo']?' checked':'').' />
                        <label for="r_str_seo">'.$trans->__('Darf die Suchmaschinen-Sichtbarkeit eines Elements festlegen').'</label>
                    </p>
                    <p>
                        <input type="checkbox" name="r[str][dk]" value="1" id="r_str_dk"'.($ro['str']['dk']?' checked':'').' />
                        <label for="r_str_dk">'.$trans->__('Darf den Bereich &quot;Zuordnung &amp; Automatisierung&quot; verwalten').'</label>
                    </p>
                    
                    <div class="template lmore">
                        <input type="checkbox" name="r[str][template]" value="1" id="r_str_template"'.($ro['str']['template']?' checked':'').' />
                        <label for="r_str_template">'.$trans->__('Darf nur folgende Template-Dateien auswählen').'</label>
                        
                        <div class="more"'.($ro['str']['template']?' style="display:block;"':'').'>';
                            $ordner = '../content/templates';
                            $handle = opendir($ordner);
                            while ($file = readdir ($handle)) 
                            {
                                if($file != '.' && $file != '..') 
                                {
                                    $npointer = $ordner.'/'.$file;
                                    if(is_dir($npointer)) 
                                    {                                            
                                        $template_temp = $base->open_template_config($npointer.'/config.php');
                                        
                                        if($template_temp['name'])
                                        {
                                            $tslug = $base->slug($template_temp['name']);
                                            $dat = $template_temp['files'];
                                            if(!is_array($dat)) $dat = array();
                                            
                                            echo '
                                            <div class="temp">
                                                <h4 class="calibri"><span>Template</span> '.$template_temp['name'].'</h4>
                                                <div class="td">
                                                    <p class="tdn">
                                                        <input class="has_standards" type="checkbox" name="r[str][td]['.$tslug.'][index]" value="1" id="r_str_td_'.$tslug.'_index"'.($ro['str']['td'][$tslug]['index']?' checked':'').' />
                                                        <label for="r_str_td_'.$tslug.'_index">'.$trans->__('Standard Template-Datei').'</label>
                                                    </p>
                                                    <p class="tda'.(!$ro['str']['td'][$tslug]['index']?' not_active':'').'">
                                                        <input type="radio" name="r[str][tda]['.$tslug.']" value="" id="r_str_tda_'.$tslug.'_index"'.(!$ro['str']['tda'][$tslug]?' checked':'').''.(!$ro['str']['td'][$tslug]['index']?' disabled':'').' />
                                                        <label for="r_str_tda_'.$tslug.'_index">'.$trans->__('Als Standard').'</label>
                                                    </p>
                                                </div>';
                                                foreach($dat as $td1 => $td2)
                                                {
                                                    echo '
                                                    <div class="td">
                                                        <p class="tdn">
                                                            <input class="has_standards" type="checkbox" name="r[str][td]['.$tslug.']['.$td2.']" value="1" id="r_str_td_'.$tslug.'_'.$td2.'"'.($ro['str']['td'][$tslug][$td2]?' checked':'').' />
                                                            <label for="r_str_td_'.$tslug.'_'.$td2.'">'.Strings::cut($td1, 25).'</label>
                                                        </p>
                                                        <p class="tda'.(!$ro['str']['td'][$tslug][$td2]?' not_active':'').'">
                                                            <input type="radio" name="r[str][tda]['.$tslug.']" value="'.$td2.'" id="r_str_tda_'.$tslug.'_'.$td2.'"'.($ro['str']['tda'][$tslug] == $td2 && $ro['str']['td'][$tslug][$td2]?' checked':'').''.(!$ro['str']['td'][$tslug][$td2]?' disabled':'').' />
                                                            <label for="r_str_tda_'.$tslug.'_'.$td2.'">'.$trans->__('Als Standard').'</label>
                                                        </p>
                                                    </div>';
                                                }
                                            echo '
                                            </div>';                                                            
                                        }
                                    }
                                }
                            } 
                            closedir($handle);    
                        echo '
                        </div>
                    </div>
                </div>
            </div>
            <p>
                <input type="checkbox" name="r[str][struk]" value="1" id="r_str_struk"'.($ro['str']['struk']?' checked':'').' />
                <label for="r_str_struk">'.$trans->__('Darf Strukturen verwalten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[str][menue]" value="1" id="r_str_menue"'.($ro['str']['menue']?' checked':'').' />
                <label for="r_str_menue">'.$trans->__('Darf Menüs verwalten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[str][slots]" value="1" id="r_str_slots"'.($ro['str']['slots']?' checked':'').' />
                <label for="r_str_slots">'.$trans->__('Darf Slots verwalten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[str][kat]" value="1" id="r_str_kat"'.($ro['str']['kat']?' checked':'').' />
                <label for="r_str_kat">'.$trans->__('Darf Kategorien verwalten').'</label>
            </p>
        </article>
    </div>
    <div class="area">
        <h2 class="calibri">
            <input type="checkbox" name="r[is_dok]" value="1" id="r_dok"'.($is_dok?' checked':'').' />
            <label for="r_dok">'.$trans->__('Dokumente').'</label>
        </h2>
        <article>
            <p>
                <input type="checkbox" name="r[dok][edit]" value="1" id="r_dok_edit"'.($ro['dok']['edit']?' checked':'').' />
                <label for="r_dok_edit">'.$trans->__('Darf alle bestehenden Dokumente bearbeiten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dok][new]" value="1" id="r_dok_new"'.($ro['dok']['new']?' checked':'').' />
                <label for="r_dok_new">'.$trans->__('Darf neue Dokumente anlegen und eigene Dokumente bearbeiten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dok][del]" value="1" id="r_dok_del"'.($ro['dok']['del']?' checked':'').' />
                <label for="r_dok_del">'.$trans->__('Darf bestehende Dokumente löschen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dok][publ]" value="1" id="r_dok_publ"'.($ro['dok']['publ']?' checked':'').' />
                <label for="r_dok_publ">'.$trans->__('Darf Dokumente direkt freigeben (veröffentlichen)').'</label>
            </p>
            <p'.(!$ro['dok']['publ']?' style="display:none;"':'').' class="r_dok_publ_all">
                <input type="checkbox" name="r[dok][publ_all]" value="1" id="r_dok_publ_all"'.($ro['dok']['publ_all']?' checked':'').' />
                <label for="r_dok_publ_all">'.$trans->__('Darf Dokumente anderer Benutzer freigeben (via Dokumentenfreigabe)').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dok][cats]" value="1" id="r_dok_cats"'.($ro['dok']['cats']?' checked':'').' />
                <label for="r_dok_cats">'.$trans->__('Darf Dokumenten Kategorien zuweisen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dok][acopy]" value="1" id="r_dok_acopy"'.($ro['dok']['acopy']?' checked':'').' />
                <label for="r_dok_acopy">'.$trans->__('Darf Arbeitskopien übernehmen').'</label>
            </p>';
            $zq = $fksdb->query("SELECT id, name FROM ".SQLPRE."responsibilities WHERE papierkorb = '0' ORDER BY name");
            if($fksdb->count($zq))
            {
                if(!is_array($ro['dok']['zsba']))
                    $ro['dok']['zsba'] = array();
                    
                echo '
                <div class="mopt lmore moptnb">
                    <input type="checkbox" name="r[dok][zsb]" value="1" id="r_dok_zsb"'.($ro['dok']['zsb']?' checked':'').' />
                    <label for="r_dok_zsb">'.$trans->__('Dokumentenrechte auf <strong>Zuständigkeitsbereiche</strong> einschränken').'</label>
                    
                    <div class="more"'.($ro['dok']['zsb']?' style="display:block;"':'').'>
                        <span class="desc">'.$trans->__('Oben gesetzte Rechte gelten für folgende Zuständigkeitsbereiche:').'</span>';
                        while($z = $fksdb->fetch($zq))
                        {
                            echo '
                            <p>
                                <input type="checkbox" name="r[dok][zsba][]" value="'.$z->id.'" id="r_dok_zsba'.$z->id.'"'.(in_array($z->id, $ro['dok']['zsba'])?' checked':'').' />
                                <label for="r_dok_zsba'.$z->id.'">'.$z->name.'</label>
                            </p>';
                        }
                        echo '
                    </div>
                </div>';
            }
            
            $dkklassen_inc = array();
            $ordner = "../content/dklassen";
            if(is_dir($ordner))
            {
                $handle = opendir($ordner);
                while ($file = readdir ($handle)) 
                {
                    if($file != "." && $file != "..") 
                    {
                        $fk = $base->open_dklasse($ordner.'/'.$file);
                        
                        if($fk['name'])
                            $dkklassen_inc[$file] = $fk;
                    }
                }
            }
            if(count($dkklassen_inc))
            {
                echo '
                <div class="mopt lmore moptnb">
                    <input type="checkbox" name="r[dok][dk]" value="1" id="r_dok_dk"'.($ro['dok']['dk']?' checked':'').' />
                    <label for="r_dok_dk">'.$trans->__('Dokumentenrechte auf <strong>Dokumentenklassen</strong> einschränken').'</label>
                    
                    <div class="more"'.($ro['dok']['dk']?' style="display:block;"':'').'>
                        <span class="desc">'.$trans->__('Oben gesetzte Rechte gelten für folgende Dokumentenklassen:').'</span>
                        
                        <div class="td">
                            <p class="tdn">
                                <input class="has_standards" type="checkbox" name="r[dok][dklasse][0]" value="1" id="r_dok_dklasse_0"'.($ro['dok']['dklasse'][0]?' checked':'').' />
                                <label for="r_dok_dklasse_0"><em>'.$trans->__('Keine Dokumentenklasse / freies Dokument').'</em></label>
                            </p>
                            <p class="tda'.(!$ro['dok']['dklasse'][0]?' not_active':'').'">
                                <input type="radio" name="r[dok][dklassea]" value="" id="r_dok_dklassea_0"'.(!$ro['dok']['dklassea']?' checked':'').''.(!$ro['dok']['dklasse'][0]?' disabled':'').' />
                                <label for="r_dok_dklassea_0">'.$trans->__('Als Standard').'</label>
                            </p>
                        </div>';
                        foreach($dkklassen_inc as $file => $fk)
                        {
                            $dslug = $base->slug($fk['name']);
                            
                            echo '
                            <div class="td">
                                <p class="tdn">
                                    <input class="has_standards" type="checkbox" name="r[dok][dklasse]['.$dslug.']" value="1" id="r_dok_dklasse_'.$dslug.'"'.($ro['dok']['dklasse'][$dslug]?' checked':'').' />
                                    <label for="r_dok_dklasse_'.$dslug.'">'.Strings::cut($fk['name'], 25).'</label>
                                </p>
                                <p class="tda'.(!$ro['dok']['dklasse'][$dslug]?' not_active':'').'">
                                    <input type="radio" name="r[dok][dklassea]" value="'.$dslug.'" id="r_dok_dklassea_'.$dslug.'"'.($ro['dok']['dklassea'][$dslug] == $dslug && $ro['dok']['dklasse'][$dslug]?' checked':'').''.(!$ro['dok']['dklasse'][$dslug]?' disabled':'').' />
                                    <label for="r_dok_dklassea_'.$dslug.'">'.$trans->__('Als Standard').'</label>
                                </p>
                            </div>';
                        }  
                        echo '  
                        <div class="clearer"></div>
                    </div>
                </div>';
            }
            
            echo '
            <div class="mopt lmore">
                <input type="checkbox" name="r[dok][css]" value="1" id="r_dok_css"'.($ro['dok']['css']?' checked':'').' />
                <label for="r_dok_css">'.$trans->__('Dokumentenrechte auf <strong>Formatierungen &amp; Optionen</strong> einschränken').'</label>
                
                <div class="more"'.($ro['dok']['css']?' style="display:block;"':'').'>
                    <span class="desc">'.$trans->__('Beim Arbeiten mit Dokumenten stehen folgende Optionen zur Verfügung:').'</span>
                    <p>
                        <input type="checkbox" name="r[dok][cssf]" value="1" id="r_dok_cssf"'.($ro['dok']['cssf']?' checked':'').' />
                        <label for="r_dok_cssf">'.$trans->__('Darf eigene CSS-Formatierungen definieren').'</label>
                    </p>
                    <p>
                        <input type="checkbox" name="r[dok][cssk]" value="1" id="r_dok_cssk"'.($ro['dok']['cssk']?' checked':'').' />
                        <label for="r_dok_cssk">'.$trans->__('Darf vordefinierte CSS-Klassen auswählen').'</label>
                    </p>
                </div>
            </div>
            <p>
                <input type="checkbox" name="r[dok][ezsb]" value="1" id="r_dok_ezsb"'.($ro['dok']['ezsb']?' checked':'').' />
                <label for="r_dok_ezsb">'.$trans->__('Darf Zuständigkeiten verwalten').'</label>
            </p>
        </article>
    </div>
    <div class="area">
        <h2 class="calibri">
            <input type="checkbox" name="r[is_dat]" value="1" id="r_dat"'.($is_dat?' checked':'').' />
            <label for="r_dat">'.$trans->__('Dateien').'</label>
        </h2>
        <article>
            <p>
                <input type="checkbox" name="r[dat][bilder]" value="1" id="r_dat_bilder"'.($ro['dat']['bilder']?' checked':'').' />
                <label for="r_dat_bilder">'.$trans->__('Darf auf den Bereich Bilder zugreifen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dat][dateien]" value="1" id="r_dat_dateien"'.($ro['dat']['dateien']?' checked':'').' />
                <label for="r_dat_dateien">'.$trans->__('Darf auf den Bereich Dateien zugreifen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dat][new]" value="1" id="r_dat_new"'.($ro['dat']['new']?' checked':'').' />
                <label for="r_dat_new">'.$trans->__('Darf neue Bilder und Dateien hochladen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dat][edit]" value="1" id="r_dat_edit"'.($ro['dat']['edit']?' checked':'').' />
                <label for="r_dat_edit">'.$trans->__('Darf Bilder und Dateien bearbeiten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dat][del]" value="1" id="r_dat_del"'.($ro['dat']['del']?' checked':'').' />
                <label for="r_dat_del">'.$trans->__('Darf Bilder und Dateien löschen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dat][ver]" value="1" id="r_dat_ver"'.($ro['dat']['ver']?' checked':'').' />
                <label for="r_dat_ver">'.$trans->__('Darf neue Versionen von Bildern und Dateien hochladen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[dat][dir]" value="1" id="r_dat_dir"'.($ro['dat']['dir']?' checked':'').' />
                <label for="r_dat_dir">'.$trans->__('Darf die Ordnerstrukturen verwalten').'</label>
            </p>
        </article>
    </div>
    <div class="area">
        <h2 class="calibri">
            <input type="checkbox" name="r[is_per]" value="1" id="r_per"'.($is_per?' checked':'').' />
            <label for="r_per">'.$trans->__('Personen').'</label>
        </h2>
        <article>
            <p>
                <input type="checkbox" name="r[per][edit]" value="1" id="r_per_edit"'.($ro['per']['edit']?' checked':'').' />
                <label for="r_per_edit">'.$trans->__('Darf bestehende Personen bearbeiten &amp; sperren').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[per][new]" value="1" id="r_per_new"'.($ro['per']['new']?' checked':'').' />
                <label for="r_per_new">'.$trans->__('Darf neue Personen anlegen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[per][del]" value="1" id="r_per_del"'.($ro['per']['del']?' checked':'').' />
                <label for="r_per_del">'.$trans->__('Darf bestehende Personen löschen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[per][prolle]" value="1" id="r_per_prolle"'.($ro['per']['prolle']?' checked':'').' />
                <label for="r_per_prolle"><strong>'.$trans->__('Darf Personen individuell Rollen vergeben').'</strong></label>
            </p>
            <div class="mopt lmore">
                <input type="checkbox" name="r[per][type]" value="1" id="r_per_type"'.($ro['per']['type']?' checked':'').' />
                <label for="r_per_type">'.$trans->__('Personenrechte auf <strong>Kunden oder Mitarbeiter</strong> einschränken').'</label>
                
                <div class="more"'.($ro['per']['type']?' style="display:block;"':'').'>
                    <span class="desc">'.$trans->__('Die oben gesetzten Rechte gelten für folgende Personengruppe:').'</span>
                    <p>
                        <input type="checkbox" name="r[per][kunden]" value="1" id="r_per_kunden"'.($ro['per']['kunden']?' checked':'').' />
                        <label for="r_per_kunden">'.$trans->__('Kunden').'</label>
                    </p>
                    <p>
                        <input type="checkbox" name="r[per][mitarbeiter]" value="1" id="r_per_mitarbeiter"'.($ro['per']['mitarbeiter']?' checked':'').' />
                        <label for="r_per_mitarbeiter">'.$trans->__('Mitarbeiter').'</label>
                    </p>
                </div>
            </div>
            <p>
                <input type="checkbox" name="r[per][firma]" value="1" id="r_per_firma"'.($ro['per']['firma']?' checked':'').' />
                <label for="r_per_firma">'.$trans->__('Darf Firmen verwalten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[per][rollen]" value="1" id="r_per_rollen"'.($ro['per']['rollen']?' checked':'').' />
                <label for="r_per_rollen"><strong>'.$trans->__('Darf Rollen &amp; Rechte verwalten').'</strong></label>
            </p>
        </article>
    </div>
    <div class="area">
        <h2 class="calibri">
            <input type="checkbox" name="r[is_kom]" value="1" id="r_kom"'.($is_kom?' checked':'').' />
            <label for="r_kom">'.$trans->__('Kommunikation').'</label>
        </h2>
        <article>
            <p>
                <input type="checkbox" name="r[kom][nledit]" value="1" id="r_kom_nledit"'.($ro['kom']['nledit']?' checked':'').' />
                <label for="r_kom_nledit">'.$trans->__('Darf Newsletter anlegen und bearbeiten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[kom][nlsend]" value="1" id="r_kom_nlsend"'.($ro['kom']['nlsend']?' checked':'').' />
                <label for="r_kom_nlsend">'.$trans->__('Darf Newsletter versenden').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[kom][kkanal]" value="1" id="r_kom_kkanal"'.($ro['kom']['kkanal']?' checked':'').' />
                <label for="r_kom_kkanal">'.$trans->__('Darf Kommunikationskanäle verwalten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[kom][pn]" value="1" id="r_kom_pn"'.($ro['kom']['pn']?' checked':'').' />
                <label for="r_kom_pn">'.$trans->__('Darf Nachrichten verwalten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[kom][livetalk]" value="1" id="r_kom_livetalk"'.($ro['kom']['livetalk']?' checked':'').' />
                <label for="r_kom_livetalk">'.$trans->__('Darf Livetalk-Chat einsehen und nutzen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[kom][pinnwand]" value="1" id="r_kom_pinnwand"'.($ro['kom']['pinnwand']?' checked':'').' />
                <label for="r_kom_pinnwand">'.$trans->__('Darf Pinnwand einsehen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[kom][pinnwandedit]" value="1" id="r_kom_pinnwandedit"'.($ro['kom']['pinnwandedit']?' checked':'').' />
                <label for="r_kom_pinnwandedit">'.$trans->__('Darf Pinnwand bearbeiten').'</label>
            </p>
        </article>
    </div>
    <div class="area">
        <h2 class="calibri">
            <input type="checkbox" name="r[is_suc]" value="1" id="r_suc"'.($is_suc?' checked':'').' />
            <label for="r_suc">'.$trans->__('Suche').'</label>
        </h2>
        <article>
            <p>
                <input type="checkbox" name="r[suc][suche]" value="1" id="r_suc_suche"'.($ro['suc']['suche']?' checked':'').' />
                <label for="r_suc_suche">'.$trans->__('Darf fokus durchsuchen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[suc][papierkorb]" value="1" id="r_suc_papierkorb"'.($ro['suc']['papierkorb']?' checked':'').' />
                <label for="r_suc_papierkorb">'.$trans->__('Darf Elemente aus dem Papierkorb wiederherstellen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[suc][zuve]" value="1" id="r_suc_zuve"'.($ro['suc']['zuve']?' checked':'').' />
                <label for="r_suc_zuve">'.$trans->__('Darf auf die zuletzt verwendeten Elemente zugreifen').'</label>
            </p>
        </article>
    </div>
    <div class="area'.(!count($custom_rights)?' last_area':'').'">
        <h2 class="calibri">
            <input type="checkbox" name="r[is_fks]" value="1" id="r_fks"'.($is_fks?' checked':'').' />
            <label for="r_fks">fokus</label>
        </h2>
        <article>
            <p>
                <input type="checkbox" name="r[fks][ghost]" value="1" id="r_fks_ghost"'.($ro['fks']['ghost']?' checked':'').' />
                <label for="r_fks_ghost">'.$trans->__('Darf die Website-Direktbearbeitung nutzen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[fks][foresight]" value="1" id="r_fks_foresight"'.($ro['fks']['foresight']?' checked':'').' />
                <label for="r_fks_foresight">'.$trans->__('Darf die Website-Vorschau verwenden').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[fks][indiv]" value="1" id="r_fks_indiv"'.($ro['fks']['indiv']?' checked':'').' />
                <label for="r_fks_indiv">'.$trans->__('Darf fokus gemäß den eigenen Wünschen individualisieren').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[fks][pure]" value="1" id="r_fks_pure"'.($ro['fks']['pure']?' checked':'').' />
                <label for="r_fks_pure">'.$trans->__('Darf auf die System-Bereinigung zugreifen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[fks][sitzung]" value="1" id="r_fks_sitzung"'.($ro['fks']['sitzung']?' checked':'').' />
                <label for="r_fks_sitzung">'.$trans->__('Darf sich seine Sitzungs-Informationen anzeigen lassen').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[fks][notiz]" value="1" id="r_fks_notiz"'.($ro['fks']['notiz']?' checked':'').' />
                <label for="r_fks_notiz">'.$trans->__('Darf die Persönlichen Notizen verwenden').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[fks][extensions]" value="1" id="r_fks_extensions"'.($ro['fks']['extensions']?' checked':'').' />
                <label for="r_fks_extensions">'.$trans->__('Darf Erweiterungen verwalten').'</label>
            </p>
            <p>
                <input type="checkbox" name="r[fks][opt]" value="1" id="r_fks_opt"'.($ro['fks']['opt']?' checked':'').' />
                <label for="r_fks_opt"><strong>'.$trans->__('Darf auf die Systemeinstellungen zugreifen').'</strong></label>
            </p>
        </article>
    </div>';

    if(count($custom_rights))
    {
        echo '
        <div class="area last_area">
            <h2 class="calibri">
                <input type="checkbox" name="r[is_api]" value="1" id="r_api"'.($is_api?' checked':'').' />
                <label for="r_api">Sonstiges</label>
            </h2>
            <article>';
                foreach($custom_rights as $rid => $right)
                {
                    echo '
                    <p>
                        <input type="checkbox" name="r[api]['.$rid.']" value="1" id="r_api_'.$rid.'"'.($ro['api'][$rid]?' checked':'').' />
                        <label for="r_api_'.$rid.'">'.$trans->__($right['title']).'</label>
                    </p>';
                }
            echo '
            </article>
        </div>';
    }

    echo '
    <h3 class="calibri">'.$trans->__('Fehlerseiten.').'</h3>
    <div class="area">
        '.$trans->__('Wenn ein Benutzer auf ein Strukturelement zugreifen möchte, dass diesem gemäß seiner Rolle nicht zugänglich ist, wird folgende Dokumentenkonstellation als Fehlerseite angezeigt.').'
        
        <strong>'.$trans->__('Zugeordnete Dokumente:').'</strong> 
        
        <div id="Rstruk_doks" class="dok_zuordnung">
            <img src="images/loading_grey.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
        </div>
    </div>
    
    <div class="clear"></div>
</div>

<div class="box_save">
    <button class="bs1">'.$trans->__('verwerfen').'</button>
    <button class="bs2">'.$trans->__('Rolle speichern').'</button>
</div>
</form>';
?>
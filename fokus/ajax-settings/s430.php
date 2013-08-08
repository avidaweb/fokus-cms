<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

ignore_user_abort(true);
set_time_limit(0);

if(!$user->r('fks', 'opt'))
    exit($user->noRights());
    
if($index == 's430')
{
    $a = $fksdb->save($_REQUEST['a']); 
    
    $e = $base->getOpt();
    
    if($a == 'allgemein') // Allgemein
    {
        $fks_suite = 'fokus Suite '.substr(FKS_VERSION, 0, 4).' - ';
        
        if(FKS_OPEN)
        {
            $fks_suite .= 'OPEN';
        }
        else
        {
            if($suite->rm(3) == 1)
                $fks_suite .= 'WORK';
            elseif($suite->rm(3) == 2)
                $fks_suite .= 'COMMERCE';
            else
                $fks_suite .= 'ENTERPRISE';
        }
        
        echo '
        <div class="ebox">
            <div class="rd">
                <h2 class="calibri">'.$trans->__('Dieses System wurde installiert von:').'</h2>
            </div>
        </div>
        
        <div class="ebox">
            <div class="rd">
                <table class="betreiber">
                    <tr>
                        <td class="a">'.$trans->__('Firma:').'</td>
                        <td>'.$e->firma.'</td>
                    </tr>
                    <tr>
                        <td>'.$trans->__('Ansprechpartner:').'</td>
                        <td>'.$e->vorname.' '.$e->nachname.'</td>
                    </tr>
                    <tr>
                        <td>'.$trans->__('Straße &amp; Hausnummer:').'</td>
                        <td>'.$e->str.' '.$e->hn.'</td>
                    </tr>
                    <tr>
                        <td>'.$trans->__('PLZ &amp; Ort:').'</td>
                        <td>'.$e->plz.' '.$e->ort.'</td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="ebox">
            <div class="rd">
                <table class="betreiber">
                    <tr>
                        <td class="a">'.$trans->__('E-Mail-Adresse:').'</td>
                        <td><input type="text" id="e_email" value="'.$e->email.'" /></td>
                    </tr>
                </table>
            </div>
        </div>
        
        <div class="ebox">
            <div class="rd">
                <table class="betreiber">
                    <tr>
                        <td class="a">'.$trans->__('Versionsnummer:').'</td>
                        <td>'.number_format(FKS_VERSION, 2, '.', '').'</td>
                    </tr>
                </table>
            </div>
        </div>';
    }
    elseif($a == 'system') // System 
    { 
        $e_h = floor($e->logout / 3600);
        $e_m = floor(($e->logout - ($e_h * 3600)) / 60);
        
        $vor_titel = $base->fixedUnserialize($e->vor_titel);
        $nach_titel = $base->fixedUnserialize($e->nach_titel);
        
        $cf = $base->getActiveTemplateConfig('global_custom_fields');
        if(!is_array($cf)) $cf = array();
        $cfa = count($cf);
        
        $spr = $base->db_to_array($e->cf);
    
        echo '
        <form id="atitel" method="post">
        <div class="ebox">
            <div class="at">
                <strong>'.$trans->__('Automatischer Seitentitel').'</strong>';
                            
                foreach($base->getActiveLanguages() as $sp)
                {
                    echo '
                    <table>
                        <tr>
                            <td colspan="3" class="ue">
                                <img width="22" src="'.$trans->getFlag($sp).'" alt="" />
                                <strong>'.$trans->__(strtoupper($sp)).'</strong>
                            </td>
                        </tr>
                        <tr>
                            <td><input type="text" name="vor_titel['.$sp.']" value="'.$vor_titel[$sp].'" /></td>
                            <td><strong class="nds">'.$trans->__('+ [Name der Seite] +').'</strong></td>
                            <td><input type="text" name="nach_titel['.$sp.']" value="'.$nach_titel[$sp].'" /></td>
                        </tr>
                        <tr class="las'.($cfa?' lastbeforecf':'').'">
                            <td>'.$trans->__('Text vor dem jeweiligen Titel').'</td>
                            <td></td>
                            <td>'.$trans->__('Text nach dem jeweiligen Titel').'</td>
                        </tr>';

                        $ccf = 0;
                        foreach($cf as $k => $v)
                        {
                            $ccf ++; 
                            
                            echo '
                            <tr class="cf'.($ccf == $cfa?' lastone':'').'">
                                <td colspan="3">
                                    <label>'.($v['name']).'</label>';
                                    if(!$v['type'] || $v['type'] == 'text' || $v['type'] == 'input')
                                    {
                                        echo '<input type="text" name="cf['.$sp.']['.$k.']" value="'.htmlspecialchars_decode($spr[$sp][$k]).'" />';
                                    }
                                    elseif($v['type'] == 'textarea')
                                    {
                                        echo '<textarea name="cf['.$sp.']['.$k.']">'.($spr[$sp][$k]).'</textarea>';
                                    }
                                    elseif($v['type'] == 'checkbox')
                                    {
                                        echo '<input style="width:auto;" type="checkbox" name="cf['.$sp.']['.$k.']" value="fks_true"'.($spr[$sp][$k] == 'fks_true'?' checked="checked"':'').' />';
                                    }
                                    elseif($v['type'] == 'select' && is_array($v['values']))
                                    {
                                        echo '<select name="cf['.$sp.']['.$k.']">';
                                        foreach($v['values'] as $x => $y)
                                            echo '<option value="'.$x.'"'.($spr[$sp][$k] == $x?' selected="selected"':'').'>'.$y.'</option>';
                                        echo '                     
                                        </select>';
                                    }
                                echo '
                                </td>
                            </tr>';  
                        }
                        
                    echo '
                    </table>';
                }
                
            echo '
            </div>
        </div>
        </form>
        <div class="ebox">
            <strong class="ls">'.$trans->__('Sicherheit:').'</strong>
            <div class="rs sicherheit">
                <p>
                    '.$trans->__('Benutzer automatisch nach %1 Stunden und %2 Minuten abmelden.', false, array(
                        '<input type="number" id="e_h" value="'.$e_h.'" />',
                        '<input type="number" id="e_m" value="'.$e_m.'" />'
                    )).'
                </p>
                <p>
                    <input type="checkbox" value="1" id="login_captcha"'.($e->login_captcha?' checked':'').' />
                    <label for="login_captcha">'.$trans->__('Anmeldevorgang zusätzlich durch Sicherheitsfrage schützen?').'</label>
                </p>
            </div>
        </div>
        <div class="ebox">
            <strong class="ls">'.$trans->__('Suchmaschinen:').'</strong>
            <div class="rs seo">
                <table>
                    <tr>
                        <td>'.$trans->__('Die gesamte Webeite für Suchmaschinen sperren:').'</td>
                        <td>
                            <select id="noseo">
                                <option value="0"'.(!$e->noseo?' selected="selected"':'').'>'.$trans->__('Nein, Suchmaschinen dürfen die Webeite besuchen').'</option>
                                <option value="1"'.($e->noseo?' selected="selected"':'').'>'.$trans->__('Ja, Indexierung durch Suchmaschinen global unterbinden').'</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>'.$trans->__('Bevorzugte Domain-Darstellung:').'</td>
                        <td>
                            <select id="www">
                                <option value="0"'.($e->www == 0?' selected="selected"':'').'>'.$trans->__('Keine bevorzugte Darstellung').'</option>
                                <option value="1"'.($e->www == 1?' selected="selected"':'').'>'.$trans->__('Domain mit www anzeigen').'</option>
                                <option value="2"'.($e->www == 2?' selected="selected"':'').'>'.$trans->__('Domain ohne www anzeigen').'</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>';
        
        if(count($base->getActiveTemplateConfig('files')) || count($base->getActiveTemplateConfig('mobile')))
        {
            echo '
            <div class="ebox">
                <strong class="ls">'.$trans->__('Frontend-Suche:').'</strong>
                <div class="rs seo">
                    <table>';
                        if(count($base->getActiveTemplateConfig('files')))
                        {
                            echo '
                            <tr>
                                <td>'.$trans->__('Template-Datei:').'</td>
                                <td>
                                    <select id="q_template">
                                        <option value=""'.(!$e->q_template?' selected="selected"':'').'>'.$trans->__('Standard-Template').'</option>';
                                        foreach($base->getActiveTemplateConfig('files') as $n => $u)
                                        {
                                            echo '<option value="'.$u.'"'.($e->q_template == $u?' selected="selected"':'').'>'.$n.'</option>';
                                        }
                                    echo '
                                    </select>
                                </td>
                            </tr>';
                        }
                        if(count($base->getActiveTemplateConfig('mobile')))
                        {
                            echo '
                            <tr>
                                <td>'.$trans->__('Mobile Template-Datei:').'</td>
                                <td>
                                    <select id="q_template_mobile">';
                                        foreach($base->getActiveTemplateConfig('mobile') as $n => $u)
                                        {
                                            echo '<option value="'.$u.'"'.($e->q_template_mobile == $u?' selected="selected"':'').'>'.$n.'</option>';
                                        }
                                    echo '
                                    </select>
                                </td>
                            </tr>';
                        }
                    echo '
                    </table>
                </div>
            </div>';
        }
        
        $rw_base = rtrim(stripslashes(dirname(dirname($_SERVER['SCRIPT_NAME']))).'/', '/');
        if(!$rw_base) $rw_base = '/';
        
        echo '
        <div class="ebox">
            <strong class="ls">'.$trans->__('Media-Dateien:').'</strong>
            <div class="rs seo media_daten">
                <table>
                    <tr>
                        <td>'.$trans->__('Qualität der Vorschaubilder:').'</td>
                        <td><input type="number" value="'.$e->thumb_quality.'" id="thumb_quality" size="3" />%</td>
                    </tr>
                </table>
                    
                <div id="slider_thumb"></div>
            </div>
        </div>
        
        <div class="ebox">
            <strong class="ls">'.$trans->__('Experten-Einstellungen:').'</strong>
            <div class="rs seo experte">
                <table>
                    <tr>
                        <td>'.$trans->__('RewriteBase:').'</td>
                        <td>
                            <input type="text" value="'.$e->rewritebase.'" id="rewritebase" />
                            <small>('.$trans->__('Empfohlen:').' <em>'.$rw_base.'</em>)</small>
                            
                            <div>
                                <iframe src="../test-mod-rewrite/" width="340" height="22"></iframe>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td>'.$trans->__('GZIP-Komprimierung:').'</td>
                        <td>
                            <select id="gzip">
                                <option value="0"'.(!$e->gzip?' selected':'').'>'.$trans->__('Nein, GZIP-Komprimierung nicht aktivieren').'</option>
                                <option value="1"'.($e->gzip?' selected':'').'>'.$trans->__('Ja, GZIP-Komprimierung aktivieren').'</option>
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <td>'.$trans->__('Dateien zusammenführen:').'</td>
                        <td>
                            <select id="merge_css">
                                <option value="0"'.(!$e->merge_css?' selected':'').'>'.$trans->__('Nein, CSS-Dateien einzeln einbinden').'</option>
                                <option value="1"'.($e->merge_css?' selected':'').'>'.$trans->__('Ja, CSS-Dateien zusammenführen').'</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>
        </div>';
    }
    elseif($a == 'templates') // Templates 
    {  
        $taktiv = $base->getActiveTemplateConfig('name');
        
        $vorhanden = array();
        $fehler = array(); 
        
        $ordner = '../content/templates';
        $handle = opendir($ordner);
        while ($file = readdir ($handle)) 
        {
            if($file != '.' && $file != '..') 
            {
                $npointer = $ordner.'/'.$file;
                if(is_dir($npointer)) 
                {
                    if(!file_exists($npointer.'/config.php') || !file_exists($npointer.'/index.php'))
                    {
                        $fehler[] = $trans->__('<li>Im Ordner <em>%1</em> existiert keine config.php oder index.php</li>', false, array($file));
                        continue;
                    }
                    
                    $template_temp = $base->open_template_config($npointer.'/config.php');
                    
                    if(!$template_temp['name'])
                    {
                        $fehler[] = $trans->__('<li>Im Template <em>%1</em> wurde in der config.php kein valider Bezeichner für das Template hinterlegt</li>', false, array($file));
                        continue;
                    }
                    
                    $vorhanden[$file] = $template_temp;
                }
            }
        } 
        closedir($handle);     
        
        if(count($fehler))
        {
            echo '
            <div class="ifehler">
                <strong>'.$trans->__('Bei einem oder mehreren Templates traten Fehler auf:').'</strong>
                <ul>'.implode('', $fehler).'</ul> 
            </div>';
        }
        
        echo '
        <div class="ebox templ1">
            <h2 class="templ">'.$trans->__('Momentan gewähltes Template:').'</h2>
            <strong>'.$taktiv.'</strong>
        </div>
        
        <div class="ebox templ2">
            <h2 class="templ">'.$trans->__('Verfügbare Templates').'</h2>
            
            <div class="templates">';
            
                foreach($vorhanden as $file => $cur_temp)
                {
                    $npointer = $ordner.'/'.$file;
                    $screenshot = $npointer.'/screenshot.jpg';
                    
                    echo '
                    <div class="template">
                        <div class="screenshot">
                            '.(file_exists($screenshot)?'
                                <img src="'.$screenshot.'" alt="Screenshot" width="120" />'
                                :
                                '<span>'.$trans->__('kein Bild<br />verfügbar').'</span>'
                            ).'
                        </div>
                        <table>
                            <tr>
                                <td class="fi">'.$trans->__('Name:').'</td>
                                <td><strong class="calibri">'.$cur_temp['name'].'</strong></td>
                            </tr>
                            <tr class="moreinfo">
                                <td>'.$trans->__('Template-Dateien:').'</td>
                                <td>'.(count($cur_temp['files'])).'</td>
                            </tr>
                            <tr class="moreinfo">
                                <td>'.$trans->__('Menüs:').'</td>
                                <td>'.(count($cur_temp['menus'])).'</td>
                            </tr>
                            <tr class="moreinfo">
                                <td>'.$trans->__('Slots:').'</td>
                                <td>'.(count($cur_temp['slots'])).'</td>
                            </tr>
                            <tr class="moreinfo">
                                <td>'.$trans->__('Benutzer-Felder:').'</td>
                                <td>'.(count($cur_temp['custom_fields'])).'</td>
                            </tr>
                            <tr class="moreinfo">
                                <td>'.$trans->__('CSS-Klassen:').'</td>
                                <td>'.(count($cur_temp['classes'])).'</td>
                            </tr>
                            <tr>
                                <td colspan="2" class="unten">
                                    '.($taktiv == $cur_temp['name']?
                                        '<em>'.$trans->__('Template ist aktiviert').'</em>'
                                        :
                                        '<a class="changetemplate" rel="'.$file.'">'.$trans->__('Template aktivieren').'</a>'
                                    ).'
                                </td>
                            </tr>
                        </table>
                    </div>';
                }
            
            echo '
            </div>
        </div>';
    }
    elseif($a == 'sprachen') // Sprachen 
    { 
        echo '
        <form id="sprachenform" method="post">
        <div class="ebox">
            <strong class="ls">'.$trans->__('Sprachen:').'</strong>
            <div class="rs sprachen">
                '.($suite->getLimitOfLanguages() == -1?
                    $trans->__('Sie können beliebig viele Sprachen mit dieser Lizenz verwenden und verwalten.')
                    :
                    ($suite->getLimitOfLanguages() == 1?
                        $trans->__('Sie können lediglich 1 Sprache mit dieser Lizenz verwenden und verwalten')
                        :
                        $trans->__('Sie können ingesamt %1 Sprachen mit dieser Lizenz verwenden und verwalten.', false, array($suite->getLimitOfLanguages()))
                    )
                ).'  
                
                <br />
                '.($base->getActiveLanguagesCount() != 1?
                    $trans->__('Sie haben aktuell %1 Sprachen gewählt.', false, array($base->getActiveLanguagesCount()))
                    :
                    $trans->__('Sie haben aktuell 1 Sprache gewählt')
                ).'
                
                <table id="sprachenuebersicht">';            
                foreach($base->getActiveLanguages() as $sp)
                {
                    echo '
                    <tr>
                        <td><img src="'.$trans->getFlag($sp, 2).'" alt="" /></td>
                        <td><strong>'.$trans->__(strtoupper($sp)).'</strong></td>
                    </tr>';
                }  
                echo '
                </table>
                
                '.$trans->__('Zum Verwalten und hinzufügen von Sprachen öffnen Sie bitte die untenstehende Sprachen-Übersicht.').'
                
                <br /><br />
                <a>'.$trans->__('Sprachenübersicht öffnen').'</a>
                
                <table>';
                    foreach($trans->getLanguages() as $lan)
                    {
                        echo '
                        <tr class="bg'.$base->countup().'">
                            <td class="first"><label for="clang_'.$lan.'"><img src="'.$trans->getFlag($lan, 2).'" alt="" /></label></td>
                            <td>
                                <label for="clang_'.$lan.'">
                                    '.$trans->__(strtoupper($lan)).' 
                                    <em>('.strtoupper($lan).')</em>
                                </label>
                            </td>
                            <td class="hinten"><input type="checkbox" name="land[]" id="clang_'.$lan.'" value="'.strtolower($lan).'"'.(in_array(strtolower($lan), $base->getActiveLanguages())?' checked="checked"':'').' /></td>
                        </tr>';    
                    }
                echo '
                </table>
            </div>
        </div>
        </form>';
    }
    elseif($a == 'dk') // Dokumentenklassen 
    { 
        $dka = $base->db_to_array($e->dk);
        $dk = (object)$dka;
        
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
                    
                    $dkklassen_inc[$file] = $fk;
                }
            }
        }
        
        $adk = count($dkklassen_inc);
        
        function dk_titel($trans, $felder, $wert = '', $first = false)
        {
            $rtn = ($first?'':'<option value=""'.(!$wert?' selected':'').'>'.$trans->__('kein Attribut').'</option>');
            foreach($felder as $bid => $x)
            {
                if($x['type'] < 30)
                    $rtn .= '<option value="'.$bid.'"'.($bid == $wert?' selected':'').'>'.$x['atr']['name'].'</option>';
            }
            return $rtn;
        }
        
        function dk_zeichen($trans, $wert)
        {
            $rtn = '
            <option value="0"'.($wert == 0?' selected="selected"':'').'>'.$trans->__('volle Textlänge').'</option>
            <option value="5"'.($wert == 5?' selected="selected"':'').'>'.$trans->__('max. %1 Zeichen', false, array('5')).'</option>
            <option value="10"'.($wert == 10?' selected="selected"':'').'>'.$trans->__('max. %1 Zeichen', false, array('10')).'</option>
            <option value="20"'.($wert == 20?' selected="selected"':'').'>'.$trans->__('max. %1 Zeichen', false, array('20')).'</option>
            <option value="50"'.($wert == 50?' selected="selected"':'').'>'.$trans->__('max. %1 Zeichen', false, array('50')).'</option>
            <option value="100"'.($wert == 100?' selected="selected"':'').'>'.$trans->__('max. %1 Zeichen', false, array('100')).'</option>
            <option value="200"'.($wert == 200?' selected="selected"':'').'>'.$trans->__('max. %1 Zeichen', false, array('200')).'</option>
            <option value="500"'.($wert == 500?' selected="selected"':'').'>'.$trans->__('max. %1 Zeichen', false, array('500')).'</option>
            <option value="1000"'.($wert == 1000?' selected="selected"':'').'>'.$trans->__('max. %1 Zeichen', false, array('1000')).'</option>
            <option value="2000"'.($wert == 2000?' selected="selected"':'').'>'.$trans->__('max. %1 Zeichen', false, array('2000')).'</option>';
            
            return $rtn;
        }
                    
        echo '
        <form id="dkform" method="post">
            <h3 class="calibri">'.$trans->__('Dokumentenklassen-Einstellungen.').'</h3>
            <p class="infotext">
                Es '.($adk == 1?'ist':'sind').' aktuell '.$adk.' Dokumentenklasse'.($adk == 1?'':'n').' in diesem fokus-System integriert. 
                Folgend können Sie die Einstellungen für die Dokumentenklasse'.($adk == 1?'':'n').'  verwalten.
                
                '.($adk == 1?
                    $trans->__('Es ist aktuell 1 Dokumentenklasse in diesem fokus-System integriert. Folgend können Sie die Einstellungen für diese Dokumentenklasse verwalten')
                    :
                    $trans->__('Es sind aktuell %1 Dokumentenklassen in diesem fokus-System integriert. Folgend können Sie die Einstellungen für die Dokumentenklassen  verwalten.', false, array($adk))
                ).'
            </p>';
            
            foreach($dkklassen_inc as $file => $fk)
            {
                if(!$fk['name'])
                    continue;
                    
                $slug = $base->slug($fk['name']);
                
                if($fk['related'])
                {
                    $fk = $base->open_dklasse($ordner.'/'.$fk['related']);
                    $tmp_inhalt = $fk['content'];
                    
                    $fk = $base->open_dklasse($ordner.'/'.$file);
                    $fk['content'] = $tmp_inhalt;
                }
                
                        
                $result = preg_match_all('@:(.*)\):@iU', $fk['content'], $subpattern);  
                $felder = array();
    
                foreach($subpattern[1] as $s1 => $s2)
                {
                    $b = explode('(', $s2);  
                    
                    if(in_array($b[0], $base->getBlocks('dclass')))
                    { 
                        $atr = $base->get_attributes($b[1]);     
                        if(!$atr['name'])
                            continue;                            
                                   
                        $block_type = array_search($b[0], $base->getBlocks('dclass'));    
                        $bid = $base->slug($atr['name']);
                        
                        if($block_type > 30)
                            continue;
                        
                        $felder[$bid] = array(
                            'atr' => $atr,
                            'type' => $block_type
                        );
                    }
                }  
                
                $color = ($dk->color[$slug]?$dk->color[$slug]:'88d7ff');        
                    
                echo '
                <div class="greybox">
                    <h2 class="calibri">'.$trans->__('Dokumentenklasse &quot;%1&quot;', false, array($fk['name'])).'</h2>
                    
                    <a class="rbutton rollout">'.$trans->__('Dokumentenklasse <span>öffnen</span>').'</a>
                    
                    <div class="showmore">
                        <div class="top">
                            <p>
                                <input type="checkbox" id="n_uebersicht_'.$slug.'" name="n_uebersicht['.$slug.']" value="1"'.($dk->n_uebersicht[$slug]?' checked="checked"':'').' class="n_uebersicht" />
                                <label for="n_uebersicht_'.$slug.'">
                                    '.$trans->__('Diese Dokumentenklasse <strong>nicht</strong> mit eigener Dokumentenübersicht im Bereich Dokumente anzeigen').'
                                </label>
                            </p>
                            <div class="show_color"'.($dk->n_uebersicht[$slug]?' style="display:none;"':'').'>
                                '.$trans->__('Darstellungsfarbe für diese Dokumentenklasse:').'
                                <input type="hidden" name="color['.$slug.']" value="'.$color.'" />
                                <div class="colorSelector" style="background-color:#'.$color.';"></div>
                            </div>
                        </div>
                        
                        <div class="auto">
                            <p>
                                <input type="checkbox" id="n_titel_uebersicht'.$slug.'" name="n_titel_uebersicht['.$slug.']" value="1"'.($dk->n_titel_uebersicht[$slug]?' checked="checked"':'').' />
                                <label for="n_titel_uebersicht'.$slug.'">
                                    '.$trans->__('Bezeichner für Dokumente <strong>nicht</strong> in der Dokumentenübersicht anzeigen').'
                                </label>
                            </p>
                            <p>
                                <input type="checkbox" id="auto_titel'.$slug.'" class="auto_titel" name="auto_titel['.$slug.']" value="1"'.($dk->auto_titel[$slug]?' checked="checked"':'').' />
                                <label for="auto_titel'.$slug.'">
                                    '.$trans->__('Bezeichner für Dokumente in dieser Dokumentenklasse aus folgenden Attributen automatisch generieren').'
                                </label>
                            </p>
                            <table class="select'.($dk->auto_titel[$slug]?' is_shown':'').'"'.($dk->auto_titel[$slug]?' style="display:table;"':'').'>
                                <tr class="first">
                                    <td>
                                        <select name="at_1['.$slug.']" class="at" data-zeichen="atz1">
                                            '.dk_titel($trans, $felder, $dk->at_1[$slug], true).'
                                        </select>
                                    </td>
                                    <td>
                                        -
                                    </td>
                                    <td>
                                        <select name="at_2['.$slug.']" class="at" data-zeichen="atz2">
                                            '.dk_titel($trans, $felder, $dk->at_2[$slug]).'
                                        </select>
                                    </td>
                                    <td>
                                        -
                                    </td>
                                    <td>
                                        <select name="at_3['.$slug.']" class="at" data-zeichen="atz3">
                                            '.dk_titel($trans, $felder, $dk->at_3[$slug]).'
                                        </select>
                                    </td>
                                </tr>
                                <tr class="second">
                                    <td colspan="2" class="atz1">
                                        <select name="atz_1['.$slug.']">
                                            '.dk_zeichen($trans, $dk->atz_1[$slug]).'
                                        </select>
                                    </td>
                                    <td colspan="2" class="atz2'.(!$dk->at_2[$slug] || !$dk->auto_titel[$slug]?' nobg':'').'">
                                        <select name="atz_2['.$slug.']"'.(!$dk->at_2[$slug] || !$dk->auto_titel[$slug]?' style="display:none;"':'').'>
                                            '.dk_zeichen($trans, $dk->atz_2[$slug]).'
                                        </select>
                                    </td>
                                    <td colspan="2" class="atz3'.(!$dk->at_3[$slug] || !$dk->auto_titel[$slug]?' nobg':'').'">
                                        <select name="atz_3['.$slug.']"'.(!$dk->at_3[$slug] || !$dk->auto_titel[$slug]?' style="display:none;"':'').'>
                                            '.dk_zeichen($trans, $dk->atz_3[$slug]).'
                                        </select>
                                    </td>
                                </tr>
                            </table>
                        </div>';
                        
                        foreach($felder as $bid => $x)
                        {        
                            echo '
                            <div class="atr">
                                <div class="titel">
                                    <strong>'.$x['atr']['name'].'</strong>
                                    '.$base->getBlockByID($x['type'], 'de').'
                                </div>
                                <div class="opt">
                                    <input type="checkbox" id="show'.$slug.$bid.'" class="show" name="show['.$slug.']['.$bid.']" value="1"'.($dk->show[$slug][$bid]?' checked="checked"':'').' />
                                    <label for="show'.$slug.$bid.'">
                                        '.$trans->__('Dieses Attribut in Übersichten anzeigen').'
                                    </label>
                                    
                                    <table class="more'.($dk->show[$slug][$bid]?' rmore':'').'">
                                        <tr>
                                            <td class="a">
                                                '.$trans->__('Breite in Dokumentenübersicht:').'
                                            </td>
                                            <td class="b">
                                                <select name="breite_uebersicht['.$slug.']['.$bid.']">
                                                    <option value="50"'.($dk->breite_uebersicht[$slug][$bid] == 50?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('50')).'
                                                    </option>
                                                    <option value="0"'.($dk->breite_uebersicht[$slug][$bid] == 0?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('100')).'
                                                    </option>
                                                    <option value="150"'.($dk->breite_uebersicht[$slug][$bid] == 150?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('150')).'
                                                    </option>
                                                    <option value="200"'.($dk->breite_uebersicht[$slug][$bid] == 200?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('200')).'
                                                    </option>
                                                    <option value="250"'.($dk->breite_uebersicht[$slug][$bid] == 250?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('250')).'
                                                    </option>
                                                    <option value="300"'.($dk->breite_uebersicht[$slug][$bid] == 300?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('300')).'
                                                    </option>
                                                    <option value="350"'.($dk->breite_uebersicht[$slug][$bid] == 350?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('350')).'
                                                    </option>
                                                </select>
                                                <select name="laenge_uebersicht['.$slug.']['.$bid.']">
                                                    '.dk_zeichen($trans, $dk->laenge_uebersicht[$slug][$bid]).'
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="a">'.$trans->__('Breite im Releationselement selbst:').'</td>
                                            <td class="b">
                                                <select name="breite_verwaltung['.$slug.']['.$bid.']">
                                                    <option value="50"'.($dk->breite_verwaltung[$slug][$bid] == 50?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('50')).'
                                                    </option>
                                                    <option value="0"'.($dk->breite_verwaltung[$slug][$bid] == 0?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('100')).'
                                                    </option>
                                                    <option value="150"'.($dk->breite_verwaltung[$slug][$bid] == 150?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('150')).'
                                                    </option>
                                                    <option value="200"'.($dk->breite_verwaltung[$slug][$bid] == 200?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('200')).'
                                                    </option>
                                                    <option value="250"'.($dk->breite_verwaltung[$slug][$bid] == 250?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('250')).'
                                                    </option>
                                                    <option value="300"'.($dk->breite_verwaltung[$slug][$bid] == 300?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('300')).'
                                                    </option>
                                                    <option value="350"'.($dk->breite_verwaltung[$slug][$bid] == 350?' selected="selected"':'').'>
                                                        '.$trans->__('%1 Pixel', false, array('350')).'
                                                    </option>
                                                </select>
                                                <select name="laenge_verwaltung['.$slug.']['.$bid.']">
                                                    '.dk_zeichen($trans, $dk->laenge_verwaltung[$slug][$bid]).'
                                                </select>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="c" colspan="2">
                                                <input type="checkbox" id="show_relation'.$slug.$bid.'" name="show_relation['.$slug.']['.$bid.']" value="1"'.($dk->show_relation[$slug][$bid]?' checked="checked"':'').' />
                                                <label for="show_relation'.$slug.$bid.'">
                                                    '.$trans->__('Wird in Releations-Element-Vorschau angezeigt <span>(automatische Breite)</span>').'
                                                </label>
                                            </td>
                                        </tr>
                                    </table>
                                </div>
                            </div>';
                        }
                
                    echo '
                    </div>
                </div>';
            }
            
            echo '
        </form>';
    }
    elseif($a == 'backup' && $suite->rm(12)) 
    { 
        @ini_set('upload_max_filesize', '128M');
        @ini_set('memory_limit', '200M');
        @ini_set('max_execution_time', 1800);
        
        $upload_time_limit = (ini_get('max_execution_time') < ini_get('max_input_time')?ini_get('max_execution_time'):ini_get('max_input_time'));
        
        echo '
        <h2 class="calibri">'.$trans->__('Datenbank sichern.').'</h2>
        <p>
            '.$trans->__('Hier haben Sie die Möglichkeit, die komplette CMS fokus Datenbank zu sichern. Dazu tragen Sie als erstes die E-Mail-Adresse ein, an die der komplette fokus-Dump verschickt werden soll. Danach können Sie den Backup-Vorgang starten. Sie erhalten eine Benachrichtigung sobald der Sicherungs-Vorgang abgeschlossen ist.').'
        </p>
        <table>
        <tr>
            <td>
                <span>'.$trans->__('Email:').'</span>
                <input type="text" id="bemail" value="'.$e->email.'" />
            </td>
            <td class="b">
                <button>'.$trans->__('Backup starten').'</button>
            </td>
        </tr>
        </table>
        <div id="bstatus">
            <h2 class="calibri">'.$trans->__('Die Datenbank wird gesichert...').'</h2>
            <div id="progresssbar"></div>
            <div class="info">
                '.$trans->__('Gesicherte Tabellen:').' 
            </div>
        </div>
        
        <form id="db_import" method="post" enctype="multipart/form-data">
        <div class="ifehler"></div>
        <div class="iok"></div>
        <div>
            <h2 class="calibri">'.$trans->__('Datenbank wiederherstellen.').'</h2>
            <p>
                '.$trans->__('Hier haben Sie die Möglichkeit, einen gesicherten fokus-Dump komplett wiederherzustellen. Bitte beachten Sie, dass dieser Vorgang im Vorfeld die komplette Datenbank (also auch ihre personenbezogenen Login-Daten oder Rollenzuordnungen) entfernt. Diese Funktion sollte also nur im Notfall und mit großer Vorsicht ausgeführt werden.').'
            </p>
            <p class="file">
                <button name="dump_file">'.$trans->__('fokus-Dump-Datei hochladen').'</button>
                <em>
                    '.$trans->__('(max. Dateigröße: %1MB / max. Uploaddauer: %2 Minuten)', false, array(
                        $base->getUploadLimit(),
                        round($upload_time_limit / 60, 1)
                    )).'
                </em>
            </p>
            <p class="pw">
                <label for="best_pw">'.$trans->__('Passwort bestätigen:').'</label> 
                <input type="password" id="best_pw" name="best_pw" placeholder="'.$trans->__('Ihr Passwort').'" />
            </p>
            <p class="go">
                <input type="submit" name="go" value="'.$trans->__('Import starten').'" disabled />
            </p>
        </div>
        </form>';
    }
    elseif($a == 'fehler') 
    { 
        $ep = $base->db_to_array($base->getOpt('error_pages'));
        
        echo '
        <form id="error_pages">
            <div class="ebox">
                <strong class="ls">'.$trans->__('Darstellung:').'</strong>
                <div class="rs">
                    <table>
                        <tr>
                            <td>'.$trans->__('Template-Datei:').'</td>
                            <td>
                                <select name="template">
                                    <option value=""'.(!$ep['template']?' selected':'').'>'.$trans->__('Standard-Template').'</option>';
                                    foreach((array)$base->getActiveTemplateConfig('files') as $n => $u)
                                    {
                                        echo '<option value="'.$u.'"'.($ep['template'] == $u?' selected':'').'>'.$n.'</option>';
                                    }
                                echo '
                                </select>
                            </td>
                        </tr>';
                                                
                        if(count($base->getActiveTemplateConfig('mobile')))
                        {
                            echo '
                            <tr>
                                <td>'.$trans->__('Mobile Template-Datei:').'</td>
                                <td>
                                    <select name="template_mobile">';
                                        foreach($base->getActiveTemplateConfig('mobile') as $n => $u)
                                        {
                                            echo '<option value="'.$u.'"'.($ep['template_mobile'] == $u?' selected':'').'>'.$n.'</option>';
                                        }
                                    echo '
                                    </select>
                                </td>
                            </tr>';
                        }
                    echo '
                    </table>
                </div>
            </div>
            <div class="ebox">
                <strong class="ls">'.$trans->__('Inhalte:').'</strong>
                <div class="rs">
                    <div class="errorbox">
                        <h2 class="calibri">'.$trans->__('#404 - Nicht gefunden').'</h2>
                        <p class="info">
                            Die angeforderte Ressource wurde nicht gefunden. Dieser Statuscode kann ebenfalls verwendet werden, um eine Anfrage ohne näheren Grund abzuweisen. 
                        </p>
                        <a class="rbutton goaway" data-error="404">'.$trans->__('Fehlerseite verwalten').'</a>
                    </div>
                    
                    <div class="errorbox">
                        <h2 class="calibri">'.$trans->__('#403 - Verboten').'</h2>
                        <p class="info">
                            Die Anfrage wurde mangels Berechtigung des Clients nicht durchgeführt.
                        </p>
                        <a class="rbutton goaway" data-error="403">'.$trans->__('Fehlerseite verwalten').'</a>
                    </div>
                    
                    <div class="errorbox">
                        <h2 class="calibri">'.$trans->__('#500 - Unerwarteter Serverfehler').'</h2>
                        <p class="info">
                            Dies ist ein „Sammel-Statuscode“ für unerwartete Serverfehler.
                        </p>
                        <a class="rbutton goaway" data-error="500">'.$trans->__('Fehlerseite verwalten').'</a>
                    </div>
                    
                    <div class="errorbox">
                        <h2 class="calibri">'.$trans->__('#503 - Nicht verfügbar').'</h2>
                        <p class="info">
                            Der Server steht temporär nicht zur Verfügung, zum Beispiel wegen Überlastung oder Wartungsarbeiten.
                        </p>
                        <a class="rbutton goaway" data-error="503">'.$trans->__('Fehlerseite verwalten').'</a>
                    </div>
                </div>
            </div>
        </form>';
    }
}     
?>
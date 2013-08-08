<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if($index != 'n250' || !$user->isAdmin())
    exit($user->noRights());
        
if(!$user->r('dok'))
    exit($user->noRights());

if(!isset($rel)) $rel = 0;

$id = $fksdb->save($_GET['id'], 1);
if(!$id) $id = intval($rel);

$doc = $fksdb->fetch("SELECT * FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$doc)
    exit('<div class="ifehler">'.$trans->__('Dokument nicht gefunden.').'</div>');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())) {}
else exit($user->noRights());

if($doc->anfang || $doc->bis)
{
    $dscheck = $base->find_check_document_statusB($doc->id, $doc->anfang, $doc->bis, $doc->statusB);
    if($dscheck >= 0)
        $doc->statusB = $dscheck;
}

$user->lastUse('dokument', $doc->id);

$dv = $fksdb->query("SELECT id FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND aktiv = '1' LIMIT 1");
$dve = $fksdb->fetch("SELECT * FROM ".SQLPRE."document_versions WHERE dokument = '".$id."' AND id = '".$doc->dversion_edit."' LIMIT 1"); 

$nicht_zustaendig = false;
$dzsb = array();
if($doc->zsb && !$user->isSuperAdmin() && count($user->getAvaibleCompetence()))
{
    $dzsb = $base->fixedUnserialize($doc->zsb);
    if(count($dzsb))
    {
        $nicht_zustaendig = true;
        
        foreach($dzsb as $zid)
        {
            if($user->isCompetent($zid))
                $nicht_zustaendig = false;    
        }
    }
}

if(($doc->closed_to <= $base->getTime() || $doc->closed_by == $user->getID()) && (!$dve->edit || $dve->von == $user->getID()) && !$doc->papierkorb && !$nicht_zustaendig)
{
    // Dokument sperren
    $close = $fksdb->query("UPDATE ".SQLPRE."documents SET closed_by = '".$user->getID()."', closed_to = '".($base->getTime() + 15)."' WHERE id = '".$id."' LIMIT 1");
    
    if($doc->klasse)
    {
        if($user->getIndiv()->dokument_anlegen == 1)
            $user->getIndiv()->dokument_anlegen = 0;
        if($user->getIndiv()->dokument_oeffnen == 1)
            $user->getIndiv()->dokument_oeffnen = 2;
    }
        
    echo '
    <h1>
        <span>'.(!$doc->produkt?'Dokument':'Produkt').'</span>: '.Strings::cut($doc->titel, 35).'
        <a id="quick_preview" class="rbutton goaway" rel="'.$doc->id.'" title="'. $trans->__('Detaillierte Vorschau dieses Dokuments &ouml;ffnen') .'">'. $trans->__('Vorschau öffnen') .'</a>
        
        <input type="hidden" id="open_dok_id" value="'.$doc->id.'" />
        
        '.($dve->timestamp_edit || $doc->timestamp_freigegeben?'<input type="hidden" id="indiv_tab" value="'.($user->getIndiv()->dokument_oeffnen?$user->getIndiv()->dokument_oeffnen:0).'" />':'
        <input type="hidden" id="indiv_tab" value="'.($user->getIndiv()->dokument_anlegen?$user->getIndiv()->dokument_anlegen:0).'" />').'
    </h1>
    
    <div class="box" id="zurfreigabe">
        <img src="images/loading_white.gif" alt="'. $trans->__('Bitte warten.. Inhalt wird geladen..') .'" class="ladebalken" />
    </div>
    
    <div class="box" id="sprache_vorschau">
        <div id="quick_sprachen"></div>
    </div>
    
    <div class="box">
        <div id="doc">
            <ul id="docN">
                <li><a href="#doc1">'. $trans->__('Allgemein') .'</a></li>
                <li'.($doc->klasse || $doc->produkt?' style="display:none;"':'').'><a href="#doc2">'. $trans->__('Layout') .'</a></li>
                <li><a href="#doc3">'. $trans->__('Inhalt') .'</a></li>
                <li><a href="#doc4">'. $trans->__('Optimierung') .'</a></li>
                '.($suite->rm(8) && $fksdb->count($dv)?'<li class="machrechts"><a href="#doc5">'. $trans->__('Zeitsprung') .'</a></li>':'').'
            </ul>
            
            <div id="doc1" data-id="'.$doc->id.'" class="docC ui-tabs-hide"><img src="images/loading_white.gif" alt="'. $trans->__('Bitte warten.. Inhalt wird geladen..') .'" class="ladebalken" /></div>
            <div id="doc2" data-id="'.$doc->id.'" class="docC ui-tabs-hide"></div>
            <div id="doc3" data-id="'.$doc->id.'" class="docC ui-tabs-hide"></div>
            <div id="doc4" data-id="'.$doc->id.'" class="docC ui-tabs-hide"></div>
            '.($suite->rm(8) && $fksdb->count($dv)?'<div id="doc5" class="docC ui-tabs-hide"></div>':'').'
        </div>
        
        '.(!$doc->klasse && !$doc->produkt?'    
        <div id="mehrfachauswahl">
            <a class="mfa">'. $trans->__('Mehrfachauswahl & Optionen öffnen') .'</a>
            <a class="mfaS" rel="mfa_del">'. $trans->__('Markierte Elemente <strong>löschen</strong>') .'</a>
            <a class="mfaS" rel="mfa_copy">'. $trans->__('Markierte Elemente <strong>duplizieren</strong>') .'</a>
            '.($suite->rm(4)?'<a class="mfaS" rel="mfa_ablage">'. $trans->__('Markierte Elemente in die <strong>Zwischenablage</strong> kopieren</a>') .'':'').'
        </div>':'').'
    </div>
    
    <div class="box_save">
        <input type="button" value="'. $trans->__('verwerfen') .'" class="bs1" />
        <input type="button" value="'. $trans->__('speichern') .'" class="bs2" />
    </div>'; 
}
else
{
    ///////////////////////////////////////////////////
    require_once('../inc/classes.view/class.fks.php');
    $fks = new Page(array(
        'fksdb' => $fksdb,
        'base' => $base,
        'suite' => $suite,
        'trans' => $trans,
        'user' => $user,
        'api' => $api
    ), array(
        'title' => $doc->titel,
        'language' => $dve->language,
        'preview' => $doc->id,
        'dversion_preview' => $dve->id,
        'dclass' => ($doc->klasse?$doc->id:0)
    ));
    
    require_once('../inc/classes.view/class.content.php');
    $content = new Content(array(
        'fksdb' => $fksdb,
        'base' => $base,
        'suite' => $suite,
        'trans' => $trans,
        'user' => $user,
        'api' => $api,
        'fks' => $fks
    ));
    
    require_once('../inc/classes.blocks/_basic.php');
    
    $content->setGalerie(array('img_width' => 150, 'img_height' => 150));
    $content->setForm(array('view' => 'flat'));
    
    $p = array(
        'id' => 'vcontent',
        'document_class' => '',
        'document_width' => '650',
        'column_class' => 'vspalte',
        'column_padding' => array(8, 20, 8, 0),
        'column_padding_last' => array(8, 0, 8)
    );
    $inhalt = $content->get($p);
    
    $ben = $base->user($doc->closed_by, ' ', 'vorname', 'nachname');

    echo '
    <h1><span>'.(!$doc->produkt?'Dokument':'Produkt').'</span>: '.Strings::cut($doc->titel, 45).'</h1>';
    
    if($doc->papierkorb)
    {
        echo '
        <div class="box fehlerbox">
            <strong>'. $trans->__('Dieses Dokument befindet sich im Papierkorb') .'</strong>
            '. $trans->__('Sie haben dieses Dokument in den Papierkorb verschoben. <br />Um es bearbeiten zu können, müssen Sie es erst wiederherstellen.') .'
        </div>';
    }
    else
    {
        echo '<div class="box" id="dok_closed">';
        
        if($nicht_zustaendig)
        {
            $ztext = '';
            $zq = $fksdb->query("SELECT id, name FROM ".SQLPRE."responsibilities WHERE papierkorb = '0'");
            while($z = $fksdb->fetch($zq))
            {
                if(in_array($z->id, $dzsb))
                    $ztext .= ($ztext?', ':'').$z->name;
            }
            
            echo '
            <h5>'. $trans->__('Dieses %1 unterliegt einem Zuständigkeitsbereich.', false, array((!$doc->produkt?'Dokument':'Produkt'))) .'</h5>
            
            <table>
                <tr>
                    <td class="fi">Name des '.(!$doc->produkt?$trans->__('Dokuments'):$tarns->__('Produktes')).':</td>
                    <td>'.$doc->titel.'</td>
                </tr>
                <tr>
                    <td class="fi">ID des '.(!$doc->produkt?$trans->__('Dokuments'):$tarns->__('Produktes')).':</td>
                    <td>'.$doc->id.'</td>
                </tr>
                <tr>
                    <td class="fi">'. $trans->__('Zuständigkeitsbereiche:') .'</td>
                    <td>'.$ztext.'</td>
                </tr>
                <tr>
                    <td class="fi">'. $trans->__('Letzte &Auml;nderung am') .' '.(!$doc->produkt?$trans->__('Dokuments'):$tarns->__('Produktes')).':</td>
                    <td>'.date('d.m.Y', $doc->timestamp_edit).' ('.$base->doc_edit($doc->timestamp_edit).')</td>
                </tr>
            </table>';   
        }
        elseif($doc->closed_to > $base->getTime())
        {
            echo '
            <h5>'. $trans->__('Dieses %1 wird momentan bearbeitet von %2.', false, array((!$doc->produkt?'Dokument':'Produkt'), $ben)) .'</h5>
            
            <table>
                <tr>
                    <td class="fi">'. $trans->__('Name des %1:', false, array((!$doc->produkt?'Dokuments':'Produktes'))) .'</td>
                    <td>'.$doc->titel.'</td>
                </tr>
                <tr>
                    <td class="fi">'. $trans->__('ID des %1:', false, array((!$doc->produkt?'Dokuments':'Produktes'))) .'</td>
                    <td>'.$doc->id.'</td>
                </tr>
                <tr>
                    <td class="fi">'. $trans->__('Letzte Änderung am %1:', false, array((!$doc->produkt?'Dokument':'Produkt'))) .'</td>
                    <td>'.date('d.m.Y', $doc->timestamp_edit).' ('.$base->doc_edit($doc->timestamp_edit).')</td>
                </tr>
            </table>
            
            <p>
                <a id="n645" class="inc_communication" rel="'.$doc->closed_by.'">'. $trans->__('Nachricht an %1 schreiben', false, array($ben)) .'</a>
            </p>';
        }
        else
        {
            $ben2 = $base->user($dve->von, ' ', $trans->__('vorname'), $trans->__('nachname'));
            
            echo '
            <h5>'. $trans->__('Dieses %1 wurde bearbeitet%2 von %3.', false, array((!$doc->produkt?'Dokument':'Produkt'), ($dve->ende?' und zur Freigabe vorgelegt':''), $ben2)) .'</h5>
            
            <table>
                <tr>
                    <td class="fi">'. $trans->__('Name des %1:', false, array((!$doc->produkt?'Dokuments':'Produktes'))) .'</td>
                    <td>'.$doc->titel.'</td>
                </tr>
                <tr>
                    <td class="fi">'. $trans->__('ID des %1:', false, array((!$doc->produkt?'Dokuments':'Produktes'))) .'</td>
                    <td>'.$doc->id.'</td>
                </tr>
                <tr>
                    <td class="fi">'. $trans->__('Letzte Änderung am %1:', false, array((!$doc->produkt?'Dokument':'Produkt'))) .'</td>
                    <td>'.date('d.m.Y', $doc->timestamp_edit).' ('.$base->doc_edit($doc->timestamp_edit).')</td>
                </tr>
                <tr>
                    <td class="fi">'. $trans->__('Letzter Zugriff auf das %1:', false, array((!$doc->produkt?'Dokument':'Produkt'))) .'</td>
                    <td>'.$base->is_online($doc->closed_to, true).' '.$trans->__('von') .' '. $ben.'</td>
                </tr>
            </table>
            
            <p>
                '.($user->r('kom', 'pn')?'<a id="n645" class="inc_communication" rel="'.$dve->von.'">'. $trans->__('Nachricht an %1 schreiben</a>', false, array($ben2)):'').'
                '.($user->r('dok', 'acopy')?'<a class="get_dokument" rel="'.$doc->id.'">'. $trans->__('Arbeitskopie übernehmen') .'</a>':'').'
                '.($dve->ende && $user->r('dok', 'publ')?'<a class="dfreigabe" rel="'.$doc->id.'_'.$dve->id.'">'.(!$doc->produkt?$trans->__('Dokument'):$trans->__('Produkt')).' '. $trans->__('freigeben') .'</a>':'').'
            </p>';
        }
        
        echo '</div>';
    }
    echo '
    <div class="box" id="dok_preview">
        '.$inhalt.'
    </div>';
}
?>
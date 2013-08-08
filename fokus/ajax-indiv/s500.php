<?php
if($index != 's500' || !$user->r('fks', 'customize'))
    exit($user->noRights());
    
$i = $user->getIndiv();
        
if(!$i->saved)
{
    $i->bild_w = 100;
    $i->bild_h = 0;
    $i->bild_wt = 1;
    $i->bild_p = 3;
    
    $i->bildt_w = 100;
    $i->bildt_h = 0;
    $i->bildt_wt = 1;
    $i->bildt_p = 2;    
}
    
echo '
<h1>'.$trans->__('Workflow optimieren.').'</h1>

<div class="box">
    <form id="workflowo" method="post">
        <div class="greybox">
            <h2 class="calibri">'.$trans->__('Arbeiten mit Dokumenten.').'</h2>
            '.$trans->__('Optimieren Sie Ihren Arbeitsfluss beim Arbeiten mit Dokumenten.').'
            
            <input type="hidden" name="saved" value="true" />
            
            <table>
                <tr>
                    <td class="a">'.$trans->__('Größe und Position für Bild-Elemente:').'</td>
                    <td class="bild">
                        <input type="number" placeholder="'.$trans->__('Breite').'" name="bild_w" value="'.$i->bild_w.'" class="bild_w"'.($i->bild_wt == 2?' style="display:none;" disabled':'').' />
                        <input type="number" placeholder="'.$trans->__('Höhe').'" name="bild_h" value="'.$i->bild_h.'" class="bild_h"'.($i->bild_wt >= 1?' style="display:none;" disabled':'').' />
                        <span'.($i->bild_wt == 2?' style="display:none;"':'').'>/</span>
                        <select name="bild_wt" class="bild_wt">
                            <option value="0"'.($i->bild_wt == 0?' selected':'').'>'.$trans->__('Pixel').'</option>
                            <option value="1"'.($i->bild_wt == 1?' selected':'').'>'.$trans->__('Prozent').'</option>
                            <option value="2"'.($i->bild_wt == 2?' selected':'').'>'.$trans->__('Originalgröße').'</option>
                        </select>
                        <select name="bild_p" class="bild_p">
                            <option value="0"'.($i->bild_p == 0?' selected':'').'>'.$trans->__('Links').'</option>
                            <option value="1"'.($i->bild_p == 1?' selected':'').'>'.$trans->__('Rechts').'</option>
                            <option value="2"'.($i->bild_p == 2?' selected':'').'>'.$trans->__('Zentriert').'</option>
                            <option value="3"'.($i->bild_p == 3?' selected':'').'>'.$trans->__('Bündig').'</option>
                        </select>
                    </td>
                </tr>
                <tr class="aunten">
                    <td class="a">'.$trans->__('Größe und Position für Bilder in Text-Elementen:').'</td>
                    <td class="bild">
                        <input type="number" placeholder="'.$trans->__('Breite').'" name="bildt_w" value="'.$i->bildt_w.'" class="bild_w"'.($i->bildt_wt == 2?' style="display:none;" disabled':'').' />
                        <input type="number" placeholder="'.$trans->__('Höhe').'" name="bildt_h" value="'.$i->bildt_h.'" class="bild_h"'.($i->bildt_wt >= 1?' style="display:none;" disabled':'').' />
                        <span'.($i->bildt_wt == 2?' style="display:none;"':'').'>/</span>
                        <select name="bildt_wt" class="bild_wt">
                            <option value="0"'.($i->bildt_wt == 0?' selected':'').'>'.$trans->__('Pixel').'</option>
                            <option value="1"'.($i->bildt_wt == 1?' selected':'').'>'.$trans->__('Prozent').'</option>
                            <option value="2"'.($i->bildt_wt == 2?' selected':'').'>'.$trans->__('Originalgröße').'</option>
                        </select>
                        <select name="bildt_p" class="bild_p">
                            <option value="0"'.($i->bildt_p == 0?' selected':'').'>'.$trans->__('Links im Text').'</option>
                            <option value="1"'.($i->bildt_p == 1?' selected':'').'>'.$trans->__('Rechts im Text').'</option>
                            <option value="2"'.($i->bildt_p == 2?' selected':'').'>'.$trans->__('Über dem Text').'</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">'.$trans->__('Beim <strong>Öffnen</strong> eines Dokumentes folgenden Tab öffnen:').'</td>
                    <td>
                        <select name="dokument_oeffnen">
                            <option value="0"'.($i->dokument_oeffnen == 0?' selected':'').'>'.$trans->__('Allgemein').'</option>
                            <option value="1"'.($i->dokument_oeffnen == 1?' selected':'').'>'.$trans->__('Layout').'</option>
                            <option value="2"'.($i->dokument_oeffnen == 2?' selected':'').'>'.$trans->__('Inhalt').'</option>
                            <option value="3"'.($i->dokument_oeffnen == 3?' selected':'').'>'.$trans->__('Optimierung').'</option>
                            <option value="4"'.($i->dokument_oeffnen == 4?' selected':'').'>'.$trans->__('Zeitsprung').'</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">'.$trans->__('Beim <strong>Anlegen</strong> eines Dokumentes folgenden Tab öffnen:').'</td>
                    <td>
                        <select name="dokument_anlegen">
                            <option value="0"'.($i->dokument_anlegen == 0?' selected':'').'>'.$trans->__('Allgemein').'</option>
                            <option value="1"'.($i->dokument_anlegen == 1?' selected':'').'>'.$trans->__('Layout').'</option>
                            <option value="2"'.($i->dokument_anlegen == 2?' selected':'').'>'.$trans->__('Inhalt').'</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="greybox">
            <h2 class="calibri">'.$trans->__('Arbeiten mit Strukturelementen.').'</h2>
            '.$trans->__('Optimieren Sie Ihren Arbeitsfluss beim Arbeiten mit Strukturelementen.').'
            
            <table>
                <tr>
                    <td class="a">'.$trans->__('Status von neuen Strukturelementen nach dem Anlegen:').'</td>
                    <td>
                        <select name="struktur_status">
                            <option value="0"'.($i->struktur_status == 0?' selected':'').'>'.$trans->__('gesperrt').'</option>
                            <option value="1"'.($i->struktur_status == 1?' selected':'').'>'.$trans->__('freigegeben').'</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
        
        <div class="greybox">
            <h2 class="calibri">'.$trans->__('Allgemeine Optimierungen.').'</h2>
            '.$trans->__('Hier können Sie generelle Optimierungen durchführen.').'
            
            <table class="generell">
                <tr class="aunten">
                    <td class="a">'.$trans->__('Anzahl an Arbeitsbereichen:').'</td>
                    <td>
                        <input type="number" value="'.max(1, $i->workspaces).'" name="workspaces" />
                    </td>
                </tr>
                <tr class="aunten">
                    <td class="a">'.$trans->__('Beim Öffnen eines neuen Fensters:').'</td>
                    <td>
                        <select name="fenster_neu">
                            <option value="0"'.($i->fenster_neu == 0?' selected':'').'>'.$trans->__('geöffnete Fenster weiterhin anzeigen').'</option>
                            <option value="1"'.($i->fenster_neu == 1?' selected':'').'>'.$trans->__('geöffnete Fenster ablegen').'</option>
                            <option value="2"'.($i->fenster_neu == 2?' selected':'').'>'.$trans->__('geöffnete Fenster schließen').'</option>
                        </select>
                    </td>
                </tr>
                <tr class="aunten">
                    <td class="a">'.$trans->__('Öffnen von Kindelementen in der Hauptnavigation:').'</td>
                    <td>
                        <select name="subnavi">
                            <option value="0"'.($i->subnavi == 0?' selected':'').'>'.$trans->__('bei Mausberührung').'</option>
                            <option value="1"'.($i->subnavi == 1?' selected':'').'>'.$trans->__('bei Klick').'</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">'.$trans->__('Folgendes Fenster bei Klick auf <strong>Struktur</strong> öffnen:').'</td>
                    <td>
                        <select name="open_struktur">
                            <option value="n120"'.($i->open_struktur == 'n120'?' selected':'').(!$user->r('str', 'ele')?' disabled':'').'>
                                '.$trans->__('Aktuelle Struktur bearbeiten').'
                            </option>
                            <option value="n100"'.($i->open_struktur == 'n100'?' selected':'').(!$user->r('str', 'struk')?' disabled':'').'>
                                '.$trans->__('Strukturauswahl öffnen').'
                            </option>
                            <option value="n170"'.($i->open_struktur == 'n170'?' selected':'').(!$user->r('str', 'menue') || !count($base->getActiveTemplateConfig('menus'))?' disabled':'').'>
                                '.$trans->__('Menüs bearbeiten').'
                            </option>
                            <option value="n180"'.($i->open_struktur == 'n180'?' selected':'').(!$user->r('str', 'slots') || !count($base->getActiveTemplateConfig('slots'))?' disabled':'').'>
                                '.$trans->__('Slots bearbeiten').'
                            </option>
                            <option value="n190"'.($i->open_struktur == 'n190'?' selected':'').(!$user->r('str', 'kat')?' disabled':'').'>
                                '.$trans->__('Kategorien verwalten').'
                            </option>
                        </select>
                    </td>
                </tr> 
                <tr>
                    <td class="a">'.$trans->__('Folgendes Fenster bei Klick auf <strong>Dokumente</strong> öffnen:').'</td>
                    <td>
                        <select name="open_dokumente">
                            <option value="n200"'.($i->open_dokumente == 'n200'?' selected':'').(!$user->r('dok')?' disabled':'').'>
                                '.$trans->__('Dokumentenverwaltung öffnen').'
                            </option>
                            <option value="n210"'.($i->open_dokumente == 'n210'?' selected':'').(!$user->r('dok', 'new')?' disabled':'').'>
                                '.$trans->__('Neues Dokument anlegen').'
                            </option>
                            <option value="n220"'.($i->open_dokumente == 'n220'?' selected':'').(!$user->r('dok', 'publ')?' disabled':'').'>
                                '.$trans->__('Dokumentenfreigabe').'
                            </option>
                            <option value="n280"'.($i->open_dokumente == 'n280'?' selected':'').(!$user->r('dok', 'ezsb')?' disabled':'').'>
                                '.$trans->__('Zuständigkeiten verwalten').'
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">'.$trans->__('Folgendes Fenster bei Klick auf <strong>Dateien</strong> öffnen:').'</td>
                    <td>
                        <select name="open_dateien">
                            <option value="n400"'.($i->open_dateien == 'n400'?' selected':'').(!$user->r('dat', 'bilder')?' disabled':'').'>
                                '.$trans->__('Bilder verwalten').'
                            </option>
                            <option value="n402"'.($i->open_dateien == 'n402'?' selected':'').(!$user->r('dat', 'dateien')?' disabled':'').'>
                                '.$trans->__('Dateien verwalten').'
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">'.$trans->__('Folgendes Fenster bei Klick auf <strong>Personen</strong> öffnen:').'</td>
                    <td>
                        <select name="open_personen">
                            <option value="n510"'.($i->open_personen == 'n510'?' selected':'').((($user->r('per', 'edit') || $user->r('per', 'new')) && (!$user->r('per', 'type') || $user->r('per', 'kunden')))?'':' disabled').'>
                                '.$trans->__('Kunden verwalten').'
                            </option>
                            <option value="n515"'.($i->open_personen == 'n515'?' selected':'').((($user->r('per', 'edit') || $user->r('per', 'new')) && (!$user->r('per', 'type') || $user->r('per', 'mitarbeiter')))?'':' disabled').'>
                                '.$trans->__('Mitarbeiter verwalten').'
                            </option>
                            <option value="n520"'.($i->open_personen == 'n520'?' selected':'').(!$user->r('per', 'firma')?' disabled':'').'>
                                '.$trans->__('Firmen verwalten').'
                            </option>
                            <option value="n540"'.($i->open_personen == 'n540'?' selected':'').(!$user->r('per', 'rollen')?' disabled':'').'>
                                '.$trans->__('Rollen &amp; Rechte verwalten').'
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">'.$trans->__('Folgendes Fenster bei Klick auf <strong>Kommunikation</strong> öffnen:').'</td>
                    <td>
                        <select name="open_kommunikation">
                            <option value="n610"'.($i->open_kommunikation == 'n610'?' selected':'').((!$user->r('kom', 'nledit') && !$user->r('kom', 'nlsend')) || !$suite->rm(5)?' disabled':'').'>
                                '.$trans->__('Newsletter verwalten').'
                            </option>
                            <option value="n630"'.($i->open_kommunikation == 'n630'?' selected':'').(!$user->r('kom', 'kkanal')?' disabled':'').'>
                                '.$trans->__('Kommunikationkanäle auswerten').'
                            </option>
                            <option value="n640"'.($i->open_kommunikation == 'n640'?' selected':'').(!$user->r('kom', 'pn') || !$suite->rm(10)?' disabled':'').'>
                                '.$trans->__('Nachrichten verwalten').'
                            </option>
                            <option value="n650"'.($i->open_kommunikation == 'n650'?' selected':'').(!$user->r('kom', 'livetalk') || !$suite->rm(10)?' disabled':'').'>
                                '.$trans->__('Livetalk').'
                            </option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">'.$trans->__('Folgendes Fenster bei Klick auf <strong>Suchen</strong> öffnen:').'</td>
                    <td>
                        <select name="open_suche">
                            <option value="s110"'.($i->open_suche == 's110'?' selected':'').(!$user->r('suc', 'suche')?' disabled':'').'>
                                '.$trans->__('fokus durchsuchen').'
                            </option>
                            <option value="s115"'.($i->open_suche == 's115'?' selected':'').(!$user->r('suc', 'papierkorb')?' disabled':'').'>
                                '.$trans->__('Papierkorb öffnen').'
                            </option>
                            <option value="s120"'.($i->open_suche == 's120'?' selected':'').(!$user->r('suc', 'zuve')?' disabled':'').'>
                                '.$trans->__('zuletzt verwendet').'
                            </option>
                        </select>
                    </td>
                </tr>';
                
                /*
                <tr>
                    <td class="a">'.$trans->__('Folgendes Fenster bei Klick auf <strong>fokus</strong> öffnen:').'</td>
                    <td>
                        <select name="open_fokus">
                            <option value="web"'.($i->open_fokus == 'web'?' selected':'').'>
                                '.$trans->__('Website öffnen').'
                            </option>
                            <option value="ghost"'.($i->open_fokus == 'ghost'?' selected':'').(!$suite->rm(2) || !$user->r('fks', 'ghost')?' disabled':'').'>
                                '.$trans->__('Website-Direktbearbeitung').'
                            </option>
                            <option value="s450"'.($i->open_fokus == 's450'?' selected':'').(!$suite->rm(4) || !$user->r('fks', 'foresight')?' disabled':'').'>
                                '.$trans->__('Website-Vorschau').'
                            </option>
                            <option value="s500"'.($i->open_fokus == 's500'?' selected':'').(!$user->r('fks', 'indiv')?' disabled':'').'>
                                '.$trans->__('fokus individualisieren').'
                            </option>
                            <option value="s490"'.($i->open_fokus == 's490'?' selected':'').(!$user->r('fks', 'pure')?' disabled':'').'>
                                '.$trans->__('fokus aufräumen').'
                            </option>
                            <option value="s420"'.($i->open_fokus == 's420'?' selected':'').(!$user->r('fks', 'opt')?' disabled':'').'>
                                '.$trans->__('Systemeinstellungen').'
                            </option>
                            <option value="s440"'.($i->open_fokus == 's440'?' selected':'').(!$user->r('fks', 'sitzung')?' disabled':'').'>
                                '.$trans->__('Sitzungs-Information').'
                            </option>
                            <option value="s480"'.($i->open_fokus == 's480'?' selected':'').(!$user->r('fks', 'notiz')?' disabled':'').'>
                                '.$trans->__('Persönliche Notizen').'
                            </option>
                            <option value="logout"'.($i->open_fokus == 'logout'?' selected':'').'>
                                '.$trans->__('Abmelden').'
                            </option>
                        </select>
                    </td>
                </tr>
                */
                
                echo '
            </table>
        </div>
        
        <div class="greybox">
            <h2 class="calibri">'.$trans->__('Kommunikation.').'</h2>
            '.$trans->__('Optimieren Sie ihre projektbezogene Kommunikation.').'
            
            '.(!$user->data('email')?
                '<div class="ifehler">
                    '.$trans->__('Einige Optionen sind deaktiviert, da Sie in ihrem Benutzerprofil keine Emailadresse hinterlegt haben').'
                </div>'
                :
                ''
            ).'
            
            <table class="generell">
                <tr>
                    <td class="a">'.$trans->__('Bei PN via E-Mail benachrichtigen:').'</td>
                    <td>
                        <select name="no_email_pn"'.(!$user->data('email')?' disabled':'').'>
                            <option value="0"'.($i->no_email_pn == 0?' selected':'').'>'.$trans->__('E-Mail Benachrichtigung aktiv').'</option>
                            <option value="1"'.($i->no_email_pn == 1?' selected':'').'>'.$trans->__('E-Mail Benachrichtigung nicht aktiv').'</option>
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">'.$trans->__('Bei Dokumenten-Zuweisung via E-Mail benachrichtigen:').'</td>
                    <td>
                        <select name="no_email_get_document"'.(!$user->data('email')?' disabled':'').'>
                            <option value="0"'.($i->no_email_get_document == 0?' selected':'').'>'.$trans->__('E-Mail Benachrichtigung aktiv').'</option>
                            <option value="1"'.($i->no_email_get_document == 1?' selected':'').'>'.$trans->__('E-Mail Benachrichtigung nicht aktiv').'</option>
                        </select>
                    </td>
                </tr>
            </table>
        </div>
    </form>
</div>

<div class="box_save">
    <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
    <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
</div>';
?>
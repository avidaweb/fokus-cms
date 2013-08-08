<?php
if($index == 'n120')
{
    if(!$user->r('str', 'ele'))
        exit($user->noRights());
    
    $struk = $fksdb->fetch("SELECT * FROM ".SQLPRE."structures WHERE id = '".$base->getStructureID()."' LIMIT 1");
    if(!$struk)
        exit('<div class="ifehler">'.$trans->__('Momentan ist keine Struktur zur Bearbeitung gewählt').'</div>');
    
    echo '
    <h1>'.(!$v->just_select?$trans->__('Struktur bearbeiten:').' '.$struk->titel:$trans->__('Strukturelement auswählen')).'.</h1>
    
    <div class="movebox" id="strukturelemente">
        <canvas width="600" height="1"></canvas>
    
        <div class="loadme">
            <img src="images/loading_grey.gif" alt="Bitte warten.. Inhalt wird geladen.." class="ladebalken" />
        </div>
        
        <img src="images/moveboxH.png" alt="" class="schatten" />
        <div class="moved baum" data-kat="0"></div>
        <img src="images/moveboxB.png" alt="" class="schatten" />
    </div>';
    
	$ha = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."elements WHERE struktur = '".$base->getStructureID()."' AND papierkorb = '0' AND is_hidden = '1'"));
    
    echo '
    <div class="box" id="weitere_se_optionen">';
		if(!$v->just_select)
		{
			echo '
			<div class="mfa">
				<a class="rbutton rollout">'.$trans->__('Mehrfachauswahl <span>öffnen</span>').'</a>
				<p>
					<a class="direct disabled" data-task="del">'.$trans->__('Markierte Elemente <strong>löschen</strong>').'</a>
					<a class="direct disabled" data-task="close">'.$trans->__('Markierte Elemente <strong>sperren</strong>').'</a>
					<a class="direct disabled" data-task="free">'.$trans->__('Markierte Elemente <strong>freigeben</strong>').'</a>
					<a class="direct disabled" data-task="clone">'.$trans->__('Markierte Elemente <strong>duplizieren</strong>').'</a>
					<a class="edit disabled">'.$trans->__('Markierte Elemente <strong>bearbeiten</strong>').'</a>
				</p>
				<input type="hidden" name="is_mfa" value="" />
			</div>';
		}
        
        echo '
        <div class="extend">
            <a class="open_all">'.$trans->__('Komplette Struktur ausklappen').'</a> | 
            <a class="close_all">'.$trans->__('Komplette Struktur einklappen').'</a>
        </div>';
        
		if($ha)
		{
			echo '
            <div class="hidden_elements">
    			<input type="checkbox" name="show_all" id="hidden_elementsshow_all" value="1" />
                <label for="hidden_elementsshow_all">
                    '.($ha == 1?
                    $trans->__('1 verstecktes Strukturelement <span>anzeigen</span>')
                    :
                    $trans->__('%1 versteckte Strukturelemente <span>anzeigen</span>', false, array($ha))
                    ).'
                </label>
            </div>';
		}
	echo '
    </div>';
}
?>
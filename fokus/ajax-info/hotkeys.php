<?php
if($index != 'hotkeys')
    exit();
    
echo '
<h1>'.$trans->__('Hotkeys.').'</h1>

<div class="box">
	<table id="sessioninfo">
		<tr>
			<td colspan="2"><strong>'.$trans->__('Folgende Tastaturkürzel können im fokus Backend verwendet werden:').'</strong></td>
		</tr>
		<tr>
			<td>ESC</td>
			<td class="right">'.$trans->__('Das aktuell geöffnete Fenster schließen.').'</td>
		</tr>
		<tr>
			<td>TAB</td>
			<td class="right">'.$trans->__('Durch die geöffneten Fenster navigieren.').'</td>
		</tr>
		<tr>
			<td>STRG + S</td>
			<td class="right">'.$trans->__('Das aktuell geöffnete Fenster abspeichern.').'</td>
		</tr>
		<tr>
			<td>STRG + F</td>
			<td class="right">'.$trans->__('Die Suche öffnen.').'</td>
		</tr>
		<tr>
			<td>STRG + X</td>
			<td class="right">'.$trans->__('Die Fehler-Konsole öffnen.').'</td>
		</tr>
		<tr>
			<td>STRG + H</td>
			<td class="right">'.$trans->__('Die Hotkey-Übersicht öffnen.').'</td>
		</tr>
    </table>
</div>';
?>
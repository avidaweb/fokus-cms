<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->isAdmin())
    exit();

$c = $base->fixedUnserialize($row->html);
if(!is_array($c)) $c = array();
$c = (object)$c;

$ordner = '../content/dklassen/';
$fk = $base->open_dklasse($ordner.$dkdatei.'.php');

if(!$fk['name'])
    exit('<div class="fehlerbox">Dokumentenklasse wurde nicht gefunden.</div>');
if(!is_array($fk['relation'][$realname]))
    exit('<div class="fehlerbox">'. $trans->__('Dieses Relationselement wurde in der Dokumentenklasse <em>%1</em> nicht korrekt definiert.', false, array($fk['name'])) .'</div>');
    
$related = $fk['relation'][$realname];
$rklasse = str_replace('.php', '', $related['dclass']);
$rinhalt = $related['content'];
$rlimit = intval($related['limit']);

$rk = $fk;
$fk = $base->open_dklasse($ordner.$rklasse.'.php');

if(!$fk['name'])
    exit('<div class="fehlerbox">'. $trans->__('Referenzierte Dokumentenklasse <em>%1</em> wurde nicht gefunden.', false, array($rklasse)) .'</div>');

echo '
<form id="relationsform">
    <input type="hidden" name="owndk" value="'.$dkdatei.'" />
    <input type="hidden" name="related" value="'.$rklasse.'" />
    <input type="hidden" name="sort" value="'.$c->sort.'" />
    <input type="hidden" name="limit" value="'.$rlimit.'" />

    <div class="movebox">
        <img src="images/moveboxH.png" alt="" class="schatten" />
        <div class="moved baum" data-kat="0">
        
        </div>
        <img src="images/moveboxB.png" alt="" class="schatten" />
    </div>      
</form>';

if($rlimit)
{
    echo '
    <div class="box s12">
        <em>
            '.($rlimit == 1?
                $trans->__('Es kann maximal 1 Dokument ausgewählt werden.')
                :
                $trans->__('Es können maximal %1 Dokumente ausgewählt werden.', false, array($rlimit))
            ).'
        </em>
    </div>';    
}
?>
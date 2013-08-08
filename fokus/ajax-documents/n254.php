<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('dok') || $index != 'n254')
    exit($user->noRights());

$offen = $fksdb->save($_REQUEST['offen']);
$klasse = $fksdb->save($_REQUEST['klasse']);

echo '
<div id="docPop">

    <div id="docP1" class="docPopC">
        <a'.($offen == 'docP1'?' style="font-weight: bold;"':'').'>Text-Elemente</a>
        <div class="blockz"'.($offen == 'docP1'?' style="display: block;"':'').'>';

            $fcount = 1;
            for($x = 10; $x<30; $x++)
            {
                if(!$base->getBlockByID($x, 'de') || ($klasse && !$base->getBlockByID($x, 'dclass')))
                    continue;

                echo '
                <div id="b_'.$x.'" class="doc_inhalt_popup block" title="'.$trans->__('Tastaturkürzel F%1', false, array($fcount)).'">
                    <span class="a">'.$base->getBlockByID($x, 'de').'</span>
                    <span class="b"></span>
                </div>';

                $fcount ++;
            }
        echo '
        </div>
    </div>

    <div id="docP2" class="docPopC">
        <a'.($offen == 'docP2'?' style="font-weight: bold;"':'').'>'. $trans->__('Medien-Elemente') .'</a>
        <div class="blockz"'.($offen == 'docP2'?' style="display: block;"':'').'>';
            for($x = 30; $x<50; $x++)
            {
                if(!$base->getBlockByID($x, 'de') || ($klasse && !$base->getBlockByID($x, 'dclass')))
                        continue;

                echo '
                <div id="b_'.$x.'" class="doc_inhalt_popup block">
                    <span class="a">'.$base->getBlockByID($x, 'de').'</span>
                    <span class="b"></span>
                </div>';
            }
        echo '
        </div>
    </div>

    <div id="docP3" class="docPopC">
        <a'.($offen == 'docP3'?' style="font-weight: bold;"':'').'>'. $trans->__('Dynamik-Elemente') .'</a>
        <div class="blockz"'.($offen == 'docP3'?' style="display: block;"':'').'>';
            for($x = 50; $x<100; $x++)
            {
                if(!$base->getBlockByID($x, 'de') || ($klasse && !$base->getBlockByID($x, 'dclass')))
                        continue;

                echo '
                <div id="b_'.$x.'" class="doc_inhalt_popup block">
                    <span class="a">'.$base->getBlockByID($x, 'de').'</span>
                    <span class="b"></span>
                </div>';
            }
        echo '
        </div>
    </div>';

    $extb = $api->getBlocks();
    if(count($extb))
    {
        echo '
        <div id="docP5" class="docPopC">
            <a'.($offen == 'docP5'?' style="font-weight: bold;"':'').'>'. $trans->__('Erweiterungen') .'</a>
            <div class="blockz"'.($offen == 'docP5'?' style="display: block;"':'').'>';
                foreach($extb as $uid => $bar)
                {
                    $nname = ($bar->getShort()?$bar->getShort():$bar->getName());
                    if(!$nname || !$bar->getName() || !$uid)
                        continue;

                    echo '<div id="b_100" data-extension="'.$uid.'" class="doc_inhalt_popup block" title="'.$bar->getName().'"><span class="a">'.$nname.'</span><span class="b"></span></div>';
                }
            echo '
            </div>
        </div>';
    }

    if($suite->rm(4))
    {
        $zaQ = $fksdb->query("SELECT * FROM ".SQLPRE."clipboard WHERE benutzer = '".$user->getID()."' AND type = 'inhaltselement' ORDER BY timestamp DESC");
        if($fksdb->count($zaQ))
        {
            echo '
            <div id="docP4" class="docPopC last">
                <a'.($offen == 'docP4'?' style="font-weight: bold;"':'').'>'. $trans->__('Zwischenablage') .'</a>
                <div class="blockz"'.($offen == 'docP4'?' style="display: block;"':'').'>';
                    while($za = $fksdb->fetch($zaQ))
                    {
                        $block = $fksdb->fetch("SELECT type, html FROM ".SQLPRE."blocks WHERE id = '".$za->aid."' AND dokument = '".$za->aid2."' LIMIT 1");
                        $b = Strings::cut(htmlspecialchars(rawurldecode(strip_tags(htmlspecialchars_decode(Strings::cleanString($block->html))))), 80);
                        $x = $block->type;

                        if(!$block)
                            continue;

                        if(!$base->getBlockByID($x, 'de') || ($klasse && !$base->getBlockByID($x, 'dclass')))
                            continue;

                        echo '<div id="b_'.$x.'_'.$za->aid.'" class="doc_inhalt_popup block" title="'.$b.'"><span class="a">'.$base->getBlockByID($x, 'de').'</span><span class="b"></span></div>';
                    }
                echo '
                </div>
            </div>';
        }
    }
echo '
</div>';
?>
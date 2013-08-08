<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'nlsend') || !$suite->rm(5) || $index != 'n621')
    exit($user->noRights());

function send_newsletter($recipent, $sender, $subject, $message, $cc = "", $bcc = "")
{
    $header  = "MIME-Version: 1.0\r\n";
    $header .= "Content-type: text/html; charset=utf-8\r\n";

    $header .= "From: ".$sender."\r\n";
    $header .= "Reply-To: ".$sender."\r\n";
    if($cc)
        $header .= "Cc: ".$cc."\r\n";
    if($bcc)
        $header .= "Bcc: ".$bcc."\r\n";
    $header .= "X-Mailer: PHP ". phpversion();

    if(!$recipent || !$message)
        return false;

    mail($recipent, $subject, $message, $header);
    return true;
}

parse_str($_POST['f'], $f);
$c = (object)$f;

$k = $fksdb->fetch("SELECT * FROM ".SQLPRE."newsletters WHERE id = '".$c->kid."' LIMIT 1");
if(!$k) exit($trans->__('Dieser Newsletter existiert nicht mehr'));

if(!$k->template)
    $k->template = 'index.php';

////////////// Inhalts buffern //////////////////////
ob_start();

require_once('../inc/classes.view/class.fks.php');
$fks = new Page(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans,
    'user' => $user,
    'api' => $api
), array(
    'id' => $id,
    'title' => $c->betreff,
    'language' => $trans->getInputLanguage(),
    'preview' => true,
    'dversion_preview' => false
));

/// NAVIGATION
require_once('../inc/classes.view/class.navigation.php');
$navigation = new Navigation();

/// CONTENT
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

require_once($api->getTemplateConfig());

$lf = $fks->getTemplateLanguageFile();
if($lf)
    require_once('../'.$lf);

require_once($api->getTemplateDir('/', false).$k->template);

$nachricht = ob_get_contents();
ob_end_clean();
//////////////////////////////////////////////////////////

$count = $fksdb->save($_POST['count']);
$step = 2;

if(!$c->absender)
    $c->absender = $base->getOpt('email');

if($_POST['test'] == 'test')
{
    send_newsletter($c->testmail, $c->absender, $c->betreff, $nachricht);
    echo 'ende';
    die();
}

$empf = array();
$empfT = explode(',', $c->newsletter_empf);
foreach($empfT as $e)
{
    $e = trim($e);
    if($base->is_valid_email($e))
        $empf[] = $e;
}

if($c->cc && $c->cc_email)
{
    if($c->cc_type == 1 && $count == 0)
    {
        send_newsletter($c->cc_email, $c->absender, $c->betreff, $nachricht);
    }
    elseif($c->cc_type == 2)
    {
        $cc = $c->cc_email;
    }
    elseif($c->cc_type == 3)
    {
        $bcc = $c->cc_email;
    }
}

if($count < count($empf))
{
    for($x = 0; $x < $step; $x++)
    {
        $akt = $count + $x;
        $mailto = $empf[$akt];

        if($mailto)
        {
            send_newsletter($mailto, $c->absender, $c->betreff, $nachricht, $cc, $bcc);
        }
    }

    $nc = ($count + $step);
    echo ($nc > count($empf)?count($empf):$nc);
}
else
{
    $upd = $fksdb->query("UPDATE ".SQLPRE."newsletters SET send = '".($k->send + 1)."', last_send = '".$base->getTime()."' WHERE id = '".$k->id."' LIMIT 1");
    echo 'ende';
}
?>
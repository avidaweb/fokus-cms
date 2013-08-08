<?php 
define('IS_INDEX', true, true);
require('inc/header.php');

$api->executeHook('index', $classes);

require_once('inc/classes.view/class.fks.php');
$fks = new Page(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans,
    'user' => $user,
    'api' => $api
), array(
    'id' => $fksdb->save($_GET['id'], 1),
    'title' => $fksdb->save($_GET['titel']),
    'language' => $fksdb->save($_GET['lan']),
    'paging' => $fksdb->save($_GET['seite'], 1),
    'preview' => $fksdb->save($_GET['vorschau']),
    'dclass' => $fksdb->save($_GET['dk']),
    'templatefile' => $fksdb->save($_GET['td']),
    'area' => $fksdb->save($_GET['bereich']),
    'search' => $fksdb->save($_GET['fks_q']),
    'error' => $fksdb->save($_GET['error']),
    'cp' => $fksdb->save($_GET['cp']),
    'cp_vars' => $fksdb->save($_GET['cp_vars'])
));
$api->setStatic('fks', $fks);

require_once('inc/classes.view/class.navigation.php');
$navigation = new Navigation(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans,
    'user' => $user,
    'api' => $api,
    'fks' => $fks
));
$api->setStatic('navigation', $navigation);

require_once('inc/classes.view/class.content.php');
$content = new Content(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans,
    'user' => $user,
    'api' => $api,
    'fks' => $fks
));
$api->setStatic('content', $content);

require_once('inc/classes.blocks/_basic.php');

// fire fks hook
$classes = array_merge($classes, array(
    'fks' => $fks,
    'navigation' => $navigation,
    'content' => $content
));
$api->executeHook('fks', $classes);

require_once($fks->getTemplate());

$api->executeHook('fks_close', $classes);

$fksdb->close();
unset($fksdb, $base, $suite, $api, $trans, $user, $fks, $navigation, $content);

exit();
?>
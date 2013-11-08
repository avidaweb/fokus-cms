<?php 
$root = '';
if(defined('IS_BACKEND') || defined('IS_SITEMAP') || defined('IS_FEED') || defined('IS_AJAX') || defined('IS_STATIC') || defined('IS_INSTALLATION') || defined('IS_CSS'))
    $root = '../';
if(defined('IS_BACKEND_DEEP'))
    $root = '../../';
if(defined('IS_FORESIGHT') || defined('IS_GHOST'))
    $root = '../../../';


if(file_exists($root.'fokus-config.php'))
    include($root.'fokus-config.php');


define('ROOT', $root, true);
define('DOMAIN', $domain, true);
define('HOME_DIR', ROOT, true);
define('BACKEND_DIR', ROOT.'fokus/', true);
define('TEMPLATES_DIR', ROOT.'content/templates/', true);
define('EXTENSIONS_DIR', ROOT.'content/extensions/', true);
define('DEPENDENCE', true, true);
define('FKS_VERSION', 2013.30, true);


if(!defined('IS_INSTALLATION'))
{
    if((!file_exists(ROOT.'fokus-config.php') || INSTALLED != 'true'))
    {
        header('Location: '.ROOT.'installation/');
        exit();
    }
    if(is_dir(ROOT.'installation'))
    {
        header('Location: '.ROOT.'installation/delete.php');
        exit();
    }
}


require(ROOT.'inc/classes.database/database-select.php');

require(ROOT.'inc/classes.other/class.strings.php'); 

require(ROOT.'inc/classes.core/class.suites.php'); 
$suite = new Suite($fksdb); 

require(ROOT.'inc/classes.core/class.translation.php');
$trans = new Translation(array(
    'fokus_language' => $fokus_language,
    'input_language' => $input_language,
    'standard_language' => $standard_language
));

require(ROOT.'inc/classes.core/class.base.php');
$base = new Base(array(
    'fksdb' => $fksdb,
    'trans' => $trans
));

require(ROOT.'inc/classes.core/class.user.php');
$user = new User(array(
    'cookie_alias' => $cookies,
    'salts' => $salts,
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'trans' => $trans
)); 


// load block class
require(ROOT.'inc/classes.other/class.block.php');

// load api class
require(ROOT.'inc/classes.core/class.api.php');
$api = new API(array(
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'user' => $user,
    'trans' => $trans
));  

// classes array
$classes = array(
    'api' => $api,
    'fksdb' => $fksdb,
    'base' => $base,
    'suite' => $suite,
    'user' => $user,
    'trans' => $trans
);


// load extensions
$api->getExtensions(true, true, true);
$api->executeHook('extensions_loaded', $classes);


// load template init.php
$template_init = TEMPLATE_DIR.'init.php'; 
if(file_exists($template_init)) 
{ 
    ob_start(); 
    include($template_init); 
    ob_end_clean();
}
$api->executeHook('template_init_loaded', $classes); 
?>
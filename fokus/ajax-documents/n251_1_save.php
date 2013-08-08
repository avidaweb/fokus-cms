<?php
if(!defined('DEPENDENCE'))
    exit('is dependent');
    
if(!$user->r('dok') || $index != 'n251_1_save')
    exit($user->noRights());
     
$id = $fksdb->save($_POST['id'], 1);
$task = $fksdb->save($_POST['task']);
parse_str($_POST['f'], $f);

$doc = $fksdb->fetch("SELECT * FROM ".SQLPRE."documents WHERE id = '".$id."' LIMIT 1");
if(!$doc)
    exit('<div class="ifehler">'.$trans->__('Dokument nicht gefunden.').'</div>');

if($user->r('dok', 'edit') || ($user->r('dok', 'new') && $doc->von == $user->getID())) {}
else exit($user->noRights());

if($task == 'format')
{
    $css_classes = '';
    foreach((array)$f['classes'] as $s)
        $css_classes .= ' '.$fksdb->save($s).' ';
    $css_classes = ' '.trim(Strings::removeDoubleSpace($css_classes)).' ';
    
    $fksdb->update("documents", array(
        "css_klasse" => $css_classes
    ), array(
        "id" => $doc->id
    ), 1);
}
elseif($task == 'categories')
{
    if(!$user->r('dok', 'cats'))
        exit($user->noRights());
        
    $cats_a = array();
    foreach((array)$f['kat'] as $s)
        $cats_a[] = $fksdb->save($s);
    
    $fksdb->update("documents", array(
        "kats" => serialize($cats_a),
    ), array(
        "id" => $doc->id
    ), 1);
}
elseif($task == 'custom_fields')
{
    $cfa = $base->fixedUnserialize($doc->cf);
    foreach((array)$f['cf_used'] as $cf_key)
    {
        $cf_val = $f['cf'][$cf_key];
        $cfa[$fksdb->save($cf_key)] = $fksdb->save($cf_val);
    }
    
    $fksdb->update("documents", array(
        "cf" => serialize($cfa)
    ), array(
        "id" => $doc->id
    ), 1);
}
elseif($task == 'options')
{
    $start = explode('.', $fksdb->save($f['anfang'])); 
    $end = explode('.', $fksdb->save($f['bis']));
    $start_h = $fksdb->save($f['anfangA']);
    $start_m = $fksdb->save($f['anfangB']);
    $end_h = $fksdb->save($f['bisA']);
    $end_m = $fksdb->save($f['bisB']);
    
    $date = explode('.', $fksdb->save($f['datum']));  

    $start_timestamp = 0;
    $end_timestamp = 0;
    $date_timestamp = 0;
    if($f['anfangC']) $start_timestamp = mktime($start_h, $start_m, 0, $start[1], $start[0], $start[2]);
    if($f['bisC']) $end_timestamp = mktime($end_h, $end_m, 0, $end[1], $end[0], $end[2]);
    if($f['datum']) $date_timestamp = mktime(0, 0, 0, $date[1], $date[0], $date[2]);
    
    $start_timestamp = ($start_timestamp > 100000?$start_timestamp:0);
    $end_timestamp = ($end_timestamp > 100000?$end_timestamp:0);
    
    $no_search = $fksdb->save($f['no_search'], 1);
    $author = $fksdb->save($f['author'], 1);
    
    $zsb_temp = array();
    foreach((array)$f['zsb'] as $s)
        $zsb_temp[] = $fksdb->save($s);
    $zsbS = serialize($zsb_temp);
    
    $roles_temp = array();
    foreach((array)$f['role'] as $s)
        $roles_temp[] = $fksdb->save($s);
    $roles = serialize($roles_temp);
    
    
    $fksdb->update("documents", array(
        "anfang" => $start_timestamp,
        "bis" => $end_timestamp,
        "datum" => $date_timestamp,
        "author" => $author,
        "no_search" => $no_search,
        "zsb" => $zsbS,
        "rollen" => $roles,
        "statusB" => $base->find_document_statusB($doc->gesperrt, $start_timestamp, $end_timestamp, $doc->timestamp_freigegeben)
    ), array(
        "id" => $doc->id
    ), 1); 
}

exit();
?>
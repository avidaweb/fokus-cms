<?php
if(!defined('DEPENDENCE'))
    exit('class is dependent');

if(!$user->r('kom', 'nledit') || !$suite->rm(5) || $index != 'n616')
    exit($user->noRights());

$r = $fksdb->fetch("SELECT doks FROM ".SQLPRE."newsletters WHERE id = '".$rel."' LIMIT 1");

$doks = $base->db_to_array($r->doks);
if(!is_array($doks)) $doks = array();

$new = $fksdb->save($_POST['newe']);
if($new)
{
    $doks[] = $new;
    $updt = $fksdb->query("UPDATE ".SQLPRE."newsletters SET doks = '".$base->array_to_db($doks)."' WHERE id = '".$rel."' LIMIT 1");
}

$del = $fksdb->save($_POST['del']);
if($del)
{
    foreach($doks as $k => $f)
    {
        if($f == $del)
            unset($doks[$k]);
    }
    $updt = $fksdb->query("UPDATE ".SQLPRE."newsletters SET doks = '".$base->array_to_db($doks)."' WHERE id = '".$rel."' LIMIT 1");
}


require(ROOT.'inc/classes.backend/class.document.preview.php');

$dsql = "";
foreach($doks as $f)
    $dsql .= ($dsql?" OR ":"")." id = '".$f."' ";

$docs = $fksdb->rows("SELECT id, titel, statusA, statusB, dversion_edit, klasse, dk1, dkt1, dk2, dkt2, dk3, dkt3, dk4, dkt4 FROM ".SQLPRE."documents WHERE papierkorb = '0' AND (".$dsql.")");

foreach($doks as $f)
{
    $preview = new DocumentPreview($classes);
    echo $preview->getNewsletterPreview($f, $docs, $dk);
}

echo '
<button class="bl">'.$trans->__('Dokument einfügen').'</button>';
?>
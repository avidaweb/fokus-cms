<?php
if($index == 'date')
{
    $datum = $fksdb->save($_REQUEST['datum']);
    
    if($datum == 'h')
    {
        $datum = date('d.m.Y');
    }
    elseif(Strings::strExists('+', $datum))
    {
        $plus = intval(str_replace('+', '', $datum));
        $datum = date('d.m.Y', mktime(0, 0, 0, date("m"), (date("d")+$plus), date("Y")));
    }
    elseif(Strings::strExists('-', $datum))
    {
        $plus = intval(str_replace('-', '', $datum));
        $datum = date('d.m.Y', mktime(0, 0, 0, date("m"), (date("d")-$plus), date("Y")));
    }
    
    echo $datum;
}  
?>
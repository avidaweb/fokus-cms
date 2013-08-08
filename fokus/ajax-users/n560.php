<?php
if($user->r('per') && $index == 'n560')
{
    $edit = $fksdb->save($_POST['edit']);
    $id = $fksdb->save($_POST['id']);
    $type = $fksdb->save($_POST['type']);
    
    $rC = $fksdb->count($fksdb->query("SELECT id FROM ".SQLPRE."user_roles WHERE benutzer = '".$id."' AND rolle = '1'"));
    if($rC && $user->getRole() != 1 && $id != $user->getID())
        exit($trans->__('Keine Rechte da Superadministrator'));
    
    $eid = $fksdb->save($_POST['eid']);
    $cmitarbeiter = $fksdb->save($_POST['cmitarbeiter']);
    $ckunde = $fksdb->save($_POST['ckunde']);
    $status = $fksdb->save($_POST['status']);
    $avatar = $fksdb->save($_POST['avatar']);
    $anrede = $fksdb->save($_POST['anrede']);
    $vorname = $fksdb->save($_POST['vorname']);
    $nachname = $fksdb->save($_POST['nachname']);
    $namenszusatz = $fksdb->save($_POST['namenszusatz']);
    $str = $fksdb->save($_POST['str']);
    $hn = $fksdb->save($_POST['hn']);
    $plz = $fksdb->save($_POST['plz']);
    $ort = $fksdb->save($_POST['ort']);
    $land = $fksdb->save($_POST['land']);
    $tel_p = $fksdb->save($_POST['tel_p']);
    $tel_g = $fksdb->save($_POST['tel_g']);
    $fax = $fksdb->save($_POST['fax']);
    $tel_p_d = $fksdb->save($_POST['tel_p_d']);
    $tel_g_d = $fksdb->save($_POST['tel_g_d']);
    $fax_d = $fksdb->save($_POST['fax_d']);
    $mobil = $fksdb->save($_POST['mobil']);
    $email = $fksdb->save($_POST['email']);
    $firma = $fksdb->save($_POST['firma']);
    $position = $fksdb->save($_POST['position']);
    $tags = $fksdb->save($_POST['tags']);   
    $pw2 = $fksdb->save($_POST['pw']); 
    $von = explode('.', $fksdb->save($_POST['von'])); 
    $bis = explode('.', $fksdb->save($_POST['bis']));  
    $von_h = $fksdb->save($_POST['von_h']); 
    $von_m = $fksdb->save($_POST['von_m']); 
    $bis_h = $fksdb->save($_POST['bis_h']); 
    $bis_m = $fksdb->save($_POST['bis_m']); 
    $sendmail = $fksdb->save($_POST['sendmail']);
    $pindiv = $fksdb->save($_POST['pindiv']);
    
    if($_POST['von']) $vonA = mktime(($von_h?$von_h:0), ($von_m?$von_m:0), 0, $von[1], $von[0], $von[2]);
    if($_POST['bis']) $bisA = mktime(($bis_h?$bis_h:0), ($bis_m?$bis_m:0), 0, $bis[1], $bis[0], $bis[2]);
    
    $pw = $user->getPasswordHash($pw2);
    
    parse_str($_POST['f'], $fa);
    if(!is_array($fa)) exit();
    
    $cfa = array();
	if(count($fa['cf']))
	{
		foreach($fa['cf'] as $cfk => $cfv)
			$cfa[$fksdb->save($cfk)] = $fksdb->save($cfv);
	}
    $cf_save = $base->array_to_db($cfa);
    
    if(!$edit)
    {
        if(!$user->r('per', 'new'))
            exit();
            
        if(!$tkunde && $type == 1)
            exit();
        if(!$tmitarbeiter && $type == 2)
            exit();
            
        $pindiv_per = $fksdb->fetch("SELECT indiv, widgets FROM ".SQLPRE."users WHERE id = '".$base->getOpt()->pindiv."' AND papierkorb = '0' LIMIT 1");
            
        $fksdb->insert("users", array(
        	"eid" => $eid,
        	"status" => $status,
        	"pw" => $pw,
            "avatar" => $avatar,
            "anrede" => $anrede,
        	"vorname" => $vorname,
        	"nachname" => $nachname,
        	"namenszusatz" => $namenszusatz,
        	"str" => $str,
        	"hn" => $hn,
        	"plz" => $plz,
        	"ort" => $ort,
        	"land" => $land,
        	"tel_p" => $tel_p,
        	"tel_g" => $tel_g,
        	"fax" => $fax,
        	"tel_p_d" => $tel_p_d,
        	"tel_g_d" => $tel_g_d,
        	"fax_d" => $fax_d,
        	"mobil" => $mobil,
        	"email" => $email,
        	"firma" => $firma,
        	"position" => $position,
        	"tags" => $tags,
        	"von" => $vonA,
        	"bis" => $bisA,
        	"registriert" => time(),
        	"registriert_von" => $user->getID(),
        	"type" => $type,
        	"indiv" => $pindiv_per->indiv,
        	"widgets" => $pindiv_per->widgets,
        	"cf" => $cf_save,
        ));   
        $id = $fksdb->getInsertedID();
        
        $login = $user->getLoginHash($id);
        $update = $fksdb->query("UPDATE ".SQLPRE."users SET login = '".$login."' WHERE id = '".$id."' LIMIT 1"); 
        
        if($sendmail && $email)
        {
            $subject = $trans->__('Registrierung auf %1', false, array(str_replace('http://', '', DOMAIN)));
            
            $translatemail = array(
                ($nachname?' '.$trans->__($anrede).' '.$vorname.' '.$nachname:''),
                DOMAIN.'/',
                DOMAIN.'/fokus/',
                ($vorname && $nachname?$vorname.' '.$nachname:'').($vorname && $nachname && $email?' // ':'').$email,
                $pw2,
                str_replace('http://', '', DOMAIN)
            );
            
            $message = $trans->__('Guten Tag %1,
            
es wurde für Sie ein Zugang zur Webseite %6 erstellt.    


Frontend: %2
Backend: %3

Benutzername: %4
Passwort: %5', false, $translatemail);

            $base->email($email, $subject, $message, $base->getOpt('email'));
        }
        
        if($pindiv)
        {
            $upopt = $fksdb->query("UPDATE ".SQLPRE."options SET pindiv = '".$id."' WHERE id = '1' LIMIT 1");
            $base->getOpt()->pindiv = $id;    
        }
        
        if(!$user->r('per', 'prolle'))
            exit();
    }
    else
    {
        if(!$user->r('per', 'edit'))
            exit();
            
        if(!$tkunde && $ckunde == 'true')
            $ckunde = false;
        if(!$tmitarbeiter && $cmitarbeiter == 'true')
            $cmitarbeiter = false;
            
        if(!$ckunde && !$user->isAdmin())
            exit();
            
        $new_type = ($cmitarbeiter == 'true' && $ckunde == 'true'?0:($cmitarbeiter == 'true'?2:1));
        $login = $user->getLoginHash($id);
        
        $update = $fksdb->query("UPDATE ".SQLPRE."users SET ".($pw2?"pw = '".$pw."', ":"")."login = '".$login."', eid = '".$eid."', status = '".$status."', avatar = '".$avatar."', anrede = '".$anrede."', vorname = '".$vorname."', nachname = '".$nachname."', namenszusatz = '".$namenszusatz."', str = '".$str."', hn = '".$hn."', plz = '".$plz."', ort = '".$ort."', land = '".$land."', tel_p = '".$tel_p."', tel_g = '".$tel_g."', fax = '".$fax."', tel_p_d = '".$tel_p_d."', tel_g_d = '".$tel_g_d."', fax_d = '".$fax_d."', mobil = '".$mobil."', email = '".$email."', firma = '".$firma."', position = '".$position."', tags = '".$tags."', von = '".$vonA."', bis = '".$bisA."', type = '".$new_type."', cf = '".$cf_save."' WHERE id = '".$id."' LIMIT 1");
        
        if($pindiv)
        {
            $upopt = $fksdb->query("UPDATE ".SQLPRE."options SET pindiv = '".$id."' WHERE id = '1' LIMIT 1");
            $base->initOpt();   
        }   
    }
    
    echo $id;
} 
?>
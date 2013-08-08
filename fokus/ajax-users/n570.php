<?php
if($user->r('per', 'firma') && $index == 'n570')
{
    $companyQ = $fksdb->query("SELECT * FROM ".SQLPRE."companies WHERE id = '".$rel."' LIMIT 1");
    $company = $fksdb->fetch($companyQ);
    
    if(!$fksdb->count($companyQ))
    {
        echo '
        <h1>'.$trans->__('Neue Firma anlegen.').'</h1>';
    }
    else
    {
        $user->lastUse('firma', $company->id);
        
        echo '
        <h1>'.$trans->__('%1 bearbeiten.', false, array($company->name)).'</h1>';
    }
    
    echo '
    <form action="post">
    <div class="box" id="firma_edit">
        '.($fksdb->count($companyQ)?'<input type="hidden" value="'.$company->id.'" name="id" id="id" />':'').'
        <table>
            <tr>
                <td class="ftd">'.$trans->__('Name').'</td>
                <td><input type="text" id="name" name="name" value="'.$company->name.'" /></td>
            </tr>
        </table>
        <table>
            <tr>
                <td class="ftd">'.$trans->__('Straße').'</td>
                <td><input type="text" id="str" name="str" value="'.$company->str.'" style="width:380px" /><input type="text" id="hn" name="hn" value="'.$company->hn.'" style="width:50px" /></td>
            </tr>
            <tr>
                <td class="ftd">'.$trans->__('PLZ &amp; Ort').'</td>
                <td><input type="text" id="plz" name="plz" value="'.$company->plz.'" style="width:70px" /><input type="text" id="ort" name="ort" value="'.$company->ort.'" style="width:360px" /></td>
            </tr>
            <tr>
                <td class="ftd">'.$trans->__('Land').'</td>
                <td>
                    <select id="land" name="land">';
                    foreach($trans->getCountries() as $ll)
                        echo '<option value="'.$ll.'"'.($ll==$company->land || (!$company->land && $ll == 'Deutschland')?' selected="selected"':'').'>'.$ll.'</option>';
                    echo '
                    </select>
                </td>
            </tr>
        </table>
        <table>
            <tr>
                <td class="ftd">'.$trans->__('Telefon').'</td>
                <td>
                    <input type="text" id="telA" name="telA" value="'.$company->telA.'" style="width:85px" />
                    <input type="text" id="telB" name="telB" value="'.$company->telB.'" style="width:100px" />
                    <input type="text" id="telC" name="telC" value="'.$company->telC.'" style="width:70px" /><br />
                    
                    <span style="width:85px">'.$trans->__('(Vorwahl)').'</span>
                    <span style="width:100px">'.$trans->__('(Hauptnummer)').'</span>
                    <span style="width:70px">'.$trans->__('(Durchwahl)').'</span>
                </td>
            </tr>
            <tr>
                <td class="ftd">'.$trans->__('E-Mail-Adresse').'</td>
                <td><input type="text" id="email" name="email" value="'.$company->email.'" /></td>
            </tr>
        </table>
        <table>
            <tr>
                <td class="ftd">'.$trans->__('Branche').'</td>
                <td><input type="text" id="branche" name="branche" value="'.$company->branche.'" /></td>
            </tr>
            <tr>
                <td class="ftd">'.$trans->__('Status').'</td>
                <td>
                    <select id="status" name="status">
                        <option value=""'.(!$company->status?' selected="selected"':'').'></option>
                        <option value="1"'.($company->status == 1?' selected="selected"':'').'>'.$trans->__('Lieferant').'</option>
                        <option value="2"'.($company->status == 2?' selected="selected"':'').'>'.$trans->__('Auftraggeber').'</option>
                        <option value="3"'.($company->status == 3?' selected="selected"':'').'>'.$trans->__('Kooperationspartner').'</option>
                        <option value="4"'.($company->status == 4?' selected="selected"':'').'>'.$trans->__('Außenstehend').'</option>
                    </select>
                </td>
            </tr>
            <tr>
                <td class="ftd">'.$trans->__('Stichwort &amp; Notizen').'</td>
                <td><textarea type="text" id="tags" name="tags" rows="2">'.$company->tags.'</textarea></td>
            </tr>
        </table>
    </div>
    <div class="box_save">
        <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
        <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
    </div>
    </form>';
}
?>
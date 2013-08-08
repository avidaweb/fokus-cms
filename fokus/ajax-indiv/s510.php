<?php
if($index != 's510' || !$user->isAdmin())
    exit($user->noRights());
    
echo '
<h1>'.$trans->__('Persönliche Einstellungen.').'</h1>

<div class="box">
    <form id="persoein" method="post">
        <div class="greybox">
            <h3 class="calibri">'.$trans->__('Hier können Sie ihre persönlichen Einstellungen für Ihr Benutzerkonto verwalten.').'</h3>
            
            <table>
                <tr class="firstrow">
                    <td>'.$trans->__('Anrede').'</td>
                    <td>
                        <select name="anrede">';
                        $anreden = array('', 'Herr', 'Frau', 'Dr.', 'Dipl.-Ing.', 'Professor');
                        foreach($anreden as $ll)
                            echo '<option value="'.$ll.'"'.($ll == $user->data('anrede')?' selected="selected"':'').'>'.$trans->__($ll).'</option>';
                        echo '
                        </select>
                    </td>
                </tr>
                <tr>
                    <td class="a">'.$trans->__('Vorname').'</td>
                    <td><input type="text" name="vorname" value="'.$user->data('vorname').'" required="required" /></td>
                </tr>
                <tr>
                    <td>'.$trans->__('Nachname').'</td>
                    <td><input type="text" name="nachname" value="'.$user->data('nachname').'" required="required" /></td>
                </tr>
                <tr>
                    <td>'.$trans->__('Namenszusatz').'</td>
                    <td><input type="text" name="namenszusatz" value="'.$user->data('namenszusatz').'" /></td>
                </tr>
                <tr>
                    <td>'.$trans->__('E-Mail-Adresse').'</td>
                    <td><input type="email" name="email" value="'.$user->data('email').'" /></td>
                </tr>
                <tr class="pw">
                    <td>'.$trans->__('Passwort').'</td>
                    <td class="b">
                        <input type="checkbox" id="new_pw_be" name="new_pw" value="1" class="auto" />
                        <label for="new_pw_be">'.$trans->__('Neues Passwort vergeben').'</label>
                        
                        <div class="getnewpw">
                            <input type="password" name="pw" class="pw" value="" />
                            <input type="text" name="pw_t" class="pw_t" value="" autocomplete="off" placeholder="'.$trans->__('Neues Passwort').'" />
                            
                            <p>
                                <input type="checkbox" id="pw_klartext" name="pw_klar" value="1" class="auto" />
                                <label for="pw_klartext">'.$trans->__('Passwort im Klartext eingeben').'</label>
                            </p>
                        </div>
                    </td>
                </tr>
            </table>
            
            '.$trans->__('Alle relevanten Details Ihres Benutzerkontos können Sie in Ihrem %1 bearbeiten.', false, array(
                '<a class="inc_users" id="n535" rel="'.$user->getID().'">'.$trans->__('Mitarbeiter-Profil').'</a>'
            )).'
        </div>
    </form>
</div>

<div class="box_save">
    <input type="button" value="'.$trans->__('verwerfen').'" class="bs1" /> 
    <input type="button" value="'.$trans->__('speichern').'" class="bs2" />
</div>';
?>
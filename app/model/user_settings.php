<?php
class UserSettingsModel
{
    //private $db_functions;

    /*
    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }
    */

    public function getUser($dbh)
    {
        $userDb = '';
        if (isset($_SESSION['user_id']) and is_numeric($_SESSION['user_id'])) {
            $qry = "SELECT * FROM humo_users LEFT JOIN humo_groups
                ON humo_users.user_group_id=humo_groups.group_id
                WHERE humo_users.user_id='" . $_SESSION['user_id'] . "'";
            @$result = $dbh->query($qry);
            if ($result->rowCount() > 0) {
                @$userDb = $result->fetch(PDO::FETCH_OBJ);
            }
        }
        return $userDb;
    }

    // *** $humo_option is needed for mail script ***
    public function updateSettings($dbh, $dataDb, $user, $humo_option)
    {
        $result_message = '';
        if (isset($_POST['update_settings'])) {
            if ($_POST["register_password"] != $_POST["register_repeat_password"]) {
                $result_message = __('ERROR: No identical passwords');
            }

            if ($result_message == '') {
                //$user_register_date = date("Y-m-d H:i");
                $sql = "UPDATE humo_users SET user_mail='" . safe_text_db($_POST["register_mail"]) . "'";
                if ($_POST["register_password"] != '')
                    $hashToStoreInDb = password_hash($_POST["register_password"], PASSWORD_DEFAULT);
                if (isset($hashToStoreInDb)) $sql .= ", user_password_salted='" . $hashToStoreInDb . "'";
                $sql .= " WHERE user_id=" . $user->user_id;
                $result = $dbh->query($sql);

                $result_message = __('Your settings are updated!');

                // *** Only update 2FA settings if database is updated and 2FA settings are changed ***
                if (isset($user->user_2fa_enabled) and isset($_POST['user_2fa_check'])) {
                    // *** 2FA Authenticator (2fa_code = code from 2FA authenticator) ***
                    if (!isset($_POST['user_2fa_enabled']) and $user->user_2fa_enabled) {
                        // *** Disable 2FA ***
                        $sql = "UPDATE humo_users SET user_2fa_enabled='' WHERE user_id=" . $user->user_id;
                        $result = $dbh->query($sql);
                    }
                    if (isset($_POST['user_2fa_enabled']) and !$user->user_2fa_enabled) {
                        if ($_POST['2fa_code'] and is_numeric($_POST['2fa_code'])) {
                            $Authenticator = new Authenticator();
                            $checkResult = $Authenticator->verifyCode($user->user_2fa_auth_secret, $_POST['2fa_code'], 2);        // 2 = 2*30sec clock tolerance
                            if (!$checkResult) {
                                $result_message = __('Wrong 2FA code. Please enter valid 2FA code to enable 2FA authentication.') . '<br>';
                            } else {
                                $sql = "UPDATE humo_users SET user_2fa_enabled='1' WHERE user_id=" . $user->user_id;
                                $result = $dbh->query($sql);
                                $result_message = __('Enabled 2FA authentication.') . '<br>';
                            }
                        } else {
                            // *** No 2FA code entered ***
                            $result_message = __('Wrong 2FA code. Please enter valid 2FA code to enable 2FA authentication.') . '<br>';
                        }
                    }
                }
            }

            if ($dataDb->tree_email) {
                $register_address = $dataDb->tree_email;
                $register_subject = "HuMo-genealogy. " . __('Updated profile') . ": " . $user->user_name . "\n";

                // *** It's better to use plain text in the subject ***
                $register_subject = strip_tags($register_subject, ENT_QUOTES);

                $register_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
                $register_message .= "<br><br>\n";
                $register_message .= __('User updated his/ her profile') . "<br>\n";
                $register_message .= __('Name') . ':' . $user->user_name . "<br>\n";
                $register_message .= __('E-mail') . ": <a href='mailto:" . $_POST['register_mail'] . "'>" . $_POST['register_mail'] . "</a><br>\n";

                include_once(__DIR__ . '/../../include/mail.php');

                // *** Set who the message is to be sent from ***
                $mail->setFrom($_POST['register_mail'], $user->user_name);
                // *** Set who the message is to be sent to ***
                $mail->addAddress($register_address, $register_address);
                // *** Set the subject line ***
                $mail->Subject = $register_subject;
                $mail->msgHTML($register_message);
                // *** Replace the plain text body with one created manually ***
                //$mail->AltBody = 'This is a plain-text message body';
                //if (!$mail->send()) {
                //    echo '<br><b>'.__('Sending e-mail failed!').' '. $mail->ErrorInfo.'</b>';
                //} else {
                //    echo '<br><b>'.__('E-mail sent!').'</b><br>';
                //}
            }
        }
        return $result_message;
    }

    public function showQRcode($dbh, $get_user, $user)
    {
        if (isset($get_user->user_name) and $user['group_menu_change_password'] == 'y') {
            if ($user['group_menu_change_password'] == 'y') {
                if (isset($get_user->user_2fa_auth_secret)) {
                    // *** 2FA Two factor authentification ***
                    $Authenticator = new Authenticator();
                    if ($get_user->user_2fa_auth_secret) {
                        $user_2fa_auth_secret = $get_user->user_2fa_auth_secret;
                    } else {
                        $user_2fa_auth_secret = $Authenticator->generateRandomSecret();

                        // *** Save auth_secret, so it's not changed anymore ***
                        $sql = "UPDATE humo_users SET user_2fa_auth_secret='" . safe_text_db($user_2fa_auth_secret) . "' WHERE user_id=" . $get_user->user_id;
                        $result = $dbh->query($sql);
                    }

                    if (isset($_GET['2fa']) and $_GET['2fa'] == '1') {
                        //$siteusernamestr= "Your Sites Unique String";
                        $siteusernamestr = 'HuMo-genealogy ' . $_SERVER['SERVER_NAME'];
                        $twofa["qrCodeUrl"] = $Authenticator->getQR($siteusernamestr, $user_2fa_auth_secret);
                        $twofa["checked"] = '';
                        if ($get_user->user_2fa_enabled == 1) $twofa["checked"] = ' checked="true"';
                    }
                }
            }
        }
        if (isset($twofa)) return $twofa;
    }
}

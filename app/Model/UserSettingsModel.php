<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use Genealogy\Include\Authenticator;
use PDO;

class UserSettingsModel extends BaseModel
{
    private $userDb = null;

    public function __construct($config)
    {
        parent::__construct($config);
        $this->getUser();
    }

    public function getUser()
    {
        if (isset($_SESSION['user_id']) && is_numeric($_SESSION['user_id'])) {
            $qry = "SELECT * FROM humo_users LEFT JOIN humo_groups
                ON humo_users.user_group_id=humo_groups.group_id
                WHERE humo_users.user_id='" . $_SESSION['user_id'] . "'";
            $result = $this->dbh->query($qry);
            if ($result->rowCount() > 0) {
                $this->userDb = $result->fetch(PDO::FETCH_OBJ);
            }
        }
    }

    public function getUserDb()
    {
        return $this->userDb;
    }

    public function updateSettings($dataDb): string
    {
        $result_message = '';
        if (isset($_POST['update_settings'])) {
            if ($_POST["register_password"] != $_POST["register_repeat_password"]) {
                $result_message = __('ERROR: No identical passwords');
            }

            if ($result_message == '') {
                if ($_POST["register_password"] != '') {
                    $hashToStoreInDb = password_hash($_POST["register_password"], PASSWORD_DEFAULT);
                }
                $sql = "UPDATE humo_users SET user_mail = :user_mail";
                $params = [
                    ':user_mail' => $_POST["register_mail"]
                ];
                if (isset($hashToStoreInDb)) {
                    $sql .= ", user_password_salted = :user_password_salted";
                    $params[':user_password_salted'] = $hashToStoreInDb;
                }
                $sql .= " WHERE user_id = :user_id";
                $params[':user_id'] = $this->userDb->user_id;

                $stmt = $this->dbh->prepare($sql);
                $stmt->execute($params);

                $result_message = __('Your settings are updated!');

                // *** Only update 2FA settings if database is updated and 2FA settings are changed ***
                if ($this->userDb->user_2fa_enabled && isset($_POST['user_2fa_check'])) {
                    // *** 2FA Authenticator (2fa_code = code from 2FA authenticator) ***
                    if (!isset($_POST['user_2fa_enabled']) && $this->userDb->user_2fa_enabled) {
                        // *** Disable 2FA ***
                        $sql = "UPDATE humo_users SET user_2fa_enabled='' WHERE user_id=" . $this->userDb->user_id;
                        $this->dbh->query($sql);
                    }
                    if (isset($_POST['user_2fa_enabled']) && !$this->userDb->user_2fa_enabled) {
                        if ($_POST['2fa_code'] && is_numeric($_POST['2fa_code'])) {
                            $Authenticator = new Authenticator();
                            // *** 2 = 2*30sec clock tolerance ***
                            $checkResult = $Authenticator->verifyCode($this->userDb->user_2fa_auth_secret, $_POST['2fa_code'], 2);
                            if (!$checkResult) {
                                $result_message = __('Wrong 2FA code. Please enter valid 2FA code to enable 2FA authentication.') . '<br>';
                            } else {
                                $sql = "UPDATE humo_users SET user_2fa_enabled='1' WHERE user_id=" . $this->userDb->user_id;
                                $this->dbh->query($sql);
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
                $register_subject = "HuMo-genealogy. " . __('Updated profile') . ": " . $this->userDb->user_name . "\n";

                // *** It's better to use plain text in the subject ***
                $register_subject = strip_tags($register_subject, ENT_QUOTES);

                $register_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
                $register_message .= "<br><br>\n";
                $register_message .= __('User updated his/ her profile') . "<br>\n";
                $register_message .= __('Name') . ':' . $this->userDb->user_name . "<br>\n";
                $register_message .= __('E-mail') . ": <a href='mailto:" . $_POST['register_mail'] . "'>" . $_POST['register_mail'] . "</a><br>\n";

                $humo_option = $this->humo_option; // Used in mail.php
                include_once(__DIR__ . '/../../include/mail.php');

                // *** Set who the message is to be sent from ***
                $mail->setFrom($_POST['register_mail'], $this->userDb->user_name);
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

    public function showQRcode()
    {
        if ((isset($this->userDb->user_name) and $this->user['group_menu_change_password'] == 'y') && $this->user['group_menu_change_password'] == 'y') {
            if (isset($this->userDb->user_2fa_auth_secret)) {
                // *** 2FA Two factor authentification ***
                $Authenticator = new Authenticator();
                if ($this->userDb->user_2fa_auth_secret) {
                    $user_2fa_auth_secret = $this->userDb->user_2fa_auth_secret;
                } else {
                    $user_2fa_auth_secret = $Authenticator->generateRandomSecret();

                    // *** Save auth_secret, so it's not changed anymore ***
                    $sql = "UPDATE humo_users SET user_2fa_auth_secret = :auth_secret WHERE user_id = :user_id";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->execute([
                        ':auth_secret' => $user_2fa_auth_secret,
                        ':user_id' => $this->userDb->user_id
                    ]);
                }

                if (isset($_GET['2fa']) && $_GET['2fa'] == '1') {
                    //$siteusernamestr= "Your Sites Unique String";
                    $siteusernamestr = 'HuMo-genealogy ' . $_SERVER['SERVER_NAME'];
                    $twofa["qrCodeUrl"] = $Authenticator->getQR($siteusernamestr, $user_2fa_auth_secret);
                    $twofa["checked"] = '';
                    if ($this->userDb->user_2fa_enabled == 1) {
                        $twofa["checked"] = ' checked="true"';
                    }
                }
            }
        }
        if (isset($twofa)) {
            return $twofa;
        }
        return null;
    }
}

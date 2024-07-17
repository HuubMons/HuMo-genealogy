<?php
class RegisterModel
{
    private $db_functions;

    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }

    public function getFormdata()
    {
        $register["name"] = '';
        if (isset($_POST['register_name'])) {
            $register["name"] = $_POST['register_name'];
        }

        $register["mail"] = '';
        if (isset($_POST['register_mail'])) {
            $register["mail"] = $_POST['register_mail'];
        }

        $register["text"] = '';
        if (isset($_POST['register_text'])) {
            $register["text"] = $_POST['register_text'];
        }

        return $register;
    }

    public function register_allowed($humo_option)
    {
        // *** Check block_spam_answer ***
        $register["register_allowed"] = false;
        if (isset($_POST['send_mail']) && (isset($_POST['register_block_spam']) && strtolower($_POST['register_block_spam']) === strtolower($humo_option["block_spam_answer"]))) {
            $register["register_allowed"] = true;
        }
        if ($humo_option["registration_use_spam_question"] != 'y') {
            $register["register_allowed"] = true;
        }
        return $register["register_allowed"];
    }

    public function register_user($dbh, $dataDb, $humo_option, $register)
    {
        $register["show_form"] = true;
        $register["error"] = '';
        if (isset($_POST['send_mail']) && $register["register_allowed"] == true) {
            $register["show_form"] = false;

            $usersql = 'SELECT * FROM humo_users WHERE user_name="' . safe_text_db($_POST["register_name"]) . '"';
            $user = $dbh->query($usersql);
            $userDb = $user->fetch(PDO::FETCH_OBJ);
            if (isset($userDb->user_id) || strtolower(safe_text_db($_POST["register_name"])) === "admin") {
                $register["error"] = __('ERROR: username already exists');
            }

            if ($_POST["register_password"] != $_POST["register_repeat_password"]) {
                $register["error"] = __('ERROR: No identical passwords');
            }

            if (strlen($_POST["register_password"]) < 6) {
                $register["error"] = __('ERROR: Password has to contain at least 6 characters');
            }

            if (!$register["error"]) {
                $user_register_date = date("Y-m-d H:i");
                $hashToStoreInDb = password_hash($_POST["register_password"], PASSWORD_DEFAULT);
                $sql = "INSERT INTO humo_users SET
                    user_name='" . safe_text_db($_POST["register_name"]) . "',
                    user_remark='" . safe_text_db($_POST["register_text"]) . "',
                    user_register_date='" . safe_text_db($user_register_date) . "',
                    user_mail='" . safe_text_db($_POST["register_mail"]) . "',
                    user_password_salted='" . $hashToStoreInDb . "',
                    user_group_id='" . $humo_option["visitor_registration_group"] . "';";
                $result = $dbh->query($sql);

                // *** Mail new registered user to the administrator ***
                $register_address = '';
                if (isset($dataDb->tree_email)) {
                    $register_address = $dataDb->tree_email;
                } // Used in older HuMo-genealogy versions. Backwards compatible...
                if ($humo_option["general_email"]) {
                    $register_address = $humo_option["general_email"];
                }

                $register_subject = "HuMo-genealogy. " . __('New registered user') . ": " . $_POST['register_name'] . "\n";

                // *** It's better to use plain text in the subject ***
                $register_subject = strip_tags($register_subject, ENT_QUOTES);

                $register_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
                $register_message .= "<br><br>\n";
                $register_message .= __('New registered user') . "<br>\n";
                $register_message .= __('Name') . ':' . $_POST['register_name'] . "<br>\n";
                $register_message .= __('E-mail') . ": <a href='mailto:" . $_POST['register_mail'] . "'>" . $_POST['register_mail'] . "</a><br>\n";
                $register_message .= $_POST['register_text'] . "<br>\n";

                include_once(__DIR__ . '/../../include/mail.php');

                // *** Set who the message is to be sent from ***
                //$mail->setFrom($_POST['register_mail'], $_POST['register_name']);
                // *** Changed july 2024: Set who the message is to be sent from ***
                if ($humo_option["email_user"] && filter_var($humo_option["email_user"], FILTER_VALIDATE_EMAIL)) {
                    // *** Some providers don't accept other e-mail addresses because of safety reasons! ***
                    $mail->setFrom($humo_option["email_user"], $humo_option["email_user"]);
                } else {
                    $mail->setFrom($_POST['register_mail'], $_POST['register_name']);
                }

                // *** Added july 2024 ***
                $mail->AddReplyTo($_POST['register_mail'], $_POST['register_name']);

                // *** Set who the message is to be sent to ***
                $mail->addAddress($register_address, $register_address);

                // *** Set the subject line ***
                $mail->Subject = $register_subject;
                $mail->msgHTML($register_message);

                // *** Replace the plain text body with one created manually ***
                //$mail->AltBody = 'This is a plain-text message body';
                if (!$mail->send()) {
                    //  echo '<br><b>'.__('Sending e-mail failed!').' '. $mail->ErrorInfo.'</b>';
                    //  } else {
                    //  echo '<br><b>'.__('E-mail sent!').'</b><br>';
                }
            }
        }
        return $register;
    }
}

<?php
class RegisterModel extends BaseModel
{
    public function getFormdata(): array
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

    public function register_allowed()
    {
        // *** Check block_spam_answer ***
        $register["register_allowed"] = false;
        if (isset($_POST['send_mail']) && (isset($_POST['register_block_spam']) && strtolower($_POST['register_block_spam']) === strtolower($this->humo_option["block_spam_answer"]))) {
            $register["register_allowed"] = true;
        }
        if ($this->humo_option["registration_use_spam_question"] != 'y') {
            $register["register_allowed"] = true;
        }
        return $register["register_allowed"];
    }

    public function register_user($dataDb, $register): array
    {
        $register["show_form"] = true;
        $register["error"] = '';
        if (isset($_POST['send_mail']) && $register["register_allowed"] == true) {
            $register["show_form"] = false;

            $usersql = 'SELECT * FROM humo_users WHERE user_name = :user_name';
            $stmt = $this->dbh->prepare($usersql);
            $stmt->execute([':user_name' => $_POST["register_name"]]);
            $user = $this->dbh->query($usersql);
            $userDb = $user->fetch(PDO::FETCH_OBJ);
            if (isset($userDb->user_id) || strtolower($_POST["register_name"]) === "admin") {
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
                $sql = "INSERT INTO humo_users 
                    (user_name, user_remark, user_register_date, user_mail, user_password_salted, user_group_id)
                    VALUES (:user_name, :user_remark, :user_register_date, :user_mail, :user_password_salted, :user_group_id)";
                $stmt = $this->dbh->prepare($sql);
                $stmt->execute([
                    ':user_name' => $_POST["register_name"],
                    ':user_remark' => $_POST["register_text"],
                    ':user_register_date' => $user_register_date,
                    ':user_mail' => $_POST["register_mail"],
                    ':user_password_salted' => $hashToStoreInDb,
                    ':user_group_id' => $this->humo_option["visitor_registration_group"]
                ]);

                // *** Mail new registered user to the administrator ***
                $register_address = '';
                if (isset($dataDb->tree_email)) {
                    $register_address = $dataDb->tree_email;
                } // Used in older HuMo-genealogy versions. Backwards compatible...
                if ($this->humo_option["general_email"]) {
                    $register_address = $this->humo_option["general_email"];
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

                $humo_option = $this->humo_option; // Used in mail.php
                include_once(__DIR__ . '/../../include/mail.php');

                // *** Set who the message is to be sent from ***
                //$mail->setFrom($_POST['register_mail'], $_POST['register_name']);
                // *** Changed july 2024: Set who the message is to be sent from ***
                if ($this->humo_option["email_sender"] && filter_var($this->humo_option["email_sender"], FILTER_VALIDATE_EMAIL)) {
                    // *** Some providers don't accept other e-mail addresses because of safety reasons! ***
                    $mail->setFrom($this->humo_option["email_sender"], $this->humo_option["email_sender"]);
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

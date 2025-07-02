<?php
class ResetPasswordModel extends BaseModel
{
    public function get_activation_key(): string
    {
        $activation_key = '';
        if (isset($_GET['ak']) && ctype_alnum($_GET['ak'])) {
            $activation_key = $_GET['ak'];
        }
        if (isset($_POST['ak']) && ctype_alnum($_POST['ak'])) {
            $activation_key = $_POST['ak'];
        }
        return $activation_key;
    }

    public function get_userid(): int
    {
        $userid = 0;
        if (isset($_GET['userid']) && is_numeric($_GET['userid'])) {
            $userid = $_GET['userid'];
        }
        if (isset($_POST['userid']) && is_numeric($_POST['userid'])) {
            $userid = $_POST['userid'];
        }
        return $userid;
    }

    public function check_input(): string
    {
        $check_input_msg = '';
        if (isset($_POST['user_mail'])) {
            if (!filter_var($_POST['user_mail'], FILTER_VALIDATE_EMAIL)) {
                $check_input_msg = __('Your email address is not correct') . '<br>';
            }

            if (isset($_POST['register_block_spam']) && strtolower($_POST['register_block_spam']) === strtolower($this->humo_option["block_spam_answer"])) {
                //
            } else {
                $check_input_msg .= __('Wrong answer to the block-spam question! Try again...') . '<br>';
            }

            $countmail = $this->dbh->prepare("SELECT user_id, user_mail, user_name FROM humo_users WHERE user_mail=:email");
            $countmail->bindValue(':email', $_POST['user_mail'], PDO::PARAM_STR);
            $countmail->execute();
            $row = $countmail->fetch(PDO::FETCH_OBJ);
            $nr_mail = $countmail->rowCount();
            if ($nr_mail == 0) {
                $check_input_msg = __('This email address was not found in our database.') . "&nbsp;" . __('Please contact the site owner.');
            } elseif ($nr_mail > 1) {
                $check_input_msg = __('Password activation failed because mail address is used multiple times.') . '&nbsp;' . __('Please contact the site owner.');
            }
        }
        return $check_input_msg;
    }

    public function check_clicked_link($userid, $activation_key): bool
    {
        // *** Check clicked linked in mail ***
        $message_activation = false;
        if (isset($_GET['ak']) && $_GET['ak'] != '') {
            $tm = time() - 86400; // Duration within which the key is valid is 86400 sec (=24 hours) - can be adjusted here 
            $sql = $this->dbh->prepare("SELECT retrieval_userid FROM humo_pw_retrieval
                WHERE retrieval_pkey=:ak and retrieval_userid=:userid and retrieval_time > '$tm' and retrieval_status='pending'");
            $sql->bindParam(':userid', $userid, PDO::PARAM_STR, 10);
            $sql->bindParam(':ak', $activation_key, PDO::PARAM_STR, 32);
            $sql->execute();
            $no = $sql->rowCount();
            if ($no <> 1) {
                //$message_activation = __('Activation failed');
                $message_activation = true;
            }
        }
        return $message_activation;
    }

    public function check_table(): void
    {
        if (isset($_POST['user_mail'])) {
            $pw_table = $this->dbh->prepare("CREATE TABLE IF NOT EXISTS `humo_pw_retrieval` (
                `retrieval_userid` varchar(20) NOT NULL,
                `retrieval_pkey` varchar(32) NOT NULL,
                `retrieval_time` varchar(10) NOT NULL,
                `retrieval_status` varchar(7) NOT NULL
                ) DEFAULT CHARSET=utf8");
            $pw_table->execute();
        }
    }

    public function get_activation_url(): string
    {
        $site_url = '';
        if (isset($_POST['user_mail'])) {
            $site_url  = @($_SERVER["HTTPS"] != 'on') ? 'http://' . $_SERVER["SERVER_NAME"] :  'https://' . $_SERVER["SERVER_NAME"];
            // *** May 2022: removed port. For some reason port 80 was always shown ***
            //$site_url .= ( $_SERVER["SERVER_PORT"] !== 80 ) ? ":".$_SERVER["SERVER_PORT"] : "";
            $site_url .= $_SERVER["REQUEST_URI"];
        }
        return $site_url;
    }

    public function check_new_password($userid, $activation_key): string
    {
        $message_password = '';
        if (isset($_POST['password']) && $_POST['password'] != '') {
            $password = $_POST['password'];
            $password2 = $_POST['password2'];
            $tm = time() - 86400;

            $sql = $this->dbh->prepare("SELECT retrieval_userid FROM humo_pw_retrieval
                WHERE retrieval_pkey=:ak and retrieval_userid=:userid and retrieval_time > '$tm' and retrieval_status='pending'");
            $sql->bindParam(':userid', $userid, PDO::PARAM_STR, 10);
            $sql->bindParam(':ak', $activation_key, PDO::PARAM_STR, 32);
            $sql->execute();
            $no = $sql->rowCount();
            if ($no <> 1) {
                $message_password = $message_password . __('Password activation failed.') . '&nbsp;' . __('Please contact the site owner.') . '<br>';
            }

            if (strlen($password) < 4 || strlen($password) > 15) {
                $message_password = $message_password . __('Password must be at least 4 char and maximum 15 char long') . '<br>';
            }

            if ($password <> $password2) {
                $message_password = $message_password . __('The passwords don\'t match!') . '<br>';
            }
        }
        return $message_password;
    }
}

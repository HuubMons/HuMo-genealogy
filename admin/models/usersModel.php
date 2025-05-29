<?php
class UsersModel extends AdminBaseModel
{
    function update_user(): string
    {
        $alert = '';
        if (isset($_POST['change_user'])) {
            $usersql = "SELECT * FROM humo_users ORDER BY user_name";
            $user = $this->dbh->query($usersql);
            while ($userDb = $user->fetch(PDO::FETCH_OBJ)) {
                if (is_numeric($_POST[$userDb->user_id . "group_id"]) && is_numeric($_POST[$userDb->user_id . "user_id"])) {
                    $username = $_POST[$userDb->user_id . "username"];
                    $usermail = $_POST[$userDb->user_id . "usermail"];
                    if ($_POST[$userDb->user_id . "username"] == "") {
                        $username = 'GEEN NAAM / NO NAME';
                    }
                    $sql = "UPDATE humo_users SET
                        user_name='" . safe_text_db($username) . "',
                        user_mail='" . safe_text_db($usermail) . "', ";
                    if (isset($_POST[$userDb->user_id . "password"]) && $_POST[$userDb->user_id . "password"]) {
                        $hashToStoreInDb = password_hash($_POST[$userDb->user_id . "password"], PASSWORD_DEFAULT);
                        $sql = $sql . "user_password_salted='" . $hashToStoreInDb . "', user_password='', ";
                    }
                    $sql .= "user_group_id='" . safe_text_db($_POST[$userDb->user_id . "group_id"]);
                    $sql .= "' WHERE user_id=" . safe_text_db($_POST[$userDb->user_id . "user_id"]);
                    try {
                        $this->dbh->query($sql);
                    } catch (PDOException $e) {
                        $alert = __('Error: user name probably allready exist.') . '<br>';
                    }
                }
            }
        }

        if (isset($_POST['add_user']) && is_numeric($_POST["add_group_id"])) {
            $user_prep = $this->dbh->prepare("INSERT INTO humo_users SET
                user_name=:add_username, user_mail=:add_usermail,
                user_password_salted=:add_password_salted, user_group_id=:add_group_id");
            $user_prep->bindValue(':add_username', $_POST["add_username"], PDO::PARAM_STR);
            $user_prep->bindValue(':add_usermail', $_POST["add_usermail"]);
            $hashToStoreInDb = password_hash($_POST["add_password"], PASSWORD_DEFAULT);
            $user_prep->bindValue(':add_password_salted', $hashToStoreInDb);
            $user_prep->bindValue(':add_group_id', $_POST["add_group_id"], PDO::PARAM_INT);
            try {
                $user_prep->execute();
            } catch (PDOException $e) {
                $alert =  __('Error: user name probably allready exist.') . '<br>';
            }
        }

        if (isset($_POST['remove_user2']) && is_numeric($_POST['remove_user'])) {
            // *** Delete source connection ***
            $sql = "DELETE FROM humo_users WHERE user_id='" . safe_text_db($_POST['remove_user']) . "'";
            $this->dbh->query($sql);
        }

        if (isset($_GET['unblock_ip_address'])) {
            $sql = "DELETE FROM humo_user_log WHERE log_ip_address='" . safe_text_db($_GET['unblock_ip_address']) . "' AND log_status='failed'";
            $this->dbh->query($sql);
        }

        return $alert;
    }

    public function check_username_password(): array
    {
        // *** Check for standard admin username and password ***
        $user['check_admin_user'] = false;
        $user['check_admin_pw'] = false;
        $sql = "SELECT * FROM humo_users WHERE user_group_id='1'";
        $check_login = $this->dbh->query($sql);
        while ($check_loginDb = $check_login->fetch(PDO::FETCH_OBJ)) {
            if ($check_loginDb->user_name == 'admin') {
                $user['check_admin_user'] = true;
            }
            // *** Check old password method ***
            if ($check_loginDb->user_password == MD5('humogen')) {
                $user['check_admin_pw'] = true;
            }
            $check_password = password_verify('humogen', $check_loginDb->user_password_salted);
            if ($check_password) {
                $user['check_admin_pw'] = true;
            }
        }

        return $user;
    }
}

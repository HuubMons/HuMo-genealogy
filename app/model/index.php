<?php
class IndexModel
{
    public function login($dbh, $db_functions, $visitor_ip)
    {
        // *** Log in ***
        $valid_user = false;
        $index['fault'] = false;
        if (isset($_POST["username"]) && isset($_POST["password"])) {
            $resultDb = $db_functions->get_user($_POST["username"], $_POST["password"]);
            if ($resultDb) {
                $valid_user = true;

                // *** 2FA is enabled, so check 2FA code ***
                if (isset($resultDb->user_2fa_enabled) && $resultDb->user_2fa_enabled) {
                    $valid_user = false;
                    $index['fault'] = true;
                    include_once(__DIR__ . "/../../include/2fa_authentication/authenticator.php");

                    if ($_POST['2fa_code'] && is_numeric($_POST['2fa_code'])) {
                        $Authenticator = new Authenticator();
                        $checkResult = $Authenticator->verifyCode($resultDb->user_2fa_auth_secret, $_POST['2fa_code'], 2);        // 2 = 2*30sec clock tolerance
                        if ($checkResult) {
                            $valid_user = true;
                            $index['fault'] = false;
                        }
                    }
                }

                if ($valid_user) {
                    $_SESSION['user_name'] = $resultDb->user_name;
                    $_SESSION['user_id'] = $resultDb->user_id;
                    $_SESSION['user_group_id'] = $resultDb->user_group_id;

                    // *** August 2023: Also login for admin pages ***
                    // *** Edit family trees [GROUP SETTING] ***
                    $groepsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='" . $resultDb->user_group_id . "'");
                    @$groepDb = $groepsql->fetch(PDO::FETCH_OBJ);
                    if (isset($groepDb->group_edit_trees)) {
                        $group_edit_trees = $groepDb->group_edit_trees;
                    }
                    // *** Edit family trees [USER SETTING] ***
                    if (isset($resultDb->user_edit_trees) && $resultDb->user_edit_trees) {
                        if ($group_edit_trees) {
                            $group_edit_trees .= ';' . $resultDb->user_edit_trees;
                        } else {
                            $group_edit_trees = $resultDb->user_edit_trees;
                        }
                    }
                    if ($groepDb->group_admin != 'j' && $group_edit_trees == '') {
                        // *** User is not an administrator or editor ***
                        //echo __('Access to admin pages is not allowed.');
                        //exit;
                    } else {
                        $_SESSION['user_name_admin'] = $resultDb->user_name;
                        $_SESSION['user_id_admin'] = $resultDb->user_id;
                        $_SESSION['group_id_admin'] = $resultDb->user_group_id;
                    }

                    // *** Save succesful login into log! ***
                    $sql = "INSERT INTO humo_user_log SET
                        log_date='" . date("Y-m-d H:i") . "',
                        log_username='" . $resultDb->user_name . "',
                        log_ip_address='" . $visitor_ip . "',
                        log_user_admin='user',
                        log_status='success'";
                    $dbh->query($sql);

                    // *** Send to secured page ***
                    // TODO check link
                    //header("Location: index.php?menu_choice=main_index");
                    header("Location: index.php");
                    exit();
                }
            } else {
                // *** No valid user found ***
                $index['fault'] = true;

                // *** Save failed login into log! ***
                $sql = "INSERT INTO humo_user_log SET
                    log_date='" . date("Y-m-d H:i") . "',
                    log_username='" . safe_text_db($_POST["username"]) . "',
                    log_ip_address='" . $visitor_ip . "',
                    log_user_admin='user',
                    log_status='failed'";
                $dbh->query($sql);
            }
        }
        return $index;
    }
}

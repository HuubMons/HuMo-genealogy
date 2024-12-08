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

    public function get_route($humo_option)
    {
        // *** New routing script sept. 2023. Search route, return match or not found ***
        $index['page'] = 'index';
        $index['main_admin'] = $humo_option["database_name"];
        $index['tmp_path'] = '';

        $router = new Router();
        $matchedRoute = $router->get_route($_SERVER['REQUEST_URI']);
        if (isset($matchedRoute['page'])) {
            $index['page'] = $matchedRoute['page'];

            // TODO remove title from router script
            $index['main_admin'] = $matchedRoute['title'];

            if (isset($matchedRoute['select_tree_id'])) {
                $index['select_tree_id'] = $matchedRoute['select_tree_id'];
            }

            // *** Used for list_names ***
            if (isset($matchedRoute['last_name']) && is_string($matchedRoute['last_name'])) {
                $index['last_name'] = $matchedRoute['last_name'];
            }

            // Old link from http://www.stamboomzoeker.nl to updated website using new links.
            // http://127.0.0.1/humo-genealogy/gezin.php?database=humo2_&id=F59&hoofdpersoon=I151
            if ($humo_option["url_rewrite"] == 'j' && isset($_GET["database"]) && isset($_GET["id"])) {
                // Skip routing. Just use $_GET["id"] from link.
            } elseif (isset($matchedRoute['id'])) {
                // *** Used for source ***
                // TODO improve processing of these variables 
                $index['id'] = $matchedRoute['id']; // for source
                $_GET["id"] = $matchedRoute['id']; // for family page, and other pages? TODO improve processing of these variables.
            }

            if ($matchedRoute['tmp_path']) {
                $index['tmp_path'] = $matchedRoute['tmp_path'];
            }
        }
        return $index;
    }

    public function process_ltr_rtl($language)
    {
        // *** Process LTR and RTL variables ***
        $index['dirmark1'] = "&#x200E;";  //ltr marker
        $index['dirmark2'] = "&#x200F;";  //rtl marker
        $index['rtlmarker'] = "ltr";
        $index['alignmarker'] = "left";

        // *** Switch direction markers if language is RTL ***
        if ($language["dir"] == "rtl") {
            $index['dirmark1'] = "&#x200F;";  //rtl marker
            $index['dirmark2'] = "&#x200E;";  //ltr marker
            $index['rtlmarker'] = "rtl";
            $index['alignmarker'] = "right";
        }

        //if (isset($screen_mode) && $screen_mode == "PDF") {
        //    $dirmark1 = '';
        //    $dirmark2 = '';
        //}

        return $index;
    }
}

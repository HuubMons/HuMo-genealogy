<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

echo '<h1 class="center">' . __('Users') . '</h1>';

if (isset($_POST['change_user'])) {
    $usersql = "SELECT * FROM humo_users ORDER BY user_name";
    $user = $dbh->query($usersql);
    while ($userDb = $user->fetch(PDO::FETCH_OBJ)) {
        if (is_numeric($_POST[$userDb->user_id . "group_id"]) and is_numeric($_POST[$userDb->user_id . "user_id"])) {
            $username = $_POST[$userDb->user_id . "username"];
            $usermail = $_POST[$userDb->user_id . "usermail"];
            if ($_POST[$userDb->user_id . "username"] == "") {
                $username = 'GEEN NAAM / NO NAME';
            }
            $sql = "UPDATE humo_users SET
                user_name='" . safe_text_db($username) . "',
                user_mail='" . safe_text_db($usermail) . "', ";
            if (isset($_POST[$userDb->user_id . "password"]) and $_POST[$userDb->user_id . "password"]) {
                $hashToStoreInDb = password_hash($_POST[$userDb->user_id . "password"], PASSWORD_DEFAULT);
                $sql = $sql . "user_password_salted='" . $hashToStoreInDb . "', user_password='', ";
            }
            $sql .= "user_group_id='" . safe_text_db($_POST[$userDb->user_id . "group_id"]);
            $sql .= "' WHERE user_id=" . safe_text_db($_POST[$userDb->user_id . "user_id"]);
            //$result=$dbh->query($sql);
            try {
                $result = $dbh->query($sql);
            } catch (PDOException $e) {
                echo __('Error: user name probably allready exist.') . '<br>';
            }
        }
    }
}

if (isset($_POST['add_user']) and is_numeric($_POST["add_group_id"])) {
    $user_prep = $dbh->prepare("INSERT INTO humo_users SET
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
        echo __('Error: user name probably allready exist.') . '<br>';
    }
}

// *** Remove user ***
if (isset($_GET['remove_user'])) {
?>
    <div class="confirm">
        <?= __('Are you sure you want to delete this user?'); ?>
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="<?= $_GET['page']; ?>">
            <input type="hidden" name="remove_user" value="<?= $_GET['remove_user']; ?>">
            <input type="Submit" name="remove_user2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="Submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php
}
if (isset($_POST['remove_user2']) and is_numeric($_POST['remove_user'])) {
    // *** Delete source connection ***
    $sql = "DELETE FROM humo_users WHERE user_id='" . safe_text_db($_POST['remove_user']) . "'";
    $result = $dbh->query($sql);
}

if (isset($_GET['unblock_ip_address'])) {
    $sql = "DELETE FROM humo_user_log WHERE log_ip_address='" . safe_text_db($_GET['unblock_ip_address']) . "' AND log_status='failed'";
    $result = $dbh->query($sql);
}

// *************
// *** Users ***
// *************

// *** Check for standard admin username and password ***
/*
$sql="SELECT * FROM humo_users WHERE user_name='admin' OR (user_name='admin' AND user_password='".MD5('humogen')."')";
$check_login = $dbh->query($sql);
$check_loginDb=$check_login->fetch(PDO::FETCH_OBJ);
if ($check_loginDb)
    echo '<b><span style="color:red">'.__('Standard admin username or admin password is used.').'</span></b>';
*/

// *** Check for standard admin username and password ***
$check_admin_user = false;
$check_admin_pw = false;
$sql = "SELECT * FROM humo_users WHERE user_group_id='1'";
$check_login = $dbh->query($sql);
while ($check_loginDb = $check_login->fetch(PDO::FETCH_OBJ)) {
    if ($check_loginDb->user_name == 'admin') $check_admin_user = true;
    if ($check_loginDb->user_password == MD5('humogen')) $check_admin_pw = true; // *** Check old password method ***
    $check_password = password_verify('humogen', $check_loginDb->user_password_salted);
    if ($check_password) $check_admin_pw = true;
}
if ($check_admin_user and $check_admin_pw) {
    echo '<b><span style="color:red">' . __('Standard admin username and admin password is used.') . '</span></b>';
} elseif ($check_admin_user) {
    echo '<b><span style="color:red">' . __('Standard admin username is used.') . '</span></b>';
} elseif ($check_admin_pw) {
    echo '<b><span style="color:red">' . __('Standard admin password is used.') . '</span></b>';
}


//if (CMS_SPECIFIC == "Joomla") {
//    echo '<form method="POST" action="index.php?option=com_humo-gen&amp;task=admin&amp;page=users">' . "\n";
//} else {
//    echo '<form method="POST" action="index.php">' . "\n";
//}
?>
<form method="POST" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <br>
    <table class="humo standard" border="1" style="width:95%;">

        <tr class="table_header_large">
            <th><?= __('User'); ?></th>
            <th><?= __('E-mail address'); ?></th>
            <th><?= __('Change password'); ?></th>
            <th><?= __('User group'); ?></th>
            <th><?= __('Extra settings'); ?></th>
            <th><?= __('Statistics'); ?></th>
            <th><input type="Submit" name="change_user" value="<?= __('Change'); ?>"></th>
        </tr>

        <?php
        $usersql = "SELECT * FROM humo_users ORDER BY user_name";
        $user = $dbh->query($usersql);
        while ($userDb = $user->fetch(PDO::FETCH_OBJ)) {
        ?>
            <tr align="center">
                <td>
                    <?php
                    if ($userDb->user_name != 'gast' and $userDb->user_name != 'guest' and $userDb->user_id != '1') {
                        echo '<a href="index.php?page=users&remove_user=' . $userDb->user_id . '">';
                        echo '<img src="' . CMS_ROOTPATH_ADMIN . 'images/button_drop.png" border="0" alt="remove person"></a> ';
                    } else
                        echo '&nbsp;&nbsp;';

                    echo '<input type="hidden" name="' . $userDb->user_id . 'user_id" value="' . $userDb->user_id . '">';

                    // *** It's not allowed to change username "guest" (gast = backwards compatibility) ***
                    if ($userDb->user_name == 'gast' or $userDb->user_name == 'guest') {
                        echo '<input type="hidden" name="' . $userDb->user_id . 'username" value="' . $userDb->user_name . '">';
                        echo '<b>' . $userDb->user_name . '</b></td>';

                        echo '<input type="hidden" name="' . $userDb->user_id . 'usermail" value="">';
                        echo '<td><br></td>';
                        echo '<td><b>' . __('no need to log in') . '</b>';
                    } else {
                        echo '<input type="text" name="' . $userDb->user_id . 'username" value="' . $userDb->user_name . '" size="15"></td>';

                        echo '<td><input type="text" name="' . $userDb->user_id . 'usermail" value="' . $userDb->user_mail . '" size="20"></td>';

                        echo '<td><input type="password" name="' . $userDb->user_id . 'password" size="15">';
                    }
                    ?>
                </td>
                <?php

                //*** User groups ***
                if ($userDb->user_id == '1') { //1st user is always admin.
                    print '<td><input type="hidden" name="' . $userDb->user_id . 'group_id" value="1"><b>admin</b></td>';
                } else {
                    $groupsql = "SELECT * FROM humo_groups";
                    $groupresult = $dbh->query($groupsql);
                    echo '<td><select size="1" name="' . $userDb->user_id . 'group_id">';
                    while ($groupDb = $groupresult->fetch(PDO::FETCH_OBJ)) {
                        $select = '';
                        if ($userDb->user_group_id == $groupDb->group_id) $select = ' SELECTED';
                        echo '<option value="' . $groupDb->group_id . '"' . $select . '>' . $groupDb->group_name . '</option>';
                    }
                    echo '</select></td>';
                }

                //echo '<td>';
                $extra_icon = "../images/search.png";
                if ($userDb->user_hide_trees != '' or $userDb->user_edit_trees != '') {
                    $extra_icon = "../images/searchicon_red.png";
                }
                ?>
                <td>
                    <a href="#" onClick='window.open("index.php?page=editor_user_settings&user=<?= $userDb->user_id; ?>","","scrollbars=1,width=900,height=500,top=100,left=100")' ;><img src=<?= $extra_icon; ?> alt="<?= __('Search'); ?>"></a>
                </td>
                <?php

                // *** Show statistics ***
                $logbooksql = "SELECT COUNT(log_date) as nr_login FROM humo_user_log WHERE log_username='" . safe_text_db($userDb->user_name) . "'";
                $logbook = $dbh->query($logbooksql);
                $logbookDb = $logbook->fetch(PDO::FETCH_OBJ);

                $logdatesql = "SELECT log_date, log_ip_address FROM humo_user_log WHERE log_username='" . safe_text_db($userDb->user_name) . "' ORDER BY log_date DESC LIMIT 0,1";
                $logdate = $dbh->query($logdatesql);
                $logdateDb = $logdate->fetch(PDO::FETCH_OBJ);

                if ($logbookDb->nr_login) {
                    echo '<td>#' . $logbookDb->nr_login . ', ' . $logdateDb->log_date . '</td>';
                } else {
                    echo '<td><br></td>';
                }

                //echo '<td><br></td>';

                // *** Check if user is locked, using last IP address ***
                // FUNCTION CAN BE IMPROVED? MUST BE CHECKED USING USER AND IP ADDRESS?
                echo '<td>';
                $log_ip_address = 0;
                if (isset($logdateDb->log_ip_address)) $log_ip_address = $logdateDb->log_ip_address;
                if (!$db_functions->check_visitor($log_ip_address)) {
                    echo 'Access to website is blocked.<br>';
                    echo 'IP address: ' . $logdateDb->log_ip_address . '<br>';
                    echo '<a href="index.php?page=users&unblock_ip_address=' . $logdateDb->log_ip_address . '">' . __('Unblock IP address') . '</a>';
                } else echo '<br>';
                echo '</td>';
                ?>
            </tr>
        <?php
        }

        ?>
        <!-- Add user -->
        <tr align="center" bgcolor="green">
            <td><input type="text" name="add_username" size="15"></td>
            <td><input type="text" name="add_usermail" size="20"></td>
            <td><input type="password" name="add_password" size="15"></td>
            <?php
            // *** Select group for new user ***
            echo "<td><select size='1' name='add_group_id'>";
            $groupsql = "SELECT * FROM humo_groups";
            $groupresult = $dbh->query($groupsql);
            while ($groupDb = $groupresult->fetch(PDO::FETCH_OBJ)) {
                $select = '';
                if ($groupDb->group_id == '2') $select = ' SELECTED';
                echo '<option value="' . $groupDb->group_id . '"' . $select . '>' . $groupDb->group_name . '</option>';
            }
            echo '</select></td>';
            ?>
            <td></td>
            <td></td>
            <td><input type="Submit" name="add_user" value="<?= __('Add'); ?>"></td>
        </tr>
    </table>
</form>
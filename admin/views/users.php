<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}



// TODO create seperate controller script.
require_once  __DIR__ . "/../models/users.php";
$usersModel = new UsersModel($dbh);
//$usersModel->set_user_id();
$users['alert'] = $usersModel->update_user($dbh);
//$users['user_id'] = $usersModel->get_user_id();



?>
<h1 class="center"><?= __('Users');?></h1>

<!-- Remove user -->
<?php if (isset($_GET['remove_user'])) { ?>
    <div class="alert alert-danger">
        <strong><?= __('Are you sure you want to delete this user?'); ?></strong>
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="<?= $_GET['page']; ?>">
            <input type="hidden" name="remove_user" value="<?= $_GET['remove_user']; ?>">
            <input type="submit" name="remove_user2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php } ?>

<?php if ($users['alert']) { ?>
    <div class="alert alert-warning">
        <strong><?= $users['alert']; ?></strong>
    </div>
<?php }; ?>

<?php
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

$usersql = "SELECT * FROM humo_users ORDER BY user_name";
$user = $dbh->query($usersql);
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
            <th><input type="submit" name="change_user" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
        </tr>

        <?php while ($userDb = $user->fetch(PDO::FETCH_OBJ)) { ?>
            <tr align="center">
                <td>
                    <?php
                    if ($userDb->user_name != 'gast' and $userDb->user_name != 'guest' and $userDb->user_id != '1') {
                        echo '<a href="index.php?page=users&remove_user=' . $userDb->user_id . '">';
                        echo '<img src="images/button_drop.png" border="0" alt="remove person"></a> ';
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
                        if ($userDb->user_group_id == $groupDb->group_id) $select = ' selected';
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

        $groupsql = "SELECT * FROM humo_groups";
        $groupresult = $dbh->query($groupsql);
        ?>
        <!-- Add user -->
        <tr align="center" bgcolor="green">
            <td><input type="text" name="add_username" size="15"></td>
            <td><input type="text" name="add_usermail" size="20"></td>
            <td><input type="password" name="add_password" size="15"></td>
            <td>
                <!-- Select group for new user, default=family group. -->
                <select size='1' name='add_group_id'>
                    <?php
                    while ($groupDb = $groupresult->fetch(PDO::FETCH_OBJ)) {
                        $select = '';
                        if ($groupDb->group_id == '2') $select = ' selected';
                        echo '<option value="' . $groupDb->group_id . '"' . $select . '>' . $groupDb->group_name . '</option>';
                    }
                    ?>
                </select>
            </td>
            <td></td>
            <td></td>
            <td><input type="submit" name="add_user" value="<?= __('Add'); ?>" class="btn btn-sm btn-primary"></td>
        </tr>
    </table>
</form>
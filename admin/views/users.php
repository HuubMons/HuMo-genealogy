<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$usersql = "SELECT * FROM humo_users ORDER BY user_name";
$user = $dbh->query($usersql);
?>

<h1 class="center"><?= __('Users'); ?></h1>

<?php if (isset($_GET['remove_user'])) { ?>
    <div class="alert alert-danger">
        <strong><?= __('Are you sure you want to delete this user?'); ?></strong>
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="<?= $_GET['page']; ?>">
            <input type="hidden" name="remove_user" value="<?= $_GET['remove_user']; ?>">
            <input type="submit" name="remove_user2" value="<?= __('Yes'); ?>" class="btn btn-sm btn-danger">
            <input type="submit" name="submit" value="<?= __('No'); ?>" class="btn btn-sm btn-success ms-3">
        </form>
    </div>
<?php } ?>

<?php if ($edit_users['alert']) { ?>
    <div class="alert alert-warning">
        <strong><?= $edit_users['alert']; ?></strong>
    </div>
<?php } ?>

<?php if ($edit_users['check_admin_user'] && $edit_users['check_admin_pw']) { ?>
    <div class="alert alert-danger">
        <strong><?= __('Standard admin username and admin password is used.'); ?></strong>
    </div>
<?php } elseif ($edit_users['check_admin_user']) { ?>
    <div class="alert alert-danger">
        <strong><?= __('Standard admin username is used.'); ?></strong>
    </div>
<?php } elseif ($edit_users['check_admin_pw']) { ?>
    <div class="alert alert-danger">
        <strong><?= __('Standard admin password is used.'); ?></strong>
    </div>
<?php } ?>

<form method="POST" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <table class="table table-bordered">
        <thead class="table-primary">
            <tr>
                <th><img src="images/button_drop.png" border="0" alt="remove person"></th>
                <th><?= __('User'); ?></th>
                <th><?= __('E-mail address'); ?></th>
                <th><?= __('Change password'); ?></th>
                <th><?= __('User group'); ?></th>
                <th><?= __('Extra settings'); ?></th>
                <th><?= __('Statistics'); ?></th>
                <th><input type="submit" name="change_user" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></th>
            </tr>
        </thead>

        <?php while ($userDb = $user->fetch(PDO::FETCH_OBJ)) { ?>
            <tr>
                <td>
                    <input type="hidden" name="<?= $userDb->user_id; ?>user_id" value="<?= $userDb->user_id; ?>">

                    <?php if ($userDb->user_name != 'gast' && $userDb->user_name != 'guest' && $userDb->user_id != '1') { ?>
                        <a href="index.php?page=users&remove_user=<?= $userDb->user_id; ?>">
                            <img src="images/button_drop.png" border="0" alt="remove person">
                        </a>
                    <?php } ?>
                </td>

                <td>
                    <?php
                    // *** It's not allowed to change username "guest" (gast = backwards compatibility) ***
                    if ($userDb->user_name == 'gast' || $userDb->user_name == 'guest') {
                    ?>
                        <input type="hidden" name="<?= $userDb->user_id; ?>username" value="<?= $userDb->user_name; ?>">
                        <b><?= $userDb->user_name; ?></b>
                    <?php } else { ?>
                        <input type="text" name="<?= $userDb->user_id; ?>username" value="<?= $userDb->user_name; ?>" size="15" class="form-control form-control-sm">
                    <?php } ?>
                </td>

                <?php
                if ($userDb->user_name == 'gast' || $userDb->user_name == 'guest') { ?>
                    <td><input type="hidden" name="<?= $userDb->user_id; ?>usermail" value=""><br></td>
                    <td><b><?= __('no need to log in'); ?></b></td>
                <?php } else { ?>
                    <td><input type="text" name="<?= $userDb->user_id; ?>usermail" value="<?= $userDb->user_mail; ?>" size="20" class="form-control form-control-sm"></td>
                    <td><input type="password" name="<?= $userDb->user_id; ?>password" size="15" class="form-control form-control-sm"></td>
                <?php } ?>

                <!-- User groups. 1st user is always admin. -->
                <?php if ($userDb->user_id == '1') { ?>
                    <td><input type="hidden" name="<?= $userDb->user_id; ?>group_id" value="1"><b>admin</b></td>
                <?php
                } else {
                    $groupsql = "SELECT * FROM humo_groups";
                    $groupresult = $dbh->query($groupsql);
                ?>
                    <td>
                        <select size="1" name="<?= $userDb->user_id; ?>group_id" class="form-select form-select-sm">
                            <?php while ($groupDb = $groupresult->fetch(PDO::FETCH_OBJ)) { ?>
                                <option value="<?= $groupDb->group_id; ?>" <?= $userDb->user_group_id == $groupDb->group_id ? 'selected' : ''; ?>>
                                    <?= $groupDb->group_name; ?>
                                </option>
                            <?php } ?>
                        </select>
                    </td>
                <?php
                }

                $extra_icon = "../images/search.png";
                if ($userDb->user_hide_trees != '' || $userDb->user_edit_trees != '') {
                    $extra_icon = "../images/searchicon_red.png";
                }
                ?>
                <td>
                    <a href="#" onClick='window.open("index.php?page=editor_user_settings&user=<?= $userDb->user_id; ?>","","scrollbars=1,width=900,height=500,top=100,left=100")' ;>
                        <img src=<?= $extra_icon; ?> alt="<?= __('Search'); ?>">
                    </a>
                </td>

                <?php
                // *** Show statistics ***
                $logbooksql = "SELECT COUNT(log_date) as nr_login FROM humo_user_log WHERE log_username = :username";
                $logbook = $dbh->prepare($logbooksql);
                $logbook->execute([':username' => $userDb->user_name]);
                $logbookDb = $logbook->fetch(PDO::FETCH_OBJ);

                $logdatesql = "SELECT log_date, log_ip_address FROM humo_user_log WHERE log_username = :username ORDER BY log_date DESC LIMIT 0,1";
                $logdate = $dbh->prepare($logdatesql);
                $logdate->execute([':username' => $userDb->user_name]);
                $logdateDb = $logdate->fetch(PDO::FETCH_OBJ);

                if ($logbookDb->nr_login) {
                    echo '<td>#' . $logbookDb->nr_login . ', ' . $logdateDb->log_date . '</td>';
                } else {
                    echo '<td><br></td>';
                }

                // *** Check if user is locked, using last IP address ***
                // FUNCTION CAN BE IMPROVED? MUST BE CHECKED USING USER AND IP ADDRESS?
                $log_ip_address = 0;
                if (isset($logdateDb->log_ip_address)) {
                    $log_ip_address = $logdateDb->log_ip_address;
                }
                ?>
                <td>
                    <?php
                    if (!$db_functions->check_visitor($log_ip_address)) {
                        echo 'Access to website is blocked.<br>';
                        echo 'IP address: ' . $logdateDb->log_ip_address . '<br>';
                        echo '<a href="index.php?page=users&unblock_ip_address=' . $logdateDb->log_ip_address . '">' . __('Unblock IP address') . '</a>';
                    } else {
                        echo '<br>';
                    }
                    ?>
                </td>
            </tr>
        <?php
        }

        $groupsql = "SELECT * FROM humo_groups";
        $groupresult = $dbh->query($groupsql);
        ?>
        <!-- Add user -->
        <tr class="table-secondary">
            <td></td>
            <td><input type="text" name="add_username" size="15" class="form-control form-control-sm"></td>
            <td><input type="text" name="add_usermail" size="20" class="form-control form-control-sm"></td>
            <td><input type="password" name="add_password" size="15" class="form-control form-control-sm"></td>
            <td>
                <!-- Select group for new user, default=family group. -->
                <select size="1" name="add_group_id" class="form-select form-select-sm">
                    <?php while ($groupDb = $groupresult->fetch(PDO::FETCH_OBJ)) { ?>
                        <option value="<?= $groupDb->group_id; ?>" <?= $groupDb->group_id == '2' ? 'selected' : ''; ?>><?= $groupDb->group_name; ?></option>
                    <?php } ?>
                </select>
            </td>
            <td></td>
            <td></td>
            <td><input type="submit" name="add_user" value="<?= __('Add'); ?>" class="btn btn-sm btn-primary"></td>
        </tr>
    </table>
</form>
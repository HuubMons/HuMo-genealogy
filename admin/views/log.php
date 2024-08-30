<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<h1 class="center"><?= __('Log'); ?></h1>

<ul class="nav nav-tabs">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $log['menu_tab'] == 'log_users' ? 'active' : ''; ?>" href="index.php?page=<?= $page; ?>"><?= __('Logfile users'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $log['menu_tab'] == 'log_blacklist' ? 'active' : ''; ?>" href="index.php?page=<?= $page; ?>&amp;menu_admin=log_blacklist"><?= __('IP Blacklist'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float:left; background-color:white; height:500px; padding:10px;">
    <?php
    // *** User log ***
    if ($log['menu_tab'] == 'log_users') {
        $logbooksql = "SELECT * FROM humo_user_log ORDER BY log_date DESC";
        $logbook = $dbh->query($logbooksql);
    ?>
        <!-- <div class="table-responsive"> -->
            <table class="table">
                <thead class="table-primary">
                    <tr>
                        <th><?= __('Date - time'); ?></th>
                        <th><?= __('User'); ?></th>
                        <th><?= __('User/ Admin'); ?></th>
                        <th><?= __('IP address'); ?></th>
                        <th><?= __('Status'); ?></th>
                    </tr>
                </thead>
                <?php while ($logbookDb = $logbook->fetch(PDO::FETCH_OBJ)) { ?>
                    <tr>
                        <td class="text-nowrap"><?= $logbookDb->log_date; ?></td>
                        <td><?= $logbookDb->log_username; ?></td>
                        <td><?= $logbookDb->log_user_admin; ?></td>
                        <td><?= $logbookDb->log_ip_address; ?></td>
                        <td><?= $logbookDb->log_status; ?></td>
                    </tr>
                <?php } ?>
            </table>
        <!-- </div> -->
    <?php
    }

    // *** IP blacklist ***
    if ($log['menu_tab'] == 'log_blacklist') {
        printf(__('IP Blacklist: access to %s will be totally blocked for these IP addresses.'), 'HuMo-genealogy');
    ?>
        <form method='post' action='index.php?page=log&amp;menu_admin=log_blacklist'>
            <input type="hidden" name="page" value="<?= $page; ?>">
            <table class="humo" border="1">
                <tr class="table_header">
                    <th>Nr.</th>
                    <th><?= __('IP address'); ?></th>
                    <th><?= __('Description'); ?></th>
                    <th><?= __('Change / Add'); ?></th>
                    <th><?= __('Remove'); ?></th>
                </tr>
                <?php
                $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist' ORDER BY setting_order");
                // *** Number for new link ***
                $count_links = 0;
                if ($datasql->rowCount()) {
                    $count_links = $datasql->rowCount();
                }
                $new_number = 1;
                if ($count_links) {
                    $new_number = $count_links + 1;
                }
                if ($datasql) {
                    $teller = 1;
                    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                        $lijst = explode("|", $dataDb->setting_value);
                ?>
                        <tr>
                            <td>
                                <input type="hidden" name="<?= $dataDb->setting_id; ?>id" value="<?= $dataDb->setting_id; ?>"><?= $teller; ?>
                                <?php
                                if ($dataDb->setting_order != '1') {
                                    echo ' <a href="index.php?page=log&amp;menu_admin=log_blacklist&amp;up=1&amp;link_order=' . $dataDb->setting_order .
                                        '&amp;id=' . $dataDb->setting_id . '"><img src="images/arrow_up.gif" border="0" alt="up"></a>';
                                }
                                if ($dataDb->setting_order != $count_links) {
                                    echo ' <a href="index.php?page=log&amp;menu_admin=log_blacklist&amp;down=1&amp;link_order=' . $dataDb->setting_order . '&amp;id=' .
                                        $dataDb->setting_id . '"><img src="images/arrow_down.gif" border="0" alt="down"></a>';
                                }
                                ?>
                            </td>
                            <td><input type="text" name="<?= $dataDb->setting_id; ?>own_code" value="<?= $lijst[0]; ?>" size="5"></td>
                            <td><input type="text" name="<?= $dataDb->setting_id; ?>link_text" value="<?= $lijst[1]; ?>" size="20"></td>
                            <td><input type="submit" name="change_link" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></td>
                            <td><input type="submit" name="<?= $dataDb->setting_id; ?>remove_link" value="<?= __('Remove'); ?>" class="btn btn-sm btn-warning"></td>
                        </tr>
                    <?php
                        $teller++;
                    }

                    // *** Add new link ***
                    ?>
                    <tr>
                        <td><br></td>
                        <input type="hidden" name="link_order" value="<?= $new_number; ?>">
                        <td><input type="text" name="own_code" placeholder="<?= __('IP Address'); ?>" size="5"></td>
                        <td><input type="text" name="link_text" placeholder="<?= __('Description'); ?>" size="20"></td>
                        <td><input type="submit" name="add_link" value="<?= __('Add'); ?>" class="btn btn-sm btn-success"></td>
                        <td><br></td>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td colspan="4"><?= __('Database is not yet available.'); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </form>
    <?php } ?>
</div>
<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// TODO move items to model script.
if ($log['menu_tab'] == 'log_users') {
    $logbooksql = "SELECT * FROM humo_user_log ORDER BY log_date DESC";
    $logbook = $dbh->query($logbooksql);
}

if ($log['menu_tab'] == 'log_blacklist') {
    $blacklistQry = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='ip_blacklist' ORDER BY setting_order");
    // *** Number for new link ***
    $count_links = 0;
    if ($blacklistQry->rowCount()) {
        $count_links = $blacklistQry->rowCount();
    }
    $new_number = 1;
    if ($count_links) {
        $new_number = $count_links + 1;
    }
    $blacklistCounter = 0;
}
?>

<h1 class="center"><?= __('Log'); ?></h1>

<ul class="nav nav-tabs">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $log['menu_tab'] == 'log_users' ? 'active' : ''; ?>" href="index.php?page=<?= rawurlencode((string) $page); ?>"><?= __('Logfile users'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?= $log['menu_tab'] == 'log_blacklist' ? 'active' : ''; ?>" href="index.php?page=<?= rawurlencode((string) $page); ?>&amp;menu_admin=log_blacklist"><?= __('IP Blacklist'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float:left; background-color:white; height:500px; padding:10px;">
    <?php if ($log['menu_tab'] == 'log_users') { ?>
        <table class="table table-bordered">
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
                    <td class="text-nowrap"><?= $HtmlEscaper->escHtml((string) $logbookDb->log_date); ?></td>
                    <td><?= $HtmlEscaper->escHtml((string) $logbookDb->log_username); ?></td>
                    <td><?= $HtmlEscaper->escHtml((string) $logbookDb->log_user_admin); ?></td>
                    <td><?= $HtmlEscaper->escHtml((string) $logbookDb->log_ip_address); ?></td>
                    <td><?= $HtmlEscaper->escHtml((string) $logbookDb->log_status); ?></td>
                </tr>
            <?php } ?>
        </table>
    <?php
    }

    // *** IP blacklist ***
    if ($log['menu_tab'] == 'log_blacklist') {
        printf(__('IP Blacklist: access to %s will be totally blocked for these IP addresses.'), 'HuMo-genealogy');
    ?>
        <form method="post" action="index.php?page=log&amp;menu_admin=log_blacklist" class="mt-2">
            <table class="table table-bordered">
                <tr class="table-primary">
                    <th>Nr.</th>
                    <th><?= __('IP address'); ?></th>
                    <th><?= __('Description'); ?></th>
                    <th><?= __('Change / Add'); ?></th>
                    <th><?= __('Remove'); ?></th>
                </tr>
                <?php
                if ($blacklistQry) {
                    while ($blacklist = $blacklistQry->fetch(PDO::FETCH_OBJ)) {
                        $blacklistCounter++;
                        $blacklistItem = explode("|", $blacklist->setting_value);
                ?>
                        <tr>
                            <td>
                                <input type="hidden" name="<?= (int) $blacklist->setting_id; ?>id" value="<?= (int) $blacklist->setting_id; ?>"><?= (int) $blacklistCounter; ?>
                                <?php if ($blacklist->setting_order != '1') { ?>
                                    <a href="index.php?page=log&amp;menu_admin=log_blacklist&amp;up=1&amp;link_order=<?= (int) $blacklist->setting_order; ?>&amp;id=<?= (int) $blacklist->setting_id; ?>"><img src="images/arrow_up.gif" border="0" alt="up"></a>
                                <?php
                                }
                                if ($blacklist->setting_order != $count_links) {
                                ?>
                                    <a href="index.php?page=log&amp;menu_admin=log_blacklist&amp;down=1&amp;link_order=<?= (int) $blacklist->setting_order; ?>&amp;id=<?= (int) $blacklist->setting_id; ?>"><img src="images/arrow_down.gif" border="0" alt="down"></a>
                                <?php } ?>
                            </td>
                            <td><input type="text" name="<?= (int) $blacklist->setting_id; ?>own_code" value="<?= $HtmlEscaper->escHtml((string) ($blacklistItem[0] ?? '')); ?>" size="5" class="form-control form-control-sm"></td>
                            <td><input type="text" name="<?= (int) $blacklist->setting_id; ?>link_text" value="<?= $HtmlEscaper->escHtml((string) ($blacklistItem[1] ?? '')); ?>" size="20" class="form-control form-control-sm"></td>
                            <td><input type="submit" name="change_link" value="<?= __('Change'); ?>" class="btn btn-sm btn-success"></td>
                            <td><input type="submit" name="<?= (int) $blacklist->setting_id; ?>remove_link" value="<?= __('Remove'); ?>" class="btn btn-sm btn-warning"></td>
                        </tr>
                    <?php } ?>

                    <tr>
                        <td><br></td>
                        <input type="hidden" name="link_order" value="<?= (int) $new_number; ?>">
                        <td><input type="text" name="own_code" placeholder="<?= __('IP Address'); ?>" size="45" class="form-control form-control-sm"></td>
                        <td><input type="text" name="link_text" placeholder="<?= __('Description'); ?>" size="30" class="form-control form-control-sm"></td>
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
<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<h1 class="center"><?= __('Install'); ?></h1>

<?= __('Installation of the standard tables. Or create tables from a scratch, they will be filled in with standard data.'); ?><br><br>

<?php
if (!isset($_POST['install_tables2'])) {
    $check_settings = '';
    if (isset($_POST["table_settings"]) || !$install['table_settings']) {
        $check_settings = ' checked';
    }

    $check_trees = '';
    if (isset($_POST["table_trees"]) || !$install['table_trees']) {
        $check_trees = ' checked';
    }

    $check_stat = '';
    if (isset($_POST["table_stat_date"]) || !$install['table_stat_date']) {
        $check_stat = ' checked';
    }

    $check_users = '';
    if (isset($_POST["table_users"]) || !$install['table_users']) {
        $check_users = ' checked';
    }

    $check_groups = '';
    if (isset($_POST["table_groups"]) || !$install['table_groups']) {
        $check_groups = ' checked';
    }

    $check_cms_menu = '';
    if (isset($_POST["table_cms_menu"]) || !$install['table_cms_menu']) {
        $check_cms_menu = ' checked';
    }

    $check_cms_pages = '';
    if (isset($_POST["table_cms_pages"]) || !$install['table_cms_pages']) {
        $check_cms_pages = ' checked';
    }

    $check_user_notes = '';
    if (isset($_POST["table_user_notes"]) || !$install['table_user_notes']) {
        $check_user_notes = ' checked';
    }

    $check_log = '';
    if (isset($_POST["table_user_log"]) || !$install['table_user_log']) {
        $check_log = ' checked';
    }

    $check_stat_country = '';
    if (isset($_POST["table_stat_country"]) || !$install['table_stat_country']) {
        $check_stat_country = ' checked';
    }

    $check_location = '';
    if (isset($_POST["table_location"]) || !$install['table_location']) {
        $check_location = ' checked';
    }

    $username_admin = 'admin';
    if (isset($_POST["username_admin"])) {
        $username_admin = $_POST["username_admin"];
    }
    $password_admin = 'humogen';
    if (isset($_POST["password_admin"])) {
        $password_admin = $_POST["password_admin"];
    }
    $username_family = 'family';
    if (isset($_POST["username_family"])) {
        $username_family = $_POST["username_family"];
    }
    $password_family = 'admin';
    if (isset($_POST["password_family"])) {
        $password_family = $_POST["password_family"];
    }
?>
    <form method="post" action="index.php?page=install" style="display : inline;">
        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_settings" <?= $check_settings; ?> <?= !$install['table_settings'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('(Re)create settings table.'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_stat_date" <?= $check_stat; ?> <?= !$install['table_stat_date'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('(Re) create statistics tree table. <b>EXISTING STATISTICS TREES WILL BE REMOVED!</b>'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_users" <?= $check_users; ?> <?= !$install['table_users'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('(Re) create user table. <b>The user table will be filled with new users. Please add passwords:</b>'); ?></label>
        </div>

        <div class="me-4">
            <table class="table ms-4">
                <thead class="table-primary">
                    <tr class="table_header">
                        <th><?= __('User'); ?></th>
                        <th><?= __('Username'); ?></th>
                        <th><?= __('Password'); ?></th>
                    </tr>
                </thead>

                <tr>
                    <td><?= __('Administrator'); ?></td>
                    <td><input type="text" name="username_admin" value="<?= $username_admin; ?>" size="15"></td>
                    <td><input type="password" name="password_admin" value="<?= $password_admin; ?>" size="15"> <?= __('THIS WILL BE YOUR ADMIN PASSWORD! (default password = humogen)'); ?></td>
                </tr>

                <tr>
                    <td><?= __('Family or genealogists'); ?></td>
                    <td><input type="text" name="username_family" value="<?= $username_family; ?>" size="15"></td>
                    <td><input type="password" name="password_family" value="<?= $password_family; ?>" size="15"> <?= __('Password for user: "family" (default password = humogen)'); ?></td>
                </tr>

                <tr>
                    <td colspan="3"><?= __('Remark: more users can be added after installation.'); ?></td>
                </tr>
            </table>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_groups" <?= $check_groups; ?> <?= !$install['table_groups'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('(Re) create user group table.'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_cms_menu" <?= $check_cms_menu; ?> <?= !$install['table_cms_menu'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('(Re) create CMS menu table, used for menu system of own pages.'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_cms_pages" <?= $check_cms_pages; ?> <?= !$install['table_cms_pages'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('(Re) create CMS pages table, used for own pages.'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_user_notes" <?= $check_user_notes; ?> <?= !$install['table_user_notes'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('(Re) create user notes table.'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_user_log" <?= $check_log; ?> <?= !$install['table_user_log'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('Empty log table.'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_stat_country" <?= $check_stat_country; ?> <?= !$install['table_stat_country'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('Empty statistics country table.'); ?></label>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_location" <?= $check_location; ?> <?= !$install['table_location'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('(Re) create location table. <b>This will remove all locations used for World map.</b>'); ?></label>
        </div><br>

        <p><b><?= __('Family tree tables'); ?></b></p>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" name="table_trees" <?= $check_trees; ?> <?= !$install['table_trees'] ? 'disabled' : ''; ?>>
            <label class="form-check-label"><?= __('(Re) create all family tree tables. <b>*** ALL EXISTING FAMILY TREES WILL BE REMOVED! ***</b>'); ?></label>
        </div>

        <?php if (isset($_POST['install_tables'])) { ?>
            <p><?= __('Install'); ?>
                <input type="submit" name="install_tables2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
                <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
            <?php } else { ?>
            <p><input type="submit" name="install_tables" class="btn btn-sm btn-success" value="<?= __('Install'); ?>">
            <?php } ?>
            <b><?= __('Are you sure? Old settings will be deleted!'); ?></b><br>
    </form>
<?php
}

// *** Create tables ***
if (isset($_POST['install_tables2'])) {
?>
    <br><b><?= __('Creating tables:'); ?></b><br>

    <?php
    if (!$install['table_settings'] || isset($_POST["table_settings"])) {
        try {
            $dbh->query("DROP TABLE humo_settings"); // Remove table.
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_settings');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_settings (
            setting_id int(10) unsigned NOT NULL auto_increment,
            setting_variable varchar(50) CHARACTER SET utf8,
            setting_value text CHARACTER SET utf8,
            setting_order smallint(5),
            setting_tree_id smallint(5),
            PRIMARY KEY (`setting_id`)
            ) DEFAULT CHARSET=utf8");

        printf(__('filling table: %s.'), 'humo_settings');
        echo '<br>';

        $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('database_name','Web Site')");
        $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('homepage','https://humo-gen.com')");
        $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('homepage_description','Website')");
        $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('searchengine','n')");
        $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('robots_option','<META NAME=\"robots\" CONTENT=\"noindex,nofollow\">')");

        // *** Other settings are saved in the table in file: settingsGlobal.php ***

        // *** Update status number. Number must be: update_status+1! ***
        $dbh->query("INSERT INTO humo_settings (setting_variable,setting_value) values ('update_status','19')");
    }

    if (!$install['table_stat_date'] || isset($_POST["table_stat_date"])) {
        try {
            $dbh->query("DROP TABLE humo_stat_date");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_stat_date');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_stat_date (
            stat_id int(10) NOT NULL auto_increment,
            stat_easy_id varchar(100) CHARACTER SET utf8,
            stat_ip_address varchar(40) CHARACTER SET utf8,
            stat_user_agent varchar(255) CHARACTER SET utf8,
            stat_tree_id varchar(5) CHARACTER SET utf8,
            stat_gedcom_fam varchar(25) CHARACTER SET utf8,
            stat_gedcom_man varchar(25) CHARACTER SET utf8,
            stat_gedcom_woman varchar(25) CHARACTER SET utf8,
            stat_date_stat datetime,
            stat_date_linux varchar(50) CHARACTER SET utf8,
            PRIMARY KEY (`stat_id`)
        ) DEFAULT CHARSET=utf8");
    }

    if (!$install['table_groups'] || isset($_POST["table_groups"])) {
        try {
            $dbh->query("DROP TABLE humo_groups");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_groups');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_groups (
            group_id smallint(5) unsigned NOT NULL auto_increment,
            group_name varchar(25) CHARACTER SET utf8,
            group_privacy varchar(1) CHARACTER SET utf8,
            group_menu_places varchar(1) CHARACTER SET utf8,
            group_admin varchar(1) CHARACTER SET utf8,
            group_statistics varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j',
            group_menu_persons VARCHAR(1) NOT NULL DEFAULT 'j',
            group_menu_names VARCHAR(1) NOT NULL DEFAULT 'j',
            group_menu_login VARCHAR(1) NOT NULL DEFAULT 'j',
            group_birthday_rss varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j',
            group_birthday_list varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'j',
            group_latestchanges VARCHAR(1) NOT NULL DEFAULT 'j',
            group_contact VARCHAR(1) NOT NULL DEFAULT 'j',
            group_googlemaps VARCHAR(1) NOT NULL DEFAULT 'j',
            group_relcalc VARCHAR(1) NOT NULL DEFAULT 'j',
            group_showstatistics VARCHAR(1) NOT NULL DEFAULT 'j',
            group_sources varchar(1) CHARACTER SET utf8,
            group_show_restricted_source varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'y',
            group_source_presentation varchar(20) CHARACTER SET utf8,
            group_text_presentation VARCHAR(20) NOT NULL DEFAULT 'show',
            group_pictures varchar(1) CHARACTER SET utf8,
            group_photobook varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
            group_gedcomnr varchar(1) CHARACTER SET utf8,
            group_living_place varchar(1) CHARACTER SET utf8,
            group_places varchar(1) CHARACTER SET utf8,
            group_religion varchar(1) CHARACTER SET utf8,
            group_place_date varchar(1) CHARACTER SET utf8,
            group_kindindex varchar(1) CHARACTER SET utf8,
            group_event varchar(1) CHARACTER SET utf8,
            group_addresses varchar(1) CHARACTER SET utf8,
            group_own_code varchar(1) CHARACTER SET utf8,
            group_user_notes varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
            group_user_notes_notes varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
            group_user_notes_show VARCHAR(1) NOT NULL DEFAULT 'n',
            group_family_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'compact',
            group_maps_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'hide',
            group_show_age_living_person varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'y',
            group_pdf_button varchar(1) CHARACTER SET utf8,
            group_rtf_button varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
            group_work_text varchar(1) CHARACTER SET utf8,
            group_texts varchar(1) CHARACTER SET utf8,
            group_text_pers varchar(1) CHARACTER SET utf8,
            group_texts_pers varchar(1) CHARACTER SET utf8,
            group_texts_fam varchar(1) CHARACTER SET utf8,
            group_alive varchar(1) CHARACTER SET utf8,
            group_alive_date_act varchar(1) CHARACTER SET utf8,
            group_alive_date varchar(4) CHARACTER SET utf8,
            group_death_date_act varchar(1) CHARACTER SET utf8,
            group_death_date varchar(4) CHARACTER SET utf8,
            group_filter_date varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
            group_filter_death varchar(1) CHARACTER SET utf8,
            group_filter_total varchar(1) CHARACTER SET utf8,
            group_filter_name varchar(1) CHARACTER SET utf8,
            group_filter_fam varchar(1) CHARACTER SET utf8,
            group_filter_pers_show_act varchar(1) CHARACTER SET utf8,
            group_filter_pers_show varchar(50) CHARACTER SET utf8,
            group_filter_pers_hide_act varchar(1) CHARACTER SET utf8,
            group_filter_pers_hide varchar(50) CHARACTER SET utf8,
            group_pers_hide_totally_act VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
            group_pers_hide_totally VARCHAR(50) CHARACTER SET utf8 NOT NULL DEFAULT 'X',
            group_gen_protection VARCHAR(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',
            group_hide_trees VARCHAR(200) NOT NULL DEFAULT '',
            group_edit_trees VARCHAR(200) NOT NULL DEFAULT '',
            group_hide_photocat VARCHAR(200) NOT NULL DEFAULT '',
            PRIMARY KEY (`group_id`)
        ) DEFAULT CHARSET=utf8");
        //group_editor varchar(1) CHARACTER SET utf8 NOT NULL DEFAULT 'n',

        printf(__('filling table: %s.'), 'humo_groups');
        echo '<br>';

        $sql = "INSERT INTO humo_groups SET group_name='admin', group_privacy='j', group_menu_places='j', group_admin='j',
        group_sources='j', group_pictures='j', group_gedcomnr='j', group_living_place='j', group_places='j', group_religion='j',
        group_place_date='n', group_kindindex='n', group_event='j', group_addresses='j', group_own_code='j', group_pdf_button='y', group_rtf_button='y', group_work_text='j',
        group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='n',
        group_alive_date='1920', group_death_date_act='n',
        group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
        group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
        $dbh->query($sql);

        $sql = "INSERT INTO humo_groups SET group_name='family', group_privacy='n', group_menu_places='n', group_admin='n',
        group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='j', group_places='j', group_religion='n',
        group_place_date='n', group_kindindex='n', group_event='j', group_addresses='j', group_own_code='j', group_pdf_button='y', group_rtf_button='n', group_work_text='j',
        group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='n',
        group_alive_date='1920', group_death_date_act='n',
        group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
        group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
        $dbh->query($sql);

        $sql = "INSERT INTO humo_groups SET group_name='guest', group_privacy='n', group_menu_places='n', group_admin='n',
        group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
        group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n',
        group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
        group_alive_date='1920', group_death_date_act='n',
        group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
        group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
        $dbh->query($sql);

        $sql = "INSERT INTO humo_groups SET group_name='group 4', group_privacy='n', group_menu_places='n', group_admin='n',
        group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
        group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n',
        group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
        group_alive_date='1920', group_death_date_act='n',
        group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
        group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
        $dbh->query($sql);

        $sql = "INSERT INTO humo_groups SET group_name='group 5', group_privacy='j', group_menu_places='n', group_admin='n',
        group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
        group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n',
        group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
        group_alive_date='1920', group_death_date_act='n',
        group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
        group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
        $dbh->query($sql);

        $sql = "INSERT INTO humo_groups SET group_name='group 6', group_privacy='n', group_menu_places='n', group_admin='n',
        group_sources='n', group_pictures='n', group_gedcomnr='n', group_living_place='n', group_places='j', group_religion='n',
        group_place_date='n', group_kindindex='n', group_event='n', group_addresses='n', group_own_code='n', group_pdf_button='y', group_rtf_button='n', group_work_text='n',
        group_texts='j', group_text_pers='j', group_texts_pers='j', group_texts_fam='j', group_alive='n', group_alive_date_act='j',
        group_alive_date='1920', group_death_date_act='n',
        group_death_date='1980', group_filter_death='n', group_filter_total='n', group_filter_name='j', group_filter_fam='j', group_filter_pers_show_act='j',
        group_filter_pers_show='*', group_filter_pers_hide_act='n', group_filter_pers_hide='#'";
        $dbh->query($sql);
    }

    if (!$install['table_users'] || isset($_POST["table_users"])) {
        try {
            $dbh->query("DROP TABLE humo_users");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_users');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_users (
            user_id smallint(5) unsigned NOT NULL auto_increment,
            user_name varchar(25) CHARACTER SET utf8,
            user_mail varchar(100) CHARACTER SET utf8,
            user_trees text CHARACTER SET utf8,
            user_remark text CHARACTER SET utf8,
            user_password varchar(50) CHARACTER SET utf8,
            user_password_salted VARCHAR(255) CHARACTER SET utf8,
            user_2fa_enabled varchar(1) CHARACTER SET utf8 DEFAULT '',
            user_2fa_auth_secret varchar(50) CHARACTER SET utf8 DEFAULT '',
            user_status varchar(1) CHARACTER SET utf8,
            user_group_id smallint(5),
            user_hide_trees VARCHAR(200) NOT NULL DEFAULT '',
            user_edit_trees VARCHAR(200) NOT NULL DEFAULT '',
            user_ip_address varchar(45) CHARACTER SET utf8 DEFAULT '',
            user_register_date varchar(20) CHARACTER SET utf8,
            user_last_visit varchar(25) CHARACTER SET utf8,
            PRIMARY KEY  (`user_id`)
            ) DEFAULT CHARSET=utf8");

        printf(__('filling table: %s.'), 'humo_users');
        echo '<br>';

        $hashToStoreInDb = password_hash($_POST['password_admin'], PASSWORD_DEFAULT);
        $dbh->query("INSERT INTO humo_users (user_name, user_password_salted, user_group_id) values ('" . $_POST['username_admin'] . "','" . $hashToStoreInDb . "','1')");

        $hashToStoreInDb = password_hash($_POST['password_family'], PASSWORD_DEFAULT);
        $dbh->query("INSERT INTO humo_users (user_name, user_password_salted, user_group_id) values ('" . $_POST['username_family'] . "','" . $hashToStoreInDb . "','2')");

        $hashToStoreInDb = password_hash('guest', PASSWORD_DEFAULT);
        $dbh->query("INSERT INTO humo_users (user_name, user_password_salted, user_group_id) values ('guest','" . $hashToStoreInDb . "','3')");
    }

    if (!$install['table_cms_menu'] || isset($_POST["table_cms_menu"])) {
        try {
            $dbh->query("DROP TABLE humo_cms_menu");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_cms_menu');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_cms_menu (
            menu_id int(10) NOT NULL AUTO_INCREMENT,
            menu_parent_id int(10) NOT NULL DEFAULT '0',
            menu_order int(5) NOT NULL DEFAULT '0',
            menu_name varchar(25) CHARACTER SET utf8 DEFAULT '',
            PRIMARY KEY (`menu_id`)
            ) DEFAULT CHARSET=utf8");
    }

    if (!$install['table_cms_pages'] || isset($_POST["table_cms_pages"])) {
        try {
            $dbh->query("DROP TABLE humo_cms_pages");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_cms_pages');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_cms_pages (
            page_id int(10) NOT NULL AUTO_INCREMENT,
            page_status varchar(1) CHARACTER SET utf8 DEFAULT '',
            page_menu_id int(10) NOT NULL DEFAULT '0',
            page_order int(10) NOT NULL DEFAULT '0',
            page_counter int(10) NOT NULL DEFAULT '0',
            page_date datetime,
            page_edit_date datetime,
            page_title varchar(50) CHARACTER SET utf8 DEFAULT '',
            page_text longtext CHARACTER SET utf8,
            PRIMARY KEY (`page_id`)
            ) DEFAULT CHARSET=utf8");
    }

    if (!$install['table_user_log'] || isset($_POST["table_user_log"])) {
        try {
            $dbh->query("DROP TABLE humo_user_log");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_log');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_user_log (
            log_id mediumint(6) unsigned NOT NULL auto_increment,
            log_username varchar(25) CHARACTER SET utf8,
            log_date varchar(20) CHARACTER SET utf8,
            log_ip_address varchar(45) CHARACTER SET utf8 DEFAULT '',
            log_user_admin varchar(5) CHARACTER SET utf8 DEFAULT '',
            log_status varchar(10) CHARACTER SET utf8 DEFAULT '',
            PRIMARY KEY (`log_id`)
            ) DEFAULT CHARSET=utf8");
    }

    if (!$install['table_user_notes'] || isset($_POST["table_user_notes"])) {
        try {
            $dbh->query("DROP TABLE humo_user_notes");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_user_notes');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_user_notes (
            note_id smallint(5) unsigned NOT NULL auto_increment,
            note_order smallint(5),
            note_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            note_new_user_id smallint NULL DEFAULT NULL,
            note_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL,
            note_changed_user_id smallint NULL DEFAULT NULL,
            note_guest_name varchar(25) CHARACTER SET utf8 DEFAULT NULL,
            note_guest_mail varchar(25) CHARACTER SET utf8 DEFAULT NULL,
            note_note text CHARACTER SET utf8,
            note_status varchar(15) CHARACTER SET utf8,
            note_priority varchar(15) CHARACTER SET utf8,
            note_tree_id mediumint(7),
            note_kind varchar(10) CHARACTER SET utf8,
            note_connect_kind varchar(20) CHARACTER SET utf8,
            note_connect_id varchar(25) CHARACTER SET utf8,
            note_names text CHARACTER SET utf8,
            PRIMARY KEY  (`note_id`)
            ) DEFAULT CHARSET=utf8");
    }

    if (!$install['table_location'] || isset($_POST["table_location"])) {
        try {
            $dbh->query("DROP TABLE humo_location");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_location');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_location (
                location_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                location_location VARCHAR(120) CHARACTER SET utf8,
                location_lat FLOAT(10,6),
                location_lng FLOAT(10,6),
                location_status TEXT
            ) DEFAULT CHARSET=utf8");
    }

    if (!$install['table_stat_country'] || isset($_POST["table_stat_country"])) {
        try {
            $dbh->query("DROP TABLE humo_stat_country");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_stat_country');
        echo '<br>';

        $qry = "CREATE TABLE humo_stat_country (
            stat_country_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
            stat_country_ip_address varchar(40) DEFAULT NULL,
            stat_country_code VARCHAR(10) CHARACTER SET utf8
        )";
        $dbh->query($qry);
    }

    // *** Family tree tables ***
    if (!$install['table_trees'] || isset($_POST["table_trees"])) {
        try {
            $dbh->query("DROP TABLE humo_trees");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_trees');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_trees (
            tree_id smallint(5) unsigned NOT NULL auto_increment,
            tree_order smallint(5),
            tree_prefix varchar(10) CHARACTER SET utf8,
            tree_date varchar(20) CHARACTER SET utf8,
            tree_persons varchar(10) CHARACTER SET utf8,
            tree_families varchar(10) CHARACTER SET utf8,
            tree_email varchar(100) CHARACTER SET utf8,
            tree_owner varchar(100) CHARACTER SET utf8,
            tree_pict_path varchar (100) CHARACTER SET utf8,
            tree_privacy varchar (100) CHARACTER SET utf8,
            tree_gedcom varchar (100),
            tree_gedcom_program varchar (100),
            PRIMARY KEY  (`tree_id`)
            ) DEFAULT CHARSET=utf8");

        $gedcom_date = date("Y-m-d H:i");
        $sql = "INSERT INTO humo_trees SET
            tree_order='1',
            tree_prefix='humo_',
            tree_date='" . $gedcom_date . "',
            tree_persons='0',
            tree_families='0',
            tree_email='',
            tree_owner='',
            tree_pict_path='|../pictures/',
            tree_privacy=''";
        $dbh->query($sql);

        try {
            $dbh->query("DROP TABLE humo_tree_texts");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_tree_texts');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_tree_texts (
            treetext_id smallint(5) unsigned NOT NULL auto_increment,
            treetext_tree_id smallint(5),
            treetext_language varchar(100) CHARACTER SET utf8,
            treetext_name varchar(100) CHARACTER SET utf8,
            treetext_mainmenu_text text CHARACTER SET utf8,
            treetext_mainmenu_source text CHARACTER SET utf8,
            treetext_family_top text CHARACTER SET utf8,
            treetext_family_footer text CHARACTER SET utf8,
            PRIMARY KEY  (`treetext_id`)
            ) DEFAULT CHARSET=utf8");

        // *** Immediately add new tables in tree ***
        $_SESSION['tree_prefix'] = 'humo_';

        // *** Reset session values for editor ***
        unset($_SESSION['admin_pers_gedcomnumber']);
        unset($_SESSION['admin_fam_gedcomnumber']);
        unset($_SESSION['admin_tree_prefix']);
        unset($_SESSION['admin_tree_id']);

        // *** Persons ***
        try {
            $dbh->query("DROP TABLE humo_persons");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_persons');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_persons (
            pers_id INT(10) unsigned NOT NULL auto_increment,
            pers_gedcomnumber varchar(25) CHARACTER SET utf8,
            pers_tree_id mediumint(7),
            pers_tree_prefix varchar(10) CHARACTER SET utf8,
            pers_famc varchar(50) CHARACTER SET utf8,
            pers_fams varchar(150) CHARACTER SET utf8,
            pers_indexnr varchar(25) CHARACTER SET utf8,
            pers_firstname varchar(60) CHARACTER SET utf8,
            pers_callname varchar(50) CHARACTER SET utf8,
            pers_prefix varchar(20) CHARACTER SET utf8,
            pers_lastname varchar(60) CHARACTER SET utf8,
            pers_patronym varchar(50) CHARACTER SET utf8,
            pers_name_text text CHARACTER SET utf8,
            pers_sexe varchar(1) CHARACTER SET utf8,
            pers_own_code varchar(100) CHARACTER SET utf8,
            pers_birth_place varchar(120) CHARACTER SET utf8,
            pers_birth_date varchar(35) CHARACTER SET utf8,
            pers_birth_time varchar(25) CHARACTER SET utf8,
            pers_birth_text text CHARACTER SET utf8,
            pers_stillborn varchar(1) CHARACTER SET utf8 DEFAULT 'n',
            pers_bapt_place varchar(120) CHARACTER SET utf8,
            pers_bapt_date varchar(35) CHARACTER SET utf8,
            pers_bapt_text text CHARACTER SET utf8,
            pers_religion varchar(50) CHARACTER SET utf8,
            pers_death_place varchar(120) CHARACTER SET utf8,
            pers_death_date varchar(35) CHARACTER SET utf8,
            pers_death_time varchar(25) CHARACTER SET utf8,
            pers_death_text text CHARACTER SET utf8,
            pers_death_cause varchar(255) CHARACTER SET utf8,
            pers_death_age varchar(15) CHARACTER SET utf8,
            pers_buried_place varchar(120) CHARACTER SET utf8,
            pers_buried_date varchar(35) CHARACTER SET utf8,
            pers_buried_text text CHARACTER SET utf8,
            pers_cremation varchar(1) CHARACTER SET utf8,
            pers_place_index text CHARACTER SET utf8,
            pers_text text CHARACTER SET utf8,
            pers_alive varchar(20) CHARACTER SET utf8,
            pers_cal_date varchar(35) CHARACTER SET utf8,
            pers_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            pers_new_user_id smallint NULL DEFAULT NULL,
            pers_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            pers_changed_user_id smallint NULL DEFAULT NULL,
            pers_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`pers_id`),
            KEY (pers_prefix),
            KEY (pers_lastname),
            KEY (pers_gedcomnumber),
            KEY (pers_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // *** Families ***
        try {
            $dbh->query("DROP TABLE humo_families");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_families');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_families (
            fam_id INT(10) unsigned NOT NULL auto_increment,
            fam_tree_id mediumint(7),
            fam_gedcomnumber varchar(25) CHARACTER SET utf8,
            fam_man varchar(25) CHARACTER SET utf8,
            fam_man_age varchar(15) CHARACTER SET utf8,
            fam_woman varchar(25) CHARACTER SET utf8,
            fam_woman_age varchar(15) CHARACTER SET utf8,
            fam_children text CHARACTER SET utf8,
            fam_kind varchar(50) CHARACTER SET utf8,
            fam_relation_date varchar(35) CHARACTER SET utf8,
            fam_relation_place varchar(120) CHARACTER SET utf8,
            fam_relation_text text CHARACTER SET utf8,
            fam_relation_end_date varchar(35) CHARACTER SET utf8,
            fam_marr_notice_date varchar(35) CHARACTER SET utf8,
            fam_marr_notice_place varchar(120) CHARACTER SET utf8,
            fam_marr_notice_text text CHARACTER SET utf8,
            fam_marr_date varchar(35) CHARACTER SET utf8,
            fam_marr_place varchar(120) CHARACTER SET utf8,
            fam_marr_text text CHARACTER SET utf8,
            fam_marr_authority text CHARACTER SET utf8,
            fam_marr_church_notice_date varchar(35) CHARACTER SET utf8,
            fam_marr_church_notice_place varchar(120) CHARACTER SET utf8,
            fam_marr_church_notice_text text CHARACTER SET utf8,
            fam_marr_church_date varchar(35) CHARACTER SET utf8,
            fam_marr_church_place varchar(120) CHARACTER SET utf8,
            fam_marr_church_text text CHARACTER SET utf8,
            fam_religion varchar(50) CHARACTER SET utf8,
            fam_div_date varchar(35) CHARACTER SET utf8,
            fam_div_place varchar(120) CHARACTER SET utf8,
            fam_div_text text CHARACTER SET utf8,
            fam_div_authority text CHARACTER SET utf8,
            fam_text text CHARACTER SET utf8,
            fam_alive int(1),
            fam_cal_date varchar(35) CHARACTER SET utf8,
            fam_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            fam_counter mediumint(7),
            fam_new_user_id smallint NULL DEFAULT NULL,
            fam_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            fam_changed_user_id smallint NULL DEFAULT NULL,
            fam_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`fam_id`),
            KEY (fam_tree_id),
            KEY (fam_gedcomnumber),
            KEY (fam_man),
            KEY (fam_woman)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // *** Unprocessed tags ***
        try {
            $dbh->query("DROP TABLE humo_unprocessed_tags");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_unprocessed_tags');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_unprocessed_tags (
            tag_id INT(10) unsigned NOT NULL auto_increment,
            tag_pers_id INT(10),
            tag_rel_id INT(10),
            tag_event_id INT(10),
            tag_source_id INT(10),
            tag_connect_id INT(10),
            tag_repo_id INT(10),
            tag_place_id INT(10),
            tag_address_id INT(10),
            tag_text_id INT(10),
            tag_tree_id smallint(5),
            tag_tag text CHARACTER SET utf8,
            PRIMARY KEY (`tag_id`),
            KEY (tag_tree_id),
            KEY (tag_pers_id),
            KEY (tag_rel_id),
            KEY (tag_event_id),
            KEY (tag_source_id),
            KEY (tag_connect_id),
            KEY (tag_repo_id),
            KEY (tag_place_id),
            KEY (tag_address_id),
            KEY (tag_text_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // *** Repositories ***
        try {
            $dbh->query("DROP TABLE humo_repositories");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_repositories');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_repositories (
            repo_id INT(10) unsigned NOT NULL auto_increment,
            repo_tree_id smallint(5),
            repo_gedcomnr varchar(25) CHARACTER SET utf8,
            repo_name text CHARACTER SET utf8,
            repo_address text CHARACTER SET utf8,
            repo_zip varchar(20) CHARACTER SET utf8,
            repo_place varchar(120) CHARACTER SET utf8,
            repo_phone varchar(25) CHARACTER SET utf8,
            repo_date varchar(35) CHARACTER SET utf8,
            repo_text text CHARACTER SET utf8,
            repo_mail varchar(100) CHARACTER SET utf8,
            repo_url varchar(150) CHARACTER SET utf8,
            repo_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            repo_new_user_id smallint NULL DEFAULT NULL,
            repo_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            repo_changed_user_id smallint NULL DEFAULT NULL,
            repo_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`repo_id`),
            KEY (repo_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // *** Sources ***
        try {
            $dbh->query("DROP TABLE humo_sources");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_sources');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_sources (
            source_id INT(10) unsigned NOT NULL auto_increment,
            source_tree_id smallint(5),
            source_status varchar(10) CHARACTER SET utf8,
            source_gedcomnr varchar(25) CHARACTER SET utf8,
            source_shared varchar(1) CHARACTER SET utf8 DEFAULT '',
            source_order mediumint(6),
            source_title text CHARACTER SET utf8,
            source_abbr varchar(50) CHARACTER SET utf8,
            source_date varchar(35) CHARACTER SET utf8,
            source_publ varchar(150) CHARACTER SET utf8,
            source_place varchar(120) CHARACTER SET utf8,
            source_refn varchar(50) CHARACTER SET utf8,
            source_auth varchar(50) CHARACTER SET utf8,
            source_subj varchar(248) CHARACTER SET utf8,
            source_item varchar(30) CHARACTER SET utf8,
            source_kind varchar(50) CHARACTER SET utf8,
            source_text text CHARACTER SET utf8,
            source_repo_name varchar(50) CHARACTER SET utf8,
            source_repo_caln varchar(50) CHARACTER SET utf8,
            source_repo_page varchar(50) CHARACTER SET utf8,
            source_repo_gedcomnr varchar(25) CHARACTER SET utf8,
            source_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            source_new_user_id smallint NULL DEFAULT NULL,
            source_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            source_changed_user_id smallint NULL DEFAULT NULL,
            source_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`source_id`),
            KEY (source_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // *** Texts ***
        try {
            $dbh->query("DROP TABLE humo_texts");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_texts');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_texts (
            text_id INT(10) unsigned NOT NULL auto_increment,
            text_tree_id smallint(5),
            text_gedcomnr varchar(25) CHARACTER SET utf8,
            text_text text CHARACTER SET utf8,
            text_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            text_new_user_id smallint NULL DEFAULT NULL,
            text_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            text_changed_user_id smallint NULL DEFAULT NULL,
            text_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (text_id),
            KEY (text_tree_id),
            KEY (`text_gedcomnr`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // *** Connections ***
        try {
            $dbh->query("DROP TABLE humo_connections");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_connections');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_connections (
            connect_id INT(10) unsigned NOT NULL auto_increment,
            connect_tree_id smallint(5),
            connect_order mediumint(6),
            connect_kind varchar(25) CHARACTER SET utf8,
            connect_sub_kind varchar(30) CHARACTER SET utf8,
            connect_connect_id varchar(25) CHARACTER SET utf8,
            connect_date varchar(35) CHARACTER SET utf8,
            connect_place varchar(120) CHARACTER SET utf8,
            connect_time varchar(25) CHARACTER SET utf8,
            connect_page text CHARACTER SET utf8,
            connect_role varchar(75) CHARACTER SET utf8,
            connect_text text CHARACTER SET utf8,
            connect_source_id varchar(25) CHARACTER SET utf8,
            connect_item_id varchar(25) CHARACTER SET utf8,
            connect_status varchar(10) CHARACTER SET utf8,
            connect_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            connect_new_user_id smallint NULL DEFAULT NULL,
            connect_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            connect_changed_user_id smallint NULL DEFAULT NULL,
            connect_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`connect_id`),
            KEY (connect_connect_id),
            KEY (connect_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // *** Addresses ***
        try {
            $dbh->query("DROP TABLE humo_addresses");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_addresses');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_addresses (
            address_id INT(10) unsigned NOT NULL auto_increment,
            address_tree_id smallint(5),
            address_gedcomnr varchar(25) CHARACTER SET utf8,
            address_shared varchar(1) CHARACTER SET utf8 DEFAULT '',
            address_order mediumint(6),
            address_connect_kind varchar(25) DEFAULT NULL,
            address_connect_sub_kind varchar(30) DEFAULT NULL,
            address_connect_id varchar(25) CHARACTER SET utf8,
            address_address text CHARACTER SET utf8,
            address_zip varchar(20) CHARACTER SET utf8,
            address_place varchar(120) CHARACTER SET utf8,
            address_phone varchar(20) CHARACTER SET utf8,
            address_date varchar(35) CHARACTER SET utf8,
            address_text text CHARACTER SET utf8,
            address_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            address_new_user_id smallint NULL DEFAULT NULL,
            address_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            address_changed_user_id smallint NULL DEFAULT NULL,
            address_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`address_id`),
            KEY (address_tree_id),
            KEY (address_gedcomnr)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");

        // *** Events ***
        try {
            $dbh->query("DROP TABLE humo_events");
        } catch (Exception $e) {
            //
        }
        printf(__('create table: %s.'), 'humo_events');
        echo '<br>';
        $dbh->query("CREATE TABLE humo_events (
            event_id INT(10) unsigned NOT NULL auto_increment,
            event_tree_id smallint(5),
            event_gedcomnr varchar(25) CHARACTER SET utf8,
            event_order mediumint(6),
            event_connect_kind varchar(25) CHARACTER SET utf8,
            event_connect_id varchar(25) DEFAULT NULL,
            event_connect_kind2 varchar(25) CHARACTER SET utf8,
            event_connect_id2 varchar(25) DEFAULT NULL,
            event_pers_age varchar(15) CHARACTER SET utf8,
            event_kind varchar(20) CHARACTER SET utf8,
            event_event text CHARACTER SET utf8,
            event_event_extra text CHARACTER SET utf8,
            event_gedcom varchar(20) CHARACTER SET utf8,
            event_date varchar(35) CHARACTER SET utf8,
            event_place varchar(120) CHARACTER SET utf8,
            event_text text CHARACTER SET utf8,
            event_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            event_new_user_id smallint NULL DEFAULT NULL,
            event_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
            event_changed_user_id smallint NULL DEFAULT NULL,
            event_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL,
            PRIMARY KEY (`event_id`),
            KEY (event_tree_id),
            KEY (event_connect_id),
            KEY (event_connect_id2),
            KEY (event_kind)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8");
    }
    ?><br>

    <b><?= __('No errors above? This means that the database has been processed!'); ?></b><br>
    <?php printf(__('All updates completed, go to: %s'), '<a href="index.php">' . __('Main menu') . '</a>'); ?>

<?php
}

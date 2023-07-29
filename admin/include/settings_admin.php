<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

?>
<h1 class="center"><?= __('Settings'); ?></h1>
<?php

if (isset($_POST['timeline_language'])) {
    $time_lang = $_POST['timeline_language'];
} elseif (isset($_GET['timeline_language'])) {
    $time_lang = $_GET['timeline_language'];
} else {
    $time_lang = $humo_option['default_language'];
}

if (isset($_POST['save_option'])) {
    // *** Update settings ***
    $result = $db_functions->update_settings('default_skin', $_POST["default_skin"]);

    $result = $db_functions->update_settings('default_language', $_POST["default_language"]);
    $result = $db_functions->update_settings('default_language_admin', $_POST["default_language_admin"]);

    $result = $db_functions->update_settings('text_header', $_POST["text_header"]);
    $result = $db_functions->update_settings('text_footer', $_POST["text_footer"]);

    $result = $db_functions->update_settings('debug_front_pages', $_POST["debug_front_pages"]);
    $result = $db_functions->update_settings('debug_admin_pages', $_POST["debug_admin_pages"]);

    $result = $db_functions->update_settings('database_name', $_POST["database_name"]);
    $result = $db_functions->update_settings('homepage', $_POST["homepage"]);
    $result = $db_functions->update_settings('homepage_description', $_POST["homepage_description"]);

    $result = $db_functions->update_settings('rss_link', $_POST["rss_link"]);

    $result = $db_functions->update_settings('searchengine', $_POST["searchengine"]);
    $result = $db_functions->update_settings('robots_option', $_POST["robots_option"]);

    $result = $db_functions->update_settings('searchengine_cms_only', $_POST["searchengine_cms_only"]);

    $result = $db_functions->update_settings('block_spam_question', $_POST["block_spam_question"]);
    $result = $db_functions->update_settings('block_spam_answer', $_POST["block_spam_answer"]);

    $result = $db_functions->update_settings('use_spam_question', $_POST["use_spam_question"]);
    $result = $db_functions->update_settings('use_newsletter_question', $_POST["use_newsletter_question"]);

    $result = $db_functions->update_settings('visitor_registration', $_POST["visitor_registration"]);
    $result = $db_functions->update_settings('general_email', $_POST["general_email"]);
    $result = $db_functions->update_settings('visitor_registration_group', $_POST["visitor_registration_group"]);
    $result = $db_functions->update_settings('registration_use_spam_question', $_POST["registration_use_spam_question"]);
    $result = $db_functions->update_settings('password_retreival', $_POST["password_retreival"]);

    /*
    ***************************
    Kai Mahnke 2020-04
    Save email configuration settings 
    ****************************
    */
    $result = $db_functions->update_settings('mail_auto', $_POST["mail_auto"]);
    $result = $db_functions->update_settings('email_user', $_POST["email_user"]);
    $result = $db_functions->update_settings('email_password', $_POST["email_password"]);
    $result = $db_functions->update_settings('smtp_server', $_POST["smtp_server"]);
    $result = $db_functions->update_settings('smtp_port', $_POST["smtp_port"]);
    $result = $db_functions->update_settings('smtp_auth', $_POST["smtp_auth"]);
    $result = $db_functions->update_settings('smtp_encryption', $_POST["smtp_encryption"]);
    $result = $db_functions->update_settings('smtp_debug', $_POST["smtp_debug"]);
    /*
    ***************************
    End changes
    ***************************
    */

    $result = $db_functions->update_settings('descendant_generations', $_POST["descendant_generations"]);

    $result = $db_functions->update_settings('show_persons', $_POST["show_persons"]);

    $result = $db_functions->update_settings('url_rewrite', $_POST["url_rewrite"]);

    $result = $db_functions->update_settings('timezone', $_POST["timezone"]);

    $result = $db_functions->update_settings('watermark_text', $_POST["watermark_text"]);
    $result = $db_functions->update_settings('watermark_color_r', $_POST["watermark_color_r"]);
    $result = $db_functions->update_settings('watermark_color_g', $_POST["watermark_color_g"]);
    $result = $db_functions->update_settings('watermark_color_b', $_POST["watermark_color_b"]);
    $result = $db_functions->update_settings('min_search_chars', $_POST["min_search_chars"]);
    $result = $db_functions->update_settings('date_display', $_POST["date_display"]);
    $result = $db_functions->update_settings('name_order', $_POST["name_order"]);
    $result = $db_functions->update_settings('one_name_study', $_POST["one_name_study"]);
    $result = $db_functions->update_settings('one_name_thename', $_POST["one_name_thename"]);

    if (strpos($humo_option['default_timeline'], $time_lang . "!") === false) {
        // no entry for this language yet - append it
        $result = $dbh->query("UPDATE humo_settings SET setting_value=CONCAT(setting_value,'" . safe_text_db($_POST["default_timeline"]) . "') WHERE setting_variable='default_timeline'");
    } else {
        $time_arr = explode("@", substr($humo_option['default_timeline'], 0, -1));
        foreach ($time_arr as $key => $value) {
            if (strpos($value, $time_lang . "!") !== false) {
                $time_arr[$key] = substr(safe_text_db($_POST["default_timeline"]), 0, -1);
            }
        }
        $time_str = implode("@", $time_arr) . "@";
        $result = $db_functions->update_settings('default_timeline', $time_str);
    }

    // *** Upload favicon icon to folder /media ***
    if (isset($_FILES['upload_favicon']) and $_FILES['upload_favicon']['name']) {
        if ($_FILES['upload_favicon']['type'] == "image/x-icon" || $_FILES['upload_favicon']['type'] == "image/png" || $_FILES['upload_favicon']['type'] == "image/jpeg") {
            $fault = "";
            // 100000=100kb.
            if ($_FILES['upload_favicon']['size'] > 100000) {
                $fault = __('Photo too large');
            }
            if (!$fault) {
                $picture_original = '../media/favicon' . substr($_FILES['upload_favicon']['name'], -4);
                if (move_uploaded_file($_FILES['upload_favicon']['tmp_name'], $picture_original)) {
                    echo __('Changed favicon icon.');
                } else {
                    echo __('Photo upload failed, check folder rights');
                }
            }
        }
    }

    // *** Upload logo to folder /media ***
    if (isset($_FILES['upload_logo']) and $_FILES['upload_logo']['name']) {
        if ($_FILES['upload_logo']['type'] == "image/png" || $_FILES['upload_logo']['type'] == "image/jpeg") {
            $fault = "";
            // 100000=100kb.
            if ($_FILES['upload_logo']['size'] > 1000000) {
                $fault = __('Photo too large');
            }
            if (!$fault) {
                $picture_original = '../media/logo' . substr($_FILES['upload_logo']['name'], -4);
                if (move_uploaded_file($_FILES['upload_logo']['tmp_name'], $picture_original)) {
                    echo __('Changed logo.');
                } else {
                    echo __('Photo upload failed, check folder rights');
                }
            }
        }
    }
}

// *** Homepage ***
if (isset($_POST['save_option2'])) {
    // *** Slideshow ***
    $result = $db_functions->update_settings('slideshow_show', $_POST["slideshow_show"]);
    $result = $db_functions->update_settings('slideshow_01', $_POST["slideshow_slide_01"] . '|' . $_POST["slideshow_text_01"]);
    $result = $db_functions->update_settings('slideshow_02', $_POST["slideshow_slide_02"] . '|' . $_POST["slideshow_text_02"]);
    $result = $db_functions->update_settings('slideshow_03', $_POST["slideshow_slide_03"] . '|' . $_POST["slideshow_text_03"]);
    $result = $db_functions->update_settings('slideshow_04', $_POST["slideshow_slide_04"] . '|' . $_POST["slideshow_text_04"]);

    // *** Today in history ***
    //$result = $db_functions->update_settings('today_in_history_show',$_POST["today_in_history_show"]);
}

// *** Special settings ***
if (isset($_POST['save_option3'])) {
    // Jewish settings

    $setting_value = 'n';
    if (isset($_POST["david_stars"])) $setting_value = 'y';
    $result = $db_functions->update_settings('david_stars', $setting_value);

    $setting_value = 'n';
    if (isset($_POST["death_shoa"])) $setting_value = 'y';
    $result = $db_functions->update_settings('death_shoa', $setting_value);

    $setting_value = 'n';
    if (isset($_POST["admin_hebnight"])) $setting_value = 'y';
    $result = $db_functions->update_settings('admin_hebnight', $setting_value);

    $setting_value = 'n';
    if (isset($_POST["admin_hebdate"])) $setting_value = 'y';
    $result = $db_functions->update_settings('admin_hebdate', $setting_value);

    $setting_value = 'n';
    if (isset($_POST["admin_hebname"])) $setting_value = 'y';
    $result = $db_functions->update_settings('admin_hebname', $setting_value);

    $setting_value = 'n';
    if (isset($_POST["admin_brit"])) $setting_value = 'y';
    $result = $db_functions->update_settings('admin_brit', $setting_value);

    $setting_value = 'n';
    if (isset($_POST["admin_barm"])) $setting_value = 'y';
    $result = $db_functions->update_settings('admin_barm', $setting_value);

    if (isset($_POST["death_char"]) and safe_text_db($_POST["death_char"]) == "y"  and $humo_option['death_char'] == "n") {
        include(CMS_ROOTPATH . "languages/change_all.php");  // change cross to infinity
        $result = $db_functions->update_settings('death_char', 'y');
    } elseif ((!isset($_POST["death_char"]) or safe_text_db($_POST["death_char"]) == "n") and $humo_option['death_char'] == "y") {
        include(CMS_ROOTPATH . "languages/change_all.php");  // change infinity to cross
        $result = $db_functions->update_settings('death_char', 'n');
    }
}

// *** Re-read variables after changing them ***
// *** Don't use include_once! Otherwise the old value will be shown ***
include(CMS_ROOTPATH . "include/settings_global.php"); //variables

// *** Read languages in language array ***
$arr_count = 0;
$arr_count_admin = 0;
$folder = opendir(CMS_ROOTPATH . 'languages/');
while (false !== ($file = readdir($folder))) {
    if (strlen($file) < 6 and $file != '.' and $file != '..') {
        // *** Get language name ***
        include(CMS_ROOTPATH . "languages/" . $file . "/language_data.php");
        $langs[$arr_count][0] = $language["name"];
        $langs[$arr_count][1] = $file;
        $arr_count++;
        if (file_exists(CMS_ROOTPATH . 'languages/' . $file . '/' . $file . '.mo')) {
            $langs_admin[$arr_count_admin][0] = $language["name"];
            $langs_admin[$arr_count_admin][1] = $file;
            $arr_count_admin++;
        }
    }
}
closedir($folder);



// *** Show tabs ***
$menu_admin = 'settings';
if (isset($_POST['menu_admin'])) $menu_admin = $_POST['menu_admin'];
if (isset($_GET['menu_admin'])) $menu_admin = $_GET['menu_admin'];
$select_item_settings = '';
if ($menu_admin == 'settings') {
    $select_item_settings = ' pageTab-active';
}
$select_item_homepage = '';
if ($menu_admin == 'settings_homepage') {
    $select_item_homepage = ' pageTab-active';
}
$select_item_special = '';
if ($menu_admin == 'settings_special') {
    $select_item_special = ' pageTab-active';
}

?>
<p>
<div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false">
    <div class="pageHeading">
        <!-- <div class="pageHeadingText">Configuratie gegevens</div> -->
        <!-- <div class="pageHeadingWidgets" aria-hidden="true" style="display: none;"></div> -->
        <div class="pageTabsContainer" aria-hidden="false">
            <ul class="pageTabs">
                <li class="pageTabItem">
                    <div tabindex="0" class="pageTab<?= $select_item_settings; ?>"><a href="index.php?page=<?= $page; ?>"><?= __('Settings'); ?></a></div>
                </li>
                <li class="pageTabItem">
                    <div tabindex="0" class="pageTab<?= $select_item_homepage; ?>"><a href="index.php?page=<?= $page; ?>&amp;menu_admin=settings_homepage"><?= __('Homepage'); ?></a></div>
                </li>
                <li class="pageTabItem">
                    <div tabindex="0" class="pageTab<?= $select_item_special; ?>"><a href="index.php?page=<?= $page; ?>&amp;menu_admin=settings_special"><?= __('Special settings'); ?></a></div>
                </li>
            </ul>
        </div>
    </div>
</div>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;">

    <?php
    // *** Show settings ***
    if (isset($menu_admin) and $menu_admin == 'settings') {
        $folder = opendir(CMS_ROOTPATH . 'styles/');
        while (false !== ($file = readdir($folder))) {
            if (substr($file, -4, 4) == '.css') {
                $theme_folder[] = $file;
            }
        }
        closedir($folder);

        $groupsql = "SELECT * FROM humo_groups";
        $groupresult = $dbh->query($groupsql);

        // *** List of timezones ***
        // Example from website: https://stackoverflow.com/questions/4755704/php-timezone-list
        $zones_array = array();
        $timestamp = time();
        foreach (timezone_identifiers_list() as $key => $zone) {
            date_default_timezone_set($zone);
            $zones_array[$key]['zone'] = $zone;
            //$zones_array[$key]['offset'] = (int) ((int) date('O', $timestamp))/100;
            $zones_array[$key]['diff_from_GMT'] = 'UTC/GMT ' . date('P', $timestamp);
        }
    ?>
        <form method="post" action="index.php" enctype="multipart/form-data">
            <input type="hidden" name="page" value="<?= $page; ?>">

            <table class="humo" border="1">
                <tr class="table_header">
                    <th colspan="2"><?= __('General settings'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>

                <tr>
                    <td><?= __('Default skin'); ?></td>
                    <td>
                        <select size="1" name="default_skin">
                            <option value="">Standard</option>
                            <?php
                            for ($i = 0; $i < count($theme_folder); $i++) {
                                $theme = $theme_folder[$i];
                                $theme = str_replace(".css", "", $theme);
                                $select = '';
                                if ($humo_option['default_skin'] == $theme) {
                                    $select = ' selected';
                                }
                                echo '<option value="' . $theme . '"' . $select . '>' . $theme . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><?= sprintf(__('Standard language %s'), 'HuMo-genealogy'); ?></td>
                    <td>
                        <select size="1" name="default_language">
                            <?php
                            if ($langs) {
                                for ($i = 0; $i < count($langs); $i++) {
                                    $select = '';
                                    if ($humo_option['default_language'] == $langs[$i][1]) {
                                        $select = ' selected';
                                    }
                                    echo '<option value="' . $langs[$i][1] . '"' . $select . '>' . $langs[$i][0] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><?= __('Standard language admin menu'); ?></td>
                    <td>
                        <select size="1" name="default_language_admin">
                            <?php
                            if ($langs_admin) {
                                for ($i = 0; $i < count($langs_admin); $i++) {
                                    $select = '';
                                    if ($humo_option['default_language_admin'] == $langs_admin[$i][1]) {
                                        $select = ' selected';
                                    }
                                    echo '<option value="' . $langs_admin[$i][1] . '"' . $select . '>' . $langs_admin[$i][0] . '</option>';
                                }
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><?= __('Change favicon icon'); ?></td>
                    <td>
                        <?= sprintf(__('Upload favicon.ico file. File size max: %1$d kB.'), '100'); ?>
                        <input type="file" name="upload_favicon">
                        <input type="submit" name="save_option" title="submit" value="<?= __('Upload'); ?>">
                    </td>
                </tr>

                <tr>
                    <td><?= __('Scripts in &lt;head&gt; section for all pages'); ?></td>
                    <td>
                        <textarea cols="80" rows="1" name="text_header" style="height: 20px;"><?= htmlentities($humo_option["text_header"], ENT_NOQUOTES); ?></textarea><br>
                        <?= __('Can be used for statistics, counter, etc.'); ?>
                    </td>
                </tr>

                <tr>
                    <td><?= __('Text in footer for all pages'); ?></td>
                    <td>
                        <textarea cols="80" rows="1" name="text_footer" style="height: 20px;"><?= htmlentities($humo_option["text_footer"], ENT_NOQUOTES); ?></textarea><br>
                        <?= __('Can be used for statistics, counter, etc. It\'s possible to use HTML codes!'); ?>
                    </td>
                </tr>

                <!-- Debug options -->
                <tr>
                    <td valign="top"><?= sprintf(__('Debug %s front pages'), 'HuMo-genealogy'); ?></td>
                    <td>
                        <select size="1" name="debug_front_pages">
                            <option value="y"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["debug_front_pages"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select>
                        <?= sprintf(__('Only use this option to debug problems in %s.'), 'HuMo-genealogy'); ?>
                    </td>
                </tr>

                <tr>
                    <td valign="top"><?= sprintf(__('Debug %s admin pages'), 'HuMo-genealogy'); ?></td>
                    <td>
                        <select size="1" name="debug_admin_pages">
                            <option value="y"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["debug_admin_pages"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select>
                        <?= sprintf(__('Only use this option to debug problems in %s.'), 'HuMo-genealogy'); ?>
                    </td>
                </tr>

                <tr class="table_header">
                    <th colspan="2"><?= __('Search engine settings'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>

                <tr class="humo_color">
                    <td valign="top">url_rewrite<br><?= __('Improve indexing of search engines (like Google)'); ?></td>
                    <td>
                        <select size="1" name="url_rewrite">
                            <option value="j"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["url_rewrite"] != 'j') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select> <b><?= __('ATTENTION: the Apache module "mod_rewrite" has to be installed!'); ?></b><br>
                        URL&nbsp;&nbsp;: http://www.website.nl/humo-gen/family.php?id=F12<br>
                        <?= __('becomes:'); ?> http://www.website.nl/humo-gen/family/F12/<br>
                    </td>
                </tr>

                <tr class="humo_color">
                    <td><?= __('Stop search engines'); ?></td>
                    <td><select size="1" name="searchengine">
                            <option value="j"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["searchengine"] != 'j') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select><br>
                        <textarea cols="80" rows=1 name="robots_option" style="height: 20px;"><?= htmlentities($humo_option["robots_option"], ENT_NOQUOTES); ?></textarea>
                    </td>
                </tr>

                <tr class="humo_color">
                    <td><?= __('Search engines:<br>Hide family tree (no indexing)<br>Show frontpage and CMS pages'); ?></td>
                    <td><select size="1" name="searchengine_cms_only">
                            <option value="y"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["searchengine_cms_only"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select><br></td>
                </tr>

                <tr class="table_header">
                    <th colspan="2"><?= __('Contact & registration form settings'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>

                <tr>
                    <td><?= __('Block spam question'); ?><br><?= __('Block spam answer'); ?></td>
                    <td>
                        <input type="text" name="block_spam_question" value="<?= htmlentities($humo_option["block_spam_question"], ENT_NOQUOTES); ?>" size="60"><br>
                        <input type="text" name="block_spam_answer" value="<?= htmlentities($humo_option["block_spam_answer"], ENT_NOQUOTES); ?>" size="60">
                    </td>
                </tr>

                <tr>
                    <td><?= __('Mail form: use spam question'); ?></td>
                    <td>
                        <select size="1" name="use_spam_question">
                            <option value="y"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["use_spam_question"] != 'y') ' selected'; ?>><?= __('No'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td><?= __('Mail form: use newsletter question'); ?></td>
                    <td>
                        <select size="1" name="use_newsletter_question">
                            <option value="y"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["use_newsletter_question"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select>
                        <?= __('Adds the question: "Receive newsletter: yes/ no" to the mailform.'); ?>
                    </td>
                </tr>

                <tr class="humo_color">
                    <td><?= __('Visitors can register'); ?></td>
                    <td>
                        <select size="1" name="visitor_registration">
                            <option value="y"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["visitor_registration"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select>

                        <?= __('Default user-group for new users:'); ?>
                        <select size="1" name="visitor_registration_group">
                            <?php
                            while ($groupDb = $groupresult->fetch(PDO::FETCH_OBJ)) {
                                $selected = '';
                                if ($humo_option["visitor_registration_group"] == $groupDb->group_id) {
                                    $selected = '  selected';
                                }
                                echo '<option value="' . $groupDb->group_id . '"' . $selected . '>' . $groupDb->group_name . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <!-- Using HTML 5 email check -->
                <tr class="humo_color">
                    <td><?= __('Registration form: e-mail address'); ?></td>
                    <td><input type="email" name="general_email" value="<?= $humo_option["general_email"]; ?>" size="40"> <?= __('Send registration form to this e-mail address.'); ?></td>
                </tr>

                <tr class="humo_color">
                    <td><?= __('Visitor registration: use spam question'); ?></td>
                    <td>
                        <select size="1" name="registration_use_spam_question">
                            <option value="y"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["registration_use_spam_question"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Using HTML 5 email check -->
                <tr>
                    <td><?= __('Password forgotten e-mail address'); ?></td>
                    <td><input type="email" name="password_retreival" value="<?= $humo_option["password_retreival"]; ?>" size="40" placeholder="no-reply@your-website.com"> <?= __('To enable password forgotten option: set a sender e-mail address.'); ?></td>
                </tr>

                <tr class="table_header">
                    <th colspan="2"><?= __('Email Settings'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>

                <tr>
                    <td><?= __('Email Settings'); ?></td>
                    <td><?= __('TIP: mail will work without changing these parameters at most hosting providers.'); ?></td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('Mail: configuration'); ?></td>
                    <td>
                        <select size="1" name="mail_auto">
                            <option value="auto" <?php if ($humo_option["mail_auto"] == 'auto') echo ' selected'; ?>><?= __('auto'); ?></option>
                            <option value="manual" <?php if ($humo_option["mail_auto"] == 'manual') echo ' selected'; ?>><?= __('manual'); ?></option>
                        </select><br>
                        <?= __('Setting: "auto" = use settings below.<br>Setting: "manual" = change settings in /include/mail.php'); ?>
                    </td>
                </tr>

                <tr>
                    <td><?= __('Mail: username'); ?></td>
                    <td><input type="text" name="email_user" value="<?= $humo_option["email_user"]; ?>" size="32">
                        <?= __('Gmail: [email_address]@gmail.com'); ?>
                    </td>
                </tr>

                <tr>
                    <td><?= __('Mail: password'); ?></td>
                    <td><input type="text" name="email_password" value="<?= $humo_option["email_password"]; ?>" size="32"></td>
                </tr>

                <tr>
                    <td><?= __('SMTP: mail server'); ?></td>
                    <td><input type="text" name="smtp_server" value="<?= $humo_option["smtp_server"]; ?>" size="32">
                        <?= __('Gmail: smtp.gmail.com'); ?>
                    </td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('SMTP: port'); ?></td>
                    <td>
                        <select size="1" name="smtp_port">
                            <option value="25" <?php if ($humo_option["smtp_port"] == '25') echo ' selected'; ?>>25</option>
                            <option value="465" <?php if ($humo_option["smtp_port"] == '465') echo ' selected'; ?>>465</option>
                            <option value="587" <?php if ($humo_option["smtp_port"] == '587') echo ' selected'; ?>>587</option>
                        </select>
                        <?= __('Gmail: 587'); ?>
                    </td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('SMTP: authentication'); ?></td>
                    <td>
                        <select size="1" name="smtp_auth">
                            <option value="true" <?php if ($humo_option["smtp_auth"] == 'true') echo ' selected'; ?>><?= __('true'); ?></option>
                            <option value="false" <?php if ($humo_option["smtp_auth"] == 'false') echo ' selected'; ?>><?= __('false'); ?></option>
                        </select>
                        <?= __('Gmail: true'); ?>
                    </td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('SMTP: encryption type'); ?></td>
                    <td>
                        <select size="1" name="smtp_encryption">
                            <option value="tls" <?php if ($humo_option["smtp_encryption"] == 'tls') echo ' selected'; ?>>TLS</option>
                            <option value="ssl" <?php if ($humo_option["smtp_encryption"] == 'ssl') echo ' selected'; ?>>SSL</option>';
                        </select>
                        <?= __('Gmail: TLS'); ?>
                    </td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('SMTP: debugging'); ?></td>
                    <td>
                        <select size="1" name="smtp_debug">
                            <option value="0" <?php if ($humo_option["smtp_debug"] == '0') echo ' selected'; ?>><?= __('Off'); ?></option>
                            <option value="1" <?php if ($humo_option["smtp_debug"] == '1') echo ' selected'; ?>><?= __('Client'); ?></option>
                            <option value="2" <?php if ($humo_option["smtp_debug"] == '2') echo ' selected'; ?>><?= __('Client and Server'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr class="table_header">
                    <th colspan="2"><?= __('International settings'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>

                <tr>
                    <td valign="top"><?= __('Timezone'); ?></td>
                    <td>
                        <select size="1" name="timezone">
                            <?php
                            foreach ($zones_array as $t) {
                                $selected = '';
                                if ($humo_option["timezone"] == $t['zone']) {
                                    $selected = ' selected';
                                }
                                echo '<option value="' . $t['zone'] . '"' . $selected . '>' . $t['diff_from_GMT'] . ' - ' . $t['zone'] . '</option>';
                            }
                            ?>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('Minimum characters in search box'); ?></td>
                    <td><input type="text" name="min_search_chars" value="<?= $humo_option["min_search_chars"]; ?>" size="4"> <?= __('Minimum characters in search boxes (standard value=3. For Chinese set to 1).'); ?></td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('Date display'); ?></td>
                    <td>
                        <select size="1" name="date_display">
                            <option value="eu" <?php if ($humo_option["date_display"] == 'eu') echo ' selected'; ?>><?= __('Europe/Global - 5 Jan 1787'); ?></option>
                            <option value="us" <?php if ($humo_option["date_display"] == 'us') echo ' selected'; ?>><?= __('USA - Jan 5, 1787'); ?></option>
                            <option value="ch" <?php if ($humo_option["date_display"] == 'ch') echo ' selected'; ?>><?= __('China - 1787-01-05'); ?></option>
                        </select>
                    </td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('Order of names in reports'); ?></td>
                    <td>
                        <select size="1" name="name_order">
                            <option value="western" <?php if ($humo_option["name_order"] == 'western') echo ' selected'; ?>><?= __('Western'); ?></option>
                            <option value="chinese" <?php if ($humo_option["name_order"] == 'chinese') echo ' selected'; ?>><?= __('Chinese') . "/ " . __('Hungarian'); ?></option>
                        </select>
                        <?= __('Western - reports: John Smith, lists: Smith, John. Chinese 中文 - reports and lists: 刘 理想') . ". " . __('Hungarian - reports and lists: Smith John'); ?>
                    </td>
                </tr>

                <!-- timeline default -->
                <tr id="timeline_anchor">
                    <td><?= __('Default timeline file (per language)'); ?></td>
                    <td>
                        <?php
                        // *** First select language ***
                        if ($langs) {
                            echo '<select onChange="window.location =\'index.php?page=settings&timeline_language=\' + this.value + \'#timeline_anchor\'; "  size="1" name="timeline_language">';
                            // *** Default language = english ***
                            //echo '<option value="default_timelines"'.$select.'>English</option>'; // *** Don't add "English" in translation file! ***
                            echo '<option value="default_timelines"' . $select . '>' . __('Default') . '</option>'; // *** Don't add "English" in translation file! ***
                            for ($i = 0; $i < count($langs); $i++) {
                                if (is_dir(CMS_ROOTPATH . 'languages/' . $langs[$i][1] . '/timelines/')) {
                                    $select = '';
                                    if ($time_lang == $langs[$i][1]) {
                                        $select = ' selected';
                                    }
                                    echo '<option value="' . $langs[$i][1] . '"' . $select . '>' . $langs[$i][0] . '</option>';
                                }
                            }
                            echo '</select>';
                        }

                        echo "&nbsp;&nbsp;";

                        // *** First select language, then the timeline files of that language is shown ***
                        $folder = @opendir(CMS_ROOTPATH . 'languages/' . $time_lang . '/timelines/');
                        // *** Default language = english ***
                        if ($time_lang == 'default_timelines') $folder = @opendir(CMS_ROOTPATH . 'languages/' . $time_lang);
                        if ($folder !== false) {  // no use showing the option if we can't access the timeline folder
                            while (false !== ($file = readdir($folder))) {
                                if (substr($file, -4, 4) == '.txt') {
                                    $timeline_files[] = $file;
                                }
                            }
                            echo '<select size="1" name="default_timeline">';
                            for ($i = 0; $i < count($timeline_files); $i++) {
                                $timeline = $timeline_files[$i];
                                $timeline = str_replace(".txt", "", $timeline);
                                $select = "";
                                if (strpos($humo_option['default_timeline'], $time_lang . "!" . $timeline) !== false) {
                                    $select = ' selected';
                                }
                                echo '<option value="' . $time_lang . '!' . $timeline . '@"' . $select . '>' . $timeline . '</option>';
                            }
                            echo '</select>';
                            echo "&nbsp;&nbsp;";
                            echo __('First select language, then select the default timeline for that language.');
                        }
                        //@closedir($folder);
                        if ($folder !== false) @closedir($folder);
                        ?>
                    </td>
                </tr>

                <tr class="table_header">
                    <th colspan="2"><?= __('Settings Main Menu'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>

                <tr>
                    <td><?= __('Website name'); ?></td>
                    <td><input type="text" name="database_name" value="<?= $humo_option["database_name"]; ?>" size="40"></td>
                </tr>

                <!-- Upload logo. Recommended size: 167 x 25px -->
                <tr>
                    <td><?= __('Use logo image instead of text'); ?></td>
                    <td>
                        <?= printf(__('Upload logo image. Recommended size: 165 x 25 px. Picture max: %1$d MB.'), '1'); ?>
                        <input type="file" name="upload_logo">
                        <input type="submit" name="save_option" title="submit" value="<?= __('Upload'); ?>">
                    </td>
                </tr>

                <tr>
                    <td><?= __('Link homepage'); ?><br><?= __('Link description'); ?></td>
                    <td><input type="text" name="homepage" value="<?= $humo_option["homepage"]; ?>" size="40"> <span style="white-space:nowrap;"><?= __('(link to this site including http://)'); ?></span><br>
                        <input type="text" name="homepage_description" value="<?= $humo_option["homepage_description"]; ?>" size="40">
                    </td>
                </tr>

                <!-- Birthday RSS -->
                <tr>
                    <td><?= __('Link for birthdays RSS'); ?></td>
                    <td>
                        <input type="text" name="rss_link" value="<?= $humo_option["rss_link"]; ?>" size="40"> <span style="white-space:nowrap;"><?= __('(link to this site including http://)'); ?></span><br>
                        <i><?= __('This option can be turned on or off in the user groups.'); ?></i>
                    </td>
                </tr>

                <!-- FAMILY -->
                <tr class="table_header">
                    <th colspan="2"><?= __('Settings family page'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('Number of generations in descendant report'); ?></td>
                    <td><input type="text" name="descendant_generations" value="<?= $humo_option["descendant_generations"]; ?>" size="4"> <?= __('Show number of generation in descendant report (standard value=4).'); ?></td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('Number of persons in search results'); ?></td>
                    <td><input type="text" name="show_persons" value="<?= $humo_option["show_persons"]; ?>" size="4"> <?= __('Show number of persons in search results (standard value=30).'); ?></td>
                </tr>

                <!-- Watermark text and color in PDF file -->
                <tr class="table_header">
                    <th colspan="2"><?= __('Watermark text in PDF file'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>
                <tr>
                    <td style="white-space:nowrap;"><?= __('Watermark text in PDF file'); ?></td>
                    <td><input type="text" name="watermark_text" value="<?= $humo_option["watermark_text"]; ?>" size="40"> <?= __('Watermark text (clear to remove watermark)'); ?></td>
                </tr>
                <tr>
                    <td style="white-space:nowrap;"><?= __('Watermark RGB text color'); ?></td>
                    <td>
                        R:<input type="text" name="watermark_color_r" value="<?= $humo_option["watermark_color_r"]; ?>" size="4">
                        G:<input type="text" name="watermark_color_g" value="<?= $humo_option["watermark_color_g"]; ?>" size="4">
                        B:<input type="text" name="watermark_color_b" value="<?= $humo_option["watermark_color_b"]; ?>" size="4">
                        <?= __('Default values: R = 224, G = 224, B = 224.'); ?>
                    </td>
                </tr>

                <!-- Display for One Name Study web sites -->
                <tr class="table_header">
                    <th colspan="2"><?= __('Display for One Name Study web sites'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>
                <tr>
                    <td style="white-space:nowrap;"><?= __('One Name Study display'); ?>?</td>
                    <td><select size="1" name="one_name_study">
                            <option value="y"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["one_name_study"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select>
                        <?= __('Only use this option if you\'re doing a "One Name Study" project.'); ?>
                    </td>
                </tr>
                <tr>
                    <td style="white-space:nowrap;"><?= __('Enter the One Name of this site'); ?></td>
                    <td>
                        <input type="text" name="one_name_thename" value="<?= $humo_option["one_name_thename"]; ?>" size="40">
                    </td>
                </tr>

                <tr class="table_header">
                    <th colspan="2">'<?= __('Save settings'); ?> <input type="Submit" name="save_option" value="<?= __('Change'); ?>"></th>
                </tr>
            </table>
        </form>
    <?php
    }

    // *** Show homepage settings ***
    if (isset($menu_admin) and $menu_admin == 'settings_homepage') {

        // *** Reset all modules ***
        if (isset($_GET['template_homepage_reset']) and $_GET['template_homepage_reset'] == '1') {
            $sql = "DELETE FROM humo_settings WHERE setting_variable='template_homepage'";
            $result = $dbh->query($sql);

            // *** Reload page to get new values ***
            echo '<script> window.location="index.php?page=settings&menu_admin=settings_homepage";</script>';
        }

        // *** Change Module ***
        if (isset($_POST['change_module'])) {
            $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage'");
            while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                $setting_value = $_POST[$dataDb->setting_id . 'module_status'] . '|' . $_POST[$dataDb->setting_id . 'module_column'] . '|' . $_POST[$dataDb->setting_id . 'module_item'];
                if (isset($_POST[$dataDb->setting_id . 'module_option_1'])) $setting_value .= '|' . $_POST[$dataDb->setting_id . 'module_option_1'];
                if (isset($_POST[$dataDb->setting_id . 'module_option_2'])) $setting_value .= '|' . $_POST[$dataDb->setting_id . 'module_option_2'];
                $sql = "UPDATE humo_settings SET setting_value='" . safe_text_db($setting_value) . "' WHERE setting_id=" . safe_text_db($_POST[$dataDb->setting_id . 'id']);
                //echo $sql.'<br>';
                $result = $dbh->query($sql);
            }
        }

        // *** Remove module  ***
        if (isset($_GET['remove_module']) and is_numeric($_GET['remove_module'])) {
            $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_id='" . $_GET['remove_module'] . "'");
            $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
            $sql = "DELETE FROM humo_settings WHERE setting_id='" . $dataDb->setting_id . "'";
            $result = $dbh->query($sql);

            // *** Re-order links ***
            $repair_order = $dataDb->setting_order;
            $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order>" . $repair_order);
            while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_settings SET setting_order='" . ($itemDb->setting_order - 1) . "' WHERE setting_id=" . $itemDb->setting_id;
                $result = $dbh->query($sql);
            }
        }

        // *** Add module ***
        if (isset($_POST['add_module']) and is_numeric($_POST['module_order'])) {
            $setting_value = $_POST['module_status'] . "|" . $_POST['module_column'] . "|" . $_POST['module_item'];
            $sql = "INSERT INTO humo_settings SET setting_variable='template_homepage',
                setting_value='" . safe_text_db($setting_value) . "', setting_order='" . safe_text_db($_POST['module_order']) . "'";
            $result = $dbh->query($sql);
        }

        if (isset($_GET['mod_up'])) {
            // *** Search previous module ***
            $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order=" . (safe_text_db($_GET['module_order']) - 1));
            $itemDb = $item->fetch(PDO::FETCH_OBJ);

            // *** Raise previous module ***
            $sql = "UPDATE humo_settings SET setting_order='" . safe_text_db($_GET['module_order']) . "' WHERE setting_id='" . $itemDb->setting_id . "'";

            $result = $dbh->query($sql);
            // *** Lower module order ***
            $sql = "UPDATE humo_settings SET setting_order='" . (safe_text_db($_GET['module_order']) - 1) . "' WHERE setting_id=" . safe_text_db($_GET['id']);

            $result = $dbh->query($sql);
        }
        if (isset($_GET['mod_down'])) {
            // *** Search next link ***
            $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' AND setting_order=" . (safe_text_db($_GET['module_order']) + 1));
            $itemDb = $item->fetch(PDO::FETCH_OBJ);

            // *** Lower previous link ***
            $sql = "UPDATE humo_settings SET setting_order='" . safe_text_db($_GET['module_order']) . "' WHERE setting_id='" . $itemDb->setting_id . "'";

            $result = $dbh->query($sql);
            // *** Raise link order ***
            $sql = "UPDATE humo_settings SET setting_order='" . (safe_text_db($_GET['module_order']) + 1) . "' WHERE setting_id=" . safe_text_db($_GET['id']);

            $result = $dbh->query($sql);
        }

        // *** Show all links ***
    ?>
        <form method=' post' action='index.php'>
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="menu_admin" value="settings_homepage">
            <table class="humo" border="1">
                <tr class="table_header_large">
                    <th class="table_header" colspan="7"><? __('Homepage template'); ?>
                        <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;template_homepage_reset=1">[<?= __('Default settings'); ?>]</a>
                    </th>
                </tr>

                <tr class="table_header">
                    <th><?= __('Status'); ?></th>
                    <th><?= __('Position'); ?></th>
                    <th><?= __('Item'); ?></th>
                    <th><br></th>
                    <th><input type="Submit" name="change_module" value="<?= __('Change'); ?>"></th>
                </tr>
                <?php
                $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
                // *** Number for new module ***
                $count_links = 0;
                if ($datasql->rowCount()) $count_links = $datasql->rowCount();
                $new_number = 1;
                if ($count_links) $new_number = $count_links + 1;
                if ($datasql) {
                    $teller = 1;
                    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                        $lijst = explode("|", $dataDb->setting_value);
                        // *** Just to prevent error messages, set a default value ***
                        if (!isset($lijst[3])) $lijst[3] = '';
                        if (!isset($lijst[4])) $lijst[3] = '';
                ?>
                        <tr>
                            <!-- Active/ inactive with background colour -->
                            <td <?php if ($lijst[0] == 'inactive') echo 'bgcolor="orange"'; ?>>
                                <input type="hidden" name="<?= $dataDb->setting_id; ?>id" value="<?= $dataDb->setting_id; ?>">
                                <select size="1" name="<?= $dataDb->setting_id; ?>module_status">
                                    <option value="active"><?= __('Active'); ?></option>
                                    <option value="inactive" <?php if ($lijst[0] == 'inactive') echo ' selected'; ?>><?= __('Inactive'); ?></option>
                                </select>
                            </td>

                            <td>
                                <select size="1" name="<?= $dataDb->setting_id; ?>module_column">
                                    <option value="left"><?= __('Left'); ?></option>
                                    <option value="center" <?php if ($lijst[1] == 'center') echo ' selected'; ?>><?= __('Center'); ?></option>
                                    <option value="right" <?php if ($lijst[1] == 'right') echo ' selected'; ?>><?= __('Right'); ?></option>
                                </select>
                                <?php

                                if ($dataDb->setting_order != '1') {
                                    echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;mod_up=1&amp;module_order=' . $dataDb->setting_order .
                                        '&amp;id=' . $dataDb->setting_id . '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_up.gif" border="0" alt="up"></a>';
                                }
                                if ($dataDb->setting_order != $count_links) {
                                    echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;mod_down=1&amp;module_order=' . $dataDb->setting_order . '&amp;id=' .
                                        $dataDb->setting_id . '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_down.gif" border="0" alt="down"></a>';
                                }
                                ?>
                            </td>

                            <td>
                                <select size="1" name="<?= $dataDb->setting_id; ?>module_item">
                                    <option value="select_family_tree"><?= __('Select family tree'); ?></option>
                                    <option value="selected_family_tree" <?php if ($lijst[2] == 'selected_family_tree') echo ' selected'; ?>><?= __('Selected family tree'); ?></option>
                                    <option value="search" <?php if ($lijst[2] == 'search') echo ' selected'; ?>><?= __('Search'); ?></option>
                                    <option value="names" <?php if ($lijst[2] == 'names') echo ' selected'; ?>><?= __('Names'); ?></option>
                                    <option value="history" <?php if ($lijst[2] == 'history') echo ' selected'; ?>><?= __('Today in history'); ?></option>
                                    <option value="favourites" <?php if ($lijst[2] == 'favourites') echo ' selected'; ?>><?= __('Favourites'); ?></option>
                                    <option value="alphabet" <?php if ($lijst[2] == 'alphabet') ' selected'; ?>><?= __('Surnames Index'); ?></option>
                                    <option value="random_photo" <?php if ($lijst[2] == 'random_photo') echo ' selected'; ?>><?= __('Random photo'); ?></option>
                                    <option value="text" <?php if ($lijst[2] == 'text') echo ' selected'; ?>><?= __('Text'); ?></option>
                                    <option value="own_script" <?php if ($lijst[2] == 'own_script') echo ' selected'; ?>><?= __('Own script'); ?></option>
                                    <option value="cms_page" <?php if ($lijst[2] == 'cms_page') echo ' selected'; ?>><?= __('CMS Own pages'); ?></option>
                                    <option value="empty_line" <?php if ($lijst[2] == 'empty_line') echo ' selected'; ?>><?= __('EMPTY LINE'); ?></option>
                                </select>
                            </td>

                            <!-- Extra table column used for extra options -->
                            <td>
                                <?php

                                //if ($lijst[2]=='select_family_tree'){
                                //	echo ' '.__('Only use for multiple family trees.');
                                //}

                                if ($lijst[2] == 'names') {
                                    echo ' ' . __('Columns');
                                    echo ' <select size="1" name="' . $dataDb->setting_id . 'module_option_1">';
                                    echo '<option value="1">1</option>';
                                    $selected = '';
                                    if ($lijst[3] == '2') $selected = ' selected';
                                    echo '<option value="2"' . $selected . '>2</option>';
                                    $selected = '';
                                    if ($lijst[3] == '3') $selected = ' selected';
                                    echo '<option value="3"' . $selected . '>3</option>';
                                    $selected = '';
                                    if ($lijst[3] == '4') $selected = ' selected';
                                    echo '<option value="4"' . $selected . '>4</option>';
                                    echo '</select>';

                                    echo ' ' . __('Rows');
                                    echo ' <select size="1" name="' . $dataDb->setting_id . 'module_option_2">';
                                    echo '<option value="1">1</option>';
                                    $selected = '';
                                    if ($lijst[4] == '2') $selected = ' selected';
                                    echo '<option value="2"' . $selected . '>2</option>';
                                    $selected = '';
                                    if ($lijst[4] == '3') $selected = ' selected';
                                    echo '<option value="3"' . $selected . '>3</option>';
                                    $selected = '';
                                    if ($lijst[4] == '4') $selected = ' selected';
                                    echo '<option value="4"' . $selected . '>4</option>';
                                    $selected = '';
                                    if ($lijst[4] == '5') $selected = ' selected';
                                    echo '<option value="5"' . $selected . '>5</option>';
                                    $selected = '';
                                    if ($lijst[4] == '6') $selected = ' selected';
                                    echo '<option value="6"' . $selected . '>6</option>';
                                    $selected = '';
                                    if ($lijst[4] == '7') $selected = ' selected';
                                    echo '<option value="7"' . $selected . '>7</option>';
                                    $selected = '';
                                    if ($lijst[4] == '8') $selected = ' selected';
                                    echo '<option value="8"' . $selected . '>8</option>';
                                    $selected = '';
                                    if ($lijst[4] == '9') $selected = ' selected';
                                    echo '<option value="9"' . $selected . '>9</option>';
                                    $selected = '';
                                    if ($lijst[4] == '10') $selected = ' selected';
                                    echo '<option value="10"' . $selected . '>10</option>';
                                    $selected = '';
                                    if ($lijst[4] == '11') $selected = ' selected';
                                    echo '<option value="11"' . $selected . '>11</option>';
                                    $selected = '';
                                    if ($lijst[4] == '12') $selected = ' selected';
                                    echo '<option value="12"' . $selected . '>12</option>';
                                    echo '</select>';
                                }

                                if ($lijst[2] == 'text') {
                                    // *** Header text ***
                                    $header = '';
                                    if (isset($lijst[3])) $header = $lijst[3];
                                    echo '<input type="text" placeholder="' . __('Header') . '" name="' . $dataDb->setting_id . 'module_option_1" value="' . $header . '" size="30"><br>';

                                    $module_text = '';
                                    if (isset($lijst[4])) $module_text = $lijst[4];
                                    echo '<textarea rows="4" cols="50" placeholder="' . __('Text') . '" name="' . $dataDb->setting_id . 'module_option_2">' . $module_text . '</textarea><br>';

                                    echo __('Show text block, HTML codes can be used.');
                                }

                                if ($lijst[2] == 'own_script') {
                                    // *** Header text ***
                                    $header = '';
                                    if (isset($lijst[3])) $header = $lijst[3];
                                    echo '<input type="text" placeholder="' . __('Header') . '" name="' . $dataDb->setting_id . 'module_option_1" value="' . $header . '" size="30"><br>';
                                    $module_text = '';
                                    if (isset($lijst[4])) $module_text = $lijst[4];
                                    echo '<input type="text" placeholder="' . __('File name') . '" name="' . $dataDb->setting_id . 'module_option_2" value="' . $module_text . '" size="30"><br>';
                                    echo __('File name (full path) of the file with own script.');
                                }

                                if ($lijst[2] == 'cms_page') {
                                    echo ' <select size="1" name="' . $dataDb->setting_id . 'module_option_1">';
                                    $qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_status!='' ORDER BY page_menu_id, page_order");
                                    while ($pageDb = $qry->fetch(PDO::FETCH_OBJ)) {
                                        //$select=''; if ($lijst[3]==$pageDb->setting_id.'module_option_1'){ $select=' selected'; }
                                        $selected = '';
                                        if ($lijst[3] == $pageDb->page_id) {
                                            $selected = ' selected';
                                        }
                                        echo '<option value="' . $pageDb->page_id . '"' . $selected . '>' . $pageDb->page_title . '</option>';
                                    }
                                    echo '</select>';
                                    echo ' ' . __('Show text from CMS system.');
                                }

                                if ($lijst[2] == 'history') {
                                    echo ' ' . __('View');
                                    echo ' <select size="1" name="' . $dataDb->setting_id . 'module_option_1">';
                                    echo '<option value="with_table">' . __('with table') . '</option>';

                                    $selected = '';
                                    if ($lijst[3] == 'without_table') $selected = ' selected';
                                    echo '<option value="without_table"' . $selected . '>' . __('without table') . '</option>';
                                    echo '</select>';
                                }
                                ?>
                            </td>
                            <td>
                                <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_module=<?= $dataDb->setting_id; ?>">
                                    <img src="<?= CMS_ROOTPATH_ADMIN; ?>images/button_drop.png" border="0" alt="remove"></a>
                            </td>
                        </tr>
                    <?php
                        $teller++;
                    }

                    ?>
                    <!-- Add new module -->
                    <tr bgcolor="green">
                        <input type="hidden" name="module_order" value="<?= $new_number; ?>">
                        <td>
                            <select size="1" name="module_status">
                                <option value="active"><?= __('Active'); ?></option>
                                <option value="inactive"><?= __('Inactive'); ?></option>
                            </select>
                        </td>

                        <td>
                            <select size="1" name="module_column">
                                <option value="left"><?= __('Left'); ?></option>
                                <option value="center"><?= __('Center'); ?></option>
                                <option value="right"><?= __('Right'); ?></option>
                            </select>
                        </td>

                        <td>
                            <select size="1" name="module_item">
                                <option value="select_family_tree"><?= __('Select family tree'); ?></option>
                                <option value="selected_family_tree"><?= __('Selected family tree'); ?></option>
                                <option value="search"><?= __('Search'); ?></option>
                                <option value="names"><?= __('Names'); ?></option>
                                <option value="history"><?= __('Today in history'); ?></option>
                                <option value="favourites"><?= __('Favourites'); ?></option>
                                <option value="alphabet"><?= __('Surnames Index'); ?></option>
                                <option value="random_photo"><?= __('Random photo'); ?></option>
                                <option value="text"><?= __('Text'); ?></option>
                                <option value="own_script"><?= __('Own script'); ?></option>
                                <option value="cms_page"><?= __('CMS Own pages'); ?></option>
                                <option value="empty_line"><?= __('EMPTY LINE'); ?></option>
                            </select>
                        </td>

                        <td><br></td>

                        <td><input type="Submit" name="add_module" value="<?= __('Add'); ?>"></td>
                    </tr>
                <?php
                } else {
                    echo '<tr><td colspan="4">' . __('Database is not yet available.') . '</td></tr>';
                }
                ?>
            </table>
        </form>

        <?= __("If the left column isn't used, the center column will be made large automatically."); ?><br>
        <?php
        // *** Change Link ***
        if (isset($_POST['change_link'])) {
            $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
            while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                $setting_value = $_POST[$dataDb->setting_id . 'own_code'] . "|" . $_POST[$dataDb->setting_id . 'link_text'];
                $sql = "UPDATE humo_settings SET setting_value='" . safe_text_db($setting_value) . "' WHERE setting_id=" . safe_text_db($_POST[$dataDb->setting_id . 'id']);
                $result = $dbh->query($sql);
            }
        }

        // *** Remove link  ***
        if (isset($_GET['remove_link']) and is_numeric($_GET['remove_link'])) {
            $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_id='" . $_GET['remove_link'] . "'");
            $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
            $sql = "DELETE FROM humo_settings WHERE setting_id='" . $dataDb->setting_id . "'";
            $result = $dbh->query($sql);

            // *** Re-order links ***
            $repair_order = $dataDb->setting_order;
            $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order>" . $repair_order);
            while ($itemDb = $item->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_settings SET setting_order='" . ($itemDb->setting_order - 1) . "' WHERE setting_id=" . $itemDb->setting_id;
                $result = $dbh->query($sql);
            }
        }

        // *** Add link ***
        if (isset($_POST['add_link']) and is_numeric($_POST['link_order'])) {
            $setting_value = $_POST['own_code'] . "|" . $_POST['link_text'];
            $sql = "INSERT INTO humo_settings SET setting_variable='link',
                setting_value='" . safe_text_db($setting_value) . "', setting_order='" . safe_text_db($_POST['link_order']) . "'";
            $result = $dbh->query($sql);
        }

        if (isset($_GET['up'])) {
            // *** Search previous link ***
            $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order=" . (safe_text_db($_GET['link_order']) - 1));
            $itemDb = $item->fetch(PDO::FETCH_OBJ);

            // *** Raise previous link ***
            $sql = "UPDATE humo_settings SET setting_order='" . safe_text_db($_GET['link_order']) . "' WHERE setting_id='" . $itemDb->setting_id . "'";

            $result = $dbh->query($sql);
            // *** Lower link order ***
            $sql = "UPDATE humo_settings SET setting_order='" . (safe_text_db($_GET['link_order']) - 1) . "' WHERE setting_id=" . safe_text_db($_GET['id']);

            $result = $dbh->query($sql);
        }
        if (isset($_GET['down'])) {
            // *** Search next link ***
            $item = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' AND setting_order=" . (safe_text_db($_GET['link_order']) + 1));
            $itemDb = $item->fetch(PDO::FETCH_OBJ);

            // *** Lower previous link ***
            $sql = "UPDATE humo_settings SET setting_order='" . safe_text_db($_GET['link_order']) . "' WHERE setting_id='" . $itemDb->setting_id . "'";

            $result = $dbh->query($sql);
            // *** Raise link order ***
            $sql = "UPDATE humo_settings SET setting_order='" . (safe_text_db($_GET['link_order']) + 1) . "' WHERE setting_id=" . safe_text_db($_GET['id']);

            $result = $dbh->query($sql);
        }

        // *** Show all links ***
        ?>
        <h1 align=center><?= __('Homepage favourites'); ?></h1>

        <form method='post' action='index.php'>
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="menu_admin" value="settings_homepage">

            <table class="humo standard" border="1">
                <tr class="table_header_large">
                    <th class="table_header" colspan="4"><?= __('Show list of favourites in homepage'); ?></th>
                </tr>

                <tr class="table_header">
                    <th>Nr.</th>
                    <th><?= __('Own code'); ?></th>
                    <th><?= __('Description'); ?></th>
                    <th><input type="Submit" name="change_link" value="<?= __('Change'); ?>"></th>
                </tr>
                <?php
                $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link' ORDER BY setting_order");
                // *** Number for new link ***
                $count_links = 0;
                if ($datasql->rowCount()) $count_links = $datasql->rowCount();
                $new_number = 1;
                if ($count_links) $new_number = $count_links + 1;
                if ($datasql) {
                    $teller = 1;
                    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
                        $lijst = explode("|", $dataDb->setting_value);
                ?>
                        <tr>
                            <td>
                                <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;remove_link=<?= $dataDb->setting_id; ?>">
                                    <img src="<?= CMS_ROOTPATH_ADMIN; ?>images/button_drop.png" border="0" alt="remove"></a>

                                <input type="hidden" name="<?= $dataDb->setting_id; ?>id" value="<?= $dataDb->setting_id; ?>"><?= __('Link') . ' ' . $teller; ?>
                                <?php
                                if ($dataDb->setting_order != '1') {
                                    echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;up=1&amp;link_order=' . $dataDb->setting_order .
                                        '&amp;id=' . $dataDb->setting_id . '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_up.gif" border="0" alt="up"></a>';
                                }
                                if ($dataDb->setting_order != $count_links) {
                                    echo ' <a href="index.php?page=settings&amp;menu_admin=settings_homepage&amp;down=1&amp;link_order=' . $dataDb->setting_order . '&amp;id=' .
                                        $dataDb->setting_id . '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_down.gif" border="0" alt="down"></a>';
                                }
                                ?>
                            </td>
                            <td><input type="text" name="<?= $dataDb->setting_id; ?>own_code" value="<?= $lijst[0]; ?>" size="5"></td>
                            <td><input type="text" name="<?= $dataDb->setting_id; ?>link_text" value="<?= $lijst[1]; ?>" size="20"></td>
                            <td><br></td>
                        </tr>
                    <?php
                        $teller++;
                    }

                    // *** Add new link ***
                    ?>
                    <tr bgcolor="green">
                        <td><br></td>
                        <input type="hidden" name="link_order" value="' . $new_number . '">
                        <td><input type="text" name="own_code" value="Code" size="5"></td>
                        <td><input type="text" name="link_text" value="<?= __('Owner of tree'); ?>" size="20"></td>
                        <td><input type="Submit" name="add_link" value="<?= __('Add'); ?>"></td>
                    </tr>
                <?php
                } else {
                    echo '<tr><td colspan="4">' . __('Database is not yet available.') . '</td></tr>';
                }
                ?>
            </table>
        </form>

        <?= __('Own code is the code that has to be entered in your genealogy program under "own code or REFN"
<p>Do the following:<br>
1) In your genealogy program, put a code. For example, with the patriarch enter a code "patriarch".<br>
2) Enter the same code in this table (multiple codes are possible)<br>
3) After processing the GEDCOM file, an extra link will appear in the main menu, i.e. to the patriarch!<br>'); ?>

        <?php
        // *** Slideshow ***
        $slideshow_01 = explode('|', $humo_option["slideshow_01"]);
        $slideshow_02 = explode('|', $humo_option["slideshow_02"]);
        $slideshow_03 = explode('|', $humo_option["slideshow_03"]);
        $slideshow_04 = explode('|', $humo_option["slideshow_04"]);
        ?>
        <br>
        <form method="post" action="index.php">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="menu_admin" value="settings_homepage">
            <table class="humo" border="1">

                <tr class="table_header">
                    <th colspan="2"><?= __('Slideshow on the homepage'); ?> <input type="Submit" name="save_option2" value="<?= __('Change'); ?>"></th>
                </tr>

                <tr>
                    <td colspan="2"><?= __('This option shows a slideshow at the homepage. Put the images in the media/slideshow/ folder at the website.<br>Example of image link:'); ?> <b>media/slideshow/slide01.jpg</b><br>
                        <?= __('Images size should be about:'); ?> <b>950 x 170 pixels.</b>
                    </td>
                </tr>

                <tr>
                    <td style="white-space:nowrap;"><?= __('Show slideshow on the homepage'); ?>?</td>
                    <td>
                        <select size="1" name="slideshow_show">
                            <option value="y"><?= __('Yes'); ?></option>
                            <option value="n" <?php if ($humo_option["slideshow_show"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                        </select>
                    </td>
                </tr>

                <!-- Picture 1 -->
                <tr>
                    <td><?= __('Link to image'); ?> 1<br><?= __('Link description'); ?> 1</td>
                    <td><input type="text" name="slideshow_slide_01" value="<?= $slideshow_01[0]; ?>" size="40"> media/slideshow/slide01.jpg<br>
                        <input type="text" name="slideshow_text_01" value="<?= $slideshow_01[1]; ?>" size="40">
                    </td>
                </tr>
                <!-- Picture 2 -->
                <tr>
                    <td><?= __('Link to image'); ?> 2<br><?= __('Link description'); ?> 2</td>
                    <td><input type="text" name="slideshow_slide_02" value="<?= $slideshow_02[0]; ?>" size="40"> media/slideshow/slide02.jpg<br>
                        <input type="text" name="slideshow_text_02" value="<?= $slideshow_02[1]; ?>" size="40">
                    </td>
                </tr>
                <!-- Picture 3 -->
                <tr>
                    <td><?= __('Link to image'); ?> 3<br><?= __('Link description'); ?> 3</td>
                    <td><input type="text" name="slideshow_slide_03" value="<?= $slideshow_03[0]; ?>" size="40"> media/slideshow/slide03.jpg<br>
                        <input type="text" name="slideshow_text_03" value="<?= $slideshow_03[1]; ?>" size="40">
                    </td>
                </tr>
                <!-- Picture 4 -->
                <tr>
                    <td><?= __('Link to image'); ?> 4<br><?= __('Link description'); ?> 4</td>
                    <td><input type="text" name="slideshow_slide_04" value="<?= $slideshow_04[0]; ?>" size="40"> media/slideshow/slide04.jpg<br>
                        <input type="text" name="slideshow_text_04" value="<?= $slideshow_04[1]; ?>" size="40">
                    </td>
                </tr>

            </table>
        </form><br><br>
    <?php
    }

    // *** Show special settings ***
    if (isset($menu_admin) and $menu_admin == 'settings_special') {
        $checked_death_char = '';
        if (isset($humo_option['death_char']) and $humo_option['death_char'] == "y") {
            $checked_death_char = " checked ";
        }

        $checked_admin_hebdate = '';
        if (isset($humo_option['admin_hebdate']) and $humo_option['admin_hebdate'] == "y") {
            $checked_admin_hebdate = " checked ";
        }

    ?>
        <form method="post" action="index.php">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="menu_admin" value="settings_special">
            <table class="humo" border="1">
                <tr class="table_header">
                    <th colspan="2"><?= __('Special settings'); ?></th>
                </tr>

                <tr>
                    <td><?= __('Jewish settings'); ?></td>
                    <td>
                        <u><?= __('Display settings'); ?>:</u><br>
                        <input type="checkbox" id="death_char" value="y" name="death_char" <?= $checked_death_char; ?>> <label for="death_char"><?= __('Change all &#134; characters into &infin; characters in all language files'); ?> (<?= __('unchecking and saving will revert to the cross sign'); ?>)</label><br>

                        <input type="checkbox" id="admin_hebdate" value="y" name="admin_hebdate" <?= $checked_admin_hebdate; ?>> <label for="admin_hebdate"><?= __('Display Hebrew date after Gregorian date: 23 Dec 1980 (16 Tevet 5741)'); ?></label><br>
                        <?php

                        $checked = '';
                        if (isset($humo_option['david_stars']) and $humo_option['david_stars'] == "y") {
                            $checked = " checked ";
                        }
                        echo '<input type="checkbox" id="david_stars" value="y" name="david_stars" ' . $checked . '>  <label for="david_stars">' . __('Place yellow Stars of David before holocaust victims in lists and reports') . '</label><br>';

                        $checked = '';
                        if (isset($humo_option['death_shoa']) and $humo_option['death_shoa'] == "y") {
                            $checked = " checked ";
                        }
                        echo '<input type="checkbox" id="death_shoa" value="y" name="death_shoa" ' . $checked . '>  <label for="death_shoa">' . __('Add: "cause of death: murdered" to holocaust victims') . '</label><br>';
                        echo '<u>' . __('Editor settings') . ':</u><br>';

                        $checked = '';
                        if (isset($humo_option['admin_hebnight']) and $humo_option['admin_hebnight'] == "y") {
                            $checked = " checked ";
                        }
                        echo '<input type="checkbox" id="admin_hebnight" value="y" name="admin_hebnight" ' . $checked . '>  <label for="admin_hebnight">' . __('Add "night" checkbox next to Gregorian dates to calculate Hebrew date correctly') . '</label><br>';

                        $checked = '';
                        if (isset($humo_option['admin_hebname']) and $humo_option['admin_hebname'] == "y") {
                            $checked = " checked ";
                        }
                        echo '<input type="checkbox" id="admin_hebname" value="y" name="admin_hebname" ' . $checked . '>  <label for="admin_hebname">' . __('Add field for Hebrew name in name section of editor (instead of in "events" list)') . '</label><br>';

                        $checked = '';
                        if (isset($humo_option['admin_brit']) and $humo_option['admin_brit'] == "y") {
                            $checked = " checked ";
                        }
                        echo '<input type="checkbox" id="admin_brit" value="y" name="admin_brit" ' . $checked . '>  <label for="admin_brit">' . __('Add field for Brit Mila under birth fields (instead of in "events" list)') . '</label><br>';

                        $checked = '';
                        if (isset($humo_option['admin_barm']) and $humo_option['admin_barm'] == "y") {
                            $checked = " checked ";
                        }
                        echo '<input type="checkbox" id="admin_barm" value="y" name="admin_barm" ' . $checked . '>  <label for="admin_barm">' . __('Add field for Bar/ Bat Mitsva before baptise fields (instead of in "events" list)') . '</label>';
                        echo '<br><input type="Submit" style="margin:3px" name="save_option3" value="' . __('Change') . '">';
                        ?>
                    </td>
                </tr>

                <tr>
                    <td><?= __('Sitemap'); ?></td>
                    <td>
                        <b><?= __('Sitemap'); ?></b> <br>
                        <?= __('A sitemap can be used for quick indexing of the family screens by search engines. Add the sitemap link to a search engine (like Google), or add the link in a robots.txt file (in the root folder of your website). Example of robots.txt file, sitemap line:<br>
Sitemap: http://www.yourwebsite.com/humo-gen/sitemap.php'); ?>
                        <br><a href="../sitemap.php"><?= __('Sitemap'); ?></a>
                    </td>
                </tr>

            </table>
        </form>
    <?php
    }
    ?>
</div>
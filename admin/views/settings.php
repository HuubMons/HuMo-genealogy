<?php
$folder = opendir('../styles/');
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
            <th colspan="2"><?= __('General settings'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
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
            <th colspan="2"><?= __('Search engine settings'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
        </tr>

        <tr class="humo_color">
            <td valign="top">url_rewrite<br><?= __('Improve indexing of search engines (like Google)'); ?></td>
            <td>
                <select size="1" name="url_rewrite">
                    <option value="j"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["url_rewrite"] != 'j') echo ' selected'; ?>><?= __('No'); ?></option>
                </select> <b><?= __('ATTENTION: the Apache module "mod_rewrite" has to be installed!'); ?></b><br>
                URL&nbsp;&nbsp;: http://www.website.nl/humo-gen/index.php?page=family&amp;tree_id=1&amp;id=F12<br>
                <?= __('becomes:'); ?> http://www.website.nl/humo-gen/family/1/F12<br>
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
            <th colspan="2"><?= __('Contact & registration form settings'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
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
            <th colspan="2"><?= __('Email Settings'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
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
            <th colspan="2"><?= __('International settings'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
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
                        if (is_dir('../languages/' . $langs[$i][1] . '/timelines/')) {
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
                $folder = @opendir('../languages/' . $time_lang . '/timelines/');
                // *** Default language = english ***
                if ($time_lang == 'default_timelines') $folder = @opendir('../languages/' . $time_lang);
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
            <th colspan="2"><?= __('Settings Main Menu'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
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
            <th colspan="2"><?= __('Settings family page'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
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
            <th colspan="2"><?= __('Watermark text in PDF file'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
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
            <th colspan="2"><?= __('Display for One Name Study web sites'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
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
            <th colspan="2">'<?= __('Save settings'); ?> <input type="submit" name="save_option" value="<?= __('Change'); ?>"></th>
        </tr>
    </table>
</form>
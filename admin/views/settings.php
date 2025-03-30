<?php
$folder = opendir('../styles/');
while (false !== ($file = readdir($folder))) {
    if (substr($file, -4, 4) === '.css') {
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
<form method="post" action="index.php" enctype="multipart/form-data" class="p-2">
    <input type="hidden" name="page" value="<?= $page; ?>">

    <div class="genealogy_search p-2">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('General settings'); ?></h4>
            </div>
            <div class="col-md-4">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Default skin'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="default_skin" class="form-select form-select-sm">
                    <option value="">Standard</option>
                    <?php
                    for ($i = 0; $i < count($theme_folder); $i++) {
                        $theme = str_replace(".css", "", $theme_folder[$i]);
                    ?>
                        <option value="<?= $theme; ?>" <?= $humo_option['default_skin'] == $theme ? 'selected' : ''; ?>><?= $theme; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= sprintf(__('Standard language %s'), 'HuMo-genealogy'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="default_language" class="form-select form-select-sm">
                    <?php
                    if ($langs) {
                        for ($i = 0; $i < count($langs); $i++) {
                    ?>
                            <option value="<?= $langs[$i][1]; ?>" <?= $humo_option['default_language'] == $langs[$i][1] ? 'selected' : ''; ?>><?= $langs[$i][0]; ?></option>
                    <?php
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Standard language admin menu'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="default_language_admin" class="form-select form-select-sm">
                    <?php
                    if ($langs_admin) {
                        for ($i = 0; $i < count($langs_admin); $i++) {
                    ?>
                            <option value="<?= $langs_admin[$i][1]; ?>" <?= $humo_option['default_language_admin'] == $langs_admin[$i][1] ? 'selected' : ''; ?>><?= $langs_admin[$i][0]; ?></option>
                    <?php
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= __('Change favicon icon'); ?>
            </div>
            <div class="col-md-auto">
                <div class="input-group">
                    <input type="file" name="upload_favicon" id="formFile" class="form-control">
                    <input type="submit" name="save_option" title="submit" value="<?= __('Upload'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-auto">
                <label for="formFile" class="form-label"><?= sprintf(__('Upload favicon.ico file. File size max: %1$d kB.'), '100'); ?></label>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= __('Scripts in &lt;head&gt; section for all pages'); ?>
            </div>
            <div class="col-md-auto">
                <textarea cols="80" rows="1" name="text_header" class="form-control form-control-sm"><?= htmlentities($humo_option["text_header"], ENT_NOQUOTES); ?></textarea>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-auto">
                <?= __('Can be used for statistics, counter, etc.'); ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                <?= __('Text in footer for all pages'); ?>
            </div>
            <div class="col-md-auto">
                <textarea cols="80" rows="1" name="text_footer" class="form-control form-control-sm"><?= htmlentities($humo_option["text_footer"], ENT_NOQUOTES); ?></textarea>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-auto">
                <?= __('Can be used for statistics, counter, etc. It\'s possible to use HTML codes!'); ?>
            </div>
        </div>

        <!-- Debug options -->
        <div class="row mb-2">
            <div class="col-md-4">
                <?= sprintf(__('Debug %s front pages'), 'HuMo-genealogy'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="debug_front_pages" class="form-select form-select-sm">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["debug_front_pages"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </div>
            <div class="col-md-auto">
                <?= sprintf(__('Only use this option to debug problems in %s.'), 'HuMo-genealogy'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= sprintf(__('Debug %s admin pages'), 'HuMo-genealogy'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="debug_admin_pages" class="form-select form-select-sm">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["debug_admin_pages"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </div>
            <div class="col-md-auto">
                <?= sprintf(__('Only use this option to debug problems in %s.'), 'HuMo-genealogy'); ?>
            </div>
        </div>
    </div>

    <div class="genealogy_search p-2 my-2">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('Search engine settings'); ?></h4>
            </div>
            <div class="col-md-4">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row">
            <div class="col-md-4">
                url_rewrite
            </div>
            <div class="col-md-auto">
                <select size="1" name="url_rewrite" class="form-select form-select-sm">
                    <option value="j"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["url_rewrite"] != 'j') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </div>
            <div class="col-md-auto">
                <b><?= __('ATTENTION: the Apache module "mod_rewrite" has to be installed!'); ?></b>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-auto">
                <?= __('Improve indexing of search engines (like Google)'); ?><br>
                URL&nbsp;&nbsp;: http://www.website.nl/humo-gen/index.php?page=family&amp;tree_id=1&amp;id=F12<br>
                <?= __('becomes:'); ?> http://www.website.nl/humo-gen/family/1/F12<br>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Stop search engines'); ?>
            </div>
            <div class="col-md-1">
                <select size="1" name="searchengine" class="form-select form-select-sm">
                    <option value="j"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["searchengine"] != 'j') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </div>
            <div class="col-md-6">
                <textarea cols="80" rows=1 name="robots_option" class="form-control form-control-sm"><?= htmlentities($humo_option["robots_option"], ENT_NOQUOTES); ?></textarea>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Stop search engines'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="searchengine_cms_only" class="form-select form-select-sm">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["searchengine_cms_only"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
                <?= __('Search engines:<br>Hide family tree (no indexing)<br>Show frontpage and CMS pages'); ?>
            </div>
        </div>
    </div>

    <div class="genealogy_search p-2 my-2">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('Contact & registration form settings'); ?></h4>
            </div>
            <div class="col-md-4">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Block spam question'); ?>
            </div>
            <div class="col-md-8">
                <input type="text" name="block_spam_question" value="<?= htmlentities($humo_option["block_spam_question"], ENT_NOQUOTES); ?>" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Block spam answer'); ?>
            </div>
            <div class="col-md-8">
                <input type="text" name="block_spam_answer" value="<?= htmlentities($humo_option["block_spam_answer"], ENT_NOQUOTES); ?>" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Mail form: use spam question'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="use_spam_question" class="form-select form-select-sm">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["use_spam_question"] != 'y') ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Mail form: use newsletter question'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="use_newsletter_question" class="form-select form-select-sm">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["use_newsletter_question"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </div>
            <div class="col-md-auto">
                <?= __('Adds the question: "Receive newsletter: yes/ no" to the mailform.'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Visitors can register'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="visitor_registration" class="form-select form-select-sm">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["visitor_registration"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </div>

            <div class="col-md-auto">
                <?= __('Default user-group for new users:'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="visitor_registration_group" class="form-select form-select-sm">
                    <?php while ($groupDb = $groupresult->fetch(PDO::FETCH_OBJ)) { ?>
                        <option value="<?= $groupDb->group_id; ?>" <?= $humo_option["visitor_registration_group"] == $groupDb->group_id ? 'selected' : ''; ?>><?= $groupDb->group_name; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <!-- Using HTML 5 email check -->
        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Registration form: e-mail address'); ?>
            </div>
            <div class="col-md-4">
                <input type="email" name="general_email" value="<?= $humo_option["general_email"]; ?>" size="40" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <?= __('Send registration form to this e-mail address.'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Visitor registration: use spam question'); ?>
            </div>

            <div class="col-md-auto">
                <select size="1" name="registration_use_spam_question" class="form-select form-select-sm">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["registration_use_spam_question"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </div>
        </div>

        <!-- Using HTML 5 email check -->
        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('Password forgotten e-mail address'); ?>
            </div>
            <div class="col-md-4">
                <input type="email" name="password_retrieval" value="<?= $humo_option["password_retrieval"]; ?>" size="40" placeholder="no-reply@your-website.com" class="form-control form-control-sm">
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-auto">
                <?= __('To enable password forgotten option: set a sender e-mail address.'); ?>
            </div>
        </div>
    </div>

    <div class="genealogy_search p-2 my-2">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('Email Settings'); ?></h4>
            </div>
            <div class="col-md-4">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Email Settings'); ?></div>
            <div class="col-md-8"><?= __('TIP: mail will work without changing these parameters at most hosting providers.'); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Mail: sender'); ?>
            </div>

            <div class="col-md-4">
                <input type="text" name="email_sender" value="<?= $humo_option["email_sender"]; ?>" size="32" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto"><?= __('Gmail: [email_address]@gmail.com'); ?></div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-auto"><b><?= __('If filled in: will be used as mail sender.'); ?></b></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Mail: configuration'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="mail_auto" class="form-select form-select-sm">
                    <option value="auto" <?php if ($humo_option["mail_auto"] == 'auto') echo ' selected'; ?>><?= __('auto'); ?></option>
                    <option value="manual" <?php if ($humo_option["mail_auto"] == 'manual') echo ' selected'; ?>><?= __('manual'); ?></option>
                </select>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-auto">
                <?= __('Setting: "auto" = use settings below.<br>Setting: "manual" = change settings in /include/mail.php'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Mail: username'); ?>
            </div>
            <div class="col-md-4">
                <input type="text" name="email_user" value="<?= $humo_option["email_user"]; ?>" size="32" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <?= __('Gmail: [email_address]@gmail.com'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Mail: password'); ?>
            </div>
            <div class="col-md-4">
                <input type="text" name="email_password" value="<?= $humo_option["email_password"]; ?>" size="32" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('SMTP: mail server'); ?>
            </div>
            <div class="col-md-4">
                <input type="text" name="smtp_server" value="<?= $humo_option["smtp_server"]; ?>" size="32" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <?= __('Gmail: smtp.gmail.com'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('SMTP: port'); ?>
            </div>
            <div class="col-md-2">
                <select size="1" name="smtp_port" class="form-select form-select-sm">
                    <option value="25" <?php if ($humo_option["smtp_port"] == '25') echo ' selected'; ?>>25</option>
                    <option value="465" <?php if ($humo_option["smtp_port"] == '465') echo ' selected'; ?>>465</option>
                    <option value="587" <?php if ($humo_option["smtp_port"] == '587') echo ' selected'; ?>>587</option>
                </select>
            </div>
            <div class="col-md-auto">
                <?= __('Gmail: 587'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('SMTP: authentication'); ?>
            </div>
            <div class="col-md-2">
                <select size="1" name="smtp_auth" class="form-select form-select-sm">
                    <option value="true" <?php if ($humo_option["smtp_auth"] == 'true') echo ' selected'; ?>><?= __('true'); ?></option>
                    <option value="false" <?php if ($humo_option["smtp_auth"] == 'false') echo ' selected'; ?>><?= __('false'); ?></option>
                </select>
            </div>
            <div class="col-md-auto">
                <?= __('Gmail: true'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('SMTP: encryption type'); ?>
            </div>
            <div class="col-md-2">
                <select size="1" name="smtp_encryption" class="form-select form-select-sm">
                    <option value="tls" <?php if ($humo_option["smtp_encryption"] == 'tls') echo ' selected'; ?>>TLS</option>
                    <option value="ssl" <?php if ($humo_option["smtp_encryption"] == 'ssl') echo ' selected'; ?>>SSL</option>';
                </select>
            </div>
            <div class="col-md-auto">
                <?= __('Gmail: TLS'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('SMTP: debugging'); ?>
            </div>
            <div class="col-md-2">
                <select size="1" name="smtp_debug" class="form-select form-select-sm">
                    <option value="0" <?php if ($humo_option["smtp_debug"] == '0') echo ' selected'; ?>><?= __('Off'); ?></option>
                    <option value="1" <?php if ($humo_option["smtp_debug"] == '1') echo ' selected'; ?>><?= __('Client'); ?></option>
                    <option value="2" <?php if ($humo_option["smtp_debug"] == '2') echo ' selected'; ?>><?= __('Client and Server'); ?></option>
                </select>
            </div>
        </div>
    </div>

    <div class="genealogy_search p-2 my-2">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('International settings'); ?></h4>
            </div>
            <div class="col-md-4">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Timezone'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="timezone" class="form-select form-select-sm">
                    <?php foreach ($zones_array as $t) { ?>
                        <option value="<?= $t['zone']; ?>" <?= $humo_option["timezone"] == $t['zone'] ? 'selected' : ''; ?>><?= $t['diff_from_GMT']; ?> - <?= $t['zone']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Minimum characters in search box'); ?>
            </div>
            <div class="col-md-1">
                <input type="text" name="min_search_chars" value="<?= $humo_option["min_search_chars"]; ?>" size="4" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <?= __('Minimum characters in search boxes (standard value=3. For Chinese set to 1).'); ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Date display'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="date_display" class="form-select form-select-sm">
                    <option value="eu" <?php if ($humo_option["date_display"] == 'eu') echo ' selected'; ?>><?= __('Europe/Global - 5 Jan 1787'); ?></option>
                    <option value="us" <?php if ($humo_option["date_display"] == 'us') echo ' selected'; ?>><?= __('USA - Jan 5, 1787'); ?></option>
                    <option value="ch" <?php if ($humo_option["date_display"] == 'ch') echo ' selected'; ?>><?= __('China - 1787-01-05'); ?></option>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Order of names in reports'); ?>
            </div>
            <div class="col-md-auto">
                <select size="1" name="name_order" class="form-select form-selectt-sm">
                    <option value="western" <?php if ($humo_option["name_order"] == 'western') echo ' selected'; ?>><?= __('Western'); ?></option>
                    <option value="chinese" <?php if ($humo_option["name_order"] == 'chinese') echo ' selected'; ?>><?= __('Chinese') . "/ " . __('Hungarian'); ?></option>
                </select>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-auto">
                <?= __('Western - reports: John Smith, lists: Smith, John.'); ?><br>
                <?= __('Chinese 中文 - reports and lists: 刘 理想'); ?><br>
                <?= __('Hungarian - reports and lists: Smith John'); ?>.
            </div>
        </div>

        <!-- timeline default -->
        <div id="timeline_anchor" class="row mb-2">
            <div class="col-md-4">
                <?= __('Default timeline file (per language)'); ?>
            </div>
            <div class="col-md-auto">
                <!-- First select language -->
                <?php if ($langs) { ?>
                    <select onChange="window.location ='index.php?page=settings&timeline_language=' + this.value + '#timeline_anchor'; " size="1" name="timeline_language" class="form-select form-select-sm">
                        <option value="default_timelines"><?= __('Default'); ?></option>
                        <?php
                        for ($i = 0; $i < count($langs); $i++) {
                            if (is_dir('../languages/' . $langs[$i][1] . '/timelines/')) {
                        ?>
                                <option value="<?= $langs[$i][1]; ?>" <?= $settings['time_lang'] == $langs[$i][1] ? 'selected' : ''; ?>><?= $langs[$i][0]; ?></option>
                        <?php
                            }
                        }
                        ?>
                    </select>
                <?php
                }

                // *** First select language, then the timeline files of that language is shown ***
                $folder = @opendir('../languages/' . $settings['time_lang'] . '/timelines/');
                // *** Default language = english ***
                if ($settings['time_lang'] == 'default_timelines') $folder = @opendir('../languages/' . $settings['time_lang']);
                if ($folder !== false) {  // no use showing the option if we can't access the timeline folder
                    while (false !== ($file = readdir($folder))) {
                        if (substr($file, -4, 4) == '.txt') {
                            $timeline_files[] = $file;
                        }
                    }
                }
                ?>
            </div>

            <?php if ($folder !== false) { ?>
                <div class="col-md-auto">
                    <select size="1" name="default_timeline" class="form-select form-select-sm">
                        <?php
                        for ($i = 0; $i < count($timeline_files); $i++) {
                            $timeline = str_replace(".txt", "", $timeline_files[$i]);
                            $select = "";
                            if (strpos($humo_option['default_timeline'], $settings['time_lang'] . "!" . $timeline) !== false) {
                                $select = ' selected';
                            }
                        ?>
                            <option value="<?= $settings['time_lang']; ?>!<?= $timeline; ?>@" <?= $select; ?>><?= $timeline; ?></option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-md-auto">
                    <?= __('First select language, then select the default timeline for that language.'); ?>
                </div>
            <?php } ?>

        </div>
        <?php if ($folder !== false) @closedir($folder); ?>
    </div>

    <div class="genealogy_search p-2 my-2">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('Settings Main Menu'); ?></h4>
            </div>
            <div class="col-md-4">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Website name'); ?>
            </div>
            <div class="col-md-6">
                <input type="text" name="database_name" value="<?= $humo_option["database_name"]; ?>" size="40" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('Use logo image instead of text'); ?>
            </div>
            <div class="col-md-auto">
                <div class="input-group">
                    <input type="file" name="upload_logo" id="formFile" class="form-control">
                    <input type="submit" name="save_option" title="submit" value="<?= __('Upload'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-auto">
                <?= printf(__('Upload logo image. Recommended size: 165 x 25 px. Picture max: %1$d MB.'), '1'); ?>
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('Link homepage'); ?>
            </div>

            <div class="col-md-4">
                <input type="text" name="homepage" value="<?= $humo_option["homepage"]; ?>" size="40" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <span style="white-space:nowrap;"><?= __('(link to this site including http://)'); ?></span>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-4">
                <?= __('Link description'); ?>
            </div>
            <div class="col-md-4">
                <input type="text" name="homepage_description" value="<?= $humo_option["homepage_description"]; ?>" size="40" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('Link for birthdays RSS'); ?>
            </div>
            <div class="col-md-4">
                <input type="text" name="rss_link" value="<?= $humo_option["rss_link"]; ?>" size="40" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <?= __('(link to this site including http://)'); ?>
            </div>
        </div>
        <div class="row">
            <div class="col-md-4"></div>
            <div class="col-md-auto">
                <i><?= __('This option can be turned on or off in the user groups.'); ?></i>
            </div>
        </div>
    </div>

    <div class="genealogy_search p-2 my-2">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('Settings family page'); ?></h4>
            </div>
            <div class="col-md-4">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('Number of generations in descendant report'); ?>
            </div>
            <div class="col-md-1">
                <input type="text" name="descendant_generations" value="<?= $humo_option["descendant_generations"]; ?>" size="4" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <?= __('Show number of generation in descendant report (standard value=4).'); ?>
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('Number of persons in search results'); ?>
            </div>
            <div class="col-md-1">
                <input type="text" name="show_persons" value="<?= $humo_option["show_persons"]; ?>" size="4" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <?= __('Show number of persons in search results (standard value=30).'); ?>
            </div>
        </div>
    </div>

    <div class="genealogy_search p-2 my-2">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('Watermark text in PDF file'); ?></h4>
            </div>
            <div class="col-md-4">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('Watermark text in PDF file'); ?>
            </div>
            <div class="col-md-4">
                <input type="text" name="watermark_text" value="<?= $humo_option["watermark_text"]; ?>" size="40" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <?= __('Watermark text (clear to remove watermark)'); ?>
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('Watermark RGB text color'); ?>
            </div>
            <div class="col-md-auto">
                R:
            </div>
            <div class="col-md-auto">
                <input type="text" name="watermark_color_r" value="<?= $humo_option["watermark_color_r"]; ?>" size="4" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                G:
            </div>
            <div class="col-md-auto">
                <input type="text" name="watermark_color_g" value="<?= $humo_option["watermark_color_g"]; ?>" size="4" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                B:
            </div>
            <div class="col-md-auto">
                <input type="text" name="watermark_color_b" value="<?= $humo_option["watermark_color_b"]; ?>" size="4" class="form-control form-control-sm">
            </div>
            <div class="col-md-auto">
                <?= __('Default values: R = 224, G = 224, B = 224.'); ?>
            </div>
        </div>
    </div>

    <div class="genealogy_search p-2 my-2">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('Display for One Name Study web sites'); ?></h4>
            </div>
            <div class="col-md-4">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('One Name Study display'); ?>?
            </div>
            <div class="col-md-auto">
                <select size="1" name="one_name_study" class="form-select form-select-sm">
                    <option value="y"><?= __('Yes'); ?></option>
                    <option value="n" <?php if ($humo_option["one_name_study"] != 'y') echo ' selected'; ?>><?= __('No'); ?></option>
                </select>
            </div>
            <div class="col-md-auto">
                <?= __('Only use this option if you\'re doing a "One Name Study" project.'); ?>
            </div>
        </div>

        <div class="row mb-1">
            <div class="col-md-4">
                <?= __('Enter the One Name of this site'); ?>
            </div>
            <div class="col-md-6">
                <input type="text" name="one_name_thename" value="<?= $humo_option["one_name_thename"]; ?>" size="40" class="form-control form-control-sm">
            </div>
        </div>
    </div>

    <div class="genealogy_search p-2 my-2" id="country_statistics">
        <div class="row mb-2">
            <div class="col-md-4">
                <h4><?= __('Settings for country statistics'); ?></h4>
            </div>
            <div class="col-md-8">
                <input type="submit" name="save_option" value="<?= __('Change'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"></div>
            <div class="col-md-8">
                <b><?= __('These are external services. It\'s recommended to check reliability of the services before use.'); ?></b>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Disable collection of country statistics.'); ?></div>
            <div class="col-md-8">
                <div class="form-check">
                    <input type="radio" value="ip_api_collection" name="ip_api" id="ip_api_collection" class="form-check-input" <?= $humo_option['ip_api_collection'] != '' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="ip_api_enabled"><?= __('Disable collection of country statistics.'); ?></label>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('GeoPlugin: without license.'); ?></div>
            <div class="col-md-8">
                <div class="form-check">
                    <input type="radio" value="ip_api_geoplugin_old" name="ip_api" id="ip_api_geoplugin_old" class="form-check-input" <?= $humo_option['ip_api_geoplugin_old'] == 'ena' ? 'checked' : ''; ?>>
                    <label class="form-check-label" for="ip_api_geoplugin_old"><?= __('Use geoplugin.com.'); ?> <?= __('Default plugin in 2025, but will probably stop working in the future.'); ?></label>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('GeoPlugin: free license = max. 10 requests a day allowed.'); ?></div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="radio" value="ip_api_geoplugin" name="ip_api" id="ip_api_geoplugin" class="form-check-input" <?= $humo_option['geoplugin_checked'] == 'ena' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ip_api_geoplugin"><?= __('Use geoplugin.com.'); ?></label>
                        </div>
                        <label for="geoplugin">&nbsp;</label>
                    </div>
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><?= __('API key'); ?></span>
                            <input type="text" name="geoplugin_key" id="geoplugin_key" value="<?= $humo_option['geoplugin_key']; ?>" class="form-control form-control-sm">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('ip-api: free license = max. 45 requests an hour allowed.'); ?></div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="radio" value="ip_api_ip_api" name="ip_api" id="ip_api_ip_api" class="form-check-input" <?= $humo_option['ip_api_checked'] == 'ena' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ip_api_ip_api"><?= __('Use ip-api.com.'); ?></label>
                        </div>
                        <label for="ip_api_ip_api">&nbsp;</label>
                    </div>
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><?= __('API key'); ?></span>
                            <input type="text" name="ip_api_key" id="ip_api_key" value="" class="form-control form-control-sm" disabled>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- freeipapi.com -->
        <div class="row mb-2">
            <div class="col-md-4"><?= __('FreeIPAPI: free license = max. 60 requests a minute allowed.'); ?></div>
            <div class="col-md-8">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-check">
                            <input type="radio" value="ip_api_freeipapi" name="ip_api" id="ip_api_freeipapi" class="form-check-input" <?= $humo_option['freeipapi_checked'] == 'ena' ? 'checked' : ''; ?>>
                            <label class="form-check-label" for="ip_api_freeipapi"><?= __('Use FreeIPAPI.com.'); ?></label>
                        </div>
                        <label for="freeipapi">&nbsp;</label>
                    </div>
                    <div class="col-md-8">
                        <div class="input-group">
                            <span class="input-group-text"><?= __('API key'); ?></span>
                            <input type="text" name="freeipapi_key" id="freeipapi_key" value="<?= $humo_option['freeipapi_key']; ?>" class="form-control form-control-sm" disabled>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    </div>

</form>
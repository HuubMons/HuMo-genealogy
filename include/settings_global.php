<?php
// >>>> July 2022: also change admin\update\version_check.txt. In use for update through GitHub.

// *** Version line, DO NOT CHANGE THIS LINE ***
// Version nummering: 1.1.1.1 (main number, sub number, update, etc.)
$humo_option["version"] = '6.7.9a';  // Version line, DO NOT CHANGE THIS LINE
// >>>> July 2022: also change admin\update\version_check.txt. In use for update through GitHub.

// *** Beta (not stable enough for production, but it's functional ***
//$humo_option["version"]='BETA version 28 nov. 2019';  // Version line, DO NOT CHANGE THIS LINE
//$humo_option["version"]='TEST version 11 oct. 2011';  // Version line, DO NOT CHANGE THIS LINE

// *** Version date, needed for update check ***
//$humo_option["version_date"]='2019-09-01';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE
$humo_option["version_date"] = '2024-11-12';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE
// >>>> July 2022: also change admin\update\version_check.txt. In use for update through GitHub.

// *** Test lines for update procedure ***
//$humo_option["version_date"]='2012-01-01';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE
//$humo_option["version_date"]='2012-11-30';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE


// *** Database updates (can be moved to database update script later) ***
// ..............................


// *** If needed: translate setting_variabele into setting variable ***
$update_setting_qry = $dbh->query("SELECT * FROM humo_settings");
$update_settingDb = $update_setting_qry->fetch(PDO::FETCH_OBJ);
if (isset($update_settingDb->setting_variabele)) {
    $dbh->query("ALTER TABLE humo_settings CHANGE setting_variabele setting_variable VARCHAR( 50 ) CHARACTER SET utf8 NULL DEFAULT NULL");
}

// *** Update table humo_settings: translate dutch variables into english... ***
$update_setting_qry = $dbh->query("SELECT * FROM humo_settings");
while ($update_settingDb = $update_setting_qry->fetch(PDO::FETCH_OBJ)) {
    $setting = '';
    if ($update_settingDb->setting_variable == 'database_naam') {
        $setting = 'database_name';
    }
    if ($update_settingDb->setting_variable == 'homepage_omschrijving') {
        $setting = 'homepage_description';
    }
    if ($update_settingDb->setting_variable == 'zoekmachine') {
        $setting = 'searchengine';
    }
    if ($update_settingDb->setting_variable == 'optierobots') {
        $setting = 'robots_option';
    }
    if ($update_settingDb->setting_variable == 'parenteel_generaties') {
        $setting = 'descendant_generations';
    }
    if ($update_settingDb->setting_variable == 'personen_weergeven') {
        $setting = 'show_persons';
    }

    if ($setting) {
        $update_Db = $dbh->query('UPDATE humo_settings SET setting_variable="' . $setting . '" WHERE setting_variable="' . $update_settingDb->setting_variable . '"');
    }
}


// *** Read settings from database ***
@$result = $dbh->query("SELECT * FROM humo_settings");
while (@$row = $result->fetch(PDO::FETCH_NUM)) {
    $humo_option[$row[1]] = $row[2];
}

// *** Automatic installation or update ***

// THIS PART CAN BE MOVED TO DATABASE UPDATE IF NEEDED.
if (!isset($humo_option["template_homepage"]) && $humo_option["update_status"] > 10) {
    $order = 1;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|left|select_family_tree', setting_order='" . $order . "'");

    $order++;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|selected_family_tree', setting_order='" . $order . "'");

    $order++;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|names|2|4', setting_order='" . $order . "'");

    $order++;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|alphabet', setting_order='" . $order . "'");

    $order++;
    // *** Replace old "today in history setting"  ***
    if (isset($humo_option["today_in_history_show"])) {
        $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|history', setting_order='" . $order . "'");
        $dbh->query("DELETE FROM humo_settings WHERE setting_variable='today_in_history_show'");
    } else {
        $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='inactive|center|history', setting_order='" . $order . "'");
    }

    $order++;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|center|favourites', setting_order='" . $order . "'");

    $order++;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='inactive|center|text', setting_order='" . $order . "'");

    $order++;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='inactive|center|cms_page', setting_order='" . $order . "'");

    $order++;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='active|right|search', setting_order='" . $order . "'");

    $order++;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='template_homepage', setting_value='inactive|right|random_photo', setting_order='" . $order . "'");
}

if (!isset($humo_option["rss_link"])) {
    $humo_option["rss_link"] = 'http://';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='rss_link', setting_value='http://'");
}

if (!isset($humo_option["descendant_generations"])) {
    $humo_option["descendant_generations"] = '4';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='descendant_generations', setting_value='4'");
}

if (!isset($humo_option["show_persons"])) {
    $humo_option["show_persons"] = '30';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='show_persons', setting_value='30'");
}

if (!isset($humo_option["url_rewrite"])) {
    $humo_option["url_rewrite"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='url_rewrite', setting_value='n'");
}

if (!isset($humo_option["default_skin"])) {
    $humo_option["default_skin"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='default_skin', setting_value=''");
}

if (!isset($humo_option["default_language"])) {
    $humo_option["default_language"] = 'en';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='default_language', setting_value='en'");
}

if (!isset($humo_option["default_language_admin"])) {
    $humo_option["default_language_admin"] = 'en';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='default_language_admin', setting_value='en'");
}

if (!isset($humo_option["text_header"])) {
    $humo_option["text_header"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='text_header', setting_value=''");
}
if (!isset($humo_option["text_footer"])) {
    $humo_option["text_footer"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='text_footer', setting_value=''");
}

if (!isset($humo_option["timezone"])) {
    $humo_option["timezone"] = 'Europe/Amsterdam';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='timezone', setting_value='Europe/Amsterdam'");
}

// *** Automatic installation or update ***
if (!isset($humo_option["update_status"])) {
    $humo_option["update_status"] = '0';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='update_status', setting_value='0'");
}

// *** Mail form spam question ***
if (!isset($humo_option["block_spam_question"])) {
    $humo_option["block_spam_question"] = 'What is the capital of England?';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='block_spam_question', setting_value='what is the capital of England?'");
}
if (!isset($humo_option["block_spam_answer"])) {
    $humo_option["block_spam_question"] = 'london';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='block_spam_answer', setting_value='london'");
}

if (!isset($humo_option["use_spam_question"])) {
    $humo_option["use_spam_question"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='use_spam_question', setting_value='n'");
}
if (!isset($humo_option["use_newsletter_question"])) {
    $humo_option["use_newsletter_question"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='use_newsletter_question', setting_value='n'");
}

if (!isset($humo_option["visitor_registration"])) {
    $humo_option["visitor_registration"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='visitor_registration', setting_value='n'");
}
if (!isset($humo_option["general_email"])) {
    $humo_option["general_email"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='general_email', setting_value=''");
}
if (!isset($humo_option["visitor_registration_group"])) {
    $humo_option["visitor_registration_group"] = '3';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='visitor_registration_group', setting_value='3'");
}
if (!isset($humo_option["registration_use_spam_question"])) {
    $humo_option["registration_use_spam_question"] = 'y';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='registration_use_spam_question', setting_value='y'");
}

// *** Solve bug in database (name of variable) ***
if (isset($humo_option["password_retreival"])) {
    $dbh->query("INSERT INTO humo_settings SET setting_variable='password_retrieval', setting_value='" . $humo_option["password_retreival"] . "'");
    $dbh->query("DELETE FROM humo_settings WHERE setting_variable='password_retreival'");

    $humo_option["password_retrieval"] = $humo_option["password_retreival"];
}
if (!isset($humo_option["password_retrieval"])) {
    $humo_option["password_retrieval"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='password_retrieval', setting_value=''");
}

if (!isset($humo_option["update_last_check"])) {
    $humo_option["update_last_check"] = '2012_01_01';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='update_last_check', setting_value='2012_01_01'");
}
if (!isset($humo_option["update_text"])) {
    $humo_option["update_text"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='update_text', setting_value=''");
}

if (!isset($humo_option["searchengine_cms_only"])) {
    $humo_option["searchengine_cms_only"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='searchengine_cms_only', setting_value='n'");
}

// *** Gedcom reading settings 18 aug 2013, updated 30 may 2015. ***
if (!isset($humo_option["gedcom_read_add_source"])) {
    $humo_option["gedcom_read_add_source"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_read_add_source', setting_value='n'");
}

if (!isset($humo_option["gedcom_read_reassign_gedcomnumbers"])) {
    $humo_option["gedcom_read_reassign_gedcomnumbers"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_read_reassign_gedcomnumbers', setting_value='n'");
}

if (!isset($humo_option["gedcom_read_order_by_date"])) {
    $humo_option["gedcom_read_order_by_date"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_read_order_by_date', setting_value='n'");
}

if (!isset($humo_option["gedcom_read_order_by_fams"])) {
    $humo_option["gedcom_read_order_by_fams"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_read_order_by_fams', setting_value='n'");
}

if (!isset($humo_option["gedcom_read_process_geo_location"])) {
    $humo_option["gedcom_read_process_geo_location"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_read_process_geo_location', setting_value='n'");
}

if (!isset($humo_option["gedcom_process_pict_path"])) {
    $humo_option["gedcom_process_pict_path"] = 'file_name';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_process_pict_path', setting_value='file_name'");
}

if (!isset($humo_option["gedcom_read_save_pictures"])) {
    $humo_option["gedcom_read_save_pictures"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_read_save_pictures', setting_value='n'");
}

if (!isset($humo_option["gedcom_read_commit_records"])) {
    $humo_option["gedcom_read_commit_records"] = '500';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_read_commit_records', setting_value='500'");
}

if (!isset($humo_option["gedcom_read_time_out"])) {
    $humo_option["gedcom_read_time_out"] = '0';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_read_time_out', setting_value='0'");
}

// *** Watermark text and color in PDF file ***
if (!isset($humo_option["watermark_text"])) {
    $humo_option["watermark_text"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='watermark_text', setting_value=''");
}
if (!isset($humo_option["watermark_color_r"])) {
    $humo_option["watermark_color_r"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='watermark_color_r', setting_value='224'");
}
if (!isset($humo_option["watermark_color_g"])) {
    $humo_option["watermark_color_g"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='watermark_color_g', setting_value='224'");
}
if (!isset($humo_option["watermark_color_b"])) {
    $humo_option["watermark_color_b"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='watermark_color_b', setting_value='224'");
}

// *** Minimum characters in search boxes
if (!isset($humo_option["min_search_chars"])) {
    $humo_option["min_search_chars"] = '3';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='min_search_chars', setting_value='3'");
}
if (!isset($humo_option["date_display"])) {
    $humo_option["date_display"] = 'eu';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='date_display', setting_value='eu'");
}
if (!isset($humo_option["name_order"])) {
    $humo_option["name_order"] = 'western';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='name_order', setting_value='western'");
}
if (!isset($humo_option["default_timeline"])) {
    $humo_option["default_timeline"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='default_timeline', setting_value=''");
}
// one name study display
if (!isset($humo_option["one_name_study"])) {
    $humo_option["one_name_study"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='one_name_study', setting_value='n'");
}
// one name study setting of the name
if (!isset($humo_option["one_name_thename"])) {
    $humo_option["one_name_thename"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='one_name_thename', setting_value=''");
}

if (!isset($humo_option["geo_trees"])) {
    $geo_string = '';
    $humo_option["geo_trees"] = $geo_string;
    $dbh->query("INSERT INTO humo_settings SET setting_variable='geo_trees', setting_value='" . $geo_string . "'");
}

// *** Slideshow_show homepage ***
if (!isset($humo_option["slideshow_show"])) {
    $humo_option["slideshow_show"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='slideshow_show', setting_value='n'");
}
// *** Slideshow slide 1 ***
if (!isset($humo_option["slideshow_01"])) {
    $humo_option["slideshow_01"] = '|';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='slideshow_01', setting_value='|'");
}
// *** Slideshow slide 2 ***
if (!isset($humo_option["slideshow_02"])) {
    $humo_option["slideshow_02"] = '|';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='slideshow_02', setting_value='|'");
}
// *** Slideshow slide 3 ***
if (!isset($humo_option["slideshow_03"])) {
    $humo_option["slideshow_03"] = '|';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='slideshow_03', setting_value='|'");
}
// *** Slideshow slide 4 ***
if (!isset($humo_option["slideshow_04"])) {
    $humo_option["slideshow_04"] = '|';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='slideshow_04', setting_value='|'");
}

// *** Jewish settings ***
if (!isset($humo_option["david_stars"])) {
    $humo_option["david_stars"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='david_stars', setting_value='n'");
}
if (!isset($humo_option["death_char"])) {
    $humo_option["death_char"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='death_char', setting_value='n'");
}
if (!isset($humo_option["death_shoa"])) {
    $humo_option["death_shoa"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='death_shoa', setting_value='n'");
}
if (!isset($humo_option["admin_hebdate"])) {
    $humo_option["admin_hebdate"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='admin_hebdate', setting_value='n'");
}
if (!isset($humo_option["admin_hebnight"])) {
    $humo_option["admin_hebnight"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='admin_hebnight', setting_value='n'");
}
if (!isset($humo_option["admin_hebname"])) {
    $humo_option["admin_hebname"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='admin_hebname', setting_value='n'");
}
if (!isset($humo_option["admin_brit"])) {
    $humo_option["admin_brit"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='admin_brit', setting_value='n'");
}
if (!isset($humo_option["admin_barm"])) {
    $humo_option["admin_barm"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='admin_barm', setting_value='n'");
}
if (!isset($humo_option["admin_online_search"])) {
    $humo_option["admin_online_search"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='admin_online_search', setting_value='n'");
}
if (!isset($humo_option["debug_front_pages"])) {
    $humo_option["debug_front_pages"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='debug_front_pages', setting_value='n'");
}
if (!isset($humo_option["debug_admin_pages"])) {
    $humo_option["debug_admin_pages"] = 'n';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='debug_admin_pages', setting_value='n'");
}

if (!isset($humo_option["hide_languages"])) {
    $humo_option["hide_languages"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='hide_languages', setting_value=''");
}

if (!isset($humo_option["hide_themes"])) {
    $humo_option["hide_themes"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='hide_themes', setting_value=''");
}

// *** Mail settings ***
if (!isset($humo_option["email_sender"])) {
    // *** Added july 2024 ***
    $humo_option["email_sender"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='email_sender', setting_value=''");
}
if (!isset($humo_option["mail_auto"])) {
    $humo_option["mail_auto"] = 'manual';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='mail_auto', setting_value='manual'");
}
if (!isset($humo_option["email_user"])) {
    $humo_option["email_user"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='email_user', setting_value=''");
}
if (!isset($humo_option["email_password"])) {
    $humo_option["email_password"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='email_password', setting_value=''");
}
if (!isset($humo_option["smtp_server"])) {
    $humo_option["smtp_server"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='smtp_server', setting_value=''");
}
if (!isset($humo_option["smtp_port"])) {
    $humo_option["smtp_port"] = '587';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='smtp_port', setting_value='587'");
}
if (!isset($humo_option["smtp_auth"])) {
    $humo_option["smtp_auth"] = 'true';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='smtp_auth', setting_value='true'");
}
if (!isset($humo_option["smtp_encryption"])) {
    $humo_option["smtp_encryption"] = 'tls';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='smtp_encryption', setting_value='tls'");
}
if (!isset($humo_option["smtp_debug"])) {
    $humo_option["smtp_debug"] = '0';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='smtp_debug', setting_value='0'");
}

// *** GEDCOM submitter ***
if (!isset($humo_option["gedcom_submit_name"])) {
    $humo_option["gedcom_submit_name"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_submit_name', setting_value=''");
}
if (!isset($humo_option["gedcom_submit_address"])) {
    $humo_option["gedcom_submit_address"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_submit_address', setting_value=''");
}
if (!isset($humo_option["gedcom_submit_country"])) {
    $humo_option["gedcom_submit_country"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_submit_country', setting_value=''");
}
if (!isset($humo_option["gedcom_submit_mail"])) {
    $humo_option["gedcom_submit_mail"] = '';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='gedcom_submit_mail', setting_value=''");
}

// *** Merge options ***
if (!isset($humo_option["merge_chars"])) {
    $humo_option["merge_chars"] = '10';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='merge_chars', setting_value='10'");
}
if (!isset($humo_option["merge_lastname"])) {
    $humo_option["merge_lastname"] = 'YES';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='merge_lastname', setting_value='YES'");
}
if (!isset($humo_option["merge_firstname"])) {
    $humo_option["merge_firstname"] = 'YES';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='merge_firstname', setting_value='YES'");
}
if (!isset($humo_option["merge_dates"])) {
    $humo_option["merge_dates"] = 'YES';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='merge_dates', setting_value='YES'");
}
if (!isset($humo_option["merge_parentsdate"])) {
    $humo_option["merge_parentsdate"] = 'YES';
    $dbh->query("INSERT INTO humo_settings SET setting_variable='merge_parentsdate', setting_value='YES'");
}

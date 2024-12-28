<?php

/**
 * Get general ($humo_option) and user ($user) settings from database.
 */

class GeneralSettings
{
    public function get_humo_option($dbh)
    {
        // >>>> July 2022: also change admin\update\version_check.txt. In use for update through GitHub.

        // *** Version line, DO NOT CHANGE THIS LINE ***
        // Version nummering: 1.1.1.1 (main number, sub number, update, etc.)
        $humo_option["version"] = '6.8';  // Version line, DO NOT CHANGE THIS LINE
        // >>>> July 2022: also change admin\update\version_check.txt. In use for update through GitHub.

        // *** Beta (not stable enough for production, but it's functional ***
        //$humo_option["version"]='BETA version 28 nov. 2019';  // Version line, DO NOT CHANGE THIS LINE
        //$humo_option["version"]='TEST version 11 oct. 2011';  // Version line, DO NOT CHANGE THIS LINE

        // *** Version date, needed for update check ***
        //$humo_option["version_date"]='2019-09-01';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE
        $humo_option["version_date"] = '2024-12-20';  // Version date yyyy-mm-dd, DO NOT CHANGE THIS LINE
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
                $dbh->query('UPDATE humo_settings SET setting_variable="' . $setting . '" WHERE setting_variable="' . $update_settingDb->setting_variable . '"');
            }
        }


        // *** Read settings from database ***
        $result = $dbh->query("SELECT * FROM humo_settings");
        while ($row = $result->fetch(PDO::FETCH_NUM)) {
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

        if (!isset($humo_option["thumbnail_auto_create"])) {
            $humo_option["thumbnail_auto_create"] = 'n';
            $dbh->query("INSERT INTO humo_settings SET setting_variable='thumbnail_auto_create', setting_value='n'");
        }

        if (!isset($humo_option["media_privacy_mode"])) {
            $humo_option["media_privacy_mode"] = 'n';
            $dbh->query("INSERT INTO humo_settings SET setting_variable='media_privacy_mode', setting_value='n'");
        }

        return $humo_option;
    }

    private function get_user($dbh)
    {
        if (isset($_SESSION["user_name"]) && is_numeric($_SESSION["user_id"])) {
            $qry = "SELECT * FROM humo_users WHERE user_id='" . $_SESSION["user_id"] . "'";
        } else {
            // *** For guest account ("gast" is only used for backward compatibility) ***
            $qry = "SELECT * FROM humo_users WHERE user_name='gast' OR user_name='guest'";
        }
        $userqry = $dbh->query($qry);
        try {
            $userDb = $userqry->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "No valid user / Geen geldige gebruiker.";
        }
        return $userDb;
    }

    public function get_user_settings($dbh)
    {
        $userDb = $this->get_user($dbh);

        $user["user_name"] = "";
        if (isset($_SESSION["user_name"]) && is_numeric($_SESSION["user_id"])) {
            $user["user_name"] = $_SESSION["user_name"];
        }

        $groupsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='" . $userDb->user_group_id . "'");
        try {
            $groupDb = $groupsql->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo "No valid usergroup / Geen geldige gebruikersgroup.";
        }

        $user['group_statistics'] = isset($groupDb->group_statistics) ? $groupDb->group_statistics : 'j';

        $user['group_birthday_rss'] = isset($groupDb->group_birthday_rss) ? $groupDb->group_birthday_rss : 'j';

        $user['group_birthday_list'] = isset($groupDb->group_birthday_list) ? $groupDb->group_birthday_list : 'j';

        $user['group_showstatistics'] = isset($groupDb->group_showstatistics) ? $groupDb->group_showstatistics : 'j';

        $user['group_relcalc'] = isset($groupDb->group_relcalc) ? $groupDb->group_relcalc : 'j';

        $user['group_googlemaps'] = isset($groupDb->group_googlemaps) ? $groupDb->group_googlemaps : 'j';

        $user['group_contact'] = isset($groupDb->group_contact) ? $groupDb->group_contact : 'j';

        $user['group_latestchanges'] = isset($groupDb->group_latestchanges) ? $groupDb->group_latestchanges : 'j';

        $user['group_menu_cms'] = isset($groupDb->group_menu_cms) ? $groupDb->group_menu_cms : 'y';

        $user['group_menu_persons'] = isset($groupDb->group_menu_persons) ? $groupDb->group_menu_persons : 'j';

        $user['group_menu_names'] = isset($groupDb->group_menu_names) ? $groupDb->group_menu_names : 'j';

        $user['group_menu_places'] = $groupDb->group_menu_places;

        $user['group_menu_login'] = isset($groupDb->group_menu_login) ? $groupDb->group_menu_login : 'j';

        $user['group_menu_change_password'] = isset($groupDb->group_menu_change_password) ? $groupDb->group_menu_change_password : 'y';

        $user["group_privacy"] = $groupDb->group_privacy;

        $user['group_admin'] = $groupDb->group_admin;

        //$user['group_editor'] = isset($groupDb->group_editor) ? $groupDb->group_editor : 'n';

        $user['group_pictures'] = $groupDb->group_pictures;

        $user['group_photobook'] = isset($groupDb->group_photobook) ? $groupDb->group_photobook : 'n';

        $user['group_sources'] = $groupDb->group_sources;

        $user['group_show_restricted_source'] = isset($groupDb->group_show_restricted_source) ? $groupDb->group_show_restricted_source : 'y';

        $user['group_source_presentation'] = isset($groupDb->group_source_presentation) ? $groupDb->group_source_presentation : 'title';

        $user['group_text_presentation'] = isset($groupDb->group_text_presentation) ? $groupDb->group_text_presentation : 'show';

        $user['group_citation_generation'] = isset($groupDb->group_citation_generation) ? $groupDb->group_citation_generation : 'n';

        // *** User can add notes/ remarks by a person in the family tree ***
        $user['group_user_notes'] = isset($groupDb->group_user_notes) ? $groupDb->group_user_notes : 'n';

        $user['group_user_notes_show'] = isset($groupDb->group_user_notes_show) ? $groupDb->group_user_notes_show : 'n';

        $user['group_gedcomnr'] = $groupDb->group_gedcomnr; // Show gedcomnumber
        $user['group_living_place'] = $groupDb->group_living_place; // Show living place
        $user['group_places'] = $groupDb->group_places; // Show birth, bapt, death and buried places.
        $user['group_religion'] = $groupDb->group_religion; // Show birth and marr. religion
        $user['group_place_date'] = $groupDb->group_place_date; // j=place-date, n-date-place
        $user['group_kindindex'] = $groupDb->group_kindindex; // n='Mons, Henk van', j='van Mons, Henk'
        $user['group_event'] = $groupDb->group_event; // Show events
        $user['group_addresses'] = $groupDb->group_addresses; // Show addresses IN MENU
        $user['group_own_code'] = $groupDb->group_own_code; // Show Own code

        $user['group_show_age_living_person'] = isset($groupDb->group_show_age_living_person) ? $groupDb->group_show_age_living_person : 'y';

        $user['group_pdf_button'] = isset($groupDb->group_pdf_button) ? $groupDb->group_pdf_button : 'y';

        $user['group_rtf_button'] = isset($groupDb->group_rtf_button) ? $groupDb->group_rtf_button : 'n';

        $user['group_family_presentation'] = isset($groupDb->group_family_presentation) ? $groupDb->group_family_presentation : 'compact';

        $user['group_maps_presentation'] = isset($groupDb->group_maps_presentation) ? $groupDb->group_maps_presentation : 'hide';

        $user['group_work_text'] = $groupDb->group_work_text; // Show (Haza-data) worktexts
        $user['group_texts'] = $groupDb->group_texts; // Show (marriage?) text
        $user['group_text_pers'] = $groupDb->group_text_pers; // Show person text
        $user['group_texts_pers'] = $groupDb->group_texts_pers; // Show birth, bapt, death, burr. texts.
        $user['group_texts_fam'] = $groupDb->group_texts_fam; // Show marr. (licence) texts

        //Privacy filter
        $user['group_alive'] = $groupDb->group_alive; // Person filter.

        $user['group_alive_date_act'] = $groupDb->group_alive_date_act; // Privacy filter activated
        $user['group_alive_date'] = $groupDb->group_alive_date; // Privacy filter year

        $user['group_death_date_act'] = isset($groupDb->group_death_date_act) ? $groupDb->group_death_date_act : 'n';

        $user['group_death_date'] = isset($groupDb->group_death_date) ? $groupDb->group_death_date : '';

        $user['group_filter_death'] = $groupDb->group_filter_death; // Filter deceased persons
        $user['group_filter_total'] = $groupDb->group_filter_total;
        $user['group_filter_name'] = $groupDb->group_filter_name; // Privacy: show persons
        $user['group_filter_fam'] = $groupDb->group_filter_fam;

        $user['group_filter_pers_show_act'] = $groupDb->group_filter_pers_show_act; // Activate next line
        $user['group_filter_pers_show'] = $groupDb->group_filter_pers_show; // Person filter

        $user['group_filter_pers_hide_act'] = $groupDb->group_filter_pers_hide_act; // Activate next line
        $user['group_filter_pers_hide'] = $groupDb->group_filter_pers_hide; // Person filter

        $user['group_pers_hide_totally_act'] = isset($groupDb->group_pers_hide_totally_act) ? $groupDb->group_pers_hide_totally_act : 'n';

        $user['group_pers_hide_totally'] = isset($groupDb->group_pers_hide_totally) ? $groupDb->group_pers_hide_totally : 'X';

        $user['group_filter_date'] = isset($groupDb->group_filter_date) ? $groupDb->group_filter_date : 'n';

        $user['group_gen_protection'] = isset($groupDb->group_gen_protection) ? $groupDb->group_gen_protection : 'n';

        // *** Show or hide family trees, saved as ; separated id numbers ***
        $user['group_hide_trees'] = isset($groupDb->group_hide_trees) ? $groupDb->group_hide_trees : '';

        // *** Also check user settings. Example: 1, y2, 3, y4. y=yes to show family tree ***
        if (isset($userDb->user_hide_trees) && $userDb->user_hide_trees) {
            $user_hide_trees_array = explode(";", $userDb->user_hide_trees);
            foreach ($user_hide_trees_array as $key) {
                // *** Check for y (used in y1, y2 etc.). Indicates to SHOW a family tree ***
                // *** $key[0]= 1st character ***
                if ($key[0] === 'y') {
                    // *** remove y1; ***
                    $replace = $key[1] . ';';
                    $user['group_hide_trees'] = str_replace($replace, '', $user['group_hide_trees']);
                    // *** Or: remove y1 (without ;) ***
                    //$user['group_hide_trees']=str_replace($key[1],'',$user['group_hide_trees']);
                    $user['group_hide_trees'] = rtrim($user['group_hide_trees'], $key[1]);
                } else {
                    $check_array = explode(";", $user['group_hide_trees']);
                    //if (!in_array($key, $user['group_hide_trees'])){
                    if (!in_array($key, $check_array)) {
                        if ($user['group_hide_trees']) {
                            $user['group_hide_trees'] .= ';' . $key;
                        } else {
                            $user['group_hide_trees'] = $key;
                        }
                    }
                }
            }
        }

        // *** Show or hide photo categories, saved as ; separated id numbers ***
        $user['group_hide_photocat'] = isset($groupDb->group_hide_photocat) ? $groupDb->group_hide_photocat : '';

        // *** Edit family trees [GROUP SETTING], saved as ; separated id numbers (NOT USED FOR ADMINISTRATOR) ***
        $user['group_edit_trees'] = isset($groupDb->group_edit_trees) ? $groupDb->group_edit_trees : '';
        // *** Edit family trees [USER SETTING] ***
        if (isset($userDb->user_edit_trees) && $userDb->user_edit_trees) {
            if ($user['group_edit_trees']) {
                $user['group_edit_trees'] .= ';' . $userDb->user_edit_trees;
            } else {
                $user['group_edit_trees'] = $userDb->user_edit_trees;
            }
        }

        return $user;
    }
}

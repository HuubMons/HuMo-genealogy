<?php
class AdminSettingsModel
{
    public function get_menu_tab()
    {
        // *** Show tabs ***
        $menu_admin = 'settings';
        if (isset($_POST['menu_admin'])) {
            $menu_admin = $_POST['menu_admin'];
        }
        if (isset($_GET['menu_admin'])) {
            $menu_admin = $_GET['menu_admin'];
        }
        return $menu_admin;
    }

    public function get_timeline_language($humo_option)
    {
        if (isset($_POST['timeline_language'])) {
            $time_lang = $_POST['timeline_language'];
        } elseif (isset($_GET['timeline_language'])) {
            $time_lang = $_GET['timeline_language'];
        } else {
            $time_lang = $humo_option['default_language'];
        }
        return $time_lang;
    }

    public function save_settings($dbh, $db_functions, $humo_option, $settings)
    {
        if (isset($_POST['save_option'])) {
            // *** Update settings ***
            $db_functions->update_settings('default_skin', $_POST["default_skin"]);

            $db_functions->update_settings('default_language', $_POST["default_language"]);
            $db_functions->update_settings('default_language_admin', $_POST["default_language_admin"]);

            $db_functions->update_settings('text_header', $_POST["text_header"]);
            $db_functions->update_settings('text_footer', $_POST["text_footer"]);

            $db_functions->update_settings('debug_front_pages', $_POST["debug_front_pages"]);
            $db_functions->update_settings('debug_admin_pages', $_POST["debug_admin_pages"]);

            $db_functions->update_settings('database_name', $_POST["database_name"]);
            $db_functions->update_settings('homepage', $_POST["homepage"]);
            $db_functions->update_settings('homepage_description', $_POST["homepage_description"]);

            $db_functions->update_settings('rss_link', $_POST["rss_link"]);

            $db_functions->update_settings('searchengine', $_POST["searchengine"]);
            $db_functions->update_settings('robots_option', $_POST["robots_option"]);

            $db_functions->update_settings('searchengine_cms_only', $_POST["searchengine_cms_only"]);

            $db_functions->update_settings('block_spam_question', $_POST["block_spam_question"]);
            $db_functions->update_settings('block_spam_answer', $_POST["block_spam_answer"]);

            $db_functions->update_settings('use_spam_question', $_POST["use_spam_question"]);
            $db_functions->update_settings('use_newsletter_question', $_POST["use_newsletter_question"]);

            $db_functions->update_settings('visitor_registration', $_POST["visitor_registration"]);
            $db_functions->update_settings('general_email', $_POST["general_email"]);
            $db_functions->update_settings('visitor_registration_group', $_POST["visitor_registration_group"]);
            $db_functions->update_settings('registration_use_spam_question', $_POST["registration_use_spam_question"]);
            $db_functions->update_settings('password_retrieval', $_POST["password_retrieval"]);

            /*
            ***************************
            Kai Mahnke 2020-04: Save email configuration settings 
            ****************************
            */
            $db_functions->update_settings('email_sender', $_POST["email_sender"]);
            $db_functions->update_settings('mail_auto', $_POST["mail_auto"]);
            $db_functions->update_settings('email_user', $_POST["email_user"]);
            $db_functions->update_settings('email_password', $_POST["email_password"]);
            $db_functions->update_settings('smtp_server', $_POST["smtp_server"]);
            $db_functions->update_settings('smtp_port', $_POST["smtp_port"]);
            $db_functions->update_settings('smtp_auth', $_POST["smtp_auth"]);
            $db_functions->update_settings('smtp_encryption', $_POST["smtp_encryption"]);
            $db_functions->update_settings('smtp_debug', $_POST["smtp_debug"]);
            /* End changes */

            $db_functions->update_settings('descendant_generations', $_POST["descendant_generations"]);
            $db_functions->update_settings('show_persons', $_POST["show_persons"]);
            $db_functions->update_settings('url_rewrite', $_POST["url_rewrite"]);
            $db_functions->update_settings('timezone', $_POST["timezone"]);

            $db_functions->update_settings('watermark_text', $_POST["watermark_text"]);
            $db_functions->update_settings('watermark_color_r', $_POST["watermark_color_r"]);
            $db_functions->update_settings('watermark_color_g', $_POST["watermark_color_g"]);
            $db_functions->update_settings('watermark_color_b', $_POST["watermark_color_b"]);
            $db_functions->update_settings('min_search_chars', $_POST["min_search_chars"]);
            $db_functions->update_settings('date_display', $_POST["date_display"]);
            $db_functions->update_settings('name_order', $_POST["name_order"]);
            $db_functions->update_settings('one_name_study', $_POST["one_name_study"]);
            $db_functions->update_settings('one_name_thename', $_POST["one_name_thename"]);

            // *** IP API used for country statistics ***
            if ($_POST['ip_api']) {
                // *** Reset settings ***
                $dbh->query("UPDATE humo_settings SET setting_value='' WHERE setting_variable='ip_api_collection'");
                $dbh->query("UPDATE humo_settings SET setting_value='' WHERE setting_variable='ip_api_geoplugin_old'");
                $dbh->query("UPDATE humo_settings SET setting_value='dis|" . substr($humo_option['ip_api_geoplugin'], 4) . "' WHERE setting_variable='ip_api_geoplugin'");
                $dbh->query("UPDATE humo_settings SET setting_value='' WHERE setting_variable='ip_api_ip_api'");
                $dbh->query("UPDATE humo_settings SET setting_value='' WHERE setting_variable='ip_api_freeipapi'");
            }
            if ($_POST['ip_api'] == 'ip_api_collection') {
                // *** This option disables the ip_api setting ***
                $dbh->query("UPDATE humo_settings SET setting_value='dis' WHERE setting_variable='ip_api_collection'");
            } elseif ($_POST['ip_api'] == 'ip_api_geoplugin_old') {
                $dbh->query("UPDATE humo_settings SET setting_value='ena' WHERE setting_variable='ip_api_geoplugin_old'");
            } elseif ($_POST['ip_api'] == 'ip_api_geoplugin') {
                $dbh->query("UPDATE humo_settings SET setting_value='ena|" . $_POST['geoplugin_key'] . "' WHERE setting_variable='ip_api_geoplugin'");
            } elseif ($_POST['ip_api'] == 'ip_api_ip_api') {
                $dbh->query("UPDATE humo_settings SET setting_value='ena' WHERE setting_variable='ip_api_ip_api'");
            } elseif ($_POST['ip_api'] == 'ip_api_freeipapi') {
                $dbh->query("UPDATE humo_settings SET setting_value='ena' WHERE setting_variable='ip_api_freeipapi'");
            }

            if (strpos($humo_option['default_timeline'], $settings['time_lang'] . "!") === false) {
                // no entry for this language yet - append it
                $dbh->query("UPDATE humo_settings SET setting_value=CONCAT(setting_value,'" . safe_text_db($_POST["default_timeline"]) . "') WHERE setting_variable='default_timeline'");
            } else {
                $time_arr = explode("@", substr($humo_option['default_timeline'], 0, -1));
                foreach ($time_arr as $key => $value) {
                    if (strpos($value, $settings['time_lang'] . "!") !== false) {
                        $time_arr[$key] = substr(safe_text_db($_POST["default_timeline"]), 0, -1);
                    }
                }
                $time_str = implode("@", $time_arr) . "@";
                $db_functions->update_settings('default_timeline', $time_str);
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
            $db_functions->update_settings('slideshow_show', $_POST["slideshow_show"]);
            $db_functions->update_settings('slideshow_01', $_POST["slideshow_slide_01"] . '|' . $_POST["slideshow_text_01"]);
            $db_functions->update_settings('slideshow_02', $_POST["slideshow_slide_02"] . '|' . $_POST["slideshow_text_02"]);
            $db_functions->update_settings('slideshow_03', $_POST["slideshow_slide_03"] . '|' . $_POST["slideshow_text_03"]);
            $db_functions->update_settings('slideshow_04', $_POST["slideshow_slide_04"] . '|' . $_POST["slideshow_text_04"]);

            // *** Today in history ***
            //$db_functions->update_settings('today_in_history_show',$_POST["today_in_history_show"]);
        }

        // *** Special settings ***
        if (isset($_POST['save_option3'])) {
            // Jewish settings
            $setting_value = 'n';
            if (isset($_POST["david_stars"])) {
                $setting_value = 'y';
            }
            $db_functions->update_settings('david_stars', $setting_value);

            $setting_value = 'n';
            if (isset($_POST["death_shoa"])) {
                $setting_value = 'y';
            }
            $db_functions->update_settings('death_shoa', $setting_value);

            $setting_value = 'n';
            if (isset($_POST["admin_hebnight"])) {
                $setting_value = 'y';
            }
            $db_functions->update_settings('admin_hebnight', $setting_value);

            $setting_value = 'n';
            if (isset($_POST["admin_hebdate"])) {
                $setting_value = 'y';
            }
            $db_functions->update_settings('admin_hebdate', $setting_value);

            $setting_value = 'n';
            if (isset($_POST["admin_hebname"])) {
                $setting_value = 'y';
            }
            $db_functions->update_settings('admin_hebname', $setting_value);

            $setting_value = 'n';
            if (isset($_POST["admin_brit"])) {
                $setting_value = 'y';
            }
            $db_functions->update_settings('admin_brit', $setting_value);

            $setting_value = 'n';
            if (isset($_POST["admin_barm"])) {
                $setting_value = 'y';
            }
            $db_functions->update_settings('admin_barm', $setting_value);

            if (isset($_POST["death_char"]) && safe_text_db($_POST["death_char"]) == "y" && $humo_option['death_char'] == "n") {
                $humo_option['death_char'] = 'y';
                include(__DIR__ . "/../../languages/change_all.php");  // change cross to infinity
                $db_functions->update_settings('death_char', 'y');
            } elseif ((!isset($_POST["death_char"]) || safe_text_db($_POST["death_char"]) == "n") && $humo_option['death_char'] == "y") {
                $humo_option['death_char'] = 'n';
                include(__DIR__ . "/../../languages/change_all.php");  // change infinity to cross
                $db_functions->update_settings('death_char', 'n');
            }
        }
    }
}

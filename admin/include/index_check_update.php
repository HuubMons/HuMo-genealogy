<?php
// *** Enable/ disable HuMo-genealogy update check ***
if (isset($_POST['enable_update_check_change'])) {
    if (isset($_POST['enable_update_check'])) {
        $update_last_check = '2012-01-01';
        $update_text = '';
        $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';
    } else {
        $update_last_check = 'DISABLED';
        $update_text = '  ' . __('update check is disabled.');
        $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';
    }

    $result = $db_functions->update_settings('update_text', $update_text);
    $result = $db_functions->update_settings('update_last_check', $update_last_check);

    $humo_option['update_last_check'] = $update_last_check;
    //$humo_option['update_text']=$update_text;
}

// *** Check if installation is completed, before checking for an update ***
$check_update = @$dbh->query("SELECT * FROM humo_settings");
if ($check_update and $page != 'login' and $page != 'update' and $popup == false) {
    $debug_update = 'Start. ';

    // *** Manual check for update ***
    if (isset($_GET['update_check']) and $humo_option['update_last_check'] != 'DISABLED') {
        // *** Update settings ***
        $result = $db_functions->update_settings('update_last_check', '2012-01-01');
        $humo_option['update_last_check'] = '2012-01-01';
    }

    // *** Update file, example ***
    // echo "version=4.8.4\r\n";
    // echo "version_date=2012-09-02\r\n";
    // echo "test=testline";

    // *** Update check, once a day ***
    // 86400 = 1 day. yyyy-mm-dd
    if ($humo_option['update_last_check'] != 'DISABLED' and strtotime("now") - strtotime($humo_option['update_last_check']) > 86400) {
        $link_name = str_replace(' ', '_', $_SERVER['SERVER_NAME']);
        $link_version = str_replace(' ', '_', $humo_option["version"]);

        if (function_exists('curl_exec')) {
            // First try GitHub ***
            // *** Oct. 2021: Added random number to prevent CURL cache problems ***
            $source = 'https://raw.githubusercontent.com/HuubMons/HuMo-genealogy/master/admin/update/version_check.txt?random=' . rand();

            $resource = curl_init();
            curl_setopt($resource, CURLOPT_URL, $source);
            curl_setopt($resource, CURLOPT_HEADER, false);
            curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
            //curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 20);
            // *** BE AWARE: for provider Hostinger this must be a low value, otherwise the $dbh connection will be disconnected! ***
            curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 15);

            // *** Oct 2021: Don't use CURL cache ***
            curl_setopt($resource, CURLOPT_FRESH_CONNECT, true); // don't use a cached version of the url

            // *** Added for GitHub ***
            curl_setopt($resource, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($resource, CURLOPT_USERAGENT, $_SERVER['HTTP_USER_AGENT']);

            $content = curl_exec($resource);
            curl_close($resource);

            $content_array = explode(PHP_EOL, $content); // *** Split array into seperate lines ***

            // *** Debug information and validation of data ***
            if (isset($content_array[0])) {
                $debug_update .= ' Github:' . $content_array[1] . '. ';

                // *** Check if there is valid information, there should be at least 4 version lines ***
                $valid = 0;
                foreach ($content_array as $content_line) {
                    if (substr($content_line, 0, 7) == 'version') $valid++;
                }

                if ($valid > 3) {
                    $debug_update .= ' Valid.';
                } else {
                    unset($content_array);
                    $debug_update .= ' Invalid.';
                }
            }

            // *** Use humo-gen.com if GitHub isn't working ***
            if (!isset($content_array)) {
                // *** Read update data from HuMo-genealogy website ***
                // *** Oct. 2021: Added random number to prevent CURL cache problems ***
                $source = 'https://humo-gen.com/update/index.php?status=check_update&website=' . $link_name . '&version=' . $link_version . '&random=' . rand();

                //$update_file='update/temp_update_check.php';
                $resource = curl_init();
                curl_setopt($resource, CURLOPT_URL, $source);
                curl_setopt($resource, CURLOPT_HEADER, false);
                curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
                //curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 20);
                // *** BE AWARE: for provider Hostinger this must be a low value, otherwise the $dbh connection will be disconnected! ***
                curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 15);

                // *** Oct 2021: Don't use CURL cache ***
                curl_setopt($resource, CURLOPT_FRESH_CONNECT, true); // don't use a cached version of the url

                $content = curl_exec($resource);
                curl_close($resource);

                $content_array = explode(PHP_EOL, $content); // *** Split array into seperate lines ***

                // *** Debug information and validation of data ***
                if (isset($content_array[0])) {
                    $debug_update .= ' HG:' . $content_array[0] . ' ';

                    // *** Check if there is valid information, there should be 4 version lines ***
                    $valid = 0;
                    foreach ($content_array as $content_line) {
                        if (substr($content_line, 0, 7) == 'version') $valid++;
                    }

                    if ($valid > 3) {
                        $debug_update .= ' Valid.';
                    } else {
                        unset($content_array);
                        $debug_update .= ' Invalid.';
                    }
                }

                //if($content != ''){
                //	$fp = @fopen($update_file, 'w');
                //	$fw = @fwrite($fp, $content);
                //	@fclose($fp);
                //}
            }

            // *** If provider or curl blocks https link: DISABLE SSL and recheck ***
            if (!isset($content_array)) {
                // *** Oct. 2021: Added random number to prevent CURL cache problems ***
                $source = 'https://humo-gen.com/update/index.php?status=check_update&website=' . $link_name . '&version=' . $link_version . '&random=' . rand();

                //$update_file='update/temp_update_check.php';
                $resource = curl_init();
                curl_setopt($resource, CURLOPT_URL, $source);
                curl_setopt($resource, CURLOPT_HEADER, false);
                curl_setopt($resource, CURLOPT_RETURNTRANSFER, true);
                //curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 20);
                // *** BE AWARE: for provider Hostinger this must be a low value, otherwise the $dbh connection will be disconnected! ***
                curl_setopt($resource, CURLOPT_CONNECTTIMEOUT, 15);

                // *** Oct 2021: Don't use CURL cache ***
                curl_setopt($resource, CURLOPT_FRESH_CONNECT, true); // don't use a cached version of the url

                // *********************************************************************
                // *** EXTRA SETTINGS TO DISABLE SSL CHECK NEEDED FOR SOME PROVIDERS ***
                //Disable CURLOPT_SSL_VERIFYHOST and CURLOPT_SSL_VERIFYPEER by
                //setting them to false.
                curl_setopt($resource, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($resource, CURLOPT_SSL_VERIFYPEER, false);
                // *********************************************************************

                $content = curl_exec($resource);
                curl_close($resource);

                $content_array = explode(PHP_EOL, $content); // *** Split array into seperate lines ***

                // *** Debug information ***
                if (isset($content_array[0])) {
                    $debug_update .= ' 3:' . $content_array[0] . ' ';
                }
            }
        }

        // *** Copy HuMo-genealogy to server using file_get_contents ***
        /*
                if (!file_exists('update/temp_update_check.php')){
                    $source='https://humo-gen.com/update/index.php?status=check_update&website='.$link_name.'&version='.$link_version;
                    $update_file='update/temp_update_check.php';

                    $content = @file_get_contents($source);
                    //if ($content === false) {
                    //	$this->_log->addError(sprintf('Could not download update "%s"!', $updateUrl));
                    //	return false;
                    //}

                    // *** Open file ***
                    $handle = fopen($update_file, 'w');
                    //if (!$handle) {
                    //	$this->_log->addError(sprintf('Could not open file handle to save update to "%s"!', $updateFile));
                    //	return false;
                    //}

                    // *** Copy file ***
                    if (!fwrite($handle, $content)) {
                    //	$this->_log->addError(sprintf('Could not write update to file "%s"!', $updateFile));
                    //	fclose($handle);
                    //	return false;
                    }

                    fclose($handle);
                }
                */

        // *** Copy HuMo-genealogy to server using copy ***
        // DISABLED BECAUSE MOST PROVIDERS BLOCK THIS COPY FUNCTION FOR OTHER WEBSITES...
        //if (!file_exists('update/temp_update_check.php')){
        //	$source='https://humo-gen.com/update/index.php?status=check_update&website='.$link_name.'&version='.$link_version;
        //	$update_file='update/temp_update_check.php';
        //	@copy($source, $update_file);
        //}


        //if ($f = @fopen($update_file, 'r')){
        //if (is_file($update_file) AND $f = @fopen($update_file, 'r')){
        if (isset($content_array) and $content_array) {
            // *** Used for automatic update procedure ***
            $update['up_to_date'] = 'no';

            // *** HuMo-genealogy version ***
            $update['version'] = '';
            $update['version_date'] = '';
            $update['version_auto_download'] = '';
            // At this moment only 4 lines permitted that starts with version...
            $update['new_version_auto_download_github'] = '';

            // *** HuMo-genealogy beta version ***
            $update['beta_version'] = '';
            $update['beta_version_date'] = '';
            $update['beta_version_auto_download'] = '';

            //while(!feof($f)) { 
            foreach ($content_array as $content_line) {
                //$update_data = fgets( $f, 4096 );
                $update_array = explode("=", $content_line);

                // *** HuMo-genealogy version ***
                if ($update_array[0] == 'version') {
                    $update['version'] = trim($update_array[1]);
                }
                if ($update_array[0] == 'version_date') {
                    $update['version_date'] = trim($update_array[1]);
                }
                if ($update_array[0] == 'version_download') {
                    $update['version_download'] = trim($update_array[1]);
                }
                if ($update_array[0] == 'version_auto_download') {
                    $update['version_auto_download'] = trim($update_array[1]);
                }
                if ($update_array[0] == 'version_auto_download_github') {
                    $update['version_auto_download_github'] = trim($update_array[1]);
                }

                // *** HuMo-genealogy beta version ***
                if ($update_array[0] == 'beta_version') {
                    $update['beta_version'] = trim($update_array[1]);
                }
                if ($update_array[0] == 'beta_version_date') {
                    $update['beta_version_date'] = trim($update_array[1]);
                }
                if ($update_array[0] == 'beta_version_download') {
                    $update['beta_version_download'] = trim($update_array[1]);
                }
                if ($update_array[0] == 'beta_version_auto_download') {
                    $update['beta_version_auto_download'] = trim($update_array[1]);
                }
            }
            //fclose($f);

            //$humo_option["version"]='0'; // *** Test line ***
            // *** 1) Standard status ***
            $update['up_to_date'] = 'yes';
            $update_text = ' ' . __('Update check failed.');
            $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';

            //NEW
            if ($humo_option["version"] == $update['version']) {
                $update['up_to_date'] = 'yes';
                $update_text = ' ' . __('is up-to-date!');
                $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';
            }

            // *** 2) HuMo-genealogy up-to-date (checking version numbers) ***
            //if ($humo_option["version"]==$update['version']){
            // *** If GitHub numbering isn't up-to-date yet, just ignore version check. Could happen while updating sites! ***
            if (strtotime($update['version_date']) - strtotime($humo_option["version_date"]) < 0) {
                $update['up_to_date'] = 'yes';
                $update_text = ' ' . __('is up-to-date!');
                $update_text .= ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update options') . '</a>';
            }

            // *** 3) First priority: check for normal HuMo-genealogy update ***
            if (strtotime($update['version_date']) - strtotime($humo_option["version_date"]) > 0) {
                $update['up_to_date'] = 'no';
                $update_text = ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Update available') . ' (' . $update['version'] . ')!</a>';
            }
            // *** 4) Second priority: check for Beta version update ***
            elseif (strtotime($update['beta_version_date']) - strtotime($humo_option["version_date"]) > 0) {
                $update['up_to_date'] = 'yes';
                $update_text = ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Beta version available') . ' (' . $update['beta_version'] . ')!</a>';
            }

            // *** Update settings ***
            $update_last_check = date("Y-m-d");
            $result = $db_functions->update_settings('update_last_check', $update_last_check);

            // *** Remove temporary file, used for curl method ***
            //if (file_exists('update/temp_update_check.php')) unlink ('update/temp_update_check.php');
        } else {
            //$update_text= '  '.__('Online version check unavailable.');
            //$update_text.= ' <a href="'.$path_tmp.'page=install_update&update_check=1">'.__('Update options').'</a>';
            $update_text = ' <a href="' . $path_tmp . 'page=install_update&amp;update_check=1">' . __('Online version check unavailable.') . '</a>';

            if (!function_exists('curl_exec')) $update_text .= ' Extension php_curl.dll is disabled.';
            elseif (!is_writable('update')) $update_text .= ' Folder admin/update/ is read only.';

            //if( !ini_get('allow_url_fopen') ) $update_text.=' Setting allow_url_fopen is disabled.';

            // *** Update settings, only check for update once a day ***
            $update_last_check = date("Y-m-d");
            $result = $db_functions->update_settings('update_last_check', $update_last_check);
        }

        $result = $db_functions->update_settings('update_text', $update_text);

        $update_text .= ' *';

        // *** Show debug information ***
        if (isset($_POST['debug_update'])) {
            $update_text .= ' ' . __('Debug information:') . ' [' . $debug_update . ']';
        }
    } else {
        // No online check now, use saved text...
        $update_text = $humo_option["update_text"];
    }
    echo $update_text;
}

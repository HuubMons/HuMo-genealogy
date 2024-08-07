<?php
/*
Original Google Maps script: Yossi.
April 2022 Huub: Added OpenStreetMap.
*/

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// TODO rename script to maps or edit_maps?
// TODO create controller/ model.
?>

<h1 align=center><?= __('World map administration'); ?></h1>
<table class="humo standard" border="1" style="width:900px;">
    <?php if (isset($_POST['makedatabase'])) { ?>
        <tr class="table_header">
            <th><?= __('Creating/ updating database'); ?></th>
        </tr>
        <tr>
            <td>
                <?php
                $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
                if (!$temp->rowCount()) {
                    // no database exists - so create it
                    // (Re)create a location table "humo_location" for each tree (humo1_ , humo2_ etc)
                    // It has 4 columns:
                    //     1. id
                    //     2. name of location
                    //     3. latitude as received from a geocode call
                    //     4. longitude as received from a geocode call
                    //     5. status: what is this location used for: birth/bapt/death/buried, and by which tree(s)

                    echo '<br>' . __('Creating location database') . '<br>';

                    $locationtbl = "CREATE TABLE humo_location (
                        location_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                        location_location VARCHAR(120) CHARACTER SET utf8,
                        location_lat FLOAT(10,6),
                        location_lng FLOAT(10,6),
                        location_status TEXT
                    )";
                    $dbh->query($locationtbl);
                }
                $count_parsed = 0;
                $map_notfound_array = array();
                $map_count_found = 0;
                $map_count_notfound = 0;
                $flag_stop = 0;

                echo __('Started adding to data base.') . '<br>';
                echo __('Starting time') . ': ' . date('G:i:s') . '<br><br>';
                sleep(1); // make sure this gets printed before the next is executed

                // If the locations are taken from one tree, add the id of this tree to humo_settings "geo_trees", if not already there
                // so we can update correctly with the "REFRESH BIRTH/DEATH STATUS" option further on.
                if ($_SESSION['geo_tree']  != "all_geo_trees") { // we add locations from one tree
                    if (strpos($humo_option['geo_trees'], "@" . $_SESSION['geo_tree'] . ";") === false) { // this tree_id does not appear already
                        $result = $db_functions->update_settings('geo_trees', $humo_option['geo_trees'] . "@" . $_SESSION['geo_tree']);
                        // add tree_prefix if not already present
                        $humo_option['geo_trees'] .= "@" . $_SESSION['geo_tree'] . ';'; // humo_option is used further on before page is refreshed so we have to update it manually
                    }
                } else {
                    $str = "";
                    $tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
                    $tree_search_result = $dbh->query($tree_search_sql);
                    while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                        $str .= "@" . $tree_searchDb->tree_id . ";";
                    }
                    $result = $db_functions->update_settings('geo_trees', $str);
                    $humo_option['geo_trees'] = $str; // humo_option is used further on before page is refreshed so we have to update it manually
                }
                foreach ($_SESSION['add_locations'] as $value) {
                    $count_parsed++;
                    //if($count_parsed<110 OR $count_parsed > 125) continue;
                    $loc = urlencode($value);

                    // *** OpenStreetMap, use GeoKeo to get geolocation data ***
                    if (isset($humo_option["use_world_map"]) && $humo_option["use_world_map"] == 'OpenStreetMap') {
                        $url = "https://geokeo.com/geocode/v1/search.php?q=" . $loc . "&api=" . $humo_option['geokeo_api_key'];
                        $json = file_get_contents($url);
                        $json = json_decode($json);
                        //if(array_key_exists('status',$json)){
                        if (isset($json->status) && $json->status == 'ok') {
                            $map_count_found++;
                            //$address = $json->results[0]->formatted_address;
                            $latitude = $json->results[0]->geometry->location->lat;
                            $longitude = $json->results[0]->geometry->location->lng;
                            //$dbh->query("INSERT INTO humo_location SET location_location='".$loc."', location_lat='".$latitude."', location_lng='".$longitude."'");
                            $dbh->query("INSERT INTO humo_location SET location_location='" . safe_text_db($value) . "', location_lat='" . $latitude . "', location_lng='" . $longitude . "'");
                        }
                    } else {
                        // *** Google Maps ***
                        //$api_key = '';
                        $api_key = '?callback=Function.prototype';
                        // Key is meant for showing maps and should be set to restriction: "HTTP referrers". This key will only be used here if no second key is present.
                        // This key will only work here if admin temporarily set it to restriction "None" or to "IP addresses" with server IP.
                        if (isset($humo_option['google_api_key']) && $humo_option['google_api_key'] != '') {
                            //$api_key = "&key=" . $humo_option['google_api_key'];
                            $api_key = "&key=" . $humo_option['google_api_key'] . '&callback=Function.prototype';
                        }

                        //$api_key2 = ''; // Key meant for geolocation. Is protected by "IP addresses" restriction.
                        $api_key2 = '?callback=Function.prototype'; // Key meant for geolocation. Is protected by "IP addresses" restriction.           
                        if (isset($humo_option['google_api_key2']) && $humo_option['google_api_key2'] != '') {
                            $api_key2 = "&key=" . $humo_option['google_api_key2'] . '&callback=Function.prototype';
                        }
                        if ($api_key2 === "") {
                            $api_key2 = $api_key;
                        }  // if no second key is present, try to use first key.

                        $jsonurl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $loc . $api_key2;

                        //echo $api_key." - ".$api_key2."<br>";
                        //echo $jsonurl."<br>";
                        //$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$loc.$api_key2);
                        //echo $json;

                        $json = file_get_contents($jsonurl, 0, null, 0);
                        // file_get_contents won't work if "allow_url_fopen" is disabled by host for security considerations.
                        // in that case try the PHP "curl" extension that is installed on most hosts (but we still check...)
                        if (!$json) {
                            if (extension_loaded('curl')) {
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $jsonurl);
                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                                //curl_setopt($ch, CURLOPT_IPRESOLVE, CURL_IPRESOLVE_V4 );
                                $json = curl_exec($ch);
                                curl_close($ch);
                            } else {
                                echo __('<b>A location database could not be created.</b>
<p>This could mean that the PHP function "allow_url_fopen" was disabled by your webhost for security considerations and the PHP extension "curl" (which is an alternative to allow_url_fopen) is not loaded on the server.
<p>You could contact your webhost and request to either have "allow_url_fopen" enabled or "curl" loaded.');
                                exit();
                            }
                        }

                        echo '*';  // show progress by simple progress bar of *******
                        if ($count_parsed % 100 == 0) {
                            echo '<br>';
                        }

                        $json_output = json_decode($json, true);
                        if ($json_output['status'] == "OK") {
                            $map_count_found++;
                            $lat = $json_output['results'][0]['geometry']['location']['lat'];
                            $lng = $json_output['results'][0]['geometry']['location']['lng'];
                            $dbh->query("INSERT INTO humo_location (location_location, location_lat, location_lng) VALUES('" . safe_text_db($value) . "', '" . $lat . "', '" . $lng . "') ");

                            sleep(1);  // crucial, otherwise google kicks you out after a few queries
                        } elseif ($json_output['status'] == "ZERO_RESULTS") { // store locations that were not found by google geocoding
                            $map_notfound_array[] = $json_output['status'] . ' - ' . $value;
                            $map_count_notfound++;
                            $dbh->query("INSERT INTO humo_no_location (no_location_location) VALUES('" . safe_text_db($value) . "') ");
                            sleep(1);  // crucial, otherwise google kicks you out after a few queries
                        } elseif ($json_output['status'] == "OVER_QUERY_LIMIT") {
                            $flag_stop = 1;
                            break;  // out of foreach
                        } elseif ($json_output['status'] == "REQUEST_DENIED") {
                            echo "Error type: " . $json_output['status'] . "<br>";
                            echo "Error message: " . $json_output['error_message'];
                            $flag_stop = 2;
                            break;
                        } else {
                            // could be // or "INVALID_REQUEST" but that can't really happen, because this code is perfect....   ;-)
                        }
                    }
                } // end of foreach


                if ($flag_stop == 0) {
                    echo '<p style="color:red;font-size:120%"><b> ' . __('Finished updating geo-location database') . '<b></p>';
                    echo __('Finish time') . ': ' . date('G:i:s') . '<br><br>';
                    echo $map_count_found . ' ' . __('locations were successfully mapped.') . ' <br><br>';

                    if ($map_notfound_array) { // some locations were not found by geocoding
                        printf(__('The following %d new locations were passed for query, but were not found. Please check their validity.'), $map_count_notfound);
                        echo '<br>';
                        foreach ($map_notfound_array as $value) {
                            echo $value . "<br>";
                        }
                    }
                } elseif ($flag_stop == 2) {
                    // REQUEST DENIED - error message already displayed
                } else {  // the process was interrupted because of OVER_QUERY_LIMIT. Explain to the admin!
                    echo '<p style="color:red;font-size:120%"><b> ' . __('The process was interrupted because Google limits to maximum 2500 queries within one day (counting is reset at midnight PST, which is 08:00 AM GMT)') . '</b></p>';
                    printf(__('In total %1$d out of %2$d new locations were passed for query to Google.'), $count_parsed, count($_SESSION['add_locations']));
                    echo __('Tomorrow you can run this process again to add the locations that were not passed for geocoding today.');
                    echo '<p>' . $map_count_found . ' ' . __('locations were recognized by geocoding and have been saved in the database.') . '</p><br>';

                    if ($map_notfound_array) { // some locations were not found by geocoding
                        echo '<b>';
                        printf(__('The following %d new locations were passed for query, but were not found. Please check their validity.'), $map_count_notfound);
                        echo '</b><br>';
                        foreach ($map_notfound_array as $value) {
                            echo $value . "<br>";
                        }
                    }
                }

                // refresh the location_status column
                $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
                if ($temp->rowCount() > 0) {
                    refresh_status();  // see function at end of script
                }

                unset($_SESSION['add_locations']);
                ?>
            </td>
        </tr>
        <br>
        <form action="index.php?page=google_maps" method="post">
            <input type="submit" style="font-size:14px" value="<?= __('Back'); ?>" class="btn btn-sm btn-secondary">
            <br>
        </form><br>
    <?php
    }  // end - if add to database

    //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ MAIN SCREEN on entry into the google maps option in the admin menu

    else {  // main screen
        if (isset($_POST['deletedatabase'])) {
            $dbh->query("DROP TABLE humo_location");
            $result = $db_functions->update_settings('geo_trees', '');
        }
        if (isset($_POST['refresh_no_locs'])) { // refresh non-indexable locations table
            $new_no_locs = array();
            // (only take bapt place if no birth place and only take burial place if no death place)
            $unionstring = "SELECT pers_birth_place FROM humo_persons
                UNION SELECT pers_bapt_place FROM humo_persons WHERE pers_birth_place = ''
                UNION SELECT pers_death_place FROM humo_persons
                UNION SELECT pers_buried_place FROM humo_persons WHERE pers_death_place = ''";

            // from here on we can use only "pers_birth_place", since the UNION puts also all other locations under pers_birth_place
            $map_person = $dbh->query("SELECT pers_birth_place, count(*) AS quantity
                FROM (" . $unionstring . ") AS x GROUP BY pers_birth_place ");

            // make array of all stored non indexable locations
            $no_location = $dbh->query("SELECT no_location_location FROM humo_no_location");
            while (@$no_locationDb = $no_location->fetch(PDO::FETCH_OBJ)) {
                $non_exist_locs[] = $no_locationDb->no_location_location;
            }

            while (@$personDb = $map_person->fetch(PDO::FETCH_OBJ)) { // loop thru all locations in database
                foreach ($non_exist_locs as $value) {  // loop thru stored list of non-indexable loactions
                    if ($value == $personDb->pers_birth_place) { // check if this non-indexable location indeed still exists in the birth/death places in database
                        $new_no_locs[] = $value;  // if it does - add to array
                    }
                }
            }
            $dbh->query("TRUNCATE TABLE humo_no_location");
            foreach ($new_no_locs as $value) {
                $dbh->query("INSERT INTO humo_no_location (no_location_location) VALUES('" . safe_text_db($value) . "') ");
            }
        }


        //~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CHECK FOR GOOGLE MAPS API KEY ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    ?>
        <tr class="table_header">
            <th><?= __('World map API Keys'); ?></th>
        </tr>
        <tr>
            <td>
                <?php
                $use_world_map = 'Google';
                $use_world_query = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'use_world_map'");
                $use_worldDb = $use_world_query->fetch(PDO::FETCH_OBJ);
                if ($use_worldDb) {
                    $use_world_map = $use_worldDb->setting_value;
                    // *** Update value ***
                    if (isset($_POST['use_world_map']) && ($_POST['use_world_map'] == 'OpenStreetMap' || $_POST['use_world_map'] == 'Google')) {
                        $temp = $dbh->query("UPDATE humo_settings SET setting_value='" . $_POST['use_world_map'] . "' WHERE setting_variable='use_world_map'");
                        $use_world_map = $_POST['use_world_map'];
                        $humo_option["use_world_map"] = $_POST['use_world_map'];
                    }
                } elseif (isset($_POST['use_world_map']) && $_POST['use_world_map'] == 'OpenStreetMap') {
                    // *** No value in database, add new value ***
                    $temp = $dbh->query("INSERT INTO humo_settings SET setting_variable='use_world_map', setting_value='OpenStreetMap'");
                    $use_world_map = $_POST['use_world_map'];
                    $humo_option["use_world_map"] = $_POST['use_world_map'];
                }
                ?>
                <form action="index.php?page=google_maps" method="post" style="display:inline">
                    <input type="radio" name="use_world_map" value="Google" <?= $use_world_map == 'Google' ? ' checked' : '' ?>> <?= __('Use Google Maps'); ?><br>
                    <input type="radio" name="use_world_map" value="OpenStreetMap" <?= $use_world_map == 'OpenStreetMap' ? ' checked' : '' ?>> <?= __('Use OpenStreetMap'); ?><br>
                    <input type="submit" style="font-size:14px" value="<?= __('Save'); ?>" name="api_save" class="btn btn-sm btn-secondary">
                </form>

                <?php if (isset($humo_option["use_world_map"]) && $humo_option["use_world_map"] == 'Google') {; ?>
                    <h3><?= __('Google Maps API Keys'); ?></h3>
                    <?= __('To use the Google maps options, you need a Google account.'); ?>
                    <?= __('If you don\'t have a Google account, first create one. Once logged into your Google account, go to:'); ?>
                    <?= '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key#get-an-api-key" target="_blank">' . __('Get API key') . '</a>'; ?>
                    <?= __('and follow the instructions.'); ?><br>
                    <strong><?= __('Create two keys'); ?>:</strong><br>
                    <ul>
                        <li>
                            <?= __('For the first key, set restriction to <strong>"HTTP referrers"</strong> and enter your website domain name.'); ?><br>
                            <?= __('If your domain looks like \'www.mydomain.com\', enter:'); ?><strong> *.mydomain.com/*</strong><br><?= __('If your domain looks like \'mydomain.com\', enter:'); ?> <strong>mydomain.com/*</strong>
                        </li><br>

                        <?php
                        //Function to try every way to resolve domain IP. Is more accurate than good old: gethostbyname($_SERVER['SERVER_NAME']) or gethostbyname(gethostname()) ;
                        function get_host()
                        {
                            if (isset($_SERVER['HTTP_X_FORWARDED_HOST']) && ($host = $_SERVER['HTTP_X_FORWARDED_HOST'])) {
                                $elements = explode(',', $host);
                                $host = trim(end($elements));
                            } elseif (!$host = $_SERVER['HTTP_HOST']) {
                                if (!$host = $_SERVER['SERVER_NAME']) {
                                    $host = empty($_SERVER['SERVER_ADDR']) ? '' : $_SERVER['SERVER_ADDR'];
                                }
                            }
                            // Remove port number from host
                            $host = preg_replace('/:\d+$/', '', $host);
                            return trim($host);
                        }
                        // get IPv4 address
                        $ip = gethostbyname(get_host());
                        // get IPv6 address
                        $ipv6 = dns_get_record(get_host(), DNS_AAAA);

                        echo '<li>' . __('For the second key, set restriction to <strong>"IP addresses"</strong> and enter your server IP.') . " " . __('Not your computer\'s IP!') . "<br>";
                        echo __('Your server IP would seem to be:') . " <strong>" . $ip . "</strong><br>";
                        if (isset($ipv6[0]['ipv6'])) {  // contains the IPv6 address is present
                            echo __('Your server also has an IPv6 address. If the above IP doesn\'t work, try the IPv6 which would seem to be:') . " <strong>" . $ipv6[0]['ipv6'] . "</strong><br>";
                        }
                        echo __('If this doesn\'t work, contact your provider and try to obtain the proper IP address from them.') . '<br><br>';
                        ?>
                        <li><?= __('Once you receive the keys enter them in the two fields below and save.'); ?><br></li>
                    </ul>
                <?php
                }

                $api_1 = '';
                // *** Admin requested to delete the existing key - show field to enter updated key ***
                $api_query = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'google_api_key'");
                $apiDb = $api_query->fetch(PDO::FETCH_OBJ);
                if ($apiDb) {
                    $api_1 = $apiDb->setting_value;
                    // *** Update value ***
                    if (isset($_POST['api_1'])) {
                        $temp = $dbh->query("UPDATE humo_settings SET setting_value='" . $_POST['api_1'] . "' WHERE setting_variable='google_api_key'");
                        $api_1 = $_POST['api_1'];
                    }
                } elseif (isset($_POST['api_1'])) {
                    // *** No value in database, add new value ***
                    $temp = $dbh->query("INSERT INTO humo_settings SET setting_variable='google_api_key', setting_value='" . $_POST['api_1'] . "'");
                    $api_1 = $_POST['api_1'];
                }

                $api_2 = '';
                $api_query = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'google_api_key2'");
                $api_2Db = $api_query->fetch(PDO::FETCH_OBJ);
                if ($api_2Db) {
                    $api_2 = $api_2Db->setting_value;
                    // *** Update value ***
                    if (isset($_POST['api_2'])) {
                        $temp = $dbh->query("UPDATE humo_settings SET setting_value='" . $_POST['api_2'] . "' WHERE setting_variable='google_api_key2'");
                        $api_2 = $_POST['api_2'];
                    }
                } elseif (isset($_POST['api_2'])) {
                    // *** No value in database, add new value ***
                    $temp = $dbh->query("INSERT INTO humo_settings SET setting_variable='google_api_key2', setting_value='" . $_POST['api_2'] . "'");
                    $api_2 = $_POST['api_2'];
                }

                $api_geokeo = '';
                $api_query = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable = 'geokeo_api_key'");
                $api_2Db = $api_query->fetch(PDO::FETCH_OBJ);
                if ($api_2Db) {
                    $api_geokeo = $api_2Db->setting_value;
                    // *** Update value ***
                    if (isset($_POST['api_geokeo'])) {
                        $temp = $dbh->query("UPDATE humo_settings SET setting_value='" . $_POST['api_geokeo'] . "' WHERE setting_variable='geokeo_api_key'");
                        $api_geokeo = $_POST['api_geokeo'];
                    }
                } elseif (isset($_POST['api_geokeo']) && $_POST['api_geokeo'] != '') {
                    // *** No value in database, add new value ***
                    $temp = $dbh->query("INSERT INTO humo_settings SET setting_variable='geokeo_api_key', setting_value='" . $_POST['api_geokeo'] . "'");
                    $api_geokeo = $_POST['api_geokeo'];
                }
                ?>

                <?php if (isset($humo_option["use_world_map"]) && $humo_option["use_world_map"] == 'Google') {; ?>
                    <form action="index.php?page=google_maps" method="post" style="display:inline">
                        <?= __('API key'); ?> 1 (restriction: <strong>HTTP referrers</strong>):
                        <input type="text" id="api_1" name="api_1" value="<?= $api_1; ?>" size="40">
                        <input type="submit" style="font-size:14px" value="<?= __('Save'); ?>" name="api_save" class="btn btn-sm btn-secondary">
                    </form><br>

                    <form action="index.php?page=google_maps" method="post" style="display:inline">
                        <?= __('API key') . " 2 (restriction: <strong>IP addresses</strong>): "; ?>
                        &nbsp;&nbsp;&nbsp;<input type="text" id="api_2" name="api_2" value="<?= $api_2; ?>" size="40">
                        <input type="submit" style="font-size:14px" value="<?= __('Save'); ?>" name="api2_save" class="btn btn-sm btn-secondary">
                    </form><br>
                <?php } ?>

                <!-- OpenStreetMap -->
                <?php if (isset($humo_option["use_world_map"]) && $humo_option["use_world_map"] == 'OpenStreetMap') {; ?>
                    <h3><?= __('OpenStreetMap API Keys'); ?></h3>
                    <?= __('To use OpenStreetMap we need geolocation data of all places. Go to <a href="https://geokeo.com" target="_blank">https://geokeo.com</a> and create an account to get the API key.'); ?><br>

                    <form action="index.php?page=google_maps" method="post" style="display:inline">
                        <?= __('API key') . ' Geokeo: '; ?>
                        <input type="text" id="api_geokeo" name="api_geokeo" value="<?= $api_geokeo; ?>" size="40">
                        <input type="submit" style="font-size:14px" value="<?= __('Save'); ?>" name="api_save" class="btn btn-sm btn-secondary">
                    </form><br><br>
                <?php } ?>
            </td>
        </tr>

        <!-- ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ CREATE/UPDATE GEOLOCATION DATABASE ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~ -->
        <tr class="table_header">
            <th><?= __('Create or update geolocation database'); ?></th>
        </tr>

        <tr>
            <td>
                <?php
                if (isset($_POST['check_new'])) { // the "Check" button was pressed
                    $unionstring = '';

                    if (isset($_SESSION['geo_tree']) && $_SESSION['geo_tree'] != "all_geo_trees") {
                        // (only take bapt place if no birth place and only take burial place if no death place)
                        $unionstring .= "SELECT pers_birth_place FROM humo_persons WHERE pers_tree_id='" . $_SESSION['geo_tree'] . "' UNION
                            SELECT pers_bapt_place FROM humo_persons WHERE pers_tree_id='" . $_SESSION['geo_tree'] . "' AND pers_birth_place = '' UNION
                            SELECT pers_death_place FROM humo_persons WHERE pers_tree_id='" . $_SESSION['geo_tree'] . "' UNION
                            SELECT pers_buried_place FROM humo_persons WHERE pers_tree_id='" . $_SESSION['geo_tree'] . "' AND pers_death_place = ''";
                    } else {
                        // (only take bapt place if no birth place and only take burial place if no death place)
                        $unionstring .= "SELECT pers_birth_place FROM humo_persons
                            UNION SELECT pers_bapt_place FROM humo_persons WHERE pers_birth_place = ''
                            UNION SELECT pers_death_place FROM humo_persons
                            UNION SELECT pers_buried_place FROM humo_persons WHERE pers_death_place = ''";
                    }

                    // from here on we can use only "pers_birth_place", since the UNION puts also all other locations under pers_birth_place
                    $map_person = $dbh->query("SELECT pers_birth_place, count(*) AS quantity FROM (" . $unionstring . ") AS x GROUP BY pers_birth_place ");

                    $add_locations = array();

                    // make array of all existing locations in database	
                    $exist_locs = array();
                    $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
                    $is_database = false;
                    if ($temp->rowCount() > 0) {
                        // there is a database 
                        $is_database = true;
                        $location = $dbh->query("SELECT location_location FROM humo_location");
                        while (@$locationDb = $location->fetch(PDO::FETCH_OBJ)) {
                            $exist_locs[] = $locationDb->location_location;
                        }
                    }

                    // make array of all non-recognized locations (from previous attempts)
                    $non_exist_locs = array();
                    $temp = $dbh->query("SHOW TABLES LIKE 'humo_no_location'");
                    $is_noloc_database = false;
                    if ($temp->rowCount() > 0) {
                        // there is a table with not found locations 
                        $is_noloc_database = true;
                        $no_location = $dbh->query("SELECT no_location_location FROM humo_no_location");
                        while (@$no_locationDb = $no_location->fetch(PDO::FETCH_OBJ)) {
                            $non_exist_locs[] = $no_locationDb->no_location_location;
                        }
                    } else {
                        // Database table for non recognized locations doesn't exists so create it.
                        // We need it in a minute to prevent google api queries that we already know won't yield results
                        $temp = $dbh->query("SHOW TABLES LIKE 'humo_no_location'");
                        if (!$temp->rowCount()) {
                            // no such database table exists - so create it
                            // (Re)create a location table "humo_no_location"
                            // It has 2 columns:
                            //     1. id
                            //     2. name of location
                            $nolocationtbl = "CREATE TABLE humo_no_location (
                                no_location_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                                no_location_location VARCHAR(120) CHARACTER SET utf8
                            )";
                            $dbh->query($nolocationtbl);
                        }
                    }

                    $thistree_non_exist = array();
                    // This will hold only those non-indexable locations (from $non_exist_locs) that appear in the chosen tree (or trees if 'all' was chosen)

                    while (@$personDb = $map_person->fetch(PDO::FETCH_OBJ)) {
                        // for each location we check:
                        // 1. if it has already been indexed (if so, skip it)
                        // 2. if in the past it couldn't be found by google api (if so, skip it)
                        // If neither of these two cases - add it to the array of locations to be queried through google api ($add_locations)

                        if ($is_database) {
                            // there is a database - see if the location already exists and if so - continue with a next loop
                            foreach ($exist_locs as $value) {
                                if ($value == $personDb->pers_birth_place) {  // this location has already been mapped
                                    continue 2;  //continue the outer while loop 
                                }
                            }
                            if ($is_noloc_database) { // stored list of non-indexable locations exists
                                foreach ($non_exist_locs as $value) {
                                    if ($value == $personDb->pers_birth_place) {  // this location cannot be mapped (not found by google api)
                                        $thistree_non_exist[] = $value;
                                        continue 2;  //continue the outer while loop
                                    }
                                }
                            }
                        }
                        // add the new location to an array for use if the user presses YES
                        if ($personDb->pers_birth_place) {
                            $add_locations[] = $personDb->pers_birth_place;
                        }
                    }

                    /*
                    while (@$personDb=$map_person->fetch(PDO::FETCH_OBJ)){
                        $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
                        if($temp->rowCount() > 0) {
                            // there is a database - see if the location already exists and if so - continue with a next loop
                            $location=$dbh->query("SELECT location_location FROM humo_location");
                            while (@$locationDb=$location->fetch(PDO::FETCH_OBJ)){
                                if($locationDb->location_location == $personDb->pers_birth_place) {
                                    continue 2;  //continue the outer while loop
                                }
                            }
                        }
                        // add the new location to an array for use if the user presses YES
                        if($personDb->pers_birth_place) { $add_locations[] = $personDb->pers_birth_place; }
                    }
                    */
                    //echo 'Calculating......<br><br>'; // with a large existing data base and large number of locations to check this can take a second or two...
                    if (!$add_locations) {
                        echo '<p>' . __('No new locations were found to add to the database') . '</p>';
                        if ($thistree_non_exist) {
                            echo '<b>';
                            printf(__('The following %d locations are already known as non-indexable by Google. Please check their validity.'), count($thistree_non_exist));
                            echo '</b><br>';
                            foreach ($thistree_non_exist as $value) {
                                echo $value . "<br>";
                            }
                        }
                    } else {
                        $_SESSION['add_locations'] = $add_locations;
                        $new_locations = count($add_locations);

                        $map_totalsecs = $new_locations * 1.25;
                        $map_mins = floor($map_totalsecs / 60);

                        //$map_secs = $map_totalsecs % 60;
                        $map_secs = floor($map_totalsecs) % 60; // *** Use floor to prevent error message in PHP 8.x ***

                        $one_tree = "";
                        if (isset($_SESSION['geo_tree']) && $_SESSION['geo_tree'] != "all_geo_trees") {
                            $tree_search_sql2 = "SELECT * FROM humo_trees WHERE tree_id='" . $_SESSION['geo_tree'] . "'";
                            $tree_search_result2 = $dbh->query($tree_search_sql2);
                            $tree_searchDb2 = $tree_search_result2->fetch(PDO::FETCH_OBJ);
                            $treetext2 = show_tree_text($tree_searchDb2->tree_id, $selected_language);
                            $one_tree = "<b>" . __('Family tree') . " " . @$treetext2['name'] . ": </b>";
                        }
                        echo $one_tree;
                        printf(__('There are %s new unique birth/ death locations to add to the database.'), $new_locations);
                        echo '<br><br>';

                        // *** Show list of locations to add to the database ***
                        foreach ($add_locations as $val) {
                            echo $val . "<br>";
                        }
                ?>
                        <br>
                        <?php printf(__('This will take approximately <b>%1$d minutes and %2$d seconds.</b>'), $map_mins, $map_secs); ?><br>
                        <?= __('Do you wish to add these locations to the database database now?'); ?><br>
                        <form action="index.php?page=google_maps" method="post">
                            <input type="submit" style="font-size:14px" value="<?= __('YES'); ?>" name="makedatabase" class="btn btn-sm btn-secondary">
                        </form><br>

                    <?php
                        if ($thistree_non_exist) {
                            echo "<br><b>";
                            printf(__('The following %d locations are already known as non-indexable by Google. Please check their validity.'), count($thistree_non_exist));
                            echo '</b><br>';
                            foreach ($thistree_non_exist as $value) {
                                echo $value . "<br>";
                            }
                        }
                    }
                } else {
                    $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
                    if ($temp->rowCount() > 0) {
                        echo '<br>' . __('A geolocation database exists.') . '<br>';
                    } else {
                        echo '<br><b>' . __('No geolocation database found.') . '</b><br>';
                    }
                    echo __('Check how many new locations have to be indexed and how long the indexing may take (approximately).');

                    // SELECT FAMILY TREE
                    $tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
                    $tree_search_result = $dbh->query($tree_search_sql);
                    $count = 0;
                    $selected = '';
                    if (!isset($_SESSION['geo_tree']) || isset($_POST['database']) && $_POST['database'] == "all_geo_trees") {
                        $selected = ' selected';
                        $_SESSION['geo_tree'] = "all_geo_trees";
                    }
                    ?>
                    &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                    <br>
                    <form method="POST" action="index.php?page=google_maps" style="display : inline;">
                        <select size="1" name="database" onChange="this.form.submit();">
                            <option value="all_geo_trees" <?= $selected; ?>><?= __('All family trees'); ?></option>
                            <?php
                            while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                                $selected = '';
                                if (isset($_POST['database'])) {
                                    if ($tree_searchDb->tree_prefix == $_POST['database']) {
                                        $selected = ' selected';
                                        $_SESSION['geo_tree'] = $tree_searchDb->tree_id;
                                    }
                                } elseif (isset($_SESSION['geo_tree']) && $_SESSION['geo_tree'] == $tree_searchDb->tree_id) {
                                    $selected = ' selected';
                                }
                                $treetext = show_tree_text($tree_searchDb->tree_id, $selected_language);
                                echo '<option value="' . $tree_searchDb->tree_prefix . '"' . $selected . '>' . @$treetext['name'] . '</option>';
                                $count++;
                            }
                            ?>
                        </select>
                    </form><br>

                    <form method="POST" name="checkform" action="index.php?page=google_maps" style="display : inline;">
                        <br><input type="submit" name="check_new" value="<?= __('Check'); ?>" class="btn btn-sm btn-secondary"><br><br>
                    </form>
                <?php } ?>
            </td>
        </tr>

        <?php
        $temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
        if ($temp->rowCount() > 0) {
        ?>
            <!-- REFRESH BIRTH/DEATH STATUS -->
            <tr class="table_header">
                <th><?= __('Refresh birth/ death status and tree affiliation of all locations'); ?></th>
            </tr>

            <tr>
                <td>
                    <?php
                    if (isset($_POST['refresh'])) {
                        refresh_status();  // see function at end of script
                        echo '<div style="color:red;font-weight:bold;">' . __('The birth/ death status and tree affiliation has been refreshed.') . '</div><br>';
                    } else {
                    ?>
                        <form action="index.php?page=google_maps" method="post">
                            <?php printf(__('The "Find a location on the map" pull-down displays a list according to the chosen tree and the birth/ death mapping choice. For this to work properly, the birth/death status and tree affiliation of all locations has to be up to date.<br><br><b>TIP:</b> When you import a gedcom, you can mark the option "Add new locations to geo-location database" and the location status of existing locations will also be updated automatically! (If you didn\'t mark this option on import, use the "Update geolocation database" above. This will also refresh the existing location status).<p><b>When to use this button:</b><ul><li> if you edited location data directly with the %s editor</li><li>if you wish to delete all locations that have become obsolete (mark the box below)</li></ul></p>'), 'HuMo-genealogy'); ?>
                            <input type="checkbox" name="purge"> <?= __('Also delete all locations that have become obsolete (not connected to any persons anymore)'); ?><br>
                            <input type="submit" style="font-size:14px" value="<?= __('Refresh'); ?>" name="refresh" class="btn btn-sm btn-secondary">
                        </form>
                    <?php } ?>
                </td>
            </tr>

            <!-- EDIT GEOLOCATION DATABASE -->
            <tr class="table_header">
                <th><?= __('Edit geolocation database'); ?></th>
            </tr>
            <?php
            echo '<tr><td>';
            if (isset($_POST['loc_change']) || isset($_POST['loc_add']) || isset($_POST['yes_change']) || isset($_POST['cancel_change'])) {
                // the "change" or "add" buttons were used -- show the place that was added or changed
                // the "YES" was pressed -- the lat/lng of bottom box are used so they have to be shown
                // the "NO" button was pressed -- we leave the bottom box as it was so the user may consider again
                $lat = $_POST['add_lat'];
                $lng = $_POST['add_lng'];
            } else {
                if (isset($_POST['flag_form'])) {
                    // the pulldown was used -- so show the place that was chosen
                    $result = $dbh->query("SELECT * FROM humo_location WHERE location_id = " . safe_text_db($_POST['loc_find']));
                } elseif (isset($_POST['loc_delete'])) {
                    // "delete" was used -- so show map+marker for first on list
                    $dbh->query("DELETE FROM humo_location WHERE location_id = " . $_POST['loc_del_id']);
                    $result = $dbh->query("SELECT * FROM humo_location ORDER BY location_location");
                } else {
                    // page was newly entered -- so show map+marker for first on list
                    $result = $dbh->query("SELECT * FROM humo_location ORDER BY location_location");
                }
                if ($result->rowCount() > 0) { // doesn't exist yet
                    $row = $result->fetch();
                    $lat = $row['location_lat'];
                    $lng = $row['location_lng'];
                }
            }

            // *** Google maps ***
            if ($use_world_map == 'Google') {
                $api_key = '';
                if (isset($humo_option['google_api_key']) && $humo_option['google_api_key'] != '') {
                    $api_key = '?key=' . $humo_option['google_api_key'] . '&callback=Function.prototype';
                } else {
                    $api_key = '?callback=Function.prototype';
                }
                // TODO check this...
                if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                    echo '<script src="https://maps.google.com/maps/api/js' . $api_key . '"></script>';
                } else {
                    echo '<script src="https://maps.google.com/maps/api/js' . $api_key . '"></script>';
                }
            ?>
                <script>
                    function disableEnterKey(e) {
                        // works for FF and Chrome
                        var key;
                        if (window.event) {
                            key = window.event.keyCode;
                        } else {
                            key = e.which;
                        }
                        if (key == 13) {
                            return false;
                        } else {
                            return true;
                        }
                    }

                    function testForEnter()
                    // works for IE
                    {
                        if (navigator.userAgent.indexOf("MSIE") != -1) {
                            if (event.keyCode == 13) {
                                event.cancelBubble = true;
                                event.returnValue = false;
                            }
                        }
                    }
                </script>
                <script>
                    var geocoder;
                    var map;
                    var markers = [];

                    function initialize() {
                        geocoder = new google.maps.Geocoder();
                        <?php
                        echo 'var latlng = new google.maps.LatLng(' . $lat . ',' . $lng . ');';
                        ?>
                        var myOptions = {
                            zoom: 12,
                            center: latlng,
                            mapTypeId: google.maps.MapTypeId.ROADMAP
                        }
                        map = new google.maps.Map(document.getElementById("map_canvas"), myOptions);
                        <?php
                        echo 'document.getElementById("latbox").innerHtml = latlng.lat().toFixed(5);';
                        echo 'document.getElementById("lngbox").innerHtml = latlng.lng().toFixed(5);';
                        ?>
                        map.setCenter(latlng);

                        markers[0] = new google.maps.Marker({
                            map: map,
                            position: latlng,
                            draggable: true
                        });
                        google.maps.event.addListener(markers[0], 'drag', function(event) {
                            document.getElementById("latbox").value = event.latLng.lat().toFixed(5);
                            document.getElementById("lngbox").value = event.latLng.lng().toFixed(5);
                        });
                    }

                    function clearMarker() {
                        for (j = 0; j < markers.length; j++) {
                            if (markers[j] != undefined) markers[j].setMap(null);
                        }
                    }

                    function codeAddress() {
                        clearMarker();
                        var address = document.getElementById("address").value;
                        geocoder.geocode({
                            'address': address
                        }, function(results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                document.getElementById("latbox").innerHtml = results[0].geometry.location.lat().toFixed(5);
                                document.getElementById("lngbox").innerHtml = results[0].geometry.location.lng().toFixed(5);
                                document.getElementById("latbox").value = results[0].geometry.location.lat().toFixed(5);
                                document.getElementById("lngbox").value = results[0].geometry.location.lng().toFixed(5);
                                map.setCenter(results[0].geometry.location);

                                markers[1] = new google.maps.Marker({
                                    map: map,
                                    position: results[0].geometry.location,
                                    draggable: true
                                });
                                google.maps.event.addListener(markers[1], 'drag', function(event) {
                                    document.getElementById("latbox").value = event.latLng.lat().toFixed(5);
                                    document.getElementById("lngbox").value = event.latLng.lng().toFixed(5);

                                });
                            } else {
                                alert("Geocode was not successful for the following reason: " + status);
                            }
                            markers.push(markers[1]);
                        });
                    }
                </script>
            <?php }; ?>

            <body onload="initialize()">
                <table style="width:100%;border:none">
                    <tr>
                        <td valign="top" colspan="2">
                            <?php
                            $leave_bottom = false;
                            if (isset($_POST['loc_delete'])) {
                                // delete location
                                echo '<span style="color:red;font-weight:bold;">' . __('Deleted location:') . str_replace("\'", "'", safe_text_db($_POST['loc_del_name'])) . '</span><br>';
                            }
                            if (isset($_POST['loc_change']) || isset($_POST['yes_change']) || isset($_POST['cancel_change'])) {
                                // "change" location or "yes" button pressed
                                $pos = strpos($_POST['add_name'], $_POST['loc_del_name']);

                                if (!isset($_POST['cancel_change']) && ($pos !== false || isset($_POST['yes_change']))) {  // the name in pulldown appears in the name in the search box
                                    $dbh->query("UPDATE humo_location SET location_location ='" . safe_text_db($_POST['loc_del_name']) . "', location_lat =" . floatval($_POST['add_lat']) . ", location_lng = " . floatval($_POST['add_lng']) . " WHERE location_location = '" . safe_text_db($_POST['loc_del_name']) . "'");
                                    echo '<span style="color:red;font-weight:bold;">' . __('Changed location:') . ' ' . str_replace("\'", "'", safe_text_db($_POST['loc_del_name'])) . '</span><br>';
                                } elseif (isset($_POST['cancel_change'])) {
                                    $leave_bottom = true;
                                } else {
                                    $leave_bottom = true;
                                    echo '<span style="color:red;font-weight:bold;">Are you sure you want to change the lat/lng of </span><b>' . $_POST['loc_del_name'] . '</b>';
                                    echo '<span style="color:red;font-weight:bold;"> and set them to those that belong to </span><b>' . $_POST['add_name'] . '?</b></span><br>';
                            ?>
                                    <form method="POST" name="check_change" action="index.php?page=google_maps" style="display : inline;">
                                        <input type="hidden" name="add_lat" value="<?= $_POST['add_lat']; ?>">
                                        <input type="hidden" name="add_lng" value="<?= $_POST['add_lng']; ?>">
                                        <input type="hidden" name="add_name" value="<?= $_POST['add_name']; ?>">
                                        <input type="hidden" name="loc_del_name" value="<?= $_POST['loc_del_name']; ?>">
                                        <input type="hidden" name="loc_del_id" value="<?= $_POST['loc_del_id']; ?>">
                                        <input type="submit" name="yes_change" value="<?= __('YES'); ?>" class="btn btn-sm btn-secondary">
                                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                        <input type="submit" name="cancel_change" value="<?= __('Cancel'); ?>" class="btn btn-sm btn-secondary">
                                    </form><br><br>
                            <?php
                                }
                            }
                            if (isset($_POST['loc_add'])) {
                                //  we added new location
                                //  make sure this location doesn't exist yet! otherwise we get doubles
                                //  if the location already exists do as if "change" was pressed.
                                @$result = $dbh->query("SELECT location_location FROM humo_location WHERE location_location = '" . safe_text_db($_POST['add_name']) . "'");
                                if ($result->rowCount() == 0) { // doesn't exist yet
                                    $dbh->query("INSERT INTO humo_location (location_location, location_lat, location_lng) VALUES('" . safe_text_db($_POST['add_name']) . "','" . floatval($_POST['add_lat']) . "','" . floatval($_POST['add_lng']) . "') ");
                                    echo '<span style="color:red;font-weight:bold;">' . __('Added location:') . ' ' . str_replace("\'", "'", safe_text_db($_POST['add_name'])) . '</span><br>';
                                } else { // location already exists, just update the lat/lng
                                    $dbh->query("UPDATE humo_location SET location_location ='" . $_POST['add_name'] . "', location_lat =" . floatval($_POST['add_lat']) . ", location_lng = " . floatval($_POST['add_lng']) . " WHERE location_location = '" . safe_text_db($_POST['add_name']) . "'");
                                    echo '<span style="color:red;font-weight:bold;"> ' . str_replace("\'", "'", safe_text_db($_POST['add_name'])) . ': Location already exists.<br>Updated lat/lng.</span><br>';
                                }
                            }

                            ?>
                            <form method="POST" name="dbform" action="index.php?page=google_maps" style="display : inline;">
                                <?php
                                $loc_list = $dbh->query("SELECT * FROM humo_location ORDER BY location_location");
                                echo '<input type="hidden" name="flag_form" value="dummy">';
                                echo '<select size="1" onChange="document.dbform.submit();" name="loc_find" id="loc_find">';
                                $find_default = true;
                                while ($loc_listDb = $loc_list->fetch(PDO::FETCH_OBJ)) {
                                    $selected = '';
                                    if (isset($_POST['loc_find'])) {
                                        if ($loc_listDb->location_id == $_POST['loc_find']) {
                                            $selected = " selected";
                                        }
                                    } elseif (isset($_POST['loc_change']) || isset($_POST['yes_change']) || isset($_POST['cancel_change'])) {
                                        if ($loc_listDb->location_location == $_POST['loc_del_name']) {
                                            $selected = " selected";
                                        }
                                    } elseif (isset($_POST['loc_add'])) {
                                        if ($loc_listDb->location_location == $_POST['add_name']) {
                                            $selected = " selected";
                                        }
                                    } elseif ($find_default) {
                                        // first location on the list
                                        $_POST['loc_find'] = $loc_listDb->location_id;
                                        $find_default = false;
                                    }
                                    echo '<option value="' . $loc_listDb->location_id . '"' . $selected . ' >' . $loc_listDb->location_location . ' </option>';
                                }
                                echo '</select>';
                                ?>
                            </form>
                        </td>

                        <td style="width:360px" rowspan="12">
                            <!-- Show Google Maps -->
                            <?php if ($use_world_map == 'Google') { ?>
                                <div id="map_canvas" style="height:360px; width:360px;"></div>
                            <?php } ?>

                            <?php
                            if (isset($_POST['loc_add'])) {
                                // we have added or changed a location - so show that location after page load
                                $result = $dbh->query("SELECT * FROM humo_location WHERE location_location = '" . safe_text_db($_POST['add_name']) . "'");
                            } elseif (isset($_POST['loc_change']) || isset($_POST['yes_change']) || isset($_POST['cancel_change'])) {
                                // we have changed a location by "Change" or by "YES" - so show that location after page load
                                // or we pushed the "NO" button and want to leave the situation as it was
                                $result = $dbh->query("SELECT * FROM humo_location WHERE location_id = " . $_POST['loc_del_id']);
                            } elseif (isset($_POST['loc_find'])) {
                                // default: show the location that was selected with the pull down box
                                $result = $dbh->query("SELECT * FROM humo_location WHERE location_id = " . $_POST['loc_find']);
                            }
                            $resultDb = $result->fetch(PDO::FETCH_OBJ);

                            $location_id = '';
                            if ($resultDb) {
                                $location_id = $resultDb->location_id;
                            }
                            $location_location = '';
                            if ($resultDb) {
                                $location_location = $resultDb->location_location;
                            }
                            $location_lat = '';
                            if ($resultDb) {
                                $location_lat = $resultDb->location_lat;
                            }
                            $location_lng = '';
                            if ($resultDb) {
                                $location_lng = $resultDb->location_lng;
                            }

                            $search_name = $location_location;
                            $search_lat = $location_lat;
                            $search_lng = $location_lng;

                            if ($leave_bottom) {
                                $search_name = $_POST['add_name'];
                                $search_lat =  $_POST['add_lat'];
                                $search_lng =  $_POST['add_lng'];
                            }
                            ?>

                            <?php
                            // *** OpenStreetMap ***
                            if ($use_world_map == 'OpenStreetMap') {
                            ?>
                                <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
                                <script src="../assets/leaflet/leaflet.js"></script>

                                <div id="map" style="width: 600px; height: 300px;"></div>

                            <?php
                                // *** Map using fitbound (all markers visible) ***
                                //echo '<script>
                                //    var map = L.map("map").setView([48.85, 2.35], 10);
                                //    var markers = [';
                                echo '<script>
                                    var map = L.map("map").setView([' . $location_lat . ', ' . $location_lng . '], 15);
                                    var markers = [';

                                // *** Add all markers from array ***
                                //echo 'L.marker([' . $location_lat . ', ' . $location_lng . ']) .bindPopup(\'' . addslashes($location_location) . '\')';

                                echo '];
                                    var group = L.featureGroup(markers).addTo(map);
                                    setTimeout(function () {
                                        map.fitBounds(group.getBounds());
                                    }, 1000);
                                    L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
                                        attribution: \'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors\'
                                    }).addTo(map);
                                </script>';
                            }
                            ?>
                        </td>
                    </tr>

                    <form method="POST" name="delform" action="index.php?page=google_maps" style="display : inline;">
                        <tr>
                            <th colspan="2"><?= __('Details from the database'); ?></th>
                        </tr>
                        <tr>
                            <td><?= __('Location'); ?>:</td>
                            <td><input type="text" id="loc_name" name="loc_name" value="<?= $location_location; ?>" size="20" style="background-color:#d8d8d8;color:#585858" READONLY></td>
                        </tr>
                        <tr>
                            <td><?= __('Latitude'); ?>:</td>
                            <td><input type="text" id="loc_lat" name="loc_lat" value="<?= $location_lat; ?>" size="20" style="background-color:#d8d8d8;color:#585858" READONLY></td>
                        </tr>
                        <tr>
                            <td><?= __('Longitude'); ?>:</td>
                            <td><input type="text" id="loc_lng" name="loc_lng" value="<?= $location_lng; ?>" size="20" style="background-color:#d8d8d8;color:#585858" READONLY></td>
                        </tr>
                        <tr>
                            <td align="center" colspan="2">
                                <input type="hidden" name="loc_del_id" value="<?= $location_id; ?>">
                                <input type="hidden" name="loc_del_name" value="<?= $location_location; ?>">
                                <input type="submit" name="loc_delete" value="<?= __('Delete this location'); ?>" class="btn btn-sm btn-danger">
                            </td>
                        </tr>
                        <tr>
                            <td style="border:none;height:20px"></td>
                        </tr>
                        <tr>
                            <th colspan="2"><?= __('Change or add locations'); ?><br><?= __('(You can also drag the marker!)'); ?></th>
                        </tr>
                        <tr>
                            <td colspan="2"><input id="address" onKeyPress="return disableEnterKey(event);" onKeyDown="testForEnter();" type="textbox" value="<?= $search_name; ?>" size="36" name="add_name">
                                <input type="button" name="loc_search" value="<?= __('Search'); ?>" onclick="codeAddress();" class="btn btn-sm btn-secondary">
                            </td>
                        </tr>
                        <tr>
                            <td><?= __('Latitude'); ?>:</td>
                            <td><input size="20" type="text" id="latbox" name="add_lat" onKeyPress="return disableEnterKey(event);" onKeyDown="testForEnter();" value="<?= $search_lat; ?>"></td>
                        </tr>
                        <tr>
                            <td><?= __('Longitude'); ?>:</td>
                            <td><input size="20" type="text" id="lngbox" name="add_lng" onKeyPress="return disableEnterKey(event);" onKeyDown="testForEnter();" value="<?= $search_lng; ?>"></td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                <input type="submit" name="loc_change" value="<?= __('Change this location'); ?>" class="btn btn-sm btn-secondary">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="submit" name="loc_add" value="<?= __('Add this location'); ?>" class="btn btn-sm btn-secondary">
                            </td>
                        </tr>
                    </form>
                </table>

                <?php
                echo '</td></tr>';

                //~~~~~~~~~~~~~~~~~~~~~~~~~~~~DELETE GEOLOCATION DATABASE~~~~~~~~~~~~~~~~~~~~~~~~~

                echo '<tr class="table_header"><th>' . __('Delete geolocation database') . '</th></tr>';
                echo '<tr><td>';

                if (isset($_POST['deletedatabase'])) {
                    echo '<p style="color:red;font-weight:bold;">' . __('Database was deleted!') . '<p>';
                } else {  // there is a database
                    $num_rows = $loc_list->rowCount();
                    printf(__('Here you can delete your entire geolocation database (%d entries).<br>If you are absolutely sure, press the button below.'), $num_rows);
                    echo '<br><form action="index.php?page=google_maps" method="post">';
                    echo '<input type="submit" value="' . __('DELETE ENTIRE GEOLOCATION DATABASE') . '" name="deletedatabase" class="btn btn-sm btn-danger">';
                    echo '<br></form><br>';
                }
                if (isset($_POST['refresh_no_locs'])) {
                    echo '<p style="color:red;font-weight:bold;">' . __('List of non-indexable locations was refreshed!') . '<p>';
                } else {
                    $temp1 = $dbh->query("SHOW TABLES LIKE 'humo_no_location'");
                    if ($temp1->rowCount() > 0) {
                        $no_loc_list = $dbh->query("SELECT * FROM humo_no_location ORDER BY no_location_location");
                        $num_rows1 = $no_loc_list->rowCount();
                        if ($num_rows1 > 0) {
                            printf(__('Here you can refresh the list of %d non-indexable locations that was stored in your database after previous geolocation processes.<br>Do this if you have corrected non-indexable locations in your data or have imported updated gedcoms and some of these locations may no longer appear in your data.'), $num_rows1);
                            echo '<br><form action="index.php?page=google_maps" method="post">';
                            echo '<input type="submit" value="' . __('REFRESH LIST OF NON-INDEXABLE LOCATIONS') . '" name="refresh_no_locs" class="btn btn-sm btn-danger">';
                            echo '<br></form><br>';
                        }
                    }
                }
                echo '</td></tr>';

                //~~~~~~~~~~~~~~~~~~~~~~~~~~~ SETTINGS ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
                echo '<tr class="table_header"><th>' . __('Settings') . '</th></tr>';
                echo '<tr><td>';
                ?>
                <form name="slider" action="index.php?page=google_maps" method="POST">
                    <?php
                    echo __('The slider has 10 steps. By default the starting year is 1560 with 9 intervals of 50 years up till 2010 and beyond.<br>You can set the starting year yourself for each tree, to suit it to the earliest years in that tree<br>The 9 intervals will be calculated automatically. Some example starting years for round intervals:<br>1110 (intv. 100), 1560 (intv. 50), 1695 (intv. 35),1740 (intv. 30), 1785 (intv. 25), 1830 (intv. 20)') . '<br><br>';

                    // *** Select family tree ***
                    $tree_id_string = " AND ( ";
                    $id_arr = explode(";", substr($humo_option['geo_trees'], 0, -1)); // substr to remove trailing ;
                    foreach ($id_arr as $value) {
                        $tree_id_string .= "tree_id='" . substr($value, 1) . "' OR ";  // substr removes leading "@" in geo_trees setting string
                    }
                    $tree_id_string = substr($tree_id_string, 0, -4) . ")"; // take off last " ON " and add ")"

                    $tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' " . $tree_id_string . " ORDER BY tree_order";
                    $tree_search_result = $dbh->query($tree_search_sql);
                    $rowspan = $tree_search_result->rowCount() + 1;
                    ?>
                    <table>
                        <tr>
                            <th><?= __('Name of tree'); ?></th>
                            <th style="text-align:center"><?= __('Starting year'); ?></th>
                            <th style="text-align:center"><?= __('Interval'); ?></th>
                            <th rowspan=<?= $rowspan; ?>>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type="submit" name="submit" value="<?= __('Change'); ?>" class="btn btn-sm btn-secondary"></th>
                        </tr>
                        <?php
                        // TODO Form inside form??
                        echo '<form method="POST" action="maps.php" style="display : inline;">';

                        while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                            ${"slider_choice" . $tree_searchDb->tree_prefix} = "1560"; // default
                            $query = "SELECT * FROM humo_settings WHERE setting_variable='gslider_" . $tree_searchDb->tree_prefix . "' ";
                            $result = $dbh->query($query);
                            $offset = "slider_choice_" . $tree_searchDb->tree_prefix;
                            if ($result->rowCount() > 0) {
                                $slider_choiceDb = $result->fetch(PDO::FETCH_OBJ);
                                ${"slider_choice" . $tree_searchDb->tree_prefix} = $slider_choiceDb->setting_value;
                                if (isset($_POST[$offset])) {
                                    $result = $db_functions->update_settings('gslider_' . $tree_searchDb->tree_prefix, $_POST[$offset]);
                                    ${"slider_choice" . $tree_searchDb->tree_prefix} = $_POST[$offset];
                                }
                            } elseif (isset($_POST[$offset])) {
                                $sql = "INSERT INTO humo_settings SET setting_variable='gslider_" . $tree_searchDb->tree_prefix . "', setting_value='" . $_POST[$offset] . "'";
                                $dbh->query($sql);
                                ${"slider_choice" . $tree_searchDb->tree_prefix} = $_POST[$offset];
                            }

                            $treetext = show_tree_text($tree_searchDb->tree_id, $selected_language);
                            $interval = round((2010 - ${"slider_choice" . $tree_searchDb->tree_prefix}) / 9);
                            echo "<tr><td>" . $treetext['name'] . "</td>";
                            echo "<td><input style='text-align:center' type='text' name='" . $offset . "' value='{${"slider_choice" .$tree_searchDb->tree_prefix}}'></td>";
                            echo "<td style='text-align:center'>" . $interval . "</td></tr>";
                        }
                        ?>
                    </table>
                </form>

                <?php
                $query = "SELECT * FROM humo_settings WHERE setting_variable='gslider_default_pos' ";
                $result = $dbh->query($query);

                if (isset($_GET['slider_default'])) {
                    if ($result->rowCount() > 0) {
                        $result = $db_functions->update_settings('gslider_default_pos', $_GET['slider_default']);
                        $sl_def = $_GET['slider_default'];
                    } else {
                        $sql = "INSERT INTO humo_settings SET setting_variable='gslider_default_pos', setting_value='" . $_GET['slider_default'] . "'";
                        $dbh->query($sql);
                        $sl_def = $_GET['slider_default'];
                    }
                } else {
                    if ($result->rowCount() > 0) {
                        $sl_default_pos = $result->fetch();
                        $sl_def = $sl_default_pos['setting_value'];
                    } else {
                        $sl_def = "all";
                    }
                }

                ?>
                <br><?= __('Default slider position'); ?>:
                <select size="1" name="slider_default" id="slider_default" onChange="window.location='index.php?page=google_maps&slider_default='+this.value;">
                    <?php
                    $selected = "";
                    if ($sl_def == "off") {
                        $selected = " selected ";
                    }
                    echo '<option value="off" ' . $selected . '>' . __('OFF position (leftmost position)') . '</option>';
                    $selected = "";
                    if ($sl_def == "all") {
                        $selected = " selected ";
                    }
                    echo '<option value="all" ' . $selected . '>' . __('Show all periods (rightmost position)') . '</option>';
                    ?>
                </select>
                <?php

                $query = "SELECT * FROM humo_settings WHERE setting_variable='google_map_type' ";
                $result = $dbh->query($query);
                if (isset($_GET['maptype_default'])) {
                    if ($result->rowCount() > 0) {
                        $result = $db_functions->update_settings('google_map_type', $_GET['maptype_default']);
                        $maptype_def = $_GET['maptype_default'];
                    } else {
                        $sql = "INSERT INTO humo_settings SET setting_variable='google_map_type', setting_value='" . $_GET['maptype_default'] . "'";
                        $dbh->query($sql);
                        $maptype_def = $_GET['maptype_default'];
                    }
                } else {
                    if ($result->rowCount() > 0) {
                        $maptype_default = $result->fetch();
                        $maptype_def = $maptype_default['setting_value'];
                    } else {
                        $maptype_def = "ROADMAP";
                    }
                }

                ?>
                <br><br><?= __('Default map type'); ?>:
                <select size="1" name="maptype_default" id="maptype_default" onChange="window.location='index.php?page=google_maps&maptype_default='+this.value;">
                    <?php
                    $selected = "";
                    if ($maptype_def == "ROADMAP") {
                        $selected = " selected ";
                    }
                    echo '<option value="ROADMAP" ' . $selected . '>' . __('Regular map (ROADMAP)') . '</option>';
                    $selected = "";
                    if ($maptype_def == "HYBRID") {
                        $selected = " selected ";
                    }
                    echo '<option value="HYBRID" ' . $selected . '>' . __('Satellite map with roads and places (HYBRID)') . '</option>';
                    ?>
                </select>
                <?php

                $query = "SELECT * FROM humo_settings WHERE setting_variable='google_map_zoom' ";
                $result = $dbh->query($query);
                if (isset($_GET['map_zoom_default'])) {
                    if ($result->rowCount() > 0) {
                        $result = $db_functions->update_settings('google_map_zoom', $_GET['map_zoom_default']);
                        $mapzoom_def = $_GET['map_zoom_default'];
                    } else {
                        $sql = "INSERT INTO humo_settings SET setting_variable='google_map_zoom', setting_value='" . $_GET['map_zoom_default'] . "'";
                        $dbh->query($sql);
                        $mapzoom_def = $_GET['map_zoom_default'];
                    }
                } else {
                    if ($result->rowCount() > 0) {
                        $mapzoom_default = $result->fetch();
                        $mapzoom_def = $mapzoom_default['setting_value'];
                    } else {
                        $mapzoom_def = "11";
                    }
                }
                ?>

                <br><br><?= __('Default zoom'); ?>:
                <select size="1" name="map_zoom_default" id="map_zoom_default" onChange="window.location='index.php?page=google_maps&map_zoom_default='+this.value;">
                    <?php
                    for ($x = 1; $x < 15; $x++) {
                        $selected = "";
                        if ($mapzoom_def == $x) {
                            $selected = " selected ";
                        }
                        echo '<option value="' . $x . '" ' . $selected . '>' . $x . '</option>';
                    }
                    ?>
                </select>
        <?php

            //END NEW
            //echo '</form>';
            echo '</td></tr>';
        }
    }
    //else {
    //  echo __('No geolocation database found');
    //}
        ?>
</table>

<?php
// *** Function to refresh location_status column ***
function refresh_status()
{
    global $dbh, $humo_option;

    // make sure the location_status column exists. If not create it
    $result = $dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
    $exists = $result->rowCount();
    if (!$exists) {
        $dbh->query("ALTER TABLE humo_location ADD location_status TEXT AFTER location_lng");
    }

    $all_loc = $dbh->query("SELECT location_location FROM humo_location");
    while ($all_locDb = $all_loc->fetch(PDO::FETCH_OBJ)) {
        $loca_array[$all_locDb->location_location] = "";
    }
    $status_string = "";

    $tree_id_string = " WHERE ";
    $id_arr = explode(";", substr($humo_option['geo_trees'], 0, -1)); // substr to take off last ;
    foreach ($id_arr as $value) {
        $tree_id_string .= "pers_tree_id='" . substr($value, 1) . "' OR ";   // substr removes leading "@" in geo_trees setting string
    }
    $tree_id_string = substr($tree_id_string, 0, -4); // take off last " OR"

    $result = $dbh->query("SELECT pers_tree_id, pers_tree_prefix, pers_birth_place, pers_bapt_place, pers_death_place, pers_buried_place
        FROM humo_persons" . $tree_id_string);
    while ($resultDb = $result->fetch(PDO::FETCH_OBJ)) {
        if (isset($loca_array[$resultDb->pers_birth_place]) && strpos($loca_array[$resultDb->pers_birth_place], $resultDb->pers_tree_prefix . "birth ") === false) {
            $loca_array[$resultDb->pers_birth_place] .= $resultDb->pers_tree_prefix . "birth ";
        }
        if (isset($loca_array[$resultDb->pers_bapt_place]) && strpos($loca_array[$resultDb->pers_bapt_place], $resultDb->pers_tree_prefix . "bapt ") === false) {
            $loca_array[$resultDb->pers_bapt_place] .= $resultDb->pers_tree_prefix . "bapt ";
        }
        if (isset($loca_array[$resultDb->pers_death_place]) && strpos($loca_array[$resultDb->pers_death_place], $resultDb->pers_tree_prefix . "death ") === false) {
            $loca_array[$resultDb->pers_death_place] .= $resultDb->pers_tree_prefix . "death ";
        }
        if (isset($loca_array[$resultDb->pers_buried_place]) && strpos($loca_array[$resultDb->pers_buried_place], $resultDb->pers_tree_prefix . "buried ") === false) {
            $loca_array[$resultDb->pers_buried_place] .= $resultDb->pers_tree_prefix . "buried ";
        }
    }

    foreach ($loca_array as $key => $value) {
        if (isset($_POST['purge']) && ($value === "" || $value == NULL)) {
            $dbh->query("DELETE FROM humo_location WHERE location_location = '" . addslashes($key) . "'");
        } else {
            $dbh->query("UPDATE humo_location SET location_status = '" . $value . "' WHERE location_location = '" . addslashes($key) . "'");
        }
    }
}

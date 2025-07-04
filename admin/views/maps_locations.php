<?php

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

if (isset($_POST['makedatabase'])) {
    $count_parsed = 0;
    $map_notfound_array = array();
    $map_count_found = 0;
    $map_count_notfound = 0;
    $flag_stop = 0;
}

if (isset($_POST['loc_delete2']) && is_numeric($_POST['location_id'])) {
    $dbh->query("DELETE FROM humo_location WHERE location_id = " . $_POST['location_id']);
}

// *** Empty entire geolocation table ***
if (isset($_POST['deletedatabase2'])) {
    $dbh->query("TRUNCATE TABLE humo_location");
    $db_functions->update_settings('geo_trees', '');
}

if (isset($_POST['check_new'])) {
    if ($maps['geo_tree_id'] != '') {
        // (only take bapt place if no birth place and only take burial place if no death place)
        $unionstring = "SELECT pers_birth_place FROM humo_persons WHERE pers_tree_id='" . $maps['geo_tree_id'] . "' 
            UNION SELECT pers_bapt_place FROM humo_persons WHERE pers_tree_id='" . $maps['geo_tree_id'] . "' AND pers_birth_place = ''
            UNION SELECT pers_death_place FROM humo_persons WHERE pers_tree_id='" . $maps['geo_tree_id'] . "'
            UNION SELECT pers_buried_place FROM humo_persons WHERE pers_tree_id='" . $maps['geo_tree_id'] . "' AND pers_death_place = ''";
    } else {
        // (only take bapt place if no birth place and only take burial place if no death place)
        $unionstring = "SELECT pers_birth_place FROM humo_persons
            UNION SELECT pers_bapt_place FROM humo_persons WHERE pers_birth_place = ''
            UNION SELECT pers_death_place FROM humo_persons
            UNION SELECT pers_buried_place FROM humo_persons WHERE pers_death_place = ''";
    }
    //echo $unionstring;
    // from here on we can use only "pers_birth_place", since the UNION puts also all other locations under pers_birth_place
    $map_person = $dbh->query("SELECT pers_birth_place, count(*) AS quantity FROM (" . $unionstring . ") AS x GROUP BY pers_birth_place ");

    $add_locations = array();

    // make array of all existing locations in database	
    $exist_locs = array();
    $location = $dbh->query("SELECT location_location FROM humo_location WHERE location_lat IS NOT NULL");
    while ($locationDb = $location->fetch(PDO::FETCH_OBJ)) {
        $exist_locs[] = $locationDb->location_location;
    }

    // make array of all non-recognized locations (from previous attempts)
    $non_exist_locs = array();

    $no_location = $dbh->query("SELECT location_location FROM humo_location WHERE location_lat IS NULL");
    while ($no_locationDb = $no_location->fetch(PDO::FETCH_OBJ)) {
        $non_exist_locs[] = $no_locationDb->location_location;
    }

    $thistree_non_exist = array();

    while ($personDb = $map_person->fetch(PDO::FETCH_OBJ)) {
        // for each location we check:
        // 1. if it has already been indexed (if so, skip it)
        // 2. if in the past it couldn't be found by google api (if so, skip it)
        // If neither of these two cases - add it to the array of locations to be queried through google api ($add_locations)

        // TODO check this code.
        foreach ($exist_locs as $value) {
            if ($value == $personDb->pers_birth_place) {  // this location has already been mapped
                continue 2;  //continue the outer while loop 
            }
        }

        // TODO check this code.
        foreach ($non_exist_locs as $value) {
            if ($value == $personDb->pers_birth_place) {  // this location cannot be mapped (not found by google api)
                $thistree_non_exist[] = $value;
                continue 2;  //continue the outer while loop
            }
        }

        // add the new location to an array for use if the user presses YES
        if ($personDb->pers_birth_place) {
            $add_locations[] = $personDb->pers_birth_place;
        }
    }
}
?>

<!-- Alert boxes -->
<form action="index.php?page=maps&amp;menu=locations" method="post">
    <!-- Confirm to delete entire geolocation table -->
    <?php if (isset($_POST['deletedatabase'])) { ?>
        <div class="alert alert-danger" role="alert">
            <?= __('Are your sure you want to delete the <b>entire geolocation database</b>?'); ?>
            <input type="submit" value="<?= __('Yes'); ?>" name="deletedatabase2" class="btn btn-sm btn-danger">
            <input type="submit" value="<?= __('No'); ?>" name="" class="btn btn-sm btn-primary">
        </div>
    <?php } ?>
    <?php if (isset($_POST['deletedatabase2'])) { ?>
        <div class="alert alert-success" role="alert"><?= __('Geolocation database is deleted!'); ?></div>
    <?php } ?>

    <?php
    /*
    if (isset($_POST['refresh'])) {
        refresh_status($dbh, $humo_option);  // see function at end of script
    ?>
        <div class="alert alert-success" role="alert"><?= __('The locationlist has been refreshed.'); ?></div>
    <?php }
    */
    ?>

    <?php if (isset($_POST['loc_delete']) && (is_numeric($_POST['location_id']))) { ?>
        <input type="hidden" name="location_id" value="<?= $_POST['location_id']; ?>">
        <div class="alert alert-danger" role="alert">
            <?php printf(__('Are your sure you want to delete location "%s"?'), str_replace("\'", "'", $safeTextDb->safe_text_db($_POST['location_location']))); ?>
            <input type="submit" value="<?= __('Yes'); ?>" name="loc_delete2" class="btn btn-sm btn-danger">
            <input type="submit" value="<?= __('No'); ?>" name="" class="btn btn-sm btn-primary">
        </div>
    <?php } ?>
    <?php if (isset($_POST['loc_delete2'])) { ?>
        <div class="alert alert-success" role="alert"><?= __('Location is deleted.'); ?></div>
    <?php } ?>
</form>


<div class="p-3 m-2 genealogy_search container-md">
    <div class="row mb-1 p-2 bg-primary-subtle">
        <?= __('Create or update geolocation database'); ?>
    </div>

    <?php
    // *** Select family tree ***
    $tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
    $tree_search_result = $dbh->query($tree_search_sql);
    ?>
    <form method="POST" action="index.php?page=maps&amp;menu=locations">
        <div class="row mb-2">
            <div class="col-md-12 mb-1">
                <?= __('Check how many new locations have to be indexed and how long the indexing may take (approximately).'); ?><br>
            </div>

            <div class="col-md-auto">
                <select name="tree_id" class="form-select form-select-sm">
                    <option value="0"><?= __('All family trees'); ?></option>
                    <?php
                    while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                        $treetext = $showTreeText -> show_tree_text($tree_searchDb->tree_id, $selected_language);
                    ?>
                        <option value="<?= $tree_searchDb->tree_id; ?>" <?= $tree_searchDb->tree_id == $maps['geo_tree_id'] ? 'selected' : ''; ?>>
                            <?= $treetext['name']; ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="col-md-auto">
                <input type="submit" name="check_new" value="<?= __('Check'); ?>" class="btn btn-sm btn-secondary"><br><br>
            </div>
        </div>
    </form>

    <?php
    if (isset($_POST['check_new'])) {
        if (!$add_locations) {
    ?>
            <div class="alert alert-success" role="alert">
                <?= __('No new locations were found to add to the database'); ?>
            </div>
        <?php
        } else {
            $_SESSION['add_locations'] = $add_locations;
            $new_locations = count($add_locations);

            $map_totalsecs = $new_locations * 1.25;
            $map_mins = floor($map_totalsecs / 60);
            $map_secs = floor($map_totalsecs) % 60; // *** Use floor to prevent error message in PHP 8.x ***

            $one_tree = "";
            if ($maps['geo_tree_id'] != '') {
                $tree_search_sql2 = "SELECT * FROM humo_trees WHERE tree_id='" . $maps['geo_tree_id'] . "'";
                $tree_search_result2 = $dbh->query($tree_search_sql2);
                $tree_searchDb2 = $tree_search_result2->fetch(PDO::FETCH_OBJ);
                $treetext2 = $showTreeText -> show_tree_text($tree_searchDb2->tree_id, $selected_language);
                $one_tree = "<b>" . __('Family tree') . " " . $treetext2['name'] . ": </b>";
            }
        ?>
            <div class="alert alert-warning" role="alert">
                <?= $one_tree; ?>
                <?php printf(__('There are %s new unique birth/ death locations to add to the database.'), $new_locations); ?><br>

                <br>
                <?php printf(__('This will take approximately <b>%1$d minutes and %2$d seconds.</b>'), $map_mins, $map_secs); ?><br>
                <?= __('Do you wish to add these locations to the database now?'); ?>
                <form action="index.php?page=maps&amp;menu=locations" method="post">
                    <input type="submit" value="<?= __('Yes'); ?>" name="makedatabase" class="btn btn-sm btn-primary">
                    <input type="submit" value="<?= __('No'); ?>" class="btn btn-sm btn-secondary">
                </form><br>

                <!-- Show list of locations to add to the database -->
                <?php foreach ($add_locations as $val) { ?>
                    <?= $val; ?><br>
                <?php } ?>

            </div>

        <?php
        }

        if ($thistree_non_exist) {
        ?>
            <div class="alert alert-warning" role="alert">
                <b><?php printf(__('The following %d locations are already known as non-indexable. Please check their validity.'), count($thistree_non_exist)); ?></b><br>

                <form action="index.php?page=maps&amp;menu=locations" method="post">
                    <input type="hidden" name="non_exist_locations" value="1">
                    <input type="hidden" name="check_new" value="1">
                    <input type="submit" value="<?= __('Retry to index these locations'); ?>" name="makedatabase" class="btn btn-sm btn-primary">
                </form><br>

                <?php foreach ($thistree_non_exist as $value) { ?>
                    <?= $value; ?><br>
                <?php } ?>
            </div>
    <?php
        }
    }
    ?>


    <?php if (isset($_POST['makedatabase'])) { ?>
        <?= __('Started adding to data base.'); ?><br>
        <?php
        sleep(1); // make sure this gets printed before the next is executed

        // TODO Sept. 2024: refresh option is no longer needed. Also check geo_trees variable.
        // If the locations are taken from one tree, add the id of this tree to humo_settings "geo_trees", if not already there
        // so we can update correctly with the "REFRESH BIRTH/DEATH STATUS" option further on.
        if ($maps['geo_tree_id'] != '') {
            if (strpos($humo_option['geo_trees'], "@" . $maps['geo_tree_id'] . ";") === false) { // this tree_id does not appear already
                $db_functions->update_settings('geo_trees', $humo_option['geo_trees'] . "@" . $maps['geo_tree_id']);
                // add tree_prefix if not already present
                $humo_option['geo_trees'] .= "@" . $maps['geo_tree_id'] . ';'; // humo_option is used further on before page is refreshed so we have to update it manually
            }
        } else {
            $str = "";
            $tree_search_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
            $tree_search_result = $dbh->query($tree_search_sql);
            while ($tree_searchDb = $tree_search_result->fetch(PDO::FETCH_OBJ)) {
                $str .= "@" . $tree_searchDb->tree_id . ";";
            }
            $db_functions->update_settings('geo_trees', $str);
            $humo_option['geo_trees'] = $str; // humo_option is used further on before page is refreshed so we have to update it manually
        }

        // *** Index new locations or non indexed locations from database ***
        if (isset($_POST['non_exist_locations'])) {
            $index_locations = $thistree_non_exist;
        } else {
            $index_locations = $_SESSION['add_locations'];
        }

        //foreach ($_SESSION['add_locations'] as $value) {
        foreach ($index_locations as $value) {
            $count_parsed++;
            //if($count_parsed<110 OR $count_parsed > 125) continue;
            $loc = urlencode($value);

            // *** OpenStreetMap, use GeoKeo to get geolocation data ***
            if ($maps['use_world_map'] == 'OpenStreetMap') {
                $url = "https://geokeo.com/geocode/v1/search.php?q=" . $loc . "&api=" . $maps['geokeo_api'];
                $json = file_get_contents($url);
                $json = json_decode($json);
                //if(array_key_exists('status',$json)){
                if (isset($json->status) && $json->status == 'ok') {
                    $map_count_found++;
                    //$address = $json->results[0]->formatted_address;
                    $latitude = $json->results[0]->geometry->location->lat;
                    $longitude = $json->results[0]->geometry->location->lng;

                    if (isset($_POST['non_exist_locations'])) {
                        $stmt = $dbh->prepare("UPDATE humo_location SET location_location = :location, location_lat = :lat, location_lng = :lng WHERE location_location = :location");
                        $stmt->bindValue(':location', $value, PDO::PARAM_STR);
                        $stmt->bindValue(':lat', $latitude, PDO::PARAM_STR);
                        $stmt->bindValue(':lng', $longitude, PDO::PARAM_STR);
                        $stmt->execute();
                    } else {
                        $stmt = $dbh->prepare("INSERT INTO humo_location (location_location, location_lat, location_lng) VALUES (:location, :lat, :lng)");
                        $stmt->bindValue(':location', $value, PDO::PARAM_STR);
                        $stmt->bindValue(':lat', $latitude, PDO::PARAM_STR);
                        $stmt->bindValue(':lng', $longitude, PDO::PARAM_STR);
                        $stmt->execute();
                    }

                    sleep(1);
                } else {
                    //echo $json->status.'!! ';
                    //$map_notfound_array[] = $json_output['status'] . ' - ' . $value;
                    //$map_count_notfound++;

                    if (!isset($_POST['non_exist_locations'])) {
                        $stmt = $dbh->prepare("INSERT INTO humo_location (location_location) VALUES (:location)");
                        $stmt->bindValue(':location', $value, PDO::PARAM_STR);
                        $stmt->execute();
                    }

                    sleep(1);
                }
            } else {
                // *** Google Maps ***
                // Key 1 is meant for showing maps and should be set to restriction: "HTTP referrers". This key will only be used here if no second key is present.
                // This key will only work here if admin temporarily set it to restriction "None" or to "IP addresses" with server IP.

                // Key 2 meant for geolocation. Is protected by "IP addresses" restriction.

                // if no second key is present, try to use first key.
                //$api_key = $maps['google_api2'];
                //if ($maps['google_api2'] === "") {
                //    $api_key = $maps['google_api1'];
                //}

                //$jsonurl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $loc . '&key=' . $api_key . '&callback=Function.prototype';
                $jsonurl = "https://maps.googleapis.com/maps/api/geocode/json?address=" . $loc . '&key=' . $maps['google_api1'] . '&callback=Function.prototype';

                //echo $api_key." - ".$api_key2."<br>";
                //echo $jsonurl."<br>";
                //$json = file_get_contents("https://maps.googleapis.com/maps/api/geocode/json?address=".$loc.$api_key);
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

                    if (isset($_POST['non_exist_locations'])) {
                        $stmt = $dbh->prepare("UPDATE humo_location SET location_location = :location, location_lat = :lat, location_lng = :lng WHERE location_location = :location");
                        $stmt->bindValue(':location', $value, PDO::PARAM_STR);
                        $stmt->bindValue(':lat', $lat, PDO::PARAM_STR);
                        $stmt->bindValue(':lng', $lng, PDO::PARAM_STR);
                        $stmt->execute();
                    } else {
                        $stmt = $dbh->prepare("INSERT INTO humo_location (location_location, location_lat, location_lng) VALUES (:location, :lat, :lng)");
                        $stmt->bindValue(':location', $value, PDO::PARAM_STR);
                        $stmt->bindValue(':lat', $lat, PDO::PARAM_STR);
                        $stmt->bindValue(':lng', $lng, PDO::PARAM_STR);
                        $stmt->execute();
                    }

                    sleep(1);  // crucial, otherwise google kicks you out after a few queries
                } elseif ($json_output['status'] == "ZERO_RESULTS") { // store locations that were not found by google geocoding
                    $map_notfound_array[] = $json_output['status'] . ' - ' . $value;
                    $map_count_notfound++;

                    if (!isset($_POST['non_exist_locations'])) {
                        $stmt = $dbh->prepare("INSERT INTO humo_location (location_location) VALUES (:location)");
                        $stmt->bindValue(':location', $value, PDO::PARAM_STR);
                        $stmt->execute();
                    }

                    sleep(1);  // crucial, otherwise google kicks you out after a few queries
                } elseif ($json_output['status'] == "OVER_QUERY_LIMIT") {
                    $flag_stop = 1;
                    break;  // out of foreach
                } elseif ($json_output['status'] == "REQUEST_DENIED") {
        ?>
                    <div class="alert alert-danger" role="alert">
                        <?= "Error type: " . $json_output['status']; ?><br>
                        <?= "Error message: " . $json_output['error_message']; ?>
                    </div>
            <?php
                    $flag_stop = 2;
                    break;
                } else {
                    // could be // or "INVALID_REQUEST" but that can't really happen, because this code is perfect....   ;-)
                }
            }
        }


        if ($flag_stop == 0) {
            ?>
            <div class="alert alert-success" role="alert">
                <?= $map_count_found . ' ' . __('locations were successfully mapped.'); ?>
            </div>
        <?php
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
        //refresh_status($dbh, $humo_option);  // see function at end of script

        unset($_SESSION['add_locations']);
        ?>
    <?php } ?>

    <?php
    /* Sept. 2024: no longer needed.
    <form action="index.php?page=maps&amp;menu=locations" method="post">
        <div class="row mb-2">
            <div class="col-md-12">
                <?= __('Refresh the location list to display a proper functional "Find a location on the map" list.'); ?><br>
                <input type="checkbox" name="purge" class="form-check-input"> <?= __('Also delete all locations that have become obsolete (not connected to any persons anymore).'); ?>
                <input type="submit" value="<?= __('Refresh'); ?>" name="refresh" class="btn btn-sm btn-secondary">
            </div>
        </div>
    </form>
    */
    ?>

    <div class="row mb-2">
        <div class="col-md-12">
            <?php
            $loc_list = $dbh->query("SELECT location_id FROM humo_location WHERE location_lat IS NOT NULL ORDER BY location_location");
            $num_rows = $loc_list->rowCount();

            $no_loc_list = $dbh->query("SELECT location_id FROM humo_location WHERE location_lat IS NULL ORDER BY location_location");
            $num_rows1 = $no_loc_list->rowCount();
            ?>
            <br>
            <form action="index.php?page=maps&amp;menu=locations" method="post">
                <input type="submit" value="<?= __('Delete entire geolocation database'); ?>" name="deletedatabase" class="btn btn-sm btn-danger">
                <?php printf(__('%d locations.'), $num_rows); ?>
                <?php printf(__('%d non-indexable locations.'), $num_rows1); ?>
            </form>
        </div>
    </div>

</div>

<div class="p-3 m-2 genealogy_search container-md">
    <div class="row mb-1 p-2 bg-primary-subtle">
        <?= __('Edit geolocation database'); ?>
    </div>

    <div class="row mb-2">
        <div class="col-md-12">
            <?php
            if (isset($_POST['loc_change']) || isset($_POST['loc_add']) || isset($_POST['yes_change']) || isset($_POST['cancel_change'])) {
                // the "change" or "add" buttons were used -- show the place that was added or changed
                // the "YES" was pressed -- the lat/lng of bottom box are used so they have to be shown
                // the "NO" button was pressed -- we leave the bottom box as it was so the user may consider again
                $lat = $_POST['location_lat'];
                $lng = $_POST['location_lng'];
            } else {
                if (isset($_POST['flag_form'])) {
                    // TODO check for numeric
                    // the pulldown was used -- so show the place that was chosen
                    $stmt = $dbh->prepare("SELECT * FROM humo_location WHERE location_id = :location_id");
                    $stmt->bindValue(':location_id', $_POST['loc_find'], PDO::PARAM_INT);
                    $stmt->execute();
                    $result = $stmt;
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
            if ($maps['use_world_map'] == 'Google') {
                // TODO check this...
                if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                    echo '<script src="https://maps.google.com/maps/api/js?key=' . $maps['google_api1'] . '&callback=Function.prototype"></script>';
                } else {
                    echo '<script src="http://maps.google.com/maps/api/js?key=' . $maps['google_api1'] . '&callback=Function.prototype"></script>';
                }
            ?>
                <script>
                    /*
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
                    */

                    /*
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
                    */
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
            <?php } ?>

            <div class="row mb-2">
                <div class="col-md-6">

                    <?php
                    $leave_bottom = false;
                    if (isset($_POST['loc_change']) || isset($_POST['yes_change']) || isset($_POST['cancel_change'])) {
                        // "change" location or "yes" button pressed
                        $pos = strpos($_POST['add_name'], $_POST['location_location']);

                        if (!isset($_POST['cancel_change']) && ($pos !== false || isset($_POST['yes_change']))) {  // the name in pulldown appears in the name in the search box
                            $stmt = $dbh->prepare("UPDATE humo_location SET location_location = :location_location, location_lat = :location_lat, location_lng = :location_lng WHERE location_id = :location_id");
                            $stmt->bindValue(':location_location', $_POST['location_location'], PDO::PARAM_STR);
                            $stmt->bindValue(':location_lat', floatval($_POST['location_lat']));
                            $stmt->bindValue(':location_lng', floatval($_POST['location_lng']));
                            $stmt->bindValue(':location_id', $_POST['location_id'], PDO::PARAM_INT);
                            $stmt->execute();
                        } elseif (isset($_POST['cancel_change'])) {
                            $leave_bottom = true;
                        } else {

                            // TODO remove this part. Just process the change.
                            $leave_bottom = true;
                            echo '<span style="color:red;font-weight:bold;">Are you sure you want to change the lat/lng of </span><b>' . $_POST['location_location'] . '</b>';
                            echo '<span style="color:red;font-weight:bold;"> and set them to those that belong to </span><b>' . $_POST['add_name'] . '?</b></span><br>';
                    ?>
                            <form method="POST" name="check_change" action="index.php?page=maps&amp;menu=locations">
                                <input type="hidden" name="location_lat" value="<?= $_POST['location_lat']; ?>">
                                <input type="hidden" name="location_lng" value="<?= $_POST['location_lng']; ?>">
                                <input type="hidden" name="add_name" value="<?= $_POST['add_name']; ?>">
                                <input type="hidden" name="location_location" value="<?= $_POST['location_location']; ?>">
                                <input type="hidden" name="location_id" value="<?= $_POST['location_id']; ?>">
                                <input type="submit" name="yes_change" value="<?= __('YES'); ?>" class="btn btn-sm btn-secondary">
                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                <input type="submit" name="cancel_change" value="<?= __('Cancel'); ?>" class="btn btn-sm btn-secondary">
                            </form><br><br>

                        <?php
                        }
                    }

                    // *** Add or change location ***
                    if (isset($_POST['loc_add'])) {
                        $stmt = $dbh->prepare("SELECT location_location FROM humo_location WHERE location_location = :location_location");
                        $stmt->bindValue(':location_location', $_POST['add_name'], PDO::PARAM_STR);
                        $stmt->execute();
                        $result = $stmt;
                        if ($result->rowCount() == 0) {
                            // doesn't exist yet
                            $stmt = $dbh->prepare("INSERT INTO humo_location (location_location, location_lat, location_lng) VALUES (:location, :lat, :lng)");
                            $stmt->bindValue(':location', $_POST['add_name'], PDO::PARAM_STR);
                            $stmt->bindValue(':lat', floatval($_POST['location_lat']));
                            $stmt->bindValue(':lng', floatval($_POST['location_lng']));
                            $stmt->execute();
                        } elseif (is_numeric($_POST['location_id'])) {
                            $stmt = $dbh->prepare("UPDATE humo_location SET location_location = :location_location, location_lat = :location_lat, location_lng = :location_lng WHERE location_id = :location_id");
                            $stmt->bindValue(':location_location', $_POST['add_name'], PDO::PARAM_STR);
                            $stmt->bindValue(':location_lat', floatval($_POST['location_lat']));
                            $stmt->bindValue(':location_lng', floatval($_POST['location_lng']));
                            $stmt->bindValue(':location_id', $_POST['location_id'], PDO::PARAM_INT);
                            $stmt->execute();
                        ?>
                            <span style="color:red;font-weight:bold;"><?= __('Location already exists. Updated latitude/longitude'); ?></span><br>
                    <?php
                        }
                    }

                    $loc_list = $dbh->query("SELECT * FROM humo_location ORDER BY location_location");
                    $find_default = true;
                    ?>
                    <form method="POST" name="dbform" action="index.php?page=maps&amp;menu=locations">
                        <input type="hidden" name="flag_form" value="dummy">

                        <div class="row mb-2">
                            <div class="col-md-6">
                                <select size="1" onChange="document.dbform.submit();" name="loc_find" id="loc_find" class="form-select form-select-sm">
                                    <?php
                                    while ($loc_listDb = $loc_list->fetch(PDO::FETCH_OBJ)) {
                                        $selected = '';
                                        if (isset($_POST['loc_find'])) {
                                            if ($loc_listDb->location_id == $_POST['loc_find']) {
                                                $selected = " selected";
                                            }
                                        } elseif (isset($_POST['loc_change']) || isset($_POST['yes_change']) || isset($_POST['cancel_change'])) {
                                            if ($loc_listDb->location_location == $_POST['location_location']) {
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
                                    ?>
                                        <option value="<?= $loc_listDb->location_id; ?>" <?= $selected; ?>>
                                            <?= $loc_listDb->location_location; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                    </form>


                    <?php
                    if (isset($_POST['loc_add'])) {
                        // we have added or changed a location - so show that location after page load
                        $stmt = $dbh->prepare("SELECT * FROM humo_location WHERE location_location = :location_location");
                        $stmt->bindValue(':location_location', $_POST['add_name'], PDO::PARAM_STR);
                        $stmt->execute();
                        $result = $stmt;
                    } elseif (isset($_POST['loc_change']) || isset($_POST['yes_change']) || isset($_POST['cancel_change'])) {
                        // we have changed a location by "Change" or by "YES" - so show that location after page load
                        // or we pushed the "NO" button and want to leave the situation as it was
                        $result = $dbh->query("SELECT * FROM humo_location WHERE location_id = " . $_POST['location_id']);
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
                        $search_lat =  $_POST['location_lat'];
                        $search_lng =  $_POST['location_lng'];
                    }
                    ?>


                    <form method="POST" name="delform" action="index.php?page=maps&amp;menu=locations">
                        <input type="hidden" name="location_id" value="<?= $location_id; ?>">
                        <input type="hidden" name="location_location" value="<?= $location_location; ?>">
                        <div class="row mb-2">
                            <div class="col-md-6">
                                <input type="text" name="add_name" id="address" value="<?= $search_name; ?>" size="36" class="form-control form-control-sm">
                            </div>
                            <div class="col-md-auto">
                                <input type="button" name="loc_search" value="<?= __('Search'); ?>" onclick="codeAddress();" class="btn btn-sm btn-secondary">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-5">
                                <?= __('Latitude'); ?>
                            </div>
                            <div class="col-md-3">
                                <input type="text" size="20" id="latbox" name="location_lat" value="<?= $search_lat; ?>" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-5">
                                <?= __('Longitude'); ?>
                            </div>
                            <div class="col-md-3">
                                <input type="text" size="20" id="lngbox" name="location_lng" value="<?= $search_lng; ?>" class="form-control form-control-sm">
                            </div>
                        </div>

                        <?= __('You can also drag the marker.'); ?><br><br>

                        <input type="submit" name="loc_change" value="<?= __('Change this location'); ?>" class="btn btn-sm btn-secondary">&nbsp;
                        <input type="submit" name="loc_add" value="<?= __('Add this location'); ?>" class="btn btn-sm btn-secondary">
                        <input type="submit" name="loc_delete" value="<?= __('Delete this location'); ?>" class="btn btn-sm btn-danger">

                    </form>

                </div>
                <div class="col-md-6">

                    <!-- Show Google Maps -->
                    <?php if ($maps['use_world_map'] == 'Google') { ?>
                        <div id="map_canvas" style="height:360px;"></div>
                    <?php } ?>

                    <!-- OpenStreetMap -->
                    <?php if ($maps['use_world_map'] == 'OpenStreetMap') { ?>
                        <link rel="stylesheet" href="../assets/leaflet/leaflet.css">
                        <script src="../assets/leaflet/leaflet.js"></script>

                        <div id="map" style="height: 300px;"></div>

                    <?php
                        // *** Map using fitbound (all markers visible) ***
                        echo '<script>
                            var map = L.map("map").setView([' . $location_lat . ', ' . $location_lng . '], 15);
                            var markers = [';

                        echo '];
                            var group = L.featureGroup(markers).addTo(map);
                            setTimeout(function () {
                                map.fitBounds(group.getBounds());
                            }, 1000);
                            L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
                                attribution: \'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors\'
                            }).addTo(map);
                        </script>';
                    } ?>

                </div>
            </div>
        </div>
    </div>

    <?php
    // *** Function to refresh location_status column ***
    /*
    function refresh_status($dbh, $humo_option)
    {
        $all_loc = $dbh->query("SELECT location_location FROM humo_location");
        while ($all_locDb = $all_loc->fetch(PDO::FETCH_OBJ)) {
            $loca_array[$all_locDb->location_location] = "";
        }

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
    */

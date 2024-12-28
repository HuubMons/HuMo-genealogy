<?php
class MapsModel
{
    public function select_world_map($humo_option)
    {
        $select = 'Google';
        if (isset($humo_option["use_world_map"]) && $humo_option["use_world_map"] == 'OpenStreetMap') {
            $select = 'OpenStreetMap';
        }
        return $select;
    }

    // *** Get list of selected family names ***
    public function get_family_names()
    {
        $family_names = '';
        if (isset($_POST['items'])) {
            $family_names = $_POST['items'];
        }
        return $family_names;
    }

    // *** Show line of selected family names ***
    public function show_family_names($family_names)
    {
        $names = '';
        if ($family_names) {
            foreach ($family_names as $value) {
                $pos = strpos($value, '_');
                $pref = substr($value, $pos + 1);
                if ($pref !== '') {
                    $pref .= ' ';
                }
                $last = substr($value, 0, $pos);
                $names .= $pref . $last . ", ";
            }
            $names = substr($names, 0, -2); // take off last ", "
        }
        return $names;
    }

    public function get_maps_type()
    {
        // *** Set birth or death display. Default values ***
        // *** BE AWARE: session values are used in google_initiate script. If these are disabled, the slider doesn't work ***
        $maps['display_birth'] = true;
        $maps['display_death'] = false;
        if (!isset($_SESSION['type_death']) && !isset($_SESSION['type_death'])) {
            $_SESSION['type_birth'] = 1;
            $_SESSION['type_death'] = 0;
        }
        if (isset($_SESSION['type_death']) && $_SESSION['type_death'] == 1) {
            $maps['display_death'] = true;
            $maps['display_birth'] = false;
        }
        if (isset($_POST['map_type']) && $_POST['map_type'] == "type_birth") {
            $_SESSION['type_birth'] = 1;
            $_SESSION['type_death'] = 0;
            $maps['display_birth'] = true;
            $maps['display_death'] = false;
        }
        if (isset($_POST['map_type']) && $_POST['map_type'] == "type_death") {
            $_SESSION['type_death'] = 1;
            $_SESSION['type_birth'] = 0;
            $maps['display_death'] = true;
            $maps['display_birth'] = false;
        }
        return $maps;
    }

    // *** Slider settings ***
    public function get_slider_settings($dbh, $tree_prefix_quoted)
    {
        // *** Slider defaults ***
        $maps['slider_min'] = 1560;  // first year shown on slider
        $maps['slider_step'] = "50";     // interval
        $maps['slider_off'] = "1510"; // OFF position (first year minus step, year is not shown)
        $maps['slider_year'] = date("Y");

        // check for stored min value, created with google maps admin menu
        $query = "SELECT setting_value FROM humo_settings WHERE setting_variable='gslider_" . $tree_prefix_quoted . "' ";
        $result = $dbh->query($query);
        if ($result->rowCount() > 0) {
            $sliderDb = $result->fetch(PDO::FETCH_OBJ);
            $maps['slider_min'] = $sliderDb->setting_value;
            $maps['slider_step'] = floor(($maps['slider_year'] - $maps['slider_min']) / 9);
            $maps['slider_off'] = $maps['slider_min'] - $maps['slider_step'];
        }

        $qry = "SELECT setting_value FROM humo_settings WHERE setting_variable='gslider_default_pos'";
        $result = $dbh->query($qry);
        if ($result->rowCount() > 0) {
            $def = $result->fetch(); // defaults to array
            if ($def['setting_value'] == "off") {
                // slider at leftmost position
                $maps['slider_default_year'] = $maps['slider_off'];
                $maps['slider_default_display'] = "------>";
                $maps['slider_makesel'] = "";
            } else {
                // slider ar rightmost position
                $maps['slider_default_year'] = $maps['slider_year'];
                $maps['slider_default_display'] = $maps['slider_default_year'];
                $maps['slider_makesel'] = " makeSelection(3); ";
            }
        } else {
            //$maps['slider_default_year'] = $maps['slider_off']; $maps['slider_default_display'] = "------>"; $maps['slider_makesel']=""; // slider at leftmost position 
            $maps['slider_default_year'] = $maps['slider_year'];
            $maps['slider_default_display'] = $maps['slider_default_year'];
            $maps['slider_makesel'] = " makeSelection(3); ";  // slider at rightmost position (default)
        }
        return $maps;
    }

    public function get_maps_descendants($dbh, $tree_id)
    {
        global $desc_array;

        // *** Find descendants of chosen person ***
        $maps['desc_chosen_name'] = '';
        $maps['desc_array'] = '';

        $_SESSION['desc_array'] = '';

        // *** Example: persged=I529&persfams=F191;F192 ***
        if (isset($_GET['persged']) && isset($_GET['persfams'])) {
            $chosenperson = $_GET['persged'];
            $persfams = $_GET['persfams'];
            $persfams_arr = explode(';', $persfams);

            //also check privacy
            $myresult = $dbh->query("SELECT pers_lastname, pers_firstname, pers_prefix FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $chosenperson . "'");
            $myresultDb = $myresult->fetch(PDO::FETCH_OBJ);
            $chosenname = $myresultDb->pers_firstname . ' ' . strtolower(str_replace('_', '', $myresultDb->pers_prefix)) . ' ' . $myresultDb->pers_lastname;

            // *** Start function here - recursive if started ***
            $desc_array = [];

            // TODO improve code. Also check global anscestors_descendant script.
            global $descendant_array;
            $generation_number = 0; // generation number
            $nr_generations = 20;
            get_descendants($persfams_arr[0], $chosenperson, $generation_number, $nr_generations);
            $desc_array = $descendant_array;

            if ($desc_array != '') {
                $desc_array = array_unique($desc_array); // removes duplicate persons (because of related ancestors)
                $_SESSION['desc_array'] = $desc_array; // for use in namesearch.php

                $maps['desc_chosen_name'] = $chosenname;
                $maps['desc_array'] = $desc_array;
            }
        } else {
            unset($_SESSION['desc_array']);
        }

        return $maps;
    }

    public function get_maps_ancestors($dbh, $tree_id)
    {
        // *** Find ancestors ***
        // TODO $_GET['anc_persfams'] isn't used.
        global $anc_array, $db_functions;

        $_SESSION['anc_array'] = '';
        if (isset($_GET['anc_persged']) && isset($_GET['anc_persfams'])) {
            $chosenperson = $_GET['anc_persged'];
            //$persfams = $_GET['anc_persfams'];
            //$persfams_arr = explode(';', $persfams);

            //also check privacy
            $myresult = $dbh->query("SELECT pers_lastname, pers_firstname, pers_prefix FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $chosenperson . "'");
            $myresultDb = $myresult->fetch(PDO::FETCH_OBJ);
            $chosenname = $myresultDb->pers_firstname . ' ' . strtolower(str_replace('_', '', $myresultDb->pers_prefix)) . ' ' . $myresultDb->pers_lastname;
            $anc_array = get_ancestors($db_functions, $chosenperson);
            $_SESSION['anc_array'] = $anc_array; // for use in namesearch.php
        } else {
            $chosenname = '';
            $anc_array = '';
            unset($_SESSION['anc_array']);
        }

        $maps['chosen_name'] = $chosenname;
        $maps['anc_array'] = $anc_array;
        return $maps;
    }

    // *** Sept. 2024: at this moment only used for OpenStreetMap ***
    public function get_locations($dbh, $tree_id, $maps)
    {
        $maps['location'][] = '';
        $maps['latitude'][] = '';
        $maps['longitude'][] = '';
        $maps['location_text'][] = '';
        $maps['location_text_count'][] = '';

        $namesearch_string = '';
        if ($maps['family_names'] != '') {
            $namesearch_string = ' AND (';
            foreach ($maps['family_names'] as $value) {
                //$namesearch_string .= " pers_lastname = '".$value."' OR ";
                //$namesearch_string .= " totalname = '".$value."' OR ";
                $namesearch_string .= "CONCAT(pers_lastname,'_',LOWER(SUBSTRING_INDEX(pers_prefix,'_',1))) = '" . $value . "' OR ";
            }
            $namesearch_string = substr($namesearch_string, 0, -3) . ")"; // take off last "OR "
        }

        if ($maps['display_birth']) {
            /*
            $persoon = $dbh->query("SELECT * FROM humo_location LEFT JOIN humo_persons
                ON humo_location.location_location = humo_persons.pers_birth_place
                OR humo_location.location_location = humo_persons.pers_bapt_place
                WHERE location_lat IS NOT NULL AND pers_tree_id='" . $tree_id . "'");
            */
            // See problems with death / burial. Also change this birth/ baptise query.
            $persoon = $dbh->query("SELECT * FROM humo_location LEFT JOIN humo_persons
                ON humo_location.location_location = humo_persons.pers_birth_place
                WHERE location_lat IS NOT NULL AND pers_tree_id='" . $tree_id . "' " . $namesearch_string . " ORDER BY location_location");
        } elseif ($maps['display_death']) {
            /*
            $persoon = $dbh->query("SELECT * FROM humo_location LEFT JOIN humo_persons
                ON humo_location.location_location = humo_persons.pers_death_place
                OR humo_location.location_location = humo_persons.pers_buried_place
                WHERE location_lat IS NOT NULL AND pers_tree_id='" . $tree_id . "'");
            */

            $persoon = $dbh->query("SELECT * FROM humo_location LEFT JOIN humo_persons
                ON humo_location.location_location = humo_persons.pers_death_place
                WHERE location_lat IS NOT NULL AND pers_tree_id='" . $tree_id . "' " . $namesearch_string . " ORDER BY location_location");
        }
        while ($personDb = $persoon->fetch(PDO::FETCH_OBJ)) {
            if ($maps['display_birth']) {
                $place = $personDb->pers_birth_place;
                $date = $personDb->pers_birth_date;
                //if (!$personDb->pers_birth_place && $personDb->pers_bapt_place) {
                //    $place = $personDb->pers_bapt_place;
                //}
                //if (!$personDb->pers_birth_date && $personDb->pers_bapt_date) {
                //    $date = $personDb->pers_bapt_date;
                //}
            } elseif ($maps['display_death']) {
                $place = $personDb->pers_death_place;
                $date = $personDb->pers_death_date;

                // TODO Check (also for birth/ bapt): $personDb->location_location.
                // Could be different if death and burial places are different.
                // Something like this (query will find death place and buried place in 2 rows):
                //if (!$personDb->pers_death_place && $personDb->pers_buried_place==$personDb->location_location) {
                //    $place = $personDb->pers_buried_place;
                //    // Maybe show a [] character for person?
                //}

                //if (!$personDb->pers_death_place && $personDb->pers_buried_place) {
                //    $place = $personDb->pers_buried_place;
                //}
                //if (!$personDb->pers_death_date && $personDb->pers_buried_date) {
                //    $date = $personDb->pers_buried_date;
                //}
            }

            // *** Use person class ***
            // TODO: this slows down page for large family trees. Use Javascript to show persons?
            $person_cls = new PersonCls($personDb);
            $name = $person_cls->person_name($personDb);

            $key = array_search($place, $maps['location']);
            if (isset($key) && $key > 0) {
                // *** Check the number of lines of the text_array ***
                $maps['location_text_count'][$key]++;
                // *** For now: limited results in text box of OpenStreetMap ***
                if ($maps['location_text_count'][$key] < 26) {
                    $maps['location_text'][$key] .= '<br>' . addslashes($name["standard_name"]);
                }
                if ($maps['location_text_count'][$key] == 26) {
                    $maps['location_text'][$key] .= '<br>' . __('Results are limited.');
                }
            } else {
                // *** Add location to array ***
                $maps['location'][] = htmlspecialchars($place);
                $maps['latitude'][] = $personDb->location_lat;
                $maps['longitude'][] = $personDb->location_lng;

                $maps['location_text'][] = addslashes('<h4>' . $place . '</h4>' . $name["standard_name"]);
                $maps['location_text_count'][] = 1; // *** Number of text lines ***
            }
        }
        return $maps;
    }

    public function get_locations_google($dbh, $tree_id, $maps)
    {
        // TODO probably better not to load all places. Combine queries?
        // Allready done for openstreetmap.
        $locarray = [];
        $location = $dbh->query("SELECT location_id, location_location, location_lat, location_lng FROM humo_location WHERE location_lat IS NOT NULL ORDER BY location_location");
        while ($locationDb = $location->fetch(PDO::FETCH_OBJ)) {
            $locarray[$locationDb->location_location][0] = htmlspecialchars($locationDb->location_location);
            $locarray[$locationDb->location_location][1] = $locationDb->location_lat;
            $locarray[$locationDb->location_location][2] = $locationDb->location_lng;
            $locarray[$locationDb->location_location][3] = 0;    // till starting year  (depending on settings)
            $locarray[$locationDb->location_location][4] = 0;    // + 1 interval
            $locarray[$locationDb->location_location][5] = 0;    // + 2 intervals
            $locarray[$locationDb->location_location][6] = 0;    // + 3 intervals
            $locarray[$locationDb->location_location][7] = 0;    // + 4 intervals
            $locarray[$locationDb->location_location][8] = 0;    // + 5 intervals
            $locarray[$locationDb->location_location][9] = 0;    // + 6 intervals
            $locarray[$locationDb->location_location][10] = 0;   // + 7 intervals
            $locarray[$locationDb->location_location][11] = 0;   // + 8 intervals
            $locarray[$locationDb->location_location][12] = 0;   // till today (=2010 and beyond)
            $locarray[$locationDb->location_location][13] = 0;   // all

            // *** Added 15-09-2024 ***
            $locarray[$locationDb->location_location][14] = $locationDb->location_id;
        }

        if (isset($maps['desc_array']) && $maps['desc_array'] != '') {
            $desc_asc_array = $maps['desc_array'];
        } elseif (isset($maps['anc_array']) && $maps['anc_array'] != '') {
            $desc_asc_array = $maps['anc_array'];
        }

        if (isset($desc_asc_array) && $desc_asc_array != '') {
            foreach ($desc_asc_array as $value) {
                if ($_SESSION['type_birth'] == 1) {
                    $persoon = $dbh->query("SELECT pers_firstname, pers_birth_place, pers_birth_date, pers_bapt_place, pers_bapt_date
                        FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                        AND pers_gedcomnumber ='" . $value . "'
                        AND (pers_birth_place !='' OR (pers_birth_place ='' AND pers_bapt_place !=''))");

                    // TODO use join. Prebuild array not needed anymore.
                    //$persoon = $dbh->query("SELECT * FROM humo_location LEFT JOIN humo_persons
                    //ON humo_location.location_location = humo_persons.pers_birth_place
                    //OR humo_location.location_location = humo_persons.pers_bapt_place
                    //WHERE location_lat IS NOT NULL AND pers_tree_id='" . $tree_id . "'");


                    $personDb = $persoon->fetch(PDO::FETCH_OBJ);
                } elseif ($_SESSION['type_death'] == 1) {
                    $persoon = $dbh->query("SELECT pers_firstname, pers_death_place, pers_death_date, pers_buried_place, pers_buried_date
                        FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                        AND pers_gedcomnumber ='" . $value . "'
                        AND (pers_death_place !='' OR (pers_death_place ='' AND pers_buried_place !=''))");
                    $personDb = $persoon->fetch(PDO::FETCH_OBJ);
                }
                if ($personDb) {
                    if ($_SESSION['type_birth'] == 1) {
                        $place = $personDb->pers_birth_place;
                        $date = $personDb->pers_birth_date;
                        if (!$personDb->pers_birth_place and $personDb->pers_bapt_place) {
                            $place = $personDb->pers_bapt_place;
                        }
                        if (!$personDb->pers_birth_date and $personDb->pers_bapt_date) {
                            $date = $personDb->pers_bapt_date;
                        }
                    } elseif ($_SESSION['type_death'] == 1) {
                        $place = $personDb->pers_death_place;
                        $date = $personDb->pers_death_date;
                        if (!$personDb->pers_death_place and $personDb->pers_buried_place) {
                            $place = $personDb->pers_buried_place;
                        }
                        if (!$personDb->pers_death_date and $personDb->pers_buried_date) {
                            $date = $personDb->pers_buried_date;
                        }
                    }

                    if (isset($locarray[$place])) { // birthplace exists in location database
                        if ($date) {
                            $year = substr($date, -4);

                            if ($year > 1 and $year < $maps['slider_min']) {
                                $locarray[$place][3]++;
                            }
                            if ($year > 1 and $year < ($maps['slider_min'] + $maps['slider_step'])) {
                                $locarray[$place][4]++;
                            }
                            if ($year > 1 and $year < ($maps['slider_min'] + (2 * $maps['slider_step']))) {
                                $locarray[$place][5]++;
                            }
                            if ($year > 1 and $year < ($maps['slider_min'] + (3 * $maps['slider_step']))) {
                                $locarray[$place][6]++;
                            }
                            if ($year > 1 and $year < ($maps['slider_min'] + (4 * $maps['slider_step']))) {
                                $locarray[$place][7]++;
                            }
                            if ($year > 1 and $year < ($maps['slider_min'] + (5 * $maps['slider_step']))) {
                                $locarray[$place][8]++;
                            }
                            if ($year > 1 and $year < ($maps['slider_min'] + (6 * $maps['slider_step']))) {
                                $locarray[$place][9]++;
                            }
                            if ($year > 1 and $year < ($maps['slider_min'] + (7 * $maps['slider_step']))) {
                                $locarray[$place][10]++;
                            }
                            if ($year > 1 and $year < ($maps['slider_min'] + (8 * $maps['slider_step']))) {
                                $locarray[$place][11]++;
                            }
                            if ($year > 1 and $year < 2100) {
                                $locarray[$place][12]++;
                            }
                            $locarray[$place][13]++;  // array of all people incl people without birth date
                        } else {
                            $locarray[$place][13]++; // array of all people incl people without birth date
                        }
                    }
                }
            }
        } else {
            $namesearch_string = '';
            if ($maps['family_names'] != '') {
                $namesearch_string = ' AND (';
                foreach ($maps['family_names'] as $value) {
                    //$namesearch_string .= " pers_lastname = '".$value."' OR ";
                    //$namesearch_string .= " totalname = '".$value."' OR ";
                    $namesearch_string .= "CONCAT(pers_lastname,'_',LOWER(SUBSTRING_INDEX(pers_prefix,'_',1))) = '" . $value . "' OR ";
                }
                $namesearch_string = substr($namesearch_string, 0, -3) . ")"; // take off last "OR "
            }

            if ($_SESSION['type_birth'] == 1) {
                $persoon = $dbh->query("SELECT pers_birth_place, pers_birth_date, pers_bapt_place, pers_bapt_date
                    FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                    AND (pers_birth_place !='' OR (pers_birth_place ='' AND pers_bapt_place !='')) " . $namesearch_string);
            } elseif ($_SESSION['type_death'] == 1) {
                $persoon = $dbh->query("SELECT pers_death_place, pers_death_date, pers_buried_place, pers_buried_date
                    FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                    AND (pers_death_place !='' OR (pers_death_place ='' AND pers_buried_place !='')) " . $namesearch_string);
            }
            while ($personDb = $persoon->fetch(PDO::FETCH_OBJ)) {

                if ($_SESSION['type_birth'] == 1) {
                    $place = $personDb->pers_birth_place;
                    $date = $personDb->pers_birth_date;
                    if (!$personDb->pers_birth_place and $personDb->pers_bapt_place) {
                        $place = $personDb->pers_bapt_place;
                    }
                    if (!$personDb->pers_birth_date and $personDb->pers_bapt_date) {
                        $date = $personDb->pers_bapt_date;
                    }
                } elseif ($_SESSION['type_death'] == 1) {
                    $place = $personDb->pers_death_place;
                    $date = $personDb->pers_death_date;
                    if (!$personDb->pers_death_place and $personDb->pers_buried_place) {
                        $place = $personDb->pers_buried_place;
                    }
                    if (!$personDb->pers_death_date and $personDb->pers_buried_date) {
                        $date = $personDb->pers_buried_date;
                    }
                }

                if (isset($locarray[$place])) { // birthplace exists in location database
                    if ($date) {
                        $year = substr($date, -4);

                        if ($year > 1 and $year < $maps['slider_min']) {
                            $locarray[$place][3]++;
                        }
                        if ($year > 1 and $year < ($maps['slider_min'] + $maps['slider_step'])) {
                            $locarray[$place][4]++;
                        }
                        if ($year > 1 and $year < ($maps['slider_min'] + (2 * $maps['slider_step']))) {
                            $locarray[$place][5]++;
                        }
                        if ($year > 1 and $year < ($maps['slider_min'] + (3 * $maps['slider_step']))) {
                            $locarray[$place][6]++;
                        }
                        if ($year > 1 and $year < ($maps['slider_min'] + (4 * $maps['slider_step']))) {
                            $locarray[$place][7]++;
                        }
                        if ($year > 1 and $year < ($maps['slider_min'] + (5 * $maps['slider_step']))) {
                            $locarray[$place][8]++;
                        }
                        if ($year > 1 and $year < ($maps['slider_min'] + (6 * $maps['slider_step']))) {
                            $locarray[$place][9]++;
                        }
                        if ($year > 1 and $year < ($maps['slider_min'] + (7 * $maps['slider_step']))) {
                            $locarray[$place][10]++;
                        }
                        if ($year > 1 and $year < ($maps['slider_min'] + (8 * $maps['slider_step']))) {
                            $locarray[$place][11]++;
                        }
                        if ($year > 1 and $year < 2050) {
                            $locarray[$place][12]++;
                        }
                        $locarray[$place][13]++;  // array of all people incl people without birth date
                    } else {
                        $locarray[$place][13]++; // array of all people incl people without birth date
                    }
                }
            }
        }
        return $locarray;
    }
}

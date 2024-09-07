<?php
class MapsModel
{
    /*
    private $db_functions;

    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }
    */

    public function select_world_map($humo_option)
    {
        $select = 'Google';
        if (isset($humo_option["use_world_map"]) && $humo_option["use_world_map"] == 'OpenStreetMap') {
            $select = 'OpenStreetMap';
        }
        return $select;
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

    public function get_locations($dbh, $tree_id, $maps)
    {
        $maps['location'][] = '';
        $maps['latitude'][] = '';
        $maps['longitude'][] = '';
        $maps['location_text'][] = '';
        $maps['location_text_count'][] = '';

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
                WHERE location_lat IS NOT NULL AND pers_tree_id='" . $tree_id . "'");
        } elseif ($maps['display_death']) {
            /*
            $persoon = $dbh->query("SELECT * FROM humo_location LEFT JOIN humo_persons
                ON humo_location.location_location = humo_persons.pers_death_place
                OR humo_location.location_location = humo_persons.pers_buried_place
                WHERE location_lat IS NOT NULL AND pers_tree_id='" . $tree_id . "'");
            */

            $persoon = $dbh->query("SELECT * FROM humo_location LEFT JOIN humo_persons
                ON humo_location.location_location = humo_persons.pers_death_place
                WHERE location_lat IS NOT NULL AND pers_tree_id='" . $tree_id . "'");
        }
        while (@$personDb = $persoon->fetch(PDO::FETCH_OBJ)) {
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
            $person_cls = new person_cls($personDb);
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
}

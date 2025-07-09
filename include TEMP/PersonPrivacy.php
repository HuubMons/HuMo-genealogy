<?php

/**
 * June 2025 Huub Mons: separate class for person privacy handling.
 */

namespace Genealogy\Include;

class PersonPrivacy
{
    public function get_privacy($personDb): bool
    {
        global $user, $dataDb;

        $privacy = false;  // *** Standard: show all persons ***

        if ($user['group_privacy'] == 'n') {
            $privacy = true;  // *** Standard: filter privacy data of person ***
            // *** $personDb is empty by N.N. person ***
            if ($personDb) {
                // *** HuMo-genealogy, Haza-data and Aldfaer alive/ deceased status ***

                if ($user['group_alive'] == "j") {
                    if ($personDb->pers_alive == 'deceased') {
                        $privacy = false;
                    }
                    if ($personDb->pers_alive == 'alive') {
                        $privacy = true;
                    }
                }

                // *** Privacy filter: date ***
                if ($user["group_alive_date_act"] == "j") {
                    if ($personDb->pers_birth_date) {
                        if (substr($personDb->pers_birth_date, -2) === "BC") {
                            // born before year 0
                            $privacy = false;
                        } elseif (substr($personDb->pers_birth_date, -2, 1) === " " || substr($personDb->pers_birth_date, -3, 1) === " ") {
                            // born between year 0 and 99
                            $privacy = false;
                        } elseif (substr($personDb->pers_birth_date, -4) < $user["group_alive_date"]) {
                            // born from year 100 onwards but before $user["group_alive_date"]
                            $privacy = false;
                        } else {
                            // *** overwrite pers_alive status ***
                            $privacy = true;
                        }
                    }
                    if ($personDb->pers_bapt_date) {
                        if (substr($personDb->pers_bapt_date, -2) === "BC") {
                            // baptized before year 0
                            $privacy = false;
                        } elseif (substr($personDb->pers_bapt_date, -2, 1) === " " || substr($personDb->pers_bapt_date, -3, 1) === " ") {
                            // baptized between year 0 and 99
                            $privacy = false;
                        } elseif (substr($personDb->pers_bapt_date, -4) < $user["group_alive_date"]) {
                            // baptized from year 100 onwards but before $user["group_alive_date"]
                            $privacy = false;
                        } else {
                            // *** overwrite pers_alive status ***
                            $privacy = true;
                        }
                    }
                    if ($personDb->pers_cal_date) {
                        if (substr($personDb->pers_cal_date, -2) === "BC") {
                            // calculated born before year 0
                            $privacy = false;
                        } elseif (substr($personDb->pers_cal_date, -2, 1) === " " || substr($personDb->pers_cal_date, -3, 1) === " ") {
                            // calculated born between year 0 and 99
                            $privacy = false;
                        } elseif (substr($personDb->pers_cal_date, -4) < $user["group_alive_date"]) {
                            // calculated born from year 100 onwards but before $user["group_alive_date"]
                            $privacy = false;
                        } else {
                            // *** overwrite pers_alive status ***
                            $privacy = true;
                        }
                    }

                    // *** Check if deceased persons should be filtered ***
                    if ($user["group_filter_death"] == 'n') {
                        // *** If person is deceased, filter is off ***
                        if ($personDb->pers_death_date || $personDb->pers_death_place) {
                            $privacy = false;
                        }
                        if ($personDb->pers_buried_date || $personDb->pers_buried_place) {
                            $privacy = false;
                        }
                        // *** pers_alive for deceased persons without date ***
                        if ($personDb->pers_alive == 'deceased') {
                            $privacy = false;
                        }
                    }
                }

                // *** Privacy filter: date ***
                if ($user["group_death_date_act"] == "j") {
                    if ($personDb->pers_death_date) {
                        if (substr($personDb->pers_death_date, -2) === "BC") {
                            // person died BC
                            $privacy = false;
                        } elseif (substr($personDb->pers_death_date, -2, 1) === " " || substr($personDb->pers_death_date, -3, 1) === " ") {
                            // person died between year 0 and 99
                            $privacy = false;
                        } elseif (substr($personDb->pers_death_date, -4) < $user["group_death_date"]) {
                            // person died after year 100 until $user["group_death_date"]
                            $privacy = false;
                        } else {
                            // *** overwrite pers_alive status ***
                            $privacy = true;
                        }
                    }
                    if ($personDb->pers_buried_date) {
                        if (substr($personDb->pers_buried_date, -2) === "BC") {
                            // person buried BC
                            $privacy = false;
                        } elseif (substr($personDb->pers_buried_date, -2, 1) === " " || substr($personDb->pers_buried_date, -3, 1) === " ") {
                            // person buried between year 0 and 99
                            $privacy = false;
                        } elseif (substr($personDb->pers_buried_date, -4) < $user["group_death_date"]) {
                            // person buried after year 100 until $user["group_death_date"]
                            $privacy = false;
                        } else {
                            // *** overwrite pers_alive status ***
                            $privacy = true;
                        }
                    }
                }

                // *** Filter person's WITHOUT any date's ***
                if ($user["group_filter_date"] == 'j' && ($personDb->pers_birth_date == '' && $personDb->pers_bapt_date == '' && $personDb->pers_death_date == '' && $personDb->pers_buried_date == '' && $personDb->pers_cal_date == '' && $personDb->pers_cal_date == '')) {
                    $privacy = false;
                }

                // *** Privacy filter exceptions (added a space for single character check) ***
                if (
                    $user["group_filter_pers_show_act"] == 'j' && strpos(' ' . $personDb->pers_own_code, $user["group_filter_pers_show"]) > 0
                ) {
                    $privacy = false;
                }
                if (
                    $user["group_filter_pers_hide_act"] == 'j' && strpos(' ' . $personDb->pers_own_code, $user["group_filter_pers_hide"]) > 0
                ) {
                    $privacy = true;
                }
            }
        }

        // *** Completely filter a person, if option "completely filter a person" is activated ***
        if ($personDb && ($user["group_pers_hide_totally_act"] == 'j' && strpos(' ' . $personDb->pers_own_code, $user["group_pers_hide_totally"]) > 0)) {
            $privacy = true;
        }

        // *** Privacy filter for whole family tree ***
        if (isset($dataDb->tree_privacy)) {
            if ($dataDb->tree_privacy == 'filter_persons') {
                $privacy = true;
            }
            if ($dataDb->tree_privacy == 'show_persons') {
                $privacy = false;
            }
        }
        return $privacy;
    }
}

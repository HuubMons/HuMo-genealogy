<?php
// *** Function to display date - place or place - date. ***
function date_place($process_date, $process_place, $hebnight = "")
{
    global $language, $user, $screen_mode, $dirmark1, $humo_option;

    $self = $_SERVER['QUERY_STRING'] ?? '';
    $hebdate = '';
    if ($humo_option['admin_hebdate'] == "y") {
        if (
            stripos($self, "star") === FALSE
            and stripos($self, "hour") === FALSE
            and stripos($self, "ancestor_chart") === FALSE
            and stripos($self, "ancestor_sheet") === FALSE
        )
            $hebdate = hebdate($process_date, $hebnight);
    }
    if ($process_place == ' ') {
        $process_place = '';
    } // *** If there is no place ***

    if ($user['group_place_date'] == 'j') {
        $text = '';
        if ($user['group_places'] == 'j' and $process_place) {
            //$text=__('PLACE_AT ').$process_place." ";
            if (__('PLACE_AT ') != 'PLACE_AT ') {
                $text = __('PLACE_AT ');
            }
            $text .= $process_place . " ";
        }
        //$text.=$dirmark1.language_date($process_date).$hebdate;
        $text .= language_date($process_date) . $hebdate;
        if ($text) $text = $dirmark1 . $text; // *** Only add $dirmark if there is data ***
    } else {
        //$text=$dirmark1.language_date($process_date).$hebdate;
        $text = language_date($process_date) . $hebdate;
        if ($text) $text = $dirmark1 . $text; // *** Only add $dirmark if there is data ***

        if ($user['group_places'] == 'j' and $process_place) {
            //$text.=' '.__('PLACE_AT ').$process_place;
            if ($process_date) $text .= ' '; // *** Only add space if there is a date ***
            if (__('PLACE_AT ') != 'PLACE_AT ') {
                $text .= __('PLACE_AT ');
            }
            $text .= $process_place;
        }
    }
    return $text;
}

function hebdate($datestr, $hebnight = "")
{
    global $language;
    $hebdate = '';
    $year = NULL;
    $month = NULL;
    $day = NULL;
    $year = search_year($datestr);
    if ($year) {
        $month = search_month($datestr);
        if ($month) {
            $day = search_day($datestr);
        }
    }
    if ($year != NULL and $month != NULL and $day != NULL) {

        // if after  nightfall is marked, take next gregorian day
        if ($hebnight == "y") {
            if ($month == "1" or $month == "3" or $month == "5" or $month == "7" or $month == "8" or $month == "10" or $month == "12") {
                // months with 31 days
                if ($day < 31) {
                    $day = $day + 1;
                } elseif ($day == 31) {
                    $day = 1;
                    if ($month != 12) {
                        $month = $month + 1;
                    } else {
                        $month = 1;
                        $year = $year + 1;
                    }
                }
            } elseif ($month == "4" or $month == "6" or $month == "9" or $month == "11") {
                // months with 30 days
                if ($day < 30) {
                    $day = $day + 1;
                } elseif ($day == 30) {
                    $day = 1;
                    $month = $month + 1;
                }
            } elseif ($month == "2") {
                // february
                if (($year % 4 != 0) or ($year % 100 == 0 and $year % 400 != 0)) {     // not leapyear
                    if ($day < 28) {
                        $day = $day + 1;
                    } elseif ($day == 28) {
                        $day = 1;
                        $month = $month + 1;
                    }
                } else {  // leapyear
                    if ($day < 29) {
                        $day = $day + 1;
                    } elseif ($day == 29) {
                        $day = 1;
                        $month = $month + 1;
                    }
                }
            }
        }

        $str = jdtojewish(gregoriantojd($month, $day, $year), false);
        $string = explode("/", $str);
        if ($language["dir"] == "rtl") {
            if ($string[0] == 1) $month = "תשרי";
            if ($string[0] == 2) $month = "חשון";
            if ($string[0] == 3) $month = "כסלו";
            if ($string[0] == 4) $month = "טבת";
            if ($string[0] == 5) $month = "שבט";

            $m = array(3, 6, 8, 11, 14, 17, 19);
            $meuberet = in_array(($string[2] % 19), $m);
            if ($meuberet) {
                if ($string[0] == 6) $month = "אדר ראשון";
                if ($string[0] == 7) $month = "אדר שני";
            } else {
                if ($string[0] == 6) $month = "אדר";
                if ($string[0] == 7) $month = "אדר";
            }

            if ($string[0] == 8) $month = "ניסן";
            if ($string[0] == 9) $month = "אייר";
            if ($string[0] == 10) $month = "סיון";
            if ($string[0] == 11) $month = "תמוז";
            if ($string[0] == 12) $month = "אב";
            if ($string[0] == 13) $month = "אלול";
        } else {
            if ($string[0] == 1) $month = "Tishrei";
            if ($string[0] == 2) $month = "Cheshvan";
            if ($string[0] == 3) $month = "Kislev";
            if ($string[0] == 4) $month = "Tevet";
            if ($string[0] == 5) $month = "Shevat";

            $m = array(3, 6, 8, 11, 14, 17, 19);
            $meuberet = in_array(($string[2] % 19), $m);
            if ($meuberet) {
                if ($string[0] == 6) $month = "Adar I";
                if ($string[0] == 7) $month = "Adar II";
            } else {
                if ($string[0] == 6) $month = "Adar";
                if ($string[0] == 7) $month = "Adar";
            }

            if ($string[0] == 8) $month = "Nisan";
            if ($string[0] == 9) $month = "Iyar";
            if ($string[0] == 10) $month = "Sivan";
            if ($string[0] == 11) $month = "Tamuz";
            if ($string[0] == 12) $month = "Av";
            if ($string[0] == 13) $month = "Ellul";
        }
        $hebdate = ' (' . $string[1] . ' ' . $month . ' ' . $string[2] . ')';
    }
    return $hebdate;
}

function search_year($search_date)
{
    $year = substr($search_date, -4, 4);
    if ($year < 2100 and $year > 0) {
    } else {
        $year = null;
    }
    return ($year);
}

function search_month($search_date)
{
    $month = strtoupper(substr($search_date, -8, 3));
    if ($month == "JAN") {
        $text = 1;
    } else if ($month == "FEB") {
        $text = 2;
    } else if ($month == "MAR") {
        $text = 3;
    } else if ($month == "APR") {
        $text = 4;
    } else if ($month == "MAY") {
        $text = 5;
    } else if ($month == "JUN") {
        $text = 6;
    } else if ($month == "JUL") {
        $text = 7;
    } else if ($month == "AUG") {
        $text = 8;
    } else if ($month == "SEP") {
        $text = 9;
    } else if ($month == "OCT") {
        $text = 10;
    } else if ($month == "NOV") {
        $text = 11;
    } else if ($month == "DEC") {
        $text = 12;
    } else {
        $text = null;
    }
    return ($text);
}

function search_day($search_date)
{
    $day = '';
    if (strlen($search_date) == 11) {    // 12 sep 2002 or 08 sep 2002
        $day = substr($search_date, -11, 2);
        if (substr($day, 0, 1) == "0") {   // 08 aug 2002
            $day = substr($day, 1, 1);
        }
    }
    if (strlen($search_date) == 10) {    // 8 aug 2002
        $day = substr($search_date, -10, 1);
    }
    if ($day) {
        $day = (int)$day;
    }
    if ($day > 0 and $day < 32) {
    } else {
        $day = null;
    }
    return ($day);
}

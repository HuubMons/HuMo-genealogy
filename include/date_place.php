<?php
// *** Function to display date - place or place - date. ***
function date_place($process_date, $process_place, $hebnight = "")
{
    global $user, $dirmark1, $humo_option;

    $self = $_SERVER['QUERY_STRING'] ?? '';
    $hebdate = '';
    if ($humo_option['admin_hebdate'] == "y" && (stripos($self, "star") === FALSE && stripos($self, "hour") === FALSE && stripos($self, "ancestor_chart") === FALSE && stripos($self, "ancestor_sheet") === FALSE)) {
        $hebdate = hebdate($process_date, $hebnight);
    }
    if ($process_place == ' ') {
        $process_place = '';
    }

    if ($user['group_place_date'] == 'j') {
        $text = '';
        if ($user['group_places'] == 'j' && $process_place) {
            if (__('PLACE_AT ') != 'PLACE_AT ') {
                $text = __('PLACE_AT ');
            }
            $text .= $process_place . " ";
        }
        $text .= language_date($process_date) . $hebdate;
        if ($text) $text = $dirmark1 . $text; // *** Only add $dirmark if there is data ***
    } else {
        $text = language_date($process_date) . $hebdate;
        if ($text) $text = $dirmark1 . $text; // *** Only add $dirmark if there is data ***

        if ($user['group_places'] == 'j' and $process_place) {
            if ($process_date) {
                $text .= ' ';
            }
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
    if ($year != NULL && $month != NULL && $day != NULL) {

        // if after  nightfall is marked, take next gregorian day
        if ($hebnight == "y") {
            if ($month == "1" || $month == "3" || $month == "5" || $month == "7" || $month == "8" || $month == "10" || $month == "12") {
                // months with 31 days
                if ($day < 31) {
                    $day += 1;
                } elseif ($day == 31) {
                    $day = 1;
                    if ($month != 12) {
                        $month += 1;
                    } else {
                        $month = 1;
                        $year += 1;
                    }
                }
            } elseif ($month == "4" || $month == "6" || $month == "9" || $month == "11") {
                // months with 30 days
                if ($day < 30) {
                    $day += 1;
                } elseif ($day == 30) {
                    $day = 1;
                    $month += 1;
                }
            } elseif ($month == "2") {
                // february
                if ($year % 4 != 0 || $year % 100 == 0 && $year % 400 != 0) {
                    // not leapyear
                    if ($day < 28) {
                        $day += 1;
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
            if ($string[0] == 1) {
                $month = "תשרי";
            }
            if ($string[0] == 2) {
                $month = "חשון";
            }
            if ($string[0] == 3) {
                $month = "כסלו";
            }
            if ($string[0] == 4) {
                $month = "טבת";
            }
            if ($string[0] == 5) {
                $month = "שבט";
            }

            $m = array(3, 6, 8, 11, 14, 17, 19);
            $meuberet = in_array(($string[2] % 19), $m);
            if ($meuberet) {
                if ($string[0] == 6) {
                    $month = "אדר ראשון";
                }
                if ($string[0] == 7) {
                    $month = "אדר שני";
                }
            } else {
                if ($string[0] == 6) {
                    $month = "אדר";
                }
                if ($string[0] == 7) {
                    $month = "אדר";
                }
            }

            if ($string[0] == 8) {
                $month = "ניסן";
            }
            if ($string[0] == 9) {
                $month = "אייר";
            }
            if ($string[0] == 10) {
                $month = "סיון";
            }
            if ($string[0] == 11) {
                $month = "תמוז";
            }
            if ($string[0] == 12) {
                $month = "אב";
            }
            if ($string[0] == 13) {
                $month = "אלול";
            }
        } else {
            if ($string[0] == 1) {
                $month = "Tishrei";
            }
            if ($string[0] == 2) {
                $month = "Cheshvan";
            }
            if ($string[0] == 3) {
                $month = "Kislev";
            }
            if ($string[0] == 4) {
                $month = "Tevet";
            }
            if ($string[0] == 5) {
                $month = "Shevat";
            }

            $m = array(3, 6, 8, 11, 14, 17, 19);
            $meuberet = in_array(($string[2] % 19), $m);
            if ($meuberet) {
                if ($string[0] == 6) {
                    $month = "Adar I";
                }
                if ($string[0] == 7) {
                    $month = "Adar II";
                }
            } else {
                if ($string[0] == 6) {
                    $month = "Adar";
                }
                if ($string[0] == 7) {
                    $month = "Adar";
                }
            }

            if ($string[0] == 8) {
                $month = "Nisan";
            }
            if ($string[0] == 9) {
                $month = "Iyar";
            }
            if ($string[0] == 10) {
                $month = "Sivan";
            }
            if ($string[0] == 11) {
                $month = "Tamuz";
            }
            if ($string[0] == 12) {
                $month = "Av";
            }
            if ($string[0] == 13) {
                $month = "Ellul";
            }
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
    return $year;
}

function search_month($search_date)
{
    $month = strtoupper(substr($search_date, -8, 3));
    if ($month === "JAN") {
        $text = 1;
    } elseif ($month === "FEB") {
        $text = 2;
    } elseif ($month === "MAR") {
        $text = 3;
    } elseif ($month === "APR") {
        $text = 4;
    } elseif ($month === "MAY") {
        $text = 5;
    } elseif ($month === "JUN") {
        $text = 6;
    } elseif ($month === "JUL") {
        $text = 7;
    } elseif ($month === "AUG") {
        $text = 8;
    } elseif ($month === "SEP") {
        $text = 9;
    } elseif ($month === "OCT") {
        $text = 10;
    } elseif ($month === "NOV") {
        $text = 11;
    } elseif ($month === "DEC") {
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
        if (substr($day, 0, 1) === "0") {   // 08 aug 2002
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

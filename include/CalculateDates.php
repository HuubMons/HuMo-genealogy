<?php

/**
 * Script by Yossi Beck
 * Function calculate_marriage added by Huub Mons
 * Translated all variables and remarks by Huub Mons
 *
 * 24-04-2020 Huub: Added EST and removed some old code.
 * 20-12-2022 Huub: Improved showing of dates for child. Including showing of months, weeks and days.
 */

namespace Genealogy\Include;

class CalculateDates
{

    public function search_month($search_date)
    {
        if (strpos($search_date, "JAN") !== false) {
            $text = 1;
        } elseif (strpos($search_date, "FEB") !== false) {
            $text = 2;
        } elseif (strpos($search_date, "MAR") !== false) {
            $text = 3;
        } elseif (strpos($search_date, "APR") !== false) {
            $text = 4;
        } elseif (strpos($search_date, "MAY") !== false) {
            $text = 5;
        } elseif (strpos($search_date, "JUN") !== false) {
            $text = 6;
        } elseif (strpos($search_date, "JUL") !== false) {
            $text = 7;
        } elseif (strpos($search_date, "AUG") !== false) {
            $text = 8;
        } elseif (strpos($search_date, "SEP") !== false) {
            $text = 9;
        } elseif (strpos($search_date, "OCT") !== false) {
            $text = 10;
        } elseif (strpos($search_date, "NOV") !== false) {
            $text = 11;
        } elseif (strpos($search_date, "DEC") !== false) {
            $text = 12;
        } else {
            $text = null;
        }
        return ($text);
    }

    /*
    public function search_day($search_date) {
        $day="";
        if (strlen($search_date)==11) {
            // 12 sep 2002 or 08 sep 2002
            $day=substr($search_date, -11, 2);
            if(substr($day,0,1)=="0") {
                // 08 aug 2002
                $day=substr($day,1,1);
            }
        }
        if (strlen($search_date)==10) { 
            // 8 aug 2002
            $day=substr($search_date, -10, 1);
        }
        if ($day) {
            $day=(int)$day;
        }
        if ($day>0 AND $day<32) {
            //
        }
        else {
            $day=null;
        }
        return($day);
    }
    */

    public function search_day($search_date)
    {
        $day = null;
        if ($this->search_month($search_date) != null) {
            // a day value only makes sense if there is a month
            if (is_numeric(trim(substr($search_date, 0, 2)))) {
                // this will give true for "08 FEB", "8 FEB"
                $day = trim(substr($search_date, 0, 2));
            }
        }
        return ($day);
    }

    public function search_year($search_date)
    {
        if (is_numeric(substr($search_date, -4, 4))) {
            $year = trim(substr($search_date, -4, 4));
        } elseif (is_numeric(substr($search_date, -3, 3))) {
            $year = trim(substr($search_date, -3, 3));
        } elseif (is_numeric(substr($search_date, -2, 2))) {
            $year = trim(substr($search_date, -2, 2));
        } else {
            $year = null;
        }
        return ($year);
    }

    public function process_special_text($date1, $date2, $baptism)
    {
        $date1_remark = null; // pers_birth_date
        $date2_remark = null; // pers_death_date
        $text = null;

        if (strlen(stristr($date1, "BEF")) > 0) {
            $date1_remark = "Bef";
        } elseif (strlen(stristr($date1, "AFT")) > 0) {
            $date1_remark = "Aft";
        } elseif (strlen(stristr($date1, "ABT")) > 0) {
            $date1_remark = "Abt";
        } elseif (strlen(stristr($date1, "EST")) > 0) {
            // Calculate EST the same as ABT
            $date1_remark = "Abt";
        } elseif (strlen(stristr($date1, "CAL")) > 0) {
            // calculate CAL the same as ABT
            $date1_remark = "Abt";
        } elseif (strlen(stristr($date1, "INT")) > 0) {
            // calculate INT the same as ABT
            $date1_remark = "Abt";
        } elseif (strlen(stristr($date1, "BET")) > 0) {
            // Don't calculate BET text
            $date1_remark = "Bet";
        } else {
            $date1_remark = null;
        }

        if (strlen(stristr($date2, "BEF")) > 0) {
            $date2_remark = "Bef";
        } elseif (strlen(stristr($date2, "AFT")) > 0) {
            $date2_remark = "Aft";
        } elseif (strlen(stristr($date2, "ABT")) > 0) {
            $date2_remark = "Abt";
        } elseif (strlen(stristr($date2, "EST")) > 0) {
            // Calculate EST the same as ABT
            $date2_remark = "Abt";
        } elseif (strlen(stristr($date2, "CAL")) > 0) {
            // Calculate CAL the same as ABT
            $date2_remark = "Abt";
        } elseif (strlen(stristr($date2, "INT")) > 0) {
            // calculate INT the same as ABT
            $date2_remark = "Abt";
        } elseif (strlen(stristr($date2, "BET")) > 0) {
            // Don't calculate BET text
            $date2_remark = "Bet";
        } else {
            $date2_remark = null;
        }

        if ($date1_remark || $date2_remark) {
            // there is at least 1 remark
            if ($date1_remark === "Bef") {
                if ($date2_remark === "Bef" || $date2_remark === "Abt") {
                    // Can't calculate age. flag -1
                    $text = -1;
                } elseif ($date2_remark === null || $date2_remark === "Aft") {
                    $text = __('at least') . " ";
                }
            } elseif ($date1_remark === "Aft") {
                if ($date2_remark === "Aft" || $date2_remark === "Abt") {
                    // Can't calculate age. flag -1
                    $text = -1;
                } elseif ($date2_remark === null || $date2_remark === "Bef") {
                    $text = __('at most') . " ";
                }
            } elseif ($date1_remark === "Abt") {
                if ($date2_remark === "Bef" || $date2_remark === "Aft") {
                    // Can't calculate age. flag -1
                    $text = -1;
                } elseif ($date2_remark === null || $date2_remark === "Abt") {
                    $text = __('approximately') . " ";
                }
            } elseif ($date1_remark === null) {
                if ($date2_remark === "Bef") {
                    $text = __('at most') . " ";
                } elseif ($date2_remark === "Aft") {
                    $text = __('at least') . " ";
                } elseif ($date2_remark === "Abt") {
                    $text = __('approximately') . " ";
                }
            } elseif ($date1_remark === "Bet" || $date2_remark === "Bet") {
                // Don't calculate age if text = BET, sorry...
                $text = -1;
            }
        } else {
            // No remarks
            $text = null;
        }

        // *** If calculated with baptism, always use about ***
        if ($baptism == true && $text != -1) {
            $text = __('approximately') . " ";
        }

        return ($text);
    }

    // *** $age_check=false/true. true=show short age text. ***
    public function calculate_age($baptism_date, $birth_date, $death_date, $age_check = false, $age_event = '')
    {
        global $user;

        // *** handle person born and died BC ***
        if (substr($birth_date, -2, 2) === "BC" && substr($death_date, -2, 2) === "BC") {
            $temp = $birth_date;
            $birth_date = substr($death_date, 0, -3);
            $death_date = substr($temp, 0, -3);
        }

        // *** handle person born BC and died after year zero ***
        elseif (substr($birth_date, -2, 2) === "BC" && $death_date != "" && substr($death_date, -2, 2) !== "BC") {
            $first = $this->search_year(substr($birth_date, 0, -3));
            $secnd = $this->search_year($death_date);
            $totl  = (int)$first + (int)$secnd;
            return (", " . ($totl - 1) . " or " . $totl . " " . __('years'));
        }

        $birth_date = strtoupper($birth_date);

        // *** Also calculate age if only baptism and death date is known ***
        $baptism = false;
        if ($birth_date === '') {
            $baptism = true;
            $birth_date = $baptism_date;
        }

        // *** Remove gregorian date from date ***
        //  convert "1 Jan 1634/5" to "1 Jan 1634".
        //  and convert "25 Dec 1600/4 Jan 1601" to "25 Dec 1600".
        if (strpos($birth_date, '/') > 0) {
            $temp = explode('/', $birth_date);
            $birth_date = $temp[0];
            $baptism = true;
        }
        if (strpos($death_date, '/') > 0) {
            $temp = explode('/', $death_date);
            $death_date = $temp[0];
            $baptism = true;
        }

        // *** Calculate age by living person ***
        if ($death_date == '') {
            $death_date = date("j M Y");

            // *** Show or hide age calculation for living persons ***
            if ($user['group_show_age_living_person'] != 'y') {
                $birth_date = '';
                $death_date = '';
            }
        }
        $death_date = strtoupper($death_date);
        $calculated_age = '';
        $age = "";

        //if (($birth_year=$this->search_year($birth_date)) AND ($death_year=$this->search_year($death_date))) {
        // There must be 2 years....
        $birth_year = $this->search_year($birth_date);
        $death_year = $this->search_year($death_date);
        if ($birth_year && $death_year) {
            // There must be 2 years....

            // Check for EST AFT ABT etc. If calculation is not possible: $special_text -1
            $special_text = $this->process_special_text($birth_date, $death_date, $baptism);

            // *** Calculate age in year/ month/ week/ days for children age of < 3 years ***
            //if($birth_year==$death_year) {
            // born 1850 - death 1850
            if ($death_year - $birth_year < 3) {
                // born 1850 - death 1850 or born 1849 - death 1850.
                if (!$special_text) {

                    // *** December 2022: now more exact calculation is used ***
                    $age = __('under 1 year old');

                    if (($birth_month = $this->search_month($birth_date)) and ($death_month = $this->search_month($death_date))) {
                        // There must be 2 months
                        // *** Dead within one month, don't show age ***
                        if ($birth_month == $death_month) {
                            $age = '';
                        }
                    }

                    // *** December 2022: Show exact months, weeks and days ***
                    $special_text = '';

                    $birth_month = $this->search_month($birth_date);
                    $birth_day = $this->search_day($birth_date);
                    if (!$birth_day) {
                        $birth_day = '01';
                        $special_text = __('approximately') . " ";
                    }

                    $death_month = $this->search_month($death_date);
                    $death_day = $this->search_day($death_date);
                    if (!$death_day) {
                        $death_day = '01';
                        $special_text = __('approximately') . " ";
                    }
                    if ($birth_month && $birth_day && $death_month && $death_day) {
                        $date1 = date_create($birth_year . '-' . $birth_month . '-' . $birth_day);
                        $date2 = date_create($death_year . '-' . $death_month . '-' . $death_day);
                        $interval = date_diff($date1, $date2);

                        $age = $special_text;
                        $years = $interval->format("%y");
                        if ($years > 0) {
                            $age = $years . ' ';
                            if ($years > 1) {
                                $age .= __('years');
                            } else {
                                $age .= __('year');
                            }
                        }

                        $months = $interval->format("%m");
                        if ($months) {
                            //if ($years) $age.=';';
                            if ($years) {
                                $age .= ' ' . __('and') . ' ';
                            }
                            $age .= $months . ' ';
                            if ($months > 1) {
                                $age .= __('months');
                            } else {
                                $age .= __('month');
                            }
                        }

                        if (!$special_text) {
                            $days = $interval->format("%d"); // *** Count total days ***
                            $weeks = floor($days / 7); // *** Count weeks ***
                            $days_remainder   = floor($days % 7); // *** Count resuming of days ***

                            if ($weeks > 0) {
                                //if ($years OR $months){
                                //  $age.=';';
                                //}
                                if ($years or $months) {
                                    $age .= ' ' . __('and') . ' ';
                                }
                                $age .= $weeks . ' ';
                                if ($weeks > 1) {
                                    $age .= __('weeks');
                                } else {
                                    $age .= __('week');
                                }
                            }

                            if ($days_remainder > 0) {
                                //if ($years OR $weeks) $age.=';';
                                if ($years or $months or $weeks) {
                                    $age .= ' ' . __('and') . ' ';
                                }
                                $age .= $days_remainder . ' ';
                                if ($days_remainder > 1) {
                                    $age .= __('days');
                                } else {
                                    $age .= __('day');
                                }
                            }
                        } else {
                            // *** Dates not complete, so skip calculation of date. ***
                            // Example: born SEP 1944, died 21 SEP 1944
                            $age = '';
                        }
                    }

                    // *** Don't show age if birthdate = deathdate ***
                    if ($birth_date == $death_date) {
                        $age = '';
                    }
                } else {
                    if ($special_text != -1) {
                        // *** Used for text like: approximately 1 years married ***
                        // DISABLED because born +/- 22 jul 1990 and died +/- 22 jul 1990 = 1 years...
                        //$age=$special_text.__('1 years');
                    }
                }
            } else {
                // Month is needed for better calculation
                if (($birth_month = $this->search_month($birth_date)) and ($death_month = $this->search_month($death_date))) {
                    // 2 months
                    if ($birth_month == $death_month) {
                        // same month: we need day for exact age
                        if (($birth_day = $this->search_day($birth_date)) and ($death_day = $this->search_day($death_date))) {
                            // 2 days
                            // *** Show "about" is calculated with "baptism" ***
                            if ($special_text) {
                                $age = $special_text;
                            }
                            if (($birth_day == $death_day) or ($birth_day < $death_day)) {
                                $calculated_age = $death_year - $birth_year;
                                $age .= $calculated_age . " " . __('years');
                            } else if ($birth_day > $death_day) {
                                $calculated_age = ($death_year - $birth_year) - 1;
                                $age .= $calculated_age . " " . __('years');
                            }
                        } else {
                            // Day is missing in 1 or 2 date's
                            if (!$special_text) {
                                $calculated_age = ($death_year - $birth_year) - 1;
                                $age = $calculated_age . " " . __('or') . " " . ($death_year - $birth_year) . " " . __('years');
                            } else {
                                if ($special_text != -1) {
                                    $calculated_age = $death_year - $birth_year;
                                    $age = $special_text . $calculated_age . " " . __('years');
                                }
                            }
                        }
                    } else if ($birth_month < $death_month) {
                        // No day needed
                        if ($special_text != -1) {
                            $calculated_age = $death_year - $birth_year;
                            $age = $special_text . $calculated_age . " " . __('years');
                        }
                    } else if ($birth_month > $death_month) {
                        // No day needed
                        if ($special_text != -1) {
                            $calculated_age = ($death_year - $birth_year) - 1;
                            $age = $special_text . $calculated_age . " " . __('years');
                        }
                    }
                } else {
                    // Month is missing in 1 or 2 years.
                    if (!$special_text) {
                        // no EST ABT AFT BEF
                        $calculated_age = ($death_year - $birth_year) - 1;
                        $age = $calculated_age . " " . __('or') . " " . ($death_year - $birth_year) . " " . __('years');
                    } else {
                        if ($special_text != -1) {
                            $calculated_age = $death_year - $birth_year;
                            $age = $special_text . $calculated_age . " " . __('years');
                        }
                        // "about 45 or 46 year", "at most 45 or 46 year" is written as: "about 46 year" or at most 46 year
                    }
                }
            }
            if ($age) {
                if ($age_event == '') {
                    //$age=", ".$age;
                    $age = ", " . __('age') . ' ' . $age;
                    // *** Probably needed for some languages, so it's possible to change order of items. ***
                    // *** Problem, texts like: age under 1 year old ***
                    //$age=', '.sprintf('age %s', $age);
                } elseif ($age_event == 'relation') {
                    $age = ", " . __('age by relation') . ' ' . $age;
                } else {
                    $age = ", " . __('age by marriage') . ' ' . $age;
                }
            }
            if ($calculated_age > 120) {
                $age = '';
            }

            if ($age_check == true) {
                $age = $calculated_age;
            }

            return ($age);
        }
    }

    // *** $age_check=false/true. true=show shortened age ***
    // *** Function calculate_marriage added by Huub Mons ***
    public function calculate_marriage($church_marr_date, $marr_date, $end_date, $age_check = false)
    {
        $marr_date = strtoupper($marr_date ?? '');

        // *** Also calculate marriage if only marriage date is known ***
        $baptism = false;
        if ($marr_date === '') {
            $baptism = true;
            $marr_date = $church_marr_date;
        }

        // *** Remove gregorian date from date ***
        //      convert "1 Jan 1634/5" to "1 Jan 1634".
        //      and convert "25 Dec 1600/4 Jan 1601" to "25 Dec 1600".
        if (strpos($marr_date, '/') > 0) {
            $temp = explode('/', $marr_date);
            $marr_date = $temp[0];
            $baptism = true;
        }
        if (strpos($end_date, '/') > 0) {
            $temp = explode('/', $end_date);
            $end_date = $temp[0];
            $baptism = true;
        }

        // *** Calculate age by living persons ***
        if ($end_date == '') {
            $end_date = date("j M Y");
        }
        $end_date = strtoupper($end_date);
        $calculated_age = ''; // *** Calculated age ***
        $age = "";

        // there must be 2 dates...
        if (($start_year = $this->search_year($marr_date)) && ($end_year = $this->search_year($end_date))) {

            // Check for EST AFT ABT
            $special_text = $this->process_special_text($marr_date, $end_date, $baptism);

            if ($start_year == $end_year) {
                // start 1850 - end 1850
                if (!$special_text) {
                    // Not in use in marriage calulation
                    //$age=__('under 1 year old');
                } else {
                    if ($special_text != -1) {
                        // Not in use in marriage calulation
                        //$age=$special_text.__('1 years');
                    }
                }
            } else {
                // Months are not the same: we need month for exact calculation
                if (($start_month = $this->search_month($marr_date))  and ($end_month = $this->search_month($end_date))) {
                    // 2 month
                    if ($start_month == $end_month) {
                        // same month, we need day for exact calculation
                        if (($start_day = $this->search_day($marr_date)) and ($end_day = $this->search_day($end_date))) {
                            // 2 days
                            if ($special_text) {
                                $age = $special_text;
                            }
                            if (($start_day == $end_day) or ($start_day < $end_day)) {
                                $calculated_age = $end_year - $start_year;
                                $age .= $calculated_age . " " . __('years');
                            } else if ($start_day > $end_day) {
                                $calculated_age = ($end_year - $start_year) - 1;
                                $age .= $calculated_age . " " . __('years');
                            }
                        } else {
                            // Day is missing in 1 or 2 dates
                            if (!$special_text) {
                                $calculated_age = ($end_year - $start_year) - 1;
                                $age = $calculated_age . " " . __('or') . " " . ($end_year - $start_year) . " " . __('years');
                            } else {
                                if ($special_text != -1) {
                                    $calculated_age = $end_year - $start_year;
                                    $age = $special_text . $calculated_age . " " . __('years');
                                }
                            }
                        }
                    } else if ($start_month < $end_month) {
                        // no day needed
                        if ($special_text != -1) {
                            $calculated_age = $end_year - $start_year;
                            $age = $special_text . $calculated_age . " " . __('years');
                        }
                    } else if ($start_month > $end_month) {
                        // no day needed
                        if ($special_text != -1) {
                            $calculated_age = ($end_year - $start_year) - 1;
                            $age = $special_text . $calculated_age . " " . __('years');
                        }
                    }
                } else {
                    // Month is missing in 1 or 2 years
                    if (!$special_text) {
                        // no EST ABT AFT BEF
                        $calculated_age = ($end_year - $start_year) - 1;
                        $age = $calculated_age . " " . __('or') . " " . ($end_year - $start_year) . " " . __('years');
                    } else {
                        if ($special_text != -1) {
                            $calculated_age = $end_year - $start_year;
                            $age = $special_text . $calculated_age . " " . __('years');
                        }
                    }
                }
            }

            if ($age) {
                $age = ' (' . sprintf(__('married %s'), $age) . ')';

                /*
                // TEST: must be checked for type of relation. $age is filtered in marriageCls.php line 574.
                global $relation_check;
                if ($relation_check==true){
                    $age=' ('.sprintf(__('relation %s'), $age).')';
                }
                else{
                    $age=' ('.sprintf(__('married %s'), $age).')';
                }
                */
            }

            if ($calculated_age > 80) {
                $age = '';
            }

            // Not in use in marriage calulation
            //if ($age_check==true){
            //  $age=$calculated_age;
            //

            return ($age);
        }
    }
}

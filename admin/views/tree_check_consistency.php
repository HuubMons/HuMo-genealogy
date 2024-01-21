<?php
// displays menu for date consistency check
echo '<form method="POST" action="index.php" style="display : inline;">';
echo '<input type="hidden" name="page" value="' . $page . '">';
echo '<input type="hidden" name="tab" value="consistency">';

// easily set other defaults:
$b1_def    = 50;  //Birth date - more than X years after mother's birth
$b2_def    = 60;  //Birth date - more than X years after father's birth
$b3_def    = 15;  //Birth date - less than X years after mother's birth
$b4_def    = 15;  //Birth date - less than X years after father's birth
$bp1_def   = 50;  //Baptism date - more than X years after mother's birth
$bp2_def   = 60;  //Baptism date - more than X years after father's birth
$bp3_def   = 15;  //Baptism date - less than X years after mother's birth
$bp4_def   = 15;  //Baptism date - less than X years after father's birth
$marr1_def = 15;  //Marriage date(s) - less than X years after birth date
$marr2_def = 30;  //Marriage age - age difference of more than X years between partners
$age1_def  = 100; //Age (by death date) - more than X years
$age2_def  = 100; //Age (by burial date) - more than X years
$age3_def  = 100; //Age (up till today) - more than X years 
$b5_def      = 9;   //Birth date - less than 9 months after parents' wedding date
$b6_def      = 9;   //Birth date - less than 9 months after previous sibbling

$checked = " checked";
if (isset($_POST['unmark'])) $checked = '';
if (isset($_POST['mark_all'])) $checked = ' checked';

echo '<h3>' . __('Check consistency of dates') . '</h3>';
echo __('You can mark or unmark any of the check options and change defaults. Then press ');
echo '<input type="submit" style="font-size:120%" name="final_check" value="' . __('Check') . '"><br>';
echo __('(with default settings a full check may take between 12-15 seconds per 10,000 persons)') . '<br><br>';
if ($rtlmarker == "ltr") echo '<table class="humo" style="width:100%;text-align:left;border:none"><tr><td style="border:none;width:50%">';
else echo '<table class="humo" style="width:100%;text-align:right;border:none"><tr><td style="border:none;width:50%">';
echo '<input type="submit" style="font-size:90%" name="unmark" value="' . __('Unmark all options') . '">&nbsp;';
echo '<input type="submit" style="font-size:90%" name="mark_all" value="' . __('Mark all options') . '"><br>';
echo '<input type="checkbox" id="1" name="birth_date1" value="1" ' . $checked . '>' . __('Birth date - after bapt/marr/death/burial date') . '<br>';
// id 2 was moved to end
echo '<input type="checkbox" id="3" name="birth_date3" value="1" ' . $checked . '>' . __('Birth date - more than ');
echo '<input type="text" name="birth_date3_nr" style="width:30px" value="' . $b1_def . '">';
echo __(' years after mother\'s birth') . '<br>';
echo '<input type="checkbox" id="4" name="birth_date4" value="1" ' . $checked . '>' . __('Birth date - more than ');
echo '<input type="text" name="birth_date4_nr" style="width:30px" value="' . $b2_def . '">';
echo __(' years after father\'s birth') . '<br>';
echo '<input type="checkbox" id="5" name="birth_date5" value="1" ' . $checked . '>' . __('Birth date - less than ');
echo '<input type="text" name="birth_date5_nr" style="width:30px" value="' . $b3_def . '">';
echo __(' years after mother\'s birth') . '<br>';
echo '<input type="checkbox" id="6" name="birth_date6" value="1" ' . $checked . '>' . __('Birth date - less than ');
echo '<input type="text" name="birth_date6_nr" style="width:30px" value="' . $b4_def . '">';
echo __(' years after father\'s birth') . '<br>';
//NEW
echo '<input type="checkbox" id="23" name="birth_date7" value="1" ' . $checked . '>' . __('Birth date - less than ');
echo '<input type="text" name="birth_date7_nr" style="width:30px" value="' . $b5_def . '">';
echo __(' months after wedding parents') . '<br>';
echo '<input type="checkbox" id="24" name="birth_date8" value="1" ' . $checked . '>' . __('Birth date - before wedding parents') . '<br>';
echo '<input type="checkbox" id="25" name="birth_date9" value="1" ' . $checked . '>' . __('Birth date - less than ');
echo '<input type="text" name="birth_date9_nr" style="width:30px" value="' . $b6_def . '">';
echo __(' months after previous child of mother') . '<br>';
//END NEW
echo '<input type="checkbox" id="7" name="baptism_date1" value="1" ' . $checked . '>' . __('Baptism date - after death/burial date') . '<br>';
// id 8 was joined in with id 2
echo '<input type="checkbox" id="9" name="baptism_date3" value="1" ' . $checked . '>' . __('Baptism date - more than ');
echo '<input type="text" name="baptism_date3_nr" style="width:30px" value="' . $bp1_def . '">';
echo __(' years after mother\'s birth') . '<br>';
echo '<input type="checkbox" id="10" name="baptism_date4" value="1" ' . $checked . '>' . __('Baptism date - more than ');
echo '<input type="text" name="baptism_date4_nr" style="width:30px" value="' . $bp2_def . '">';
echo __(' years after father\'s birth') . '<br>';
echo '<input type="checkbox" id="11" name="baptism_date5" value="1" ' . $checked . '>' . __('Baptism date - less than ');
echo '<input type="text" name="baptism_date5_nr" style="width:30px" value="' . $bp3_def . '">';
echo __(' years after mother\'s birth') . '<br>';
echo '<input type="checkbox" id="12" name="baptism_date6" value="1" ' . $checked . '>' . __('Baptism date - less than ');
echo '<input type="text" name="baptism_date6_nr" style="width:30px" value="' . $bp4_def . '">';
echo __(' years after father\'s birth') . '<br>';
echo '</td><td style="border:none;width:50%"><br>';
echo '<input type="checkbox" id="13" name="marriage_date1" value="1" ' . $checked . '>' . __('Marriage date(s) - after death/burial date') . '<br>';
echo '<input type="checkbox" id="14" name="marriage_date2" value="1" ' . $checked . '>' . __('Marriage date(s) - less than ');
echo '<input type="text" name="marriage_date2_nr" style="width:30px" value="' . $marr1_def . '">';
echo __(' years after birth date') . '<br>';
echo '<input type="checkbox" id="15" name="marriage_age" value="1" ' . $checked . '>' . __('Marriage age - age difference of more than ');
echo '<input type="text" name="marriage_age_nr" style="width:30px" value="' . $marr2_def . '">';
echo __(' years between partners') . '<br>';
echo '<input type="checkbox" id="16" name="death_date1" value="1" ' . $checked . '>' . __('Death date - after burial date') . '<br>';
echo '<input type="checkbox" id="17" name="death_date2" value="1" ' . $checked . '>' . __('Death date - bef birth of mother') . '<br>';
echo '<input type="checkbox" id="18" name="death_date3" value="1" ' . $checked . '>' . __('Death date - bef birth of father') . '<br>';
echo '<input type="checkbox" id="19" name="burial_date1" value="1" ' . $checked . '>' . __('Burial date - bef birth of mother') . '<br>';
echo '<input type="checkbox" id="20" name="burial_date2" value="1" ' . $checked . '>' . __('Burial date - bef birth of father') . '<br>';
echo '<input type="checkbox" id="21" name="age1" value="1" ' . $checked . '>' . __('Age (by death date) - more than ');
echo '<input type="text" name="age1_nr" style="width:30px" value="' . $age1_def . '">';
echo __(' years') . '<br>';
echo '<input type="checkbox" id="22" name="age2" value="1" ' . $checked . '>' . __('Age (by burial date) - more than ');
echo '<input type="text" name="age2_nr" style="width:30px" value="' . $age2_def . '">';
echo __(' years') . '<br>';
// since displaying people with no death/bur date and not marked as deceased might give a long list, this is not checked by default
echo '<input type="checkbox" id="2" name="birth_date2" value="1">' . __('Age (up till today) - more than ');
echo '<input type="text" name="birth_date2_nr" style="width:30px" value="' . $age3_def . '">';
echo __(' years <b>(may give long list!)') . '</b><br>';
//echo '<br>';
echo '</td></tr></table>';

if (isset($_POST['final_check'])) {
    // performs the date consistency check
    echo '<h3>' . __('Results') . '</h3>';
    if ($rtlmarker == "ltr") echo '<table class="humo" style="width:100%;text-align:left">';
    else echo '<table class="humo" style="width:100%;text-align:right">';
    echo '<tr><th style="width:20%;border:1px solid black;text-align:center;padding-left:5px;padding-right:5px">' . __('Person') . '</th>';
    echo '<th style="width:10%;border:1px solid black;text-align:center;padding-left:5px;padding-right:5px">' . __('ID') . '</th>';
    echo '<th style="width:35%;border:1px solid black;text-align:center;padding-left:5px;padding-right:5px">' . __('Possible consistency problems') . '</th>';
    echo '<th style="width:35%;border:1px solid black;text-align:center;padding-left:5px;padding-right:5px">' . __('Details') . '</th></tr>';

    $results_found = 0;

    // *** First get pers_id, otherwise there will be a memory problem if a large family tree is used ***
    $person_start = $dbh->query("SELECT pers_id FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' ORDER BY pers_lastname,pers_firstname");
    while ($person_startDb = $person_start->fetch()) {

        // *** Now get all data for one person at a time ***
        $person = $dbh->query("SELECT * FROM humo_persons WHERE pers_id='" . $person_startDb['pers_id'] . "'");
        $personDb = $person->fetch();

        /*	// using class slows down considerably: 10,000 persons without class 15 sec, with class for name: over 4 minutes...
            $persclass = New person_cls($personDb);
            $name=$persclass->person_name($personDb); 
        */
        $name = $personDb['pers_lastname'] . ", " . $personDb['pers_firstname'] . ' ' . str_replace("_", " ", $personDb['pers_prefix']);

        // person's dates
        $b_date = '';
        if (isset($personDb['pers_birth_date'])) $b_date = $personDb['pers_birth_date'];
        $bp_date = '';
        if (isset($personDb['pers_bapt_date'])) $bp_date = $personDb['pers_bapt_date'];
        $d_date = '';
        if (isset($personDb['pers_death_date'])) $d_date = $personDb['pers_death_date'];
        $bu_date = '';
        if (isset($personDb['pers_buried_date'])) $bu_date = $personDb['pers_buried_date'];

        // marriage(s) dates and spouses birth date
        if (isset($personDb['pers_fams'])) {
            $marr_dates = array(); // marriage dates array
            $marr_notice_dates = array(); // marriage notice dates array
            $marr_church_dates = array(); // marriage church dates array
            $marr_church_notice_dates = array(); // marriage church notice dates array
            $spouse_dates = array(); // array of spouse birth dates
            $marr_array = array(); // array of marriage gedcomnumbers
            $spouse = "fam_woman";
            if ($personDb['pers_sexe'] == "F") $spouse = "fam_man";
            $marr_array = explode(';', $personDb['pers_fams']);

            for ($x = 0; $x < count($marr_array); $x++) {
                $marriages = $dbh->query("SELECT fam_marr_date, fam_marr_notice_date, fam_marr_church_date, fam_marr_church_notice_date, " . $spouse . " 
                        FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber ='" . $marr_array[$x] . "'");
                $marriagesDb = $marriages->fetch(PDO::FETCH_OBJ);
                if ($marriagesDb !== false) {
                    $marr_dates[$x] = $marriagesDb->fam_marr_date;
                    $marr_notice_dates[$x] = $marriagesDb->fam_marr_notice_date;
                    $marr_church_dates[$x] = $marriagesDb->fam_marr_church_date;
                    $marr_church_notice_dates[$x] = $marriagesDb->fam_marr_church_notice_date;
                    if ($personDb['pers_sexe'] == "F") {
                        $spouses =  $dbh->query("SELECT pers_birth_date FROM humo_persons 
                                WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber ='" . $marriagesDb->fam_man . "'");
                    } else {
                        $spouses =  $dbh->query("SELECT pers_birth_date FROM humo_persons 
                                WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber ='" . $marriagesDb->fam_woman . "'");
                    }
                    $spousesDb = $spouses->fetch(PDO::FETCH_OBJ);
                    if (isset($spousesDb->pers_birth_date)) $spouse_dates[] = $spousesDb->pers_birth_date;
                }
            }
        }

        // parents' dates
        $m_b_date = ''; // mother's birth date
        $f_b_date = ''; // father's birth date
        $par_marr_date = ''; // parents' wedding date
        $sib_b_date = ''; // previous sibling birth date
        $m_fams = ''; // marriage(s) of mother (to find previous sibling)
        $m_fams_arr = array(); // marriage(s) array of mother (to find previous sibling)

        if (isset($personDb['pers_famc'])) {
            $parents = $dbh->query("SELECT fam_gedcomnumber, fam_man, fam_woman, fam_children, fam_marr_date, fam_marr_church_date, fam_marr_notice_date, fam_relation_date
                    FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber ='" . $personDb['pers_famc'] . "'");
            $parentsDb = $parents->fetch(PDO::FETCH_OBJ);
            //NEW - find parents wedding date
            if (isset($parentsDb->fam_marr_date)) {
                $par_marr_date = $parentsDb->fam_marr_date;
            } elseif (isset($parentsDb->fam_marr_church_date)) { // if no civil date try religious marriage
                $par_marr_date = $parentsDb->fam_marr_church_date;
            } elseif (isset($parentsDb->fam_marr_notice_date)) { // if no civil or religious date, try notice date
                $par_marr_date = $parentsDb->fam_marr_notice_date;
            } elseif (isset($parentsDb->fam_marr_relation_date)) { // if non of above try relation date
                $par_marr_date = $parentsDb->fam_relation_date;
            }
            //END NEW
            if (isset($parentsDb->fam_woman)) {
                $mother = $dbh->query("SELECT pers_birth_date, pers_fams FROM humo_persons
                        WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber = '" . $parentsDb->fam_woman . "'");
                $motherDb = $mother->fetch(PDO::FETCH_OBJ);
                if (isset($motherDb->pers_birth_date)) $m_b_date = $motherDb->pers_birth_date;
                if (isset($motherDb->pers_fams)) {
                    $m_fams = $motherDb->pers_fams; // needed for sibling search
                    $m_fams_arr = explode(";", $m_fams);
                }
            }
            if (isset($parentsDb->fam_man)) {
                $father = $dbh->query("SELECT pers_birth_date FROM humo_persons 
                        WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber = '" . $parentsDb->fam_man . "'");
                $fatherDb = $father->fetch(PDO::FETCH_OBJ);
                if (isset($fatherDb->pers_birth_date)) $f_b_date = $fatherDb->pers_birth_date;
            }
            //NEW - find previous born sibling
            if (isset($parentsDb->fam_children)) {
                $ch_array = explode(";", $parentsDb->fam_children);
                $num_ch = count($ch_array); // number of children
                $first_ch = 0;
                if ($num_ch > 1) {  // more than 1 children
                    $count = 0;
                    while ($ch_array[$count] != $personDb['pers_gedcomnumber']) {
                        $count++;
                    }
                    if ($count > 0) {  // person is not first child
                        $prev_sib_gednr = $ch_array[$count - 1]; // gedcomnumber of previous sibling
                        $sib = $dbh->query("SELECT pers_birth_date FROM humo_persons
                                WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber ='" . $prev_sib_gednr . "' AND pers_birth_date NOT LIKE ''");
                        $sibDb = $sib->fetch(PDO::FETCH_OBJ);
                        if (isset($sibDb->pers_birth_date)) {
                            $sib_b_date = $sibDb->pers_birth_date;
                        }
                    } elseif ($count == 0) {
                        $first_ch = 1;
                    }    // this is first child in own fam
                }
                if ($num_ch == 1 or $first_ch == 1) { // if this only or first child in this marriage - look for previous marriage of mother
                    if (isset($m_fams_arr) and count($m_fams_arr) > 1 and $m_fams_arr[0] != $parentsDb->fam_gedcomnumber) {
                        // if mother has more than one marriage and this is not the first, then look for last child in previous marriage
                        $count = 0;
                        while ($m_fams_arr[$count] != $parentsDb->fam_gedcomnumber) {
                            $count++;
                        }
                        $prev_marr_ged = $m_fams_arr[$count - 1];
                        $prev_marr = $dbh->query("SELECT * FROM humo_families
                                WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $prev_marr_ged . "' AND fam_children NOT LIKE ''");
                        $prev_marrDb = $prev_marr->fetch(PDO::FETCH_OBJ);
                        if (isset($prev_marrDb->fam_children)) {
                            $prev_ch_arr = explode(";", $prev_marrDb->fam_children);
                            $prev_ch_num = count($prev_ch_arr);
                            $prev_ch_ged = $prev_ch_arr[$prev_ch_num - 1]; // last child

                            $sibDb = $db_functions->get_person($prev_ch_ged);
                            if (isset($sibDb->pers_birth_date) and $sibDb->pers_birth_date != '') {
                                $sib_b_date = $sibDb->pers_birth_date;
                            }
                        }
                    }
                }
            }
            //END NEW
        }

        if (
            $b_date == '' and $bp_date == '' and $d_date == '' and $bu_date == ''
            and $m_b_date == '' and $f_b_date == '' and !isset($personDb['pers_fams'])
        ) {
            continue; // if no relevant dates at all - don't bother - move to next person
        }

        if ($b_date != '') {

            // ID 1 -  Birth date - after bapt/marr/death/burial date
            if (isset($_POST["birth_date1"]) and $_POST["birth_date1"] == "1") {
                if ($bp_date != '' and compare_seq($b_date, $bp_date) == "2") {
                    write_pers($name, "1", $b_date, $bp_date, __("birth date"), __("baptism date"), 0);
                    $results_found++;
                }
                if ($d_date != '' and compare_seq($b_date, $d_date) == "2") {
                    write_pers($name, "1", $b_date, $d_date, __("birth date"), __("death date"), 0);
                    $results_found++;
                }
                if ($bu_date != '' and compare_seq($b_date, $bu_date) == "2") {
                    write_pers($name, "1", $b_date, $bu_date, __("birth date"), __("burial date"), 0);
                    $results_found++;
                }
                for ($i = 0; $i < count($marr_dates); $i++) {
                    if (isset($marr_dates[$i]) and compare_seq($b_date, $marr_dates[$i]) == "2") {
                        write_pers($name, "1", $b_date, $marr_dates[$i], __("birth date"), __("marriage"), 0);
                        $results_found++;
                    }
                }
            }

            // ID 3 - Birth date more than X years after mother's birth date
            if (isset($_POST["birth_date3"]) and $_POST["birth_date3"] == "1") {
                if ($m_b_date != '') {
                    $gap = compare_gap($m_b_date, $b_date);
                    if ($gap !== false and $gap > $_POST["birth_date3_nr"]) {
                        write_pers($name, "3", $b_date, $m_b_date, __("birth date"), __('mother'), $_POST["birth_date3_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 4 - Birth date more than X years after father's birth date
            if (isset($_POST["birth_date4"]) and $_POST["birth_date4"] == "1") {
                if ($f_b_date != '') {
                    $gap = compare_gap($f_b_date, $b_date);
                    if ($gap !== false and $gap > $_POST["birth_date4_nr"]) {
                        write_pers($name, "4", $b_date, $f_b_date, __("birth date"), __('father'), $_POST["birth_date4_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 5 - Birth date less than X years after mother's birth date
            if (isset($_POST["birth_date5"]) and $_POST["birth_date5"] == "1") {
                if ($m_b_date != '') {
                    $gap = compare_gap($m_b_date, $b_date);
                    if ($gap !== false and $gap < $_POST["birth_date5_nr"]) {
                        write_pers($name, "5", $b_date, $m_b_date, __("birth date"), __('mother'), $_POST["birth_date5_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 6 - Birth date less than X years after father's birth date
            if (isset($_POST["birth_date6"]) and $_POST["birth_date6"] == "1") {
                if ($f_b_date != '') {
                    $gap = compare_gap($f_b_date, $b_date);
                    if ($gap !== false and $gap < $_POST["birth_date6_nr"]) {
                        write_pers($name, "6", $b_date, $f_b_date, __("birth date"), __('father'), $_POST["birth_date6_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 23 - Birth date less than X months after parents' wedding date
            if (isset($_POST["birth_date7"]) and $_POST["birth_date7"] == "1") {
                if ($par_marr_date != '' and compare_seq($par_marr_date, $b_date) != "2") {
                    $gap = compare_month_gap($par_marr_date, $b_date, $_POST["birth_date7_nr"]);
                    if ($gap !== false) {
                        write_pers($name, "23", $b_date, $par_marr_date, __("birth date"), __('parents wedding date'), $_POST["birth_date7_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 24 - Birth date before parents' wedding date
            if (isset($_POST["birth_date8"]) and $_POST["birth_date8"] == "1") {
                if ($par_marr_date != '' and compare_seq($par_marr_date, $b_date) == "2") {
                    write_pers($name, "24", $b_date, $par_marr_date, __("birth date"), __("parents wedding date"), 0);
                    $results_found++;
                }
            }

            // ID 25 - Birth date less than 9 months after previous child of the mother
            if (isset($_POST["birth_date9"]) and $_POST["birth_date9"] == "1") {
                if ($sib_b_date != '' and compare_seq($sib_b_date, $b_date) == "1") {
                    $gap = compare_month_gap($sib_b_date, $b_date, $_POST["birth_date9_nr"]);
                    if ($gap !== false) {
                        write_pers($name, "25", $b_date, $sib_b_date, __("birth date"), __('previous child of mother'), $_POST["birth_date9_nr"]);
                        $results_found++;
                    }
                }
            }

            //END NEW

        } // end if b_date!=''

        if ($bp_date != '') {
            // ID 7 - Baptism date - after death/burial date
            if (isset($_POST["baptism_date1"]) and $_POST["baptism_date1"] == "1") {
                if ($d_date != '' and compare_seq($bp_date, $d_date) == "2") {
                    write_pers($name, "7", $bp_date, $d_date, __("baptism date"), __("death date"), 0);
                    $results_found++;
                }
                if ($bu_date != '' and compare_seq($bp_date, $bu_date) == "2") {
                    write_pers($name, "7", $bp_date, $bu_date, __("baptism date"), __("burial date"), 0);
                    $results_found++;
                }
            }

            // ID 8    CANCELLED - was joined with age check ID 2

            // ID 9 - Baptism date more than X years after mother's birth date
            if (isset($_POST["baptism_date3"]) and $_POST["baptism_date3"] == "1") {
                if ($m_b_date != '') {
                    $gap = compare_gap($m_b_date, $bp_date);
                    if ($gap !== false and $gap > $_POST["baptism_date3_nr"]) {
                        write_pers($name, "9", $bp_date, $m_b_date, __("baptism date"), __('mother'), $_POST["baptism_date3_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 10  - Baptism date more than X years after father's birth date
            if (isset($_POST["baptism_date4"]) and $_POST["baptism_date4"] == "1") {
                if ($f_b_date != '') {
                    $gap = compare_gap($f_b_date, $bp_date);
                    if ($gap !== false and $gap > $_POST["baptism_date4_nr"]) {
                        write_pers($name, "10", $bp_date, $f_b_date, __("baptism date"), __('father'), $_POST["baptism_date4_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 11  - Baptism date less than X years after mother's birth date
            if (isset($_POST["baptism_date5"]) and $_POST["baptism_date5"] == "1") {
                if ($m_b_date != '') {
                    $gap = compare_gap($m_b_date, $bp_date);
                    if ($gap !== false and $gap < $_POST["baptism_date5_nr"]) {
                        write_pers($name, "11", $bp_date, $m_b_date, __("baptism date"), __('mother'), $_POST["baptism_date5_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 12  - Baptism date less than X years after father's birth date
            if (isset($_POST["baptism_date6"]) and $_POST["baptism_date6"] == "1") {
                if ($f_b_date != '') {
                    $gap = compare_gap($f_b_date, $bp_date);
                    if ($gap !== false and $gap < $_POST["baptism_date6_nr"]) {
                        write_pers($name, "12", $bp_date, $f_b_date, __("baptism date"), __('father'), $_POST["baptism_date6_nr"]);
                        $results_found++;
                    }
                }
            }
        }  // end if bp_date!=''

        if (isset($personDb['pers_fams'])) {

            // ID 13 - Marriage date after death/burial date
            if (isset($_POST["marriage_date1"]) and $_POST["marriage_date1"] == "1") {
                for ($i = 0; $i < count($marr_dates); $i++) {
                    if ($marr_dates[$i] != '') {
                        if ($d_date != '' and compare_seq($marr_dates[$i], $d_date) == "2") {
                            write_pers($name, "13", $marr_dates[$i], $d_date, __("marriage"), __("death date"), 0);
                            $results_found++;
                        }
                        if ($bu_date != '' and compare_seq($marr_dates[$i], $bu_date) == "2") {
                            write_pers($name, "13", $marr_dates[$i], $bu_date, __("marriage"), __("burial date"), 0);
                            $results_found++;
                        }
                    }

                    if ($marr_notice_dates[$i] != '') {
                        if ($d_date != '' and compare_seq($marr_notice_dates[$i], $d_date) == "2") {
                            write_pers($name, "13", $marr_notice_dates[$i], $d_date, __("marriage notice"), __("death date"), 0);
                            $results_found++;
                        }
                        if ($bu_date != '' and compare_seq($marr_notice_dates[$i], $bu_date) == "2") {
                            write_pers($name, "13", $marr_notice_dates[$i], $bu_date, __("marriage notice"), __("burial date"), 0);
                            $results_found++;
                        }
                    }
                    if ($marr_church_dates[$i] != '') {
                        if ($d_date != '' and compare_seq($marr_church_dates[$i], $d_date) == "2") {
                            write_pers($name, "13", $marr_church_dates[$i], $d_date, __("church marriage"), __("death date"), 0);
                            $results_found++;
                        }
                        if ($bu_date != '' and compare_seq($marr_church_dates[$i], $bu_date) == "2") {
                            write_pers($name, "13", $marr_church_dates[$i], $bu_date, __("church marriage"), __("burial date"), 0);
                            $results_found++;
                        }
                    }
                    if ($marr_church_notice_dates[$i] != '') {
                        if ($d_date != '' and compare_seq($marr_church_notice_dates[$i], $d_date) == "2") {
                            write_pers($name, "13", $marr_church_notice_dates[$i], $d_date, __("church marriage notice"), __("death date"), 0);
                            $results_found++;
                        }
                        if ($bu_date != '' and compare_seq($marr_church_notice_dates[$i], $bu_date) == "2") {
                            write_pers($name, "13", $marr_church_notice_dates[$i], $bu_date, __("church marriage notice"), __("burial date"), 0);
                            $results_found++;
                        }
                    }
                }
            }

            // ID 14 - Marriage date less than X years after birth date
            if (isset($_POST["marriage_date2"]) and $_POST["marriage_date2"] == "1") {
                for ($i = 0; $i < count($marr_dates); $i++) {
                    if ($marr_dates[$i] != '' and $b_date != '') {
                        $gap = compare_gap($b_date, $marr_dates[$i]);
                        if ($gap !== false and $gap >= 0 and $gap < $_POST["marriage_date2_nr"]) {
                            write_pers($name, "14", $marr_dates[$i], $b_date, __("marriage"), __('birth date'), $_POST["marriage_date2_nr"]);
                            $results_found++;
                        }
                    }
                    if ($marr_notice_dates[$i] != '' and $b_date != '') {
                        $gap = compare_gap($b_date, $marr_notice_dates[$i]);
                        if ($gap !== false and $gap >= 0 and $gap < $_POST["marriage_date2_nr"]) {
                            write_pers($name, "14", $marr_notice_dates[$i], $b_date, __("marriage notice"), __('birth date'), $_POST["marriage_date2_nr"]);
                            $results_found++;
                        }
                    }
                    if ($marr_church_dates[$i] != '' and $b_date != '') {
                        $gap = compare_gap($b_date, $marr_church_dates[$i]);
                        if ($gap !== false and $gap >= 0 and $gap < $_POST["marriage_date2_nr"]) {
                            write_pers($name, "14", $marr_church_dates[$i], $b_date, __("church marriage"), __('birth date'), $_POST["marriage_date2_nr"]);
                            $results_found++;
                        }
                    }
                    if ($marr_church_notice_dates[$i] != '' and $b_date != '') {
                        $gap = compare_gap($b_date, $marr_church_notice_dates[$i]);
                        if ($gap !== false and $gap >= 0 and $gap < $_POST["marriage_date2_nr"]) {
                            write_pers($name, "14", $marr_church_notice_dates[$i], $b_date, __("church marriage notice"), __('birth date'), $_POST["marriage_date2_nr"]);
                            $results_found++;
                        }
                    }
                }
            }

            // ID 15 - More than X years age difference between spouses
            if (isset($_POST["marriage_age"]) and $_POST["marriage_age"] == "1") {
                for ($i = 0; $i < count($spouse_dates); $i++) {
                    if ($spouse_dates[$i] != '' and $b_date != '') {
                        $gap = compare_gap($b_date, $spouse_dates[$i]);
                        if (
                            $gap !== false and
                            abs($gap) > $_POST["marriage_age_nr"]
                        ) {
                            write_pers($name, "15", $spouse_dates[$i], $b_date, __("birth date"), __("Spouse"), $_POST["marriage_age_nr"]);
                            $results_found++;
                        }
                    }
                }
            }
        } // end if pers_fams

        if ($d_date != '') {

            // ID 16 - Death date after burial date
            if (isset($_POST["death_date1"]) and $_POST["death_date1"] == "1") {
                if ($bu_date != '' and compare_seq($d_date, $bu_date) == "2") {
                    write_pers($name, "16", $d_date, $bu_date, __("death date"), __("burial date"), 0);
                    $results_found++;
                }
            }

            // ID 17 - Death date before mother's birth date
            if (isset($_POST["death_date2"]) and $_POST["death_date2"] == "1") {
                if ($m_b_date != '' and compare_seq($d_date, $m_b_date) == "1") {
                    write_pers($name, "17", $d_date, $m_b_date, __("death date"), __("mother"), 0);
                    $results_found++;
                }
            }

            // ID 18 - Death date before father's birth date
            if (isset($_POST["death_date3"]) and $_POST["death_date3"] == "1") {
                if ($f_b_date != '' and compare_seq($d_date, $f_b_date) == "1") {
                    write_pers($name, "18", $d_date, $f_b_date, __("death date"), __("father"), 0);
                    $results_found++;
                }
            }
        } // end if d_date!=''

        if ($bu_date != '') {
            // ID 19 - Burial date before mother's birth date
            if (isset($_POST["burial_date1"]) and $_POST["burial_date1"] == "1") {
                if ($m_b_date != '' and compare_seq($bu_date, $m_b_date) == "1") {
                    write_pers($name, "19", $bu_date, $m_b_date, __("burial date"), __("mother"), 0);
                    $results_found++;
                }
            }

            // ID 20 - Burial date before father's birth date
            if (isset($_POST["burial_date2"]) and $_POST["burial_date2"] == "1") {
                if ($f_b_date != '' and compare_seq($bu_date, $f_b_date) == "1") {
                    write_pers($name, "20", $bu_date, $f_b_date, __("burial date"), __("father"), 0);
                    $results_found++;
                }
            }
        } // end if bu_date!=''

        if ($b_date != '' or $bp_date != '') {

            // ID 21 - Age by death date
            if (isset($_POST["age1"]) and $_POST["age1"] == "1") {
                if ($d_date != '') {
                    if ($b_date != '') {
                        $start_date = $b_date;
                        $txt = __("birth date");
                    } else {
                        $start_date = $bp_date;
                        $txt = __("baptism date");
                    }
                    $gap = compare_gap($start_date, $d_date);
                    if ($gap !== false and $gap > $_POST["age1_nr"]) {
                        write_pers($name, "21", $start_date, $d_date, $txt, __('death date'), $_POST["age1_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 22 - Age by burial date
            if (isset($_POST["age2"]) and $_POST["age2"] == "1") {
                if ($bu_date != '') {
                    if ($b_date != '') {
                        $start_date = $b_date;
                        $txt = __("birth date");
                    } else {
                        $start_date = $bp_date;
                        $txt = __("baptism date");
                    }
                    $gap = compare_gap($start_date, $bu_date);
                    if ($gap !== false and $gap > $_POST["age1_nr"]) {
                        write_pers($name, "22", $start_date, $bu_date, $txt, __('burial date'), $_POST["age2_nr"]);
                        $results_found++;
                    }
                }
            }

            // ID 2 - Age up till today (no death/burial date)
            if (isset($_POST["birth_date2"]) and $_POST["birth_date2"] == "1") {
                $alive = '';
                if (isset($personDb['pers_alive'])) $alive = $personDb['pers_alive'];
                $d_place = '';
                if (isset($personDb['pers_death_place'])) $d_place = $personDb['pers_death_place'];
                $bu_place = '';
                if (isset($personDb['pers_buried_place'])) $bu_place = $personDb['pers_buried_place'];
                if ($d_date == '' and $bu_date == ''  and $d_place == '' and $bu_place == '' and $alive != "deceased") {
                    if ($b_date != '') {
                        $start_date = $b_date;
                        $txt = __("birth date");
                    } else {
                        $start_date = $bp_date;
                        $txt = __("baptism date");
                    }
                    $gap = compare_gap($start_date, date("j M Y"));
                    if ($gap !== false and $gap > $_POST["birth_date2_nr"]) {
                        write_pers($name, "2", $start_date, '', $txt, '', $_POST["birth_date2_nr"]);
                        $results_found++;
                    }
                }
            }
        } // end if $b_date!='' OR $bp_date!=''

    } // end of while loop with $personDb

    if ($results_found == 0) echo '<tr><td style="color:red;text-align:center;font-weight:bold;font-size:120%" colspan=4><br>No inconsistencies found!<br><br></td></tr>';
    echo '</table>';
}


function compare_seq($first_date, $second_date)
{
    // checks sequence of 2 dates (which is the earlier date)
    include_once(__DIR__ . '/../../include/calculate_age_cls.php');
    $process_date = new calculate_year_cls;

    // take care of combined julian/gregorian dates (1678/9)
    if (strpos($first_date, '/') > 0) {
        $temp = explode('/', $first_date);
        $first_date = $temp[0];
    }
    if (strpos($second_date, '/') > 0) {
        $temp = explode('/', $second_date);
        $second_date = $temp[0];
    }

    $first_date = strtoupper($first_date); // $process_date->search_month uses upppercase months: DEC, FEB
    $second_date = strtoupper($second_date);

    $year1 = $process_date->search_year($first_date);
    $month1 = $process_date->search_month($first_date);
    $day1 = $process_date->search_day($first_date);
    $year2 = $process_date->search_year($second_date);
    $month2 = $process_date->search_month($second_date);
    $day2 = $process_date->search_day($second_date);

    if ($year1 and $year2) {
        if ($year1 > $year2) return "2"; // a > b
        elseif ($year1 < $year2) return "1"; // a < b
        elseif ($year1 == $year2) {
            if ($month1 and $month2) {
                if ($month1 > $month2) return "2"; // a > b
                elseif ($month1 < $month2) return "1"; // a < b
                elseif ($month1 == $month2) {
                    if ($day1 and $day2) {
                        if ($day1 > $day2) return "2"; // a > b
                        elseif ($day1 < $day2) return "1"; // a < b
                        elseif ($day1 == $day2) return "3"; // equal
                    } else return "3"; // equal
                }
            } else return "3"; // equal
        }
    } else return 0; // insufficient data
}

function compare_month_gap($first_date, $second_date, $monthgap)
{
    // checks gap in months between two dates (to check for birth less than X months after wedding)
    include_once(__DIR__ . '/../../include/calculate_age_cls.php');
    $process_date = new calculate_year_cls;

    // take care of combined julian/gregorian dates (1678/9)
    if (strpos($first_date, '/') > 0) {
        $temp = explode('/', $first_date);
        $first_date = $temp[0];
    }
    if (strpos($second_date, '/') > 0) {
        $temp = explode('/', $second_date);
        $second_date = $temp[0];
    }
    $first_date = strtoupper($first_date); // $process_date->search_month uses upppercase months: DEC, FEB
    $second_date = strtoupper($second_date);
    $year1 = $process_date->search_year($first_date);
    $month1 = $process_date->search_month($first_date);
    $day1 = $process_date->search_day($first_date);
    $year2 = $process_date->search_year($second_date);
    $month2 = $process_date->search_month($second_date);
    $day2 = $process_date->search_day($second_date);

    if ($year1 and $year2 and $month1 and $month2) {
        if ($year1 == $year2) {  // dates in same year - we can deduct month1 from month2
            if (($month2 - $month1) < $monthgap) return $month2 - $month1;
            else return false;
        } elseif ($year1 + 1 == $year2) { // consecutive years
            if (((12 - $month1) + $month2) < $monthgap) return (12 - $month1) + $month2;
            else return false;
        } else return false;
    } else return false; // insufficient data
}

function compare_gap($first_date, $second_date)
{
    // finds gap between 2 years. No need for months or days, since we look for gaps of several years
    include_once(__DIR__ . '/../../include/calculate_age_cls.php');
    $process_date = new calculate_year_cls;

    // take care of combined julian/gregorian dates (1678/9)
    if (strpos($first_date, '/') > 0) {
        $temp = explode('/', $first_date);
        $first_date = $temp[0];
    }
    if (strpos($second_date, '/') > 0) {
        $temp = explode('/', $second_date);
        $second_date = $temp[0];
    }

    $year1 = $process_date->search_year($first_date);
    $year2 = $process_date->search_year($second_date);

    if ($year1 and $year2) return ($year2 - $year1);
    else return false;
}

function write_pers($name, $id, $first_date, $second_date, $first_text, $second_text, $nr)
{
    // displays results for date consistency check
    global $personDb, $tree_id, $gap;
    $dash = '<span style="font-size:140%;color:red"> &#8596; </span>';
    $second_colon = ': ';

    // use short term for "Details" column
    $first = $first_text;
    $second = $second_text;
    if ($first_text == __('birth date')) $first = __('BORN_SHORT');
    if ($first_text == __('baptism date')) $first = __('BAPTISED_SHORT');
    if ($first_text == __('death date')) $first = __('DIED_SHORT');
    if ($first_text == __('burial date')) $first = __('BURIED_SHORT');
    if ($second_text == __('birth date')) $second = __('BORN_SHORT');
    if ($second_text == __('baptism date')) $second = __('BAPTISED_SHORT');
    if ($second_text == __('death date')) $second = __('DIED_SHORT');
    if ($second_text == __('burial date')) $second = __('BURIED_SHORT');

    echo '<tr><td style="padding-left:5px;padding-right:5px"><a href="../admin/index.php?page=editor&menu_tab=person&tree_id=' . $tree_id . '&person=' . $personDb['pers_gedcomnumber'] . '" target=\'_blank\'>' . $name . '</a></td>';

    echo '<td style="padding-left:5px;padding-right:5px">' . $personDb['pers_gedcomnumber'] . '</td>';
    echo '<td style="padding-left:5px;padding-right:5px">';

    if ($id == "1" or $id == "7" or $id == "13" or $id == "16") {
        echo $first_text . ' ' . __("after") . ' ' . $second_text;
    } elseif ($id == "3" or $id == "4" or $id == "9" or $id == "10") {
        printf(__("%s more than %d years after %s"), $first, $nr, __('birth date') . ' ' . $second_text);
        $second = $second_text . ' ' . __('BORN_SHORT');
    }
    //elseif($id=="9" OR $id=="10") { printf(__("%s more than %d years after %s"),$first,$nr,__('birth date').' '.$second_text); $second = $second_text.' '.__('BAPTISED_SHORT');}
    elseif ($id == "5" or $id == "6" or $id == "11" or $id == "12") {
        printf(__("%s before or less than %d years after %s"), $first, $nr, __('birth date') . ' ' . $second_text);
        $second = $second_text . ' ' . __('BORN_SHORT');
    }
    //elseif($id=="11" OR $id=="12"){ printf(__("%s before or less than %d years after %s"),$first,$nr,__('birth date').' '.$second_text); $second = $second_text.' '.__('BAPTISED_SHORT');}
    elseif ($id == "14") {
        printf(__("%s less than %d years after %s"), $first, $nr, $second_text);
    } elseif ($id == "17" or $id == "18" or $id == "19" or $id == "20") {
        echo $first . ' ' . __("before") . ' ' . __('birth date') . ' ' . $second_text;
        $second = $second_text . ' ' . __('BORN_SHORT');
    } elseif ($id == "2") {
        printf(__("age (up till today) more than %d years (age: %d)"), $nr, $gap);
        $dash = '';
        $second_colon = '';
    } elseif ($id == "21" or $id == "22") {
        printf(__("age (by %s) more than %d years (age: %d)"), $second_text, $nr, $gap);
    } elseif ($id == "15") {
        printf(__("age difference of more than %d years with spouse (%d)"), $nr, abs($gap));
        $second = strtolower($second_text) . ' ' . __('BORN_SHORT');
    } elseif ($id == "23" or $id == "25") {
        printf(__("%s less than %d months after %s"), $first, $nr, $second_text);
    } elseif ($id == "24") {
        printf(__("%s before %s"), $first, $second_text);
    }
    echo '</td>';
    echo '<td style="padding-left:5px;padding-right:5px">' . $first . ': ' . $first_date . $dash . $second . $second_colon . $second_date . '</td></tr>';
}
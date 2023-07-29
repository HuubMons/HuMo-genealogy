<?php

/**
 * Descendant chart. Used to be part of family.php, seperated in july 2023.
 * 
 * July 2023: this script will be refactored. Under construction.
 * 
 */

$screen_mode = 'STAR';

$hourglass = false;
if (isset($_GET["screen_mode"]) and $_GET["screen_mode"] == 'HOUR') {
    $hourglass = true;
}

//TODO check PDF variables. PDF is moved to seperate scripts.
$pdf_source = array();  // is set in show_sources.php with sourcenr as key to be used in source appendix
global $dbh, $chosengen, $genarray, $size, $keepfamily_id, $keepmain_person, $direction;
global $pdf_footnotes;
global $parent1Db, $parent2Db;

//global $temp,$templ_person;
//global $templ_relation;
global $templ_name;

// *** Needed for hourglass ***
include_once(__DIR__ . '../../header.php'); // returns CMS_ROOTPATH constant



// TODO create seperate controller script.
// TEMPORARY CONTROLLER HERE:
require_once  __DIR__ . "/../models/family.php";
$get_family = new Family($dbh);
$family_id = $get_family->getFamilyId();
$main_person = $get_family->getMainPerson();
//$family_expanded =  $get_family->getFamilyExpanded();
//$source_presentation =  $get_family->getSourcePresentation();
//$picture_presentation =  $get_family->getPicturePresentation();
//$text_presentation =  $get_family->getTextPresentation();
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



@set_time_limit(300);

$family_nr = 1;  // *** process multiple families ***

// *** Check if family gedcomnumber is valid ***
$db_functions->check_family($family_id);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($main_person);

$dna = "none"; // DNA setting
if (isset($_GET["dnachart"])) {
    $dna = $_GET["dnachart"];
}
if (isset($_POST["dnachart"])) {
    $dna = $_POST["dnachart"];
}
$chosengen = 4;
if ($dna != "none") $chosengen = "All"; // in DNA chart by default show all generations
if (isset($_GET["chosengen"])) {
    $chosengen = $_GET["chosengen"];
}
if (isset($_POST["chosengen"])) {
    $chosengen = $_POST["chosengen"];
}
$chosengenanc = 4;  // for hourglass -- no. of generations of ancestors
if (isset($_GET["chosengenanc"])) {
    $chosengenanc = $_GET["chosengenanc"];
}
if (isset($_POST["chosengenanc"])) {
    $chosengenanc = $_POST["chosengenanc"];
}
if (isset($_SESSION['chartsize'])) {
    $size = $_SESSION['chartsize'];
} else {
    $size = 50;
    if ($dna != "none") $size = 25;
} // in DNA chart by default zoom position 4
if (isset($_GET["chosensize"])) {
    $size = $_GET["chosensize"];
}
if (isset($_POST["chosensize"])) {
    $size = $_POST["chosensize"];
}
$_SESSION['chartsize'] = $size;
$keepfamily_id = $family_id;
$keepmain_person = $main_person;
$direction = 0; // vertical
if (isset($_GET["direction"])) {
    $direction = $_GET["direction"];
}
if (isset($_POST["direction"])) {
    $direction = $_POST["direction"];
}

if ($dna != "none") {
    if (isset($_GET["bf"])) {
        $base_person_famc = $_GET["bf"];
    }
    if (isset($_POST["bf"])) {
        $base_person_famc = $_POST["bf"];
    }
    if (isset($_GET["bs"])) {
        $base_person_sexe = $_GET["bs"];
    }
    if (isset($_POST["bs"])) {
        $base_person_sexe = $_POST["bs"];
    }
    if (isset($_GET["bn"])) {
        $base_person_name = $_GET["bn"];
    }
    if (isset($_POST["bn"])) {
        $base_person_name = $_POST["bn"];
    }
    if (isset($_GET["bg"])) {
        $base_person_gednr = $_GET["bg"];
    }
    if (isset($_POST["bg"])) {
        $base_person_gednr = $_POST["bg"];
    }
}

$descendant_report = true;
$genarray = array();
$family_expanded = false;


// DNA chart -> change base person to earliest father-line (Y-DNA) or mother-line (Mt-DNA) ancestor
$max_generation = 100;
@$dnaDb = $db_functions->get_person($main_person);

$dnapers_cls = new person_cls;
$dnaname = $dnapers_cls->person_name($dnaDb);
$base_person_name =  $dnaname["standard_name"];    // need these 4 in report_descendant.php
$base_person_sexe = $dnaDb->pers_sexe;
$base_person_famc = $dnaDb->pers_famc;
$base_person_gednr = $dnaDb->pers_gedcomnumber;

if ($dna == "ydna" or $dna == "ydnamark") {
    while (isset($dnaDb->pers_famc) and $dnaDb->pers_famc != "") {
        @$dnaparDb = $db_functions->get_family($dnaDb->pers_famc);
        if ($dnaparDb->fam_man == "") break;
        else {
            $main_person = $dnaparDb->fam_man;
            $family_id  = $dnaDb->pers_famc;
            @$dnaDb = $db_functions->get_person($dnaparDb->fam_man);
        }
    }
}
if ($dna == "mtdna" or $dna == "mtdnamark") {
    while (isset($dnaDb->pers_famc) and $dnaDb->pers_famc != "") {
        @$dnaparDb = $db_functions->get_family($dnaDb->pers_famc);
        if ($dnaparDb->fam_woman == "") break;
        else {
            $main_person = $dnaparDb->fam_woman;
            $family_id  = $dnaDb->pers_famc;
            @$dnaDb = $db_functions->get_person($dnaparDb->fam_woman);
        }
    }
}

// *******************
// *** Show family ***
// *******************
if ($family_id) {
    $descendant_family_id2[] = $family_id;
    $descendant_main_person2[] = $main_person;

    $arraynr = 0;

    // *** Nr. of generations ***
    if ($chosengen != "All") {
        $max_generation = $chosengen - 2;
    } else {
        $max_generation = 100;
    } // any impossibly high number, will anyway stop at last generation

    for ($descendant_loop = 0; $descendant_loop <= $max_generation; $descendant_loop++) {
        $descendant_family_id2[] = 0;
        $descendant_main_person2[] = 0;
        if (!isset($descendant_family_id2[1])) {
            break;
        }

        // TEST code (only works with family, will give error in descendant report and DNA reports:
        // if (!isset($descendant_family_id2[0])){ break; }

        // *** Copy array ***
        unset($descendant_family_id);
        $descendant_family_id = $descendant_family_id2;
        unset($descendant_family_id2);

        unset($descendant_main_person);
        $descendant_main_person = $descendant_main_person2;
        unset($descendant_main_person2);

        if ($descendant_loop != 0) {
            if (isset($genarray[$arraynr])) {
                $temppar = $genarray[$arraynr]["par"];
            }
            while (isset($genarray[$temppar]["gen"]) and $genarray[$temppar]["gen"] == $descendant_loop - 1) {
                $lst_in_array += $genarray[$temppar]["nrc"];
                $temppar++;
            }
        }
        $nrchldingen = 0;

        // *** Nr of families in one generation ***
        $nr_families = count($descendant_family_id);
        for ($descendant_loop2 = 0; $descendant_loop2 < $nr_families; $descendant_loop2++) {

            while (
                isset($genarray[$arraynr]["non"]) and $genarray[$arraynr]["non"] == 1
                and isset($genarray[$arraynr]["gen"]) and $genarray[$arraynr]["gen"] == $descendant_loop
            ) {
                //$genarray[$arraynr]["nrc"]==0;
                $genarray[$arraynr]["nrc"] = 0;
                $arraynr++;
            }

            // Original code:
            //if ($descendant_family_id[$descendant_loop2]==''){ break; }
            if ($descendant_family_id[$descendant_loop2] == '0') {
                break;
            }

            $family_id_loop = $descendant_family_id[$descendant_loop2];
            $main_person = $descendant_main_person[$descendant_loop2];
            $family_nr = 1;

            // *** Count marriages of man ***
            $familyDb = $db_functions->get_family($family_id_loop);
            $parent1 = '';
            $parent2 = '';
            $swap_parent1_parent2 = false;
            // *** Standard main person is the father ***
            if ($familyDb->fam_man) {
                $parent1 = $familyDb->fam_man;
            }
            // *** After clicking the mother, the mother is main person ***
            if ($familyDb->fam_woman == $main_person) {
                $parent1 = $familyDb->fam_woman;
                $swap_parent1_parent2 = true;
            }

            // *** Check for parent1: N.N. ***
            if ($parent1) {
                // *** Save parent1 families in array ***
                $personDb = $db_functions->get_person($parent1);
                $marriage_array = explode(";", $personDb->pers_fams);
                $count_marr = substr_count($personDb->pers_fams, ";");
            } else {
                $marriage_array[0] = $family_id_loop;
                $count_marr = "0";
            }

            // *** Loop multiple marriages of main_person ***
            for ($parent1_marr = 0; $parent1_marr <= $count_marr; $parent1_marr++) {
                $id = $marriage_array[$parent1_marr];
                @$familyDb = $db_functions->get_family($id);

                // Oct. 2021 New method:
                if ($swap_parent1_parent2 == true) {
                    $parent1 = $familyDb->fam_woman;
                    $parent2 = $familyDb->fam_man;
                } else {
                    $parent1 = $familyDb->fam_man;
                    $parent2 = $familyDb->fam_woman;
                }
                @$parent1Db = $db_functions->get_person($parent1);
                // *** Proces parent1 using a class ***
                $parent1_cls = new person_cls($parent1Db);

                @$parent2Db = $db_functions->get_person($parent2);
                // *** Proces parent2 using a class ***
                $parent2_cls = new person_cls($parent2Db);

                // *** Proces marriage using a class ***
                $marriage_cls = new marriage_cls($familyDb, $parent1_cls->privacy, $parent2_cls->privacy);
                $family_privacy = $marriage_cls->privacy;


                // *************************************************************
                // *** Parent1 (normally the father)                         ***
                // *************************************************************
                if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, woman without man
                    if ($family_nr == 1) {
                        //*** Show data of parent1 ***
                        if ($descendant_loop == 0) {
                            $name = $parent1_cls->person_name($parent1Db);
                            $genarray[$arraynr]["nam"] = $name["standard_name"];
                            if (isset($name["colour_mark"]))
                                $genarray[$arraynr]["nam"] .= $name["colour_mark"];
                            $genarray[$arraynr]["init"] = $name["initials"];
                            $genarray[$arraynr]["short"] = $name["short_firstname"];
                            $genarray[$arraynr]["fams"] = $id;
                            if (isset($parent1Db->pers_gedcomnumber))
                                $genarray[$arraynr]["gednr"] = $parent1Db->pers_gedcomnumber;
                            $genarray[$arraynr]["2nd"] = 0;

                            if ($swap_parent1_parent2 == true) {
                                $genarray[$arraynr]["sex"] = "v";
                                if ($dna == "mtdnamark" or $dna == "mtdna") {
                                    $genarray[$arraynr]["dna"] = 1;
                                } else $genarray[$arraynr]["dna"] = "no";
                            } else {
                                $genarray[$arraynr]["sex"] = "m";
                                if ($dna == "ydnamark" or $dna == "ydna" or $dna == "mtdnamark" or $dna == "mtdna") {
                                    $genarray[$arraynr]["dna"] = 1;
                                } else $genarray[$arraynr]["dna"] = "no";
                            }
                        }
                        //$family_nr++;
                    } else {
                        // *** Show standard marriage text and name in 2nd, 3rd, etc. marriage ***
                        if ($descendant_loop == 0) {
                            $genarray[$arraynr] = $genarray[$arraynr - 1];
                            $genarray[$arraynr]["2nd"] = 1;
                            //$genarray[$arraynr]["fams"]=$id;
                        }
                        $genarray[$arraynr]["huw"] = $marriage_cls->marriage_data($familyDb, $family_nr, 'shorter');
                        $genarray[$arraynr]["fams"] = $id;
                    }
                    $family_nr++;
                } // *** End check of PRO-GEN ***


                // *************************************************************
                // *** Marriage                                              ***
                // *************************************************************
                if ($familyDb->fam_kind != 'PRO-GEN') {  // onecht kind, wife without man

                    // *** Check if marriage data must be hidden (also hidden if privacy filter is active) ***
                    if (
                        $user["group_pers_hide_totally_act"] == 'j' and isset($parent1Db->pers_own_code)
                        and strpos(' ' . $parent1Db->pers_own_code, $user["group_pers_hide_totally"]) > 0
                    ) {
                        $family_privacy = true;
                    }
                    if (
                        $user["group_pers_hide_totally_act"] == 'j' and isset($parent2Db->pers_own_code)
                        and strpos(' ' . $parent2Db->pers_own_code, $user["group_pers_hide_totally"]) > 0
                    ) {
                        $family_privacy = true;
                    }

                    if ($family_privacy) {
                        $genarray[$arraynr]["htx"] = $marriage_cls->marriage_data($familyDb, '', 'short');
                    } else {
                        $genarray[$arraynr]["htx"] = $marriage_cls->marriage_data();
                    }
                }

                // *************************************************************
                // *** Parent2 (normally the mother)                         ***
                // *************************************************************
                if ($parent2Db) {
                    $name = $parent2_cls->person_name($parent2Db);
                    $genarray[$arraynr]["sps"] = $name["standard_name"];
                    $genarray[$arraynr]["spgednr"] = $parent2Db->pers_gedcomnumber;
                } else {
                    $genarray[$arraynr]["sps"] = __('Unknown');
                    $genarray[$arraynr]["spgednr"] = ''; // this is a non existing NN spouse!
                }
                $genarray[$arraynr]["spfams"] = $id;


                // *************************************************************
                // *** Marriagetext                                          ***
                // *************************************************************
                $temp = '';

                if ($descendant_loop == 0) {
                    $lst_in_array = $count_marr;
                    $genarray[$arraynr]["gen"] = 0;
                    $genarray[$arraynr]["par"] = -1;
                    $genarray[$arraynr]["chd"] = $arraynr + 1;
                    $genarray[$arraynr]["non"] = 0;
                }

                // *************************************************************
                // *** Children                                              ***
                // *************************************************************

                if (!$familyDb->fam_children) {
                    $genarray[$arraynr]["nrc"] = 0;
                }

                if ($familyDb->fam_children) {
                    $childnr = 1;
                    $child_array = explode(";", $familyDb->fam_children);

                    $genarray[$arraynr]["nrc"] = count($child_array);
                    // dna -> count only man or women
                    if ($dna == "ydna" or $dna == "mtdna") {
                        $countdna = 0;
                        foreach ($child_array as $i => $value) {
                            @$childDb = $db_functions->get_person($child_array[$i]);
                            if ($dna == "ydna" and $childDb->pers_sexe == "M" and $genarray[$arraynr]["sex"] == "m" and $genarray[$arraynr]["dna"] == 1) $countdna++;
                            elseif ($dna == "mtdna" and $genarray[$arraynr]["sex"] == "v" and $genarray[$arraynr]["dna"] == 1) $countdna++;
                        }
                        $genarray[$arraynr]["nrc"] = $countdna;
                    }

                    $show_privacy_text = false;
                    foreach ($child_array as $i => $value) {
                        @$childDb = $db_functions->get_person($child_array[$i]);
                        // *** Use person class ***
                        $child_cls = new person_cls($childDb);

                        $chdn_in_gen = $nrchldingen + $childnr;
                        $place = $lst_in_array + $chdn_in_gen;

                        //if (isset($genarray[$arraynr]["sex"]) AND isset($genarray[$arraynr]["dna"] )){
                        if (($dna == "ydnamark" or $dna == "ydna") and $childDb->pers_sexe == "M"
                            and $genarray[$arraynr]["sex"] == "m" and $genarray[$arraynr]["dna"] == 1
                        ) {
                            $genarray[$place]["dna"] = 1;
                        } elseif (($dna == "mtdnamark" or $dna == "mtdna") and $genarray[$arraynr]["sex"] == "v" and $genarray[$arraynr]["dna"] == 1) {
                            $genarray[$place]["dna"] = 1;
                        } elseif ($dna == "ydna" or $dna == "mtdna") {
                            continue;
                        } else {
                            $genarray[$place]["dna"] = "no";
                        }
                        //}

                        $genarray[$place]["gen"] = $descendant_loop + 1;
                        $genarray[$place]["par"] = $arraynr;
                        $genarray[$place]["chd"] = $childnr;
                        $genarray[$place]["non"] = 0;
                        $genarray[$place]["nrc"] = 0;
                        $genarray[$place]["2nd"] = 0;
                        $name = $child_cls->person_name($childDb);
                        $genarray[$place]["nam"] = $name["standard_name"] . $name["colour_mark"];
                        $genarray[$place]["init"] = $name["initials"];
                        $genarray[$place]["short"] = $name["short_firstname"];
                        $genarray[$place]["gednr"] = $childDb->pers_gedcomnumber;
                        if ($childDb->pers_fams) {
                            $childfam = explode(";", $childDb->pers_fams);
                            $genarray[$place]["fams"] = $childfam[0];
                        } else {
                            $genarray[$place]["fams"] = $childDb->pers_famc;
                        }
                        if ($childDb->pers_sexe == "F") {
                            $genarray[$place]["sex"] = "v";
                        } else {
                            $genarray[$place]["sex"] = "m";
                        }

                        // *** Build descendant_report ***
                        if ($descendant_report == true and $childDb->pers_fams and $descendant_loop < $max_generation) {

                            // *** 1st family of child ***
                            $child_family = explode(";", $childDb->pers_fams);

                            // *** Check for double families in descendant report (if a person relates or marries another person in the same family) ***
                            if (isset($check_double) and in_array($child_family[0], $check_double)) {
                                // *** Don't show this family, double... ***
                            } else
                                $descendant_family_id2[] = $child_family[0];

                            if (count($child_family) > 1) {
                                for ($k = 1; $k < count($child_family); $k++) {
                                    $childnr++;
                                    $thisplace = $place + $k;
                                    $genarray[$thisplace] = $genarray[$place];
                                    $genarray[$thisplace]["chd"] = $childnr;
                                    $genarray[$thisplace]["2nd"] = 1;
                                    $genarray[$arraynr]["nrc"] += 1;
                                }
                            }

                            // *** YB: show children first in descendant_report ***
                            $descendant_main_person2[] = $childDb->pers_gedcomnumber;
                        } else {
                            $genarray[$place]["non"] = 1;
                        }

                        $childnr++;
                    }
                    $nrchldingen += ($childnr - 1);
                }

                $arraynr++;
            } // Show multiple marriages

        } // Multiple families in 1 generation

    } // nr. of generations
    //} // end if not STARSIZE
} // End of single person

// *** If source footnotes are selected, show them here ***
if (isset($_SESSION['save_source_presentation']) and $_SESSION['save_source_presentation'] == 'footnote') {
    echo show_sources_footnotes();
}

if ($hourglass === false) { // in hourglass there's more code after family.php is included
    include_once(CMS_ROOTPATH . "report_descendant.php");
    generate();
    printchart();

    include_once(CMS_ROOTPATH . "footer.php");
}

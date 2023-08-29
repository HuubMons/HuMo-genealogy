<?php

/**
 * OUTLINE REPORT  - report_outline.php
 * by Yossi Beck - Nov 2008 - (on basis of Huub's family.php)
 * Jul 2011 Huub: translation of variables to English
 */
@set_time_limit(300);


//TODO split into PDF and HTML script.

global $show_date, $dates_behind_names, $nr_generations;
global $screen_mode, $language, $humo_option, $user, $selected_language;

// Check for PDF screen mode
$screen_mode = '';
if (isset($_POST["screen_mode"]) and ($_POST["screen_mode"] == 'PDF-L' or $_POST["screen_mode"] == 'PDF-P')) {
    $screen_mode = 'PDF';
}

include_once("header.php"); // returns CMS_ROOTPATH constant



// TODO create seperate controller script.
// TEMPORARY CONTROLLER HERE:
require_once  __DIR__ . "/models/family.php";
$get_family = new Family($dbh);
$family_id = $get_family->getFamilyId();
$main_person = $get_family->getMainPerson();
/*
$family_expanded =  $get_family->getFamilyExpanded();
$source_presentation =  $get_family->getSourcePresentation();
$picture_presentation =  $get_family->getPicturePresentation();
$text_presentation =  $get_family->getTextPresentation();
$number_roman = $get_family->getNumberRoman();
$number_generation = $get_family->getNumberGeneration();
$descendant_report = $get_family->getDescendantReport();
*/
if ($screen_mode != 'PDF') {
    $descendant_header = $get_family->getDescendantHeader('Outline report', $tree_id, $family_id, $main_person);
}
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



if ($screen_mode != 'PDF') {  //we can't have a menu in pdf...
    include_once(CMS_ROOTPATH . "menu.php");
} else {
    if (isset($_SESSION['tree_prefix'])) {
        $dataqry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
            ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
            AND humo_tree_texts.treetext_language='" . $selected_language . "'
            WHERE tree_prefix='" . $tree_prefix_quoted . "'";
        @$datasql = $dbh->query($dataqry);
        @$dataDb = $datasql->fetch(PDO::FETCH_OBJ);
        $tree_id = $dataDb->tree_id;
    }

    include_once(CMS_ROOTPATH . "include/db_functions_cls.php");
    $db_functions = new db_functions;
    $db_functions->set_tree_id($tree_id);
}

// *** Check if family gedcomnumber is valid ***
$db_functions->check_family($family_id);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($main_person);

$show_details = false;
if (isset($_GET["show_details"])) {
    $show_details = $_GET["show_details"];
}
if (isset($_POST["show_details"])) {
    $show_details = $_POST["show_details"];
}

$show_date = true;
if (isset($_GET["show_date"])) {
    $show_date = $_GET["show_date"];
}
if (isset($_POST["show_date"])) {
    $show_date = $_POST["show_date"];
}

$dates_behind_names = true;
if (isset($_GET["dates_behind_names"])) {
    $dates_behind_names = $_GET["dates_behind_names"];
}
if (isset($_POST["dates_behind_names"])) {
    $dates_behind_names = $_POST["dates_behind_names"];
}

// **********************************************************
// *** Maximum number of generations in descendant_report ***
// **********************************************************
$nr_generations = ($humo_option["descendant_generations"] - 1);
if (isset($_GET["nr_generations"])) {
    $nr_generations = $_GET["nr_generations"];
}
if (isset($_POST["nr_generations"])) {
    $nr_generations = $_POST["nr_generations"];
}

if ($screen_mode == 'PDF') {
    //initialize pdf generation
    $pdfdetails = array();
    $pdf_marriage = array();
    @$persDb = $db_functions->get_person($main_person);
    // *** Use person class ***
    $pers_cls = new person_cls($persDb);
    $name = $pers_cls->person_name($persDb);
    $title = pdf_convert(__('Outline report') . __(' of ') . pdf_convert($name["standard_name"]));

    $pdf = new PDF();
    $pdf->SetTitle($title, true);
    $pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
    if (isset($_POST["screen_mode"]) and $_POST["screen_mode"] == "PDF-L") {
        $pdf->AddPage("L");
    } else {
        $pdf->AddPage("P");
    }

    $pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
    $pdf->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
    $pdf->AddFont('DejaVu', 'I', 'DejaVuSansCondensed-Oblique.ttf', true);
    $pdf->AddFont('DejaVu', 'BI', 'DejaVuSansCondensed-BoldOblique.ttf', true);

    $pdf->SetFont($pdf_font, 'B', 15);
    $pdf->Ln(4);
    $pdf->MultiCell(0, 10, __('Outline report') . __(' of ') . pdf_convert($name["standard_name"]), 0, 'C');
    $pdf->Ln(4);
    $pdf->SetFont($pdf_font, '', 12);
}

if ($screen_mode != "PDF") {
    //echo '<h1 class="standard_header fonts">' . __('Outline report') . '</h1>';
    echo $descendant_header;

    echo '<div class="pers_name center print_version">';

    // ******************************************************
    // ******** Button: Show full details (book)  ***********
    // ******************************************************

    echo '<form method="POST" action="report_outline.php" style="display : inline;">';
    echo '<input type="hidden" name="id" value="' . $family_id . '">';
    echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
    echo '<input type="hidden" name="main_person" value="' . $main_person . '">';

    if ($show_details == true) {
        echo '<input type="hidden" name="show_details" value="0">';
        echo '<input class="fonts" type="Submit" name="submit" value="' . __('Hide full details') . '">';
    } else {
        echo '<input type="hidden" name="show_details" value="1">';
        echo '<input class="fonts" type="Submit" name="submit" value="' . __('Show full details') . '">';
    }
    echo '</form>&nbsp;';

    if (!$show_details) {
        // ***************************************
        // ******** Button: Show date  ***********
        // ***************************************
        echo '<form method="POST" action="report_outline.php" style="display : inline;">';
        echo '<input type="hidden" name="id" value="' . $family_id . '">';
        echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
        echo '<input type="hidden" name="main_person" value="' . $main_person . '">';
        if ($show_date == true) {
            echo '<input type="hidden" name="show_date" value="0">';
            echo '<input class="fonts" type="Submit" name="submit" value="' . __('Hide dates') . '">';
        } else {
            echo '<input type="hidden" name="show_date" value="1">';
            echo '<input class="fonts" type="Submit" name="submit" value="' . __('Show dates') . '">';
        }
        echo '</form>';

        // *****************************************************************
        // ******** Show button: date after or below each other ************
        // *****************************************************************
        echo ' <form method="POST" action="report_outline.php" style="display : inline;">';
        echo '<input type="hidden" name="id" value="' . $family_id . '">';
        echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
        echo '<input type="hidden" name="main_person" value="' . $main_person . '">';
        if ($dates_behind_names == "1") {
            echo '<input type="hidden" name="dates_behind_names" value="0">';
            echo '<input type="Submit" class="fonts" name="submit" value="' . __('Dates below names') . '">';
        } else {
            echo '<input type="hidden" name="dates_behind_names" value="1">';
            echo '<input type="Submit" class="fonts" name="submit" value="' . __('Dates beside names') . '">';
        }
        echo '</form>';
    }

    // ********************************************************
    // ******** Show button: nr. of generations    ************
    // ********************************************************
    echo ' <span class="button fonts">';
    echo __('Choose number of generations to display') . ': ';

    echo '<select size=1 name="selectnr_generations" onChange="window.location=this.value;" style="display:inline;">';

    for ($i = 2; $i < 20; $i++) {
        $nr_gen = $i - 1;
        echo '<option';
        if ($nr_gen == $nr_generations) {
            echo ' selected';
        }
        echo ' value="report_outline.php?nr_generations=' . $nr_gen . '&amp;id=' . $family_id . '&amp;main_person=' . $main_person . '&amp;show_details=' . $show_details . '&amp;show_date=' . $show_date . '&amp;dates_behind_names=' . $dates_behind_names . '">' . $i . '</option>';
    }
    echo '<option';
    if ($nr_generations == 50) {
        echo ' selected';
    }

    echo ' value="report_outline.php?nr_generations=50&amp;id=' . $family_id . '&amp;main_person=' . $main_person . '&amp;show_date=' . $show_date . '&amp;dates_behind_names=' . $dates_behind_names . '"> ALL </option>';
    echo '</select>';
    echo '</span>';

    if (!$show_details) {
        echo '&nbsp;&nbsp;&nbsp;<span>';
        //if($language["dir"]!="rtl") {
        if ($user["group_pdf_button"] == 'y' and $language["dir"] != "rtl" and $language["name"] != "简体中文") {
            //Show pdf button
            echo ' <form method="POST" action="' . $uri_path . 'report_outline.php" style="display : inline;">';
            echo '<input type="hidden" name="database" value="' . $_SESSION['tree_prefix'] . '">';
            echo '<input type="hidden" name="screen_mode" value="PDF-P">';
            echo '<input type="hidden" name="id" value="' . $family_id . '">';
            echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
            echo '<input type="hidden" name="dates_behind_names" value="' . $dates_behind_names . '">';
            echo '<input type="hidden" name="show_date" value="' . $show_date . '">';
            echo '<input type="hidden" name="main_person" value="' . $main_person . '">';
            echo '<input class="fonts" type="Submit" name="submit" value="' . __('PDF (Portrait)') . '">';
            echo '</form>';
        }
        echo '</span>';

        echo '&nbsp;&nbsp;&nbsp;<span>';
        //if($language["dir"]!="rtl") {
        if ($user["group_pdf_button"] == 'y' and $language["dir"] != "rtl" and $language["name"] != "简体中文") {
            //Show pdf button
            echo ' <form method="POST" action="' . $uri_path . 'report_outline.php" style="display : inline;">';
            echo '<input type="hidden" name="database" value="' . $_SESSION['tree_prefix'] . '">';
            echo '<input type="hidden" name="screen_mode" value="PDF-L">';
            echo '<input type="hidden" name="id" value="' . $family_id . '">';
            echo '<input type="hidden" name="nr_generations" value="' . $nr_generations . '">';
            echo '<input type="hidden" name="dates_behind_names" value="' . $dates_behind_names . '">';
            echo '<input type="hidden" name="show_date" value="' . $show_date . '">';
            echo '<input type="hidden" name="main_person" value="' . $main_person . '">';
            echo '<input class="fonts" type="Submit" name="submit" value="' . __('PDF (Landscape)') . '">';
            echo '</form>';
        }
        echo '</span>';
    }

    echo '</div><br>';
} // if not PDF

$gn = 0;   // generatienummer

// *************************************
// ****** FUNCTION OUTLINE *************  // recursive function
// *************************************

function outline($family_id, $main_person, $gn, $nr_generations)
{
    global $dbh, $db_functions, $tree_prefix_quoted, $pdf, $pdf_font, $show_details, $show_date, $dates_behind_names, $nr_generations;
    global $language, $dirmark1, $dirmark1, $screen_mode, $user;

    $family_nr = 1; //*** Process multiple families ***

    $show_privacy_text = false;

    if ($nr_generations < $gn) {
        return;
    }
    $gn++;

    // *** Count marriages of man ***
    // *** YB: if needed show woman as main_person ***
    @$familyDb = $db_functions->get_family($family_id, 'man-woman');
    $parent1 = '';
    $parent2 = '';
    $swap_parent1_parent2 = false;

    // *** Standard main_person is the father ***
    if ($familyDb->fam_man) {
        $parent1 = $familyDb->fam_man;
    }
    // *** If mother is selected, mother will be main_person ***
    if ($familyDb->fam_woman == $main_person) {
        $parent1 = $familyDb->fam_woman;
        $swap_parent1_parent2 = true;
    }

    // *** Check family with parent1: N.N. ***
    if ($parent1) {
        // *** Save man's families in array ***
        @$personDb = $db_functions->get_person($parent1, 'famc-fams');
        $marriage_array = explode(";", $personDb->pers_fams);
        $nr_families = substr_count($personDb->pers_fams, ";");
    } else {
        $marriage_array[0] = $family_id;
        $nr_families = "0";
    }

    // *** Loop multiple marriages of main_person ***
    for ($parent1_marr = 0; $parent1_marr <= $nr_families; $parent1_marr++) {
        @$familyDb = $db_functions->get_family($marriage_array[$parent1_marr]);

        // *** Privacy filter man and woman ***
        @$person_manDb = $db_functions->get_person($familyDb->fam_man);
        $man_cls = new person_cls($person_manDb);
        $privacy_man = $man_cls->privacy;

        @$person_womanDb = $db_functions->get_person($familyDb->fam_woman);
        $woman_cls = new person_cls($person_womanDb);
        $privacy_woman = $woman_cls->privacy;

        $marriage_cls = new marriage_cls($familyDb, $privacy_man, $privacy_woman);
        $family_privacy = $marriage_cls->privacy;

        // *************************************************************
        // *** Parent1 (normally the father)                         ***
        // *************************************************************
        if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, vrouw zonder man
            if ($family_nr == 1) {
                // *** Show data of man ***

                $dir = "";
                if ($language["dir"] == "rtl") {
                    $dir = "rtl";    // in the following code calls the css indentation for rtl pages: "div.rtlsub2" instead of "div.sub2"
                }

                $indent = $dir . 'sub' . $gn;  // hier wordt de indent bepaald voor de namen div class (sub1, sub2 enz. die in gedcom.css staan)
                if ($screen_mode != "PDF") {
                    echo '<div class="' . $indent . '">';
                    echo '<span style="font-weight:bold;font-size:120%">' . $gn . ' </span>';
                } else {
                    $pdf->SetLeftMargin($gn * 10);
                    $pdf->Write(8, "\n");
                    $pdf->Write(8, $gn . '  ');
                }
                if ($swap_parent1_parent2 == true) {
                    if ($screen_mode != "PDF") {
                        echo $woman_cls->name_extended("outline");
                        if ($show_details and !$privacy_woman) {
                            echo $woman_cls->person_data("outline", $familyDb->fam_gedcomnumber);
                        }
                    } else {
                        $pdf->SetFont($pdf_font, 'B', 12);
                        $pdf->Write(8, pdf_convert($woman_cls->name_extended("outline")));
                        $pdf->SetFont($pdf_font, '', 12);
                    }
                    if ($show_date == "1" and !$privacy_woman and !$show_details) {
                        if ($screen_mode != "PDF") {
                            echo $dirmark1 . ',';
                            if ($dates_behind_names == false) {
                                echo '<br>';
                            }
                            echo ' &nbsp; (' . language_date($person_womanDb->pers_birth_date) . ' - ' . language_date($person_womanDb->pers_death_date) . ')';
                        } else {
                            if ($dates_behind_names == false) {
                                $pdf->SetLeftMargin($gn * 10 + 4);
                                $pdf->Write(8, "\n");
                            }
                            $pdf_text = language_date($person_womanDb->pers_birth_date) . ' - ' . language_date($person_womanDb->pers_death_date);
                            $pdf->Write(8, ' (' . pdf_convert($pdf_text) . ')');
                        }
                    }
                } else {
                    if ($screen_mode != "PDF") {
                        echo $man_cls->name_extended("outline");
                        if ($show_details and !$privacy_man) {
                            echo $man_cls->person_data("outline", $familyDb->fam_gedcomnumber);
                        }
                    } else {
                        $pdf->SetFont($pdf_font, 'B', 12);
                        $pdf->Write(8, pdf_convert($man_cls->name_extended("outline")));
                        $pdf->SetFont($pdf_font, '', 12);
                    }
                    if ($show_date == "1" and !$privacy_man and !$show_details) {
                        if ($screen_mode != "PDF") {
                            echo $dirmark1 . ',';
                            if ($dates_behind_names == false) {
                                echo '<br>';
                            }
                            echo ' &nbsp; (' . language_date($person_manDb->pers_birth_date) . ' - ' . language_date($person_manDb->pers_death_date) . ')';
                        } else {
                            if ($dates_behind_names == false) {
                                $pdf->SetLeftMargin($gn * 10 + 4);
                                $pdf->Write(8, "\n");
                            }
                            $pdf_text = language_date($person_manDb->pers_birth_date) . ' - ' . language_date($person_manDb->pers_death_date);
                            $pdf->Write(8, ' (' . pdf_convert($pdf_text) . ')');
                        }
                    }
                }
                if ($screen_mode != "PDF") {
                    echo '</div>';
                }
            } else {
            }   // empty: no second show of data of main_person in outline report
            $family_nr++;
        } // *** end check of PRO-GEN ***

        // *************************************************************
        // *** Parent2 (normally the mother)                         ***
        // *************************************************************

        // *** Totally hide parent2 if setting is active ***
        $show_parent2 = true;
        if ($swap_parent1_parent2) {
            if ($user["group_pers_hide_totally_act"] == 'j' and strpos(' ' . $person_manDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
                $show_privacy_text = true;
                $family_privacy = true;
                $show_parent2 = false;
            }
        } else {
            if ($user["group_pers_hide_totally_act"] == 'j' and strpos(' ' . $person_womanDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
                $show_privacy_text = true;
                $family_privacy = true;
                $show_parent2 = false;
            }
        }

        if ($screen_mode != "PDF") {


            // TODO improve this script and use $parent1Db and $parent2Db.
            // Needed for marriage_cls.php. Workaround to solve bug.
            global $parent1Db, $parent2Db;
            if ($swap_parent1_parent2) {
                $parent1Db = $person_womanDb;
                $parent2Db = $person_manDb;
            } else {
                $parent1Db = $person_manDb;
                $parent2Db = $person_womanDb;
            }


            echo '<div class="' . $indent . '" style="font-style:italic">';
            if (!$show_details) {
                echo ' x ' . $dirmark1;
            } else {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                if ($parent1_marr == 0) {
                    if ($family_privacy) {
                        echo $marriage_cls->marriage_data($familyDb, '', 'short') . "<br>";
                    } else {
                        echo $marriage_cls->marriage_data() . "<br>";
                        //echo $marriage_cls->marriage_data($familyDb) . "<br>";
                    }
                } else {
                    echo $marriage_cls->marriage_data($familyDb, $parent1_marr + 1, 'shorter') . ' <br>';
                }
            }
        } else {
            $pdf->SetLeftMargin($gn * 10);
            $pdf->Write(8, "\n");
            $pdf->Write(8, 'x  ');
        }

        if ($show_parent2 and $swap_parent1_parent2) {
            if ($screen_mode != "PDF") {
                if ($show_details) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                }
                echo $man_cls->name_extended("outline");
                if ($show_details and !$privacy_man) {
                    echo $man_cls->person_data("outline", $familyDb->fam_gedcomnumber);
                }
            } else {
                $pdf->SetFont($pdf_font, 'BI', 12);
                $pdf->Write(8, pdf_convert($man_cls->name_extended("outline")));
                $pdf->SetFont($pdf_font, '', 12);
            }
            if ($show_date == "1" and !$privacy_man and !$show_details) {
                if ($screen_mode != "PDF") {
                    echo $dirmark1 . ',';
                    if ($dates_behind_names == false) {
                        echo '<br>';
                    }
                    echo ' &nbsp; (' . @language_date($person_manDb->pers_birth_date) . ' - ' . @language_date($person_manDb->pers_death_date) . ')';
                } else {
                    if ($dates_behind_names == false) {
                        $pdf->SetLeftMargin($gn * 10 + 4);
                        $pdf->Write(8, "\n");
                    }
                    $pdf_text = language_date($person_manDb->pers_birth_date) . ' - ' . language_date($person_manDb->pers_death_date);
                    $pdf->Write(8, ' (' . pdf_convert($pdf_text) . ')');
                }
            }
        } elseif ($show_parent2) {
            if ($screen_mode != "PDF") {
                if ($show_details) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                }
                echo $woman_cls->name_extended("outline");
                if ($show_details and !$privacy_woman) {
                    echo $woman_cls->person_data("outline", $familyDb->fam_gedcomnumber);
                }
            } else {
                $pdf->SetFont($pdf_font, 'BI', 12);
                $pdf->Write(8, pdf_convert($woman_cls->name_extended("outline")));
                $pdf->SetFont($pdf_font, '', 12);
            }
            if ($show_date == "1" and !$privacy_woman and !$show_details) {
                if ($screen_mode != "PDF") {
                    echo $dirmark1 . ',';
                    if ($dates_behind_names == false) {
                        echo '<br>';
                    }
                    echo ' &nbsp; (' . @language_date($person_womanDb->pers_birth_date) . ' - ' . @language_date($person_womanDb->pers_death_date) . ')';
                } else {
                    if ($dates_behind_names == false) {
                        $pdf->SetLeftMargin($gn * 10 + 4);
                        $pdf->Write(8, "\n");
                    }
                    $pdf_text = language_date($person_womanDb->pers_birth_date) . ' - ' . language_date($person_womanDb->pers_death_date);
                    $pdf->Write(8, ' (' . pdf_convert($pdf_text) . ')');
                }
            }
        } elseif ($screen_mode != "PDF") {
            // *** No permission to show parent2 ***
            echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
        }
        if ($screen_mode != "PDF") {
            echo '</div>';
        }

        // *************************************************************
        // *** Children                                              ***
        // *************************************************************
        if ($familyDb->fam_children) {
            $childnr = 1;
            $child_array = explode(";", $familyDb->fam_children);
            foreach ($child_array as $i => $value) {
                @$childDb = $db_functions->get_person($child_array[$i]);

                // *** Totally hide children if setting is active ***
                if ($user["group_pers_hide_totally_act"] == 'j' and strpos(' ' . $childDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
                    if ($screen_mode != "PDF" and !$show_privacy_text) {
                        echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
                        $show_privacy_text = true;
                    }
                    continue;
                }

                $child_cls = new person_cls($childDb);
                $child_privacy = $child_cls->privacy;

                // *** Build descendant_report ***
                if ($childDb->pers_fams) {
                    // *** 1e family of child ***
                    $child_family = explode(";", $childDb->pers_fams);
                    $child1stfam = $child_family[0];
                    outline($child1stfam, $childDb->pers_gedcomnumber, $gn, $nr_generations);  // recursive
                } else {    // Child without own family
                    if ($nr_generations >= $gn) {
                        $childgn = $gn + 1;
                        $childindent = $dir . 'sub' . $childgn;
                        if ($screen_mode != "PDF") {
                            echo '<div class="' . $childindent . '">';
                            echo '<span style="font-weight:bold;font-size:120%">' . $childgn . ' ' . '</span>';
                            echo $child_cls->name_extended("outline");
                            if ($show_details and !$child_privacy) {
                                echo $child_cls->person_data("outline", "");
                            }
                        } else {
                            $pdf->SetLeftMargin($childgn * 10);
                            $pdf->Write(8, "\n");
                            $pdf->Write(8, $childgn . '  ');
                            $pdf->SetFont($pdf_font, 'B', 12);
                            $pdf->Write(8, pdf_convert($child_cls->name_extended("outline")));
                            $pdf->SetFont($pdf_font, '', 12);
                        }
                        if ($show_date == "1" and !$child_privacy and !$show_details) {
                            if ($screen_mode != "PDF") {
                                echo $dirmark1 . ',';
                                if ($dates_behind_names == false) {
                                    echo '<br>';
                                }
                                echo ' &nbsp; (' . language_date($childDb->pers_birth_date) . ' - ' . language_date($childDb->pers_death_date) . ')';
                            } else {
                                if ($dates_behind_names == false) {
                                    $pdf->SetLeftMargin($childgn * 10 + 4);
                                    $pdf->Write(8, "\n");
                                }
                                $pdf_text = language_date($childDb->pers_birth_date) . ' - ' . language_date($childDb->pers_death_date);
                                $pdf->Write(8, ' (' . pdf_convert($pdf_text) . ')');
                            }
                        }
                        if ($screen_mode != "PDF") {
                            echo '</div>';
                        }
                    }
                }
                if ($screen_mode != "PDF") {
                    echo "\n";
                } else {
                }
                $childnr++;
            }
        }
    } // Show  multiple marriages

} // End of outline function


// ******* Start function here - recursive if started ******
if ($screen_mode != 'PDF') {
    echo '<table class="humo outlinetable"><tr><td>';
}

outline($family_id, $main_person, $gn, $nr_generations);

if ($screen_mode != 'PDF') {
    echo '</td></tr></table>';
    include_once(CMS_ROOTPATH . "views/footer.php");
} else {
    $pdf->Output($title . ".pdf", "I");
}

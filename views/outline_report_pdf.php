<?php

/**
 * OUTLINE REPORT  - outline_report.php
 * by Yossi Beck - Nov 2008 - (on basis of Huub's family script)
 * Jul 2011 Huub: translation of variables to English
 * Oct 2023 Huub: seperated HTML and PDF files.
 */

$screen_mode = 'PDF';

// *** Added in nov 2023. TODO check variable, could be added in route.  ***
$tree_id = 0;
if (isset($_POST['tree_id']) && is_numeric($_POST['tree_id'])) {
    $tree_id = $_POST['tree_id'];
}

// TODO create seperate controller script.
$get_family = new \Genealogy\App\Model\FamilyModel($config);
$data["family_id"] = $get_family->getFamilyId();
$data["main_person"] = $get_family->getMainPerson();
$data["text_presentation"] =  $get_family->getTextPresentation();
$data["family_expanded"] =  $get_family->getFamilyExpanded();


$db_functions->set_tree_id($tree_id);

// *** Check if family gedcomnumber is valid ***
$db_functions->check_family($data["family_id"]);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

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

/**
 * Maximum number of generations in descendant_report
 */
$nr_generations = ($humo_option["descendant_generations"] - 1);
if (isset($_GET["nr_generations"])) {
    $nr_generations = $_GET["nr_generations"];
}
if (isset($_POST["nr_generations"])) {
    $nr_generations = $_POST["nr_generations"];
}

//initialize pdf generation
$pdfdetails = array();
$pdf_marriage = array();
$persDb = $db_functions->get_person($data["main_person"]);

$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$personName = new \Genealogy\Include\PersonName();

$privacy = $personPrivacy->get_privacy($persDb);
$name = $personName->get_person_name($persDb, $privacy);

// *** Loading without autoload ***
require_once __DIR__ . '/../include/tfpdf/tFPDFextend.php';
$pdf = new tFPDFextend();

$title = $pdf->pdf_convert(__('Outline report') . __(' of ') . $pdf->pdf_convert($name["standard_name"]));
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

$pdf->SetFont($pdf->pdf_font, 'B', 15);
$pdf->Ln(4);
$pdf->MultiCell(0, 10, __('Outline report') . __(' of ') . $pdf->pdf_convert($name["standard_name"]), 0, 'C');
$pdf->Ln(4);
$pdf->SetFont($pdf->pdf_font, '', 12);



$path_form = $processLinks->get_link($uri_path, 'outline_report', $tree_id);

$generation_number = 0;

function outline($outline_family_id, $outline_main_person, $generation_number, $nr_generations)
{
    global $db_functions, $pdf, $show_details, $show_date, $dates_behind_names, $nr_generations, $language, $user;

    $personPrivacy = new \Genealogy\Include\PersonPrivacy();
    $personName_extended = new \Genealogy\Include\PersonNameExtended();
    $languageDate = new \Genealogy\Include\LanguageDate();
    $totallyFilterPerson = new \Genealogy\Include\TotallyFilterPerson();

    $family_nr = 1; //*** Process multiple families ***

    $show_privacy_text = false;

    if ($nr_generations < $generation_number) {
        return;
    }
    $generation_number++;

    // *** Count marriages of man ***
    // *** YB: if needed show woman as main_person ***
    $familyDb = $db_functions->get_family($outline_family_id, 'man-woman');
    $parent1 = '';
    $parent2 = '';
    $swap_parent1_parent2 = false;

    // *** Standard main_person is the father ***
    if ($familyDb->fam_man) {
        $parent1 = $familyDb->fam_man;
    }
    // *** If mother is selected, mother will be main_person ***
    if ($familyDb->fam_woman == $outline_main_person) {
        $parent1 = $familyDb->fam_woman;
        $swap_parent1_parent2 = true;
    }

    // *** Check family with parent1: N.N. ***
    if ($parent1) {
        // *** Save man's families in array ***
        $personDb = $db_functions->get_person($parent1, 'famc-fams');
        $marriage_array = explode(";", $personDb->pers_fams);
        $nr_families = substr_count($personDb->pers_fams, ";");
    } else {
        $marriage_array[0] = $outline_family_id;
        $nr_families = "0";
    }

    // *** Loop multiple marriages of main_person ***
    for ($parent1_marr = 0; $parent1_marr <= $nr_families; $parent1_marr++) {
        $familyDb = $db_functions->get_family($marriage_array[$parent1_marr]);

        // *** Privacy filter man and woman ***
        $person_manDb = $db_functions->get_person($familyDb->fam_man);
        $privacy_man = $personPrivacy->get_privacy($person_manDb);

        $person_womanDb = $db_functions->get_person($familyDb->fam_woman);
        $privacy_woman = $personPrivacy->get_privacy($person_womanDb);

        $marriage_cls = new \Genealogy\Include\MarriageCls($familyDb, $privacy_man, $privacy_woman);
        // TODO check $family_privacy;
        $family_privacy = $marriage_cls->get_privacy();

        /**
         * Show parent1 (normally the father)
         */
        if ($familyDb->fam_kind != 'PRO-GEN') {
            //onecht kind, vrouw zonder man
            if ($family_nr == 1) {
                // *** Show data of man ***

                $dir = "";
                if ($language["dir"] == "rtl") {
                    $dir = "rtl";    // in the following code calls the css indentation for rtl pages: "div.rtlsub2" instead of "div.sub2"
                }

                $indent = $dir . 'sub' . $generation_number;  // hier wordt de indent bepaald voor de namen div class (sub1, sub2 enz. die in gedcom.css staan)
                $pdf->SetLeftMargin($generation_number * 10);
                $pdf->Write(8, "\n");
                $pdf->Write(8, $generation_number . '  ');

                if ($swap_parent1_parent2 == true) {
                    $pdf->SetFont($pdf->pdf_font, 'B', 12);
                    $pdf->Write(8, $pdf->pdf_convert($personName_extended->name_extended($person_womanDb, $privacy_woman, "outline")));
                    $pdf->SetFont($pdf->pdf_font, '', 12);

                    if ($show_date == "1" && !$privacy_woman && !$show_details) {
                        if ($dates_behind_names == false) {
                            $pdf->SetLeftMargin($generation_number * 10 + 4);
                            $pdf->Write(8, "\n");
                        }
                        $pdf_text = $languageDate->language_date($person_womanDb->pers_birth_date) . ' - ' . $languageDate->language_date($person_womanDb->pers_death_date);
                        $pdf->Write(8, ' (' . $pdf->pdf_convert($pdf_text) . ')');
                    }
                } else {
                    $pdf->SetFont($pdf->pdf_font, 'B', 12);
                    $pdf->Write(8, $pdf->pdf_convert($personName_extended->name_extended($person_manDb, $privacy_man, "outline")));
                    $pdf->SetFont($pdf->pdf_font, '', 12);
                    if ($show_date == "1" && !$privacy_man && !$show_details) {
                        if ($dates_behind_names == false) {
                            $pdf->SetLeftMargin($generation_number * 10 + 4);
                            $pdf->Write(8, "\n");
                        }
                        $pdf_text = $languageDate->language_date($person_manDb->pers_birth_date) . ' - ' . $languageDate->language_date($person_manDb->pers_death_date);
                        $pdf->Write(8, ' (' . $pdf->pdf_convert($pdf_text) . ')');
                    }
                }
            } else {
            }   // empty: no second show of data of main_person in outline report
            $family_nr++;
        } // *** end check of PRO-GEN ***

        /**
         * Show parent2 (normally the mother)
         */

        // *** Totally hide parent2 if setting is active ***
        $show_parent2 = true;
        if ($swap_parent1_parent2) {
            if ($totallyFilterPerson->isTotallyFiltered($user, $person_manDb)) {
                $show_privacy_text = true;
                $family_privacy = true;
                $show_parent2 = false;
            }
        } else {
            if ($totallyFilterPerson->isTotallyFiltered($user, $person_womanDb)) {
                $show_privacy_text = true;
                $family_privacy = true;
                $show_parent2 = false;
            }
        }

        $pdf->SetLeftMargin($generation_number * 10);
        $pdf->Write(8, "\n");
        $pdf->Write(8, 'x  ');

        if ($show_parent2 && $swap_parent1_parent2) {
            $pdf->SetFont($pdf->pdf_font, 'BI', 12);
            $pdf->Write(8, $pdf->pdf_convert($personName_extended->name_extended($person_manDb, $privacy_man, "outline")));
            $pdf->SetFont($pdf->pdf_font, '', 12);
            if ($show_date == "1" && !$privacy_man && !$show_details) {
                if ($dates_behind_names == false) {
                    $pdf->SetLeftMargin($generation_number * 10 + 4);
                    $pdf->Write(8, "\n");
                }
                $pdf_text = $languageDate->language_date($person_manDb->pers_birth_date) . ' - ' . $languageDate->language_date($person_manDb->pers_death_date);
                $pdf->Write(8, ' (' . $pdf->pdf_convert($pdf_text) . ')');
            }
        } elseif ($show_parent2) {
            $pdf->SetFont($pdf->pdf_font, 'BI', 12);
            $pdf->Write(8, $pdf->pdf_convert($personName_extended->name_extended($person_womanDb, $privacy_woman, "outline")));
            $pdf->SetFont($pdf->pdf_font, '', 12);

            if ($show_date == "1" && !$privacy_woman && !$show_details) {
                if ($dates_behind_names == false) {
                    $pdf->SetLeftMargin($generation_number * 10 + 4);
                    $pdf->Write(8, "\n");
                }
                $pdf_text = $languageDate->language_date($person_womanDb->pers_birth_date) . ' - ' . $languageDate->language_date($person_womanDb->pers_death_date);
                $pdf->Write(8, ' (' . $pdf->pdf_convert($pdf_text) . ')');
            }
        }

        /**
         * Show children
         */
        if ($familyDb->fam_children) {
            $childnr = 1;
            $child_array = explode(";", $familyDb->fam_children);
            foreach ($child_array as $i => $value) {
                $childDb = $db_functions->get_person($child_array[$i]);

                // *** Totally hide children if setting is active ***
                if ($totallyFilterPerson->isTotallyFiltered($user, $childDb)) {
                    if (!$show_privacy_text) {
                        //echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
                        //$show_privacy_text = true;
                    }
                    continue;
                }

                $child_privacy = $personPrivacy->get_privacy($childDb);

                // *** Build descendant_report ***
                if ($childDb->pers_fams) {
                    // *** 1e family of child ***
                    $child_family = explode(";", $childDb->pers_fams);
                    $child1stfam = $child_family[0];
                    outline($child1stfam, $childDb->pers_gedcomnumber, $generation_number, $nr_generations);  // recursive
                } else {
                    // Child without own family
                    if ($nr_generations >= $generation_number) {
                        $childgn = $generation_number + 1;
                        $childindent = $dir . 'sub' . $childgn;
                        $pdf->SetLeftMargin($childgn * 10);
                        $pdf->Write(8, "\n");
                        $pdf->Write(8, $childgn . '  ');
                        $pdf->SetFont($pdf->pdf_font, 'B', 12);
                        $pdf->Write(8, $pdf->pdf_convert($personName_extended->name_extended($childDb, $child_privacy, "outline")));
                        $pdf->SetFont($pdf->pdf_font, '', 12);

                        if ($show_date == "1" and !$child_privacy and !$show_details) {
                            if ($dates_behind_names == false) {
                                $pdf->SetLeftMargin($childgn * 10 + 4);
                                $pdf->Write(8, "\n");
                            }
                            $pdf_text = $languageDate->language_date($childDb->pers_birth_date) . ' - ' . $languageDate->language_date($childDb->pers_death_date);
                            $pdf->Write(8, ' (' . $pdf->pdf_convert($pdf_text) . ')');
                        }
                    }
                }
                $childnr++;
            }
        }
    } // Show  multiple marriages

}

// ******* Start function here - recursive if started ******
outline($data["family_id"], $data["main_person"], $generation_number, $nr_generations);

$pdf->Output($title . ".pdf", "I");

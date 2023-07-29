<?php

/**
 * Family/ relation page PDF export
 * Seperated from family.php in july 2023 by Huub.
 * 
 * July 2023: NOT A VIEW YET (must be seperated into MVC). REFACTORING UNDER CONSTRUCTION.
 * First step is refactoring family.php and PDF script...
 */

$screen_mode = 'PDF';

$pdf_source = array();  // is set in show_sources.php with sourcenr as key to be used in source appendix
global $dbh, $chosengen, $genarray, $size, $keepfamily_id, $keepmain_person, $direction;
global $pdf_footnotes;
global $parent1Db, $parent2Db;
global $templ_name;

include_once(__DIR__ . '../../header.php'); // returns CMS_ROOTPATH constant



// TODO create seperate controller script.
// TEMPORARY CONTROLLER HERE:
require_once  __DIR__ . "/../models/family.php";
$get_family = new Family($dbh);
$family_id = $get_family->getFamilyId();
$main_person = $get_family->getMainPerson();
//$family_expanded =  $get_family->getFamilyExpanded();
// TODO expanded view is disabled for PDF. Will we using expand in future for PDF?
// *** No expanded view in PDF export ***
$family_expanded = false;
$source_presentation =  $get_family->getSourcePresentation();
$picture_presentation =  $get_family->getPicturePresentation();
$text_presentation =  $get_family->getTextPresentation();
$number_roman = $get_family->getNumberRoman();
$number_generation = $get_family->getNumberGeneration();
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



@set_time_limit(300);

// TODO test code. Is missing in family.php, maybe not needed.
include_once(__DIR__ . "../../include/db_functions_cls.php");
$db_functions = new db_functions;

// TODO test code. Is missing in family.php, maybe not needed.
if (isset($_SESSION['tree_prefix'])) {
    $dataqry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
        ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
        AND humo_tree_texts.treetext_language='" . $selected_language . "'
        WHERE tree_prefix='" . $tree_prefix_quoted . "'";
    @$datasql = $dbh->query($dataqry);
    @$dataDb = $datasql->fetch(PDO::FETCH_OBJ);
}
$tree_id = $dataDb->tree_id;
$db_functions->set_tree_id($tree_id);

$family_nr = 1;  // *** process multiple families ***

// *** Check if family gedcomnumber is valid ***
$db_functions->check_family($family_id);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($main_person);

// **********************************************************
// *** Maximum number of generations in descendant report ***
// **********************************************************
$max_generation = ($humo_option["descendant_generations"] - 1);

$descendant_report = false;
if (isset($_GET['descendant_report'])) {
    $descendant_report = true;
}
if (isset($_POST['descendant_report'])) {
    $descendant_report = true;
}

$pdfdetails = array();
$pdf_marriage = array();
$pdf = new PDF();

// *** Generate title of PDF file ***
@$persDb = $db_functions->get_person($main_person);
// *** Use class to process person ***
$pers_cls = new person_cls($persDb);
$name = $pers_cls->person_name($persDb);
if (!$descendant_report == false) {
    $title = pdf_convert(__('Descendant report') . __(' of ') . $name["standard_name"]);
} else {
    $title = pdf_convert(__('Family group sheet') . __(' of ') . $name["standard_name"]);
}
$pdf->SetTitle($title, true);

$pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
$pdf->AddPage();

// add utf8 fonts
$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
$pdf->AddFont('DejaVu', 'I', 'DejaVuSansCondensed-Oblique.ttf', true);
$pdf->AddFont('DejaVu', 'BI', 'DejaVuSansCondensed-BoldOblique.ttf', true);

$pdf->SetFont($pdf_font, '', 12);


// **************************
// *** Show single person ***
// **************************
if (!$family_id) {
    // starfieldchart is never called when there is no own fam so no need to mark this out
    // *** Privacy filter ***
    @$parent1Db = $db_functions->get_person($main_person);
    // *** Use class to show person ***
    $parent1_cls = new person_cls($parent1Db);

    // *** Show familysheet name: user's choice or default ***
    $pdf->Cell(0, 2, " ", 0, 1);
    $pdf->SetFont($pdf_font, 'BI', 12);
    $pdf->SetFillColor(196, 242, 107);

    $treetext = show_tree_text($dataDb->tree_id, $selected_language);
    $family_top = $treetext['family_top'];
    if ($family_top != '') {
        $pdf->Cell(0, 6, pdf_convert($family_top), 0, 1, 'L', true);
    } else {
        $pdf->Cell(0, 6, pdf_convert(__('Family group sheet')), 0, 1, 'L', true);
    }

    //$pdf->SetFont($pdf_font,'B',12);
    //$pdf->Write(8,str_replace("&quot;",'"',$parent1_cls->name_extended("parent1")));

    // *** Name ***
    $pdfdetails = $parent1_cls->name_extended("parent1");
    if ($pdfdetails) {
        //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"long");
        $pdf->write_name($templ_name, $pdf->GetX() + 5, "long");

        // *** Resets line ***
        $pdf->MultiCell(0, 8, '', 0, "L");
    }
    $indent = $pdf->GetX();

    //$pdf->SetFont($pdf_font,'',12);
    //$pdf->Write(8,"\n");
    $id = '';
    $pdfdetails = $parent1_cls->person_data("parent1", $id);
    if ($pdfdetails) $pdf->pdfdisplay($pdfdetails, "parent");
}

// *******************
// *** Show family ***
// *******************
else {
    $pdf->SetFont($pdf_font, 'B', 15);
    $pdf->Ln(4);
    $name = $pers_cls->person_name($persDb);
    if (!$descendant_report == false) {
        $pdf->MultiCell(0, 10, __('Descendant report') . __(' of ') . str_replace("&quot;", '"', $name["standard_name"]), 0, 'C');
    } else {
        $pdf->MultiCell(0, 10, __('Family group sheet') . __(' of ') . str_replace("&quot;", '"', $name["standard_name"]), 0, 'C');
    }
    $pdf->Ln(4);
    $pdf->SetFont($pdf_font, '', 12);

    $descendant_family_id2[] = $family_id;
    $descendant_main_person2[] = $main_person;

    // *** Nr. of generations ***
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

        if ($descendant_report == true) {
            $pdf->SetLeftMargin(10);
            $pdf->Cell(0, 2, "", 0, 1);
            $pdf->SetFont($pdf_font, 'BI', 14);
            $pdf->SetFillColor(200, 220, 255);
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                $pdf->SetY(20);
            }
            $pdf->Cell(0, 8, pdf_convert(__('generation ')) . $number_roman[$descendant_loop + 1], 0, 1, 'C', true);
            $pdf->SetFont($pdf_font, '', 12);

            // *** Added mar. 2021 ***
            unset($templ_name);
        }

        // *** Nr of families in one generation ***
        $nr_families = count($descendant_family_id);
        for ($descendant_loop2 = 0; $descendant_loop2 < $nr_families; $descendant_loop2++) {
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


                // *******************************************************************
                // *** Show family                                                 ***
                // *******************************************************************
                // *** Internal link for descendant_report ***
                if ($descendant_report == true) {
                    // *** Internal link (Roman number_generation) ***
                    // put internal PDF link to family
                    $pdf->Cell(0, 1, " ", 0, 1);
                    $romannr = $number_roman[$descendant_loop + 1] . '-' . $number_generation[$descendant_loop2 + 1];
                    if (isset($link[$romannr])) {
                        $pdf->SetLink($link[$romannr], -1); //link to this family from child with "volgt"
                    }
                    $parlink[$id] = $pdf->Addlink();
                    $pdf->SetLink($parlink[$id], -1);   // link to this family from parents
                }

                // Show "Family Page", user's choice or default
                $pdf->SetLeftMargin(10);
                $pdf->Cell(0, 2, " ", 0, 1);
                if ($pdf->GetY() > 260 and $descendant_loop2 != 0) {
                    // move to next page so family sheet banner won't be last on page
                    // but if we are in first family in generation, the gen banner
                    // is already checked so no need here
                    $pdf->AddPage();
                    $pdf->SetY(20);
                }
                $pdf->SetFont($pdf_font, 'BI', 12);
                $pdf->SetFillColor(186, 244, 193);

                $treetext = show_tree_text($dataDb->tree_id, $selected_language);
                $family_top = $treetext['family_top'];
                if ($family_top != '') {
                    $pdf->SetLeftMargin(10);
                    $pdf->Cell(0, 6, pdf_convert($family_top), 0, 1, 'L', true);
                } else {
                    $pdf->SetLeftMargin(10);
                    $pdf->Cell(0, 6, pdf_convert(__('Family group sheet')), 0, 1, 'L', true);
                }
                $pdf->SetFont($pdf_font, '', 12);

                // *************************************************************
                // *** Parent1 (normally the father)                         ***
                // *************************************************************
                if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, woman without man
                    if ($family_nr == 1) {
                        //*** Show data of parent1 ***
                        if ($descendant_report == true) {
                            $pdf->Write(8, $number_roman[$descendant_loop + 1] . '-' . $number_generation[$descendant_loop2 + 1] . " ");
                        }

                        //  PDF rendering of name + details
                        unset($templ_person);
                        unset($templ_name);

                        // *** Name ***
                        $pdfdetails = $parent1_cls->name_extended("parent1");
                        if ($pdfdetails) {
                            //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"long");
                            $pdf->write_name($templ_name, $pdf->GetX() + 5, "long");

                            // *** Resets line ***
                            $pdf->MultiCell(0, 8, '', 0, "L");
                        }
                        $indent = $pdf->GetX();

                        // *** Person data ***
                        $pdf->SetLeftMargin($indent);
                        $pdfdetails = $parent1_cls->person_data("parent1", $id);
                        if ($pdfdetails) {
                            $pdf->pdfdisplay($pdfdetails, "parent1");
                        }
                        $pdf->SetLeftMargin($indent - 5);
                        //$family_nr++;
                    } else {
                        // *** Show standard marriage text and name in 2nd, 3rd, etc. marriage ***
                        $pdf->SetLeftMargin($indent);
                        $pdf_marriage = $marriage_cls->marriage_data($familyDb, $family_nr, 'shorter');
                        $pdf->Write(8, $pdf_marriage["relnr_rel"] . __(' of ') . "\n");

                        unset($templ_person);
                        unset($templ_name);

                        // *** PDF rendering of name ***
                        $pdfdetails = $parent1_cls->name_extended("parent1");
                        if ($pdfdetails) {
                            //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"kort");
                            //TODO check: kort
                            $pdf->write_name($templ_name, $pdf->GetX() + 5, "kort");

                            // *** Resets line ***
                            $pdf->MultiCell(0, 8, '', 0, "L");
                        }
                        $indent = $pdf->GetX();
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
                        $pdf_marriage = $marriage_cls->marriage_data($familyDb, '', 'short');
                        $pdf->SetLeftMargin($indent);
                        if ($pdf_marriage) {
                            $pdf->displayrel($pdf_marriage, "dummy");
                        }
                    } else {
                        $pdf_marriage = $marriage_cls->marriage_data();
                        $pdf->SetLeftMargin($indent);
                        if ($pdf_marriage) {
                            $pdf->displayrel($pdf_marriage, "dummy");
                        }
                    }
                }

                // *************************************************************
                // *** Parent2 (normally the mother)                         ***
                // *************************************************************
                unset($templ_person);
                unset($templ_name);
                // PDF rendering of name + details
                $pdf->Write(8, " "); // IMPORTANT - otherwise at bottom of page man/woman.gif image will print, but name may move to following page!
                $pdfdetails = $parent2_cls->name_extended("parent2");
                if ($pdfdetails) {
                    //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"long");
                    $pdf->write_name($templ_name, $pdf->GetX() + 5, "long");

                    // *** Resets line ***
                    $pdf->MultiCell(0, 8, '', 0, "L");
                }
                $indent = $pdf->GetX();

                $pdfdetails = $parent2_cls->person_data("parent2", $id);
                $pdf->SetLeftMargin($indent);
                if ($pdfdetails) {
                    $pdf->pdfdisplay($pdfdetails, "parent2");
                }


                // *************************************************************
                // *** Marriagetext                                          ***
                // *************************************************************
                $temp = '';

                if ($family_privacy) {
                    // No marriage data
                } else {
                    if ($user["group_texts_fam"] == 'j' and process_text($familyDb->fam_text)) {
                        // PDF rendering of marriage notes
                        //$pdf->SetFont($pdf_font,'I',11);
                        //$pdf->Write(6,process_text($familyDb->fam_text)."\n");
                        //$pdf->Write(6,show_sources2("family","fam_text_source",$familyDb->fam_gedcomnumber)."\n");
                        //$pdf->SetFont($pdf_font,'',12);

                        $templ_relation["fam_text"] = $familyDb->fam_text;
                        $temp = "fam_text";

                        $source_array = show_sources2("family", "fam_text_source", $familyDb->fam_gedcomnumber);
                        if ($source_array) {
                            $templ_relation["fam_text_source"] = $source_array['text'];
                            $temp = "fam_text_source";
                        }
                    }
                }

                // *** Show addresses by family ***
                if ($user['group_living_place'] == 'j') {
                    $fam_address = show_addresses('family', 'family_address', $familyDb->fam_gedcomnumber);
                }

                // *** Family source ***
                $source_array = show_sources2("family", "family_source", $familyDb->fam_gedcomnumber);
                if ($source_array) {
                    if ($temp) $templ_relation[$temp] .= '. ';

                    $templ_relation["fam_source"] = $source_array['text'];
                    $temp = "fam_source";
                    $pdf->displayrel($templ_relation, "dummy");
                }

                // *************************************************************
                // *** Children                                              ***
                // *************************************************************

                if ($familyDb->fam_children) {
                    $childnr = 1;
                    $child_array = explode(";", $familyDb->fam_children);

                    unset($templ_person);
                    unset($templ_name);

                    $pdf->SetLeftMargin(10);
                    $pdf->SetDrawColor(200);  // grey line
                    $pdf->Cell(0, 2, " ", 'B', 1);

                    $show_privacy_text = false;
                    foreach ($child_array as $i => $value) {
                        @$childDb = $db_functions->get_person($child_array[$i]);
                        // *** Use person class ***
                        $child_cls = new person_cls($childDb);

                        // For now don't use this code in DNA and other graphical charts. Because they will be corrupted.
                        // *** Person must be totally hidden ***
                        if ($user["group_pers_hide_totally_act"] == 'j' and strpos(' ' . $childDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
                            $show_privacy_text = true;
                            continue;
                        }

                        // *** PDF rendering of name and details ***
                        $pdf->SetFont($pdf_font, 'B', 11);
                        $pdf->SetLeftMargin($indent);
                        $pdf->Write(6, $childnr . '. ');

                        unset($templ_person);
                        unset($templ_name);
                        $pdfdetails = $child_cls->name_extended("child");
                        if ($pdfdetails) {
                            //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"long");
                            $pdf->write_name($templ_name, $pdf->GetX() + 5, "child");

                            // *** Resets line ***
                            //$pdf->MultiCell(0,8,'',0,"L");   // NOT IN USE WITH CHILD
                        }
                        //$indent=$pdf->GetX();

                        // *** Build descendant_report ***
                        if ($descendant_report == true and $childDb->pers_fams and $descendant_loop < $max_generation) {

                            // *** 1st family of child ***
                            $child_family = explode(";", $childDb->pers_fams);

                            // *** Check for double families in descendant report (if a person relates or marries another person in the same family) ***
                            if (isset($check_double) and in_array($child_family[0], $check_double)) {
                                // *** Don't show this family, double... ***
                            } else
                                $descendant_family_id2[] = $child_family[0];

                            // *** Save all marriages of person in check array ***
                            for ($k = 0; $k < count($child_family); $k++) {
                                $check_double[] = $child_family[$k];
                                // *** Save "Follows: " text in array, also needed for doubles... ***
                                $follows_array[] = $number_roman[$descendant_loop + 2] . '-' . $number_generation[count($descendant_family_id2)];
                            }

                            // *** YB: show children first in descendant_report ***
                            $descendant_main_person2[] = $childDb->pers_gedcomnumber;

                            // PDF rendering of link to own family
                            $pdf->Write(6, ', ' . __('follows') . ': ');
                            $search_nr = array_search($child_family[0], $check_double);
                            $romnr = $follows_array[$search_nr];
                            $link[$romnr] = $pdf->AddLink();
                            $pdf->SetFont($pdf_font, 'B', 11);
                            $pdf->SetTextColor(28, 28, 255); // "B" was "U" . Underscore doesn't exist in tfpdf
                            $pdf->Write(6, $romnr . "\n", $link[$romnr]);
                            $pdf->SetFont($pdf_font, '', 12);
                            $pdf->SetTextColor(0);
                            $parentchild[$romnr] = $id;
                        } else {
                            // *** PDF rendering of child details ***
                            $pdf->Write(6, "\n");
                            unset($templ_person);
                            unset($templ_name);

                            $pdf_child = $child_cls->person_data("child", $id);
                            if ($pdf_child) {
                                $child_indent = $indent + 5;
                                $pdf->SetLeftMargin($child_indent);
                                $pdf->pdfdisplay($pdf_child, "child");
                                $pdf->SetLeftMargin($indent);
                            }
                        }

                        $childnr++;
                    }
                    $pdf->SetFont($pdf_font, '', 12);
                }
            } // Show multiple marriages

        } // Multiple families in 1 generation

    } // nr. of generations
} // End of single person


// *** List appendix of sources ***
if (!empty($pdf_source) and ($source_presentation == 'footnote' or $user['group_sources'] == 'j')) {
    //include_once(CMS_ROOTPATH . "source.php");
    include_once(__DIR__ . '/../source.php');
    $pdf->AddPage(); // appendix on new page
    $pdf->SetFont($pdf_font, "B", 14);
    $pdf->Write(8, __('Sources') . "\n\n");
    $pdf->SetFont($pdf_font, '', 10);
    // *** The $pdf_source array is set in show_sources.php with sourcenr as key and value if a linked source is given ***
    $count = 0;

    foreach ($pdf_source as $key => $value) {
        $count++;
        if (isset($pdf_source[$key])) {
            $pdf->SetLink($pdf_footnotes[$count - 1], -1);
            $pdf->SetFont($pdf_font, 'B', 10);
            $pdf->Write(6, $count . ". ");
            if ($user['group_sources'] == 'j') {
                source_display($pdf_source[$key]);  // function source_display from source.php, called with source nr.
            } elseif ($user['group_sources'] == 't') {
                $sourceDb = $db_functions->get_source($pdf_source[$key]);
                if ($sourceDb->source_title or $sourceDb->source_text) {
                    //$pdf->SetFont($pdf_font,'B',10);
                    //$pdf->Write(6,__('Title').": ");
                    $pdf->SetFont($pdf_font, '', 10);

                    if (trim($sourceDb->source_title))
                        $txt = ' ' . trim($sourceDb->source_title);
                    else $txt = ' ' . trim($sourceDb->source_text);

                    if ($sourceDb->source_date or $sourceDb->source_place) {
                        $txt .= " " . date_place($sourceDb->source_date, $sourceDb->source_place);
                    }
                    $pdf->Write(6, $txt . "\n");
                }
            }
            $pdf->Write(2, "\n");
            $pdf->SetDrawColor(200);  // grey line
            $pdf->Cell(0, 2, " ", 'B', 1);
            $pdf->Write(4, "\n");
        }
    }
    unset($value);
}

$pdf->Output($title . ".pdf", "I");

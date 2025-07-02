<?php

/**
 * Family/ relation page PDF export
 * Seperated from family script in july 2023 by Huub.
 */

$personPrivacy = new PersonPrivacy();
$personName = new PersonName();
$personName_extended = new PersonNameExtended;
$personData = new PersonData;
$datePlace = new DatePlace();
$processText = new ProcessText();

$screen_mode = 'PDF';
$pdf_source = array();  // is set in show_sources.php with sourcenr as key to be used in source appendix
$dirmark1 = '';
$dirmark2 = '';

// TODO create seperate controller script.
$get_family = new FamilyModel($config);
$data["family_id"] = $get_family->getFamilyId();
$data["main_person"] = $get_family->getMainPerson();
$data["family_expanded"] =  $get_family->getFamilyExpanded();
$data["source_presentation"] =  $get_family->getSourcePresentation();
$data["picture_presentation"] =  $get_family->getPicturePresentation();
$data["text_presentation"] =  $get_family->getTextPresentation();
$data["number_roman"] = $get_family->getNumberRoman();
$data["number_generation"] = $get_family->getNumberGeneration();

$db_functions->set_tree_id($tree_id);

$family_nr = 1;  // *** process multiple families ***

// *** Check if family gedcomnumber is valid ***
$db_functions->check_family($data["family_id"]);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

// *** Maximum number of generations in descendant report ***
$max_generation = ($humo_option["descendant_generations"] - 1);

$data["descendant_report"] = false;
if (isset($_GET['descendant_report'])) {
    $data["descendant_report"] = true;
}
if (isset($_POST['descendant_report'])) {
    $data["descendant_report"] = true;
}

$pdfdetails = array();
$pdf_marriage = array();
$pdf = new tFPDFextend();

// *** Generate title of PDF file ***
$persDb = $db_functions->get_person($data["main_person"]);
$privacy = $personPrivacy->get_privacy($persDb);
$name = $personName->get_person_name($persDb, $privacy);

if (!$data["descendant_report"] == false) {
    $title = $pdf->pdf_convert(__('Descendant report') . __(' of ') . $name["standard_name"]);
} else {
    $title = $pdf->pdf_convert(__('Family group sheet') . __(' of ') . $name["standard_name"]);
}
$pdf->SetTitle($title, true);

$pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
$pdf->AddPage();

// add utf8 fonts
$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
$pdf->AddFont('DejaVu', 'I', 'DejaVuSansCondensed-Oblique.ttf', true);
$pdf->AddFont('DejaVu', 'BI', 'DejaVuSansCondensed-BoldOblique.ttf', true);

$pdf->SetFont($pdf->pdf_font, '', 12);


/**
 * Show single person
 */
if (!$data["family_id"]) {
    $parent1Db = $db_functions->get_person($data["main_person"]);
    $parent1_privacy = $personPrivacy->get_privacy($parent1Db);

    // *** Show familysheet name: user's choice or default ***
    $pdf->Cell(0, 2, " ", 0, 1);
    $pdf->SetFont($pdf->pdf_font, 'BI', 12);
    $pdf->SetFillColor(196, 242, 107);

    $treetext = $showTreeText ->show_tree_text($tree_id, $selected_language);
    $family_top = $treetext['family_top'];
    if ($family_top != '') {
        $pdf->Cell(0, 6, $pdf->pdf_convert($family_top), 0, 1, 'L', true);
    } else {
        $pdf->Cell(0, 6, $pdf->pdf_convert(__('Family group sheet')), 0, 1, 'L', true);
    }

    // *** Name ***
    $pdfdetails = $personName_extended->name_extended($parent1Db, $parent1_privacy, "parent1");
    if ($pdfdetails) {
        $pdf->write_name($templ_name, $pdf->GetX() + 5, "long");

        // *** Resets line ***
        $pdf->MultiCell(0, 8, '', 0, "L");
    }
    $indent = $pdf->GetX();

    $id = '';
    $pdfdetails = $personData->person_data($parent1Db, $parent1_privacy, "parent1", $id);
    if ($pdfdetails) {
        $pdf->pdfdisplay($pdfdetails, "parent");
    }
}

// *******************
// *** Show family ***
// *******************
else {
    $pdf->SetFont($pdf->pdf_font, 'B', 15);
    $pdf->Ln(4);

    $privacy = $personPrivacy->get_privacy($persDb);
    $name = $personName->get_person_name($persDb, $privacy);

    if (!$data["descendant_report"] == false) {
        $pdf->MultiCell(0, 10, __('Descendant report') . __(' of ') . str_replace("&quot;", '"', $name["standard_name"]), 0, 'C');
    } else {
        $pdf->MultiCell(0, 10, __('Family group sheet') . __(' of ') . str_replace("&quot;", '"', $name["standard_name"]), 0, 'C');
    }
    $pdf->Ln(4);
    $pdf->SetFont($pdf->pdf_font, '', 12);

    $descendant_family_id2[] = $data["family_id"];
    $descendant_main_person2[] = $data["main_person"];

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

        if ($data["descendant_report"] == true) {
            $pdf->SetLeftMargin(10);
            $pdf->Cell(0, 2, "", 0, 1);
            $pdf->SetFont($pdf->pdf_font, 'BI', 14);
            $pdf->SetFillColor(200, 220, 255);
            if ($pdf->GetY() > 250) {
                $pdf->AddPage();
                $pdf->SetY(20);
            }
            $pdf->Cell(0, 8, $pdf->pdf_convert(__('generation ')) . $data["number_roman"][$descendant_loop + 1], 0, 1, 'C', true);
            $pdf->SetFont($pdf->pdf_font, '', 12);

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
            $data["main_person"] = $descendant_main_person[$descendant_loop2];
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
            if ($familyDb->fam_woman == $data["main_person"]) {
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
                $familyDb = $db_functions->get_family($id);

                // Oct. 2021 New method:
                if ($swap_parent1_parent2 == true) {
                    $parent1 = $familyDb->fam_woman;
                    $parent2 = $familyDb->fam_man;
                } else {
                    $parent1 = $familyDb->fam_man;
                    $parent2 = $familyDb->fam_woman;
                }
                $parent1Db = $db_functions->get_person($parent1);
                $parent1_privacy = $personPrivacy->get_privacy($parent1Db);

                $parent2Db = $db_functions->get_person($parent2);
                $parent2_privacy = $personPrivacy->get_privacy($parent2Db);

                $marriage_cls = new MarriageCls($familyDb, $parent1_privacy, $parent2_privacy);
                $family_privacy = $marriage_cls->get_privacy();


                /**
                 * Show family
                 */
                // *** Internal link for descendant_report ***
                if ($data["descendant_report"] == true) {
                    // *** Internal link (Roman number_generation) ***
                    // put internal PDF link to family
                    $pdf->Cell(0, 1, " ", 0, 1);
                    $romannr = $data["number_roman"][$descendant_loop + 1] . '-' . $data["number_generation"][$descendant_loop2 + 1];
                    if (isset($link[$romannr])) {
                        $pdf->SetLink($link[$romannr], -1); //link to this family from child with "volgt"
                    }
                    $parlink[$id] = $pdf->Addlink();
                    $pdf->SetLink($parlink[$id], -1);   // link to this family from parents
                }

                // Show "Family Page", user's choice or default
                $pdf->SetLeftMargin(10);
                $pdf->Cell(0, 2, " ", 0, 1);
                if ($pdf->GetY() > 260 && $descendant_loop2 != 0) {
                    // move to next page so family sheet banner won't be last on page
                    // but if we are in first family in generation, the gen banner
                    // is already checked so no need here
                    $pdf->AddPage();
                    $pdf->SetY(20);
                }
                $pdf->SetFont($pdf->pdf_font, 'BI', 12);
                $pdf->SetFillColor(186, 244, 193);

                $treetext = $showTreeText ->show_tree_text($tree_id, $selected_language);
                $family_top = $treetext['family_top'];
                if ($family_top != '') {
                    $pdf->SetLeftMargin(10);
                    $pdf->Cell(0, 6, $pdf->pdf_convert($family_top), 0, 1, 'L', true);
                } else {
                    $pdf->SetLeftMargin(10);
                    $pdf->Cell(0, 6, $pdf->pdf_convert(__('Family group sheet')), 0, 1, 'L', true);
                }
                $pdf->SetFont($pdf->pdf_font, '', 12);

                /**
                 * Parent1 (normally the father)
                 */
                if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, woman without man
                    if ($family_nr == 1) {
                        //*** Show data of parent1 ***
                        if ($data["descendant_report"] == true) {
                            $pdf->Write(8, $data["number_roman"][$descendant_loop + 1] . '-' . $data["number_generation"][$descendant_loop2 + 1] . " ");
                        }

                        //  PDF rendering of name + details
                        unset($templ_person);
                        unset($templ_name);

                        // *** Name ***
                        $pdfdetails = $personName_extended->name_extended($parent1Db, $parent1_privacy, "parent1");
                        if ($pdfdetails) {
                            //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"long");
                            $pdf->write_name($templ_name, $pdf->GetX() + 5, "long");

                            // *** Resets line ***
                            $pdf->MultiCell(0, 8, '', 0, "L");
                        }
                        $indent = $pdf->GetX();

                        // *** Person data ***
                        $pdf->SetLeftMargin($indent);
                        $pdfdetails = $personData->person_data($parent1Db, $parent1_privacy,"parent1", $id);
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
                        $pdfdetails = $personName_extended->name_extended($parent1Db, $parent1_privacy, "parent1");
                        if ($pdfdetails) {
                            //TODO check: kort
                            $pdf->write_name($templ_name, $pdf->GetX() + 5, "kort");

                            // *** Resets line ***
                            $pdf->MultiCell(0, 8, '', 0, "L");
                        }
                        $indent = $pdf->GetX();
                    }
                    $family_nr++;
                }


                /**
                 * Show marriage
                 */
                if ($familyDb->fam_kind != 'PRO-GEN') {  // onecht kind, wife without man

                    // *** Check if marriage data must be hidden (also hidden if privacy filter is active) ***
                    if (
                        $user["group_pers_hide_totally_act"] == 'j' && isset($parent1Db->pers_own_code) && strpos(' ' . $parent1Db->pers_own_code, $user["group_pers_hide_totally"]) > 0
                    ) {
                        $family_privacy = true;
                    }
                    if (
                        $user["group_pers_hide_totally_act"] == 'j' && isset($parent2Db->pers_own_code) && strpos(' ' . $parent2Db->pers_own_code, $user["group_pers_hide_totally"]) > 0
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

                /**
                 * Parent2 (normally the mother)
                 */
                unset($templ_person);
                unset($templ_name);
                // PDF rendering of name + details
                $pdf->Write(8, " "); // IMPORTANT - otherwise at bottom of page man/woman.gif image will print, but name may move to following page!
                $pdfdetails = $personName_extended->name_extended($parent2Db, $parent2_privacy, "parent2");
                if ($pdfdetails) {
                    //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"long");
                    $pdf->write_name($templ_name, $pdf->GetX() + 5, "long");

                    // *** Resets line ***
                    $pdf->MultiCell(0, 8, '', 0, "L");
                }
                $indent = $pdf->GetX();

                $pdfdetails = $personData->person_data($parent2Db, $parent2_privacy,"parent2", $id);
                $pdf->SetLeftMargin($indent);
                if ($pdfdetails) {
                    $pdf->pdfdisplay($pdfdetails, "parent2");
                }


                /**
                 * Marriagetext
                 */
                $temp = '';

                if ($family_privacy) {
                    // No marriage data
                } elseif ($user["group_texts_fam"] == 'j' && $processText->process_text($familyDb->fam_text)) {
                    // PDF rendering of marriage notes
                    //$pdf->SetFont($pdf->pdf_font,'I',11);
                    //$pdf->Write(6,process_text($familyDb->fam_text)."\n");
                    //$pdf->Write(6,show_sources2("family","fam_text_source",$familyDb->fam_gedcomnumber)."\n");
                    //$pdf->SetFont($pdf->pdf_font,'',12);
                    $templ_relation["fam_text"] = $familyDb->fam_text;
                    $temp = "fam_text";
                    $source_array = show_sources2("family", "fam_text_source", $familyDb->fam_gedcomnumber);
                    if ($source_array) {
                        $templ_relation["fam_text_source"] = $source_array['text'];
                        $temp = "fam_text_source";
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

                /**
                 * Children
                 */
                if ($familyDb->fam_children) {
                    $childnr = 1;
                    $child_array = explode(";", $familyDb->fam_children);

                    unset($templ_person);
                    unset($templ_name);

                    $pdf->SetLeftMargin(10);
                    $pdf->SetDrawColor(200);  // grey line
                    $pdf->Cell(0, 2, " ", 'B', 1);

                    $show_privacy_text = false;

                    // TODO show text in PDF export
                    // *** Show "Child(ren):" ***
                    /*
                    echo '<div class="py-3"><b>';
                    if (count($child_array) == '1') {
                        echo __('Child') . ':';
                    } else {
                        echo __('Children') . ':';
                    }
                    echo '</b></div>';
                    */

                    foreach ($child_array as $i => $value) {
                        $childDb = $db_functions->get_person($child_array[$i]);
                        $child_privacy = $personPrivacy->get_privacy($childDb);

                        // For now don't use this code in DNA and other graphical charts. Because they will be corrupted.
                        // *** Person must be totally hidden ***
                        if ($user["group_pers_hide_totally_act"] == 'j' && strpos(' ' . $childDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
                            $show_privacy_text = true;
                            continue;
                        }

                        // *** PDF rendering of name and details ***
                        $pdf->SetFont($pdf->pdf_font, 'B', 11);
                        $pdf->SetLeftMargin($indent);
                        $pdf->Write(6, $childnr . '. ');

                        unset($templ_person);
                        unset($templ_name);
                        $pdfdetails = $personName_extended->name_extended($childDb, $child_privacy, "child");
                        if ($pdfdetails) {
                            //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"long");
                            $pdf->write_name($templ_name, $pdf->GetX() + 5, "child");

                            // *** Resets line ***
                            //$pdf->MultiCell(0,8,'',0,"L");   // NOT IN USE WITH CHILD
                        }
                        //$indent=$pdf->GetX();

                        // *** Build descendant_report ***
                        if ($data["descendant_report"] == true && $childDb->pers_fams && $descendant_loop < $max_generation) {

                            // *** 1st family of child ***
                            $child_family = explode(";", $childDb->pers_fams);

                            // *** Check for double families in descendant report (if a person relates or marries another person in the same family) ***
                            if (isset($check_double) && in_array($child_family[0], $check_double)) {
                                // *** Don't show this family, double... ***
                            } else
                                $descendant_family_id2[] = $child_family[0];

                            // *** Save all marriages of person in check array ***
                            for ($k = 0; $k < count($child_family); $k++) {
                                $check_double[] = $child_family[$k];
                                // *** Save "Follows: " text in array, also needed for doubles... ***
                                $follows_array[] = $data["number_roman"][$descendant_loop + 2] . '-' . $data["number_generation"][count($descendant_family_id2)];
                            }

                            // *** YB: show children first in descendant_report ***
                            $descendant_main_person2[] = $childDb->pers_gedcomnumber;

                            // PDF rendering of link to own family
                            $pdf->Write(6, ', ' . __('follows') . ': ');
                            $search_nr = array_search($child_family[0], $check_double);
                            $romnr = $follows_array[$search_nr];
                            $link[$romnr] = $pdf->AddLink();
                            $pdf->SetFont($pdf->pdf_font, 'B', 11);
                            $pdf->SetTextColor(28, 28, 255); // "B" was "U" . Underscore doesn't exist in tfpdf
                            $pdf->Write(6, $romnr . "\n", $link[$romnr]);
                            $pdf->SetFont($pdf->pdf_font, '', 12);
                            $pdf->SetTextColor(0);
                            $parentchild[$romnr] = $id;
                        } else {
                            // *** PDF rendering of child details ***
                            $pdf->Write(6, "\n");
                            unset($templ_person);
                            unset($templ_name);

                            $pdf_child = $personData->person_data($childDb, $child_privacy, "child", $id);
                            if ($pdf_child) {
                                $child_indent = $indent + 5;
                                $pdf->SetLeftMargin($child_indent);
                                $pdf->pdfdisplay($pdf_child, "child");
                                $pdf->SetLeftMargin($indent);
                            }
                        }

                        $childnr++;
                    }
                    $pdf->SetFont($pdf->pdf_font, '', 12);
                }
            } // Show multiple marriages

        } // Multiple families in 1 generation

    } // nr. of generations
}


// *** List appendix of sources ***
if (!empty($pdf_source) and ($data["source_presentation"] == 'footnote' or $user['group_sources'] == 'j')) {
    include_once(__DIR__ . '/../include/show_source_pdf.php');
    $pdf->AddPage(); // appendix on new page
    $pdf->SetFont($pdf->pdf_font, "B", 14);
    $pdf->Write(8, __('Sources') . "\n\n");
    $pdf->SetFont($pdf->pdf_font, '', 10);
    // *** The $pdf_source array is set in show_sources.php with sourcenr as key and value if a linked source is given ***
    $count = 0;

    foreach ($pdf_source as $key => $value) {
        $count++;
        if (isset($pdf_source[$key])) {
            $pdf->SetLink($pdf_footnotes[$count - 1], -1);
            $pdf->SetFont($pdf->pdf_font, 'B', 10);
            $pdf->Write(6, $count . ". ");
            if ($user['group_sources'] == 'j') {
                source_display_pdf($pdf_source[$key]);  // function source_display from source.php, called with source nr.
            } elseif ($user['group_sources'] == 't') {
                $sourceDb = $db_functions->get_source($pdf_source[$key]);
                if ($sourceDb->source_title || $sourceDb->source_text) {
                    //$pdf->SetFont($pdf->pdf_font,'B',10);
                    //$pdf->Write(6,__('Title').": ");
                    $pdf->SetFont($pdf->pdf_font, '', 10);

                    if (trim($sourceDb->source_title))
                        $txt = ' ' . trim($sourceDb->source_title);
                    else $txt = ' ' . trim($sourceDb->source_text);

                    if ($sourceDb->source_date || $sourceDb->source_place) {
                        $txt .= " " . $datePlace->date_place($sourceDb->source_date, $sourceDb->source_place);
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

<?php

/**
 * First test script made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011: translated all variables to english by: Huub Mons.
 */

$screen_mode = 'PDF';
$pdf_source = array();  // is set in show_sources with sourcenr as key to be used in source appendix

$db_functions->set_tree_id($tree_id);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

//initialize pdf generation
$pdfdetails = array();
$pdf_marriage = array();

// *** Loading without autoload (tFPDF isn't using nameclass yet) ***
require_once __DIR__ . '/../include/tfpdf/tFPDFextend.php';
$pdf = new tFPDFextend();

$persDb = $db_functions->get_person($data["main_person"]);

$personPrivacy = new \Genealogy\Include\PersonPrivacy();
$personName = new \Genealogy\Include\PersonName();
$personName_extended = new \Genealogy\Include\PersonNameExtended();
$personData = new \Genealogy\Include\PersonData();
$showSourcePDF = new \Genealogy\Include\ShowSourcePDF();
$ancestorLabel = new \Genealogy\Include\AncestorLabel();

$privacy = $personPrivacy->get_privacy($persDb);
$name = $personName->get_person_name($persDb, $privacy);
$datePlace = new \Genealogy\Include\DatePlace();

$title = $pdf->pdf_convert(__('Ancestor report') . __(' of ') . $pdf->pdf_convert($name["standard_name"]), 0, 'C');

$pdf->SetTitle($title, true);
$pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
$pdf->AddPage();

$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
$pdf->AddFont('DejaVu', 'I', 'DejaVuSansCondensed-Oblique.ttf', true);
$pdf->AddFont('DejaVu', 'BI', 'DejaVuSansCondensed-BoldOblique.ttf', true);

$pdf->SetFont($pdf->pdf_font, 'B', 15);
$pdf->Ln(4);

$pdf->MultiCell(0, 10, __('Ancestor report') . __(' of ') . $pdf->pdf_convert($name["standard_name"]), 0, 'C');
$pdf->Ln(4);
$pdf->SetFont($pdf->pdf_font, '', 12);

$ancestor_array2[] = $data["main_person"];
$ancestor_number2[] = 1;
$marriage_gedcomnumber2[] = 0;
$generation = 1;

$listed_array = array();

// *** Loop for ancestor report ***
while (isset($ancestor_array2[0])) {
    unset($ancestor_array);
    $ancestor_array = $ancestor_array2;
    unset($ancestor_array2);

    unset($ancestor_number);
    $ancestor_number = $ancestor_number2;
    unset($ancestor_number2);

    unset($marriage_gedcomnumber);
    $marriage_gedcomnumber = $marriage_gedcomnumber2;
    unset($marriage_gedcomnumber2);

    $pdf->Cell(0, 2, "", 0, 1);
    $pdf->SetFont($pdf->pdf_font, 'BI', 14);
    $pdf->SetFillColor(200, 220, 255);
    if ($pdf->GetY() > 260) {
        $pdf->AddPage();
        $pdf->SetY(20);
    }

    $generationLabel = $ancestorLabel->getLabel($generation);
    if ($generationLabel) {
        $pdf->Cell(0, 8, $pdf->pdf_convert(__('Generation') . ' ' . $data["rom_nr"][$generation] . ' (' . $generationLabel . ')'), 0, 1, 'C', true);
    } elseif (isset($data["rom_nr"][$generation])) {
        $pdf->Cell(0, 8, $pdf->pdf_convert(__('Generation') . ' ' . $data["rom_nr"][$generation]), 0, 1, 'C', true);
    }

    $pdf->SetFont($pdf->pdf_font, '', 12);
    // *** Loop per generation ***
    $counter = count($ancestor_array);

    // *** Loop per generation ***
    for ($i = 0; $i < $counter; $i++) {

        $listednr = '';
        // Check this code, can be improved?
        foreach ($listed_array as $key => $value) {
            if ($value == $ancestor_array[$i]) {
                $listednr = $key;
            }
            // if person was already listed, $listednr gets kwartier number for reference in report:
            // instead of person's details it will say: "already listed above under number 4234"
            // and no additional ancestors will be looked for, to prevent duplicated branches
        }
        if ($listednr == '') {
            //if not listed yet, add person to array
            $listed_array[$ancestor_number[$i]] = $ancestor_array[$i];
        }

        if ($ancestor_array[$i] != '0') {
            $person_manDb = $db_functions->get_person($ancestor_array[$i]);
            $privacy_man = $personPrivacy->get_privacy($person_manDb);

            if (strtolower($person_manDb->pers_sexe) === 'm' && $ancestor_number[$i] > 1) {
                $familyDb = $db_functions->get_family($marriage_gedcomnumber[$i]);

                // *** Use privacy filter of woman ***
                $person_womanDb = $db_functions->get_person($familyDb->fam_woman);
                $privacy_woman = $personPrivacy->get_privacy($person_womanDb);

                $marriage_cls = new \Genealogy\Include\MarriageCls($familyDb, $privacy_man, $privacy_woman);
                $family_privacy = $marriage_cls->get_privacy();
            }

            unset($templ_person);

            // PDF number
            $pdf->SetX(10);
            $pdf->pdf_ancestor_name($ancestor_number[$i], $person_manDb->pers_sexe, '');

            // *** Name ***
            unset($templ_person);
            unset($templ_name);
            $pdfdetails = $personName_extended->name_extended($person_manDb, $privacy_man, "child");
            if ($pdfdetails) {
                // BUG: layout_pdf.php isn't used anymore. For some reason name is in smaller font.

                //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"long");
                $pdf->write_name($templ_name, $pdf->GetX() + 5, "long");
                // *** Resets line ***
                $pdf->MultiCell(0, 8, '', 0, "L");
            }

            if ($listednr == '') {
                $pdfdetails = $personData->person_data($person_manDb, $privacy_man, "standard", $ancestor_array[$i]);
                if ($pdfdetails) {
                    $pdf->SetLeftMargin(38);
                    $pdf->pdfdisplay($pdfdetails, "ancestor");
                    $pdf->SetLeftMargin(10);
                } elseif ($ancestor_number[$i] > 9999) {
                    $pdf->Ln(8); // (number) was placed under previous number
                    //  and there's no data so we have to move 1 line down for next person 
                }
            } else {
                // person was already listed
                $thisx = $pdf->GetX();
                $pdf->SetX($thisx + 28);
                $pdf->Write(8, __('Already listed above as number ') . $listednr . "\n");
                $pdf->SetX($thisx);
            }

            //$temp = 0;
            $temp = floor($ancestor_number[$i] % 2);
            if ($ancestor_number[$i] > 1 && $temp == 1 && $i + 1 < count($ancestor_array)) {
                // if we're not in first generation (one person)
                // and we are after writing the woman's details
                // and there is at least one person of another family to come in this generation
                // then place a devider line
                //$pdf->Cell(0,1,"",'B',1);
                //$pdf->Ln(1);

                // *** Added space ***
                $pdf->Ln(7);

                $pdf->Cell(0, 1, "", 'B', 1);

                // *** Added space ***
                $pdf->Ln(4);
            }

            // Show own marriage (new line, after man)
            if (strtolower($person_manDb->pers_sexe) === 'm' && $ancestor_number[$i] > 1) {
                if ($family_privacy) {
                    $pdf->SetX(37);
                    $pdf->Write(6, __(' to: ') . "\n");

                    // If privacy filter is activated, show divorce
                    if ($familyDb->fam_div_date || $familyDb->fam_div_place) {
                        $pdf->Write(6, ' (' . trim(__('divorced ')) . ')');
                    }
                    // Show end of relation here?
                    //if ($familyDb->fam_relation_end_date){
                    //  echo ' <span class="divorse">('.trim(__('divorced ')).')</span>';
                    //}
                } else {
                    // To calculate age by marriage.
                    $parent1Db = $person_manDb;
                    $parent2Db = $person_womanDb;
                    //show pdf MARRIAGE DATA
                    $pdf_marriage = $marriage_cls->marriage_data();
                    if ($pdf_marriage) {
                        $pdf->displayrel($pdf_marriage, "ancestor");
                    }
                }
            }

            // ==	Check for parents
            if ($person_manDb->pers_famc && $listednr == '') {
                $family_parentsDb = $db_functions->get_family($person_manDb->pers_famc);
                if ($family_parentsDb->fam_man) {
                    $ancestor_array2[] = $family_parentsDb->fam_man;
                    $ancestor_number2[] = (2 * $ancestor_number[$i]);
                    $marriage_gedcomnumber2[] = $person_manDb->pers_famc;
                }

                if ($family_parentsDb->fam_woman) {
                    $ancestor_array2[] = $family_parentsDb->fam_woman;
                    $ancestor_number2[] = (2 * $ancestor_number[$i] + 1);
                    $marriage_gedcomnumber2[] = $person_manDb->pers_famc;
                } else {
                    // *** N.N. name ***
                    $ancestor_array2[] = '0';
                    $ancestor_number2[] = (2 * $ancestor_number[$i] + 1);
                    $marriage_gedcomnumber2[] = $person_manDb->pers_famc;
                }
            }
        } else {

            // *** Show N.N. person ***
            $person_manDb = $db_functions->get_person($ancestor_array[$i]);
            $privacy_man = $personPrivacy->get_privacy($person_manDb);

            unset($templ_person);

            // pdf NUMBER + NAME + DATA  NN PERSON
            // *** May 2021: changed, only number is shown **
            //$pdf->pdf_ancestor_name($ancestor_number[$i],'',__('N.N.'));
            $pdf->SetX(10);
            $pdf->pdf_ancestor_name($ancestor_number[$i], '', '');
            //$pdf->SetX($pdf->GetX()+3);
            //$pdf->MultiCell(0,8,__('N.N.'),0,"L");
            //$pdf->SetFont($pdf->pdf_font,'',12);

            $pdf->SetLeftMargin(38);
            $pdf->SetX($pdf->GetX() + 3);
            $personName_extended->name_extended($person_manDb, $privacy_man, "child");
            $pdf->Ln(7);

            $pdfdetails = $personData->person_data($person_manDb, $privacy_man, "standard", $ancestor_array[$i]);
            if ($pdfdetails) {
                $pdf->pdfdisplay($pdfdetails, "ancestor");
            } elseif ($ancestor_number[$i] > 9999) {
                $pdf->Ln(8); // (number) was placed under previous number
                //  and there's no data so we have to move 1 line down for next person 
            }
            $temp = 0;
            $temp = floor($ancestor_number[$i] % 2);
            if ($ancestor_number[$i] > 1 && $temp == 1 && $i + 1 < count($ancestor_array)) {
                // if we're not in first generation (one person)
                // and we are after writing the woman's details
                // and there is at least one person of another family to come in this generation
                // then place a divider line between the families in this generation
                $pdf->Cell(0, 1, "", 'B', 1);
                $pdf->Ln(1);
            }
        }
    }    // loop per generation
    $generation++;
}


// List appendix of sources
if (!empty($pdf_source) and ($data["source_presentation"] == 'footnote' or $user['group_sources'] == 'j')) {
    $pdf->AddPage(); // appendix on new page
    $pdf->SetFont($pdf->pdf_font, "B", 14);
    $pdf->Write(8, __('Sources') . "\n\n");
    $pdf->SetFont($pdf->pdf_font, '', 10);
    // the $pdf_source array is set in show_sources with sourcenr as key and value if a linked source is given
    $count = 0;

    foreach ($pdf_source as $key => $value) {
        $count++;
        if (isset($pdf_source[$key])) {
            $pdf->SetLink($pdf_footnotes[$count - 1], -1);
            $pdf->SetFont($pdf->pdf_font, 'B', 10);
            $pdf->Write(6, $count . ". ");
            if ($user['group_sources'] == 'j') {
                $showSourcePDF->source_display_pdf($pdf_source[$key]);  // function source_display from source.php, called with source nr.
            } elseif ($user['group_sources'] == 't') {
                $db_functions->get_source($pdf_source[$key]);
                if ($sourceDb->source_title) {
                    $pdf->SetFont($pdf->pdf_font, 'B', 10);
                    $pdf->Write(6, __('Title:') . " ");
                    $pdf->SetFont($pdf->pdf_font, '', 10);
                    $txt = ' ' . trim($sourceDb->source_title);
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

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

$pdf_source = array();  // is set in show_sources.php with sourcenr as key to be used in source appendix

include_once(__DIR__ . "/layout_pdf.php");



// TODO create seperate controller script.
require_once  __DIR__ . "/../app/model/ancestor.php";
$get_ancestor = new AncestorModel($dbh);
$data["main_person"] = $get_ancestor->getMainPerson();
$rom_nr = $get_ancestor->getNumberRoman();

// TODO for now using extended class.
$data["text_presentation"] =  $get_ancestor->getTextPresentation();
$data["family_expanded"] =  $get_ancestor->getFamilyExpanded();
$data["picture_presentation"] =  $get_ancestor->getPicturePresentation();
// source_presentation is saved in session.



// TODO improve this code. $tree_id allready processed in header.
// 2024: at this moment this can't be removed yet...
//       Variable $dataDb->tree_pict_path is used to show pictures in PDF in show_picture.php!!!
// *** Set variable for queries ***
$tree_prefix_quoted = safe_text_db($_SESSION['tree_prefix']);
if (isset($_SESSION['tree_prefix'])) {
    $dataqry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
        ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
        AND humo_tree_texts.treetext_language='" . $selected_language . "'
        WHERE tree_prefix='" . $tree_prefix_quoted . "'";
    @$datasql = $dbh->query($dataqry);
    @$dataDb = @$datasql->fetch(PDO::FETCH_OBJ);
}
//$tree_prefix = $dataDb->tree_prefix;
//$tree_id = $dataDb->tree_id;

$db_functions->set_tree_id($tree_id);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

//initialize pdf generation
$pdfdetails = array();
$pdf_marriage = array();

$pdf = new PDF();
@$persDb = $db_functions->get_person($data["main_person"]);
// *** Use person class ***
$pers_cls = new Person_cls($persDb);
$name = $pers_cls->person_name($persDb);

// $title not in use?
//$title=pdf_convert(__('Ancestor report').__(' of ').str_replace("&quot;",'"',$name["standard_name"]),0,'C');
$title = pdf_convert(__('Ancestor report') . __(' of ') . pdf_convert($name["standard_name"]), 0, 'C');

$pdf->SetTitle($title, true);
$pdf->SetAuthor('Huub Mons (pdf: Yossi Beck)');
$pdf->AddPage();

$pdf->AddFont('DejaVu', '', 'DejaVuSansCondensed.ttf', true);
$pdf->AddFont('DejaVu', 'B', 'DejaVuSansCondensed-Bold.ttf', true);
$pdf->AddFont('DejaVu', 'I', 'DejaVuSansCondensed-Oblique.ttf', true);
$pdf->AddFont('DejaVu', 'BI', 'DejaVuSansCondensed-BoldOblique.ttf', true);

$pdf->SetFont($pdf_font, 'B', 15);
$pdf->Ln(4);
$name = $pers_cls->person_name($persDb);
//$pdf->MultiCell(0,10,__('Ancestor report').__(' of ').$name["standard_name"],0,'C');
$pdf->MultiCell(0, 10, __('Ancestor report') . __(' of ') . pdf_convert($name["standard_name"]), 0, 'C');
$pdf->Ln(4);
$pdf->SetFont($pdf_font, '', 12);

$ancestor_array2[] = $data["main_person"];
$ancestor_number2[] = 1;
$marriage_gedcomnumber2[] = 0;
$generation = 1;

$language["gen1"] = '';
if (__('PROBANT') != 'PROBANT') {
    $language["gen1"] .= __('PROBANT');
}
$language["gen2"] = __('Parents');
$language["gen3"] = __('Grandparents');
$language["gen4"] = __('Great-Grandparents');
$language["gen5"] = __('Great Great-Grandparents');
$language["gen6"] = __('3rd Great-Grandparents');
$language["gen7"] = __('4th Great-Grandparents');
$language["gen8"] = __('5th Great-Grandparents');
$language["gen9"] = __('6th Great-Grandparents');
$language["gen10"] = __('7th Great-Grandparents');
$language["gen11"] = __('8th Great-Grandparents');
$language["gen12"] = __('9th Great-Grandparents');
$language["gen13"] = __('10th Great-Grandparents');
$language["gen14"] = __('11th Great-Grandparents');
$language["gen15"] = __('12th Great-Grandparents');
$language["gen16"] = __('13th Great-Grandparents');
$language["gen17"] = __('14th Great-Grandparents');
$language["gen18"] = __('15th Great-Grandparents');
$language["gen19"] = __('16th Great-Grandparents');
$language["gen20"] = __('17th Great-Grandparents');
$language["gen21"] = __('18th Great-Grandparents');
$language["gen22"] = __('19th Great-Grandparents');
$language["gen23"] = __('20th Great-Grandparents');
$language["gen24"] = __('21th Great-Grandparents');
$language["gen25"] = __('22th Great-Grandparents');
$language["gen26"] = __('23th Great-Grandparents');
$language["gen27"] = __('24th Great-Grandparents');
$language["gen28"] = __('25th Great-Grandparents');
$language["gen29"] = __('26th Great-Grandparents');
$language["gen30"] = __('27th Great-Grandparents');
$language["gen31"] = __('28th Great-Grandparents');
$language["gen32"] = __('29th Great-Grandparents');
$language["gen33"] = __('30th Great-Grandparents');
$language["gen34"] = __('31th Great-Grandparents');
$language["gen35"] = __('32th Great-Grandparents');
$language["gen36"] = __('33th Great-Grandparents');
$language["gen37"] = __('34th Great-Grandparents');
$language["gen38"] = __('35th Great-Grandparents');
$language["gen39"] = __('36th Great-Grandparents');
$language["gen40"] = __('37th Great-Grandparents');
$language["gen41"] = __('38th Great-Grandparents');
$language["gen42"] = __('39th Great-Grandparents');
$language["gen43"] = __('40th Great-Grandparents');
$language["gen44"] = __('41th Great-Grandparents');
$language["gen45"] = __('42th Great-Grandparents');
$language["gen46"] = __('43th Great-Grandparents');
$language["gen47"] = __('44th Great-Grandparents');
$language["gen48"] = __('45th Great-Grandparents');
$language["gen49"] = __('46th Great-Grandparents');
$language["gen50"] = __('47th Great-Grandparents');

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

    //echo 'pdf generation<br>';
    $pdf->Cell(0, 2, "", 0, 1);
    $pdf->SetFont($pdf_font, 'BI', 14);
    $pdf->SetFillColor(200, 220, 255);
    if ($pdf->GetY() > 260) {
        $pdf->AddPage();
        $pdf->SetY(20);
    }
    if (isset($language["gen" . $generation]) && $language["gen" . $generation]) {
        $pdf->Cell(0, 8, pdf_convert(__('generation ') . $rom_nr[$generation] . ' (' . $language["gen" . $generation] . ')'), 0, 1, 'C', true);
    } elseif (isset($rom_nr[$generation])) {
        $pdf->Cell(0, 8, pdf_convert(__('generation ') . $rom_nr[$generation]), 0, 1, 'C', true);
    }
    $pdf->SetFont($pdf_font, '', 12);
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
        if ($listednr == '') {  //if not listed yet, add person to array
            $listed_array[$ancestor_number[$i]] = $ancestor_array[$i];
        }

        if ($ancestor_array[$i] != '0') {
            @$person_manDb = $db_functions->get_person($ancestor_array[$i]);
            $man_cls = new Person_cls($person_manDb);
            $privacy_man = $man_cls->privacy;

            if (strtolower($person_manDb->pers_sexe) === 'm' && $ancestor_number[$i] > 1) {
                @$familyDb = $db_functions->get_family($marriage_gedcomnumber[$i]);

                // *** Use privacy filter of woman ***
                @$person_womanDb = $db_functions->get_person($familyDb->fam_woman);
                $woman_cls = new Person_cls($person_womanDb);
                $privacy_woman = $woman_cls->privacy;

                // *** Use class for marriage ***
                $marriage_cls = new Marriage_cls($familyDb, $privacy_man, $privacy_woman);
                $family_privacy = $marriage_cls->privacy;
            }

            unset($templ_person);

            // pdf NUMBER + MAN NAME + DATA
            // *** May 2021: changed, only number is shown **
            //$pdf->pdf_ancestor_name($ancestor_number[$i],$person_manDb->pers_sexe,$man_cls->name_extended("child"));
            $pdf->SetX(10);
            $pdf->pdf_ancestor_name($ancestor_number[$i], $person_manDb->pers_sexe, '');

            //$pdf->SetX($pdf->GetX()+3);
            //$pdf->MultiCell(0,8,$man_cls->name_extended("child"),0,"L");
            //$pdf->SetFont($pdf_font,'',12);

            //TEST WERKT (fout bij hogere nummers)
            //$pdf->SetLeftMargin(38);
            //$pdf->SetX($pdf->GetX()+3);
            //$man_cls->name_extended("child");
            //$pdf->Ln(7);

            // *** Name ***
            unset($templ_person);
            unset($templ_name);
            $pdfdetails = $man_cls->name_extended("child");
            if ($pdfdetails) {
                //$pdf->write_name($pdfdetails,$pdf->GetX()+5,"long");
                $pdf->write_name($templ_name, $pdf->GetX() + 5, "long");
                // *** Resets line ***
                //$pdf->MultiCell(0,8,'',0,"L");
            }

            if ($listednr == '') {
                $pdfdetails = $man_cls->person_data("standard", $ancestor_array[$i]);
                if ($pdfdetails) {
                    $pdf->SetLeftMargin(38);
                    $pdf->pdfdisplay($pdfdetails, "ancestor");
                    $pdf->SetLeftMargin(10);
                } elseif ($ancestor_number[$i] > 9999) {
                    $pdf->Ln(8); // (number) was placed under previous number
                    //  and there's no data so we have to move 1 line down for next person 
                }
            } else { // person was already listed
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
                @$family_parentsDb = $db_functions->get_family($person_manDb->pers_famc);
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
            @$person_manDb = $db_functions->get_person($ancestor_array[$i]);
            $man_cls = new Person_cls($person_manDb);
            $privacy_man = $man_cls->privacy;

            unset($templ_person);

            // pdf NUMBER + NAME + DATA  NN PERSON
            // *** May 2021: changed, only number is shown **
            //$pdf->pdf_ancestor_name($ancestor_number[$i],'',__('N.N.'));
            $pdf->SetX(10);
            $pdf->pdf_ancestor_name($ancestor_number[$i], '', '');
            //$pdf->SetX($pdf->GetX()+3);
            //$pdf->MultiCell(0,8,__('N.N.'),0,"L");
            //$pdf->SetFont($pdf_font,'',12);

            $pdf->SetLeftMargin(38);
            $pdf->SetX($pdf->GetX() + 3);
            $man_cls->name_extended("child");
            $pdf->Ln(7);

            $pdfdetails = $man_cls->person_data("standard", $ancestor_array[$i]);
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
}    // loop ancestor report


// Code for ancestor report PDF -- list appendix of sources
if ($screen_mode == "PDF" and !empty($pdf_source) and ($data["source_presentation"] == 'footnote' or $user['group_sources'] == 'j')) {
    include_once(__DIR__ . "/../include/show_source_pdf.php");
    $pdf->AddPage(); // appendix on new page
    $pdf->SetFont($pdf_font, "B", 14);
    $pdf->Write(8, __('Sources') . "\n\n");
    $pdf->SetFont($pdf_font, '', 10);
    // the $pdf_source array is set in show_sources.php with sourcenr as key and value if a linked source is given
    $count = 0;

    foreach ($pdf_source as $key => $value) {
        $count++;
        if (isset($pdf_source[$key])) {
            $pdf->SetLink($pdf_footnotes[$count - 1], -1);
            $pdf->SetFont($pdf_font, 'B', 10);
            $pdf->Write(6, $count . ". ");
            if ($user['group_sources'] == 'j') {
                source_display_pdf($pdf_source[$key]);  // function source_display from source.php, called with source nr.
            } elseif ($user['group_sources'] == 't') {
                $db_functions->get_source($pdf_source[$key]);
                if ($sourceDb->source_title) {
                    $pdf->SetFont($pdf_font, 'B', 10);
                    $pdf->Write(6, __('Title:') . " ");
                    $pdf->SetFont($pdf_font, '', 10);
                    $txt = ' ' . trim($sourceDb->source_title);
                    if ($sourceDb->source_date || $sourceDb->source_place) {
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

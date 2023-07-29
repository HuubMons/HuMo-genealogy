<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011: translated all variables to english by: Huub Mons.
 */

@set_time_limit(3000);

//==========================
global $humo_option, $user, $marr_date_array, $marr_place_array;
global $gedcomnumber, $language;
global $screen_mode, $dirmark1, $dirmark2, $pdf_footnotes;

$screen_mode = '';
if (isset($_POST["screen_mode"]) and $_POST["screen_mode"] == 'PDF') {
    $screen_mode = 'PDF';
}
if (isset($_POST["screen_mode"]) and $_POST["screen_mode"] == 'RTF') {
    $screen_mode = 'RTF';
}

$pdf_source = array();  // is set in show_sources.php with sourcenr as key to be used in source appendix

include_once("header.php"); // returns CMS_ROOTPATH constant



// TODO create seperate controller script.
// TEMPORARY CONTROLLER HERE:
require_once  __DIR__ . "/models/ancestor.php";
$get_ancestor = new Ancestor($dbh);
//$family_id = $get_family->getFamilyId();
//$main_person = $get_family->getMainPerson();
//$family_expanded =  $get_family->getFamilyExpanded();
//$source_presentation =  $get_family->getSourcePresentation();
//$picture_presentation =  $get_family->getPicturePresentation();
//$text_presentation =  $get_family->getTextPresentation();
$rom_nr = $get_ancestor->getNumberRoman();
//$number_generation = $get_family->getNumberGeneration();
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



if ($screen_mode != 'PDF') {  //we can't have a menu in pdf...
    include_once(CMS_ROOTPATH . "menu.php");
} else {
    include_once(CMS_ROOTPATH . "include/db_functions_cls.php");
    $db_functions = new db_functions;

    if (isset($_SESSION['tree_prefix'])) {
        $dataqry = "SELECT * FROM humo_trees LEFT JOIN humo_tree_texts
            ON humo_trees.tree_id=humo_tree_texts.treetext_tree_id
            AND humo_tree_texts.treetext_language='" . $selected_language . "'
            WHERE tree_prefix='" . $tree_prefix_quoted . "'";
        @$datasql = $dbh->query($dataqry);
        @$dataDb = @$datasql->fetch(PDO::FETCH_OBJ);
    }

    $tree_prefix = $dataDb->tree_prefix;
    $tree_id = $dataDb->tree_id;
    $db_functions->set_tree_id($dataDb->tree_id);
}

// *** TODO CHECK: $family_id is actually a person_id... ***
$family_id = 'I1'; // *** Default value, normally not used... ***
if (isset($_GET["id"])) {
    $family_id = $_GET["id"];
}
if (isset($_POST["id"])) {
    $family_id = $_POST["id"];
}

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($family_id);

// *** Source presentation selected by user (title/ footnote or hide sources) ***
// *** Default setting is selected by administrator ***
if (isset($_GET['source_presentation'])) {
    $_SESSION['save_source_presentation'] = safe_text_db($_GET["source_presentation"]);
}
$source_presentation = $user['group_source_presentation'];
if (isset($_SESSION['save_source_presentation'])) {
    $source_presentation = $_SESSION['save_source_presentation'];
} else {
    // *** Save setting in session (if no choice is made, this is admin default setting) ***
    $_SESSION['save_source_presentation'] = safe_text_db($source_presentation);
}

if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
    echo '<div class="standard_header fonts">';
    if ($screen_mode == 'ancestor_chart') {
        echo __('Ancestor chart');
    } else {
        echo __('Ancestor report');

        //if($user["group_pdf_button"]=='y' AND $language["dir"]!="rtl") {
        if ($user["group_pdf_button"] == 'y' and $language["dir"] != "rtl" and $language["name"] != "简体中文") {
            // Show pdf button
            echo ' <form method="POST" action="' . $uri_path . 'report_ancestor.php?show_sources=1" style="display : inline;">';
            echo '<input type="hidden" name="id" value="' . $family_id . '">';
            echo '<input type="hidden" name="database" value="' . $_SESSION['tree_prefix'] . '">';
            echo '<input type="hidden" name="screen_mode" value="PDF">';

            // *** needed to check PDF M/F/? icons ***
            echo '<input type="hidden" name="ancestor_report" value="1">';

            echo '<input class="fonts" type="Submit" name="submit" value="' . __('PDF Report') . '">';
            echo '</form>';
        }

        if ($user["group_rtf_button"] == 'y' and $language["dir"] != "rtl") {
            // Show rtf button
            echo ' <form method="POST" action="' . $uri_path . 'report_ancestor.php?show_sources=1" style="display : inline;">';
            echo '<input type="hidden" name="id" value="' . $family_id . '">';
            echo '<input type="hidden" name="database" value="' . $_SESSION['tree_prefix'] . '">';
            echo '<input type="hidden" name="screen_mode" value="RTF">';

            // *** needed to check RTF M/F/? icons ***
            echo '<input type="hidden" name="ancestor_report" value="1">';

            echo '<input class="fonts" type="Submit" name="submit" value="' . __('RTF Report') . '">';
            echo '</form>';
        }
    }
    echo '</div>';
}

if ($screen_mode == 'PDF') {
    //initialize pdf generation
    $pdfdetails = array();
    $pdf_marriage = array();

    $pdf = new PDF();
    @$persDb = $db_functions->get_person($family_id);
    // *** Use person class ***
    $pers_cls = new person_cls($persDb);
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
}
if ($screen_mode == 'RTF') {  // initialize rtf generation
    require_once 'include/phprtflite/lib/PHPRtfLite.php';

    // *** registers PHPRtfLite autoloader (spl) ***
    PHPRtfLite::registerAutoloader();
    // *** rtf document instance ***
    $rtf = new PHPRtfLite();

    // *** Add section ***
    $sect = $rtf->addSection();

    // *** RTF Settings ***
    $arial10 = new PHPRtfLite_Font(10, 'Arial');
    $arial12 = new PHPRtfLite_Font(12, 'Arial');
    $arial14 = new PHPRtfLite_Font(14, 'Arial', '#000066');
    //Fonts
    $fontHead = new PHPRtfLite_Font(12, 'Arial');
    $fontSmall = new PHPRtfLite_Font(3);
    $fontAnimated = new PHPRtfLite_Font(10);
    $fontLink = new PHPRtfLite_Font(10, 'Helvetica', '#0000cc');

    $parNames = new PHPRtfLite_ParFormat();
    $parNames->setBackgroundColor('#FFFFFF');
    $parNames->setIndentLeft(0);
    $parNames->setSpaceBefore(0);
    $parNames->setSpaceAfter(0);

    $parHead = new PHPRtfLite_ParFormat(PHPRtfLite_ParFormat::TEXT_ALIGN_CENTER);
    $parHead->setSpaceBefore(3);
    $parHead->setSpaceAfter(8);
    $parHead->setBackgroundColor('#baf4c1');

    $parGen = new PHPRtfLite_ParFormat(PHPRtfLite_ParFormat::TEXT_ALIGN_CENTER);
    $parGen->setSpaceBefore(0);
    $parGen->setSpaceAfter(8);
    $parGen->setBackgroundColor('#baf4c1');

    $parSimple = new PHPRtfLite_ParFormat();
    $parSimple->setIndentLeft(2.5);
    $parSimple->setIndentRight(0.5);

    // *** Generate title of RTF file ***
    @$persDb = $db_functions->get_person($family_id);
    // *** Use person class ***
    $pers_cls = new person_cls($persDb);
    $name = $pers_cls->person_name($persDb);
    $title = __('Ancestor report') . __(' of ') . $name["standard_name"];

    //$sect->writeText($title, $arial14, new PHPRtfLite_ParFormat());
    $sect->writeText($title, $arial14, $parHead);

    $file_name = date("Y_m_d_H_i_s") . '.rtf';
    // *** FOR TESTING PURPOSES ONLY ***
    if (@file_exists("../gedcom-bestanden")) $file_name = '../gedcom-bestanden/' . $file_name;
    else $file_name = 'tmp_files/' . $file_name;

    // *** Automatically remove old RTF files ***
    $dh  = opendir('tmp_files');
    while (false !== ($filename = readdir($dh))) {
        if (substr($filename, -3) == "rtf") {
            // *** Remove files older then today ***
            if (substr($filename, 0, 10) != date("Y_m_d")) unlink('tmp_files/' . $filename);
        }
    }
}

$ancestor_array2[] = $family_id;
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

if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
    echo '<table style="border-style:none" align="center"><tr><td></td></tr>';
}

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

    if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
        echo '</table>';

        if (isset($rom_nr[$generation]))
            echo '<div class="standard_header fonts">' . __('generation ') . $rom_nr[$generation];
        if (isset($language["gen" . $generation]) and $language["gen" . $generation]) {
            echo ' (' . $language["gen" . $generation] . ')';
        }
        echo '</div><br>';

        echo '<table class="humo standard" align="center">';
    } elseif ($screen_mode == "RTF") {
        $rtf_text = __('generation ') . $rom_nr[$generation];
        $sect->writeText($rtf_text, $arial14, $parGen);
    } else {
        //echo 'pdf generation<br>';
        $pdf->Cell(0, 2, "", 0, 1);
        $pdf->SetFont($pdf_font, 'BI', 14);
        $pdf->SetFillColor(200, 220, 255);
        if ($pdf->GetY() > 260) {
            $pdf->AddPage();
            $pdf->SetY(20);
        }
        if (isset($language["gen" . $generation]) and $language["gen" . $generation]) {
            $pdf->Cell(0, 8, pdf_convert(__('generation ') . $rom_nr[$generation] . ' (' . $language["gen" . $generation] . ')'), 0, 1, 'C', true);
        } else {
            if (isset($rom_nr[$generation]))
                $pdf->Cell(0, 8, pdf_convert(__('generation ') . $rom_nr[$generation]), 0, 1, 'C', true);
        }
        $pdf->SetFont($pdf_font, '', 12);
    }

    // *** Loop per generation ***
    for ($i = 0; $i < count($ancestor_array); $i++) {

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
            $man_cls = new person_cls($person_manDb);
            $privacy_man = $man_cls->privacy;

            if (strtolower($person_manDb->pers_sexe) == 'm' and $ancestor_number[$i] > 1) {
                @$familyDb = $db_functions->get_family($marriage_gedcomnumber[$i]);

                // *** Use privacy filter of woman ***
                @$person_womanDb = $db_functions->get_person($familyDb->fam_woman);
                $woman_cls = new person_cls($person_womanDb);
                $privacy_woman = $woman_cls->privacy;

                // *** Use class for marriage ***
                $marriage_cls = new marriage_cls($familyDb, $privacy_man, $privacy_woman);
                $family_privacy = $marriage_cls->privacy;
            }
            if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
                echo '<tr><td valign="top" width="80" nowrap><b>' . $ancestor_number[$i] .
                    '</b> (' . floor($ancestor_number[$i] / 2) . ')</td>';

                echo '<td>';
                //*** Show data man ***
                echo '<div class="parent1">';
                // ***  Use "child", to show a link for own family. ***
                echo $man_cls->name_extended("child");
                if ($listednr == '') {
                    echo $man_cls->person_data("standard", $ancestor_array[$i]);
                } else { // person was already listed
                    echo ' <strong> (' . __('Already listed above as number ') . $listednr . ') </strong>';
                }
                echo '</div>';
                echo '</td></tr>';
            } elseif ($screen_mode == "RTF") {
                $sect->writeText('', $arial12, new PHPRtfLite_ParFormat());
                $table = $sect->addTable();
                $table->addRow(1);
                $table->addColumnsList(array(2, 0.5, 14));

                $rtf_text = $ancestor_number[$i] . "(" . floor($ancestor_number[$i] / 2) . ")";
                $cell = $table->getCell(1, 1);
                $cell->writeText($rtf_text, $arial10, $parNames);

                $rtf_text = strip_tags($man_cls->name_extended("child"), "<b><i>");
                $cell = $table->getCell(1, 2);

                if ($person_manDb->pers_sexe == "M")
                    $cell->addImage('images/man.jpg', null);
                elseif ($person_manDb->pers_sexe == "F")
                    $cell->addImage(CMS_ROOTPATH . 'images/woman.jpg', null);
                else
                    $cell->addImage(CMS_ROOTPATH . 'images/unknown.jpg', null);

                $cell = $table->getCell(1, 3);
                $cell->writeText($rtf_text, $arial12, $parNames);
                if ($listednr == '') {
                    $rtf_text = strip_tags($man_cls->person_data("standard", $ancestor_array[$i]), "<b><i>");
                    $rtf_text = substr($rtf_text, 0, -1); // take off newline
                } else { // person was already listed
                    $rtf_text = strip_tags('(' . __('Already listed above as number ') . $listednr . ') ', "<b><i>");
                }
                $cell->writeText($rtf_text, $arial12, $parNames);

                $result = show_media('person', $person_manDb->pers_gedcomnumber);
                if (isset($result[1]) and count($result[1]) > 0) {
                    $break = 1;
                    $textarr = array();
                    $goodpics = FALSE;
                    foreach ($result[1] as $key => $value) {
                        if (strpos($key, "path") !== FALSE) {
                            $type = substr($result[1][$key], -3);
                            if ($type == "jpg" or $type == "png") {
                                if ($goodpics == FALSE) { //found 1st pic - make table
                                    $table2 = $sect->addTable();
                                    $table2->addRow(0.1);
                                    $table2->addColumnsList(array(2.5, 5, 5));
                                    $goodpics = TRUE;
                                }
                                $break++;
                                $cell = $table2->getCell(1, $break);
                                $imageFile = $value;
                                $image = $cell->addImage($imageFile);
                                $txtkey = str_replace("pic_path", "pic_text", $key);
                                if (isset($result[1][$txtkey])) {
                                    $textarr[] = $result[1][$txtkey];
                                } else {
                                    $textarr[] = "&nbsp;";
                                }
                            }
                        }
                        if ($break == 3) break; // max 2 pics
                    }
                    $break1 = 1;
                    if (count($textarr) > 0) {
                        $table2->addRow(0.1); //add row only if there is photo text
                        foreach ($textarr as $value) {
                            $break1++;
                            $cell = $table2->getCell(2, $break1);
                            $cell->writeText($value);
                        }
                    }
                }
            } else {
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

                $temp = 0;
                $temp = floor($ancestor_number[$i] % 2);
                if ($ancestor_number[$i] > 1 and $temp == 1 and $i + 1 < count($ancestor_array)) {
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
            }

            // Show own marriage (new line, after man)
            if (strtolower($person_manDb->pers_sexe) == 'm' and $ancestor_number[$i] > 1) {
                if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
                    echo '<tr><td>&nbsp;</td><td>';
                    echo '<span class="marriage">';
                }
                if ($family_privacy) {
                    if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
                        echo __(' to: ');
                    } elseif ($screen_mode == "RTF") {
                        $rtf_text = __(' to: ');
                        $sect->writeText($rtf_text, $arial12, $parSimple);
                    } else {
                        $pdf->SetX(37);
                        $pdf->Write(6, __(' to: ') . "\n");
                    }

                    // If privacy filter is activated, show divorce
                    if ($familyDb->fam_div_date or $familyDb->fam_div_place) {
                        if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
                            echo ' <span class="divorse">(' . trim(__('divorced ')) . ')</span>';
                        } elseif ($screen_mode == "RTF") {
                            $rtf_text = trim(__('divorced '));
                            $sect->writeText($rtf_text, $arial12, $parSimple);
                        } else {
                            $pdf->Write(6, ' (' . trim(__('divorced ')) . ')');
                        }
                    }
                    // Show end of relation here?
                    //if ($familyDb->fam_relation_end_date){
                    //  echo ' <span class="divorse">('.trim(__('divorced ')).')</span>';
                    //}
                } else {
                    // To calculate age by marriage.
                    $parent1Db = $person_manDb;
                    $parent2Db = $person_womanDb;
                    if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
                        echo $marriage_cls->marriage_data();
                    } elseif ($screen_mode == "RTF") {
                        $rtf_text = strip_tags($marriage_cls->marriage_data(), "<b><i>");
                        $sect->writeText($rtf_text, $arial12, $parSimple);
                    } else {
                        //show pdf MARRIAGE DATA
                        $pdf_marriage = $marriage_cls->marriage_data();
                        if ($pdf_marriage) {
                            $pdf->displayrel($pdf_marriage, "ancestor");
                        }
                    }
                }
                if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
                    echo '</span>';
                    echo '</td></tr>';
                }
            }

            // ==	Check for parents
            if ($person_manDb->pers_famc  and $listednr == '') {
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
            $man_cls = new person_cls($person_manDb);
            $privacy_man = $man_cls->privacy;

            if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
                echo '<tr><td valign="top" width="80" nowrap><b>' . $ancestor_number[$i] .
                    '</b> (' . floor($ancestor_number[$i] / 2) . ')</td>';

                echo '<td>';
                //*** Show person_data of man ***
                echo '<div class="parent1">';
                // ***  Use "child", to show a link to own family. ***
                echo $man_cls->name_extended("child");
                echo $man_cls->person_data("standard", $ancestor_array[$i]);
                echo '</div>';
                echo '</td></tr>';
            } elseif ($screen_mode == "RTF") {
                $sect->writeText('', $arial12, new PHPRtfLite_ParFormat());
                $table = $sect->addTable();
                $table->addRow(1);
                $table->addColumnsList(array(2, 0.5, 14));

                $rtf_text = $ancestor_number[$i] . "(" . floor($ancestor_number[$i] / 2) . ")";
                $cell = $table->getCell(1, 1);
                $cell->writeText($rtf_text, $arial10, $parNames);
                $cell = $table->getCell(1, 2);

                if ($person_manDb and $person_manDb->pers_sexe == "M")
                    $cell->addImage('images/man.jpg', null);
                elseif ($person_manDb and $person_manDb->pers_sexe == "F")
                    $cell->addImage(CMS_ROOTPATH . 'images/woman.jpg', null);
                else
                    $cell->addImage(CMS_ROOTPATH . 'images/unknown.jpg', null);

                $rtf_text = strip_tags($man_cls->name_extended("child"), "<b><i>");
                $cell = $table->getCell(1, 3);
                $cell->writeText($rtf_text, $arial12, $parNames);
                $rtf_text = strip_tags($man_cls->person_data("standard", $ancestor_array[$i]), "<b><i>");
                $rtf_text = substr($rtf_text, 0, -1); // take off newline
                $cell->writeText($rtf_text, $arial12, $parNames);
            } else {
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
                if ($ancestor_number[$i] > 1 and $temp == 1 and $i + 1 < count($ancestor_array)) {
                    // if we're not in first generation (one person)
                    // and we are after writing the woman's details
                    // and there is at least one person of another family to come in this generation
                    // then place a divider line between the families in this generation
                    $pdf->Cell(0, 1, "", 'B', 1);
                    $pdf->Ln(1);
                }
            }
        }
    }    // loop per generation
    $generation++;
}    // loop ancestor report


// Code for ancestor report PDF -- list appendix of sources
if ($screen_mode == "PDF" and !empty($pdf_source) and ($source_presentation == 'footnote' or $user['group_sources'] == 'j')) {
    include_once(CMS_ROOTPATH . "source.php");
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
                source_display($pdf_source[$key]);  // function source_display from source.php, called with source nr.
            } elseif ($user['group_sources'] == 't') {
                $db_functions->get_source($pdf_source[$key]);
                if ($sourceDb->source_title) {
                    $pdf->SetFont($pdf_font, 'B', 10);
                    $pdf->Write(6, __('Title:') . " ");
                    $pdf->SetFont($pdf_font, '', 10);
                    $txt = ' ' . trim($sourceDb->source_title);
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

// Finishing code for ancestor report
if ($screen_mode == '') {
    echo '</table>';
    // *** If source footnotes are selected, show them here ***
    if (isset($_SESSION['save_source_presentation']) and $_SESSION['save_source_presentation'] == 'footnote') {
        echo show_sources_footnotes();
    }
}

// Finishing code for ancestor chart and ancestor report
if ($screen_mode != 'PDF' and $screen_mode != 'RTF') {
    include_once(CMS_ROOTPATH . "footer.php");
} elseif ($screen_mode == 'RTF') { // initialize rtf generation
    // *** Save rtf document to file ***
    $rtf->save($file_name);

    echo '<br><br><a href="' . $file_name . '">' . __('Download RTF report.') . '</a>';
    echo '<br><br>' . __('TIP: Don\'t use Wordpad to open this file (the lay-out will be wrong!). It\'s better to use a text processor like Word or OpenOffice Writer.');

    $text = '<br><br><form method="POST" action="' . $uri_path . 'report_ancestor.php?database=' . $_SESSION['tree_prefix'] . '&amp;id=' . $family_id . '" style="display : inline;">';

    echo '<input type="hidden" name="screen_mode" value="">';

    $text .= '<input class="fonts" type="Submit" name="submit" value="' . __('Back') . '">';
    $text .= '</form> ';
    echo $text;
}

// Finishing code for ancestor report PDF and ancestor sheet PDF
else {
    $pdf->Output($title . ".pdf", "I");
}

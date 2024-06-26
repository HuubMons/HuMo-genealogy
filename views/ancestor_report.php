<?php

/**
 * First test scipt made by: Klaas de Winkel
 * Graphical script made by: Theo Huitema
 * Graphical part: better lay-out (colours) and pictures made by: Rene Janssen
 * Graphical part: improved lay-out by: Huub Mons.
 * Ancestor sheet, PDF export for ancestor report and ancestor sheet, image generation for chart made by: Yossi Beck.
 * July 2011: translated all variables to english by: Huub Mons.
 */

//TODO use seperate file for RTF?
$screen_mode = '';
if (isset($_POST["screen_mode"]) && $_POST["screen_mode"] == 'RTF') {
    $screen_mode = 'RTF';
}

//$pdf_source = array();  // is set in show_sources.php with sourcenr as key to be used in source appendix

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

if ($screen_mode !== 'RTF') {
    //echo '<h1 class="standard_header">'.__('Ancestor report').'</h1>';
    echo $data["ancestor_header"];
}

if ($screen_mode === 'RTF') {  // initialize rtf generation
    require_once __DIR__ . '/../include/phprtflite/lib/PHPRtfLite.php';

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
    @$persDb = $db_functions->get_person($data["main_person"]);
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
        // *** Remove files older then today ***
        if (substr($filename, -3) == "rtf" && substr($filename, 0, 10) !== date("Y_m_d")) {
            unlink('tmp_files/' . $filename);
        }
    }
}

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

if ($screen_mode !== 'RTF') {
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

    if ($screen_mode !== 'RTF') {
        echo '</table>';

        if (isset($data["rom_nr"][$generation])) {
            echo '<h2 class="standard_header">' . __('generation ') . $data["rom_nr"][$generation];
        }
        if (isset($language["gen" . $generation]) && $language["gen" . $generation]) {
            echo ' (' . $language["gen" . $generation] . ')';
        }

        if ($generation == 1) {
            if ($user["group_pdf_button"] == 'y' && $language["dir"] != "rtl" && $language["name"] != "简体中文") {
                // Show pdf button

                //$vars['id'] = $data["main_person"];
                //$link = $link_cls->get_link($uri_path, 'ancestor_report', $tree_id, true, $vars);
                //$link .= 'screen_mode=ancestor_chart&amp;show_sources=1';

                $link = $uri_path . 'views/ancestor_report_pdf.php';
?>
                <form method="POST" action="<?= $link; ?>" style="display : inline;">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <input type="hidden" name="id" value="<?= $data["main_person"]; ?>">
                    <input type="hidden" name="database" value="<?= $_SESSION['tree_prefix']; ?>">
                    <input type="hidden" name="screen_mode" value="PDF">

                    <!-- needed to check PDF M/F/? icons -->
                    <input type="hidden" name="ancestor_report" value="1">

                    <input type="submit" class="btn btn-sm btn-info" value="<?= __('PDF'); ?>" name="submit">
                </form>

            <?php
            }

            if ($user["group_rtf_button"] == 'y' && $language["dir"] != "rtl") {
                // Show rtf button
                $vars['id'] = $data["main_person"];
                $link = $link_cls->get_link($uri_path, 'ancestor_report', $tree_id, true, $vars);
                $link .= 'show_sources=1';
            ?>
                <form method="POST" action="<?= $link; ?>" style="display : inline;">
                    <input type="hidden" name="screen_mode" value="RTF">

                    <!-- needed to check RTF M/F/? icons -->
                    <input type="hidden" name="ancestor_report" value="1">

                    <input type="submit" class="btn btn-sm btn-info" value="<?= __('RTF'); ?>" name="submit">
                </form>
<?php
            }
        }

        echo '</h2><br>';

        echo '<table class="humo standard" align="center">';
    } elseif ($screen_mode === "RTF") {
        $rtf_text = __('generation ') . $data["rom_nr"][$generation];
        $sect->writeText($rtf_text, $arial14, $parGen);
    }
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
            $man_cls = new person_cls($person_manDb);
            $privacy_man = $man_cls->privacy;

            if (strtolower($person_manDb->pers_sexe) === 'm' && $ancestor_number[$i] > 1) {
                @$familyDb = $db_functions->get_family($marriage_gedcomnumber[$i]);

                // *** Use privacy filter of woman ***
                @$person_womanDb = $db_functions->get_person($familyDb->fam_woman);
                $woman_cls = new person_cls($person_womanDb);
                $privacy_woman = $woman_cls->privacy;

                // *** Use class for marriage ***
                $marriage_cls = new marriage_cls($familyDb, $privacy_man, $privacy_woman);
                $family_privacy = $marriage_cls->privacy;
            }
            if ($screen_mode !== 'RTF') {
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
            } elseif ($screen_mode === "RTF") {
                $sect->writeText('', $arial12, new PHPRtfLite_ParFormat());
                $table = $sect->addTable();
                $table->addRow(1);
                $table->addColumnsList(array(2, 0.5, 14));

                $rtf_text = $ancestor_number[$i] . "(" . floor($ancestor_number[$i] / 2) . ")";
                $cell = $table->getCell(1, 1);
                $cell->writeText($rtf_text, $arial10, $parNames);

                $rtf_text = strip_tags($man_cls->name_extended("child"), "<b><i>");
                $cell = $table->getCell(1, 2);

                if ($person_manDb->pers_sexe == "M") {
                    $cell->addImage('images/man.jpg', null);
                } elseif ($person_manDb->pers_sexe == "F") {
                    $cell->addImage('images/woman.jpg', null);
                } else {
                    $cell->addImage('images/unknown.jpg', null);
                }

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
                if (isset($result[1]) && count($result[1]) > 0) {
                    $break = 1;
                    $textarr = array();
                    $goodpics = FALSE;
                    foreach ($result[1] as $key => $value) {
                        if (strpos($key, "path") !== FALSE) {
                            $type = substr($result[1][$key], -3);
                            if ($type === "jpg" || $type === "png") {
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
                        if ($break == 3) {
                            break;
                        } // max 2 pics
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
            }

            // Show own marriage (new line, after man)
            if (strtolower($person_manDb->pers_sexe) === 'm' && $ancestor_number[$i] > 1) {
                if ($screen_mode !== 'RTF') {
                    echo '<tr><td>&nbsp;</td><td>';
                    echo '<span class="marriage">';
                }
                if ($family_privacy) {
                    if ($screen_mode !== 'RTF') {
                        echo __(' to: ');
                    } elseif ($screen_mode === "RTF") {
                        $rtf_text = __(' to: ');
                        $sect->writeText($rtf_text, $arial12, $parSimple);
                    }

                    // If privacy filter is activated, show divorce
                    if ($familyDb->fam_div_date || $familyDb->fam_div_place) {
                        if ($screen_mode !== 'RTF') {
                            echo ' <span class="divorse">(' . trim(__('divorced ')) . ')</span>';
                        } elseif ($screen_mode === "RTF") {
                            $rtf_text = trim(__('divorced '));
                            $sect->writeText($rtf_text, $arial12, $parSimple);
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
                    if ($screen_mode !== 'RTF') {
                        echo $marriage_cls->marriage_data();
                    } elseif ($screen_mode === "RTF") {
                        $rtf_text = strip_tags($marriage_cls->marriage_data(), "<b><i>");
                        $sect->writeText($rtf_text, $arial12, $parSimple);
                    }
                }
                if ($screen_mode !== 'RTF') {
                    echo '</span>';
                    echo '</td></tr>';
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
            $man_cls = new person_cls($person_manDb);
            $privacy_man = $man_cls->privacy;

            if ($screen_mode !== 'RTF') {
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
            } elseif ($screen_mode === "RTF") {
                $sect->writeText('', $arial12, new PHPRtfLite_ParFormat());
                $table = $sect->addTable();
                $table->addRow(1);
                $table->addColumnsList(array(2, 0.5, 14));

                $rtf_text = $ancestor_number[$i] . "(" . floor($ancestor_number[$i] / 2) . ")";
                $cell = $table->getCell(1, 1);
                $cell->writeText($rtf_text, $arial10, $parNames);
                $cell = $table->getCell(1, 2);

                if ($person_manDb && $person_manDb->pers_sexe == "M") {
                    $cell->addImage('images/man.jpg', null);
                } elseif ($person_manDb && $person_manDb->pers_sexe == "F") {
                    $cell->addImage('images/woman.jpg', null);
                } else {
                    $cell->addImage('images/unknown.jpg', null);
                }

                $rtf_text = strip_tags($man_cls->name_extended("child"), "<b><i>");
                $cell = $table->getCell(1, 3);
                $cell->writeText($rtf_text, $arial12, $parNames);
                if ($man_cls->person_data("standard", $ancestor_array[$i])) {
                    $rtf_text = strip_tags($man_cls->person_data("standard", $ancestor_array[$i]), "<b><i>");
                    $rtf_text = substr($rtf_text, 0, -1); // take off newline
                }
                $cell->writeText($rtf_text, $arial12, $parNames);
            }
        }
    }    // loop per generation
    $generation++;
}    // loop ancestor report

// Finishing code for ancestor report
if ($screen_mode === '') {
    echo '</table>';
    // *** If source footnotes are selected, show them here ***
    if (isset($_SESSION['save_source_presentation']) && $_SESSION['save_source_presentation'] == 'footnote') {
        echo show_sources_footnotes();
    }
}

// Finishing code for ancestor chart and ancestor report
if ($screen_mode !== 'RTF') {
    echo '<br><br>';
} elseif ($screen_mode === 'RTF') { // initialize rtf generation
    // *** Save rtf document to file ***
    $rtf->save($file_name);

    echo '<br><br><a href="' . $file_name . '">' . __('Download RTF report.') . '</a>';
    echo '<br><br>' . __('TIP: Don\'t use Wordpad to open this file (the lay-out will be wrong!). It\'s better to use a text processor like Word or OpenOffice Writer.');

    $vars['id'] = $data["main_person"];
    $link = $link_cls->get_link($uri_path, 'ancestor_report', $tree_id, false, $vars);

    $text = '<br><br><form method="POST" action="' . $link . '" style="display : inline;">';
    // TODO check code.
    echo '<input type="hidden" name="screen_mode" value="">';
    $text .= '<input type="Submit" name="submit" value="' . __('Back') . '">';
    $text .= '</form> ';
    echo $text;
}

<?php

/**
 * Family/ relation page RTF export
 * 
 * July 2023: this script will be refactored. Under construction.
 * 
 */

$screen_mode = 'RTF';



// TODO create seperate controller script.
$get_family = new \Genealogy\App\Model\FamilyModel($config);
$data["family_id"] = $get_family->getFamilyId();
$data["main_person"] = $get_family->getMainPerson();
$data["family_expanded"] = 'compact';
$data["source_presentation"] =  $get_family->getSourcePresentation();
$data["picture_presentation"] =  $get_family->getPicturePresentation();
$data["text_presentation"] =  $get_family->getTextPresentation();
$data["number_roman"] = $get_family->getNumberRoman();
$data["number_generation"] = $get_family->getNumberGeneration();



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

require_once __DIR__ . '/../include/phprtflite/lib/PHPRtfLite.php';
$data["family_expanded"] = 'compact';

// *** registers PHPRtfLite autoloader (spl) ***
PHPRtfLite::registerAutoloader();
// *** rtf document instance ***
$rtf = new PHPRtfLite();

// *** Add section ***
$sect = $rtf->addSection();

// *** RTF Settings ***
$arial12 = new PHPRtfLite_Font(12, 'Arial');
$arial14 = new PHPRtfLite_Font(14, 'Arial', '#000066');
//Fonts
$fontHead = new PHPRtfLite_Font(12, 'Arial');
$fontSmall = new PHPRtfLite_Font(3);
$fontAnimated = new PHPRtfLite_Font(10);
$fontLink = new PHPRtfLite_Font(10, 'Helvetica', '#0000cc');

$parBlack = new PHPRtfLite_ParFormat();
$parBlack->setIndentRight(12.5);
//$parBlack->setBackgroundColor('#000000');
$parBlack->setSpaceBefore(12);

$parHead = new PHPRtfLite_ParFormat();
$parHead->setSpaceBefore(3);
$parHead->setSpaceAfter(8);
$parHead->setBackgroundColor('#baf4c1');

$parSimple = new PHPRtfLite_ParFormat();
$parSimple->setIndentLeft(1);
$parSimple->setIndentRight(0.5);

$par_child_text = new PHPRtfLite_ParFormat();
$par_child_text->setIndentLeft(0.5);
$par_child_text->setIndentRight(0.5);

//$rtf->setMargins(3, 1, 1 ,2);

// *** Generate title of RTF file ***
$persDb = $db_functions->get_person($data["main_person"]);

$personPrivacy = new \Genealogy\Include\PersonPrivacy;
$personName = new \Genealogy\Include\PersonName;
$personName_extended = new \Genealogy\Include\PersonNameExtended;
$personData = new \Genealogy\Include\PersonData;
$processText = new \Genealogy\Include\ProcessText;
$showSources = new \Genealogy\Include\ShowSources;
$totallyFilterPerson = new \Genealogy\Include\TotallyFilterPerson;

$privacy = $personPrivacy->get_privacy($persDb);
$name = $personName->get_person_name($persDb, $privacy);

if (!$data["descendant_report"] == false) {
    $title = __('Descendant report') . __(' of ') . $name["standard_name"];
} else {
    $title = __('Family group sheet') . __(' of ') . $name["standard_name"];
}
//$sect->writeText($title, $arial14, new PHPRtfLite_ParFormat());
$sect->writeText($title, $arial14, $parHead);

$file_name = date("Y_m_d_H_i_s") . '.rtf';
// *** FOR TESTING PURPOSES ONLY ***
if (file_exists(__DIR__ . '/../../gedcom-bestanden')) {
    $download_link = '../../gedcom-bestanden/' . $file_name;
    $file_name = __DIR__ . '/../../gedcom-bestanden/' . $file_name;
} else {
    $download_link = 'tmp_files/' . $file_name;
    $file_name = __DIR__ . '/../tmp_files/' . $file_name;
}

// *** Automatically remove old RTF files ***
$dh  = opendir(__DIR__ . '/../tmp_files');
while (false !== ($filename = readdir($dh))) {
    // *** Remove files older then today ***
    if (substr($filename, -3) == "rtf" && substr($filename, 0, 10) !== date("Y_m_d")) {
        unlink(__DIR__ . '/../tmp_files/' . $filename);
    }
}


/**
 * Show single person
 */
if (!$data["family_id"]) {
    // starfieldchart is never called when there is no own fam so no need to mark this out
    // *** Privacy filter ***
    $parent1Db = $db_functions->get_person($data["main_person"]);
    $parent1_privacy = $personPrivacy->get_privacy($parent1Db);

    $rtf_text = strip_tags($personName_extended->name_extended($parent1Db, $parent1_privacy, "parent1"), "<b><i>");
    $sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
    $id = '';
    $rtf_text = strip_tags($personData->person_data($parent1Db, $parent1_privacy, "parent1", $id), "<b><i>");
    $sect->writeText($rtf_text, $arial12, $parSimple);
}

// *******************
// *** Show family ***
// *******************
else {
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
            $rtf_text = __('generation ') . $data["number_roman"][$descendant_loop + 1];
            $sect->writeText($rtf_text, $arial14, $parHead);
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

                $marriage_cls = new \Genealogy\Include\MarriageCls($familyDb, $parent1_privacy, $parent2_privacy);
                $family_privacy = $marriage_cls->get_privacy();


                /**
                 * Show family
                 */
                // *** Internal link for descendant_report ***
                if ($data["descendant_report"] == true) {
                    // *** Internal link (Roman number_generation) ***
                    //$rtf_text=$data["number_roman"][$descendant_loop+1].'-'.$data["number_generation"][$descendant_loop2+1].' ';
                    //$sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                }

                $sect->addEmptyParagraph($fontSmall, $parBlack);

                $treetext = $showTreeText->show_tree_text($selectedFamilyTree->tree_id, $selected_language);
                $rtf_text = $treetext['family_top'];
                if ($rtf_text != '') {
                    $sect->writeText($rtf_text, $arial14, $parHead);
                } else {
                    $sect->writeText(__('Family group sheet'), $arial14, $parHead);
                }

                /**
                 * Show parent1 (normally the father)
                 */
                if ($familyDb->fam_kind != 'PRO-GEN') {
                    //onecht kind, woman without man
                    if ($family_nr == 1) {
                        //*** Show data of parent1 ***
                        $rtf_text = ' <b>' . $data["number_roman"][$descendant_loop + 1] . '-' . $data["number_generation"][$descendant_loop2 + 1] . '</b> ';
                        $sect->writeText($rtf_text, $arial12);

                        // *** Start new line ***
                        $sect->writeText('', $arial12, new PHPRtfLite_ParFormat());

                        $rtf_text = strip_tags($personName_extended->name_extended($parent1Db, $parent1_privacy, "parent1"), "<b><i>");
                        $sect->writeText($rtf_text, $arial12);
                        $id = '';
                        $rtf_text = strip_tags($personData->person_data($parent1Db, $parent1_privacy, "parent1", $id), "<b><i>");
                        $sect->writeText($rtf_text, $arial12, $parSimple);

                        // *** Show RTF media ***
                        if (!$parent1_privacy) {
                            show_rtf_media('person', $parent1Db->pers_gedcomnumber);
                        }
                    } else {
                        // *** Show standard marriage text and name in 2nd, 3rd, etc. marriage ***
                        $rtf_text = strip_tags($marriage_cls->marriage_data($familyDb, $family_nr, 'shorter'), "<b><i>");
                        $sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());

                        // *** Start new line ***
                        $sect->writeText('', $arial12, new PHPRtfLite_ParFormat());

                        // *** Start new line ***
                        $sect->writeText('', $arial12, new PHPRtfLite_ParFormat());

                        $rtf_text = strip_tags($personName_extended->name_extended($parent1Db, $parent1_privacy, "parent1"), "<b><i>");
                        //$sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                        $sect->writeText($rtf_text, $arial12);
                    }
                    $family_nr++;
                }


                /**
                 * Show marriage
                 */
                if ($familyDb->fam_kind != 'PRO-GEN') {
                    // onecht kind, wife without man

                    // *** Check if marriage data must be hidden (also hidden if privacy filter is active) ***
                    if (
                        $totallyFilterPerson->isTotallyFiltered($user, $parent1Db)
                    ) {
                        $family_privacy = true;
                    }
                    if (
                        $totallyFilterPerson->isTotallyFiltered($user, $parent2Db)
                    ) {
                        $family_privacy = true;
                    }

                    if ($family_privacy) {
                        // *** Show standard marriage data ***
                        $rtf_text = strip_tags($marriage_cls->marriage_data($familyDb, '', 'short'), "<b><i>");
                        $sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                    } else {
                        $rtf_text = strip_tags($marriage_cls->marriage_data(), "<b><i>");
                        $sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());

                        // *** Show RTF media ***
                        show_rtf_media('family', $familyDb->fam_gedcomnumber);
                    }
                }

                /**
                 * Show parent2 (normally the mother)
                 */
                $sect->addEmptyParagraph($fontSmall, $parBlack);

                // *** Start new line ***
                $sect->writeText('', $arial12, new PHPRtfLite_ParFormat());

                $rtf_text = strip_tags($personName_extended->name_extended($parent2Db, $parent2_privacy, "parent2"), "<b><i>");
                //$sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                $sect->writeText($rtf_text, $arial12);
                $rtf_text = strip_tags($personData->person_data($parent2Db, $parent2_privacy, "parent2", $id), "<b><i>");
                $sect->writeText($rtf_text, $arial12, $parSimple);

                // *** Show RTF media ***
                if (!$parent2_privacy) {
                    show_rtf_media('person', $parent2Db->pers_gedcomnumber);
                }


                /**
                 * Show marriage text
                 */
                $temp = '';

                if ($family_privacy) {
                    // No marriage data
                } elseif ($user["group_texts_fam"] == 'j' && $processText->process_text($familyDb->fam_text)) {
                    $sect->addEmptyParagraph($fontSmall, $parBlack);
                    $rtf_text = strip_tags($processText->process_text($familyDb->fam_text), "<b><i>");
                    $sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                    $source_array = $showSources->show_sources2("family", "fam_text_source", $familyDb->fam_gedcomnumber);
                    if ($source_array) {
                        $rtf_text = strip_tags($source_array['text'], "<b><i>");
                        //$sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                        $sect->writeText($rtf_text, $arial12, null);
                    }
                }

                // *** Show addresses by family ***
                if ($user['group_living_place'] == 'j') {
                    $showAddresses = new \Genealogy\Include\ShowAddresses();
                    $fam_address = $showAddresses->show_addresses('family', 'family_address', $familyDb->fam_gedcomnumber);
                    if ($fam_address) {
                        $rtf_text = strip_tags($fam_address, "<b><i>");
                        $sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                    }
                }

                // *** Family source ***
                $source_array = $showSources->show_sources2("family", "family_source", $familyDb->fam_gedcomnumber);
                if ($source_array) {
                    $rtf_text = strip_tags($source_array['text'], "<b><i>");
                    //$sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                    $sect->writeText($rtf_text, $arial12, null);
                }


                /**
                 * Show children
                 */
                if ($familyDb->fam_children) {
                    $childnr = 1;
                    $child_array = explode(";", $familyDb->fam_children);

                    // *** Show "Child(ren):" ***
                    if (count($child_array) == '1') {
                        $rtf_text = '<b>' . __('Child') . ':</b>';
                        $sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                    } else {
                        $rtf_text = '<b>' . __('Children') . ':</b>';
                        $sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());
                    }

                    $show_privacy_text = false;
                    foreach ($child_array as $i => $value) {
                        $childDb = $db_functions->get_person($child_array[$i]);
                        $child_privacy = $personPrivacy->get_privacy($childDb);

                        // *** Person must be totally hidden ***
                        if ($totallyFilterPerson->isTotallyFiltered($user, $childDb)) {
                            $show_privacy_text = true;
                            continue;
                        }

                        $rtf_text = $childnr . '. ';
                        $sect->writeText($rtf_text, $arial12, new PHPRtfLite_ParFormat());

                        $rtf_text = strip_tags($personName_extended->name_extended($childDb, $child_privacy, "child"), '<b><i>');
                        $sect->writeText($rtf_text, $arial12);

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
                            $search_nr = array_search($child_family[0], $check_double);
                            $rtf_text = '<b><i>, ' . __('follows') . ': </i></b>' . $follows_array[$search_nr];
                            $sect->writeText($rtf_text, $arial12);
                        } elseif ($personData->person_data($childDb, $child_privacy, "child", $id)) {
                            $rtf_text = strip_tags($personData->person_data($childDb, $child_privacy, "child", $id), '<b><i>');
                            $sect->writeText($rtf_text, $arial12, $par_child_text);
                            // *** Show RTF media ***
                            if (!$child_privacy) {
                                show_rtf_media('person', $childDb->pers_gedcomnumber);
                            }
                        }

                        $childnr++;
                    }
                }
            } // Show multiple marriages

        } // Multiple families in 1 generation

    } // nr. of generations
}

// *** If source footnotes are selected, show them here ***
if (isset($_SESSION['save_source_presentation']) && $_SESSION['save_source_presentation'] == 'footnote') {
    $showSourcesFootnotes = new \Genealogy\Include\ShowSourcesFootnotes();
    $rtf_text = strip_tags($showSourcesFootnotes->show_sources_footnotes());
    // *** BUG: add Endnote doesn't show text in rtf file! ***
    //$sect->addEndnote($rtf_text);
    $sect->writeText('<br>');
    $sect->writeText($rtf_text, $arial12);
}


// *** Save rtf document to file ***
$rtf->save($file_name);

$vars['pers_family'] = $data["family_id"];
$link = $processLinks->get_link($uri_path, 'family', $tree_id, true, $vars);
$link .= "main_person=" . $data["main_person"];
?>
<br><br><a href="<?= $download_link; ?>"><?= __('Download RTF report.'); ?></a>
<br><br><?= __('TIP: Don\'t use Wordpad to open this file (the lay-out will be wrong!). It\'s better to use a text processor like Word or OpenOffice Writer.'); ?>
<br><br>
<form method="POST" action="<?= $link; ?>" style="display : inline;">
    <input type="hidden" name="screen_mode" value="">
    <?php if ($data["descendant_report"] == true) { ?>
        <input type="hidden" name="descendant_report" value="<?= $data["descendant_report"]; ?>">
    <?php } ?>
    <input type="Submit" name="submit" value="<?= __('Back'); ?>" class="btn btn-sm btn-primary">
</form>

<?php
function show_rtf_media($media_kind, $gedcomnumber)
{
    // *** Show RTF media ***
    global $sect;

    $showMedia = new \Genealogy\Include\ShowMedia();
    $result = $showMedia->show_media($media_kind, $gedcomnumber);
    if (isset($result[1]) && count($result[1]) > 0) {
        $break = 0;
        $textarr = array();
        $goodpics = FALSE;
        foreach ($result[1] as $key => $value) {
            if (strpos($key, "path") !== FALSE) {
                $type = substr($result[1][$key], -3);
                if ($type === "jpg" || $type === "png") {
                    if ($goodpics == FALSE) {
                        //found 1st pic - make table
                        $table = $sect->addTable();
                        $table->addRow(0.1);
                        $table->addColumnsList(array(5, 5, 5));
                        $goodpics = TRUE;
                    }
                    $break++;
                    $cell = $table->getCell(1, $break);
                    $imageFile = $value;
                    $image = $cell->addImage($imageFile);
                    $txtkey = str_replace("pic_path", "pic_text", $key);
                    $textarr[] = isset($result[1][$txtkey]) ? $result[1][$txtkey] : "&nbsp;";
                }
            }

            //if($break==3) break; // max 3 pics
            // *** Process multiple pictures ***
            if ($break == 3) {
                $break1 = 0;
                if (count($textarr) > 0) {
                    $table->addRow(0.1); //add row only if there is photo text
                    foreach ($textarr as $value) {
                        $break1++;
                        $cell = $table->getCell(2, $break1);
                        $cell->writeText($value);
                    }
                }
                unset($textarr);
                $goodpics = FALSE;
                $break = 0;
            }
        }
        $break1 = 0;

        if (isset($textarr) and count($textarr) > 0) {
            $table->addRow(0.1); //add row only if there is photo text
            foreach ($textarr as $value) {
                $break1++;
                $cell = $table->getCell(2, $break1);
                $cell->writeText($value);
            }
        }
    }
}

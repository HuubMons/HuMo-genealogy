<?php

/**
 * OUTLINE REPORT  - outline_report.php
 * by Yossi Beck - Nov 2008 - (on basis of Huub's family script)
 * Jul. 2011 Huub: translation of variables to English
 * Nov. 2023 Huub: rebuild to MVC
 */

$screen_mode = '';

// *** Check if family gedcomnumber is valid ***
$db_functions->check_family($data["family_id"]);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

// Just change variables for now, because function "outline" will be moved to model too...
$show_details = $data["show_details"];
$show_date = $data["show_date"];
$dates_behind_names = $data["dates_behind_names"];
$nr_generations = $data["nr_generations"];

$path_form = $processLinks->get_link($uri_path, 'outline_report', $tree_id);

//echo '<h1 class="standard_header">' . __('Outline report') . '</h1>';
echo $data["descendant_header"];
?>

<div class="pers_name center d-print-none">
    <!-- Button: show full detais -->
    <form method="POST" action="<?= $path_form; ?>" style="display : inline;">
        <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
        <input type="hidden" name="nr_generations" value="<?= $nr_generations; ?>">
        <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">

        <?php if ($show_details == true) { ?>
            <input type="hidden" name="show_details" value="0">
            <input class="btn btn-sm btn-success" type="Submit" name="submit" value="<?= __('Hide full details'); ?>">
        <?php } else { ?>
            <input type="hidden" name="show_details" value="1">
            <input class="btn btn-sm btn-success" type="Submit" name="submit" value="<?= __('Show full details'); ?>">
        <?php } ?>
    </form>&nbsp;

    <?php if (!$show_details) { ?>
        <!-- Button: show date -->
        <form method="POST" action="<?= $path_form; ?>" style="display : inline;">
            <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
            <input type="hidden" name="nr_generations" value="<?= $nr_generations; ?>">
            <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
            <?php if ($show_date == true) { ?>
                <input type="hidden" name="show_date" value="0">
                <input class="btn btn-sm btn-success" type="Submit" name="submit" value="<?= __('Hide dates'); ?>">
            <?php } else { ?>
                <input type="hidden" name="show_date" value="1">
                <input class="btn btn-sm btn-success" type="Submit" name="submit" value="<?= __('Show dates'); ?>">
            <?php } ?>
        </form>&nbsp;

        <!-- Show button: date after or below each other -->
        <form method="POST" action="<?= $path_form; ?>" style="display : inline;">
            <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
            <input type="hidden" name="nr_generations" value="<?= $nr_generations; ?>">
            <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
            <?php if ($dates_behind_names == "1") { ?>
                <input type="hidden" name="dates_behind_names" value="0">
                <input type="submit" class="btn btn-sm btn-success" name="submit" value="<?= __('Dates below names'); ?>">
            <?php } else { ?>
                <input type="hidden" name="dates_behind_names" value="1">
                <input type="submit" class="btn btn-sm btn-success" name="submit" value="<?= __('Dates beside names'); ?>">
            <?php } ?>
        </form>
    <?php } ?>

    <!-- Show button: nr. of generations -->
    &nbsp;<span class="button">
        <?= __('Choose number of generations to display'); ?>:

        <select size="1" name="selectnr_generations" class="form-select form-select-sm" onChange="window.location=this.value;" style="display:inline; width: 100px;">
            <?php
            $path_tmp = $processLinks->get_link($uri_path, 'outline_report', $tree_id, true);
            for ($i = 2; $i < 20; $i++) {
                $nr_gen = $i - 1;
            ?>
                <option <?php if ($nr_gen == $nr_generations) echo ' selected'; ?> value="<?= $path_tmp; ?>nr_generations=<?= $nr_gen; ?>&amp;id=<?= $data["family_id"]; ?>&amp;main_person=<?= $data["main_person"]; ?>&amp;show_details=<?= $show_details; ?>&amp;show_date=<?= $show_date; ?>&amp;dates_behind_names=<?= $dates_behind_names; ?>"><?= $i; ?></option>
            <?php } ?>
            <option <?= ($nr_generations == 50) ? 'selected' : ''; ?> value="<?= $path_tmp; ?>nr_generations=50&amp;id=<?= $data["family_id"]; ?>&amp;main_person=<?= $data["main_person"]; ?>&amp;show_date=<?= $show_date; ?>&amp;dates_behind_names=<?= $dates_behind_names; ?>"><?= __('All'); ?></option>
        </select>
    </span>

    <?php
    if (!$show_details) {
        if ($user["group_pdf_button"] == 'y' and $language["dir"] != "rtl" and $language["name"] != "简体中文") {
            // TODO check all variables.
            if ($humo_option["url_rewrite"] == "j") {
                //$link = $uri_path . 'outline_report_pdf/' . $tree_id . '/' . $data["family_id"] . '?main_person=' . $data["main_person"];
                $link = $uri_path . 'outline_report_pdf';
            } else {
                //$link = $uri_path . 'index.php?page=outline_report_pdf' . $tree_id . '&amp;id=' . $data["family_id"] . '&amp;main_person=' . $data["main_person"];
                $link = $uri_path . 'index.php?page=outline_report_pdf';
            }
            if ($data["descendant_report"] == true) {
                $link .= '&amp;descendant_report=1';
            }
    ?>

            <!-- Show pdf button landscape -->
            &nbsp;&nbsp;&nbsp;<form method="POST" action="<?= $link; ?>" style="display:inline-block;">
                <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                <input type="hidden" name="screen_mode" value="PDF-P">
                <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
                <input type="hidden" name="nr_generations" value="<?= $nr_generations; ?>">
                <input type="hidden" name="dates_behind_names" value="<?= $dates_behind_names; ?>">
                <input type="hidden" name="show_date" value="<?= $show_date; ?>">
                <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
                <input class="btn btn-sm btn-info" type="Submit" name="submit" value="<?= __('PDF (Portrait)'); ?>">
            </form>

            <!-- Show pdf button portrait -->
            &nbsp;<form method="POST" action="<?= $link; ?>" style="display : inline;">
                <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                <input type="hidden" name="screen_mode" value="PDF-L">
                <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
                <input type="hidden" name="nr_generations" value="<?= $nr_generations; ?>">
                <input type="hidden" name="dates_behind_names" value="<?= $dates_behind_names; ?>">
                <input type="hidden" name="show_date" value="<?= $show_date; ?>">
                <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
                <input class="btn btn-sm btn-info" type="Submit" name="submit" value="<?= __('PDF (Landscape)'); ?>">
            </form>
    <?php
        }
    }
    ?>
</div><br>

<?php
$generation_number = 0;

/**
 * Recursive function outline
 */
function outline($outline_family_id, $outline_main_person, $generation_number, $nr_generations)
{
    global $db_functions, $show_details, $show_date, $dates_behind_names, $nr_generations;
    global $language, $dirmark1, $dirmark1, $user;

    $personPrivacy = new \Genealogy\Include\PersonPrivacy();
    $personName_extended = new \Genealogy\Include\PersonNameExtended();
    $personData = new \Genealogy\Include\PersonData();
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
        $family_privacy = $marriage_cls->get_privacy();

        /**
         * Show parent1 (normally the father)
         */
        if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, vrouw zonder man
            if ($family_nr == 1) {
                // *** Show data of man ***

                $dir = "";
                if ($language["dir"] == "rtl") {
                    $dir = "rtl";    // in the following code calls the css indentation for rtl pages: "div.rtlsub2" instead of "div.sub2"
                }

                $indent = $dir . 'sub' . $generation_number;  // hier wordt de indent bepaald voor de namen div class (sub1, sub2 enz. die in gedcom.css staan)
?>
                <div class="<?= $indent; ?>">
                    <span style="font-weight:bold;font-size:120%"><?= $generation_number; ?> </span>
                    <?php
                    if ($swap_parent1_parent2 == true) {
                        echo $personName_extended->name_extended($person_womanDb, $privacy_woman, "outline");
                        if ($show_details && !$privacy_woman) {
                            echo $personData->person_data($person_womanDb, $privacy_woman, "outline", $familyDb->fam_gedcomnumber);
                        }

                        if ($show_date == "1" && !$privacy_woman && !$show_details) {
                            echo $dirmark1 . ',';
                            if ($dates_behind_names == false) {
                                echo '<br>';
                            }
                            echo ' &nbsp; (' . $languageDate->language_date($person_womanDb->pers_birth_date) . ' - ' . $languageDate->language_date($person_womanDb->pers_death_date) . ')';
                        }
                    } else {
                        echo $personName_extended->name_extended($person_manDb, $privacy_man, "outline");
                        if ($show_details && !$privacy_man) {
                            echo $personData->person_data($person_manDb, $privacy_man, "outline", $familyDb->fam_gedcomnumber);
                        }

                        if ($show_date == "1" && !$privacy_man && !$show_details) {
                            echo $dirmark1 . ',';
                            if ($dates_behind_names == false) {
                                echo '<br>';
                            }
                            echo ' &nbsp; (' . $languageDate->language_date($person_manDb->pers_birth_date) . ' - ' . $languageDate->language_date($person_manDb->pers_death_date) . ')';
                        }
                    }
                    ?>
                </div>
        <?php
            } else {
                // empty: no second show of data of main_person in outline report
            }
            $family_nr++;
        }

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

        // TODO improve this script and use $parent1Db and $parent2Db.
        // Needed for marriageCls.php. Workaround to solve bug.
        global $parent1Db, $parent2Db;
        if ($swap_parent1_parent2) {
            $parent1Db = $person_womanDb;
            $parent2Db = $person_manDb;
        } else {
            $parent1Db = $person_manDb;
            $parent2Db = $person_womanDb;
        }
        ?>

        <div class="<?= $indent; ?>" style="font-style:italic">
            <?php
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

            if ($show_parent2 && $swap_parent1_parent2) {
                echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                echo $personName_extended->name_extended($person_manDb, $privacy_man, "outline");
                if ($show_details && !$privacy_man) {
                    echo $personData->person_data($person_manDb, $privacy_man, "outline", $familyDb->fam_gedcomnumber);
                }

                if ($show_date == "1" && !$privacy_man && !$show_details) {
                    echo $dirmark1 . ',';
                    if ($dates_behind_names == false) {
                        echo '<br>';
                    }
                    echo ' &nbsp; (' . @$languageDate->language_date($person_manDb->pers_birth_date) . ' - ' . @$languageDate->language_date($person_manDb->pers_death_date) . ')';
                }
            } elseif ($show_parent2) {
                if ($show_details) {
                    echo "&nbsp;&nbsp;&nbsp;&nbsp;";
                }
                echo $personName_extended->name_extended($person_womanDb, $privacy_woman, "outline");
                if ($show_details && !$privacy_woman) {
                    echo $personData->person_data($person_womanDb, $privacy_woman, "outline", $familyDb->fam_gedcomnumber);
                }

                if ($show_date == "1" && !$privacy_woman && !$show_details) {
                    echo $dirmark1 . ',';
                    if ($dates_behind_names == false) {
                        echo '<br>';
                    }
                    echo ' &nbsp; (' . @$languageDate->language_date($person_womanDb->pers_birth_date) . ' - ' . @$languageDate->language_date($person_womanDb->pers_death_date) . ')';
                }
            } else {
                // *** No permission to show parent2 ***
                echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
            }
            ?>
        </div>

        <?php
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
                        echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
                        $show_privacy_text = true;
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
                } else {    // Child without own family
                    if ($nr_generations >= $generation_number) {
                        $childgn = $generation_number + 1;
                        $childindent = $dir . 'sub' . $childgn;
        ?>
                        <div class="<?= $childindent; ?>">
                            <span style="font-weight:bold;font-size:120%"><?= $childgn; ?></span>
                            <?php
                            echo $personName_extended->name_extended($childDb, $child_privacy, "outline");
                            if ($show_details and !$child_privacy) {
                                echo $personData->person_data($childDb, $child_privacy, "outline", "");
                            }

                            if ($show_date == "1" and !$child_privacy and !$show_details) {
                                echo $dirmark1 . ',';
                                if ($dates_behind_names == false) {
                                    echo '<br>';
                                }
                                echo ' &nbsp; (' . $languageDate->language_date($childDb->pers_birth_date) . ' - ' . $languageDate->language_date($childDb->pers_death_date) . ')';
                            }
                            ?>
                        </div>
<?php
                    }
                }
                echo "\n";
                $childnr++;
            }
        }
    } // Show  multiple marriages

}

// *** Start function here - recursive if started ***
?>
<?php outline($data["family_id"], $data["main_person"], $generation_number, $nr_generations); ?>
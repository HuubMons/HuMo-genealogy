<?php

/**
 * Family/ relation page
 * 
 * July 2023 Huub: seperated RTF, PDF and descendant chart scripts.
 */

// TODO check this variable.
$screen_mode = '';

// *** "Last visited" id is used for contact form ***
$last_visited = $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
$_SESSION['save_last_visitid'] = $last_visited;

// *** Show person/ family topline: family top text, pop-up settings, PDF export, favourite ***
function topline($data)
{
    global $dataDb, $dbh, $bot_visit, $descendant_loop, $parent1_marr, $rtlmarker;
    global $alignmarker, $language, $uri_path;
    global $user, $tree_id, $humo_option, $link_cls;
    global $parent1_cls, $parent1Db, $parent2_cls, $parent2Db, $selected_language;

    $treetext = show_tree_text($dataDb->tree_id, $selected_language);
?>

    <tr class="table_headline">
        <td class="table_header">
            <div class="family_page_toptext"><?= $treetext['family_top']; ?><br></div>
        </td>

        <td class="table_header" width="220" style="text-align:right;">
            <!-- Hide selections for bots, and second family screen (descendant report etc.) -->
            <?php if (!$bot_visit && $descendant_loop == 0 && $parent1_marr == 0) { ?>
                <?php
                $vars['pers_family'] = $data["family_id"];
                $settings_url = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
                $url_add = '';
                if ($data["main_person"]) {
                    $settings_url .= "main_person=" . $data["main_person"];
                    $url_add = '&amp;';
                }

                $desc_rep = '';
                if ($data["descendant_report"] == true) {
                    $desc_rep = '&amp;descendant_report=1';
                }
                ?>

                <!-- Settings in pop-up screen -->
                <div class="dropdown dropend d-inline">
                    <button class="btn btn-sm" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <img src="images/settings.png" alt="<?= __('Settings'); ?>">
                    </button>
                    <ul class="dropdown-menu p-2" style="width:400px;">
                        <li>
                            <h4><?= __('Settings family screen'); ?></h4>
                        </li>

                        <li>
                            <!-- Compact / Expanded view buttons -->
                            <b><?= __('Family Page'); ?></b><br>
                            <input type="radio" name="keuze0" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>family_expanded=compact<?= $desc_rep; ?>&xx='+this.value" <?= $data["family_expanded"] == 'compact' ? 'checked' : ''; ?>> <?= __('Compact view'); ?><br>
                            <input type="radio" name="keuze0" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>family_expanded=expanded1<?= $desc_rep; ?>&xx='+this.value" <?= $data["family_expanded"] == 'expanded1' ? 'checked' : ''; ?>> <?= __('Expanded view'); ?> 1<br>
                            <input type="radio" name="keuze0" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>family_expanded=expanded2<?= $desc_rep; ?>&xx='+this.value" <?= $data["family_expanded"] == 'expanded2' ? 'checked' : ''; ?>> <?= __('Expanded view'); ?> 2<br>
                        </li>

                        <!-- Select source presentation (as title/ footnote or hide sources) -->
                        <?php if ($user['group_sources'] != 'n') { ?>
                            <li>&nbsp;</li>
                            <li>
                                <b><?= __('Sources'); ?></b><br>
                                <input type="radio" name="keuze1" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>source_presentation=title<?= $desc_rep; ?>&xx='+this.value" <?= $data["source_presentation"] == 'title' ? 'checked' : ''; ?>> <?= __('Show source'); ?><br>
                                <input type="radio" name="keuze1" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>source_presentation=footnote<?= $desc_rep; ?>&xx='+this.value" <?= $data["source_presentation"] == 'footnote' ? 'checked' : ''; ?>> <?= __('Show source as footnote'); ?><br>
                                <input type="radio" name="keuze1" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>source_presentation=hide<?= $desc_rep; ?>&xx='+this.value" <?= $data["source_presentation"] == 'hide' ? 'checked' : ''; ?>> <?= __('Hide sources'); ?><br>
                            </li>
                        <?php
                        }

                        // *** Show/ hide maps ***
                        if ($user["group_googlemaps"] == 'j' && $data["descendant_report"] == false) {
                        ?>
                            <li>&nbsp;</li>
                            <li>
                                <?php
                                // TODO: maybe count valid locations in table.
                                // *** Only show selection if there is a location database ***
                                //$temp = $dbh->query("SHOW TABLES LIKE 'humo_location'");
                                //if ($temp->rowCount()) {
                                ?>
                                <b><?= __('Family map'); ?></b><br>
                                <input type="radio" name="keuze2" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>maps_presentation=show&xx='+this.value" <?= $data["maps_presentation"] == 'show' ? 'checked' : ''; ?>> <?= __('Show family map'); ?><br>
                                <input type="radio" name="keuze2" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>maps_presentation=hide&xx='+this.value" <?= $data["maps_presentation"] == 'hide' ? 'checked' : ''; ?>> <?= __('Hide family map'); ?><br>
                                <?php
                                //}
                                ?>
                            </li>
                        <?php } ?>

                        <?php if ($user['group_pictures'] == 'j') { ?>
                            <li>&nbsp;</li>
                            <li>
                                <b><?= __('Pictures'); ?></b><br>
                                <input type="radio" name="keuze3" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>picture_presentation=show<?= $desc_rep; ?>&xx='+this.value" <?= $data["picture_presentation"] == 'show' ? 'checked' : ''; ?>> <?= __('Show pictures'); ?><br>
                                <input type="radio" name="keuze3" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>picture_presentation=hide<?= $desc_rep; ?>&xx='+this.value" <?= $data["picture_presentation"] == 'hide' ? 'checked' : ''; ?>> <?= __('Hide pictures'); ?><br>
                            </li>
                        <?php } ?>

                        <li>&nbsp;</li>
                        <li>
                            <b><?= __('Texts'); ?></b><br>
                            <input type="radio" name="keuze4" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>text_presentation=show<?= $desc_rep; ?>&xx='+this.value" <?= $data["text_presentation"] == 'show' ? 'checked' : ''; ?>> <?= __('Show texts'); ?><br>
                            <input type="radio" name="keuze4" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>text_presentation=popup<?= $desc_rep; ?>&xx='+this.value" <?= $data["text_presentation"] == 'popup' ? 'checked' : ''; ?>> <?= __('Show texts in popup screen'); ?><br>
                            <input type="radio" name="keuze4" value="" onclick="javascript: document.location.href='<?= $settings_url . $url_add; ?>text_presentation=hide<?= $desc_rep; ?>&xx='+this.value" <?= $data["text_presentation"] == 'hide' ? 'checked' : ''; ?>> <?= __('Hide texts'); ?><br>
                        </li>
                    </ul>
                </div>

                <!-- PDF button -->
                <?php if ($user["group_pdf_button"] == 'y' && $language["dir"] != "rtl" && $language["name"] != "简体中文") { ?>
                    &nbsp;&nbsp;&nbsp;<form method="POST" action="<?= $uri_path; ?>views/family_pdf.php" style="display:inline-block; vertical-align:middle;">
                        <!-- TODO check variables in forms (database -> tree_id). -->
                        <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                        <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
                        <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
                        <?php
                        if ($data["descendant_report"] == true) { ?>
                            <input type="hidden" name="descendant_report" value="<?= $data["descendant_report"]; ?>">
                        <?php } ?>

                        <input class="btn btn-sm btn-info" type="Submit" name="submit" value="<?= __('PDF'); ?>">
                    </form>
                <?php
                }

                // *** RTF button ***
                if ($user["group_rtf_button"] == 'y' && $language["dir"] != "rtl") {
                    if ($humo_option["url_rewrite"] == "j") {
                        echo '&nbsp;&nbsp;&nbsp;<form method="POST" action="' . $uri_path . 'family_rtf" style="display:inline-block; vertical-align:middle;">';
                    } else {
                        echo '&nbsp;&nbsp;&nbsp;<form method="POST" action="' . $uri_path . 'index.php?page=family_rtf" style="display:inline-block; vertical-align:middle;">';
                    }
                ?>
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
                    <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
                    <input type="hidden" name="screen_mode" value="RTF">
                    <?php if ($data["descendant_report"] == true) { ?>
                        <input type="hidden" name="descendant_report" value="<?= $data["descendant_report"]; ?>">
                    <?php } ?>

                    <input class="btn btn-sm btn-info" type="Submit" name="submit" value="<?= __('RTF'); ?>">
                <?php

                    echo '</form> ';
                }

                // *** Add family to favourite list ***
                // If there is a N.N. father, then use mother in favourite icon.
                if (!isset($parent1Db->pers_gedcomnumber)) {
                    $name = $parent2_cls->person_name($parent2Db);
                    $favorite_gedcomnumber = $parent2Db->pers_gedcomnumber;
                } else {
                    $name = $parent1_cls->person_name($parent1Db);
                    $favorite_gedcomnumber = $parent1Db->pers_gedcomnumber;
                }

                if ($name) {
                    // *** New cookies only need 3 variables ***
                    $favorite_value = $tree_id . '|' . $data["family_id"] . '|' . $favorite_gedcomnumber;
                    $check = false;
                    if (isset($_SESSION['save_favorites'])) {
                        foreach ($_SESSION['save_favorites'] as $key => $value) {
                            if ($value == $favorite_value) {
                                $check = true;
                            }
                        }
                    }

                    $vars['pers_family'] = $data["family_id"];
                    $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
                    $link .= "main_person=" . $data["main_person"];
                ?>
                    &nbsp;&nbsp;&nbsp;
                    <form method="POST" action="<?= $link; ?>" style="display:inline-block; vertical-align:middle;">
                        <?php
                        if ($data["descendant_report"] == true) {
                            echo '<input type="hidden" name="descendant_report" value="1">';
                        }
                        if ($check == false) {
                            echo '<input type="hidden" name="favorite" value="' . $favorite_value . '">';
                            echo ' <input type="image" src="images/favorite.png" name="favorite_button" alt="' . __('Add to favourite list') . '">';
                        } else {
                            echo '<input type="hidden" name="favorite_remove" value="' . $favorite_value . '">';
                            echo ' <input type="image" src="images/favorite_blue.png" name="favorite_button" alt="' . __('Add to favourite list') . '">';
                        }
                        ?>
                    </form>
            <?php
                }
            } // End of bot visit
            ?>
        </td>
    </tr>
    <?php
}


$family_nr = 1;  // *** process multiple families ***

// *** Check if family gedcomnumber is valid ***
$db_functions->check_family($data["family_id"]);

// *** Check if person gedcomnumber is valid ***
$db_functions->check_person($data["main_person"]);

// **********************************************************
// *** Maximum number of generations in descendant report ***
// **********************************************************
$max_generation = ($humo_option["descendant_generations"] - 1);

// **************************
// *** Show single person ***
// **************************
if (!$data["family_id"]) {
    // *** Privacy filter ***
    @$parent1Db = $db_functions->get_person($data["main_person"]);
    // *** Use class to show person ***
    $parent1_cls = new Person_cls($parent1Db);

    // *** Add tip in person screen ***
    if (!$bot_visit) {
    ?>
        <div class="d-print-none"><b>
                <?php printf(__('TIP: use %s for other (ancestor and descendant) reports.'), '<img src="images/reports.gif">'); ?>
            </b><br><br>
        </div>
    <?php
    }

    $id = '';
    ?>

    <table class="humo standard">
        <!-- Show person topline (top text, settings, favourite) -->
        <?php topline($data); ?>
        <tr>
            <td colspan="4">
                <!--  Show person data -->
                <span class="parent1">
                    <?= $parent1_cls->name_extended("parent1"); ?>
                    <?= $parent1_cls->person_data("parent1", $id); ?>
                </span>
            </td>
        </tr>
    </table>
    <?php
}

// *******************
// *** Show family ***
// *******************
else {
    $descendant_family_id2[] = $data["family_id"];
    $descendant_main_person2[] = $data["main_person"];

    // *** Nr. of generations ***
    $location_prep = $dbh->prepare("SELECT * FROM humo_location WHERE location_lat IS NOT NULL AND location_location =?");
    $location_prep->bindParam(1, $location_var);

    $old_stat_prep = $dbh->prepare("UPDATE humo_families SET fam_counter=? WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber=?");
    $old_stat_prep->bindParam(1, $fam_counter_var);
    $old_stat_prep->bindParam(2, $fam_gednr_var);

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
            // *** Show links to other charts at top of page ***
            if ($descendant_loop == 0) {
                echo $data["descendant_header"];
            }

            echo '<h2 class="standard_header">' . ucfirst(__('generation ')) . $data["number_roman"][$descendant_loop + 1] . '</h2>';
        }

        // *** Nr of families in one generation ***
        $nr_families = count($descendant_family_id);
        for ($descendant_loop2 = 0; $descendant_loop2 < $nr_families; $descendant_loop2++) {
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
                @$familyDb = $db_functions->get_family($id);

                // *** Don't count search bots, crawlers etc. ***
                if (!$bot_visit) {
                    // *** Update (old) statistics counter ***
                    $fam_counter = $familyDb->fam_counter + 1;
                    $fam_counter_var = $fam_counter;
                    $fam_gednr_var = $id;
                    $old_stat_prep->execute();

                    // *** Extended statistics ***
                    if ($data["descendant_report"] == false && $user['group_statistics'] == 'j') {
                        $stat_easy_id = $familyDb->fam_tree_id . '-' . $familyDb->fam_gedcomnumber . '-' . $familyDb->fam_man . '-' . $familyDb->fam_woman;
                        $update_sql = "INSERT INTO humo_stat_date SET
                            stat_easy_id='" . $stat_easy_id . "',
                            stat_ip_address='" . $visitor_ip . "',
                            stat_user_agent='" . $_SERVER['HTTP_USER_AGENT'] . "',
                            stat_tree_id='" . $familyDb->fam_tree_id . "',
                            stat_gedcom_fam='" . $familyDb->fam_gedcomnumber . "',
                            stat_gedcom_man='" . $familyDb->fam_man . "',
                            stat_gedcom_woman='" . $familyDb->fam_woman . "',
                            stat_date_stat='" . date("Y-m-d H:i") . "',
                            stat_date_linux='" . time() . "'";
                        $result = $dbh->query($update_sql);

                        // *** June 2023: get country code for statistics ***
                        // *** Check if country code is known for this IP address ***
                        $sql = "SELECT stat_country_ip_address FROM humo_stat_country WHERE stat_country_ip_address = :stat_country_ip_address";
                        try {
                            $qry = $dbh->prepare($sql);
                            $qry->bindValue(':stat_country_ip_address', $visitor_ip, PDO::PARAM_STR);
                            $qry->execute();
                        } catch (PDOException $e) {
                            //echo $e->getMessage() . '<br>';
                        }

                        $record = $qry->fetch(PDO::FETCH_OBJ);
                        if (!isset($record->stat_country_ip_address)) {
                            if (strlen($visitor_ip) > 6) {
                                $sql = "INSERT INTO humo_stat_country
                                    SET stat_country_ip_address = :stat_country_ip_address,
                                    stat_country_code =:stat_country_code";

                                // *** Get country code ***
                                include_once(__DIR__ . '/../include/geoplugin/geoplugin.class.php');
                                $geoplugin = new geoPlugin();
                                $geoplugin->locate();

                                try {
                                    $qry = $dbh->prepare($sql);
                                    $qry->bindValue(':stat_country_ip_address', $visitor_ip, PDO::PARAM_STR);
                                    $qry->bindValue(':stat_country_code', $geoplugin->countryCode, PDO::PARAM_STR);
                                    $qry->execute();
                                } catch (PDOException $e) {
                                    //echo $e->getMessage() . '<br>';
                                }
                            }
                        }
                    }
                }

                if ($swap_parent1_parent2 == true) {
                    $parent1 = $familyDb->fam_woman;
                    $parent2 = $familyDb->fam_man;
                } else {
                    $parent1 = $familyDb->fam_man;
                    $parent2 = $familyDb->fam_woman;
                }
                @$parent1Db = $db_functions->get_person($parent1);
                // *** Proces parent1 using a class ***
                $parent1_cls = new Person_cls($parent1Db);

                @$parent2Db = $db_functions->get_person($parent2);
                // *** Proces parent2 using a class ***
                $parent2_cls = new Person_cls($parent2Db);

                // *** Proces marriage using a class ***
                $marriage_cls = new marriage_cls($familyDb, $parent1_cls->privacy, $parent2_cls->privacy);
                $family_privacy = $marriage_cls->privacy;


                // *******************************************************************
                // *** Show family                                                 ***
                // *******************************************************************

                // *** Internal link for descendant_report ***
                if ($data["descendant_report"] == true) {
                    // *** Internal link (Roman number_generation) ***
                    echo '<a name="' . $data["number_roman"][$descendant_loop + 1] . '-' . $data["number_generation"][$descendant_loop2 + 1] . '">';
                    echo '&nbsp;</a>';
                }

                // *** Add tip in family screen ***
                if (!$bot_visit && $descendant_loop == 0 && $parent1_marr == 0) {
    ?>
                    <div class="d-print-none"><b>
                            <?php printf(__('TIP: use %s for other (ancestor and descendant) reports.'), '<img src="images/reports.gif">'); ?>
                        </b><br><br>
                    </div>
                <?php } ?>

                <table class="humo standard">
                <!-- <table class="table"> -->
                    <?php
                    // *** Show family top line (family top text, settings, favourite) ***
                    topline($data);

                    echo '<tr><td colspan="4">';

                    // *************************************************************
                    // *** Parent1 (normally the father)                         ***
                    // *************************************************************
                    if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, woman without man
                        if ($family_nr == 1) {
                    ?>
                            <!-- Show data of parent1 -->
                            <div class="parent1">
                                <?php
                                // *** Show roman number in descendant_report ***
                                if ($data["descendant_report"] == true) {
                                    echo '<b>' . $data["number_roman"][$descendant_loop + 1] . '-' . $data["number_generation"][$descendant_loop2 + 1] . '</b> ';
                                }

                                $show_name_texts = true;
                                echo $parent1_cls->name_extended("parent1", $show_name_texts);
                                echo $parent1_cls->person_data("parent1", $id);

                                // *** Change page title ***
                                if ($descendant_loop == 0 && $descendant_loop2 == 0) {
                                    $name = $parent1_cls->person_name($parent1Db);
                                    echo '<script>';
                                    echo 'document.title = "' . __('Family Page') . ': ' . $name["index_name"] . '";';
                                    echo '</script>';
                                }
                                ?>
                            </div>
                        <?php
                        } else {
                            // *** Show standard marriage text and name in 2nd, 3rd, etc. marriage (relation) ***
                        ?>
                            <div class="py-3">
                                <?= $marriage_cls->marriage_data($familyDb, $family_nr, 'shorter'); ?>
                            </div>
                            <?= $parent1_cls->name_extended("parent1"); ?><br>
                        <?php
                        }
                        $family_nr++;
                    } // *** End check of PRO-GEN ***


                    // *************************************************************
                    // *** Marriage                                              ***
                    // *************************************************************
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
                        ?>

                        <br>
                        <div class="marriage">
                            <?php
                            // *** $family_privacy='1' = filter ***
                            if ($family_privacy) {
                                // *** Show standard marriage data ***
                                echo $marriage_cls->marriage_data($familyDb, '', 'short');
                            } else {
                                echo $marriage_cls->marriage_data();
                            }
                            ?>
                        </div><br>
                    <?php
                    }

                    // *************************************************************
                    // *** Parent2 (normally the mother)                         ***
                    // *************************************************************
                    ?>
                    <div class="parent2">
                        <?php
                        // *** Person must be totally hidden ***
                        if ($user["group_pers_hide_totally_act"] == 'j' && isset($parent2Db->pers_own_code) && strpos(' ' . $parent2Db->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
                            echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
                        } else {
                            $show_name_texts = true;
                            echo $parent2_cls->name_extended("parent2", $show_name_texts);
                            echo $parent2_cls->person_data("parent2", $id);
                        }
                        ?>
                    </div>

                    <?php
                    // *************************************************************
                    // *** Marriagetext                                          ***
                    // *************************************************************
                    $temp = '';

                    if ($family_privacy) {
                        // No marriage data
                    } elseif ($user["group_texts_fam"] == 'j' && process_text($familyDb->fam_text)) {
                        echo '<br>' . process_text($familyDb->fam_text, 'family');
                        // *** BK: source by family text ***
                        $source_array = show_sources2("family", "fam_text_source", $familyDb->fam_gedcomnumber);
                        if ($source_array) {
                            echo $source_array['text'];
                        }
                    }

                    // *** Show addresses by family ***
                    if ($user['group_living_place'] == 'j') {
                        $fam_address = show_addresses('family', 'family_address', $familyDb->fam_gedcomnumber);
                        if ($fam_address) {
                            echo '<br>' . $fam_address;
                        }
                    }

                    // *** Family source ***
                    $source_array = show_sources2("family", "family_source", $familyDb->fam_gedcomnumber);
                    if ($source_array) {
                        echo $source_array['text'];
                    }


                    // *************************************************************
                    // *** Children                                              ***
                    // *************************************************************

                    if ($familyDb->fam_children) {
                        $childnr = 1;
                        $child_array = explode(";", $familyDb->fam_children);
                        $show_privacy_text = false;

                        // TODO improve layout in RTF export
                    ?>
                        <div class="py-3">
                            <b>
                                <?= (count($child_array) == '1') ? __('Child') . ':' : __('Children') . ':'; ?>
                            </b>
                        </div>

                        <?php
                        foreach ($child_array as $i => $value) {
                            @$childDb = $db_functions->get_person($child_array[$i]);
                            // *** Use person class ***
                            $child_cls = new Person_cls($childDb);

                            // For now don't use this code in DNA and other graphical charts. Because they will be corrupted.
                            // *** Person must be totally hidden ***
                            if ($user["group_pers_hide_totally_act"] == 'j' && strpos(' ' . $childDb->pers_own_code, $user["group_pers_hide_totally"]) > 0) {
                                if (!$show_privacy_text) {
                                    echo __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
                                }
                                $show_privacy_text = true;
                                continue;
                            }

                        ?>
                            <div class="children">
                                <div class="child_nr" id="person_<?= $childDb->pers_gedcomnumber; ?>"><?= $childnr; ?>.</div>
                                <?php
                                echo $child_cls->name_extended("child");

                                // *** Build descendant_report ***
                                if ($data["descendant_report"] == true && $childDb->pers_fams && $descendant_loop < $max_generation) {
                                    // *** 1st family of child ***
                                    $child_family = explode(";", $childDb->pers_fams);

                                    // *** Check for double families in descendant report (if a person relates or marries another person in the same family) ***
                                    if (isset($check_double) && in_array($child_family[0], $check_double)) {
                                        // *** Don't show this family, double... ***
                                    } else {
                                        $descendant_family_id2[] = $child_family[0];
                                    }
                                    // *** Save all marriages of person in check array ***
                                    $counter = count($child_family);

                                    // *** Save all marriages of person in check array ***
                                    for ($k = 0; $k < $counter; $k++) {
                                        $check_double[] = $child_family[$k];
                                        // *** Save "Follows: " text in array, also needed for doubles... ***
                                        $follows_array[] = $data["number_roman"][$descendant_loop + 2] . '-' . $data["number_generation"][count($descendant_family_id2)];
                                    }

                                    // *** YB: show children first in descendant_report ***
                                    $descendant_main_person2[] = $childDb->pers_gedcomnumber;
                                    $search_nr = array_search($child_family[0], $check_double);
                                    echo '<b><i>, ' . __('follows') . ': </i></b>';
                                    echo '<a href="' . str_replace("&", "&amp;", $_SERVER['REQUEST_URI']) . '#' . $follows_array[$search_nr] . '">' . $follows_array[$search_nr] . '</a>';
                                } else {
                                    echo $child_cls->person_data("child", $id);
                                }
                                ?>
                            </div><br>
                        <?php
                            $childnr++;
                        }
                    }

                    // *********************************************************************************************
                    // *** Check for adoptive parents (just for sure: made it for multiple adoptive parents...) ***
                    // *********************************************************************************************
                    $famc_adoptive_qry_prep = $db_functions->get_events_kind($familyDb->fam_gedcomnumber, 'adoption');
                    foreach ($famc_adoptive_qry_prep as $famc_adoptiveDb) {
                        @$childDb = $db_functions->get_person($famc_adoptiveDb->event_connect_id);
                        // *** Use person class ***
                        $child_cls = new Person_cls($childDb);
                        ?>
                        <tr>
                            <td colspan="4">
                                <div class="children">
                                    <b><?= __('Adopted child:'); ?></b><?= $child_cls->name_extended("child"); ?>
                                </div>
                            </td>
                        </tr>
                    <?php
                    }

                    // *************************************************************
                    // *** Check for adoptive parent ESPECIALLY MADE FOR ALDFAER ***
                    // *************************************************************
                    $famc_adoptive_by_person_qry_prep = $db_functions->get_events_kind($familyDb->fam_man, 'adoption_by_person');
                    foreach ($famc_adoptive_by_person_qry_prep as $famc_adoptiveDb) {
                        @$childDb = $db_functions->get_person($famc_adoptiveDb->event_connect_id);
                        // *** Use person class ***
                        $child_cls = new Person_cls($childDb);
                    ?>
                        <tr>
                            <td colspan="4">
                                <div class="children">
                                    <?php
                                    if ($famc_adoptiveDb->event_gedcom == 'steph') {
                                        echo '<b>' . __('Stepchild') . ':</b>';
                                    } elseif ($famc_adoptiveDb->event_gedcom == 'legal') {
                                        echo '<b>' . __('Legal child') . ':</b>';
                                    } elseif ($famc_adoptiveDb->event_gedcom == 'foster') {
                                        echo '<b>' . __('Foster child') . ':</b>';
                                    } else {
                                        echo '<b>' . __('Adopted child:') . '</b>';
                                    }
                                    ?>
                                    <?= $child_cls->name_extended("child"); ?>
                                </div>
                            </td>
                        </tr>
                    <?php
                    }
                    // *************************************************************
                    // *** Check for adoptive parent ESPECIALLY MADE FOR ALDFAER ***
                    // *************************************************************
                    $famc_adoptive_by_person_qry_prep = $db_functions->get_events_kind($familyDb->fam_woman, 'adoption_by_person');
                    foreach ($famc_adoptive_by_person_qry_prep as $famc_adoptiveDb) {
                        @$childDb = $db_functions->get_person($famc_adoptiveDb->event_connect_id);
                        // *** Use person class ***
                        $child_cls = new Person_cls($childDb);
                    ?>
                        <tr>
                            <td colspan="4">
                                <div class="children">
                                    <?php
                                    if ($famc_adoptiveDb->event_gedcom == 'steph') {
                                        echo '<b>' . __('Stepchild') . ':</b>';
                                    } elseif ($famc_adoptiveDb->event_gedcom == 'legal') {
                                        echo '<b>' . __('Legal child') . ':</b>';
                                    } elseif ($famc_adoptiveDb->event_gedcom == 'foster') {
                                        echo '<b>' . __('Foster child') . ':</b>';
                                    } else {
                                        echo '<b>' . __('Adopted child:') . '</b>';
                                    }

                                    echo ' ' . $child_cls->name_extended("child");
                                    ?>
                                </div>
                            </td>
                        </tr>
                    <?php } ?>
                </table><br>

                <?php
                // *** Show Google or OpenStreetMap map ***
                if ($user["group_googlemaps"] == 'j' && $data["descendant_report"] == false && $data["maps_presentation"] == 'show') {
                    unset($location_array);
                    unset($lat_array);
                    unset($lon_array);
                    unset($text_array);

                    $location_array[] = '';
                    $lat_array[] = '';
                    $lon_array[] = '';
                    $text_array[] = '';

                    $newline = "\\n";
                    if (isset($humo_option["use_world_map"]) && $humo_option["use_world_map"] == 'OpenStreetMap') {
                        $newline = '<br>';
                    }


                    // BIRTH man
                    if (!$parent1_cls->privacy) {
                        $location_var = $parent1Db->pers_birth_place;
                        if ($location_var != '') {
                            $short = __('BORN_SHORT');
                            if ($location_var == '') {
                                $location_var = $parent1Db->pers_bapt_place;
                                $short = __('BAPTISED_SHORT');
                            }
                            $location_prep->execute();
                            $man_birth_result = $location_prep->rowCount();
                            if ($man_birth_result > 0) {
                                $info = $location_prep->fetch();
                                $name = $parent1_cls->person_name($parent1Db);
                                $google_name = $name["standard_name"];

                                $location_array[] = $location_var;
                                $lat_array[] = $info['location_lat'];
                                $lon_array[] = $info['location_lng'];
                                $text_array[] = addslashes($google_name . ", " . $short . ' ' . $location_var);
                            }
                        }
                    }

                    // BIRTH woman
                    if ($parent2Db && !$parent2_cls->privacy) {
                        $location_var = $parent2Db->pers_birth_place;
                        if ($location_var != '') {
                            $short = __('BORN_SHORT');
                            if ($location_var == '') {
                                $location_var = $parent2Db->pers_bapt_place;
                                $short = __('BAPTISED_SHORT');
                            }
                            $location_prep->execute();
                            $woman_birth_result = $location_prep->rowCount();
                            if ($woman_birth_result > 0) {
                                $info = $location_prep->fetch();
                                $name = $parent2_cls->person_name($parent2Db);
                                $google_name = $name["standard_name"];
                                $key = array_search($location_var, $location_array);
                                if (isset($key) && $key > 0) {
                                    $text_array[$key] .= $newline . addslashes($google_name . ", " . $short . ' ' . $location_var);
                                } else {
                                    $location_array[] = $location_var;
                                    $lat_array[] = $info['location_lat'];
                                    $lon_array[] = $info['location_lng'];
                                    $text_array[] = addslashes($google_name . ", " . $short . ' ' . $location_var);
                                }
                            }
                        }
                    }

                    // DEATH man
                    if ($parent1Db && !$parent1_cls->privacy) {
                        $location_var = $parent1Db->pers_death_place;
                        $short = __('DIED_SHORT');
                        if ($location_var == '') {
                            $location_var = $parent1Db->pers_buried_place;
                            $short = __('BURIED_SHORT');
                        }
                        if ($location_var != '') {
                            $location_prep->execute();
                            $man_death_result = $location_prep->rowCount();

                            if ($man_death_result > 0) {
                                $info = $location_prep->fetch();

                                $name = $parent1_cls->person_name($parent1Db);
                                $google_name = $name["standard_name"];
                                $key = array_search($location_var, $location_array);
                                if (isset($key) && $key > 0) {
                                    $text_array[$key] .= $newline . addslashes($google_name . ", " . $short . ' ' . $location_var);
                                } else {
                                    $location_array[] = $location_var;
                                    $lat_array[] = $info['location_lat'];
                                    $lon_array[] = $info['location_lng'];
                                    $text_array[] = addslashes($google_name . ", " . $short . ' ' . $location_var);
                                }
                            }
                        }
                    }

                    // DEATH woman
                    if ($parent2Db && !$parent2_cls->privacy) {
                        $location_var = $parent2Db->pers_death_place;
                        $short = __('DIED_SHORT');
                        if ($location_var == '') {
                            $location_var = $parent2Db->pers_buried_place;
                            $short = __('BURIED_SHORT');
                        }
                        if ($location_var != '') {
                            $location_prep->execute();
                            $woman_death_result = $location_prep->rowCount();
                            if ($woman_death_result > 0) {
                                $info = $location_prep->fetch();

                                $name = $parent2_cls->person_name($parent2Db);
                                $google_name = $name["standard_name"];
                                $key = array_search($location_var, $location_array);
                                if (isset($key) && $key > 0) {
                                    $text_array[$key] .= $newline . addslashes($google_name . ", " . $short . ' ' . $location_var);
                                } else {
                                    $location_array[] = $location_var;
                                    $lat_array[] = $info['location_lat'];
                                    $lon_array[] = $info['location_lng'];
                                    $text_array[] = addslashes($google_name . ", " . $short . ' ' . $location_var);
                                }
                            }
                        }
                    }

                    // MARRIED
                    $location_var = $familyDb->fam_marr_place;
                    if ($location_var != '') {
                        $location_prep->execute();
                        $marriage_result = $location_prep->rowCount();

                        if ($marriage_result > 0) {
                            $info = $location_prep->fetch();

                            $name = $parent1_cls->person_name($parent1Db);
                            $google_name = $name["standard_name"];

                            $name = $parent2_cls->person_name($parent2Db);
                            $google_name .= ' & ' . $name["standard_name"];

                            if (!$parent1_cls->privacy && !$parent2_cls->privacy) {
                                $key = array_search($familyDb->fam_marr_place, $location_array);
                                if (isset($key) && $key > 0) {
                                    $text_array[$key] .= $newline . addslashes($google_name . ", " . __('married') . ' ' . $familyDb->fam_marr_place);
                                } else {
                                    $location_array[] = $familyDb->fam_marr_place;
                                    $lat_array[] = $info['location_lat'];
                                    $lon_array[] = $info['location_lng'];
                                    $text_array[] = addslashes($google_name . ", " . __('married') . ' ' . $familyDb->fam_marr_place);
                                }
                            }
                        }
                    }


                    $child_array = explode(";", $familyDb->fam_children);
                    for ($i = 0; $i <= substr_count($familyDb->fam_children, ";"); $i++) {
                        @$childDb = $db_functions->get_person($child_array[$i]);
                        if ($childDb !== false) {  // no error in query
                            // *** Use person class ***
                            $person_cls = new Person_cls($childDb);
                            if (!$person_cls->privacy) {

                                // *** Child birth ***
                                $location_var = $childDb->pers_birth_place;
                                if ($location_var != '') {
                                    $location_prep->execute();
                                    $child_result = $location_prep->rowCount();

                                    if ($child_result > 0) {
                                        $info = $location_prep->fetch();

                                        $name = $person_cls->person_name($childDb);
                                        $google_name = $name["standard_name"];
                                        $key = array_search($childDb->pers_birth_place, $location_array);
                                        if (isset($key) && $key > 0) {
                                            $text_array[$key] .= $newline . addslashes($google_name . ", " . __('BORN_SHORT') . ' ' . $childDb->pers_birth_place);
                                        } else {
                                            $location_array[] = $childDb->pers_birth_place;
                                            $lat_array[] = $info['location_lat'];
                                            $lon_array[] = $info['location_lng'];
                                            $text_array[] = addslashes($google_name . ", " . __('BORN_SHORT') . ' ' . $childDb->pers_birth_place);
                                        }
                                    }
                                }
                                // *** Child death ***
                                $location_var = $childDb->pers_death_place;
                                if ($location_var != '') {
                                    $location_prep->execute();
                                    $child_result = $location_prep->rowCount();

                                    if ($child_result > 0) {
                                        $info = $location_prep->fetch();

                                        $name = $person_cls->person_name($childDb);
                                        $google_name = $name["standard_name"];
                                        $key = array_search($childDb->pers_death_place, $location_array);
                                        if (isset($key) && $key > 0) {
                                            $text_array[$key] .= $newline . addslashes($google_name . ", " . __('DIED_SHORT') . ' ' . $childDb->pers_death_place);
                                        } else {
                                            $location_array[] = $childDb->pers_death_place;
                                            $lat_array[] = $info['location_lat'];
                                            $lon_array[] = $info['location_lng'];
                                            $text_array[] = addslashes($google_name . ", " . __('DIED_SHORT') . ' ' . $childDb->pers_death_place);
                                        }
                                    }
                                }
                            }
                        }
                    }


                    // *** OpenStreetMap ***
                    if (isset($humo_option["use_world_map"]) && $humo_option["use_world_map"] == 'OpenStreetMap') {
                        $map = 'map' . $family_nr;
                        $markers = 'markers' . $family_nr;
                        $group = 'group' . $family_nr;

                        if ($family_nr == 2) { // *** Only include once ***
                ?>
                            <link rel="stylesheet" href="assets/leaflet/leaflet.css">
                            <script src="assets/leaflet/leaflet.js"></script>
                        <?php } ?>
                        <!-- Show openstreetmap by every family -->
                        <div id="<?= $map; ?>" style="height: 400px;" class="container-md"></div><br>

                        <?php
                        // *** Map using fitbound (all markers visible) ***
                        echo '<script>
                            var ' . $map . ' = L.map("' . $map . '").setView([48.85, 2.35], 10);
                            var ' . $markers . ' = [';

                        // *** Add all markers from array ***
                        for ($i = 1; $i < count($location_array); $i++) {
                            if ($i > 1) echo ',';
                            echo 'L.marker([' . $lat_array[$i] . ', ' . $lon_array[$i] . ']) .bindPopup(\'' . $text_array[$i] . '\')';
                            echo "\n";
                        }

                        echo '];
                            var ' . $group . ' = L.featureGroup(' . $markers . ').addTo(' . $map . ');
                            setTimeout(function () {
                                ' . $map . '.fitBounds(' . $group . '.getBounds());
                            }, 1000);
                            L.tileLayer(\'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png\', {
                                attribution: \'&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors\'
                            }).addTo(' . $map . ');
                        </script>';
                    } else {

                        $show_google_map = false;
                        // *** Only show main javascript once ***
                        if ($family_nr == 2) {
                            $api_key = '';
                            if (isset($humo_option['google_api_key']) && $humo_option['google_api_key'] != '') {
                                $api_key = "&key=" . $humo_option['google_api_key'];
                            }

                            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                                echo '<script src="https://maps.google.com/maps/api/js?v=3' . $api_key . '&callback=initMap&v=weekly&libraries=marker"></script>';
                            } else {
                                echo '<script src="http://maps.google.com/maps/api/js?v=3' . $api_key . '&callback=initMap&v=weekly&libraries=marker"></script>';
                            }

                            echo '<script>
                                var center = null;
                                var map=new Array();
                                var currentPopup;
                                var bounds = new google.maps.LatLngBounds();
                            </script>';

                            /*
                            echo '<script>
                                function addMarker(family_nr, lat, lng, info, icon) {
                                    var pt = new google.maps.LatLng(lat, lng);
                                    var fam_nr=family_nr;
                                    bounds.extend(pt);
                                    //bounds(fam_nr).extend(pt);
                                    var marker = new google.maps.Marker({
                                        position: pt,
                                        icon: icon,
                                        title: info,
                                        map: map[fam_nr]
                                    });
                                }
                            </script>';
                            */

                            echo '<script>
                                function addMarker(family_nr, lat, lng, info, icon) {
                                    var pt = new google.maps.LatLng(lat, lng);
                                    var fam_nr=family_nr;
                                    bounds.extend(pt);
                                    //bounds(fam_nr).extend(pt);
                                    var marker = new google.maps.Marker({
                                        position: pt,
                                        title: info,
                                        map: map[fam_nr]
                                    });
                                }
                            </script>';
                        }

                        $api_key = '';
                        if (isset($humo_option['google_api_key']) && $humo_option['google_api_key'] != '') {
                            $api_key = "&key=" . $humo_option['google_api_key'];
                        }

                        //$maptype = "ROADMAP";
                        //if (isset($humo_option['google_map_type'])) {
                        //    $maptype = $humo_option['google_map_type'];
                        //}

                        //mapTypeId: google.maps.MapTypeId.' . $maptype . ',
                        echo '<script>
                            function initMap' . $family_nr . '(family_nr) {
                                var fam_nr=family_nr;
                                map[fam_nr] = new google.maps.Map(document.getElementById(fam_nr), {
                                    center: new google.maps.LatLng(50.917293, 5.974782),
                                    maxZoom: 16,
                                    mapTypeControl: true,
                                    mapTypeControlOptions: {
                                        style: google.maps.MapTypeControlStyle.HORIZONTAL_BAR
                                    },
                                    mapId: "MAP_07_2024", // Map ID is required for advanced markers.
                                });
                                ';

                        // *** Add all markers from array ***
                        for ($i = 1; $i < count($location_array); $i++) {
                            $show_google_map = true;

                            if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
                                echo ("addMarker($family_nr,$lat_array[$i], $lon_array[$i], '" . $text_array[$i] . "', 'https://chart.googleapis.com/chart?chst=d_map_spin&chld=0.5|0|f7fe2e|10|_|" . $api_key . "');\n");
                            } else {
                                echo ("addMarker($family_nr,$lat_array[$i], $lon_array[$i], '" . $text_array[$i] . "', 'http://chart.googleapis.com/chart?chst=d_map_spin&chld=0.5|0|f7fe2e|10|_|" . $api_key . "');\n");
                            }
                        }

                        echo 'center = bounds.getCenter();
                        map[fam_nr].fitBounds(bounds);
                        }
                            </script>';

                        if ($show_google_map == true) {
                        ?>
                            <?= __('Family events'); ?><br>
                            <div style="height: 400px;" id="<?= $family_nr; ?>" class="container-md"></div><br>
                            <script>
                                initMap<?= $family_nr; ?>(<?= $family_nr; ?>);
                            </script>
    <?php
                        }
                    }
                }
            } // Show multiple marriages

        } // Multiple families in 1 generation

    } // nr. of generations
} // End of single person

// *** If source footnotes are selected, show them here ***
if (isset($_SESSION['save_source_presentation']) && $_SESSION['save_source_presentation'] == 'footnote') {
    echo show_sources_footnotes();
}

// *** Generate citations, that can be used as a source for this person/ page ***
/* EXAMPLE:
"Family Page: Bethel, Catherine Ann Charles." database, Dolly Mae Alpha Index - Wyannie Malone Historical Museum (http://subscriber.bahamasgenealogyrecor ... son=I52982 : accessed 17 April 2016, Catherine Anne Charles Bethel, born 19 feb 1809 at New Providence, Bahamas; citing Christ Church Cathedral - Baptismal Register. Book 2, Whites -Page 99, item 21. for period Feb. 7, 1802 to Dec. 22, 1840.
*/
if ($user['group_citation_generation'] == 'y') {
    $name1 = $parent1_cls->person_name($parent1Db);
    if (isset($parent2Db)) {
        $name2 = $parent2_cls->person_name($parent2Db);
    }
    ?>
    <br><b><?= __('Citation for:') . ' ' . __('Family Page'); ?></b><br>

    <span class="citation">
        <?php
        // *** Name of citation ***
        echo '"' . __('Family Page') . ': ' . $name1['name'];
        if (isset($name2['name']) && $name2['name']) {
            echo ' &amp; ' . $name2['name'] . '."';
        }

        // *** Link to family page ***
        echo ' HuMo-genealogy - ' . $humo_option["database_name"] . ' (';

        $vars['pers_family'] = $data["family_id"];
        $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
        $link .= "main_person=" . $data["main_person"];
        if ($humo_option["url_rewrite"] == "j") {
            echo $link;
        } else {
            echo 'http://' . $_SERVER['SERVER_NAME'] . $link;
        }

        echo ' : ' . __('accessed') . ' ' . date("d F Y");
        echo ')';

        // *** Name and GEDCOM number of main person ***
        if ($parent1Db) {
            echo ' ' . $name1['name'] . ' #' . $parent1Db->pers_gedcomnumber;

            // *** Birth or baptise date ***
            if (isset($family_privacy) && !$family_privacy) {
                if ($parent1Db->pers_birth_date || $parent1Db->pers_birth_place) {
                    echo ', ' . __('born') . ' ' . date_place($parent1Db->pers_birth_date, $parent1Db->pers_birth_place);
                } elseif ($parent1Db->pers_bapt_date || $parent1Db->pers_bapt_place) {
                    echo ', ' . __('baptised') . ' ' . date_place($parent1Db->pers_bapt_date, $parent1Db->pers_bapt_place);
                }
            }
        }
        ?>
    </span><br><br>
    <?php
}

// *** Extra footer text / User notes in family screen ***
if ($data["descendant_report"] == false) {
    // *** Show extra footer text in family screen ***
    $treetext = show_tree_text($dataDb->tree_id, $selected_language);
    echo $treetext['family_footer'];

    if ($user['group_user_notes_show'] == 'y') {
        $note_qry = "SELECT * FROM humo_user_notes WHERE note_tree_id='" . $tree_id . "'
            AND note_connect_kind='person' AND note_connect_id='" . $data["main_person"] . "'
            AND note_kind='user' AND note_status = 'approved'";
        $note_result = $dbh->query($note_qry);
        $num_rows = $note_result->rowCount();
    ?>
        <table align="center" class="humo">
            <tr class="humo_user_notes">
                <th>
                    <?php if ($num_rows) echo '<a href="#humo_user_notes"></a> '; ?>
                    <?= __('User notes'); ?>
                </th>
                <th colspan="2">
                    <?php
                    if ($num_rows) {
                        printf(__('There are %d user added notes.'), $num_rows);
                    } else {
                        printf(__('There are %d user added notes.'), 0);
                    }
                    ?>
                </th>
            </tr>

            <?php
            while ($noteDb = $note_result->fetch(PDO::FETCH_OBJ)) {
                $user_name = '';
                if ($noteDb->note_new_user_id) {
                    $user_qry = "SELECT * FROM humo_users WHERE user_id='" . $noteDb->note_new_user_id . "'";
                    $user_result = $dbh->query($user_qry);
                    $userDb = $user_result->fetch(PDO::FETCH_OBJ);
                    $user_name = $userDb->user_name;
                }
            ?>
                <tr>
                    <td valign="top">
                        <?= show_datetime($noteDb->note_new_datetime) . ' ' . $user_name; ?><br>
                    </td>
                    <td>
                        <?= nl2br($noteDb->note_note); ?>
                    </td>
                </tr>
            <?php } ?>
        </table><br>
        <?php
    }

    // *** User is allowed to add a note to a person in the family tree ***
    if ($user['group_user_notes'] == 'y' && is_numeric($_SESSION['user_id'])) {
        // *** Find user that adds a note ***
        $usersql = 'SELECT * FROM humo_users WHERE user_id="' . $_SESSION['user_id'] . '"';
        $user_note = $dbh->query($usersql);
        $userDb = $user_note->fetch(PDO::FETCH_OBJ);

        // *** Name of selected person in family tree ***
        $name = $parent1_cls->person_name($parent1Db);
        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
        $start_url = '';
        if (isset($parent1Db->pers_tree_id)) {
            $start_url = $parent1_cls->person_url2($parent1Db->pers_tree_id, $parent1Db->pers_famc, $parent1Db->pers_fams, $parent1Db->pers_gedcomnumber);
        }

        if (isset($_POST['send_mail'])) {
            // *** note_status show/ hide/ moderate options ***
            $sql = "INSERT INTO humo_user_notes SET
                note_new_user_id='" . safe_text_db($_SESSION['user_id']) . "',
                note_kind='user',
                note_note='" . safe_text_db($_POST["user_note"]) . "',
                note_connect_kind='person',
                note_connect_id='" . safe_text_db($data["main_person"]) . "',
                note_tree_id='" . $tree_id . "',
                note_names='" . safe_text_db($name["standard_name"]) . "';";
            $result = $dbh->query($sql);

            // *** Mail new user note to the administrator ***
            $register_address = $dataDb->tree_email;
            $register_subject = "HuMo-genealogy. " . __('New user note') . ": " . $userDb->user_name . "\n";

            // *** It's better to use plain text in the subject ***
            $register_subject = strip_tags($register_subject, ENT_QUOTES);

            $register_message = sprintf(__('Message sent through %s from the website.'), 'HuMo-genealogy');
            $register_message .= "<br><br>\n";
            $register_message .= __('New user note') . "<br>\n";
            $register_message .= __('Name') . ':' . $userDb->user_name . "<br>\n";
            //$register_message .=__('E-mail').": <a href='mailto:".$_POST['register_mail']."'>".$_POST['register_mail']."</a><br>\n";
            $register_message .= $_POST['user_note'] . "<br>\n";

            $vars['pers_family'] = $data["family_id"];
            $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
            $link .= "main_person=" . $data["main_person"];
            $register_message .= __('User note by family') . ': <a href="' . $link . '">' . safe_text_db($name["standard_name"]) . '</a>';

            include_once(__DIR__ . '/../include/mail.php');

            // *** Set who the message is to be sent from ***
            $mail->setFrom($userDb->user_mail, $userDb->user_name);
            // *** Set who the message is to be sent to ***
            $mail->addAddress($register_address, $register_address);
            // *** Set the subject line ***
            $mail->Subject = $register_subject;
            $mail->msgHTML($register_message);
            // *** Replace the plain text body with one created manually ***
            //$mail->AltBody = 'This is a plain-text message body';
            if (!$mail->send()) {
                //	echo '<br><b>'.__('Sending e-mail failed!').' '. $mail->ErrorInfo.'</b>';
                //} else {
                //	echo '<br><b>'.__('E-mail sent!').'</b><br>';
            }

            echo '<table align="center" class="humo">';
            echo '<tr><th><a name="add_info"></a>' . __('Your information is saved and will be reviewed by the webmaster.') . '</th></tr>';
            echo '</table>';
        } else {
        ?>
            <!-- Script voor expand and collapse of items -->
            <script>
                function hideShow(el_id) {
                    // *** Hide or show item ***
                    var arr = document.getElementsByName('row' + el_id);
                    for (i = 0; i < arr.length; i++) {
                        if (arr[i].style.display != "none") {
                            arr[i].style.display = "none";
                        } else {
                            arr[i].style.display = "";
                        }
                    }
                    // *** Change [+] into [-] or reverse ***
                    if (document.getElementById('hideshowlink' + el_id).innerHTML == "[+]")
                        document.getElementById('hideshowlink' + el_id).innerHTML = "[-]";
                    else
                        document.getElementById('hideshowlink' + el_id).innerHTML = "[+]";
                }
            </script>

            <form method="POST" action="<?= $start_url; ?>#add_info" style="display : inline;">
                <input type="hidden" name="id" value="<?= $data["family_id"]; ?>">
                <input type="hidden" name="main_person" value="<?= $data["main_person"]; ?>">
                <table align="center" class="humo" width="40%">
                    <tr id="add_info">
                        <th colspan="2">
                            <a href="<?= $start_url; ?>#add_info" onclick="hideShow(1);"><span id="hideshowlink1">[+]</span></a>
                            <?= ' ' . __('Add information or remarks'); ?>
                        </th>
                    </tr>

                    <tr style="display:none;" id="row1" name="row1">
                        <td><?= __('Person'); ?></td>
                        <td><?= $name["standard_name"]; ?></td>
                    </tr>

                    <tr style="display:none;" id="row1" name="row1">
                        <td><?= __('Name'); ?></td>
                        <td><?= $userDb->user_name; ?></td>
                    </tr>

                    <?php if ($userDb->user_mail == '') { ?>
                        <tr style="background-color:#FF6600; display:none;" id="row1" name="row1">
                            <td><?= __('E-mail address'); ?></td>
                            <td><?= __('Your e-mail address is missing. Please add you\'re mail address here: '); ?> <a href="user_settings.php"><?= __('Settings'); ?></a></td>
                        </tr>
                    <?php
                    }

                    $register_text = '';
                    if (isset($_POST['register_text'])) {
                        $register_text = $_POST['register_text'];
                    }
                    ?>
                    <tr style="display:none;" id="row1" name="row1">
                        <td><?= __('Text'); ?></td>
                        <td><textarea name="user_note" rows="5" cols="40"><?= $register_text; ?></textarea></td>
                    </tr>

                    <tr style="display:none;" id="row1" name="row1">
                        <td></td>
                        <td><input type="submit" name="send_mail" value="<?= __('Send'); ?>"></td>
                    </tr>
                </table>
            </form>
<?php
        }
    }
}
?>

<br>
<br>
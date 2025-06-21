<?php

/**
 * relations.php - checks relationships between person X and person Y
 *
 * Aug. 2010 written by Yossi Beck - for HuMo-genealogy
 * 2011 - 2023 adjusted for several languages by Yossi Beck
 * Feb. 2014 extended marital calculator added by Yossi Beck
 * Nov. 2023 prepare MVC model by Huub Mons.
 * Jan. 2025 changed variables into array variables to be used for MVC by Huub.
 *
 * contains the following functions:
 * create_rel_array      - creates $rel_array with GEDCOM nr and generation nr of ancestors of person X and Y
 * compare_rel_array     - compares the $rel_array arrays of X and Y to find common ancestor (can be the persons themselves)
 * calculate_rel         - if found, determines the nature of the relation (siblings, ancestors, nephews etc.)
 * calculate_ancestor    - calculates the degree of relations (2nd great-grandfather)
 * calculate_descendant  - calculates the degree of relations (3rd great-grandson)
 * calculate_nephews     - calculates the degree of relations (grand-niece)
 * calculate_uncles      - calculates the degree of relations (4th great-grand-uncle)
 * calculate_cousins     - calculates the degree of relations (2nd cousin twice removed)
 * search_marital        - if no direct blood relation found, searches for relation between spouses of person X and Y
 * display_table         - displays simple chart showing the found relationship
 * unset_var             - unsets the vital variables before searching marital relations
 * get_person            - retrieves person from MySQL database by GEDCOM nr
 * dutch_ancestor        - special algorithm to process complicated dutch terminology for distant ancestors
 *
 * Values of the $relation['relation_type'] variable (for displaying table with lineage if a match is found):
 * 1 = parent - child
 * 2 = child - parent
 * 3 = uncle - nephew
 * 4 = nephew - uncle
 * 5 = cousin
 * 6 = siblings
 * 7 = spouses or self
 *
 * Values of the $relation['spouse'] variable (flagging type of relationship check):
 * 0 = checks relation X vs Y
 * 1 = checks relation spouse of X versus person Y
 * 2 = checks relation person X versus spouse of Y
 * 3 = checks relation spouse of X versus spouse of Y
 *
 * Values in the genarray:
 * the genarray is an array of the ancestors of a base person (one of the two persons entered in the search or their spouses)
 * genarray[][0] = GEDCOM number of the person
 * genarray[][1] = number of generations (counted from base person)
 * genarray[][2] = array number of child
 *
 * Values other global variables:
 * $relation['double_spouse'] - flags situation where searched persons X and Y are both spouses of a third person
 * $relation['special_spouseX'] (and Y) - flags situation where the regular text "spouse of" has to be changed:
 * ----- for example: "X is spouse of brother of Y" should become "X is sister-in-law of Y"
 * $relation['sexe1'], $relation['sexe2'] - the sexe of persons 1 and 2
 * $relation["person1"], $relation["person2"] - GEDCOM nr of the searched persons X and Y
 * 
 * 
 * REFACTOR: There are multiple relation calculation methods:
 * 
 * Standard relation calculation:
 *   display_table($relation)
 * 
 * Marriage relation calculation:
 *   display_table($relation) ==>> called second time.
 * 
 * Extended relation calculation:
 *   extended_calculator($firstcall1, $firstcall2);
 *   ext_calc_join_path($workarr, $path2, $pers2, $ref);
 *   ext_calc_display_result($totalpath, $db_functions, $relation);
 */

// TODO create function to show person.
// TODO use a popup selection screen to select persons?

$person_privacy = new PersonPrivacy;
$person_name = new PersonName;

$limit = 500; // *** Limit results ***

//global $relation;
?>

<form method="POST" action="<?= $relation['rel_path']; ?>" style="display : inline;">
    <div class="p-2 me-sm-2 genealogy_search">
        <div class="row">
            <div class="col-md-auto"><br><b><?= __('Person') . ' 1'; ?></b></div>
            <div class="col-md-auto">
                <?= __('Name'); ?>
                <div class="input-group mb-3">
                    <input type="text" name="search_name" value="<?= safe_text_show($relation["search_name1"]); ?>" size="20" placeholder="<?= __('Name'); ?>" class="form-control form-control-sm">
                    <input type="submit" name="button_search_name1" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>

            <div class="col-md-auto">
                <?= __('or: ID'); ?>
                <div class="input-group mb-3">
                    <input type="text" name="search_gednr" value="<?= safe_text_show($relation["search_gednr1"]); ?>" size="8" class="form-control form-control-sm">
                    <input type="submit" name="button_search_id1" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>

            <div class="col-md-3">
                <?php
                if (isset($_SESSION["button_search_name1"]) && $_SESSION["button_search_name1"] == 1) {
                    $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id=" . $tree_id . " ORDER BY pers_lastname, pers_firstname LIMIT 0," . $limit;

                    if ($relation["search_name1"] != '') {
                        // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
                        $relation["search_name1"] = str_replace(' ', '%', $relation["search_name1"]);
                        // *** In case someone entered "Mons, Huub" using a comma ***
                        $relation["search_name1"] = str_replace(',', '', $relation["search_name1"]);
                        // *** August 2022: new query ***
                        $search_qry = "
                            SELECT * FROM humo_persons LEFT JOIN humo_events
                            ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id 
                            WHERE pers_tree_id='" . $tree_id . "' AND
                                (
                                CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_patronym,pers_lastname) LIKE '%" . safe_text_db($relation["search_name1"]) . "%'
                                OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%" . safe_text_db($relation["search_name1"]) . "%' 
                                OR CONCAT(pers_patronym,pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($relation["search_name1"]) . "%' 
                                OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%" . safe_text_db($relation["search_name1"]) . "%'
                                OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . safe_text_db($relation["search_name1"]) . "%'
                                OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%" . safe_text_db($relation["search_name1"]) . "%' 
                                OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($relation["search_name1"]) . "%' 
                                OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%" . safe_text_db($relation["search_name1"]) . "%'
                                )
                                GROUP BY pers_id, event_event, event_kind, event_id
                                ORDER BY pers_lastname, pers_firstname, CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) LIMIT 0," . $limit;
                    } elseif ($relation["search_gednr1"] != '') {
                        $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND (pers_gedcomnumber = '" . $relation["search_gednr1"] . "' OR pers_gedcomnumber = 'I" . $relation["search_gednr1"] . "')";
                    }

                    // *** Link from person pop-up menu ***
                    if (isset($_SESSION["search_pers_id"])) {
                        $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_id='" . $_SESSION["search_pers_id"] . "'";
                    }

                    $search_result = $dbh->query($search_qry);
                    if ($search_result) {
                        $number_results = $search_result->rowCount();
                        if ($number_results > 0) {
                ?>

                            <?= __('Pick a name from search results'); ?>

                            <select size="1" name="person1" class="form-select form-select-sm">
                                <?php
                                while ($searchDb = $search_result->fetch(PDO::FETCH_OBJ)) {
                                    $privacy = $person_privacy->get_privacy($searchDb);
                                    $name = $person_name->get_person_name($searchDb, $privacy);
                                    if ($name["show_name"]) {
                                        $birth = '';
                                        if ($searchDb->pers_bapt_date) {
                                            $birth = ' ' . __('~') . ' ' . date_place($searchDb->pers_bapt_date, '');
                                        }
                                        if ($searchDb->pers_birth_date) {
                                            $birth = ' ' . __('*') . ' ' . date_place($searchDb->pers_birth_date, '');
                                        }
                                        if ($person_privacy) {
                                            $birth = '';
                                        }

                                        $selected = '';
                                        if (isset($relation["person1"])) {
                                            if ($searchDb->pers_gedcomnumber == $relation["person1"] && !(isset($_POST["button_search_name1"]) && $relation["search_name1"] == '' && $relation["search_gednr1"] == '')) {
                                                $selected = 'selected';
                                            }
                                        }
                                ?>
                                        <option value="<?= $searchDb->pers_gedcomnumber; ?>" <?= $selected; ?>>
                                            <?= $name["index_name"] . $birth; ?> [<?= $searchDb->pers_gedcomnumber; ?>]
                                        </option>
                                    <?php
                                    }
                                }
                                // *** Simple test only, if number of results = limit then show message ***
                                if ($number_results == $limit) {
                                    ?>
                                    <option value=""><?= __('Results are limited, use search to find more persons.'); ?></option>
                                <?php } ?>
                            </select>
                        <?php } else { ?>
                            <select size="1" name="notfound" value="1" class="form-select form-select-sm">
                                <option><?= __('Person not found'); ?></option>
                            </select>
                <?php
                        }
                    }
                } ?>
            </div>
        </div>

        <!-- Second person -->
        <div class="row">
            <div class="col-md-auto"><br><b><?= __('Person') . ' 2'; ?></b></div>
            <div class="col-md-auto">
                <?= __('Search'); ?>
                <div class="input-group mb-3">
                    <input type="text" name="search_name2" value="<?= safe_text_show($relation["search_name2"]); ?>" size="20" placeholder="<?= __('Name'); ?>" class="form-control form-control-sm">
                    <input type="submit" name="button_search_name2" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>

            <div class="col-md-auto">
                <?= __('or: ID'); ?>
                <div class="input-group mb-3">
                    <input type="text" name="search_gednr2" value="<?= safe_text_show($relation["search_gednr2"]); ?>" size="8" class="form-control form-control-sm">
                    <input type="submit" name="button_search_id2" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>

            <div class="col-md-3">
                <?php
                if (isset($_SESSION["button_search_name2"]) && $_SESSION["button_search_name2"] == 1) {
                    $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id=" . $tree_id . " ORDER BY pers_lastname, pers_firstname LIMIT 0," . $limit;

                    if ($relation["search_name2"] != '') {
                        // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
                        $relation["search_name2"] = str_replace(' ', '%', $relation["search_name2"]);
                        // *** In case someone entered "Mons, Huub" using a comma ***
                        $relation["search_name2"] = str_replace(',', '', $relation["search_name2"]);
                        // *** August 2022: new query ***
                        $search_qry = "
                            SELECT * FROM humo_persons LEFT JOIN humo_events
                            ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id 
                            WHERE pers_tree_id='" . $tree_id . "' AND
                                (
                                CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_patronym,pers_lastname) LIKE '%" . safe_text_db($relation["search_name2"]) . "%'
                                OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%" . safe_text_db($relation["search_name2"]) . "%' 
                                OR CONCAT(pers_patronym,pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($relation["search_name2"]) . "%' 
                                OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%" . safe_text_db($relation["search_name2"]) . "%'
                                OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . safe_text_db($relation["search_name2"]) . "%'
                                OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%" . safe_text_db($relation["search_name2"]) . "%' 
                                OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($relation["search_name2"]) . "%' 
                                OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%" . safe_text_db($relation["search_name2"]) . "%'
                                )
                            GROUP BY pers_id, event_event, event_kind, event_id
                            ORDER BY pers_lastname, pers_firstname, CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) LIMIT 0," . $limit;
                    } elseif ($relation["search_gednr2"] != '') {
                        $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND (pers_gedcomnumber = '" . $relation["search_gednr2"] . "' OR pers_gedcomnumber = 'I" . $relation["search_gednr2"] . "')";
                    }

                    // *** Link from person pop-up menu ***
                    if (isset($_SESSION["search_pers_id2"])) {
                        $search_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_id='" . $_SESSION["search_pers_id2"] . "'";
                    }

                    $search_result2 = $dbh->query($search_qry);
                    if ($search_result2) {
                        $number_results = $search_result2->rowCount();
                        if ($number_results > 0) {
                ?>

                            <?= __('Pick a name from search results'); ?>

                            <select size="1" name="person2" class="form-select form-select-sm">
                                <?php
                                while ($searchDb2 = $search_result2->fetch(PDO::FETCH_OBJ)) {
                                    $privacy = $person_privacy->get_privacy($searchDb2);
                                    $name = $person_name->get_person_name($searchDb2, $privacy);
                                    if ($name["show_name"]) {
                                        $birth = '';
                                        if ($searchDb2->pers_bapt_date) {
                                            $birth = ' ' . __('~') . ' ' . date_place($searchDb2->pers_bapt_date, '');
                                        }
                                        if ($searchDb2->pers_birth_date) {
                                            $birth = ' ' . __('*') . ' ' . date_place($searchDb2->pers_birth_date, '');
                                        }
                                        if ($person_privacy) {
                                            $birth = '';
                                        }

                                        $selected = '';
                                        if (isset($relation["person2"]) && $searchDb2->pers_gedcomnumber == $relation["person2"] && !(isset($_POST["button_search_name2"]) && $relation["search_name2"] == '' && $relation["search_gednr2"] == '')) {
                                            $selected = 'selected';
                                        }
                                ?>
                                        <option value="<?= $searchDb2->pers_gedcomnumber; ?>" <?= $selected; ?>>
                                            <?= $name["index_name"] . $birth; ?> [<?= $searchDb2->pers_gedcomnumber; ?>]
                                        </option>
                                    <?php
                                    }
                                }
                                // *** Simple test only, if number of results = limit then show message ***
                                if ($number_results == $limit) {
                                    ?>
                                    <option value=""><?= __('Results are limited, use search to find more persons.'); ?></option>
                                <?php } ?>
                            </select>
                        <?php } else { ?>
                            <select size="1" name="notfound" value="1" class="form-select form-select-sm">
                                <option><?= __('Person not found'); ?></option>
                            </select>
                <?php
                        }
                    }
                } ?>
            </div>
        </div>

        <div class="row">
            <div class="col-md-1"></div>

            <div class="col-md-auto">
                <input type="submit" name="switch" value="<?= __('Switch persons'); ?>" class="btn btn-sm btn-secondary">
            </div>

            <div class="col-md-auto">
                <!-- Help popup. Remark: Bootstrap popover javascript in layout script. -->
                <style>
                    .popover {
                        max-width: 500px;
                    }

                    .popover-body {
                        height: 500px;
                        overflow-y: auto;
                    }
                </style>
                <?php $popup_text =  __('This calculator will find the following relationships:<br>
<ul><li>Any blood relationship between X and Y ("X is great-grandfather of Y", "X is 3rd cousin once removed of Y" etc.)</li>
<li>Blood relationship between the spouse of X and person Y ("X is spouse of 2nd cousin of Y", "X is son-in-law of Y")</li>
<li>Blood relationship between person X and the spouse of Y ("X is 2nd cousin of spouse of Y", "X is father-in-law of Y")</li>
<li>Blood relationship between spouse of X and spouse of Y ("X spouse of sister-in-law of Y" etc.)</li>
<li>Direct marital relation ("X is spouse of Y")</li></ul>
Directions for use:<br>
<ul><li>Enter first and/or last name (or part of names) in the search boxes and press "Search". Repeat this for person 1 and 2.</li>
<li>If more than 1 person is found, select the one you want from the search result pulldown box. Repeat this for person 1 and 2.</li>
<li>Now press the "Calculate relationships" button on the right.</li>
<li><b>TIP: when you click "search" with empty first <u>and</u> last name boxes you will get a list with all persons in the database. (May take a few seconds)</b></li></ul>'); ?>
                <?php $popup_text = str_replace('"', "'", $popup_text); ?>

                <button type="button" class="btn btn-sm btn-secondary" data-bs-html="true" data-bs-container="body" data-bs-toggle="popover" data-bs-placement="right" data-bs-content="<?= $popup_text; ?>">
                    <?= __('Help'); ?>
                </button>
            </div>

            <div class="col-md-auto">
                <input type="submit" name="calculator" value="<?= __('Calculate relationships'); ?>" class="btn btn-sm btn-success">
            </div>

        </div>
    </div>

    <!-- Show results for extended relationship calculator -->
    <?php if (isset($_POST["extended"]) || isset($_POST["next_path"])) { ?>
        <br>
        <table class="ext">
            <tr>
                <td>
                    <?php if ($relation['show_extended_message']) { ?>
                        <div class="alert alert-secondary" role="alert">
                            <strong><?= $relation['show_extended_message']; ?></strong>
                        </div>
                    <?php
                    }
                    if ($relation['totalpath']) {
                        ext_calc_display_result($relation['totalpath'], $db_functions, $relation);
                    }
                    ?>
                </td>
            </tr>
        </table>
    <?php
    }


    $relation_marital = $relation; // Stores original $relation array.
    $relation = $relation['standard_extended']; // Part of $relation that contains standard and extended relation calculation.


    if ($relation['start_calculation'] && !$relation['search_results']) {
    ?>
        <div class="alert alert-warning mt-3" role="alert">
            <?= __('You have to search and than choose Person 1 and Person 2 from the search result pulldown'); ?>
        </div>
    <?php
    }

    // *** Calculate bloodrelation if calculate button or switch button is pressed ***
    if ($relation['start_calculation'] && $relation['search_results']) {
    ?>
        <br>
        <table class="ext">
            <tr>
                <?php
                // *** Bloodrelationship ***
                if ($relation['rel_text']) {
                    $relation['bloodrel'] = true;
                    echo '<td style="padding-right:30px;vertical-align:text-top;">';
                ?>
                    <span class="fs-4"><?= __('Blood relationship'); ?></span><br><br>
                    <?php
                    if ($selected_language == "cn" && strpos($relation['rel_text'], "notext") !== false) {
                        // don't display text if relation can't be phrased  
                    } else {
                        if ($selected_language == "fi") {
                    ?>
                            <!-- who -->
                            Kuka:
                        <?php } ?>
                        &nbsp;&nbsp;<a class="relsearch" href="<?= $relation['link1']; ?>main_person=<?= $relation['rel_arrayX'][0][0]; ?>"><?= $relation['name1']; ?></a>
                        <?php if ($selected_language == "fi") { ?>
                            <!-- to whom -->
                            &nbsp;&nbsp;Kenelle:
                        <?php } else { ?>
                            <?= $relation['language_is'] . $relation['rel_text']; ?>
                        <?php } ?>
                        <a class="relsearch" href="<?= $relation['link2']; ?>main_person=<?= $relation['rel_arrayY'][0][0]; ?>">
                            <?= $relation['name2']; ?>
                        </a><?= $relation['rel_text_nor_dan']; ?><br>
                        <?= $relation['dutch_text']; ?>
                        <?php if ($selected_language == "fi") { ?>
                            Sukulaisuus tai muu suhde: <b><?= $relation['rel_text']; ?></b>
                        <?php } ?>
                        <hr style="width:100%;height:0.25em;color:darkblue;background-color:darkblue;">
                    <?php
                    }
                    $relation['bloodreltext'] = $relation['rel_text'];

                    display_table($relation);
                }

                /*
                * Marital relationship
                *
                * This part shows for example this relationship: Uncle <-> Wife of nephew.
                * Relation types
                * 3 = uncle - nephew
                * 4 = nephew - uncle
                * 5 = cousin
                * 6 = siblings
                */

                // TODO refactor. BUT: this part uses the same variables as the blood relationship.
                if ($relation['relation_type'] != 1 && $relation['relation_type'] != 2 && $relation['relation_type'] != 7) {
                    $relation['foundX_nr'] = '';
                    $relation['foundY_nr'] = '';
                    $relation['foundX_gen'] = '';
                    $relation['foundY_gen'] = '';
                    $relation['foundX_match'] = '';
                    $relation['foundY_match'] = '';
                    $relation['relation_type'] = '';
                    $relation['rel_text'] = '';
                    $relation['spouse'] = '';

                    //search_marital($selected_language); // Will return a new $relation['rel_text'].

                    // TEST
                    $relation = $relation_marital; // Reset $relation array to original values.


                    if ($relation['rel_text']) {
                        // notext is used in Chinese display if relation can't be worded.
                        // check if this involves a marriage or a partnership of any kind
                        $relmarriedX = 0;
                        if (isset($relation['famspouseX'])) {
                            $kindrel = $dbh->query("SELECT fam_kind FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $relation['famspouseX'] . "'");
                            $kindrelDb = $kindrel->fetch(PDO::FETCH_OBJ);
                            if (
                                isset($kindrelDb->fam_kind) &&
                                $kindrelDb->fam_kind != 'living together' &&
                                $kindrelDb->fam_kind != 'engaged' &&
                                $kindrelDb->fam_kind != 'homosexual' &&
                                $kindrelDb->fam_kind != 'unknown' &&
                                $kindrelDb->fam_kind != 'non-marital' &&
                                $kindrelDb->fam_kind != 'partners' &&
                                $kindrelDb->fam_kind != 'registered'
                            ) {
                                $relmarriedX = 1;  // use: husband or wife
                            } else {
                                $relmarriedX = 0;  // use: partner
                            }
                        }

                        $relmarriedY = 0;
                        if (isset($relation['famspouseY'])) {
                            $kindrel2 = $dbh->query("SELECT fam_kind FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $relation['famspouseY'] . "'");
                            $kindrel2Db = $kindrel2->fetch(PDO::FETCH_OBJ);
                            if (
                                isset($kindrel2Db->fam_kind) &&
                                $kindrel2Db->fam_kind != 'living together' &&
                                $kindrel2Db->fam_kind != 'engaged' &&
                                $kindrel2Db->fam_kind != 'homosexual' &&
                                $kindrel2Db->fam_kind != 'unknown' &&
                                $kindrel2Db->fam_kind != 'non-marital' &&
                                $kindrel2Db->fam_kind != 'partners' &&
                                $kindrel2Db->fam_kind != 'registered'
                            ) {
                                $relmarriedY = 1;  // use: husband or wife
                            } else {
                                $relmarriedY = 0;  // use: partner
                            }
                        }

                        if ($relation['bloodrel']) {
                            echo '</td><td style="padding-left:30px;border-left:2px solid #bbbbbb;vertical-align:text-top;">';
                        } else {
                            echo '<td>';
                        }
                    ?>

                        <span class="d-print-none">
                            <input type="submit" name="extended" value="<?= __('Use Extended Calculator'); ?>" class="btn btn-sm btn-success">
                        </span>
                        &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                        <span class="fs-4"><?= __('Marital relationship'); ?></span><br><br>

                        <?php
                        if ($relation['double_spouse'] == 1) {
                            // X and Y are both spouses of Z
                            $spouseidDb = $db_functions->get_person($relation['rel_arrayspouseX'][$relation['foundX_match']][0]);
                            $privacy = $person_privacy->get_privacy($spouseidDb);
                            $name = $person_name->get_person_name($spouseidDb, $privacy);
                            $spousename = $name["name"];
                        ?>

                            <span>&nbsp;&nbsp;
                                <a class="relsearch" href="<?= $relation['link1']; ?>main_person=<?= $relation['rel_arrayX'][0][0]; ?>"><?= $relation['name1']; ?></a>
                                <?= __('and'); ?>:
                                <a class='relsearch' href='<?= $relation['link2']; ?>main_person=<?= $relation['rel_arrayY'][0][0]; ?>'><?= $relation['name2']; ?></a>
                                <?php if ($relation['sexe1'] == "M") { ?>
                                    <?= __('are both husbands of'); ?>
                                <?php } else { ?>
                                    <?= __('are both wifes of'); ?>
                                <?php } ?>
                                <a href='<?= $relation['link2']; ?>main_person=<?= $relation['rel_arrayspouseX'][$relation['foundX_match']][0]; ?>'><?= $spousename; ?></a>
                            </span><br>

                            <?php
                        } elseif ($relation['rel_text'] != "notext") {
                            $spousetext1 = '';
                            $spousetext2 = '';
                            $finnish_spouse1 = '';
                            $finnish_spouse2 = '';

                            if (($relation['spouse'] == 1 && $relation['special_spouseX'] !== 1) || $relation['spouse'] == 3) {
                                if ($relmarriedX == 0 && $selected_language != "cn") {
                                    $spousetext1 = __('partner') . __(' of ');
                                    $finnish_spouse1 = __('partner');
                                } else {
                                    if ($relation['sexe1'] == 'M') {
                                        $spousetext1 = ' ' . __('husband of') . ' ';
                                        if ($selected_language == "fi") {
                                            $finnish_spouse1 = 'mies';
                                        }
                                        if ($selected_language == "cn") {
                                            // "A's wife is B"
                                            $spousetext1 = '妻子';
                                        }
                                    } else {
                                        $spousetext1 = ' ' . __('wife of') . ' ';
                                        if ($selected_language == "fi") {
                                            $finnish_spouse1 = 'vaimo';
                                        }
                                        if ($selected_language == "cn") {
                                            // "A's husband is B"
                                            $spousetext1 = '丈夫';
                                        }
                                    }
                                }
                            }
                            if (($relation['spouse'] == 2 || $relation['spouse'] == 3) && $relation['special_spouseY'] !== 1) {
                                if ($relmarriedY == 0 && $selected_language != "cn") {
                                    $spousetext2 = __('partner') . __(' of ');
                                    $finnish_spouse2 = __('partner');
                                } else {
                                    if ($relation['sexe2'] == 'M') {
                                        $spousetext2 = ' ' . __('wife of') . ' ';
                                        if ($selected_language == "fi") {
                                            $finnish_spouse2 = 'mies';
                                        }
                                        // yes - it's really husband cause the sentence goes differently
                                        if ($selected_language == "cn") {
                                            // "A's uncle's husband is B"
                                            $spousetext2 = '丈夫';
                                        }
                                    } else {
                                        $spousetext2 = ' ' . __('husband of') . ' ';
                                        if ($selected_language == "fi") {
                                            $finnish_spouse2 = 'vaimo';
                                        }
                                        // yes - it's really wife cause the sentence goes differently
                                        if ($selected_language == "cn") {
                                            // "A's uncle's wife is B"
                                            $spousetext2 = '妻子';
                                        }
                                    }
                                }
                            }

                            // very different phrasing for correct grammar
                            if ($selected_language == "fi") {
                            ?>
                                Kuka:
                                <span>
                                    &nbsp;&nbsp;
                                    <a class="relsearch" href="<?= $relation['link1']; ?>main_person=<?= $relation['rel_arrayX'][0][0]; ?>">
                                        <?= $relation['name1']; ?>
                                    </a>
                                    &nbsp;&nbsp;Kenelle:
                                    <a class='relsearch' href="<?= $relation['link2']; ?>main_person=<?= $relation['rel_arrayY'][0][0]; ?>">
                                        <?= $relation['name2']; ?>
                                    </a>
                                </span><br>
                                Sukulaisuus tai muu suhde:
                                <?php
                                if (!$relation['special_spouseX'] && !$relation['special_spouseY'] && $relation['relation_type'] != 7) {
                                    if ($spousetext2 != '' && $spousetext1 == '') {
                                ?>
                                        <!-- X is relative of spouse of Y -->
                                        (<a href="<?= $relation['link1']; ?>main_person=<?= $relation['rel_arrayX'][0][0]; ?>"><?= $relation['name1']; ?></a>
                                        - <?= $relation['spousenameY']; ?>):&nbsp;&nbsp;<?= $relation['rel_text']; ?><br>
                                        <?= $relation['spousenameY']; ?>, <?= $finnish_spouse2; ?>
                                        <a href="<?= $relation['link2']; ?>main_person=<?= $relation['rel_arrayY'][0][0]; ?>"><?= $relation['name2']; ?></a>
                                    <?php } elseif ($spousetext1 !== '' && $spousetext2 === '') { ?>
                                        <!-- X is spouse of relative of Y -->
                                        (<?= $relation['spousenameX']; ?> - <a href="<?= $relation['link2']; ?>main_person=<?= $relation['rel_arrayY'][0][0]; ?>"><?= $relation['name2']; ?></a>):&nbsp;&nbsp;<?= $relation['rel_text']; ?><br>
                                        <?= $relation['spousenameX']; ?>, <?= $finnish_spouse1; ?>
                                        <a href="<?= $relation['link1']; ?>main_person=<?= $relation['rel_arrayX'][0][0]; ?>"><?= $relation['name1']; ?></a>
                                    <?php
                                    } else {
                                        // X is spouse of relative of spouse of Y
                                        echo '(' . $relation['spousenameX'] . ' - ' . $relation['spousenameY'] . '):&nbsp;&nbsp;' . $relation['rel_text'] . '<br>';
                                        echo $relation['spousenameX'] . ', ' . $finnish_spouse1 . ' ';
                                        echo "<a href='" . $relation['link1'] . "main_person=" . $relation['rel_arrayX'][0][0] . "'>" . $relation['name1'] . "</a><br>";
                                        echo $relation['spousenameY'] . ', ' . $finnish_spouse2 . ' ';
                                        echo "<a href='" . $relation['link2'] . "main_person=" . $relation['rel_arrayY'][0][0] . "'>" . $relation['name2'] . "</a>";
                                    }
                                } elseif ($relation['special_spouseX'] || $relation['special_spouseY']) {
                                    ?>
                                    <!-- brother-in-law/sister-in-law/father-in-law/mother-in-law -->
                                    <b><?= $relation['rel_text']; ?></b><br>
                                <?php
                                } elseif ($relation['relation_type'] == 7) {
                                    if ($relmarriedX == 0 || $relmarriedY == 0) {
                                        echo '<b>' . __('partner') . '</b><br>';
                                    } else {
                                        echo '<b>' . $finnish_spouse1 . '</b><br>';
                                    }
                                }
                            }

                            else {
                                // Norwegian grammar...
                                if ($spousetext2 === '') {
                                    $relation['rel_text_nor_dan2'] = '';
                                } else {
                                    $relation['rel_text_nor_dan'] = '';
                                }
                                if ($selected_language == "cn") {
                                    if ($relation['rel_text'] == " ") { // A's husband/wife is B
                                        $relation['rel_text'] = "是";
                                    } else {
                                        mb_internal_encoding("UTF-8");
                                        if ($spousetext1 !== "" && $spousetext2 === "") {
                                            $spousetext1 .= '的';
                                        } elseif ($spousetext2 !== "" && $spousetext1 === "") {
                                            $relation['rel_text'] = mb_substr($relation['rel_text'], 0, -1) . '的';
                                            $spousetext2 .= '是';
                                        } elseif ($spousetext1 !== "" && $spousetext2 !== "") {
                                            $spousetext1 .= '的';
                                            $relation['rel_text'] = mb_substr($relation['rel_text'], 0, -1) . '的';
                                            $spousetext2 .= '是';
                                        }
                                    }
                                }
                                if ($relation['relation_type'] == 6 || $relation['relation_type'] == 7) {
                                    $relation['rel_text_nor_dan'] = '';
                                }
                                ?>

                                <span>
                                    &nbsp;&nbsp;
                                    <a class="relsearch" href="<?= $relation['link1']; ?>main_person=<?= $relation['rel_arrayX'][0][0]; ?>"><?= $relation['name1']; ?></a>
                                    <?= $relation['language_is'] . $spousetext1 . $relation['rel_text'] . $relation['rel_text_nor_dan2'] . $spousetext2; ?>
                                    <a class="relsearch" href="<?= $relation['link2']; ?>main_person=<?= $relation['rel_arrayY'][0][0]; ?>"><?= $relation['name2']; ?></a>
                                    <?= $relation['rel_text_nor_dan']; ?>
                                </span><br>
                        <?php
                            }
                        }
                        ?>

                        <hr style="width:100%;height:0.25em;color:darkblue;background-color:darkblue;">

                    <?php
                        display_table($relation);
                    }
                }

                if ($relation['rel_text'] == '') {
                    ?>
                    <td <?= $relation['bloodreltext'] ? 'style="width:60px"' : ''; ?>>&nbsp;</td>

                    <?php if ($relation['bloodreltext'] == '') { ?>
                        <td style="text-align:left;border-left:0px;padding:10px;vertical-align:text-top;width:800px">
                            <div style='font-weight:bold;'><?= __('No blood relation or direct marital relation found'); ?></div>
                        <?php } else { ?>
                        <td class="d-print-none" style="padding-left:50px;padding-right:10px;vertical-align:text-top;border-left:2px solid #bbbbbb;width:350px;">
                            <span class="fs-4"><?= __('Marital relationship'); ?></span><br><br>
                            <div style="font-weight:bold;margin-bottom:10px;"><?= __('No direct marital relation found'); ?></div>
                        <?php } ?>

                        <hr style="width:100%;height:0.25em;color:darkblue;background-color:darkblue;">

                        <?= __("You may wish to try finding a connection with the <span style='font-weight:bold'>Extended Marital Calculator</span> below.<br>
This will find connections that span over many marital relations and generations.<br>
Computing time will vary depending on the size of the tree and the distance between the two persons.<br>
For example, in a 10,000 person tree even the most distant persons will usually be found within 1-2 seconds.<br>
In a 75,000 person tree the most distant persons may take up to 8 sec to find."); ?><br><br>
                        <input type="submit" name="extended" value="<?= __('Perform extended marital calculation'); ?>" class="btn btn-sm btn-success">

                        </td>
                    <?php } else { ?>
                        </td>
                    <?php } ?>
            </tr>
        </table>
    <?php } ?>
</form>
<br><br><br>

<?php
/* displays result of extended marital calculator */
function ext_calc_display_result($result, $db_functions, $relation)
{
    // $result holds the entire track of persons from person 1 to person 2
    // this string is made up of items sperated by ";"
    // each items starts with "par" (parent), "chd" (child) or "spo" (spouse), followed by the gedcomnumber of the person
    // example: parI232;parI65;chdI2304;spoI212;parI304
    // the par-chd-spo prefixes indicate if the person was called up by his parent, child or spouse so we can later create the graphical display

    $person_privacy = new PersonPrivacy;
    $person_name = new PersonName;

    $map = array();    // array that will hold all data needed for the graphical display
    $tracks = explode(";", $result); // $tracks is array with each person in the trail

    /* initialize  */
    for ($x = 0; $x < count($tracks); $x++) {
        $map[$x][0] = "1"; /* x value in graphical display */
        $map[$x][1] = "1"; /* y value in graphical display */
        $map[$x][2] = "1"; /* colspan value (usually 1, turns 2 for two column parent) */
        $map[$x][3] = substr($tracks[$x], 0, 3); /* call value (oar, chd, spo) */
        $map[$x][4] = substr($tracks[$x], 3); /* gedcomnumber value */
    }

    $xval = 1;
    $yval = 1;
    $miny = 1;
    $maxy = 1;
    $marrsign = array();

    // fill map array
    for ($x = 0; $x < count($tracks); $x++) {
        //$ged = substr($tracks[$x], 3);    // gedcomnumber
        $cal = substr($tracks[$x], 0, 3);  // par, chd, spo
        if ($cal === "fst") {
            continue;
        }
        if ($cal === "spo") {
            $marrsign[$xval + 1] = $yval;
            $xval += 2;
            $map[$x][0] = $xval;
            $map[$x][1] = $yval;
        }
        if ($cal === "chd") {
            $yval--;
            if ($yval < $miny) {
                $miny = $yval;
            }
            $map[$x][0] = $xval;
            $map[$x][1] = $yval;
            if (isset($map[$x + 1]) && $map[$x + 1][3] === "par") {
                $map[$x][2] = 2;
            }
        }
        if ($cal === "par") {
            $yval++;
            if ($yval > $maxy) {
                $maxy = $yval;
            }
            if ($map[$x - 1][3] === "chd") {
                $xval++;
            }
            $map[$x][0] = $xval;
            $map[$x][1] = $yval;
        }
    }
    if ($miny < 1) {
        for ($x = 0; $x < count($map); $x++) {
            $map[$x][1] += (1 + abs($miny));
            if ($map[$x][1] > $maxy)    $maxy = $map[$x][1];
        }
        if (isset($marrsign)) {
            foreach ($marrsign as $key => $value) {
                $marrsign[$key] += (1 + abs($miny));
            }
        }
    }
?>

    <div class="d-print-none" style="padding:3px;width:auto;">
        <input type="submit" name="next_path" value="<?= __('Try to find another path'); ?>" class="btn btn-sm btn-success">
        &nbsp;&nbsp;<?= __('With each consecutive search the path may get longer and computing time may increase!'); ?>
    </div>

    <!-- the following code displays the graphical view of the found trail -->
    <br>
    <table style="border:0px;border-collapse:separate;border-spacing:30px 1px;">
        <?php for ($a = 1; $a <= $maxy; $a++) { ?>
            <tr>
                <?php
                $next_line = [];
                for ($b = 1; $b <= $xval; $b++) {
                    $colsp = false;
                    for ($x = 0; $x < count($map); $x++) {
                        if ($map[$x][0] == $b && $map[$x][1] == $a) {
                            $ancDb = $db_functions->get_person($map[$x][4]);

                            $border = "border:1px solid #777777;";
                            // person A and B (first and last) get thicker border
                            if ($map[$x][4] == $relation["person1"] || $map[$x][4] == $relation["person2"]) {
                                $border = "border:2px solid #666666;";
                            }

                            $privacy = $person_privacy->get_privacy($ancDb);
                            $name = $person_name->get_person_name($ancDb, $privacy);

                            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                            $person_link = new PersonLink();
                            $url = $person_link->get_person_link($ancDb);

                            $colsp = true;

                            if ($map[$x][2] == 2) {
                                $b++;
                                $next_line[] = "&#8593;";   // up arrows under two column parent
                                $next_line[] = "&#8595;";   // down arrows under two column parent
                            } elseif (isset($map[$x + 1][3]) && $map[$x + 1][3] === "par") {
                                $next_line[] = "&#8595;";  // arrow down
                            } elseif (isset($map[$x][3]) && $map[$x][3] === "chd") {
                                $next_line[] = "&#8593;"; // arrow up
                            } else {
                                $next_line[] = "&nbsp;";  // empty box
                            }
                ?>

                            <td class="<?= $ancDb->pers_sexe ==  'M' ? 'extended_man' : 'extended_woman'; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>" <?= $map[$x][2] == 2 ? 'colspan=2' : ''; ?>>
                                <a href="<?= $url; ?>"><?= $name["name"]; ?></a>
                            </td>
                        <?php
                        }
                    }

                    // display the X sign between two married people
                    if ($colsp == false) {
                        $next_line[] = "&nbsp;";
                        ?>
                        <td style="font-weight:bold;font-size:130%;width:10px;text-align:center;border:0px;padding:0px">
                            <?php if (isset($marrsign[$b]) && $marrsign[$b] == $a) { ?>
                                X
                            <?php } ?>
                        </td>
                <?php
                    }
                }
                ?>
            </tr>

            <!-- The following code places a row with arrows (or blanks) under a row with name boxes -->
            <?php if ($a != $maxy) { ?>
                <tr>
                    <?php foreach ($next_line as $value) { ?>
                        <td style='padding:2px;color:black;width:10px;font-weight:bold;font-size:140%;text-align:center;'>
                            <?= $value; ?>
                        </td>
                    <?php } ?>
                </tr>
        <?php
            }
        }
        ?>
    </table>
    <?php
}


// *** Show calculated relation ***
function display_table($relation)
{
    global $db_functions, $tree_id, $link_cls, $uri_path;

    $person_privacy = new PersonPrivacy;
    $person_name = new PersonName;

    $vars['pers_family'] = $relation['famspouseX'];
    $linkSpouseX = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

    $vars['pers_family'] = $relation['famspouseY'];
    $linkSpouseY = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

    //$border="border:1px solid #777777;";
    $border = "";

    if ($relation['relation_type'] == 1 || $relation['relation_type'] == 2) {
        if ($relation['relation_type'] == 1 && $relation['foundY_gen'] == 1 && $relation['spouse'] == '') {
            // father-son - no need for table
        } elseif ($relation['relation_type'] == 2 && $relation['foundX_gen'] == 1 && $relation['spouse'] == '') {
            // son-father - no need for table
        } else {
            if ($relation['spouse'] == 1) {
                $relation['rel_arrayX'] = $relation['rel_arrayspouseX'];
            }
            if ($relation['spouse'] == 2) {
                $relation['rel_arrayY'] = $relation['rel_arrayspouseY'];
            }
            if ($relation['spouse'] == 3) {
                $relation['rel_arrayX'] = $relation['rel_arrayspouseX'];
                $relation['rel_arrayY'] = $relation['rel_arrayspouseY'];
            }

            if ($relation['relation_type'] == 2) {
                $tempfound = $relation['foundY_nr'];
                $relation['foundY_nr'] = $relation['foundX_nr'];
                $relation['foundX_nr'] = $tempfound;

                $temprel = $relation['rel_arrayY'];
                $relation['rel_arrayY'] = $relation['rel_arrayX'];
                $relation['rel_arrayX'] = $temprel;

                $tempname = $relation['name1'];
                $relation['name1'] = $relation['name2'];
                $relation['name2'] = $tempname;

                $tempfam = $relation['famspouseX'];
                $relation['famspouseX'] = $relation['famspouseY'];
                $relation['famspouseY'] = $tempfam;

                $tempfamily = $relation['family_id1'];
                $relation['family_id1'] = $relation['family_id2'];
                $relation['family_id2'] = $tempfamily;

                $tempged = $relation['gednr2'];
                $relation['gednr2'] = $relation['gednr1'];
                $relation['gednr1'] = $tempged;
            }
    ?>

            <br>
            <table class="newrel" style="border:0px;border-collapse:separate;border-spacing:3px 1px;">
                <?php
                $persidDb = $db_functions->get_person($relation['rel_arrayX'][0][0]);
                $privacy = $person_privacy->get_privacy($persidDb);
                $name = $person_name->get_person_name($persidDb, $privacy);
                if (($relation['spouse'] == 1 && $relation['relation_type'] == 1) || ($relation['spouse'] == 2 && $relation['relation_type'] == 2) || $relation['spouse'] == 3) {
                ?>
                    <tr>
                        <td class="<?= $persidDb->pers_sexe == "M" ?  "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>">
                            <a href="<?= $linkSpouseX; ?>main_person=<?= $relation['rel_arrayX'][0][0]; ?>"><?= $name["name"]; ?></a>
                        </td>

                        <td style="border:0px;">&nbsp;&nbsp;X&nbsp;&nbsp;</td>

                        <td class="<?= $relation['sexe1'] == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>">
                            <a class="search" href="<?= $relation['link1']; ?>main_person=<?= $relation['gednr1']; ?>"><?= $relation['name1']; ?></a>
                        </td>
                    </tr>

                    <tr>
                        <td style="border:0px;">&#8593;</td> <!-- arrow up -->
                        <td style="border:0px;">&nbsp;</td>
                        <td style="border:0px;">&nbsp;</td>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td class="<?= $persidDb->pers_sexe == "M" ?  "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>">
                            <a class="search" href="<?= $relation['link1']; ?>main_person=<?= $relation['rel_arrayX'][0][0]; ?>"><?= $name["name"]; ?></a>
                        </td>
                        <?php if (($relation['spouse'] == 1 && $relation['relation_type'] == 2) || ($relation['spouse'] == 2 && $relation['relation_type'] == 1)) { ?>
                            <td style="border:0px;">&nbsp;</td>
                            <td style="border:0px;">&nbsp;</td>
                        <?php } ?>
                    </tr>

                    <tr>
                        <td style="border:0px;">&#8595;</td> <!-- arrow down -->
                    </tr>
                <?php } ?>

                <?php
                $count = $relation['foundY_nr'];
                while ($count != 0) {
                    $persidDb = $db_functions->get_person($relation['rel_arrayY'][$count][0]);
                    $privacy = $person_privacy->get_privacy($persidDb);
                    $name = $person_name->get_person_name($persidDb, $privacy);

                    if ($persidDb->pers_fams) {
                        $fams = $persidDb->pers_fams;
                        $tempfam = explode(";", $fams);
                        $fam = $tempfam[0];
                    } else {
                        $fam = $persidDb->pers_famc;
                    }
                    $vars['pers_family'] = $fam;
                    $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

                    $count = $relation['rel_arrayY'][$count][2];
                ?>
                    <tr>
                        <td class="<?= $persidDb->pers_sexe == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>">
                            <a href="<?= $link; ?>main_person=<?= $persidDb->pers_gedcomnumber; ?>"><?= $name["name"]; ?></a>
                        </td>

                        <?php if ($relation['spouse'] == 1 || $relation['spouse'] == 2 || $relation['spouse'] == 3) { ?>
                            <td style="border:0px;">&nbsp;</td>
                        <?php } ?>
                    </tr>

                    <tr>
                        <td style="border:0px;">&#8593;</td> <!-- arrow up -->
                    <?php } ?>
                    <!-- TODO check this code -->
                    </tr>

                    <tr>
                        <?php
                        $persidDb = $db_functions->get_person($relation['rel_arrayY'][0][0]);
                        $privacy = $person_privacy->get_privacy($persidDb);
                        $name = $person_name->get_person_name($persidDb, $privacy);
                        if ($relation['spouse'] == 1 && $relation['relation_type'] == 2 || $relation['spouse'] == 2 && $relation['relation_type'] == 1 || $relation['spouse'] == 3) {
                        ?>
                            <td class="<?= $persidDb->pers_sexe == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>">
                                <a href="<?= $linkSpouseY; ?>main_person=<?= $relation['rel_arrayY'][0][0]; ?>"><?= $name["name"]; ?></a>
                            </td>

                            <td style="border:0px;">&nbsp;&nbsp;X&nbsp;&nbsp;</td>

                            <td class="<?= $relation['sexe2'] == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>">
                                <a class="search" href="<?= $relation['link2']; ?>main_person=<?= $relation['gednr2']; ?>">
                                    <?= $relation['name2']; ?>
                                </a>
                            </td>
                        <?php } else { ?>
                            <td class="<?= $persidDb->pers_sexe == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>">
                                <a class="search" href="<?= $relation['link2']; ?>main_person=<?= $relation['rel_arrayY'][0][0]; ?>">
                                    <!-- TODO check this, should be $name["name"]? -->
                                    <?= $relation['name2']; ?>
                                </a>
                            </td>
                            <?php if ($relation['spouse'] == 1 && $relation['relation_type'] == 1 || $relation['spouse'] == 2 && $relation['relation_type'] == 2) { ?>
                                <td style="border:0px;">&nbsp;</td>
                                <td style="border:0px;">&nbsp;</td>
                        <?php
                            }
                        }
                        ?>
                    </tr>
            </table>
        <?php
        }
    }

    if ($relation['relation_type'] == 3 || $relation['relation_type'] == 4 || $relation['relation_type'] == 5 || $relation['relation_type'] == 6) {
        $rowcount = max($relation['foundX_gen'], $relation['foundY_gen']);
        $countX = $relation['foundX_nr'];
        $countY = $relation['foundY_nr'];
        $name1_done = 0;
        $name2_done = 0;

        $colspan = 3;
        if ($relation['spouse'] == 1) {
            $relation['rel_arrayX'] = $relation['rel_arrayspouseX'];
        }
        if ($relation['spouse'] == 2) {
            $relation['rel_arrayY'] = $relation['rel_arrayspouseY'];
        }
        if ($relation['spouse'] == 3) {
            $relation['rel_arrayX'] = $relation['rel_arrayspouseX'];
            $relation['rel_arrayY'] = $relation['rel_arrayspouseY'];
        }

        $persidDb = $db_functions->get_person($relation['rel_arrayX'][$relation['foundX_match']][0]);
        $privacy = $person_privacy->get_privacy($persidDb);
        $name = $person_name->get_person_name($persidDb, $privacy);

        if ($persidDb->pers_fams) {
            $fams = $persidDb->pers_fams;
            $tempfam = explode(";", $fams);
            $fam = $tempfam[0];
        } else {
            $fam = $persidDb->pers_famc;
        }
        $vars['pers_family'] = $fam;
        $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
        ?>

        <br>
        <table class="newrel" style="border-collapse:separate;border-spacing:3px 1px;">
            <tr>
                <?php if ($relation['spouse'] == 1 || $relation['spouse'] == 3) { ?>
                    <td style="border:0px;">&nbsp;</td>
                    <td style="border:0px;">&nbsp;</td>
                <?php } ?>

                <td class="<?= $persidDb->pers_sexe == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>" colspan="<?= $colspan; ?>">
                    <a href="<?= $link; ?>main_person=<?= $persidDb->pers_gedcomnumber; ?>"><?= $name["name"]; ?></a>
                </td>

                <?php if ($relation['spouse'] == 2 || $relation['spouse'] == 3) { ?>
                    <td style="border:0px;">&nbsp;</td>
                    <td style="border:0px;">&nbsp;</td>
                <?php } ?>
            </tr>

            <tr>
                <?php if ($relation['spouse'] == 1 || $relation['spouse'] == 3) { ?>
                    <td style="border:0px;">&nbsp;</td>
                    <td style="border:0px;">&nbsp;</td>
                <?php } ?>
                <td style="border:0px;">&#8593;</td> <!-- arrow up -->
                <td style="border:0px;">&nbsp;</td>
                <td style="border:0px;">&#8595;</td> <!-- arrow down -->
                <?php if ($relation['spouse'] == 2 || $relation['spouse'] == 3) { ?>
                    <td style="border:0px;">&nbsp;</td>
                    <td style="border:0px;">&nbsp;</td>
                <?php } ?>
            </tr>

            <?php for ($e = 1; $e <= $rowcount; $e++) { ?>
                <tr>
                    <?php
                    if ($countX != 0) {
                        $persidDb = $db_functions->get_person($relation['rel_arrayX'][$countX][0]);
                        $privacy = $person_privacy->get_privacy($persidDb);
                        $name = $person_name->get_person_name($persidDb, $privacy);

                        if ($relation['spouse'] == 1 || $relation['spouse'] == 3) {
                    ?>
                            <td style="border:0px;">&nbsp;</td>
                            <td style="border:0px;">&nbsp;</td>
                        <?php
                        }
                        if ($persidDb->pers_fams) {
                            $fams = $persidDb->pers_fams;
                            $tempfam = explode(";", $fams);
                            $fam = $tempfam[0];
                        } else {
                            $fam = $persidDb->pers_famc;
                        }
                        $vars['pers_family'] = $fam;
                        $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
                        ?>
                        <td class="<?= $persidDb->pers_sexe == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>">
                            <a href="<?= $link; ?>main_person=<?= $persidDb->pers_gedcomnumber; ?>"><?= $name["name"]; ?></a>
                        </td>

                        <?php
                        $countX = $relation['rel_arrayX'][$countX][2];
                    } elseif ($name1_done == 0) {
                        if ($relation['spouse'] == 1 || $relation['spouse'] == 3) {
                            $persidDb = $db_functions->get_person($relation['rel_arrayX'][0][0]);
                            $privacy = $person_privacy->get_privacy($persidDb);
                            $name = $person_name->get_person_name($persidDb, $privacy);

                            if ($persidDb->pers_fams) {
                                $fams = $persidDb->pers_fams;
                                $tempfam = explode(";", $fams);
                                $fam = $tempfam[0];
                            } else {
                                $fam = $persidDb->pers_famc;
                            }

                        ?>
                            <td class="<?= $relation['sexe1'] == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px">
                                <a class="search" href="<?= $relation['link1']; ?>main_person=<?= $relation['gednr1']; ?>"><?= $relation['name1']; ?></a>
                            </td>

                            <td style="border:0px;">&nbsp;&nbsp;X&nbsp;&nbsp;</td>

                            <td class="<?= $persidDb->pers_sexe == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px">
                                <a href="<?= $relation['fam_path'] . $fam; ?>&amp;main_person=<?= $persidDb->pers_gedcomnumber; ?>"><?= $name["name"]; ?></a>
                            </td>

                        <?php } else { ?>

                            <td class="<?= $relation['sexe1'] == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px">
                                <a class="search" href="<?= $relation['link1']; ?>main_person=<?= $relation['gednr1']; ?>"><?= $relation['name1']; ?></a>
                            </td>
                        <?php
                        }
                        $name1_done = 1;
                    } else {
                        if ($relation['spouse'] == 1 || $relation['spouse'] == 3) {
                        ?>
                            <td style="border:0px;">&nbsp;</td>
                            <td style="border:0px;">&nbsp;</td>
                        <?php } ?>
                        <td style="border:0px;">&nbsp;</td>
                    <?php
                    }

                    if ($countY != 0) {
                        $persidDb = $db_functions->get_person($relation['rel_arrayY'][$countY][0]);
                        $privacy = $person_privacy->get_privacy($persidDb);
                        $name = $person_name->get_person_name($persidDb, $privacy);

                        if ($persidDb->pers_fams) {
                            $fams = $persidDb->pers_fams;
                            $tempfam = explode(";", $fams);
                            $fam = $tempfam[0];
                        } else {
                            $fam = $persidDb->pers_famc;
                        }
                        $vars['pers_family'] = $fam;
                        $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
                    ?>
                        <td style="border:0px;width:70px">&nbsp;</td>

                        <td class="<?= $persidDb->pers_sexe == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;<?= $border; ?>">
                            <a href="<?= $link; ?>main_person=<?= $persidDb->pers_gedcomnumber; ?>"><?= $name["name"]; ?></a>
                        </td>

                        <?php if ($relation['spouse'] == 2 || $relation['spouse'] == 3) { ?>
                            <td style="border:0px;">&nbsp;</td>
                            <td style="border:0px;">&nbsp;</td>
                        <?php
                        }
                        $countY = $relation['rel_arrayY'][$countY][2];
                    } elseif ($name2_done == 0) {
                        if ($relation['spouse'] == 2 || $relation['spouse'] == 3) {
                            $persidDb = $db_functions->get_person($relation['rel_arrayY'][0][0]);
                            $privacy = $person_privacy->get_privacy($persidDb);
                            $name = $person_name->get_person_name($persidDb, $privacy);

                            if ($persidDb->pers_fams) {
                                $fams = $persidDb->pers_fams;
                                $tempfam = explode(";", $fams);
                                $fam = $tempfam[0];
                            } else {
                                $fam = $persidDb->pers_famc;
                            }
                            $vars['pers_family'] = $fam;
                            $link = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
                        ?>
                            <td style="border:0px;width:70px">&nbsp;</td>

                            <td class="<?= $persidDb->pers_sexe == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;">
                                <a href="<?= $link; ?>main_person=<?= $persidDb->pers_gedcomnumber; ?>"><?= $name["name"]; ?></a>
                            </td>

                            <td style="border:0px;">&nbsp;&nbsp;X&nbsp;&nbsp;</td>

                            <td class="<?= $relation['sexe2'] == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;">
                                <a class="search" href="<?= $relation['link2']; ?>main_person=<?= $relation['gednr2']; ?>"><?= $relation['name2']; ?></a>
                            </td>

                        <?php } else { ?>
                            <td style="border:0px;width:70px">&nbsp;</td>

                            <td class="<?= $relation['sexe2'] == "M" ? "extended_man" : "extended_woman"; ?>" style="width:200px;text-align:center;padding:2px;">
                                <a class="search" href="<?= $relation['link2']; ?>main_person=<?= $relation['gednr2']; ?>"><?= $relation['name2']; ?></a>
                            </td>
                        <?php
                        }
                        $name2_done = 1;
                    } else {
                        ?>
                        <td style="border:0px;width:70px;">&nbsp;</td>
                        <td style="border:0px;">&nbsp;</td>
                        <?php if ($relation['spouse'] == 2 || $relation['spouse'] == 3) { ?>
                            <td style="border:0px;">&nbsp;</td>
                            <td style="border:0px;">&nbsp;</td>
                    <?php
                        }
                    }
                    ?>
                </tr>

                <tr>
                    <?php if ($relation['spouse'] == 1 || $relation['spouse'] == 3) { ?>
                        <td style="border:0px;">&nbsp;</td>
                        <td style="width:50px;border:0px;">&nbsp;</td>
                    <?php
                    }

                    if ($name1_done == 0) {
                    ?>
                        <td style="border:0px;">&#8593;</td> <!-- arrow up -->
                    <?php } else { ?>
                        <td style="border:0px;">&nbsp;</td>
                    <?php } ?>

                    <td style="border:0px;">&nbsp;</td>

                    <?php if ($name2_done == 0) { ?>
                        <td style="border:0px;">&#8595;</td> <!-- arrow down -->
                    <?php } else { ?>
                        <td style="border:0px;">&nbsp;</td>
                    <?php
                    }

                    if ($relation['spouse'] == 2 || $relation['spouse'] == 3) {
                    ?>
                        <td style="border:0px;">&nbsp;</td>
                        <td style="border:0px;">&nbsp;</td>
                    <?php } ?>
                </tr>
            <?php } ?>
        </table>
<?php
    }
}

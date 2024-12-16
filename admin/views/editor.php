<?php

/**
 * This is the editor file for HuMo-genealogy.
 *
 * If you are reading this in your web browser, your server is probably
 * not configured correctly to run PHP applications!
 *
 * See the manual for basic setup instructions
 *
 * https://humo-gen.com
 *
 * Copyright (C) 2008-2024 Huub Mons,
 * Klaas de Winkel, Jan Maat, Jeroen Beemster, Louis Ywema, Theo Huitema,
 * René Janssen, Yossi Beck
 * and others.
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}
?>

<!-- Only use Save button, don't use [Enter] -->
<script>
    $(document).on("keypress", ":input:not(textarea)", function(event) {
        return event.keyCode != 13;
    });
</script>

<?php
$path_prefix = '../';

$editor_cls = new Editor_cls; // TODO editor_cls is also added in controller.
$EditorEvent = new EditorEvent;

// *** Temp variables ***
$pers_gedcomnumber = $editor['pers_gedcomnumber']; // *** Temp variable ***
$marriage = $editor['marriage']; // *** Temp variable ***

//TEST
//include (__DIR__.'/../include/editor_sources.php');

// TODO move items from editor_inc.php to model and view scripts.
// *** Process queries ***
include_once(__DIR__ . "/../include/editor_inc.php");

$person_found = true;
?>

<div class="p-3 m-2 genealogy_search">
    <?php if ($editor['new_tree'] == false) { ?>
        <div class="row mb-2">
            <div class="col-md-3">
                <form method="POST" action="index.php?menu_tab=person" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <div class="input-group input-group-sm">
                        <label for="favourites" class="input-group-text"><img src="../images/favorite_blue.png">&nbsp;</label>

                        <select size="1" name="person" onChange="this.form.submit();" class="form-select form-select-sm">
                            <option value=""><?= __('Favourites list'); ?></option>
                            <?php while ($favDb = $editor['favorites']->fetch(PDO::FETCH_OBJ)) { ?>
                                <option value="<?= $favDb->setting_value; ?>"><?= $editor_cls->show_selected_person($favDb); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </form>
            </div>

            <div class="col-md-3">
                <form method="POST" action="index.php?menu_tab=person" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <select size="1" name="person" onChange="this.form.submit();" class="form-select form-select-sm">
                        <option value=""><?= __('Latest changes'); ?></option>
                        <?php
                        if (isset($pers_id)) {
                            $counter = count($pers_id);
                            for ($i = 0; $i < $counter; $i++) {
                                $person2_qry = "SELECT * FROM humo_persons WHERE pers_id='" . $pers_id[$i] . "'";
                                $person2_result = $dbh->query($person2_qry);
                                $person2 = $person2_result->fetch(PDO::FETCH_OBJ);
                                if ($person2) {
                                    $pers_user = '';
                                    if ($person2->pers_new_user_id) {
                                        // TODO improve query using join.
                                        $user_qry = "SELECT user_name FROM humo_users WHERE user_id='" . $person2->pers_new_user_id . "'";
                                        $user_result = $dbh->query($user_qry);
                                        $userDb = $user_result->fetch(PDO::FETCH_OBJ);
                                        $user_name = $userDb->user_name;

                                        $pers_user = ' [' . __('Added by') . ': ' . $user_name . ']';
                                    } elseif ($person2->pers_changed_user_id) {
                                        // TODO improve query using join.
                                        $user_qry = "SELECT user_name FROM humo_users WHERE user_id='" . $person2->pers_changed_user_id . "'";
                                        $user_result = $dbh->query($user_qry);
                                        $userDb = $user_result->fetch(PDO::FETCH_OBJ);
                                        $user_name = $userDb->user_name;

                                        $pers_user = ' [' . __('Changed by') . ': ' . $user_name . ']';
                                    }
                        ?>
                                    <option value="<?= $person2->pers_gedcomnumber; ?>"><?= $editor_cls->show_selected_person($person2) . $pers_user; ?></option>
                        <?php
                                }
                            }
                        }
                        ?>
                    </select>
                </form>
            </div>

        </div>
    <?php } ?>

    <?php if ($editor['new_tree'] == false) { ?>
        <div class="row">

            <div class="col-md-3">
                <?= select_tree($dbh, $page, $tree_id); ?>
            </div>

            <div class="col-md-auto">
                <form method="POST" action="index.php?menu_tab=person" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <div class="input-group">
                        <!-- Search persons firstname/ lastname -->
                        <label for="search_name" class="col-auto col-form-label col-form-label-sm"><?= __('Person'); ?>:&nbsp;</label>
                        <input type="text" name="search_quicksearch" id="search_name" class="form-control form-control-sm" placeholder="<?= __('Name'); ?>" value="<?= $editor['search_name']; ?>" size="15">
                        <input type="submit" class="btn btn-sm btn-secondary" value="<?= __('Search'); ?>">
                    </div>
                </form>
            </div>

            <!-- <div class="col-auto"> -->
            <?php
            unset($person_result);
            if ($editor['search_name'] != '') {
                // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
                $search_name = str_replace(' ', '%', $editor['search_name']);

                // *** In case someone entered "Mons, Huub" using a comma ***
                $search_name = str_replace(',', '', $search_name);

                // *** December 2021: removed pers_callname from query ***
                // *** January added by Chris: GROUP BY event_id. Otherwise no results in some cases? ***
                $person_qry = "
                    SELECT * FROM humo_persons
                    LEFT JOIN humo_events
                    ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id 
                    WHERE pers_tree_id='" . $tree_id . "' AND
                        (
                        CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_patronym,pers_lastname) LIKE '%" . safe_text_db($search_name) . "%'
                        OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%" . safe_text_db($search_name) . "%' 
                        OR CONCAT(pers_patronym,pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($search_name) . "%' 
                        OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%" . safe_text_db($search_name) . "%'
                        OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . safe_text_db($search_name) . "%'
                        OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%" . safe_text_db($search_name) . "%' 
                        OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($search_name) . "%' 
                        OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%" . safe_text_db($search_name) . "%'
                        )
                        GROUP BY pers_id
                        ORDER BY pers_lastname, pers_firstname, CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED)
                    ";

                // Next line was before ORDER BY line. Doesn't work if only_full_group is disabled
                //		GROUP BY pers_id, event_event, event_kind, event_id

                // *** 27-03-2023: Improved for GROUP BY, there were double results ***
                // *** Only get pers_id, otherwise GROUP BY doesn't work properly (double results) ***
                //SELECT pers_gedcomnumber FROM humo_persons
                //	GROUP BY pers_gedcomnumber
                /*
                    $person_qry="
                        SELECT pers_id FROM humo_persons
                        LEFT JOIN humo_events
                        ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id 
                        WHERE pers_tree_id='".$tree_id."' AND
                            (
                            CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_patronym,pers_lastname) LIKE '%".safe_text_db($search_name)."%'
                            OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%".safe_text_db($search_name)."%' 
                            OR CONCAT(pers_patronym,pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text_db($search_name)."%' 
                            OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%".safe_text_db($search_name)."%'

                            OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%".safe_text_db($search_name)."%'
                            OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%".safe_text_db($search_name)."%' 
                            OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%".safe_text_db($search_name)."%' 
                            OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%".safe_text_db($search_name)."%'
                            )
                            GROUP BY pers_id
                            ORDER BY pers_lastname, pers_firstname, CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED)
                    ";
                    //echo $person_qry;
                    */

                $person_result = $dbh->query($person_qry);
            } elseif ($editor['pers_gedcomnumber']) {
                // *** Heredis GEDCOM don't uses I, so don't add an I anymore! ***
                // if(substr($editor['pers_gedcomnumber'],0,1)!="i" AND substr($editor['pers_gedcomnumber'],0,1)!="I") { $editor['pers_gedcomnumber'] = "I".$editor['pers_gedcomnumber']; }
                $person_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($editor['pers_gedcomnumber']) . "'";
                $person_result = $dbh->query($person_qry);
                $person = $person_result->fetch(PDO::FETCH_OBJ);
                if ($person) {
                    $pers_gedcomnumber = $person->pers_gedcomnumber;
                }
            }
            ?>
            <!-- </div> -->

            <div class="col-md-3">
                <?php
                if ($editor['search_name'] != '' && isset($person_result)) {
                    $nr_persons = $person_result->rowCount();
                    // *** No person found ***
                    if ($nr_persons == 0) {
                        $person_found = false;
                        $pers_gedcomnumber = ''; // *** Don't show a person if there are no results ***
                    }
                    // *** Found 1 person, directly select this person ***
                    elseif ($nr_persons == 1) {
                        // *** Don't show pull-down menu if there is only 1 result ***
                        $person = $person_result->fetch(PDO::FETCH_OBJ);
                        $pers_gedcomnumber = $person->pers_gedcomnumber;
                        $_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;
                        $selected = ' selected';

                        // *** Reset marriage number ***
                        $fams1 = explode(";", $person->pers_fams);
                        $marriage = $fams1[0];
                        $_SESSION['admin_fam_gedcomnumber'] = $marriage;
                    }
                    // *** Found multiple persons ***
                    elseif ($nr_persons > 0) {
                ?>
                        <form method="POST" action="index.php?menu_tab=person" style="display : inline;">
                            <input type="hidden" name="page" value="<?= $page; ?>">
                            <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                            <select size="1" name="person" class="form-select form-select-sm bg-primary-subtle" onChange="this.form.submit();">
                                <option value=""><?= __('Results'); ?></option>
                                <?php
                                while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
                                    // *** Get all person data ***
                                    // Probably not needed at this moment. Query contains all data.
                                    $person2 = $db_functions->get_person_with_id($person->pers_id);
                                    $selected = '';
                                    if ((!isset($_POST["search_quicksearch"]) and isset($pers_gedcomnumber)) && $person2->pers_gedcomnumber == $pers_gedcomnumber) {
                                        $selected = ' selected';
                                    }

                                    echo '<option value="' . $person2->pers_gedcomnumber . '"' . $selected . '>' .
                                        $editor_cls->show_selected_person($person2) . '</option>';
                                }
                                ?>
                            </select>
                        </form>
                <?php
                    }
                    // *** Don't show a person if there are multiple results ***
                    if ($nr_persons > 1 && isset($_POST["search_quicksearch"])) {
                        $pers_gedcomnumber = '';
                    }
                }
                ?>
            </div>

            <?php
            // *** Show message if no person is found ***
            if ($editor['pers_gedcomnumber'] == '') {
                $person_found = false;
            }
            if ($editor['pers_gedcomnumber'] != '' && isset($person_result)) {
                $nr_persons = $person_result->rowCount();
                // *** No person found ***
                if ($nr_persons == 0) {
                    $person_found = false;
                    $pers_gedcomnumber = ''; // *** Don't show a person if there are no results ***
                }
            }
            ?>
            <div class="col-auto">
                <!-- Search person GEDCOM number -->
                <form method="POST" action="index.php?menu_tab=person" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <div class="input-group input-group-sm">
                        <label for="search_id" class="col-md-auto col-form-label col-form-label-sm"><?= __('or ID:'); ?>&nbsp;</label>
                        <input type="text" id="search_id" name="search_id" class="form-select form-select-sm" value="<?= $editor['search_id']; ?>" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>">
                        <input type="submit" class="btn btn-sm btn-secondary" value="<?= __('Search'); ?>">
                    </div>
                </form>
            </div>
        </div>
        <!-- end of check for new tree -->
    <?php } ?>
</div>

<!-- Show message if no person is found -->
<?php if (!$person_found) { ?>
    <div class="alert alert-primary" role="alert">
        <?= __('Person not found'); ?>
    </div>
<?php } ?>

<?php
// *** Show delete message ***
if (isset($_POST['person_remove'])) {
    $disabled = ' disabled';
    $selected = '';
    //if ($selected_alive=='alive'){ $selected=' checked'; }
?>
    <div class="alert alert-danger">
        <?= __('This will disconnect this person from parents, spouses and children <b>and delete it completely from the database.</b> Do you wish to continue?'); ?><br>

        <!-- GRAYED-OUT and DISABLED! UNDER CONSTRUCTION! -->
        <input type="checkbox" name="XXXXX" value="XXXXX" <?= $selected . $disabled; ?>> <?= __('Also remove ALL RELATED PERSONS (including all items)'); ?><br>
        </span>

        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="submit" name="person_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php
}

// *** Disconnect child ***
if (isset($_GET['child_disconnect'])) {
?>
    <div class="alert alert-danger">
        <?= __('Are you sure you want to disconnect this child?'); ?>
        <form method="post" action="index.php" style="display : inline;">
            <input type="hidden" name="page" value="<?= $_GET['page']; ?>">
            <input type="hidden" name="family_id" value="<?= $_GET['family_id']; ?>">
            <input type="hidden" name="child_disconnect2" value="<?= $_GET['child_disconnect']; ?>">
            <input type="hidden" name="child_disconnect_gedcom" value="<?= $_GET['child_disconnect_gedcom']; ?>">
            <input type="submit" name="child_disconnecting" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php
}

if ($editor['confirm']) {
    echo $editor['confirm'];
}
// *** For events ***
if ($confirm) {
    echo $confirm;
}
if ($editor['confirm_note']) {
    echo $editor['confirm_note'];
}

$check_person = false;
if (isset($pers_gedcomnumber)) {
    if ($editor['new_tree'] == false && $editor['add_person'] == false && !$pers_gedcomnumber) {
        $check_person = false;
    }

    // *** Get person data to show name and calculate nr. of items ***
    $person = $db_functions->get_person($pers_gedcomnumber);
    if ($person) {
        $check_person = true;

        // *** Also set $marriage, this could be another family (needed to calculate ancestors used by colour event) ***
        if (isset($person->pers_fams) && $person->pers_fams) {
            $marriage_array = explode(";", $person->pers_fams);
            // *** Don't change if a second marriage is selected in the editor ***
            //if (!in_array($marriage, $marriage_array)){
            if (!isset($marriage) || !in_array($marriage, $marriage_array)) {
                $marriage = $marriage_array[0];
                $_SESSION['admin_fam_gedcomnumber'] = $marriage;
            }
        }
    }
    if (!$person && $editor['new_tree'] == false && $editor['add_person'] == false) {
        $check_person = false;
    }
}
if ($editor['new_tree']) {
    $check_person = true;
}
if ($check_person) {
    // *** Exit if selection of person is needed ***
    //if ($editor['new_tree']==false AND $editor['add_person']==false AND !$pers_gedcomnumber) exit;

    // *** Get person data to show name and calculate nr. of items ***
    //$person = $db_functions->get_person($pers_gedcomnumber);
    //if (!$person AND $editor['new_tree']==false AND $editor['add_person']==false) exit;

    // *** Save person GEDCOM number, needed for source pop-up ***
    $_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;

    // *** Tab menu ***
    $menu_tab = 'person';
    if (isset($_GET['menu_tab'])) {
        $menu_tab = $_GET['menu_tab'];
        $_SESSION['admin_menu_tab'] = $menu_tab;
    }
    if (isset($_SESSION['admin_menu_tab'])) {
        $menu_tab = $_SESSION['admin_menu_tab'];
    }
    if (isset($_GET['add_person'])) {
        $menu_tab = 'person';
    }
?>

    <ul class="nav nav-tabs mt-1">
        <li class="nav-item me-1">
            <a class="nav-link genealogy_nav-link <?php if ($menu_tab == 'person') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_tab=person"><?= __('Person'); ?></a>
        </li>
        <li class="nav-item me-1">
            <a class="nav-link genealogy_nav-link <?php if ($menu_tab == 'marriage') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;menu_tab=marriage"><?= __('Family'); ?></a>
        </li>

        <div class="pt-2 ms-3">

            <?php
            if ($person) {
                // *** Browser through persons: previous button ***
                if (substr($person->pers_gedcomnumber, 1) > 1) {
                    // *** First do a quick check, much faster for large family trees ***
                    $check_pers_gedcomnumber = (substr($person->pers_gedcomnumber, 1) - 1);
                    $check_pers_gedcomnumber = 'I' . $check_pers_gedcomnumber;
                    $previous_qry = "SELECT pers_gedcomnumber FROM humo_persons
                        WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $check_pers_gedcomnumber . "'";
                    $previous_result = $dbh->query($previous_qry);
                    $previousDb = $previous_result->fetch(PDO::FETCH_OBJ);

                    // *** Second quick check ***
                    if (!$previousDb) {
                        $check_pers_gedcomnumber = (substr($person->pers_gedcomnumber, 1) - 2);
                        $check_pers_gedcomnumber = 'I' . $check_pers_gedcomnumber;
                        $previous_qry = "SELECT pers_gedcomnumber FROM humo_persons
                            WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $check_pers_gedcomnumber . "'";
                        $previous_result = $dbh->query($previous_qry);
                        $previousDb = $previous_result->fetch(PDO::FETCH_OBJ);
                    }

                    if (!$previousDb) {
                        // *** Browser through persons: previous button ***
                        // *** VERY SLOW in large family trees ***
                        $previous_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                            AND CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) < '" . substr($person->pers_gedcomnumber, 1) . "'
                            ORDER BY CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) DESC LIMIT 0,1";
                        // BLADEREN WERKT NIET GOED:
                        //$previous_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='".$tree_id."'
                        //	AND CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) < '".substr($person->pers_gedcomnumber,1)."'
                        //	ORDER BY pers_gedcomnumber DESC LIMIT 0,1";
                        $previous_result = $dbh->query($previous_qry);
                        $previousDb = $previous_result->fetch(PDO::FETCH_OBJ);
                    }

                    // *** Link to first GEDCOM number in database ***
                    // *** First do a quick check for I1 ***
                    $first_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='I1'";
                    $first_result = $dbh->query($first_qry);
                    $firstDb = $first_result->fetch(PDO::FETCH_OBJ);
                    // *** Second quick check (GEDCOM number I1 could be missing, this wil increase speed) ***
                    if (!$firstDb) {
                        $first_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='I2'";
                        $first_result = $dbh->query($first_qry);
                        $firstDb = $first_result->fetch(PDO::FETCH_OBJ);
                    }
                    if (!$firstDb) {
                        // *** VERY SLOW in large family trees ***
                        $first_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                            ORDER BY CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) LIMIT 0,1";
                        $first_result = $dbh->query($first_qry);
                        $firstDb = $first_result->fetch(PDO::FETCH_OBJ);
                    }
            ?>
                    <form method="POST" action="index.php?menu_tab=person" style="display : inline;">
                        <input type="hidden" name="page" value="<?= $page; ?>">
                        <input type="hidden" name="person" value="<?= $firstDb->pers_gedcomnumber; ?>">
                        <input type="submit" value="<<">
                    </form>

                    <?php if ($previousDb) { ?>
                        <form method="POST" action="index.php?menu_tab=person" style="display : inline;">
                            <input type="hidden" name="page" value="<?= $page; ?>">
                            <input type="hidden" name="person" value="<?= $previousDb->pers_gedcomnumber; ?>">
                            <input type="submit" value="<">
                        </form>
                    <?php
                    }
                } else {
                    ?>
                    <input type="submit" value="<<" disabled>
                    <input type="submit" value="<" disabled>
                <?php
                }

                // *** Browser through persons: next button ***
                // *** First do a quick check, much faster for large family trees!!!!! ***
                $check_pers_gedcomnumber = (substr($person->pers_gedcomnumber, 1) + 1);
                $check_pers_gedcomnumber = 'I' . $check_pers_gedcomnumber;
                $next_qry = "SELECT pers_gedcomnumber FROM humo_persons
                    WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $check_pers_gedcomnumber . "'";
                $next_result = $dbh->query($next_qry);
                $nextDb = $next_result->fetch(PDO::FETCH_OBJ);

                // *** Second quick check (a GEDCOM number could be missing, this wil increase speed) ***
                if (!$nextDb) {
                    $check_pers_gedcomnumber = (substr($person->pers_gedcomnumber, 1) + 2);
                    $check_pers_gedcomnumber = 'I' . $check_pers_gedcomnumber;
                    $next_qry = "SELECT pers_gedcomnumber FROM humo_persons
                        WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $check_pers_gedcomnumber . "'";
                    $next_result = $dbh->query($next_qry);
                    $nextDb = $next_result->fetch(PDO::FETCH_OBJ);
                }

                if (!$nextDb) {
                    // *** Next button ***
                    // *** VERY SLOW in large family trees ***
                    $next_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                        AND CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) > '" . substr($person->pers_gedcomnumber, 1) . "'
                        ORDER BY CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) LIMIT 0,1";
                    // BLADEREN WERKT NIET GOED:
                    //$next_qry = "SELECT pers_gedcomnumber FROM humo_persons
                    //	WHERE pers_tree_id='".$tree_id."'
                    //	AND CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) > '".substr($person->pers_gedcomnumber,1)."'
                    //	ORDER BY pers_gedcomnumber LIMIT 0,1";
                    $next_result = $dbh->query($next_qry);
                    $nextDb = $next_result->fetch(PDO::FETCH_OBJ);
                }
                if ($nextDb) {
                ?>
                    <form method="POST" action="index.php?menu_tab=person" style="display : inline;">
                        <input type="hidden" name="page" value="<?= $page; ?>">
                        <input type="hidden" name="person" value="<?= $nextDb->pers_gedcomnumber; ?>">
                        <input type="submit" value=">">
                    </form>
                <?php } else { ?>
                    <input type="submit" value=">" disabled>
                    <?php
                }


                // *** Link to last GEDCOM number in database ***
                // *** VERY SLOW in large family trees (so it's disabled for large family trees) ***
                $nr_persons = $db_functions->count_persons($tree_id);
                if ($nr_persons < 100000) { // *** Disabled for large family trees ***
                    $last_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'
                        ORDER BY CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED) DESC LIMIT 0,1";
                    $last_result = $dbh->query($last_qry);
                    $lastDb = $last_result->fetch(PDO::FETCH_OBJ);
                    if (substr($lastDb->pers_gedcomnumber, 2) > substr($person->pers_gedcomnumber, 2)) {
                    ?>
                        <form method="POST" action="index.php?menu_tab=person" style="display : inline;">
                            <input type="hidden" name="page" value="<?= $page; ?>">
                            <input type="hidden" name="person" value="<?= $lastDb->pers_gedcomnumber; ?>">
                            <input type="submit" value=">>">
                        </form>
                    <?php } else { ?>
                        <input type="submit" value=">>" disabled>
                <?php
                    }
                }
            }

            // *** Browse ***
            // *** Change CSS links ***
            echo '
                <style>
                .ltrsddm div a {
                    display:inline;
                    padding: 0px;
                }
                </style>';

            // *** Show navigation pop-up ***
            echo '&nbsp;&nbsp;<div class="' . $rtlmarker . 'sddm" style="display:inline;">';
            echo '<a href="#" style="display:inline" onmouseover="mopen(event,\'browse_menu\',0,0)" onmouseout="mclosetime()">';
            echo '[' . __('Browse') . ']</a>';
            echo '<div class="sddm_fixed"
                style="text-align:left; z-index:400; padding:4px; border: 1px solid rgb(153, 153, 153);
                direction:' . $rtlmarker . '; box-shadow: 2px 2px 2px #999; border-radius: 3px;" id="browse_menu"
                onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
            if ($editor['add_person'] == false) {
                ?>
                <table>
                    <tr>
                        <td style="vertical-align: top; width:auto; border: solid 0px; border-right:solid 1px #999999;">
                            <?php
                            // *** Show person ***
                            echo '<span style="font-weight:bold; font-size:1.1em">' . show_person($person->pers_gedcomnumber, false, false) . '</span><br>';

                            // *** Show marriages and children ***
                            if ($person->pers_fams) {
                                // *** Search for own family ***
                                $fams1 = explode(";", $person->pers_fams);
                                $fam_count = count($fams1);
                                for ($i = 0; $i < $fam_count; $i++) {
                                    $familyDb = $db_functions->get_family($fams1[$i]);

                                    $show_marr_status = ucfirst(__('marriage/ relation'));
                                    if ($familyDb->fam_marr_notice_date || $familyDb->fam_marr_notice_place || $familyDb->fam_marr_date || $familyDb->fam_marr_place || $familyDb->fam_marr_church_notice_date || $familyDb->fam_marr_church_notice_place || $familyDb->fam_marr_church_date || $familyDb->fam_marr_church_place) {
                                        $show_marr_status = __('Married');
                                    }

                            ?>
                                    <span style="display:block; margin-top:5px; padding:2px; border:solid 1px #0000FF; width:350px;">
                                        <a href="index.php?page=editor&amp;menu_tab=marriage&amp;marriage_nr=<?= $familyDb->fam_gedcomnumber; ?>"><b><?= $show_marr_status; ?></b></a>
                                        <?php
                                        echo __(' to: ');

                                        if ($person->pers_gedcomnumber == $familyDb->fam_man) {
                                            echo show_person($familyDb->fam_woman) . '<br>';
                                        } else {
                                            echo show_person($familyDb->fam_man) . '<br>';
                                        }

                                        if ($familyDb->fam_children) {
                                            echo '<b>' . __('Children') . '</b><br>';
                                            $child_array = explode(";", $familyDb->fam_children);
                                            foreach ($child_array as $j => $value) {
                                                echo ($j + 1) . '. ' . show_person($child_array[$j]) . '<br>';
                                            }
                                        }
                                        ?>
                                    </span>
                            <?php
                                }
                            }
                            ?>
                        </td>

                        <td style="vertical-align: top;">
                            <!-- Show parents and siblings (brothers and sisters) -->
                            <b><?= __('Parents'); ?></b><br>
                            <?php
                            if ($person->pers_famc) {
                                // *** Search for parents ***
                                $family_parentsDb = $db_functions->get_family($person->pers_famc, 'man-woman');

                                //*** Father ***
                                if ($family_parentsDb->fam_man) {
                                    echo show_person($family_parentsDb->fam_man);
                                } else {
                                    echo __('N.N.');
                                }

                                echo ' ' . __('and') . '<br>';

                                //*** Mother ***
                                if ($family_parentsDb->fam_woman) {
                                    echo show_person($family_parentsDb->fam_woman);
                                } else {
                                    echo __('N.N.');
                                }

                                echo '<br><br>';

                                // *** Siblings (brothers and sisters) ***
                                if ($family_parentsDb->fam_children) {
                                    $fam_children_array = explode(";", $family_parentsDb->fam_children);
                                    $child_count = count($fam_children_array);
                                    if ($child_count > 1) {
                                        echo '<b>' . __('Siblings') . '</b><br>';
                                        foreach ($fam_children_array as $j => $value) {
                                            echo ($j + 1) . '. ';
                                            if ($fam_children_array[$j] == $person->pers_gedcomnumber) {
                                                // *** Don't show link ***
                                                echo show_person($fam_children_array[$j], false, false) . '<br>';
                                            } else {
                                                echo show_person($fam_children_array[$j]) . '<br>';
                                            }
                                        }
                                    }
                                }
                            } else {
                                echo __('There are no parents.') . '<br>';
                            }
                            ?>
                        </td>
                    </tr>
                </table>
            <?php
            }

            echo '<br>';
            printf(__('Editing in %s? <b>Always backup your data!</b>'), 'HuMo-genealogy');

            echo '</div>';
            echo '</div>';
            // *** End of browse pop-up ***

            // *** Example of family screen in pop-up ***
            if ($person) {
                // Onderstaande person_url2 werkt niet altijd goed!
                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                //$popup_cls = new Person_cls;
                //$url=$popup_cls->person_url2($person->pers_tree_id,$person->pers_famc,$person->pers_fams,$person->pers_gedcomnumber);
                //echo " <a href=\"#\" onClick=\"window.open('".$url."', '','width=800,height=500')\"><b>[".__('Preview').']</b></a>';

                $pers_family = '';
                if ($person->pers_famc) {
                    $pers_family = $person->pers_famc;
                }
                if ($person->pers_fams) {
                    $person_fams = explode(';', $person->pers_fams);
                    $pers_family = $person_fams[0];
                }

                $vars['pers_family'] = $pers_family;
                $link = $link_cls->get_link('../', 'family', $tree_id, true, $vars);
                $link .= "main_person=" . $person->pers_gedcomnumber;
                echo " <a href=\"#\" onClick=\"window.open('" . $link . "', '','width=800,height=500')\"><b>[" . __('Preview') . ']</b></a>';
            }
            ?>

            <!-- Add person -->
            &nbsp;&nbsp;&nbsp;<a href="index.php?page=<?= $page; ?>&amp;add_person=1">
                <img src="images/person_connect.gif" border="0" title="<?= __('Add person'); ?>" alt="<?= __('Add person'); ?>"> <?= __('Add person'); ?></a>

            <!-- Help popup -->
            &nbsp;&nbsp;&nbsp;&nbsp;
            <div class="<?= $rtlmarker; ?>sddm" style="display:inline;">
                <a href="#" style="display:inline" onmouseover="mopen(event,'help_menu',10,150)" onmouseout="mclosetime()">
                    <img src="../images/help.png" height="16" width="16"> <?= __('Help'); ?>
                </a>
                <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                    <?= __('Examples of date entries:'); ?><br>
                    <b><?= __('13 october 1813, 13 oct 1813, 13-10-1813, 13/10/1813, 13.10.1813, 13,10,1813, between 1986 and 1987, 13 oct 1100 BC.'); ?></b><br>
                    <?= __('In all text fields it\'s possible to add a hidden text/ own remarks by using # characters. Example: #Check birthday.#'); ?><br>
                    <img src="../images/search.png" alt="<?= __('Search'); ?>"> <?= __('= click to open selection popup screen.'); ?><br>
                    <b>[+]</b> <?= __('= click to open extended editor items.'); ?>
                </div>
            </div>

        </div>
    </ul>

    <div style="background-color:white; height:500px; padding:10px;">

        <?php
        // *****************
        // *** Show data ***
        // *****************

        // *** Text area size ***
        $field_date = 10;
        $field_place = 25;
        $field_popup = "width=800,height=500,top=100,left=50,scrollbars=yes";
        //$field_text = 'style="height: 45px; width:550px;"';
        $field_text = 'style="height: 45px;"';
        //$field_text_medium = 'style="height: 45px; width:550px;"';
        $field_text_medium = 'style="height: 45px;"';
        //$field_text_large = 'style="height: 100px; width:550px"';
        $field_text_large = 'style="height: 200px;"';

        // *** Script voor expand and collapse of items ***
        // Script is used for person, family AND source editor.
        echo '
        <script>
        function hideShow(el_id){
            // *** Hide or show item ***
            var arr = document.getElementsByClassName(\'row\'+el_id);
            for (i=0; i<arr.length; i++){
                if(arr[i].style.display!="none"){
                    arr[i].style.display="none";
                }else{
                    arr[i].style.display="";
                }
            }

            // *** April 2023: disabled [+] and [-] links ***
            // *** Change [+] into [-] or reverse ***
            //if (document.getElementById(\'hideshowlink\'+el_id).innerHTML == "[+]")
            //	document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[-]";
            //else
            //	document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[+]";
        }
        </script>';

        // *******************
        // *** Show person ***
        // *******************

        if ($editor['add_person'] == true) {
            $pers_gedcomnumber = '';
            $pers_firstname = ''; //$pers_callname='';
            $pers_prefix = '';
            $pers_lastname = '';
            $pers_patronym = '';
            $pers_name_text = '';
            $pers_alive = '';
            $pers_cal_date = '';
            $pers_sexe = '';
            $pers_own_code = '';
            $person_text = '';

            $pers_birth_date = '';
            $pers_birth_place = '';
            $pers_birth_time = '';
            $pers_stillborn = '';
            $pers_birth_text = '';
            $pers_bapt_date = '';
            $pers_bapt_place = '';
            $pers_religion = '';
            $pers_bapt_text = '';
            $pers_death_date = '';
            $pers_death_place = '';
            $pers_death_time = '';
            $pers_death_cause = '';
            $pers_death_text = '';
            $pers_death_age = '';
            $pers_buried_date = '';
            $pers_buried_place = '';
            $pers_cremation = '';
            $pers_buried_text = '';
            $pers_quality = '';
            // the following only exist if user requested jewish dates after nightfall:
            $pers_birth_date_hebnight = '';
            $pers_death_date_hebnight = '';
            $pers_buried_date_hebnight = '';
        } else {
            $pers_gedcomnumber = $person->pers_gedcomnumber;
            $pers_firstname = str_replace('"', '&#34;', $person->pers_firstname); //$pers_callname=str_replace('"','&#34;',$person->pers_callname);
            $pers_prefix = str_replace('"', '&#34;', $person->pers_prefix);
            $pers_lastname = str_replace('"', '&#34;', $person->pers_lastname);
            $pers_patronym = str_replace('"', '&#34;', $person->pers_patronym);
            $pers_name_text = $person->pers_name_text;
            $pers_alive = $person->pers_alive;
            $pers_cal_date = $person->pers_cal_date;
            $pers_sexe = $person->pers_sexe;
            $pers_own_code = $person->pers_own_code;
            $person_text = $person->pers_text;

            $pers_birth_date = $person->pers_birth_date;
            $pers_birth_place = $person->pers_birth_place;
            $pers_birth_time = $person->pers_birth_time;
            $pers_stillborn = $person->pers_stillborn;
            $pers_birth_text = $person->pers_birth_text;
            $pers_bapt_date = $person->pers_bapt_date;
            $pers_bapt_place = $person->pers_bapt_place;
            $pers_religion = $person->pers_religion;
            $pers_bapt_text = $person->pers_bapt_text;
            $pers_death_date = $person->pers_death_date;
            $pers_death_place = $person->pers_death_place;
            $pers_death_time = $person->pers_death_time;
            $pers_death_cause = $person->pers_death_cause;
            $pers_death_text = $person->pers_death_text;
            $pers_death_age = $person->pers_death_age;
            $pers_buried_date = $person->pers_buried_date;
            $pers_buried_place = $person->pers_buried_place;
            $pers_cremation = $person->pers_cremation;
            $pers_buried_text = $person->pers_buried_text;
            $pers_quality = $person->pers_quality;
            // the following only exist if user requested jewish dates after nightfall:
            $pers_birth_date_hebnight = '';
            $pers_death_date_hebnight = '';
            $pers_buried_date_hebnight = '';
            if ($humo_option['admin_hebnight'] == "y") {
                if (isset($person->pers_birth_date_hebnight)) {
                    $pers_birth_date_hebnight = $person->pers_birth_date_hebnight;
                }
                if (isset($person->pers_death_date_hebnight)) {
                    $pers_death_date_hebnight = $person->pers_death_date_hebnight;
                }
                if (isset($person->pers_buried_date_hebnight)) {
                    $pers_buried_date_hebnight = $person->pers_buried_date_hebnight;
                }
            }
        }

        // *** Script voor expand and collapse of items ***
        echo '
        <script>
        function hideShowAll(){
            // *** PERSON: Change [+] into [-] or reverse ***
            if (document.getElementById(\'hideshowlinkall\').innerHTML == "[+]")
                document.getElementById(\'hideshowlinkall\').innerHTML = "[-]";
            else
                document.getElementById(\'hideshowlinkall\').innerHTML = "[+]";

            var items = [1,2,3,4,5,13,20,21,51,53,54,55,61,62];
            for(j=0; j<items.length; j++){
                // *** Hide or show item ***
                var arr = document.getElementsByClassName(\'row\'+items[j]);
                for (i=0; i<arr.length; i++){
                    if(arr[i].style.display!="none"){
                        arr[i].style.display="none";
                    }else{
                        arr[i].style.display="";
                    }
                }

                // *** April 2023: removed several [+] and [-] links ***
                // *** Check if items exists (profession and addresses are not always available) ***
                if (document.getElementById(\'hideshowlink\'+items[j]) !== null){
                    // *** Change [+] into [-] or reverse ***
                    // *** Change [+] into [-] or reverse ***
                    if (document.getElementById(\'hideshowlink\'+items[j]).innerHTML == "[+]")
                        document.getElementById(\'hideshowlink\'+items[j]).innerHTML = "[-]";
                    else
                        document.getElementById(\'hideshowlink\'+items[j]).innerHTML = "[+]";
                }
            }
        }

        // *** Marriage ***
        function hideShowAll2(){
            // *** MARRIAGE: Change [+] into [-] or reverse ***
            if (document.getElementById(\'hideshowlinkall2\').innerHTML == "[+]")
                document.getElementById(\'hideshowlinkall2\').innerHTML = "[-]";
            else
                document.getElementById(\'hideshowlinkall2\').innerHTML = "[+]";

            var items = [6,7,8,9,10,11,52,53,110];
            for(j=0; j<items.length; j++){
                // *** Hide or show item ***
                var arr = document.getElementsByClassName(\'row\'+items[j]);
                for (i=0; i<arr.length; i++){
                    if(arr[i].style.display!="none"){
                        arr[i].style.display="none";
                    }else{
                        arr[i].style.display="";
                    }
                }

                // *** Change [+] into [-] or reverse ***
                // *** Check if items exists (profession and addresses are not always avaiable) ***
                if (document.getElementById(\'hideshowlink\'+items[j]) !== null){
                    if (document.getElementById(\'hideshowlink\'+items[j]).innerHTML == "[+]")
                        document.getElementById(\'hideshowlink\'+items[j]).innerHTML = "[-]";
                    else
                        document.getElementById(\'hideshowlink\'+items[j]).innerHTML = "[+]";
                }
            }
        }
        </script>';

        // *** Show person tab ***
        if ($menu_tab == 'person') {
            include(__DIR__ . '/editor_person.php');
        }

        // *** Show relation tab ***
        if ($menu_tab == 'marriage') {
            include(__DIR__ . '/editor_relation.php');
        }
    } // End person check



    // *****************
    // *** FUNCTIONS ***
    // *****************

    // *** Show event options ***
    function event_option($event_gedcom, $event)
    {
        global $language;
        $selected = '';
        if ($event_gedcom == $event) {
            $selected = ' selected';
        }
        return '<option value="' . $event . '"' . $selected . '>' . language_event($event) . '</option>';
    }

    // *** New function mar. 2024 ***
    // *** Show number of sources and show indication if source is connected ***
    function check_sources($connect_kind, $connect_sub_kind, $connect_connect_id)
    {
        global $tree_id, $dbh, $db_functions;

        $connect_qry = "SELECT connect_connect_id, connect_source_id FROM humo_connections
            WHERE connect_tree_id='" . $tree_id . "'
            AND connect_sub_kind='" . $connect_sub_kind . "' AND connect_connect_id='" . $connect_connect_id . "'";
        $connect_sql = $dbh->query($connect_qry);
        $source_count = $connect_sql->rowCount();
        $source_error = 0;
        while ($connectDb = $connect_sql->fetch(PDO::FETCH_OBJ)) {
            if (!$connectDb->connect_source_id) {
                $source_error = 1;
            } else {
                // *** Check if source is empty ***
                $sourceDb = $db_functions->get_source($connectDb->connect_source_id);
                if (!$sourceDb->source_title && !$sourceDb->source_text && !$sourceDb->source_date && !$sourceDb->source_place && !$sourceDb->source_refn) {
                    $source_error = 2;
                }
            }
        }

        $style = '';
        if ($source_error == '1') {
            $style = ' style="background-color:#FFAA80"';
        } // *** No source connected, colour = orange ***
        if ($source_error == '2') {
            $style = ' style="background-color:#FFFF00"';
        } // *** Source is empty, colour = yellow ***

        if ($source_count) {
            //return '<span ' . $style . '>[' . $source_count . ']</span>';
            return ' <span ' . $style . '>#' . $source_count . '</span>';
        } else {
            return;
        }
    }

    // *** Show link to sources (mar. 2024 version 3) ***
    function source_link3($connect_kind, $connect_sub_kind, $connect_connect_id)
    {
        // TODO improve this unique_id.
        $unique_id = $connect_kind . $connect_sub_kind . $connect_connect_id;
        ?>
        <!-- Button trigger modal for sources -->
        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#sourceModal<?= $unique_id; ?>">
            <?= __('Source'); ?>
        </button>

        <!-- same code is used in edit_address.php -->
        <div class="modal fade" id="sourceModal<?= $unique_id; ?>" tabindex="-1" aria-labelledby="sourceModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-xl">
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="sourceModalLabel"><?= __('Source'); ?></h1>
                        <button type="button" class="btn-close" id="source<?= $unique_id; ?>" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <?php
                        $url = 'index.php?page=editor_sources';
                        if ($connect_kind) {
                            $url .= '&connect_kind=' . $connect_kind;
                        }
                        $url .= '&connect_sub_kind=' . $connect_sub_kind;
                        if ($connect_connect_id) {
                            $url .= '&connect_connect_id=' . $connect_connect_id;
                        }
                        ?>
                        <!-- TODO only load iframe if there are sources? Otherwise add link to add sources? -->
                        <!-- Mar. 2024: added lazy loading (only load iframe if iframe is opened) -->
                        <iframe id="source_iframe" style="width:800px;height:800px;" title="source_iframe" src="<?= $url; ?>" loading="lazy"></iframe>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= __('Close'); ?></button>
                        <!-- <button type="button" class="btn btn-sm btn-primary">Save changes</button> -->
                    </div>
                </div>
            </div>
        </div>
    <?php
    }

    // *** Person edit lines (in use for adding person/ parents/ children) ***
    function edit_firstname($name, $value)
    {
    ?>
        <div class="row mb-2">
            <div class="col-md-3"><b><?= ucfirst(__('firstname')); ?></b></div>
            <div class="col-md-7"><input type="text" name="<?= $name; ?>" value="<?= $value; ?>" size="35" class="form-control form-control-sm"></div>
        </div>
    <?php
    }

    function edit_prefix($name, $value)
    {
    ?>
        <div class="row mb-2">
            <div class="col-md-3"><?= ucfirst(__('prefix')); ?></div>
            <div class="col-md-7">
                <input type="text" name="<?= $name; ?>" value="<?= $value; ?>" size="10" class="form-control form-control-sm">
                <span style="font-size: 13px;"><?= __("For example: d\' or:  van_ (use _ for a space)"); ?></span>
            </div>
        </div>
    <?php
    }

    function edit_lastname($name, $value)
    {
    ?>
        <div class="row mb-2">
            <div class="col-md-3"><b><?= ucfirst(__('lastname')); ?></b></div>
            <div class="col-md-7">
                <input type="text" name="<?= $name; ?>" value="<?= $value; ?>" size="35" class="form-control form-control-sm">
            </div>
        </div>
    <?php
    }

    function edit_patronymic($name, $value)
    {
    ?>
        <div class="row mb-2">
            <div class="col-md-3"><?= ucfirst(__('patronymic')); ?></div>
            <div class="col-md-7">
                <input type="text" name="<?= $name; ?>" value="<?= $value; ?>" size="35" class="form-control form-control-sm">
            </div>
        </div>
    <?php
    }

    function edit_event_name($name_select, $name_text, $value)
    {
    ?>
        <div class="row mb-2">
            <div class="col-md-3">
                <select size="1" name="<?= $name_select; ?>" class="form-select form-select-sm">
                    <!-- Nickname, alias, adopted name, hebrew name, etc. -->
                    <?php event_selection(''); ?>
                </select>
            </div>
            <div class="col-md-7">
                <input type="text" name="<?= $name_text; ?>" placeholder="<?= __('Nickname') . ' - ' . __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>" value="<?= $value; ?>" size="35" class="form-control form-control-sm">
            </div>
        </div>
    <?php
    }

    function edit_privacyfilter($name, $value)
    {
    ?>
        <div class="row mb-2">
            <div class="col-md-3"><?= __('Privacy filter'); ?></div>
            <div class="col-md-7">
                <input type="radio" name="<?= $name; ?>" value="alive" class="form-check-input" id="<?= $name; ?>">
                <label class="form-check-label" for="<?= $name; ?>"><?= __('alive'); ?></label>

                <input type="radio" name="<?= $name; ?>" value="deceased" class="form-check-input" id="<?= $name; ?>">
                <label class="form-check-label" for="<?= $name; ?>"><?= __('deceased'); ?></label>
            </div>
        </div>
    <?php
    }

    function edit_sexe($name, $checked)
    {
    ?>
        <div class="row mb-2">
            <div class="col-md-3"><?= __('Sex'); ?></div>
            <div class="col-md-7">
                <input type="radio" name="<?= $name; ?>" value="M" class="form-check-input" id="<?= $name; ?>" <?= $checked == 'M' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="<?= $name; ?>"><?= __('male'); ?></label>

                <input type="radio" name="<?= $name; ?>" value="F" class="form-check-input" id="<?= $name; ?>" <?= $checked == 'F' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="<?= $name; ?>"><?= __('female'); ?></label>

                <input type="radio" name="<?= $name; ?>" value="" class="form-check-input" id="<?= $name; ?>" <?= $checked == '' ? 'checked' : ''; ?>>
                <label class="form-check-label" for="<?= $name; ?>">?</label>
            </div>
        </div>
    <?php
    }

    function edit_profession($name, $value)
    {
    ?>
        <div class="row mb-2">
            <div class="col-md-3"><?= __('Profession'); ?></div>
            <div class="col-md-7">
                <input type="text" name="<?= $name; ?>" value="<?= $value; ?>" size="35" class="form-control form-control-sm">
            </div>
        </div>
    <?php
    }

    // *** New function aug. 2021: Add partner or child ***
    function add_person($person_kind, $pers_sexe)
    {
        global $phpself, $page, $rtlmarker, $editor_cls, $field_place, $field_date;
        global $familyDb, $marriage, $db_functions, $field_popup;

        $pers_prefix = '';
        $pers_lastname = '';

        if ($person_kind == 'partner') {
            $form = 5;
            echo ' <form method="POST" style="display: inline;" action="' . $phpself . '#marriage" name="form5" id="form5">';
        } else {
            // *** Add child to family ***
            $form = 6;
            echo ' <form method="POST" style="display: inline;" action="' . $phpself . '#marriage" name="form6" id="form6">';

            echo '<input type="hidden" name="child_connect" value="1">';
            if (isset($familyDb->fam_children)) {
                echo '<input type="hidden" name="children" value="' . $familyDb->fam_children . '">';
            }

            // TODO check code. Both variables show the same value.
            echo '<input type="hidden" name="family_id" value="' . $familyDb->fam_gedcomnumber . '">';
            echo '<input type="hidden" name="marriage_nr" value="' . $marriage . '">';

            // *** Get default prefix and lastname ***
            if ($familyDb->fam_man) {
                $personDb = $db_functions->get_person($familyDb->fam_man);
                $pers_prefix = $personDb->pers_prefix;
                $pers_lastname = $personDb->pers_lastname;
            }
        }
    ?>
        <input type="hidden" name="page" value="<?= $page; ?>">
        <input type="hidden" name="pers_name_text" value="">
        <input type="hidden" name="pers_birth_text" value="">
        <input type="hidden" name="pers_bapt_text" value="">
        <input type="hidden" name="pers_religion" value="">
        <input type="hidden" name="pers_death_cause" value="">
        <input type="hidden" name="pers_death_time" value="">
        <input type="hidden" name="pers_death_age" value="">
        <input type="hidden" name="pers_death_text" value="">
        <input type="hidden" name="pers_buried_text" value="">
        <input type="hidden" name="pers_cremation" value="">
        <input type="hidden" name="person_text" value="">
        <input type="hidden" name="pers_own_code" value="">

        <div class="row m-2">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <h2>
                    <?= $person_kind == 'partner' ? __('Add relation') : __('Add child'); ?>
                </h2>
            </div>
        </div>

        <?= edit_firstname('pers_firstname', ''); ?>
        <?= edit_prefix('pers_prefix', $pers_prefix); ?>
        <?= edit_lastname('pers_lastname', $pers_lastname); ?>
        <?= edit_patronymic('pers_patronym', ''); ?>
        <?= edit_event_name('event_gedcom_new', 'event_event_name_new', ''); ?>
        <?= edit_privacyfilter('pers_alive', ''); ?>
        <?= edit_sexe('pers_sexe', $pers_sexe); ?>

        <!-- Birth -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('born')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_birth_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_birth_date', '', '', 'pers_birth_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_birth_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_birth_place" value="" size="<?= $field_place; ?>" class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=pers_birth_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                </div>
            </div>
        </div>
        <!-- Birth time and stillborn option -->
        <?php if ($person_kind == 'child') { ?>
            <div class="row mb-2">
                <label for="pers_birth_place" class="col-sm-3 col-form-label"><?= ucfirst(__('birth time')); ?></label>
                <div class="col-md-2">
                    <input type="text" name="pers_birth_time" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="pers_stillborn" class="form-check-input"> <?= __('stillborn child'); ?>
                </div>
            </div>
        <?php } else { ?>
            <input type="hidden" name="pers_birth_time" value="">
        <?php } ?>

        <!-- Baptise -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('baptised')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_bapt_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_bapt_date', '', '', 'pers_bapt_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_bapt_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_bapt_place" value="" size="<?= $field_place; ?>" class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=pers_bapt_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                </div>
            </div>
        </div>

        <!-- Died -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('died')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_death_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_death_date', '', '', 'pers_death_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_bapt_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_death_place" value="" size="<?= $field_place; ?>" class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=pers_death_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                </div>
            </div>
        </div>

        <!-- Buried -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('buried')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_buried_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_buried_date', '', '', 'pers_buried_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_buried_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_buried_place" value="" size="<?= $field_place; ?>" class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=pers_buried_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                </div>
            </div>
        </div>

        <!-- Profession -->
        <input type="hidden" name="event_date_profession_prefix" value=''>
        <input type="hidden" name="event_date_profession" value=''>
        <?= edit_profession('event_profession', ''); ?>

        <div class="row mb-2">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <?php if ($person_kind == 'partner') { ?>
                    <input type="submit" name="relation_add" value="<?= __('Add relation'); ?>" class="btn btn-sm btn-success">
                <?php } else { ?>
                    <input type="submit" name="person_add" value="<?= __('Add child'); ?>" class="btn btn-sm btn-success">
                <?php } ?>
            </div>
        </div>

    <?php
        echo '</form>';
    }


    function show_person($gedcomnumber, $gedcom_date = false, $show_link = true)
    {
        global $dbh, $db_functions, $page;
        if ($gedcomnumber) {
            $personDb = $db_functions->get_person($gedcomnumber);

            $name = '';
            $name .= $personDb->pers_firstname . ' ';
            if ($personDb->pers_patronym) {
                $name .= $personDb->pers_patronym . ' ';
            }
            $name .= strtolower(str_replace("_", " ", $personDb->pers_prefix)) . $personDb->pers_lastname;
            if (trim($name) === '') {
                $name = '[' . __('NO NAME') . ']';
            }

            if ($show_link == true) {
                $text = '<a href="index.php?page=' . $page . '&amp;menu_tab=person&amp;tree_id=' . $personDb->pers_tree_id .
                    '&amp;person=' . $personDb->pers_gedcomnumber . '">' . $name . '</a>' . "\n";
            } else {
                $text = $name . "\n";
            }
        } else {
            $text = __('N.N.');
        }

        if ($gedcom_date == true) {
            if ($personDb->pers_birth_date) {
                $text .= ' * ' . date_place($personDb->pers_birth_date, '');
            } elseif ($personDb->pers_bapt_date) {
                $text .= ' ~ ' . date_place($personDb->pers_bapt_date, '');
            } elseif ($personDb->pers_death_date) {
                $text .= ' &#134; ' . date_place($personDb->pers_death_date, '');
                //$text.=' &dagger; '.date_place($personDb->pers_death_date,'');
            } elseif ($personDb->pers_buried_date) {
                $text .= ' [] ' . date_place($personDb->pers_buried_date, '');
            }
        }
        return $text;
    }

    // ***NEW FUNCTION jan. 2021 ***
    function edit_addresses($connect_kind, $connect_sub_kind, $connect_connect_id)
    {
        global $dbh, $tree_id, $page, $editor_cls, $field_place, $field_text;
        global $rtlmarker, $field_popup;

        $rtlmarker = "ltr";

        // ****************************************************
        // *** Show and edit addresses/residences by person ***
        // ****************************************************
    ?>
        <tr class="table_header_large" id="addresses">
            <td style="border-right:0px;"><b><?= __('Addresses'); ?></b></td>
            <td colspan="2">
                <?php
                if ($connect_kind == 'person') {
                    echo ' <input type="submit" name="person_add_address" value="' . __('Add') . '" class="btn btn-sm btn-outline-primary">';
                } else {
                    echo ' <input type="submit" name="relation_add_address" value="' . __('Add') . '" class="btn btn-sm btn-outline-primary">';
                }

                // *** HELP POPUP for address ***
                ?>
                &nbsp;
                <div class="<?= $rtlmarker; ?>sddm" style="display:inline;">
                    <a href="#" style="display:inline" onmouseover="mopen(event,'help_address_shared',0,0)" onmouseout="mclosetime()">
                        <img src="../images/help.png" height="16" width="16">
                    </a>
                    <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_address_shared" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                        <b><?= __('A shared address can be connected to multiple persons or relations.'); ?></b><br>
                        <b><?= __('A shared address is only supported by the Haza-data and HuMo-genealogy programs.'); ?></b><br>
                    </div>
                </div>
            </td>
        </tr>

        <?php
        $connect_qry = $dbh->query("SELECT * FROM humo_connections
            WHERE connect_tree_id='" . $tree_id . "'
            AND connect_sub_kind='" . $connect_sub_kind . "'
            AND connect_connect_id='" . safe_text_db($connect_connect_id) . "'
            ORDER BY connect_order");
        $count = $connect_qry->rowCount();
        $address_nr = 0;
        //TODO replace $addressDb with something like $connectDb
        while ($addressDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
            $address_nr++;
            $key = $addressDb->connect_id;

            // *** Check order number, restore if number is wrong (because of problems in earlier versions) ***
            if ($addressDb->connect_order != $address_nr) {
                $addressDb->connect_order = $address_nr;
                $sql = "UPDATE humo_connections SET connect_order='" . $address_nr . "' WHERE connect_id='" . $addressDb->connect_id . "'";
                $dbh->query($sql);
            }
        ?>

            <!-- <tr style="display:none;" class="row55"> -->
            <tr class="humo_color">
                <td style="border-right:0px;">
                    <input type="hidden" name="connect_change[<?= $key; ?>]" value="<?= $addressDb->connect_id; ?>">
                    <input type="hidden" name="connect_connect_id[<?= $key; ?>]" value="<?= $addressDb->connect_connect_id; ?>">
                    <input type="hidden" name="connect_kind[<?= $key; ?>]" value="<?= $connect_kind; ?>">
                    <input type="hidden" name="connect_sub_kind[<?= $key; ?>]" value="<?= $connect_sub_kind; ?>">
                    <input type="hidden" name="connect_page[<?= $key; ?>]" value="">
                    <input type="hidden" name="connect_place[<?= $key; ?>]" value="">

                    <!-- Send old values, so changes of values can be detected -->
                    <input type="hidden" name="connect_date_old[<?= $addressDb->connect_id; ?>]" value="<?= $addressDb->connect_date; ?>">
                    <input type="hidden" name="connect_role_old[<?= $addressDb->connect_id; ?>]" value="<?= $addressDb->connect_role; ?>">
                    <input type="hidden" name="connect_text_old[<?= $addressDb->connect_id; ?>]" value="<?= $addressDb->connect_text; ?>">

                    <?php
                    // *** Remove address ***
                    echo '<a href="index.php?page=' . $page . '&amp;person_place_address=1&amp;connect_drop=' . $addressDb->connect_id . '">
                <img src="images/button_drop.png" border="0" alt="drop"></a>';

                    // *** Order addresses ***
                    if ($addressDb->connect_order < $count) {
                        echo ' <a href="index.php?page=' . $page .
                            '&amp;person_place_address=1&amp;connect_down=' . $addressDb->connect_id .
                            '&amp;connect_kind=' . $addressDb->connect_kind .
                            '&amp;connect_sub_kind=' . $addressDb->connect_sub_kind .
                            '&amp;connect_connect_id=' . $addressDb->connect_connect_id .
                            '&amp;connect_order=' . $addressDb->connect_order;
                        echo '"><img src="images/arrow_down.gif" border="0" alt="down"></a>';
                    } else {
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                    }

                    if ($addressDb->connect_order > 1) {
                        echo ' <a href="index.php?page=' . $page .
                            '&amp;person_place_address=1&amp;connect_up=' . $addressDb->connect_id .
                            '&amp;connect_kind=' . $addressDb->connect_kind .
                            '&amp;connect_sub_kind=' . $addressDb->connect_sub_kind .
                            '&amp;connect_connect_id=' . $addressDb->connect_connect_id .
                            '&amp;connect_order=' . $addressDb->connect_order;
                        echo '"><img src="images/arrow_up.gif" border="0" alt="up"></a>';
                    } else {
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                    }
                    ?>
                </td>
                <?php
                // *** Show addresses by person or relation ***
                $address3_qry = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_gedcomnr='" . $addressDb->connect_item_id . "'");
                $address3Db = $address3_qry->fetch(PDO::FETCH_OBJ);

                if ($address3Db) {
                    // *** Use hideshow to show and hide the editor lines ***
                    $hideshow = '8000' . $address3Db->address_id;
                    // *** If address AND place are missing show all editor fields ***
                    $display = ' display:none;';
                    if ($address3Db->address_address == '' && $address3Db->address_place == '') {
                        $display = '';
                    }
                }
                ?>

                <td colspan="2">
                    <?php
                    //echo '<div style="border: 2px solid red">';
                    if ($address3Db) {
                        $address = $address3Db->address_address . ' ' . $address3Db->address_place;
                        if ($address3Db->address_address == '' && $address3Db->address_place == '') {
                            $address = __('EMPTY LINE');
                        }

                        // *** Also show date and place ***
                        //if ($addressDb->connect_date) $address.=', '.date_place($addressDb->connect_date,'');
                        if ($addressDb->connect_date) {
                            $address .= ', ' . hideshow_date_place($addressDb->connect_date, '');
                        }
                    ?>

                        <span class="hideshowlink" onclick="hideShow(<?= $hideshow; ?>);"><?= $address; ?>
                            <?php
                            if ($address3Db->address_text || $addressDb->connect_text) {
                                echo ' <img src="images/text.png" height="16" alt="' . __('text') . '">';
                            }

                            if ($addressDb->connect_id) {
                                if ($connect_kind == 'person') {
                                    $connect_kind = 'person';
                                    $connect_sub_kind_source = 'pers_address_connect_source';
                                } else {
                                    $connect_kind = 'family';
                                    $connect_sub_kind_source = 'fam_address_connect_source';
                                }

                                $check_sources_text = check_sources($connect_kind, $connect_sub_kind_source, $addressDb->connect_id);
                                echo $check_sources_text;
                            }
                            ?>
                        </span>

                        <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;<?= $display; ?>"><br>

                            <input type="hidden" name="change_address_id[<?= $address3Db->address_id; ?>]" value="<?= $address3Db->address_id; ?>">

                            <!-- Send old values, so changes of values can be detected -->
                            <input type="hidden" name="address_shared_old[<?= $address3Db->address_id; ?>]" value="<?= $address3Db->address_shared; ?>">
                            <input type="hidden" name="address_address_old[<?= $address3Db->address_id; ?>]" value="<?= $address3Db->address_address; ?>">
                            <input type="hidden" name="address_place_old[<?= $address3Db->address_id; ?>]" value="<?= $address3Db->address_place; ?>">
                            <input type="hidden" name="address_text_old[<?= $address3Db->address_id; ?>]" value="<?= $address3Db->address_text; ?>">
                            <input type="hidden" name="address_phone_old[<?= $address3Db->address_id; ?>]" value="<?= $address3Db->address_phone; ?>">
                            <input type="hidden" name="address_zip_old[<?= $address3Db->address_id; ?>]" value="<?= $address3Db->address_zip; ?>">

                            <input type="hidden" name="connect_item_id_old[<?= $address3Db->address_id; ?>]" value="<?= $addressDb->connect_item_id; ?>">

                            <?= __('Address GEDCOM number:'); ?> <?= $address3Db->address_gedcomnr; ?>&nbsp;&nbsp;&nbsp;&nbsp;

                            <!-- Shared address, to connect address to multiple persons or relations -->
                            <input type="checkbox" name="address_shared_<?= $address3Db->address_id; ?>" value="no_data" <?= $address3Db->address_shared ? 'checked' : ''; ?>> <?= __('Shared address'); ?><br>

                            <?php
                            // *** Don't use date here. Date of connection table will be used ***
                            //echo $editor_cls->date_show($address3Db->address_date,'address_date',"[$address3Db->address_id]").' ';

                            if ($connect_kind == 'person') {
                                $form = 1;
                                //$place_item='place_person';
                            } else {
                                $form = 2;
                                //$place_item='place_relation';
                            }

                            // *** Save latest place in table humo_persons as person_place_index (in use for place index) ***
                            if ($connect_kind == 'person') {
                                global $pers_gedcomnumber;
                                if ($addressDb->connect_order == $count) {
                                    $sql = "UPDATE humo_persons SET
                                    pers_place_index='" . safe_text_db($address3Db->address_place) . "'
                                    WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($pers_gedcomnumber) . "'";
                                    $dbh->query($sql);
                                }
                            }
                            ?>

                            <div class="row mb-2">
                                <label for="address_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                                <div class="col-md-7">
                                    <div class="input-group">
                                        <input type="text" name="address_place_<?= $address3Db->address_id; ?>" value="<?= $address3Db->address_place; ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                        <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=address_place&amp;address_id=<?= $address3Db->address_id; ?>","","<?= $field_popup; ?>")'><img src=" ../images/search.png" alt="<?= __('Search'); ?>"></a>
                                    </div>
                                </div>
                            </div>

                            <?php
                            /*
                            *** DISABLED. It's possible to add a source by address, in address editor ***
                            // *** Source by address (now shown in red box, so it's clear it belongs to the address) ***
                            // *** New code, not tested yet ***
                            <?php if ($address3Db) { ?>
                            <div class="row mb-2">
                            <label for="address_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                            <div class="col-md-7">
                                <?php
                                source_link3('person', 'address_source', $address3Db->address_gedcomnr);
                                echo $check_sources_text;
                                ?>
                            </div>
                            </div>
                            <?php } ?>
                            */
                            ?>

                            <!-- Edit address -->
                            <div class="row mb-2">
                                <label for="address_address" class="col-md-3 col-form-label"><?= __('Street'); ?></label>
                                <div class="col-md-7">
                                    <input type="text" name="address_address_<?= $address3Db->address_id; ?>" value="<?= $address3Db->address_address; ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- Edit Zip code -->
                            <div class="row mb-2">
                                <label for="address_zip" class="col-md-3 col-form-label"><?= __('Zip code'); ?></label>
                                <div class="col-md-3">
                                    <input type="text" name="address_zip_<?= $address3Db->address_id; ?>" value="<?= $address3Db->address_zip; ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- Edit phone -->
                            <div class="row mb-2">
                                <label for="address_phone" class="col-md-3 col-form-label"><?= __('Phone'); ?></label>
                                <div class="col-md-3">
                                    <input type="text" name="address_phone_<?= $address3Db->address_id; ?>" value="<?= $address3Db->address_phone; ?>" class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- Edit text -->
                            <div class="row mb-2">
                                <label for="address_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                                <div class="col-md-7">
                                    <textarea rows="1" name="address_text_<?= $address3Db->address_id; ?>" <?= $field_text; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($address3Db->address_text); ?></textarea>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <label for="pers_buried_place" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                                <div class="col-md-7">
                                    <?= $editor_cls->date_show($addressDb->connect_date, 'connect_date', "[$addressDb->connect_id]"); ?>
                                </div>
                            </div>

                            <?php
                            $connect_role = '';
                            if (isset($addressDb->connect_role)) {
                                $connect_role = htmlspecialchars($addressDb->connect_role);
                            }
                            ?>
                            <div class="row mb-2">
                                <label for="pers_buried_place" class="col-md-3 col-form-label"><?= __('Addressrole'); ?></label>
                                <div class="col-md-3">
                                    <input type="text" name="connect_role[<?= $key; ?>]" value="<?= $connect_role; ?>" size="6" class="form-control form-control-sm">
                                </div>
                            </div>

                            <!-- Extra text by address -->
                            <div class="row mb-2">
                                <label for="pers_buried_place" class="col-md-3 col-form-label"><?= __('Extra text by address'); ?></label>
                                <div class="col-md-7">
                                    <textarea name="connect_text[<?= $addressDb->connect_id; ?>]" <?= $field_text; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($addressDb->connect_text); ?></textarea>
                                </div>
                            </div>

                            <?php if ($address3Db) { ?>
                                <?php
                                if ($connect_kind == 'person') {
                                    $connect_kind = 'person';
                                    $connect_sub_kind_source = 'pers_address_connect_source';
                                } else {
                                    $connect_kind = 'family';
                                    $connect_sub_kind_source = 'fam_address_connect_source';
                                }
                                ?>
                                <div class="row mb-2">
                                    <label for="pers_birth_text" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                    <div class="col-md-7">
                                        <?php
                                        source_link3($connect_kind, $connect_sub_kind_source, $addressDb->connect_id);
                                        echo $check_sources_text;
                                        ?>
                                    </div>
                                </div>
                            <?php
                            }

                            // *** Use hideshow to show and hide the editor lines ***
                            if (isset($hideshow) && substr($hideshow, 0, 4) === '8000') {
                            ?>
                        </span>
                    <?php
                            }
                        } else {
                            // *** Add new address ***
                            $addressqry = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_shared='1' ORDER BY address_place, address_address");
                    ?>
                    <input type="hidden" name="connect_date[<?= $key; ?>]" value="">
                    <input type="hidden" name="connect_date_prefix[<?= $key; ?>]" value="">
                    <input type="hidden" name="connect_role[<?= $key; ?>]" value="">

                    <!-- Added april 2024 -->
                    <input type="hidden" name="connect_text[<?= $key; ?>]" value="">

                    <?= __('Address'); ?>
                    <select size="1" name="connect_item_id[<?= $key; ?>]" style="width: 300px">
                        <option value=""><?= __('Select address'); ?></option>
                        <?php
                            while ($address2Db = $addressqry->fetch(PDO::FETCH_OBJ)) {
                                // *** Only shared addresses (at this moment) ***
                                $selected = '';
                                if ($addressDb->connect_item_id == $address2Db->address_gedcomnr) {
                                    $selected = ' selected';
                                }
                                echo '<option value="' . $address2Db->address_gedcomnr . '"' . $selected . '>' . $address2Db->address_place . ', ' . $address2Db->address_address;
                                if ($address2Db->address_text) {
                                    echo ' ' . substr($address2Db->address_text, 0, 40);
                                    if (strlen($address2Db->address_text) > 40) {
                                        echo '...';
                                    }
                                }
                                echo ' [' . $address2Db->address_gedcomnr . ']</option>';
                            }
                        ?>
                    </select>

                    <?= __('Or: add new address'); ?>
                <?php
                            echo ' <a href="index.php?page=' . $page;
                            if ($connect_kind == 'person') {
                                echo '&amp;person_place_address=1';
                            } else {
                                echo '&amp;family_place_address=1';
                            }
                            echo '&amp;address_add2=1&amp;connect_id=' . $addressDb->connect_id . '
                            &amp;connect_kind=' . $addressDb->connect_kind . '&amp;connect_sub_kind=' . $addressDb->connect_sub_kind . '
                            &amp;connect_connect_id=' . $addressDb->connect_connect_id . '#addresses">[' . __('Add') . ']</a> ';
                        }

                        //echo '</div>';
                ?>
                </td>
            </tr>
        <?php
        }

        // *** Show places or addresses if save or arrow links are used ***
        if (isset($_GET['person_place_address']) || isset($_GET['family_place_address'])) {
            // *** Script voor expand and collapse of items ***
            //if (isset($_GET['pers_place'])) $link_id='54';
            if (isset($_GET['person_place_address']) || isset($_GET['family_place_address'])) {
                $link_id = '55';
            }
            echo '
            <script>
            function Show(el_id){
                // *** Hide or show item ***
                var arr = document.getElementsByClassName(\'row\'+el_id);
                for (i=0; i<arr.length; i++){
                    arr[i].style.display="";
                }
                // *** Change [+] into [-] ***
                document.getElementById(\'hideshowlink\'+el_id).innerHTML = "[-]";
            }
            </script>';

            echo '<script>
            Show("' . $link_id . '");
        </script>';
        }
    }

    // *** Show editor notes. $note_connect_kind=person/family ***
    function show_editor_notes($note_connect_kind)
    {
        global $dbh, $tree_id, $pers_gedcomnumber, $field_text_large, $editor_cls, $marriage;

        // *** $note_connect_id = I123 or F123 ***
        $note_connect_id = $pers_gedcomnumber;
        if ($note_connect_kind == 'family') {
            $note_connect_id = $marriage;
        }

        $note_qry = "SELECT * FROM humo_user_notes
            WHERE note_tree_id='" . $tree_id . "'
            AND note_kind='editor' AND note_connect_kind='" . $note_connect_kind . "'
            AND note_connect_id='" . $note_connect_id . "'";
        $note_result = $dbh->query($note_qry);
        $num_rows = $note_result->rowCount();

        // *** Otherwise link won't work second time because of added anchor ***
        $anchor = '#editor_notes';
        if (isset($_GET['note_add'])) {
            $anchor = '';
        }
        ?>
        <tr class="table_header_large">
            <td><a name="editor_notes"></a><?= __('Editor notes'); ?></td>
            <td colspan="2">
                <a href="index.php?page=editor&amp;note_add=<?= $note_connect_kind . $anchor; ?>">[<?= __('Add'); ?>]</a>
                <?php
                if ($num_rows) {
                    printf(__('There are %d editor notes.'), $num_rows);
                } else {
                    printf(__('There are %d editor notes.'), 0);
                }
                ?>
            </td>
        </tr>
        <?php while ($noteDb = $note_result->fetch(PDO::FETCH_OBJ)) { ?>
            <tr>
                <td>
                    <!-- Link to remove note -->
                    <a href="index.php?page=editor&amp;note_drop=<?= $noteDb->note_id; ?>">
                        <img src="images/button_drop.png" border="0" alt="down">
                    </a>
                </td>
                <td colspan="2">
                    <input type="hidden" name="note_id[<?= $noteDb->note_id; ?>]" value="<?= $noteDb->note_id; ?>">
                    <input type="hidden" name="note_connect_kind[<?= $noteDb->note_id; ?>]" value="<?= $note_connect_kind; ?>">

                    <?php
                    $user_name = '';
                    if ($noteDb->note_new_user_id) {
                        $user_result = $dbh->query("SELECT * FROM humo_users WHERE user_id='" . $noteDb->note_new_user_id . "'");
                        $user_addedDb = $user_result->fetch(PDO::FETCH_OBJ);
                        $user_name = $user_addedDb->user_name;
                    }
                    ?>
                    <?= __('Added by'); ?> <b><?= $user_name; ?></b> (<?= show_datetime($noteDb->note_new_datetime); ?>)<br>

                    <?php
                    if ($noteDb->note_changed_user_id) {
                        //TODO combine queries
                        $user_name = '';
                        if ($noteDb->note_changed_user_id) {
                            $user_result = $dbh->query("SELECT * FROM humo_users WHERE user_id='" . $noteDb->note_changed_user_id . "'");
                            $userDb = $user_result->fetch(PDO::FETCH_OBJ);
                            $user_name = $userDb->user_name;
                        }

                        echo __('Changed by') . ' <b>' . $user_name . '</b> (' . show_datetime($noteDb->note_changed_datetime) . ')<br>';
                    }
                    ?>

                    <b><?= $noteDb->note_names; ?></b><br>

                    <textarea rows="1" name="note_note[<?= $noteDb->note_id; ?>]" <?= $field_text_large; ?> class="form-control form-control-sm"><?= $editor_cls->text_show($noteDb->note_note); ?></textarea><br>

                    <?= __('Priority'); ?>
                    <select size="1" name="note_priority[<?= $noteDb->note_id; ?>]">
                        <option value="Low"><?= __('Low'); ?></option>
                        <option value="Normal" <?= $noteDb->note_priority == 'Normal' ? ' selected' : ''; ?>><?= __('Normal'); ?></option>
                        <option value="High" <?= $noteDb->note_priority == 'High' ? ' selected' : ''; ?>><?= __('High'); ?></option>
                    </select>

                    &nbsp;&nbsp;&nbsp;&nbsp;<?= __('Status'); ?>
                    <select size="1" name="note_status[<?= $noteDb->note_id; ?>]">
                        <option value="Not started"><?= __('Not started'); ?></option>
                        <option value="In progress" <?php if ($noteDb->note_status == 'In progress') echo ' selected'; ?>><?= __('In progress'); ?></option>
                        <option value="Completed" <?php if ($noteDb->note_status == 'Completed') echo ' selected'; ?>><?= __('Completed'); ?></option>
                        <option value="Postponed" <?php if ($noteDb->note_status == 'Postponed') echo ' selected'; ?>><?= __('Postponed'); ?></option>
                        <option value="Cancelled" <?php if ($noteDb->note_status == 'Cancelled') echo ' selected'; ?>><?= __('Cancelled'); ?></option>
                    </select>
                </td>
            </tr>
    <?php
        }
    }

    function hideshow_date_place($hideshow_date, $hideshow_place)
    {
        // *** If date ends with ! then date isn't valid. Show red line ***
        $check_date = false;
        if (isset($hideshow_date) && substr($hideshow_date, -1) === '!') {
            $check_date = true;
            $hideshow_date = substr($hideshow_date, 0, -1);
        }
        $text = date_place($hideshow_date, $hideshow_place);
        if ($check_date) {
            $text = '<span style="background-color:#FFAA80">' . $text . '</span>';
        }
        return $text;
    }

    function hideshow_editor($hideshow, $text, $check_text)
    {
        if (!$text) {
            $text = '[' . __('Add') . ']';
        }

        $return_text = '<span class="hideshowlink" onclick="hideShow(' . $hideshow . ');">' . $text;
        if ($check_text) $return_text .= ' <img src="images/text.png" height="16" alt="' . __('text') . '">';
        $return_text .= '</span>';

        return $return_text;
    }

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
?>

<!-- Only use Save button, don't use [Enter] -->
<script>
    $(document).on("keypress", ":input:not(textarea)", function(event) {
        return event.keyCode != 13;
    });
</script>

<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}



// TODO create seperate controller script.
$phpself = 'index.php';
$sourcestring = '../source.php?';
$addresstring = '../address.php?';
$path_prefix = '../';

include_once(__DIR__ . "/../include/editor_cls.php");
$editor_cls = new editor_cls;

include_once(__DIR__ . "/../include/select_tree.php");

// *** Used for person color selection for descendants and ancestors, etc. ***
include_once(__DIR__ . "/../../include/ancestors_descendants.php");

include(__DIR__ . '/../include/editor_event_cls.php');
$event_cls = new editor_event_cls;

// *** Editor icon for admin and editor: select family tree ***
if (isset($tree_id) and $tree_id) {
    $db_functions->set_tree_id($tree_id);
}

require_once  __DIR__ . "/../models/editor.php";
$editorModel = new EditorModel($dbh, $tree_id, $tree_prefix, $db_functions, $editor_cls, $humo_option);
$editorModel->set_hebrew_night();

$editorModel->set_pers_gedcomnumber($db_functions);
$editorModel->set_search_name();
$editorModel->set_marriage();

$confirm = $editorModel->update_editor();

$editor['pers_gedcomnumber'] = $editorModel->get_pers_gedcomnumber();
$pers_gedcomnumber = $editor['pers_gedcomnumber']; // *** Temp variable ***

$editor['search_id'] = $editorModel->get_search_id();

$editor['search_name'] = $editorModel->get_search_name();

$editor['new_tree'] = $editorModel->get_new_tree();
$editorModel->set_favorite($dbh, $tree_id);

$editor['marriage'] = $editorModel->get_marriage();
$marriage = $editor['marriage']; // *** Temp variable ***

// *** Check for new person ***
$editorModel->set_add_person();
$editor['add_person'] = $editorModel->get_add_person();
$add_person = $editor['add_person']; // *** Temp variable ***

//TEST
//include (__DIR__.'/../include/editor_sources.php');

// TODO move items from editor_inc.php to model and view scripts.
// *** Process queries ***
$confirm2 = $confirm; // *** Temp variable ***
$confirm = ''; // *** Temp variable ***
include_once(__DIR__ . "/../include/editor_inc.php");
$confirm .= $confirm2 . $confirm; // *** Temp variable ***

if ($editor['new_tree'] == false) {
    // *** Favourites ***
    $fav_qry = "SELECT * FROM humo_settings LEFT JOIN humo_persons ON setting_value=pers_gedcomnumber
        WHERE setting_variable='admin_favourite' AND setting_tree_id='" . safe_text_db($tree_id) . "' AND pers_tree_id='" . safe_text_db($tree_id) . "'";
    $fav_result = $dbh->query($fav_qry);

    // *** Update cache for list of latest changes ***
    cache_latest_changes();
}

$person_found = true;
?>

<div class="p-3 m-2 genealogy_search">
    <?php if ($editor['new_tree'] == false) { ?>
        <div class="row mb-2">
            <div class="col-auto">
                <img src="../images/favorite_blue.png">
            </div>

            <div class="col-2">
                <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <select size="1" name="person" onChange="this.form.submit();" class="form-select form-select-sm">
                        <option value=""><?= __('Favourites list'); ?></option>
                        <?php
                        while ($favDb = $fav_result->fetch(PDO::FETCH_OBJ)) {
                            echo '<option value="' . $favDb->setting_value . '">' . $editor_cls->show_selected_person($favDb) . '</option>';
                        }
                        ?>
                    </select>
                </form>
            </div>

            <div class="col-2">
                <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <select size="1" name="person" onChange="this.form.submit();" class="form-select form-select-sm">
                        <option value=""><?= __('Latest changes'); ?></option>
                        <?php
                        if (isset($pers_id)) {
                            for ($i = 0; $i < count($pers_id); $i++) {
                                $person2_qry = "SELECT * FROM humo_persons WHERE pers_id='" . $pers_id[$i] . "'";
                                $person2_result = $dbh->query($person2_qry);
                                $person2 = $person2_result->fetch(PDO::FETCH_OBJ);
                                if ($person2) {
                                    $pers_user = '';
                                    if ($person2->pers_new_user) $pers_user = ' [' . __('Added by') . ': ' . $person2->pers_new_user . ']';
                                    elseif ($person2->pers_changed_user) $pers_user = ' [' . __('Changed by') . ': ' . $person2->pers_changed_user . ']';
                                    echo '<option value="' . $person2->pers_gedcomnumber . '">' . $editor_cls->show_selected_person($person2) . $pers_user . '</option>';
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

            <div class="col-auto">
                <label for="tree" class="col-form-label">
                    <?= __('Family tree'); ?>:
                </label>
            </div>

            <div class="col-2">
                <?= select_tree($dbh, $page, $tree_id); ?>
            </div>

            <div class="col-auto">
                <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <div class="input-group">
                        <!-- Search persons firstname/ lastname -->
                        <label for="search_name" class="col-auto col-form-label"><?= __('Person'); ?>:&nbsp;</label>
                        <input type="text" name="search_quicksearch" id="search_name" class="form-control" placeholder="<?= __('Name'); ?>" value="<?= $editor['search_name']; ?>" size="15">
                        <input type="submit" class="btn btn-sm btn-secondary" value="<?= __('Search'); ?>">
                    </div>
                </form>
            </div>

            <!--            <div class="col-auto"> -->
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
                if ($person) $pers_gedcomnumber = $person->pers_gedcomnumber;
            }
            ?>
            <!--            </div> -->

            <div class="col-auto">
                <?php
                if ($editor['search_name'] != '' and isset($person_result)) {
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
                        <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                            <input type="hidden" name="page" value="<?= $page; ?>">
                            <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                            <select size="1" name="person" class="form-select" style="width: 200px; background-color: #ffaa80;" onChange="this.form.submit();">
                                <option value=""><?= __('Results'); ?></option>
                                <?php
                                while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
                                    // *** Get all person data ***
                                    // Probably not needed at this moment. Query contains all data.
                                    $person2 = $db_functions->get_person_with_id($person->pers_id);
                                    $selected = '';
                                    if (!isset($_POST["search_quicksearch"]) and isset($pers_gedcomnumber)) {
                                        if ($person2->pers_gedcomnumber == $pers_gedcomnumber) {
                                            $selected = ' selected';
                                        }
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
                    if ($nr_persons > 1 and isset($_POST["search_quicksearch"])) {
                        $pers_gedcomnumber = '';
                    }
                }
                ?>
            </div>

            <div class="col-auto">
                <!-- Search person GEDCOM number -->
                <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                    <input type="hidden" name="page" value="<?= $page; ?>">
                    <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                    <div class="input-group">
                        <label for="search_id" class="col-auto col-form-label"><?= __('or ID:'); ?>&nbsp;</label>
                        <input type="text" id="search_id" name="search_id" class="form-select form-select-sm" value="<?= $editor['search_id']; ?>" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>">
                        <input type="submit" class="btn btn-sm btn-secondary" value="<?= __('Search'); ?>">

                        <?php
                        // *** Show message if no person is found ***
                        if ($editor['pers_gedcomnumber'] == '') {
                            $person_found = false;
                        }
                        if ($editor['pers_gedcomnumber'] != '' and isset($person_result)) {
                            $nr_persons = $person_result->rowCount();
                            // *** No person found ***
                            if ($nr_persons == 0) {
                                $person_found = false;
                                $pers_gedcomnumber = ''; // *** Don't show a person if there are no results ***
                            }
                        }
                        ?>

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

        <form method="post" action="<?= $phpself; ?>" style="display : inline;">
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
        <form method="post" action="<?= $phpself; ?>" style="display : inline;">
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

if ($confirm) {
    echo $confirm;
}

$check_person = false;
if (isset($pers_gedcomnumber)) {
    if ($editor['new_tree'] == false and $add_person == false and !$pers_gedcomnumber) $check_person = false;

    // *** Get person data to show name and calculate nr. of items ***
    $person = $db_functions->get_person($pers_gedcomnumber);
    if ($person) {
        $check_person = true;

        // *** Also set $marriage, this could be another family (needed to calculate ancestors used by colour event) ***
        if (isset($person->pers_fams) and $person->pers_fams) {
            $marriage_array = explode(";", $person->pers_fams);
            // *** Don't change if a second marriage is selected in the editor ***
            //if (!in_array($marriage, $marriage_array)){
            if (!isset($marriage) or !in_array($marriage, $marriage_array)) {
                $marriage = $marriage_array[0];
                $_SESSION['admin_fam_gedcomnumber'] = $marriage;
            }
        }
    }
    if (!$person and $editor['new_tree'] == false and $add_person == false) $check_person = false;
}
if ($editor['new_tree']) $check_person = true;
if ($check_person) {
    // *** Exit if selection of person is needed ***
    //if ($editor['new_tree']==false AND $add_person==false AND !$pers_gedcomnumber) exit;

    // *** Get person data to show name and calculate nr. of items ***
    //$person = $db_functions->get_person($pers_gedcomnumber);
    //if (!$person AND $editor['new_tree']==false AND $add_person==false) exit;

    // *** Save person GEDCOM number, needed for source pop-up ***
    $_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;

    // *** Tab menu ***
    $menu_tab = 'person';
    if (isset($_GET['menu_tab'])) {
        $menu_tab = $_GET['menu_tab'];
        $_SESSION['admin_menu_tab'] = $menu_tab;
    }
    if (isset($_SESSION['admin_menu_tab'])) $menu_tab = $_SESSION['admin_menu_tab'];
    if (isset($_GET['add_person'])) $menu_tab = 'person';
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
                    <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                        <input type="hidden" name="page" value="<?= $page; ?>">
                        <input type="hidden" name="person" value="<?= $firstDb->pers_gedcomnumber; ?>">
                        <input type="submit" value="<<">
                    </form>

                    <?php
                    if ($previousDb) {
                    ?>
                        <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
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
                    <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                        <input type="hidden" name="page" value="<?= $page; ?>">
                        <input type="hidden" name="person" value="<?= $nextDb->pers_gedcomnumber; ?>">
                        <input type="submit" value=">">
                    </form>
                    <?php
                } else {
                    echo ' <input type="submit" value=">" disabled>';
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
                        <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                            <input type="hidden" name="page" value="<?= $page; ?>">
                            <input type="hidden" name="person" value="<?= $lastDb->pers_gedcomnumber; ?>">
                            <input type="submit" value=">>">
                        </form>
                <?php
                    } else {
                        echo ' <input type="submit" value=">>" disabled>';
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
            if ($add_person == false) {
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
                                    if (
                                        $familyDb->fam_marr_notice_date or $familyDb->fam_marr_notice_place
                                        or $familyDb->fam_marr_date or $familyDb->fam_marr_place
                                        or $familyDb->fam_marr_church_notice_date or $familyDb->fam_marr_church_notice_place
                                        or $familyDb->fam_marr_church_date or $familyDb->fam_marr_church_place
                                    )
                                        $show_marr_status = __('Married');

                            ?>
                                    <span style="display:block; margin-top:5px; padding:2px; border:solid 1px #0000FF; width:350px;">
                                        <a href="index.php?page=editor&amp;menu_tab=marriage&amp;marriage_nr=<?= $familyDb->fam_gedcomnumber; ?>"><b><?= $show_marr_status; ?></b></a>
                                        <?php
                                        echo __(' to: ');

                                        if ($person->pers_gedcomnumber == $familyDb->fam_man)
                                            echo show_person($familyDb->fam_woman) . '<br>';
                                        else
                                            echo show_person($familyDb->fam_man) . '<br>';

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

                            echo '</td><td style="vertical-align: top;">';

                            // *** Show parents and siblings (brothers and sisters) ***
                            echo '<b>' . __('Parents') . '</b><br>';
                            if ($person->pers_famc) {
                                // *** Search for parents ***
                                $family_parentsDb = $db_functions->get_family($person->pers_famc, 'man-woman');

                                //*** Father ***
                                if ($family_parentsDb->fam_man) echo show_person($family_parentsDb->fam_man);
                                else echo __('N.N.');

                                echo ' ' . __('and') . '<br>';

                                //*** Mother ***
                                if ($family_parentsDb->fam_woman) echo show_person($family_parentsDb->fam_woman);
                                else echo __('N.N.');

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
                //$popup_cls = New person_cls;
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

    <div style="float: left; background-color:white; height:500px; padding:10px;">

        <?php
        // *****************
        // *** Show data ***
        // *****************

        // *** Source iframe size ***
        echo '
        <style>
        .source_iframe {
            width:800px;
            height:500px;
        }
        </style>';

        // *** Text area size ***
        $field_date = 10;
        $field_place = 25;
        $field_popup = "width=800,height=500,top=100,left=50,scrollbars=yes";
        $field_text = 'style="height: 45px; width:550px;"';
        $field_text_medium = 'style="height: 45px; width:550px;"';
        $field_text_large = 'style="height: 100px; width:550px"';

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
        </script>
        ';

        // *******************
        // *** Show person ***
        // *******************

        if ($add_person == true) {
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

    // *** Show link to sources (version 2) ***
    function source_link2($hideshow, $connect_connect_id, $connect_sub_kind, $link = '')
    {
        global $tree_id, $dbh, $db_functions, $style_source;

        // *** Standard: hide source. If there is an error: show source ***
        $style_source = ' style="display:none;"';

        $connect_qry = "SELECT connect_connect_id, connect_source_id FROM humo_connections
            WHERE connect_tree_id='" . $tree_id . "'
            AND connect_sub_kind='" . $connect_sub_kind . "' AND connect_connect_id='" . $connect_connect_id . "'";
        $connect_sql = $dbh->query($connect_qry);
        $source_count = $connect_sql->rowCount();
        $source_error = 0;
        while ($connectDb = $connect_sql->fetch(PDO::FETCH_OBJ)) {
            if (!$connectDb->connect_source_id) {
                $source_error = 1;
                $style_source = '';
            } else {
                // *** Check if source is empty ***
                $sourceDb = $db_functions->get_source($connectDb->connect_source_id);
                if (!$sourceDb->source_title and !$sourceDb->source_text and !$sourceDb->source_date and !$sourceDb->source_place and !$sourceDb->source_refn) {
                    $source_error = 2;
                    $style_source = '';
                }
            }
        }

        $text = '&nbsp;';

        $style = '';
        if ($source_error == '1') $style = ' style="background-color:#FFAA80"'; // *** No source connected, colour = orange ***
        if ($source_error == '2') $style = ' style="background-color:#FFFF00"'; // *** Source is empty, colour = yellow ***
        $text .= '<span class="hideshowlink"' . $style . ' onclick="hideShow(' . $hideshow . ');">' . __('source') . ' [' . $source_count . ']</span>';

        return $text;
    }

    // *** Source in iframe ***
    function edit_sources($hideshow, $connect_kind, $connect_sub_kind, $connect_connect_id)
    {
        // *** Example ***
        //src="index.php?page=editor_sources&'.
        //$event_group.'&connect_kind='.$connect_kind.'&connect_sub_kind='.$connect_sub_kind.'&connect_connect_id='.$connect_connect_id.'">

        $text = '<tr style="display:none;" class="row' . $hideshow . '"><td></td><td colspan="3">
            <iframe id="source_iframe" class="source_iframe" title="source_iframe"
                src="index.php?page=editor_sources';
        if ($connect_kind) $text .= '&connect_kind=' . $connect_kind;
        $text .= '&connect_sub_kind=' . $connect_sub_kind;
        if ($connect_connect_id) $text .= '&connect_connect_id=' . $connect_connect_id;
        $text .= '">
            </iframe>
            </td></tr>';

        //TEST
        //include (__DIR__.'/../index.php?page=editor_sources');
        //include_once (__DIR__.'/../include/editor_sources.php');

        return $text;
    }


    function witness_edit($event_connect_id2, $event_text, $witness, $multiple_rows = '')
    {
        global $tree_id, $menu_tab, $field_popup;
        $text = '';

        // *** Witness select popup screen ***
        //$value = '';
        //if (substr($witness, 0, 1) == '@') {
        //    $value = substr($witness, 1, -1);
        //}

        $person_item = 'person_witness';
        if ($menu_tab == 'marriage') $person_item = 'marriage_witness';

        // *** Orange items if no witness name is selected or added in text ***
        $style = '';
        //if (!$witness) $style = 'style="background-color:#FFAA80"';
        if (!$witness and !$event_connect_id2) $style = 'style="background-color:#FFAA80"';

        //$text .= '<input ' . $style . ' type="text" name="text_event2' . substr($multiple_rows, 1, -1) . '" value="' . $value . '" size="17" placeholder="' . __('GEDCOM number (ID)') . '">';
        $text .= '<input ' . $style . ' type="text" name="event_connect_id2' . substr($multiple_rows, 1, -1) . '" value="' . $event_connect_id2 . '" size="17" placeholder="' . __('GEDCOM number (ID)') . '">';
        $text .= '<a href="#" onClick=\'window.open("index.php?page=editor_person_select&person=0&person_item=' . $person_item . '&event_row=' . substr($multiple_rows, 1, -1) . '&tree_id=' . $tree_id . '","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a>';

        // *** Witness: text field ***
        //$witness_value = $witness;
        //if (substr($witness, 0, 1) == '@') {
        //    $witness_value = '';
        //}
        //$text .= ' <b>' . __('or') . ':</b> <input type="text" ' . $style . ' name="text_event' . $multiple_rows . '" value="' . htmlspecialchars($witness_value) . '" placeholder="' . $event_text . '" size="44">';
        $text .= ' <b>' . __('or') . ':</b> <input type="text" ' . $style . ' name="text_event' . $multiple_rows . '" value="' . htmlspecialchars($witness) . '" placeholder="' . $event_text . '" size="44">';

        return $text;
    }


    // *** New function aug. 2021: Add partner or child ***
    function add_person($person_kind, $pers_sexe)
    {
        global $phpself, $page, $rtlmarker, $editor_cls, $field_place, $field_date;
        global $familyDb, $marriage, $db_functions, $field_popup;

        $pers_prefix = '';
        $pers_lastname = '';

        if ($person_kind == 'partner') {
            echo ' <form method="POST" style="display: inline;" action="' . $phpself . '#marriage" name="form5" id="form5">';
        } else {
            // *** Add child to family ***
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

        <table class="humo" style="margin-left:0px;">
            <?php
            if ($person_kind == 'partner') {
                echo '<tr class="table_header"><th colspan="2">' . __('Add relation') . '</th></tr>';
            } else {
                echo '<tr class="table_header"><th colspan="2">' . __('Add child') . '</th></tr>';
            }
            ?>

            <tr>
                <td><b><?= __('firstname'); ?></b></td>
                <td><input type="text" name="pers_firstname" value="" size="35" placeholder="<?= ucfirst(__('firstname')); ?>"></td>
            </tr>

            <tr>
                <td><?= __('prefix'); ?></td>
                <td><input type="text" name="pers_prefix" value="<?= $pers_prefix; ?>" size="10" placeholder="<?= ucfirst(__('prefix')); ?>">
                    <span style="font-size:13px;"><?= __("For example: d\' or:  van_ (use _ for a space)"); ?></span><br>
                </td>
            </tr>

            <tr>
                <td><b><?= __('lastname'); ?></b></td>
                <td>
                    <input type="text" name="pers_lastname" value="<?= $pers_lastname; ?>" size="35" placeholder="<?= ucfirst(__('lastname')); ?>">
                    <?= __('patronymic'); ?> <input type="text" name="pers_patronym" value="" size="20" placeholder="<?= ucfirst(__('patronymic')); ?>">
                </td>
            </tr>

            <!-- Nickname, alias, adopted name, hebrew name, etc. -->
            <tr>
                <td><br></td>
                <td>
                    <select size="1" name="event_gedcom_new" style="width: 150px">
                        <?php event_selection(''); ?>
                    </select>
                    <input type="text" name="event_event_name_new" placeholder="<?= __('Nickname') . ' - ' . __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>" value="" size="35">
                </td>
            </tr>

            <tr>
                <td><?= __('Privacy filter'); ?></td>
                <td>
                    <input type="radio" name="pers_alive" value="alive"> <?= __('alive'); ?>
                    <input type="radio" name="pers_alive" value="deceased"> <?= __('deceased'); ?>
                </td>
            </tr>

            <tr>
                <td><?= __('Sex'); ?></td>
                <td>
                    <input type="radio" name="pers_sexe" value="M" <?php if ($pers_sexe == 'M') echo ' checked'; ?>> <?= __('male'); ?>
                    <input type="radio" name="pers_sexe" value="F" <?php if ($pers_sexe == 'F') echo ' checked'; ?>> <?= __('female'); ?>
                    <input type="radio" name="pers_sexe" value="" <?php if ($pers_sexe == '') echo ' checked'; ?>> ?
                </td>
            </tr>

            <?php
            if ($person_kind == 'partner') {
                // *** Add new partner ***
                $form = 5;
            } else {
                // *** Add new child ***
                $form = 6;
            }

            // *** Born ***
            echo '<tr><td>' . ucfirst(__('born')) . '</td><td>';
            echo $editor_cls->date_show('', 'pers_birth_date', '', '', '', 'pers_birth_date_hebnight') . ' ';
            echo ' <input type="text" name="pers_birth_place" placeholder="' . ucfirst(__('place')) . '" value="" size="' . $field_place . '">';
            echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=' . $form . '&amp;place_item=pers_birth_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a></td></tr>';

            // *** Birth time and stillborn option ***
            if ($person_kind == 'child') {
                echo '<tr><td style="border-right:0px;">' . __('birth time') . '</td><td style="border-left:0px;"><input type="text" placeholder="' . __('birth time') . '" name="pers_birth_time" value="" size="' . $field_date . '">';
                echo '<input type="checkbox" name="pers_stillborn"> ' . __('stillborn child');
                echo '</td></tr>';
            } else {
                echo '<input type="hidden" name="pers_birth_time" value="">';
            }

            // *** Baptise ***
            echo '<tr><td>' . ucfirst(__('baptised')) . '</td><td>';
            echo $editor_cls->date_show('', 'pers_bapt_date', '', '', '', 'pers_bapt_date_hebnight') . ' ';
            echo ' <input type="text" name="pers_bapt_place" placeholder="' . ucfirst(__('place')) . '" value="" size="' . $field_place . '">';
            echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=' . $form . '&amp;place_item=pers_bapt_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a></td></tr>';

            // *** Died ***
            echo '<tr><td>' . ucfirst(__('died')) . '</td><td>';
            echo $editor_cls->date_show('', 'pers_death_date', '', '', '', 'pers_death_date_hebnight') . ' ';
            echo '  <input type="text" name="pers_death_place" placeholder="' . ucfirst(__('place')) . '" value="" size="' . $field_place . '">';
            echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=' . $form . '&amp;place_item=pers_death_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a></td></tr>';

            // *** Buried ***
            echo '<tr><td>' . ucfirst(__('buried')) . '</td><td>';
            echo $editor_cls->date_show('', 'pers_buried_date', '', '', '', 'pers_buried_date_hebnight') . ' ';
            echo '  <input type="text" name="pers_buried_place" placeholder="' . ucfirst(__('place')) . '" value="" size="' . $field_place . '">';
            echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=' . $form . '&amp;place_item=pers_buried_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a></td></tr>';

            // *** Profession ***
            ?>
            <tr>
                <td><?= __('Profession'); ?></td>
                <td>
                    <input type="text" name="event_profession" placeholder="<?= __('Profession'); ?>" value="" size="35">
                </td>
            </tr>
            <?php

            if ($person_kind == 'partner') {
                echo '<tr class="humo_color"><td></td><td><input type="submit" name="relation_add" value="' . __('Add relation') . '"></td></tr>';
            } else {
                echo '<tr class="humo_color"><td></td><td><input type="submit" name="person_add" value="' . __('Add child') . '"></td></tr>';
            }
            ?>
        </table>
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
            if ($personDb->pers_patronym) $name .= $personDb->pers_patronym . ' ';
            $name .= strtolower(str_replace("_", " ", $personDb->pers_prefix)) . $personDb->pers_lastname;
            if (trim($name) == '') $name = '[' . __('NO NAME') . ']';

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
                    echo ' <input type="submit" name="person_add_address" value="' . __('Add') . '">';
                } else {
                    echo ' <input type="submit" name="relation_add_address" value="' . __('Add') . '">';
                }

                // *** HELP POPUP for address ***
                ?>
                &nbsp;<div class="<?= $rtlmarker; ?>sddm" style="display:inline;">
                    <a href="#" style="display:inline" onmouseover="mopen(event,'help_address_shared',0,0)" onmouseout="mclosetime()">
                        <img src="../images/help.png" height="16" width="16">
                    </a>
                    <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_address_shared" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                        <b><?= __('A shared address can be connected to multiple persons or relations.'); ?></b><br>
                        <b><?= __('A shared address is only supported by the Haza-data and HuMo-genealogy programs.'); ?></b><br>
                    </div>
                </div><br>
            </td>
            <td></td>
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
                $address3_qry = $dbh->query("SELECT * FROM humo_addresses
                WHERE address_tree_id='" . $tree_id . "'
                AND address_gedcomnr='" . $addressDb->connect_item_id . "'");
                $address3Db = $address3_qry->fetch(PDO::FETCH_OBJ);

                if ($address3Db) {
                    // *** Use hideshow to show and hide the editor lines ***
                    $hideshow = '8000' . $address3Db->address_id;
                    // *** If address AND place are missing show all editor fields ***
                    $display = ' display:none;';
                    if ($address3Db->address_address == '' and $address3Db->address_place == '') $display = '';
                }

                echo '<td colspan="2">';
                // *** Source ***
                // There is no source used in the CONNECTION. Only at the address.
                //  echo '<input type="hidden" name="connect_source_id[' . $key . ']" value="">';
                //  echo '<input type="hidden" name="connect_text[' . $key . ']" value="">';

                //echo '<div style="border: 2px solid red">';
                if ($address3Db) {
                    $address = $address3Db->address_address . ' ' . $address3Db->address_place;
                    if ($address3Db->address_address == '' and $address3Db->address_place == '') $address = __('EMPTY LINE');

                    // *** Also show date and place ***
                    //if ($addressDb->connect_date) $address.=', '.date_place($addressDb->connect_date,'');
                    if ($addressDb->connect_date) $address .= ', ' . hideshow_date_place($addressDb->connect_date, '');

                    echo '<span class="hideshowlink" onclick="hideShow(' . $hideshow . ');">' . $address;
                    if ($address3Db->address_text or $addressDb->connect_text) echo ' <img src="images/text.png" height="16" alt="' . __('text') . '">';
                    echo '</span>';

                    echo '<span class="humo row' . $hideshow . '" style="margin-left:0px;' . $display . '">';
                    echo '<br>';

                    echo '<input type="hidden" name="change_address_id[' . $address3Db->address_id . ']" value="' . $address3Db->address_id . '">';

                    // *** Send old values, so changes of values can be detected ***
                    echo '<input type="hidden" name="address_shared_old[' . $address3Db->address_id . ']" value="' . $address3Db->address_shared . '">';
                    echo '<input type="hidden" name="address_address_old[' . $address3Db->address_id . ']" value="' . $address3Db->address_address . '">';
                    echo '<input type="hidden" name="address_place_old[' . $address3Db->address_id . ']" value="' . $address3Db->address_place . '">';
                    echo '<input type="hidden" name="address_text_old[' . $address3Db->address_id . ']" value="' . $address3Db->address_text . '">';
                    echo '<input type="hidden" name="address_phone_old[' . $address3Db->address_id . ']" value="' . $address3Db->address_phone . '">';
                    echo '<input type="hidden" name="address_zip_old[' . $address3Db->address_id . ']" value="' . $address3Db->address_zip . '">';

                    echo '<input type="hidden" name="connect_item_id_old[' . $address3Db->address_id . ']" value="' . $addressDb->connect_item_id . '">';

                    echo __('Address GEDCOM number:') . ' ' . $address3Db->address_gedcomnr . '&nbsp;&nbsp;&nbsp;&nbsp;';

                    // *** Shared address, to connect address to multiple persons or relations ***
                    $checked = '';
                    if ($address3Db->address_shared) $checked = ' checked';
                    echo '<input type="checkbox" name="address_shared_' . $address3Db->address_id . '" value="no_data"' . $checked . '> ' . __('Shared address') . '<br>';

                    // *** Don't use date here. Date of connection table will be used ***
                    //echo $editor_cls->date_show($address3Db->address_date,'address_date',"[$address3Db->address_id]").' ';
                    echo editor_label2(__('Place'));
                    echo '<input type="text" name="address_place_' . $address3Db->address_id . '" placeholder="' . __('Place') . '" value="' . $address3Db->address_place . '" size="' . $field_place . '">';

                    if ($connect_kind == 'person') {
                        $form = 1;
                        //$place_item='place_person';
                    } else {
                        $form = 2;
                        //$place_item='place_relation';
                    }
                    echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=' . $form . '&amp;place_item=address_place&amp;address_id=' . $address3Db->address_id . '","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a>';

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

                    // *** Source by address (now shown in red box, so it's clear it belongs to the address) ***
                    if ($address3Db) {
                        echo '&nbsp;&nbsp;' . __('Source') . ' ';

                        //if ($connect_kind=='person') $connect_sub_kind2='pers_address_source';
                        //	else $connect_sub_kind2='fam_address_source';
                        ////function source_link2($hideshow,$connect_connect_id, $connect_sub_kind){
                        //echo source_link2('20'.$addressDb->connect_id,$address3Db->address_gedcomnr,$connect_sub_kind2,'addresses');

                        echo source_link2('20' . $addressDb->connect_id, $address3Db->address_gedcomnr, 'address_source', 'addresses');
                    }
                    echo '<br>';

                    // *** Edit address ***
                    echo editor_label2(__('Street'));
                    echo '<input type="text" name="address_address_' . $address3Db->address_id . '" placeholder="' . __('Street') . '" value="' . $address3Db->address_address . '"  style="width: 500px"><br>';

                    // *** Edit Zip code ***
                    echo editor_label2(__('Zip code'));
                    echo '<input type="text" name="address_zip_' . $address3Db->address_id . '" placeholder="' . __('Zip code') . '" value="' . $address3Db->address_zip . '"  style="width: 200px"><br>';

                    // *** Edit phone ***
                    echo editor_label2(__('Phone'));
                    echo '<input type="text" name="address_phone_' . $address3Db->address_id . '" placeholder="' . __('Phone') . '" value="' . $address3Db->address_phone . '"  style="width: 200px"><br>';

                    // *** Edit text ***
                    echo editor_label2(__('Text'));
                    echo '<textarea rows="1" name="address_text_' . $address3Db->address_id . '" placeholder="' . __('Text') . '"' . $field_text . '>' .
                        $editor_cls->text_show($address3Db->address_text) . '</textarea><br>';

                    // *** Edit address date and address role ***
                    echo editor_label2(__('Date'));
                    echo $editor_cls->date_show($addressDb->connect_date, 'connect_date', "[$addressDb->connect_id]") . '<br>';

                    echo editor_label2(__('Addressrole'));
                    $connect_role = '';
                    if (isset($addressDb->connect_role)) $connect_role = htmlspecialchars($addressDb->connect_role);
                    echo '<input type="text" name="connect_role[' . $key . ']" value="' . $connect_role . '" size="6"><br>';

                    //echo '<div style="border: 2px solid red">';
                    // *** Extra text by address ***
                    echo editor_label2(__('Extra text by address'));
                    echo '<textarea name="connect_text[' . $addressDb->connect_id . ']" placeholder="' . __('Extra text by address') . '" ' . $field_text . '>' . $editor_cls->text_show($addressDb->connect_text) . '</textarea>';
                    //echo '</div>';

                    // *** Use hideshow to show and hide the editor lines ***
                    if (isset($hideshow) and substr($hideshow, 0, 4) == '8000') echo '</span>';
                } else {
                    // *** Add new address ***
                    echo '<input type="hidden" name="connect_date[' . $key . ']" value="">';
                    echo '<input type="hidden" name="connect_date_prefix[' . $key . ']" value="">';
                    echo '<input type="hidden" name="connect_role[' . $key . ']" value="">';

                    $addressqry = $dbh->query("SELECT * FROM humo_addresses
                        WHERE address_tree_id='" . $tree_id . "' AND address_shared='1'
                        ORDER BY address_place, address_address");
                    echo ' ' . __('Address') . ' ';
                    echo '<select size="1" name="connect_item_id[' . $key . ']" style="width: 300px">';
                    echo '<option value="">' . __('Select address') . '</option>';
                    while ($address2Db = $addressqry->fetch(PDO::FETCH_OBJ)) {
                        // *** Only share address if address is shared ***
                        $selected = '';
                        if ($addressDb->connect_item_id == $address2Db->address_gedcomnr) $selected = ' selected';
                        echo '<option value="' . $address2Db->address_gedcomnr . '"' . $selected . '>' .
                            @$address2Db->address_place . ', ' . $address2Db->address_address;

                        if ($address2Db->address_text) {
                            echo ' ' . substr($address2Db->address_text, 0, 40);
                            if (strlen($address2Db->address_text) > 40) echo '...';
                        }

                        echo ' [' . @$address2Db->address_gedcomnr . ']</option>';
                    }
                    echo '</select>';

                    echo ' ' . __('Or: add new address');
                    //echo ' <a href="index.php?page='.$page.
                    //&amp;person_place_address=1
                    //&amp;address_add2=1
                    //&amp;connect_id='.$addressDb->connect_id.'
                    //&amp;connect_kind='.$addressDb->connect_kind.'
                    //&amp;connect_sub_kind='.$addressDb->connect_sub_kind.'
                    //&amp;connect_connect_id='.$addressDb->connect_connect_id.'
                    //#addresses">['.__('Add').']</a> ';
                    echo ' <a href="index.php?page=' . $page;
                    if ($connect_kind == 'person')
                        echo '&amp;person_place_address=1';
                    else
                        echo '&amp;family_place_address=1';
                    echo '&amp;address_add2=1
                    &amp;connect_id=' . $addressDb->connect_id . '
                    &amp;connect_kind=' . $addressDb->connect_kind . '
                    &amp;connect_sub_kind=' . $addressDb->connect_sub_kind . '
                    &amp;connect_connect_id=' . $addressDb->connect_connect_id . '
                    #addresses">[' . __('Add') . ']</a> ';
                }

                //echo '</div>';

                /*
                // *** Edit address date and address role ***
                echo $editor_cls->date_show($addressDb->connect_date,'connect_date',"[$addressDb->connect_id]");
                $connect_role=''; if (isset($addressDb->connect_role)) $connect_role=htmlspecialchars($addressDb->connect_role);
                echo ' '.__('Addressrole').' <input type="text" name="connect_role['.$key.']" value="'.$connect_role.'" size="6">';

                // *** Extra text by address ***
                echo '<br><textarea name="connect_text['.$addressDb->connect_id.']" placeholder="'.__('Extra text by address').'" '.$field_text.'>'.$editor_cls->text_show($addressDb->connect_text).'</textarea>';
                */

                echo '</td>';
                echo '<td style="vertical-align:bottom;">';
                // *** Source by address-connection ***
                if ($address3Db) {
                    // *** This part is moved to the red address box ***
                    //if ($connect_kind=='person') $connect_sub_kind2='pers_address_source';
                    //	else $connect_sub_kind2='fam_address_source';
                    //function source_link2($hideshow,$connect_connect_id, $connect_sub_kind){
                    //echo source_link2('20'.$addressDb->connect_id,$address3Db->address_gedcomnr,$connect_sub_kind2,'addresses');

                    if ($connect_kind == 'person') $connect_sub_kind2 = 'pers_address_connect_source';
                    else $connect_sub_kind2 = 'fam_address_connect_source';
                    //function source_link2($hideshow,$connect_connect_id, $connect_sub_kind){
                    echo source_link2('21' . $addressDb->connect_id, $addressDb->connect_id, $connect_sub_kind2, 'addresses');
                }
                echo '</td>';
                ?>
            </tr>
        <?php

            // *** Show source by address ***
            if (isset($address3Db->address_gedcomnr)) {
                //edit_sources($hideshow,$connect_kind,$connect_sub_kind,$connect_connect_id)
                echo edit_sources('20' . $addressDb->connect_id, 'address', 'address_source', $address3Db->address_gedcomnr);
            }

            // *** Show source by address-connection ***
            if (isset($address3Db->address_gedcomnr) and $connect_kind == 'person') {
                // *** Show iframe source ***
                //echo edit_sources('20'.$addressDb->connect_id,'person','pers_address_source',$address3Db->address_gedcomnr);

                // *** Source connect to link person-address ***
                echo edit_sources('21' . $addressDb->connect_id, 'person', 'pers_address_connect_source', $addressDb->connect_id);
            } elseif (isset($address3Db->address_gedcomnr)) {
                // *** Show iframe source ***
                //echo edit_sources('20'.$addressDb->connect_id,'family','fam_address_source',$address3Db->address_gedcomnr);

                // *** Source connect to link family-address ***
                echo edit_sources('21' . $addressDb->connect_id, 'family', 'fam_address_connect_source', $addressDb->connect_id);
            }
        }

        // *** Show places or addresses if save or arrow links are used ***
        if (isset($_GET['person_place_address']) or isset($_GET['family_place_address'])) {
            // *** Script voor expand and collapse of items ***
            //if (isset($_GET['pers_place'])) $link_id='54';
            if (isset($_GET['person_place_address']) or isset($_GET['family_place_address'])) $link_id = '55';
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

    // *** force_update = only update cache, so skip some variables ***
    function cache_latest_changes($force_update = false)
    {
        global $dbh, $tree_id, $pers_id;
        $cache = '';
        $cache_count = 0;
        $cache_exists = false;
        $cache_check = false; // *** Use cache for large family trees ***
        $cacheqry = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='cache_latest_changes' AND setting_tree_id='" . $tree_id . "'");
        $cacheDb = $cacheqry->fetch(PDO::FETCH_OBJ);
        if ($cacheDb) {
            $cache_exists = true;
            $cache_array = explode("|", $cacheDb->setting_value);
            foreach ($cache_array as $cache_line) {
                $cacheDb = json_decode(unserialize($cache_line));

                if (!$force_update) $pers_id[] = $cacheDb->pers_id;

                $cache_check = true;
                $test_time = time() - 10800; // *** 86400 = 1 day, 7200 = 2 hours, 10800 = 3 hours ***
                if ($cacheDb->time < $test_time) $cache_check = false;
            }
        }

        if ($force_update) $cache_check = false;

        if ($cache_check == false) {
            // *** First get pers_id, will be quicker in very large family trees ***
            /*
            $person_qry = "(SELECT pers_id, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
                FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NOT NULL AND pers_changed_date!='')
                UNION (SELECT pers_id, STR_TO_DATE(pers_new_date,'%d %b %Y') AS changed_date, pers_new_time as changed_time
                FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NULL)
                ORDER BY changed_date DESC, changed_time DESC LIMIT 0,15";
            */

            $person_qry = "(SELECT pers_id, pers_changed_datetime as changed_datetime
                FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_datetime IS NOT NULL)
                UNION (SELECT pers_id, pers_new_datetime AS changed_datetime
                FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_datetime IS NULL)
                ORDER BY changed_datetime DESC LIMIT 0,15";
            $person_result = $dbh->query($person_qry);
            $count_latest_changes = $person_result->rowCount();
            while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
                // *** Cache: only use cache if there are > 5.000 persons in database ***
                //if (isset($dataDb->tree_persons) AND $dataDb->tree_persons>5000){
                $person->time = time(); // *** Add linux time to array ***
                if ($cache) $cache .= '|';
                $cache .= serialize(json_encode($person));
                $cache_count++;
                //}
                if (!$force_update) $pers_id[] = $person->pers_id;
            }

            // *** Add or renew cache in database (only if cache_count is valid) ***
            if ($cache and ($cache_count == $count_latest_changes)) {
                if ($cache_exists) {
                    //$sql = "UPDATE humo_settings SET setting_variable='cache_latest_changes', setting_value='" . safe_text_db($cache) . "' WHERE setting_tree_id='" . safe_text_db($tree_id) . "' AND setting_variable='cache_latest_changes'";

                    // Because of bug found in jan. 2024, remove value from database and insert again.
                    $sql = "DELETE FROM humo_settings WHERE setting_tree_id='" . safe_text_db($tree_id) . "' AND setting_variable='cache_latest_changes'";
                    $dbh->query($sql);

                    $sql = "INSERT INTO humo_settings SET
                        setting_variable='cache_latest_changes', setting_value='" . safe_text_db($cache) . "', setting_tree_id='" . safe_text_db($tree_id) . "'";
                    $dbh->query($sql);
                } else {
                    $sql = "INSERT INTO humo_settings SET
                    setting_variable='cache_latest_changes', setting_value='" . safe_text_db($cache) . "', setting_tree_id='" . safe_text_db($tree_id) . "'";
                    $dbh->query($sql);
                }
            }
        }
    }

    // *** Show editor notes. $note_connect_kind=person/family ***
    function show_editor_notes($note_connect_kind)
    {
        global $dbh, $tree_id, $pers_gedcomnumber, $field_text_large, $editor_cls, $marriage;

        // *** $note_connect_id = I123 or F123 ***
        $note_connect_id = $pers_gedcomnumber;
        if ($note_connect_kind == 'family') $note_connect_id = $marriage;

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
                if ($num_rows)
                    printf(__('There are %d editor notes.'), $num_rows);
                else
                    printf(__('There are %d editor notes.'), 0);
                ?>
            </td>
            <td></td>
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

                    <textarea rows="1" placeholder="<?= __('Text'); ?>" name="note_note[<?= $noteDb->note_id; ?>]" <?= $field_text_large; ?>><?= $editor_cls->text_show($noteDb->note_note); ?></textarea><br>

                    <?= __('Priority'); ?>
                    <select size="1" name="note_priority[<?= $noteDb->note_id; ?>]">
                        <option value="Low"><?= __('Low'); ?></option>
                        <option value="Normal" <?php if ($noteDb->note_priority == 'Normal') echo ' selected'; ?>><?= __('Normal'); ?></option>
                        <option value="High" <?php if ($noteDb->note_priority == 'High') echo ' selected'; ?>><?= __('High'); ?></option>
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
                <td></td>
            </tr>
    <?php
        }
    }

    function editor_label($label, $style = '')
    {
        $text = '<div class="editor_item">';
        if ($style == 'bold') $text .= '<b>';
        $text .= ucfirst($label);
        if ($style == 'bold') $text .= '</b>';
        $text .= '</div>';
        return $text;
    }
    function editor_label2($label, $style = '')
    {
        //$text = '<span style="display: inline-block; width:220px; vertical-align: top;">';
        $text = '<span style="display: inline-block; width:150px; vertical-align: top;">';
        if ($style == 'bold') $text .= '<b>';
        $text .= ucfirst($label);
        if ($style == 'bold') $text .= '</b>';
        $text .= '</span>';
        return $text;
    }

    function hideshow_date_place($hideshow_date, $hideshow_place)
    {
        // *** If date ends with ! then date isn't valid. Show red line ***
        $check_date = false;
        if (isset($hideshow_date) and substr($hideshow_date, -1) == '!') {
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
        $display = ' display:none;';
        if (!$text) $text = '[' . __('Add') . ']';

        $return_text = '<span class="hideshowlink" onclick="hideShow(' . $hideshow . ');">' . $text;
        if ($check_text) $return_text .= ' <img src="images/text.png" height="16" alt="' . __('text') . '">';
        $return_text .= '</span>';

        $return_text .= '<span class="humo row' . $hideshow . '" style="margin-left:0px;' . $display . '"><br>';

        return $return_text;
    }

    // *** Set same width of columns (in 2 different tables) in tab family ***
    echo '
<script>
$("#chtd1").width($("#target1").width());
$("#chtd2").width($("#target3").width());
$("#chtd3").width($("#target2").width());
</script> ';

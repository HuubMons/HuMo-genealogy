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
 * ----------
 *
 * Copyright (C) 2008-2023 Huub Mons,
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

// *** Don't leave page if there are unsaved items ***
?>
<script src="include/areyousure/jquery.are-you-sure.js"></script>
<script src="include/areyousure/ays-beforeunload-shim.js"></script>

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

//globals for joomla
global $tree_prefix, $gedcom_date, $gedcom_time, $pers_gedcomnumber;



// TODO create seperate controller script.
// TEMPORARY CONTROLLER HERE:
require_once  __DIR__ . "/../models/editor.php";
$get_editor = new Editor($dbh);
$menu_admin = $get_editor->getMenuAdmin();
/*
$main_person = $get_family->getMainPerson();
$family_expanded =  $get_family->getFamilyExpanded();
$source_presentation =  $get_family->getSourcePresentation();
$picture_presentation =  $get_family->getPicturePresentation();
*/
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



$phpself = 'index.php';
$joomlastring = '';
$family_string = '../family.php?';
$sourcestring = '../source.php?';
$addresstring = '../address.php?';
$path_prefix = '../';

$joomlapath = CMS_ROOTPATH_ADMIN . 'include/';

include_once($joomlapath . "editor_cls.php");
$editor_cls = new editor_cls;

//Moved to model:
//include_once(CMS_ROOTPATH . "include/language_date.php");
//include_once(CMS_ROOTPATH . "include/date_place.php");
//include_once(CMS_ROOTPATH . "include/language_event.php");

// *** Used for person color selection for descendants and ancestors, etc. ***
include_once(CMS_ROOTPATH . "include/ancestors_descendants.php");

include('editor_event_cls.php');
$event_cls = new editor_event_cls;


// *****************************
// *** HuMo-genealogy Editor ***
// *****************************

$new_tree = false;

/*  moved to model
$menu_admin = 'person';
if (isset($_GET["menu_admin"])) {
    $menu_admin = $_GET['menu_admin'];
    $_SESSION['admin_menu_admin'] = $menu_admin;
}
if (isset($_SESSION['admin_menu_admin'])) {
    $menu_admin = $_SESSION['admin_menu_admin'];
}
*/

// *** Used for new selected family tree or search person etc. ***
if (isset($_POST["tree_id"])) {
    $pers_gedcomnumber = '';
    unset($_SESSION['admin_pers_gedcomnumber']);
}

// *** Editor icon for admin and editor: select family tree ***
if (isset($tree_id) and $tree_id) {
    $db_functions->set_tree_id($tree_id);
}

// *** Delete session variables for new person ***
if (isset($_POST['person_add'])) {
    unset($_SESSION['admin_pers_gedcomnumber']);
    unset($_SESSION['admin_fam_gedcomnumber']);
}

// *** Save person GEDCOM number ***
$pers_gedcomnumber = '';
if (isset($_POST["person"]) and $_POST["person"]) {
    $pers_gedcomnumber = $_POST['person'];
    $_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;

    $search_id = safe_text_db($_POST['person']);
    $_SESSION['admin_search_id'] = $search_id;

    //$_SESSION['admin_search_name']='';
    //$search_name='';
}
if (isset($_GET["person"])) {
    $pers_gedcomnumber = $_GET['person'];
    $_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;

    $search_id = safe_text_db($_GET['person']);
    $_SESSION['admin_search_id'] = $search_id;

    $_SESSION['admin_search_name'] = '';
    $search_name = '';
}

if (isset($_SESSION['admin_pers_gedcomnumber'])) {
    $pers_gedcomnumber = $_SESSION['admin_pers_gedcomnumber'];
}

// *** Save family GEDCOM number ***
if (isset($pers_gedcomnumber) and $pers_gedcomnumber) {
    $person = $db_functions->get_person($pers_gedcomnumber);

    // *** Person no longer exists! ***
    if (!isset($person->pers_gedcomnumber)) {
        $pers_gedcomnumber = '';
    }
}

$userid = false;
if (is_numeric($_SESSION['user_id_admin'])) $userid = $_SESSION['user_id_admin'];
$username = $_SESSION['user_name_admin'];
$gedcom_date = strtoupper(date("d M Y"));
$gedcom_time = date("H:i:s");

// for jewish settings only for humo_persons table:
if ($humo_option['admin_hebnight'] == "y") {
    $column_qry = $dbh->query('SHOW COLUMNS FROM humo_persons');
    while ($columnDb = $column_qry->fetch()) {
        $field_value = $columnDb['Field'];
        $field[$field_value] = $field_value;
    }
    if (!isset($field['pers_birth_date_hebnight'])) {
        $sql = "ALTER TABLE humo_persons ADD pers_birth_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_birth_date;";
        $result = $dbh->query($sql);
    }
    if (!isset($field['pers_death_date_hebnight'])) {
        $sql = "ALTER TABLE humo_persons ADD pers_death_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_death_date;";
        $result = $dbh->query($sql);
    }
    if (!isset($field['pers_buried_date_hebnight'])) {
        $sql = "ALTER TABLE humo_persons ADD pers_buried_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER pers_buried_date;";
        $result = $dbh->query($sql);
    }

    $column_qry = $dbh->query('SHOW COLUMNS FROM humo_families');
    while ($columnDb = $column_qry->fetch()) {
        $field_value = $columnDb['Field'];
        $field[$field_value] = $field_value;
    }
    if (!isset($field['fam_marr_notice_date_hebnight'])) {
        $sql = "ALTER TABLE humo_families ADD fam_marr_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_notice_date;";
        $result = $dbh->query($sql);
    }
    if (!isset($field['fam_marr_date_hebnight'])) {
        $sql = "ALTER TABLE humo_families ADD fam_marr_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_date;";
        $result = $dbh->query($sql);
    }
    if (!isset($field['fam_marr_church_notice_date_hebnight'])) {
        $sql = "ALTER TABLE humo_families ADD fam_marr_church_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_church_notice_date;";
        $result = $dbh->query($sql);
    }
    if (!isset($field['fam_marr_church_date_hebnight'])) {
        $sql = "ALTER TABLE humo_families ADD fam_marr_church_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER fam_marr_church_date;";
        $result = $dbh->query($sql);
    }

    $column_qry = $dbh->query('SHOW COLUMNS FROM humo_events');
    while ($columnDb = $column_qry->fetch()) {
        $field_value = $columnDb['Field'];
        $field[$field_value] = $field_value;
    }
    if (!isset($field['event_date_hebnight'])) {
        $sql = "ALTER TABLE humo_events ADD event_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER event_date;";
        $result = $dbh->query($sql);
    }
}
// end jewish settings

// *** Child is added, show marriage page ***
if (isset($_POST['child_connect'])) $marriage = $_POST['marriage_nr'];

if (isset($person->pers_fams) and $person->pers_fams) {
    if (isset($_POST["marriage_nr"]) and $_POST["marriage_nr"]) {
        $marriage = $_POST['marriage_nr'];
        $_SESSION['admin_fam_gedcomnumber'] = $marriage;
    }
    if (isset($_GET["marriage_nr"])) {
        $marriage = $_GET['marriage_nr'];
        $_SESSION['admin_fam_gedcomnumber'] = $marriage;
    }

    // *** Get marriage number, also used for 2nd, 3rd etc. relation ***
    if (isset($_SESSION['admin_fam_gedcomnumber'])) {
        $marriage = $_SESSION['admin_fam_gedcomnumber'];
    } else {
        // *** Just in case there is no marriage variable found ***
        $fams1 = explode(";", $person->pers_fams);
        $marriage = $fams1[0];
        $_SESSION['admin_fam_gedcomnumber'] = $marriage;
    }
}


// *** Check for new person ***
$add_person = false;
if (isset($_GET['add_person'])) {
    $add_person = true;
}

if (isset($tree_id)) {
    // *** Process queries ***
    include_once($joomlapath . "editor_inc.php");

    // *** New family tree: no default or selected pers_gedcomnumer, add new person ***
    if ($pers_gedcomnumber == '') {
        // *** Open editor screen first time after starting browser ***
        unset($_SESSION['admin_pers_gedcomnumber']);

        // *** Select first person to show (also check if person still exists) ***
        $new_nr_qry = "SELECT * FROM humo_settings LEFT JOIN humo_persons
            ON setting_value=pers_gedcomnumber
            WHERE setting_variable='admin_favourite'
            AND setting_tree_id='" . safe_text_db($tree_id) . "'
            AND pers_tree_id='" . safe_text_db($tree_id) . "'
            LIMIT 0,1";
        $new_nr_result = $dbh->query($new_nr_qry);

        if ($new_nr_result and $new_nr_result->rowCount()) {
            @$new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);
            $pers_gedcomnumber = $new_nr->setting_value;
            $_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;
        } else {
            $new_nr_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . safe_text_db($tree_id) . "' LIMIT 0,1";
            $new_nr_result = $dbh->query($new_nr_qry);
            @$new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);
            if (isset($new_nr->pers_gedcomnumber)) {
                $pers_gedcomnumber = $new_nr->pers_gedcomnumber;
                $_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;
            }
        }

        // *** New family tree ***
        if ($pers_gedcomnumber == '') {
            $add_person = true;
            $_GET['add_person'] = '1';
            $new_tree = true;
        }
    }

    // *** Select person ***
    $search_name = '';
    $search_id = '';

    if ($add_person == true) {
        $_SESSION['admin_search_name'] = '';
        $_SESSION['admin_search_id'] = '';
    }

    // *** Search person name ***
    if (isset($_POST["search_quicksearch"])) {
        $search_name = safe_text_db($_POST['search_quicksearch']);
        $_SESSION['admin_search_name'] = $search_name;

        $search_id = '';
        $_SESSION['admin_search_id'] = '';
    }
    if (isset($_SESSION['admin_search_name'])) {
        $search_name = $_SESSION['admin_search_name'];
    }


    // *** Search GEDCOM number ***
    if (isset($_POST["search_id"])) {
        $search_id = safe_text_db($_POST['search_id']);
        $_SESSION['admin_search_id'] = $search_id;
        $_SESSION['admin_search_name'] = '';
        $search_name = '';
    }
    if (isset($_SESSION['admin_search_id']))
        $search_id = $_SESSION['admin_search_id'];

    if ($menu_admin == 'person') {
        if ($new_tree == false) {
            // *** Select family tree ***
            echo __('Family tree') . ': ';
            $editor_cls->select_tree($page);

            // *** Favourites ***
            $fav_qry = "SELECT * FROM humo_settings LEFT JOIN humo_persons
                ON setting_value=pers_gedcomnumber
                WHERE setting_variable='admin_favourite'
                AND setting_tree_id='" . safe_text_db($tree_id) . "'
                AND pers_tree_id='" . safe_text_db($tree_id) . "'";
            $fav_result = $dbh->query($fav_qry);
?>
            &nbsp;&nbsp;&nbsp;<img src="<?= CMS_ROOTPATH; ?>images/favorite_blue.png">
            <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                <select size="1" name="person" onChange="this.form.submit();" style="width: 200px">
                    <option value=""><?= __('Favourites list'); ?></option>
                    <?php
                    while ($favDb = $fav_result->fetch(PDO::FETCH_OBJ)) {
                        echo '<option value="' . $favDb->setting_value . '">' . $editor_cls->show_selected_person($favDb) . '</option>';
                    }
                    ?>
                </select>
            </form>
            <?php

            // *** Update cache for list of latest changes ***
            cache_latest_changes();

            ?>
            &nbsp;&nbsp;&nbsp;
            <form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                <select size="1" name="person" onChange="this.form.submit();" style="width: 200px">
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
        <?php
        }

        // *** Show delete message ***
        if ($confirm) echo $confirm;

        if ($new_tree == false) {
        ?>
            <br>
            <table class="humo" style="text-align:left; width:98%; margin-left: initial; margin-right: initial;">
                <tr class="table_header_large">
                    <td>
                        <!-- Search persons firstname/ lastname -->
                        &nbsp;<form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                            <input type="hidden" name="page" value="<?= $page; ?>">
                            <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                            <?= __('Person') . ':'; ?>
                            <input class="fonts" type="text" name="search_quicksearch" placeholder="<?= __('Name'); ?>" value="<?= $search_name; ?>" size="15">
                            <input class="fonts" type="submit" value="<?= __('Search'); ?>">
                        </form>
                        <?php

                        unset($person_result);
                        $idsearch = false; // flag for search with ID;
                        if ($search_name != '') {
                            // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
                            $search_name = str_replace(' ', '%', $search_name);

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
                        } elseif ($search_id != '') {
                            // *** Heredis GEDCOM don't uses I, so don't add an I anymore! ***
                            // *** Make entry "48" into "I48" ***
                            //if(substr($search_id,0,1)!="i" AND substr($search_id,0,1)!="I") {
                            //	$search_id = "I".$search_id;
                            //}
                            $person_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($search_id) . "'";
                            //echo $person_qry;
                            $person_result = $dbh->query($person_qry);

                            $person = $person_result->fetch(PDO::FETCH_OBJ);
                            if ($person) $pers_gedcomnumber = $person->pers_gedcomnumber;

                            $idsearch = true;
                        }


                        if ($idsearch == false and isset($person_result)) {
                            $nr_persons = $person_result->rowCount();
                            // *** No person found ***
                            if ($nr_persons == 0) {
                                echo '<b>' . __('Person not found') . '</b> ';
                                $pers_gedcomnumber = ''; // *** Don't show a person if there are no results ***
                            }
                            // *** Found 1 person ***
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
                                    <select size="1" name="person" style="width: 200px; background-color: #ffaa80;" onChange="this.form.submit();">
                                        <option value=""><?= __('Results'); ?></option>
                                        <?php

                                        $nr_persons = $person_result->rowCount();
                                        while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
                                            // *** Get all person data ***
                                            // Probably not needed at this moment. Query contains all data.
                                            //$person2 = $db_functions->get_person($person->pers_gedcomnumber);
                                            $person2 = $db_functions->get_person_with_id($person->pers_id);
                                            $selected = '';
                                            //if (isset($pers_gedcomnumber)){
                                            if (!isset($_POST["search_quicksearch"]) and isset($pers_gedcomnumber)) {
                                                //if ($person->pers_gedcomnumber==$pers_gedcomnumber){ $selected=' selected'; }
                                                if ($person2->pers_gedcomnumber == $pers_gedcomnumber) {
                                                    $selected = ' selected';
                                                }
                                            }

                                            // *** Directly select first founded person! ***
                                            if ($nr_persons == 1) {
                                                //$pers_gedcomnumber=$person->pers_gedcomnumber;
                                                $pers_gedcomnumber = $person2->pers_gedcomnumber;
                                                $_SESSION['admin_pers_gedcomnumber'] = $pers_gedcomnumber;
                                                $selected = ' selected';

                                                // *** Reset marriage number ***
                                                $fams1 = explode(";", $person->pers_fams);
                                                $marriage = $fams1[0];
                                                $_SESSION['admin_fam_gedcomnumber'] = $marriage;
                                            }
                                            //echo '<option value="'.$person->pers_gedcomnumber.'"'.$selected.'>'.
                                            //	$editor_cls->show_selected_person($person).'</option>';
                                            echo '<option value="' . $person2->pers_gedcomnumber . '"' . $selected . '>' .
                                                $editor_cls->show_selected_person($person2) . '</option>';
                                        }
                                        ?>
                                    </select>
                                </form>
                        <?php
                            }
                            // *** Don't show a person if there are multiple results ***
                            if ($nr_persons > 1 and isset($_POST["search_quicksearch"])) $pers_gedcomnumber = '';
                        }

                        // *** Search person GEDCOM number ***
                        ?>
                        &nbsp;<form method="POST" action="<?= $phpself; ?>?menu_tab=person" style="display : inline;">
                            <input type="hidden" name="page" value="<?= $page; ?>">
                            <input type="hidden" name="tree_id" value="<?= $tree_id; ?>">
                            <?= __('or ID:'); ?>
                            <input class="fonts" type="text" name="search_id" value="<?= $search_id; ?>" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>">
                            <input class="fonts" type="submit" value="<?= __('Search'); ?>">
                        </form>
                        <?php
                        // *** Show message if no person is found ***
                        if ($search_id != '' and $person_result->rowCount() == 0) {
                            echo '<b>' . __('Person not found') . '</b>';
                            $pers_gedcomnumber = ''; // *** Don't show a person if there are no results ***
                        }

                        // *** Add new person ***
                        echo '&nbsp;&nbsp;&nbsp; <a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;menu_admin=person&amp;add_person=1">
            <img src="' . CMS_ROOTPATH_ADMIN . 'images/person_connect.gif" border="0" title="' . __('Add person') . '" alt="' . __('Add person') . '"> ' .
                            __('Add person') . '</a>';

                        // HELP POPUP
                        //echo '<div class="fonts '.$rtlmarker.'sddm" style="border:1px solid #d8d8d8; margin-top:2px; display:inline;">';
                        echo '&nbsp;&nbsp;&nbsp;&nbsp;<div class="fonts ' . $rtlmarker . 'sddm" style="display:inline;">';
                        echo '<a href="#" style="display:inline" ';
                        echo 'onmouseover="mopen(event,\'help_menu\',10,150)"';
                        echo ' onmouseout="mclosetime()">';
                        echo '<img src="../images/help.png" height="16" width="16">';
                        echo '</a>';
                        //echo '<div class="sddm_fixed" style="'.$popwidth.' z-index:400; text-align:'.$alignmarker.'; padding:4px; direction:'.$rtlmarker.'" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
                        echo '<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:' . $rtlmarker . '" id="help_menu" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
                        echo __('Examples of date entries:') . '<br>';
                        echo '<b>' . __('13 october 1813, 13 oct 1813, 13-10-1813, 13/10/1813, 13.10.1813, 13,10,1813, between 1986 and 1987, 13 oct 1100 BC.') . '</b><br>';
                        echo __('In all text fields it\'s possible to add a hidden text/ own remarks by using # characters. Example: #Check birthday.#') . '<br>';

                        echo '<img src="../images/search.png" alt="' . __('Search') . '"> ' . __('= click to open selection popup screen.') . '<br>';
                        echo ' <b>[+]</b> ' . __('= click to open extended editor items.');
                        echo '</div>';
                        echo '</div>';
                        ?>
                    </td>
                </tr>
            </table>
        <?php
            //ob_flush(); flush(); // IE
        } // *** end of check for new tree ***

    } else {
        echo '<br>';
    }
}


$check_person = false;
if (isset($pers_gedcomnumber)) {
    if ($new_tree == false and $add_person == false and !$pers_gedcomnumber) $check_person = false;

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
    if (!$person and $new_tree == false and $add_person == false) $check_person = false;
}
if ($new_tree) $check_person = true;
if ($check_person) {
    // *** Exit if selection of person is needed ***
    //if ($new_tree==false AND $add_person==false AND !$pers_gedcomnumber) exit;

    // *** Get person data to show name and calculate nr. of items ***
    //$person = $db_functions->get_person($pers_gedcomnumber);
    //if (!$person AND $new_tree==false AND $add_person==false) exit;

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

    if ($menu_admin == 'person' and isset($tree_prefix)) {
        //echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="margin-left:210px; width:900px;">';
        echo '<p><div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false" style="width:900px;">';
        echo '<div class="pageHeading">';
        echo '<div class="pageTabsContainer" aria-hidden="false" style="">';
        echo '<ul class="pageTabs">';
        //echo '<li class="pageTabItem"><div tabindex="0" class="pageTab pageTab-active">Details</div></li>';

        $select_item = '';
        if ($menu_tab == 'person') {
            $select_item = ' pageTab-active';
        }
        echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '"><a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;menu_tab=person">' . __('Person') . "</a></div></li>";

        if (!isset($_GET['add_person'])) {
            // *** Family tree data ***
            $select_item = '';
            if ($menu_tab == 'marriage') {
                $select_item = ' pageTab-active';
            }
            //echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_tab=marriage">'.__('Marriage(s) and children');
            echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '">';
            //echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_tab=marriage">'.ucfirst(__('marriage/ relation'));
            //echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_tab=marriage">'.ucfirst(__('marriage')).'/ '.__('Children');

            // *** Add familynumber in link (needed for multiple relations/ marriages) ***
            //$fams1=explode(";",$person->pers_fams); $first_marriage=$fams1[0];
            //echo '<a href="index.php?'.$joomlastring.'page='.$page.'&amp;marriage_nr='.$first_marriage.
            //'&amp;menu_tab=marriage">'.ucfirst(__('marriage')).'/ '.__('Children');
            $fams1 = explode(";", $person->pers_fams);
            $first_marriage = $fams1[0];
            echo '<a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;marriage_nr=' . $first_marriage .
                '&amp;menu_tab=marriage">' . __('Family');

            //if (isset($marriage)) echo ' *';
            echo "</a></div></li>";

            //$select_item=''; if ($menu_tab=='children'){ $select_item=' pageTab-active'; }
            //echo '<li class="pageTabItem"><div tabindex="0" class="pageTab'.$select_item.'"><a href="index.php?'.$joomlastring.'page='.$page.'&amp;menu_tab=children">'.__('Children')."</a></div></li>";
        }

        if ($person) {
            // *** Browser through persons: previous button ***
            if (substr($person->pers_gedcomnumber, 1) > 1) {
                // *** First do a quick check, much faster for large family trees!!!!! ***
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
                    //if ($previousDb){
                    //	echo '<form method="POST" action="'.$phpself.'?menu_tab=person" style="display : inline;">';
                    //		echo '<input type="hidden" name="page" value="'.$page.'">';
                    //		echo '<input type="hidden" name="person" value="'.$previousDb->pers_gedcomnumber.'">';
                    //		echo ' <input type="submit" value="<">';
                    //	echo '</form>';
                    //}
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
                echo '<form method="POST" action="' . $phpself . '?menu_tab=person" style="display : inline;">';
                echo '<input type="hidden" name="page" value="' . $page . '">';
                echo '<input type="hidden" name="person" value="' . $firstDb->pers_gedcomnumber . '">';
                echo ' <input type="submit" value="<<">';
                echo '</form>';

                if ($previousDb) {
                    echo '<form method="POST" action="' . $phpself . '?menu_tab=person" style="display : inline;">';
                    echo '<input type="hidden" name="page" value="' . $page . '">';
                    echo '<input type="hidden" name="person" value="' . $previousDb->pers_gedcomnumber . '">';
                    echo ' <input type="submit" value="<">';
                    echo '</form>';
                }
            }

            // *** Browser through persons: previous button ***
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
                echo ' <form method="POST" action="' . $phpself . '?menu_tab=person" style="display : inline;">';
                echo '<input type="hidden" name="page" value="' . $page . '">';
                echo '<input type="hidden" name="person" value="' . $nextDb->pers_gedcomnumber . '">';
                echo ' <input type="submit" value=">">';
                echo '</form>';
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
                    echo '<form method="POST" action="' . $phpself . '?menu_tab=person" style="display : inline;">';
                    echo '<input type="hidden" name="page" value="' . $page . '">';
                    echo '<input type="hidden" name="person" value="' . $lastDb->pers_gedcomnumber . '">';
                    echo ' <input type="submit" value=">>">';
                    echo '</form>';
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
        echo '&nbsp;&nbsp;<div class="fonts ' . $rtlmarker . 'sddm" style="display:inline;">';
        echo '<a href="#" style="display:inline" ';
        echo 'onmouseover="mopen(event,\'browse_menu\',0,0)"';
        echo 'onmouseout="mclosetime()">';
        //echo '***'.__('Navigate').'***</a>';
        echo '[' . __('Browse') . ']</a>';
        echo '<div class="sddm_fixed"
            style="text-align:left; z-index:400; padding:4px; border: 1px solid rgb(153, 153, 153);
            direction:' . $rtlmarker . ';
            box-shadow: 2px 2px 2px #999;
            border-radius: 3px;"
            id="browse_menu"
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
                                    <a href="index.php?<?= $joomlastring; ?>page=editor&amp;menu_tab=marriage&amp;marriage_nr=<?= $familyDb->fam_gedcomnumber; ?>"><b><?= $show_marr_status; ?></b></a>
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

        //echo '</div>';
        echo '</div>';
        echo '</div>';
        // *** End of browse pop-up ***

        // *** Example of family screen in pop-up ***
        if ($person) {
            // Onderstaande person_url2 werkt niet altijd goed!!!
            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            //$popup_cls = New person_cls;
            //$url=$popup_cls->person_url2($person->pers_tree_id,$person->pers_famc,$person->pers_fams,$person->pers_gedcomnumber);
            //echo " <a href=\"#\" onClick=\"window.open('".CMS_ROOTPATH.$url."', '','width=800,height=500')\"><b>[".__('Preview').']</b></a>';

            $pers_family = '';
            if ($person->pers_famc) {
                $pers_family = $person->pers_famc;
            }
            if ($person->pers_fams) {
                $person_fams = explode(';', $person->pers_fams);
                $pers_family = $person_fams[0];
            }
            echo " <a href=\"#\" onClick=\"window.open('../family.php?tree_id=" . $person->pers_tree_id . "&amp;id=" . $pers_family . "&amp;main_person=" . $person->pers_gedcomnumber . "', '','width=800,height=500')\"><b>[" . __('Preview') . ']</b></a>';
        }

        echo '</ul>';
        echo '</div>';
        echo '</div>';
        echo '</div>';

        // *** Align content to the left ***
        //echo '<div style="float: left; background-color:white; height:500px; padding:10px;">';
        //echo '<div style="float: left; background-color:white; height:500px; margin-left:205px; padding-top:10px;">';
        echo '<div style="float: left; background-color:white; height:500px; padding:10px;">';

        //ob_flush(); flush(); // IE
    }

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
    //$field_text = 'style="height: 18px; width:550px;"';
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

    if ($menu_admin == 'person') {

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

        if ($menu_tab == 'person') {
            // *** Don't leave page if there are unsaved items ***
            echo "
            <script>
            $(function() {
                // Enable on selected forms
                $('#form1').areYouSure();
            });
            </script>";
        ?>
            <!-- Start of editor table -->
            <form method="POST" action="<?= $phpself; ?>" style="display : inline;" enctype="multipart/form-data" name="form1" id="form1">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="person" value="<?= $pers_gedcomnumber; ?>">

                <!-- Date needed to check if birth or baptise date is changed -->
                <input type="hidden" name="pers_birth_date_previous" value="<?= $pers_birth_date; ?>">
                <input type="hidden" name="pers_bapt_date_previous" value="<?= $pers_bapt_date; ?>">

                <!-- <table class="humo" border="1" style="line-height: 180%;"> -->
                <table class="humo" border="1" style="line-height: 150%;">
                    <?php
                    // *** Show mother and father with a link ***
                    if ($add_person == false) {
                        // *** Update settings ***
                        if (isset($_POST['admin_online_search'])) {
                            if ($_POST['admin_online_search'] == 'y' or $_POST['admin_online_search'] == 'n') {
                                $result = $db_functions->update_settings('admin_online_search', $_POST["admin_online_search"]);
                                $humo_option["admin_online_search"] = $_POST['admin_online_search'];
                            }
                        }

                    ?>
                        <!-- Open Archives -->
                        <tr>
                            <th class="table_header_large" colspan="4"><?= __('Open Archives'); ?>
                                <!-- Ignore the Are You Sure script -->
                                &nbsp;&nbsp;&nbsp;&nbsp;
                                <select size="1" name="admin_online_search" onChange="this.form.submit();" class="ays-ignore">
                                    <option value="y"><?= __('Online search enabled'); ?></option>
                                    <option value="n" <?php if ($humo_option["admin_online_search"] != 'y') echo ' selected'; ?>><?= __('Online search disabled'); ?></option>
                                </select>
                                <?php

                                // *** Show archive list ***
                                // *** Change CSS links ***
                                /*
                                echo '
                                    <style>
                                    .ltrsddm div a {
                                        display:inline;
                                        padding: 0px;
                                    }
                                    </style>';
                                */
                                // *** Show navigation pop-up ***
                                echo '&nbsp;&nbsp;<div class="fonts ' . $rtlmarker . 'sddm" style="display:inline;">';
                                echo '<a href="#" style="display:inline" ';
                                echo 'onmouseover="mopen(event,\'archive_menu\',0,0)"';
                                echo 'onmouseout="mclosetime()">';
                                echo '[' . __('Archives') . ']</a>';
                                //echo '<div class="sddm_fixed"
                                //	style="text-align:left; z-index:400; padding:4px;
                                //	direction:'.$rtlmarker.'"
                                //	id="browse_menu"
                                //	onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
                                echo '<div class="sddm_fixed"
                                    style="text-align:left; z-index:400; padding:4px; border: 1px solid rgb(153, 153, 153);
                                    direction:' . $rtlmarker . ';
                                    box-shadow: 2px 2px 2px #999;
                                    border-radius: 3px;"
                                    id="archive_menu"
                                    onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';

                                // *** Show box with list link to archives ***
                                if ($add_person == false) {
                                    $OAfromyear = '';
                                    if ($person->pers_birth_date) {
                                        if (substr($person->pers_birth_date, -4)) $OAfromyear = substr($person->pers_birth_date, -4);
                                    } elseif ($person->pers_bapt_date) {
                                        if (substr($person->pers_bapt_date, -4)) $OAfromyear = substr($person->pers_bapt_date, -4);
                                    }

                                    // *** Show person ***
                                    //echo '<b>'.__('Person').'</b><br>';
                                    //echo '<span style="font-weight:bold; font-size:12px">'.show_person($person->pers_gedcomnumber).'</span><br>';
                                    //echo show_person($person->pers_gedcomnumber).'<br>';
                                    echo show_person($person->pers_gedcomnumber, false, false) . '<br><br>';

                                    // *** GeneaNet ***
                                    // https://nl.geneanet.org/fonds/individus/?size=10&amp;
                                    //nom=Heijnen&prenom=Andreas&ampprenom_operateur=or&amp;place__0__=Wouw+Nederland&amp;go=1
                                    $link = 'https://geneanet.org/fonds/individus/?size=10&amp;nom=' . urlencode($person->pers_lastname) . '&amp;prenom=' . urlencode($person->pers_firstname);
                                    //if ($OAfromyear!='') $link.='&amp;birthdate_from='.$OAfromyear.'&birthdate_until='.$OAfromyear;
                                    echo '<a href="' . $link . '&amp;go=1" target="_blank">Geneanet.org</a><br><br>';

                                    // *** StamboomZoeker.nl ***
                                    // UITLEG: https://www.stamboomzoeker.nl/page/16/zoekhulp
                                    // sn: Familienaam
                                    // fn: Voornaam
                                    // bd: Twee geboortejaren met een streepje (-) er tussen
                                    // bp: Geboorteplaats
                                    // http://www.stamboomzoeker.nl/?a=search&fn=andreas&sn=heijnen&np=1&bd1=1655&bd2=1655&bp=wouw+nederland
                                    $link = 'http://www.stamboomzoeker.nl/?a=search&amp;fn=' . urlencode($person->pers_firstname) . '&amp;sn=' . urlencode($person->pers_lastname);
                                    if ($OAfromyear != '') $link .= '&amp;bd1=' . $OAfromyear . '&amp;bd2=' . $OAfromyear;
                                    echo '<a href="' . $link . '" target="_blank">Familytreeseeker.com/ StamboomZoeker.nl</a><br><br>';

                                    // *** GenealogieOnline ***
                                    //https://www.genealogieonline.nl/zoeken/index.php?q=mons&vn=nikus&pn=harderwijk
                                    $link = 'https://genealogieonline.nl/zoeken/index.php?q=' . urlencode($person->pers_lastname) . '&amp;vn=' . urlencode($person->pers_firstname);
                                    //if ($OAfromyear!='') $link.='&amp;bd1='.$OAfromyear.'&amp;bd2='.$OAfromyear;
                                    echo '<a href="' . $link . '" target="_blank">Genealogyonline.nl/ Genealogieonline.nl</a><br><br>';

                                    // FamilySearch
                                    //https://www.familysearch.org/search/record/results?q.givenName=Marie&q.surname=CORNEZ&count=20
                                    $link = 'http://www.familysearch.org/search/record/results?count=20&q.givenName=' . urlencode($person->pers_firstname) . '&q.surname=' . urlencode($person->pers_lastname);
                                    //if ($OAfromyear!='') $link.='&amp;birthdate_from='.$OAfromyear.'&amp;birthdate_until='.$OAfromyear;
                                    echo '<a href="' . $link . '" target="_blank">FamilySearch</a><br><br>';

                                    // *** GrafTombe ***
                                    // http://www.graftombe.nl/names/search?forename=Andreas&surname=Heijnen&birthdate_from=1655
                                    // &amp;birthdate_until=1655&amp;submit=Zoeken&amp;r=names-search
                                    $link = 'http://www.graftombe.nl/names/search?forename=' . urlencode($person->pers_firstname) . '&amp;surname=' . urlencode($person->pers_lastname);
                                    if ($OAfromyear != '') $link .= '&amp;birthdate_from=' . $OAfromyear . '&amp;birthdate_until=' . $OAfromyear;
                                    echo '<a href="' . $link . '&amp;submit=Zoeken&amp;r=names-search" target="_blank">Graftombe.nl</a><br><br>';

                                    // *** WieWasWie ***
                                    // https://www.wiewaswie.nl/nl/zoeken/?q=Andreas+Adriaensen+Heijnen
                                    $link = 'https://www.wiewaswie.nl/nl/zoeken/?q=' . urlencode($person->pers_firstname) .
                                        '+' . urlencode($person->pers_lastname);
                                    //if ($OAfromyear!='') $link.='&amp;birthdate_from='.$OAfromyear.'&amp;birthdate_until='.$OAfromyear;
                                    echo '<a href="' . $link . '" target="_blank">WieWasWie</a><br><br>';

                                    // *** StamboomOnderzoek ***
                                    // https://www.stamboomonderzoek.com/default/search.php?
                                    // myfirstname=Andreas&mylastname=Heijnen&lnqualify=startswith&mybool=AND&showdeath=1&tree=-x--all--x-
                                }

                                echo '</div>';
                                echo '</div>';
                                // *** End of archive list pop-up ***
                                ?>
                            </th>
                        </tr>
                        <?php
                        if ($humo_option["admin_online_search"] == 'y') {

                            function openarchives_new($name, $year_or_period)
                            {
                                if (function_exists('curl_exec')) {
                                    $ch = curl_init();
                                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                                    $OAapi = 'https://api.openarch.nl/1.0/records/search.json?name=';
                                    $OAurl = $OAapi . urlencode($name . $year_or_period);   # via urlencode, zodat ook andere tekens dan spatie juist worden gecodeerd

                                    curl_setopt($ch, CURLOPT_URL, $OAurl);
                                    $result = curl_exec($ch);
                                    curl_close($ch);

                                    $jsonData = json_decode($result, TRUE);
                                    echo '<tr class="humo_color"><td colspan="4">';
                                    echo '<b>' . __('Search') . ': <a href="https://www.openarch.nl/search.php?name=' . urlencode($name . $year_or_period) .
                                        '" target="_blank">https://www.openarch.nl/search.php?name=' . $name . $year_or_period . '</a></b><br>';
                                    echo '</td></tr>';
                                    if (isset($jsonData["response"]["docs"]) and count($jsonData["response"]["docs"]) > 0) {
                                        foreach ($jsonData["response"]["docs"] as $OAresult) {   # het voordeel van JSON/json_dcode is dat je er eenvoudig mee kunt werken (geen Iterator nodig)
                                            $OAday = '';
                                            if (isset($OAresult["eventdate"]["day"])) $OAday = $OAresult["eventdate"]["day"];
                                            //$OAmonthName=date('M', mktime(0, 0, 0, $OAresult["eventdate"]["archive"], 10));   # laat PHP zelf de maandnaam maken
                                            $OAmonthName = '';
                                            if (isset($OAresult["eventdate"]["month"]))
                                                $OAmonthName = date('M', mktime(0, 0, 0, $OAresult["eventdate"]["month"], 10));   # laat PHP zelf de maandnaam maken
                                            $OAyear = '';
                                            if (isset($OAresult["eventdate"]["year"])) $OAyear = $OAresult["eventdate"]["year"];
                                            $OAeventdate = join(" ", array($OAday, $OAmonthName, $OAyear));

                                            echo '<tr><td colspan="4">';
                                            echo '<a href="' . $OAresult["url"] . '" target="openarch.nl">';   # geen aparte 'link' maar heeft de regel als link, door target steeds zelfde window
                                            echo $OAresult["personname"] . ' (' . $OAresult["relationtype"] . ')';
                                            echo ', ' . $OAresult["eventtype"] . ' ' . $OAeventdate . ' ' . $OAresult["eventplace"];
                                            echo ', ' . $OAresult["archive"] . '/' . $OAresult["sourcetype"];
                                            echo '</a></td></tr>';
                                        }
                                    } else {
                                        echo '<tr><td colspan="4">' . __('No results found') . '</td></tr>';
                                    }
                                }
                            }

                            # Bepaal te zoeken jaar of periode (waardoor er maar één zoekactie is benodigd)
                            $OAfromyear = '';
                            if ($person->pers_birth_date) {
                                if (substr($person->pers_birth_date, -4)) $OAfromyear = substr($person->pers_birth_date, -4);
                            } elseif ($person->pers_bapt_date) {
                                if (substr($person->pers_bapt_date, -4)) $OAfromyear = substr($person->pers_bapt_date, -4);
                            }

                            $OAuntilyear = '';
                            if ($person->pers_death_date) {
                                if (substr($person->pers_death_date, -4)) $OAuntilyear = substr($person->pers_death_date, -4);
                            } elseif ($person->pers_buried_date) {
                                if (substr($person->pers_buried_date, -4)) $OAuntilyear = substr($person->pers_buried_date, -4);
                            }

                            $OAsearchname = $person->pers_firstname . ' ' . $person->pers_lastname;

                            openarchives_new($OAsearchname, ' ' . $OAfromyear);

                            if ($OAuntilyear) {
                                openarchives_new($OAsearchname, ' ' . $OAuntilyear);
                            }

                            if ($OAfromyear or $OAuntilyear) {
                                $OAyear_or_period = '';
                                if ($OAfromyear != '' && $OAuntilyear == '') {
                                    $OAyear_or_period = ' ' . $OAfromyear . '-' . ($OAfromyear + 100);
                                }
                                if ($OAfromyear == '' && $OAuntilyear != '') {
                                    $OAyear_or_period = ' ' . ($OAuntilyear - 100) . '-' . $OAuntilyear;
                                }
                                if ($OAfromyear != '' && $OAuntilyear != '') {
                                    $OAyear_or_period = ' ' . $OAfromyear . '-' . $OAuntilyear;
                                }
                                if (isset($_POST['search_period'])) {
                                    openarchives_new($OAsearchname, $OAyear_or_period);
                                } else {
                                    echo '<tr class="humo_color"><td colspan="4"><input type="Submit" name="search_period" value="' . __('Search using period') . '">';
                                    echo ' <b>' . __('Search') . ': <a href="https://www.openarch.nl/search.php?name=' . urlencode($OAsearchname . $OAyear_or_period) .
                                        '" target="_blank">https://www.openarch.nl/search.php?name=' . $OAsearchname . $OAyear_or_period . '</a></b><br>';
                                    echo '</td></tr>';
                                }
                            }
                        }

                        // *** Empty line in table ***
                        //echo '<tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr>';

                        // *** April 2023, temporary text ***
                        echo '<tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">';
                        echo __('If links in this page are not good visible, refresh the page once using: [CTRL] - Refresh.');
                        echo '&nbsp;</td></tr>';

                        //ob_flush(); flush(); // IE


                        //echo '<tr><th class="table_header_large" colspan="4">'.ucfirst(__('parents')).'</tr>';

                        echo '<tr><td><b>' . ucfirst(__('parents')) . '</b></td><td colspan="3">';
                        $parent_text = '';

                        if ($person->pers_famc) {
                            // *** Search for parents ***
                            $family_parentsDb = $db_functions->get_family($person->pers_famc, 'man-woman');

                            //*** Father ***
                            if ($family_parentsDb->fam_man) $parent_text .= show_person($family_parentsDb->fam_man);
                            //	else $parent_text=__('N.N.');

                            $parent_text .= ' ' . __('and') . ' ';

                            //*** Mother ***
                            if ($family_parentsDb->fam_woman) $parent_text .= show_person($family_parentsDb->fam_woman);
                            //	else $parent_text.=__('N.N.');
                        } else {
                            $hideshow = 701;
                        ?>
                            <!-- Add existing or new parents -->
                            <b><?= __('There are no parents.'); ?></b><a href="index.php?<?= $joomlastring; ?>page=<?= $page; ?>&amp;menu_admin=person&amp;add_parents=1">
                                <a href="#" onclick="hideShow('<?= $hideshow; ?>');"><?= __('Add parents'); ?></a>
                                <span class="humo row701" style="margin-left:0px; display:none;">
                                    <table class="humo" style="margin-left:0px;">
                                        <tr class="table_header">
                                            <th></th>
                                            <th><?= __('Father'); ?></th>
                                            <th><?= __('Mother'); ?></th>
                                        </tr>
                                        <tr>
                                            <td><b><?= __('firstname'); ?></b></td>
                                            <td><input type="text" name="pers_firstname1" value="" size="35" placeholder="<?= ucfirst(__('firstname')); ?>"></td>
                                            <td><input type="text" name="pers_firstname2" value="" size="35" placeholder="<?= ucfirst(__('firstname')); ?>"></td>
                                        </tr>
                                        <tr>
                                            <td><?= __('prefix'); ?></td>
                                            <!-- HELP POPUP for prefix -->
                                            <td><input type="text" name="pers_prefix1" value="<?= $pers_prefix; ?>" size="10" placeholder="<?= ucfirst(__('prefix')); ?>">
                                                <div class="fonts <?= $rtlmarker; ?>sddm" style="display:inline;">
                                                    <a href="#" style="display:inline" onmouseover="mopen(event,'help_prefix',100,400)" onmouseout="mclosetime()">
                                                        <img src="../images/help.png" height="16" width="16">
                                                    </a>
                                                    <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_prefix" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                                        <b><?= __("For example: d\' or:  van_ (use _ for a space)"); ?></b><br>
                                                    </div>
                                                </div>
                                            </td>

                                            <!-- HELP POPUP for prefix -->
                                            <td><input type="text" name="pers_prefix2" value="" size="10" placeholder="<?= ucfirst(__('prefix')); ?>">
                                                <div class="fonts <?= $rtlmarker; ?>sddm" style="display:inline;">
                                                    <a href="#" style="display:inline" onmouseover="mopen(event,'help_prefix',100,400)" onmouseout="mclosetime()">
                                                        <img src="../images/help.png" height="16" width="16">
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>

                                        <!-- Lastname -->
                                        <tr>
                                            <td><b><?= __('lastname'); ?></b></td>
                                            <td>
                                                <input type="text" name="pers_lastname1" value="<?= $pers_lastname; ?>" size="35" placeholder="<?= ucfirst(__('lastname')); ?>">
                                            </td>
                                            <td><input type="text" name="pers_lastname2" value="" size="35" placeholder="<?= ucfirst(__('lastname')); ?>"></td>
                                        </tr>

                                        <!--  Patronym -->
                                        <tr>
                                            <td><?= __('patronymic'); ?></td>
                                            <td>
                                                <input type="text" name="pers_patronym1" value="<?= $pers_patronym; ?>" size="35" placeholder="<?= ucfirst(__('patronymic')); ?>">
                                            </td>
                                            <td><input type="text" name="pers_patronym2" value="" size="35" placeholder="<?= ucfirst(__('patronymic')); ?>"></td>
                                        </tr>

                                        <tr>
                                            <td><br>
                                            </td>
                                            <td>
                                                <select size="1" name="event_gedcom_add1" style="width: 150px">
                                                    <!-- Nickname, alias, adopted name, hebrew name, etc. -->
                                                    <?php event_selection($data_listDb->event_gedcom); ?>
                                                </select><br>
                                                <input type="text" name="event_event_name1" placeholder="<?= __('Nickname') . ' - ' . __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>" value="" size="35">
                                            </td>
                                            <td>
                                                <select size="1" name="event_gedcom_add2" style="width: 150px">
                                                    <!-- Nickname, alias, adopted name, hebrew name, etc. -->
                                                    <?php event_selection($data_listDb->event_gedcom); ?>
                                                </select><br>
                                                <input type="text" name="event_event_name2" placeholder="<?= __('Nickname') . ' - ' . __('Prefix') . ' - ' . __('Suffix') . ' - ' . __('Title'); ?>" value="" size="35">
                                            </td>
                                        </tr>

                                        <!-- Privacy filter -->
                                        <tr>
                                            <td><?= __('Privacy filter'); ?></td>
                                            <td>
                                                <input type="radio" name="pers_alive1" value="alive"><?= __('alive'); ?>
                                                <input type="radio" name="pers_alive1" value="deceased"><?= __('deceased'); ?>
                                            </td>
                                            <td>
                                                <input type="radio" name="pers_alive2" value="alive"><?= __('alive'); ?>
                                                <input type="radio" name="pers_alive2" value="deceased"><?= __('deceased'); ?>
                                            </td>
                                        </tr>

                                        <tr>
                                            <td><?= __('Sex'); ?></td>
                                            <td>
                                                <input type="radio" name="pers_sexe1" value="M" checked><?= __('male'); ?>
                                                <input type="radio" name="pers_sexe1" value="F"><?= __('female'); ?>
                                                <input type="radio" name="pers_sexe1" value="">?
                                            </td>
                                            <td>
                                                <input type="radio" name="pers_sexe2" value="M"><?= __('male'); ?>
                                                <input type="radio" name="pers_sexe2" value="F" checked><?= __('female'); ?>
                                                <input type="radio" name="pers_sexe2" value=""> ?
                                            </td>
                                        </tr>

                                        <!-- Profession -->
                                        <tr>
                                            <td><?= __('Profession'); ?></td>
                                            <td>
                                                <input type="text" name="event_profession1" placeholder="<?= __('Profession'); ?>" value="" size="35">
                                            </td>
                                            <td>
                                                <input type="text" name="event_profession2" placeholder="<?= __('Profession'); ?>" value="" size="35">
                                            </td>
                                        </tr>

                                        <tr class="humo_color">
                                            <td colspan="3"><input type="Submit" name="add_parents2" value="<?= __('Add parents'); ?>"></td>
                                        </tr>
                                    </table><br>

                                    <?= __('Or select an existing family as parents:'); ?>
                                    <input class="fonts" type="text" name="add_parents" placeholder="<?= __('GEDCOM number (ID)'); ?>" value="" size="20">
                                    <a href="#" onClick='window.open("index.php?page=editor_relation_select","","<?= $field_popup; ?>")'><img src="../images/search.png" alt=<?= __('Search'); ?>></a>
                                    <input type="Submit" name="dummy2" value="<?= __('Select'); ?>">
                                </span> <!-- End of hide item -->
                            <?php
                        }

                        echo $parent_text . '</td></tr>';

                        // *** Show message if age < 0 or > 120 ***
                        $error_color = '';
                        $show_message = '&nbsp;';
                        if (($person->pers_bapt_date or $person->pers_birth_date) and $person->pers_death_date) {
                            include_once(CMS_ROOTPATH . "include/calculate_age_cls.php");
                            $process_age = new calculate_year_cls;
                            $age = $process_age->calculate_age($person->pers_bapt_date, $person->pers_birth_date, $person->pers_death_date, true);
                            if ($age and ($age < 0 or $age > 120)) {
                                $error_color = 'background-color:#FFAA80;';
                                $show_message = '&nbsp;' . __('age') . ' ' . $age . ' ' . __('year');
                            }
                        }

                            ?>
                            <tr>
                                <!-- Show empty line or error message in table -->
                                <td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;<?= $error_color; ?>">
                                    <?= $show_message; ?>
                                </td>
                            </tr>
                        <?php
                    }
                        ?>
                        <tr class="table_header_large">
                            <?php
                            // *** Hide or show all hide-show items ***
                            $hide_show_all = '<a href="#" onclick="hideShowAll();"><span id="hideshowlinkall">[+]</span> ' . __('All') . '</a> ';

                            if ($add_person == false) {
                                echo '<td>' . $hide_show_all . ' <input type="Submit" name="person_remove" value="' . __('Delete person') . '"></td>';
                                //echo '<td style="border-right: none"></td>';
                            } else {
                                // *** New person: no delete example link ***
                                echo '<td>' . $hide_show_all . '</td>';
                                //echo '<td style="border-right: none"><br></td>';
                            }

                            //echo '<th style="border-left: none; text-align:left;">'.__('Person');
                            echo '<th style="border-left: none; text-align:left; font-size: 1.5em;" colspan="2">';

                            if ($add_person == false) {
                                //echo ': ['.$pers_gedcomnumber.'] '.show_person($person->pers_gedcomnumber,false,false);
                                echo '[' . $pers_gedcomnumber . '] ' . show_person($person->pers_gedcomnumber, false, false);

                                // *** Add person to admin favourite list ***
                                $fav_qry = "SELECT * FROM humo_settings
                                    WHERE setting_variable='admin_favourite'
                                    AND setting_tree_id='" . safe_text_db($tree_id) . "'
                                    AND setting_value='" . $pers_gedcomnumber . "'";
                                $fav_result = $dbh->query($fav_qry);
                                $rows = $fav_result->rowCount();
                                if ($rows > 0)
                                    echo '<a href="' . $phpself . '?page=editor&amp;person=' . $pers_gedcomnumber . '&amp;pers_favorite=0"><img src="' . CMS_ROOTPATH . 'images/favorite_blue.png" style="border: 0px"></a>';
                                else
                                    echo '<a href="' . $phpself . '?page=editor&amp;person=' . $pers_gedcomnumber . '&amp;pers_favorite=1"><img src="' . CMS_ROOTPATH . 'images/favorite.png" style="border: 0px"></a>';
                                echo '<br>';
                            }
                            echo '</th><td>';

                            if ($add_person == false) {
                                echo '<input type="Submit" name="person_change" value="' . __('Save') . '">';
                            } else {
                                echo '<input type="Submit" name="person_add" value="' . __('Add') . '">';
                            }
                            ?>
                            </td>
                        </tr>

                        <tr>
                            <!-- Name-->
                            <?php
                            $hideshow = '1';
                            $display = ' display:none;';
                            // *** New person: show all name fields ***
                            if (!$pers_gedcomnumber) $display = '';
                            ?>
                            <td><a name="name"></a><b><?= __('Name'); ?></b></td>
                            <td colspan="2">
                                <?php
                                // *** Use hideshow to show and hide the editor lines ***
                                if ($pers_gedcomnumber) {
                                    echo '<span class="hideshowlink" onclick="hideShow(' . $hideshow . ');"><b>';
                                    echo '[' . $pers_gedcomnumber . '] ' . show_person($person->pers_gedcomnumber, false, false);
                                    if ($pers_name_text) echo ' <img src="images/text.png" height="16">';
                                    echo '</b></span><br>';
                                }
                                echo '<span class="humo row' . $hideshow . '" style="margin-left:0px;' . $display . '">';

                                // *** Firstname ***
                                //echo '<input type="text" name="pers_firstname" value="'.$pers_firstname.'"  size="35" placeholder="'.ucfirst(__('firstname')).'"><br>';
                                echo editor_label(__('firstname'), 'bold');
                                echo '<input type="text" name="pers_firstname" value="' . $pers_firstname . '"  size="35"><br>';

                                // *** Prefix ***
                                //echo '<input type="text" name="pers_prefix" value="'.$pers_prefix.'" size="10" placeholder="'.ucfirst(__('prefix')).'"> '.__("For example: d\' or:  van_ (use _ for a space)").'<br>';
                                echo editor_label(__('prefix') . '. ' . __("For example: d\' or:  van_ (use _ for a space)"));
                                echo '<input type="text" name="pers_prefix" value="' . $pers_prefix . '" size="10"><br>';

                                // *** Lastname ***
                                //echo '<input type="text" name="pers_lastname" value="'.$pers_lastname.'" size="35" placeholder="'.ucfirst(__('lastname')).'"> ';
                                //echo __('patronymic').' <input type="text" name="pers_patronym" value="'.$pers_patronym.'" size="20" placeholder="'.ucfirst(__('patronymic')).'">';
                                echo editor_label(__('lastname'), 'bold');
                                echo '<input type="text" name="pers_lastname" value="' . $pers_lastname . '" size="35"><br>';

                                // *** Patronym *** 
                                echo editor_label(__('patronymic'));
                                echo '<input type="text" name="pers_patronym" value="' . $pers_patronym . '" size="20">';

                                if ($humo_option['admin_hebname'] == "y") {  // user requested hebrew name field to be displayed here, not under "events"
                                    $sql = "SELECT * FROM humo_events WHERE event_gedcom = '_HEBN' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_kind='name' AND event_connect_kind='person'";
                                    $result = $dbh->query($sql);
                                    if ($result->rowCount() > 0) {
                                        $hebnameDb = $result->fetch(PDO::FETCH_OBJ);
                                        $he_name =  $hebnameDb->event_event;
                                    } else {
                                        $he_name = "";
                                    }

                                    echo editor_label(__('Hebrew name') . '. ' . __('For example: Joseph ben Hirsch Zvi'));
                                    echo '<input type="text" name="even_hebname" value="' . htmlspecialchars($he_name) . '" size="35"> ';
                                }

                                // *** Person text by name ***
                                echo editor_label(__('text'));
                                $text = $editor_cls->text_show($pers_name_text);
                                //$field_text_selected=$field_text; if ($text) $field_text_selected=$field_text_medium;
                                // *** Check if there are multiple lines in text ***
                                $field_text_selected = $field_text;
                                if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                                echo '<textarea rows="1" name="pers_name_text" ' . $field_text_selected . '>' . $text . '</textarea>';

                                //TEST
                                //echo '<br><br><input type="Submit" name="person_change" value="' . __('Save') . '">';

                                //TEST Ajax script
                                /*
?>
<script>
    $(document).ready(function() {
        $("#submit_ajax").click(function() {
            var tree_id='<?= $tree_id;?>';
            var pers_gedcomnumber='<?= $pers_gedcomnumber;?>';
            var pers_firstname = $("#pers_firstname").val();
            var pers_lastname = $("#pers_lastname").val();
            //if (name == '' || email == '' || contact == '' || gender == '' || msg == '') {
            //	alert("Insertion Failed Some Fields are Blank....!!");
            //} else {
                // Returns successful data submission message when the entered information is stored in database.
                $.post("include/editor_ajax.php", {
                    tree_id1: tree_id,
                    pers_gedcomnumber1: pers_gedcomnumber,
                    pers_firstname1: pers_firstname,
                    pers_lastname1: pers_lastname,
                }, function(data) {
                    alert(data);
                    //$('#form_ajax')[0].reset(); // To reset form fields
                });
            //}

            // Show name in <div>
            document.getElementById("ajax_pers_firstname").innerHTML = pers_firstname;
            document.getElementById("ajax_pers_lastname").innerHTML = pers_lastname;

            // TEST for hideshow of item.
            hideShow(1);
        });
    });
</script>

<br><br>
<div id="ajax_pers_fullname"><?= $pers_firstname.' '.$pers_lastname; ?></div>
<div id="ajax_pers_firstname"><?= $pers_firstname; ?></div>
<div id="ajax_pers_lastname"><?= $pers_lastname; ?></div>

<label>Name:</label>
<input id="pers_firstname" value="<?= $pers_firstname; ?>" placeholder="Your Name" type="text">
<label>Name:</label>
<input id="pers_lastname" value="<?= $pers_lastname; ?>" placeholder="Your Name" type="text">
<input id="submit_ajax" type="button" value="Submit">
    <?php
// END TEST SCRIPT
*/


                                echo '</span>';
                                ?>
                            </td>

                            <td>
                                <?php
                                if (!isset($_GET['add_person'])) {
                                    // *** Source by name ***
                                    //source_link('individual',$pers_gedcomnumber,'pers_name_source');
                                    echo source_link2('500', $pers_gedcomnumber, 'pers_name_source', 'name');


                                    //TEST MODAL
                                ?>
                                    <!--
<style>
/* The Modal (background) */
.modal {
  display: none;
  position: fixed;
  z-index: 1;
  padding-top: 100px;
  left: 0;
  top: 0;
  width: 100%;
  height: 100%;
  overflow: auto;
  background-color: rgb(0,0,0);
  background-color: rgba(0,0,0,0.4);
}

/* Modal Content */
.modal-content {
  position: relative;
  background-color: #fefefe;
  margin: auto;
  padding: 0;
  border: 1px solid #888;
  width: 80%;
  height:80%;
  overflow: auto;
  box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2),0 6px 20px 0 rgba(0,0,0,0.19);
  -webkit-animation-name: animatetop;
  -webkit-animation-duration: 0.4s;
  animation-name: animatetop;
  animation-duration: 0.4s
}

/* Add Animation */
@-webkit-keyframes animatetop {
  from {top:-300px; opacity:0} 
  to {top:0; opacity:1}
}

@keyframes animatetop {
  from {top:-300px; opacity:0}
  to {top:0; opacity:1}
}

/* The Close Button */
.close {
  color: white;
  float: right;
  font-size: 28px;
  font-weight: bold;
}

.close:hover,
.close:focus {
  color: #000;
  text-decoration: none;
  cursor: pointer;
}

.modal-header {
  padding: 2px 16px;
  background-color: #5cb85c;
  color: white;
}

.modal-body {padding: 2px 16px;}

.modal-footer {
  padding: 2px 16px;
  background-color: #5cb85c;
  color: white;
}
</style>
-->
                                    <!-- <h2>Animated Modal with Header and Footer</h2> -->

                                    <!-- Trigger/Open The Modal -->
                                    <!-- <button id="myBtn">Open Modal</button> -->
                                    <!--
<span id="myBtn" class="hideshowlink">Open Modal</span>
-->
                                    <!-- The Modal -->
                                    <!--
<div id="myModal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <span class="close">&times;</span>
      <h2>Modal Header</h2>
    </div>
    <div class="modal-body">
      <p>Some text in the Modal Body</p>

        <table>
            <?php //echo edit_sources_test('500', 'person', 'pers_name_source', $pers_gedcomnumber); 
            ?>
        </table>

    </div>
    <div class="modal-footer">
      <h3>Modal Footer</h3>
    </div>
  </div>
</div>

<script>
// Get the modal
var modal = document.getElementById("myModal");

// Get the button that opens the modal
var btn = document.getElementById("myBtn");

// Get the <span> element that closes the modal
var span = document.getElementsByClassName("close")[0];

// When the user clicks the button, open the modal 
btn.onclick = function() {
  modal.style.display = "block";
}

// When the user clicks on <span> (x), close the modal
span.onclick = function() {
  modal.style.display = "none";
}

// When the user clicks anywhere outside of the modal, close it
window.onclick = function(event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
}
</script>
-->
                                <?php

                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                        //TEST labels en vakken naast elkaar.

                        /*
echo '</tr>';
    echo '<td>'.__('Name').'</td>';
    echo '<td colspan="2">';
        echo editor_label2(__('firstname'),'bold');
        echo '<input type="text" name="pers_firstname" value="'.$pers_firstname.'"  size="35"><br>';

        echo editor_label2(__('prefix'));
        echo '<input type="text" name="pers_prefix" value="'.$pers_prefix.'" size="10"> ';
        echo __("For example: d\' or:  van_ (use _ for a space)").'<br>';

        echo editor_label2(__('lastname'),'bold');
        echo '<input type="text" name="pers_lastname" value="'.$pers_lastname.'" size="35"><br>';

        echo editor_label2(__('patronymic'));
        echo '<input type="text" name="pers_patronym" value="'.$pers_patronym.'" size="20"><br>';

        // *** Person text by name ***
        echo editor_label2(__('text'));
        $text=$editor_cls->text_show($pers_name_text);
        //$field_text_selected=$field_text; if ($text) $field_text_selected=$field_text_medium;
        // *** Check if there are multiple lines in text ***
        $field_text_selected=$field_text; if ($text AND preg_match('/\R/',$text)) $field_text_selected=$field_text_medium;
        echo '<textarea rows="1" name="pers_name_text" '.$field_text_selected.'>'.$text.'</textarea>';
    echo '</td>';
    echo '<td></td>';
echo '</tr>';
*/

                        // *** Show source by name in iframe ***
                        echo edit_sources('500', 'person', 'pers_name_source', $pers_gedcomnumber);

                        if ($add_person == false) {
                            // *** Event name (also show ADD line for prefix, suffix, title etc. ***
                            echo $event_cls->show_event('person', $pers_gedcomnumber, 'name');

                            //TEST if editing is done in table, Ajax could be used.
                            //echo '<tr><td></td><td colspan="3"><table class="humo">';
                            //echo $event_cls->show_event('person', $pers_gedcomnumber, 'name');
                            //echo '</table></td></tr>';

                            // *** NPFX Name prefix like: Lt. Cmndr. ***
                            echo $event_cls->show_event('person', $pers_gedcomnumber, 'NPFX');

                            // *** NSFX Name suffix like: jr. ***
                            echo $event_cls->show_event('person', $pers_gedcomnumber, 'NSFX');

                            // *** Title of Nobility ***
                            echo $event_cls->show_event('person', $pers_gedcomnumber, 'nobility');

                            // *** Title ***
                            echo $event_cls->show_event('person', $pers_gedcomnumber, 'title');

                            // *** Lordship ***
                            echo $event_cls->show_event('person', $pers_gedcomnumber, 'lordship');
                        }

                        // *** Alive ***

                        // *** Disable radio boxes if person is deceased ***
                        $disabled = '';
                        if ($pers_death_date or $pers_death_place or $pers_buried_date or $pers_buried_place) {
                            $disabled = ' disabled';
                        }

                        if ($pers_alive == 'deceased') {
                            $selected_alive = '';
                            $selected_deceased = ' checked';
                        } else {
                            $selected_alive = ' checked';
                            $selected_deceased = '';
                        }
                        ?>
                        <tr class="humo_color">
                            <td><?= __('Privacy filter'); ?></td>
                            <td colspan="2">
                                <input type="radio" name="pers_alive" value="alive" <?= $selected_alive . $disabled; ?>> <?= __('alive'); ?>
                                <?php

                                echo ' <input type="radio" name="pers_alive" value="deceased"' . $selected_deceased . $disabled . '> ' . __('deceased');

                                // *** Estimated/ calculated (birth) date, can be used for privacy filter ***
                                if (!$pers_cal_date) $pers_cal_date = 'dd mmm yyyy';
                                echo '<span style="color:#6D7B8D;">';
                                echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<a href="index.php?page=cal_date">' . __('Calculated birth date') . ':</a> ' . language_date($pers_cal_date);
                                echo '</span>';
                                ?>
                            </td>
                            <td></td>
                        </tr>
                        <?php

                        // *** Sexe ***
                        $colour = '';
                        // *** If sexe = unknown then show a red line (new person = other colour). ***
                        if ($pers_sexe == '') $colour = ' bgcolor="#FFAA80"';
                        if ($add_person == true and $pers_sexe == '') $colour = ' bgcolor="#FFAA80"';

                        $selected_m = '';
                        if ($pers_sexe == 'M') $selected_m = ' checked';
                        $selected_f = '';
                        if ($pers_sexe == 'F') $selected_f = ' checked';
                        $selected_u = '';
                        if ($pers_sexe == '') $selected_u = ' checked';
                        ?>
                        <tr>
                            <td><a name="sex"></a><?= __('Sex'); ?></td>
                            <td<?= $colour; ?> colspan="2">
                                <input type="radio" name="pers_sexe" value="M" <?= $selected_m; ?>> <?= __('male'); ?>
                                <input type="radio" name="pers_sexe" value="F" <?= $selected_f; ?>> <?= __('female'); ?>
                                <input type="radio" name="pers_sexe" value="" <?= $selected_u; ?>> ?
                                </td>
                                <td>
                                    <?php
                                    if (!isset($_GET['add_person'])) {
                                        //source_link('individual',$pers_gedcomnumber,'pers_sexe_source');
                                        echo source_link2('501', $pers_gedcomnumber, 'pers_sexe_source', 'sex');
                                    }
                                    ?>
                                </td>
                        </tr>
                        <?php
                        // *** Show source by sexe in iframe ***
                        echo edit_sources('501', 'person', 'pers_sexe_source', $pers_gedcomnumber);

                        //TEST (also after other items in this script)
                        // *** Empty line in table ***
                        //$divider='<tr style="height:8px;"><td colspan="4" class="table_empty_line"></td></tr>';
                        //echo $divider;


                        // *** Born ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '2';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                        ?>
                        <tr class="humo_color">
                            <td><a name="born"></a>
                                <b><?= ucfirst(__('born')); ?></b>
                            </td>
                            <?php
                            /*
                            <td style="border-right:0px; vertical-align: top;">
                                <?= ucfirst(__('born')); ?>
                                <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;<?= $display; ?>">
                                    <br><?= __('date'); ?>
                                    <?php

                                    // HELP POPUP
                                    echo '&nbsp;&nbsp;<div class="fonts ' . $rtlmarker . 'sddm" style="display:inline;">';
                                    echo '<a href="#" style="display:inline" ';
                                    echo 'onmouseover="mopen(event,\'help_date\')"';
                                    echo 'onmouseout="mclosetime()">';
                                    echo '<img src="../images/help.png" height="16" width="16">';
                                    echo '</a>';
                                    echo '<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:' . $rtlmarker . '" id="help_date" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
                                    echo __('Examples of date entries:') . '<br>';
                                    echo '<b>' . __('13 october 1813, 13 oct 1813, 13-10-1813, 13/10/1813, 13.10.1813, 13,10,1813, between 1986 and 1987, 13 oct 1100 BC.') . '</b><br>';
                                    echo '</div>';
                                    echo '</div><br>';

                                    echo __('birth time') . '<br>';
                                    echo ucfirst(__('text'));
                                    echo '</span>';
                                    echo '</td>';
*/

                            echo '<td colspan="2">';
                            $hideshow_text = hideshow_date_place($pers_birth_date, $pers_birth_place);
                            if ($pers_birth_time) {
                                $hideshow_text .= ' ' . __('at') . ' ' . $pers_birth_time . ' ' . __('hour');
                            }
                            //TEST
                            //if (!$hideshow_text) $hideshow_text=ucfirst(__('born'));
                            echo hideshow_editor($hideshow, $hideshow_text, $pers_birth_text);

                            echo editor_label2(__('Date'));
                            echo $editor_cls->date_show($pers_birth_date, 'pers_birth_date', '', '', $pers_birth_date_hebnight, 'pers_birth_date_hebnight') . '<br>';

                            echo editor_label2(__('place'));
                            echo '<input type="text" name="pers_birth_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($pers_birth_place) . '" size="' . $field_place . '">';
                            //echo '<input type="text" name="pers_birth_place" placeholder="'.ucfirst(__('place')).'" value="'.htmlspecialchars($pers_birth_place).'" size="'.$field_place.'">';

                            // *** Auto complete doesn't work properly yet... ***
                            //echo __('place').' <input list="place_auto_complete" name="pers_birth_place" placeholder="'.ucfirst(__('place')).'" value="'.htmlspecialchars($pers_birth_place).'" size="'.$field_place.'">';

                            echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_birth_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                            echo editor_label2(__('birth time'));
                            echo '<input type="text" placeholder="' . __('birth time') . '" name="pers_birth_time" value="' . $pers_birth_time . '" size="' . $field_date . '">';
                            //echo '<input type="text" name="pers_birth_time" value="'.$pers_birth_time.'" size="'.$field_date.'">';
                            // *** Stillborn child ***
                            $check = '';
                            if (isset($pers_stillborn) and $pers_stillborn == 'y') {
                                $check = ' checked';
                            }
                            echo '<input type="checkbox" name="pers_stillborn" ' . $check . '> ' . __('stillborn child') . '<br>';

                            // *** Check if there are multiple lines in text ***
                            $field_text_selected = $field_text;
                            if ($pers_birth_text and preg_match('/\R/', $pers_birth_text)) $field_text_selected = $field_text_medium;
                            echo editor_label2(__('text'));
                            echo '<textarea rows="1" placeholder="' . __('text') . '" name="pers_birth_text" ' . $field_text_selected . '>' .
                                $editor_cls->text_show($pers_birth_text) . '</textarea>';

                            // *** End of hideshow_editor span ***
                            echo '</span>';
                            echo '</td>';

                            // *** Source by birth ***
                            echo '<td>';
                            if (!isset($_GET['add_person'])) {
                                //source_link('individual',$pers_gedcomnumber,'pers_birth_source');
                                echo source_link2('502', $pers_gedcomnumber, 'pers_birth_source', 'born');
                            }
                            ?>
                            </td>
                        </tr>
                        <?php
                        // *** Show source by birth in iframe ***
                        echo edit_sources('502', 'person', 'pers_birth_source', $pers_gedcomnumber);

                        // *** Birth declaration ***
                        if ($add_person == false) echo $event_cls->show_event('person', $pers_gedcomnumber, 'birth_declaration');

                        // **** BRIT MILA ***
                        if ($humo_option['admin_brit'] == "y" and $pers_sexe != "F") {

                            // *** Use hideshow to show and hide the editor lines ***
                            $hideshow = '20';
                            // *** If items are missing show all editor fields ***
                            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                            $sql = "SELECT * FROM humo_events WHERE event_gedcom = '_BRTM' AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_connect_kind='person'";
                            $result = $dbh->query($sql);
                            if ($result->rowCount() > 0) {
                                $britDb = $result->fetch(PDO::FETCH_OBJ);
                                $britdate = $britDb->event_date;
                                $britplace = $britDb->event_place;
                                $brittext = $britDb->event_text;
                            } else {
                                $britdate = "";
                                $britplace = "";
                                $brittext = "";
                            }
                            $britDb = $result->fetch(PDO::FETCH_OBJ);

                            echo '<tr>';
                            //echo '<td><a href="#" onclick="hideShow(20);"><span id="hideshowlink20">[+]</span></a> ';
                            echo '<td>' . ucfirst(__('Brit Mila')) . '</td>';

                            echo '<td colspan="2">';
                            $hideshow_text = hideshow_date_place($britdate, $britplace);
                            echo hideshow_editor($hideshow, $hideshow_text, $brittext);

                            echo editor_label2(__('Date'));
                            echo $editor_cls->date_show($britdate, 'even_brit_date') . '<br>';

                            echo editor_label2(__('place'));
                            echo '<input type="text" name="even_brit_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($britplace) . '" size="' . $field_place . '"><br>';

                            // *** Check if there are multiple lines in text ***
                            $text = $editor_cls->text_show($brittext);
                            $field_text_selected = $field_text;
                            if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                            echo editor_label2(__('text'));
                            echo '<textarea rows="1" placeholder="' . __('text') . '" name="even_brit_text" ' . $field_text_selected . '>' . $text . '</textarea>';

                            echo '<br>' . __('To display this, the option "Show events" has to be checked in "Users -> Groups"');
                            // echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=even_brit_place","","'.$field_popup.'")\'><img src="../images/search.png" alt="'.__('Search').'"></a>';
                            // *** End of hideshow_editor span ***
                            echo '</span>';
                            echo '</td>';

                            // *** Source by Brit Mila ***
                            echo '<td>';
                            // No source yet.
                            echo '</td></tr>';
                        }

                        //*** BAR/BAT MITSVA ***
                        if ($humo_option['admin_barm'] == "y") {
                            // *** Use hideshow to show and hide the editor lines ***
                            $hideshow = '21';
                            // *** If items are missing show all editor fields ***
                            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                            $sql = "SELECT * FROM humo_events WHERE (event_gedcom = 'BARM' OR event_gedcom = 'BASM') AND event_connect_id = '" . $pers_gedcomnumber . "' AND event_connect_kind='person'";
                            $result = $dbh->query($sql);
                            if ($result->rowCount() > 0) {
                                $barmDb = $result->fetch(PDO::FETCH_OBJ);
                                $bardate =  $barmDb->event_date;
                                $barplace =  $barmDb->event_place;
                                $bartext =  $barmDb->event_text;
                            } else {
                                $bardate = "";
                                $barplace = "";
                                $bartext = "";
                            }

                            echo '<tr>';
                            //echo '<td><a href="#" onclick="hideShow(21);"><span id="hideshowlink21">[+]</span></a> ';
                            echo '<td>';
                            if ($pers_sexe == "F") {
                                echo __('Bat Mitzvah');
                            } else {
                                echo __('Bar Mitzvah');
                            }
                            echo '</td>';

                            echo '<td colspan="2">';
                            $hideshow_text = hideshow_date_place($bardate, $barplace);
                            echo hideshow_editor($hideshow, $hideshow_text, $bartext);

                            echo editor_label2(__('Date'));
                            echo $editor_cls->date_show($bardate, 'even_barm_date') . '<br>';

                            echo editor_label2(__('place'));
                            echo '<input type="text" name="even_barm_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($barplace) . '" size="' . $field_place . '"><br>';
                            //echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=even_barm_place","","'.$field_popup.'")\><img src="../images/search.png" alt="'.__('Search').'"></a>';

                            // *** Check if there are multiple lines in text ***
                            $text = $editor_cls->text_show($bartext);
                            $field_text_selected = $field_text;
                            if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                            echo editor_label2(__('text'));
                            echo '<textarea rows="1" placeholder="' . __('text') . '" name="even_barm_text" ' . $field_text_selected . '>' . $text . '</textarea>';

                            echo '<br>' . __('To display this, the option "Show events" has to be checked in "Users -> Groups"');
                            echo '</span>';
                            echo '</td>';

                            // *** Source by Bar Mitsva ***
                            echo '<td>';
                            //if (!isset($_GET['add_person'])){
                            //	// no source yet
                            //}
                            echo '</td></tr>';
                        }


                        // *** Empty line in table ***
                        //echo $divider;


                        // *** Baptise ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '3';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
                        ?>
                        <tr>
                            <td><a name="baptised"></a><b><?= ucfirst(__('baptised')); ?></b></td>
                            <?php
                            echo '<td colspan="2">';
                            $hideshow_text = hideshow_date_place($pers_bapt_date, $pers_bapt_place);

                            if ($pers_religion) $hideshow_text .= ' (' . __('religion') . ': ' . $pers_religion . ')';

                            echo hideshow_editor($hideshow, $hideshow_text, $pers_bapt_text);

                            echo editor_label2(__('Date'));
                            echo $editor_cls->date_show($pers_bapt_date, 'pers_bapt_date') . '<br>';

                            echo editor_label2(__('place'));
                            echo '<input type="text" name="pers_bapt_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($pers_bapt_place) . '" size="' . $field_place . '">';
                            echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_bapt_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                            $text = htmlspecialchars($pers_religion);
                            echo editor_label2(__('religion'));
                            echo '<input type="text" name="pers_religion" placeholder="' . __('religion') . '" value="' . $text . '" size="20"><br>';

                            $text = $editor_cls->text_show($pers_bapt_text);
                            // *** Check if there are multiple lines in text ***
                            $field_text_selected = $field_text;
                            if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                            echo editor_label2(__('text'));
                            echo '<textarea rows="1" placeholder="' . __('text') . '" name="pers_bapt_text" ' . $field_text_selected . '>' . $text . '</textarea>';
                            echo '</span>';
                            echo '</td>';

                            // *** Source by baptise ***
                            echo '<td>';
                            if (!isset($_GET['add_person'])) {
                                //source_link('individual',$pers_gedcomnumber,'pers_bapt_source');
                                echo source_link2('503', $pers_gedcomnumber, 'pers_bapt_source', 'baptised');
                            }
                            ?>
                            </td>
                        </tr>
                        <?php
                        // *** Show source by baptise in iframe ***
                        echo edit_sources('503', 'person', 'pers_bapt_source', $pers_gedcomnumber);

                        // *** Baptism Witness ***
                        if ($add_person == false) echo $event_cls->show_event('person', $pers_gedcomnumber, 'baptism_witness');


                        // *** Empty line in table ***
                        //echo $divider;

                        // *** Died ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '4';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                        ?>
                        <tr class="humo_color">
                            <td><a name="died"></a>
                                <b><?= ucfirst(__('died')); ?></b>
                            </td>
                            <?php

                            echo '<td colspan="2">';
                            $hideshow_text = hideshow_date_place($pers_death_date, $pers_death_place);

                            if ($pers_death_time)
                                $hideshow_text .= ' ' . __('at') . ' ' . $pers_death_time . ' ' . __('hour');

                            if ($pers_death_cause) {
                                if ($hideshow_text) $hideshow_text .= ', ';
                                $pers_death_cause2 = '';
                                if ($pers_death_cause == 'murdered') {
                                    $pers_death_cause2 = __('cause of death') . ': ' . __('murdered');
                                }
                                if ($pers_death_cause == 'drowned') {
                                    $pers_death_cause2 = __('cause of death') . ': ' . __('drowned');
                                }
                                if ($pers_death_cause == 'perished') {
                                    $pers_death_cause2 = __('cause of death') . ': ' . __('perished');
                                }
                                if ($pers_death_cause == 'killed in action') {
                                    $pers_death_cause2 = __('killed in action');
                                }
                                if ($pers_death_cause == 'being missed') {
                                    $pers_death_cause2 = __('being missed');
                                }
                                if ($pers_death_cause == 'committed suicide') {
                                    $pers_death_cause2 = __('cause of death') . ': ' . __('committed suicide');
                                }
                                if ($pers_death_cause == 'executed') {
                                    $pers_death_cause2 = __('cause of death') . ': ' . __('executed');
                                }
                                if ($pers_death_cause == 'died young') {
                                    $pers_death_cause2 = __('died young');
                                }
                                if ($pers_death_cause == 'died unmarried') {
                                    $pers_death_cause2 = __('died unmarried');
                                }
                                if ($pers_death_cause == 'registration') {
                                    $pers_death_cause2 = __('registration');
                                } //2 TYPE registration?
                                if ($pers_death_cause == 'declared death') {
                                    $pers_death_cause2 = __('declared death');
                                }
                                if ($pers_death_cause2) {
                                    $hideshow_text .= $pers_death_cause2;
                                } else {
                                    $hideshow_text .= __('cause of death') . ' ' . $pers_death_cause;
                                }
                            }

                            echo hideshow_editor($hideshow, $hideshow_text, $pers_death_text);

                            echo editor_label2(__('Date'));
                            echo $editor_cls->date_show($pers_death_date, 'pers_death_date', '', '', $pers_death_date_hebnight, 'pers_death_date_hebnight') . '<br>';

                            echo editor_label2(__('place'));
                            echo '<input type="text" name="pers_death_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($pers_death_place) . '" size="' . $field_place . '">';
                            echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_death_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                            // *** Age by death ***
                            echo editor_label2(__('Age'));
                            echo '<input type="text" name="pers_death_age" placeholder="' . __('Age') . '" value="' . $pers_death_age . '" size="3">';
                            // *** HELP POPUP for age by death ***
                            echo '&nbsp;&nbsp;<div class="fonts ' . $rtlmarker . 'sddm" style="display:inline;">';
                            echo '<a href="#" style="display:inline" ';
                            echo 'onmouseover="mopen(event,\'help_menu2\',100,400)"';
                            echo 'onmouseout="mclosetime()">';
                            echo '<img src="../images/help.png" height="16" width="16">';
                            echo '</a>';
                            echo '<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:' . $rtlmarker . '" id="help_menu2" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
                            echo '<b>' . __('If death year and age are used, then birth year is calculated automatically (when empty).') . '</b><br>';
                            echo '</div>';
                            echo '</div><br>';

                            echo editor_label2(__('death time'));
                            echo '<input type="text" name="pers_death_time" placeholder="' . __('death time') . '" value="' . $pers_death_time . '" size="' . $field_date . '"><br>';

                            echo editor_label2(__('cause'));
                            //echo ' ' . __('cause') . ' ';
                            $cause = false;
                            echo '<select size="1" name="pers_death_cause">';
                            echo '<option value=""></option>';

                            $selected = '';
                            if ($pers_death_cause == 'murdered') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="murdered"' . $selected . '>' . __('murdered') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'drowned') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="drowned"' . $selected . '>' . __('drowned') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'perished') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="perished"' . $selected . '>' . __('perished') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'killed in action') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="killed in action"' . $selected . '>' . __('killed in action') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'being missed') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="being missed"' . $selected . '>' . __('being missed') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'committed suicide') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="committed suicide"' . $selected . '>' . __('committed suicide') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'executed') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="executed"' . $selected . '>' . __('executed') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'died young') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="died young"' . $selected . '>' . __('died young') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'died unmarried') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="died unmarried"' . $selected . '>' . __('died unmarried') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'registration') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="registration"' . $selected . '>' . __('registration') . '</option>';

                            $selected = '';
                            if ($pers_death_cause == 'declared death') {
                                $cause = true;
                                $selected = ' selected';
                            }
                            echo '<option value="declared death"' . $selected . '>' . __('declared death') . '</option>';

                            echo '</select>';

                            echo ' <b>' . __('or') . ':</b> ';
                            $pers_death_cause2 = '';
                            if ($pers_death_cause and $cause == false) $pers_death_cause2 = $pers_death_cause;
                            echo '<input type="text" name="pers_death_cause2" placeholder="' . __('cause') . '" value="' . $pers_death_cause2 . '" size="' . $field_date . '"><br>';

                            $text = $editor_cls->text_show($pers_death_text);
                            // *** Check if there are multiple lines in text ***
                            $field_text_selected = $field_text;
                            if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                            echo editor_label2(__('text'));
                            echo '<textarea rows="1" placeholder="' . __('text') . '" name="pers_death_text" ' . $field_text_selected . '>' . $text . '</textarea>';
                            echo '</span>';

                            // *** Source by death ***
                            echo '</td><td>';
                            if (!isset($_GET['add_person'])) {
                                //source_link('individual',$pers_gedcomnumber,'pers_death_source');
                                echo source_link2('504', $pers_gedcomnumber, 'pers_death_source', 'died');
                            }
                            echo '</td>';
                            ?>
                        </tr>
                        <?php
                        // *** Show source by death in iframe ***
                        echo edit_sources('504', 'person', 'pers_death_source', $pers_gedcomnumber);

                        // *** Death Declaration ***
                        if ($add_person == false) echo $event_cls->show_event('person', $pers_gedcomnumber, 'death_declaration');

                        // *** Empty line in table ***
                        //echo $divider;

                        // *** Buried ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '5';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                        ?>
                        <tr>
                            <?php
                            echo '<td><a name="buried"></a>';
                            //echo '<a href="#" onclick="hideShow(5);"><span id="hideshowlink5">[+]</span></a> ';
                            echo '<b>' . __('Buried') . '</b></td>';

                            echo '<td colspan="2">';
                            $hideshow_text = hideshow_date_place($pers_buried_date, $pers_buried_place);
                            echo hideshow_editor($hideshow, $hideshow_text, $pers_buried_text);

                            echo editor_label2(__('Date'));
                            echo $editor_cls->date_show($pers_buried_date, 'pers_buried_date', '', '', $pers_buried_date_hebnight, 'pers_buried_date_hebnight') . '<br>';

                            echo editor_label2(__('place'));
                            echo '<input type="text" name="pers_buried_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($pers_buried_place) . '" size="' . $field_place . '">';
                            echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=1&amp;place_item=pers_buried_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                            echo editor_label2(__('method of burial'));
                            echo '<select size="1" name="pers_cremation">';
                            echo '<option value="">' . __('buried') . '</option>';

                            $selected = '';
                            if ($pers_cremation == '1') {
                                $selected = ' selected';
                            }
                            echo '<option value="1"' . $selected . '>' . __('cremation') . '</option>';

                            $selected = '';
                            if ($pers_cremation == 'R') {
                                $selected = ' selected';
                            }
                            echo '<option value="R"' . $selected . '>' . __('resomated') . '</option>';

                            $selected = '';
                            if ($pers_cremation == 'S') {
                                $selected = ' selected';
                            }
                            echo '<option value="S"' . $selected . '>' . __('sailor\'s grave') . '</option>';

                            $selected = '';
                            if ($pers_cremation == 'D') {
                                $selected = ' selected';
                            }
                            echo '<option value="D"' . $selected . '>' . __('donated to science') . '</option>';
                            echo '</select><br>';

                            $text = $editor_cls->text_show($pers_buried_text);
                            // *** Check if there are multiple lines in text ***
                            $field_text_selected = $field_text;
                            if ($text and preg_match('/\R/', $text)) $field_text_selected = $field_text_medium;
                            echo editor_label2(__('text'));
                            echo '<textarea rows="1" placeholder="' . __('text') . '" name="pers_buried_text" ' . $field_text_selected . '>' . $text . '</textarea>';
                            echo '</span>';
                            echo '</td>';

                            // *** Source by burial ***
                            echo '<td>';
                            if (!isset($_GET['add_person'])) {
                                //source_link('individual',$pers_gedcomnumber,'pers_buried_source');
                                echo source_link2('505', $pers_gedcomnumber, 'pers_buried_source', 'buried');
                            }
                            echo '</td>';

                            ?>
                        </tr>
                        <?php
                        // *** Show source by burial in iframe ***
                        echo edit_sources('505', 'person', 'pers_buried_source', $pers_gedcomnumber);

                        // *** Burial Witness ***
                        if ($add_person == false) echo $event_cls->show_event('person', $pers_gedcomnumber, 'burial_witness');

                        // *** Empty line in table ***
                        //echo $divider;

                        // *** General text by person ***
                        ?>
                        <tr class="humo_color">
                            <td><a name="text_person"></a><?= __('Text for person'); ?></td>
                            <td colspan="2"><textarea rows="1" placeholder="<?= __('Text for person'); ?>" name="person_text" <?= $field_text_large; ?>><?= $editor_cls->text_show($person_text); ?></textarea>
                            </td>
                            <td>
                                <?php
                                // *** Source by text ***
                                if (!isset($_GET['add_person'])) {
                                    //source_link('individual',$pers_gedcomnumber,'pers_text_source');
                                    echo source_link2('506', $pers_gedcomnumber, 'pers_text_source', 'text_person');
                                }
                                ?>
                            </td>
                        </tr>
                        <?php
                        // *** Show source by person tekst in iframe ***
                        echo edit_sources('506', 'person', 'pers_text_source', $pers_gedcomnumber);

                        if (!isset($_GET['add_person'])) {
                            // *** Person sources in new person editor screen ***
                            echo '<tr><td><a name="source_person"></a>' . __('Source for person') . '</td><td colspan="2"></td>';
                            echo '<td>';
                            //source_link('individual',$pers_gedcomnumber,'person_source');
                            echo source_link2('507', $pers_gedcomnumber, 'person_source', 'source_person');
                            echo '</td></tr>';
                            // *** Show source by person in iframe ***
                            echo edit_sources('507', 'person', 'person_source', $pers_gedcomnumber);
                        }

                        // *** Own code ***
                        ?>
                        <tr class="humo_color">
                            <td><?= ucfirst(__('own code')); ?></td>
                            <td colspan="2"><input type="text" name="pers_own_code" placeholder="<?= __('own code'); ?>" value="<?= htmlspecialchars($pers_own_code); ?>" style="width: 500px">
                                <!-- HELP POPUP for own code -->
                                &nbsp;&nbsp;<div class="fonts <?= $rtlmarker; ?>sddm" style="display:inline;">
                                    <a href="#" style="display:inline" onmouseover="mopen(event,' help_menu3',100,400)" onmouseout="mclosetime()">
                                        <img src="../images/help.png" height="16" width="16">
                                    </a>
                                    <div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:<?= $rtlmarker; ?>" id="help_menu3" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">
                                        <b><?= __('Use own code for your own remarks.<br>
It\'s possible to use own code for special privacy options, see Admin > Users > Groups.<br>
It\'s also possible to add your own icons by a person! Add the icon in the images folder e.g. \'person.gif\', and add \'person\' in the own code field.'); ?></b><br>
                                    </div>
                                </div>
                            </td>
                            <td></td>
                        </tr>
                        <?php

                        // *** Profession(s) ***
                        echo $event_cls->show_event('person', $pers_gedcomnumber, 'profession');

                        // *** Religion ***
                        echo $event_cls->show_event('person', $pers_gedcomnumber, 'religion');

                        if (!isset($_GET['add_person'])) {
                            // *** Show and edit places by person ***
                            edit_addresses('person', 'person_address', $pers_gedcomnumber);
                        } // *** End of check for new person ***

                        if (!isset($_GET['add_person'])) {
                            // *** Person event editor ***
                            echo $event_cls->show_event('person', $pers_gedcomnumber, 'person');

                            // *** Picture ***
                            echo $event_cls->show_event('person', $pers_gedcomnumber, 'picture');

                            // *** Quality ***
                            // Disabled quality by person. Quality officially belongs to a source...
                            /*
            echo '<tr><td>'.__('Quality of data').'</td>';
            echo '<td style="border-right:0px;"></td>';
                echo '<td style="border-left:0px;"><select size="1" name="pers_quality" style="width: 400px">';
                echo '<option value="">'.ucfirst(__('quality: default')).'</option>';
                $selected=''; if ($pers_quality=='0'){ $selected=' selected'; }
                echo '<option value="0"'.$selected.'>'.ucfirst(__('quality: unreliable evidence or estimated data')).'</option>';
                $selected=''; if ($pers_quality=='1'){ $selected=' selected'; }
                echo '<option value="1"'.$selected.'>'.ucfirst(__('quality: questionable reliability of evidence')).'</option>';
                $selected=''; if ($pers_quality=='2'){ $selected=' selected'; }
                echo '<option value="2"'.$selected.'>'.ucfirst(__('quality: data from secondary evidence')).'</option>';
                $selected=''; if ($pers_quality=='3'){ $selected=' selected'; }
                echo '<option value="3"'.$selected.'>'.ucfirst(__('quality: data from direct source')).'</option>';
                echo '</select></td>';
            echo '<td></td>';
            echo '</tr>';
            */

                            // *** Show unprocessed GEDCOM tags ***
                            $tag_qry = "SELECT * FROM humo_unprocessed_tags
                                WHERE tag_tree_id='" . $tree_id . "'
                                AND tag_pers_id='" . $person->pers_id . "'";
                            $tag_result = $dbh->query($tag_qry);
                            //$num_rows = $tag_result->rowCount();
                            $tagDb = $tag_result->fetch(PDO::FETCH_OBJ);
                            if (isset($tagDb->tag_tag)) {
                                $tags_array = explode('<br>', $tagDb->tag_tag);
                                $num_rows = count($tags_array);
                        ?>
                                <tr class="humo_tags_pers humo_color">
                                    <td>
                                        <a href="#humo_tags_pers" onclick="hideShow(61);"><span id="hideshowlink61">[+]</span></a>
                                        <?= __('GEDCOM tags'); ?>
                                    </td>
                                    <td colspan="2">
                                        <?php
                                        if ($tagDb->tag_tag) {
                                            printf(__('There are %d unprocessed GEDCOM tags.'), $num_rows);
                                        } else {
                                            printf(__('There are %d unprocessed GEDCOM tags.'), 0);
                                        }
                                        ?>
                                    </td>
                                    <td></td>
                                </tr>
                                <tr style="display:none;" class="row61">
                                    <td></td>
                                    <td colspan="2"><?= $tagDb->tag_tag; ?></td>
                                    <td></td>
                                </tr>
                        <?php
                            }

                            // *** Show editor notes ***
                            show_editor_notes('person');

                            // *** Show user added notes ***
                            $note_qry = "SELECT * FROM humo_user_notes
                                WHERE note_tree_id='" . $tree_id . "'
                                AND note_kind='user' AND note_connect_kind='person' AND note_connect_id='" . $pers_gedcomnumber . "'";
                            $note_result = $dbh->query($note_qry);
                            $num_rows = $note_result->rowCount();

                            echo '<tr class="table_header_large"><td>';
                            if ($num_rows)
                                echo '<a href="#humo_user_notes" onclick="hideShow(62);"><span id="hideshowlink62">[+]</span></a> ';
                            echo __('User notes') . '</td><td colspan="2">';
                            if ($num_rows)
                                printf(__('There are %d user added notes.'), $num_rows);
                            else
                                printf(__('There are %d user added notes.'), 0);
                            echo '</td><td></td></tr>';
                            while ($noteDb = $note_result->fetch(PDO::FETCH_OBJ)) {
                                $user_qry = "SELECT * FROM humo_users
                                    WHERE user_id='" . $noteDb->note_new_user_id . "'";
                                $user_result = $dbh->query($user_qry);
                                $userDb = $user_result->fetch(PDO::FETCH_OBJ);

                                echo '<tr class="row62" style="display:none;"><td></td>';
                                echo '<td colspan="2">';

                                echo __('Added by') . ' <b>' . $userDb->user_name . '</b> (' . language_date($noteDb->note_new_date) . ' ' . $noteDb->note_new_time . ')<br>';

                                echo '<b>' . $noteDb->note_names . '</b><br>';

                                echo '<textarea readonly rows="1" placeholder="' . __('Text') . '" ' . $field_text_large . '>' . $editor_cls->text_show($noteDb->note_note) . '</textarea>';

                                echo '</td>';
                                echo '<td></td></tr>';
                            }

                            // *** Person added by user ***
                            if ($person->pers_new_user) {
                                echo '<tr class="table_header_large"><td>' . __('Added by') . '</td>';
                                //echo '<td colspan="2">'.$person->pers_new_user.' ('.$person->pers_new_date.' '.$person->pers_new_time.')</td><td></td></tr>';
                                echo '<td colspan="2">' . $person->pers_new_user . ' (' . language_date($person->pers_new_date) . ' ' . $person->pers_new_time . ')</td><td></td></tr>';
                            }
                            // *** Person changed by user ***
                            if ($person->pers_changed_user) {
                                echo '<tr class="table_header_large"><td>' . __('Changed by') . '</td>';
                                echo '<td colspan="2">' . $person->pers_changed_user . ' (' . language_date($person->pers_changed_date) . ' ' . $person->pers_changed_time . ')</td><td></td></tr>';
                            }
                        }


                        // *** Extra "Save" line ***
                        echo '<tr class="table_header_large">';
                        echo '<td></td><td colspan="2"></td>';
                        echo '<td style="border-left: none; text-align:left; font-size: 1.5em;">';
                        if ($add_person == false) {
                            echo '<input type="Submit" name="person_change" value="' . __('Save') . '">';
                        } else {
                            echo '<input type="Submit" name="person_add" value="' . __('Add') . '">';
                        }
                        echo '</td>';
                        echo '</tr>';

                        ?>
                </table><br>
                <!-- End of person form -->
            </form>

            <?php
        } // *** end of menu_tab ***


        if ($menu_tab == 'marriage') {
            //if ($menu_tab=='marriage' OR $menu_tab=='children'){

            // ***********************************
            // *** Marriages and children list ***
            // ***********************************
            //echo '<table class="humo" border="1">';
            //if (!isset($_GET['add_person'])){
            // *** Empty line in table ***
            //echo '<tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr>';

            //echo '<tr><th class="table_header" colspan="4">'.__('Marriage(s) and children').'</tr>';
            //echo '<tr><th class="table_header" colspan="4">'.ucfirst(__('marriage/ relation')).'</tr>';
            //}

            if ($add_person == false) {
            ?>
                <table class="humo" border="1">
                    <?php
                    if ($person->pers_fams) {
                        // *** Search for own family ***
                        $fams1 = explode(";", $person->pers_fams);
                        $fam_count = count($fams1);
                        if ($fam_count > 0) {
                            //					echo '<tr><th class="table_header" colspan="4">'.ucfirst(__('marriage/ relation')).'</th></tr>';
                            for ($i = 0; $i < $fam_count; $i++) {
                                $family = $dbh->query("SELECT * FROM humo_families
                            WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $fams1[$i] . "'");
                                $familyDb = $family->fetch(PDO::FETCH_OBJ);

                                // *** Highlight selected relation if there are multiple relations ***
                                $line_selected = '';
                                if ($fam_count > 1 and $familyDb->fam_gedcomnumber == $marriage) $line_selected = ' bgcolor="#99ccff"';

                                echo '<tr' . $line_selected . '><td id="chtd1">';
                                if ($fam_count > 1) {
                                    echo '<form method="POST" action="' . $phpself . '">';
                                    echo '<input type="hidden" name="page" value="' . $page . '">';
                                    echo '<input type="hidden" name="marriage_nr" value="' . $familyDb->fam_gedcomnumber . '">';
                                    echo ' <input type="Submit" name="dummy3" value="' . __('Select family') . ' ' . ($i + 1) . '">';
                                    echo '</form>';
                                } else {
                                    //echo ucfirst(__('marriage')).' '.($i+1);
                                    echo ucfirst(__('Family')) . ' ' . ($i + 1);
                                }
                                echo '</td><td id="chtd2" valign="top">';

                                if ($i < ($fam_count - 1)) {
                                    echo ' <a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;person_id=' . $person->pers_id . '&amp;fam_down=' . $i . '&amp;fam_array=' . $person->pers_fams . '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_down.gif" border="0" alt="fam_down"></a> ';
                                } else {
                                    echo '&nbsp;&nbsp;&nbsp;';
                                }
                                if ($i > 0) {
                                    echo ' <a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;person_id=' . $person->pers_id . '&amp;fam_up=' . $i . '&amp;fam_array=' . $person->pers_fams . '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_up.gif" border="0" alt="fam_up"></a> ';
                                } else {
                                    //echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                                }

                                echo '</td><td id="chtd3" colspan="2"><b>';
                                echo show_person($familyDb->fam_man) . ' ' . __('and') . ' ' . show_person($familyDb->fam_woman);
                                echo '</b>';

                                if ($familyDb->fam_marr_date) {
                                    echo ' X ' . date_place($familyDb->fam_marr_date, '');
                                }
                                echo '<br>';
                                echo '</td></tr>';
                            }
                        }
                    }

                    // *** Add new relation ***
                    if ($menu_tab != 'children') {
                        $hideshow = '700';
                    ?>
                        <tr>
                            <td><b><?= __('Add relation'); ?></b></td>
                            <td colspan="2"><a href="#" onclick="hideShow(<?= $hideshow; ?>);"><img src="images/family_connect.gif"> <?= __('Add new relation to this person'); ?></a>
                                (<?= trim(show_person($person->pers_gedcomnumber, false, false)); ?>)
                            </td>
                        </tr>
                        <?php
                        $pers_sexe = '';
                        if ($person->pers_sexe == 'M') $pers_sexe = 'F';
                        if ($person->pers_sexe == 'F') $pers_sexe = 'M';

                        ?>
                        <tr style="display:none;" class="row<?= $hideshow; ?>">
                            <td id="chtd1"></td>
                            <td id="chtd2"></td>
                            <td id="chtd3">
                                <?= add_person('partner', $pers_sexe); ?><br><br>
                                <form method="POST" style="display: inline;" action="<?= $phpself; ?>#marriage" name="form4" id="form4">
                                    <input type="hidden" name="page" value="<?= $page; ?>">

                                    <?= __('Or add relation with existing person:'); ?> <input class="fonts" type="text" name="relation_add2" value="" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>" required>

                                    <a href="#" onClick=\'window.open("index.php?page=editor_person_select&person=0&person_item=relation_add2&tree_id=<?= $tree_id; ?>","","<?= $field_popup; ?>")\'><img src=" ../images/search.png" alt="<?= __('Search'); ?>"></a>

                                    <input type="Submit" name="dummy4" value="<?= __('Add relation'); ?>">
                                </form>
                            </td>
                        </tr>
                    <?php
                    }
                    ?>
                </table><br>
                <?php

                // ***********************
                // *** Marriage editor ***
                // ***********************

                // *** Select marriage ***
                //if ($person->pers_fams){
                if ($menu_tab == 'marriage' and $person->pers_fams) {

                    $familyDb = $db_functions->get_family($marriage);

                    $fam_kind = $familyDb->fam_kind;
                    $man_gedcomnumber = $familyDb->fam_man;
                    $woman_gedcomnumber = $familyDb->fam_woman;
                    $fam_gedcomnumber = $familyDb->fam_gedcomnumber;
                    $fam_relation_date = $familyDb->fam_relation_date;
                    $fam_relation_end_date = $familyDb->fam_relation_end_date;
                    // *** Check if variabele exists, needed for PHP 8.1 ***
                    $fam_relation_place = '';
                    if (isset($familyDb->fam_relation_place)) $fam_relation_place = $familyDb->fam_relation_place;
                    $fam_relation_text = $editor_cls->text_show($familyDb->fam_relation_text);
                    $fam_marr_notice_date = $familyDb->fam_marr_notice_date;
                    $fam_marr_notice_place = '';
                    if (isset($familyDb->fam_marr_notice_place)) $fam_marr_notice_place = $familyDb->fam_marr_notice_place;
                    $fam_marr_notice_text = $editor_cls->text_show($familyDb->fam_marr_notice_text);
                    $fam_marr_date = $familyDb->fam_marr_date;
                    $fam_marr_place = '';
                    if (isset($familyDb->fam_marr_place)) $fam_marr_place = $familyDb->fam_marr_place;
                    $fam_marr_text = $editor_cls->text_show($familyDb->fam_marr_text);
                    $fam_marr_authority = $editor_cls->text_show($familyDb->fam_marr_authority);
                    $fam_man_age = $familyDb->fam_man_age;
                    $fam_woman_age = $familyDb->fam_woman_age;
                    $fam_marr_church_notice_date = $familyDb->fam_marr_church_notice_date;
                    $fam_marr_church_notice_place = '';
                    if (isset($familyDb->fam_marr_church_notice_place)) $fam_marr_church_notice_place = $familyDb->fam_marr_church_notice_place;
                    $fam_marr_church_notice_text = $editor_cls->text_show($familyDb->fam_marr_church_notice_text);
                    $fam_marr_church_date = $familyDb->fam_marr_church_date;
                    $fam_marr_church_place = '';
                    if (isset($familyDb->fam_marr_church_place)) $fam_marr_church_place = $familyDb->fam_marr_church_place;
                    $fam_marr_church_text = $editor_cls->text_show($familyDb->fam_marr_church_text);
                    $fam_religion = '';
                    if (isset($familyDb->fam_religion)) $fam_religion = $familyDb->fam_religion;
                    $fam_div_date = $familyDb->fam_div_date;
                    $fam_div_place = '';
                    if (isset($familyDb->fam_div_place)) $fam_div_place = $familyDb->fam_div_place;
                    $fam_div_text = $editor_cls->text_show($familyDb->fam_div_text);
                    $fam_div_authority = $editor_cls->text_show($familyDb->fam_div_authority);

                    $fam_marr_notice_date_hebnight = '';
                    $fam_marr_date_hebnight = '';
                    $fam_marr_church_notice_date_hebnight = '';
                    $fam_marr_church_date_hebnight = '';
                    if ($humo_option['admin_hebnight'] == "y") {
                        if (isset($familyDb->fam_marr_notice_date_hebnight)) {
                            $fam_marr_notice_date_hebnight = $familyDb->fam_marr_notice_date_hebnight;
                        }
                        if (isset($familyDb->fam_marr_date_hebnight)) {
                            $fam_marr_date_hebnight = $familyDb->fam_marr_date_hebnight;
                        }
                        if (isset($familyDb->fam_marr_church_notice_date_hebnight)) {
                            $fam_marr_church_notice_date_hebnight = $familyDb->fam_marr_church_notice_date_hebnight;
                        }
                        if (isset($familyDb->fam_marr_church_date_hebnight)) {
                            $fam_marr_church_date_hebnight = $familyDb->fam_marr_church_date_hebnight;
                        }
                    }

                    // *** Checkbox for no data by divorce ***
                    $fam_div_no_data = false;
                    if ($fam_div_date or $fam_div_place or $fam_div_text) $fam_div_no_data = true;
                    $fam_text = $editor_cls->text_show($familyDb->fam_text);

                    // *** Don't leave page if there are unsaved items ***
                    echo "
            <script>
            $(function() {
                // Enable on selected forms
                $('#form2').areYouSure();
            });
            </script>";

                    echo '<form method="POST" action="' . $phpself . '" style="display : inline;" enctype="multipart/form-data"  name="form2" id="form2">';
                    echo '<input type="hidden" name="page" value="' . $page . '">';

                    // *** Only add <br> if there are multiple marriages ***
                    //$fams1=explode(";",$person->pers_fams);
                    //$fam_count=substr_count($person->pers_fams, ";");
                    //if ($fam_count>0){
                    //	echo '<br>' ;
                    //}

                    // *** Show delete message ***
                    if ($confirm_relation) {
                        echo $confirm_relation;
                    }
                ?>
                    <table class="humo" border="1">
                        <?php
                        // *** Show delete message ***
                        //if ($confirm_relation){
                        //	echo '<tr><td colspan="4" class="table_empty_line" style="border: solid 1px white;"><br>'.$confirm_relation.'</td><tr>';
                        //}

                        // *** Empty line in table ***
                        //echo '<tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr>';

                        echo '<tr class="table_header_large">';

                        // *** Hide or show all hide-show items ***
                        $hide_show_all = '<a href="#marriage" onclick="hideShowAll2();"><span id="hideshowlinkall2">[+]</span> ' . __('All') . '</a> ';

                        // *** Remove marriage ***
                        if (isset($marriage)) {
                            echo '<td id="target1">' . $hide_show_all . '<a name="marriage"></a><input type="Submit" name="fam_remove" value="' . __('Delete relation') . '"></td>';
                        } else {
                            echo '<td id="target1">' . $hide_show_all . '<a name="marriage"></a><br></td>';
                        }

                        //echo '<th id="target2" colspan="2">'.__('Edit marriage');
                        echo '<th id="target2" colspan="2" style="font-size: 1.5em;">';
                        //echo ': ['.$fam_gedcomnumber.'] '.show_person($man_gedcomnumber).' '.__('and').' '.show_person($woman_gedcomnumber).'<br>';
                        echo '[' . $fam_gedcomnumber . '] ' . show_person($man_gedcomnumber) . ' ' . __('and') . ' ' . show_person($woman_gedcomnumber) . '<br>';

                        echo '<td id="target3">';
                        echo '<input type="Submit" name="marriage_change" value="' . __('Save') . '">';
                        echo '</td></tr>';

                        if (isset($marriage)) {
                            echo '<input type="hidden" name="marriage_nr" value="' . $marriage . '">';
                        }

                        //echo '<tr><td>'.__('Marriage').'</td>';
                        echo '<tr><td>' . ucfirst(__('marriage/ relation')) . '</td>';
                        echo '<td colspan="2">';

                        echo __('Select person 1') . ' <input class="fonts" type="text" name="connect_man" value="' . $man_gedcomnumber . '" size="5">';

                        echo '<a href="#" onClick=\'window.open("index.php?page=editor_person_select&person_item=man&person=' . $man_gedcomnumber . '&tree_id=' . $tree_id . '","","width=500,height=500,top=100,left=100,scrollbars=yes")\'><img src="../images/search.png" alt="' . __('Search') . '"></a>';

                        $person = $db_functions->get_person($man_gedcomnumber);

                        // *** Automatically calculate birth date if marriage date and marriage age by man is used ***
                        if (
                            isset($_POST["fam_man_age"]) and $_POST["fam_man_age"] != ''
                            and $fam_marr_date != '' and $person->pers_birth_date == '' and $person->pers_bapt_date == ''
                        ) {
                            $pers_birth_date = 'ABT ' . (substr($fam_marr_date, -4) - $_POST["fam_man_age"]);
                            $sql = "UPDATE humo_persons SET pers_birth_date='" . safe_text_db($pers_birth_date) . "'
                    WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($man_gedcomnumber) . "'";
                            $result = $dbh->query($sql);
                        }

                        echo ' <b>' . $editor_cls->show_selected_person($person) . '</b>';

                        // *** Use old value to detect change of man in marriage ***
                        echo '<input type="hidden" name="connect_man_old" value="' . $man_gedcomnumber . '">';

                        echo '<br>' . __('and');

                        if (!isset($_GET['add_marriage'])) {
                            echo ' <BUTTON TYPE="submit" name="parents_switch" title="Switch Persons" class="button"><img src="' . CMS_ROOTPATH_ADMIN . 'images/turn_around.gif" width="17"></BUTTON>';
                        }
                        echo '<br>';

                        echo __('Select person 2') . ' <input class="fonts" type="text" name="connect_woman" value="' . $woman_gedcomnumber . '" size="5">';

                        echo '<a href="#" onClick=\'window.open("index.php?page=editor_person_select&person_item=woman&person=' . $woman_gedcomnumber . '&tree_id=' . $tree_id . '","","width=500,height=500,top=100,left=100,scrollbars=yes")\'><img src="../images/search.png" alt="' . __('Search') . '"></a>';

                        $person = $db_functions->get_person($woman_gedcomnumber);

                        // *** Automatically calculate birth date if marriage date and marriage age by woman is used ***
                        if (
                            isset($_POST["fam_woman_age"]) and $_POST["fam_woman_age"] != ''
                            and $fam_marr_date != '' and $person->pers_birth_date == '' and $person->pers_bapt_date == ''
                        ) {
                            $pers_birth_date = 'ABT ' . (substr($fam_marr_date, -4) - $_POST["fam_woman_age"]);
                            $sql = "UPDATE humo_persons SET pers_birth_date='" . safe_text_db($pers_birth_date) . "'
                                WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($woman_gedcomnumber) . "'";
                            $result = $dbh->query($sql);
                        }

                        echo ' <b>' . $editor_cls->show_selected_person($person) . '</b>';

                        // *** Use old value to detect change of woman in marriage ***
                        echo '<input type="hidden" name="connect_woman_old" value="' . $woman_gedcomnumber . '">';

                        echo '</td><td></td></tr>';

                        // *** $marriage is empty by single persons ***
                        if (isset($marriage)) {
                            echo '<input type="hidden" name="marriage" value="' . $marriage . '">';
                        }

                        // *** Living together ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '6';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                        echo '<tr class="humo_color"><td><a name="relation"></a>';
                        //echo '<a href="#marriage" onclick="hideShow(6);"><span id="hideshowlink6">[+]</span></a> ';
                        echo __('Living together') . '</td>';

                        echo '<td colspan="2">';
                        $hideshow_text = hideshow_date_place($fam_relation_date, $fam_relation_place);
                        if ($fam_relation_end_date) {
                            if ($hideshow_text) $hideshow_text .= '.';
                            $hideshow_text .= ' ' . __('End living together') . ' ' . $fam_relation_end_date;
                        }
                        echo hideshow_editor($hideshow, $hideshow_text, $fam_relation_text);

                        echo editor_label2(__('Date'));
                        echo $editor_cls->date_show($fam_relation_date, 'fam_relation_date') . '<br>';

                        echo editor_label2(__('place'));
                        echo '<input type="text" name="fam_relation_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($fam_relation_place) . '" size="' . $field_place . '">';
                        echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_relation_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                        // *** End of living together ***
                        echo editor_label2(__('End date'));
                        echo $editor_cls->date_show($fam_relation_end_date, "fam_relation_end_date") . '<br>';

                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_relation_text and preg_match('/\R/', $fam_relation_text)) $field_text_selected = $field_text_medium;
                        echo editor_label2(__('text'));
                        echo '<textarea rows="1" placeholder="' . __('text') . '" name="fam_relation_text" ' . $field_text_selected . '>' . $fam_relation_text . '</textarea>';
                        echo '</span>';
                        echo '</td><td>';
                        // *** Source by relation ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            //source_link('relation',$marriage,'fam_relation_source');
                            echo source_link2('600', $marriage, 'fam_relation_source', 'relation');
                        }
                        echo '</td></tr>';
                        // *** Show source by relation in iframe ***
                        echo edit_sources('600', 'family', 'fam_relation_source', $marriage);

                        // *** Marriage notice ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '7';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                        echo '<tr><td><a name="marr_notice"></a>';
                        //echo '<a href="#marriage" onclick="hideShow(7);"><span id="hideshowlink7">[+]</span></a> ';
                        echo __('Notice of Marriage') . '</td>';

                        echo '<td colspan="2">';
                        $hideshow_text = hideshow_date_place($fam_marr_notice_date, $fam_marr_notice_place);
                        echo hideshow_editor($hideshow, $hideshow_text, $fam_marr_notice_text);

                        echo editor_label2(__('Date'));
                        echo $editor_cls->date_show($fam_marr_notice_date, "fam_marr_notice_date", "", "", $fam_marr_notice_date_hebnight, "fam_marr_notice_date_hebnight") . '<br>';

                        echo editor_label2(__('place'));
                        echo '<input type="text" name="fam_marr_notice_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($fam_marr_notice_place) . '" size="' . $field_place . '">';
                        echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_marr_notice_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_notice_text and preg_match('/\R/', $fam_marr_notice_text)) $field_text_selected = $field_text_medium;
                        echo editor_label2(__('text'));
                        echo '<textarea rows="1" placeholder="' . __('text') . '" name="fam_marr_notice_text" ' . $field_text_selected . '>' . $fam_marr_notice_text . '</textarea>';
                        echo '</span>';
                        echo '</td>';

                        echo '<td>';
                        // *** Source by fam_marr_notice ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            //source_link('relation',$marriage,'fam_marr_notice_source');
                            echo source_link2('601', $marriage, 'fam_marr_notice_source', 'marr_notice');
                        }
                        echo '</td></tr>';
                        // *** Show source by relation in iframe ***
                        echo edit_sources('601', 'family', 'fam_marr_notice_source', $marriage);

                        // *** Marriage ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '8';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                        echo '<tr class="humo_color"><td><a name="marriage_relation"></a>';
                        //echo '<a href="#marriage" onclick="hideShow(8);"><span id="hideshowlink8">[+]</span></a> ';
                        //echo __('Marriage').'</td>';
                        echo ucfirst(__('marriage/ relation')) . '</td>';

                        echo '<td colspan="2">';
                        $hideshow_text = '';

                        if (!$fam_kind) $hideshow_text .= '<span style="background-color:#FFAA80">' . __('Marriage/ Related') . '</span>';

                        $date_place = date_place($fam_marr_date, $fam_marr_place);
                        if ($date_place) {
                            if ($hideshow_text) $hideshow_text .= ', ';
                            $hideshow_text .= $date_place;
                        }

                        if ($fam_marr_authority) {
                            //if ($hideshow_text) $hideshow_text.='.';
                            $hideshow_text .= ' [' . $fam_marr_authority . ']';
                        }

                        echo hideshow_editor($hideshow, $hideshow_text, $fam_marr_text);

                        echo editor_label2(__('Date'));
                        echo $editor_cls->date_show($fam_marr_date, "fam_marr_date", "", "", $fam_marr_date_hebnight, "fam_marr_date_hebnight") . '<br>';

                        echo editor_label2(__('place'));
                        echo '<input type="text" name="fam_marr_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($fam_marr_place) . '" size="' . $field_place . '">';
                        echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_marr_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                        // *** Age of man by marriage ***
                        echo editor_label2(__('Age person 1'));
                        echo '<input type="text" name="fam_man_age" placeholder="' . __('Age') . '" value="' . $fam_man_age . '" size="3">';

                        // *** HELP POPUP for age by marriage ***
                        echo '&nbsp;&nbsp;<div class="fonts ' . $rtlmarker . 'sddm" style="display:inline;">';
                        echo '<a href="#" style="display:inline" ';
                        echo 'onmouseover="mopen(event,\'help_menu2\',100,400)"';
                        echo 'onmouseout="mclosetime()">';
                        echo '<img src="../images/help.png" height="16" width="16">';
                        echo '</a>';
                        echo '<div class="sddm_fixed" style="text-align:left; z-index:400; padding:4px; direction:' . $rtlmarker . '" id="help_menu2" onmouseover="mcancelclosetime()" onmouseout="mclosetime()">';
                        echo '<b>' . __('If birth year of man or woman is empty it will be calculated automatically using age by marriage.') . '</b><br>';
                        echo '</div>';
                        echo '</div><br>';

                        // *** Age of woman by marriage ***
                        echo editor_label2(__('Age person 2'));
                        echo '<input type="text" name="fam_woman_age" placeholder="' . __('Age') . '" value="' . $fam_woman_age . '" size="3"><br>';

                        if (!$fam_kind)
                            echo editor_label2('<span style="background-color:#FFAA80">' . __('Marriage/ Related') . '</span>');
                        else
                            echo editor_label2(__('Marriage/ Related'));

                        echo '<select size="1" name="fam_kind">';
                        //echo '<option value="civil">'.__('Married').' </option>';
                        echo '<option value="">' . __('Marriage/ Related') . ' </option>';

                        $selected = '';
                        if ($fam_kind == 'civil') {
                            $selected = ' selected';
                        }
                        echo '<option value="civil"' . $selected . '>' . __('Married') . '</option>';

                        $selected = '';
                        if ($fam_kind == 'living together') {
                            $selected = ' selected';
                        }
                        echo '<option value="living together"' . $selected . '>' . __('Living together') . '</option>';

                        $selected = '';
                        if ($fam_kind == 'living apart together') {
                            $selected = ' selected';
                        }
                        echo '<option value="living apart together"' . $selected . '>' . __('Living apart together') . '</option>';

                        $selected = '';
                        if ($fam_kind == 'intentionally unmarried mother') {
                            $selected = ' selected';
                        }
                        echo '<option value="intentionally unmarried mother"' . $selected . '>' . __('Intentionally unmarried mother') . '</option>';

                        $selected = '';
                        if ($fam_kind == 'homosexual') {
                            $selected = ' selected';
                        }
                        echo '<option value="homosexual"' . $selected . '>' . __('Homosexual') . '</option>';

                        $selected = '';
                        if ($fam_kind == 'non-marital') {
                            $selected = ' selected';
                        }
                        echo '<option value="non-marital"' . $selected . '>' . __('Non_marital') . '</option>';

                        $selected = '';
                        if ($fam_kind == 'extramarital') {
                            $selected = ' selected';
                        }
                        echo '<option value="extramarital"' . $selected . '>' . __('Extramarital') . '</option>';

                        $selected = '';
                        if ($fam_kind == 'partners') {
                            $selected = ' selected';
                        }
                        echo '<option value="partners"' . $selected . '>' . __('Partner') . '</option>';

                        $selected = '';
                        if ($fam_kind == 'registered') {
                            $selected = ' selected';
                        }
                        echo '<option value="registered"' . $selected . '>' . __('Registered partnership') . '</option>';

                        $selected = '';
                        if ($fam_kind == 'unknown') {
                            $selected = ' selected';
                        }
                        echo '<option value="unknown"' . $selected . '>' . __('Unknown relation') . '</option>';
                        echo '</select><br>';

                        echo editor_label2(__('Registrar'));
                        echo '<input type="text" placeholder="' . __('Registrar') . '" name="fam_marr_authority" value="' . $fam_marr_authority . '" size="60"><br>';

                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_text and preg_match('/\R/', $fam_marr_text)) $field_text_selected = $field_text_medium;
                        echo editor_label2(__('text'));
                        echo '<textarea rows="1" placeholder="' . __('text') . '" name="fam_marr_text" ' . $field_text_selected . '>' . $fam_marr_text . '</textarea>';

                        echo '</span>';
                        echo '</td>';

                        echo '<td>';
                        // *** Source by fam_marr ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            //source_link('relation',$marriage,'fam_marr_source');
                            echo source_link2('602', $marriage, 'fam_marr_source', 'marriage_relation');
                        }
                        echo '</td></tr>';
                        // *** Show source by relation in iframe ***
                        echo edit_sources('602', 'family', 'fam_marr_source', $marriage);

                        // *** Marriage Witness ***
                        echo $event_cls->show_event('family', $marriage, 'marriage_witness');

                        // *** Religious marriage notice ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '9';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                        echo '<tr><td><a name="marr_church_notice"></a>';
                        //echo '<a href="#marriage" onclick="hideShow(9);"><span id="hideshowlink9">[+]</span></a> ';
                        echo __('Religious Notice of Marriage') . '</td>';

                        echo '<td colspan="2">';
                        $hideshow_text = hideshow_date_place($fam_marr_church_notice_date, $fam_marr_church_notice_place);
                        echo hideshow_editor($hideshow, $hideshow_text, $fam_marr_church_notice_text);

                        echo editor_label2(__('Date'));
                        echo $editor_cls->date_show($fam_marr_church_notice_date, "fam_marr_church_notice_date", "", "", $fam_marr_church_notice_date_hebnight, "fam_marr_church_notice_date_hebnight") . '<br>';

                        echo editor_label2(__('place'));
                        echo '<input type="text" name="fam_marr_church_notice_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($fam_marr_church_notice_place) . '" size="' . $field_place . '">';
                        echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_marr_church_notice_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_church_notice_text and preg_match('/\R/', $fam_marr_church_notice_text)) $field_text_selected = $field_text_medium;
                        echo editor_label2(__('text'));
                        echo '<textarea rows="1" placeholder="' . __('text') . '" name="fam_marr_church_notice_text" ' . $field_text_selected . '>' . $fam_marr_church_notice_text . '</textarea>';
                        echo '</span>';
                        echo '</td>';

                        echo '<td>';
                        // *** Source by fam_marr_church_notice ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            //source_link('relation',$marriage,'fam_marr_church_notice_source');
                            echo source_link2('603', $marriage, 'fam_marr_church_notice_source', 'marr_church_notice');
                        }
                        echo '</td></tr>';
                        // *** Show source by relation in iframe ***
                        echo edit_sources('603', 'family', 'fam_marr_church_notice_source', $marriage);

                        // *** Church marriage ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '10';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                        echo '<tr class="humo_color"><td><a name="marr_church"></a>';
                        echo __('Religious Marriage') . '</td>';

                        echo '<td colspan="2">';
                        $hideshow_text = hideshow_date_place($fam_marr_church_date, $fam_marr_church_place);
                        echo hideshow_editor($hideshow, $hideshow_text, $fam_marr_church_text);

                        echo editor_label2(__('Date'));
                        echo $editor_cls->date_show($fam_marr_church_date, "fam_marr_church_date", "", "", $fam_marr_church_date_hebnight, "fam_marr_church_date_hebnight") . '<br>';

                        echo editor_label2(__('place'));
                        echo '<input type="text" name="fam_marr_church_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($fam_marr_church_place) . '" size="' . $field_place . '">';
                        echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_marr_church_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_church_text and preg_match('/\R/', $fam_marr_church_text)) $field_text_selected = $field_text_medium;
                        echo editor_label2(__('text'));
                        echo '<textarea rows="1" placeholder="' . __('text') . '" name="fam_marr_church_text" ' . $field_text_selected . '>' . $fam_marr_church_text . '</textarea>';
                        echo '</span>';
                        echo '</td>';

                        echo '<td>';
                        // *** Source by fam_marr_church ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            //source_link('relation',$marriage,'fam_marr_church_source');
                            echo source_link2('604', $marriage, 'fam_marr_church_source', 'marr_church');
                        }

                        echo '</td></tr>';
                        // *** Show source in iframe ***
                        echo edit_sources('604', 'family', 'fam_marr_church_source', $marriage);

                        // *** Marriage Witness (church) ***
                        echo $event_cls->show_event('family', $marriage, 'marriage_witness_rel');

                        // *** Religion ***
                        //echo '<tr class="humo_color"><td rowspan="1">'.__('Religion').'</td>';
                        echo '<tr class="humo_color"><td rowspan="1"></td>';
                        echo '<td colspan="2">';
                        echo editor_label2(__('Religion'));
                        echo '<input type="text" placeholder="' . __('Religion') . '" name="fam_religion" value="' . htmlspecialchars($fam_religion) . '" size="60"></td>';
                        echo '<td></td></tr>';

                        // *** Divorce ***
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '11';
                        // *** If items are missing show all editor fields ***
                        $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';

                        echo '<tr><td><a name="divorce"></a>';
                        //echo '<a href="#marriage" onclick="hideShow(11);"><span id="hideshowlink11">[+]</span></a> ';
                        echo __('Divorce') . '</td>';

                        echo '<td colspan="2">';
                        $hideshow_text = hideshow_date_place($fam_div_date, $fam_div_place);

                        if ($fam_div_authority) {
                            //if ($hideshow_text) $hideshow_text.='.';
                            $hideshow_text .= ' [' . $fam_div_authority . ']';
                        }

                        echo hideshow_editor($hideshow, $hideshow_text, $fam_div_text);

                        echo editor_label2(__('Date'));
                        echo $editor_cls->date_show($fam_div_date, "fam_div_date") . '<br>';

                        echo editor_label2(__('place'));
                        echo '<input type="text" name="fam_div_place" placeholder="' . ucfirst(__('place')) . '" value="' . htmlspecialchars($fam_div_place) . '" size="' . $field_place . '">';
                        echo '<a href="#" onClick=\'window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_div_place","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a><br>';

                        $text = '';
                        if ($fam_div_authority) $text = htmlspecialchars($fam_div_authority);
                        echo editor_label2(__('Registrar'));
                        echo '<input type="text" placeholder="' . __('Registrar') . '" name="fam_div_authority" value="' . $text . '" size="60"><br>';

                        if ($fam_div_text == 'DIVORCE') $fam_div_text = ''; // *** Hide this text, it's a hidden value for a divorce without data ***
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_div_text and preg_match('/\R/', $fam_div_text)) $field_text_selected = $field_text_medium;
                        echo editor_label2(__('text'));
                        echo '<textarea rows="1" placeholder="' . __('text') . '" name="fam_div_text" ' . $field_text_selected . '>' . $fam_div_text . '</textarea>';
                        echo '</span>';
                        echo '</td>';

                        echo '<td>';
                        // *** Source by fam_div ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            //source_link('relation',$marriage,'fam_div_source');
                            echo source_link2('605', $marriage, 'fam_div_source', 'divorce');
                        }
                        echo '</td></tr>';
                        // *** Show source by relation in iframe ***
                        echo edit_sources('605', 'family', 'fam_div_source', $marriage);

                        // *** Use checkbox for divorse without further data ***
                        echo '<tr><td></td>';
                        echo '<td colspan="2">';
                        $checked = '';
                        if ($fam_div_no_data) $checked = ' checked';
                        echo '<input type="checkbox" name="fam_div_no_data" value="no_data"' . $checked . '> ' . __('Divorce (use this checkbox for a divorce without further data).');
                        echo '</td><td></td></tr>';

                        // *** General text by relation ***
                        echo '<tr class="humo_color"><td><a name="fam_text"></a>' . __('Text by relation') . '</td>';
                        echo '<td style="border-right:0px;"></td>';
                        echo '<td style="border-left:0px;">';
                        echo '<textarea rows="1" placeholder="' . __('Text by relation') . '" name="fam_text"' . $field_text_large . '>' . $fam_text . '</textarea>';
                        echo '</td><td>';
                        // *** Source by text ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            //source_link('relation',$marriage,'fam_text_source');
                            echo source_link2('606', $marriage, 'fam_text_source', 'fam_text');
                        }
                        echo '</td></tr>';
                        // *** Show source by relation in iframe ***
                        echo edit_sources('606', 'family', 'fam_text_source', $marriage);

                        // *** Relation sources in new person editor screen ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            echo '<tr><td><a name="fam_source"></a>' . __('Source by relation') . '</td><td colspan="2">';
                            echo '</td><td>';
                            //source_link('relation',$marriage,'family_source');
                            echo source_link2('607', $marriage, 'family_source', 'fam_source');
                            echo '</td></tr>';
                        }
                        // *** Show source by relation in iframe ***
                        echo edit_sources('607', 'family', 'family_source', $marriage);

                        // *** Picture ***
                        echo $event_cls->show_event('family', $marriage, 'marriage_picture');

                        // *** Family event editor ***
                        echo $event_cls->show_event('family', $marriage, 'family');

                        // *** Show and edit addresses by family ***
                        edit_addresses('family', 'family_address', $marriage);

                        // *** Show unprocessed GEDCOM tags ***
                        $tag_qry = "SELECT * FROM humo_unprocessed_tags
                WHERE tag_tree_id='" . $tree_id . "'
                AND tag_rel_id='" . $familyDb->fam_id . "'";
                        $tag_result = $dbh->query($tag_qry);
                        //$num_rows = $tag_result->rowCount();
                        $tagDb = $tag_result->fetch(PDO::FETCH_OBJ);
                        if (isset($tagDb->tag_tag)) {
                            $tags_array = explode('<br>', $tagDb->tag_tag);
                            $num_rows = count($tags_array);
                            echo '<tr class="humo_tags_fam"><td>';
                            echo '<a href="#humo_tags_fam" onclick="hideShow(110);"><span id="hideshowlink110">[+]</span></a> ';
                            echo __('GEDCOM tags') . '</td><td colspan="2">';
                            if ($tagDb->tag_tag) {
                                printf(__('There are %d unprocessed GEDCOM tags.'), $num_rows);
                            } else {
                                printf(__('There are %d unprocessed GEDCOM tags.'), 0);
                            }
                            echo '</td><td></td></tr>';
                            //echo '<tr style="display:none;" class="row110" name="row110"><td></td>';
                            echo '<tr style="display:none;" class="row110"><td></td>';
                            echo '<td colspan="2">' . $tagDb->tag_tag . '</td>';
                            echo '<td></td></tr>';
                        }

                        // *** Show editor notes ***
                        show_editor_notes('family');

                        // *** Relation added by user ***
                        if ($familyDb->fam_new_user) {
                            echo '<tr class="table_header_large"><td>' . __('Added by') . '</td>';
                            //echo '<td colspan="2">'.$familyDb->fam_new_user.' ('.$familyDb->fam_new_date.' '.$familyDb->fam_new_time.')</td><td></td></tr>';
                            echo '<td colspan="2">' . $familyDb->fam_new_user . ' (' . language_date($familyDb->fam_new_date) . ' ' . $familyDb->fam_new_time . ')</td><td></td></tr>';
                        }
                        // *** Relation changed by user ***
                        if ($familyDb->fam_changed_user) {
                            echo '<tr class="table_header_large"><td>' . __('Changed by') . '</td>';
                            //echo '<td colspan="2">'.$familyDb->fam_changed_user.' ('.$familyDb->fam_changed_date.' '.$familyDb->fam_changed_time.')</td><td></td></tr>';
                            echo '<td colspan="2">' . $familyDb->fam_changed_user . ' (' . language_date($familyDb->fam_changed_date) . ' ' . $familyDb->fam_changed_time . ')</td><td></td></tr>';
                        }

                        // *** Extra "Save" line ***
                        echo '<tr class="table_header_large">';
                        echo '<td></td><td colspan="2"></td>';
                        echo '<td style="border-left: none; text-align:left; font-size: 1.5em;">';
                        echo '<input type="Submit" name="marriage_change" value="' . __('Save') . '">';
                        echo '</td>';
                        echo '</tr>';

                        ?>
                    </table><br>
                    <?php
                    echo '</form>';

                    //if ($menu_tab=='children' and $person->pers_fams){
                    //if ($person->pers_fams){
                    if ($marriage) {

                        // *** Automatic order of children ***
                        if (isset($_GET['order_children'])) {
                            function date_string($text)
                            {
                                // *** Remove special date items ***
                                $text = str_replace('BEF ', '', $text);
                                $text = str_replace('ABT ', '', $text);
                                $text = str_replace('AFT ', '', $text);
                                $text = str_replace('BET ', '', $text);
                                $text = str_replace('INT ', '', $text);
                                $text = str_replace('EST ', '', $text);
                                $text = str_replace('CAL ', '', $text);

                                $day = '';
                                // *** Skip $day if there is only year ***
                                if (strlen($text) > 4) {
                                    // Add 0 if day is single digit: 9 JUN 1954
                                    if (substr($text, 1, 1) == ' ') $day = '0' . substr($text, 0, 1);
                                    elseif (is_numeric(substr($text, 0, 2))) $day = substr($text, 0, 2);
                                    else $day = '00';
                                } else {
                                    $text = '00 ' . $text; // No month, use 00.
                                    $day = '00'; // No day, use 00.
                                }

                                $text = str_replace("JAN", "01", $text);
                                $text = str_replace("FEB", "02", $text);
                                $text = str_replace("MAR", "03", $text);
                                $text = str_replace("APR", "04", $text);
                                $text = str_replace("MAY", "05", $text);
                                $text = str_replace("JUN", "06", $text);
                                $text = str_replace("JUL", "07", $text);
                                $text = str_replace("AUG", "08", $text);
                                $text = str_replace("SEP", "09", $text);
                                $text = str_replace("OCT", "10", $text);
                                $text = str_replace("NOV", "11", $text);
                                $text = str_replace("DEC", "12", $text);
                                //$returnstring = substr($text,-4).substr(substr($text,-7),0,2).substr($text,0,2);
                                $returnstring = substr($text, -4) . substr(substr($text, -7), 0, 2) . $day;

                                return $returnstring;
                                // Solve maybe later: date_string 2 mei is smaller then 10 may (2 birth in 1 month is rare...).
                            }

                            //echo '<br>&gt;&gt;&gt; '.__('Order children...');

                            //only get children...
                            $fam_qry = $dbh->query("SELECT * FROM humo_families
                    WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $marriage . "'");
                            $famDb = $fam_qry->fetch(PDO::FETCH_OBJ);
                            $child_array = explode(";", $famDb->fam_children);
                            $nr_children = count($child_array);
                            if ($nr_children > 1) {
                                unset($children_array);
                                for ($i = 0; $i < $nr_children; $i++) {
                                    @$childDb = $db_functions->get_person($child_array[$i]);

                                    $child_array_nr = $child_array[$i];
                                    if ($childDb->pers_birth_date) {
                                        $children_array[$child_array_nr] = date_string($childDb->pers_birth_date);
                                    } elseif ($childDb->pers_bapt_date) {
                                        $children_array[$child_array_nr] = date_string($childDb->pers_bapt_date);
                                    } else {
                                        $children_array[$child_array_nr] = '';
                                    }
                                }

                                asort($children_array);

                                $fam_children = '';
                                foreach ($children_array as $key => $val) {
                                    if ($fam_children != '') {
                                        $fam_children .= ';';
                                    }
                                    $fam_children .= $key;
                                }

                                if ($famDb->fam_children != $fam_children) {
                                    $sql = "UPDATE humo_families SET fam_children='" . $fam_children . "'
                            WHERE fam_id='" . $famDb->fam_id . "'";
                                    $dbh->query($sql);
                                }
                            }
                        }

                        // *** Show children ***
                        $family = $dbh->query("SELECT * FROM humo_families
                WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $marriage . "'");
                        $familyDb = $family->fetch(PDO::FETCH_OBJ);
                        if ($familyDb->fam_children) {
                            echo '<a name="children"></a>';
                            echo __('Use this icon to order children (drag and drop)') . ': <img src="' . CMS_ROOTPATH_ADMIN . 'images/drag-icon.gif" border="0">';

                            echo '<br>' . __('Or automatically order children:') . ' <a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;menu_tab=marriage&amp;marriage_nr=' . $marriage . '&amp;order_children=1#children">' . __('Automatic order children') . '</a>';

                            if (isset($_GET['order_children'])) echo ' <b>' . __('Children are re-ordered.') . '</b>';

                            //echo __('Children').':<br>';
                            $fam_children_array = explode(";", $familyDb->fam_children);
                            echo '<ul id="sortable' . $i . '" class="sortable">';
                            foreach ($fam_children_array as $j => $value) {
                                // *** Create new children variabele, for disconnect child ***
                                $fam_children = '';
                                foreach ($fam_children_array as $k => $value) {
                                    if ($k != $j) {
                                        $fam_children .= $fam_children_array[$k] . ';';
                                    }
                                }
                                $fam_children = substr($fam_children, 0, -1); // *** strip last ; character ***

                                echo '<li><span style="cursor:move;" id="' . $fam_children_array[$j] . '" class="handle' . $i . '" ><img src="' . CMS_ROOTPATH_ADMIN . 'images/drag-icon.gif" border="0" title="' . __('Drag to change order (saves automatically)') . '" alt="' . __('Drag to change order') . '"></span>&nbsp;&nbsp;';

                                echo '<a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;family_id=' . $familyDb->fam_id . '&amp;child_disconnect=' . $fam_children .
                                    '&amp;child_disconnect_gedcom=' . $fam_children_array[$j] . '">
                        <img src="' . CMS_ROOTPATH_ADMIN . 'images/person_disconnect.gif" border="0" title="' . __('Disconnect child') . '" alt="' . __('Disconnect child') . '"></a>';
                                echo '&nbsp;&nbsp;<span id="chldnum' . $fam_children_array[$j] . '">' . ($j + 1) . '</span>. ' . show_person($fam_children_array[$j], true) . '</li>';
                            }
                            echo '</ul>';
                        }

                        // *** Add child ***
                        $pers_sexe = '';
                        add_person('child', $pers_sexe);

                        // *** Search existing person as child ***
                        echo '<form method="POST" action="' . $phpself . '" style="display : inline;" name="form7" id="form7">';
                        echo '<input type="hidden" name="page" value="' . $page . '">';
                        //if (isset($_GET['children'])){
                        if (isset($familyDb->fam_children)) {
                            echo '<input type="hidden" name="children" value="' . $familyDb->fam_children . '">';
                        }
                        echo '<input type="hidden" name="family_id" value="' . $familyDb->fam_gedcomnumber . '">';
                        echo __('Or add existing person as a child:') . ' <input class="fonts" type="text" name="child_connect2" value="" size="17" placeholder="' . __('GEDCOM number (ID)') . '" required>';

                        echo '<a href="#" onClick=\'window.open("index.php?page=editor_person_select&person=0&person_item=child_connect2&tree_id=' . $tree_id . '","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a>';

                        echo ' <input type="Submit" name="dummy4" value="' . __('Select child') . '">';
                        echo '</form><br>';
                        //echo '<p>'.__('Or add a new child:').'<br>';

                        // *** Order children using drag and drop using jquery and jqueryui ***
                    ?>
                        <script>
                            $('#sortable' + '<?= $i; ?>').sortable({
                                handle: '.handle' + '<?= $i; ?>'
                            }).bind('sortupdate', function() {
                                var childstring = "";
                                var chld_arr = document.getElementsByClassName("handle" + "<?= $i; ?>");
                                for (var z = 0; z < chld_arr.length; z++) {
                                    childstring = childstring + chld_arr[z].id + ";";
                                    document.getElementById('chldnum' + chld_arr[z].id).innerHTML = (z + 1);
                                }
                                childstring = childstring.substring(0, childstring.length - 1);
                                $.ajax({
                                    url: "include/drag.php?drag_kind=children&chldstring=" + childstring + "&family_id=" + "<?= $familyDb->fam_id; ?>",
                                    success: function(data) {},
                                    error: function(xhr, ajaxOptions, thrownError) {
                                        alert(xhr.status);
                                        alert(thrownError);
                                    }
                                });
                            });
                        </script>
    <?php
                    }
                }
            }
        }    // End of menu_tab
        //if ($menu_admin=='person' AND $menu_tab!='children') echo '</div>';

        // Moved children to relation part of the script.

    }
} // End person check


//} was person check


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
//function iframe_source($hideshow,$connect_kind,$connect_sub_kind,$connect_connect_id){
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
    return $text;
}


//function witness_edit($witness, $multiple_rows=''){
function witness_edit($event_text, $witness, $multiple_rows = '')
{
    global $dbh, $tree_id, $language, $menu_tab, $field_popup;
    $text = '';

    // *** Witness select popup screen ***
    $value = '';
    if (substr($witness, 0, 1) == '@') {
        $value = substr($witness, 1, -1);
        //$text.=show_person(substr($witness,1,-1),$gedcom_date=false, $show_link=false).'<br>';
    }

    $person_item = 'person_witness';
    if ($menu_tab == 'marriage') $person_item = 'marriage_witness';

    // *** Orange items if no witness name is selected or added in text ***
    $style = '';
    if (!$witness) $style = 'style="background-color:#FFAA80"';

    $text .= '<input class="fonts" ' . $style . ' type="text" name="text_event2' . substr($multiple_rows, 1, -1) . '" value="' . $value . '" size="17" placeholder="' . __('GEDCOM number (ID)') . '">';
    $text .= '<a href="#" onClick=\'window.open("index.php?page=editor_person_select&person=0&person_item=' . $person_item . '&event_row=' . substr($multiple_rows, 1, -1) . '&tree_id=' . $tree_id . '","","' . $field_popup . '")\'><img src="../images/search.png" alt="' . __('Search') . '"></a>';

    // *** Witness: text field ***
    $witness_value = $witness;
    if (substr($witness, 0, 1) == '@') {
        $witness_value = '';
    }
    $text .= ' <b>' . __('or') . ':</b> <input type="text" ' . $style . ' name="text_event' . $multiple_rows . '" value="' . htmlspecialchars($witness_value) . '" placeholder="' . $event_text . '" size="44">';

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
            echo '<tr class="humo_color"><td></td><td><input type="Submit" name="relation_add" value="' . __('Add relation') . '"></td></tr>';
        } else {
            echo '<tr class="humo_color"><td></td><td><input type="Submit" name="person_add" value="' . __('Add child') . '"></td></tr>';
        }
        ?>
    </table>
<?php
    echo '</form>';
}


function show_person($gedcomnumber, $gedcom_date = false, $show_link = true)
{
    global $dbh, $db_functions, $page, $joomlastring;
    if ($gedcomnumber) {
        $personDb = $db_functions->get_person($gedcomnumber);

        $name = '';
        $name .= $personDb->pers_firstname . ' ';
        if ($personDb->pers_patronym) $name .= $personDb->pers_patronym . ' ';
        $name .= strtolower(str_replace("_", " ", $personDb->pers_prefix)) . $personDb->pers_lastname;
        if (trim($name) == '') $name = '[' . __('NO NAME') . ']';

        if ($show_link == true) {
            $text = '<a href="index.php?' . $joomlastring . 'page=' . $page . '&amp;menu_tab=person&amp;tree_id=' . $personDb->pers_tree_id .
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
    global $dbh, $tree_id, $joomlastring, $page, $editor_cls, $field_place, $field_text;
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
                echo ' <input type="Submit" name="person_add_address" value="' . __('Add') . '">';
            } else {
                echo ' <input type="Submit" name="relation_add_address" value="' . __('Add') . '">';
            }

            // *** HELP POPUP for address ***
            ?>
            &nbsp;<div class="fonts <?= $rtlmarker; ?>sddm" style="display:inline;">
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
                echo '<a href="index.php?' . $joomlastring . 'page=' . $page .
                    '&amp;person_place_address=1&amp;connect_drop=' . $addressDb->connect_id . '">
                <img src="' . CMS_ROOTPATH_ADMIN . 'images/button_drop.png" border="0" alt="drop"></a>';

                // *** Order addresses ***
                if ($addressDb->connect_order < $count) {
                    echo ' <a href="index.php?' . $joomlastring . 'page=' . $page .
                        '&amp;person_place_address=1&amp;connect_down=' . $addressDb->connect_id .
                        '&amp;connect_kind=' . $addressDb->connect_kind .
                        '&amp;connect_sub_kind=' . $addressDb->connect_sub_kind .
                        '&amp;connect_connect_id=' . $addressDb->connect_connect_id .
                        '&amp;connect_order=' . $addressDb->connect_order;
                    echo '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_down.gif" border="0" alt="down"></a>';
                } else {
                    echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                }

                if ($addressDb->connect_order > 1) {
                    echo ' <a href="index.php?' . $joomlastring . 'page=' . $page .
                        '&amp;person_place_address=1&amp;connect_up=' . $addressDb->connect_id .
                        '&amp;connect_kind=' . $addressDb->connect_kind .
                        '&amp;connect_sub_kind=' . $addressDb->connect_sub_kind .
                        '&amp;connect_connect_id=' . $addressDb->connect_connect_id .
                        '&amp;connect_order=' . $addressDb->connect_order;
                    echo '"><img src="' . CMS_ROOTPATH_ADMIN . 'images/arrow_up.gif" border="0" alt="up"></a>';
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
                            pers_place_index='" . $address3Db->address_place . "'
                            WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . safe_text_db($pers_gedcomnumber) . "'";
                        $result = $dbh->query($sql);
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
                //echo ' <a href="index.php?'.$joomlastring.'page='.$page.
                //'&amp;menu_admin=person
                //&amp;person_place_address=1
                //&amp;address_add2=1
                //&amp;connect_id='.$addressDb->connect_id.'
                //&amp;connect_kind='.$addressDb->connect_kind.'
                //&amp;connect_sub_kind='.$addressDb->connect_sub_kind.'
                //&amp;connect_connect_id='.$addressDb->connect_connect_id.'
                //#addresses">['.__('Add').']</a> ';
                echo ' <a href="index.php?' . $joomlastring . 'page=' . $page .
                    '&amp;menu_admin=' . $connect_kind;
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
    $cacheqry = $dbh->query("SELECT * FROM humo_settings
        WHERE setting_variable='cache_latest_changes' AND setting_tree_id='" . $tree_id . "'");
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
        $person_qry = "(SELECT pers_id, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
            FROM humo_persons
            WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NOT NULL AND pers_changed_date!='')";

        $person_qry .= " UNION (SELECT pers_id, STR_TO_DATE(pers_new_date,'%d %b %Y') AS changed_date, pers_new_time as changed_time
            FROM humo_persons
            WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NULL) ";

        $person_qry .= " ORDER BY changed_date DESC, changed_time DESC LIMIT 0,15";
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
                $sql = "UPDATE humo_settings SET
                    setting_variable='cache_latest_changes', setting_value='" . safe_text_db($cache) . "'
                    WHERE setting_tree_id='" . safe_text_db($tree_id) . "'";
                $result = $dbh->query($sql);
            } else {
                $sql = "INSERT INTO humo_settings SET
                    setting_variable='cache_latest_changes', setting_value='" . safe_text_db($cache) . "',
                    setting_tree_id='" . safe_text_db($tree_id) . "'";
                $result = $dbh->query($sql);
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
            <a href="index.php?page=editor&amp;menu_admin=person&amp;note_add=<?= $note_connect_kind . $anchor; ?>">[<?= __('Add'); ?>]</a>
            <?php
            if ($num_rows)
                printf(__('There are %d editor notes.'), $num_rows);
            else
                printf(__('There are %d editor notes.'), 0);
            ?>
        </td>
        <td></td>
    </tr>
    <?php

    while ($noteDb = $note_result->fetch(PDO::FETCH_OBJ)) {
        //TODO combine queries.
        $user_result = $dbh->query("SELECT * FROM humo_users WHERE user_id='" . $noteDb->note_new_user_id . "'");
        $user_addedDb = $user_result->fetch(PDO::FETCH_OBJ);
    ?>
        <tr>
            <td>
                <!-- Link to remove note -->
                <a href="index.php?page=editor&amp;menu_admin=person&amp;note_drop=<?= $noteDb->note_id; ?>">
                    <img src="<?= CMS_ROOTPATH_ADMIN; ?>images/button_drop.png" border="0" alt="down"></a>
            </td>
            <td colspan="2">
                <input type="hidden" name="note_id[<?= $noteDb->note_id; ?>]" value="<?= $noteDb->note_id; ?>">
                <input type="hidden" name="note_connect_kind[<?= $noteDb->note_id; ?>]" value="<?= $note_connect_kind; ?>">
                <?= __('Added by'); ?> <b><?= $user_addedDb->user_name; ?></b> (<?= language_date($noteDb->note_new_date) . ' ' . $noteDb->note_new_time; ?>)<br>
                <?php
                if ($noteDb->note_changed_user_id) {
                    //TODO combine queries
                    $user_result = $dbh->query("SELECT * FROM humo_users WHERE user_id='" . $noteDb->note_changed_user_id . "'");
                    $userDb = $user_result->fetch(PDO::FETCH_OBJ);
                    echo __('Changed by') . ' <b>' . $userDb->user_name . '</b> (' . language_date($noteDb->note_changed_date) . ' ' . $noteDb->note_changed_time . ')<br>';
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

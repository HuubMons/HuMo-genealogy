<?php

/**
 * This is the editor file for HuMo-genealogy.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

//globals for joomla
global $tree_prefix, $gedcom_date, $gedcom_time, $pers_gedcomnumber;



// TODO create seperate controller script.
// TEMPORARY CONTROLLER HERE:
require_once  __DIR__ . "/../models/editor.php";
$get_editor = new EditorModel($dbh);
$menu_admin = $get_editor->getMenuAdmin();
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



$phpself = 'index.php';
$sourcestring = '../source.php?';
$addresstring = '../address.php?';

$field_text_large = 'style="height: 100px; width:550px"';

include_once(__DIR__ . "/../include/editor_cls.php");
$editor_cls = new editor_cls;

include(__DIR__ . '/../include/editor_event_cls.php');
$event_cls = new editor_event_cls;


// *****************************
// *** HuMo-genealogy Editor ***
// *****************************

$new_tree = false;

// *** Editor icon for admin and editor: select family tree ***
if (isset($tree_id) and $tree_id) {
    $db_functions->set_tree_id($tree_id);
}

$userid = false;
if (is_numeric($_SESSION['user_id_admin'])) $userid = $_SESSION['user_id_admin'];
$username = $_SESSION['user_name_admin'];
$gedcom_date = strtoupper(date("d M Y"));
$gedcom_time = date("H:i:s");

if (isset($tree_id)) {
    // *** Process queries ***
    include_once(__DIR__ . "/../include/editor_inc.php");
}


// *******************
// *** Show places ***
// *******************

if ($menu_admin == 'places') {
    echo '<h1 class="center">' . __('Rename places') . '</h1>';

    //echo __('Update all places here. At this moment these places are updated: birth, baptise, death and burial places.').'<br>';

    if (isset($_POST['place_change'])) {
        $sql = "UPDATE humo_persons SET pers_birth_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE pers_tree_id='" . $tree_id . "' AND pers_birth_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_persons SET pers_bapt_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE pers_tree_id='" . $tree_id . "' AND pers_bapt_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_persons SET pers_death_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE pers_tree_id='" . $tree_id . "' AND pers_death_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_persons SET pers_buried_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE pers_tree_id='" . $tree_id . "' AND pers_buried_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_families SET fam_relation_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE fam_tree_id='" . $tree_id . "' AND fam_relation_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_families SET fam_marr_notice_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE fam_tree_id='" . $tree_id . "' AND fam_marr_notice_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_families SET fam_marr_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE fam_tree_id='" . $tree_id . "' AND fam_marr_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_families SET fam_marr_church_notice_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE fam_tree_id='" . $tree_id . "' AND fam_marr_church_notice_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_families SET fam_marr_church_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE fam_tree_id='" . $tree_id . "' AND fam_marr_church_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_families SET fam_div_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE fam_tree_id='" . $tree_id . "' AND fam_div_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_addresses SET address_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE address_tree_id='" . $tree_id . "' AND address_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_events SET event_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE event_tree_id='" . $tree_id . "' AND event_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_sources SET source_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE source_tree_id='" . $tree_id . "' AND source_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        $sql = "UPDATE humo_connections SET connect_place='" . $editor_cls->text_process($_POST['place_new']) . "'
            WHERE connect_tree_id='" . $tree_id . "' AND connect_place='" . safe_text_db($_POST["place_old"]) . "'";
        $result = $dbh->query($sql);

        if (isset($_POST["google_maps"])) {
            // *** Check if Google Maps table already exist ***
            $tempqry = $dbh->query("SHOW TABLES LIKE 'humo_location'");
            if ($tempqry->rowCount()) {
                $sql = "UPDATE humo_location
                    SET location_location ='" . safe_text_db($_POST['place_new']) . "'
                    WHERE location_location = '" . safe_text_db($_POST['place_old']) . "'";
                $result = $dbh->query($sql);
            }
        }

        // *** Show changed place again ***
        $_POST["place_select"] = $_POST['place_new'];

        //echo '<b>'.__('UPDATE OK!').'</b> ';
    }

    $first = true;
    $person_qry = '';
    if (isset($_POST['person_places'])) {
        $first = false;
        $person_qry .= "(SELECT pers_birth_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_birth_place)
            UNION (SELECT pers_bapt_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_bapt_place)
            UNION (SELECT pers_death_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_death_place)
            UNION (SELECT pers_buried_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_buried_place)";
    }

    if (isset($_POST['family_places'])) {
        if (!$first) {
            $first = false;
            $person_qry .= " UNION ";
        }
        $person_qry .= "(SELECT fam_relation_place as place_edit FROM humo_families WHERE fam_tree_id='" . $tree_id . "' GROUP BY fam_relation_place)
            UNION (SELECT fam_marr_notice_place as place_edit FROM humo_families WHERE fam_tree_id='" . $tree_id . "' GROUP BY fam_marr_notice_place)
            UNION (SELECT fam_marr_place as place_edit FROM humo_families WHERE fam_tree_id='" . $tree_id . "' GROUP BY fam_marr_place)
            UNION (SELECT fam_marr_church_notice_place as place_edit FROM humo_families WHERE fam_tree_id='" . $tree_id . "' GROUP BY fam_marr_church_notice_place)
            UNION (SELECT fam_div_place as place_edit FROM humo_families WHERE fam_tree_id='" . $tree_id . "' GROUP BY fam_div_place)";
    }

    if (isset($_POST['other_places'])) {
        if (!$first) {
            $first = false;
            $person_qry .= " UNION ";
        }
        $person_qry .= "(SELECT address_place as place_edit FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' GROUP BY address_place)
            UNION (SELECT event_place as place_edit FROM humo_events WHERE event_tree_id='" . $tree_id . "' GROUP BY event_place)
            UNION (SELECT source_place as place_edit FROM humo_sources WHERE source_tree_id='" . $tree_id . "' GROUP BY source_place)
            UNION (SELECT connect_place as place_edit FROM humo_connections WHERE connect_tree_id='" . $tree_id . "' GROUP BY connect_place)";
    }

    // *** Order results ***
    if ($person_qry != '') {
        $person_qry .= ' ORDER BY place_edit';
    }

    // *** Just for sure: if no $_POST is found show person places ***
    if ($person_qry == '') {
        $_POST['person_places'] = 'on';
        $person_qry .= "(SELECT pers_birth_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_birth_place)
            UNION (SELECT pers_bapt_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_bapt_place)
            UNION (SELECT pers_death_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_death_place)
            UNION (SELECT pers_buried_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_buried_place)
            ORDER BY place_edit";
    }

    $person_result = $dbh->query($person_qry);
    echo '<table class="humo standard" style="text-align:center;"><tr class="table_header_large"><td>';

    // *** Select family tree ***
    echo __('Family tree') . ': ';
    $editor_cls->select_tree($page);

    echo ' <form method="POST" action="' . $phpself . '" style="display : inline;">';
    echo $person_result->rowCount() . ' ' . __('Places') . '. ';
    echo __('Select location');
    echo ' <input type="hidden" name="page" value="' . $page . '">';
    echo '<select size="1" name="place_select">';
    while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
        if ($person->place_edit != '') {
            $selected = '';
            if (isset($_POST["place_select"]) and $_POST["place_select"] == $person->place_edit) {
                $selected = " selected";
            }
            echo '<option value="' . $person->place_edit . '"' . $selected . '>' . $person->place_edit . '</option>';
        }
    }
    echo '</select><br>';

    $check = '';
    if (isset($_POST['person_places'])) $check = ' checked';
    echo '<input type="checkbox" name="person_places"' . $check . '>' . __('Person places');
    $check = '';
    if (isset($_POST['family_places'])) $check = ' checked';
    echo ' <input type="checkbox" name="family_places"' . $check . '>' . __('Family places');
    $check = '';
    if (isset($_POST['other_places'])) $check = ' checked';
    echo ' <input type="checkbox" name="other_places"' . $check . '>' . __('Other places (sources, events, addresses, etc.)');

    echo ' <input type="Submit" name="dummy8" value="' . __('Select') . '">';
    echo '</form>';
    echo '</td></tr></table><br>';

    // *** Change selected place ***
    if (isset($_POST["place_select"]) and $_POST["place_select"] != '') {
        echo '<form method="POST" action="' . $phpself . '">';
        echo '<table class="humo standard" border="1">';
        echo '<tr class="table_header"><th colspan="2">' . __('Change location') . '</th></tr>';
        echo '<tr><td>';
        echo '<input type="hidden" name="page" value="' . $page . '">';
        echo '<input type="hidden" name="place_old" value="' . $_POST["place_select"] . '">';

        if (isset($_POST['person_places'])) echo '<input type="hidden" name="person_places" value="on">';
        if (isset($_POST['family_places'])) echo '<input type="hidden" name="family_places" value="on">';
        if (isset($_POST['other_places'])) echo '<input type="hidden" name="other_places" value="on">';

        echo __('Change location') . ':</td><td><input type="text" name="place_new" value="' . $_POST["place_select"] . '" size="60"><br>';
        echo '<input type="Checkbox" name="google_maps" value="1" checked>' . __('Also change Google Maps table.') . '<br>';
        echo '<input type="Submit" name="place_change" value="' . __('Save') . '">';
        echo '</td></tr>';
        echo '</table>';
        echo '</form>';
    }

    //echo '<br><br><br>'; // in some browser settings the bottom line (with the event choice!) is hidden under bottom bar
}

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
$get_editor = new Editor($dbh);
$menu_admin = $get_editor->getMenuAdmin();
//$this->view("families", array(
//    "family" => $family,
//    "title" => __('Family')
//));



$phpself = 'index.php';
$joomlastring = '';
$sourcestring = '../source.php?';
$addresstring = '../address.php?';
//$path_prefix = '../';

$field_text_large = 'style="height: 100px; width:550px"';

include_once(__DIR__."/../include/editor_cls.php");
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
    include_once(__DIR__."/../include/editor_inc.php");
}


// ****************************
// *** Show/ edit addresses ***
// ****************************

if ($menu_admin == 'addresses') {
    if (isset($_POST['address_add'])) {
        // *** Generate new GEDCOM number ***
        $new_gedcomnumber = 'R' . $db_functions->generate_gedcomnr($tree_id, 'address');

        //address_date='".safe_text_db($_POST['address_date'])."',
        $sql = "INSERT INTO humo_addresses SET
            address_tree_id='" . $tree_id . "',
            address_gedcomnr='" . $new_gedcomnumber . "',
            address_shared='1',
            address_address='" . $editor_cls->text_process($_POST['address_address']) . "',
            address_zip='" . safe_text_db($_POST['address_zip']) . "',
            address_place='" . $editor_cls->text_process($_POST['address_place']) . "',
            address_phone='" . safe_text_db($_POST['address_phone']) . "',
            address_text='" . $editor_cls->text_process($_POST['address_text']) . "',
            address_new_user='" . $username . "',
            address_new_date='" . $gedcom_date . "',
            address_new_time='" . $gedcom_time . "'";
        $result = $dbh->query($sql);

        //$new_address_qry= "SELECT * FROM humo_addresses
        //	WHERE address_tree_id='".$tree_id."' ORDER BY address_id DESC LIMIT 0,1";
        //$new_address_result = $dbh->query($new_address_qry);
        //$new_address=$new_address_result->fetch(PDO::FETCH_OBJ);
        //$_POST['address_id']=$new_address->address_id;
        $_POST['address_id'] = $dbh->lastInsertId();
    }

    if (isset($_POST['address_change'])) {
        //address_photo='".safe_text_db($_POST['address_photo'])."',

        // *** Date by address is processed in connection table ***
        //address_date='".$editor_cls->date_process('address_date')."',
        $sql = "UPDATE humo_addresses SET
            address_address='" . $editor_cls->text_process($_POST['address_address']) . "',
            address_zip='" . safe_text_db($_POST['address_zip']) . "',
            address_place='" . $editor_cls->text_process($_POST['address_place']) . "',
            address_phone='" . safe_text_db($_POST['address_phone']) . "',
            address_text='" . $editor_cls->text_process($_POST['address_text'], true) . "',
            address_changed_user='" . $username . "',
            address_changed_date='" . $gedcom_date . "',
            address_changed_time='" . $gedcom_time . "'
            WHERE address_id='" . safe_text_db($_POST["address_id"]) . "'";
        $result = $dbh->query($sql);
    }

    if (isset($_POST['address_remove'])) {
        echo '<div class="confirm">';
        echo __('Are you sure you want to remove this address and ALL address references?');
        echo ' <form method="post" action="' . $phpself . '" style="display : inline;">';
        echo '<input type="hidden" name="page" value="' . $page . '">';
        echo '<input type="hidden" name="address_id" value="' . $_POST['address_id'] . '">';
        echo '<input type="hidden" name="address_gedcomnr" value="' . $_POST['address_gedcomnr'] . '">';
        echo ' <input type="Submit" name="address_remove2" value="' . __('Yes') . '" style="color : red; font-weight: bold;">';
        echo ' <input type="Submit" name="dummy7" value="' . __('No') . '" style="color : blue; font-weight: bold;">';
        echo '</form>';
        echo '</div>';
    }
    if (isset($_POST['address_remove2'])) {
        echo '<div class="confirm">';

        // *** Remove sources by this address from connection table ***
        $sql = "DELETE FROM humo_connections
            WHERE connect_tree_id='" . $tree_id . "'
            AND connect_kind='address' AND connect_connect_id='" . safe_text_db($_POST["address_id"]) . "'";
        $result = $dbh->query($sql);

        // *** Delete connections to address, and re-order remaining address connections ***
        $connect_sql = "SELECT * FROM humo_connections
            WHERE connect_tree_id='" . $tree_id . "'
            AND connect_sub_kind='person_address'
            AND connect_item_id='" . safe_text_db($_POST["address_gedcomnr"]) . "'";
        $connect_qry = $dbh->query($connect_sql);
        while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
            // *** Delete source connections ***
            $sql = "DELETE FROM humo_connections WHERE connect_id='" . $connectDb->connect_id . "'";
            $result = $dbh->query($sql);

            // *** Re-order remaining source connections ***
            $event_order = 1;
            $event_sql = "SELECT * FROM humo_connections
                WHERE connect_tree_id='" . $tree_id . "'
                AND connect_kind='" . $connectDb->connect_kind . "'
                AND connect_sub_kind='" . $connectDb->connect_sub_kind . "'
                AND connect_connect_id='" . $connectDb->connect_connect_id . "'
                ORDER BY connect_order";
            $event_qry = $dbh->query($event_sql);
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_connections
                    SET connect_order='" . $event_order . "'
                    WHERE connect_id='" . $eventDb->connect_id . "'";
                $result = $dbh->query($sql);
                $event_order++;
            }
        }

        // *** Delete address ***
        $sql = "DELETE FROM humo_addresses
            WHERE address_id='" . safe_text_db($_POST["address_id"]) . "'";
        $result = $dbh->query($sql);

        echo __('Address has been removed!');
        echo '</div>';
    }


    // *****************
    // *** Addresses ***
    // *****************
    echo '<h1 class="center">' . __('Shared addresses') . '</h1>';
    echo __('These addresses can be connected to multiple persons, families and other items.');

    echo '<table class="humo standard" style="text-align:center;"><tr class="table_header_large"><td>';

    // *** Select family tree ***
    echo __('Family tree') . ': ';
    $editor_cls->select_tree($page);

    $address_id = '';
    echo ' <form method="POST" action="' . $phpself . '" style="display : inline;">';
    echo '<input type="hidden" name="page" value="' . $page . '">';

    $address_qry = $dbh->query("SELECT * FROM humo_addresses
        WHERE address_tree_id='" . $tree_id . "' AND address_shared='1'
        ORDER BY address_place, address_address");

    echo __('Select address') . ': ';
    echo '<select size="1" name="address_id" onChange="this.form.submit();" style="width: 200px">';
    echo '<option value="">' . __('Select address') . '</option>';
    while ($addressDb = $address_qry->fetch(PDO::FETCH_OBJ)) {
        $selected = '';
        if (isset($_POST['address_id'])) {
            if ($_POST['address_id'] == $addressDb->address_id) {
                $selected = ' selected';
                $address_id = $addressDb->address_id;
            }
        }
        echo '<option value="' . $addressDb->address_id . '"' . $selected . '>' .
            @$addressDb->address_place . ', ' . $addressDb->address_address;
        if ($addressDb->address_text) {
            echo ' ' . substr($addressDb->address_text, 0, 40);
            if (strlen($addressDb->address_text) > 40) echo '...';
        }
        echo ' [' . @$addressDb->address_gedcomnr . ']</option>' . "\n";
    }
    echo '</select>';

    echo ' ' . __('or') . ': ';
    echo '<input type="Submit" name="add_address" value="' . __('Add address') . '">';
    echo '</form>';

    echo '</td></tr></table><br>';

    // *** Show selected address ***
    //if ($address_id AND isset($_POST['address_id'])){
    if ($address_id or isset($_POST['add_address'])) {
        if (isset($_POST['add_address'])) {
            $address_gedcomnr = '';
            $address_address = '';
            $address_date = '';
            $address_zip = '';
            $address_place = '';
            $address_phone = '';
            $address_text = '';
            //$address_photo='';
            //$address_source='';
        } else {
            @$address_qry2 = $dbh->query("SELECT * FROM humo_addresses
                    WHERE address_tree_id='" . $tree_id . "' AND address_id='" . safe_text_db($_POST["address_id"]) . "'");

            $die_message = __('No valid address number.');
            try {
                @$addressDb = $address_qry2->fetch(PDO::FETCH_OBJ);
            } catch (PDOException $e) {
                echo $die_message;
            }
            $address_gedcomnr = $addressDb->address_gedcomnr;
            //OLD CODE
            //$_SESSION['admin_address_gedcomnumber']=$address_gedcomnr; // *** Used for source ***

            $address_address = $addressDb->address_address;
            $address_date = $addressDb->address_date;
            $address_zip = $addressDb->address_zip;
            $address_place = $addressDb->address_place;
            $address_phone = $addressDb->address_phone;
            $address_text = $addressDb->address_text;
            //$address_photo=$addressDb->address_photo;
            //$address_source=$addressDb->address_source;
        }

        echo '<form method="POST" action="' . $phpself . '">';
        echo '<input type="hidden" name="page" value="' . $page . '">';
        echo '<input type="hidden" name="address_id" value="' . $_POST['address_id'] . '">';
        echo '<input type="hidden" name="address_gedcomnr" value="' . $address_gedcomnr . '">';

        echo '<table class="humo standard" border="1">';
        echo '<tr class="table_header"><th>' . __('Option') . '</th><th colspan="2">' . __('Value') . '</th></tr>';

        //echo '<tr><td>';
        //echo ucfirst(__('date')).' - '.__('place').'</td><td>'.$editor_cls->date_show($address_date,"address_date");
        echo '<tr><td>' . __('Place') . '</td><td>';
        echo '<input type="text" name="address_place" value="' . htmlspecialchars($address_place) . '" size="50"></td></tr>';

        echo '<tr><td>' . __('Street') . '</td><td><input type="text" name="address_address" value="' . htmlspecialchars($address_address) . '" size="60" required></td></tr>';

        echo '<tr><td>' . __('Zip code') . '</td><td><input type="text" name="address_zip" value="' . $address_zip . '" size="60"></td></tr>';

        echo '<tr><td>' . __('Phone') . '</td><td><input type="text" name="address_phone" value="' . $address_phone . '" size="60"></td></tr>';

        //echo '<tr><td>'.__('Picture').'</td><td><input type="text" name="address_photo" value="'.$address_photo.'" size="60"></td></tr>';

        // *** Source by address ***
        echo '<tr><td>' . ucfirst(__('source')) . '</td><td>';
        if (isset($addressDb->address_id)) {
            echo source_link2('20', $addressDb->address_gedcomnr, 'address_source', 'addresses');
        }
        echo '</td></tr>';
        // *** Show source by address ***
        if (isset($addressDb->address_gedcomnr)) {
            //edit_sources($hideshow,$connect_kind,$connect_sub_kind,$connect_connect_id)
            echo edit_sources('20', 'address', 'address_source', $addressDb->address_gedcomnr);
        }

        echo '<tr><td>' . ucfirst(__('text')) . '</td><td><textarea rows="1" name="address_text" ' . $field_text_large . '>' .
            $editor_cls->text_show($address_text) . '</textarea></td></tr>';

        if (isset($_POST['add_address'])) {
            echo '<tr><td>' . __('Add') . '</td><td><input type="Submit" name="address_add" value="' . __('Add') . '"></td></tr>';
        } else {
            echo '<tr><td>' . __('Save') . '</td><td><input type="Submit" name="address_change" value="' . __('Save') . '">';
            echo ' ' . __('or') . ' ';
            echo '<input type="Submit" name="address_remove" value="' . __('Delete') . '">';
            echo '</td></tr>';
        }

        echo '</table>' . "\n";
        echo '</form>';

        // *** Example in IFRAME ***
        if (!isset($_POST['add_address'])) {
            if ($humo_option["url_rewrite"] == "j") {
                $url='../address/' . $tree_id . '/' . $addressDb->address_gedcomnr;
            } else {
                $url='../address.php?tree_id=' . $tree_id . '&amp;id=' . $addressDb->address_gedcomnr;
            }
        
            echo '<p>' . __('Preview') . '<br>';
            //echo '<iframe src ="' . $addresstring . 'tree_id=' . $tree_id . '&gedcomnumber=' . $addressDb->address_gedcomnr . '" class="iframe">';
            //echo '<iframe src ="../address/' . $tree_id . '/' . $addressDb->address_gedcomnr . '" class="iframe">';
            echo '<iframe src ="'.$url.'" class="iframe">';
            echo '  <p>Your browser does not support iframes.</p>';
            echo '</iframe>';
        }
    }
}


// *****************
// *** FUNCTIONS ***
// *****************

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
    <iframe id="source_iframe" class="source_iframe" title="source_iframe" src="index.php?page=editor_sources';
    if ($connect_kind) $text .= '&connect_kind=' . $connect_kind;
    $text .= '&connect_sub_kind=' . $connect_sub_kind;
    if ($connect_connect_id) $text .= '&connect_connect_id=' . $connect_connect_id;
    $text .= '">
    </iframe>
    </td></tr>';
    return $text;
}

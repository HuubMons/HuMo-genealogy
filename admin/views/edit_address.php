<?php

/**
 * Edit or add an (shared) address.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}



// TODO create seperate controller script.
include_once(__DIR__ . "/../include/editor_cls.php");
$editor_cls = new editor_cls;

require_once  __DIR__ . "/../models/edit_address.php";
$editAddressModel = new EditAddressModel($dbh);
$editAddressModel->set_address_id();
$editAddressModel->update_address($dbh, $tree_id, $db_functions, $editor_cls);
$editAddress['address_id'] = $editAddressModel->get_address_id();



$phpself = 'index.php';
$field_text_large = 'style="height: 100px; width:550px"';

include(__DIR__ . '/../include/editor_event_cls.php');
$event_cls = new editor_event_cls;

// *** Editor icon for admin and editor: select family tree ***
if (isset($tree_id) and $tree_id) {
    $db_functions->set_tree_id($tree_id);
}

$address_qry = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_shared='1' ORDER BY address_place, address_address");
?>

<h1 class="center"><?= __('Shared addresses'); ?></h1>
<?= __('These addresses can be connected to multiple persons, families and other items.'); ?>

<?php if (isset($_POST['address_remove'])) { ?>
    <div class="alert alert-danger">
        <strong><?= __('Are you sure you want to remove this address and ALL address references?'); ?></strong>
        <form method="post" action="<?= $phpself; ?>" style="display : inline;">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="address_id" value="<?= $editAddress['address_id']; ?>">
            <input type="hidden" name="address_gedcomnr" value="<?= $_POST['address_gedcomnr']; ?>">
            <input type="submit" name="address_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
            <input type="submit" name="dummy7" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
        </form>
    </div>
<?php }; ?>

<?php if (isset($_POST['address_remove2'])) { ?>
    <div class="alert alert-success">
        <strong><?= __('Address has been removed!'); ?></strong>
    </div>
<?php }; ?>

<form method="POST" action="<?= $phpself; ?>" style="display : inline;">
    <input type="hidden" name="page" value="<?= $page; ?>">

    <div class="p-3 m-2 genealogy_search">
        <div class="row">
            <div class="col-auto">
                <label for="tree" class="col-form-label">
                    <?= __('Family tree'); ?>:
                </label>
            </div>

            <div class="col-auto">
                <?= $editor_cls->select_tree($page); ?>
            </div>

            <div class="col-auto">
                <label for="address" class="col-form-label">
                    <?= __('Select address'); ?>:
                </label>
            </div>

            <div class="col-auto">
                <select size="1" name="address_id" class="form-select form-select-sm" onChange="this.form.submit();" style="width: 200px">
                    <option value=""><?= __('Select address'); ?></option>
                    <?php
                    while ($addressDb = $address_qry->fetch(PDO::FETCH_OBJ)) {
                        $selected = '';
                        if ($editAddress['address_id'] == $addressDb->address_id) {
                            $selected = ' selected';
                        }
                    ?>
                        <option value="<?= $addressDb->address_id; ?>" <?= $selected; ?>>
                            <?= $addressDb->address_place; ?>, <?= $addressDb->address_address; ?>
                            <?php
                            if ($addressDb->address_text) {
                                echo ' ' . substr($addressDb->address_text, 0, 40);
                                if (strlen($addressDb->address_text) > 40) echo '...';
                            }
                            ?>
                            [<?= $addressDb->address_gedcomnr; ?>]
                        </option>
                    <?php
                    }
                    ?>
                </select>
            </div>

            <div class="col-auto">
                <?= __('or'); ?>:
                <input type="submit" name="add_address" value="<?= __('Add address'); ?>" class="btn btn-sm btn-secondary">
            </div>
        </div>
    </div>
</form>
<?php

// *** Show selected address ***
if ($editAddress['address_id'] or isset($_POST['add_address'])) {
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
        @$address_qry2 = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_id='" . $editAddress['address_id'] . "'");
        $die_message = __('No valid address number.');
        try {
            @$addressDb = $address_qry2->fetch(PDO::FETCH_OBJ);
        } catch (PDOException $e) {
            echo $die_message;
        }
        $address_gedcomnr = $addressDb->address_gedcomnr;

        $address_address = $addressDb->address_address;
        $address_date = $addressDb->address_date;
        $address_zip = $addressDb->address_zip;
        $address_place = $addressDb->address_place;
        $address_phone = $addressDb->address_phone;
        $address_text = $addressDb->address_text;
        //$address_photo=$addressDb->address_photo;
        //$address_source=$addressDb->address_source;
    }

?>
    <form method="POST" action="<?= $phpself; ?>">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <input type="hidden" name="address_id" value="<?= $editAddress['address_id']; ?>">
        <input type="hidden" name="address_gedcomnr" value="<?= $address_gedcomnr; ?>">
        <?php
        echo '<table class="humo standard" border="1">';
        echo '<tr class="table_header"><th>' . __('Option') . '</th><th colspan="2">' . __('Value') . '</th></tr>';

        //$editor_cls->date_show($address_date,"address_date");
        echo '<tr><td>' . __('Place') . '</td><td>';
        echo '<input type="text" name="address_place" value="' . htmlspecialchars($address_place) . '" size="50"></td></tr>';

        echo '<tr><td>' . __('Street') . '</td><td><input type="text" name="address_address" value="' . htmlspecialchars($address_address) . '" size="60" required></td></tr>';

        echo '<tr><td>' . __('Zip code') . '</td><td><input type="text" name="address_zip" value="' . $address_zip . '" size="60"></td></tr>';

        echo '<tr><td>' . __('Phone') . '</td><td><input type="text" name="address_phone" value="' . $address_phone . '" size="60"></td></tr>';

        //echo '<tr><td>'.__('Picture').'</td><td><input type="text" name="address_photo" value="'.$address_photo.'" size="60"></td></tr>';

        // TODO Check, doesn't work anymore?
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
            echo '<tr><td>' . __('Add') . '</td><td><input type="submit" name="address_add" value="' . __('Add') . '"></td></tr>';
        } else {
            echo '<tr><td>' . __('Save') . '</td><td><input type="submit" name="address_change" value="' . __('Save') . '">';
            echo ' ' . __('or') . ' ';
            echo '<input type="submit" name="address_remove" value="' . __('Delete') . '">';
            echo '</td></tr>';
        }

        echo '</table>' . "\n";
        ?>
    </form>
<?php

    // *** Example in IFRAME ***
    if (!isset($_POST['add_address'])) {
        if ($humo_option["url_rewrite"] == "j") {
            $url = '../address/' . $tree_id . '/' . $addressDb->address_gedcomnr;
        } else {
            $url = '../address.php?tree_id=' . $tree_id . '&amp;id=' . $addressDb->address_gedcomnr;
        }

        echo '<p>' . __('Preview') . '<br>';
        echo '<iframe src ="' . $url . '" class="iframe">';
        echo '  <p>Your browser does not support iframes.</p>';
        echo '</iframe>';
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
    //src="index.php?page=editor_sources&'.$event_group.'&connect_kind='.$connect_kind.'&connect_sub_kind='.$connect_sub_kind.'&connect_connect_id='.$connect_connect_id.'">
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

<?php

/**
 * Edit or add an (shared) address.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$field_text_large = 'style="height: 100px; width:550px"';

$EditorEvent = new EditorEvent;

// *** Editor icon for admin and editor: select family tree ***
if (isset($tree_id) && $tree_id) {
    $db_functions->set_tree_id($tree_id);
}

$address_qry = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_shared='1' ORDER BY address_place, address_address");
?>

<h1 class="center"><?= __('Shared addresses'); ?></h1>
<?= __('These addresses can be connected to multiple persons, families and other items.'); ?>

<?php if (isset($_POST['address_remove'])) { ?>
    <div class="alert alert-danger">
        <strong><?= __('Are you sure you want to remove this address and ALL address references?'); ?></strong>
        <form method="post" action="index.php?page=edit_addresses" style="display : inline;">
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

<form method="POST" action="index.php?page=edit_addresses" style="display : inline;">
    <div class="p-3 my-md-2 genealogy_search container-md">
        <div class="row">
            <div class="col-md-3">
                <?= select_tree($dbh, $page, $tree_id); ?>
            </div>

            <div class="col-md-auto">
                <label for="address" class="col-form-label">
                    <?= __('Select address'); ?>:
                </label>
            </div>
            <div class="col-md-3">
                <select size="1" name="address_id" class="form-select form-select-sm" onChange="this.form.submit();">
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
                                if (strlen($addressDb->address_text) > 40) {
                                    echo '...';
                                }
                            }
                            ?>
                            [<?= $addressDb->address_gedcomnr; ?>]
                        </option>
                    <?php } ?>
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
if ($editAddress['address_id']) {
    $address_qry2 = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_id='" . $editAddress['address_id'] . "'");
    $die_message = __('No valid address number.');
    try {
        $addressDb = $address_qry2->fetch(PDO::FETCH_OBJ);
    } catch (PDOException $e) {
        echo $die_message;
    }
}

if (isset($addressDb->address_id) || isset($_POST['add_address'])) {
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

    <form method="POST" action="index.php?page=edit_addresses">
        <input type="hidden" name="address_id" value="<?= $editAddress['address_id']; ?>">
        <input type="hidden" name="address_gedcomnr" value="<?= $address_gedcomnr; ?>">
        <div class="p-2 my-md-2 genealogy_search container-md">
            <div class="row mb-2">
                <div class="col-md-1"></div>

                <!-- date -->

                <div class="col-md-2">
                    <?= __('Place'); ?>
                </div>
                <div class="col-md-4">
                    <input type="text" name="address_place" value="<?= htmlspecialchars($address_place); ?>" size="50" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2">
                    <?= __('Street'); ?>
                </div>
                <div class="col-md-4">
                    <input type="text" name="address_address" value="<?= htmlspecialchars($address_address); ?>" size="60" required class="form-control form-control-sm"></td>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2">
                    <?= __('Zip code'); ?>
                </div>
                <div class="col-md-4">
                    <input type="text" name="address_zip" value="<?= $address_zip; ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2">
                    <?= __('Phone'); ?>
                </div>
                <div class="col-md-4">
                    <input type="text" name="address_phone" value="<?= $address_phone; ?>" size="60" class="form-control form-control-sm">
                </div>
            </div>

            <!-- <tr><td>'.__('Picture').'</td><td><input type="text" name="address_photo" value="'.$address_photo.'" size="60" class="form-control form-control-sm"></td></tr>'; -->

            <!-- Source by address -->
            <?php if (!isset($_POST['add_address'])) { ?>
                <div class="row mb-2">
                    <div class="col-md-1"></div>
                    <div class="col-md-2">
                        <?= __('Source'); ?>
                    </div>
                    <div class="col-md-4">
                        <!-- Button trigger modal for sources -->
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-toggle="modal" data-bs-target="#sourceModal">
                            <?= __('Source'); ?>
                        </button>

                        <!-- Modal, same code as found in editor.php  -->
                        <div class="modal fade" id="sourceModal" tabindex="-1" aria-labelledby="sourceModalLabel" aria-hidden="true">
                            <div class="modal-dialog modal-xl">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5" id="sourceModalLabel"><?= __('Source'); ?></h1>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body">
                                        <!-- Show source by address -->
                                        <?php if (isset($addressDb->address_gedcomnr)) { ?>
                                            <iframe id="source_iframe" style="width:800px;height:800px;" title="source_iframe" src="index.php?page=editor_sources&connect_kind=address&connect_sub_kind=address_source&connect_connect_id=<?= $addressDb->address_gedcomnr; ?>" style="width:750px;height:400px;"></iframe>
                                        <?php }   ?>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal"><?= __('Close'); ?></button>
                                        <!-- <button type="button" class="btn btn-sm btn-primary">Save changes</button> -->
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php
                        // *** Show number of sources, and indication of properly connected sources ***
                        $connect_qry = "SELECT connect_connect_id, connect_source_id FROM humo_connections
                            WHERE connect_tree_id='" . $tree_id . "'
                            AND connect_sub_kind='address_source' AND connect_connect_id='" . $addressDb->address_gedcomnr . "'";
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
                                if (!$sourceDb->source_title && !$sourceDb->source_text && !$sourceDb->source_date && !$sourceDb->source_place && !$sourceDb->source_refn) {
                                    $source_error = 2;
                                    $style_source = '';
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
                        ?>
                        <span <?= $style; ?>">[<?= $source_count; ?>]</span>

                    </div>
                </div>
            <?php } ?>

            <div class="row mb-2">
                <div class="col-md-1"></div>
                <div class="col-md-2">
                    <?= __('Text'); ?>
                </div>
                <div class="col-md-4">
                    <textarea rows="1" name="address_text" <?= $field_text_large; ?> class="form-control form-control-sm"><?= $editAddress['editor_cls']->text_show($address_text); ?></textarea>
                </div>
            </div>

            <?php if (isset($_POST['add_address'])) { ?>
                <div class="row mb-2">
                    <div class="col-md-1"></div>
                    <div class="col-md-2">
                        <?= __('Add'); ?>
                    </div>
                    <div class="col-md-4">
                        <input type="submit" name="address_add" value="<?= __('Add'); ?>" class="btn btn-sm btn-success">
                    </div>
                </div>
            <?php } else { ?>
                <div class="row mb-2">
                    <div class="col-md-1"></div>
                    <div class="col-md-2">
                        <?= __('Save'); ?>
                    </div>
                    <div class="col-md-4">
                        <input type="submit" name="address_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">
                        <?= __('or'); ?>
                        <input type="submit" name="address_remove" value="<?= __('Delete'); ?>" class="btn btn-sm btn-secondary">
                    </div>
                </div>
            <?php } ?>
        </div>
        </div>
    </form>

    <?php
    // *** Example of address in IFRAME ***
    if (!isset($_POST['add_address'])) {
        if ($humo_option["url_rewrite"] == "j") {
            $url = '../address/' . $tree_id . '/' . $addressDb->address_gedcomnr;
        } else {
            $url = '../index.php?page=address&amp;tree_id=' . $tree_id . '&amp;id=' . $addressDb->address_gedcomnr;
        }
    ?>
        <br><?= __('Preview'); ?><br>
        <iframe src="<?= $url; ?>" class="iframe">
            <p>Your browser does not support iframes.</p>
        </iframe>
<?php
    }
}

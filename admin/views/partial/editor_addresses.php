<?php

/**
 * Show and edit addresses/residences by person
 * 2021: New script.
 * 2025: Moved script to partial folder.
 */
?>

<tr id="addresses">
    <td style="border-right:0px;"><b><?= __('Addresses'); ?></b></td>
    <td colspan="2">
        <input type="submit" name="<?= $connect_kind == 'person' ? 'person_add_address' : 'relation_add_address'; ?>" value="<?= __('Add'); ?>" class="btn btn-sm btn-outline-primary">

        <!-- Help popover for address -->
        <button type="button" class="btn btn-sm btn-secondary"
            data-bs-toggle="popover" data-bs-placement="right" data-bs-custom-class="popover-wide" data-bs-html="true"
            data-bs-content="<?= __('A shared address can be connected to multiple persons or relations.'); ?><br>
                <?= __('A shared address is only supported by the Haza-data and HuMo-genealogy programs.'); ?>">
            ?
        </button>
    </td>
</tr>

<?php
$connect_qry = $dbh->prepare("SELECT * FROM humo_connections
    WHERE connect_tree_id = :tree_id
    AND connect_sub_kind = :connect_sub_kind
    AND connect_connect_id = :connect_connect_id
    ORDER BY connect_order");
$connect_qry->execute([
    ':tree_id' => $tree_id,
    ':connect_sub_kind' => $connect_sub_kind,
    ':connect_connect_id' => $connect_connect_id
]);
$count = $connect_qry->rowCount();
$address_nr = 0;

if ($count > 0) {
?>
    <tr>
        <td></td>
        <td colspan="2">
            <!-- create unique sortable id -->
            <?php $sortable_id = $connect_sub_kind . $connect_connect_id; ?>
            <ul id="sortable_addresses<?= $HtmlEscaper->escHtml((string) $sortable_id); ?>" class="sortable_addresses list-group" data-sortable-id="<?= $HtmlEscaper->escHtml((string) $sortable_id); ?>">
                <?php
                while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
                    $address_nr++;
                    $key = $connectDb->connect_id;

                    // *** Check order number, restore if number is wrong (because of problems in earlier versions) ***
                    if ($connectDb->connect_order != $address_nr) {
                        $connectDb->connect_order = $address_nr;
                        $sql = "UPDATE humo_connections SET connect_order='" . $address_nr . "' WHERE connect_id='" . $connectDb->connect_id . "'";
                        $dbh->query($sql);
                    }

                    // *** Show addresses by person or relation ***
                    $address3_qry = $dbh->query("SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_gedcomnr='" . $connectDb->connect_item_id . "'");
                    $address3Db = $address3_qry->fetch(PDO::FETCH_OBJ);
                    if ($address3Db) {
                        // *** Use hideshow to show and hide the editor lines ***
                        $hideshow = '8000' . $address3Db->address_id;
                        // *** If address AND place are missing show all editor fields ***
                        $display = ' display:none;';
                        if ($address3Db->address_address == '' && $address3Db->address_place == '') {
                            $display = '';
                        }

                        $address = $address3Db->address_address . ' ' . $address3Db->address_place;
                        if ($address3Db->address_address == '' && $address3Db->address_place == '') {
                            $address = __('EMPTY LINE');
                        }

                        // *** Also show date and place ***
                        if ($connectDb->connect_date) {
                            $address .= ', ' . hideshow_date_place($connectDb->connect_date, '');
                        }
                        $address = $HtmlEscaper->escHtml((string) $address);

                        if ($connectDb->connect_id) {
                            if ($connect_kind == 'person') {
                                $connect_kind = 'person';
                                $connect_sub_kind_source = 'pers_address_connect_source';
                            } else {
                                $connect_kind = 'family';
                                $connect_sub_kind_source = 'fam_address_connect_source';
                            }
                        }
                        $check_sources_text = check_sources($connect_kind, $connect_sub_kind_source, $connectDb->connect_id);

                        // TODO check code.
                        if ($connect_kind == 'person') {
                            $form = 1;
                        } else {
                            $form = 2;
                        }

                        $connect_role = '';
                        if (isset($connectDb->connect_role)) {
                            $connect_role = htmlspecialchars($connectDb->connect_role);
                        }
                    }
                ?>

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1">
                                <input type="hidden" name="connect_change[<?= $HtmlEscaper->escHtml((string) $key); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>">
                                <input type="hidden" name="connect_connect_id[<?= $HtmlEscaper->escHtml((string) $key); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_connect_id); ?>">
                                <input type="hidden" name="connect_kind[<?= $HtmlEscaper->escHtml((string) $key); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connect_kind); ?>">
                                <input type="hidden" name="connect_sub_kind[<?= $HtmlEscaper->escHtml((string) $key); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connect_sub_kind); ?>">
                                <input type="hidden" name="connect_page[<?= $HtmlEscaper->escHtml((string) $key); ?>]" value="">
                                <input type="hidden" name="connect_place[<?= $HtmlEscaper->escHtml((string) $key); ?>]" value="">

                                <!-- Send old values, so changes of values can be detected -->
                                <input type="hidden" name="connect_date_old[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_date); ?>">
                                <input type="hidden" name="connect_role_old[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_role); ?>">
                                <input type="hidden" name="connect_text_old[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_text); ?>">

                                <?php if ($count > 1) { ?>
                                    <span style="cursor:move;" id="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>" class="handle me-2">
                                        <img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>">
                                    </span>
                                <?php } else { ?>
                                    <span class="me-2">&nbsp;&nbsp;&nbsp;</span>
                                <?php } ?>

                                <!-- Remove address -->
                                <a href="index.php?page=<?= $HtmlEscaper->escHtml((string) $page); ?>&amp;person_place_address=1&amp;connect_drop=<?= rawurlencode((string) $connectDb->connect_id); ?>">
                                    <img src="images/button_drop.png" border="0" alt="drop">
                                </a>
                            </div>

                            <div class="col-md-11">
                                <?php if ($address3Db) { ?>
                                    <span class="hideshowlink" onclick="hideShow(<?= $hideshow; ?>);"><?= $address; ?>
                                        <?php if ($address3Db->address_text || $connectDb->connect_text) { ?>
                                            <img src="images/text.png" height="16" alt="<?= __('text'); ?>">
                                        <?php } ?>
                                        <?= $check_sources_text; ?>
                                    </span>

                                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;<?= $display; ?>"><br>
                                        <input type="hidden" name="change_address_id[<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>">

                                        <!-- Send old values, so changes of values can be detected -->
                                        <input type="hidden" name="address_shared_old[<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_shared); ?>">
                                        <input type="hidden" name="address_address_old[<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_address); ?>">
                                        <input type="hidden" name="address_place_old[<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_place); ?>">
                                        <input type="hidden" name="address_text_old[<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_text); ?>">
                                        <input type="hidden" name="address_phone_old[<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_phone); ?>">
                                        <input type="hidden" name="address_zip_old[<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_zip); ?>">

                                        <input type="hidden" name="connect_item_id_old[<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connectDb->connect_item_id); ?>">

                                        <?= __('Address GEDCOM number:'); ?> <?= $HtmlEscaper->escHtml((string) $address3Db->address_gedcomnr); ?>&nbsp;&nbsp;&nbsp;&nbsp;

                                        <!-- Shared address, to connect address to multiple persons or relations -->
                                        <input type="checkbox" name="address_shared_<?= $address3Db->address_id; ?>" value="no_data" class="form-check-input" <?= $address3Db->address_shared ? 'checked' : ''; ?>> <?= __('Shared address'); ?><br>

                                        <?php
                                        // *** Don't use date here. Date of connection table will be used ***
                                        //echo $editor_cls->date_show($address3Db->address_date,'address_date',"[$address3Db->address_id]").' ';
                                        ?>

                                        <div class="row my-2">
                                            <label for="address_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                                            <div class="col-md-7">
                                                <div class="input-group">
                                                    <input type="text" name="address_place_<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>" id="address_place_<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_place); ?>" placeholder="<?= __('Start typing to search for a place.'); ?>" size="<?= $HtmlEscaper->escHtml((string) $field_place); ?>" class="place-autocomplete form-control form-control-sm">
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
                                                <input type="text" name="address_address_<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_address); ?>" class="form-control form-control-sm">
                                            </div>
                                        </div>

                                        <!-- Edit Zip code -->
                                        <div class="row mb-2">
                                            <label for="address_zip" class="col-md-3 col-form-label"><?= __('Zip code'); ?></label>
                                            <div class="col-md-3">
                                                <input type="text" name="address_zip_<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_zip); ?>" class="form-control form-control-sm">
                                            </div>
                                        </div>

                                        <!-- Edit phone -->
                                        <div class="row mb-2">
                                            <label for="address_phone" class="col-md-3 col-form-label"><?= __('Phone'); ?></label>
                                            <div class="col-md-3">
                                                <input type="text" name="address_phone_<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>" value="<?= $HtmlEscaper->escHtml((string) $address3Db->address_phone); ?>" class="form-control form-control-sm">
                                            </div>
                                        </div>

                                        <!-- Edit text -->
                                        <div class="row mb-2">
                                            <label for="address_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                                            <div class="col-md-7">
                                                <textarea rows="1" name="address_text_<?= $HtmlEscaper->escHtml((string) $address3Db->address_id); ?>" <?= $HtmlEscaper->escHtml((string) $field_text); ?> class="form-control form-control-sm"><?= $editor_cls->text_show($address3Db->address_text); ?></textarea>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="pers_buried_place" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                                            <div class="col-md-7">
                                                <?= $editor_cls->date_show($connectDb->connect_date, 'connect_date', "[$connectDb->connect_id]"); ?>
                                            </div>
                                        </div>

                                        <!-- Highlight section for shared addresses only -->
                                        <div class="alert alert-info p-3 mb-3" role="alert">
                                            <small><?= __('Only use the following fields for shared addresses.'); ?></small>

                                            <div class="row my-2">
                                                <label for="pers_buried_place" class="col-md-3 col-form-label"><?= __('Addressrole'); ?></label>
                                                <div class="col-md-3">
                                                    <input type="text" name="connect_role[<?= $HtmlEscaper->escHtml((string) $key); ?>]" value="<?= $HtmlEscaper->escHtml((string) $connect_role); ?>" size="6" class="form-control form-control-sm">
                                                </div>
                                            </div>

                                            <!-- Extra text by address -->
                                            <div class="row mb-2">
                                                <label for="pers_buried_place" class="col-md-3 col-form-label"><?= __('Extra text by address'); ?></label>
                                                <div class="col-md-7">
                                                    <textarea name="connect_text[<?= $HtmlEscaper->escHtml((string) $connectDb->connect_id); ?>]" <?= $HtmlEscaper->escHtml((string) $field_text); ?> class="form-control form-control-sm"><?= $editor_cls->text_show($connectDb->connect_text); ?></textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row mb-2">
                                            <label for="source_text" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                            <div class="col-md-7">
                                                <?php
                                                source_link3($connect_kind, $connect_sub_kind_source, $connectDb->connect_id);
                                                echo $check_sources_text;
                                                ?>
                                            </div>
                                        </div>
                                        <!-- Use hideshow to show and hide the editor lines -->
                                        <?php if (isset($hideshow) && substr($hideshow, 0, 4) === '8000') { ?>
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
                                <input type="hidden" name="connect_text[<?= $key; ?>]" value="">

                                <div class="row">
                                    <div class="col-md-auto mt-1">
                                        <?= __('Address'); ?>
                                    </div>
                                    <div class="col-md-auto">
                                        <select size="1" name="connect_item_id[<?= $key; ?>]" style="width: 300px" class="form-select form-select-sm">
                                            <option value=""><?= __('Select address'); ?></option>
                                            <!-- Only shared addresses (at this moment) -->
                                            <?php while ($address2Db = $addressqry->fetch(PDO::FETCH_OBJ)) { ?>
                                                <option value="<?= $address2Db->address_gedcomnr; ?>" <?= $connectDb->connect_item_id == $address2Db->address_gedcomnr ? 'selected' : ''; ?>>
                                                    <?= $address2Db->address_place; ?>, <?= $address2Db->address_address; ?>
                                                    <?php
                                                    if ($address2Db->address_text) {
                                                        echo ' ' . substr($address2Db->address_text, 0, 40);
                                                        if (strlen($address2Db->address_text) > 40) {
                                                            echo '...';
                                                        }
                                                    }
                                                    ?>
                                                    [<?= $address2Db->address_gedcomnr; ?>]
                                                </option>
                                            <?php } ?>
                                        </select>
                                    </div>

                                    <div class="col-md-auto mt-1">
                                        <?= __('Or: add new address'); ?>
                                    </div>
                                    <div class="col-md-auto mt-1">
                                        <a href="index.php?page=<?= $HtmlEscaper->escHtml((string) $page); ?><?= $connect_kind == 'person' ? '&amp;person_place_address=1' : '&amp;family_place_address=1'; ?>&amp;address_add2=1&amp;connect_id=<?= rawurlencode((string) $connectDb->connect_id); ?>&amp;connect_kind=<?= rawurlencode((string) $connectDb->connect_kind); ?>&amp;connect_sub_kind=<?= rawurlencode((string) $connectDb->connect_sub_kind); ?>&amp;connect_connect_id=<?= rawurlencode((string) $connectDb->connect_connect_id); ?>#addresses">
                                            [<?= __('Add'); ?>]
                                        </a>
                                    </div>
                                </div>

                            <?php } ?>
                            </div>
                        </div>
                    </li>

                <?php } ?>
            </ul>

            <!-- Order items using drag and drop using jquery and jqueryui, only used if there are multiple addresses -->
            <?php if ($count > 1) { ?>
                <script src="../assets/js/order_addresses.js"></script>
            <?php } ?>

        </td>
    </tr>
<?php } ?>

<?php
// *** Show places or addresses if save or arrow links are used ***
if (isset($_GET['person_place_address']) || isset($_GET['family_place_address'])) {
    // *** Script voor expand and collapse of items ***
    //if (isset($_GET['pers_place'])) $link_id='54';
    if (isset($_GET['person_place_address']) || isset($_GET['family_place_address'])) {
        $link_id = '55';
    }
    echo '<script>
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

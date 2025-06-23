<?php

/**
 * Show and edit addresses/residences by person
 * 2021: New script.
 * 2025: Moved script to partial folder.
 */
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
        ?>

        <!-- Help popup for address -->
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
if ($count > 0) {
?>
    <tr>
        <td></td>
        <td colspan="2">
            <!-- create unique sortable id -->
            <?php $sortable_id = $connect_sub_kind . $connect_connect_id; ?>
            <ul id="sortable_addresses<?= $sortable_id; ?>" class="sortable_addresses<?= $sortable_id; ?> list-group">
                <?php
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

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1">
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

                                <?php if ($count > 1) { ?>
                                    <span style="cursor:move;" id="<?= $addressDb->connect_id; ?>" class="handle me-2">
                                        <img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>">
                                    </span>
                                <?php } else { ?>
                                    <span class="me-2">&nbsp;&nbsp;&nbsp;</span>
                                <?php } ?>

                                <!-- Remove address -->
                                <a href="index.php?page=<?= $page; ?>&amp;person_place_address=1&amp;connect_drop=<?= $addressDb->connect_id; ?>">
                                    <img src="images/button_drop.png" border="0" alt="drop">
                                </a>
                            </div>

                            <div class="col-md-11">
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

                                <?php
                                if ($address3Db) {
                                    $address = $address3Db->address_address . ' ' . $address3Db->address_place;
                                    if ($address3Db->address_address == '' && $address3Db->address_place == '') {
                                        $address = __('EMPTY LINE');
                                    }

                                    // *** Also show date and place ***
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
                                    <!-- Only shared addresses (at this moment) -->
                                    <?php while ($address2Db = $addressqry->fetch(PDO::FETCH_OBJ)) { ?>
                                        <option value="<?= $address2Db->address_gedcomnr; ?>" <?= $addressDb->connect_item_id == $address2Db->address_gedcomnr ? 'selected' : ''; ?>>
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

                                <?= __('Or: add new address'); ?>
                                <a href="index.php?page=<?= $page; ?><?= $connect_kind == 'person' ? '&amp;person_place_address=1' : '&amp;family_place_address=1'; ?>&amp;address_add2=1&amp;connect_id=<?= $addressDb->connect_id; ?>&amp;connect_kind=<?= $addressDb->connect_kind; ?>&amp;connect_sub_kind=<?= $addressDb->connect_sub_kind; ?>&amp;connect_connect_id=<?= $addressDb->connect_connect_id; ?>#addresses">
                                    [<?= __('Add'); ?>]
                                </a>

                            <?php } ?>
                            </div>
                        </div>
                    </li>

                <?php } ?>
            </ul>

            <!-- Order items using drag and drop using jquery and jqueryui, only used if there are multiple events -->
            <?php if ($count > 1) { ?>
                <script>
                    $('#sortable_addresses<?= $sortable_id; ?>').sortable({
                        handle: '.handle'
                    }).bind('sortupdate', function() {
                        var orderstring = "";
                        var order_arr = document.getElementsByClassName("handle");
                        for (var z = 0; z < order_arr.length; z++) {
                            orderstring = orderstring + order_arr[z].id + ";";
                            //document.getElementById('ordernum' + order_arr[z].id).innerHTML = (z + 1);
                        }

                        orderstring = orderstring.substring(0, orderstring.length - 1);
                        $.ajax({
                            url: "include/drag.php?drag_kind=addresses&order=" + orderstring,
                            success: function(data) {},
                            error: function(xhr, ajaxOptions, thrownError) {
                                alert(xhr.status);
                                alert(thrownError);
                            }
                        });
                    });
                </script>
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

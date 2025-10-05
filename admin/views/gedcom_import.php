<?php

/**
 * This is the GEDCOM processing file for HuMo-genealogy.
 *
 * If you are reading this in your web browser, your server is probably
 * not configured correctly to run PHP applications!
 *
 * See the manual for basic setup instructions
 *
 * https://humo-gen.com
 *
 * Copyright (C) 2008-2025 Huub Mons,
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

<?php if ($trees['step'] == '1') { ?>
    <b><?= __('STEP 1) Select GEDCOM file:'); ?></b>

    <div class="p-3 mt-2 me-sm-2 genealogy_search">

        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-7"><?= __('Add and remove GEDCOM files'); ?></div>
        </div>

        <div class="row mb-12">
            <div class="col-md-auto">
                <?php if ($trees['upload_success']) { ?>
                    <div class="alert alert-success" role="alert"><b><?= $trees['upload_success']; ?></b></div>
                <?php } ?>

                <?php if ($trees['upload_failed']) { ?>
                    <div class="alert alert-danger" role="alert"><b><?= $trees['upload_failed']; ?></b></div>
                <?php } ?>

                <!-- Upload form -->
                <form name='uploadform' enctype='multipart/form-data' action="index.php?page=tree&amp;menu_admin=tree_gedcom" method="post">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="hidden" name="upload" value="Upload">
                    <div class="row mt-2">
                        <div class="col-md-8">
                            <div class="input-group">
                                <input type="file" name="upload_file" class="form-control form-control-sm">
                                <input type="submit" name="submit" value="<?= __('Upload'); ?>" class="btn btn-sm btn-success">
                            </div>
                        </div>
                    </div>
                </form><br>
                <?= __('Here you can upload a GEDCOM file (for example: gedcom_name.ged or gedcom_name.zip).'); ?><br>
                <?= __('ATTENTION: the privileges of the file map may have to be adjusted!'); ?><br>
                <?= __('Another option is to upload GEDCOM files manually by using FTP to folder: /humo-gen/admin/gedcom_files/'); ?><br><br>
            </div>
        </div>

        <div class="row mb-12">
            <div class="col-md-auto">
                <!-- Form to remove GEDCOM files -->
                <?php if (isset($_POST['remove_gedcom_files'])) { ?>
                    <div class="alert alert-warning" role="alert">
                        <?= __('Are you sure to remove GEDCOM files?'); ?>
                        <form name="remove_gedcomfiles" action="index.php?page=tree&amp;menu_admin=tree_gedcom" method="post">
                            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                            <input type="hidden" name="remove_gedcom_files2" value="<?= $_POST['remove_gedcom_files']; ?>">
                            <input type="submit" name="remove_confirm" value="<?= __('Yes'); ?>" class="btn btn-sm btn-danger">
                            <input type="submit" name="submit" value="<?= __('No'); ?>" class="btn btn-sm btn-success ms-3">
                        </form>
                    </div>
                <?php } elseif (isset($_POST['remove_gedcom_files2']) && isset($_POST['remove_confirm'])) { ?>
                    <?php for ($i = 0; $i < count($trees['removed_filenames']); $i++) { ?>
                        <?= $trees['removed_filenames'][$i]; ?> <?= __('GEDCOM file is REMOVED.'); ?><br>
                    <?php } ?>
                <?php } else { ?>
                    <?= __('If needed remove GEDCOM files (except test GEDCOM file):'); ?>
                    <form name="remove_gedcomfiles" action="index.php?page=tree&amp;menu_admin=tree_gedcom" method="post">
                        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                        <div class="row mt-2">
                            <div class="col-md-8">
                                <div class="input-group">
                                    <select size="1" name="remove_gedcom_files" class="form-select form-select-sm">
                                        <option value="gedcom_files_all"><?= __('Remove all GEDCOM files'); ?></option>
                                        <option value="gedcom_files_1_month">
                                            <?php printf(__('Remove GEDCOM files older than %d month(s)'), 1); ?>
                                        </option>
                                        <option value="gedcom_files_1_year">
                                            <?php printf(__('Remove GEDCOM files older than %d year(s)'), 1); ?>
                                        </option>
                                    </select>
                                    <input type="submit" name="submit" value="<?= __('Remove'); ?>" class="btn btn-sm btn-warning">
                                </div>
                            </div>
                        </div>
                    </form><br>
                <?php } ?>
            </div>
        </div>

    </div>

    <?php
    $dh  = opendir($trees['gedcom_directory']);
    while (false !== ($filename = readdir($dh))) {
        if (strtolower(substr($filename, -3)) === "ged") {
            $filenames[] = $trees['gedcom_directory'] . "/" . $filename;
        }
    }
    // *** Order GEDCOM files by alfabet ***
    if (isset($filenames)) {
        usort($filenames, 'strnatcasecmp');
    }

    // *** Find last GEDCOM file that was used for this tree ***
    $result = $dbh->query("SELECT tree_gedcom FROM humo_trees WHERE tree_id='" . $trees['tree_id'] . "'");
    $treegedDb = $result->fetch();
    ?>
    <form method="post" action="index.php?page=tree&amp;menu_admin=tree_gedcom" style="display : inline">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">

        <div class="p-3 mt-3 me-sm-2 genealogy_search">

            <div class="row mb-2 p-2 bg-primary-subtle">
                <div class="col-md-7"><?= __('Select GEDCOM file and settings'); ?></div>
            </div>

            <div class="row mb-3">
                <div class="col-md-auto"><?= __('Select GEDCOM file'); ?></div>
                <div class="col-md-auto">
                    <select size="1" name="gedcom_file" class="form-select form-select-sm">
                        <?php for ($i = 0; $i < count($filenames); $i++) { ?>
                            <!-- if this was last GEDCOM file that was used for this tree - select it -->
                            <option value="<?= $filenames[$i]; ?>" <?= $filenames[$i] == $treegedDb['tree_gedcom'] ? 'selected' : ''; ?>><?= $filenames[$i]; ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="accordion" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="false" aria-controls="collapseOne">
                            <?= __('GEDCOM settings'); ?>
                        </button>
                    </h2>
                    <div id="collapseOne" class="accordion-collapse collapse">
                        <div class="accordion-body genealogy_search">

                            <div class="form-check">
                                <input type="checkbox" name="add_source" value="" id="add_source" <?= $humo_option["gedcom_read_add_source"] == 'y' ? 'checked' : ''; ?> class="form-check-input">
                                <label class="form-check-label" for="add_source">
                                    <?= __('Add a general source connected to all persons in this GEDCOM file.'); ?>
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" name="reassign_gedcomnumbers" value="" id="reassign_gedcomnumbers" <?= $humo_option["gedcom_read_reassign_gedcomnumbers"] == 'y' ? 'checked' : ''; ?> class="form-check-input">
                                <label class="form-check-label" for="reassign_gedcomnumbers">
                                    <?= __('Reassign new ID numbers for persons, fams etc. (don\'t use IDs from GEDCOM)'); ?>
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" name="order_by_date" value="" id="order_by_date" <?= $humo_option["gedcom_read_order_by_date"] == 'y' ? 'checked' : ''; ?> class="form-check-input">
                                <label class="form-check-label" for="order_by_date">
                                    <?= __('Order children by date (only needed if children are in wrong order)'); ?>
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" name="order_by_fams" value="" id="order_by_fams" <?= $humo_option["gedcom_read_order_by_fams"] == 'y' ? 'checked' : ''; ?> class="form-check-input">
                                <label class="form-check-label" for="order_by_fams">
                                    <?= __('Order families by date (only needed if families are in wrong order)'); ?>
                                </label>
                            </div>

                            <?php /*
                            <div class="form-check">
                                <input type="checkbox" name="process_geo_location" value="" id="process_geo_location" <?= $humo_option["gedcom_read_process_geo_location"] == 'y' ? 'checked' : ''; ?> class="form-check-input">
                                <label class="form-check-label" for="process_geo_location">
                                    <?= __('Add new locations to geo-location database (for Google Maps locations). This will slow down reading of GEDCOM file!'); ?>
                                </label>
                            </div>
                            */ ?>

                            <div class="form-check">
                                <input type="checkbox" name="save_pictures" value="" id="save_pictures" <?= $humo_option["gedcom_read_save_pictures"] == 'y' ? 'checked' : ''; ?> class="form-check-input">
                                <label class="form-check-label" for="save_pictures">
                                    <?= __('Don\'t remove picture links from database (only needed for Geneanet GEDCOM file).'); ?><br>
                                    <span style="font-size: 13px;"><?= __('In Geneanet add HuMo-genealogy picture id in the source by a person. Using this example: #media1254,media13454#'); ?></span>
                                </label>
                            </div>

                            <!-- <input name="check_gedcom_process_pict_path" checked disabled class="form-check-input"> -->
                            <div class="row mt-2 mb-3">
                                <div class="col-md-auto"><?= __('Picture settings'); ?></div>
                                <div class="col-md-auto">
                                    <select size="1" name="gedcom_process_pict_path" class="form-select form-select-sm">
                                        <option value="file_name" <?= $humo_option["gedcom_process_pict_path"] == 'file_name' ? 'selected' : ''; ?>>
                                            <?= __('Only process picture file name. For example: picture.jpg [DEFAULT]'); ?>
                                        </option>
                                        <option value="full_path" <?= $humo_option["gedcom_process_pict_path"] == 'full_path' ? 'selected' : ''; ?>>
                                            <?= __('Process full picture path. For example: picture_path&#92;picture.jpg'); ?>
                                        </option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

            <div class="accordion" id="accordionExample">
                <div class="accordion-item">
                    <h2 class="accordion-header">
                        <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                            <?= __('GEDCOM process settings'); ?>
                        </button>
                    </h2>
                    <div id="collapseTwo" class="accordion-collapse collapse">
                        <div class="accordion-body genealogy_search">

                            <div class="form-check">
                                <input type="checkbox" name="check_processed" value="" id="check_processed" class="form-check-input">
                                <label class="form-check-label" for="check_processed">
                                    <?= __('Show non-processed items when processing GEDCOM (can be a long list!'); ?>
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" name="show_gedcomnumbers" value="" id="show_gedcomnumbers" class="form-check-input">
                                <label class="form-check-label" for="show_gedcomnumbers">
                                    <?= __('Show all numbers when processing GEDCOM (useful when a time-out occurs!)'); ?>
                                </label>
                            </div>

                            <div class="form-check">
                                <input type="checkbox" name="debug_mode" value="" id="debug_mode" class="form-check-input">
                                <label class="form-check-label" for="debug_mode">
                                    <?= __('Debug mode'); ?>
                                </label>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-auto"><?= __('Batch processing'); ?></div>
                                <div class="col-md-auto">
                                    <select size="1" name="commit_records" class="form-select form-select-sm">
                                        <option value="1"><?= __('1 record (slow processing, but needs less server-memory)'); ?></option>
                                        <option value="10" <?= $humo_option["gedcom_read_commit_records"] == '10' ? 'selected' : ''; ?>>10 <?= __('records per batch'); ?></option>
                                        <option value="100" <?= $humo_option["gedcom_read_commit_records"] == '100' ? 'selected' : ''; ?>>100 <?= __('records per batch'); ?></option>
                                        <option value="500" <?= $humo_option["gedcom_read_commit_records"] == '500' ? 'selected' : ''; ?>>500 <?= __('records per batch'); ?></option>
                                        <option value="1000" <?= $humo_option["gedcom_read_commit_records"] == '1000' ? 'selected' : ''; ?>>1000 <?= __('records per batch'); ?></option>
                                        <option value="5000" <?= $humo_option["gedcom_read_commit_records"] == '5000' ? 'selected' : ''; ?>>5000 <?= __('records per batch'); ?></option>
                                        <option value="10000" <?= $humo_option["gedcom_read_commit_records"] == '10000' ? 'selected' : ''; ?>>10000 <?= __('records per batch'); ?></option>
                                        <option value="9000000" <?= $humo_option["gedcom_read_commit_records"] == '9000000' ? 'selected' : ''; ?>> <?= __('ALL records (fast processing, but needs server-memory)'); ?></option>
                                    </select>
                                </div>
                            </div>

                            <?php
                            // *** Controlled time-out ***
                            $time_out = 0;
                            if ($humo_option["gedcom_read_time_out"]) {
                                $time_out = $humo_option["gedcom_read_time_out"];
                            }
                            if (isset($_POST['timeout_restart'])) {
                                if (isset($_SESSION['save_process_time']) && $_SESSION['save_process_time']) {
                                    $time_out = ($_SESSION['save_process_time'] - 3);
                                }
                                echo '<b>' . __('Time-out detected! Controlled time-out setting is adjusted. Retry reading of GEDCOM with new setting.') . '</b><br>';
                            }
                            $max_time = ini_get("max_execution_time");
                            ?>
                            <div class="row mb-2">
                                <div class="col-md-1">
                                    <input type="text" name="time_out" value="<?= $time_out; ?>" size="2" class="form-control form-control-sm">
                                </div>
                                <div class="col-md-7">
                                    <?= __('seconds. Controlled time-out, the GEDCOM script will restart and continue.<br>Use this if the server has a time-out setting (set less seconds then server time-out).<br>0 = disable controlled time-out.'); ?>
                                    <?php printf(__('Your server time-out setting is: %s seconds.'), $max_time); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <div class="p-3 mt-3 me-sm-2 genealogy_search">
            <?php
            // *** Show extra warning if there is an existing family tree ***
            $nr_persons = $db_functions->count_persons($trees['tree_id']);
            if ($nr_persons > 0) {
                // *** Option to add GEDCOM file to family tree if this family tree isn't empty ***
                $treetext = $showTreeText->show_tree_text($trees['tree_id'], $selected_language);
                $tree_text2 = '';
                if ($treetext['name']) {
                    $tree_text2 = $treetext['name'];
                }
            ?>
                <div class="form-check">
                    <input type="radio" value="no" name="add_tree" onchange="document.getElementById('step2').disabled = !this.checked;" class="form-check-input">
                    <label class="form-check-label" for="flexRadioDefault1">
                        <?php printf(__('Yes, replace existing family tree: <b>"%1$s"</b> with %2$s persons!'), $tree_text2, $nr_persons); ?>
                    </label>
                </div>

                <div class="form-check mb-3">
                    <input type="radio" value="yes" name="add_tree" onchange="document.getElementById('step2').disabled = !this.checked;" class="form-check-input">
                    <label class="form-check-label" for="flexRadioDefault1"><?= __('Add this GEDCOM file to the existing tree'); ?></label>
                </div>
                <input type="hidden" name="step" value="2">
                <input type="submit" name="submit" id="step2" disabled value="<?= __('Step'); ?> 2" class="btn btn-sm btn-success">
            <?php } else { ?>
                <input type="hidden" name="step" value="2">
                <input type="submit" name="submit" value="<?= __('Step'); ?> 2" class="btn btn-sm btn-success">
            <?php } ?>
        </div>
    </form><br>
    <?php
}

// *** Step 2 generate tables ***
elseif ($trees['step'] == '2') {
    $setting_value = 'n';
    if (isset($_POST["add_source"])) {
        $setting_value = 'y';
    }
    $db_functions->update_settings('gedcom_read_add_source', $setting_value);

    $setting_value = 'n';
    if (isset($_POST["reassign_gedcomnumbers"])) {
        $setting_value = 'y';
    }
    $db_functions->update_settings('gedcom_read_reassign_gedcomnumbers', $setting_value);

    $setting_value = 'n';
    if (isset($_POST["order_by_date"])) {
        $setting_value = 'y';
    }
    $db_functions->update_settings('gedcom_read_order_by_date', $setting_value);

    $setting_value = 'n';
    if (isset($_POST["order_by_fams"])) {
        $setting_value = 'y';
    }
    $db_functions->update_settings('gedcom_read_order_by_fams', $setting_value);

    /*
    $setting_value = 'n';
    if (isset($_POST["process_geo_location"])) {
        $setting_value = 'y';
    }
    $db_functions->update_settings('gedcom_read_process_geo_location', $setting_value);
    */

    if (isset($_POST['gedcom_process_pict_path'])) {
        $db_functions->update_settings('gedcom_process_pict_path', $_POST['gedcom_process_pict_path']);
    }

    $humo_option["gedcom_read_save_pictures"] = 'n';
    $setting_value = 'n';
    if (isset($_POST["save_pictures"])) {
        $setting_value = 'y';
        $humo_option["gedcom_read_save_pictures"] = 'y'; // *** Because variable is needed directly ***
    }
    $db_functions->update_settings('gedcom_read_save_pictures', $setting_value);

    if (isset($_POST['commit_records'])) {
        $db_functions->update_settings('gedcom_read_commit_records', $_POST['commit_records']);
    }

    if (isset($_POST['time_out']) && is_numeric($_POST['time_out'])) {
        $db_functions->update_settings('gedcom_read_time_out', $_POST['time_out']);
    }


    if (!isset($_POST['add_tree']) || isset($_POST['add_tree']) && $_POST['add_tree'] == 'no') {
        $_SESSION['add_tree'] = false;
        $limit = 2500;
    ?>
        <b><?= __('STEP 2) Remove old family tree:'); ?></b><br><br>

        <!-- Time out button -->
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_gedcom">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="gedcom_file" value="<?= $_POST['gedcom_file']; ?>">
            <input type="hidden" name="add_tree" value="no">
            <input type="hidden" name="step" value="2">

            <?php
            if (isset($_POST['check_processed'])) {
                echo '<input type="hidden" name="check_processed" value="1">';
            }
            /* TODO: change all variables (move to model)
            if ($trees['check_processed']) {
            ?>
                <input type="hidden" name="check_processed" value="1">
            <?php
            }
            */

            if (isset($_POST['show_gedcomnumbers'])) {
            ?>
                <input type="hidden" name="show_gedcomnumbers" value="1">
            <?php
            }
            if (isset($_POST['debug_mode'])) {
            ?>
                <input type="hidden" name="debug_mode" value="1">
            <?php
            }
            if (isset($_POST['time_out']) && is_numeric($_POST['time_out'])) {
            ?>
                <input type="hidden" name="time_out" value="<?= $_POST['time_out']; ?>">
            <?php } ?>

            <?= __('ONLY use in case of a time-out, to continue click:'); ?>
            <input type="submit" name="submit" value="<?= __('Step'); ?> 2" class="btn btn-sm btn-secondary">
        </form><br>
    <?php } ?>

    <?php if (!isset($_POST['add_tree']) || isset($_POST['add_tree']) && $_POST['add_tree'] == 'no') { ?>
        <!-- Use Ajax asynchronous loading -->
        <div class="spinner-border" id="loading_step2" role="status">
            <span class="visually-hidden">Loading...</span>
        </div>


        <!-- TEST -->
        <?php /*
        <?php $_SESSION['save_import_progress'] = 1; ?>
        <div class="progress" style="height:20px">
            <div class="progress-bar" role="progressbar"></div>
        </div>
        <script>
            var bar = document.querySelector(".progress-bar");
            bar.style.width = "1%";
            bar.innerText = "1%";
        </script>
        */ ?>


        <? /*
        // REMOVED FROM SCRIPT:
        // var add_source = "<?= isset($_POST['add_source']) ? htmlspecialchars($_POST['add_source'], ENT_QUOTES, 'UTF-8') : ''; ?>";
        // data.append('add_source', add_source);

        // var gedcom_file = "<?= isset($_POST['gedcom_file']) ? htmlspecialchars($_POST['gedcom_file'], ENT_QUOTES, 'UTF-8') : ''; ?>";
        // data.append('gedcom_file', gedcom_file);
        */ ?>

        <script>
            // Send $_POST to file.
            // var data = new FormData();

            // *** Read progress value every second ***
            function checkProgress() {
                /*
                // TEST 1: progress bar using AJAX
                var xhrProgress = new XMLHttpRequest();
                xhrProgress.open('GET', 'views/gedcom_import_progress.php', true);
                xhrProgress.onreadystatechange = function() {
                    if (xhrProgress.readyState == 4 && xhrProgress.status == 200) {
                        var response = JSON.parse(xhrProgress.responseText);

                        //var progressBar = document.getElementById('progress-bar');
                        //progressBar.style.width = response.percentComplete + '%';
                        //progressBar.innerText = Math.round(response.percentComplete) + '%';

                        var bar = document.querySelector(".progress-bar");
                        // response.percentComplete = 40;
                        bar.style.width = response.percentComplete + "%";
                        bar.innerText = response.percentComplete + "%";

                        if (response.percentComplete >= 100) {
                            clearInterval(progressInterval);
                        }

                        //document.getElementById('content_step2').innerHTML = 'TEST';
                        //document.getElementById('content_step2').innerHTML = xhr.responseText; // Show results
                    }
                };
                xhrProgress.send();
                //xhrProgress = null;
                */


                // TEST 2: progress bar without ajax
                fetch('views/gedcom_import_progress.php')
                    .then(response => response.json())
                    .then(data => {
                        var bar = document.querySelector(".progress-bar");
                        bar.style.width = data.percentComplete + "%";
                        bar.innerText = data.percentComplete + "%";

                        if (data.percentComplete >= 100) {
                            clearInterval(progressInterval);
                        }
                    });


                // TEST 3: Works, but doesn't show actual progress
                // var bar = document.querySelector(".progress-bar");
                // number = parseInt(bar.style.width) + 10;
                // bar.style.width = number + "%";
                // bar.innerText = number + "%";
                // if (number >= 100) {
                //     clearInterval(progressInterval);
                // }
            }

            //var progressInterval = setInterval(checkProgress, 1000); // Check progress every second

            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'index.php?page=gedcom_import2', true);
            //xhr.open('POST', 'index.php?page=gedcom_import2', true);

            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    document.getElementById('loading_step2').style.display = 'none'; // Hide spinning image
                    document.getElementById('button_step3').removeAttribute("disabled"); // Enable button 3
                    document.getElementById('content_step2').innerHTML = xhr.responseText; // Show results


                    // *** Complete progress bar ***
                    // clearInterval(progressInterval);
                    // var bar = document.querySelector(".progress-bar");
                    // bar.style.width = 100 + "%";
                    // bar.innerText = 100 + "%";


                }
            };
            xhr.send();
            //xhr.send(data);
        </script>
        <div id="content_step2"></div>

    <?php
    } else {
        $_SESSION['add_tree'] = true;
    ?>
        <div class="alert alert-warning mt-4" role="alert">
            <?= __('The data in this GEDCOM will be appended to the existing data in this tree!'); ?>
        </div>
    <?php
    }

    //$progress_counter=0;
    $handle = fopen($_POST["gedcom_file"], "r");

    // *** Get character set from GEDCOM file ***
    $accent = 'UTF-8';  // *** Default for GEDCOM 7 = UTF-8 ***
    while (!feof($handle)) {
        $buffer = fgets($handle, 4096);
        $buffer = trim($buffer); // *** Strip starting spaces for Pro-gen and ending spaces for Ancestry.
        // *** Save accent kind (ASCII, ANSI, ANSEL or UTF-8) ***
        if (substr($buffer, 0, 6) === '1 CHAR') {
            $accent = substr($buffer, 7);
            break;
        }
    }

    // *** PREPARE PROGRESS BAR ***
    $_SESSION['save_progress2'] = '0';
    $_SESSION['save_perc'] = '0';
    $_SESSION['save_total'] = '0';
    $_SESSION['save_starttime'] = '0';
    // *** END PREPARE PROGRESS BAR ***

    // Reset variables (needed to proceed after time out)
    $_SESSION['save_pointer'] = '0';

    // *** Reset gen_program ***
    $gen_program = '';
    $_SESSION['save_gen_program'] = $gen_program;
    $gen_program_version = '';
    $_SESSION['save_gen_program_version'] = $gen_program_version;
    ?>
    <br><br>
    <form method="post" action="index.php?page=tree&amp;menu_admin=tree_gedcom">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="gedcom_accent" value="<?= $accent; ?>">
        <input type="hidden" name="gedcom_file" value="<?= $_POST['gedcom_file']; ?>">
        <?php
        // TODO check values
        if (isset($_POST['check_processed'])) {
            echo '<input type="hidden" name="check_processed" value="1">';
        }
        if (isset($_POST['show_gedcomnumbers'])) {
            echo '<input type="hidden" name="show_gedcomnumbers" value="1">';
        }
        if (isset($_POST['debug_mode'])) {
            echo '<input type="hidden" name="debug_mode" value="1">';
        }
        if (isset($_POST['time_out']) && is_numeric($_POST['time_out'])) {
            echo '<input type="hidden" name="time_out" value="' . $_POST['time_out'] . '">';
        }

        if (!isset($_POST['add_tree'])) {
            // *** Reset nr of persons and families ***
            $sql = $dbh->query("UPDATE humo_trees SET tree_persons='', tree_families='' WHERE tree_prefix='" . $tree_prefix . "'");
        }

        if (isset($_POST['add_tree']) && $_POST['add_tree'] == 'yes') {
        ?>
            <input type="hidden" name="add_tree" value="yes">
        <?php } else { ?>
            <input type="hidden" name="add_tree" value="no">
        <?php } ?>

        <input type="hidden" name="step" value="3">
        <input type="submit" id="button_step3" name="submit" value="<?= __('Step'); ?> 3" class="btn btn-sm btn-success" <?= (!isset($_POST['add_tree']) || isset($_POST['add_tree']) && $_POST['add_tree'] == 'no') ? 'disabled' : ''; ?>>
    </form>

    <?php if (isset($_POST['add_tree']) && $_POST['add_tree'] == 'yes') { ?>
        <br><br>
        <form method="post" style="display:inline" action="index.php?page=tree&amp;menu_admin=tree_gedcom">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="submit" name="back" value="<?= __('Cancel'); ?>" class="btn btn-sm btn-secondary">
        </form>
    <?php } ?>

    <?php
}

/**
 * STEP 3 READ GEDCOM file
 */
elseif ($trees['step'] == '3') {
    // *** Processing time ***
    if ($_SESSION['save_starttime'] == 0) {
        $_SESSION['save_starttime'] = time();
    }
    $_SESSION['save_start_timeout'] = time(); // *** Start controlled time-out ***

    // begin step 3 merge additions
    $add_tree = false;
    if ($_SESSION['add_tree'] == true) {
        $add_tree = true;
        unset($_SESSION['add_tree']); // we don't want the session variable to persist - can cause problems!
    }

    $reassign = false;
    if ($humo_option["gedcom_read_reassign_gedcomnumbers"] == 'y') {
        $reassign = true;
    }

    if ($add_tree == true) {
        // if we add a tree we have to change the gedcomnumbers of pers, fam, source, addresses and notes
        // so that they will be different from the existing ones.
        // therefore we check what is the largest gednr in each of them
        // and in gedcom_cls.php we add this number to the ones in the new gedcom
        // this way they will never be the same as the existing ones

        // *** Generate new GEDCOM number ***
        $largest_pers_ged = $db_functions->generate_gedcomnr($trees['tree_id'], 'person');

        // *** Generate new GEDCOM number ***
        $largest_fam_ged = $db_functions->generate_gedcomnr($trees['tree_id'], 'family');

        // *** Generate new GEDCOM number ***
        $largest_source_ged = $db_functions->generate_gedcomnr($trees['tree_id'], 'source');

        // *** Generate new GEDCOM number ***
        $largest_address_ged = $db_functions->generate_gedcomnr($trees['tree_id'], 'address');

        // *** Generate new GEDCOM number ***
        $largest_repo_ged = $db_functions->generate_gedcomnr($trees['tree_id'], 'repo');

        // *** Generate new GEDCOM number ***
        $largest_text_ged = $db_functions->generate_gedcomnr($trees['tree_id'], 'text');

        // @O40@ object table
        // *** Generate new GEDCOM number ***
        $largest_object_ged = $db_functions->generate_gedcomnr($trees['tree_id'], 'event');
    }

    // For merging when we read in a new tree we have to make sure that the relevant rel_merge row in the Db is removed.
    $qry = "DELETE FROM humo_settings WHERE setting_variable ='rel_merge_" . $trees['tree_id'] . "'";
    $dbh->query($qry);
    // we have to make sure that the dupl_arr session is unset if it exists.
    if (isset($_SESSION['dupl_arr_' . $trees['tree_id']])) {
        unset($_SESSION['dupl_arr_' . $trees['tree_id']]);
        // we have to make sure the present_compare session is unset, if exists
    }
    if (isset($_SESSION['present_compare_' . $trees['tree_id']])) {
        unset($_SESSION['present_compare_' . $trees['tree_id']]);
    }
    // End step 3 merge additions 

    // *** Weblog Class ***

    // variables to reassign new gedcomnumbers (in gedcom_cls.php)
    if (isset($reassign_array)) {
        unset($reassign_array);
    }
    if ($reassign == true) {
        // reassign gedcomnumbers when importing tree
        $new_gednum["I"] = 1;
        $new_gednum["F"] = 1;
        $new_gednum["M"] = 1;
        $new_gednum["O"] = 1;
        $new_gednum["S"] = 1;
        $new_gednum["R"] = 1;
        $new_gednum["RP"] = 1;
        $new_gednum["N"] = 1;
    }
    if ($add_tree == true) {
        // reassign gedcomnumbers when importing added tree in merging
        $new_gednum["I"] = $largest_pers_ged;
        $new_gednum["F"] = $largest_fam_ged;
        $new_gednum["M"] = $largest_fam_ged;
        $new_gednum["O"] = $largest_fam_ged;
        $new_gednum["S"] = $largest_source_ged;
        $new_gednum["R"] = $largest_address_ged;
        $new_gednum["RP"] = $largest_repo_ged;
        $new_gednum["N"] = $largest_text_ged;
    }

    $gedcomImport = new \Genealogy\Include\GedcomImport($dbh, $tree_id, $tree_prefix, $humo_option, $add_tree, $reassign);

    require(__DIR__ . "/../include/prefixes.php");
    $loop2 = count($pers_prefix);
    for ($i = 0; $i < $loop2; $i++) {
        //$prefix[$i]=addslashes($pers_prefix[$i]);
        $prefix[$i] = $pers_prefix[$i];
        $prefix[$i] = str_replace("_", " ", $prefix[$i]);
        $prefix_length[$i] = strlen($prefix[$i]);
    }

    echo __('<b>STEP 3) Processing GEDCOM file:</b>
<p>The following lines have to be processed without error messages...<br>
<b>Processing may take a while!!</b>') . '<br>';

    // *** some providers use a timeout of 30 seconden, continue button needed. ***
    if ($_POST['time_out'] == '0') {
    ?>
        <form method="post" action="index.php?page=tree&amp;menu_admin=tree_gedcom" style="display : inline">
            <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
            <input type="hidden" name="timeout_restart" value="1">
            <input type="hidden" name="gedcom_accent" value="<?= $_POST['gedcom_accent']; ?>">

            <?php
            // TODO check values
            if (isset($_POST['check_processed'])) {
                echo '<input type="hidden" name="check_processed" value="1">';
            }
            if (isset($_POST['show_gedcomnumbers'])) {
                echo '<input type="hidden" name="show_gedcomnumbers" value="1">';
            }
            if (isset($_POST['debug_mode'])) {
                echo '<input type="hidden" name="debug_mode" value="1">';
            }
            ?>
            <?= __('ONLY use in case of a time-out, to continue click:'); ?> <input type="submit" name="timeout" value="<?= __('Restart'); ?>" class="btn btn-sm btn-secondary">
            <?= __('Restarts reading of GEDCOM using a controlled time-out.'); ?>
        </form><br><br>
    <?php
    }

    $process_gedcom = '';
    $buffer2 = '';

    // *** PREPARE PROGRESS BAR ***
    $progress2 = $_SESSION['save_progress2'];

    if (!isset($_POST['show_gedcomnumbers'])) {
    ?>
        <div class="progress" style="height:20px">
            <div class="progress-bar"></div>
        </div>

        <!-- Progress information -->
        <div id="information"></div>

        <?php
        $i = $_SESSION['save_progress2']; // save number of lines processed
        $perc = $_SESSION['save_perc'];   // save percentage processed

        $total = 0;
        // only first time in session: count number of lines in gedcom
        if ($_SESSION['save_total'] == '0') {
            $address_high = 1;
            $_SESSION['new_address_gedcomnr'] = 1;
            $source_high = 1;
            $_SESSION['new_source_gedcomnr'] = 1;

            $handle = fopen($_POST["gedcom_file"], "r");
            while (!feof($handle)) {
                $line = fgets($handle);
                $line = trim($line);

                // *** Find highest address gedcomnumber ***
                // 0 @R1@ RESI
                if (substr($line, -6, 6) === '@ RESI') {
                    $address_gedcomnr = substr($line, 4, -6);
                    if ($address_gedcomnr > $address_high) {
                        $address_high = $address_gedcomnr;
                    }
                    $_SESSION['new_address_gedcomnr'] = (int)$address_high + 1;
                    //echo 'ADDRESS: '.$address_gedcomnr.'!'.$address_high.' '.$_SESSION['new_address_gedcomnr'].'<br>';
                }

                // *** Find highest source gedcomnumber ***
                // 0 @S189@ SOUR
                if (substr($line, -6, 6) === '@ SOUR') {
                    $source_gedcomnr = substr($line, 4, -6);
                    if ($source_gedcomnr > $source_high) {
                        $source_high = $source_gedcomnr;
                    }
                    // *** Gedcom torture file uses: 0 @SOURCE1@ SOUR ***
                    if (is_int($source_gedcomnr)) {
                        $_SESSION['new_source_gedcomnr'] = $source_high + 1;
                    }
                    //echo 'SOURCE: '.$source_gedcomnr.'!'.$source_high.' '.$_SESSION['new_source_gedcomnr'].'<br>';
                }

                $total++; // Counts total number of lines.
            }
            $_SESSION['save_total'] = $total;
            fclose($handle);
        }
        $total = $_SESSION['save_total'];

        //ob_start(); // if used: statusbar doesn't work
        // Javascript for initial display of the progress bar and information (or after timeout)
        $percent = $perc . "%";
        if ($perc == 0) {
            $percent = "0.5%"; // show at least some green
        }
        ?>
        <script>
            //document.getElementById("progress").innerHTML="<div style=\"width:<?= $percent; ?>;background-color:#00CC00;\">&nbsp;</div>";
            document.getElementById("information").innerHTML = "<?= $i; ?> / <?= $total; ?> <?= __('lines processed'); ?> (<?= $perc; ?>%)";
        </script>

        <!-- Apr. 2024 New bootstrap bar -->
        <!-- $('.progress').css('width', count + "%"); -->
        <script>
            var bar = document.querySelector(".progress-bar");
            bar.style.width = <?= $perc; ?> + "%";
            //bar.style.width = `${perc}%`;
            bar.innerText = <?= $perc; ?> + "%";
        </script>

        <?php
        // This is for the buffer achieve the minimum size in order to flush data
        echo str_repeat(' ', 1024 * 64);
        // Send output to browser immediately
        ob_flush();
        flush();

        // determines the steps in percentages - regular: 2%
        $devider = 50;
        // 1% for larger files with over 200,000 lines
        if ($total > 200000) {
            $devider = 100;
        }
        // 0.5% for very large files
        if ($total > 1000000) {
            $devider = 200;
        }
        $step = round($total / $devider);
    }
    // *** END preparation of progress bar ***


    require_once(__DIR__ . "/../include/ansel2unicode/ansel2unicode.php");
    global $a2u;
    $a2u = new Ansel2Unicode();

    function encode($buffer, $gedcom_accent)
    {
        global $a2u;

        if ($gedcom_accent == "ASCII") {
            // These methods don't work :-(
            //$buffer=iconv("ASCII","UTF-8//IGNORE//TRANSLIT",$buffer);
            //$buffer = mb_convert_encoding($buffer, 'UTF-8', 'ISO-8859-2');

            // It looks like this is the only method that alway works:
            // Step 1: convert ASCII to html entities.
            $buffer = asciihtml($buffer);
            // Step 2: convert entities to UTF-8.
            $buffer = html_entity_decode($buffer, ENT_QUOTES, 'UTF-8');
        }

        if ($gedcom_accent == "ANSEL") {
            $buffer = $a2u->convert($buffer);

            // *** Method below is a lot faster, but accent characters are a problem ***
            //$buffer=asciihtml($buffer);
            //$buffer=anselhtml($buffer);
            //$buffer = html_entity_decode($buffer, ENT_QUOTES, 'UTF-8');
        }

        if ($gedcom_accent == "ANSI") {
            //$buffer=htmlentities($buffer,ENT_QUOTES,'ISO-8859-1');
            //$buffer=ansihtml($buffer);
            $buffer = iconv("windows-1252", "UTF-8", $buffer);
            //$buffer=iconv("windows-1252","UTF-8//IGNORE//TRANSLIT",$buffer);
        }

        if ($gedcom_accent == "UTF-8") {
            // *** No conversion needed ***
        }

        //echo mb_detect_encoding($buffer); // *** Show character set: doesn't seem to work properly... ***
        //echo '<br>';

        //$buffer=addslashes($buffer);
        return $buffer;
    }


    // TEST: lock tables. Unfortunately not much faster than usual... ONLY FOR MYISAM TABLES!
    /*
    mysql_query("LOCK TABLES
        humo_person
        humo_events
        humo_addresses
        humo_family
        humo_connections
        humo_humo_location
        humo_texts
        humo_sources
        humo_repositories
        WRITE;");
    */
    // *** Batch processing for InnoDB tables ***
    $commit_counter = 0;
    $commit_records = $humo_option["gedcom_read_commit_records"];
    if ($commit_records > 1) {
        $dbh->beginTransaction();
    }

    // *****************
    // *** Read file ***
    // *****************

    $handle = fopen($_POST["gedcom_file"], "r");

    // *** CONTINUE AFTER TIME_OUT ***
    // Set pointer if continued
    if ($_SESSION['save_pointer'] > 0) {
        fseek($handle, $_SESSION['save_pointer']);
    }

    if (isset($_SESSION['save_gen_program'])) {
        $gen_program = $_SESSION['save_gen_program'];
        $gedcomImport->set_gen_program($gen_program);
    }
    if (isset($_SESSION['save_gen_program_version'])) {
        $gen_program_version = $_SESSION['save_gen_program_version'];
        $gedcomImport->set_gen_program_version($gen_program_version);
    }

    $level0 = '';
    $level1 = '';
    $last_pointer = 0;

    while (!feof($handle)) {
        //$buffer = fgets($handle, 4096);
        $buffer = fgets($handle);
        $buffer = rtrim($buffer, "\n\r");  // *** strip newline ***
        $buffer = ltrim($buffer, " ");  // *** Strip starting spaces, for Pro-gen ***

        //TEST, show line after controlled time-out:
        //if ($last_pointer==0) echo 'New line: '.$buffer.'<br>';

        // *** Controlled timeout pointers, save last pointer before a "0 @" line ***
        $previous_pointer = $last_pointer;
        $last_pointer = ftell($handle);
        if (substr($buffer, 0, 3) === '0 @') {
            $save_pointer = $previous_pointer;
        }

        // TEST: show memory usage
        //if (!isset($memory)) $memory=memory_get_usage();
        //$calc_memory=(memory_get_usage()-$memory);
        //if ($calc_memory>100){
        //  echo '<b>';
        //}
        //	echo '<br>'.memory_get_usage().' '.$calc_memory.'@ ';
        //	$memory=memory_get_usage();
        //	echo ' '.$buffer;
        //if ($calc_memory>100){
        //  echo '!!</b>';
        //}

        // *** Commit genealogical data every x records. CAN ONLY BE USED WITH InnoDB TABLES!! ***
        if ($commit_records > 1) {
            $commit_counter++;
            if ($commit_counter > $humo_option["gedcom_read_commit_records"]) {
                $commit_counter = 0;
                // *** Save data in database ***
                if ($dbh->inTransaction()) {
                    $dbh->commit();
                }
                // *** Start next process batch ***
                $dbh->beginTransaction();
            }
        }

        // *** Strip all spaces for Ancestry GEDCOM ***
        if (isset($gen_program) && $gen_program == 'Ancestry.com Family Trees') {
            $buffer = rtrim($buffer, " ");
        }

        $start_gedcom = '';
        // *** Remove BOM header from UTF-8 BOM file ***
        if ($start_gedcom == '') {
            if (substr($buffer, 0, 3) == pack("CCC", 0xef, 0xbb, 0xbf)) {
                // *** Remove BOM UTF-8 characters from 1st line ***
                $buffer = substr($buffer, 3);
            }
        }
        if (substr($buffer, 0, 3) === '0 @' || $buffer === "0 TRLR") {
            $start_gedcom = 1;
        }

        // *** Start reading GEDCOM parts ***
        if ($start_gedcom) {
            if ($process_gedcom === "person") {
                $buffer2 = encode($buffer2, $_POST["gedcom_accent"]);
                $gedcomImport->process_person($buffer2);
                $process_gedcom = '';
                $buffer2 = '';

                // TEST: show memory usage
                //if (!isset($memory)) $memory=memory_get_usage();
                //$calc_memory=(memory_get_usage()-$memory);
                //if ($calc_memory>100){
                //  echo '<b>';
                //}
                //	echo '<br>'.memory_get_usage().' '.$calc_memory.'@ ';
                //	$memory=memory_get_usage();
                //	echo ' '.$buffer;
                //if ($calc_memory>100){
                //  echo '!!</b>';
                //}

            } elseif ($process_gedcom === "family") {
                $buffer2 = encode($buffer2, $_POST["gedcom_accent"]);
                $gedcomImport->process_family($buffer2, 0, 0);
                $process_gedcom = '';
                $buffer2 = '';
            } elseif ($process_gedcom === "text") {
                $buffer2 = encode($buffer2, $_POST["gedcom_accent"]);
                $gedcomImport->process_text($buffer2);
                $process_gedcom = '';
                $buffer2 = '';
            } elseif ($process_gedcom === "source") {
                $buffer2 = encode($buffer2, $_POST["gedcom_accent"]);
                $gedcomImport->process_source($buffer2);
                $process_gedcom = '';
                $buffer2 = '';
            }

            // *** Repository ***
            elseif ($process_gedcom === "repository") {
                $buffer2 = encode($buffer2, $_POST["gedcom_accent"]);
                $gedcomImport->process_repository($buffer2);
                $process_gedcom = '';
                $buffer2 = '';
            } elseif ($process_gedcom === "address") {
                $buffer2 = encode($buffer2, $_POST["gedcom_accent"]);
                $gedcomImport->process_address($buffer2);
                $process_gedcom = '';
                $buffer2 = '';
            } elseif ($process_gedcom === "object") {
                $buffer2 = encode($buffer2, $_POST["gedcom_accent"]);
                $gedcomImport->process_object($buffer2);
                $process_gedcom = '';
                $buffer2 = '';
            }
        }

        // *** CHECK ***
        if (substr($buffer, -6, 6) === '@ INDI') {
            $process_gedcom = "person";
            $buffer2 = '';
        } elseif (substr($buffer, -5, 5) === '@ FAM') {
            $process_gedcom = "family";
            $buffer2 = '';
        } elseif (substr($buffer, 0, 3) === '0 @') {
            // *** Aldfaer text: 0 @N954@ NOTE ***
            if (strpos($buffer, '@ NOTE') > 1) {
                $process_gedcom = "text";
                $buffer2 = '';
            }

            if (substr($buffer, -6, 6) === '@ SOUR') {
                $process_gedcom = "source";
                $buffer2 = '';
            } elseif (substr($buffer, -6, 6) === '@ REPO') {
                $process_gedcom = "repository";
                $buffer2 = '';
            } elseif (substr($buffer, -6, 6) === '@ RESI') {
                $process_gedcom = "address";
                $buffer2 = '';
            } elseif (substr($buffer, -6, 6) === '@ OBJE') {
                $process_gedcom = "object";
                $buffer2 = '';
            }
        }

        $buffer2 = $buffer2 . $buffer . "\n";

        // *** Save level0 ***
        if (substr($buffer, 0, 1) === '0') {
            $level0 = substr($buffer, 2, 6);
        }
        if (substr($buffer, 0, 1) === '1') {
            $level1 = substr($buffer, 2, 6);
        }

        // *** 1 SOUR Haza-Data ***
        //	0 HEAD
        //	1 SOUR PRO-GEN
        //	2 VERS 3.22
        //
        //	0 HEAD
        //	1 SOUR Haza-Data
        //	2 VERS 7.2
        if ($level0 === 'HEAD') {
            if (substr($buffer, 0, 6) === '1 SOUR') {
                $gen_program = substr($buffer, 7);
                $_SESSION['save_gen_program'] = $gen_program;
                $gedcomImport->set_gen_program($gen_program);

                echo '<br><br>' . __('GEDCOM file') . ': <b>' . $gen_program . '</b>, ';

                printf(__('this is an <b>%s</b> file'), $_POST["gedcom_accent"]);
                echo '<br>';

                // Save tree <-> GEDCOM connection - write GEDCOM to "tree_gedcom" in relevant tree
                $dbh->query("UPDATE humo_trees SET tree_gedcom='" . $_POST["gedcom_file"] . "', tree_gedcom_program='" . $gen_program . "' WHERE tree_prefix='" . $tree_prefix . "'");
            }

            // *** First "VERS" normally is program version ***
            if ($gen_program_version == '' && substr($buffer, 2, 4) === 'VERS') {
                $gen_program_version = substr($buffer, 7);
                $gedcomImport->set_gen_program_version($gen_program_version);

                $_SESSION['save_gen_program_version'] = $gen_program_version;
            }

            // *** Get GEDCOM version (only for GEDCOM 7!) ***
            // 1 GEDC
            // 2 VERS 7.0
            //if ($level1=='GEDC' AND substr($buffer,2,4)=='VERS'){
            //	//echo $buffer.'??';
            //}
        }

        // *** progress bar ***
        // TODO: use a function, see GEDCOM export example.
        //if (!isset($_POST['show_gedcomnumbers']) AND $progress>($progress_counter/500)){
        if (!isset($_POST['show_gedcomnumbers'])) {
            $i++;
            $_SESSION['save_progress2'] = $i;

            // Calculate the percentage
            if ($i % $step == 0) {
                if ($devider == 50) {
                    $perc += 2;
                } elseif ($devider == 100) {
                    $perc += 1;
                } elseif ($devider == 200) {
                    $perc += 0.5;
                }
                $_SESSION['save_perc'] = $perc;
                $percent = $perc . "%";

                //ob_start(); // if used: statusbar doesn't work
        ?>
                <script>
                    //document.getElementById("progress").innerHTML = "<div style=\"width:<?= $percent; ?>;background-color:#00CC00;\">&nbsp;</div>";
                    document.getElementById("information").innerHTML = "<?= $i; ?> / <?= $total; ?> <?= __('lines processed'); ?>";
                </script>

                <!-- Apr. 2024 New bootstrap bar -->
                <script>
                    var bar = document.querySelector(".progress-bar");
                    bar.style.width = <?= $perc; ?> + "%";
                    bar.innerText = <?= $perc; ?> + "%";
                </script>

            <?php
                // TODO These items don't work properly. Probably because of the for loops.
                // This is for the buffer achieve the minimum size in order to flush data
                echo str_repeat(' ', 1024 * 64);
                // Send output to browser immediately
                ob_flush();
                flush();
            }
        }

        // *** Save process time every cycle (time-out) ***
        $process_time = time();
        $_SESSION['save_process_time'] = $process_time - $_SESSION['save_starttime'];

        // *** Controlled time-out ***
        $time_out = 0;
        if (is_numeric($_POST['time_out'])) {
            $time_out = $_POST['time_out'];
        }
        if ($time_out > 0) {
            if (($process_time - $_SESSION['save_start_timeout']) > $time_out) {

                // *** Save data in database ***
                $dbh->commit();

                // *** Save pointer of GEDCOM file ***
                $_SESSION['save_pointer'] = $save_pointer;

                // *** Set time for next cycle ***
                $_SESSION['save_start_timeout'] = time();

                // *** Restart after controlled time-out. ***
            ?>
                <form method="post" action="index.php?page=tree&amp;menu_admin=tree_gedcom" style="display : inline">
                    <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
                    <input type="hidden" name="gedcom_accent" value="<?= $_POST['gedcom_accent']; ?>">
                    <?php
                    if (isset($_POST['check_processed'])) {
                        echo '<input type="hidden" name="check_processed" value="1">';
                    }
                    if (isset($_POST['show_gedcomnumbers'])) {
                        echo '<input type="hidden" name="show_gedcomnumbers" value="1">';
                    }
                    if (isset($_POST['debug_mode'])) {
                        echo '<input type="hidden" name="debug_mode" value="1">';
                    }
                    ?>
                    <input type="hidden" name="gedcom_file" value="<?= $_POST['gedcom_file']; ?>">
                    <input type="hidden" name="time_out" value="<?= $time_out; ?>">
                    <input type="hidden" name="step" value="3">
                    <b><?= __('Controlled time-out to continue reading of GEDCOM file, click:'); ?></b> <input type="submit" name="submit" value="<?= __('Step'); ?> 3" class="btn btn-sm btn-success"><br>
                    <?php printf(' <b>' . __('Or wait %s seconds for automatic continuation. Some browsers will give a reload message...') . '</b>', '5'); ?>
                </form><br><br>

                <!-- Automatic reload after 5 seconds -->
                <script>
                    setTimeout(function() {
                        location.reload(true);
                    }, 5000);
                </script>
        <?php
                exit();
            }
        }
    }
    fclose($handle);


    // *** Add a general source to all persons in this GEDCOM file ***
    if ($humo_option["gedcom_read_add_source"] == 'y') {
        // *** Generate new GEDCOM number ***
        $new_gedcomnumber = 'S' . $db_functions->generate_gedcomnr($trees['tree_id'], 'source');
        $gedcom_date = strtoupper(date("d M Y"));

        $sql = "INSERT INTO humo_sources SET
            source_tree_id='" . $trees['tree_id'] . "',
            source_gedcomnr='" . $new_gedcomnumber . "',
            source_status='',
            source_title='" . __('Persons added by GEDCOM import.') . "',
            source_date='" . $gedcom_date . "',
            source_place='',
            source_publ='',
            source_refn='',
            source_auth='',
            source_subj='',
            source_item='',
            source_kind='',
            source_repo_caln='',
            source_repo_page='',
            source_repo_gedcomnr='',
            source_text='" . __('Persons added by GEDCOM import.') . "'";
        $dbh->query($sql);

        // *** Replace temporary source number by all persons by a final source number ***
        $gebeurtsql = "UPDATE humo_connections SET
            connect_source_id='" . $new_gedcomnumber . "'
            WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_source_id='Stemporary'";
        $dbh->query($gebeurtsql);
    }


    // *** End of MyISAM batch processing ***
    // mysql_query("UNLOCK TABLES;");
    // *** End of InnoDB batch processing ***
    if ($commit_records > 1) {
        $dbh->commit();
    }

    // *** Show endtime ***
    $end_time = time();
    printf('<br>' . __('Reading in the file took: %d seconds') . '<br>', $end_time - $_SESSION['save_starttime']);

    //*** Show "non-processed GEDCOM items" ***
    if (isset($_POST['check_processed'])) {
        ?>
        <div style="height:350px;width:900px;overflow-y:scroll;white-space:nowrap;">
            <table class="table">
                <tr>
                    <th>nr.</th>
                    <th colspan="5"><?= __('Non-processed items'); ?></th>
                </tr>
                <tr>
                    <th><br></th>
                    <th><?= __('Level'); ?> 0</th>
                    <th><?= __('Level'); ?> 1</th>
                    <th><?= __('Level'); ?> 2</th>
                    <th><?= __('Level'); ?> 3</th>
                    <th><?= __('text'); ?></th>
                </tr>
                <?php
                $not_processed = $gedcomImport->get_not_processed();
                if (isset($not_processed)) {
                    $counter = count($not_processed);
                    for ($i = 0; $i < $counter; $i++) {
                ?>
                        <tr>
                            <td><?= ($i + 1); ?></td>
                            <td><?= $not_processed[$i]; ?></td>
                        </tr>
                    <?php
                    }
                } else {
                    ?>
                    <tr>
                        <td>0</td>
                        <td colspan="4"><?= __('All items have been processed!'); ?></td>
                    </tr>
                <?php } ?>
            </table>
        </div>
    <?php
    }
    if (!isset($_POST['show_gedcomnumbers'])) {
        //ob_start(); // if used: statusbar doesn't work
    ?>
        <script>
            document.getElementById("information").innerHTML = "<?= $total; ?> / <?= $total; ?> <?= __('lines processed'); ?>";
        </script>

        <!-- Apr. 2024 New bootstrap bar -->
        <script>
            var bar = document.querySelector(".progress-bar");
            bar.style.width = 100 + "%";
            bar.innerText = 100 + "%";
        </script>

    <?php
        ob_flush();
        flush();
    }
    ?>
    <br>
    <form method="post" action="index.php?page=tree&amp;menu_admin=tree_gedcom">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="gen_program" value="<?= $gen_program; ?>">
        <input type="hidden" name="gen_program_version" value="<?= $gen_program_version; ?>">
        <input type="hidden" name="step" value="4">
        <input type="submit" name="submit" value="<?= __('Step'); ?> 4" class="btn btn-sm btn-success">
    </form>
<?php
}

// *** Step 4 ***
elseif ($trees['step'] == '4') {
    $start_time = time();
    $gen_program = $_POST['gen_program'];
    //$gedcomImport->set_gen_program($gen_program);

    $gen_program_version = $_POST['gen_program_version'];
    //$gedcomImport->set_gen_program_version($gen_program_version);
?>

    <b><?= __('STEP 4) Final database processing:'); ?></b><br>

    <!-- To proceed if a (30 seconds) timeout has occured -->
    <form method="post" action="index.php?page=tree&amp;menu_admin=tree_gedcom">
        <input type="hidden" name="tree_id" value="<?= $trees['tree_id']; ?>">
        <input type="hidden" name="gen_program" value="<?= $_POST['gen_program']; ?>">
        <input type="hidden" name="gen_program_version" value="<?= $_POST['gen_program_version']; ?>">
        <input type="hidden" name="step" value="4">
        <br><?= __('ONLY use in case of a time-out, to continue click:'); ?> <input type="submit" name="submit" value="<?= __('Step'); ?> 4" class="btn btn-sm btn-secondary">
    </form><br>

    <!-- Show progress -->
    <div class="progress" style="height:20px">
        <div class="progress-bar"></div>
    </div>

    <!-- Progress information -->
    <div id="information"></div>

    <?php
    $total = 1;
    $i = 0;

    // *** Quick check for seperate saved texts in database (used in Aldfaer program and Reunion) and store them as standard texts ***
    $search_text_qry = $dbh->query("SELECT text_id FROM humo_texts WHERE text_tree_id='" . $trees['tree_id'] . "' LIMIT 0,1");
    $count_text = $search_text_qry->rowCount();
    if ($count_text > 0) {
        // *** Number of records in text table, used to show a status counter ***
        //$total_text_qry = $dbh->query("SELECT COUNT(*) FROM humo_texts WHERE text_tree_id='" . $trees['tree_id'] . "'");
        $total_text_qry = $dbh->query("SELECT COUNT(text_id) FROM humo_texts WHERE text_tree_id='" . $trees['tree_id'] . "'");
        $total_text_db = $total_text_qry->fetch();
        $total_texts = $total_text_db[0];
        $total_processed_texts = 0;

        echo '<br>&gt;&gt;&gt; ' . __('Processing of referenced texts into standard texts...');
        echo ' [' . $total_texts . ' text records].';

        $db_functions->set_tree_id($trees['tree_id']);

        // *** Batch processing for InnoDB tables ***
        $commit_counter = 0;
        $commit_records = $humo_option["gedcom_read_commit_records"];
        if ($commit_records > 1) {
            $dbh->beginTransaction();
        }

        // *** Prepare progress bar ***
        $count_person = $dbh->query("SELECT COUNT(pers_id) FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "'");
        $count_personDb = $count_person->fetch();
        $nr_records = $count_personDb[0];

        $count_fam = $dbh->query("SELECT COUNT(fam_id) FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "'");
        $count_famDb = $count_fam->fetch();
        $nr_records += $count_famDb[0];

        $perc = 0;
        if ($nr_records > 0) {
            //ob_start(); // if used: statusbar doesn't work

            // Javascript for initial display of the progress bar and information (or after timeout)
            $percent = $perc . "%";
            if ($perc == 0) {
                $percent = "0.5%"; // show at least some green
            }
            echo '<script>';
            //echo 'document.getElementById("progress").innerHTML="<div style=\"width:' . $percent . ';background-color:#00CC00;\">&nbsp;</div>";';
            echo 'document.getElementById("information").innerHTML="' . $i . ' / ' . $nr_records . ' ' . __('lines processed') . ' (' . $perc . '%).";';
            echo '</script>';

    ?>
            <!-- Apr. 2024 New bootstrap bar -->
            <script>
                var bar = document.querySelector(".progress-bar");
                bar.style.width = <?= $perc; ?> + "%";
                bar.innerText = <?= $perc; ?> + "%";
            </script>
            <?php

            // This is for the buffer achieve the minimum size in order to flush data
            echo str_repeat(' ', 1024 * 64);
            // Send output to browser immediately
            ob_flush();
            flush();

            $devider = 50; // determines the steps in percentages - regular: 2%
            // 1% for larger files with over 200,000 lines
            if ($nr_records > 200000) {
                $devider = 100;
            }
            // 0.5% for very large files
            if ($nr_records > 1000000) {
                $devider = 200;
            }
            $step = round($nr_records / $devider);
        }

        // *** Process texts in person table ***
        // *** First only read pers_id, otherwise too much memory use ***
        $person2_qry = $dbh->query("SELECT pers_id FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "'");
        /* Isn't faster?
        $person2_qry=$dbh->query("SELECT pers_id FROM humo_persons
        	WHERE pers_tree_id='".$trees['tree_id']."'
        	AND LEFT(pers_text,1)='@'
            OR LEFT(pers_name_text,1)='@'
            OR LEFT(pers_birth_text,1)='@'
            OR LEFT(pers_bapt_text,1)='@'
            OR LEFT(pers_death_text,1)='@'
            OR LEFT(pers_buried_text,1)='@'
        ");
        */

        while ($person2Db = $person2_qry->fetch(PDO::FETCH_OBJ)) {
            $personDb = $db_functions->get_person_with_id($person2Db->pers_id);

            $pers_text = '';
            //TODO use array to check for first character?
            //if (substr($personDb->pers_text, 0, 1) == '@') {
            if ($personDb->pers_text && $personDb->pers_text[0] == '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($personDb->pers_text, 1, -1));
                if ($search_textDb) {
                    $pers_text = $search_textDb->text_text;

                    // *** Search for all connected sources ***
                    $connect_order = 0;
                    $connect_qry = "SELECT connect_source_id FROM humo_connections
                        WHERE connect_tree_id='" . $trees['tree_id'] . "'
                        AND connect_kind='ref_text' AND connect_sub_kind='ref_text_source'
                        AND connect_connect_id='" . $search_textDb->text_gedcomnr . "'
                        ORDER BY connect_order";
                    $connect_sql = $dbh->query($connect_qry);
                    while ($connectDb = $connect_sql->fetch(PDO::FETCH_OBJ)) {
                        // *** Add to connection table ***
                        $connect_order++;
                        $stmt = $dbh->prepare(
                            "INSERT INTO humo_connections SET
                                connect_tree_id = :tree_id,
                                connect_order = :connect_order,
                                connect_kind = 'person',
                                connect_sub_kind = 'pers_text_source',
                                connect_connect_id = :connect_id,
                                connect_source_id = :source_id"
                        );
                        $stmt->execute([
                            ':tree_id' => $trees['tree_id'],
                            ':connect_order' => $connect_order,
                            ':connect_id' => $personDb->pers_gedcomnumber,
                            ':source_id' => $connectDb->connect_source_id
                        ]);
                    }
                }
            }

            $pers_name_text = '';
            if (substr($personDb->pers_name_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($personDb->pers_name_text, 1, -1));
                if ($search_textDb) {
                    $pers_name_text = $search_textDb->text_text;
                }
            }

            //$pers_birth_text = '';
            if (substr($personDb->pers_birth_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($personDb->pers_birth_text, 1, -1));
                if ($search_textDb) {
                    //$pers_birth_text = $search_textDb->text_text;

                    $stmt = $dbh->prepare("UPDATE humo_events SET event_text = :event_text WHERE event_id = :event_id");
                    $stmt->execute([
                        ':event_text' => $search_textDb->text_text,
                        ':event_id' => $personDb->pers_birth_event_id
                    ]);
                }
            }

            //$pers_bapt_text = '';
            if (substr($personDb->pers_bapt_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($personDb->pers_bapt_text, 1, -1));
                if ($search_textDb) {
                    //$pers_bapt_text = $search_textDb->text_text;

                    $stmt = $dbh->prepare("UPDATE humo_events SET event_text = :event_text WHERE event_id = :event_id");
                    $stmt->execute([
                        ':event_text' => $search_textDb->text_text,
                        ':event_id' => $personDb->pers_bapt_event_id
                    ]);
                }
            }

            //$pers_death_text = '';
            if (substr($personDb->pers_death_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($personDb->pers_death_text, 1, -1));
                if ($search_textDb) {
                    //$pers_death_text = $search_textDb->text_text;

                    $stmt = $dbh->prepare("UPDATE humo_events SET event_text = :event_text WHERE event_id = :event_id");
                    $stmt->execute([
                        ':event_text' => $search_textDb->text_text,
                        ':event_id' => $personDb->pers_death_event_id
                    ]);
                }
            }

            //$pers_buried_text = '';
            if (substr($personDb->pers_buried_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($personDb->pers_buried_text, 1, -1));
                if ($search_textDb) {
                    //$pers_buried_text = $search_textDb->text_text;

                    $stmt = $dbh->prepare("UPDATE humo_events SET event_text = :event_text WHERE event_id = :event_id");
                    $stmt->execute([
                        ':event_text' => $search_textDb->text_text,
                        ':event_id' => $personDb->pers_buried_event_id
                    ]);
                }
            }

            // *** Save all standard person texts ***
            //if ($pers_text || $pers_name_text || $pers_birth_text || $pers_bapt_text || $pers_death_text || $pers_buried_text) {
            if ($pers_text || $pers_name_text) {
                $first_item = true;
                // *** Remark: no need to check for fam_tree_id because fam_id is used ***
                // Build the fields to update and their values
                $fields = [];
                $params = [':pers_id' => $personDb->pers_id];

                if ($pers_text) {
                    $fields[] = "pers_text = :pers_text";
                    $params[':pers_text'] = $pers_text;
                }
                if ($pers_name_text) {
                    $fields[] = "pers_name_text = :pers_name_text";
                    $params[':pers_name_text'] = $pers_name_text;
                }
                /*
                if ($pers_birth_text) {
                    $fields[] = "pers_birth_text = :pers_birth_text";
                    $params[':pers_birth_text'] = $pers_birth_text;
                }
                if ($pers_bapt_text) {
                    $fields[] = "pers_bapt_text = :pers_bapt_text";
                    $params[':pers_bapt_text'] = $pers_bapt_text;
                }
                if ($pers_death_text) {
                    $fields[] = "pers_death_text = :pers_death_text";
                    $params[':pers_death_text'] = $pers_death_text;
                }
                if ($pers_buried_text) {
                    $fields[] = "pers_buried_text = :pers_buried_text";
                    $params[':pers_buried_text'] = $pers_buried_text;
                }
                */

                if (!empty($fields)) {
                    $sql = "UPDATE humo_persons SET " . implode(', ', $fields) . " WHERE pers_id = :pers_id";
                    $stmt = $dbh->prepare($sql);
                    $stmt->execute($params);
                }

                // *** progress bar ***
                $i++;
                $_SESSION['save_progress2'] = $i;

                // Calculate the percentage
                if ($step > 0 && $i % $step == 0) {
                    if ($devider == 50) {
                        $perc += 2;
                    } elseif ($devider == 100) {
                        $perc += 1;
                    } elseif ($devider == 200) {
                        $perc += 0.5;
                    }
                    $_SESSION['save_perc'] = $perc;
                    $percent = $perc . "%";

                    // Javascript for updating the progress bar and information
                    //ob_start(); // if used: statusbar doesn't work
            ?>
                    <script>
                        //document.getElementById("progress").innerHTML = "<div style=\"width:<?= $percent; ?>;background-color:#00CC00;\">&nbsp;</div>"; 
                        document.getElementById("information").innerHTML = "<?= $i; ?> / <?= $nr_records; ?> <?= __('lines processed'); ?> <?= __('persons'); ?>";
                    </script>

                    <!-- Apr. 2024 New bootstrap bar -->
                    <script>
                        var bar = document.querySelector(".progress-bar");
                        bar.style.width = <?= $perc; ?> + "%";
                        bar.innerText = <?= $perc; ?> + "%";
                    </script>

                <?php
                    // This is for the buffer achieve the minimum size in order to flush data
                    echo str_repeat(' ', 1024 * 64);

                    // Send output to browser immediately
                    ob_flush();
                    flush();
                }

                // *** Commit genealogical data every x records. CAN ONLY BE USED WITH InnoDB TABLES!! ***
                if ($commit_records > 1) {
                    $commit_counter++;
                    if ($commit_counter > $humo_option["gedcom_read_commit_records"]) {
                        $commit_counter = 0;
                        // *** Save data in database ***
                        $dbh->commit();
                        // *** Start next process batch ***
                        $dbh->beginTransaction();
                    }
                }
            }
        }

        // *** End of InnoDB batch processing ***
        if ($commit_records > 1) {
            // *** Save data in database ***
            $dbh->commit();
            // *** Start next process batch ***
            $dbh->beginTransaction();
        }


        // *** Process texts in family table ***
        // *** Memory improvement, only read 1 full record at a time ***
        $fam_qry2 = $dbh->query("SELECT fam_id FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "'");
        while ($famDb2 = $fam_qry2->fetch(PDO::FETCH_OBJ)) {
            /*
            $fam_qry = $dbh->query("SELECT fam_id, fam_gedcomnumber, fam_text, fam_relation_text, fam_marr_notice_text,
                fam_marr_text, fam_marr_church_notice_text, fam_marr_church_text, fam_div_text
                FROM humo_families WHERE fam_id='" . $famDb2->fam_id . "'");
            $famDb = $fam_qry->fetch(PDO::FETCH_OBJ);
            */
            $famDb = $db_functions->get_family_with_id($famDb2->fam_id);

            $fam_text = '';
            if (substr($famDb->fam_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($famDb->fam_text, 1, -1));
                if ($search_textDb) {
                    $fam_text = $search_textDb->text_text;
                }
            }

            //$fam_relation_text = '';
            if (substr($famDb->fam_relation_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($famDb->fam_relation_text, 1, -1));
                if ($search_textDb) {
                    //$fam_relation_text = $search_textDb->text_text;

                    $stmt = $dbh->prepare("UPDATE humo_events SET event_text = :event_text WHERE event_id = :event_id");
                    $stmt->execute([
                        ':event_text' => $search_textDb->text_text,
                        ':event_id' => $fam_personDb->fam_relation_event_id
                    ]);
                }
            }

            //$fam_marr_notice_text = '';
            if (substr($famDb->fam_marr_notice_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($famDb->fam_marr_notice_text, 1, -1));
                if ($search_textDb) {
                    //$fam_marr_notice_text = $search_textDb->text_text;

                    $stmt = $dbh->prepare("UPDATE humo_events SET event_text = :event_text WHERE event_id = :event_id");
                    $stmt->execute([
                        ':event_text' => $search_textDb->text_text,
                        ':event_id' => $famDb->fam_marr_event_id
                    ]);
                }
            }

            $fam_marr_text = '';
            if (substr($famDb->fam_marr_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($famDb->fam_marr_text, 1, -1));
                if ($search_textDb) {
                    $fam_marr_text = $search_textDb->text_text;
                    // *** Search for all connected sources ***
                    $connect_order = 0;
                    $connect_qry = "SELECT connect_source_id FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "'
                        AND connect_kind='ref_text' AND connect_sub_kind='ref_text_source'
                        AND connect_connect_id='" . $search_textDb->text_gedcomnr . "'
                        ORDER BY connect_order";
                    $connect_sql = $dbh->query($connect_qry);
                    while ($connectDb = $connect_sql->fetch(PDO::FETCH_OBJ)) {
                        // *** Add to connection table ***
                        $connect_order++;
                        $stmt = $dbh->prepare(
                            "INSERT INTO humo_connections SET
                                connect_tree_id = :tree_id,
                                connect_order = :connect_order,
                                connect_kind = 'family',
                                connect_sub_kind = 'family_text',
                                connect_connect_id = :connect_id,
                                connect_source_id = :source_id"
                        );
                        $stmt->execute([
                            ':tree_id' => $trees['tree_id'],
                            ':connect_order' => $connect_order,
                            ':connect_id' => $famDb->fam_gedcomnumber,
                            ':source_id' => $connectDb->connect_source_id
                        ]);
                    }
                }
            }

            //$fam_marr_church_notice_text = '';
            if (substr($famDb->fam_marr_church_notice_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($famDb->fam_marr_church_notice_text, 1, -1));
                if ($search_textDb) {
                    //$fam_marr_church_notice_text = $search_textDb->text_text;

                    $stmt = $dbh->prepare("UPDATE humo_events SET event_text = :event_text WHERE event_id = :event_id");
                    $stmt->execute([
                        ':event_text' => $search_textDb->text_text,
                        ':event_id' => $famDb->fam_marr_church_notice_event_id
                    ]);
                }
            }

            //$fam_marr_church_text = '';
            if (substr($famDb->fam_marr_church_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($famDb->fam_marr_church_text, 1, -1));
                if ($search_textDb) {
                    //$fam_marr_church_text = $search_textDb->text_text;

                    $stmt = $dbh->prepare("UPDATE humo_events SET event_text = :event_text WHERE event_id = :event_id");
                    $stmt->execute([
                        ':event_text' => $search_textDb->text_text,
                        ':event_id' => $famDb->fam_marr_church_event_id
                    ]);
                }
            }

            //$fam_div_text = '';
            if (substr($famDb->fam_div_text ?? '', 0, 1) === '@') {
                $total_processed_texts++;
                $search_textDb = $db_functions->get_text(substr($famDb->fam_div_text, 1, -1));
                if ($search_textDb) {
                    //$fam_div_text = $search_textDb->text_text;

                    $stmt = $dbh->prepare("UPDATE humo_events SET event_text = :event_text WHERE event_id = :event_id");
                    $stmt->execute([
                        ':event_text' => $search_textDb->text_text,
                        ':event_id' => $famDb->fam_div_event_id
                    ]);
                }
            }

            // *** Save all standard family texts ***
            // $fam_text || $fam_relation_text || $fam_marr_notice_text || $fam_marr_text || $fam_marr_church_notice_text || $fam_marr_church_text || $fam_div_text
            if ($fam_text) {
                $first_item = true;
                // *** Remark: no need to check for fam_tree_id because fam_id is used ***
                // Build the fields to update and their values
                $fields = [];
                $params = [':fam_id' => $famDb->fam_id];

                if ($fam_text) {
                    $fields[] = "fam_text = :fam_text";
                    $params[':fam_text'] = $fam_text;
                }

                if (!empty($fields)) {
                    $sql = "UPDATE humo_families SET " . implode(', ', $fields) . " WHERE fam_id = :fam_id";
                    $stmt = $dbh->prepare($sql);
                    $stmt->execute($params);
                }

                /*
                // *** Update progress ***
                $total++;
                echo '<script>';
                //echo 'document.getElementById("information").innerHTML="'.$total.' '.__('lines processed').'";';
                $status = ' [' . __('families') . ' ' . ($total_texts - $total_processed_texts) . ']';
                echo 'document.getElementById("information").innerHTML="' . $total . ' ' . __('lines processed') . $status . '";';
                echo '</script>';
                ob_flush();
                flush();
                */

                // *** progress bar ***
                //if (!isset($_POST['show_gedcomnumbers'])) {
                $i++;
                $_SESSION['save_progress2'] = $i;

                // Calculate the percentage
                if ($i % $step == 0) {
                    if ($devider == 50) {
                        $perc += 2;
                    } elseif ($devider == 100) {
                        $perc += 1;
                    } elseif ($devider == 200) {
                        $perc += 0.5;
                    }
                    $_SESSION['save_perc'] = $perc;
                    $percent = $perc . "%";

                    // Javascript for updating the progress bar and information
                    //ob_start(); // if used: statusbar doesn't work

                    echo '<script>';
                    //echo 'document.getElementById("progress").innerHTML="<div style=\"width:' . $percent . ';background-color:#00CC00;\">&nbsp;</div>";';
                    echo 'document.getElementById("information").innerHTML="' . $i . ' / ' . $nr_records . ' ' . __('lines processed') . ' (' . $percent . ')' . ' ' . __('families') . '";';
                    echo '</script>';

                ?>
                    <!-- Apr. 2024 New bootstrap bar -->
                    <script>
                        var bar = document.querySelector(".progress-bar");
                        bar.style.width = <?= $perc; ?> + "%";
                        bar.innerText = <?= $perc; ?> + "%";
                    </script>
        <?php

                    // This is for the buffer achieve the minimum size in order to flush data
                    echo str_repeat(' ', 1024 * 64);

                    // Send output to browser immediately
                    ob_flush();
                    flush();
                }
                //}

                // *** Commit genealogical data every x records. CAN ONLY BE USED WITH InnoDB TABLES!! ***
                if ($commit_records > 1) {
                    $commit_counter++;
                    if ($commit_counter > $humo_option["gedcom_read_commit_records"]) {
                        $commit_counter = 0;
                        // *** Save data in database ***
                        $dbh->commit();
                        // *** Start next process batch ***
                        $dbh->beginTransaction();
                    }
                }
            }
        }

        // *** End of InnoDB batch processing ***
        if ($commit_records > 1) {
            // *** Save data in database ***
            $dbh->commit();
        }
        //ob_start(); // if used: statusbar doesn't work
        ?>
        <script>
            document.getElementById("information").innerHTML = "<?= $nr_records; ?> / <?= $nr_records; ?> <?= __('lines processed'); ?>";
        </script>
    <?php
        ob_flush();
        flush();
    }
    ?>

    <!-- Apr. 2024 New bootstrap bar -->
    <script>
        var bar = document.querySelector(".progress-bar");
        bar.style.width = 100 + "%";
        bar.innerText = 100 + "%";
    </script>

<?php
    // *** Process text by name etc. ***
    echo '<br>&gt;&gt;&gt; ' . __('Processing texts IN names...');
    $person_qry = $dbh->query("SELECT pers_id, pers_name_text, pers_firstname, pers_lastname
        FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_name_text!=''");
    while ($personDb = $person_qry->fetch(PDO::FETCH_OBJ)) {
        //*** Haza-data option: text IN name where "*" is. ***
        if ($personDb->pers_name_text) {
            // *** Check * in firstname ***
            $position = strpos($personDb->pers_firstname, '*');
            if ($position !== false) {
                // pers_name_text change into: process_text(pers_name_text)
                $pers_firstname = substr($personDb->pers_firstname, 0, $position) . $personDb->pers_name_text . substr($personDb->pers_firstname, $position + 1);
                $stmt = $dbh->prepare("UPDATE humo_persons SET pers_firstname = :firstname, pers_name_text = '' WHERE pers_id = :pers_id");
                $stmt->execute([
                    ':firstname' => $pers_firstname,
                    ':pers_id' => $personDb->pers_id
                ]);
            }

            // ***Check * in lastname ***
            $position = strpos($personDb->pers_lastname, '*');
            if ($position !== false) {
                //pers_name_text change into: process_text(pers_name_text)
                $pers_lastname = substr($personDb->pers_lastname, 0, $position) . $personDb->pers_name_text . substr($personDb->pers_lastname, $position + 1);
                $dbh->query("UPDATE humo_persons SET pers_lastname='" . $pers_lastname . "', pers_name_text='' WHERE pers_id='" . $personDb->pers_id . "'");
            }
        }
    }

    // *** Jeroen Beemster Jan 2006. Code rewritten in June 2013 by Huub. Order children and marriages ***
    // If there are children without dates, ordering doesn't work very good...
    if ($humo_option["gedcom_read_order_by_date"] == 'y') {
        $db_functions->set_tree_id($trees['tree_id']);

        // TODO double function.
        function date_string($text)
        {
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
            return substr($text, -4) . substr(substr($text, -7), 0, 2) . substr($text, 0, 2);
            // Solve maybe later: date_string 2 mei is smaller then 10 may (2 birth in 1 month is rare...).
        }

        echo '<br>&gt;&gt;&gt; ' . __('Order children...');

        // TODO only get fam_id and fam_children
        $fam_qry = $dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_children!='' AND (INSTR(fam_children,';')>0) ");
        while ($famDb = $fam_qry->fetch(PDO::FETCH_OBJ)) {
            $child_array = explode(";", $famDb->fam_children);
            //echo '<br>'.$famDb->fam_children.' ';
            $nr_children = count($child_array);
            unset($children_array);
            for ($i = 0; $i < $nr_children; $i++) {
                $childDb = $db_functions->get_person($child_array[$i]);

                $child_array_nr = $child_array[$i];
                if ($childDb->pers_birth_date) {
                    $children_array[$child_array_nr] = date_string($childDb->pers_birth_date);
                } elseif ($childDb->pers_bapt_date) {
                    $children_array[$child_array_nr] = date_string($childDb->pers_bapt_date);
                } else {
                    $children_array[$child_array_nr] = '';
                }
                //echo $children_array[$child_array_nr].' ';
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
                $dbh->query("UPDATE humo_families SET fam_children='" . $fam_children . "' WHERE fam_id='" . $famDb->fam_id . "'");
            }
        }
    }

    // *** Order families, added in november 2018 by Huub. ***
    // If there is a relation without dates, ordering doesn't work very good...
    if ($humo_option["gedcom_read_order_by_fams"] == 'y') {
        $db_functions->set_tree_id($trees['tree_id']);

        function date_string2($text)
        {
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
            return substr($text, -4) . substr(substr($text, -7), 0, 2) . substr($text, 0, 2);
            // Solve maybe later: date_string 2 mei is smaller then 10 may (2 marriages in 1 month is rare...).
        }

        echo '<br>&gt;&gt;&gt; ' . __('Order families...');

        // *** Find only persons with multiple relations ***
        // TODO only get pers_id and pers_fams
        $person = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_fams!='' AND (INSTR(pers_fams,';')>0) ");
        while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
            $fam_array = explode(";", $personDb->pers_fams);
            $nr_fams = count($fam_array);

            unset($families_array);
            for ($i = 0; $i < $nr_fams; $i++) {
                $famDb = $db_functions->get_family($fam_array[$i]);

                $fam_array_nr = $fam_array[$i];
                if ($famDb->fam_relation_date) {
                    $families_array[$fam_array_nr] = date_string2($famDb->fam_relation_date);
                } elseif ($famDb->fam_marr_notice_date) {
                    $families_array[$fam_array_nr] = date_string2($famDb->fam_marr_notice_date);
                } elseif ($famDb->fam_marr_date) {
                    $families_array[$fam_array_nr] = date_string2($famDb->fam_marr_date);
                } elseif ($famDb->fam_marr_church_notice_date) {
                    $families_array[$fam_array_nr] = date_string2($famDb->fam_marr_church_notice_date);
                } elseif ($famDb->fam_marr_church_date) {
                    $families_array[$fam_array_nr] = date_string2($famDb->fam_marr_church_date);
                } else {
                    $families_array[$fam_array_nr] = '';
                }

                //echo $families_array[$fam_array_nr].' ';
            }

            asort($families_array);

            $families = '';
            foreach ($families_array as $key => $val) {
                if ($families != '') {
                    $families .= ';';
                }
                $families .= $key;
            }

            if ($personDb->pers_fams != $families) {
                $dbh->query("UPDATE humo_persons SET pers_fams='" . $families . "' WHERE pers_id='" . $personDb->pers_id . "'");
            }
        }
    }


    // *** Process Aldfaer adoption children: remove unnecessary added relations ***
    // *** Aldfaer uses a fictive family number for adoption. The family number is removed, a person number is used ***
    if ($gen_program == 'ALDFAER') {
        $famc_adoptive_qry = $dbh->query("SELECT * FROM humo_events WHERE event_tree_id='" . $trees['tree_id'] . "' AND event_kind='adoption_by_person'");
        while ($famc_adoptiveDb = $famc_adoptive_qry->fetch(PDO::FETCH_OBJ)) {
            $fam = $famc_adoptiveDb->event_event;

            // *** Remove fams number from man and woman ***
            $new_nr_qry = "SELECT * FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber='" . $fam . "'";
            $new_nr_result = $dbh->query($new_nr_qry);
            $new_nr = $new_nr_result->fetch(PDO::FETCH_OBJ);

            // *** Replace familynumber with person number ***
            if ($new_nr->fam_man) {
                $dbh->query("UPDATE humo_events SET event_event='" . $new_nr->fam_man . "' WHERE event_id='" . $famc_adoptiveDb->event_id . "'");
                $personnr = $new_nr->fam_man;
            }
            if ($new_nr->fam_woman) {
                $dbh->query("UPDATE humo_events SET event_event='" . $new_nr->fam_woman . "' WHERE event_id='" . $famc_adoptiveDb->event_id . "'");
                $personnr = $new_nr->fam_woman;
            }

            if ($new_nr->fam_man || $new_nr->fam_woman) {
                $person_qry = "SELECT pers_id, pers_gedcomnumber, pers_fams FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "' AND pers_gedcomnumber='" . $personnr . "'";
                $person_result = $dbh->query($person_qry);
                $person_db = $person_result->fetch(PDO::FETCH_OBJ);
                if ($person_db->pers_gedcomnumber) {
                    unset($fams2);
                    $fams = explode(";", $person_db->pers_fams);
                    foreach ($fams as $key => $value) {
                        if ($fams[$key] != $fam) {
                            $fams2[] = $fams[$key];
                        }
                    }
                    $fams3 = '';
                    if (isset($fams2[0])) {
                        $fams3 = implode(";", $fams2);
                    }

                    $dbh->query("UPDATE humo_persons SET pers_fams='" . $fams3 . "' WHERE pers_id='" . $person_db->pers_id . "'");
                }
            }

            $dbh->query("DELETE FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "' AND fam_gedcomnumber='" . $fam . "'");
        }
    }

    // *** Try to proces picture (added in source) from Geneanet. HuMo-genealogy media number must be added in Geneanet source by person ***
    // *** Example: 1 media item #media12354#
    // *** Multiple media items: #media1235,media3345#
    if ($humo_option["gedcom_read_save_pictures"] == 'y') {
        $connect_qry = $dbh->query("SELECT * FROM humo_connections WHERE connect_tree_id='" . $trees['tree_id'] . "' AND connect_sub_kind='person_source'");
        while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
            // *** Get source ***
            if ($connectDb->connect_source_id) {
                $db_functions->set_tree_id($trees['tree_id']);
                $sourceDb = $db_functions->get_source($connectDb->connect_source_id);

                // *** Process multiple media items by a person ***
                // *** Added a '!' sign to prevent '0' detection. The routine will stop then! ***
                $media_items = '!' . $sourceDb->source_text;
                $first = strpos($media_items, '#');
                if ($first) {
                    $media_items = substr($media_items, $first + 1);
                    $second = strpos($media_items, '#');
                    $media_items = substr($media_items, 0, $second);

                    $media_items_array = explode(',', $media_items);
                    $count_media = count($media_items_array);
                    for ($i = 0; $i < $count_media; $i++) {
                        $event_sql = "UPDATE humo_events SET
                            event_connect_id='" . $connectDb->connect_connect_id . "'
                            WHERE event_id='" . substr($media_items_array[$i], 5) . "'";
                        $dbh->query($event_sql);
                    }
                }
            }
        }
    }

    // *** Count persons and families ***
    echo '<br>&gt;&gt;&gt; ' . __('Counting persons and families and enter into database...') . ' ';
    // *** Calculate number of persons and families ***
    $person_qry = $dbh->query("SELECT pers_id FROM humo_persons WHERE pers_tree_id='" . $trees['tree_id'] . "'");
    $persons = $person_qry->rowCount();

    $family_qry = $dbh->query("SELECT fam_id FROM humo_families WHERE fam_tree_id='" . $trees['tree_id'] . "'");
    $families = $family_qry->rowCount();

    $tree_date = date("Y-m-d H:i");
    $sql = "UPDATE humo_trees SET tree_persons='" . $persons . "', tree_families='" . $families . "', tree_date='" . $tree_date . "' WHERE tree_prefix='" . $tree_prefix . "'";
    $dbh->query($sql);

    // *** Remove cache ***
    $stmt = $dbh->prepare("DELETE FROM humo_settings WHERE setting_variable LIKE :cache AND setting_tree_id = :tree_id");
    $stmt->execute([
        ':cache' => 'cache%',
        ':tree_id' => $trees['tree_id']
    ]);

    // Show process time:
    $end_time = time();
    printf('<p>' . __('Processing took: %d seconds') . '<br>', $end_time - $start_time);
    echo __('No error messages? In this case the database is ready for use!');

    printf('<p><b>' . __('Ready! Now click %s to watch the family tree') . '</b><br>', ' <a href="../index.php">index.php</a> ');
    echo __('TIP: Use <a href="index.php?page=cal_date">"Calculated birth dates"</a> for a better privacy filter.');

    // *** Reset selected person in editor ***
    unset($_SESSION['admin_pers_gedcomnumber']);
    unset($_SESSION['admin_fam_gedcomnumber']);
}

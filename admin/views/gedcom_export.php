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
@set_time_limit(3000);

$persids = array();
$famsids = array();
$noteids = array();
?>

<h1 class="center"><?= __('GEDCOM file export'); ?></h1>

<b><?= __('Don\'t use a GEDCOM file as a backup for your genealogical data!'); ?></b><br>
<?= __('A GEDCOM file is only usefull to exchange genealogical data with other genealogical programs.'); ?><br>
<?= __('Use "Database backup" for a proper backup.'); ?><br><br>

<?php
if (isset($_POST['submit_button'])) {
    // *** Show processed lines ***
    //$line_nr = 0;
    //$line_counter = 500;
?>
    <!--
    <div class="alert alert-success">
        <div id="information" style="display: inline;"></div>
    </div>
-->

    <!-- Bootstrap progress bar -->
    <div class="progress" style="height:20px">
        <div class="progress-bar"></div>
    </div>

    <?= __('GEDCOM file will be exported to gedcom_files/ folder'); ?><br>
<?php } ?>


<?php
// *** Start GEDCOM export ***
if (isset($tree_id) and isset($_POST['submit_button'])) {
    require_once __DIR__ . "/../include/gedcom_export.php";
?>
    <?= __('GEDCOM file is generated'); ?><br>

    <form method="POST" action="include/gedcom_download.php" target="_blank">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <input type="hidden" name="file_name" value="<?= $export['path'] . $export['file_name']; ?>">
        <input type="hidden" name="file_name_short" value="<?= $export['file_name']; ?>">
        <input type="submit" name="something" value="<?= __('Download GEDCOM file'); ?>" class="btn btn-sm btn-success">
    </form><br>
<?php } ?>

<form method="POST" id="gedcom_export" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">

    <div class="p-3 my-md-2 genealogy_search container-md">
        <div class="row mb-2 p-2 bg-primary-subtle">
            <div class="col-md-7"><?= __('Select family tree to export and click "Start export"'); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Choose family tree to export'); ?></div>

            <div class="col-md-4">
                <?php
                $tree_sql = "SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order";
                $tree_result = $dbh->query($tree_sql);
                if ($export["part_tree"] == 'part') {
                    // we have to refresh so that the persons to choose from will belong to this tree!
                    echo '<input type="hidden" name="flag_newtree" value=\'0\'>';
                }
                ?>
                <select <?= $export["part_tree"] == 'part' ? ' onChange="this.form.flag_newtree.value=\'1\';this.form.submit();" ' : ''; ?> size="1" name="tree_id" class="form-select form-select-sm">
                    <?php
                    while ($treeDb = $tree_result->fetch(PDO::FETCH_OBJ)) {
                        $treetext = show_tree_text($treeDb->tree_id, $selected_language);
                    ?>
                        <option value="<?= $treeDb->tree_id; ?>" <?= $treeDb->tree_id == $tree_id ? 'selected' : ''; ?>><?= $treetext['name']; ?></option>
                    <?php } ?>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Whole tree or part'); ?></div>
            <div class="col-md-4">
                <input type="radio" onClick="javascript:this.form.submit();" value="whole" name="part_tree" <?= $export["part_tree"] == "part" ? '' : 'checked'; ?> class="form-check-input"> <?= __('Whole tree'); ?><br>
                <input type="radio" onClick="javascript:this.form.submit();" value="part" name="part_tree" <?= $export["part_tree"] == "part" ? 'checked' : ''; ?> class="form-check-input"> <?= __('Partial tree'); ?>
            </div>
        </div>

        <?php if ($export["part_tree"] == "part") { ?>
            <?php
            // *** Select person ***
            $search_quicksearch = '';
            $search_id = '';
            if (isset($_POST["search_quicksearch"])) {
                $search_quicksearch = safe_text_db($_POST['search_quicksearch']);
                $_SESSION['admin_search_quicksearch'] = $search_quicksearch;
                $_SESSION['admin_search_id'] = '';
                $search_id = '';
            }
            if (isset($_SESSION['admin_search_quicksearch'])) {
                $search_quicksearch = $_SESSION['admin_search_quicksearch'];
            }

            if (isset($_POST["search_id"]) and (!isset($_POST["search_quicksearch"]) or $_POST["search_quicksearch"] == '')) {
                // if both name and ID given go by name
                $search_id = safe_text_db($_POST['search_id']);
                $_SESSION['admin_search_id'] = $search_id;
                $_SESSION['admin_search_quicksearch'] = '';
                $search_quicksearch = '';
            }
            if (isset($_SESSION['admin_search_id'])) {
                $search_id = $_SESSION['admin_search_id'];
            }
            ?>

            <div class="row mb-2">
                <div class="col-md-4"><?= __('Choose person'); ?></div>
                <div class="col-md-4">
                    <!-- Search persons firstname/ lastname -->
                    <input type="text" name="search_quicksearch" value="<?= $search_quicksearch; ?>" size="15" class="form-control form-control-sm">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4"></div>
                <div class="col-md-4">
                    <?= __('or ID:'); ?><br>
                    <input type="text" name="search_id" value="<?= $search_id; ?>" size="8" class="form-control form-control-sm">
                    <input type="submit" value="<?= __('Search'); ?>" class="btn btn-sm btn-secondary">
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4"></div>
                <div class="col-md-8">
                    <?php
                    unset($person_result);

                    $idsearch = false; // flag for search with ID;
                    if ($search_quicksearch != '') {
                        // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
                        $search_quicksearch = str_replace(' ', '%', $search_quicksearch);

                        // *** In case someone entered "Mons, Huub" using a comma ***
                        $search_quicksearch = str_replace(',', '', $search_quicksearch);

                        $person_qry = "SELECT pers_lastname, pers_firstname, pers_gedcomnumber, pers_prefix FROM humo_persons
                            WHERE pers_tree_id='" . $tree_id . "'
                            AND (CONCAT(pers_firstname,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%$search_quicksearch%'
                            OR CONCAT(pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%$search_quicksearch%' 
                            OR CONCAT(pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%$search_quicksearch%' 
                            OR CONCAT(REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%$search_quicksearch%')
                            ORDER BY pers_lastname, pers_firstname, CAST(substring(pers_gedcomnumber, 2) AS UNSIGNED)";
                        $person_result = $dbh->query($person_qry);
                    } elseif ($search_id != '') {
                        if (substr($search_id, 0, 1) != "i" and substr($search_id, 0, 1) != "I") {
                            $search_id = "I" . $search_id;
                        } //make entry "48" into "I48"
                        $person_qry = "SELECT pers_lastname, pers_firstname, pers_gedcomnumber, pers_prefix FROM humo_persons
                            WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $search_id . "'";
                        $person_result = $dbh->query($person_qry);
                        $idsearch = true;
                    } else {
                        $person_qry = "SELECT pers_tree_id, pers_lastname, pers_firstname, pers_gedcomnumber, pers_prefix FROM humo_persons
                            WHERE pers_tree_id='" . $tree_id . "' LIMIT 0,1";
                        $person_result = $dbh->query($person_qry);
                    }

                    $pers_gedcomnumber = '';
                    if (isset($_POST['person']) and $_POST['flag_newtree'] != '1') {
                        $pers_gedcomnumber = $_POST['person'];
                    }
                    ?>

                    <select size="1" name="person" class="form-select form-select-sm">
                        <?php while ($person = $person_result->fetch(PDO::FETCH_OBJ)) { ?>
                            <option value="<?= $person->pers_gedcomnumber; ?>" <?= (isset($pers_gedcomnumber) && $person->pers_gedcomnumber == $pers_gedcomnumber) ? 'selected' : ''; ?>>
                                <?= $person->pers_lastname; ?>, <?= $person->pers_firstname . ' ' . strtolower(str_replace("_", " ", $person->pers_prefix)); ?> [<?= $person->pers_gedcomnumber; ?>]
                            </option>
                        <?php } ?>
                    </select>
                </div>
            </div>


            <div class="row mb-2">
                <div class="col-md-4"><?= __('Number of generations to export'); ?></div>
                <div class="col-md-4">
                    <select size="1" name="nr_generations" class="form-select form-select-sm">
                        <option value="50"><?= __('All'); ?></option>
                        <?php for ($i = 1; $i < 20; $i++) { ?>
                            <option value="<?= $i; ?>" <?= isset($_POST['nr_generations']) and $_POST['nr_generations'] == $i ? 'selected' : ''; ?>><?= ($i + 1); ?></option>
                        <?php } ?>
                    </select>
                </div>
            </div>

            <div class="row mb-2">
                <!-- PMB - start of check buttons for options -->
                <div class="col-md-4"><?= __('Choose type of export'); ?></div>
                <div class="col-md-8">
                    <input type="radio" value="descendant" name="kind_tree" <?= (isset($_POST['kind_tree']) && $_POST['kind_tree'] == "ancestor") ? '' : 'checked'; ?> class="form-check-input"> <?= __('Descendants'); ?><br>

                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="desc_spouses" value="1" <?= isset($_POST['kind_tree']) and !isset($_POST['desc_spouses']) ? '' : 'checked'; ?> class="form-check-input"> <?= __('Include spouses of descendants'); ?><br>

                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="desc_sp_parents" value="1" <?= isset($_POST['desc_sp_parents']) ? 'checked' : ''; ?> class="form-check-input"> <?= __('Include parents of spouses'); ?><br>

                    <input type="radio" value="ancestor" name="kind_tree" <?= isset($_POST['kind_tree']) and $_POST['kind_tree'] == "ancestor" ? 'checked' : ''; ?> class="form-check-input"> <?= __('Ancestors'); ?><br>

                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="ances_spouses" value="1" <?= isset($_POST['kind_tree']) and !isset($_POST['ances_spouses']) ? '' : 'checked'; ?> class="form-check-input"> <?= __('Include spouse(s) of base person'); ?><br>

                    &nbsp;&nbsp;&nbsp;&nbsp;<input type="checkbox" name="ances_sibbl" value="1" <?= isset($_POST['ances_sibbl']) ? 'checked' : ''; ?> class="form-check-input"> <?= __('Include sibblings of ancestors and base person'); ?>
                </div>
            </div>
        <?php } ?>
    </div>


    <?php
    // *** GEDCOM submitter/ GEDCOM inzender ***
    /* Full example, if all items were used:
        0 @SUBMITTER@ SUBM
        1 NAME Firstname Lastname
        1 ADDR Submitter address line 1
        2 CONT Submitter address line 2
        2 ADR1 Submitter address line 1
        2 ADR2 Submitter address line 2
        2 CITY Submitter address city
        2 STAE Submitter address state
        2 POST Submitter address ZIP code
        2 CTRY Submitter address country
        1 PHON Submitter phone number 1
        1 PHON Submitter phone number 2
        1 PHON Submitter phone number 3 (last one!)
        1 LANG English
        1 OBJE
        2 FORM jpeg
        2 TITL Submitter Multimedia File
        2 FILE ImgFile.JPG
        2 NOTE @N1@
        1 RFN Submitter Registered RFN
        1 RIN 1
        1 CHAN
        2 DATE 7 Sep 2000
        3 TIME 8:35:36
        */
    ?>


    <div class="p-3 my-md-2 genealogy_search container-md">
        <div class="row mb-2 p-2 bg-primary-subtle">
            <div class="col-md-7"><?= __('GEDCOM submitter'); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Name'); ?></div>
            <div class="col-md-4">
                <input type="text" name="gedcom_submit_name" value="<?= $export['submit_name']; ?>" size="35" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Address'); ?></div>
            <div class="col-md-4">
                <input type="text" name="gedcom_submit_address" value="<?= $export['submit_address']; ?>" size="35" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Country'); ?></div>
            <div class="col-md-4">
                <input type="text" name="gedcom_submit_country" value="<?= $export['submit_country']; ?>" size="35" class="form-control form-control-sm">
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('E-mail'); ?></div>
            <div class="col-md-4">
                <!-- Using HTML5 mail validation -->
                <input type="email" name="gedcom_submit_mail" value="<?= $export['submit_mail']; ?>" size="35" class="form-control form-control-sm">
            </div>
        </div>
    </div>

    <div class="p-3 my-md-2 genealogy_search container-md">
        <div class="row mb-2 p-2 bg-primary-subtle">
            <div class="col-md-7"><?= __('Settings'); ?></div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('GEDCOM version'); ?></div>

            <div class="col-md-4">
                <select size="1" name="gedcom_version" class="form-select form-select-sm">
                    <option value="70"><?= __('GEDCOM 7.0'); ?></option>
                    <option value="551" <?= isset($_POST['gedcom_version']) && $_POST['gedcom_version'] == '551' ? 'selected' : ''; ?>><?= __('GEDCOM 5.5.1'); ?></option>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Character set'); ?></div>
            <div class="col-md-4">
                <select size="1" name="gedcom_char_set" class="form-select form-select-sm">
                    <?php
                    $selected = '';
                    if (isset($_POST['gedcom_char_set']) and $_POST['gedcom_char_set'] == 'UTF-8') {
                        $selected = 'selected';
                    }
                    if (isset($_POST['gedcom_version']) && $_POST['gedcom_version'] == '70') {
                        // *** GEDCOM 7.0 is selected, always use UTF-8 character set ***
                        $_POST['gedcom_char_set'] = 'UTF-8';
                        $selected = 'selected';
                    }
                    ?>
                    <option value="UTF-8" <?= $selected; ?>><?= __('UTF-8 (recommended character set)'); ?></option>

                    <option value="ANSI" <?= isset($_POST['gedcom_char_set']) and $_POST['gedcom_char_set'] == 'ANSI' ? 'selected' : ''; ?>>ANSI</option>

                    <option value="ASCII" <?= isset($_POST['gedcom_char_set']) and $_POST['gedcom_char_set'] == 'ASCII' ? 'selected' : ''; ?>>ASCII</option>
                </select>
            </div>
            <div class="col-md-4">
                <?= __('GEDCOM 7.0 always uses the UTF-8 character set.'); ?>
            </div>
        </div>

        <?php
        // PMB - Start of dropdowns for 'Normal' export options
        // We need to set the default though for the opening form or the dropdowns don't appear...
        if (isset($_POST['export_type']) && ($_POST['export_type']=='normal' || $_POST['export_type']=='minimal')) {
            $export_type = $_POST['export_type'];
        } else {
            $export_type = 'normal';
        }
        ?>
        <div class="row mb-2">
            <div class="col-md-4"><?= __('Export type'); ?></div>
            <div class="col-md-8">
                <?php
                // PMB - select whether 'normal' or 'minimal' export, this uses the same style as the 'All' or 'Individual' selector
                // 'normal' will show the dropdowns for 'text' and 'sources'
                // 'minimal' will be used in the export to 'turn off' extra info being included
                ?>
                <input type="radio" onClick="javascript:this.form.submit();" value="normal" name="export_type" <?= $export_type == "normal" ? 'checked' : ''; ?> class="form-check-input"> <?= __('Normal'); ?><br>
                <input type="radio" onClick="javascript:this.form.submit();" value="minimal" name="export_type" <?= $export_type == "minimal" ? 'checked' : ''; ?> class="form-check-input"> <?= __('Minimal'); ?>
            </div>
        </div>

        <?php if ($export_type == 'normal') { ?>
            <div class="row mb-2">
                <div class="col-md-4"><?= __('Export texts'); ?></div>
                <div class="col-md-4">
                    <select size="1" name="gedcom_texts" class="form-select form-select-sm">
                        <option value="yes"><?= __('Yes'); ?></option>
                        <option value="no" <?= isset($_POST['gedcom_texts']) and $_POST['gedcom_texts'] == 'no' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                    </select>
                </div>
            </div>

            <div class="row mb-2">
                <div class="col-md-4"><?= __('Export sources'); ?></div>
                <div class="col-md-4">
                    <select size="1" name="gedcom_sources" class="form-select form-select-sm">
                        <option value="yes"><?= __('Yes'); ?></option>
                        <option value="no" <?= isset($_POST['gedcom_sources']) and $_POST['gedcom_sources'] == 'no' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                    </select>
                </div>
            </div>
        <?php } ?>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Export longitude & latitude by places'); ?></div>
            <div class="col-md-4">
                <select size="1" name="gedcom_geocode" class="form-select form-select-sm">
                    <option value="yes"><?= __('Yes'); ?></option>
                    <option value="no" <?= isset($_POST['gedcom_geocode']) and $_POST['gedcom_geocode'] == 'no' ? 'selected' : ''; ?>><?= __('No'); ?></option>
                </select>
            </div>
        </div>

        <!-- Can _LOC tag be used in GEDCOM 7.x? -->
        <!-- Shared addresses are not GEDCOM compatible. Add an option for export -->
        <div class="row mb-2">
            <div class="col-md-4"><?= __('Shared addresses'); ?></div>
            <div class="col-md-8">
                <?php
                $sql = "SELECT * FROM humo_addresses WHERE address_tree_id='" . $tree_id . "' AND address_shared='1' LIMIT 0,1";
                $address = $dbh->query($sql);
                if ($address->rowCount() > 0) {
                    $selected = '';
                    if (isset($_POST['gedcom_shared_addresses']) and $_POST['gedcom_shared_addresses'] == 'standard') {
                        $selected = ' selected';
                    }
                ?>
                    <select size="1" name="gedcom_shared_addresses" class="form-select form-select-sm">
                        <option value="non_standard"><?= __('Export shared addresses'); ?></option>
                        <option value="standard" <?= $selected; ?>><?= __('Convert all shared addresses as single addresses'); ?></option>
                    </select><br>
                <?php
                    echo __('"Shared addresses" is <b>only compatible</b> with HuMo-genealogy and Haza-21 programs.<br>
Other programs: convert shared addresses. The "shared address" option will be lost.');
                } else {
                    echo __('There are no shared addresses, standard GEDCOM export is used.');
                }
                ?>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('Show export status'); ?></div>
            <div class="col-md-4">
                <select size="1" name="gedcom_status" class="form-select form-select-sm">
                    <option value="no"><?= __('No'); ?></option>
                    <option value="yes" <?= isset($_POST['gedcom_status']) and $_POST['gedcom_status'] == 'yes' ? 'selected' : ''; ?>><?= __('Yes'); ?></option>
                </select>
            </div>
        </div>

        <div class="row mb-2">
            <div class="col-md-4"><?= __('GEDCOM export'); ?></div>
            <div class="col-md-8">
                <input type="submit" name="submit_button" value="<?= __('Start export'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>
    </div>
</form>
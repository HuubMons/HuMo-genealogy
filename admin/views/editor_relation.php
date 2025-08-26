<?php

/**
 * Marriages/ relations and children list
 * 
 * When marriage is added, man is first and woman is second (this is done automatically if sexe is known!).
 * This is needed to show proper colours in graphical reports.
 */

// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$datePlace = new \Genealogy\Include\DatePlace();
$languageDate = new \Genealogy\Include\LanguageDate;
$validateGedcomnumber = new \Genealogy\Include\ValidateGedcomnumber();

// TODO: move code to model script.
if ($person->pers_fams) {
    $familyDb = $db_functions->get_family($marriage);

    $fam_kind = $familyDb->fam_kind;
    $man_gedcomnumber = $familyDb->fam_man;
    $woman_gedcomnumber = $familyDb->fam_woman;
    $fam_gedcomnumber = $familyDb->fam_gedcomnumber;
    $fam_relation_date = $familyDb->fam_relation_date;
    $fam_relation_end_date = $familyDb->fam_relation_end_date;
    // *** Check if variabele exists, needed for PHP 8.1 ***
    $fam_relation_place = '';
    if (isset($familyDb->fam_relation_place)) {
        $fam_relation_place = $familyDb->fam_relation_place;
    }
    $fam_relation_text = $editor_cls->text_show($familyDb->fam_relation_text);
    $fam_marr_notice_date = $familyDb->fam_marr_notice_date;
    $fam_marr_notice_place = '';
    if (isset($familyDb->fam_marr_notice_place)) {
        $fam_marr_notice_place = $familyDb->fam_marr_notice_place;
    }
    $fam_marr_notice_text = $editor_cls->text_show($familyDb->fam_marr_notice_text);
    $fam_marr_date = $familyDb->fam_marr_date;
    $fam_marr_place = '';
    if (isset($familyDb->fam_marr_place)) {
        $fam_marr_place = $familyDb->fam_marr_place;
    }
    $fam_marr_text = $editor_cls->text_show($familyDb->fam_marr_text);
    $fam_marr_authority = $editor_cls->text_show($familyDb->fam_marr_authority);
    $fam_man_age = $familyDb->fam_man_age;
    $fam_woman_age = $familyDb->fam_woman_age;
    $fam_marr_church_notice_date = $familyDb->fam_marr_church_notice_date;
    $fam_marr_church_notice_place = '';
    if (isset($familyDb->fam_marr_church_notice_place)) {
        $fam_marr_church_notice_place = $familyDb->fam_marr_church_notice_place;
    }
    $fam_marr_church_notice_text = $editor_cls->text_show($familyDb->fam_marr_church_notice_text);
    $fam_marr_church_date = $familyDb->fam_marr_church_date;
    $fam_marr_church_place = '';
    if (isset($familyDb->fam_marr_church_place)) {
        $fam_marr_church_place = $familyDb->fam_marr_church_place;
    }
    $fam_marr_church_text = $editor_cls->text_show($familyDb->fam_marr_church_text);
    $fam_religion = '';
    if (isset($familyDb->fam_religion)) {
        $fam_religion = $familyDb->fam_religion;
    }
    $fam_div_date = $familyDb->fam_div_date;
    $fam_div_place = '';
    if (isset($familyDb->fam_div_place)) {
        $fam_div_place = $familyDb->fam_div_place;
    }
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
    if ($fam_div_date || $fam_div_place || $fam_div_text) {
        $fam_div_no_data = true;
    }
    $fam_text = $editor_cls->text_show($familyDb->fam_text);

    $person1 = $db_functions->get_person($man_gedcomnumber); // TODO: there allready is $person for person data.
    $person2 = $db_functions->get_person($woman_gedcomnumber);
}

$hideshow = '700';
// *** Set pers_sexe for new partner ***
if ($person->pers_sexe == 'M') {
    $new_partner_sexe = 'F';
} else {
    $new_partner_sexe = 'M';
}
?>

<div class="p-1 m-2 genealogy_search">
    <?php
    if ($person->pers_fams) {
        // *** Search for own family ***
        $fams1 = explode(";", $person->pers_fams);
        $fam_count = count($fams1);
        if ($fam_count > 0) {
            for ($i = 0; $i < $fam_count; $i++) {
                $family = $dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $fams1[$i] . "'");
                $familyDb = $family->fetch(PDO::FETCH_OBJ);

                // *** Highlight selected relation if there are multiple relations ***
                $line_selected = '';
                $button_selected = 'btn-secondary';
                if ($fam_count > 1 and $familyDb->fam_gedcomnumber == $marriage) {
                    //$line_selected = 'bg-primary-subtle';
                    $button_selected = 'btn-primary';
                }
    ?>

                <div class="row mb-2 <?= $line_selected; ?>">
                    <div class="col-2">
                        <?php if ($fam_count > 1) { ?>
                            <form method="POST" action="index.php?page=editor&amp;menu_tab=marriage">
                                <input type="hidden" name="marriage_nr" value="<?= $familyDb->fam_gedcomnumber; ?>">
                                <input type="submit" name="dummy3" value="<?= __('Family') . ' ' . ($i + 1); ?>" class="btn btn-sm <?= $button_selected; ?>">
                            </form>
                        <?php } else { ?>
                            <?= __('Family'); ?>
                        <?php } ?>
                    </div>

                    <div class="col-1">
                        <?php if ($i < ($fam_count - 1)) { ?>
                            <a href="index.php?page=<?= $page; ?>&amp;person_id=<?= $person->pers_id; ?>&amp;fam_down=<?= $i; ?>&amp;fam_array=<?= $person->pers_fams; ?>">
                                <img src="images/arrow_down.gif" border="0" alt="fam_down">
                            </a>
                        <?php } else { ?>
                            &nbsp;&nbsp;&nbsp;
                        <?php
                        }
                        if ($i > 0) {
                        ?>
                            <a href="index.php?page=<?= $page; ?>&amp;person_id=<?= $person->pers_id; ?>&amp;fam_up=<?= $i; ?>&amp;fam_array=<?= $person->pers_fams; ?>">
                                <img src="images/arrow_up.gif" border="0" alt="fam_up">
                            </a>
                        <?php
                        } else {
                            //echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                        }
                        ?>
                    </div>

                    <div class="col-9">
                        <b><?= show_person($familyDb->fam_man) . ' ' . __('and') . ' ' . show_person($familyDb->fam_woman); ?></b>
                        <?php
                        if ($familyDb->fam_marr_date) {
                            echo ' X ' . $datePlace->date_place($familyDb->fam_marr_date, '');
                        }
                        ?>
                    </div>

                </div>
    <?php
            }
        }

        // *** Automatically calculate birth date if marriage date and marriage age by man is used ***
        if (
            isset($_POST["fam_man_age"]) && $_POST["fam_man_age"] != '' && $fam_marr_date != '' && $person1->pers_birth_date == '' && $person1->pers_bapt_date == ''
        ) {
            $pers_birth_date = 'ABT ' . (substr($fam_marr_date, -4) - $_POST["fam_man_age"]);
            $sql = "UPDATE humo_persons SET pers_birth_date = :pers_birth_date
                WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :man_gedcomnumber";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':pers_birth_date' => $pers_birth_date,
                ':tree_id' => $tree_id,
                ':man_gedcomnumber' => $man_gedcomnumber
            ]);
        }

        // *** Automatically calculate birth date if marriage date and marriage age by woman is used ***
        if (
            isset($_POST["fam_woman_age"]) && $_POST["fam_woman_age"] != '' && $fam_marr_date != '' && $person2->pers_birth_date == '' && $person2->pers_bapt_date == ''
        ) {
            $pers_birth_date = 'ABT ' . (substr($fam_marr_date, -4) - $_POST["fam_woman_age"]);
            $sql = "UPDATE humo_persons SET pers_birth_date = :pers_birth_date
                WHERE pers_tree_id = :tree_id AND pers_gedcomnumber = :woman_gedcomnumber";
            $stmt = $dbh->prepare($sql);
            $stmt->execute([
                ':pers_birth_date' => $pers_birth_date,
                ':tree_id' => $tree_id,
                ':woman_gedcomnumber' => $woman_gedcomnumber
            ]);
        }
    } ?>
</div>

<!-- Add new relation -->
<div class="p-1 m-2 genealogy_search">
    <div class="row mb-2">
        <div class="col-md-3"><b><?= __('Add relation'); ?></b></div>
        <div class="col-md-9">
            <a href="#" onclick="hideShow(<?= $hideshow; ?>);"><img src="images/family_connect.gif" alt="<?= __('Add relation'); ?>" title="<?= __('Add relation'); ?>"> <?= __('Add new relation to this person'); ?></a>
            (<?= trim(show_person($person->pers_gedcomnumber, false, false)); ?>)
        </div>
    </div>
</div>

<div style="display:none;" class="row<?= $hideshow; ?> p-3 m-2 genealogy_search">
    <?= add_person('partner', $new_partner_sexe); ?><br><br>
    <form method="POST" style="display: inline;" action="index.php?page=editor&amp;menu_tab=marriage#marriage" name="form4" id="form4">
        <div class="row mb-2">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <?= __('Or add relation with existing person:'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <div class="col-md-3"></div>
            <div class="col-md-6">
                <div class="input-group">
                    <input type="text" name="relation_add2" value="" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>" required class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_person_select&person=0&person_item=relation_add2&tree_id=<?= $tree_id; ?>","","<?= $field_popup; ?>")'><img src=" ../images/search.png" alt="<?= __('Search'); ?>"></a>
                </div>
            </div>
            <div class="col-md-1">
                <input type="submit" name="dummy4" value="<?= __('Add relation'); ?>" class="btn btn-sm btn-success">
            </div>
        </div>
    </form>
</div>

<!-- Marriage editor -->
<?php if ($person->pers_fams) { ?>
    <?php /*
    Don't use this link, witness buttons won't work anymore
    <form method="POST" action="index.php?page=editor&amp;menu_tab=marriage" style="display : inline;" enctype="multipart/form-data" name="form2" id="form2">
    */ ?>

    <form method="POST" action="index.php" style="display : inline;" enctype="multipart/form-data" name="form2" id="form2">
        <input type="hidden" name="page" value="editor">

        <input type="hidden" name="connect_man_old" value="<?= $man_gedcomnumber; ?>">
        <input type="hidden" name="connect_woman_old" value="<?= $woman_gedcomnumber; ?>">

        <?php if (isset($marriage)) { ?>
            <input type="hidden" name="marriage_nr" value="<?= $marriage; ?>">

            <!-- $marriage is empty by single persons -->
            <input type="hidden" name="marriage" value="<?= $marriage; ?>">
        <?php } ?>


        <?php
        if (isset($_GET['fam_remove']) || isset($_POST['fam_remove'])) {
            if (isset($_GET['fam_remove']) && $validateGedcomnumber->validate($_GET['fam_remove'])) {
                $fam_remove = $_GET['fam_remove'];
            };
            if (isset($_POST['marriage_nr']) && $validateGedcomnumber->validate($_POST['marriage_nr'])) {
                $fam_remove = $_POST['marriage_nr'];
            };

            $new_nr = $db_functions->get_family($fam_remove);
        ?>
            <div class="alert alert-danger">
                <?php if ($new_nr->fam_children) { ?>
                    <strong><?= __('If you continue, ALL children will be disconnected automatically!'); ?></strong><br>
                <?php } ?>
                <?= __('Are you sure to remove this mariage?'); ?>
                <input type="hidden" name="fam_remove3" value="<?= $fam_remove; ?>">
                <input type="submit" name="fam_remove2" value="<?= __('Yes'); ?>" class="btn btn-sm btn-danger">
                <input type="submit" name="submit" value="<?= __('No'); ?>" class="btn btn-sm btn-success ms-3">
            </div>
        <?php } ?>

        <table class="table table-light">
            <!-- Empty line in table -->
            <!-- <tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr> -->

            <thead class="table-primary">
                <tr>
                    <!-- Hide or show all hide-show items -->
                    <td id="target1">
                        <a href="#marriage" onclick="hideShowAll2();"><span id="hideshowlinkall2">[+]</span> <?= __('All'); ?></a>
                        <a name="marriage"></a>
                    </td>

                    <th id="target2" colspan="2" style="font-size: 1.5em;">
                        <input type="submit" name="marriage_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">
                        [<?= $fam_gedcomnumber; ?>] <?= show_person($man_gedcomnumber); ?> <?= __('and'); ?> <?= show_person($woman_gedcomnumber); ?>
                    </th>
                </tr>
            </thead>

            <tr>
                <td><?= ucfirst(__('marriage/ relation')); ?></td>
                <td colspan="2">
                    <?php if ($person1->pers_sexe == 'F' && $person2->pers_sexe == 'M') { ?>
                        <div class="alert alert-danger" role="alert">
                            <?= __('Person 1 should be the man. Switch person 1 and person 2.'); ?>
                            <button type="submit" name="parents_switch" title="Switch Persons" class="button"><img src="images/turn_around.gif" width="17" alt="<?= __('Switch Persons'); ?>"></button>
                        </div>
                    <?php } ?>

                    <div class="row mb-2">
                        <div class="col-md-auto">
                            <?= __('Select person 1'); ?>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="connect_man" value="<?= $man_gedcomnumber; ?>" size="5" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-auto">
                            <a href="#" onClick='window.open("index.php?page=editor_person_select&person_item=man&person=<?= $man_gedcomnumber; ?>&tree_id=<?= $tree_id; ?>","","width=500,height=500,top=100,left=100,scrollbars=yes")'>
                                <img src="../images/search.png" alt="<?= __('Search'); ?>">
                            </a>
                        </div>
                        <div class="col-md-auto">
                            <b><?= $editor_cls->show_selected_person($person1); ?></b>
                        </div>
                    </div>

                    <?= __('and'); ?>

                    <div class="row mt-3">
                        <div class="col-md-auto">
                            <?= __('Select person 2'); ?>
                        </div>
                        <div class="col-md-2">
                            <input type="text" name="connect_woman" value="<?= $woman_gedcomnumber; ?>" size="5" class="form-control form-control-sm">
                        </div>
                        <div class="col-md-auto">
                            <a href="#" onClick='window.open("index.php?page=editor_person_select&person_item=woman&person=<?= $woman_gedcomnumber; ?>&tree_id=<?= $tree_id; ?>","","width=500,height=500,top=100,left=100,scrollbars=yes")'>
                                <img src="../images/search.png" alt="<?= __('Search'); ?>">
                            </a>
                        </div>
                        <div class="col-md-auto">
                            <b><?= $editor_cls->show_selected_person($person2); ?></b>
                        </div>
                    </div>
                </td>
            </tr>

            <?php
            // *** Living together ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '6';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>

            <tr>
                <td><a name="relation"></a>
                    <!-- <a href="#marriage" onclick="hideShow(6);"><span id="hideshowlink6">[+]</span></a> -->
                    <?= __('Living together'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_relation_date, $fam_relation_place);
                    if ($fam_relation_end_date) {
                        if ($hideshow_text) {
                            $hideshow_text .= '.';
                        }
                        $hideshow_text .= ' ' . __('End living together') . ' ' . $fam_relation_end_date;
                    }
                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_relation_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }
                    echo hideshow_editor($hideshow, $hideshow_text, $fam_relation_text);
                    ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                        <div class="row mb-2">
                            <label for="fam_relation_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_relation_date, 'fam_relation_date'); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_relation_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_relation_place" value="<?= htmlspecialchars($fam_relation_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_relation_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                                </div>
                            </div>
                        </div>

                        <!-- End of living together -->
                        <div class="row mb-2">
                            <label for="fam_relation_end_date" class="col-md-3 col-form-label"><?= __('End date'); ?></label>
                            <div class="col-md-7">
                                <?= $editor_cls->date_show($fam_relation_end_date, "fam_relation_end_date"); ?>
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_relation_text && preg_match('/\R/', $fam_relation_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_relation_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_relation_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $fam_relation_text; ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_relation_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_relation_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    </span>
                </td>
            </tr>

            <?php
            // *** Marriage notice ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '7';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>
            <tr>
                <td><a name="marr_notice"></a>
                    <!-- <a href="#marriage" onclick="hideShow(7);"><span id="hideshowlink7">[+]</span></a> -->
                    <?= __('Notice of Marriage'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_marr_notice_date, $fam_marr_notice_place);
                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_marr_notice_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }
                    echo hideshow_editor($hideshow, $hideshow_text, $fam_marr_notice_text);
                    ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">
                        <div class="row mb-2">
                            <label for="fam_marr_notice_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_marr_notice_date, "fam_marr_notice_date", "", $fam_marr_notice_date_hebnight, "fam_marr_notice_date_hebnight"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_notice_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_marr_notice_place" value="<?= htmlspecialchars($fam_marr_notice_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_marr_notice_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                                </div>
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_notice_text && preg_match('/\R/', $fam_marr_notice_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_notice_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_marr_notice_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $fam_marr_notice_text; ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_marr_notice_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_marr_notice_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    </span>
                </td>
            </tr>

            <?php
            // *** Marriage ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '8';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>

            <tr>
                <td><a name="marriage_relation"></a>
                    <!-- <a href="#marriage" onclick="hideShow(8);"><span id="hideshowlink8">[+]</span></a> -->
                    <!-- <?= __('Marriage'); ?></td> -->
                    <?= ucfirst(__('marriage/ relation')); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = '';
                    if (!$fam_kind) {
                        $hideshow_text .= '<span style="background-color:#FFAA80">' . __('Marriage/ Related') . '</span>';
                    }

                    $dateplace = $datePlace->date_place($fam_marr_date, $fam_marr_place);
                    if ($dateplace) {
                        if ($hideshow_text) {
                            $hideshow_text .= ', ';
                        }
                        $hideshow_text .= $dateplace;
                    }

                    if ($fam_marr_authority) {
                        //if ($hideshow_text){
                        //  $hideshow_text.='.';
                        //}
                        $hideshow_text .= ' [' . $fam_marr_authority . ']';
                    }

                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_marr_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }
                    ?>
                    <?= hideshow_editor($hideshow, $hideshow_text, $fam_marr_text); ?>

                    <input type="submit" name="add_marriage_witness" value="<?= __('witness') . ' - ' . __('officiator'); ?>" class="btn btn-sm btn-outline-primary ms-4">

                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                        <div class="row mb-2">
                            <label for="fam_marr_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_marr_date, "fam_marr_date", "", $fam_marr_date_hebnight, "fam_marr_date_hebnight"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_marr_place" value="<?= htmlspecialchars($fam_marr_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_marr_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                                </div>
                            </div>
                        </div>

                        <!-- Age of man by marriage -->
                        <div class="row mb-2">
                            <label for="fam_man_age" class="col-md-3 col-form-label"><?= __('Age person 1'); ?></label>
                            <div class="col-md-3">
                                <div class="input-group">
                                    <input type="text" name="fam_man_age" value="<?= $fam_man_age; ?>" size="3" class="form-control form-control-sm">

                                    <!-- Help popover -->
                                    <button type="button" class="btn btn-sm btn-secondary"
                                        data-bs-toggle="popover" data-bs-placement="right" data-bs-custom-class="popover-wide"
                                        data-bs-content="<?= __('If birth year of man or woman is empty it will be calculated automatically using age by marriage.'); ?>">
                                        ?
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Age of woman by marriage -->
                        <div class="row mb-2">
                            <label for="fam_woman_age" class="col-md-3 col-form-label"><?= __('Age person 2'); ?></label>
                            <div class="col-md-3">
                                <input type="text" name="fam_woman_age" value="<?= $fam_woman_age; ?>" size="3" class="form-control form-control-sm">
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_kind" class="col-md-3 col-form-label">
                                <?php if (!$fam_kind) { ?>
                                    <span style="background-color:#FFAA80"><?= __('Marriage/ Related'); ?></span>
                                <?php } else { ?>
                                    <?= __('Marriage/ Related'); ?>
                                <?php } ?>
                            </label>
                            <div class="col-md-3">
                                <select size="1" id="fam_kind" name="fam_kind" class="form-select form-select-sm">
                                    <option value=""><?= __('Marriage/ Related'); ?></option>
                                    <option value="civil" <?= $fam_kind == 'civil' ? ' selected' : ''; ?>><?= __('Married'); ?></option>
                                    <option value="living together" <?= $fam_kind == 'living together' ? ' selected' : ''; ?>><?= __('Living together'); ?></option>
                                    <option value="living apart together" <?= $fam_kind == 'living apart together' ? ' selected' : ''; ?>><?= __('Living apart together'); ?></option>
                                    <option value="intentionally unmarried mother" <?= $fam_kind == 'intentionally unmarried mother' ? ' selected' : ''; ?>><?= __('Intentionally unmarried mother'); ?></option>
                                    <option value="homosexual" <?= $fam_kind == 'homosexual' ? ' selected' : ''; ?>><?= __('Homosexual'); ?></option>
                                    <option value="non-marital" <?= $fam_kind == 'non-marital' ? ' selected' : ''; ?>><?= __('Non_marital'); ?></option>
                                    <option value="extramarital" <?= $fam_kind == 'extramarital' ? ' selected' : ''; ?>><?= __('Extramarital'); ?></option>
                                    <option value="partners" <?= $fam_kind == 'partners' ? ' selected' : ''; ?>><?= __('Partner'); ?></option>
                                    <option value="registered" <?= $fam_kind == 'registered' ? ' selected' : ''; ?>><?= __('Registered partnership'); ?></option>
                                    <option value="unknown" <?= $fam_kind == 'unknown' ? ' selected' : ''; ?>><?= __('Unknown relation'); ?></option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_authority" class="col-md-3 col-form-label"><?= __('Registrar'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="fam_marr_authority" value="<?= $fam_marr_authority; ?>" size="60" class="form-control form-control-sm">
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_text && preg_match('/\R/', $fam_marr_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_marr_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $fam_marr_text; ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_marr_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_marr_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                    </span>
                </td>

            </tr>

            <?php
            // *** Marriage Witness ***
            echo $EditorEvent->show_event('MARR', $marriage, 'ASSO');

            // *** Religious marriage notice ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '9';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>

            <tr>
                <td><a name="marr_church_notice"></a>
                    <!-- <a href="#marriage" onclick="hideShow(9);"><span id="hideshowlink9">[+]</span></a> -->
                    <?= __('Religious Notice of Marriage'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_marr_church_notice_date, $fam_marr_church_notice_place);

                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_marr_church_notice_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }

                    echo hideshow_editor($hideshow, $hideshow_text, $fam_marr_church_notice_text);
                    ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                        <div class="row mb-2">
                            <label for="fam_marr_church_notice_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_marr_church_notice_date, "fam_marr_church_notice_date", "", $fam_marr_church_notice_date_hebnight, "fam_marr_church_notice_date_hebnight"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_church_notice_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_marr_church_notice_place" value="<?= htmlspecialchars($fam_marr_church_notice_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_marr_church_notice_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                                </div>
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_church_notice_text && preg_match('/\R/', $fam_marr_church_notice_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_church_notice_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_marr_church_notice_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $fam_marr_church_notice_text; ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_marr_church_notice_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_marr_church_notice_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                    </span>
                </td>

            </tr>

            <?php
            // *** Church marriage ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '10';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>

            <tr>
                <td><a name="marr_church"></a>
                    <?= __('Religious Marriage'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_marr_church_date, $fam_marr_church_place);

                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_marr_church_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }
                    ?>
                    <?= hideshow_editor($hideshow, $hideshow_text, $fam_marr_church_text); ?>

                    <input type="submit" name="add_marriage_witness_rel" value="<?= __('witness') . ' - ' . __('clergy'); ?>" class="btn btn-sm btn-outline-primary ms-4">

                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                        <div class="row mb-2">
                            <label for="fam_marr_church_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_marr_church_date, "fam_marr_church_date", "", $fam_marr_church_date_hebnight, "fam_marr_church_date_hebnight"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_marr_church_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_marr_church_place" value="<?= htmlspecialchars($fam_marr_church_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_marr_church_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                                </div>
                            </div>
                        </div>

                        <?php
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_church_text && preg_match('/\R/', $fam_marr_church_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_church_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_marr_church_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $fam_marr_church_text; ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_marr_church_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_marr_church_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                    </span>
                </td>

            </tr>

            <?php
            // *** Marriage Witness (church) ***
            echo $EditorEvent->show_event('MARR_REL', $marriage, 'ASSO');
            ?>

            <!-- Religion -->
            <tr>
                <td rowspan="1"><?= __('Religion'); ?></td>
                <td colspan="2">
                    <div class="row mb-2">
                        <!-- <label for="fam_marr_authority" class="col-md-3 col-form-label"><?= __('Religion'); ?></label> -->
                        <div class="col-md-7">
                            <input type="text" name="fam_religion" value="<?= htmlspecialchars($fam_religion); ?>" size="60" class="form-control form-control-sm">
                        </div>
                    </div>
                </td>
            </tr>

            <?php
            // *** Divorce ***
            // *** Use hideshow to show and hide the editor lines ***
            $hideshow = '11';
            // *** If items are missing show all editor fields ***
            $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
            ?>
            <tr>
                <td>
                    <a name="divorce"></a>
                    <!-- <a href="#marriage" onclick="hideShow(11);"><span id="hideshowlink11">[+]</span></a> -->
                    <?= __('Divorce'); ?>
                </td>

                <td colspan="2">
                    <?php
                    $hideshow_text = hideshow_date_place($fam_div_date, $fam_div_place);

                    if ($fam_div_authority) {
                        //if ($hideshow_text) $hideshow_text.='.';
                        $hideshow_text .= ' [' . $fam_div_authority . ']';
                    }

                    if ($marriage) {
                        $check_sources_text = check_sources('family', 'fam_div_source', $marriage);
                        $hideshow_text .= $check_sources_text;
                    }

                    echo hideshow_editor($hideshow, $hideshow_text, $fam_div_text);
                    ?>
                    <span class="humo row<?= $hideshow; ?>" style="margin-left:0px;display:none;">

                        <div class="row mb-2">
                            <label for="fam_div_date" class="col-md-3 col-form-label"><?= __('Date'); ?></label>
                            <div class="col-md-7">
                                <?php $editor_cls->date_show($fam_div_date, "fam_div_date"); ?>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <label for="fam_div_place" class="col-md-3 col-form-label"><?= __('Place'); ?></label>
                            <div class="col-md-7">
                                <div class="input-group">
                                    <input type="text" name="fam_div_place" value="<?= htmlspecialchars($fam_div_place); ?>" size="<?= $field_place; ?>" class="form-control form-control-sm">
                                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=2&amp;place_item=fam_div_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                                </div>
                            </div>
                        </div>

                        <?php
                        $text = '';
                        if ($fam_div_authority) {
                            $text = htmlspecialchars($fam_div_authority);
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_marr_church_text" class="col-md-3 col-form-label"><?= __('Registrar'); ?></label>
                            <div class="col-md-7">
                                <input type="text" name="fam_div_authority" value="<?= $text; ?>" size="60" class="form-control form-control-sm">
                            </div>
                        </div>

                        <?php
                        if ($fam_div_text == 'DIVORCE') {
                            // *** Hide this text, it's a hidden value for a divorce without data ***
                            $fam_div_text = '';
                        }
                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_div_text && preg_match('/\R/', $fam_div_text)) {
                            $field_text_selected = $field_text_medium;
                        }
                        ?>
                        <div class="row mb-2">
                            <label for="fam_div_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label>
                            <div class="col-md-7">
                                <textarea rows="1" name="fam_div_text" <?= $field_text_selected; ?> class="form-control form-control-sm"><?= $fam_div_text; ?></textarea>
                            </div>
                        </div>

                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <label for="fam_div_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label>
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'fam_div_source', $marriage);
                                    echo $check_sources_text;
                                    ?>
                                </div>
                            </div>
                        <?php } ?>

                    </span>
                </td>

            </tr>

            <?php
            // TODO: move to divorse lines?
            // *** Use checkbox for divorse without further data ***
            ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <input type="checkbox" name="fam_div_no_data" value="no_data" class="form-check-input" <?= $fam_div_no_data ? ' checked' : ''; ?>>
                    <?= __('Divorce (use this checkbox for a divorce without further data).'); ?>
                </td>
            </tr>

            <!-- General text by relation -->
            <tr>
                <td><a name="fam_text"></a><?= __('Text by relation'); ?></td>
                <td style="border-left:0px;">
                    <div class="row mb-2">
                        <!-- <label for="fam_relation_text" class="col-md-3 col-form-label"><?= __('Text'); ?></label> -->
                        <div class="col-md-12">
                            <textarea rows="1" name="fam_text" <?= $field_text_large; ?> class="form-control form-control-sm"><?= $fam_text; ?></textarea>
                        </div>
                    </div>

                    <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                        <div class="row mb-2">
                            <!-- <label for="fam_text_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label> -->
                            <div class="col-md-7">
                                <?php
                                source_link3('family', 'fam_text_source', $marriage);

                                if ($marriage) {
                                    $check_sources_text = check_sources('family', 'fam_text_source', $marriage);
                                    echo $check_sources_text;
                                }
                                ?>
                            </div>
                        </div>
                    <?php } ?>
                </td>
            </tr>

            <!-- Relation sources -->
            <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                <tr>
                    <td><a name="fam_source"></a><?= __('Source by relation'); ?></td>
                    <td colspan="2">
                        <?php if (isset($marriage) && !isset($_GET['add_marriage'])) { ?>
                            <div class="row mb-2">
                                <!-- <label for="family_source" class="col-md-3 col-form-label"><?= __('Source'); ?></label> -->
                                <div class="col-md-7">
                                    <?php
                                    source_link3('family', 'family_source', $marriage);

                                    if ($marriage) {
                                        $check_sources_text = check_sources('family', 'family_source', $marriage);
                                        echo $check_sources_text;
                                    }
                                    ?>
                                </div>
                            </div>
                        <?php } ?>
                    </td>
                </tr>
            <?php
            }

            // *** Picture ***
            echo $EditorEvent->show_event('family', $marriage, 'marriage_picture');

            // *** Family event editor ***
            ?>
            <tr id="event_family_link">
                <td><?= __('Events'); ?></td>
                <td colspan="2">
                    <div class="row">
                        <!-- Add relation event -->
                        <div class="col-4">
                            <select size="1" name="event_kind" aria-label="<?= __('Events'); ?>" class="form-select form-select-sm">
                                <option value="event"><?= __('Event'); ?></option>
                                <option value="URL"><?= __('URL/ Internet link'); ?></option>
                            </select>
                        </div>

                        <div class="col-3">
                            <input type="submit" name="marriage_event_add" value="<?= __('Add event'); ?>" class="btn btn-sm btn-outline-primary">

                            <!-- Help popover for events -->
                            <button type="button" class="btn btn-sm btn-secondary"
                                data-bs-toggle="popover" data-bs-placement="right" data-bs-custom-class="popover-wide"
                                data-bs-content="<?= __('For items like:') . ' ' . __('Event') . ', ' . __('Marriage contract') . ', ' . __('Marriage license') . ', ' . __('etc.'); ?>">
                                ?
                            </button>
                        </div>
                    </div>
                </td>
            </tr>
            <?php
            echo $EditorEvent->show_event('family', $marriage, 'family');

            // *** Show and edit addresses by family ***
            $connect_kind = 'family';
            $connect_sub_kind = 'family_address';
            $connect_connect_id = $marriage;
            include_once __DIR__ . '/partial/editor_addresses.php';

            // *** Show unprocessed GEDCOM tags ***
            $tag_qry = "SELECT * FROM humo_unprocessed_tags WHERE tag_tree_id='" . $tree_id . "' AND tag_rel_id='" . $familyDb->fam_id . "'";
            $tag_result = $dbh->query($tag_qry);
            //$num_rows = $tag_result->rowCount();
            $tagDb = $tag_result->fetch(PDO::FETCH_OBJ);
            if (isset($tagDb->tag_tag)) {
                $tags_array = explode('<br>', $tagDb->tag_tag);
                $num_rows = count($tags_array);
            ?>
                <tr class="humo_tags_fam">
                    <td>
                        <a href="#humo_tags_fam" onclick="hideShow(110);"><span id="hideshowlink110">[+]</span></a>
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
                </tr>
                <tr style="display:none;" class="row110">
                    <td></td>
                    <td colspan="2"><?= $tagDb->tag_tag; ?></td>
                </tr>
            <?php
            }

            // *** Show editor notes ***
            $note_connect_kind = 'family';
            include_once __DIR__ . '/partial/editor_notes.php';

            // *** Relation added by user ***
            // TODO check for 1970-01-01 00:00:01
            if ($familyDb->fam_new_user_id || $familyDb->fam_new_datetime) {
            ?>
                <tr>
                    <td><?= __('Added by'); ?></td>
                    <td colspan="2"><?= $languageDate->show_datetime($familyDb->fam_new_datetime) . ' ' . $db_functions->get_user_name($familyDb->fam_new_user_id); ?></td>
                </tr>
            <?php
            }

            // *** Relation changed by user ***
            if ($familyDb->fam_changed_user_id || $familyDb->fam_changed_datetime) {
            ?>
                <tr>
                    <td><?= __('Changed by'); ?></td>
                    <td colspan="2"><?= $languageDate->show_datetime($familyDb->fam_changed_datetime) . ' ' . $db_functions->get_user_name($familyDb->fam_changed_user_id); ?></td>
                </tr>
            <?php
            }

            // *** Extra "Save" line ***
            ?>
            <tr>
                <td></td>
                <td colspan="2">
                    <input type="submit" name="marriage_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">

                    <?= __('or'); ?>

                    <!-- Remove marriage -->
                    <?php if (isset($marriage)) { ?>
                        <input type="submit" name="fam_remove" value="<?= __('Delete relation'); ?>" class="btn btn-sm btn-secondary">
                    <?php } ?>
                </td>
            </tr>
        </table><br>
    </form>

    <?php
    if ($marriage) {
        // TODO: move to model script.
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
                    if (substr($text, 1, 1) === ' ') {
                        $day = '0' . substr($text, 0, 1);
                    } elseif (is_numeric(substr($text, 0, 2))) {
                        $day = substr($text, 0, 2);
                    } else {
                        $day = '00';
                    }
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

            //TODO only get children...
            $fam_qry = $dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $marriage . "'");
            $famDb = $fam_qry->fetch(PDO::FETCH_OBJ);
            $child_array = explode(";", $famDb->fam_children);
            $nr_children = count($child_array);
            if ($nr_children > 1) {
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
                    $sql = "UPDATE humo_families SET fam_children='" . $fam_children . "' WHERE fam_id='" . $famDb->fam_id . "'";
                    $dbh->query($sql);
                }
            }
        }

        // *** Show children ***
        $family = $dbh->query("SELECT * FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $marriage . "'");
        $familyDb = $family->fetch(PDO::FETCH_OBJ);
        if ($familyDb->fam_children) {
    ?>
            <a name="children"></a>
            <?= __('Use this icon to order children (drag and drop)'); ?>: <img src="images/drag-icon.gif" border="0" alt="<?= __('Drag to change order'); ?>" title="<?= __('Drag to change order'); ?>"><br>
            <?= __('Or automatically order children:'); ?> <a href="index.php?page=<?= $page; ?>&amp;menu_tab=marriage&amp;marriage_nr=<?= $marriage; ?>&amp;order_children=1#children">
                <?= __('Automatic order children'); ?>
            </a>
            <?php if (isset($_GET['order_children'])) { ?>
                <b><?= __('Children are re-ordered.'); ?></b>
            <?php
            }

            //echo __('Children').':<br>';
            $fam_children_array = explode(";", $familyDb->fam_children);
            ?>
            <ul id="sortable<?= $i; ?>" class="sortable-children sortable-pages list-group ui-sortable" data-family-id="<?= $familyDb->fam_id; ?>">
                <?php
                foreach ($fam_children_array as $j => $value) {
                    // *** Create new children variabele, for disconnect child ***
                    $fam_children = '';
                    foreach ($fam_children_array as $k => $value) {
                        if ($k != $j) {
                            $fam_children .= $fam_children_array[$k] . ';';
                        }
                    }
                    $fam_children = substr($fam_children, 0, -1); // *** strip last ; character ***
                ?>

                    <li class="list-group-item">
                        <div class="row">
                            <div class="col-md-1">
                                <span style="cursor:move;" id="<?= $fam_children_array[$j]; ?>" class="child-handle" data-child-index="<?= $j; ?>">
                                    <img src="images/drag-icon.gif" border="0" title="<?= __('Drag to change order (saves automatically)'); ?>" alt="<?= __('Drag to change order'); ?>">
                                </span>
                            </div>

                            <div class="col-md-1">
                                <a href="index.php?page=<?= $page; ?>&amp;family_id=<?= $familyDb->fam_id; ?>&amp;child_disconnect=<?= $fam_children; ?>&amp;child_disconnect_gedcom=<?= $fam_children_array[$j]; ?>">
                                    <img src="images/person_disconnect.gif" border="0" title="<?= __('Disconnect child'); ?>" alt="<?= __('Disconnect child'); ?>">
                                </a>
                            </div>

                            <div class="col-md-10">
                                <span id="chldnum<?= $fam_children_array[$j]; ?>">
                                    <?= ($j + 1); ?>
                                </span>
                                <?= show_person($fam_children_array[$j], true); ?>
                            </div>
                        </div>
                    </li>
                <?php } ?>
            </ul>
        <?php } ?>


        <!-- Add child -->
        <?php $hideshow = 702; ?>
        <div id="add_child" class="p-1 m-2 genealogy_search">
            <div class="row mb-2">
                <div class="col-md-3"><b><?= __('Add child'); ?></b></div>
                <div class="col-md-7">
                    <a href="#add_child" onclick="hideShow(<?= $hideshow; ?>);"><b><?= __('Add child'); ?></b></a>
                </div>
            </div>
        </div>

        <!-- <div class="p-3 m-2 genealogy_search"> -->
        <div style="display:none;" class="row<?= $hideshow; ?> p-3 m-2 genealogy_search">
            <?= add_person('child', ''); ?><br>

            <!-- Search existing person as child -->
            <form method="POST" action="index.php?page=editor&amp;menu_tab=marriage" style="display : inline;" name="form7" id="form7">
                <?php
                if (isset($familyDb->fam_children)) {
                    echo '<input type="hidden" name="children" value="' . $familyDb->fam_children . '">';
                }
                ?>
                <input type="hidden" name="family_id" value="<?= $familyDb->fam_gedcomnumber; ?>">

                <div class="row mb-2">
                    <div class="col-md-3"></div>
                    <div class="col-md-7">
                        <?= __('Or add existing person as a child:'); ?>
                    </div>
                </div>
                <div class="row mb-2">
                    <div class="col-md-3"></div>
                    <div class="col-md-6">
                        <div class="input-group">
                            <input type="text" name="child_connect2" value="" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>" required class="form-control form-control-sm">
                            <a href="#" onClick='window.open("index.php?page=editor_person_select&person=0&person_item=child_connect2&tree_id=<?= $tree_id; ?>","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                        </div>
                    </div>
                    <div class="col-md-1">
                        <input type="submit" name="dummy4" value="<?= __('Select child'); ?>" class="btn btn-sm btn-success">
                    </div>
                </div>
            </form>
        </div><br><br>

        <!-- Order children using drag and drop (using jquery and jqueryui) -->
        <script src="../assets/js/order_children.js"></script>
    <?php
    }
}

// TODO: use separate view script.
// *** New function aug. 2021: Add partner or child ***
function add_person($person_kind, $pers_sexe)
{
    global $page, $editor_cls, $field_place, $field_date, $familyDb, $marriage, $db_functions, $field_popup;

    $pers_prefix = '';
    $pers_lastname = '';

    if ($person_kind == 'partner') {
        $form = 5;
        $form_name = 'form5';
    } else {
        // *** Add child to family ***
        $form = 6;
        $form_name = 'form6';

        // *** Get default prefix and lastname ***
        if ($familyDb->fam_man) {
            $personDb = $db_functions->get_person($familyDb->fam_man);
            $pers_prefix = $personDb->pers_prefix;
            $pers_lastname = $personDb->pers_lastname;
        }
    }
    ?>

    <form method="POST" style="display: inline;" action="index.php?page=editor#marriage" name="<?= $form_name; ?>" id="<?= $form_name; ?>">
        <?php if ($person_kind != 'partner') { ?>
            <input type="hidden" name="child_connect" value="1">
            <?php if (isset($familyDb->fam_children)) { ?>
                <input type="hidden" name="children" value="<?= $familyDb->fam_children; ?>">
            <?php } ?>
            <!-- TODO check code. Both variables show the same value -->
            <input type="hidden" name="family_id" value="<?= $familyDb->fam_gedcomnumber; ?>">
            <input type="hidden" name="marriage_nr" value="<?= $marriage; ?>">
        <?php } ?>
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

        <div class="row m-2">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <h2>
                    <?= $person_kind == 'partner' ? __('Add relation') : __('Add child'); ?>
                </h2>
            </div>
        </div>

        <?php edit_firstname('pers_firstname', ''); ?>
        <?php edit_prefix('pers_prefix', $pers_prefix); ?>
        <?php edit_lastname('pers_lastname', $pers_lastname); ?>
        <?php edit_patronymic('pers_patronym', ''); ?>
        <?php edit_event_name('event_gedcom_new', 'event_event_name_new', ''); ?>
        <?php edit_privacyfilter('pers_alive', 'alive'); ?>
        <?php edit_sexe('pers_sexe', $pers_sexe); ?>

        <!-- Birth -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('born')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_birth_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_birth_date', '', '', 'pers_birth_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_birth_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_birth_place" value="" size="<?= $field_place; ?>" class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=pers_birth_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                </div>
            </div>
        </div>
        <!-- Birth time and stillborn option -->
        <?php if ($person_kind == 'child') { ?>
            <div class="row mb-2">
                <label for="pers_birth_place" class="col-sm-3 col-form-label"><?= ucfirst(__('birth time')); ?></label>
                <div class="col-md-2">
                    <input type="text" name="pers_birth_time" value="" size="<?= $field_date; ?>" class="form-control form-control-sm">
                </div>
                <div class="col-md-5">
                    <input type="checkbox" name="pers_stillborn" class="form-check-input"> <?= __('stillborn child'); ?>
                </div>
            </div>
        <?php } else { ?>
            <input type="hidden" name="pers_birth_time" value="">
        <?php } ?>

        <!-- Baptise -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('baptised')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_bapt_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_bapt_date', '', '', 'pers_bapt_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_bapt_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_bapt_place" value="" size="<?= $field_place; ?>" class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=pers_bapt_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                </div>
            </div>
        </div>

        <!-- Died -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('died')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_death_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_death_date', '', '', 'pers_death_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_bapt_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_death_place" value="" size="<?= $field_place; ?>" class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=pers_death_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                </div>
            </div>
        </div>

        <!-- Buried -->
        <div class="row mb-1 p-2 bg-primary-subtle">
            <div class="col-md-3"><?= ucfirst(__('buried')); ?></div>
        </div>
        <div class="row mb-2">
            <label for="pers_buried_date" class="col-sm-3 col-form-label"><?= __('Date'); ?></label>
            <div class="col-md-7">
                <?php $editor_cls->date_show('', 'pers_buried_date', '', '', 'pers_buried_date_hebnight'); ?>
            </div>
        </div>
        <div class="row mb-2">
            <label for="pers_buried_place" class="col-sm-3 col-form-label"><?= __('Place'); ?></label>
            <div class="col-md-7">
                <div class="input-group">
                    <input type="text" name="pers_buried_place" value="" size="<?= $field_place; ?>" class="form-control form-control-sm">
                    <a href="#" onClick='window.open("index.php?page=editor_place_select&amp;form=<?= $form; ?>&amp;place_item=pers_buried_place","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                </div>
            </div>
        </div>

        <!-- Profession -->
        <input type="hidden" name="event_date_profession_prefix" value=''>
        <input type="hidden" name="event_date_profession" value=''>
        <?php edit_profession('event_profession', ''); ?>

        <div class="row mb-2">
            <div class="col-md-3"></div>
            <div class="col-md-7">
                <?php if ($person_kind == 'partner') { ?>
                    <input type="submit" name="relation_add" value="<?= __('Add relation'); ?>" class="btn btn-sm btn-success">
                <?php } else { ?>
                    <input type="submit" name="person_add" value="<?= __('Add child'); ?>" class="btn btn-sm btn-success">
                <?php } ?>
            </div>
        </div>
    </form>
<?php
}

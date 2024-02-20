<?php
// ***********************************
// *** Marriages and children list ***
// ***********************************

// TODO check line.
if ($add_person == false) {
?>
    <table class="humo" border="1">
        <?php
        if ($person->pers_fams) {
            // *** Search for own family ***
            $fams1 = explode(";", $person->pers_fams);
            $fam_count = count($fams1);
            if ($fam_count > 0) {
                //echo '<tr><th class="table_header" colspan="4">'.ucfirst(__('marriage/ relation')).'</th></tr>';
                for ($i = 0; $i < $fam_count; $i++) {
                    $family = $dbh->query("SELECT * FROM humo_families
                        WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $fams1[$i] . "'");
                    $familyDb = $family->fetch(PDO::FETCH_OBJ);

                    // *** Highlight selected relation if there are multiple relations ***
                    $line_selected = '';
                    if ($fam_count > 1 and $familyDb->fam_gedcomnumber == $marriage) $line_selected = ' bgcolor="#99ccff"';

        ?>
                    <tr <?= $line_selected; ?>>
                        <td id="chtd1">
                            <?php if ($fam_count > 1) { ?>
                                <form method="POST" action="<?= $phpself; ?>">
                                    <input type="hidden" name="page" value="<?= $page; ?>">
                                    <input type="hidden" name="marriage_nr" value="<?= $familyDb->fam_gedcomnumber; ?>">
                                    <input type="submit" name="dummy3" value="<?= __('Select family') . ' ' . ($i + 1); ?>" class="btn btn-sm btn-secondary">
                                </form>
                            <?php
                            } else {
                                //echo ucfirst(__('marriage')).' '.($i+1);
                                echo ucfirst(__('Family')) . ' ' . ($i + 1);
                            }
                            ?>
                        </td>
                        <td id="chtd2" valign="top">
                            <?php
                            if ($i < ($fam_count - 1)) {
                                echo ' <a href="index.php?page=' . $page . '&amp;person_id=' . $person->pers_id . '&amp;fam_down=' . $i . '&amp;fam_array=' . $person->pers_fams . '"><img src="images/arrow_down.gif" border="0" alt="fam_down"></a> ';
                            } else {
                                echo '&nbsp;&nbsp;&nbsp;';
                            }
                            if ($i > 0) {
                                echo ' <a href="index.php?page=' . $page . '&amp;person_id=' . $person->pers_id . '&amp;fam_up=' . $i . '&amp;fam_array=' . $person->pers_fams . '"><img src="images/arrow_up.gif" border="0" alt="fam_up"></a> ';
                            } else {
                                //echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
                            }
                            ?>
                        </td>
                        <td id="chtd3" colspan="2">
                            <b><?= show_person($familyDb->fam_man) . ' ' . __('and') . ' ' . show_person($familyDb->fam_woman); ?></b>
                            <?php
                            if ($familyDb->fam_marr_date) {
                                echo ' X ' . date_place($familyDb->fam_marr_date, '');
                            }
                            ?>
                        </td>
                    </tr>
            <?php
                }
            }
        }

        // *** Add new relation ***
        if ($menu_tab != 'children') {
            $hideshow = '700';

            $pers_sexe = '';
            if ($person->pers_sexe == 'M') $pers_sexe = 'F';
            if ($person->pers_sexe == 'F') $pers_sexe = 'M';
            ?>
            <tr>
                <td><b><?= __('Add relation'); ?></b></td>
                <td colspan="2"><a href="#" onclick="hideShow(<?= $hideshow; ?>);"><img src="images/family_connect.gif"> <?= __('Add new relation to this person'); ?></a>
                    (<?= trim(show_person($person->pers_gedcomnumber, false, false)); ?>)
                </td>
            </tr>

            <tr style="display:none;" class="row<?= $hideshow; ?>">
                <td id="chtd1"></td>
                <td id="chtd2"></td>
                <td id="chtd3">
                    <?= add_person('partner', $pers_sexe); ?><br><br>
                    <form method="POST" style="display: inline;" action="<?= $phpself; ?>#marriage" name="form4" id="form4">
                        <input type="hidden" name="page" value="<?= $page; ?>">
                        <?= __('Or add relation with existing person:'); ?> <input type="text" name="relation_add2" value="" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>" required>
                        <a href="#" onClick='window.open("index.php?page=editor_person_select&person=0&person_item=relation_add2&tree_id=<?= $tree_id; ?>","","<?= $field_popup; ?>")'><img src=" ../images/search.png" alt="<?= __('Search'); ?>"></a>
                        <input type="submit" name="dummy4" value="<?= __('Add relation'); ?>" class="btn btn-sm btn-success">
                    </form>
                </td>
            </tr>
        <?php } ?>
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
    ?>

        <form method="POST" action="<?= $phpself; ?>" style="display : inline;" enctype="multipart/form-data" name="form2" id="form2">
            <input type="hidden" name="page" value="<?= $page; ?>">

            <?php
            if (isset($_GET['fam_remove']) or isset($_POST['fam_remove'])) {
                if (isset($_GET['fam_remove'])) {
                    $fam_remove = safe_text_db($_GET['fam_remove']);
                };
                if (isset($_POST['marriage_nr'])) {
                    $fam_remove = safe_text_db($_POST['marriage_nr']);
                };

                $new_nr = $db_functions->get_family($fam_remove);
            ?>
                <div class="alert alert-danger">
                    <?php if ($new_nr->fam_children) { ?>
                        <strong><?= __('If you continue, ALL children will be disconnected automatically!'); ?></strong><br>
                    <?php } ?>
                    <?= __('Are you sure to remove this mariage?'); ?>
                    <!-- <form method="post" action="' . $phpself . '#marriage" style="display : inline;"> -->
                    <!-- <input type="hidden" name="page" value="<?= $page; ?>"> -->
                    <input type="hidden" name="fam_remove3" value="<?= $fam_remove; ?>">
                    <input type="submit" name="fam_remove2" value="<?= __('Yes'); ?>" style="color : red; font-weight: bold;">
                    <input type="submit" name="submit" value="<?= __('No'); ?>" style="color : blue; font-weight: bold;">
                    <!-- </form> -->
                </div>
            <?php
            }

            // *** Show delete message ***
            if ($confirm_relation) {
                echo $confirm_relation;
            }
            ?>
            <table class="humo" border="1">

                <!-- Empty line in table -->
                <!-- <tr><td colspan="4" class="table_empty_line" style="border-left: solid 1px white; border-right: solid 1px white;">&nbsp;</td></tr> -->

                <tr class="table_header_large">
                    <!-- Hide or show all hide-show items -->
                    <td id="target1">
                        <a href="#marriage" onclick="hideShowAll2();"><span id="hideshowlinkall2">[+]</span> <?= __('All'); ?></a>
                        <a name="marriage"></a>
                        <?php
                        // *** Remove marriage ***
                        if (isset($marriage)) {
                            echo '<input type="submit" name="fam_remove" value="' . __('Delete relation') . '" class="btn btn-sm btn-secondary">';
                        } else {
                            echo '<br>';
                        }
                        ?>
                    </td>

                    <th id="target2" colspan="2" style="font-size: 1.5em;">
                        [<?= $fam_gedcomnumber; ?>] <?= show_person($man_gedcomnumber); ?> <?= __('and'); ?> <?= show_person($woman_gedcomnumber); ?>
                    </th>

                    <td id="target3">
                        <input type="submit" name="marriage_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">
                    </td>
                </tr>

                <?php
                if (isset($marriage)) {
                    echo '<input type="hidden" name="marriage_nr" value="' . $marriage . '">';
                }
                ?>

                <tr>
                    <td><?= ucfirst(__('marriage/ relation')); ?></td>
                    <td colspan="2">
                        <?php
                        echo __('Select person 1') . ' <input type="text" name="connect_man" value="' . $man_gedcomnumber . '" size="5">';

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
                            echo ' <button type="submit" name="parents_switch" title="Switch Persons" class="button"><img src="images/turn_around.gif" width="17"></button>';
                        }
                        echo '<br>';

                        echo __('Select person 2') . ' <input type="text" name="connect_woman" value="' . $woman_gedcomnumber . '" size="5">';

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
                        ?>
                    </td>
                    <td></td>
                </tr>

                <?php
                // *** $marriage is empty by single persons ***
                if (isset($marriage)) {
                    echo '<input type="hidden" name="marriage" value="' . $marriage . '">';
                }

                // *** Living together ***
                // *** Use hideshow to show and hide the editor lines ***
                $hideshow = '6';
                // *** If items are missing show all editor fields ***
                $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
                ?>

                <tr class="humo_color">
                    <td><a name="relation"></a>
                        <!-- <a href="#marriage" onclick="hideShow(6);"><span id="hideshowlink6">[+]</span></a> -->
                        <?= __('Living together'); ?>
                    </td>

                    <td colspan="2">
                        <?php
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
                        ?>
                    </td>
                    <td>
                        <?php
                        // *** Source by relation ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            echo source_link2('600', $marriage, 'fam_relation_source', 'relation');
                        }
                        ?>
                    </td>
                </tr>

                <?php
                // *** Show source by relation in iframe ***
                echo edit_sources('600', 'family', 'fam_relation_source', $marriage);

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
                        ?>
                    </td>

                    <td>
                        <?php
                        // *** Source by fam_marr_notice ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            echo source_link2('601', $marriage, 'fam_marr_notice_source', 'marr_notice');
                        }
                        ?>
                    </td>
                </tr>

                <?php
                // *** Show source by relation in iframe ***
                echo edit_sources('601', 'family', 'fam_marr_notice_source', $marriage);

                // *** Marriage ***
                // *** Use hideshow to show and hide the editor lines ***
                $hideshow = '8';
                // *** If items are missing show all editor fields ***
                $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
                ?>

                <tr class="humo_color">
                    <td><a name="marriage_relation"></a>
                        <!-- <a href="#marriage" onclick="hideShow(8);"><span id="hideshowlink8">[+]</span></a> -->
                        <!-- <?= __('Marriage'); ?></td> -->
                        <?= ucfirst(__('marriage/ relation')); ?>
                    </td>

                    <td colspan="2">
                        <?php
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
                        echo '&nbsp;&nbsp;<div class="' . $rtlmarker . 'sddm" style="display:inline;">';
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
                        ?>
                        <select size="1" name="fam_kind">
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
                        </select><br>

                        <?php
                        echo editor_label2(__('Registrar'));
                        echo '<input type="text" placeholder="' . __('Registrar') . '" name="fam_marr_authority" value="' . $fam_marr_authority . '" size="60"><br>';

                        // *** Check if there are multiple lines in text ***
                        $field_text_selected = $field_text;
                        if ($fam_marr_text and preg_match('/\R/', $fam_marr_text)) $field_text_selected = $field_text_medium;
                        echo editor_label2(__('text'));
                        echo '<textarea rows="1" placeholder="' . __('text') . '" name="fam_marr_text" ' . $field_text_selected . '>' . $fam_marr_text . '</textarea>';

                        echo '</span>';
                        ?>
                    </td>

                    <td>
                        <?php
                        // *** Source by fam_marr ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            echo source_link2('602', $marriage, 'fam_marr_source', 'marriage_relation');
                        }
                        ?>
                    </td>
                </tr>

                <?php
                // *** Show source by relation in iframe ***
                echo edit_sources('602', 'family', 'fam_marr_source', $marriage);

                // *** Marriage Witness ***
                echo $event_cls->show_event('family', $marriage, 'marriage_witness');

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
                        ?>
                    </td>

                    <td>
                        <?php
                        // *** Source by fam_marr_church_notice ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            echo source_link2('603', $marriage, 'fam_marr_church_notice_source', 'marr_church_notice');
                        }
                        ?>
                    </td>
                </tr>

                <?php
                // *** Show source by relation in iframe ***
                echo edit_sources('603', 'family', 'fam_marr_church_notice_source', $marriage);

                // *** Church marriage ***
                // *** Use hideshow to show and hide the editor lines ***
                $hideshow = '10';
                // *** If items are missing show all editor fields ***
                $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
                ?>

                <tr class="humo_color">
                    <td><a name="marr_church"></a>
                        <?= __('Religious Marriage'); ?>
                    </td>

                    <td colspan="2">
                        <?php
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
                        ?>
                    </td>

                    <td>
                        <?php
                        // *** Source by fam_marr_church ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            echo source_link2('604', $marriage, 'fam_marr_church_source', 'marr_church');
                        }
                        ?>
                    </td>
                </tr>
                <?php
                // *** Show source in iframe ***
                echo edit_sources('604', 'family', 'fam_marr_church_source', $marriage);

                // *** Marriage Witness (church) ***
                echo $event_cls->show_event('family', $marriage, 'marriage_witness_rel');

                // *** Religion ***
                //echo '<tr class="humo_color"><td rowspan="1">'.__('Religion').'</td>';
                ?>
                <tr class="humo_color">
                    <td rowspan="1"></td>
                    <td colspan="2">
                        <?php
                        echo editor_label2(__('Religion'));
                        echo '<input type="text" placeholder="' . __('Religion') . '" name="fam_religion" value="' . htmlspecialchars($fam_religion) . '" size="60">';
                        ?>
                    </td>
                    <td></td>
                </tr>

                <?php
                // *** Divorce ***
                // *** Use hideshow to show and hide the editor lines ***
                $hideshow = '11';
                // *** If items are missing show all editor fields ***
                $display = ' display:none;'; //if ($address3Db->address_address=='' AND $address3Db->address_place=='') $display='';
                ?>
                <tr>
                    <td><a name="divorce"></a>
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
                        ?>
                    </td>

                    <td>
                        <?php
                        // *** Source by fam_div ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            echo source_link2('605', $marriage, 'fam_div_source', 'divorce');
                        }
                        ?>
                    </td>
                </tr>
                <?php
                // *** Show source by relation in iframe ***
                echo edit_sources('605', 'family', 'fam_div_source', $marriage);

                // *** Use checkbox for divorse without further data ***
                ?>
                <tr>
                    <td></td>
                    <td colspan="2">
                        <input type="checkbox" name="fam_div_no_data" value="no_data" <?= $fam_div_no_data ? ' checked' : ''; ?>>
                        <?= __('Divorce (use this checkbox for a divorce without further data).'); ?>
                    </td>
                    <td></td>
                </tr>
                <?php

                // *** General text by relation ***
                ?>
                <tr class="humo_color">
                    <td><a name="fam_text"></a><?= __('Text by relation'); ?></td>
                    <td style="border-right:0px;"></td>
                    <td style="border-left:0px;">
                        <textarea rows="1" placeholder="<?= __('Text by relation'); ?>" name="fam_text" <?= $field_text_large; ?>><?= $fam_text; ?></textarea>
                    </td>
                    <td>
                        <?php
                        // *** Source by text ***
                        if (isset($marriage) and !isset($_GET['add_marriage'])) {
                            echo source_link2('606', $marriage, 'fam_text_source', 'fam_text');
                        }
                        ?>
                    </td>
                </tr>

                <?php
                // *** Show source by relation in iframe ***
                echo edit_sources('606', 'family', 'fam_text_source', $marriage);

                // *** Relation sources in new person editor screen ***
                if (isset($marriage) and !isset($_GET['add_marriage'])) {
                ?>
                    <tr>
                        <td><a name="fam_source"></a><?= __('Source by relation'); ?></td>
                        <td colspan="2"></td>
                        <td>
                            <?= source_link2('607', $marriage, 'family_source', 'fam_source'); ?>
                        </td>
                    </tr>
                <?php
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
                        <td></td>
                    </tr>
                    <tr style="display:none;" class="row110">
                        <td></td>
                        <td colspan="2"><?= $tagDb->tag_tag; ?></td>
                        <td></td>
                    </tr>
                <?php
                }

                // *** Show editor notes ***
                show_editor_notes('family');

                // *** Relation added by user ***
                // TODO check for 1970-01-01 00:00:01
                if ($familyDb->fam_new_user_id or $familyDb->fam_new_datetime) {
                    $user_name = '';
                    if ($familyDb->fam_new_user_id) {
                        $user_qry = "SELECT user_name FROM humo_users WHERE user_id='" . $familyDb->fam_new_user_id . "'";
                        $user_result = $dbh->query($user_qry);
                        $userDb = $user_result->fetch(PDO::FETCH_OBJ);
                        $user_name = $userDb->user_name;
                    }

                ?>
                    <tr class="table_header_large">
                        <td><?= __('Added by'); ?></td>
                        <td colspan="2"><?= show_datetime($familyDb->fam_new_datetime) . ' ' . $user_name; ?></td>
                        <td></td>
                    </tr>
                <?php
                }

                // *** Relation changed by user ***
                if ($familyDb->fam_changed_user_id or $familyDb->fam_changed_datetime) {
                    $user_name = '';
                    if ($familyDb->fam_changed_user_id) {
                        $user_qry = "SELECT user_name FROM humo_users WHERE user_id='" . $familyDb->fam_changed_user_id . "'";
                        $user_result = $dbh->query($user_qry);
                        $userDb = $user_result->fetch(PDO::FETCH_OBJ);
                        $user_name = $userDb->user_name;
                    }
                ?>
                    <tr class="table_header_large">
                        <td><?= __('Changed by'); ?></td>
                        <td colspan="2"><?= show_datetime($familyDb->fam_changed_datetime) . ' ' . $user_name; ?></td>
                        <td></td>
                    </tr>
                <?php
                }

                // *** Extra "Save" line ***
                ?>
                <tr class="table_header_large">
                    <td></td>
                    <td colspan="2"></td>
                    <td style="border-left: none; text-align:left; font-size: 1.5em;">
                        <input type="submit" name="marriage_change" value="<?= __('Save'); ?>" class="btn btn-sm btn-success">
                    </td>
                </tr>
            </table><br>
        </form>

        <?php
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

                //TODO only get children...
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
                echo __('Use this icon to order children (drag and drop)') . ': <img src="images/drag-icon.gif" border="0">';
                echo '<br>' . __('Or automatically order children:') . ' <a href="index.php?page=' . $page . '&amp;menu_tab=marriage&amp;marriage_nr=' . $marriage . '&amp;order_children=1#children">' . __('Automatic order children') . '</a>';
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

                    echo '<li><span style="cursor:move;" id="' . $fam_children_array[$j] . '" class="handle' . $i . '" ><img src="images/drag-icon.gif" border="0" title="' . __('Drag to change order (saves automatically)') . '" alt="' . __('Drag to change order') . '"></span>&nbsp;&nbsp;';

                    echo '<a href="index.php?page=' . $page . '&amp;family_id=' . $familyDb->fam_id . '&amp;child_disconnect=' . $fam_children .
                        '&amp;child_disconnect_gedcom=' . $fam_children_array[$j] . '">
                        <img src="images/person_disconnect.gif" border="0" title="' . __('Disconnect child') . '" alt="' . __('Disconnect child') . '"></a>';
                    echo '&nbsp;&nbsp;<span id="chldnum' . $fam_children_array[$j] . '">' . ($j + 1) . '</span>. ' . show_person($fam_children_array[$j], true) . '</li>';
                }
                echo '</ul>';
            }

            // *** Add child ***
            $pers_sexe = '';
            add_person('child', $pers_sexe);

            // *** Search existing person as child ***
        ?>
            <form method="POST" action="<?= $phpself; ?>" style="display : inline;" name="form7" id="form7">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <?php
                if (isset($familyDb->fam_children)) {
                    echo '<input type="hidden" name="children" value="' . $familyDb->fam_children . '">';
                }
                ?>
                <input type="hidden" name="family_id" value="<?= $familyDb->fam_gedcomnumber; ?>">
                <?= __('Or add existing person as a child:'); ?> <input type="text" name="child_connect2" value="" size="17" placeholder="<?= __('GEDCOM number (ID)'); ?>" required>
                <a href="#" onClick='window.open("index.php?page=editor_person_select&person=0&person_item=child_connect2&tree_id=<?= $tree_id; ?>","","<?= $field_popup; ?>")'><img src="../images/search.png" alt="<?= __('Search'); ?>"></a>
                <input type="submit" name="dummy4" value="<?= __('Select child'); ?>" class="btn btn-sm btn-success">
            </form><br><br>

            <!-- Order children using drag and drop using jquery and jqueryui -->
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

<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$tree_result = $db_functions->get_trees();
?>

<h1 class="center"><?= __('Calculated birth date'); ?></h1>

<?= __('Calculated birth date is an estimated/ calculated date that is used for the privacy filter.<br>
These calculated dates will be used for persons where all dates are missing (no birth, baptise, death or burial dates).<br>
Calculation will be done using birth, baptise, death, burial and marriage dates of persons and these dates of parents and children.'); ?><br><br>

<form method="POST" action="index.php">
    <input type="hidden" name="page" value="cal_date">

    <div class="row justify-content-center align-items-center"">
        <div class=" col-auto">
        <?= __('Choose family tree'); ?>
    </div>

    <div class="col-auto">
        <select size="1" name="tree_id" class="form-select form-select-sm">
            <?php
            foreach ($tree_result as $treeDb) {
                $treetext = show_tree_text($treeDb->tree_id, $selected_language);
            ?>
                <option value="<?= $treeDb->tree_id; ?>" <?= $treeDb->tree_id == $tree_id ? 'selected' : ''; ?>><?= $treetext['name']; ?></option>
            <?php } ?>
        </select>
    </div>

    <div class="col-auto">
        <input type="submit" name="submit_button" class="btn btn-sm btn-success" value="<?= __('Select'); ?>">
    </div>
    </div>
</form><br>

<?php
if (isset($_POST['submit_button']) && isset($tree_id)) {
    $db_functions->set_tree_id($tree_id);

    function calculate_person($gedcomnumber)
    {
        global $db_functions;
        $pers_cal_date = '';
        $person2_db = $db_functions->get_person($gedcomnumber);
        if ($person2_db) {
            if ($person2_db->pers_cal_date) {
                $pers_cal_date = $person2_db->pers_cal_date;
            } elseif ($person2_db->pers_birth_date) {
                $pers_cal_date = $person2_db->pers_birth_date;
            } elseif ($person2_db->pers_bapt_date) {
                $pers_cal_date = $person2_db->pers_bapt_date;
            }
            $pers_cal_date = substr($pers_cal_date, -4);
        }
        return $pers_cal_date;
    }

?>
    <!-- TODO use div instead of table -->
    <table class="humo standard" style="width:800px;" border="1">
        <tr>
            <td colspan="2">
                <?php
                // *** Process estimates/ calculated date for privacy filter ***
                $person_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND (pers_cal_date='' OR pers_cal_date IS NULL)";
                $person_result = $dbh->query($person_qry);
                while ($person_db = $person_result->fetch(PDO::FETCH_OBJ)) {
                    $pers_cal_date = '';
                    if ($person_db->pers_birth_date) {
                        $pers_cal_date = $person_db->pers_birth_date;
                    } elseif ($person_db->pers_bapt_date) {
                        $pers_cal_date = $person_db->pers_bapt_date;
                    }

                    // *** Check first marriage of person ***
                    if ($pers_cal_date == '' && $person_db->pers_fams) {
                        $marriage_array = explode(";", $person_db->pers_fams);
                        $fam_db = $db_functions->get_family($marriage_array[0]);
                        if ($fam_db->fam_marr_date) {
                            $pers_cal_date = $fam_db->fam_marr_date;
                        }
                        if ($fam_db->fam_marr_church_date) {
                            $pers_cal_date = $fam_db->fam_marr_church_date;
                        }
                        if ($pers_cal_date) {
                            $pers_cal_date = substr($pers_cal_date, -4);
                            if ($pers_cal_date !== '' && $pers_cal_date !== '0') {
                                $pers_cal_date -= 25;
                            }
                        }

                        // *** Check date of man/ wife ***
                        $gedcomnumber = $fam_db->fam_man;
                        if ($person_db->pers_gedcomnumber == $fam_db->fam_man) {
                            $gedcomnumber = $fam_db->fam_woman;
                        }
                        $pers_cal_date = calculate_person($gedcomnumber);

                        // *** Check date of children ***
                        if ($pers_cal_date == '' && $fam_db->fam_children) {
                            $children_array = explode(";", $fam_db->fam_children);
                            $pers_cal_date = calculate_person($children_array[0]);
                            if ($pers_cal_date) {
                                $pers_cal_date -= 25;
                            }
                        }
                    }

                    // *** Check marriage of parents ***
                    if ($pers_cal_date == '' && $person_db->pers_famc) {
                        $fam_qry = "SELECT fam_man, fam_woman, fam_relation_date, fam_marr_notice_date, fam_marr_date, fam_marr_church_notice_date, fam_marr_church_date, fam_div_date
                            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_gedcomnumber='" . $person_db->pers_famc . "'";
                        $fam_result = $dbh->query($fam_qry);
                        $fam_db = $fam_result->fetch(PDO::FETCH_OBJ);
                        if ($fam_db->fam_marr_date) {
                            $pers_cal_date = $fam_db->fam_marr_date;
                        }
                        if ($fam_db->fam_marr_church_date) {
                            $pers_cal_date = $fam_db->fam_marr_church_date;
                        }
                        if ($pers_cal_date) {
                            $pers_cal_date = substr($pers_cal_date, -4);
                            if ($pers_cal_date !== '' && $pers_cal_date !== '0') {
                                $pers_cal_date += 1;
                            }
                        }

                        // *** Check date of father ***
                        if ($pers_cal_date == '' && $fam_db->fam_man) {
                            $pers_cal_date = calculate_person($fam_db->fam_man);
                            if ($pers_cal_date) {
                                $pers_cal_date += 25;
                            }
                        }
                        // *** Check date of mother ***
                        if ($pers_cal_date == '' && $fam_db->fam_woman) {
                            $pers_cal_date = calculate_person($fam_db->fam_woman);
                            if ($pers_cal_date) {
                                $pers_cal_date += 25;
                            }
                        }
                    }

                    if ($pers_cal_date == '' && $person_db->pers_death_date) {
                        $pers_cal_date = substr($person_db->pers_death_date, -4);
                        if ($pers_cal_date !== '' && $pers_cal_date !== '0') {
                            $pers_cal_date -= 60;
                        }
                    }

                ?>
                    <span style="width:80px; display:inline-block;"><?= $person_db->pers_gedcomnumber; ?></span>
                <?php
                    // TODO use class to show name.
                    echo $person_db->pers_firstname . ' ' . strtolower(str_replace("_", " ", $person_db->pers_prefix)) . $person_db->pers_lastname;
                    echo ' ' . $pers_cal_date;
                    if ($pers_cal_date == '') {
                        echo '<b>' . __('No dates') . '</b>';
                    }
                    echo '<br>';

                    $sql = "UPDATE humo_persons SET pers_cal_date='" . $pers_cal_date . "' WHERE pers_tree_id='" . $tree_id . "' AND pers_id='" . $person_db->pers_id . "'";
                    $dbh->query($sql);
                }
                ?>
                <b><?= __('Calculation of birth dates is completed. Sometimes more dates will be found if calculation is restarted!'); ?></b>
            </td>
        </tr>
    </table>
<?php } ?>
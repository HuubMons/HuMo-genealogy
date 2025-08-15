<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

$tree_result = $db_functions->get_trees();

$db_functions->set_tree_id($tree_id);

function calculate_year($date)
{
    if (strlen($date) > 4) {
        return substr($date, -4);
    }
    return $date;
}

function calculate_person($db_functions, $gedcomnumber)
{
    $pers_cal_date = '';
    $person = $db_functions->get_person($gedcomnumber);
    if ($person) {
        if ($person->pers_cal_date) {
            $pers_cal_date = calculate_year($person->pers_cal_date);
        } elseif ($person->pers_birth_date) {
            $pers_cal_date = calculate_year($person->pers_birth_date);
        } elseif ($person->pers_bapt_date) {
            $pers_cal_date = calculate_year($person->pers_bapt_date);
        }
    }
    return $pers_cal_date;
}

function calculate_correction($date, $correction)
{
    if (is_numeric($date) && $correction != 0) {
        $date += $correction;
    }
    return $date;
}
?>

<h1 class="center"><?= __('Calculated birth date'); ?></h1>

<?= __('Calculated birth date is an estimated/ calculated date that is used for the privacy filter.<br>
These calculated dates will be used for persons where all dates are missing (no birth, baptise, death or burial dates).<br>
Calculation will be done using birth, baptise, death, burial and marriage dates of persons and these dates of parents and children.'); ?><br><br>

<form method="POST" action="index.php?page=cal_date">
    <div class="row justify-content-center align-items-center me-1">
        <div class=" col-auto">
            <?= __('Choose family tree'); ?>
        </div>

        <div class="col-auto">
            <select size="1" name="tree_id" aria-label="<?= __('Choose family tree'); ?>" class="form-select form-select-sm">
                <?php
                foreach ($tree_result as $treeDb) {
                    $treetext = $showTreeText->show_tree_text($treeDb->tree_id, $selected_language);
                ?>
                    <option value="<?= $treeDb->tree_id; ?>" <?= $treeDb->tree_id == $tree_id ? 'selected' : ''; ?>><?= $treetext['name']; ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-auto">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="recalculate" value="" id="flexCheckDefault">
                <label class="form-check-label" for="flexCheckDefault">
                    <?= __('Recalculate all persons'); ?>
                </label>
            </div>
        </div>

        <div class="col-auto">
            <input type="submit" name="submit_button" class="btn btn-sm btn-success" value="<?= __('Select'); ?>">
        </div>
    </div>
</form><br>

<?php if (isset($_POST['submit_button']) && isset($tree_id)) { ?>
    <table class="table">
        <tr>
            <td>
                <?php
                // *** Process estimates/ calculated date for privacy filter ***
                $person_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND (pers_cal_date='' OR pers_cal_date IS NULL)";

                if (isset($_POST['recalculate'])) {
                    $person_qry = "SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'";
                }

                $person_result = $dbh->query($person_qry);
                while ($person_db = $person_result->fetch(PDO::FETCH_OBJ)) {
                    $pers_cal_date = '';
                    $used_item = '';

                    if ($person_db->pers_birth_date) {
                        $pers_cal_date = $person_db->pers_birth_date;
                        $used_item = 'own birth date';
                    } elseif ($person_db->pers_bapt_date) {
                        $pers_cal_date = $person_db->pers_bapt_date;
                        $used_item = 'own baptise date';
                    }

                    // *** Check first marriage of person ***
                    if ($pers_cal_date == '' && $person_db->pers_fams) {
                        $marriage_array = explode(";", $person_db->pers_fams);
                        $fam_db = $db_functions->get_family($marriage_array[0]);
                        if ($fam_db->fam_marr_date) {
                            $pers_cal_date = $fam_db->fam_marr_date;
                            $pers_cal_date = calculate_year($pers_cal_date);
                            $used_item = 'own marriage date ' . $pers_cal_date;
                            $pers_cal_date = calculate_correction($pers_cal_date, -25);
                        } elseif ($fam_db->fam_marr_church_date) {
                            $pers_cal_date = $fam_db->fam_marr_church_date;
                            $pers_cal_date = calculate_year($pers_cal_date);
                            $used_item = 'own marriage religion date ' . $pers_cal_date;
                            $pers_cal_date = calculate_correction($pers_cal_date, -25);
                        }

                        // *** Check date of man/ wife ***
                        if ($pers_cal_date == '') {
                            $gedcomnumber = $fam_db->fam_man;
                            if ($person_db->pers_gedcomnumber == $fam_db->fam_man) {
                                $gedcomnumber = $fam_db->fam_woman;
                            }
                            $pers_cal_date = calculate_person($db_functions, $gedcomnumber);
                            $used_item = 'birth/ baptise date of man';
                            if ($person_db->pers_gedcomnumber == $fam_db->fam_man) {
                                $used_item = 'birth/ baptise date of wife';
                            }
                        }

                        // *** Check date of children ***
                        if ($pers_cal_date == '' && $fam_db->fam_children) {
                            $children_array = explode(";", $fam_db->fam_children);
                            $pers_cal_date = calculate_person($db_functions, $children_array[0]);
                            if ($pers_cal_date) {
                                $used_item = 'birth/ baptise date of child: ' . $pers_cal_date;
                                $pers_cal_date = calculate_correction($pers_cal_date, -25);
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
                            $pers_cal_date = calculate_year($pers_cal_date);
                            $used_item = 'marriage date of parents ' . $pers_cal_date;
                            $pers_cal_date = calculate_correction($pers_cal_date, +1);
                        }
                        if ($fam_db->fam_marr_church_date) {
                            $pers_cal_date = $fam_db->fam_marr_church_date;
                            $pers_cal_date = calculate_year($pers_cal_date);
                            $used_item = 'marriage religious date of parents ' . $pers_cal_date;
                            $pers_cal_date = calculate_correction($pers_cal_date, +1);
                        }

                        // *** Check date of father ***
                        if ($pers_cal_date == '' && $fam_db->fam_man) {
                            $pers_cal_date = calculate_person($db_functions, $fam_db->fam_man);
                            if ($pers_cal_date) {
                                $used_item = 'birt or baptise date of father ' . $pers_cal_date;
                                $pers_cal_date = calculate_correction($pers_cal_date, +25);
                            }
                        }
                        // *** Check date of mother ***
                        if ($pers_cal_date == '' && $fam_db->fam_woman) {
                            $pers_cal_date = calculate_person($db_functions, $fam_db->fam_woman);
                            if ($pers_cal_date) {
                                $used_item = 'birt or baptise date of mother ' . $pers_cal_date;
                                $pers_cal_date = calculate_correction($pers_cal_date, +25);
                            }
                        }
                    }

                    if ($pers_cal_date == '' && $person_db->pers_death_date) {
                        $pers_cal_date = substr($person_db->pers_death_date, -4, -60);
                        if ($pers_cal_date !== '' && $pers_cal_date !== '0') {
                            $used_item = 'death date of person ' . $pers_cal_date;
                            $pers_cal_date = calculate_correction($pers_cal_date, -60);
                        }
                    }
                ?>

                    <span style="width:80px; display:inline-block;"><?= $person_db->pers_gedcomnumber; ?></span>
                    <?= $person_db->pers_firstname; ?> <?= strtolower(str_replace("_", " ", $person_db->pers_prefix)); ?><?= $person_db->pers_lastname; ?> <?= $pers_cal_date; ?>
                    <?= $used_item ? ' (<i>found ' . $used_item . '</i>) ' : ''; ?>
                    <?= $pers_cal_date == '' ? '<b>' . __('No dates') . '</b>' : ''; ?><br>
                <?php
                    $dbh->query("UPDATE humo_persons SET pers_cal_date='" . $pers_cal_date . "' WHERE pers_tree_id='" . $tree_id . "' AND pers_id='" . $person_db->pers_id . "'");
                }
                ?><br>
                <b><?= __('Calculation of birth dates is completed. Sometimes more dates will be found if calculation is restarted!'); ?></b>
            </td>
        </tr>
    </table>
<?php } ?>
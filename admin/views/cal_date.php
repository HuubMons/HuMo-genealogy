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

// TODO: Replace this function by function calculate_person_with_id.
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
function calculate_person_with_id($db_functions, $person_id)
{
    $pers_cal_date = '';
    $person = $db_functions->get_person_with_id($person_id);
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

<h1 class="center"><?= $HtmlEscaper->escHtml(__('Calculated birth date')); ?></h1>

<?= __('Calculated birth date is an estimated/ calculated date that is used for the privacy filter.<br>
These calculated dates will be used for persons where all dates are missing (no birth, baptise, death or burial dates).<br>
Calculation will be done using birth, baptise, death, burial and marriage dates of persons and these dates of parents and children.'); ?><br><br>

<form method="POST" action="index.php?page=cal_date">
    <div class="row justify-content-center align-items-center me-1">
        <div class=" col-auto">
            <?= $HtmlEscaper->escHtml(__('Choose family tree')); ?>
        </div>

        <div class="col-auto">
            <select size="1" name="tree_id" aria-label="<?= $HtmlEscaper->escHtml(__('Choose family tree')); ?>" class="form-select form-select-sm">
                <?php
                foreach ($tree_result as $treeDb) {
                    $treetext = $showTreeText->show_tree_text($treeDb->tree_id, $selected_language);
                ?>
                    <option value="<?= $HtmlEscaper->escHtml((string) $treeDb->tree_id); ?>" <?= $treeDb->tree_id == $tree_id ? 'selected' : ''; ?>><?= $HtmlEscaper->escHtml((string) $treetext['name']); ?></option>
                <?php } ?>
            </select>
        </div>

        <div class="col-auto">
            <div class="form-check">
                <input type="checkbox" class="form-check-input" name="recalculate" value="" id="flexCheckDefault">
                <label class="form-check-label" for="flexCheckDefault">
                    <?= $HtmlEscaper->escHtml(__('Recalculate all persons')); ?>
                </label>
            </div>
        </div>

        <div class="col-auto">
            <input type="submit" name="submit_button" class="btn btn-sm btn-success" value="<?= $HtmlEscaper->escHtml(__('Select')); ?>">
        </div>
    </div>
</form><br>

<?php if (isset($_POST['submit_button']) && isset($tree_id)) { ?>
    <table class="table">
        <tr>
            <td>
                <?php
                // *** Process estimates/ calculated date for privacy filter ***
                $person_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND (pers_cal_date='' OR pers_cal_date IS NULL)";
                if (isset($_POST['recalculate'])) {
                    $person_qry = "SELECT pers_gedcomnumber FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'";
                }
                $person_result = $dbh->query($person_qry);
                while ($person1_db = $person_result->fetch(PDO::FETCH_OBJ)) {
                    $person_db = $db_functions->get_person($person1_db->pers_gedcomnumber);

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
                    $relation = $db_functions->get_first_relation($person_db->pers_id);
                    if (isset($relation->relation_id)) {
                        $fam_db = $db_functions->get_family_with_id($relation->relation_id);
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
                            $gedcomnumber = $fam_db->partner1_gedcomnumber;
                            if ($person_db->pers_gedcomnumber == $fam_db->partner1_gedcomnumber) {
                                $gedcomnumber = $fam_db->partner2_gedcomnumber;
                            }
                            $pers_cal_date = calculate_person($db_functions, $gedcomnumber);
                            $used_item = 'birth/ baptise date of man';
                            if ($person_db->pers_gedcomnumber == $fam_db->partner1_gedcomnumber) {
                                $used_item = 'birth/ baptise date of wife';
                            }
                        }

                        // *** Check date of children ***
                        $children = $db_functions->get_children($fam_db->fam_id);
                        if ($pers_cal_date == '' && count($children) > 0) {
                            foreach ($children as $child) {
                                $pers_cal_date = calculate_person_with_id($db_functions, $child->person_id);
                                if ($pers_cal_date) {
                                    $used_item = 'birth/ baptise date of child: ' . $pers_cal_date;
                                    $pers_cal_date = calculate_correction($pers_cal_date, -25);
                                    break;
                                }
                            }
                        }
                    }

                    // *** Check marriage of parents ***
                    if ($pers_cal_date == '' && $person_db->parent_relation_id) {
                        $fam_db =  $db_functions->get_family_with_id($person_db->parent_relation_id);

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
                        if ($pers_cal_date == '' && $fam_db->partner1_gedcomnumber) {
                            $pers_cal_date = calculate_person($db_functions, $fam_db->partner1_gedcomnumber);
                            if ($pers_cal_date) {
                                $used_item = 'birt or baptise date of father ' . $pers_cal_date;
                                $pers_cal_date = calculate_correction($pers_cal_date, +25);
                            }
                        }
                        // *** Check date of mother ***
                        if ($pers_cal_date == '' && $fam_db->partner2_gedcomnumber) {
                            $pers_cal_date = calculate_person($db_functions, $fam_db->partner2_gedcomnumber);
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

                    <span style="width:80px; display:inline-block;"><?= $HtmlEscaper->escHtml((string) $person_db->pers_gedcomnumber); ?></span>
                    <?= $HtmlEscaper->escHtml((string) $person_db->pers_firstname); ?> <?= $HtmlEscaper->escHtml(strtolower(str_replace("_", " ", (string) $person_db->pers_prefix))); ?><?= $HtmlEscaper->escHtml((string) $person_db->pers_lastname); ?> <?= $HtmlEscaper->escHtml((string) $pers_cal_date); ?>
                    <?= $used_item ? ' (<i>found ' . $HtmlEscaper->escHtml((string) $used_item) . '</i>) ' : ''; ?>
                    <?= $pers_cal_date == '' ? '<b>' . $HtmlEscaper->escHtml(__('No dates')) . '</b>' : ''; ?><br>
                <?php
                    $dbh->query("UPDATE humo_persons SET pers_cal_date='" . $pers_cal_date . "' WHERE pers_tree_id='" . $tree_id . "' AND pers_id='" . $person_db->pers_id . "'");
                }
                ?><br>
                <b><?= __('Calculation of birth dates is completed. Sometimes more dates will be found if calculation is restarted!'); ?></b>
            </td>
        </tr>
    </table>
<?php } ?>
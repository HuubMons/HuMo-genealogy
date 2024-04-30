<?php
$editor = '';
if (isset($_POST['editor'])) $editor = safe_text_db($_POST['editor']);
$limit = 50;
if (isset($_POST['limit']) and is_numeric($_POST['limit'])) $limit = safe_text_db($_POST['limit']);

$show_persons = false;
$show_families = false;
if (isset($_POST['show_persons']) and $_POST['show_persons'] == '1') $show_persons = true;
if (isset($_POST['show_families']) and $_POST['show_families'] == '1') $show_families = true;
// *** Select persons if no choice is made (first time opening this page) ***
if (!$show_persons and !$show_families) $show_persons = true;

$person_cls = new person_cls;
$row = 0;

if ($show_persons) {
    if ($editor) {
        // *** Show latest changes and additions: editor is selected ***
        // *** Remark: ordering is done in the array, but also needed here to get good results if $limit is a low value ***
        $person_qry = "(SELECT *, pers_changed_datetime AS changed_datetime
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_datetime IS NOT NULL AND pers_changed_user_id='" . $editor . "')
            UNION (SELECT *, pers_new_datetime AS changed_datetime
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_datetime IS NULL AND pers_new_user_id='" . $editor . "')
            ORDER BY changed_datetime DESC LIMIT 0," . $limit;
        //LIMIT 0,".$limit;
    } else {
        // *** Show latest changes and additions ***
        // *** Remark: ordering is done in the array, but also needed here to get good results if $limit is a low value ***
        $person_qry = "(SELECT *, pers_changed_datetime AS changed_datetime
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_datetime IS NOT NULL)
            UNION (SELECT *, pers_new_datetime AS changed_datetime
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_datetime IS NULL)
            ORDER BY changed_datetime DESC LIMIT 0," . $limit;
        //LIMIT 0,".$limit;
        //FROM humo_persons WHERE pers_tree_id='".$tree_id."')
    }

    $person_result = $dbh->query($person_qry);
    while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
        $result_array[$row][0] = __('Person');

        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
        $uri_path = '../'; // *** Needed if url_rewrite is enabled ***
        $url = $person_cls->person_url2($person->pers_tree_id, $person->pers_famc, $person->pers_fams, $person->pers_gedcomnumber);

        $text = '<a href="' . $url . '">' . $person->pers_firstname . ' ' . $person->pers_prefix . $person->pers_lastname . '</a>';

        $result_array[$row][1] = $text;

        //$text = '<nobr>' . language_date($person->pers_changed_date) . ' ' . $person->pers_changed_time . ' ' . $person->pers_changed_user . '</nobr>';
        $text = '';
        if ($person->pers_changed_datetime) {
            $user_name = '';
            if ($person->pers_changed_user_id) {
                $editor_name = $dbh->query("SELECT user_name FROM humo_users WHERE user_id='" . $person->pers_changed_user_id . "'");
                $editorDb = $editor_name->fetch(PDO::FETCH_OBJ);
                $user_name = $editorDb->user_name;
            }

            $text .= show_datetime($person->pers_changed_datetime) . ' ' . $user_name;
        }
        $result_array[$row][2] = $text;

        //$text = '<nobr>' . language_date($person->pers_new_date) . ' ' . $person->pers_new_time . ' ' . $person->pers_new_user . '</nobr>';
        $text = '';
        // TODO check if this could be added in query.
        if ($person->pers_new_datetime != '1970-01-01 00:00:01') {
            $user_name = '';
            if ($person->pers_new_user_id) {
                $editor_name = $dbh->query("SELECT user_name FROM humo_users WHERE user_id='" . $person->pers_new_user_id . "'");
                $editorDb = $editor_name->fetch(PDO::FETCH_OBJ);
                $user_name = $editorDb->user_name;
            }

            $text .= show_datetime($person->pers_new_datetime) . ' ' . $user_name;
        }
        $result_array[$row][3] = $text;

        // *** Used for ordering by date - time ***
        //$result_array[$row][4] = $person->changed_date . ' ' . $person->changed_time;
        $result_array[$row][4] = $person->changed_datetime;
        $row++;
    }
}

if ($show_families) {
    if ($editor) {
        // *** Show latest changes and additions: editor is selected ***
        // *** Remark: ordering is done in the array, but also needed here to get good results if $limit is a low value ***
        $person_qry = "(SELECT *, fam_changed_datetime AS changed_datetime
            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_changed_datetime IS NOT NULL AND fam_changed_user_id='" . $editor . "')
            UNION (SELECT *, fam_new_datetime AS changed_datetime
            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_changed_datetime IS NULL AND fam_new_user_id='" . $editor . "')
            ORDER BY changed_datetime DESC LIMIT 0," . $limit;
    } else {
        // *** Show latest changes and additions ***
        // *** Remark: ordering is done in the array, but also needed here to get good results if $limit is a low value ***
        $person_qry = "(SELECT *, fam_changed_datetime AS changed_datetime
            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_changed_datetime IS NOT NULL)
            UNION (SELECT *, fam_new_datetime AS changed_datetime
            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_changed_datetime IS NULL)
            ORDER BY changed_datetime DESC LIMIT 0," . $limit;
    }

    $person_result = $dbh->query($person_qry);
    while ($person = $person_result->fetch(PDO::FETCH_OBJ)) {
        // check if standard functions can be used.
        //$personDb=$db_functions->get_person($parent1);
        $person2_qry = "(SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_gedcomnumber='" . $person->fam_man . "')";
        $person2_result = $dbh->query($person2_qry);
        $person2 = $person2_result->fetch(PDO::FETCH_OBJ);

        if (isset($person2->pers_tree_id)) {
            $result_array[$row][0] = __('Family');

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            $uri_path = '../'; // *** Needed if url_rewrite is enabled ***
            $url = $person_cls->person_url2($person2->pers_tree_id, $person2->pers_famc, $person2->pers_fams, $person2->pers_gedcomnumber);

            $text = '<a href="' . $url . '">' . $person2->pers_firstname . ' ' . $person2->pers_prefix . $person2->pers_lastname . '</a>';
            $result_array[$row][1] = $text;

            //$text='<nobr>'.strtolower($person->fam_changed_date).' '.$person->fam_changed_time.' '.$person->fam_changed_user.'</nobr>';
            //$text = language_date($person->fam_changed_date) . ' ' . $person->fam_changed_time . ' ' . $person->fam_changed_user;
            $text = '';
            if ($person->fam_changed_datetime) {
                $user_name = '';
                if ($person->fam_changed_user_id) {
                    $editor_name = $dbh->query("SELECT user_name FROM humo_users WHERE user_id='" . $person->fam_changed_user_id . "'");
                    $editorDb = $editor_name->fetch(PDO::FETCH_OBJ);
                    $user_name = $editorDb->user_name;
                }

                $text .= show_datetime($person->fam_changed_datetime) . ' ' . $user_name;
            }
            $result_array[$row][2] = $text;

            //$text='<nobr>'.strtolower($person->fam_new_date).' '.$person->fam_new_time.' '.$person->fam_new_user.'</nobr>';
            //$text = language_date($person->fam_new_date) . ' ' . $person->fam_new_time . ' ' . $person->fam_new_user;
            $text = '';
            if ($person->fam_new_datetime != '1970-01-01 00:00:01') {
                $user_name = '';
                if ($person->fam_new_user_id) {
                    $editor_name = $dbh->query("SELECT user_name FROM humo_users WHERE user_id='" . $person->fam_new_user_id . "'");
                    $editorDb = $editor_name->fetch(PDO::FETCH_OBJ);
                    $user_name = $editorDb->user_name;
                }

                $text .= show_datetime($person->fam_new_datetime) . ' ' . $user_name;
            }
            $result_array[$row][3] = $text;

            // *** Used for ordering by date - time ***
            //$result_array[$row][4] = $person->changed_date . ' ' . $person->changed_time;
            $result_array[$row][4] = $person->changed_datetime;
            $row++;
        }
    }
}

// *** Order array ***
function cmp($a, $b)
{
    //return strcmp($a[4], $b[4]);	// ascending
    return strcmp($b[4], $a[4]);    // descending
}
usort($result_array, "cmp");

// *** Select editor ***
$editor = '';
if (isset($_POST['editor'])) $editor = safe_text_db($_POST['editor']);

// *** List of editors, depending of selected items (persons and/ or families) ***
$select_editor_qry = "(SELECT pers_new_user_id AS user FROM humo_persons WHERE pers_tree_id='" . $tree_id . "')
    UNION (SELECT pers_changed_user_id AS user FROM humo_persons WHERE pers_tree_id='" . $tree_id . "')";
if ($show_families) {
    $select_editor_qry .= " UNION (SELECT fam_new_user_id AS user FROM humo_families WHERE fam_tree_id='" . $tree_id . "')";
    $select_editor_qry .= " UNION (SELECT fam_changed_user_id AS user FROM humo_families WHERE fam_tree_id='" . $tree_id . "')";
}
$select_editor_qry .= " ORDER BY user DESC LIMIT 0,50";
$select_editor_result = $dbh->query($select_editor_qry);
?>

<h3><?= __('Latest changes'); ?></h3>

<form method="POST" action="index.php">
    <input type="hidden" name="page" value="<?= $page; ?>">
    <input type="hidden" name="tab" value="changes">

    <!-- <div class="row p-2 mb-3 mx-sm-1"> -->
    <div class="row gy-2 gx-3 align-items-center">
        <div class="col-auto">

            <div class="input-group">
                <label for="editor" class="col-sm-auto col-form-label"><?= __('Select editor:'); ?>&nbsp;</label>
                <select size="1" name="editor" id="editor" class="form-select form-select-sm">
                    <option value=""><?= __('All editors'); ?></option>
                    <?php
                    while ($select_editorDb = $select_editor_result->fetch(PDO::FETCH_OBJ)) {
                        if ($select_editorDb->user) {
                            $qry = $dbh->query("SELECT * FROM humo_users WHERE user_id='" . $select_editorDb->user . "'");
                            $editorDb = $qry->fetch(PDO::FETCH_OBJ);
                    ?>
                            <option value="<?= $select_editorDb->user; ?>" <?= $select_editorDb->user == $editor ? ' selected' : ''; ?>>
                                <?= $editorDb->user_name; ?>
                            </option>
                    <?php
                        }
                    }
                    ?>
                </select>
            </div>
        </div>

        <!-- Number of results in list -->
        <div class="col-auto">
            <div class="input-group">
                <label for="limit" class="col-sm-auto col-form-label"><?= __('Results'); ?>:&nbsp;</label>
                <select size="1" name="limit" id="limit" class="form-select form-select-sm">
                    <option value="50">50</option>
                    <option value="100" <?= $limit == 100 ? ' selected' : ''; ?>>100</option>
                    <option value="200" <?= $limit == 200 ? ' selected' : ''; ?>>200</option>
                    <option value="500" <?= $limit == 500 ? ' selected' : ''; ?>>500</option>
                </select>
            </div>
        </div>

        <div class="col-auto">
            <input type="checkbox" id="1" name="show_persons" id="show_persons" class="form-check-input" value="1" <?= $show_persons ? ' checked' : ''; ?>>
            <label class="form-check-label" for="show_persons"><?= __('Persons'); ?></label>

            <input type="checkbox" id="1" name="show_families" id="show_families" class="form-check-input ms-2" value="1" <?= $show_families ? ' checked' : ''; ?>>
            <label class="form-check-label" for="show_families"><?= __('Families'); ?></label>
        </div>

        <!-- Future options: also select sources, addresses, etc.? -->

        <div class="col-auto">
            <input type="submit" name="last_changes" class="btn btn-sm btn-success" value="<?= __('Select'); ?>">
        </div>
    </div>
</form><br>

<!-- Show results -->
<!-- <div style="margin-left:auto; margin-right:auto; height:350px; width:90%; overflow-y: scroll;"> -->
<!-- <div style="margin-left:auto; margin-right:auto; height:400px; overflow-y: scroll;"> -->
<div style="margin-left:auto; margin-right:auto;">
    <table class="humo" style="width:100%">
        <tr>
            <th style="text-align: center"><?= __('Item'); ?></th>
            <th style="text-align: center"><?= __('Changed/ Added'); ?></th>
            <th style="text-align: center"><?= __('When changed'); ?></th>
            <th style="text-align: center"><?= __('When added'); ?></th>
        </tr>

        <!-- Show results -->
        <?php for ($row = 0; $row < count($result_array); $row++) { ?>
            <tr>
                <td><?= $result_array[$row][0]; ?></td>
                <td><?= $result_array[$row][1]; ?></td>
                <td><?= $result_array[$row][2]; ?></td>
                <td><?= $result_array[$row][3]; ?></td>
            </tr>
        <?php } ?>
    </table><br><br>
</div>
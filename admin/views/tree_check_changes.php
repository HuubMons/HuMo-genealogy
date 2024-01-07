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
        $person_qry = "(SELECT *, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NOT NULL AND pers_changed_date!='' AND pers_changed_user='" . $editor . "')
            UNION (SELECT *, STR_TO_DATE(pers_new_date,'%d %b %Y') AS changed_date, pers_new_time as changed_time
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NULL AND pers_new_user='" . $editor . "')
            ORDER BY changed_date DESC, changed_time DESC LIMIT 0," . $limit;
        //LIMIT 0,".$limit;
    } else {
        // *** Show latest changes and additions ***
        // *** Remark: ordering is done in the array, but also needed here to get good results if $limit is a low value ***
        $person_qry = "(SELECT *, STR_TO_DATE(pers_changed_date,'%d %b %Y') AS changed_date, pers_changed_time as changed_time
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NOT NULL AND pers_changed_date!='')
            UNION (SELECT *, STR_TO_DATE(pers_new_date,'%d %b %Y') AS changed_date, pers_new_time as changed_time
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_changed_date IS NULL)
            ORDER BY changed_date DESC, changed_time DESC LIMIT 0," . $limit;
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

        //$text='<nobr>'.strtolower($person->pers_changed_date).' '.$person->pers_changed_time.' '.$person->pers_changed_user.'</nobr>';
        $text = '<nobr>' . language_date($person->pers_changed_date) . ' ' . $person->pers_changed_time . ' ' . $person->pers_changed_user . '</nobr>';
        $result_array[$row][2] = $text;

        //$text='<nobr>'.strtolower($person->pers_new_date).' '.$person->pers_new_time.' '.$person->pers_new_user.'</nobr>';
        $text = '<nobr>' . language_date($person->pers_new_date) . ' ' . $person->pers_new_time . ' ' . $person->pers_new_user . '</nobr>';
        $result_array[$row][3] = $text;

        // *** Used for ordering by date - time ***
        $result_array[$row][4] = $person->changed_date . ' ' . $person->changed_time;
        $row++;
    }
}

if ($show_families) {
    if ($editor) {
        // *** Show latest changes and additions: editor is selected ***
        // *** Remark: ordering is done in the array, but also needed here to get good results if $limit is a low value ***
        $person_qry = "(SELECT *, STR_TO_DATE(fam_changed_date,'%d %b %Y') AS changed_date, fam_changed_time as changed_time
            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_changed_date IS NOT NULL AND fam_changed_date!='' AND fam_changed_user='" . $editor . "')
            UNION (SELECT *, STR_TO_DATE(fam_new_date,'%d %b %Y') AS changed_date, fam_new_time as changed_time
            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_changed_date IS NULL AND fam_new_user='" . $editor . "')
            ORDER BY changed_date DESC, changed_time DESC LIMIT 0," . $limit;
    } else {
        // *** Show latest changes and additions ***
        // *** Remark: ordering is done in the array, but also needed here to get good results if $limit is a low value ***
        $person_qry = "(SELECT *, STR_TO_DATE(fam_changed_date,'%d %b %Y') AS changed_date, fam_changed_time as changed_time
            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_changed_date IS NOT NULL AND fam_changed_date!='')
            UNION (SELECT *, STR_TO_DATE(fam_new_date,'%d %b %Y') AS changed_date, fam_new_time as changed_time
            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_changed_date IS NULL)
            ORDER BY changed_date DESC, changed_time DESC LIMIT 0," . $limit;
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
            $text = '<nobr>' . language_date($person->fam_changed_date) . ' ' . $person->fam_changed_time . ' ' . $person->fam_changed_user . '</nobr>';
            $result_array[$row][2] = $text;

            //$text='<nobr>'.strtolower($person->fam_new_date).' '.$person->fam_new_time.' '.$person->fam_new_user.'</nobr>';
            $text = '<nobr>' . language_date($person->fam_new_date) . ' ' . $person->fam_new_time . ' ' . $person->fam_new_user . '</nobr>';
            $result_array[$row][3] = $text;

            // *** Used for ordering by date - time ***
            $result_array[$row][4] = $person->changed_date . ' ' . $person->changed_time;
            $row++;
        }
    }
}

echo '<h3>' . __('Latest changes') . '</h3>';

// *** Select editor ***
$editor = '';
if (isset($_POST['editor'])) $editor = safe_text_db($_POST['editor']);
echo '<form method="POST" action="index.php" style="display : inline;">';
echo '<input type="hidden" name="page" value="' . $page . '">';
echo '<input type="hidden" name="tab" value="changes">';

echo __('Select editor:');  // class "noprint" hides it when printing

// *** List of editors, depending of selected items (persons and/ or families) ***
$changes_qry = "(SELECT pers_new_user AS user FROM humo_persons WHERE pers_tree_id='" . $tree_id . "')
    UNION (SELECT pers_changed_user AS user FROM humo_persons WHERE pers_tree_id='" . $tree_id . "')";
if ($show_families) {
    $changes_qry .= " UNION (SELECT fam_new_user AS user FROM humo_families WHERE fam_tree_id='" . $tree_id . "')";
    $changes_qry .= " UNION (SELECT fam_changed_user AS user FROM humo_families WHERE fam_tree_id='" . $tree_id . "')";
}
$changes_qry .= " ORDER BY user DESC LIMIT 0,50";

$changes_result = $dbh->query($changes_qry);
echo ' <select size="1" name="editor">';
echo '<option value="">' . __('All editors') . '</option>';
while ($changeDb = $changes_result->fetch(PDO::FETCH_OBJ)) {
    if ($changeDb->user) {
        $selected = '';
        if ($changeDb->user == $editor) {
            $selected = ' selected';
        }
        echo '<option value="' . $changeDb->user . '"' . $selected . '>' . $changeDb->user . '</option>';
    }
}
echo '</select>';

// *** Number of results in list ***
echo ' ' . __('Results') . ': <select size="1" name="limit">';
echo '<option value="50">50</option>';
$selected = '';
if ($limit == 100) {
    $selected = ' selected';
}
echo '<option value="100"' . $selected . '>100</option>';
$selected = '';
if ($limit == 200) {
    $selected = ' selected';
}
echo '<option value="200"' . $selected . '>200</option>';
$selected = '';
if ($limit == 500) {
    $selected = ' selected';
}
echo '<option value="500"' . $selected . '>500</option>';
echo '</select>';

// *** Select item ***
$checked = '';
if ($show_persons) $checked = ' checked';
echo ' <input type="checkbox" id="1" name="show_persons" value="1" ' . $checked . '>' . __('Persons');
$checked = '';
if ($show_families) $checked = ' checked';
echo ' <input type="checkbox" id="1" name="show_families" value="1" ' . $checked . '>' . __('Families');
/*
$checked = ''; //if($show_sources) $checked=' checked';
echo ' <input type="checkbox" id="1" name="show_sources" value="1" '.$checked.'>'.__('Sources');
$checked = ''; //if($show_addresses) $checked=' checked';
echo ' <input type="checkbox" id="1" name="show_addresses" value="1" '.$checked.'>'.__('Addresses');
*/

echo ' <input type="Submit" name="last_changes" value="' . __('Select') . '">';
echo '</form><br><br>';
?>

<!-- Show results -->
<div style="margin-left:auto;margin-right:auto;height:350px;width:90%; overflow-y: scroll;">
    <table class="humo" style="width:100%">
        <tr>
            <th style="text-align: center"><?= __('Item'); ?></th>
            <th style="text-align: center"><?= __('Changed/ Added'); ?></th>
            <th style="text-align: center"><?= __('When changed'); ?></th>
            <th style="text-align: center"><?= __('When added'); ?></th>
        </tr>

        <?php
        // *** Order array ***
        function cmp($a, $b)
        {
            //return strcmp($a[4], $b[4]);	// ascending
            return strcmp($b[4], $a[4]);    // descending
        }
        usort($result_array, "cmp");

        // *** Show results ***
        for ($row = 0; $row < count($result_array); $row++) {
            //echo '<tr><td>!'.$result_array[$row][4].' '.$result_array[$row][0].'</td><td>'.$result_array[$row][1].'</td>';
            echo '<tr><td>' . $result_array[$row][0] . '</td><td>' . $result_array[$row][1] . '</td>';
            echo '<td>' . $result_array[$row][2] . '</td><td>' . $result_array[$row][3] . '</td></tr>';
        }
        ?>
    </table><br><br>
</div>
<?php
// TODO show fixed number of columns and lastnames. Also use pagination.

// MAIN SETTINGS
$maxcols = 3; // number of name & nr. colums in table. For example 3 means 3x name col + nr col
if (isset($_POST['maxcols'])) {
    $maxcols = $_POST['maxcols'];
}
$col_width = ((round(100 / $maxcols)) - 6) . "%";

$maxnames = 51;
if (isset($_POST['freqsurnames'])) {
    $maxnames = $_POST['freqsurnames'];
}

// *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
$personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names FROM humo_persons
    WHERE pers_tree_id='" . $tree_id . "' AND pers_lastname NOT LIKE ''
    GROUP BY pers_prefix,pers_lastname ORDER BY count_last_names DESC LIMIT 0," . $maxnames;
$person = $dbh->query($personqry);
$freq_last_names = [];
$freq_pers_prefix = [];
$freq_count_last_names = [];
while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
    $freq_last_names[] = $personDb->pers_lastname;
    $freq_pers_prefix[] = $personDb->pers_prefix;
    $freq_count_last_names[] = $personDb->count_last_names;
}
$row = round(count($freq_last_names) / $maxcols);

$processLinks = new \Genealogy\Include\ProcessLinks($uri_path);
$path_tmp = $processLinks->get_link($uri_path, 'list', $tree_id, true);
?>

<form method="POST" action="<?= $path2; ?>menu_tab=stats_surnames&amp;tree_id=<?= $tree_id; ?>" id="frqnames" class="my-3">
    <div class="mb-2 row me-1">
        <div class="col-md-1"></div>

        <div class="col-md-3 text-end">
            <?= __('Number of displayed surnames'); ?>:
        </div>

        <div class="col-md-1">
            <select size=1 class="form-select form-select-sm" name="freqsurnames" aria-label="<?= __('Select number of displayed surnames'); ?>" onChange="this.form.submit();">
                <option value="25" <?= $maxnames == 25 ? 'selected' : ''; ?>>25</option>
                <!-- 51 so no empty last field (if more names than this) -->
                <option value="51" <?= $maxnames == 51 ? 'selected' : ''; ?>>50</option>';
                <option value="75" <?= $maxnames == 75 ? 'selected' : ''; ?>>75</option>';
                <option value="100" <?= $maxnames == 100 ? 'selected' : ''; ?>>100</option>';
                <!-- 201 so no empty last field (if more names than this) -->
                <option value="201" <?= $maxnames == 201 ? 'selected' : ''; ?>>200</option>';
                <option value="300" <?= $maxnames == 300 ? 'selected' : ''; ?>>300</option>';
                <option value="100000" <?= $maxnames == 100000 ? 'selected' : ''; ?>><?= __('All'); ?></option>';
            </select>
        </div>

        <div class="col-md-3 text-end">
            <?= __('Number of columns'); ?>:
        </div>
        <div class="col-md-1">
            <select size=1 class="form-select form-select-sm" aria-label="<?= __('Select number of columns'); ?>" name="maxcols" onChange="this.form.submit();">
                <?php for ($i = 1; $i < 7; $i++) { ?>
                    <option value="<?= $i; ?>" <?= $maxcols == $i ? 'selected' : ''; ?>><?= $i; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>
</form>

<table class="table nametbl mt-4">
    <thead class="table-primary">
        <tr>
            <?php for ($x = 1; $x < $maxcols; $x++) { ?>
                <th width="<?= $col_width; ?>"><?= __('Surname'); ?></th>
                <th style="text-align:center;border-right-width:3px;width:6%"><?= __('Total'); ?></th>
            <?php } ?>
            <th width="<?= $col_width; ?>"><?= __('Surname'); ?></th>
            <th style="text-align:center;width:6%"><?= __('Total'); ?></th>
        </tr>
    </thead>

    <?php for ($i = 0; $i < $row; $i++) { ?>
        <tr>
            <?php
            for ($n = 0; $n < $maxcols; $n++) {
                $nr = $i + ($row * $n);
            ?>
                <td class="namelst">
                    <?php
                    if (isset($freq_last_names[$nr])) {
                        $top_pers_lastname = '';
                        if ($freq_pers_prefix[$nr]) {
                            $top_pers_lastname = str_replace("_", " ", $freq_pers_prefix[$nr]);
                        }
                        $top_pers_lastname .= $freq_last_names[$nr];
                        if ($user['group_kindindex'] == "j") {
                            echo '<a href="' . $path_tmp . 'pers_lastname=' . str_replace("_", " ", $freq_pers_prefix[$nr]) . str_replace("&", "|", $freq_last_names[$nr]);
                        } else {
                            $top_pers_lastname = $freq_last_names[$nr];
                            if ($freq_pers_prefix[$nr]) {
                                $top_pers_lastname .= ', ' . str_replace("_", " ", $freq_pers_prefix[$nr]);
                            }
                            echo '<a href="' . $path_tmp . 'pers_lastname=' . str_replace("&", "|", $freq_last_names[$nr]);
                            if ($freq_pers_prefix[$nr]) {
                                echo '&amp;pers_prefix=' . $freq_pers_prefix[$nr];
                            } else {
                                echo '&amp;pers_prefix=EMPTY';
                            }
                        }
                        echo '&amp;part_lastname=equals">' . $top_pers_lastname . "</a>";
                    } else {
                        echo '~';
                    }
                    ?>
                </td>

                <td class="namenr" style="text-align:center; <?= $n != $maxcols - 1 ? 'border-right-width:3px;' : ''; ?>">
                    <?php
                    if (isset($freq_last_names[$nr])) {
                        echo $freq_count_last_names[$nr];
                    } else {
                        echo '~';
                    }
                    ?>
                </td>
            <?php } ?>
        </tr>
    <?php } ?>
</table>

<!--Show gray bar in name box. Graphical indication of number of names -->
<script>
    var baseperc = <?= (int)$freq_count_last_names[0]; ?>;
</script>
<script src="assets/js/stats_graphical_bar.js"></script>
<?php
// *** Get names from database ***
$number_high = 0;

// Mons, van or: van Mons
if ($user['group_kindindex'] == "j") {
    // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
    $personqry = "SELECT pers_prefix, pers_lastname, count(pers_lastname) as count_last_names
        FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND CONCAT(pers_prefix,pers_lastname) LIKE '" . $list_names["last_name"] . "%'
        GROUP BY pers_prefix, pers_lastname ORDER BY CONCAT(pers_prefix, pers_lastname)";

    // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
    $count_qry = "SELECT pers_lastname, pers_prefix
        FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND CONCAT(pers_prefix,pers_lastname) LIKE '" . $list_names["last_name"] . "%'
        GROUP BY pers_prefix, pers_lastname";

    if ($list_names["last_name"] == 'all') {
        // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
        $personqry = "SELECT pers_prefix, pers_lastname, count(pers_lastname) as count_last_names
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_prefix, pers_lastname ORDER BY CONCAT(pers_prefix, pers_lastname)";

        // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
        $count_qry = "SELECT pers_prefix, pers_lastname FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_prefix, pers_lastname";
    }
} else {
    // *** Select alphabet first_character ***
    // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
    $personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
        FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_lastname LIKE '" . $list_names["last_name"] . "%'
        GROUP BY pers_lastname, pers_prefix";

    // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
    $count_qry = "SELECT pers_lastname, pers_prefix
        FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_lastname LIKE '" . $list_names["last_name"] . "%'
        GROUP BY pers_lastname, pers_prefix";

    if ($list_names["last_name"] == 'all') {
        // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
        $personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_lastname, pers_prefix";

        // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
        $count_qry = "SELECT pers_lastname, pers_prefix FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY pers_lastname, pers_prefix";
    }
}

// *** Add limit to query (results per page) ***
if ($list_names["max_names"] != '999') {
    $personqry .= " LIMIT " . $list_names["item"] . "," . $list_names["max_names"];
}
$person = $dbh->query($personqry);
while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
    if ($personDb->pers_lastname == '') {
        $personDb->pers_lastname = '...';
    }
    $freq_last_names[] = $personDb->pers_lastname;
    $freq_pers_prefix[] = $personDb->pers_prefix;
    $freq_count_last_names[] = $personDb->count_last_names;
    if ($personDb->count_last_names > $number_high) {
        $number_high = $personDb->count_last_names;
    }
}
if (isset($freq_last_names)) {
    $row = ceil(count($freq_last_names) / $list_names["max_cols"]);
}

// *** Total number of persons for multiple pages ***
$result = $dbh->query($count_qry);
$count_persons = $result->rowCount();

// *** If number of displayed surnames is "ALL" change value into number of surnames ***
$nr_persons = $list_names["max_names"];
if ($nr_persons == 'ALL') {
    $nr_persons = $count_persons;
}

if ($humo_option["url_rewrite"] == "j") {
    $url = $uri_path . 'list_names/' . $tree_id . '/' . $list_names["last_name"];
} else {
    $url = 'index.php?page=list_names&amp;tree_id=' . $tree_id . '&amp;last_name=' . $list_names["last_name"];
}


//*** Show number of persons and pages ***
$show_line_pages = false;
// *** Check for search results ***
if ($person->rowCount() > 0) {
    if ($humo_option["url_rewrite"] == "j") {
        $uri_path_string = $uri_path . 'list_names/' . $tree_id . '/' . $list_names["last_name"] . '?';
    } else {
        $uri_path_string = 'index.php?page=list_names&amp;last_name=' . $list_names["last_name"] . '&amp;';
    }

    // "<="
    $list_names["previous_link"] = '';
    $list_names["previous_status"] = '';
    if ($list_names["start"] > 1) {
        $show_line_pages = true;
        $calculated = ($list_names["start"] - 2) * $nr_persons;
        $list_names["previous_link"] = $uri_path_string . "start=" . ($list_names["start"] - 20) . "&amp;item=" . $calculated;
    }
    if ($list_names["start"] <= 0) {
        $list_names["start"] = 1;
    }
    if ($list_names["start"] == '1') {
        $list_names["previous_status"] = 'disabled';
    }

    // 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
    for ($i = $list_names["start"]; $i <= $list_names["start"] + 19; $i++) {
        $calculated = ($i - 1) * $nr_persons;
        if ($calculated < $count_persons) {
            $list_names["page_nr"][] = $i;
            if ($list_names["item"] == $calculated) {
                $list_names["page_link"][$i] = '';
                $list_names["page_status"][$i] = 'active';
            } else {
                $show_line_pages = true;
                $list_names["page_link"][$i] = $uri_path_string . "start=" . $list_names["start"] . "&amp;item=" . $calculated;
                $list_names["page_status"][$i] = '';
            }
        }
    }

    // "=>"
    $list_names["next_link"] = '';
    $list_names["next_status"] = '';
    $calculated = ($i - 1) * $nr_persons;
    if ($calculated < $count_persons) {
        $show_line_pages = true;
        $list_names["next_link"] = $uri_path_string . "start=" . $i . "&amp;item=" . $calculated;
    } else {
        $list_names["next_status"] = 'disabled';
    }
}
?>

<!-- <h1 class="standard_header"><?= __('Frequency of Surnames'); ?></h1> -->

<!-- Show line of first character last names -->
<div style="text-align:center" class="mt-2">
    <?php
    foreach ($list_names["alphabet_array"] as $alphabet) {
        $vars['last_name'] = $alphabet;
        $link = $link_cls->get_link($uri_path, 'list_names', $tree_id, false, $vars);
    ?>
        <a href="<?= $link; ?>"><?= $alphabet; ?></a>
    <?php
    }

    $vars['last_name'] = 'all';
    $link = $link_cls->get_link($uri_path, 'list_names', $tree_id, false, $vars);
    ?>
    <a href="<?= $link; ?>"><?= __('All names'); ?></a>
</div>

<!-- Show options line -->
<form method="POST" action="<?= $url; ?>" style="display:inline;" id="frqnames">
    <div class="row mb-3 me-1 mt-3">
        <div class="col-sm-3"></div>
        <div class="col-sm-3">
            <select size=1 name="freqsurnames" class="form-select form-select-sm" onChange="this.form.submit();">
                <option><?= __('Number of displayed surnames'); ?></option>
                <option value="25">25</option>
                <option value="51">50</option> <!-- 51 so no empty last field (if more names than this) -->
                <option value="75">75</option>
                <option value="100">100</option>
                <option value="201">200</option> <!-- 201 so no empty last field (if more names than this) -->
                <option value="300">300</option>
                <option value="999"><?= __('All'); ?></option>
            </select>
        </div>
        <div class="col-sm-3">
            <select size=1 name="maxcols" class="form-select form-select-sm" onChange="this.form.submit();">
                <option><?= __('Number of columns'); ?></option>
                <?php for ($i = 1; $i < 7; $i++) { ?>
                    <option value="<?= $i; ?>"><?= $i; ?></option>
                <?php } ?>
            </select>
        </div>
        <div class="col-sm-3"></div>
    </div>
</form>

<?php if ($show_line_pages) { ?>
    <div style="text-align:center">
        <?php $data = $list_names; ?>
        <?php include __DIR__ . '/partial/pagination.php'; ?>
    </div>
<?php } ?>

<?php $col_width = ((round(100 / $list_names["max_cols"])) - 6) . "%"; ?>
<table class="table table-sm nametbl">
    <thead class="table-primary">
        <tr>
            <?php for ($x = 1; $x < $list_names["max_cols"]; $x++) { ?>
                <th width="<?= $col_width; ?>"><?= __('Name'); ?></th>
                <th style="text-align:center;border-right-width:3px;width:6%"><?= __('Total'); ?></th>
            <?php } ?>
            <th width="<?= $col_width; ?>"><?= __('Name'); ?></th>
            <th style="text-align:center;width:6%"><?= __('Total'); ?></th>
        </tr>
    </thead>

    <?php if (isset($row)) { ?>
        <?php for ($i = 0; $i < $row; $i++) { ?>
            <tr>
                <?php
                // *** Show names in columns and rows ***
                for ($n = 0; $n < $list_names["max_cols"]; $n++) {
                    $nr = $i + ($row * $n);
                    $path_tmp = $link_cls->get_link($uri_path, 'list', $tree_id, true);

                    if (isset($freq_last_names[$nr])) {
                        $top_pers_lastname = '';
                        if ($freq_pers_prefix[$nr]) {
                            $top_pers_lastname = str_replace("_", " ", $freq_pers_prefix[$nr]);
                        }
                        $top_pers_lastname .= $freq_last_names[$nr];

                        $pers_prefix = '';
                        if ($user['group_kindindex'] == "j") {
                            $pers_lastname = str_replace("_", " ", $freq_pers_prefix[$nr]) . str_replace("&", "|", $freq_last_names[$nr]);
                        } else {
                            $top_pers_lastname = $freq_last_names[$nr];
                            if ($freq_pers_prefix[$nr]) {
                                $top_pers_lastname .= ', ' . str_replace("_", " ", $freq_pers_prefix[$nr]);
                            }
                            $pers_lastname = str_replace("&", "|", $freq_last_names[$nr]);

                            if ($freq_pers_prefix[$nr]) {
                                $pers_prefix = '&amp;pers_prefix=' . $freq_pers_prefix[$nr];
                            } else {
                                $pers_prefix = '&amp;pers_prefix=EMPTY';
                            }
                        }
                    }
                ?>
                    <td class="namelst">
                        <?php if (isset($freq_last_names[$nr])) { ?>
                            <a href="<?= $path_tmp; ?>pers_lastname=<?= $pers_lastname; ?><? $pers_prefix; ?>&amp;part_lastname=equals">
                                <?= $top_pers_lastname; ?>
                            </a>
                        <?php } else { ?>
                            -
                        <?php } ?>
                    </td>

                    <td class="namenr" style="text-align:center">
                        <?= isset($freq_last_names[$nr]) ? $freq_count_last_names[$nr] : '-'; ?>
                    </td>
                <?php } ?>
            </tr>
        <?php } ?>
    <?php } ?>
</table>

<!-- Show number of names with gray background bar -->
<script>
    var tbl = document.getElementsByClassName("nametbl")[0];
    var rws = tbl.rows;
    var baseperc = <?= $number_high; ?>;
    for (var i = 0; i < rws.length; i++) {
        var tbs = rws[i].getElementsByClassName("namenr");
        var nms = rws[i].getElementsByClassName("namelst");
        for (var x = 0; x < tbs.length; x++) {
            var percentage = parseInt(tbs[x].innerHTML, 10);
            percentage = (percentage * 100) / baseperc;
            if (percentage > 0.1) {
                nms[x].style.backgroundImage = "url(images/lightgray.png)";
                nms[x].style.backgroundSize = percentage + "%" + " 100%";
                nms[x].style.backgroundRepeat = "no-repeat";
                nms[x].style.color = "rgb(0, 140, 200)";
            }
        }
    }
</script><br><br>
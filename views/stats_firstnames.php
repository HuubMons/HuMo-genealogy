<?php
// TODO use MVC model

$maxnames = 30;
if (isset($_POST['freqfirstnames'])) {
    $maxnames = $_POST['freqfirstnames'];
}

$m_first_names = array();
$f_first_names = array();

// men
$personqry = "SELECT pers_firstname FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_sexe='M' AND pers_firstname NOT LIKE ''";
$person = $dbh->query($personqry);
while (@$personDb = $person->fetch(PDO::FETCH_OBJ)) {
    $fstname_arr = explode(" ", $personDb->pers_firstname);
    for ($s = 0; $s < count($fstname_arr); $s++) {
        $fstname_arr[$s] = str_replace(array("'", "\"", "(", ")", "[", "]", ".", ",", "\\"), array("", "", "", "", "", "", "", "", ""), $fstname_arr[$s]);
        if ($fstname_arr[$s] !== "" && is_numeric($fstname_arr[$s]) === false && $fstname_arr[$s] !== "-" && preg_match('/^[A-Z]$/', $fstname_arr[$s]) != 1) {
            if (isset($m_first_names[$fstname_arr[$s]])) {
                $m_first_names[$fstname_arr[$s]]++;
            } else {
                $m_first_names[$fstname_arr[$s]] = 1;
            }
        }
    }
}
arsort($m_first_names);
uksort(
    $m_first_names,
    function ($a, $b) use ($m_first_names) {
        if ($m_first_names[$a] === $m_first_names[$b]) {
            return strcmp($a, $b);
        }
        return ($m_first_names[$a] < $m_first_names[$b]) ? 1 : -1;
    }
);

//women
$personqry = "SELECT pers_firstname FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_sexe='F' AND pers_firstname NOT LIKE ''";
$person = $dbh->query($personqry);
while (@$personDb = $person->fetch(PDO::FETCH_OBJ)) {
    $fstname_arr = explode(" ", $personDb->pers_firstname);
    for ($s = 0; $s < count($fstname_arr); $s++) {
        $fstname_arr[$s] = str_replace(array("'", "\"", "(", ")", "[", "]", ".", ",", "\\"), array("", "", "", "", "", "", "", "", ""), $fstname_arr[$s]);
        if ($fstname_arr[$s] !== "" && is_numeric($fstname_arr[$s]) === false && $fstname_arr[$s] !== "-" && preg_match('/^[A-Z]$/', $fstname_arr[$s]) != 1) {
            if (isset($f_first_names[$fstname_arr[$s]])) {
                $f_first_names[$fstname_arr[$s]]++;
            } else {
                $f_first_names[$fstname_arr[$s]] = 1;
            }
        }
    }
}
arsort($f_first_names);
uksort(
    $f_first_names,
    function ($a, $b) use ($f_first_names) {
        if ($f_first_names[$a] === $f_first_names[$b]) {
            return strcmp($a, $b);
        }
        return ($f_first_names[$a] < $f_first_names[$b]) ? 1 : -1;
    }
);

$path_tmp = $link_cls->get_link($uri_path, 'list', $tree_id, true);

count($m_first_names) < count($f_first_names) ? $most = count($f_first_names) : $most = count($m_first_names);
if ($most > $maxnames) {
    $most = $maxnames;
}
$row = round($most / 2);
$m_keys = array_keys($m_first_names);
$f_keys = array_keys($f_first_names);
?>

<div style="text-align:center">
    <form method="POST" action="<?= $path2; ?>menu_tab=stats_firstnames&amp;tree_id=<?= $tree_id; ?> " style="display:inline;" id="frqfirnames">
        <div class="mb-2 row me-1">
            <div class="col-sm-2"></div>

            <div class="col-sm-3 text-end">
                <?= __('Number of displayed first names'); ?>:
            </div>

            <div class="col-sm-1">
                <select size=1 name="freqfirstnames" onChange="this.form.submit();" class="form-select form-select-sm">
                    <option value="30" <?= $maxnames == 30 ? 'selected' : ''; ?>>30</option>
                    <option value="50" <?= $maxnames == 50 ? 'selected' : ''; ?>>50</option>
                    <option value="75" <?= $maxnames == 75 ? 'selected' : ''; ?>>75</option>
                    <option value="100" <?= $maxnames == 100 ? 'selected' : ''; ?>>100</option>
                    <option value="200" <?= $maxnames == 200 ? 'selected' : ''; ?>>200</option>
                    <option value="300" <?= $maxnames == 300 ? 'selected' : ''; ?>>300</option>
                    <option value="100000" <?= $maxnames == 100000 ? 'selected' : ''; ?>><?= __('All'); ?></option>
                </select>
            </div>
        </div>
    </form>
</div>

<!-- <table style="width:90%;" class="humo nametbl" align="center"> -->
<table class="table nametbl mt-4">
    <thead class="table-primary">
        <tr style="height:25px">
            <th style="border-right-width:6px;width:50%" colspan="4"><span style="font-size:135%"><?= __('Male'); ?></span></th>
            <th style="width:50%" colspan="4"><span style="font-size:135%"><?= __('Female'); ?></span></th>
        </tr>
    </thead>

    <thead class="table-primary">
        <tr>
            <th width="19%"><?= __('First name'); ?></th>
            <th style="text-align:center;border-right-width:3px;width:6%"><?= __('Total'); ?></th>
            <th width="19%"><?= __('First name'); ?></th>
            <th style="text-align:center;border-right-width:6px;width:6%"><?= __('Total'); ?></th>
            <th width="19%"><?= __('First name'); ?></th>
            <th style="text-align:center;border-right-width:3px;width:6%"><?= __('Total'); ?></th>
            <th width="19%"><?= __('First name'); ?></th>
            <th style="text-align:center;width:6%"><?= __('Total'); ?></th>
        </tr>
    </thead>

    <?php for ($i = 0; $i < $row; $i++) { ?>
        <tr>
            <!-- male 1st name -->
            <td class="m_namelst">
                <?php
                if (isset($m_keys[$i]) && isset($m_first_names[$m_keys[$i]])) {
                    echo '<a href="' . $path_tmp . 'sexe=M&amp;pers_firstname=' . $m_keys[$i] . '&amp;part_firstname=contains">' . $m_keys[$i] . "</a>";
                }
                ?>
            </td>

            <!-- male 1st nr -->
            <td class="m_namenr" style="text-align:center;border-right-width:3px">
                <?php
                if (isset($m_keys[$i]) && isset($m_first_names[$m_keys[$i]])) {
                    echo $m_first_names[$m_keys[$i]];
                }
                ?>
            </td>

            <!-- male 2nd name -->
            <td class="m_namelst">
                <?php if (isset($m_keys[$i + $row]) && isset($m_first_names[$m_keys[$i + $row]])) { ?>
                    <a href="<?= $path_tmp; ?>sexe=M&amp;pers_firstname=<?= $m_keys[$i + $row]; ?>&amp;part_firstname=contains"><?= $m_keys[$i + $row]; ?></a>
                <?php } ?>
            </td>

            <!-- male 2nd nr -->
            <td class="m_namenr" style="text-align:center;border-right-width:6px">
                <?php
                if (isset($m_keys[$i + $row]) && isset($m_first_names[$m_keys[$i + $row]])) {
                    echo $m_first_names[$m_keys[$i + $row]];
                }
                ?>
            </td>

            <!-- female 1st name -->
            <td class="f_namelst">
                <?php
                if (isset($f_keys[$i]) && isset($f_first_names[$f_keys[$i]])) {
                    echo '<a href="' . $path_tmp . 'sexe=F&amp;pers_firstname=' . $f_keys[$i] . '&amp;part_firstname=contains">' . $f_keys[$i] . "</a>";
                }
                ?>
            </td>

            <!-- female 1st nr -->
            <td class="f_namenr" style="text-align:center;border-right-width:3px">
                <?php
                if (isset($f_keys[$i]) && isset($f_first_names[$f_keys[$i]])) {
                    echo $f_first_names[$f_keys[$i]];
                }
                ?>
            </td>

            <!-- female 2nd name -->
            <td class="f_namelst">
                <?php
                if (isset($f_keys[$i + $row]) && isset($f_first_names[$f_keys[$i + $row]])) {
                    echo '<a href="' . $path_tmp . 'sexe=F&amp;pers_firstname=' . $f_keys[$i + $row] . '&amp;part_firstname=contains">' . $f_keys[$i + $row] . "</a>";
                }
                ?>
            </td>

            <!-- female 2nd nr -->
            <td class="f_namenr" style="text-align:center;border-right-width:1px">
                <?php
                if (isset($f_keys[$i + $row]) && isset($f_first_names[$f_keys[$i + $row]])) {
                    echo $f_first_names[$f_keys[$i + $row]];
                }
                ?>
            </td>
        </tr>
    <?php } ?>

</table><br>

<?php
// *** Show lightgray bars ***
$m_baseperc = reset($m_first_names);
$f_baseperc = reset($f_first_names);

echo '
<script>
var tbl = document.getElementsByClassName("nametbl")[0];
var rws = tbl.rows; var m_baseperc = ' . $m_baseperc . '; var f_baseperc = ' . $f_baseperc . ';
for(var i = 0; i < rws.length; i ++) {
    var m_tbs =  rws[i].getElementsByClassName("m_namenr");
    var m_nms = rws[i].getElementsByClassName("m_namelst");
    var f_tbs =  rws[i].getElementsByClassName("f_namenr");
    var f_nms = rws[i].getElementsByClassName("f_namelst");
    for(var x = 0; x < m_tbs.length; x ++) {
        if(parseInt(m_tbs[x].innerHTML, 10) != NaN && parseInt(m_tbs[x].innerHTML, 10) > 0) {
            var percentage = parseInt(m_tbs[x].innerHTML, 10);
            percentage = (percentage * 100)/m_baseperc;
            m_nms[x].style.backgroundImage= "url(images/lightgray.png)"; 
            m_nms[x].style.backgroundSize = percentage + "%" + " 100%";
            m_nms[x].style.backgroundRepeat = "no-repeat";
            m_nms[x].style.color = "rgb(0, 140, 200)";
        }
    }
    for(var x = 0; x < f_tbs.length; x ++) {
        if(parseInt(m_tbs[x].innerHTML, 10) != NaN && parseInt(m_tbs[x].innerHTML, 10) > 0) {
            var percentage = parseInt(f_tbs[x].innerHTML, 10);
            percentage = (percentage * 100)/f_baseperc;
            f_nms[x].style.backgroundImage= "url(images/lightgray.png)"; 
            f_nms[x].style.backgroundSize = percentage + "%" + " 100%";
            f_nms[x].style.backgroundRepeat = "no-repeat";
            f_nms[x].style.color = "rgb(0, 140, 200)";
        }
    }
}
</script>';

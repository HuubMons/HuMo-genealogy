<?php
// TODO use MVC model
$HtmlEscaper = new \Genealogy\Include\HtmlEscaper();

$maxnames = 30;
if (isset($_POST['freqfirstnames']) && in_array((int)$_POST['freqfirstnames'], [30, 50, 75, 100, 200, 300, 100000], true)) {
    $maxnames = (int)$_POST['freqfirstnames'];
}

$m_first_names = array();
$f_first_names = array();

// Man
$personqry = "SELECT pers_firstname FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_sexe='M' AND pers_firstname NOT LIKE ''";
$person = $dbh->query($personqry);
while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
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

// Woman
$personqry = "SELECT pers_firstname FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_sexe='F' AND pers_firstname NOT LIKE ''";
$person = $dbh->query($personqry);
while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
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

$path_tmp = $processLinks->get_link($uri_path, 'list', $tree_id, true);
$path_tmp_url = html_entity_decode($path_tmp, ENT_QUOTES, 'UTF-8');
$form_action = $processLinks->get_link($uri_path, 'statistics', $tree_id, true) . 'menu_tab=stats_firstnames';

count($m_first_names) < count($f_first_names) ? $most = count($f_first_names) : $most = count($m_first_names);
if ($most > $maxnames) {
    $most = $maxnames;
}
$row = round($most / 2);
$m_keys = array_keys($m_first_names);
$f_keys = array_keys($f_first_names);
$m_baseperc = $m_first_names ? (int)reset($m_first_names) : 0;
$f_baseperc = $f_first_names ? (int)reset($f_first_names) : 0;
?>

<div style="text-align:center" class="my-3">
    <form method="POST" action="<?= $HtmlEscaper->escHtml($form_action); ?>" id="frqfirnames">
        <div class="mb-2 row me-1">
            <div class="col-sm-2"></div>

            <div class="col-sm-3 text-end">
                <?= __('Number of displayed first names'); ?>:
            </div>

            <div class="col-sm-1">
                <select size=1 name="freqfirstnames" aria-label="<?= __('Select number of displayed first names'); ?>" onChange="this.form.submit();" class="form-select form-select-sm">
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

<table class="table nametbl my-4">
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
                <?php if (isset($m_keys[$i]) && isset($m_first_names[$m_keys[$i]])) { ?>
                    <?php $personLink = $path_tmp_url . 'sexe=M&pers_firstname=' . rawurlencode((string)$m_keys[$i]) . '&part_firstname=contains'; ?>
                    <a href="<?= $HtmlEscaper->escHtml($personLink); ?>"><?= $HtmlEscaper->escHtml($m_keys[$i]); ?></a>
                <?php } ?>
            </td>

            <!-- male 1st nr -->
            <td class="m_namenr" style="text-align:center;border-right-width:3px">
                <?php if (isset($m_keys[$i]) && isset($m_first_names[$m_keys[$i]])) { ?>
                    <?= (int)$m_first_names[$m_keys[$i]]; ?>
                <?php } ?>
            </td>

            <!-- male 2nd name -->
            <td class="m_namelst">
                <?php if (isset($m_keys[$i + $row]) && isset($m_first_names[$m_keys[$i + $row]])) { ?>
                    <?php $personLink = $path_tmp_url . 'sexe=M&pers_firstname=' . rawurlencode((string)$m_keys[$i + $row]) . '&part_firstname=contains'; ?>
                    <a href="<?= $HtmlEscaper->escHtml($personLink); ?>"><?= $HtmlEscaper->escHtml($m_keys[$i + $row]); ?></a>
                <?php } ?>
            </td>

            <!-- male 2nd nr -->
            <td class="m_namenr" style="text-align:center;border-right-width:6px">
                <?php if (isset($m_keys[$i + $row]) && isset($m_first_names[$m_keys[$i + $row]])) { ?>
                    <?= (int)$m_first_names[$m_keys[$i + $row]]; ?>
                <?php } ?>
            </td>

            <!-- female 1st name -->
            <td class="f_namelst">
                <?php if (isset($f_keys[$i]) && isset($f_first_names[$f_keys[$i]])) { ?>
                    <?php $personLink = $path_tmp_url . 'sexe=F&pers_firstname=' . rawurlencode((string)$f_keys[$i]) . '&part_firstname=contains'; ?>
                    <a href="<?= $HtmlEscaper->escHtml($personLink); ?>"><?= $HtmlEscaper->escHtml($f_keys[$i]); ?></a>
                <?php } ?>
            </td>

            <!-- female 1st nr -->
            <td class="f_namenr" style="text-align:center;border-right-width:3px">
                <?php if (isset($f_keys[$i]) && isset($f_first_names[$f_keys[$i]])) { ?>
                    <?= (int)$f_first_names[$f_keys[$i]]; ?>
                <?php } ?>
            </td>

            <!-- female 2nd name -->
            <td class="f_namelst">
                <?php if (isset($f_keys[$i + $row]) && isset($f_first_names[$f_keys[$i + $row]])) { ?>
                    <?php $personLink = $path_tmp_url . 'sexe=F&pers_firstname=' . rawurlencode((string)$f_keys[$i + $row]) . '&part_firstname=contains'; ?>
                    <a href="<?= $HtmlEscaper->escHtml($personLink); ?>"><?= $HtmlEscaper->escHtml($f_keys[$i + $row]); ?></a>
                <?php } ?>
            </td>

            <!-- female 2nd nr -->
            <td class="f_namenr" style="text-align:center;border-right-width:1px">
                <?php if (isset($f_keys[$i + $row]) && isset($f_first_names[$f_keys[$i + $row]])) { ?>
                    <?= (int)$f_first_names[$f_keys[$i + $row]]; ?>
                <?php } ?>
            </td>
        </tr>
    <?php } ?>
</table>

<!-- Show lightgray bars -->
<script>
    var m_baseperc = <?= $m_baseperc; ?>;
    var f_baseperc = <?= $f_baseperc; ?>;
</script>
<script src="assets/js/stats_firstnames.js"></script>
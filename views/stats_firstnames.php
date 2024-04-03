<?php
function first_names($max)
{
    global $dbh, $tree_id, $language, $user, $humo_option, $uri_path, $link_cls;

    $m_first_names = array();
    $f_first_names = array();

    // men
    $personqry = "SELECT pers_firstname FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_sexe='M' AND pers_firstname NOT LIKE ''";

    $person = $dbh->query($personqry);
    while (@$personDb = $person->fetch(PDO::FETCH_OBJ)) {
        $fstname_arr = explode(" ", $personDb->pers_firstname);
        for ($s = 0; $s < count($fstname_arr); $s++) {
            $fstname_arr[$s] = str_replace(array("'", "\"", "(", ")", "[", "]", ".", ",", "\\"), array("", "", "", "", "", "", "", "", ""), $fstname_arr[$s]);
            if ($fstname_arr[$s] != "" and is_numeric($fstname_arr[$s]) === false and $fstname_arr[$s] != "-" and preg_match('/^[A-Z]$/', $fstname_arr[$s]) != 1) {
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
            if ($m_first_names[$a] == $m_first_names[$b]) {
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
            if ($fstname_arr[$s] != "" and is_numeric($fstname_arr[$s]) === false  and $fstname_arr[$s] != "-" and preg_match('/^[A-Z]$/', $fstname_arr[$s]) != 1) {
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
            if ($f_first_names[$a] == $f_first_names[$b]) {
                return strcmp($a, $b);
            }
            return ($f_first_names[$a] < $f_first_names[$b]) ? 1 : -1;
        }
    );

    $path_tmp = $link_cls->get_link($uri_path, 'list', $tree_id, true);

    count($m_first_names) < count($f_first_names) ? $most = count($f_first_names) : $most = count($m_first_names);
    if ($most > $max) $most = $max;
    $row = round($most / 2);
    $count = 0;
    $m_keys = array_keys($m_first_names);
    $f_keys = array_keys($f_first_names);

    for ($i = 0; $i < $row; $i++) {
        //male 1st name
        echo '<tr><td class="m_namelst">';
        if (isset($m_keys[$i]) and isset($m_first_names[$m_keys[$i]])) {
            echo '<a href="' . $path_tmp . 'sexe=M&amp;pers_firstname=' . $m_keys[$i] . '&amp;part_firstname=contains">' . $m_keys[$i] . "</a>";
        }
        //male 1st nr
        echo '</td><td class="m_namenr" style="text-align:center;border-right-width:3px">';
        if (isset($m_keys[$i]) and isset($m_first_names[$m_keys[$i]])) {
            echo $m_first_names[$m_keys[$i]];
        }
        //male 2nd name
        echo '</td><td class="m_namelst">';
        if (isset($m_keys[$i + $row]) and isset($m_first_names[$m_keys[$i + $row]])) {
            echo '<a href="' . $path_tmp . 'sexe=M&amp;pers_firstname=' . $m_keys[$i + $row] . '&amp;part_firstname=contains">' . $m_keys[$i + $row] . "</a>";
        }
        //male 2nd nr
        echo '</td><td class="m_namenr" style="text-align:center;border-right-width:6px">';
        if (isset($m_keys[$i + $row]) and isset($m_first_names[$m_keys[$i + $row]])) {
            echo $m_first_names[$m_keys[$i + $row]];
        }
        //female 1st name
        echo '</td><td class="f_namelst">';
        if (isset($f_keys[$i]) and isset($f_first_names[$f_keys[$i]])) {
            echo '<a href="' . $path_tmp . 'sexe=F&amp;pers_firstname=' . $f_keys[$i] . '&amp;part_firstname=contains">' . $f_keys[$i] . "</a>";
        }
        //female 1st nr
        echo '</td><td class="f_namenr" style="text-align:center;border-right-width:3px">';
        if (isset($f_keys[$i]) and isset($f_first_names[$f_keys[$i]])) {
            echo $f_first_names[$f_keys[$i]];
        }
        //female 2nd name
        echo '</td><td class="f_namelst">';
        if (isset($f_keys[$i + $row]) and isset($f_first_names[$f_keys[$i + $row]])) {
            echo '<a href="' . $path_tmp . 'sexe=F&amp;pers_firstname=' . $f_keys[$i + $row] . '&amp;part_firstname=contains">' . $f_keys[$i + $row] . "</a>";
        }
        //female 2nd nr
        echo '</td><td class="f_namenr" style="text-align:center;border-right-width:1px">';
        if (isset($f_keys[$i + $row]) and isset($f_first_names[$f_keys[$i + $row]])) {
            echo $f_first_names[$f_keys[$i + $row]];
        }
        echo '</td></tr>';
    }
    return reset($m_first_names) . "@" . reset($f_first_names);
}

$maxnames = 30;
if (isset($_POST['freqfirstnames'])) {
    $maxnames = $_POST['freqfirstnames'];
}
?>
<div style="text-align:center">
    <form method="POST" action="<?= $path2; ?>menu_tab=stats_firstnames&amp;tree_id=<?= $tree_id; ?> " style="display:inline;" id="frqfirnames">

        <div class="mb-2 row">
            <div class="col-sm-2"></div>

            <div class="col-sm-3 text-end">
                <?= __('Number of displayed first names'); ?>:
            </div>

            <div class="col-sm-1">
                <select size=1 name="freqfirstnames" onChange="this.form.submit();" class="form-select form-select-sm">
                    <?php
                    $selected = '';
                    if ($maxnames == 30) $selected = " selected ";
                    echo '<option value="30" ' . $selected . '>30</option>';
                    $selected = '';
                    if ($maxnames == 50) $selected = " selected ";
                    echo '<option value="50" ' . $selected . '">50</option>';
                    $selected = '';
                    if ($maxnames == 76) $selected = " selected ";
                    echo '<option value="76" ' . $selected . '">75</option>';
                    $selected = '';
                    if ($maxnames == 100) $selected = " selected ";
                    echo '<option value="100" ' . $selected . '">100</option>';
                    $selected = '';
                    if ($maxnames == 200) $selected = " selected ";
                    echo '<option value="200" ' . $selected . '">200</option>';
                    $selected = '';
                    if ($maxnames == 300) $selected = " selected ";
                    echo '<option value="300" ' . $selected . '">300</option>';
                    $selected = '';
                    if ($maxnames == 100000) $selected = " selected ";
                    echo '<option value="100000" ' . $selected . '">' . __('All') . '</option>';
                    ?>
                </select>
            </div>
        </div>
    </form>
</div><br>

<table style="width:90%;" class="humo nametbl" align="center">
    <tr class=table_headline style="height:25px">
        <th style="border-right-width:6px;width:50%" colspan="4"><span style="font-size:135%"><?= __('Male'); ?></span></th>
        <th style="width:50%" colspan="4"><span style="font-size:135%"><?= __('Female'); ?></span></th>
    </tr>
    <tr class=table_headline>
        <th width="19%"><?= __('First name'); ?></th>
        <th style="text-align:center;font-size:90%;border-right-width:3px;width:6%"><?= __('Total'); ?></th>
        <th width="19%"><?= __('First name'); ?></th>
        <th style="text-align:center;font-size:90%;border-right-width:6px;width:6%"><?= __('Total'); ?></th>
        <th width="19%"><?= __('First name'); ?></th>
        <th style="text-align:center;font-size:90%;border-right-width:3px;width:6%"><?= __('Total'); ?></th>
        <th width="19%"><?= __('First name'); ?></th>
        <th style="text-align:center;font-size:90%;width:6%"><?= __('Total'); ?></th>
    </tr>
    <!-- displays table and gets return value -->
    <?php $baseperc = first_names($maxnames); ?>
</table><br>

<?php
// *** Show lightgray bars ***
$baseperc_arr = explode("@", $baseperc);
$m_baseperc = $baseperc_arr[0];  // nr of occurrences for most frequent male name - becomes 100%
$f_baseperc = $baseperc_arr[1];    // nr of occurrences for most frequent female name - becomes 100%
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

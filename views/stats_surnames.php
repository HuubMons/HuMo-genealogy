<?php
// MAIN SETTINGS
$maxcols = 3; // number of name&nr colums in table. For example 3 means 3x name col + nr col
if (isset($_POST['maxcols'])) {
    $maxcols = $_POST['maxcols'];
}

function tablerow($nr, $lastcol = false)
{
    // displays one set of name & nr column items in the row
    // $nr is the array number of the name set created in function last_names
    // if $lastcol is set to true, the last right border of the number column will not be made thicker (as the other ones are to distinguish between the name&nr sets)
    global $user, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $tree_id;
    $path_tmp = CMS_ROOTPATH . 'list.php?tree_id=' . $tree_id;
    echo '<td class="namelst">';
    if (isset($freq_last_names[$nr])) {
        $top_pers_lastname = '';
        if ($freq_pers_prefix[$nr]) {
            $top_pers_lastname = str_replace("_", " ", $freq_pers_prefix[$nr]);
        }
        $top_pers_lastname .= $freq_last_names[$nr];
        if ($user['group_kindindex'] == "j") {
            echo '<a href="' . $path_tmp . '&amp;pers_lastname=' . str_replace("_", " ", $freq_pers_prefix[$nr]) . str_replace("&", "|", $freq_last_names[$nr]);
        } else {
            $top_pers_lastname = $freq_last_names[$nr];
            if ($freq_pers_prefix[$nr]) {
                $top_pers_lastname .= ', ' . str_replace("_", " ", $freq_pers_prefix[$nr]);
            }
            echo '<a href="' . $path_tmp . '&amp;pers_lastname=' . str_replace("&", "|", $freq_last_names[$nr]);
            if ($freq_pers_prefix[$nr]) {
                echo '&amp;pers_prefix=' . $freq_pers_prefix[$nr];
            } else {
                echo '&amp;pers_prefix=EMPTY';
            }
        }
        echo '&amp;part_lastname=equals">' . $top_pers_lastname . "</a>";
    } else echo '~';
    echo '</td>';

    if ($lastcol == false)  echo '<td class="namenr" style="text-align:center;border-right-width:3px">'; // not last column numbers
    else echo '</td><td class="namenr" style="text-align:center">'; // no thick border

    if (isset($freq_last_names[$nr])) echo $freq_count_last_names[$nr];
    else echo '~';
    echo '</td>';
}

function last_names($max)
{
    global $dbh, $tree_id, $language, $user, $humo_option, $uri_path, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $maxcols;
    // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
    $personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
        FROM humo_persons
        WHERE pers_tree_id='" . $tree_id . "' AND pers_lastname NOT LIKE ''
        GROUP BY pers_prefix,pers_lastname ORDER BY count_last_names DESC LIMIT 0," . $max;
    $person = $dbh->query($personqry);
    while (@$personDb = $person->fetch(PDO::FETCH_OBJ)) {
        $freq_last_names[] = $personDb->pers_lastname;
        $freq_pers_prefix[] = $personDb->pers_prefix;
        $freq_count_last_names[] = $personDb->count_last_names;
    }
    $row = round(count($freq_last_names) / $maxcols);

    for ($i = 0; $i < $row; $i++) {
        echo '<tr>';
        for ($n = 0; $n < $maxcols; $n++) {
            if ($n == $maxcols - 1) {
                tablerow($i + ($row * $n), true); // last col
            } else {
                tablerow($i + ($row * $n)); // other cols
            }
        }
        echo '</tr>';
    }
    return $freq_count_last_names[0];
}

//echo '<h1 class="standard_header">'.__('Frequency of Surnames').'</h1>';

$maxnames = 51;
if (isset($_POST['freqsurnames'])) {
    $maxnames = $_POST['freqsurnames'];
}
?>
<div style="text-align:center">
    <form method="POST" action="<?= $path2; ?>menu_tab=stats_surnames&amp;tree_id=<?= $tree_id; ?>" style="display:inline;" id="frqnames">
        <?php
        echo __('Number of displayed surnames');
        echo ': <select size=1 name="freqsurnames" onChange="this.form.submit();" style="width: 50px; height:20px;">';
        $selected = '';
        if ($maxnames == 25) $selected = " selected ";
        echo '<option value="25" ' . $selected . '>25</option>';
        $selected = '';
        if ($maxnames == 51) $selected = " selected ";
        echo '<option value="51" ' . $selected . '>50</option>'; // 51 so no empty last field (if more names than this)
        $selected = '';
        if ($maxnames == 75) $selected = " selected ";
        echo '<option value="75" ' . $selected . '>75</option>';
        $selected = '';
        if ($maxnames == 100) $selected = " selected ";
        echo '<option value="100" ' . $selected . '>100</option>';
        $selected = '';
        if ($maxnames == 201) $selected = " selected ";
        echo '<option value="201" ' . $selected . '>200</option>'; // 201 so no empty last field (if more names than this)
        $selected = '';
        if ($maxnames == 300) $selected = " selected ";
        echo '<option value="300" ' . $selected . '>300</option>';
        $selected = '';
        if ($maxnames == 100000) $selected = " selected ";
        echo '<option value="100000" ' . $selected . '">' . __('All') . '</option>';
        echo '</select>';

        echo '&nbsp;&nbsp;&nbsp;&nbsp;' . __('Number of columns');
        echo ': <select size=1 name="maxcols" onChange="this.form.submit();" style="width: 50px; height:20px;">';
        for ($i = 1; $i < 7; $i++) {
            $selected = '';
            if ($maxcols == $i) $selected = " selected ";
            echo '<option value="' . $i . '" ' . $selected . '>' . $i . '</option>';
        }
        echo '</select>';
        ?>
    </form>
</div>

<?php $col_width = ((round(100 / $maxcols)) - 6) . "%"; ?>
<br>
<table style="width:90%;" class="humo nametbl" align="center">
    <tr class=table_headline>
        <?php
        for ($x = 1; $x < $maxcols; $x++) {
            echo '<th width="' . $col_width . '">' . __('Surname') . '</th><th style="text-align:center;font-size:90%;border-right-width:3px;width:6%">' . __('Total') . '</th>';
        }
        echo '<th width="' . $col_width . '">' . __('Surname') . '</th><th style="text-align:center;font-size:90%;width:6%">' . __('Total') . '</th>';
        ?>
    </tr>
    <!-- displays the table and sets the $baseperc (= the name with highest frequency that will be 100%) -->
    <?php $baseperc = last_names($maxnames); ?>
</table>
<?php

echo '
<script>
var tbl = document.getElementsByClassName("nametbl")[0];
var rws = tbl.rows; var baseperc = ' . $baseperc . ';
for(var i = 0; i < rws.length; i ++) {
    var tbs =  rws[i].getElementsByClassName("namenr");
    var nms = rws[i].getElementsByClassName("namelst");
    for(var x = 0; x < tbs.length; x ++) {
        var percentage = parseInt(tbs[x].innerHTML, 10);
        percentage = (percentage * 100)/baseperc;  
        if(percentage > 0.1) {
            nms[x].style.backgroundImage= "url(images/lightgray.png)"; 
            nms[x].style.backgroundSize = percentage + "%" + " 100%";
            nms[x].style.backgroundRepeat = "no-repeat";
            nms[x].style.color = "rgb(0, 140, 200)";
        }
    }
}
</script>';

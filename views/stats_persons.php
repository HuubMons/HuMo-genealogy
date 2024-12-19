<?php
function convert_date_number($date)
{
    //31 SEP 2010 -> 20100931
    // *** Remove ABT from date ***
    $date = str_replace("ABT ", "", $date);
    $date = str_replace("EST ABT ", "", $date);
    $date = str_replace("CAL ABT ", "", $date);
    $date = str_replace("AFT ", "", $date);
    $date = str_replace("BEF ", "", $date);
    $date = str_replace("EST ", "", $date);
    $date = str_replace("CAL ", "", $date);
    //$date=str_replace(" BC", "", $date);
    //$date=str_replace(" B.C.", "", $date);

    // Remove first part from date period. BET MAY 1979 AND AUG 1979 => AUG 1979.
    if (strstr($date, ' AND ')) {
        $date = strstr($date, ' AND ');
        $date = str_replace(" AND ", "", $date);
    }
    // Remove first part from date period. FROM APR 2000 TO 5 MAR 2001 => 5 MAR 2001.
    if (strstr($date, ' TO ')) {
        $date = strstr($date, ' TO ');
        $date = str_replace(" TO ", "", $date);
    }
    /* 
    // *** Check for year only ***
    if (strlen($date)=='4' AND is_numeric($date)) $date='01 JUN '.$date; // 1887 -> 01 JUN 1887
    if (strlen($date)=='8') $date='15 '.$date; // AUG 1887 -> 15 AUG 1887
    $date=str_replace(" JAN ", "01", $date);
    $date=str_replace(" FEB ", "02", $date);
    $date=str_replace(" MAR ", "03", $date);
    $date=str_replace(" APR ", "04", $date);
    $date=str_replace(" MAY ", "05", $date);
    $date=str_replace(" JUN ", "06", $date);
    $date=str_replace(" JUL ", "07", $date);
    $date=str_replace(" AUG ", "08", $date);
    $date=str_replace(" SEP ", "09", $date);
    $date=str_replace(" OCT ", "10", $date);
    $date=str_replace(" NOV ", "11", $date);
    $date=str_replace(" DEC ", "12", $date);
    $date=substr($date,-4).substr($date,2,2).substr($date,0,2);
    */

    $process_age = new CalculateDates;

    if (strpos($date, '/') > 0) {  // if date is gregorian double date, take first part:  972/73 --> 972
        $temp = explode('/', $date);
        $date = $temp[0];
    }

    $year = $process_age->search_year($date);
    if ($year == null || $year > date("Y")) {
        return null;
    }

    $month = $process_age->search_month($date);
    if ($month != null) {
        if (strlen($month) == 1) {
            $month = "0" . $month;
        }
    } else {
        $month = "07";
    }

    $day = $process_age->search_day($date);
    if ($day != null) {
        if (strlen($day) == 1) {
            $day = "0" . $day;
        }
    } else {
        $day = "01";
    }

    return $year . $month . $day;
}

// *** Men and women table ***
//TODO improve code. Probably move HTML out of this function (return array).
function show_person($row, $date = 'EMPTY')
{
    global $humo_option, $uri_path, $tree_id;

    //TEST LINE
    //global $db_functions;
    //$row = $db_functions->get_person($row->pers_gedcomnumber);

    $person_cls = new PersonCls($row);
    $privacy = $person_cls->privacy;
    $name = $person_cls->person_name($row);

    // Example:
    // <td align='center'><i>[date]</i></td>
    // <td align="center"><a href="[url]"><i><b>[name]</b></i></a></td>
    if (!$privacy) {
        $line = '';
        if ($date != 'EMPTY') {
            $line = "<td><i>" . date_place($date, '') . "</i></td>\n";
        }

        // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
        $url = $person_cls->person_url2($row->pers_tree_id, $row->pers_famc, $row->pers_fams, $row->pers_gedcomnumber);
        $line .= '<td><a href="' . $url . '"><i><b>' . $name["standard_name"] . '</b></i> </a> </td>';
    } else {
        $line = '<td>' . __('PRIVACY FILTER') . '</td>';
        if ($date != 'EMPTY') {
            $line .= '<td>' . __('PRIVACY FILTER') . '</td>';
        }
    }
    return $line;
}

$countman = 0;
$countwoman = 0;
$oldest_man_bir_date = '30003112';
$oldest_woman_bir_date = '30003112';
$oldest_man_bir_ged = '';
$oldest_woman_bir_ged = '';

$latest_man_bir_date = '0';
$latest_woman_bir_date = '0';
$latest_man_bir_ged = '';
$latest_woman_bir_ged = '';

$oldest_man_dea_date = '30003112';
$oldest_woman_dea_date = '30003112';
$oldest_man_dea_ged = '';
$oldest_woman_dea_ged = '';

$latest_man_dea_date = '0';
$latest_woman_dea_date = '0';
$latest_man_dea_ged = '';
$latest_woman_dea_ged = '';

$oldest_man_bap_date = '30003112';
$oldest_woman_bap_date = '30003112';
$oldest_man_bap_ged = '';
$oldest_woman_bap_ged = '';

$latest_man_bap_date = '0';
$latest_woman_bap_date = '0';
$latest_man_bap_ged = '';
$latest_woman_bap_ged = '';

$longest_living_man = 0;
$longest_living_woman = 0;
$longest_living_man_ged = '';
$longest_living_woman_ged = '';
$total_age_man = 0;
$total_age_woman = 0;
$man_age_count = 0;
$woman_age_count = 0;
$average_living_man = 0;
$average_living_woman = 0;

$longest_living_man_marr = 0;
$longest_living_woman_marr = 0;
$shortest_living_man_marr = 120;
$shortest_living_woman_marr = 120;
$total_age_man_marr = 0;
$total_age_woman_marr = 0;
$man_age_count_marr = 0;
$woman_age_count_marr = 0;
$average_living_man_marr = 0;
$average_living_woman_marr = 0;

$livingcalc = new CalculateDates;

$persqr = $dbh->query("SELECT pers_sexe, pers_gedcomnumber, pers_birth_date, pers_death_date, pers_bapt_date, pers_fams
    FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'");
while ($persstatDb = $persqr->fetch(PDO::FETCH_OBJ)) {
    if ($persstatDb->pers_sexe == "M") {
        $countman++;

        $manbirdate = convert_date_number($persstatDb->pers_birth_date);
        if ($manbirdate != null && $manbirdate < $oldest_man_bir_date) {
            $oldest_man_bir_date = $manbirdate;
            $oldest_man_bir_ged = $persstatDb->pers_gedcomnumber;
        }
        if ($manbirdate != null && $manbirdate > $latest_man_bir_date) {
            $latest_man_bir_date = $manbirdate;
            $latest_man_bir_ged = $persstatDb->pers_gedcomnumber;
        }

        $mandeadate = convert_date_number($persstatDb->pers_death_date);
        if ($mandeadate != null && $mandeadate < $oldest_man_dea_date) {
            $oldest_man_dea_date = $mandeadate;
            $oldest_man_dea_ged = $persstatDb->pers_gedcomnumber;
        }
        if ($mandeadate != null && $mandeadate > $latest_man_dea_date) {
            $latest_man_dea_date = $mandeadate;
            $latest_man_dea_ged = $persstatDb->pers_gedcomnumber;
        }

        $manbapdate = convert_date_number($persstatDb->pers_bapt_date);
        if ($manbapdate != null && $manbapdate < $oldest_man_bap_date) {
            $oldest_man_bap_date = $manbapdate;
            $oldest_man_bap_ged = $persstatDb->pers_gedcomnumber;
        }
        if ($manbapdate != null && $manbapdate > $latest_man_bap_date) {
            $latest_man_bap_date = $manbapdate;
            $latest_man_bap_ged = $persstatDb->pers_gedcomnumber;
        }
        if ($persstatDb->pers_death_date != "" && ($persstatDb->pers_birth_date != "" || $persstatDb->pers_bapt_date != "")) {
            $man_age = $livingcalc->calculate_age($persstatDb->pers_bapt_date, $persstatDb->pers_birth_date, $persstatDb->pers_death_date, true);
            //if($man_age >= 0 AND $man_age < 120) { // valid age
            if ($man_age && $man_age >= 0 && $man_age < 120) { // valid age
                $total_age_man += $man_age;
                $man_age_count++;
                if ($man_age >= $longest_living_man) {
                    $longest_living_man = $man_age;
                    $longest_living_man_ged = $persstatDb->pers_gedcomnumber;
                }
                if ($persstatDb->pers_fams != '') {
                    $total_age_man_marr += $man_age;
                    $man_age_count_marr++;
                    if ($man_age > $longest_living_man_marr) {
                        $longest_living_man_marr = $man_age;
                    }
                    if ($man_age < $shortest_living_man_marr && $man_age > 0) {
                        $shortest_living_man_marr = $man_age;
                    }
                }
            }
        }
    } elseif ($persstatDb->pers_sexe == "F") {
        $countwoman++;

        $womanbirdate = convert_date_number($persstatDb->pers_birth_date);
        if ($womanbirdate != null && $womanbirdate < $oldest_woman_bir_date) {
            $oldest_woman_bir_date = $womanbirdate;
            $oldest_woman_bir_ged = $persstatDb->pers_gedcomnumber;
        }
        if ($womanbirdate != null && $womanbirdate > $latest_woman_bir_date) {
            $latest_woman_bir_date = $womanbirdate;
            $latest_woman_bir_ged = $persstatDb->pers_gedcomnumber;
        }

        $womandeadate = convert_date_number($persstatDb->pers_death_date);
        if ($womandeadate != null && $womandeadate < $oldest_woman_dea_date) {
            $oldest_woman_dea_date = $womandeadate;
            $oldest_woman_dea_ged = $persstatDb->pers_gedcomnumber;
        }
        if ($womandeadate != null && $womandeadate > $latest_woman_dea_date) {
            $latest_woman_dea_date = $womandeadate;
            $latest_woman_dea_ged = $persstatDb->pers_gedcomnumber;
        }

        $womanbapdate = convert_date_number($persstatDb->pers_bapt_date);
        if ($womanbapdate != null && $womanbapdate < $oldest_woman_bap_date) {
            $oldest_woman_bap_date = $womanbapdate;
            $oldest_woman_bap_ged = $persstatDb->pers_gedcomnumber;
        }
        if ($womanbapdate != null && $womanbapdate > $latest_woman_bap_date) {
            $latest_woman_bap_date = $womanbapdate;
            $latest_woman_bap_ged = $persstatDb->pers_gedcomnumber;
        }

        if ($persstatDb->pers_death_date != "" && ($persstatDb->pers_birth_date != "" || $persstatDb->pers_bapt_date != "")) {
            $woman_age = $livingcalc->calculate_age($persstatDb->pers_bapt_date, $persstatDb->pers_birth_date, $persstatDb->pers_death_date, true);
            //if($woman_age >= 0 AND $woman_age < 120) {
            if ($woman_age && $woman_age >= 0 && $woman_age < 120) {
                $total_age_woman += $woman_age;
                $woman_age_count++;
                if ($woman_age >= $longest_living_woman) {
                    $longest_living_woman = $woman_age;
                    $longest_living_woman_ged = $persstatDb->pers_gedcomnumber;
                }
                if ($persstatDb->pers_fams != '') {
                    $total_age_woman_marr += $woman_age;
                    $woman_age_count_marr++;
                    if ($woman_age > $longest_living_woman_marr) {
                        $longest_living_woman_marr = $woman_age;
                    }
                    if ($woman_age < $shortest_living_woman_marr && $woman_age > 0) {
                        $shortest_living_woman_marr = $woman_age;
                    }
                }
            }
        }
    }
}

if ($longest_living_man == 0) {
    $longest_living_man = null;
} else {
    $average_living_man = $total_age_man / $man_age_count;
    $average_living_man_marr = $total_age_man_marr / $man_age_count_marr;
}
if ($longest_living_woman == 0) {
    $longest_living_woman = null;
} else {
    $average_living_woman = $total_age_woman / $woman_age_count;
    $average_living_woman_marr = $total_age_woman_marr / $woman_age_count_marr;
}
if ($oldest_man_bir_date == '30003112') {
    $oldest_man_bir_date = null;
}
if ($oldest_man_dea_date == '30003112') {
    $oldest_man_dea_date = null;
}
if ($oldest_man_bap_date == '30003112') {
    $oldest_man_bap_date = null;
}

if ($oldest_woman_bir_date == '30003112') {
    $oldest_woman_bir_date = null;
}
if ($oldest_woman_dea_date == '30003112') {
    $oldest_woman_dea_date = null;
}
if ($oldest_woman_bap_date == '30003112') {
    $oldest_woman_bap_date = null;
}

if ($latest_man_bir_date == '0') {
    $latest_man_bir_date = null;
}
if ($latest_man_dea_date == '0') {
    $latest_man_dea_date = null;
}
if ($latest_man_bap_date == '0') {
    $latest_man_bap_date = null;
}

if ($latest_woman_bir_date == '0') {
    $latest_woman_bir_date = null;
}
if ($latest_woman_dea_date == '0') {
    $latest_woman_dea_date = null;
}
if ($latest_woman_bap_date == '0') {
    $latest_woman_bap_date = null;
}

$man_percentage = 0;
$woman_percentage = 0;
$both = $countman + $countwoman;
if ($both) {
    $percent = ($countman / $both) * 100;
    $man_percentage = round($percent, 1);
    $percent = ($countwoman / $both) * 100;
    $woman_percentage = round($percent, 1);
} 
?>

<br>

<!-- <div class="table-responsive"> -->
<table class="table">
    <thead class="table-primary">
        <tr>
            <th><?= __('Item'); ?></th>
            <th colspan="2"><?= __('Male'); ?></th>
            <th colspan="2"><?= __('Female'); ?></th>
        </tr>
    </thead>

    <tr>
        <td><?= __('No. of persons'); ?></td>
        <td><i><?= $countman; ?></i></td>
        <td><?= $man_percentage; ?>%</td>
        <td><i><?= $countwoman; ?></i></td>
        <td><?= $woman_percentage; ?>%</td>
    </tr>

    <tr>
        <td colspan="5"><br></td>
    </tr>

    <!-- Oldest pers_birth_date man -->
    <tr>
        <td><?= __('Oldest birth date'); ?></td>
        <?php
        if ($oldest_man_bir_date != null) {
            $row = $db_functions->get_person($oldest_man_bir_ged);
            echo show_person($row, $row->pers_birth_date);
        } else {
            echo "<td></td><td></td>\n";
        }

        // *** Oldest pers_birth_date woman.
        if ($oldest_woman_bir_date != null) {
            $row = $db_functions->get_person($oldest_woman_bir_ged);
            echo show_person($row, $row->pers_birth_date);
        } else {
            echo "<td></td><td></td>";
        }
        ?>
    </tr>

    <!-- Youngest pers_birth_date man -->
    <tr>
        <td><?= __('Youngest birth date'); ?></td>
        <?php
        if ($latest_man_bir_date != null) {
            $row = $db_functions->get_person($latest_man_bir_ged);
            echo show_person($row, $row->pers_birth_date);
        } else {
            echo "<td></td><td></td>\n";
        }

        // *** Youngest pers_birth_date woman.
        if ($latest_woman_bir_date != null) {
            $row = $db_functions->get_person($latest_woman_bir_ged);
            echo show_person($row, $row->pers_birth_date);
        } else {
            echo "<td></td><td></td>";
        }
        ?>
    </tr>

    <!-- Oldest pers_bapt_date man -->
    <tr>
        <td><?= __('Oldest baptism date'); ?></td>
        <?php
        if ($oldest_man_bap_date != null) {
            $row = $db_functions->get_person($oldest_man_bap_ged);
            echo show_person($row, $row->pers_bapt_date);
        } else {
            echo "<td></td><td></td>\n";
        }

        // *** Oldest pers_bapt_date woman.
        if ($oldest_woman_bap_date != null) {
            $row = $db_functions->get_person($oldest_woman_bap_ged);
            echo show_person($row, $row->pers_bapt_date);
        } else {
            echo "<td></td><td></td>";
        }
        ?>
    </tr>

    <!-- Youngest pers_bapt_date man -->
    <tr>
        <td><?= __('Youngest baptism date'); ?></td>
        <?php
        if ($latest_man_bap_date != null) {
            $row = $db_functions->get_person($latest_man_bap_ged);
            echo show_person($row, $row->pers_bapt_date);
        } else {
            echo "<td></td><td></td>\n";
        }

        // *** Youngest pers_bapt_date woman.
        if ($latest_woman_bap_date != null) {
            $row = $db_functions->get_person($latest_woman_bap_ged);
            echo show_person($row, $row->pers_bapt_date);
        } else {
            echo "<td></td><td></td>";
        }
        ?>
    </tr>

    <!-- Oldest pers_death_date man -->
    <tr>
        <td><?= __('Oldest death date'); ?></td>
        <?php
        if ($oldest_man_dea_date != null) {
            $row = $db_functions->get_person($oldest_man_dea_ged);
            echo show_person($row, $row->pers_death_date);
        } else {
            echo "<td></td><td></td>\n";
        }

        // *** Oldest pers_death_date woman.
        if ($oldest_woman_dea_date != null) {
            $row = $db_functions->get_person($oldest_woman_dea_ged);
            echo show_person($row, $row->pers_death_date);
        } else {
            echo "<td></td><td></td>";
        }
        ?>
    </tr>

    <!-- Youngest pers_death_date man -->
    <tr>
        <td><?= __('Youngest death date'); ?></td>
        <?php
        if ($latest_man_dea_date != null) {
            $row = $db_functions->get_person($latest_man_dea_ged);
            echo show_person($row, $row->pers_death_date);
        } else {
            echo "<td></td><td></td>\n";
        }

        // *** Youngest pers_death_date woman.
        if ($latest_woman_dea_date != null) {
            $row = $db_functions->get_person($latest_woman_dea_ged);
            echo show_person($row, $row->pers_death_date);
        } else {
            echo "<td></td><td></td>";
        }
        ?>
    </tr>

    <tr>
        <td><?= __('Longest living person'); ?></td>
        <?php
        // *** Longest living man.
        if ($longest_living_man != null) {
            $row = $db_functions->get_person($longest_living_man_ged);
            echo '<td><i>' . $longest_living_man . ' ' . __('years') . "</i></td>\n";
            echo show_person($row);
        } else {
            echo "<td></td><td></td>\n";
        }
        // *** Longest living woman.
        if ($longest_living_woman != null) {
            $row = $db_functions->get_person($longest_living_woman_ged);
            echo '<td><i>' . $longest_living_woman . ' ' . __('years') . "</i></td>\n";
            echo show_person($row);
        } else {
            echo "<td></td><td></td>";
        }
        ?>
    </tr>

    <!-- Average age -->
    <tr>
        <td><?= __('Average age'); ?></td>
        <?php
        // Man
        echo '<td>';
        if ($average_living_man != 0) {
            echo round($average_living_man, 1);
        }
        echo ' ' . __('years') . '</td><td></td>';
        // Woman
        echo '<td>';
        if ($average_living_woman != 0) {
            echo round($average_living_woman, 1);
        }
        echo ' ' . __('years') . '</td><td></td>';
        ?>
    </tr>

    <!-- Average age married -->
    <tr>
        <td><?= __('Average age married persons'); ?></td>
        <?php
        // Man
        echo '<td>';
        if ($average_living_man_marr != 0) {
            echo round($average_living_man_marr, 1);
        }
        echo ' ' . __('years') . '</td><td></td>';
        // Woman
        echo '<td>';
        if ($average_living_woman_marr != 0) {
            echo round($average_living_woman_marr, 1);
        }
        echo ' ' . __('years') . '</td><td></td>';
        ?>
    </tr>

    <tr>
        <td><?= __('Lifespan range of married individuals'); ?></td>
        <td><?= $shortest_living_man_marr . ' - ' . $longest_living_man_marr . ' ' . __('years'); ?></td>
        <td>&nbsp;</td>
        <td><?= $shortest_living_woman_marr . ' - ' . $longest_living_woman_marr . ' ' . __('years'); ?></td>
        <td>&nbsp;</td>
    </tr>
</table>
<!-- </div> -->
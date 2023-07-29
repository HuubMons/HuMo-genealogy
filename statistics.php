<?php
/*
 * Statistics
 * First version: René Janssen.
 * Updated by: Huub.
 *
 * April 2015, Huub: added tab menu, and Yossi's new freqently firstnames and surnames pages.
 */

include_once("header.php"); // returns CMS_ROOTPATH constant
include_once(CMS_ROOTPATH . "menu.php");
// *** Standard function for names ***
include_once(CMS_ROOTPATH . "include/person_cls.php");
include_once(CMS_ROOTPATH . "include/language_date.php");
include_once(CMS_ROOTPATH . "include/date_place.php");
include_once(CMS_ROOTPATH . "include/calculate_age_cls.php");

if ($humo_option["url_rewrite"] == "j") {
    $path = 'statistics';
} else {
    $path = CMS_ROOTPATH . 'statistics.php';
}

// *** Get general data from family tree ***
$dataDb = $db_functions->get_tree($tree_prefix_quoted);

$tree_date = $dataDb->tree_date;
$month = ''; // *** empty date ***
if (substr($tree_date, 5, 2) == '01') {
    $month = ' ' . __('jan') . ' ';
}
if (substr($tree_date, 5, 2) == '02') {
    $month = ' ' . __('feb') . ' ';
}
if (substr($tree_date, 5, 2) == '03') {
    $month = ' ' . __('mar') . ' ';
}
if (substr($tree_date, 5, 2) == '04') {
    $month = ' ' . __('apr') . ' ';
}
if (substr($tree_date, 5, 2) == '05') {
    $month = ' ' . __('may') . ' ';
}
if (substr($tree_date, 5, 2) == '06') {
    $month = ' ' . __('jun') . ' ';
}
if (substr($tree_date, 5, 2) == '07') {
    $month = ' ' . __('jul') . ' ';
}
if (substr($tree_date, 5, 2) == '08') {
    $month = ' ' . __('aug') . ' ';
}
if (substr($tree_date, 5, 2) == '09') {
    $month = ' ' . __('sep') . ' ';
}
if (substr($tree_date, 5, 2) == '10') {
    $month = ' ' . __('oct') . ' ';
}
if (substr($tree_date, 5, 2) == '11') {
    $month = ' ' . __('nov') . ' ';
}
if (substr($tree_date, 5, 2) == '12') {
    $month = ' ' . __('dec') . ' ';
}
$tree_date = substr($tree_date, 8, 2) . $month . substr($tree_date, 0, 4);

// *** Tab menu ***
$menu_tab = 'stats_tree';
if (isset($_GET['menu_tab']) and $_GET['menu_tab'] == 'stats_tree') $menu_tab = 'stats_tree';
if (isset($_GET['menu_tab']) and $_GET['menu_tab'] == 'stats_persons') $menu_tab = 'stats_persons';
if (isset($_GET['menu_tab']) and $_GET['menu_tab'] == 'stats_surnames') $menu_tab = 'stats_surnames';
if (isset($_GET['menu_tab']) and $_GET['menu_tab'] == 'stats_firstnames') $menu_tab = 'stats_firstnames';

?>
<p>
<div class="pageHeadingContainer pageHeadingContainer-lineVisible" aria-hidden="false">
    <div class="pageHeading">
        <div class="pageTabsContainer" aria-hidden="false">
            <ul class="pageTabs">
                <?php
                $select_item = '';
                if ($menu_tab == 'stats_tree') {
                    $select_item = ' pageTab-active';
                }
                echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '"><a href="' . $path . '?' . 'tree_id=' . $tree_id . '">' . __('Family tree') . "</a></div></li>";

                $select_item = '';
                if ($menu_tab == 'stats_persons') {
                    $select_item = ' pageTab-active';
                }
                echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '"><a href="' . $path . '?menu_tab=stats_persons&amp;tree_id=' . $tree_id . '">' . __('Persons') . "</a></div></li>";

                $select_item = '';
                if ($menu_tab == 'stats_surnames') {
                    $select_item = ' pageTab-active';
                }
                echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '"><a href="' . $path . '?menu_tab=stats_surnames&amp;tree_id=' . $tree_id . '">' . __('Frequency of Surnames') . "</a></div></li>";

                $select_item = '';
                if ($menu_tab == 'stats_firstnames') {
                    $select_item = ' pageTab-active';
                }
                echo '<li class="pageTabItem"><div tabindex="0" class="pageTab' . $select_item . '"><a href="' . $path . '?menu_tab=stats_firstnames&amp;tree_id=' . $tree_id . '">' . __('Frequency of First Names') . "</a></div></li>";
                ?>
            </ul>
        </div>
    </div>
</div>

<?php
// *** Align content to the left ***
echo '<div id="statistics_screen">';

// *** Show tree statistics ***
if ($menu_tab == 'stats_tree') {
?>
    <br>
    <table class="humo small" align="center">
        <tr class="table_headline">
            <th><?= __('Item'); ?></th>
            <th><br></th>
            <th><br></th>
        </tr>

        <!-- Latest database update -->
        <tr>
            <td><?= __('Latest update'); ?></td>
            <td align="center"><i><?= $tree_date; ?></i></td>
            <td><br></td>
        </tr>

        <tr>
            <td colspan="3"><br></td>
        </tr>

        <!-- Nr. of families in database -->
        <tr>
            <td><?= __('No. of families'); ?></td>
            <td align="center"><i><?= $dataDb->tree_families; ?></i></td>
            <td><br></td>
        </tr>
        <?php

        // *** Most children in family ***
        echo "<tr><td>" . __('Most children in family') . "</td>\n";
        $test_number = "0"; // *** minimum of 0 children ***
        $res = @$dbh->query("SELECT fam_gedcomnumber, fam_man, fam_woman, fam_children
            FROM humo_families WHERE fam_tree_id='" . $tree_id . "' AND fam_children != ''");
        while (@$record = $res->fetch(PDO::FETCH_OBJ)) {
            $count_children = substr_count($record->fam_children, ';');
            $count_children = $count_children + 1;
            if ($count_children > $test_number) {
                $test_number = "$count_children";
                $man_gedcomnumber = $record->fam_man;
                $woman_gedcomnumber = $record->fam_woman;
                $fam_gedcomnumber = $record->fam_gedcomnumber;
            }
        }
        echo "<td align='center'><i>$test_number</i></td>\n";
        if ($test_number != "0") {
            @$record = $db_functions->get_person($man_gedcomnumber);
            $person_cls = new person_cls($record);
            $name = $person_cls->person_name($record);
            $man = $name["standard_name"];

            @$record = $db_functions->get_person($woman_gedcomnumber);
            $person_cls = new person_cls($record);
            $name = $person_cls->person_name($record);
            $woman = $name["standard_name"];

            //if (CMS_SPECIFIC == "Joomla") {
            //    echo '<td align="center"><a href="index.php?option=com_humo-gen&task=family&id=' . $fam_gedcomnumber . '"><i><b>' . $man . __(' and ') . $woman . '</b></i> </a></td></tr>';
            //} else {
            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            //$url=$person_cls->person_url2($fatherDb->pers_tree_id,$fatherDb->pers_famc,$fatherDb->pers_fams,$fatherDb->pers_gedcomnumber);
            $url = $person_cls->person_url2($tree_id, $fam_gedcomnumber, '', '');

            echo '<td align="center"><a href="' . $url . '"><i><b>' . $man . __(' and ') . $woman . '</b></i> </a></td></tr>';
            //}
        } else {
            echo '<td></td></tr>';
        }
        // *** Nr. of persons database ***
        $nr_persons = $dataDb->tree_persons;
        echo "<tr><td>" . __('No. of persons') . "</td>\n";
        echo "<td align='center'><i>$nr_persons</i></td>\n";
        echo '<td><br></td></tr>';
        ?>
    </table>
<?php
}


// *** Show persons statistics ***
if ($menu_tab == 'stats_persons') {

    function convert_date_number($date)
    {
        //31 SEP 2010 -> 20100931
        //$dote=$date;
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

        $process_age = new calculate_year_cls;

        if (strpos($date, '/') > 0) {  // if date is gregorian double date, take first part:  972/73 --> 972
            $temp = explode('/', $date);
            $date = $temp[0];
        }

        $year = $process_age->search_year($date);
        if ($year == null or $year > date("Y")) {
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
        $date = $year . $month . $day;

        return $date;
    }

    // *** Men and women table ***
    function show_person($row, $date = 'EMPTY')
    {
        global $humo_option, $uri_path, $tree_id;
        $person_cls = new person_cls($row);
        $privacy = $person_cls->privacy;

        $name = $person_cls->person_name($row);
        if (!$privacy) {
            $line = '';
            if ($date != 'EMPTY') $line = "<td align='center'><i>" . date_place($date, '') . "</i></td>\n";

            // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
            $url = $person_cls->person_url2($row->pers_tree_id, $row->pers_famc, $row->pers_fams, $row->pers_gedcomnumber);

            $line .= '<td align="center"><a href="' . $url . '"><i><b>' . $name["standard_name"] . '</b></i> </a> </td>';
        } else {
            $line = '<td align="center">' . __('PRIVACY FILTER') . '</td>';
            if ($date != 'EMPTY') $line .= '<td align="center">' . __('PRIVACY FILTER') . '</td>';
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

    $livingcalc = new calculate_year_cls;

    $persqr = $dbh->query("SELECT pers_sexe, pers_gedcomnumber, pers_birth_date, pers_death_date, pers_bapt_date, pers_fams
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "'");
    while ($persstatDb = $persqr->fetch(PDO::FETCH_OBJ)) {

        if ($persstatDb->pers_sexe == "M") {
            $countman++;

            $manbirdate = convert_date_number($persstatDb->pers_birth_date);
            if ($manbirdate != null and $manbirdate < $oldest_man_bir_date) {
                $oldest_man_bir_date = $manbirdate;
                $oldest_man_bir_ged = $persstatDb->pers_gedcomnumber;
            }
            if ($manbirdate != null and $manbirdate > $latest_man_bir_date) {
                $latest_man_bir_date = $manbirdate;
                $latest_man_bir_ged = $persstatDb->pers_gedcomnumber;
            }

            $mandeadate = convert_date_number($persstatDb->pers_death_date);
            if ($mandeadate != null and $mandeadate < $oldest_man_dea_date) {
                $oldest_man_dea_date = $mandeadate;
                $oldest_man_dea_ged = $persstatDb->pers_gedcomnumber;
            }
            if ($mandeadate != null and $mandeadate > $latest_man_dea_date) {
                $latest_man_dea_date = $mandeadate;
                $latest_man_dea_ged = $persstatDb->pers_gedcomnumber;
            }

            $manbapdate = convert_date_number($persstatDb->pers_bapt_date);
            if ($manbapdate != null and $manbapdate < $oldest_man_bap_date) {
                $oldest_man_bap_date = $manbapdate;
                $oldest_man_bap_ged = $persstatDb->pers_gedcomnumber;
            }
            if ($manbapdate != null and $manbapdate > $latest_man_bap_date) {
                $latest_man_bap_date = $manbapdate;
                $latest_man_bap_ged = $persstatDb->pers_gedcomnumber;
            }
            if ($persstatDb->pers_death_date != "" and ($persstatDb->pers_birth_date != "" or $persstatDb->pers_bapt_date != "")) {
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
                        if ($man_age < $shortest_living_man_marr and $man_age > 0) {
                            $shortest_living_man_marr = $man_age;
                        }
                    }
                }
            }
        } elseif ($persstatDb->pers_sexe == "F") {
            $countwoman++;

            $womanbirdate = convert_date_number($persstatDb->pers_birth_date);
            if ($womanbirdate != null and $womanbirdate < $oldest_woman_bir_date) {
                $oldest_woman_bir_date = $womanbirdate;
                $oldest_woman_bir_ged = $persstatDb->pers_gedcomnumber;
            }
            if ($womanbirdate != null and $womanbirdate > $latest_woman_bir_date) {
                $latest_woman_bir_date = $womanbirdate;
                $latest_woman_bir_ged = $persstatDb->pers_gedcomnumber;
            }

            $womandeadate = convert_date_number($persstatDb->pers_death_date);
            if ($womandeadate != null and $womandeadate < $oldest_woman_dea_date) {
                $oldest_woman_dea_date = $womandeadate;
                $oldest_woman_dea_ged = $persstatDb->pers_gedcomnumber;
            }
            if ($womandeadate != null and $womandeadate > $latest_woman_dea_date) {
                $latest_woman_dea_date = $womandeadate;
                $latest_woman_dea_ged = $persstatDb->pers_gedcomnumber;
            }

            $womanbapdate = convert_date_number($persstatDb->pers_bapt_date);
            if ($womanbapdate != null and $womanbapdate < $oldest_woman_bap_date) {
                $oldest_woman_bap_date = $womanbapdate;
                $oldest_woman_bap_ged = $persstatDb->pers_gedcomnumber;
            }
            if ($womanbapdate != null and $womanbapdate > $latest_woman_bap_date) {
                $latest_woman_bap_date = $womanbapdate;
                $latest_woman_bap_ged = $persstatDb->pers_gedcomnumber;
            }

            if ($persstatDb->pers_death_date != "" and ($persstatDb->pers_birth_date != "" or $persstatDb->pers_bapt_date != "")) {
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
                        if ($woman_age < $shortest_living_woman_marr and $woman_age > 0) {
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
    if ($oldest_man_bir_date == '30003112') $oldest_man_bir_date = null;
    if ($oldest_man_dea_date == '30003112') $oldest_man_dea_date = null;
    if ($oldest_man_bap_date == '30003112') $oldest_man_bap_date = null;

    if ($oldest_woman_bir_date == '30003112') $oldest_woman_bir_date = null;
    if ($oldest_woman_dea_date == '30003112') $oldest_woman_dea_date = null;
    if ($oldest_woman_bap_date == '30003112') $oldest_woman_bap_date = null;

    if ($latest_man_bir_date == '0') $latest_man_bir_date = null;
    if ($latest_man_dea_date == '0') $latest_man_dea_date = null;
    if ($latest_man_bap_date == '0') $latest_man_bap_date = null;

    if ($latest_woman_bir_date == '0') $latest_woman_bir_date = null;
    if ($latest_woman_dea_date == '0') $latest_woman_dea_date = null;
    if ($latest_woman_bap_date == '0') $latest_woman_bap_date = null;

    $both = $countman + $countwoman;
    @$percent = ($countman / $both) * 100;
    $man_percentage = round($percent, 1);
    @$percent = ($countwoman / $both) * 100;
    $woman_percentage = round($percent, 1);
?>
    <br>
    <table style="width:80%;" class="humo" align="center">
        <tr class=table_headline>
            <th width="20%"><?= __('Item'); ?></th>
            <th colspan="2" width="40%"><?= __('Male'); ?></th>
            <th colspan="2" width="40%"><?= __('Female'); ?></th>
        </tr>

        <tr>
            <td><?= __('No. of persons'); ?></td>
            <td align='center'><i><?= $countman; ?></i></td>
            <td align="center"><?= $man_percentage; ?>%</td>
            <td align='center'><i><?= $countwoman; ?></i></td>
            <td align="center"><?= $woman_percentage; ?>%</td>
        </tr>

        <tr>
            <td colspan="5"><br></td>
        </tr>

        <?php
        // *** Oldest pers_birth_date man.
        echo '<tr><td>' . __('Oldest birth date') . '</td>';
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
            echo "</tr>\n";
        } else {
            echo "<td></td><td></td></tr>\n";
        }

        // *** Youngest pers_birth_date man.
        echo "<tr><td>" . __('Youngest birth date') . "</td>\n";
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
            echo "</tr>\n";
        } else {
            echo "<td></td><td></td></tr>\n";
        }

        // *** Oldest pers_bapt_date man.
        echo "<tr><td>" . __('Oldest baptism date') . "</td>\n";
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
            echo "</tr>\n";
        } else {
            echo "<td></td><td></td></tr>\n";
        }

        // *** Youngest pers_bapt_date man.
        echo "<tr><td>" . __('Youngest baptism date') . "</td>\n";
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
            echo "</tr>\n";
        } else {
            echo "<td></td><td></td></tr>\n";
        }

        // *** Oldest pers_death_date man.
        echo "<tr><td>" . __('Oldest death date') . "</td>\n";
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
            echo "</tr>\n";
        } else {
            echo "<td></td><td></td></tr>\n";
        }

        // *** Youngest pers_death_date man.
        echo "<tr><td>" . __('Youngest death date') . "</td>\n";
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
            echo "</tr>\n";
        } else {
            echo "<td></td><td></td></tr>\n";
        }

        echo "<tr><td>" . __('Longest living person') . "</td>\n";
        // *** Longest living man.
        if ($longest_living_man != null) {
            $row = $db_functions->get_person($longest_living_man_ged);
            echo '<td align="center"><i>' . $longest_living_man . ' ' . __('years') . "</i></td>\n";
            echo show_person($row);
        } else {
            echo "<td></td><td></td>\n";
        }
        // *** Longest living woman.
        if ($longest_living_woman != null) {
            $row = $db_functions->get_person($longest_living_woman_ged);
            echo '<td align="center"><i>' . $longest_living_woman . ' ' . __('years') . "</i></td>\n";
            echo show_person($row);
        } else {
            echo "<td></td><td></td></tr>\n";
        }
        // *** Average age ***
        echo "<tr><td>" . __('Average age') . "</td>\n";
        // Man
        echo '<td align="center">';
        if ($average_living_man != 0) echo round($average_living_man, 1);
        echo ' ' . __('years') . '</td><td></td>';
        // Woman
        echo '<td align="center">';
        if ($average_living_woman != 0) echo round($average_living_woman, 1);
        echo ' ' . __('years') . '</td><td></td></tr>';

        // *** Average age married ***
        echo "<tr><td>" . __('Average age married persons') . "</td>\n";

        // Man
        echo '<td align="center">';
        if ($average_living_man_marr != 0) echo round($average_living_man_marr, 1);
        echo ' ' . __('years') . '</td><td></td>';
        // Woman
        echo '<td align="center">';
        if ($average_living_woman_marr != 0) echo round($average_living_woman_marr, 1);
        echo ' ' . __('years') . '</td><td></td></tr>';

        ?>
        <tr>
            <td><?= __('Lifespan range of married individuals'); ?></td>
            <td align="center"><?= $shortest_living_man_marr . ' - ' . $longest_living_man_marr . ' ' . __('years'); ?></td>
            <td align="center">&nbsp;</td>
            <td align="center"><?= $shortest_living_woman_marr . ' - ' . $longest_living_woman_marr . ' ' . __('years'); ?></td>
            <td align="center">&nbsp;</td>
        </tr>
    </table>
<?php
}

// *** Show frequent surnames ***
if ($menu_tab == 'stats_surnames') {
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
        //if (CMS_SPECIFIC == 'Joomla') {
        //    $path_tmp = 'index.php?option=com_humo-gen&amp;task=list&amp;tree_id=' . $tree_id;
        //} else {
        $path_tmp = CMS_ROOTPATH . 'list.php?tree_id=' . $tree_id;
        //}
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

    //echo '<div class="standard_header">'.__('Frequency of Surnames').'</div>';

    $maxnames = 51;
    if (isset($_POST['freqsurnames'])) {
        $maxnames = $_POST['freqsurnames'];
    }
?>
    <div style="text-align:center">
        <form method="POST" action="<?= $path; ?>?menu_tab=stats_surnames&amp;tree_id=<?= $tree_id; ?>" style="display:inline;" id="frqnames">
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
}

// *** Show frequent firstnames ***
if ($menu_tab == 'stats_firstnames') {

    function first_names($max)
    {
        global $dbh, $tree_id, $language, $user, $humo_option, $uri_path;

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

        //if (CMS_SPECIFIC == 'Joomla') {
        //    $path_tmp = 'index.php?option=com_humo-gen&amp;task=list&amp;tree_id=' . $tree_id;
        //} else {
        $path_tmp = CMS_ROOTPATH . 'list.php?tree_id=' . $tree_id;
        //}

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
                echo '<a href="' . $path_tmp . '&amp;sexe=M&amp;pers_firstname=' . $m_keys[$i] . '&amp;part_firstname=contains">' . $m_keys[$i] . "</a>";
            }
            //male 1st nr
            echo '</td><td class="m_namenr" style="text-align:center;border-right-width:3px">';
            if (isset($m_keys[$i]) and isset($m_first_names[$m_keys[$i]])) {
                echo $m_first_names[$m_keys[$i]];
            }
            //male 2nd name
            echo '</td><td class="m_namelst">';
            if (isset($m_keys[$i + $row]) and isset($m_first_names[$m_keys[$i + $row]])) {
                echo '<a href="' . $path_tmp . '&amp;sexe=M&amp;pers_firstname=' . $m_keys[$i + $row] . '&amp;part_firstname=contains">' . $m_keys[$i + $row] . "</a>";
            }
            //male 2nd nr
            echo '</td><td class="m_namenr" style="text-align:center;border-right-width:6px">';
            if (isset($m_keys[$i + $row]) and isset($m_first_names[$m_keys[$i + $row]])) {
                echo $m_first_names[$m_keys[$i + $row]];
            }
            //female 1st name
            echo '</td><td class="f_namelst">';
            if (isset($f_keys[$i]) and isset($f_first_names[$f_keys[$i]])) {
                echo '<a href="' . $path_tmp . '&amp;sexe=F&amp;pers_firstname=' . $f_keys[$i] . '&amp;part_firstname=contains">' . $f_keys[$i] . "</a>";
            }
            //female 1st nr
            echo '</td><td class="f_namenr" style="text-align:center;border-right-width:3px">';
            if (isset($f_keys[$i]) and isset($f_first_names[$f_keys[$i]])) {
                echo $f_first_names[$f_keys[$i]];
            }
            //female 2nd name
            echo '</td><td class="f_namelst">';
            if (isset($f_keys[$i + $row]) and isset($f_first_names[$f_keys[$i + $row]])) {
                echo '<a href="' . $path_tmp . '&amp;sexe=F&amp;pers_firstname=' . $f_keys[$i + $row] . '&amp;part_firstname=contains">' . $f_keys[$i + $row] . "</a>";
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
        <form method="POST" action="<?= $path; ?>?menu_tab=stats_firstnames&amp;tree_id=<?= $tree_id; ?> " style="display:inline;" id="frqfirnames">
            <?= __('Number of displayed first names'); ?>: <select size=1 name="freqfirstnames" onChange="this.form.submit();" style="width: 50px; height:20px;">
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
        </form>
    </div><br>

    <table style="width:90%;" class="humo nametbl" align="center">
        <tr class=table_headline style="height:25px">
            <?php
            echo '<th style="border-right-width:6px;width:50%" colspan="4"><span style="font-size:135%">' . __('Male') . '</span></th><th  style="width:50%" colspan="4"><span style="font-size:135%">' . __('Female') . '</span></th></tr><tr class=table_headline>';
            echo '<th width="19%">' . __('First name') . '</th><th style="text-align:center;font-size:90%;border-right-width:3px;width:6%">' . __('Total') . '</th>';
            echo '<th width="19%">' . __('First name') . '</th><th style="text-align:center;font-size:90%;border-right-width:6px;width:6%">' . __('Total') . '</th>';
            echo '<th width="19%">' . __('First name') . '</th><th style="text-align:center;font-size:90%;border-right-width:3px;width:6%">' . __('Total') . '</th>';
            echo '<th width="19%">' . __('First name') . '</th><th style="text-align:center;font-size:90%;width:6%">' . __('Total') . '</th>';
            ?>
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
}

echo '</div>'; // *** End of tab menu div ***
include_once(CMS_ROOTPATH . "footer.php");

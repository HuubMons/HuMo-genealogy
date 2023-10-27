<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

require_once(__DIR__ . "/../statistics/maxChart.class.php"); // REQUIRED FOR STATISTICS

// *** Use a class to process person data ***
global $person_cls, $statistics_screen;
$person_cls = new person_cls;

// *** Show 1 statistics line ***
function statistics_line($familyDb)
{
    global $dbh, $language, $person_cls, $selected_language, $db_functions, $link_cls;

    $tree_id = $familyDb->tree_id;
    if (isset($tree_id) and $tree_id) $db_functions->set_tree_id($tree_id);

    echo '<tr>';
    if (isset($familyDb->count_lines)) {
        echo '<td>' . $familyDb->count_lines . '</td>';
    }

    $treetext = show_tree_text($familyDb->tree_id, $selected_language);
    echo '<td>' . $treetext['name'] . '</td>';

    if (!isset($familyDb->count_lines)) {
        echo '<td>' . $familyDb->stat_date_stat . '</td>';
    }

    // *** Check if family is still in the genealogy! ***
    $checkDb = $db_functions->get_family($familyDb->stat_gedcom_fam);
    $check = false;
    if ($checkDb and $checkDb->fam_man == $familyDb->stat_gedcom_man and $checkDb->fam_woman == $familyDb->stat_gedcom_woman) $check = true;

    if ($check == true) {
        $vars['pers_family'] = $familyDb->stat_gedcom_fam;
        $link = $link_cls->get_link('../', 'family', $familyDb->tree_id, false, $vars);
        echo '<td><a href="' . $link . '">' . __('Family') . ': </a>';

        //*** Man ***
        $personDb = $db_functions->get_person($familyDb->stat_gedcom_man);

        if (!$familyDb->stat_gedcom_man)
            echo 'N.N.';
        else {
            $name = $person_cls->person_name($personDb);
            echo $name["standard_name"];
        }

        echo " &amp; ";

        //*** Woman ***
        $personDb = $db_functions->get_person($familyDb->stat_gedcom_woman);
        if (!$familyDb->stat_gedcom_woman)
            echo 'N.N.';
        else {
            $name = $person_cls->person_name($personDb);
            echo $name["standard_name"];
        }
    } else {
        echo '<td><b>' . __('FAMILY NOT FOUND IN FAMILY TREE') . '</b></td>';
    }
}

// *** Show 1 month, statistics calender ***
// *** calender($month, $year, true/false); ***
function calender($month, $year, $thismonth)
{
    global $dbh, $language, $statistics_screen;

    if ($month == '1') {
        $calender_head = __('January');
    }
    if ($month == '2') {
        $calender_head = __('February');
    }
    if ($month == '3') {
        $calender_head = __('March');
    }
    if ($month == '4') {
        $calender_head = __('April');
    }
    if ($month == '5') {
        $calender_head = __('May');
    }
    if ($month == '6') {
        $calender_head = __('June');
    }
    if ($month == '7') {
        $calender_head = __('July');
    }
    if ($month == '8') {
        $calender_head = __('August');
    }
    if ($month == '9') {
        $calender_head = __('September');
    }
    if ($month == '10') {
        $calender_head = __('October');
    }
    if ($month == '11') {
        $calender_head = __('November');
    }
    if ($month == '12') {
        $calender_head = __('December');
    }

?>
    <table class="humo standard" border="1" cellspacing="0">
        <tr class="table_header">
            <th colspan="8"><?= $calender_head . ' ' . $year; ?></th>
        </tr>

        <tr>
            <th><?= __('Nr.'); ?></th>
            <th><?= __('Monday'); ?></th>
            <th><?= __('Tuesday'); ?></th>
            <th><?= __('Wednesday'); ?></th>
            <th><?= __('Thursday'); ?></th>
            <th><?= __('Friday'); ?></th>
            <th><?= __('Saturday'); ?></th>
            <th><?= __('Sunday'); ?></th>
        </tr>

        <?php
        $week = mktime(0, 0, 0, $month, 1, $year);
        $week_number = date("W", $week);
        echo "<tr><th>$week_number</th>";

        // If neccesary skip days at start of month
        $First_Day_Of_Month = date("w", mktime(0, 0, 0, $month, 1, $year));
        if ($First_Day_Of_Month > "1") {
            echo '<td colspan="' . ($First_Day_Of_Month - 1) . '"><br></td>';
        }
        // Sunday:
        if ($First_Day_Of_Month == "0") {
            echo '<td colspan="6"><br></td>';
        }

        // Show days
        $Days_In_Month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = 1;
        $row = 1;
        $field = $First_Day_Of_Month;
        if ($field == '0') {
            $field = 7;
        }  // First day is sunday.

        $i = 1;
        for ($i; $i <= $Days_In_Month; $i++) {
            $present_day = date("Y-n-d");
            if ($day < 10) {
                $day = '0' . $day;
            }
            $date = $year . '-' . $month . '-' . $day;
            $yesterday = strtotime($date);
            $today = $yesterday + 86400;

            if ($statistics_screen == 'visitors') {
                // *** Show visitors ***
                $datasql = $dbh->query("SELECT stat_ip_address FROM humo_stat_date
                WHERE stat_date_linux > " . $yesterday . " AND stat_date_linux < " . $today . ' GROUP BY stat_ip_address');
            } else {
                // *** Show families ***
                $datasql = $dbh->query("SELECT * FROM humo_stat_date
                WHERE stat_date_linux > " . $yesterday . " AND stat_date_linux < " . $today);
            }

            if ($datasql) {
                $nr_statistics = $datasql->rowCount();
            }

            // *** Use another colour for present day ***
            $color = '';
            if ($date == $present_day) {
                $color = ' bgcolor="#00FFFF"';
            }

            echo "<td$color>$day <b>$nr_statistics</b></td>";
            $day++;
            if ($day <= $Days_In_Month) {
                $field++;
                if ($field == 8) {
                    $week = mktime(0, 0, 0, $month, $day, $year);
                    $week_number = date("W", $week);
                    echo "</tr>\n";
                    echo "<tr><th>$week_number</th>";
                    $row++;
                    $field = 1;
                }
            }

            // *** Array for graphical statistics ***
            $data[$day - 1] = $nr_statistics;
        }

        // Add end month spacers
        if ((8 - $field) >= "1") {
            echo '<td colspan="' . (8 - $field) . '"><br></td></tr>';
        }

        // *** Always make 6 rows ***
        if ($row == 5) {
            echo "</tr><tr><td colspan=8><br></td></tr>";
        }
        ?>
    </table><br>
    <?php

    // *** Show graphical month statistics ***
    //$this_month=$thismonth;
    $mc = new maxChart($data);
    //$mc->displayChart($calender_head."&nbsp;".$year,1,700,200,false,$this_month);
    $mc->displayChart($calender_head . "&nbsp;" . $year, 1, 700, 200, false, $thismonth);
}

// *** Function to show year statistics ***
function year_graphics($month, $year)
{
    global $dbh, $language, $statistics_screen;
    $start_month = $month + 1;
    $start_year = $year - 1;
    if ($month == 12) {
        $start_year = $year;
        $start_month = 1;
    }
    for ($i = 1; $i < 13; $i++) {
        if ($start_month == 13) {
            $start_month = 1;
            $start_year++;
        }

        $date = $start_year . '-' . $start_month . '-' . "1";
        $first_day = strtotime($date);
        $Days_In_Month = cal_days_in_month(CAL_GREGORIAN, $start_month, $start_year);
        $latest_day = $first_day + (86400 * $Days_In_Month);

        if ($statistics_screen == 'visitors') {
            // *** Show visitors ***
            $datasql = $dbh->query("SELECT stat_ip_address FROM humo_stat_date
                WHERE stat_date_linux > " . $first_day . " AND stat_date_linux < " . $latest_day . "
                GROUP BY stat_ip_address");
        } else {
            // *** Show visited families ***
            $datasql = $dbh->query("SELECT * FROM humo_stat_date
                WHERE stat_date_linux > " . $first_day . " AND stat_date_linux < " . $latest_day);
        }

        if ($datasql) {
            $nr_statistics = $datasql->rowCount();
        }

        if ($start_month == '1') {
            $month_name = __('jan');
        }
        if ($start_month == '2') {
            $month_name = __('feb');
        }
        if ($start_month == '3') {
            $month_name = __('mar');
        }
        if ($start_month == '4') {
            $month_name = __('apr');
        }
        if ($start_month == '5') {
            $month_name = __('may');
        }
        if ($start_month == '6') {
            $month_name = __('jun');
        }
        if ($start_month == '7') {
            $month_name = __('jul');
        }
        if ($start_month == '8') {
            $month_name = __('aug');
        }
        if ($start_month == '9') {
            $month_name = __('sep');
        }
        if ($start_month == '10') {
            $month_name = __('oct');
        }
        if ($start_month == '11') {
            $month_name = __('nov');
        }
        if ($start_month == '12') {
            $month_name = __('dec');
        }
        $short_year = substr($start_year, 2);
        $twelve_months[$month_name . "&nbsp;" . $short_year] = $nr_statistics;
        $start_month++;
    }
    $mc = new maxChart($twelve_months);
    $this_month = date("n");

    if ($statistics_screen == 'visitors') {
        $mc->displayChart(__('Visitors'), 1, 700, 200, false, $this_month);
    } else {
        $mc->displayChart(__('Visited families in the past 12 months'), 1, 700, 200, false, $this_month);
    }
}
// End statistics

// *** Country statistics ***
function country2()
{
    global $dbh;
    $temp = $dbh->query("SHOW TABLES LIKE 'humo_stat_country'");
    if ($temp->rowCount()) {
        $max = 400; // *** For now just show all countries ***

        // *** Names of countries ***
        include_once(__DIR__ . '/../include/countries.php');

        $statqry = "SELECT stat_country_code, count(stat_country_code) as count_country_code
            FROM humo_stat_country
            GROUP BY stat_country_code ORDER BY count_country_code DESC LIMIT 0," . $max;
        $stat = $dbh->query($statqry);

    ?>
        <table class="humo standard" border="1" cellspacing="0">
            <tr class="table_header">
                <th><?= __('Country of origin'); ?></th>
                <th><?= __('Number of unique visitors'); ?></th>
            </tr>
            <?php
            while (@$statDb = $stat->fetch(PDO::FETCH_OBJ)) {
                $country_code = $statDb->stat_country_code;
                $flag = "images/flags/" . $country_code . ".gif";
                if (!file_exists($flag)) {
                    $flag = 'images/flags/noflag.gif';
                }
            ?>
                <tr>
                    <td>
                        <img src="<?= $flag; ?>" width="30" height="15">&nbsp;
                        <?php
                        if ($country_code != __('Unknown') and $country_code) {
                            echo $countries[$country_code][1] . '&nbsp;(' . $country_code . ')';
                        } else {
                            echo $country_code;
                        }
                        ?>
                    </td>
                    <td><?= $statDb->count_country_code; ?></td>
                </tr>
            <?php
            }
            ?>
        </table>
<?php
    }
}
// *** End country statistics ***

$statistics_screen = 'general_statistics';
if (isset($_POST['statistics_screen']) and $_POST['statistics_screen'] == 'date_statistics') {
    $statistics_screen = 'date_statistics';
}
if (isset($_POST['statistics_screen']) and $_POST['statistics_screen'] == 'visitors') {
    $statistics_screen = 'visitors';
}
if (isset($_POST['statistics_screen']) and $_POST['statistics_screen'] == 'statistics_old') {
    $statistics_screen = 'statistics_old';
}
if (isset($_POST['statistics_screen']) and $_POST['statistics_screen'] == 'remove') {
    $statistics_screen = 'remove';
}
if (isset($_GET['tree_id'])) {
    $statistics_screen = 'statistics_old';
}

// *** Show buttons ***
$phpself = 'index.php';

$style_general_statistics = '';
if ($statistics_screen == 'general_statistics') {
    $style_general_statistics = ' class="selected_item"';
}
$style_date_statistics = '';
if ($statistics_screen == 'date_statistics') {
    $style_date_statistics = ' class="selected_item"';
}
$style_visitors = '';
if ($statistics_screen == 'visitors') {
    $style_visitors = ' class="selected_item"';
}
$style_statistics_old = '';
if ($statistics_screen == 'statistics_old') {
    $style_statistics_old = ' class="selected_item"';
}
$style_remove = '';
if ($statistics_screen == 'remove') {
    $style_remove = ' class="selected_item"';
}

?>
<h1 class="center"><?= __('Statistics'); ?></h1>
<table class="humo" style="width:90%; text-align:center; border:1px solid black;">
    <tr class="table_header_large">
        <td>
            <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="statistics_screen" value="general_statistics">
                <input type="Submit" name="submit" value="<?= __('General statistics'); ?>" <?= $style_general_statistics; ?>>
            </form>
        </td>
        <td>
            <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="statistics_screen" value="date_statistics">
                <input type="Submit" name="submit" value="<?= __('Statistics by date'); ?>" <?= $style_date_statistics; ?>>
            </form>
        </td>
        <td>
            <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="statistics_screen" value="visitors">
                <input type="Submit" name="submit" value="<?= __('Visitors'); ?>" <?= $style_visitors; ?>>
            </form>
        </td>
        <td>
            <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="statistics_screen" value="statistics_old">
                <input type="Submit" name="submit" value="<?= __('Old statistics'); ?>" <?= $style_statistics_old; ?>>
            </form>
        </td>
        <td>
            <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
                <input type="hidden" name="page" value="<?= $page; ?>">
                <input type="hidden" name="statistics_screen" value="remove">
                <input type="Submit" name="submit" value="<?= __('Remove statistics'); ?>" <?= $style_remove; ?>>
            </form>
        </td>
    </tr>
</table>
<?php

// *** Remove old statistics ***
if (isset($_POST['remove2'])) {
    $timestamp = mktime(0, 0, 0, $_POST['stat_month'], $_POST['stat_day'], $_POST['stat_year']);

    $sql = 'DELETE FROM humo_stat_date WHERE stat_date_linux < "' . $timestamp . '"';
    $result = $dbh->query($sql);

    echo '<div class="confirm">';
    echo __('Old statistics') . ' ' . date("d-m-Y", $timestamp) . ' ' . __('are erased');
    echo '</div>';
}

if ($statistics_screen == 'remove') {
    $month = date("m");
    $year = date("Y");
    $month--;
    if ($month == 0) {
        $month = 12;
        $year--;
    }

?>
    <h2><?= __('Remove statistics'); ?></h2>

    <?= __('Statistics will be removed PERMANENTLY. Make a backup first to save the statistics data'); ?><br>

    <form method="POST" action="">
        <input type="hidden" name="page" value="<?= $page; ?>">
        <?= __('Remove ALL statistics BEFORE this date:'); ?>
        <input type="text" name="stat_day" value="1" size="1">
        <input type="text" name="stat_month" value="<?= $month; ?>" size="1">

        <input type="text" name="stat_year" value="<?= $year; ?>" size="2"> <?= __('d-m-yyyy'); ?><br>

        <input type="Submit" name="remove2" value="<?= __('REMOVE statistic data'); ?>">
    </form>
<?php
}

if ($statistics_screen == 'general_statistics') {
    echo '<h2 align="center">' . __('Status statistics table') . '</h2>';
    //$family_qry=$dbh->query("SELECT *, count(humo_stat_date.stat_easy_id) as count_lines
    //	FROM humo_stat_date LEFT JOIN humo_trees
    //	ON humo_trees.tree_id=humo_stat_date.stat_tree_id
    //	GROUP BY humo_stat_date.stat_tree_id
    //	ORDER BY tree_order desc");

    $family_qry = $dbh->query("
        SELECT * FROM humo_trees as humo_trees2
        RIGHT JOIN
        (
            SELECT stat_tree_id, count(humo_stat_date.stat_easy_id) as count_lines FROM humo_stat_date
            GROUP BY stat_tree_id
        ) as humo_stat_date2
        ON humo_trees2.tree_id=humo_stat_date2.stat_tree_id
        ORDER BY tree_order desc
        ");

    echo '<table class="humo standard" border="1" cellspacing="0">';
    echo '<tr class="table_header"><th>' . ucfirst(__('family tree')) . '</th><th>' . __('Records') . '</th><th>' . __('Number of unique visitors') . '</th></tr>';
    while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
        //statistics_line($familyDb);
        if ($familyDb->tree_prefix) {
            $tree_id = $familyDb->tree_id;
            // *** Show family tree name ***
            $treetext = show_tree_text($familyDb->tree_id, $selected_language);
            echo '<tr><td>' . $treetext['name'] . '</td>';
        } else {
            echo '<tr><td><b>' . __('FAMILY TREE ERASED') . '</b></td>';
        }
        echo '<td>' . $familyDb->count_lines . '</td>';

        // *** Total number of unique visitors ***
        $count_visitors = 0;
        if ($familyDb->tree_id) {
            //$stat=$dbh->query("SELECT *
            //	FROM humo_stat_date LEFT JOIN humo_trees
            //	ON humo_trees.tree_id=humo_stat_date.stat_tree_id
            //	WHERE humo_trees.tree_id=".$familyDb->tree_id."
            //	GROUP BY stat_ip_address
            //	");
            $stat = $dbh->query("SELECT stat_ip_address
                FROM humo_stat_date LEFT JOIN humo_trees
                ON humo_trees.tree_id=humo_stat_date.stat_tree_id
                WHERE humo_trees.tree_id=" . $familyDb->tree_id . "
                GROUP BY stat_ip_address
                ");
            $count_visitors = $stat->rowCount();
        }
        echo '<td>' . $count_visitors . '</td>';
        echo '</tr>';
    }
    echo '</table>';

    echo '<h2 align="center">' . __('General statistics:') . '</h2>';

    echo '<table class="humo standard" border="1" cellspacing="0">';
    echo '<tr class="table_header"><th>' . __('Item') . '</th><th>' . __('Counter') . '</th></tr>';
    // *** Total number unique visitors ***
    $stat = $dbh->query("SELECT stat_ip_address FROM humo_stat_date GROUP BY stat_ip_address");
    $count_visitors = $stat->rowCount();
    echo '<tr><td>' . __('Total number of unique visitors:') . '</td><td>' . $count_visitors . '</td>';

    // *** Total number visited families ***
    $datasql = $dbh->query("SELECT stat_id FROM humo_stat_date");
    if ($datasql) {
        $total = $datasql->rowCount();
    }
    echo '<tr><td>' . __('Total number of visited families:') . '</td><td>' . $total . '</td>';

    // Visitors per day/ month/ year.
    // 1 day = 86400
    $time_period = strtotime("now") - 3600; // 1 hour
    $datasql = $dbh->query("SELECT * FROM humo_stat_date WHERE stat_date_linux > " . $time_period);
    if ($datasql) {
        $total = $datasql->rowCount();
    }
    echo '<tr><td>' . __('Total number of families in the last hour:') . '</td><td>' . $total . '</td>';
    echo '</table>';


    // *** Country statistics ***
    echo '<h2 align="center">' . __('Unique visitors - Country of origin') . '</h2>';
    country2();
    // *** End country statistics ***


    $nr_lines = 15; // *** Nr. of statistics lines ***

    //$family_qry=$dbh->query("SELECT *, count(humo_stat_date.stat_easy_id) as count_lines
    //	FROM humo_stat_date, humo_trees
    //	WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id
    //	GROUP BY humo_stat_date.stat_easy_id desc
    //	ORDER BY count_lines desc
    //	LIMIT 0,".$nr_lines);

    // *** Didn't use "GROUP BY stat_easy_id" because stat_tree_id is also needed, and 2 results in GROUP BY is not allowed in > MySQL 5.7 ***
    $family_qry = $dbh->query(
        "
        SELECT * FROM humo_trees as humo_trees2
        RIGHT JOIN
        (
            SELECT stat_tree_id, stat_gedcom_fam, stat_gedcom_man, stat_gedcom_woman, count(humo_stat_date.stat_easy_id) as count_lines FROM humo_stat_date
            GROUP BY stat_tree_id, stat_gedcom_fam, stat_gedcom_man, stat_gedcom_woman
        ) as humo_stat_date2
        ON humo_trees2.tree_id=humo_stat_date2.stat_tree_id
        ORDER BY count_lines desc
        LIMIT 0," . $nr_lines
    );

    echo '<h2 align="center">' . $nr_lines . ' ' . __('Most visited families:') . '</h2>';
    echo '<table class="humo standard" border="1" cellspacing="0">';
    echo '<tr class="table_header"><th>#</th><th>' . __('family tree') . '</th><th>' . __('family') . '</th></tr>';
    while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
        statistics_line($familyDb);
    }
    echo '</table>';

    //$family_qry=$dbh->query("SELECT * FROM humo_stat_date, humo_trees
    //	WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id
    //	ORDER BY humo_stat_date.stat_date_stat DESC LIMIT 0,".$nr_lines);
    // *** First line is a bit strange, but was needed for a specific provider ***
    $family_qry = $dbh->query("SELECT humo_stat_date.* , humo_trees.tree_id, humo_trees.tree_prefix FROM humo_stat_date, humo_trees 
        WHERE humo_trees.tree_id=humo_stat_date.stat_tree_id 
        ORDER BY humo_stat_date.stat_date_stat DESC LIMIT 0," . $nr_lines);
    echo '<h2 align="center">' . $nr_lines . ' ' . __('last visited families:') . '</h2>';
    echo '<table class="humo standard" border="1" cellspacing="0">';
    echo '<tr class="table_header"><th>' . __('family tree') . '</th><th>' . __('date-time') . '</th><th>' . __('family') . '</th></tr>';
    while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
        statistics_line($familyDb);
    }
    echo '</table>';
}


if ($statistics_screen == 'date_statistics') {
    // *** Selection of month ***
    $present_month = date("n");
    $month = $present_month;
    if (isset($_POST['month'])) {
        $month = $_POST['month'];
    }

?>
    <div class="center">
        <br>
        <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="statistics_screen" value="date_statistics">
            <select size='1' name='month'>
                <option value="1" <?php if ($month == '1') echo ' selected'; ?>><?= __('January'); ?></option>
                <option value="2" <?php if ($month == '2') echo ' selected'; ?>><?= __('February'); ?></option>
                <option value="3" <?php if ($month == '3') echo ' selected'; ?>><?= __('March'); ?></option>
                <option value="4" <?php if ($month == '4') echo ' selected'; ?>><?= __('April'); ?></option>
                <option value="5" <?php if ($month == '5') echo ' selected'; ?>><?= __('May'); ?></option>
                <option value="6" <?php if ($month == '6') echo ' selected'; ?>><?= __('June'); ?></option>
                <option value="7" <?php if ($month == '7') echo ' selected'; ?>><?= __('July'); ?></option>
                <option value="8" <?php if ($month == '8') echo ' selected'; ?>><?= __('August'); ?></option>
                <option value="9" <?php if ($month == '9') echo ' selected'; ?>><?= __('September'); ?></option>
                <option value="10" <?php if ($month == '10') echo ' selected'; ?>><?= __('October'); ?></option>
                <option value="11" <?php if ($month == '11') echo ' selected'; ?>><?= __('November'); ?></option>
                <option value="12" <?php if ($month == '12') echo ' selected'; ?>><?= __('December'); ?></option>
            </select>
            <?php

            // *** Selection of year ***

            // *** Search oldest record in database***
            $datasql = $dbh->query("SELECT * FROM humo_stat_date ORDER BY stat_date_linux LIMIT 0,1");
            $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
            if (isset($dataDb->stat_date_linux)) $first_year = date("Y", $dataDb->stat_date_linux);

            $present_year = date("Y");
            $year = $present_year;
            if (isset($_POST['year'])) {
                $year = $_POST['year'];
            }

            ?>
            <select size='1' name='year'>
                <?php
                for ($year_select = $first_year; $year_select <= $present_year; $year_select++) {
                    $select = '';
                    if ($year == $year_select) {
                        $select = ' selected';
                    }
                    echo '<option value="' . $year_select . '"' . $select . '>' . $year_select . '</option>';
                }
                ?>
            </select>
            <input type="Submit" name="submit" value=<?= __('Select'); ?>>
        </form>
        <?php

        // *** Visited families in this month ***
        echo '<br><br><b>' . __('Total number of visited families:') . '</b><br>';
        ?>
    </div><br>
    <?php

    // Graphic present month
    if ($month == $present_month and $year == $present_year) {
        calender($month, $year, true);
    } else {
        calender($month, $year, false);
    }

    // Graphic year
    echo "<br>";
    year_graphics($month, $year);
}

if ($statistics_screen == 'visitors') {

    // *** Selection of month ***
    $present_month = date("n");
    $month = $present_month;
    if (isset($_POST['month'])) {
        $month = $_POST['month'];
    }

    //TODO select month is double code. Also used in previous part.

    ?>
    <div class="center">
        <br>
        <form method="POST" action="<?= $phpself; ?>" style="display : inline;">
            <input type="hidden" name="page" value="<?= $page; ?>">
            <input type="hidden" name="statistics_screen" value="visitors">
            <select size='1' name='month'>
                <option value="1" <?php if ($month == '1') echo ' selected'; ?>><?= __('January'); ?></option>
                <option value="2" <?php if ($month == '2') echo ' selected'; ?>><?= __('February'); ?></option>
                <option value="3" <?php if ($month == '3') echo ' selected'; ?>><?= __('March'); ?></option>
                <option value="4" <?php if ($month == '4') echo ' selected'; ?>><?= __('April'); ?></option>
                <option value="5" <?php if ($month == '5') echo ' selected'; ?>><?= __('May'); ?></option>
                <option value="6" <?php if ($month == '6') echo ' selected'; ?>><?= __('June'); ?></option>
                <option value="7" <?php if ($month == '7') echo ' selected'; ?>><?= __('July'); ?></option>
                <option value="8" <?php if ($month == '8') echo ' selected'; ?>><?= __('August'); ?></option>
                <option value="9" <?php if ($month == '9') echo ' selected'; ?>><?= __('September'); ?></option>
                <option value="10" <?php if ($month == '10') echo ' selected'; ?>><?= __('October'); ?></option>
                <option value="11" <?php if ($month == '11') echo ' selected'; ?>><?= __('November'); ?></option>
                <option value="12" <?php if ($month == '12') echo ' selected'; ?>><?= __('December'); ?></option>
            </select>
            <?php

            // *** Selection of year ***

            // *** Find oldest record in database ***
            $datasql = $dbh->query("SELECT * FROM humo_stat_date ORDER BY stat_date_linux LIMIT 0,1");
            $dataDb = $datasql->fetch(PDO::FETCH_OBJ);
            if (isset($dataDb->stat_date_linux)) $first_year = date("Y", $dataDb->stat_date_linux);

            $present_year = date("Y");
            $year = $present_year;
            if (isset($_POST['year'])) {
                $year = $_POST['year'];
            }

            echo " <select size='1' name='year'>";
            for ($year_select = $first_year; $year_select <= $present_year; $year_select++) {
                $select = '';
                if ($year == $year_select) {
                    $select = ' selected';
                }
                echo '<option value="' . $year_select . '"' . $select . '>' . $year_select . '</option>';
            }
            echo "</select>";

            ?>
            <input type="Submit" name="submit" value="<?= __('Select'); ?>">
        </form>

        <!-- Visitors in present month -->
        <br><br><b><?= __('Visitors'); ?></b><br>
    </div><br>
<?php

    // Graphic of present month
    if ($month == $present_month and $year == $present_year) {
        calender($month, $year, true);
    } else {
        calender($month, $year, false);
    }

    // year graphic
    echo "<br>";
    year_graphics($month, $year);

    // *** User agent ***
    echo '<br><b>' . __('User agent information') . '</b><br>';
    // *** Show user agent info (50 most used user agents) ***
    $datasql = $dbh->query("SELECT stat_user_agent, count(humo_stat_date.stat_user_agent) as count_lines
        FROM humo_stat_date
        WHERE stat_user_agent LIKE '_%'
        GROUP BY humo_stat_date.stat_user_agent
        ORDER BY count_lines desc
        LIMIT 0,50");

    while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
        $stat_user_agent = $dataDb->stat_user_agent;
        if (count_chars($stat_user_agent) > 100) {
            $stat_user_agent = substr($stat_user_agent, 0, 100) . '...';
        }
        echo '<b>' . $dataDb->count_lines . '</b> ' . $stat_user_agent . '<br>';
    }
}

if ($statistics_screen == 'statistics_old') {

    // *************************
    // *** OLD statistics ***
    // *************************

    // *** Select database ***
    @$datasql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
    $num_rows = $datasql->rowCount();
    if ($num_rows > 1) {
        echo '<h2>' . __('Old statistics (numbers since last GEDCOM update)') . '</h2>';

        echo '<b>' . __('Select family tree') . '</b><br>';
        while (@$dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
            if ($dataDb->tree_prefix != 'EMPTY') {
                // *** Update date ***
                $date = $dataDb->tree_date;
                $month = ''; //voor lege datums
                // TODO translate months.
                if (substr($date, 5, 2) == '01') {
                    $month = ' jan ';
                }
                if (substr($date, 5, 2) == '02') {
                    $month = ' feb ';
                }
                if (substr($date, 5, 2) == '03') {
                    $month = ' mrt ';
                }
                if (substr($date, 5, 2) == '04') {
                    $month = ' apr ';
                }
                if (substr($date, 5, 2) == '05') {
                    $month = ' mei ';
                }
                if (substr($date, 5, 2) == '06') {
                    $month = ' jun ';
                }
                if (substr($date, 5, 2) == '07') {
                    $month = ' jul ';
                }
                if (substr($date, 5, 2) == '08') {
                    $month = ' aug ';
                }
                if (substr($date, 5, 2) == '09') {
                    $month = ' sep ';
                }
                if (substr($date, 5, 2) == '10') {
                    $month = ' okt ';
                }
                if (substr($date, 5, 2) == '11') {
                    $month = ' nov ';
                }
                if (substr($date, 5, 2) == '12') {
                    $month = ' dec ';
                }
                $date = substr($date, 8, 2) . $month . substr($date, 0, 4);

                $treetext = show_tree_text($dataDb->tree_id, $selected_language);
                if ($dataDb->tree_id == $tree_id) {
                    echo '<b>' . $treetext['name'] . '</b>';
                } else {
                    echo '<a href="index.php?page=' . $page . '&amp;tree_id=' . $dataDb->tree_id . '">' . $treetext['name'] . '</a>';
                }
                echo ' <font size=-1>(' . $date . ': ' . $dataDb->tree_persons . ' ' . __('persons') . ", " . $dataDb->tree_families . ' ' . __('families') . ")</font>\n<br>";
            }
        }
    }

    //*** Statistics ***
    if (isset($tree_id) and $tree_id) $db_functions->set_tree_id($tree_id);
    echo '<br><b>' . __('Most visited families:') . '</b><br>';
    //MAXIMUM 50 LINES
    $family_qry = $dbh->query("SELECT fam_gedcomnumber, fam_tree_id, fam_counter, fam_man, fam_woman FROM humo_families
            WHERE fam_tree_id='" . $tree_id . "' AND fam_counter ORDER BY fam_counter desc LIMIT 0,50");
    while ($familyDb = $family_qry->fetch(PDO::FETCH_OBJ)) {
        $vars['pers_family'] = $familyDb->fam_gedcomnumber;
        $link = $link_cls->get_link('../', 'family', $familyDb->fam_tree_id, false, $vars);

        echo $familyDb->fam_counter . " ";
        echo '<a href="' . $link . '">' . __('Family') . ': </a>';

        //*** Man ***
        $personDb = $db_functions->get_person($familyDb->fam_man);
        if (!$familyDb->fam_man) {
            echo __('N.N.');
        } else {
            $name = $person_cls->person_name($personDb);
            echo $name["standard_name"];
        }

        echo " &amp; ";

        //*** Woman ***
        $personDb = $db_functions->get_person($familyDb->fam_woman);
        if (!$familyDb->fam_woman) {
            echo __('N.N.');
        } else {
            $name = $person_cls->person_name($personDb);
            echo $name["standard_name"];
        }
        echo "<br>";
    }
    // *** End of old statistics ***
}
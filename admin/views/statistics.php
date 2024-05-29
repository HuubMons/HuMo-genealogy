<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

require_once(__DIR__ . "/../statistics/maxChart.class.php"); // REQUIRED FOR STATISTICS

// *** Use a class to process person data ***
global $person_cls, $tab;
$person_cls = new person_cls;

// *** Show 1 month, statistics calender ***
// *** calender($month, $year, true/false); ***
function calender($month, $year, $thismonth)
{
    global $dbh, $language, $tab;

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
        if ($First_Day_Of_Month === "0") {
            echo '<td colspan="6"><br></td>';
        }

        // Show days
        $Days_In_Month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
        $day = 1;
        $row = 1;
        $field = $First_Day_Of_Month;
        if ($field === '0') {
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

            if ($tab == 'visitors') {
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
            if ($date === $present_day) {
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
    global $dbh, $language, $tab;
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

        if ($tab == 'visitors') {
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

    if ($tab == 'visitors') {
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
                        if ($country_code != __('Unknown') && $country_code) {
                            echo $countries[$country_code][1] . '&nbsp;(' . $country_code . ')';
                        } else {
                            echo $country_code;
                        }
                        ?>
                    </td>
                    <td><?= $statDb->count_country_code; ?></td>
                </tr>
            <?php } ?>
        </table>
<?php
    }
}
// *** End country statistics ***

$tab = 'general_statistics';
// TODO check these variables (partly from old tab menu)
if (isset($_POST['tab']) && $_POST['tab'] == 'date_statistics') {
    $tab = 'date_statistics';
}
if (isset($_POST['tab']) && $_POST['tab'] == 'visitors') {
    $tab = 'visitors';
}
if (isset($_POST['tab']) && $_POST['tab'] == 'statistics_old') {
    $tab = 'statistics_old';
}
if (isset($_POST['tab']) && $_POST['tab'] == 'remove') {
    $tab = 'remove';
}
if (isset($_GET['tree_id'])) {
    $tab = 'statistics_old';
}

// *** Bootstrap tab ***
if (isset($_GET['tab'])) {
    $tab = $_GET['tab'];
}

$phpself = 'index.php';
?>

<h1 class="center"><?= __('Statistics'); ?></h1>

<ul class="nav nav-tabs">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'general_statistics') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=general_statistics"><?= __('General statistics'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'date_statistics') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=date_statistics"><?= __('Statistics by date'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'visitors') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=visitors"><?= __('Visitors'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'statistics_old') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=statistics_old"><?= __('Old statistics'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($tab == 'remove') echo 'active'; ?>" href="index.php?page=<?= $page; ?>&amp;tab=remove"><?= __('Remove statistics'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;">
    <?php
    if ($tab == 'remove') {
        include(__DIR__ . '/statistics_remove.php');
    }
    if ($tab == 'general_statistics') {
        include(__DIR__ . '/statistics_general.php');
    }
    if ($tab == 'date_statistics') {
        include(__DIR__ . '/statistics_date.php');
    }
    if ($tab == 'visitors') {
        include(__DIR__ . '/statistics_visitors.php');
    }
    if ($tab == 'statistics_old') {
        include(__DIR__ . '/statistics_old.php');
    }
    ?>
</div>
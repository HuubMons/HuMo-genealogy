<?php
// *** Safety line ***
if (!defined('ADMIN_PAGE')) {
    exit;
}

// *** Use a class to process person data ***
global $statistics;
?>

<h1 class="center"><?= __('Statistics'); ?></h1>

<ul class="nav nav-tabs">
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($statistics['tab'] == 'general_statistics') echo 'active'; ?>" href="index.php?page=statistics&amp;tab=general_statistics"><?= __('General statistics'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($statistics['tab'] == 'date_statistics') echo 'active'; ?>" href="index.php?page=statistics&amp;tab=date_statistics"><?= __('Statistics by date'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($statistics['tab'] == 'visitors') echo 'active'; ?>" href="index.php?page=statistics&amp;tab=visitors"><?= __('Visitors'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($statistics['tab'] == 'statistics_families') echo 'active'; ?>" href="index.php?page=statistics&amp;tab=statistics_families"><?= __('Families'); ?></a>
    </li>
    <li class="nav-item me-1">
        <a class="nav-link genealogy_nav-link <?php if ($statistics['tab'] == 'remove') echo 'active'; ?>" href="index.php?page=statistics&amp;tab=remove"><?= __('Remove statistics'); ?></a>
    </li>
</ul>

<!-- Align content to the left -->
<div style="float: left; background-color:white; height:500px; padding:10px;">
    <?php
    if ($statistics['tab'] == 'remove') {
        include(__DIR__ . '/statistics_remove.php');
    } elseif ($statistics['tab'] == 'date_statistics') {
        include(__DIR__ . '/statistics_date.php');
    } elseif ($statistics['tab'] == 'visitors') {
        include(__DIR__ . '/statistics_visitors.php');
    } elseif ($statistics['tab'] == 'statistics_families') {
        include(__DIR__ . '/statistics_families.php');
    } else {
        // *** Default page ***
        include(__DIR__ . '/statistics_general.php');
    }
    ?>
</div>

<?php
// *** Show 1 month, statistics calender ***
// *** calender($month, $year, true/false); ***
function calender($month, $year, $thismonth)
{
    global $dbh, $language, $statistics;

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

    <table class="table">
        <thead class="table-primary">
            <tr>
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
        </thead>

        <?php
        $week = mktime(0, 0, 0, $month, 1, $year);
        $week_number = date("W", $week);
        $First_Day_Of_Month = date("w", mktime(0, 0, 0, $month, 1, $year));
        ?>

        <tr>
            <th><?= $week_number; ?></th>

            <?php
            // If neccesary skip days at start of month
            if ($First_Day_Of_Month > "1") {
                echo '<td colspan="' . ($First_Day_Of_Month - 1) . '"><br></td>';
            }
            // Sunday:
            if ($First_Day_Of_Month === "0") {
                echo '<td colspan="6"><br></td>';
            }

            $Days_In_Month = cal_days_in_month(CAL_GREGORIAN, $month, $year);
            $day = 1;
            $row = 1;
            $field = $First_Day_Of_Month;
            if ($field === '0') {
                // First day is sunday.
                $field = 7;
            }

            $i = 1;
            for ($i; $i <= $Days_In_Month; $i++) {
                $present_day = date("Y-n-d");
                if ($day < 10) {
                    $day = '0' . $day;
                }
                $date = $year . '-' . $month . '-' . $day;
                $yesterday = strtotime($date);
                $today = $yesterday + 86400;

                $graph_labels[] = $i;

                if ($statistics['tab'] == 'visitors') {
                    // *** Show visitors ***
                    $datasql = $dbh->query("SELECT stat_ip_address FROM humo_stat_date WHERE stat_date_linux > " . $yesterday . " AND stat_date_linux < " . $today . ' GROUP BY stat_ip_address');
                } else {
                    // *** Show families ***
                    $datasql = $dbh->query("SELECT * FROM humo_stat_date WHERE stat_date_linux > " . $yesterday . " AND stat_date_linux < " . $today);
                }

                if ($datasql) {
                    $nr_statistics = $datasql->rowCount();
                }
            ?>

                <td <?= $date === $present_day ? 'class="table-secondary"' : ''; ?>><?= $day; ?> <b><?= $nr_statistics; ?></b></td>

                <?php
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

                $graph_data[] = $nr_statistics;
            }

            // Add end month spacers
            if ((8 - $field) >= "1") {
                ?>
                <td colspan="<?= (8 - $field); ?>"><br></td>
            <?php } ?>
        </tr>

        <?php
        // *** Always create 6 rows ***
        if ($row == 5) {
        ?>
            <tr>
                <td colspan=8><br></td>
            </tr>
        <?php } ?>

    </table><br>

    <?php
    // *** Show graphical month statistics ***
    if ($statistics['tab'] == 'visitors') {
        $graph_label = __('Visitors');
    } else {
        $graph_label = __('Families');
    }
    ?>

    <div>
        <canvas id="myChartVisitors"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx1 = document.getElementById('myChartVisitors');
        var label = <?php echo json_encode($graph_label); ?>;
        var labels = <?php echo json_encode($graph_labels); ?>;
        var graph_data = <?php echo json_encode($graph_data); ?>;

        new Chart(ctx1, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: graph_data,
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
<?php
}

// *** Function to show year statistics ***
function year_graphics($month, $year)
{
    global $dbh, $language, $statistics;
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
        $date = $start_year . '-' . $start_month . '-1';
        $first_day = strtotime($date);
        $Days_In_Month = cal_days_in_month(CAL_GREGORIAN, $start_month, $start_year);
        $latest_day = $first_day + (86400 * $Days_In_Month);
        if ($statistics['tab'] == 'visitors') {
            // *** Show visitors ***
            $datasql = $dbh->query("SELECT stat_ip_address FROM humo_stat_date WHERE stat_date_linux > " . $first_day . " AND stat_date_linux < " . $latest_day . " GROUP BY stat_ip_address");
        } else {
            // *** Show visited families ***
            $datasql = $dbh->query("SELECT * FROM humo_stat_date WHERE stat_date_linux > " . $first_day . " AND stat_date_linux < " . $latest_day);

            //SELECT * FROM your_table WHERE your_date_column >= DATE_SUB('2024-12-01', INTERVAL 1 MONTH);
            //$datasql = $dbh->query(" SELECT * FROM humo_stat_date WHERE stat_date_linux>= DATE_SUB('" . $start_year . "-" . $start_month . "-01', INTERVAL 1 MONTH)");
        }

        if ($datasql) {
            $graph_data[] = $datasql->rowCount();
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
        $start_month++;

        $graph_labels[] = $month_name . ' ' . substr($start_year, 2);
    }
    //$this_month = date("n");

    // *** Calculate the last 12 months ***
    /*
    for ($i = 1; $i <= 12; $i++) {
        //$graph_labels[] = date("Y-m%", strtotime(date('Y-' . $month . '-01') . " -$i months"));
        $month_number = date("n", strtotime(date('Y-' . $month . '-01') . " -$i months"));
    }
    */

    if ($statistics['tab'] == 'visitors') {
        $graph_label = __('Visitors');
    } else {
        $graph_label = __('Families');
    }
?>

    <div>
        <canvas id="myChartFamilies"></canvas>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const ctx2 = document.getElementById('myChartFamilies');
        var label = <?php echo json_encode($graph_label); ?>;
        var labels = <?php echo json_encode($graph_labels); ?>;
        var graph_data = <?php echo json_encode($graph_data); ?>;

        new Chart(ctx2, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: label,
                    data: graph_data,
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
<?php
}

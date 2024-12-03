<?php
require_once __DIR__ . "/../models/admin_statistics.php";

require_once(__DIR__ . "/../statistics/maxChart.class.php"); // REQUIRED FOR STATISTICS

class StatisticsController
{
    public function detail($dbh)
    {
        $statisticsModel = new StatisticsModel($dbh);

        $statistics['tab'] = $statisticsModel->get_tab();

        $data = $statisticsModel->get_data($dbh);
        $statistics = array_merge($statistics, $data);

        return $statistics;
    }
}

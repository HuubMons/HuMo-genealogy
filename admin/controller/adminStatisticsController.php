<?php
require_once(__DIR__ . "/../statistics/maxChart.class.php"); // REQUIRED FOR STATISTICS

class AdminStatisticsController
{
    public function detail($dbh)
    {
        $statisticsModel = new AdminStatisticsModel($dbh);

        $statistics['tab'] = $statisticsModel->get_tab();

        $data = $statisticsModel->get_data($dbh);
        $statistics = array_merge($statistics, $data);

        return $statistics;
    }
}

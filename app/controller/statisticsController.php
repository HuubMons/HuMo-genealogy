<?php
include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");

class StatisticsController
{
    public function detail()
    {
        $statisticsModel = new StatisticsModel();
        $statistics["menu_tab"] = $statisticsModel->get_menu_tab();

        return $statistics;
    }
}

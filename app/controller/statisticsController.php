<?php
class StatisticsController
{
    public function detail()
    {
        $statisticsModel = new StatisticsModel();
        $statistics["menu_tab"] = $statisticsModel->get_menu_tab();

        return $statistics;
    }
}

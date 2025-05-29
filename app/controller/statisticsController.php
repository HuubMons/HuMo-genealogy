<?php
class StatisticsController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function detail(): array
    {
        $statisticsModel = new StatisticsModel();

        $statistics["menu_tab"] = $statisticsModel->get_menu_tab();
        if ($statistics["menu_tab"] == 'stats_tree') {
            include_once(__DIR__ . "/../../include/show_tree_date.php");

            $statsTreeModel = new StatsTreeModel($this->config);
            $data = $statsTreeModel->get_data();
            $statistics = array_merge($statistics, $data);
        } elseif ($statistics["menu_tab"] == 'stats_persons') {
            $statsPersonsModel = new StatsPersonsModel($this->config);
            $data = $statsPersonsModel->get_data();
            $statistics = array_merge($statistics, $data);
        } elseif ($statistics["menu_tab"] == 'stats_surnames') {
            // $statsSurnamesModel = new StatsSurnamesModel($this->config);
        } elseif ($statistics["menu_tab"] == 'stats_firstnames') {
            // $statsFirstnamesModel = new StatsFirstnamesModel($this->config);
        }

        return $statistics;
    }
}

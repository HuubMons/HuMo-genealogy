<?php
class StatisticsController
{
    public function detail($dbh, $db_functions, $tree_id)
    {
        $statisticsModel = new StatisticsModel();
        $statistics["menu_tab"] = $statisticsModel->get_menu_tab();

        if ($statistics["menu_tab"] == 'stats_tree'){
            include_once(__DIR__ . "/../../include/show_tree_date.php");

            $statsTreeModel = new StatsTreeModel($dbh);
            $data = $statsTreeModel->get_data($dbh, $db_functions, $tree_id);
            $statistics = array_merge($statistics, $data);
        }
        elseif ($statistics["menu_tab"] == 'stats_persons'){
            $statsPersonsModel = new StatsPersonsModel($dbh);
            $data = $statsPersonsModel->get_data($dbh, $db_functions, $tree_id);
            $statistics = array_merge($statistics, $data);
        }
        elseif ($statistics["menu_tab"] == 'stats_surnames'){
            // $statsSurnamesModel = new StatsSurnamesModel($dbh);
        }
        elseif ($statistics["menu_tab"] == 'stats_firstnames'){
            // $statsFirstnamesModel = new StatsFirstnamesModel($dbh);
        }

        return $statistics;
    }
}

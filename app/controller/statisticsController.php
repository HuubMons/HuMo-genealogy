<?php
class StatisticsController
{
    public function detail()
    {
        $statisticsModel = new StatisticsModel();
        $statistics["menu_tab"] = $statisticsModel->get_menu_tab();

        /*
        if ($statistics["menu_tab"] == 'stats_tree'){
            // $statsTreeModel = new StatsTreeModel($dbh);
        }
        elseif ($statistics["menu_tab"] == 'stats_persons'){
            // $statsPersonsModel = new StatsPersonsModel($dbh);
        }
        elseif ($statistics["menu_tab"] == 'stats_surnames'){
            // $statsSurnamesModel = new StatsSurnamesModel($dbh);
        }
        elseif ($statistics["menu_tab"] == 'stats_firstnames'){
            // $statsFirstnamesModel = new StatsFirstnamesModel($dbh);
        }
        */

        return $statistics;
    }
}

<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;
use PDO;

class AdminStatisticsModel extends AdminBaseModel
{
    public function get_tab(): string
    {
        $tab = 'general_statistics';
        if (isset($_POST['tab'])) {
            $tab = $_POST['tab'];
        }
        if (isset($_GET['tab'])) {
            $tab = $_GET['tab'];
        }
        return $tab;
    }

    public function get_data(): array
    {
        // *** Search oldest record in database***
        $firstYearQry = $this->dbh->query("SELECT * FROM humo_stat_date ORDER BY stat_date_linux LIMIT 0,1");
        $firstYear = $firstYearQry->fetch(PDO::FETCH_OBJ);
        $statistics['first_year'] = '';
        if (isset($firstYear->stat_date_linux)) {
            $statistics['first_year'] = date("Y", $firstYear->stat_date_linux);
        }

        // *** Selection of month ***
        $statistics['present_month'] = date("n");

        $statistics['month'] = $statistics['present_month'];
        if (isset($_POST['month'])) {
            $statistics['month'] = $_POST['month'];
        }

        $statistics['present_year'] = date("Y");

        $statistics['year'] = $statistics['present_year'];
        if (isset($_POST['year']) && is_numeric($_POST['year'])) {
            $statistics['year'] = $_POST['year'];
        }

        return $statistics;
    }
}

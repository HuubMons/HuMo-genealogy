<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\AdminStatisticsModel;

class AdminStatisticsController
{
protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $statisticsModel = new AdminStatisticsModel($this->admin_config);

        $statistics['tab'] = $statisticsModel->get_tab();

        $data = $statisticsModel->get_data();
        $statistics = array_merge($statistics, $data);

        return $statistics;
    }
}

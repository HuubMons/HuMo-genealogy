<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\LogModel;

class LogController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $logModel = new LogModel($this->admin_config);

        $log['menu_tab'] = $logModel->get_menu_tab();

        $logModel->update_ip($log['menu_tab']);

        return $log;
    }
}

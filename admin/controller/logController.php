<?php
require_once __DIR__ . "/../models/log.php";

class LogController
{
    /*
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }
    */

    public function detail($dbh)
    {
        $logModel = new LogModel();
        $log['menu_tab'] = $logModel->get_menu_tab();

        $logModel->update_ip($dbh, $log['menu_tab']);

        return $log;
    }
}

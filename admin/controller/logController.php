<?php
class LogController
{
    public function detail($dbh)
    {
        $logModel = new LogModel();
        $log['menu_tab'] = $logModel->get_menu_tab();

        $logModel->update_ip($dbh, $log['menu_tab']);

        return $log;
    }
}

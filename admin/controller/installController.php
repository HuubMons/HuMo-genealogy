<?php
class InstallController
{
    public function detail($dbh)
    {
        $installModel = new InstallModel($dbh);
        $install = $installModel->check_tables($dbh);

        return $install;
    }
}

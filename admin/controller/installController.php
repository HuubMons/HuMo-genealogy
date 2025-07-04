<?php
class InstallController
{
    /*
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }
    */

    public function detail($dbh): array
    {
        $installModel = new InstallModel();

        $install = $installModel->check_tables($dbh);

        return $install;
    }
}

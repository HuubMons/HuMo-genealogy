<?php
class BackupController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $backupModel = new BackupModel($this->admin_config);

        $backup['menu_tab'] = $backupModel->get_menu_tab();
        $backupModel->process_old_files();
        $backup['upload_status'] = $backupModel->upload_backup_file();

        return $backup;
    }
}

<?php
class BackupController
{
    public function detail($dbh)
    {
        $backupModel = new BackupModel($dbh);

        $backup['menu_tab'] = $backupModel->get_menu_tab();

        $backupModel->process_old_files();

        $backup['upload_status'] = $backupModel->upload_backup_file();

        return $backup;
    }
}

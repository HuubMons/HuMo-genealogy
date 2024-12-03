<?php
require_once __DIR__ . "/../models/backup.php";

class BackupController
{
    public function detail($dbh)
    {
        $backupModel = new BackupModel($dbh);

        $backupModel->process_old_files();

        $backup['upload_status'] = $backupModel->upload_backup_file();

        return $backup;
    }
}

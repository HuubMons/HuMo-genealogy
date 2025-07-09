<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;

class BackupModel extends AdminBaseModel
{
    function get_menu_tab(): string
    {
        $menu_tab = 'database_backup';
        if (isset($_POST['menu_tab'])) {
            $menu_tab = $_POST['menu_tab'];
        }
        if (isset($_GET['menu_tab'])) {
            $menu_tab = $_GET['menu_tab'];
        }

        return $menu_tab;
    }

    public function process_old_files(): void
    {
        // *** Rename and remove files from previous backup procedure ***
        if (file_exists('humo_backup.sql.zip')) {
            $new_file_name = 'backup_files/' . date("Y_m_d_H_i", filemtime('humo_backup.sql.zip')) . '_humo-genealogy_backup.sql.zip';
            rename('humo_backup.sql.zip', $new_file_name);

            if (file_exists('downloadbk.php')) {
                unlink('downloadbk.php');
            }
        }
        if (file_exists('backup_tmp/readme.txt')) {
            unlink('backup_tmp/readme.txt');
            rmdir('backup_tmp');
        }
    }

    public function upload_backup_file(): string
    {
        $upload_status = '';
        if (isset($_POST['upload_the_file'])) {
            if (substr($_FILES['upload_file']['name'], -4) === ".sql" || substr($_FILES['upload_file']['name'], -8) === ".sql.zip") {
                if (move_uploaded_file($_FILES['upload_file']['tmp_name'], './backup_files/' . $_FILES['upload_file']['name'])) {
                    $upload_status = 'successful';
                } else {
                    $upload_status = 'upload failed';
                }
            } else {
                $upload_status = 'wrong extension';
            }
        }
        return $upload_status;
    }
}

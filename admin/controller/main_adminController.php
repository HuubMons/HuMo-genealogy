<?php
require_once __DIR__ . "/../models/main_admin.php";

// *** Only logoff admin ***
if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name_admin']);
    unset($_SESSION['user_id_admin']);
    unset($_SESSION['group_id_admin']);
}

$ADMIN = TRUE; // *** Override "no database" message for admin ***
include_once(__DIR__ . "/../../include/db_login.php"); // *** Database login ***

include_once(__DIR__ . "/../../include/safe.php"); // Variables

// *** Function to show family tree texts ***
include_once(__DIR__ . '/../../include/show_tree_text.php');

class Main_adminController
{
    public function detail($dbh)
    {
        $main_adminModel = new Main_adminModel();
        /*
        $index['php_version'] = $indexModel->get_php_version();

        $index_array1 = $indexModel->database_settings($database_check);
        //$index = array_merge($index, $index_array);

        $index_array2 = $indexModel->get_mysql_version($dbh);
        //$index = array_merge($index, $index_array);

        $index = array_merge($index, $index_array1, $index_array2);
        */

        //return $main_admin;
    }
}

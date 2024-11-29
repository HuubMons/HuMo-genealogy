<?php
require_once __DIR__ . "/../models/index_admin.php";

class IndexController
{
    public function detail($database_check, $dbh)
    {
        $indexModel = new IndexModel();

        $index['php_version'] = $indexModel->get_php_version();

        $index_array1 = $indexModel->database_settings($database_check);
        //$index = array_merge($index, $index_array);

        $index_array2 = $indexModel->get_mysql_version($dbh);
        //$index = array_merge($index, $index_array);

        $index = array_merge($index, $index_array1, $index_array2);



        // TODO: move to model.
        // *** Check if database and tables are ok ***
        $index['install_status'] = true;

        if (!$index['database_check']) {
            $index['install_status'] = true;

            $index['db_host'] = 'localhost';
            if (isset($_POST['db_host'])) {
                $index['db_host'] = $_POST['db_host'];
            }

            $index['db_username'] = 'root';
            if (isset($_POST['db_username'])) {
                $index['db_username'] = $_POST['db_username'];
            }

            $index['db_password'] = '';
            if (isset($_POST['db_password'])) {
                $index['db_password'] = $_POST['db_password'];
            }

            $index['db_name'] = 'humo-gen';
            if (isset($_POST['db_name'])) {
                $index['db_name'] = $_POST['db_name'];
            }
        }



        return $index;
    }
}

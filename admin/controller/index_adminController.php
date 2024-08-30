<?php
require_once __DIR__ . "/../models/index_admin.php";

class IndexController
{
    /*
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }
    */

    public function detail($database_check, $dbh)
    {
        $indexModel = new IndexModel();

        $index['php_version'] = $indexModel->get_php_version();

        $index_array1 = $indexModel->database_settings($database_check);
        //$index = array_merge($index, $index_array);

        $index_array2 = $indexModel->get_mysql_version($dbh);
        //$index = array_merge($index, $index_array);

        $index = array_merge($index, $index_array1, $index_array2);

        return $index;
    }
}

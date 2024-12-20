<?php
class BaseController
{
    protected $dbh, $db_functions, $tree_id;
    //$user

    public function __construct($dbh, $db_functions, $tree_id)
    {
        $this->dbh = $dbh;
        $this->db_functions = $db_functions;
        $this->tree_id = $tree_id;
    }
}

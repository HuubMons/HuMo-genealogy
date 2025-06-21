<?php

// May 2025: added BaseModel class.

class BaseModel
{
    protected $dbh, $db_functions, $tree_id, $user, $humo_option;

    public function __construct($config)
    {
        $this->dbh = $config['dbh'];
        $this->db_functions = $config['db_functions'];
        $this->tree_id = $config['tree_id'];
        $this->user = $config['user'];
        $this->humo_option = $config['humo_option'];
    }
}

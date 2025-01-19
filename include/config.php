<?php

/**
 * Dec. 2024: added config class
 */

class Config
{
    public $dbh;
    public $db_functions;
    public $tree_id;
    public $user;
    public $humo_option;

    public function __construct($dbh, $db_functions, $tree_id, $user, $humo_option)
    {
        $this->dbh = $dbh;
        $this->db_functions = $db_functions;
        $this->tree_id = $tree_id;
        $this->user = $user;
        $this->humo_option = $humo_option;
    }
}

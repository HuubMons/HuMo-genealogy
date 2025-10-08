<?php

/**
 * May 2025: added BaseModel class.
 */

namespace Genealogy\App\Model;

class BaseModel
{
    protected $dbh, $db_functions, $tree_id, $user, $humo_option, $uri_path, $selectedFamilyTree;

    public function __construct($config)
    {
        $this->dbh = $config['dbh'];
        $this->db_functions = $config['db_functions'];
        $this->tree_id = $config['tree_id'];
        $this->user = $config['user'];
        $this->humo_option = $config['humo_option'];
        $this->uri_path = $config['uri_path'];
        $this->selectedFamilyTree = $config['selectedFamilyTree'];

        // *** Set tree_id in db_functions ***
        $this->db_functions->set_tree_id($this->tree_id);
    }
}

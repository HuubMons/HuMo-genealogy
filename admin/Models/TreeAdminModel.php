<?php

namespace Genealogy\Admin\Models;

use PDO;

class TreeAdminModel
{
    public function count_trees($dbh)
    {
        // *** Check number of family trees, because last tree is not allowed to be removed ***
        $datasql = $dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        return $datasql->rowCount();
    }

    public function get_collation($dbh)
    {
        // ** Collation of family tree (needed for Swedish etc.) ***
        $collation_sql = $dbh->query("SHOW FULL COLUMNS FROM humo_persons WHERE Field = 'pers_firstname'");
        $collationDb = $collation_sql->fetch(PDO::FETCH_OBJ);
        return $collationDb->Collation;
    }
}

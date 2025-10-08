<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration6
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Implement the migration logic here.

        // *** Read all family trees from database ***
        $update_sql = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            // *** Update family table ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD INDEX (fam_man), ADD INDEX (fam_woman)";
            $this->dbh->query($sql);
        }
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

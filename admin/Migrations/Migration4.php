<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration4
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // *** Read all family trees from database ***
        $update_sql = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            echo '<b>Check ' . $updateDb->tree_prefix . '</b><br>';

            // *** Update events table ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "events ADD event_gedcomnr varchar(20) CHARACTER SET utf8 AFTER event_id";
            $this->dbh->query($sql);

            // *** Update person table ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_sexe_source text CHARACTER SET utf8 AFTER pers_sexe";
            $this->dbh->query($sql);
        }
    }

    public function down(): void {}
}

<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration7
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Implement the migration logic here.

        // *** Save GEDCOM file name and GEDCOM program in database ***
        // *** Test for existing column, some users already tried a new script including a database update ***
        $result = $this->dbh->query("SHOW COLUMNS FROM `humo_trees` LIKE 'tree_gedcom'");
        if ($result->rowCount() == 0) {
            $this->dbh->query("ALTER TABLE humo_trees ADD COLUMN tree_gedcom varchar (100)");
        }

        $this->dbh->query("ALTER TABLE humo_trees ADD COLUMN tree_gedcom_program varchar (100)");

        // *** Bug in table, change user_group_id ***
        $this->dbh->query("ALTER TABLE humo_users CHANGE user_group_id user_group_id smallint(5)");

        // *** Add new table, for user notes ***
        $this->dbh->query("CREATE TABLE humo_user_notes (
            note_id smallint(5) unsigned NOT NULL auto_increment,
            note_date varchar(20) CHARACTER SET utf8,
            note_time varchar(25) CHARACTER SET utf8,
            note_user_id smallint(5),
            note_note text CHARACTER SET utf8,
            note_status varchar(10) CHARACTER SET utf8,
            note_tree_prefix varchar(25) CHARACTER SET utf8,
            note_pers_gedcomnumber varchar(20) CHARACTER SET utf8,
            note_fam_gedcomnumber varchar(20) CHARACTER SET utf8,
            note_names text CHARACTER SET utf8,
            PRIMARY KEY  (`note_id`)
            ) DEFAULT CHARSET=utf8");
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

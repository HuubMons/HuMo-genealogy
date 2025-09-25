<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration5
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
            //ob_start();
            echo '<b>Check ' . $updateDb->tree_prefix . '</b><br>';
            //ob_flush();
            flush();

            // *** Automatic installation or update ***
            if (isset($field)) {
                unset($field);
            }
            $column_qry = $this->dbh->query("SHOW COLUMNS FROM " . $updateDb->tree_prefix . "person");
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
                // *** test line ***
                //print '<span>'.$field[$field_value].'</span><br>';
            }

            // *** Batch processing ***
            //$this->dbh->beginTransaction();

            if (!isset($field['pers_unprocessed_tags'])) {
                // *** Update person table ***
                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_unprocessed_tags text CHARACTER SET utf8 AFTER pers_favorite";
                $this->dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_unprocessed_tags text CHARACTER SET utf8 AFTER fam_counter";
                $this->dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "texts ADD text_unprocessed_tags text CHARACTER SET utf8 AFTER text_text";
                $this->dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "sources ADD source_unprocessed_tags text CHARACTER SET utf8 AFTER source_repo_gedcomnr";
                $this->dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "addresses ADD address_unprocessed_tags text CHARACTER SET utf8 AFTER address_photo";
                $this->dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "events ADD event_unprocessed_tags text CHARACTER SET utf8 AFTER event_text";
                $this->dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "connections ADD connect_unprocessed_tags text CHARACTER SET utf8 AFTER connect_status";
                $this->dbh->query($sql);

                $sql = "ALTER TABLE " . $updateDb->tree_prefix . "repositories ADD repo_unprocessed_tags text CHARACTER SET utf8 AFTER repo_url";
                $this->dbh->query($sql);
            }

            // *** Update tree tables ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "person ADD pers_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER pers_alive";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "family ADD fam_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER fam_alive";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "texts ADD text_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER text_text";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "sources ADD source_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER source_repo_gedcomnr";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "addresses ADD address_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER address_photo";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "events
                ADD event_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER event_text,
                ADD event_event_extra text CHARACTER SET utf8 AFTER event_event";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "connections ADD connect_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER connect_status";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "repositories ADD repo_quality varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER repo_url";
            $this->dbh->query($sql);

            // *** Commit data in database ***
            //$this->dbh->commit();
        }

        $sql = "ALTER TABLE humo_users
            ADD user_mail varchar(100) CHARACTER SET utf8 AFTER user_name,
            ADD user_trees text CHARACTER SET utf8 AFTER user_mail,
            ADD user_remark text CHARACTER SET utf8 AFTER user_trees,
            ADD user_status varchar(1) CHARACTER SET utf8 AFTER user_remark,
            ADD user_register_date varchar(20) CHARACTER SET utf8 AFTER user_group_id,
            ADD user_last_visit varchar(25) CHARACTER SET utf8 AFTER user_register_date";
        $this->dbh->query($sql);
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

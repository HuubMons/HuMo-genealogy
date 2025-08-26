<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration11
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Implement the migration logic here.

        flush();

        // *** Batch processing ***
        //$this->dbh->beginTransaction();

        // *** Update for 2 TYPE including long text: "2 TYPE E-mail address" ***
        $sql = "ALTER TABLE humo_events CHANGE event_gedcom event_gedcom VARCHAR(25) CHARACTER SET utf8,
            CHANGE event_gedcomnr event_gedcomnr VARCHAR(25) CHARACTER SET utf8,
            CHANGE event_connect_id event_connect_id VARCHAR(25) CHARACTER SET utf8,
            CHANGE event_id event_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        $this->dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update events table";</script>';
        //ob_flush();
        flush();

        // *** Automatic installation or update ***
        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_users');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['user_hide_trees'])) {
            $sql = "ALTER TABLE humo_users ADD user_hide_trees VARCHAR(200) CHARACTER SET utf8 NOT NULL DEFAULT '' AFTER user_group_id;";
            $this->dbh->query($sql);
        }
        if (!isset($field['user_edit_trees'])) {
            $sql = "ALTER TABLE humo_users ADD user_edit_trees VARCHAR(200) CHARACTER SET utf8 NOT NULL DEFAULT '' AFTER user_hide_trees;";
            $this->dbh->query($sql);
        }
        unset($field);

        // *** Change MEDIUMINT(6) into INT(10) for large family trees ***
        $sql = "ALTER TABLE humo_persons CHANGE pers_id pers_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE pers_gedcomnumber pers_gedcomnumber VARCHAR(25) CHARACTER SET utf8,
            CHANGE pers_indexnr pers_indexnr VARCHAR(25) CHARACTER SET utf8";
        $this->dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update pers table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_families CHANGE fam_id fam_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE fam_gedcomnumber fam_gedcomnumber VARCHAR(25) CHARACTER SET utf8,
            CHANGE fam_man fam_man VARCHAR(25) CHARACTER SET utf8,
            CHANGE fam_woman fam_woman VARCHAR(25) CHARACTER SET utf8";
        $this->dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update fam table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_addresses CHANGE address_id address_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE address_gedcomnr address_gedcomnr VARCHAR(25) CHARACTER SET utf8,
            CHANGE address_connect_id address_connect_id VARCHAR(25) CHARACTER SET utf8";
        $this->dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update address table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_connections CHANGE connect_id connect_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE connect_connect_id connect_connect_id VARCHAR(25) CHARACTER SET utf8,
            CHANGE connect_source_id connect_source_id VARCHAR(25) CHARACTER SET utf8,
            CHANGE connect_item_id connect_item_id VARCHAR(25) CHARACTER SET utf8";
        $this->dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update connect table";</script>';
        //ob_flush();
        flush();

        try {
            $sql = "ALTER TABLE humo_location CHANGE location_id location_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT";
            $this->dbh->query($sql);
        } catch (Exception $e) {
            //
        }

        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update location table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_sources CHANGE source_id source_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE source_gedcomnr source_gedcomnr VARCHAR(25) CHARACTER SET utf8,
            CHANGE source_repo_gedcomnr source_repo_gedcomnr VARCHAR(25) CHARACTER SET utf8";
        $this->dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update source table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_texts CHANGE text_id text_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE text_gedcomnr text_gedcomnr VARCHAR(25) CHARACTER SET utf8";
        $this->dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update texts table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_repositories CHANGE repo_id repo_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT,
            CHANGE repo_gedcomnr repo_gedcomnr varchar(25) CHARACTER SET utf8";
        $this->dbh->query($sql);
        // *** Show status of database update ***
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update repo table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_user_notes ADD note_tree_id mediumint(7) AFTER note_status,
            CHANGE note_pers_gedcomnumber note_pers_gedcomnumber varchar(25) CHARACTER SET utf8,
            CHANGE note_fam_gedcomnumber note_fam_gedcomnumber varchar(25) CHARACTER SET utf8;";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_unprocessed_tags
            CHANGE tag_pers_id tag_pers_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_rel_id tag_rel_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_address_id tag_address_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_connect_id tag_connect_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_event_id tag_event_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_place_id tag_place_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_source_id tag_source_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_text_id tag_text_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_repo_id tag_repo_id INT(10) UNSIGNED NULL DEFAULT NULL,
            CHANGE tag_id tag_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        $this->dbh->query($sql);
        //ob_start();
        echo '<script>document.getElementById("information v5_2_5").innerHTML="Update unprocessed table";</script>';
        //ob_flush();
        flush();

        $sql = "ALTER TABLE humo_stat_date
            CHANGE stat_gedcom_fam stat_gedcom_fam varchar(25) CHARACTER SET utf8,
            CHANGE stat_gedcom_man stat_gedcom_man varchar(25) CHARACTER SET utf8,
            CHANGE stat_gedcom_woman stat_gedcom_woman varchar(25) CHARACTER SET utf8";
        $this->dbh->query($sql);

        // *** Table humo_users_notes isn't always available ***
        try {
            $sql = "ALTER TABLE humo_users_notes
                CHANGE note_pers_gedcomnumber note_pers_gedcomnumber varchar(25) CHARACTER SET utf8,
                CHANGE note_fam_gedcomnumber note_fam_gedcomnumber varchar(25) CHARACTER SET utf8";
            $this->dbh->query($sql);
        } catch (Exception $e) {
            //
        }

        flush();
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration16
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Implement the migration logic here.

        global $db_functions;

        //ob_flush();
        flush();

        // *** Check table user_notes ***
        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_user_notes');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        // *** Automatic update ***
        if (!isset($field['note_order'])) {
            $sql = "ALTER TABLE humo_user_notes CHANGE note_date note_new_date varchar(20) CHARACTER SET utf8;";
            $this->dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes CHANGE note_time note_new_time varchar(25) CHARACTER SET utf8;";
            $this->dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes CHANGE note_user_id note_new_user_id smallint(5);";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE humo_user_notes ADD note_changed_date varchar(20) CHARACTER SET utf8 AFTER note_new_user_id;";
            $this->dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes ADD note_changed_time varchar(25) CHARACTER SET utf8 AFTER note_changed_date;";
            $this->dbh->query($sql);
            $sql = "ALTER TABLE humo_user_notes ADD note_changed_user_id smallint(5) AFTER note_changed_time;";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE humo_user_notes ADD note_priority varchar(15) CHARACTER SET utf8 AFTER note_status;";
            $this->dbh->query($sql);

            $sql = "ALTER TABLE humo_user_notes CHANGE note_status note_status varchar(15) CHARACTER SET utf8;";
            $this->dbh->query($sql);

            // *** Add note_order ***
            $sql = "ALTER TABLE humo_user_notes ADD note_order smallint(5) AFTER note_id;";
            $this->dbh->query($sql);

            // *** Add note_connect_kind = person/ family/ source/ repository ***
            $sql = "ALTER TABLE humo_user_notes ADD note_connect_kind varchar(20) CHARACTER SET utf8 AFTER note_tree_id;";
            $this->dbh->query($sql);

            // *** Add note_kind = user/ editor ***
            $sql = "ALTER TABLE humo_user_notes ADD note_kind varchar(10) CHARACTER SET utf8 AFTER note_tree_id;";
            $this->dbh->query($sql);

            // *** Change all existing note_connect_kind items into 'person' ***
            $sql = "UPDATE humo_user_notes SET note_connect_kind='person';";
            $this->dbh->query($sql);

            // *** Change note_pers_gedcomnumber into: note_connect_id ***
            $sql = "ALTER TABLE humo_user_notes CHANGE note_pers_gedcomnumber note_connect_id VARCHAR(25) CHARACTER SET utf8;";
            $this->dbh->query($sql);

            // *** Update tree_id, could be missing in some cases ***
            $sql = "SELECT * FROM humo_user_notes LEFT JOIN humo_trees ON note_tree_prefix=tree_prefix ORDER BY note_id;";
            $qry = $this->dbh->query($sql);
            while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                $sql2 = "UPDATE humo_user_notes SET note_tree_id='" . $qryDb->tree_id . "', note_kind='user' WHERE note_id='" . $qryDb->note_id . "'";
                $this->dbh->query($sql2);
            }

            // *** Remove note_fam_gedcomnumber ***
            $sql = "ALTER TABLE humo_user_notes DROP note_fam_gedcomnumber;";
            $this->dbh->query($sql);

            // *** Remove note_fam_gedcomnumber ***
            $sql = "ALTER TABLE humo_user_notes DROP note_tree_prefix;";
            $this->dbh->query($sql);
        }

        // *** Remove "NOT NULL" from hebnight variables ***
        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_persons');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (isset($field['pers_birth_date_hebnight'])) {
            $sql = "ALTER TABLE humo_persons CHANGE pers_birth_date_hebnight pers_birth_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $this->dbh->query($sql);
        }
        if (isset($field['pers_death_date_hebnight'])) {
            $sql = "ALTER TABLE humo_persons CHANGE pers_death_date_hebnight pers_death_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $this->dbh->query($sql);
        }
        if (isset($field['pers_buried_date_hebnight'])) {
            $sql = "ALTER TABLE humo_persons CHANGE pers_buried_date_hebnight pers_buried_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $this->dbh->query($sql);
        }

        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_families');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (isset($field['fam_marr_notice_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_notice_date_hebnight fam_marr_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $this->dbh->query($sql);
        }
        if (isset($field['fam_marr_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_date_hebnight fam_marr_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $this->dbh->query($sql);
        }
        if (isset($field['fam_marr_church_notice_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_church_notice_date_hebnight fam_marr_church_notice_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $this->dbh->query($sql);
        }
        if (isset($field['fam_marr_church_date_hebnight'])) {
            $sql = "ALTER TABLE humo_families CHANGE fam_marr_church_date_hebnight fam_marr_church_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $this->dbh->query($sql);
        }

        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_events');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (isset($field['event_date_hebnight'])) {
            $sql = "ALTER TABLE humo_events CHANGE event_date_hebnight event_date_hebnight VARCHAR(10) CHARACTER SET utf8;";
            $this->dbh->query($sql);
        }

        // *** Create table humo_stat_country
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_stat_country'");
        if (!$temp->rowCount()) {
            $qry = "CREATE TABLE humo_stat_country (
                stat_country_id INT(10) NOT NULL AUTO_INCREMENT PRIMARY KEY,
                stat_country_ip_address varchar(40) DEFAULT NULL,
                stat_country_code VARCHAR(10) CHARACTER SET utf8
            )";
            $this->dbh->query($qry);
        }

        $this->dbh->query("ALTER TABLE `humo_texts` ADD KEY `text_gedcomnr` (`text_gedcomnr`)");

        $sql = "ALTER TABLE humo_events
            ADD event_connect_kind2 varchar(25) CHARACTER SET utf8 AFTER event_connect_id,
            ADD event_connect_id2 varchar(25) DEFAULT NULL AFTER event_connect_kind2";
        $this->dbh->query($sql);

        $this->dbh->query("ALTER TABLE `humo_events` ADD KEY `event_connect_id2` (`event_connect_id2`)");

        // loop humo_events, copy @ numbers to new event_connect_id2.
        // *** Batch processing ***
        $this->dbh->beginTransaction();
        $sql = "SELECT event_id, event_event FROM humo_events WHERE LEFT(event_event,1)= '@'
            AND (event_kind='birth_declaration' OR event_kind='baptism_witness'
            OR event_kind='death_declaration' OR event_kind='burial_witness'
            OR event_kind='marriage_witness' OR event_kind='marriage_witness_rel')
        ";
        // Bug: forgot to change event_connect_kind2 into person. Solved in later update.
        $qry = $this->dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = "UPDATE humo_events SET
                event_connect_id2='" . substr($qryDb->event_event, 1, -1) . "',
                event_event=''
                WHERE event_id= '" . $qryDb->event_id . "'";
            $this->dbh->query($gebeurtsql);
        }
        // *** Commit data in database ***
        $this->dbh->commit();
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

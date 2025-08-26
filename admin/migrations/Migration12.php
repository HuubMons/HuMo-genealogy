<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration12
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

        // *** Add user_password_salted field to humo_users table ***
        $result = $this->dbh->query("SHOW COLUMNS FROM `humo_users` LIKE 'user_password_salted'");
        $exists = $result->rowCount();
        if (!$exists) {
            $this->dbh->query("ALTER TABLE humo_users ADD user_password_salted VARCHAR(255) CHARACTER SET utf8 AFTER user_password;");
        }

        $sql = "ALTER TABLE humo_sources
            CHANGE source_subj source_subj varchar(248) CHARACTER SET utf8,
            CHANGE source_place source_place varchar(120) CHARACTER SET utf8";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_user_log CHANGE log_ip_address log_ip_address varchar(45) CHARACTER SET utf8";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_repositories CHANGE repo_phone repo_phone varchar(25) CHARACTER SET utf8";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_users ADD user_ip_address VARCHAR(45) CHARACTER SET utf8 DEFAULT '' AFTER user_edit_trees;";
        $this->dbh->query($sql);

        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_persons');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['pers_new_user'])) {
            $sql = "ALTER TABLE humo_persons ADD pers_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER pers_quality;";
            $this->dbh->query($sql);
        }
        if (!isset($field['pers_changed_user'])) {
            $sql = "ALTER TABLE humo_persons ADD pers_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER pers_new_user;";
            $this->dbh->query($sql);
        }

        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_families');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['fam_new_user'])) {
            $sql = "ALTER TABLE humo_families ADD fam_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER fam_counter;";
            $this->dbh->query($sql);
        }
        if (!isset($field['fam_changed_user'])) {
            $sql = "ALTER TABLE humo_families ADD fam_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER fam_new_user;";
            $this->dbh->query($sql);
        }

        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_sources');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['source_new_user'])) {
            $sql = "ALTER TABLE humo_sources ADD source_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER source_quality;";
            $this->dbh->query($sql);
        }
        if (!isset($field['source_changed_user'])) {
            $sql = "ALTER TABLE humo_sources ADD source_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER source_new_user;";
            $this->dbh->query($sql);
        }

        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_repositories');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['repo_new_user'])) {
            $sql = "ALTER TABLE humo_repositories ADD repo_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER repo_quality;";
            $this->dbh->query($sql);
        }
        if (!isset($field['repo_changed_user'])) {
            $sql = "ALTER TABLE humo_repositories ADD repo_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER repo_new_user;";
            $this->dbh->query($sql);
        }

        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_addresses');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['address_new_user'])) {
            $sql = "ALTER TABLE humo_addresses ADD address_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER address_quality;";
            $this->dbh->query($sql);
        }
        if (!isset($field['address_changed_user'])) {
            $sql = "ALTER TABLE humo_addresses ADD address_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER address_new_user;";
            $this->dbh->query($sql);
        }

        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_events');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['event_new_user'])) {
            $sql = "ALTER TABLE humo_events ADD event_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER event_quality;";
            $this->dbh->query($sql);
        }
        if (!isset($field['event_changed_user'])) {
            $sql = "ALTER TABLE humo_events ADD event_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER event_new_user;";
            $this->dbh->query($sql);
        }

        flush();
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

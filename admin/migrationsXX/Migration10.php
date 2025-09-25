<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration10
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Implement the migration logic here.

        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_groups');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['group_source_presentation'])) {
            $sql = "ALTER TABLE humo_groups ADD group_source_presentation VARCHAR(20) NOT NULL DEFAULT 'title' AFTER group_sources;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_text_presentation'])) {
            $sql = "ALTER TABLE humo_groups ADD group_text_presentation VARCHAR(20) NOT NULL DEFAULT 'show' AFTER group_source_presentation;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_show_restricted_source'])) {
            $sql = "ALTER TABLE humo_groups ADD group_show_restricted_source VARCHAR(1) NOT NULL DEFAULT 'y' AFTER group_sources;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_death_date_act'])) {
            $sql = "ALTER TABLE humo_groups ADD group_death_date_act VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_alive_date;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_death_date'])) {
            $sql = "ALTER TABLE humo_groups ADD group_death_date VARCHAR(4) NOT NULL DEFAULT '1980' AFTER group_death_date_act;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_menu_persons'])) {
            $sql = "ALTER TABLE humo_groups ADD group_menu_persons VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_statistics;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_menu_names'])) {
            $sql = "ALTER TABLE humo_groups ADD group_menu_names VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_statistics;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_menu_login'])) {
            $sql = "ALTER TABLE humo_groups ADD group_menu_login VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_menu_names;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_showstatistics'])) {
            $sql = "ALTER TABLE humo_groups ADD group_showstatistics VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_relcalc'])) {
            $sql = "ALTER TABLE humo_groups ADD group_relcalc VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_googlemaps'])) {
            $sql = "ALTER TABLE humo_groups ADD group_googlemaps VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_contact'])) {
            $sql = "ALTER TABLE humo_groups ADD group_contact VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_latestchanges'])) {
            $sql = "ALTER TABLE humo_groups ADD group_latestchanges VARCHAR(1) NOT NULL DEFAULT 'j' AFTER group_birthday_list;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_pdf_button'])) {
            $sql = "ALTER TABLE humo_groups ADD group_pdf_button VARCHAR(1) NOT NULL DEFAULT 'y' AFTER group_own_code;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_rtf_button'])) {
            $sql = "ALTER TABLE humo_groups ADD group_rtf_button VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_pdf_button;";
            $this->dbh->query($sql);

            // *** Show RTF button in usergroup "Admin" ***
            $sql = "UPDATE humo_groups SET group_rtf_button='y' WHERE group_id=1";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_user_notes'])) {
            $sql = "ALTER TABLE humo_groups ADD group_user_notes VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_own_code;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_user_notes_show'])) {
            $sql = "ALTER TABLE humo_groups ADD group_user_notes_show VARCHAR(1) NOT NULL DEFAULT 'n' AFTER group_user_notes;";
            $this->dbh->query($sql);
        }

        if (!isset($field['group_family_presentation'])) {
            $sql = "ALTER TABLE humo_groups ADD group_family_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'compact' AFTER group_pdf_button;";
            $this->dbh->query($sql);
        }
        if (!isset($field['group_maps_presentation'])) {
            $sql = "ALTER TABLE humo_groups ADD group_maps_presentation VARCHAR(10) CHARACTER SET utf8 NOT NULL DEFAULT 'hide' AFTER group_family_presentation;";
            $this->dbh->query($sql);
        }

        if (!isset($field['group_edit trees'])) {
            $sql = "ALTER TABLE humo_groups ADD group_edit_trees VARCHAR(200) CHARACTER SET utf8 NOT NULL DEFAULT '' AFTER group_hide_trees;";
            $this->dbh->query($sql);
        }

        $sql = "ALTER TABLE humo_groups ADD group_hide_photocat varchar(200) NOT NULL DEFAULT ''";
        $this->dbh->query($sql);

        // *** Update user_log table ***
        $sql = "ALTER TABLE humo_user_log ADD log_id mediumint(6) unsigned NOT NULL auto_increment FIRST, ADD PRIMARY KEY (`log_id`),
        ADD log_status varchar(10) CHARACTER SET utf8 DEFAULT '' AFTER log_user_admin";
        $this->dbh->query($sql);

        $this->dbh->query("UPDATE humo_user_log SET log_status='success'");

        // *** Update address table ***
        $sql = "ALTER TABLE humo_addresses ADD address_connect_kind varchar(25) DEFAULT NULL AFTER address_order,
        ADD address_connect_sub_kind varchar(30) DEFAULT NULL AFTER address_connect_kind";
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_addresses SET address_connect_kind="person", address_connect_sub_kind="person" WHERE address_person_id!=""';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_addresses SET address_connect_kind="family", address_connect_sub_kind="family", address_person_id=address_family_id WHERE address_family_id!=""';
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_addresses CHANGE address_person_id address_connect_id VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci, DROP address_family_id;";
        $this->dbh->query($sql);
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

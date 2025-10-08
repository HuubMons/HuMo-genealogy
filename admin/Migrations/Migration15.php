<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration15
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

        // *** Change pers_address_source and fam_address_source into: address_source ***
        $sql_get = $this->dbh->query("SELECT * FROM humo_connections WHERE connect_sub_kind='pers_address_source' OR connect_sub_kind='fam_address_source'");
        while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
            $sql_put = "UPDATE humo_connections SET connect_kind='address', connect_sub_kind='address_source' WHERE connect_id=" . $getDb->connect_id;
            $this->dbh->query($sql_put);
        }
        // *** Update humo_settings (needed larger ID, because of bug in scripts) ***
        $sql_put = "ALTER TABLE humo_settings CHANGE setting_id setting_id INT(10) UNSIGNED NOT NULL AUTO_INCREMENT";
        $this->dbh->query($sql_put);

        // *** Add 2FA in user table ***
        $sql = "ALTER TABLE humo_users ADD user_2fa_enabled varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER user_password_salted;";
        $this->dbh->query($sql);
        $sql = "ALTER TABLE humo_users ADD user_2fa_auth_secret varchar(50) CHARACTER SET utf8 DEFAULT '' AFTER user_2fa_enabled;";
        $this->dbh->query($sql);

        // *** Change place fields into 120 characters ***
        $sql = "ALTER TABLE humo_persons
            CHANGE pers_birth_place pers_birth_place VARCHAR(120) CHARACTER SET utf8,
            CHANGE pers_bapt_place pers_bapt_place VARCHAR(120) CHARACTER SET utf8,
            CHANGE pers_death_place pers_death_place VARCHAR(120) CHARACTER SET utf8,
            CHANGE pers_buried_place pers_buried_place VARCHAR(120) CHARACTER SET utf8";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_families
            CHANGE fam_relation_place fam_relation_place varchar(120) CHARACTER SET utf8,
            CHANGE fam_marr_notice_place fam_marr_notice_place varchar(120) CHARACTER SET utf8,
            CHANGE fam_marr_place fam_marr_place varchar(120) CHARACTER SET utf8,
            CHANGE fam_marr_church_notice_place fam_marr_church_notice_place varchar(120) CHARACTER SET utf8,
            CHANGE fam_marr_church_place fam_marr_church_place varchar(120) CHARACTER SET utf8,
            CHANGE fam_div_place fam_div_place varchar(120) CHARACTER SET utf8";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_repositories CHANGE repo_place repo_place varchar(120) CHARACTER SET utf8";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_connections CHANGE connect_place connect_place varchar(120) CHARACTER SET utf8";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_addresses CHANGE address_place address_place varchar(120) CHARACTER SET utf8";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_events CHANGE event_place event_place varchar(120) CHARACTER SET utf8";
        $this->dbh->query($sql);

        $tempqry = $this->dbh->query("SHOW TABLES LIKE 'humo_location'");
        if ($tempqry->rowCount()) {
            $sql = "ALTER TABLE humo_location CHANGE location_location location_location VARCHAR(120) CHARACTER SET utf8";
            $this->dbh->query($sql);

            $result = $this->dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
            $exists = $result->rowCount();
            if (!$exists) {
                $this->dbh->query("ALTER TABLE humo_location ADD location_status TEXT AFTER location_lng");
            }
        }
        $tempqry = $this->dbh->query("SHOW TABLES LIKE 'humo_no_location'");
        if ($tempqry->rowCount()) {
            $sql = "ALTER TABLE humo_no_location CHANGE no_location_location no_location_location VARCHAR(120) CHARACTER SET utf8";
            $this->dbh->query($sql);
        }
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

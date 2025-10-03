<?php

/**
 * Database normalisation (move multiple items from table_persons and table_families to table event).
 */

namespace Genealogy\Admin\Migrations;

use PDO;
//use Exception;

class Migration20
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up()
    {
        global $humo_option;

        $parseGedcomDate = new \Genealogy\Include\ParseGedcomDate();

        // *** Empty location_status. Field will be used for geolocation status ***
        $this->dbh->exec("UPDATE humo_location SET location_status = ''");

        $this->dbh->exec("ALTER TABLE humo_events MODIFY event_date VARCHAR(40) CHARACTER SET utf8 NULL DEFAULT NULL;");

        $this->dbh->exec("
            ALTER TABLE humo_events
            ADD COLUMN person_id INT UNSIGNED NULL after event_order,
            ADD COLUMN relation_id INT UNSIGNED NULL after person_id,
            ADD COLUMN place_id INT UNSIGNED NULL after event_place,
            ADD COLUMN date_year INT NULL after event_date,
            ADD COLUMN date_month TINYINT NULL after date_year,
            ADD COLUMN date_day TINYINT NULL after date_month,
            ADD COLUMN event_time VARCHAR(25) NULL after date_day,
            ADD COLUMN authority TEXT NULL after event_event_extra,
            ADD COLUMN stillborn VARCHAR(1) DEFAULT 'n' AFTER authority,
            ADD COLUMN cause VARCHAR(255) DEFAULT NULL AFTER stillborn,
            ADD COLUMN cremation VARCHAR(1) DEFAULT NULL AFTER cause,
            ADD COLUMN event_end_date VARCHAR(35) DEFAULT NULL AFTER cremation
        ");

        // *** Add event_date_hebnight column ***
        $field = [];
        $column_qry = $this->dbh->query('SHOW COLUMNS FROM humo_events');
        while ($columnDb = $column_qry->fetch()) {
            $field_value = $columnDb['Field'];
            $field[$field_value] = $field_value;
        }
        if (!isset($field['event_date_hebnight'])) {
            $this->dbh->query("ALTER TABLE humo_events ADD event_date_hebnight VARCHAR(10) CHARACTER SET utf8 AFTER event_date;");
        }

        // *** Set event_new_date to default value in all new items ***
        $event_new_datetime = date('Y-m-d H:i:s', strtotime('1970-01-01 00:00:01'));

        // *** Move birth, baptise, etc. and marriage items to event table ***
        if ($humo_option['admin_hebnight'] == 'y') {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_time, event_place, event_text, event_date_hebnight, stillborn, event_new_datetime)
            SELECT pers_tree_id, pers_gedcomnumber, 'person', 'birth', pers_birth_date, pers_birth_time, pers_birth_place, pers_birth_text, pers_birth_date_hebnight, pers_stillborn, '" . $event_new_datetime . "'
            FROM humo_persons
            WHERE (pers_birth_date IS NOT NULL AND pers_birth_date != '')
            OR (pers_birth_place IS NOT NULL AND pers_birth_place != '')
            OR (pers_birth_text IS NOT NULL AND pers_birth_text != '')
            OR (pers_birth_time IS NOT NULL AND pers_birth_time != '')
            OR (pers_stillborn IS NOT NULL AND pers_stillborn != '')
            ");

            $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_birth_date_hebnight");
        } else {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_time, event_place, event_text, stillborn, event_new_datetime)
            SELECT pers_tree_id, pers_gedcomnumber, 'person', 'birth', pers_birth_date, pers_birth_time, pers_birth_place, pers_birth_text, pers_stillborn, '" . $event_new_datetime . "'
            FROM humo_persons
            WHERE (pers_birth_date IS NOT NULL AND pers_birth_date != '')
            OR (pers_birth_place IS NOT NULL AND pers_birth_place != '')
            OR (pers_birth_text IS NOT NULL AND pers_birth_text != '')
            OR (pers_birth_time IS NOT NULL AND pers_birth_time != '')
            OR (pers_stillborn IS NOT NULL AND pers_stillborn != '')
            ");
        }

        $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_place, event_text, event_new_datetime)
            SELECT pers_tree_id, pers_gedcomnumber, 'person', 'baptism', pers_bapt_date, pers_bapt_place, pers_bapt_text, '" . $event_new_datetime . "'
            FROM humo_persons
            WHERE (pers_bapt_date IS NOT NULL AND pers_bapt_date != '')
            OR (pers_bapt_place IS NOT NULL AND pers_bapt_place != '')
            OR (pers_bapt_text IS NOT NULL AND pers_bapt_text != '')
        ");

        if ($humo_option['admin_hebnight'] == "y") {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_time, event_place, event_text, event_date_hebnight, cause, event_pers_age, event_new_datetime)
            SELECT pers_tree_id, pers_gedcomnumber, 'person', 'death', pers_death_date, pers_death_time, pers_death_place, pers_death_text, pers_death_date_hebnight, pers_death_cause, pers_death_age, '" . $event_new_datetime . "'
            FROM humo_persons
            WHERE (pers_death_date IS NOT NULL AND pers_death_date != '')
            OR (pers_death_place IS NOT NULL AND pers_death_place != '')
            OR (pers_death_text IS NOT NULL AND pers_death_text != '')
            OR (pers_death_time IS NOT NULL AND pers_death_time != '')
            OR (pers_death_age IS NOT NULL AND pers_death_age != '')
            ");

            $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_death_date_hebnight");
        } else {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_time, event_place, event_text, cause, event_pers_age, event_new_datetime)
            SELECT pers_tree_id, pers_gedcomnumber, 'person', 'death', pers_death_date, pers_death_time, pers_death_place, pers_death_text, pers_death_cause, pers_death_age, '" . $event_new_datetime . "'
            FROM humo_persons
            WHERE (pers_death_date IS NOT NULL AND pers_death_date != '')
            OR (pers_death_place IS NOT NULL AND pers_death_place != '')
            OR (pers_death_text IS NOT NULL AND pers_death_text != '')
            OR (pers_death_time IS NOT NULL AND pers_death_time != '')
            OR (pers_death_age IS NOT NULL AND pers_death_age != '')
        ");
        }

        if ($humo_option['admin_hebnight'] == "y") {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_place, event_text, event_date_hebnight, cremation, event_new_datetime)
            SELECT pers_tree_id, pers_gedcomnumber, 'person', 'burial', pers_buried_date, pers_buried_place, pers_buried_text, pers_buried_date_hebnight, pers_cremation, '" . $event_new_datetime . "'
            FROM humo_persons
            WHERE (pers_buried_date IS NOT NULL AND pers_buried_date != '')
            OR (pers_buried_place IS NOT NULL AND pers_buried_place != '')
            OR (pers_buried_text IS NOT NULL AND pers_buried_text != '')
            ");

            $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_buried_date_hebnight");
        } else {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_place, event_text, cremation, event_new_datetime)
            SELECT pers_tree_id, pers_gedcomnumber, 'person', 'burial', pers_buried_date, pers_buried_place, pers_buried_text, pers_cremation, '" . $event_new_datetime . "'
            FROM humo_persons
            WHERE (pers_buried_date IS NOT NULL AND pers_buried_date != '')
            OR (pers_buried_place IS NOT NULL AND pers_buried_place != '')
            OR (pers_buried_text IS NOT NULL AND pers_buried_text != '')
        ");
        }

        $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_end_date, event_place, event_text, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'relation', fam_relation_date, fam_relation_end_date, fam_relation_place, fam_relation_text, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_relation_date IS NOT NULL AND fam_relation_date != '')
            OR (fam_relation_place IS NOT NULL AND fam_relation_place != '')
            OR (fam_relation_text IS NOT NULL AND fam_relation_text != '')
            OR (fam_relation_end_date IS NOT NULL AND fam_relation_end_date != '')
        ");

        if ($humo_option['admin_hebnight'] == "y") {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_heb_night, event_place, event_text, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'marriage_notice', fam_marr_notice_date, fam_marr_notice_date_hebnight, fam_marr_notice_place, fam_marr_notice_text, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_marr_notice_date IS NOT NULL AND fam_marr_notice_date != '')
            OR (fam_marr_notice_place IS NOT NULL AND fam_marr_notice_place != '')
            OR (fam_marr_notice_text IS NOT NULL AND fam_marr_notice_text != '')
            ");

            $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_notice_date_hebnight");
        } else {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_place, event_text, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'marriage_notice', fam_marr_notice_date, fam_marr_notice_place, fam_marr_notice_text, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_marr_notice_date IS NOT NULL AND fam_marr_notice_date != '')
            OR (fam_marr_notice_place IS NOT NULL AND fam_marr_notice_place != '')
            OR (fam_marr_notice_text IS NOT NULL AND fam_marr_notice_text != '')
            ");
        }

        if ($humo_option['admin_hebnight'] == "y") {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_heb_night, event_place, event_text, authority, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'marriage', fam_marr_date, fam_marr_date_hebnight, fam_marr_place, fam_marr_text, fam_marr_authority, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_marr_date IS NOT NULL AND fam_marr_date != '')
            OR (fam_marr_place IS NOT NULL AND fam_marr_place != '')
            OR (fam_marr_text IS NOT NULL AND fam_marr_text != '')
            OR (fam_marr_authority IS NOT NULL AND fam_marr_authority != '')
        ");

            $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_date_hebnight");
        } else {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_place, event_text, authority, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'marriage', fam_marr_date, fam_marr_place, fam_marr_text, fam_marr_authority, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_marr_date IS NOT NULL AND fam_marr_date != '')
            OR (fam_marr_place IS NOT NULL AND fam_marr_place != '')
            OR (fam_marr_text IS NOT NULL AND fam_marr_text != '')
            OR (fam_marr_authority IS NOT NULL AND fam_marr_authority != '')
        ");
        }

        if ($humo_option['admin_hebnight'] == "y") {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_heb_night, event_place, event_text, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'marr_church_notice', fam_marr_church_notice_date, fam_marr_church_notice_date_hebnight, fam_marr_church_notice_place, fam_marr_church_notice_text, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_marr_church_notice_date IS NOT NULL AND fam_marr_church_notice_date != '')
            OR (fam_marr_church_notice_place IS NOT NULL AND fam_marr_church_notice_place != '')
            OR (fam_marr_church_notice_text IS NOT NULL AND fam_marr_church_notice_text != '')
        ");

            $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_church_notice_date_hebnight");
        } else {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_place, event_text, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'marr_church_notice', fam_marr_church_notice_date, fam_marr_church_notice_place, fam_marr_church_notice_text, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_marr_church_notice_date IS NOT NULL AND fam_marr_church_notice_date != '')
            OR (fam_marr_church_notice_place IS NOT NULL AND fam_marr_church_notice_place != '')
            OR (fam_marr_church_notice_text IS NOT NULL AND fam_marr_church_notice_text != '')
        ");
        }

        if ($humo_option['admin_hebnight'] == "y") {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_heb_night, event_place, event_text, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'marr_church', fam_marr_church_date, fam_marr_church_hebnight, fam_marr_church_date_place, fam_marr_church_text, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_marr_church_date IS NOT NULL AND fam_marr_church_date != '')
            OR (fam_marr_church_place IS NOT NULL AND fam_marr_church_place != '')
            OR (fam_marr_church_text IS NOT NULL AND fam_marr_church_text != '')
        ");

            $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_church_date_hebnight");
        } else {
            $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_place, event_text, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'marr_church', fam_marr_church_date, fam_marr_church_place, fam_marr_church_text, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_marr_church_date IS NOT NULL AND fam_marr_church_date != '')
            OR (fam_marr_church_place IS NOT NULL AND fam_marr_church_place != '')
            OR (fam_marr_church_text IS NOT NULL AND fam_marr_church_text != '')
        ");
        }

        $this->dbh->exec("
            INSERT INTO humo_events (event_tree_id, event_connect_id, event_connect_kind, event_kind, event_date, event_place, event_text, authority, event_new_datetime)
            SELECT fam_tree_id, fam_gedcomnumber, 'family', 'divorce', fam_div_date, fam_div_place, fam_div_text, fam_div_authority, '" . $event_new_datetime . "'
            FROM humo_families
            WHERE (fam_div_date IS NOT NULL AND fam_div_date != '')
            OR (fam_div_place IS NOT NULL AND fam_div_place != '')
            OR (fam_div_text IS NOT NULL AND fam_div_text != '')
            OR (fam_div_authority IS NOT NULL AND fam_div_authority != '')
        ");

        // *** Use person id's ***
        $this->dbh->exec("
            UPDATE humo_events e
            JOIN humo_persons p ON e.event_connect_id = p.pers_gedcomnumber AND e.event_tree_id = p.pers_tree_id AND (e.event_connect_kind = 'person' OR e.event_kind = 'ASSO')
            SET e.person_id = p.pers_id
        ");
        // *** Use family id's ***
        $this->dbh->exec("
            UPDATE humo_events e
            JOIN humo_families f ON e.event_connect_id = f.fam_gedcomnumber AND e.event_tree_id = f.fam_tree_id AND (e.event_connect_kind = 'family' OR e.event_kind = 'ASSO')
            SET e.relation_id = f.fam_id
        ");

        // Temp. index to improve speed.
        $this->dbh->exec("ALTER TABLE humo_events ADD INDEX idx_event_place (event_place(100))");

        // *** Add missing places (from events table) in location table ***
        $this->dbh->exec("
            INSERT IGNORE INTO humo_location (location_location)
            SELECT DISTINCT event_place FROM humo_events WHERE event_place IS NOT NULL AND event_place != ''
            AND event_place NOT IN (SELECT location_location FROM humo_location)
        ");
        // *** Use location id's ***
        $this->dbh->exec("
            UPDATE humo_events e
            JOIN humo_location l ON e.event_place = l.location_location
            SET e.place_id = l.location_id
            WHERE e.event_place IS NOT NULL AND e.event_place != ''
        ");

        // Remove temp. index.
        $this->dbh->exec("ALTER TABLE humo_events DROP INDEX idx_event_place");

        // *** Update event date columns ***
        $this->dbh->beginTransaction();
        $stmt = $this->dbh->query("SELECT event_id, event_date FROM humo_events WHERE event_date IS NOT NULL AND event_date != ''");
        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $parsed = $parseGedcomDate->parse($row['event_date']);
            $update = $this->dbh->prepare("
                UPDATE humo_events SET date_year = :year, date_month = :month, date_day = :day WHERE event_id = :id
            ");
            $update->execute([
                ':year' => $parsed['year'],
                ':month' => $parsed['month'],
                ':day' => $parsed['day'],
                ':id' => $row['event_id'],
            ]);
        }
        $this->dbh->commit();

        // *** Remove old person fields ***
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_birth_date");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_birth_place");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_birth_text");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_bapt_date");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_bapt_place");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_bapt_text");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_death_date");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_death_time");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_death_place");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_death_text");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_death_age");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_buried_date");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_buried_place");
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_buried_text");

        // *** Remove old pers_place_index field ***
        $this->dbh->exec("ALTER TABLE humo_persons DROP COLUMN pers_place_index");

        // *** Remove old family fields ***
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_relation_date");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_relation_place");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_relation_text");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_notice_date");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_notice_place");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_notice_text");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_date");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_place");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_text");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_authority");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_church_notice_date");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_church_notice_place");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_church_notice_text");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_church_date");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_church_place");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_marr_church_text");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_div_date");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_div_place");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_div_text");
        $this->dbh->exec("ALTER TABLE humo_families DROP COLUMN fam_div_authority");

        $this->dbh->exec("ALTER TABLE humo_events DROP COLUMN event_place");

        // *** Add new keys ***
        $this->dbh->exec("ALTER TABLE humo_events ADD KEY (person_id)");
        $this->dbh->exec("ALTER TABLE humo_events ADD KEY (relation_id)");
        $this->dbh->exec("ALTER TABLE humo_events ADD KEY (place_id)");

        // *** Add unsigned to location_id (do not add PRIMARY KEY, it's allready defined) ***
        $this->dbh->exec("ALTER TABLE humo_location MODIFY location_id INT UNSIGNED NOT NULL AUTO_INCREMENT");

        // *** Add foreign key constraints ***
        $this->dbh->exec("
            ALTER TABLE humo_events
            ADD CONSTRAINT fk_event_person
            FOREIGN KEY (person_id) REFERENCES humo_persons(pers_id)
            ON DELETE SET NULL ON UPDATE CASCADE
        ");
        $this->dbh->exec("
            ALTER TABLE humo_events
            ADD CONSTRAINT fk_event_family
            FOREIGN KEY (relation_id) REFERENCES humo_families(fam_id)
            ON DELETE SET NULL ON UPDATE CASCADE
        ");
        $this->dbh->exec("
            ALTER TABLE humo_events
            ADD CONSTRAINT fk_event_place
            FOREIGN KEY (place_id) REFERENCES humo_location(location_id)
            ON DELETE SET NULL ON UPDATE CASCADE
        ");

        // *** Set event_changed_datetime to NULL in all new items (because these values were changed during the upgrade) ***
        $this->dbh->exec("
            UPDATE humo_events
            SET event_changed_datetime = NULL
            WHERE event_new_datetime = '" . $event_new_datetime . "'
        ");

        // *** Free geoplugin no longer available ***
        $stmt = $this->dbh->exec("UPDATE humo_settings SET setting_value = '' WHERE setting_variable = 'ip_api_geoplugin_old'");
        $stmt = $this->dbh->exec("UPDATE humo_settings SET setting_value = 'dis' WHERE setting_variable = 'ip_api_collection'");
    }
}

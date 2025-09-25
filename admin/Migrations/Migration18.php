<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration18
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Implement the migration logic here.

        //ob_flush();
        flush();

        // *** Restore update problem generate in version 6.4.1 (accidently changed family into person) ***
        $eventsql = "UPDATE humo_events SET event_connect_kind='family' WHERE event_kind='marriage_witness' OR event_kind='marriage_witness_rel'";
        $this->dbh->query($eventsql);

        // *** Create humo_location if not exists ***
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_location'");
        if (!$temp->rowCount()) {
            // no database exists - so create it
            // It has 4 columns:
            //     1. id
            //     2. name of location
            //     3. latitude as received from a geocode call
            //     4. longitude as received from a geocode call
            //     5. status: what is this location used for: birth/bapt/death/buried, and by which tree(s)
            $locationtbl = "CREATE TABLE humo_location (
                location_id INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
                location_location VARCHAR(120) CHARACTER SET utf8,
                location_lat FLOAT(10,6),
                location_lng FLOAT(10,6),
                location_status TEXT
            )";
            $this->dbh->query($locationtbl);
        }
        $result = $this->dbh->query("SHOW COLUMNS FROM `humo_location` LIKE 'location_status'");
        $exists = $result->rowCount();
        if (!$exists) {
            $this->dbh->query("ALTER TABLE humo_location ADD location_status TEXT AFTER location_lng");
        }

        // Table humo_no_location no longer in use.
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_no_location'");
        if ($temp->rowCount()) {
            $this->dbh->query("DROP TABLE humo_no_location");
        }

        // *** Change witnesses because of multiple kind of ASSO/ witnesses in GEDCOM 7 ***
        $this->dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='CHR', event_gedcom='WITN' WHERE event_kind='baptism_witness'");
        $this->dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='BURI', event_gedcom='WITN' WHERE event_kind='burial_witness'");
        $this->dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='MARR', event_gedcom='WITN' WHERE event_kind='marriage_witness'");
        $this->dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='MARR_REL', event_gedcom='WITN' WHERE event_kind='marriage_witness_rel'");

        // *** Change birth_declaration into birth_decl_witness ***
        // *** ONLY convert to birth_decl_witness if event_connect_kind2 is a person ***
        $this->dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='birth_declaration', event_gedcom='WITN' WHERE event_kind='birth_declaration' AND (event_connect_kind2='person' OR event_event LIKE '_%')");
        $this->dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='death_declaration', event_gedcom='WITN' WHERE event_kind='death_declaration' AND (event_connect_kind2='person' OR event_event LIKE '_%')");

        // *** Add seperate general birth_declaration and death_declaration events ***
        // *** Only convert declaration events where witness is connected ***
        $qry = $this->dbh->query("SELECT * from humo_events WHERE (event_connect_kind='birth_declaration' OR event_connect_kind='death_declaration') AND event_order='1'");

        // *** Batch processing ***
        $this->dbh->beginTransaction();

        // *** Add seperate general birth_declaration and death_declaration events ***
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            if ($qryDb->event_date || $qryDb->event_place || $qryDb->event_text) {
                $sql_put = $this->dbh->prepare("INSERT INTO humo_events SET
                    event_tree_id = :event_tree_id,
                    event_gedcomnr = '',
                    event_order = :event_order,
                    event_connect_kind = 'person',
                    event_connect_id = :event_connect_id,
                    event_kind = :event_kind,
                    event_event = '',
                    event_event_extra = :event_event_extra,
                    event_gedcom = 'EVEN',
                    event_date = :event_date,
                    event_place = :event_place,
                    event_text = :event_text,
                    event_quality = :event_quality,
                    event_new_user_id = :event_new_user_id,
                    event_new_datetime = :event_new_datetime,
                    event_changed_user_id = :event_changed_user_id,
                    event_changed_datetime = :event_changed_datetime
                ");
                $sql_put->execute([
                    ':event_tree_id' => $qryDb->event_tree_id,
                    ':event_order' => $qryDb->event_order,
                    ':event_connect_id' => $qryDb->event_connect_id,
                    ':event_kind' => $qryDb->event_connect_kind,
                    ':event_event_extra' => $qryDb->event_event_extra,
                    ':event_date' => $qryDb->event_date,
                    ':event_place' => $qryDb->event_place,
                    ':event_text' => $qryDb->event_text,
                    ':event_quality' => $qryDb->event_quality,
                    ':event_new_user_id' => $qryDb->event_new_user_id,
                    ':event_new_datetime' => $qryDb->event_new_datetime,
                    ':event_changed_user_id' => $qryDb->event_changed_user_id,
                    ':event_changed_datetime' => $qryDb->event_changed_datetime,
                ]);
                $last_insert = $this->dbh->lastInsertId();

                // *** Update sources connected to these events connections ***
                $this->dbh->query("UPDATE humo_connections SET connect_connect_id='" . $last_insert . "' WHERE connect_connect_id='" . $qryDb->event_id . "'");
            }
        }

        // *** Commit data in database ***
        $this->dbh->commit();

        // *** Update for godfather events ***
        $sql = "SELECT * from humo_events WHERE event_kind='godfather'";
        $qry = $this->dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $eventsql = "UPDATE humo_events SET
                event_connect_kind='CHR',
                event_connect_kind2='person',
                event_connect_id2='" . substr($qryDb->event_event, 1, -1) . "',
                event_kind='ASSO',
                event_event='',
                event_gedcom='GODP'
                WHERE event_id= '" . $qryDb->event_id . "'";
            $this->dbh->query($eventsql);
        }

        // Remove old rel_merge_ variables using tree_prefix. Now tree_id is used.
        $this->dbh->query("DELETE FROM humo_settings WHERE setting_variable LIKE 'rel_merge_humo%'");
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration9
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Implement the migration logic here.

        // *** Update event table ***
        $sql = "ALTER TABLE humo_events ADD event_connect_kind varchar(25) DEFAULT NULL AFTER event_order";
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_events SET event_connect_kind="person" WHERE event_person_id!=""';
        $this->dbh->query($sql);

        $sql = 'UPDATE humo_events SET event_connect_kind="family", event_person_id=event_family_id WHERE event_family_id!=""';
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_events CHANGE event_person_id event_connect_id VARCHAR(20) CHARACTER SET utf8 COLLATE utf8_general_ci";
        $this->dbh->query($sql);

        // *** Move photo's from source to event table ***
        // *** Batch processing ***
        $this->dbh->beginTransaction();
        $sql = "SELECT source_id, source_gedcomnr, source_tree_id, source_photo FROM humo_sources WHERE source_photo LIKE '_%'";
        $qry = $this->dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $source_photo = explode(";", $qryDb->source_photo);
            for ($i = 0; $i <= count($source_photo) - 1; $i++) {
                $gebeurtsql = 'INSERT INTO humo_events SET
                    event_tree_id="' . $qryDb->source_tree_id . '",
                    event_order="' . ($i + 1) . '",
                    event_connect_kind="source",
                    event_connect_id="' . $qryDb->source_gedcomnr . '",
                    event_kind="picture",
                    event_event="' . trim($source_photo[$i]) . '"';
                $this->dbh->query($gebeurtsql);
            }
        }
        // *** Commit data in database ***
        $this->dbh->commit();

        $qry = "ALTER TABLE humo_events DROP event_family_id;";
        $this->dbh->query($qry);

        $qry = "ALTER TABLE humo_sources DROP source_photo;";
        $this->dbh->query($qry);

        $qry = "ALTER TABLE humo_repositories DROP repo_photo;";
        $this->dbh->query($qry);

        $qry = "ALTER TABLE humo_addresses DROP address_photo;";
        $this->dbh->query($qry);

        // *** Update user table ***
        $sql = "ALTER TABLE humo_user_notes ADD note_guest_name varchar(25) DEFAULT NULL AFTER note_user_id,
        ADD note_guest_mail varchar(100) DEFAULT NULL AFTER note_guest_name";
        $this->dbh->query($sql);
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration19
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up()
    {
        // Code to run during the migration

        flush();

        // *** In some cases event_connect_kind is empty, because of bug in previous update ***
        $this->dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='birth_declaration', event_gedcom='WITN' WHERE event_kind='birth_declaration' AND event_connect_id2 LIKE 'I%'");
        $this->dbh->query("UPDATE humo_events SET event_kind='ASSO', event_connect_kind='death_declaration', event_gedcom='WITN' WHERE event_kind='death_declaration' AND event_connect_id2 LIKE 'I%'");

        // *** Because of bug: rerun this query, also check for empty event_connect_kind2 ***
        // *** Add seperate general birth_declaration and death_declaration events ***
        // *** Only convert declaration events where witness is connected ***
        $qry = $this->dbh->query("SELECT * from humo_events WHERE (event_connect_kind='birth_declaration' OR event_connect_kind='death_declaration') AND event_connect_kind2 = '' AND event_connect_id2 LIKE 'I%' AND event_order='1'");

        // *** Batch processing ***
        $this->dbh->beginTransaction();

        // *** Add seperate general birth_declaration and death_declaration events ***
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            if ($qryDb->event_date || $qryDb->event_place || $qryDb->event_text) {
                $sql_put = $this->dbh->prepare("INSERT INTO humo_events SET
                    event_tree_id=:event_tree_id,
                    event_gedcomnr='',
                    event_order=:event_order,
                    event_connect_kind='person',
                    event_connect_id=:event_connect_id,
                    event_kind=:event_kind,
                    event_event='',
                    event_event_extra=:event_event_extra,
                    event_gedcom='EVEN',
                    event_date=:event_date,
                    event_place=:event_place,
                    event_text=:event_text,
                    event_quality=:event_quality,
                    event_new_user_id=:event_new_user_id,
                    event_new_datetime=:event_new_datetime,
                    event_changed_user_id=:event_changed_user_id,
                    event_changed_datetime=:event_changed_datetime
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

        // Solve bug in a previous update.
        $this->dbh->query("UPDATE humo_events SET event_connect_kind2='person' WHERE event_kind='ASSO' AND event_connect_id2 LIKE 'I%'");

        flush();
    }

    public function down()
    {
        // Code to revert the migration
    }
}

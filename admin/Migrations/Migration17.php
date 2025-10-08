<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration17
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

        $this->update_datetime_username('humo_addresses', 'address');
        $this->update_datetime_username('humo_connections', 'connect');
        $this->update_datetime_username('humo_events', 'event');

        $this->update_datetime_username('humo_families', 'fam');
        $this->update_datetime_username('humo_persons', 'pers');

        $this->update_datetime_username('humo_repositories', 'repo');
        $this->update_datetime_username('humo_sources', 'source');
        $this->update_datetime_username('humo_texts', 'text');

        // *** Allready using user_id ***
        $this->update_datetime_username('humo_user_notes', 'note');

        flush();
    }

    private function update_datetime_username($table, $field)
    {
        // *** Improve date, time and username data fields ***
        $sql = "ALTER TABLE " . $table . "
            ADD " . $field . "_new_datetime datetime NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER " . $field . "_new_time,
            ADD " . $field . "_changed_datetime datetime on update CURRENT_TIMESTAMP NULL DEFAULT NULL AFTER " . $field . "_changed_time";
        if ($field != 'note') {
            $sql .= ", ADD " . $field . "_new_user_id SMALLINT NULL DEFAULT NULL AFTER " . $field . "_new_user,
                ADD " . $field . "_changed_user_id SMALLINT NULL DEFAULT NULL AFTER " . $field . "_changed_user";
        }
        $this->dbh->query($sql);

        if ($field != 'note') {
            $sql = "SELECT " . $field . "_id as id,
                " . $field . "_new_user as new_user,
                " . $field . "_new_date as new_date,
                " . $field . "_new_time as new_time,
                " . $field . "_changed_user as changed_user,
                " . $field . "_changed_date as changed_date,
                " . $field . "_changed_time as changed_time,
                new_user.user_id AS new_user_id,
                changed_user.user_id AS changed_user_id
                FROM " . $table . "
                LEFT JOIN humo_users AS new_user ON " . $field . "_new_user=new_user.user_name
                LEFT JOIN humo_users AS changed_user ON " . $field . "_changed_user=changed_user.user_name";
            //WHERE ".$field."_new_user IS NOT NULL or ".$field."_changed_user IS NOT NULL";
        } else {
            $sql = "SELECT " . $field . "_id as id,
                " . $field . "_new_date as new_date,
                " . $field . "_new_time as new_time,
                " . $field . "_changed_date as changed_date,
                " . $field . "_changed_time as changed_time
                FROM " . $table;
            //WHERE ".$field."_new_user IS NOT NULL or ".$field."_changed_user IS NOT NULL";
        }
        $qry = $this->dbh->query($sql);

        // *** Batch processing ***
        $this->dbh->beginTransaction();

        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            // *** Also update new datetime field ***
            //$new_datetime = NULL;
            $new_datetime = '1970-01-01 00:00:01';
            if ($qryDb->new_date) {
                // Convert from 06 JAN 2024 to 2024-02-06 20:54:13
                //$new_datetime = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $qryDb->new_date . ' ' . $qryDb->new_time)));
                $new_datetime = date('Y-m-d H:i:s', strtotime($qryDb->new_date . ' ' . $qryDb->new_time));
            }

            $changed_datetime = NULL;
            if ($qryDb->changed_date) {
                //if ($qryDb->changed_date and $qryDb->changed_date!='') {
                //$changed_datetime = date('Y-m-d H:i:s', strtotime(str_replace('-', '/', $qryDb->changed_date . ' ' . $qryDb->changed_time)));
                $changed_datetime = date('Y-m-d H:i:s', strtotime($qryDb->changed_date . ' ' . $qryDb->changed_time));
            }

            $sql = 'UPDATE ' . $table . ' SET ' . $field . '_new_datetime="' . $new_datetime . '"';
            if ($field != 'note' && $qryDb->new_user_id) {
                $sql .= ', ' . $field . '_new_user_id="' . $qryDb->new_user_id . '"';
            }

            if ($changed_datetime) {
                $sql .= ', ' . $field . '_changed_datetime="' . $changed_datetime . '"';
            } else {
                // *** Otherwise MySQL will add current date, not needed for conversion ***
                $sql .= ', ' . $field . '_changed_datetime=NULL';
            }

            if ($field != 'note' && $qryDb->changed_user_id) {
                $sql .= ', ' . $field . '_changed_user_id="' . $qryDb->changed_user_id . '"';
            }
            $sql .= ' WHERE ' . $field . '_id="' . $qryDb->id . '"';
            //echo $sql . '<br>';
            $this->dbh->query($sql);
        }

        // *** Commit data in database ***
        $this->dbh->commit();

        if ($field != 'note') {
            $this->dbh->query("ALTER TABLE " . $table . "
                DROP " . $field . "_new_user, DROP " . $field . "_new_date, DROP " . $field . "_new_time,
                DROP " . $field . "_changed_user, DROP " . $field . "_changed_date, DROP " . $field . "_changed_time;");
        } else {
            $this->dbh->query("ALTER TABLE " . $table . "
                DROP " . $field . "_new_date, DROP " . $field . "_new_time,
                DROP " . $field . "_changed_date, DROP " . $field . "_changed_time;");
        }
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

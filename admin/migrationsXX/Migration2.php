<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration2
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        global $updateDb;
        $start_time = time();

        // *** Read all family trees from database ***
        $update_sql = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            //ob_start();
            echo '<b>Check ' . $updateDb->tree_prefix . '</b>';
            //ob_flush();
            flush();

            // *** Update source table ***
            $sql = "ALTER TABLE " . $updateDb->tree_prefix . "sources
                ADD source_status VARCHAR(10) CHARACTER SET utf8 DEFAULT '' AFTER source_id,
                ADD source_repo_gedcomnr VARCHAR(20) CHARACTER SET utf8 DEFAULT '' AFTER source_repo_page";
            $this->dbh->query($sql);
            // *** Repositories table ***
            try {
                $tbldb = $this->dbh->query("DROP TABLE " . $updateDb->tree_prefix . "repositories"); // Remove table.
            } catch (Exception $e) {
                //
            }
            // *** Generate new table ***
            print '<br>' . __('creating repositories...') . '<br>';
            $this->dbh->query("CREATE TABLE " . $updateDb->tree_prefix . "repositories (
                repo_id mediumint(6) unsigned NOT NULL auto_increment,
                repo_gedcomnr varchar(20) CHARACTER SET utf8,
                repo_name text CHARACTER SET utf8,
                repo_address text CHARACTER SET utf8,
                repo_zip varchar(20) CHARACTER SET utf8,
                repo_place varchar(75) CHARACTER SET utf8,
                repo_phone varchar(20) CHARACTER SET utf8,
                repo_date varchar(35) CHARACTER SET utf8,
                repo_source text CHARACTER SET utf8,
                repo_text text CHARACTER SET utf8,
                repo_photo text CHARACTER SET utf8,
                repo_mail varchar(100) CHARACTER SET utf8,
                repo_url varchar(150) CHARACTER SET utf8,
                repo_new_date varchar(35) CHARACTER SET utf8,
                repo_new_time varchar(25) CHARACTER SET utf8,
                repo_changed_date varchar(35) CHARACTER SET utf8,
                repo_changed_time varchar(25) CHARACTER SET utf8,
                PRIMARY KEY (`repo_id`)) DEFAULT CHARSET=utf8");
            // *** Sources connections table ***
            try {
                $this->dbh->query("DROP TABLE " . $updateDb->tree_prefix . "connections"); // Remove table.
            } catch (Exception $e) {
                //
            }
            // *** Generate new table ***
            print ' ' . __('creating connections...');
            $this->dbh->query("CREATE TABLE " . $updateDb->tree_prefix . "connections (
                connect_id mediumint(6) unsigned NOT NULL auto_increment,
                connect_order mediumint(6),
                connect_kind varchar(25) CHARACTER SET utf8,
                connect_sub_kind varchar(30) CHARACTER SET utf8,
                connect_connect_id varchar(20) CHARACTER SET utf8,
                connect_date varchar(35) CHARACTER SET utf8,
                connect_place varchar(75) CHARACTER SET utf8,
                connect_time varchar(25) CHARACTER SET utf8,
                connect_page text CHARACTER SET utf8,
                connect_role varchar(75) CHARACTER SET utf8,
                connect_text text CHARACTER SET utf8,
                connect_source_id varchar(20) CHARACTER SET utf8,
                connect_item_id varchar(20) CHARACTER SET utf8,
                connect_status varchar(10) CHARACTER SET utf8,
                connect_new_date varchar(35) CHARACTER SET utf8,
                connect_new_time varchar(25) CHARACTER SET utf8,
                connect_changed_date varchar(35) CHARACTER SET utf8,
                connect_changed_time varchar(25) CHARACTER SET utf8,
                PRIMARY KEY (`connect_id`),
                KEY (connect_connect_id)
                ) DEFAULT CHARSET=utf8");

            // *** Move shared addresses from event to connect table ***
            // *** Batch processing ***
            $this->dbh->beginTransaction();
            $event_qry = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events WHERE event_kind='address'");
            $eventnr = 0;
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                $gebeurtsql = "INSERT INTO " . $updateDb->tree_prefix . "connections SET
                    connect_order='" . $eventDb->event_order . "',
                    connect_kind='person',
                    connect_sub_kind='person_address',
                    connect_connect_id='" . $eventDb->event_person_id . "',
                    connect_source_id='',
                    connect_role='" . $eventDb->event_event . "',
                    connect_date='" . $eventDb->event_date . "',
                    connect_item_id='" . substr($eventDb->event_source, 1, -1) . "',
                    connect_text='',
                    connect_new_date='" . $eventDb->event_new_date . "',
                    connect_new_time='" . $eventDb->event_new_time . "',
                    connect_changed_date='" . $eventDb->event_changed_date . "',
                    connect_changed_time='" . $eventDb->event_changed_time . "'
                    ";
                $this->dbh->query($gebeurtsql);
            }
            // *** Commit data in database ***
            $this->dbh->commit();

            // *** Remove old addresses from connect table ***
            $sql = "DELETE FROM " . $updateDb->tree_prefix . "events WHERE event_kind='address'";
            $this->dbh->query($sql);

            // *** Copy shared sources to new connect table ***
            // *** Batch processing ***
            $this->dbh->beginTransaction();
            $event_qry = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events WHERE event_source LIKE '_%'
            ORDER BY event_person_id,event_family_id,event_order");
            while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                unset($source_array);
                $source_array = explode(";", $eventDb->event_source);

                // *** Event by person ***
                if ($eventDb->event_person_id) {
                    $connect_kind = 'person';
                    $connect_sub_kind = 'person_source';
                    $connect_connect_id = $eventDb->event_person_id;
                }
                // *** Event by family ***
                if ($eventDb->event_family_id) {
                    $connect_kind = 'family';
                    $connect_sub_kind = 'family_source';
                    $connect_connect_id = $eventDb->event_family_id;
                }
                // *** Source by event ***
                if ($eventDb->event_kind != 'source') {
                    $connect_sub_kind = 'event_source';
                    $connect_connect_id = $eventDb->event_id;
                }

                for ($i = 0; $i <= (count($source_array) - 1); $i++) {
                    $gebeurtsql = "INSERT INTO " . $updateDb->tree_prefix . "connections SET";
                    if ($eventDb->event_kind == 'source') {
                        $gebeurtsql .= " connect_order='" . $eventDb->event_order . "',";
                    } else {
                        $gebeurtsql .= " connect_order='" . ($i + 1) . "',";
                    }

                    $gebeurtsql .= " connect_kind='" . $connect_kind . "',
                    connect_sub_kind='" . $connect_sub_kind . "',
                    connect_connect_id='" . $connect_connect_id . "',";

                    // *** Check if old source was a link or a text ***
                    if (substr($source_array[$i], 0, 1) === '@') {
                        $gebeurtsql .= " connect_source_id='" . substr($source_array[$i], 1, -1) . "'";
                    } else {
                        $gebeurtsql .= " connect_text='" . $source_array[$i] . "'";
                    }

                    if ($eventDb->event_kind == 'source') {
                        $gebeurtsql .= ",
                        connect_role='" . $eventDb->event_event . "',
                        connect_date='" . $eventDb->event_date . "',
                        connect_place='" . $eventDb->event_place . "'";
                    }
                    $this->dbh->query($gebeurtsql);
                }

                // *** Update old source fields, or remove old source records ***
                if ($eventDb->event_kind != 'source') {
                    $sql = "UPDATE " . $updateDb->tree_prefix . "events SET event_source='SOURCE'
                    WHERE event_id='" . $eventDb->event_id . "'";
                    $this->dbh->query($sql);
                } else {
                    $sql = "DELETE FROM " . $updateDb->tree_prefix . "events WHERE event_id='" . $eventDb->event_id . "'";
                    $this->dbh->query($sql);
                }
            }
            // *** Commit data in database ***
            $this->dbh->commit();


            // *** Update sources in person table ***
            // *** Batch processing ***
            $this->dbh->beginTransaction();
            $read_pers_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "person");
            while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                $update = false;
                $sql = "UPDATE " . $updateDb->tree_prefix . "person SET";

                if ($read_persDb->pers_name_source) {
                    $this->update_source($read_persDb, $read_persDb->pers_name_source, 'person', 'pers_name_source', $read_persDb->pers_gedcomnumber);
                    $sql .= " pers_name_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_birth_source) {
                    $this->update_source($read_persDb, $read_persDb->pers_birth_source, 'person', 'pers_birth_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_birth_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_bapt_source) {
                    $this->update_source($read_persDb, $read_persDb->pers_bapt_source, 'person', 'pers_bapt_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_bapt_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_death_source) {
                    $this->update_source($read_persDb, $read_persDb->pers_death_source, 'person', 'pers_death_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_death_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_buried_source) {
                    $this->update_source($read_persDb, $read_persDb->pers_buried_source, 'person', 'pers_buried_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_buried_source='SOURCE'";
                    $update = true;
                }
                if ($read_persDb->pers_text_source) {
                    $this->update_source($read_persDb, $read_persDb->pers_text_source, 'person', 'pers_text_source', $read_persDb->pers_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " pers_text_source='SOURCE'";
                    $update = true;
                }
                $sql .= " WHERE pers_id='" . $read_persDb->pers_id . "'";

                if ($update == true) {
                    $this->dbh->query($sql);
                }
            }
            // *** Commit data in database ***
            $this->dbh->commit();


            // *** Update sources in family table ***
            // *** Batch processing ***
            $this->dbh->beginTransaction();
            $read_fam_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "family");
            while ($read_famDb = $read_fam_sql->fetch(PDO::FETCH_OBJ)) {
                $update = false;
                $sql = "UPDATE " . $updateDb->tree_prefix . "family SET";

                if ($read_famDb->fam_relation_source) {
                    $this->update_source($read_famDb, $read_famDb->fam_relation_source, 'family', 'fam_relation_source', $read_famDb->fam_gedcomnumber);
                    $sql .= " fam_relation_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_marr_notice_source) {
                    $this->update_source($read_famDb, $read_famDb->fam_marr_notice_source, 'family', 'fam_marr_notice_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_marr_notice_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_marr_source) {
                    $this->update_source($read_famDb, $read_famDb->fam_marr_source, 'family', 'fam_marr_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_marr_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_marr_church_notice_source) {
                    $this->update_source($read_famDb, $read_famDb->fam_marr_church_notice_source, 'family', 'fam_marr_church_notice_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_marr_church_notice_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_marr_church_source) {
                    $this->update_source($read_famDb, $read_famDb->fam_marr_church_source, 'family', 'fam_marr_church_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_marr_church_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_div_source) {
                    $this->update_source($read_famDb, $read_famDb->fam_div_source, 'family', 'fam_div_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_div_source='SOURCE'";
                    $update = true;
                }
                if ($read_famDb->fam_text_source) {
                    $this->update_source($read_famDb, $read_famDb->fam_text_source, 'family', 'fam_text_source', $read_famDb->fam_gedcomnumber);
                    if ($update == true) {
                        $sql .= ', ';
                    }
                    $sql .= " fam_text_source='SOURCE'";
                    $update = true;
                }

                $sql .= " WHERE fam_id='" . $read_famDb->fam_id . "'";

                if ($update == true) {
                    $this->dbh->query($sql);
                }
            }
            // *** Commit data in database ***
            $this->dbh->commit();

            echo '<br>';
        }

        // *** Processing time ***
        $end_time = time();
        echo $end_time - $start_time . ' ' . __('seconds.') . '<br>';
    }

    // *** Update sources by persons and families ***
    private function update_source($read_dB, $source_value, $connect_kind, $connect_sub_kind, $connect_connect_id)
    {
        global $updateDb;
        unset($source_array);
        $source_array = explode(";", $source_value);
        for ($i = 0; $i <= (count($source_array) - 1); $i++) {
            $gebeurtsql = "INSERT INTO " . $updateDb->tree_prefix . "connections SET
                connect_order='" . ($i + 1) . "',
                connect_kind='" . $connect_kind . "',
                connect_sub_kind='" . $connect_sub_kind . "',
                connect_connect_id='" . $connect_connect_id . "',";
            // *** Check if old source was a link or a text ***
            if (substr($source_array[$i], 0, 1) === '@') {
                $gebeurtsql .= " connect_source_id='" . substr($source_array[$i], 1, -1) . "'";
            } else {
                $gebeurtsql .= " connect_text='" . $source_array[$i] . "'";
            }
            $this->dbh->query($gebeurtsql);
        }
    }

    public function down(): void
    {
        //
    }
}

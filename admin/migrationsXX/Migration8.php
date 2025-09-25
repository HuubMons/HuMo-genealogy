<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration8
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Implement the migration logic here.

        $sql = "ALTER TABLE humo_settings ADD setting_tree_id smallint(5), ADD setting_order smallint(5)";
        $this->dbh->query($sql);

        // *** Add ordering numbers by extra links in settings table ***
        $setting_order = 1;
        $update_sql = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            $sql = "UPDATE humo_settings SET setting_order='" . $setting_order . "' WHERE setting_id='" . $updateDb->setting_id . "'";
            $this->dbh->query($sql);
            $setting_order++;
        }

        // *** New table for persons ***
        $tbldbqry = "CREATE TABLE humo_persons (
        pers_id mediumint(7) unsigned NOT NULL auto_increment,
        pers_gedcomnumber varchar(20) CHARACTER SET utf8,
        pers_tree_id mediumint(7),
        pers_tree_prefix varchar(10) CHARACTER SET utf8,
        pers_famc varchar(50) CHARACTER SET utf8,
        pers_fams varchar(150) CHARACTER SET utf8,
        pers_indexnr varchar(20) CHARACTER SET utf8,
        pers_firstname varchar(60) CHARACTER SET utf8,
        pers_callname varchar(50) CHARACTER SET utf8,
        pers_prefix varchar(20) CHARACTER SET utf8,
        pers_lastname varchar(60) CHARACTER SET utf8,
        pers_patronym varchar(50) CHARACTER SET utf8,
        pers_name_text text CHARACTER SET utf8,
        pers_sexe varchar(1) CHARACTER SET utf8,
        pers_own_code varchar(100) CHARACTER SET utf8,
    pers_birth_place varchar(75) CHARACTER SET utf8,
    pers_birth_date varchar(35) CHARACTER SET utf8,
    pers_birth_time varchar(25) CHARACTER SET utf8,
    pers_birth_text text CHARACTER SET utf8,
    pers_stillborn varchar(1) CHARACTER SET utf8 DEFAULT 'n',
    pers_bapt_place varchar(75) CHARACTER SET utf8,
    pers_bapt_date varchar(35) CHARACTER SET utf8,
    pers_bapt_text text CHARACTER SET utf8,
    pers_religion varchar(50) CHARACTER SET utf8,
    pers_death_place varchar(75) CHARACTER SET utf8,
    pers_death_date varchar(35) CHARACTER SET utf8,
    pers_death_time varchar(25) CHARACTER SET utf8,
    pers_death_text text CHARACTER SET utf8,
    pers_death_cause varchar(255) CHARACTER SET utf8,
    pers_death_age varchar(15) CHARACTER SET utf8,
    pers_buried_place varchar(75) CHARACTER SET utf8,
    pers_buried_date varchar(35) CHARACTER SET utf8,
    pers_buried_text text CHARACTER SET utf8,
    pers_cremation varchar(1) CHARACTER SET utf8,
        pers_place_index text CHARACTER SET utf8,
        pers_text text CHARACTER SET utf8,
        pers_alive varchar(20) CHARACTER SET utf8,
        pers_cal_date varchar(35) CHARACTER SET utf8,
        pers_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
        pers_new_date varchar(35) CHARACTER SET utf8,
        pers_new_time varchar(25) CHARACTER SET utf8,
        pers_changed_date varchar(35) CHARACTER SET utf8,
        pers_changed_time varchar(25) CHARACTER SET utf8,
        PRIMARY KEY (`pers_id`),
        KEY (pers_prefix),
        KEY (pers_lastname),
        KEY (pers_gedcomnumber),
        KEY (pers_tree_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $this->dbh->query($tbldbqry);

        // *** New table for families ***
        $tbldbqry = "CREATE TABLE humo_families (
        fam_id mediumint(7) unsigned NOT NULL auto_increment,
        fam_tree_id mediumint(7),
        fam_gedcomnumber varchar(20) CHARACTER SET utf8,
        fam_man varchar(20) CHARACTER SET utf8,
        fam_man_age varchar(15) CHARACTER SET utf8,
        fam_woman varchar(20) CHARACTER SET utf8,
        fam_woman_age varchar(15) CHARACTER SET utf8,
        fam_children text CHARACTER SET utf8,
        fam_kind varchar(50) CHARACTER SET utf8,
    fam_relation_date varchar(35) CHARACTER SET utf8,
    fam_relation_place varchar(75) CHARACTER SET utf8,
    fam_relation_text text CHARACTER SET utf8,
    fam_relation_end_date varchar(35) CHARACTER SET utf8,
    fam_marr_notice_date varchar(35) CHARACTER SET utf8,
    fam_marr_notice_place varchar(75) CHARACTER SET utf8,
    fam_marr_notice_text text CHARACTER SET utf8,
    fam_marr_date varchar(35) CHARACTER SET utf8,
    fam_marr_place varchar(75) CHARACTER SET utf8,
    fam_marr_text text CHARACTER SET utf8,
    fam_marr_authority text CHARACTER SET utf8,
    fam_marr_church_notice_date varchar(35) CHARACTER SET utf8,
    fam_marr_church_notice_place varchar(75) CHARACTER SET utf8,
    fam_marr_church_notice_text text CHARACTER SET utf8,
    fam_marr_church_date varchar(35) CHARACTER SET utf8,
    fam_marr_church_place varchar(75) CHARACTER SET utf8,
    fam_marr_church_text text CHARACTER SET utf8,
    fam_religion varchar(50) CHARACTER SET utf8,
    fam_div_date varchar(35) CHARACTER SET utf8,
    fam_div_place varchar(75) CHARACTER SET utf8,
    fam_div_text text CHARACTER SET utf8,
    fam_div_authority text CHARACTER SET utf8,
        fam_text text CHARACTER SET utf8,
        fam_alive int(1),
        fam_cal_date varchar(35) CHARACTER SET utf8,
        fam_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
        fam_counter mediumint(7),
        fam_new_date varchar(35) CHARACTER SET utf8,
        fam_new_time varchar(25) CHARACTER SET utf8,
        fam_changed_date varchar(35) CHARACTER SET utf8,
        fam_changed_time varchar(25) CHARACTER SET utf8,
        PRIMARY KEY (`fam_id`),
        KEY (fam_tree_id),
        KEY (fam_gedcomnumber),
        KEY (fam_man),
        KEY (fam_woman)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $this->dbh->query($tbldbqry);


        // *** New table for unprocessed tags ***
        $sql = "CREATE TABLE humo_unprocessed_tags (
        tag_id mediumint(6) unsigned NOT NULL auto_increment,
        tag_pers_id mediumint(6),
        tag_rel_id mediumint(6),
        tag_event_id mediumint(6),
        tag_source_id mediumint(6),
        tag_connect_id mediumint(6),
        tag_repo_id mediumint(6),
        tag_place_id mediumint(6),
        tag_address_id mediumint(6),
        tag_text_id mediumint(6),
        tag_tree_id smallint(5),
        tag_tag text CHARACTER SET utf8,
        PRIMARY KEY (tag_id),
        KEY (tag_tree_id),
        KEY (tag_pers_id),
        KEY (tag_rel_id),
        KEY (tag_event_id),
        KEY (tag_source_id),
        KEY (tag_connect_id),
        KEY (tag_repo_id),
        KEY (tag_place_id),
        KEY (tag_address_id),
        KEY (tag_text_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
        $this->dbh->query($sql);

        // *** Get tree_id of tree_prefix humo_ ***
        $sql = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='humo_'");
        $resultDb = $sql->fetch(PDO::FETCH_OBJ);
        $humo_tree_id = $resultDb->tree_id;

        // *** Check if table exists already if not create it ***
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_repositories'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_repositories (
            repo_id mediumint(6) unsigned NOT NULL auto_increment,
            repo_tree_id smallint(5),
            repo_gedcomnr varchar(20) CHARACTER SET utf8,
            repo_name text CHARACTER SET utf8,
            repo_address text CHARACTER SET utf8,
            repo_zip varchar(20) CHARACTER SET utf8,
            repo_place varchar(75) CHARACTER SET utf8,
            repo_phone varchar(20) CHARACTER SET utf8,
            repo_date varchar(35) CHARACTER SET utf8,
            repo_text text CHARACTER SET utf8,
            repo_photo text CHARACTER SET utf8,
            repo_mail varchar(100) CHARACTER SET utf8,
            repo_url varchar(150) CHARACTER SET utf8,
            repo_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            repo_unprocessed_tags text CHARACTER SET utf8,
            repo_new_date varchar(35) CHARACTER SET utf8,
            repo_new_time varchar(25) CHARACTER SET utf8,
            repo_changed_date varchar(35) CHARACTER SET utf8,
            repo_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`repo_id`),
            KEY (repo_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $this->dbh->query($tbldbqry);
        } else {
            // *** Remove source column from repository table ***
            $qry = "ALTER TABLE humo_repositories DROP repo_source,
            ADD repo_tree_id smallint(5) AFTER repo_id,
            ADD KEY(`repo_tree_id`);";
            $sql_get = $this->dbh->query($qry);

            // *** Add repo_tree_id value in table ***
            $this->dbh->query("UPDATE humo_repositories SET repo_tree_id='" . $humo_tree_id . "' WHERE repo_id!=''");
        }


        // *** Check if table exists already if not create it ***
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_sources'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_sources (
            source_id mediumint(6) unsigned NOT NULL auto_increment,
            source_tree_id smallint(5),
            source_status varchar(10) CHARACTER SET utf8,
            source_gedcomnr varchar(20) CHARACTER SET utf8,
            source_order mediumint(6),
            source_title text CHARACTER SET utf8,
            source_abbr varchar(50) CHARACTER SET utf8,
            source_date varchar(35) CHARACTER SET utf8,
            source_publ varchar(150) CHARACTER SET utf8,
            source_place varchar(75) CHARACTER SET utf8,
            source_refn varchar(50) CHARACTER SET utf8,
            source_auth varchar(50) CHARACTER SET utf8,
            source_subj varchar(50) CHARACTER SET utf8,
            source_item varchar(30) CHARACTER SET utf8,
            source_kind varchar(50) CHARACTER SET utf8,
            source_text text CHARACTER SET utf8,
            source_photo text CHARACTER SET utf8,
            source_repo_name varchar(50) CHARACTER SET utf8,
            source_repo_caln varchar(50) CHARACTER SET utf8,
            source_repo_page varchar(50) CHARACTER SET utf8,
            source_repo_gedcomnr varchar(20) CHARACTER SET utf8,
            source_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            source_unprocessed_tags text CHARACTER SET utf8,
            source_new_date varchar(35) CHARACTER SET utf8,
            source_new_time varchar(25) CHARACTER SET utf8,
            source_changed_date varchar(35) CHARACTER SET utf8,
            source_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`source_id`),
            KEY (source_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $this->dbh->query($tbldbqry);
        } else {
            // *** Add primary key ***
            $sql = "ALTER TABLE humo_sources ADD PRIMARY KEY(`source_id`),
            ADD source_tree_id smallint(5) AFTER source_id,
            ADD KEY(`source_tree_id`);";
            $this->dbh->query($sql);

            // *** Add source_tree_id value in table ***
            $this->dbh->query("UPDATE humo_sources SET source_tree_id='" . $humo_tree_id . "' WHERE source_id!=''");

            // *** Drop old index ***
            $sql = "ALTER TABLE humo_sources DROP INDEX source_id;";
            $this->dbh->query($sql);
        }

        // *** Check if table exists already if not create it ***
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_texts'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_texts (
            text_id mediumint(6) unsigned NOT NULL auto_increment,
            text_tree_id smallint(5),
            text_gedcomnr varchar(20) CHARACTER SET utf8,
            text_text text CHARACTER SET utf8,
            text_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            text_unprocessed_tags text CHARACTER SET utf8,
            text_new_date varchar(35) CHARACTER SET utf8,
            text_new_time varchar(25) CHARACTER SET utf8,
            text_changed_date varchar(35) CHARACTER SET utf8,
            text_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (text_id),
            KEY (text_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $this->dbh->query($tbldbqry);
        } else {
            // *** Add primary key ***
            $qry = "ALTER TABLE humo_texts ADD PRIMARY KEY(`text_id`),
            ADD text_tree_id smallint(5) AFTER text_id,
            ADD KEY(`text_tree_id`)";
            $sql_get = $this->dbh->query($qry);

            // *** Add repo_tree_id value in table ***
            $this->dbh->query("UPDATE humo_texts SET text_tree_id='" . $humo_tree_id . "' WHERE text_id!=''");

            // *** Drop old index ***
            $qry = "ALTER TABLE humo_texts DROP INDEX text_id;";
            $sql_get = $this->dbh->query($qry);
        }


        // *** Check if table exists already if not create it ***
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_connections'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_connections (
            connect_id mediumint(6) unsigned NOT NULL auto_increment,
            connect_tree_id smallint(5),
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
            connect_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            connect_unprocessed_tags text CHARACTER SET utf8,
            connect_new_date varchar(35) CHARACTER SET utf8,
            connect_new_time varchar(25) CHARACTER SET utf8,
            connect_changed_date varchar(35) CHARACTER SET utf8,
            connect_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`connect_id`),
            KEY (connect_connect_id),
            KEY (connect_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $this->dbh->query($tbldbqry);
        } else {
            $sql = "ALTER TABLE humo_connections ADD connect_tree_id smallint(5) AFTER connect_id, ADD KEY(`connect_tree_id`)";
            $this->dbh->query($sql);

            // *** Add repo_tree_id value in table ***
            $this->dbh->query("UPDATE humo_connections SET connect_tree_id='" . $humo_tree_id . "' WHERE connect_id!=''");
            $this->dbh->query("UPDATE humo_connections SET connect_sub_kind='pers_event_source' WHERE connect_kind='person' AND connect_sub_kind='event_source'");
            $this->dbh->query("UPDATE humo_connections SET connect_sub_kind='fam_event_source' WHERE connect_kind='family' AND connect_sub_kind='event_source'");
            $this->dbh->query("UPDATE humo_connections SET connect_sub_kind='pers_address_source' WHERE connect_kind='person' AND connect_sub_kind='address_source'");
            $this->dbh->query("UPDATE humo_connections SET connect_sub_kind='fam_address_source' WHERE connect_kind='family' AND connect_sub_kind='address_source'");
        }

        // *** Check if table exists already if not create it ***
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_addresses'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_addresses(
            address_id mediumint(6) unsigned NOT NULL auto_increment,
            address_tree_id smallint(5),
            address_gedcomnr varchar(20) CHARACTER SET utf8,
            address_order mediumint(6),
            address_person_id varchar(20) CHARACTER SET utf8,
            address_family_id varchar(20) CHARACTER SET utf8,
            address_address text CHARACTER SET utf8,
            address_zip varchar(20) CHARACTER SET utf8,
            address_place varchar(75) CHARACTER SET utf8,
            address_phone varchar(20) CHARACTER SET utf8,
            address_date varchar(35) CHARACTER SET utf8,
            address_text text CHARACTER SET utf8,
            address_photo text CHARACTER SET utf8,
            address_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            address_unprocessed_tags text CHARACTER SET utf8,
            address_new_date varchar(35) CHARACTER SET utf8,
            address_new_time varchar(25) CHARACTER SET utf8,
            address_changed_date varchar(35) CHARACTER SET utf8,
            address_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`address_id`),
            KEY (address_tree_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $this->dbh->query($tbldbqry);
        } else {
            $sql = "ALTER TABLE humo_addresses ADD address_tree_id smallint(5) AFTER address_id, ADD KEY(`address_tree_id`)";
            $this->dbh->query($sql);

            // *** Add key ***
            //$sql = "ALTER TABLE humo_addresses ADD KEY(`address_tree_id`);";
            //$result=$this->dbh->query($sql);

            // *** Add address_tree_id value in table ***
            $this->dbh->query("UPDATE humo_addresses SET address_tree_id='" . $humo_tree_id . "' WHERE address_id!=''");

            // *** Remove source columns from addresses table ***
            $qry = "ALTER TABLE humo_addresses DROP address_source;";
            $sql_get = $this->dbh->query($qry);
        }

        // *** Check if table exists already if not create it ***
        $temp = $this->dbh->query("SHOW TABLES LIKE 'humo_events'");
        if (!$temp->rowCount()) {
            $tbldbqry = "CREATE TABLE humo_events (
            event_id mediumint(6) unsigned NOT NULL auto_increment,
            event_tree_id smallint(5),
            event_gedcomnr varchar(20) CHARACTER SET utf8,
            event_order mediumint(6),
            event_person_id varchar(20) CHARACTER SET utf8,
            event_pers_age varchar(15) CHARACTER SET utf8,
            event_family_id varchar(20) CHARACTER SET utf8,
            event_kind varchar(20) CHARACTER SET utf8,
            event_event text CHARACTER SET utf8,
            event_event_extra text CHARACTER SET utf8,
            event_gedcom varchar(10) CHARACTER SET utf8,
            event_date varchar(35) CHARACTER SET utf8,
            event_place varchar(75) CHARACTER SET utf8,
            event_text text CHARACTER SET utf8,
            event_quality varchar(1) CHARACTER SET utf8 DEFAULT '',
            event_unprocessed_tags text CHARACTER SET utf8,
            event_new_date varchar(35) CHARACTER SET utf8,
            event_new_time varchar(25) CHARACTER SET utf8,
            event_changed_date varchar(35) CHARACTER SET utf8,
            event_changed_time varchar(25) CHARACTER SET utf8,
            PRIMARY KEY (`event_id`),
            KEY (event_tree_id),
            KEY (event_person_id),
            KEY (event_family_id),
            KEY (event_kind)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8";
            $this->dbh->query($tbldbqry);
        } else {
            $sql = "ALTER TABLE humo_events ADD event_tree_id smallint(5) AFTER event_id,
            ADD event_pers_age varchar(15) CHARACTER SET utf8 AFTER event_person_id,
            ADD KEY(`event_tree_id`)";
            $this->dbh->query($sql);

            // *** Add event_tree_id value in table ***
            $this->dbh->query("UPDATE humo_events SET event_tree_id='" . $humo_tree_id . "' WHERE event_id!=''");

            // *** Remove source columns from event table ***
            $qry = "ALTER TABLE humo_events DROP event_source;";
            $sql_get = $this->dbh->query($qry);
        }

        // *** Read all family trees from database ***
        $update_sql = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {

            // *** Show status of database update ***
            //echo '<script>';
            //	echo 'document.getElementById("information").innerHTML="'.__('Update tree:').' '.$updateDb->tree_id.'";';
            //echo '</script>';
            //ob_start();
            echo __('Update tree:') . ' ' . $updateDb->tree_id . '<br>';
            //ob_flush();
            flush();

            // *** Copy items from humo[nr]_person to humo_persons table ***
            // *** Batch processing ***
            $this->dbh->beginTransaction();
            $sql_get = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "person");
            while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                $sql_put = $this->dbh->prepare("INSERT INTO humo_persons SET
                    pers_gedcomnumber = :pers_gedcomnumber,
                    pers_tree_id = :pers_tree_id,
                    pers_tree_prefix = :pers_tree_prefix,
                    pers_famc = :pers_famc,
                    pers_fams = :pers_fams,
                    pers_indexnr = :pers_indexnr,
                    pers_firstname = :pers_firstname,
                    pers_callname = :pers_callname,
                    pers_prefix = :pers_prefix,
                    pers_lastname = :pers_lastname,
                    pers_patronym = :pers_patronym,
                    pers_name_text = :pers_name_text,
                    pers_sexe = :pers_sexe,
                    pers_own_code = :pers_own_code,
                    pers_birth_place = :pers_birth_place,
                    pers_birth_date = :pers_birth_date,
                    pers_birth_time = :pers_birth_time,
                    pers_birth_text = :pers_birth_text,
                    pers_stillborn = :pers_stillborn,
                    pers_bapt_place = :pers_bapt_place,
                    pers_bapt_date = :pers_bapt_date,
                    pers_bapt_text = :pers_bapt_text,
                    pers_religion = :pers_religion,
                    pers_death_place = :pers_death_place,
                    pers_death_date = :pers_death_date,
                    pers_death_time = :pers_death_time,
                    pers_death_text = :pers_death_text,
                    pers_death_cause = :pers_death_cause,
                    pers_buried_place = :pers_buried_place,
                    pers_buried_date = :pers_buried_date,
                    pers_buried_text = :pers_buried_text,
                    pers_cremation = :pers_cremation,
                    pers_place_index = :pers_place_index,
                    pers_text = :pers_text,
                    pers_alive = :pers_alive,
                    pers_quality = :pers_quality,
                    pers_new_date = :pers_new_date,
                    pers_new_time = :pers_new_time,
                    pers_changed_date = :pers_changed_date,
                    pers_changed_time = :pers_changed_time
                ");
                $sql_put->execute([
                    ':pers_gedcomnumber' => $getDb->pers_gedcomnumber,
                    ':pers_tree_id' => $updateDb->tree_id,
                    ':pers_tree_prefix' => $getDb->pers_tree_prefix,
                    ':pers_famc' => $getDb->pers_famc,
                    ':pers_fams' => $getDb->pers_fams,
                    ':pers_indexnr' => $getDb->pers_indexnr,
                    ':pers_firstname' => $getDb->pers_firstname,
                    ':pers_callname' => $getDb->pers_callname,
                    ':pers_prefix' => $getDb->pers_prefix,
                    ':pers_lastname' => $getDb->pers_lastname,
                    ':pers_patronym' => $getDb->pers_patronym,
                    ':pers_name_text' => $getDb->pers_name_text,
                    ':pers_sexe' => $getDb->pers_sexe,
                    ':pers_own_code' => $getDb->pers_own_code,
                    ':pers_birth_place' => $getDb->pers_birth_place,
                    ':pers_birth_date' => $getDb->pers_birth_date,
                    ':pers_birth_time' => $getDb->pers_birth_time,
                    ':pers_birth_text' => $getDb->pers_birth_text,
                    ':pers_stillborn' => $getDb->pers_stillborn,
                    ':pers_bapt_place' => $getDb->pers_bapt_place,
                    ':pers_bapt_date' => $getDb->pers_bapt_date,
                    ':pers_bapt_text' => $getDb->pers_bapt_text,
                    ':pers_religion' => $getDb->pers_religion,
                    ':pers_death_place' => $getDb->pers_death_place,
                    ':pers_death_date' => $getDb->pers_death_date,
                    ':pers_death_time' => $getDb->pers_death_time,
                    ':pers_death_text' => $getDb->pers_death_text,
                    ':pers_death_cause' => $getDb->pers_death_cause,
                    ':pers_buried_place' => $getDb->pers_buried_place,
                    ':pers_buried_date' => $getDb->pers_buried_date,
                    ':pers_buried_text' => $getDb->pers_buried_text,
                    ':pers_cremation' => $getDb->pers_cremation,
                    ':pers_place_index' => $getDb->pers_place_index,
                    ':pers_text' => $getDb->pers_text,
                    ':pers_alive' => $getDb->pers_alive,
                    ':pers_quality' => $getDb->pers_quality,
                    ':pers_new_date' => $getDb->pers_new_date,
                    ':pers_new_time' => $getDb->pers_new_time,
                    ':pers_changed_date' => $getDb->pers_changed_date,
                    ':pers_changed_time' => $getDb->pers_changed_time
                ]);

                $pers_id = $this->dbh->lastInsertId();

                if ($getDb->pers_unprocessed_tags) {
                    $gebeurtsql = $this->dbh->prepare("INSERT INTO humo_unprocessed_tags SET
                        tag_pers_id = :pers_id,
                        tag_tree_id = :tree_id,
                        tag_tag = :tag
                    ");
                    $gebeurtsql->execute([
                        ':pers_id' => $pers_id,
                        ':tree_id' => $updateDb->tree_id,
                        ':tag' => $getDb->pers_unprocessed_tags
                    ]);
                }
            }

            // *** Commit data in database ***
            $this->dbh->commit();

            // *** Remove old humo[nr]_repositories table ***
            $qry = "DROP TABLE " . $updateDb->tree_prefix . "person;";
            $this->dbh->query($qry);


            // *** Copy items from humo[nr]_family to humo_families table ***
            // *** Batch processing ***
            $this->dbh->beginTransaction();
            $sql_get = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "family");
            while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                $sql_put = $this->dbh->prepare("INSERT INTO humo_families SET
                    fam_gedcomnumber = :fam_gedcomnumber,
                    fam_tree_id = :fam_tree_id,
                    fam_man = :fam_man,
                    fam_woman = :fam_woman,
                    fam_children = :fam_children,
                    fam_kind = :fam_kind,
                    fam_relation_date = :fam_relation_date,
                    fam_relation_place = :fam_relation_place,
                    fam_relation_text = :fam_relation_text,
                    fam_relation_end_date = :fam_relation_end_date,
                    fam_marr_notice_date = :fam_marr_notice_date,
                    fam_marr_notice_place = :fam_marr_notice_place,
                    fam_marr_notice_text = :fam_marr_notice_text,
                    fam_marr_date = :fam_marr_date,
                    fam_marr_place = :fam_marr_place,
                    fam_marr_text = :fam_marr_text,
                    fam_marr_authority = :fam_marr_authority,
                    fam_marr_church_notice_date = :fam_marr_church_notice_date,
                    fam_marr_church_notice_place = :fam_marr_church_notice_place,
                    fam_marr_church_notice_text = :fam_marr_church_notice_text,
                    fam_marr_church_date = :fam_marr_church_date,
                    fam_marr_church_place = :fam_marr_church_place,
                    fam_marr_church_text = :fam_marr_church_text,
                    fam_religion = :fam_religion,
                    fam_div_date = :fam_div_date,
                    fam_div_place = :fam_div_place,
                    fam_div_text = :fam_div_text,
                    fam_div_authority = :fam_div_authority,
                    fam_text = :fam_text,
                    fam_alive = :fam_alive,
                    fam_quality = :fam_quality,
                    fam_counter = :fam_counter,
                    fam_new_date = :fam_new_date,
                    fam_new_time = :fam_new_time,
                    fam_changed_date = :fam_changed_date,
                    fam_changed_time = :fam_changed_time
                ");
                $sql_put->execute([
                    ':fam_gedcomnumber' => $getDb->fam_gedcomnumber,
                    ':fam_tree_id' => $updateDb->tree_id,
                    ':fam_man' => $getDb->fam_man,
                    ':fam_woman' => $getDb->fam_woman,
                    ':fam_children' => $getDb->fam_children,
                    ':fam_kind' => $getDb->fam_kind,
                    ':fam_relation_date' => $getDb->fam_relation_date,
                    ':fam_relation_place' => $getDb->fam_relation_place,
                    ':fam_relation_text' => $getDb->fam_relation_text,
                    ':fam_relation_end_date' => $getDb->fam_relation_end_date,
                    ':fam_marr_notice_date' => $getDb->fam_marr_notice_date,
                    ':fam_marr_notice_place' => $getDb->fam_marr_notice_place,
                    ':fam_marr_notice_text' => $getDb->fam_marr_notice_text,
                    ':fam_marr_date' => $getDb->fam_marr_date,
                    ':fam_marr_place' => $getDb->fam_marr_place,
                    ':fam_marr_text' => $getDb->fam_marr_text,
                    ':fam_marr_authority' => $getDb->fam_marr_authority,
                    ':fam_marr_church_notice_date' => $getDb->fam_marr_church_notice_date,
                    ':fam_marr_church_notice_place' => $getDb->fam_marr_church_notice_place,
                    ':fam_marr_church_notice_text' => $getDb->fam_marr_church_notice_text,
                    ':fam_marr_church_date' => $getDb->fam_marr_church_date,
                    ':fam_marr_church_place' => $getDb->fam_marr_church_place,
                    ':fam_marr_church_text' => $getDb->fam_marr_church_text,
                    ':fam_religion' => $getDb->fam_religion,
                    ':fam_div_date' => $getDb->fam_div_date,
                    ':fam_div_place' => $getDb->fam_div_place,
                    ':fam_div_text' => $getDb->fam_div_text,
                    ':fam_div_authority' => $getDb->fam_div_authority,
                    ':fam_text' => $getDb->fam_text,
                    ':fam_alive' => $getDb->fam_alive,
                    ':fam_quality' => $getDb->fam_quality,
                    ':fam_counter' => $getDb->fam_counter,
                    ':fam_new_date' => $getDb->fam_new_date,
                    ':fam_new_time' => $getDb->fam_new_time,
                    ':fam_changed_date' => $getDb->fam_changed_date,
                    ':fam_changed_time' => $getDb->fam_changed_time
                ]);

                $fam_id = $this->dbh->lastInsertId();

                if ($getDb->fam_unprocessed_tags) {
                    $gebeurtsql = $this->dbh->prepare("INSERT INTO humo_unprocessed_tags SET
                        tag_rel_id = :fam_id,
                        tag_tree_id = :tree_id,
                        tag_tag = :tag
                    ");
                    $gebeurtsql->execute([
                        ':fam_id' => $fam_id,
                        ':tree_id' => $updateDb->tree_id,
                        ':tag' => $getDb->fam_unprocessed_tags
                    ]);
                }
            }

            // *** Commit data in database ***
            $this->dbh->commit();

            // *** Remove old humo[nr]_repositories table ***
            $qry = "DROP TABLE " . $updateDb->tree_prefix . "family;";
            $this->dbh->query($qry);

            // *** Change @N1@ into N1 reference ***
            $qry = "UPDATE " . $updateDb->tree_prefix . "texts SET text_gedcomnr=REPLACE(text_gedcomnr, '@', '')";
            $sql_get = $this->dbh->query($qry);

            // *** Combine multiple humo[nr]_repositories tables into 1 humo_repositories table ***
            if ($updateDb->tree_prefix != 'humo_') {
                $sql_get = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "repositories");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    $sql_put = $this->dbh->prepare("INSERT INTO humo_repositories SET
                        repo_gedcomnr = :gedcomnr,
                        repo_tree_id = :tree_id,
                        repo_name = :name,
                        repo_address = :address,
                        repo_zip = :zip,
                        repo_place = :place,
                        repo_phone = :phone,
                        repo_date = :date,
                        repo_text = :text,
                        repo_photo = :photo,
                        repo_mail = :mail,
                        repo_url = :url,
                        repo_quality = :quality,
                        repo_unprocessed_tags = :unprocessed_tags,
                        repo_new_date = :new_date,
                        repo_new_time = :new_time,
                        repo_changed_date = :changed_date,
                        repo_changed_time = :changed_time
                    ");
                    $sql_put->execute([
                        ':gedcomnr' => $getDb->repo_gedcomnr,
                        ':tree_id' => $updateDb->tree_id,
                        ':name' => $getDb->repo_name,
                        ':address' => $getDb->repo_address,
                        ':zip' => $getDb->repo_zip,
                        ':place' => $getDb->repo_place,
                        ':phone' => $getDb->repo_phone,
                        ':date' => $getDb->repo_date,
                        ':text' => $getDb->repo_text,
                        ':photo' => $getDb->repo_photo,
                        ':mail' => $getDb->repo_mail,
                        ':url' => $getDb->repo_url,
                        ':quality' => $getDb->repo_quality,
                        ':unprocessed_tags' => $getDb->repo_unprocessed_tags,
                        ':new_date' => $getDb->repo_new_date,
                        ':new_time' => $getDb->repo_new_time,
                        ':changed_date' => $getDb->repo_changed_date,
                        ':changed_time' => $getDb->repo_changed_time
                    ]);
                }

                // *** Remove old humo[nr]_repositories table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "repositories;";
                $this->dbh->query($qry);

                // *** Batch processing ***
                $this->dbh->beginTransaction();
                $sql_get = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "sources");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    $sql_put = $this->dbh->prepare("INSERT INTO humo_sources SET
                        source_tree_id = :tree_id,
                        source_status = :status,
                        source_gedcomnr = :gedcomnr,
                        source_order = :order,
                        source_title = :title,
                        source_abbr = :abbr,
                        source_date = :date,
                        source_place = :place,
                        source_publ = :publ,
                        source_refn = :refn,
                        source_auth = :auth,
                        source_subj = :subj,
                        source_item = :item,
                        source_kind = :kind,
                        source_text = :text,
                        source_photo = :photo,
                        source_repo_name = :repo_name,
                        source_repo_caln = :repo_caln,
                        source_repo_page = :repo_page,
                        source_repo_gedcomnr = :repo_gedcomnr,
                        source_quality = :quality,
                        source_unprocessed_tags = :unprocessed_tags,
                        source_new_date = :new_date,
                        source_new_time = :new_time,
                        source_changed_date = :changed_date,
                        source_changed_time = :changed_time
                    ");
                    $sql_put->execute([
                        ':tree_id' => $updateDb->tree_id,
                        ':status' => $getDb->source_status,
                        ':gedcomnr' => $getDb->source_gedcomnr,
                        ':order' => $getDb->source_order,
                        ':title' => $getDb->source_title,
                        ':abbr' => $getDb->source_abbr,
                        ':date' => $getDb->source_date,
                        ':place' => $getDb->source_place,
                        ':publ' => $getDb->source_publ,
                        ':refn' => $getDb->source_refn,
                        ':auth' => $getDb->source_auth,
                        ':subj' => $getDb->source_subj,
                        ':item' => $getDb->source_item,
                        ':kind' => $getDb->source_kind,
                        ':text' => $getDb->source_text,
                        ':photo' => $getDb->source_photo,
                        ':repo_name' => $getDb->source_repo_name,
                        ':repo_caln' => $getDb->source_repo_caln,
                        ':repo_page' => $getDb->source_repo_page,
                        ':repo_gedcomnr' => $getDb->source_repo_gedcomnr,
                        ':quality' => $getDb->source_quality,
                        ':unprocessed_tags' => $getDb->source_unprocessed_tags,
                        ':new_date' => $getDb->source_new_date,
                        ':new_time' => $getDb->source_new_time,
                        ':changed_date' => $getDb->source_changed_date,
                        ':changed_time' => $getDb->source_changed_time
                    ]);
                }
                // *** Commit data in database ***
                $this->dbh->commit();

                // *** Remove old humo[nr]_sources table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "sources;";
                $this->dbh->query($qry);

                // *** Batch processing ***
                $this->dbh->beginTransaction();
                $sql_get = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "texts");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    $sql_put = $this->dbh->prepare("INSERT INTO humo_texts SET
                        text_tree_id = :tree_id,
                        text_gedcomnr = :gedcomnr,
                        text_text = :text,
                        text_quality = :quality,
                        text_unprocessed_tags = :unprocessed_tags,
                        text_new_date = :new_date,
                        text_new_time = :new_time,
                        text_changed_date = :changed_date,
                        text_changed_time = :changed_time
                    ");
                    $sql_put->execute([
                        ':tree_id' => $updateDb->tree_id,
                        ':gedcomnr' => $getDb->text_gedcomnr,
                        ':text' => $getDb->text_text,
                        ':quality' => $getDb->text_quality,
                        ':unprocessed_tags' => $getDb->text_unprocessed_tags,
                        ':new_date' => $getDb->text_new_date,
                        ':new_time' => $getDb->text_new_time,
                        ':changed_date' => $getDb->text_changed_date,
                        ':changed_time' => $getDb->text_changed_time
                    ]);
                }
                // *** Commit data in database ***
                $this->dbh->commit();

                // *** Remove old humo[nr]_texts table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "texts;";
                $this->dbh->query($qry);

                // *** Batch processing ***
                $this->dbh->beginTransaction();
                $sql_get = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "connections");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {

                    // STORE (address and events) REFERRED ID's IN ARRAY. Connect_sub_kind:
                    //person | event_source
                    //family  | event_source
                    //person | address_source
                    //family | address_source
                    $connect_sub_kind = $getDb->connect_sub_kind;
                    if ($getDb->connect_kind == 'person' && $getDb->connect_sub_kind == 'event_source') {
                        $person_event_source[$updateDb->tree_id][] = $getDb->connect_connect_id;
                        $connect_sub_kind = 'pers_event_source';
                    }
                    if ($getDb->connect_kind == 'family' && $getDb->connect_sub_kind == 'event_source') {
                        $family_event_source[$updateDb->tree_id][] = $getDb->connect_connect_id;
                        $connect_sub_kind = 'fam_event_source';
                    }
                    if ($getDb->connect_kind == 'person' && $getDb->connect_sub_kind == 'address_source') {
                        $person_address_source[$updateDb->tree_id][] = $getDb->connect_connect_id;
                        $connect_sub_kind = 'pers_address_source';
                    }
                    if ($getDb->connect_kind == 'family' && $getDb->connect_sub_kind == 'address_source') {
                        $family_address_source[$updateDb->tree_id][] = $getDb->connect_connect_id;
                        $connect_sub_kind = 'fam_address_source';
                    }

                    $sql_put = $this->dbh->prepare("INSERT INTO humo_connections SET
                        connect_tree_id = :tree_id,
                        connect_order = :order,
                        connect_kind = :kind,
                        connect_sub_kind = :sub_kind,
                        connect_connect_id = :connect_id,
                        connect_date = :date,
                        connect_place = :place,
                        connect_time = :time,
                        connect_page = :page,
                        connect_role = :role,
                        connect_text = :text,
                        connect_source_id = :source_id,
                        connect_item_id = :item_id,
                        connect_status = :status,
                        connect_quality = :quality,
                        connect_unprocessed_tags = :unprocessed_tags,
                        connect_new_date = :new_date,
                        connect_new_time = :new_time,
                        connect_changed_date = :changed_date,
                        connect_changed_time = :changed_time
                    ");
                    $sql_put->execute([
                        ':tree_id' => $updateDb->tree_id,
                        ':order' => $getDb->connect_order,
                        ':kind' => $getDb->connect_kind,
                        ':sub_kind' => $connect_sub_kind,
                        ':connect_id' => $getDb->connect_connect_id,
                        ':date' => $getDb->connect_date,
                        ':place' => $getDb->connect_place,
                        ':time' => $getDb->connect_time,
                        ':page' => $getDb->connect_page,
                        ':role' => $getDb->connect_role,
                        ':text' => $getDb->connect_text,
                        ':source_id' => $getDb->connect_source_id,
                        ':item_id' => $getDb->connect_item_id,
                        ':status' => $getDb->connect_status,
                        ':quality' => $getDb->connect_quality,
                        ':unprocessed_tags' => $getDb->connect_unprocessed_tags,
                        ':new_date' => $getDb->connect_new_date,
                        ':new_time' => $getDb->connect_new_time,
                        ':changed_date' => $getDb->connect_changed_date,
                        ':changed_time' => $getDb->connect_changed_time
                    ]);
                }
                // *** Commit data in database ***
                $this->dbh->commit();

                // *** Remove old humo[nr]_connections table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "connections;";
                $this->dbh->query($qry);


                // *** Batch processing ***
                $this->dbh->beginTransaction();
                $sql_get = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "addresses");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    $sql_put = $this->dbh->prepare("INSERT INTO humo_addresses SET
                        address_tree_id = :tree_id,
                        address_gedcomnr = :gedcomnr,
                        address_order = :order,
                        address_person_id = :person_id,
                        address_family_id = :family_id,
                        address_address = :address,
                        address_zip = :zip,
                        address_place = :place,
                        address_phone = :phone,
                        address_date = :date,
                        address_text = :text,
                        address_photo = :photo,
                        address_quality = :quality,
                        address_unprocessed_tags = :unprocessed_tags,
                        address_new_date = :new_date,
                        address_new_time = :new_time,
                        address_changed_date = :changed_date,
                        address_changed_time = :changed_time
                    ");
                    $sql_put->execute([
                        ':tree_id' => $updateDb->tree_id,
                        ':gedcomnr' => $getDb->address_gedcomnr,
                        ':order' => $getDb->address_order,
                        ':person_id' => $getDb->address_person_id,
                        ':family_id' => $getDb->address_family_id,
                        ':address' => $getDb->address_address,
                        ':zip' => $getDb->address_zip,
                        ':place' => $getDb->address_place,
                        ':phone' => $getDb->address_phone,
                        ':date' => $getDb->address_date,
                        ':text' => $getDb->address_text,
                        ':photo' => $getDb->address_photo,
                        ':quality' => $getDb->address_quality,
                        ':unprocessed_tags' => $getDb->address_unprocessed_tags,
                        ':new_date' => $getDb->address_new_date,
                        ':new_time' => $getDb->address_new_time,
                        ':changed_date' => $getDb->address_changed_date,
                        ':changed_time' => $getDb->address_changed_time
                    ]);

                    // PROCESS connection id's
                    // UPDATE connection table
                    if ((isset($person_address_source[$updateDb->tree_id]) and in_array($getDb->address_id, $person_address_source[$updateDb->tree_id])) && $this->dbh->lastInsertId() != 0) {
                        $qry = "UPDATE humo_connections SET connect_connect_id='" . $this->dbh->lastInsertId() . "'
                                WHERE connect_tree_id='" . $updateDb->tree_id . "'
                                AND connect_sub_kind='pers_address_source' AND connect_connect_id='" . $getDb->address_id . "'";
                        $this->dbh->query($qry);
                    }
                    // PROCESS connection id's
                    // UPDATE connection table
                    if ((isset($family_address_source[$updateDb->tree_id]) and in_array($getDb->address_id, $family_address_source[$updateDb->tree_id])) && $this->dbh->lastInsertId() != 0) {
                        $qry = "UPDATE humo_connections SET connect_connect_id='" . $this->dbh->lastInsertId() . "'
                                WHERE connect_tree_id='" . $updateDb->tree_id . "'
                                AND connect_sub_kind='fam_address_source' AND connect_connect_id='" . $getDb->address_id . "'";
                        $this->dbh->query($qry);
                    }
                }
                // *** Commit data in database ***
                $this->dbh->commit();

                // *** Remove old humo[nr]_addresses table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "addresses;";
                $this->dbh->query($qry);


                // *** Batch processing ***
                $this->dbh->beginTransaction();
                $sql_get = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events");
                while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
                    //event_event='".$getDb->event_event."',
                    $sql_put = $this->dbh->prepare("INSERT INTO humo_events SET
                        event_tree_id = :tree_id,
                        event_gedcomnr = :gedcomnr,
                        event_order = :order,
                        event_person_id = :person_id,
                        event_family_id = :family_id,
                        event_kind = :kind,
                        event_event = :event,
                        event_event_extra = :event_extra,
                        event_gedcom = :gedcom,
                        event_date = :date,
                        event_place = :place,
                        event_text = :text,
                        event_quality = :quality,
                        event_unprocessed_tags = :unprocessed_tags,
                        event_new_date = :new_date,
                        event_new_time = :new_time,
                        event_changed_date = :changed_date,
                        event_changed_time = :changed_time
                    ");
                    $sql_put->execute([
                        ':tree_id' => $updateDb->tree_id,
                        ':gedcomnr' => $getDb->event_gedcomnr,
                        ':order' => $getDb->event_order,
                        ':person_id' => $getDb->event_person_id,
                        ':family_id' => $getDb->event_family_id,
                        ':kind' => $getDb->event_kind,
                        ':event' => $getDb->event_event,
                        ':event_extra' => $getDb->event_event_extra,
                        ':gedcom' => $getDb->event_gedcom,
                        ':date' => $getDb->event_date,
                        ':place' => $getDb->event_place,
                        ':text' => $getDb->event_text,
                        ':quality' => $getDb->event_quality,
                        ':unprocessed_tags' => $getDb->event_unprocessed_tags,
                        ':new_date' => $getDb->event_new_date,
                        ':new_time' => $getDb->event_new_time,
                        ':changed_date' => $getDb->event_changed_date,
                        ':changed_time' => $getDb->event_changed_time
                    ]);

                    // PROCESS connection id's
                    // UPDATE connection table
                    if ((isset($person_event_source[$updateDb->tree_id]) and in_array($getDb->event_id, $person_event_source[$updateDb->tree_id])) && $this->dbh->lastInsertId() != 0) {
                        $qry = "UPDATE humo_connections SET connect_connect_id='" . $this->dbh->lastInsertId() . "'
                                WHERE connect_tree_id='" . $updateDb->tree_id . "'
                                AND connect_sub_kind='pers_event_source' AND connect_connect_id='" . $getDb->event_id . "'";
                        $this->dbh->query($qry);
                    }
                    // UPDATE connection table
                    if ((isset($family_event_source[$updateDb->tree_id]) and in_array($getDb->event_id, $family_event_source[$updateDb->tree_id])) && $this->dbh->lastInsertId() != 0) {
                        $qry = "UPDATE humo_connections SET connect_connect_id='" . $this->dbh->lastInsertId() . "'
                                WHERE connect_tree_id='" . $updateDb->tree_id . "'
                                AND connect_sub_kind='fam_event_source' AND connect_connect_id='" . $getDb->event_id . "'";
                        $this->dbh->query($qry);
                    }
                }
                // *** Commit data in database ***
                $this->dbh->commit();

                // *** Remove old humo[nr]_events table ***
                $qry = "DROP TABLE " . $updateDb->tree_prefix . "events;";
                $this->dbh->query($qry);
            }
        }

        // *** Show status of database update ***
        //echo '<script>';
        //echo 'document.getElementById("information").innerHTML="'.__('Update table unprocessed_tags...').'";';
        //echo '</script>';
        //ob_start();
        echo __('Update table unprocessed_tags...') . '<br>';
        //ob_flush();
        flush();


        // *** Copy tags from sources to tag table ***
        // *** Batch processing ***
        $this->dbh->beginTransaction();
        $sql = "SELECT source_id, source_tree_id, source_unprocessed_tags FROM humo_sources WHERE source_unprocessed_tags LIKE '_%'";
        $qry = $this->dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = $this->dbh->prepare("INSERT INTO humo_unprocessed_tags SET
                tag_tree_id = :tree_id,
                tag_source_id = :source_id,
                tag_tag = :tag
            ");
            $gebeurtsql->execute([
                ':tree_id' => $qryDb->source_tree_id,
                ':source_id' => $qryDb->source_id,
                ':tag' => $qryDb->source_unprocessed_tags
            ]);
        }
        // *** Commit data in database ***
        $this->dbh->commit();
        // *** Remove tags from source table ***
        $qry = "ALTER TABLE humo_sources DROP source_unprocessed_tags;";
        $this->dbh->query($qry);

        // *** Copy tags from repositories table to tag table ***
        $sql = "SELECT repo_id, repo_tree_id, repo_unprocessed_tags FROM humo_repositories WHERE repo_unprocessed_tags LIKE '_%'";
        $qry = $this->dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = $this->dbh->prepare("INSERT INTO humo_unprocessed_tags SET
                tag_tree_id = :tree_id,
                tag_repo_id = :repo_id,
                tag_tag = :tag
            ");
            $gebeurtsql->execute([
                ':tree_id' => $qryDb->repo_tree_id,
                ':repo_id' => $qryDb->repo_id,
                ':tag' => $qryDb->repo_unprocessed_tags
            ]);
        }
        // *** Remove tags from repositories table ***
        $qry = "ALTER TABLE humo_repositories DROP repo_unprocessed_tags;";
        $this->dbh->query($qry);

        // *** Copy tags from texts to tag table ***
        // *** Batch processing ***
        $this->dbh->beginTransaction();
        $sql = "SELECT text_id, text_tree_id, text_unprocessed_tags FROM humo_texts WHERE text_unprocessed_tags LIKE '_%'";
        $qry = $this->dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = $this->dbh->prepare("INSERT INTO humo_unprocessed_tags SET
                tag_tree_id = :tree_id,
                tag_text_id = :text_id,
                tag_tag = :tag
            ");
            $gebeurtsql->execute([
                ':tree_id' => $qryDb->text_tree_id,
                ':text_id' => $qryDb->text_id,
                ':tag' => $qryDb->text_unprocessed_tags
            ]);
        }
        // *** Commit data in database ***
        $this->dbh->commit();
        // *** Remove tags from texts table ***
        $qry = "ALTER TABLE humo_texts DROP text_unprocessed_tags;";
        $this->dbh->query($qry);

        // *** Copy tags from connections to tag table ***
        // *** Batch processing ***
        $this->dbh->beginTransaction();
        $sql = "SELECT connect_id, connect_tree_id, connect_unprocessed_tags FROM humo_connections WHERE connect_unprocessed_tags LIKE '_%'";
        $qry = $this->dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = $this->dbh->prepare("INSERT INTO humo_unprocessed_tags SET
                tag_tree_id = :tree_id,
                tag_connect_id = :connect_id,
                tag_tag = :tag
            ");
            $gebeurtsql->execute([
                ':tree_id' => $qryDb->connect_tree_id,
                ':connect_id' => $qryDb->connect_id,
                ':tag' => $qryDb->connect_unprocessed_tags
            ]);
        }
        // *** Commit data in database ***
        $this->dbh->commit();
        // *** Remove tags from connections table ***
        $qry = "ALTER TABLE humo_connections DROP connect_unprocessed_tags;";
        $this->dbh->query($qry);

        // *** Copy tags from addresses to tag table ***
        // *** Batch processing ***
        $this->dbh->beginTransaction();
        $sql = "SELECT address_id, address_tree_id, address_unprocessed_tags FROM humo_addresses WHERE address_unprocessed_tags LIKE '_%'";
        $qry = $this->dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = $this->dbh->prepare("INSERT INTO humo_unprocessed_tags SET
                tag_tree_id = :tree_id,
                tag_address_id = :address_id,
                tag_tag = :tag
            ");
            $gebeurtsql->execute([
                ':tree_id' => $qryDb->address_tree_id,
                ':address_id' => $qryDb->address_id,
                ':tag' => $qryDb->address_unprocessed_tags
            ]);
        }
        // *** Commit data in database ***
        $this->dbh->commit();
        // *** Remove tags from addresses table ***
        $qry = "ALTER TABLE humo_addresses DROP address_unprocessed_tags;";
        $this->dbh->query($qry);

        // *** Copy tags from events to tag table ***
        // *** Batch processing ***
        $this->dbh->beginTransaction();
        $sql = "SELECT event_id, event_tree_id, event_unprocessed_tags FROM humo_events WHERE event_unprocessed_tags LIKE '_%'";
        $qry = $this->dbh->query($sql);
        while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
            $gebeurtsql = $this->dbh->prepare("INSERT INTO humo_unprocessed_tags SET
                tag_tree_id = :tree_id,
                tag_event_id = :event_id,
                tag_tag = :tag
            ");
            $gebeurtsql->execute([
                ':tree_id' => $qryDb->event_tree_id,
                ':event_id' => $qryDb->event_id,
                ':tag' => $qryDb->event_unprocessed_tags
            ]);
        }
        // *** Commit data in database ***
        $this->dbh->commit();
        // *** Remove tags from events table ***
        $qry = "ALTER TABLE humo_events DROP event_unprocessed_tags;";
        $this->dbh->query($qry);
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

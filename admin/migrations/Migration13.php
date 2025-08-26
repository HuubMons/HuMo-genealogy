<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration13
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        // Implement the migration logic here.

        global $db_functions;

        flush();

        // *** Remove unwanted file from HuMo-genealogy ***
        if (file_exists('gedcom_files/HuMo-gen 2020_05_02 UTF-8.ged')) {
            unlink('gedcom_files/HuMo-gen 2020_05_02 UTF-8.ged');
        }

        $db_update = "ALTER TABLE humo_sources ADD source_shared varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER source_gedcomnr";
        $this->dbh->query($db_update);

        $db_update = "ALTER TABLE humo_addresses ADD address_shared varchar(1) CHARACTER SET utf8 DEFAULT '' AFTER address_gedcomnr";
        $this->dbh->query($db_update);

        // *** Update ALL lines in humo_source table for all family trees ***
        $sql = "UPDATE humo_sources SET source_shared='1'";
        $this->dbh->query($sql);

        // *** Update ALL lines in humo_address table for all family trees ***
        $sql = "UPDATE humo_addresses SET address_shared='1' WHERE address_gedcomnr LIKE '_%'";
        $this->dbh->query($sql);

        // *** Batch processing ***
        //$this->dbh->beginTransaction();

        // *** Read all family trees from database ***
        $update_sql = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = $db_functions->generate_gedcomnr($updateDb->tree_id, 'address');

            $address_qry = $this->dbh->query("SELECT * FROM humo_addresses
            WHERE address_tree_id='" . $updateDb->tree_id . "'
            AND (address_connect_kind='person' OR address_connect_kind='family')");
            while ($addressDb = $address_qry->fetch(PDO::FETCH_OBJ)) {
                $sql = "INSERT INTO humo_connections SET
                connect_tree_id='" . $updateDb->tree_id . "',
                connect_item_id='R" . $new_gedcomnumber . "',
                connect_date='" . $addressDb->address_date . "',
                connect_order='" . $addressDb->address_order . "',";
                if ($addressDb->address_connect_kind == 'person') {
                    $sql .= "connect_kind='person', connect_sub_kind='person_address',";
                } else {
                    $sql .= "connect_kind='family', connect_sub_kind='family_address',";
                }
                $sql .= "connect_connect_id='" . $addressDb->address_connect_id . "'";
                $this->dbh->query($sql);

                $sql = "UPDATE humo_addresses SET
                address_gedcomnr='R" . $new_gedcomnumber . "',
                address_order=0,
                address_date='',
                address_connect_kind='',
                address_connect_sub_kind='',
                address_connect_id=''
                WHERE address_id='" . $addressDb->address_id . "'";
                $this->dbh->query($sql);

                $new_gedcomnumber++;
            }

            // *** Change ID for address by source into address GEDCOM number ***
            $sql = "SELECT * FROM humo_connections LEFT JOIN humo_addresses ON address_id=connect_connect_id
            WHERE connect_tree_id='" . $updateDb->tree_id . "'
            AND (connect_sub_kind='pers_address_source' OR connect_sub_kind='fam_address_source' OR connect_sub_kind='address_source')";
            $qry = $this->dbh->query($sql);
            while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                $sql = "UPDATE humo_connections SET connect_connect_id='" . $qryDb->address_gedcomnr . "'
                WHERE connect_id='" . $qryDb->connect_id . "'";
                //echo $sql.'<br><br>';
                $this->dbh->query($sql);
            }

            // *** Update sources ***

            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = $db_functions->generate_gedcomnr($updateDb->tree_id, 'source');

            // *** Batch processing ***
            //$this->dbh->beginTransaction();
            $sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $updateDb->tree_id . "'
            AND substring(connect_sub_kind, -7)='_source' AND connect_source_id NOT LIKE '_%'";
            $qry = $this->dbh->query($sql);
            while ($qryDb = $qry->fetch(PDO::FETCH_OBJ)) {
                $sql = $this->dbh->prepare("INSERT INTO humo_sources SET
                    source_tree_id = :tree_id,
                    source_gedcomnr = :gedcomnr,
                    source_status = '',
                    source_title = '',
                    source_date = :date,
                    source_place = '',
                    source_publ = '',
                    source_refn = '',
                    source_auth = '',
                    source_subj = '',
                    source_item = '',
                    source_kind = '',
                    source_repo_caln = '',
                    source_repo_page = '',
                    source_repo_gedcomnr = '',
                    source_text = :text
                ");
                $sql->execute([
                    ':tree_id' => $updateDb->tree_id,
                    ':gedcomnr' => 'S' . $new_gedcomnumber,
                    ':date' => $qryDb->connect_date,
                    ':text' => $qryDb->connect_text
                ]);

                $sql = "UPDATE humo_connections SET connect_text='', connect_source_id='S" . $new_gedcomnumber . "' WHERE connect_id='" . $qryDb->connect_id . "'";
                $this->dbh->query($sql);

                $new_gedcomnumber++;
            }
            // *** Commit data in database ***
            //$this->dbh->commit();

        }

        flush();
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

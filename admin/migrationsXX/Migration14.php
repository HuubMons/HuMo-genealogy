<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration14
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

        // *** Add index to humo_addresses ***
        $index_check = $this->dbh->query("SHOW KEYS FROM humo_addresses WHERE Key_name='address_gedcomnr'");
        if ($index_check->rowCount() == 0) {
            $update_sql = "ALTER TABLE `humo_addresses` ADD INDEX(`address_gedcomnr`);";
            $this->dbh->query($update_sql);
        }

        // *** Update for IPv6 ***
        $check_qry = "SELECT CHARACTER_MAXIMUM_LENGTH FROM information_schema.columns WHERE table_name = 'humo_stat_date' AND COLUMN_NAME = 'stat_ip_address'";
        $check_result = $this->dbh->query($check_qry);
        $checkDb = $check_result->fetch(PDO::FETCH_OBJ);
        if ($checkDb->CHARACTER_MAXIMUM_LENGTH == 20) {
            $update_sql = "ALTER TABLE `humo_stat_date` CHANGE `stat_ip_address` `stat_ip_address` VARCHAR(40) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL;";
            $this->dbh->query($update_sql);
        }

        // *** Add connect_new_user and connect_changed_user ***
        $sql = "ALTER TABLE humo_connections ADD connect_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER connect_quality;";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_connections ADD connect_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER connect_new_time;";
        $this->dbh->query($sql);

        // *** Add text_new_user and text_changed_user ***
        $sql = "ALTER TABLE humo_texts ADD text_new_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER text_quality;";
        $this->dbh->query($sql);

        $sql = "ALTER TABLE humo_texts ADD text_changed_user varchar(200) CHARACTER SET utf8 DEFAULT NULL AFTER text_new_time;";
        $this->dbh->query($sql);

        // *** Move callname to event table ***
        // *** Batch processing ***
        //$this->dbh->beginTransaction();
        $sql_get = $this->dbh->query("SELECT pers_gedcomnumber,pers_tree_id,pers_callname FROM humo_persons WHERE pers_callname LIKE '_%'");
        while ($getDb = $sql_get->fetch(PDO::FETCH_OBJ)) {
            $sql_put = $this->dbh->prepare("INSERT INTO humo_events SET
                event_tree_id = :event_tree_id,
                event_order = 1,
                event_connect_kind = 'person',
                event_connect_id = :event_connect_id,
                event_kind = 'name',
                event_event = :event_event,
                event_gedcom = 'NICK'
            ");
            $sql_put->execute([
                ':event_tree_id' => $getDb->pers_tree_id,
                ':event_connect_id' => $getDb->pers_gedcomnumber,
                ':event_event' => $getDb->pers_callname
            ]);
        }
        // *** Commit data in database ***
        //$this->dbh->commit();

        flush();
    }

    public function down(): void
    {
        // Implement the rollback logic here.
    }
}

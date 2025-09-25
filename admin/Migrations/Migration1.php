<?php

namespace Genealogy\Admin\Migrations;

use PDO;
use Exception;

class Migration1
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function up(): void
    {
        $start_time = time();

        // *** Automatic installation or update ***
        $update_check_sql = $this->dbh->query("SELECT * FROM humo_user_log LIMIT 0,1");
        if ($update_check_sql) {
            if (isset($field)) {
                unset($field);
            }
            $column_qry = $this->dbh->query("SHOW COLUMNS FROM humo_user_log");
            while ($columnDb = $column_qry->fetch()) {
                $field_value = $columnDb['Field'];
                $field[$field_value] = $field_value;
                // *** test line ***
                //print '<span>'.$field[$field_value].'</span><br>';
            }
            if (!isset($field['log_ip_address'])) {
                $sql = "ALTER TABLE humo_user_log ADD log_ip_address varchar(20) CHARACTER SET utf8 DEFAULT ''";
                $this->dbh->query($sql);
            }
            if (!isset($field['log_user_admin'])) {
                $sql = "ALTER TABLE humo_user_log ADD log_user_admin varchar(5) CHARACTER SET utf8 DEFAULT ''";
                $this->dbh->query($sql);
            }
        }


        // *** Update 'Empty' line if used in tree table ***
        $update_tree_sql = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix='LEEG'");
        while ($update_treeDb = $update_tree_sql->fetch(PDO::FETCH_OBJ)) {
            $update_tree_sql2 = "UPDATE humo_trees SET
            tree_prefix='EMPTY',
            tree_persons='EMPTY',
            tree_families='EMPTY',
            tree_email='EMPTY',
            tree_pict_path='EMPTY',
            tree_privacy='EMPTY'
            WHERE tree_id=" . $update_treeDb->tree_id;
            $this->dbh->query($update_tree_sql2);
        }

        // *** Read all family trees from tree table ***
        $update_sql = $this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY'");
        while ($updateDb = $update_sql->fetch(PDO::FETCH_OBJ)) {
            echo '<b>Check ' . $updateDb->tree_prefix . '</b>';

            // *** Update tree privacy ***
            $tree_privacy = '';
            if ($updateDb->tree_privacy == 'Standaard') {
                $tree_privacy = 'standard';
            }
            if ($updateDb->tree_privacy == 'personen_filteren') {
                $tree_privacy = 'filter_persons';
            }
            if ($updateDb->tree_privacy == 'personen_weergeven') {
                $tree_privacy = 'show_persons';
            }
            if ($tree_privacy) {
                $sql = "UPDATE humo_trees SET tree_privacy='" . $tree_privacy . "' WHERE tree_id='" . $updateDb->tree_id . "'";
                $this->dbh->query($sql);
            }

            // *** Update person table ***
            $privacy_sql = $this->dbh->query("SELECT pers_id, pers_alive FROM " . $updateDb->tree_prefix . "person WHERE pers_alive!=''");
            while ($privacyDb = $privacy_sql->fetch(PDO::FETCH_OBJ)) {
                $pers_alive = $privacyDb->pers_alive;
                if ($privacyDb->pers_alive == 'HZ_levend') {
                    $pers_alive = 'alive';
                }
                if ($privacyDb->pers_alive == 'HZ_ovl') {
                    $pers_alive = 'deceased';
                }
                if ($privacyDb->pers_alive == 'Aldfaer_ovl') {
                    $pers_alive = 'deceased';
                }
                $sql = 'UPDATE ' . $updateDb->tree_prefix . 'person SET pers_alive="' . $pers_alive . '"
                WHERE pers_id="' . $privacyDb->pers_id . '"';
                $this->dbh->query($sql);
            }

            // *** Update person table ***
            $pers_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "person WHERE pers_lastname='doodgeboren kind'");
            while ($persDb = $pers_sql->fetch(PDO::FETCH_OBJ)) {
                $sql = 'UPDATE ' . $updateDb->tree_prefix . 'person SET pers_lastname="N.N", pers_stillborn="y" WHERE pers_id="' . $persDb->pers_id . '"';
                $this->dbh->query($sql);
            }

            // *** Update event table: translate all event_kind items to english ***
            $read_pers_sql = $this->dbh->query("SELECT * FROM " . $updateDb->tree_prefix . "events");
            while ($read_persDb = $read_pers_sql->fetch(PDO::FETCH_OBJ)) {
                if ($read_persDb->event_kind == 'adres') {
                    $event_kind = 'address';
                }
                if ($read_persDb->event_kind == 'afbeelding') {
                    $event_kind = 'picture';
                }
                if ($read_persDb->event_kind == 'begrafenisgetuige') {
                    $event_kind = 'burial_witness';
                }
                if ($read_persDb->event_kind == 'beroep') {
                    $event_kind = 'profession';
                }
                if ($read_persDb->event_kind == 'bron') {
                    $event_kind = 'source';
                }
                if ($read_persDb->event_kind == 'doopgetuige') {
                    $event_kind = 'baptism_witness';
                }
                if ($read_persDb->event_kind == 'gebeurtenis') {
                    $event_kind = 'event';
                }
                if ($read_persDb->event_kind == 'geboorteaangifte') {
                    $event_kind = 'birth_declaration';
                }
                if ($read_persDb->event_kind == 'getuige') {
                    $event_kind = 'witness';
                }
                if ($read_persDb->event_kind == 'heerlijkheid') {
                    $event_kind = 'lordship';
                }
                if ($read_persDb->event_kind == 'kerktrgetuige') {
                    $event_kind = 'marriage_witness_rel';
                }
                if ($read_persDb->event_kind == 'naam') {
                    $event_kind = 'name';
                }
                if ($read_persDb->event_kind == 'predikaat') {
                    $event_kind = 'nobility';
                }
                if ($read_persDb->event_kind == 'overlijdensaangifte') {
                    $event_kind = 'death_declaration';
                }
                if ($read_persDb->event_kind == 'titel') {
                    $event_kind = 'title';
                }
                if ($read_persDb->event_kind == 'trgetuige') {
                    $event_kind = 'marriage_witness';
                }
                if (isset($event_kind)) {
                    $sql = 'UPDATE ' . $updateDb->tree_prefix . 'events SET event_kind="' . $event_kind . '" WHERE event_id="' . $read_persDb->event_id . '"';
                    $this->dbh->query($sql);
                }
            }

            echo ' Tree updated!<br>';

            // *** Show processing time ***
            $end_time = time();
            echo $end_time - $start_time . ' ' . __('seconds.') . '<br>';
        }
    }

    public function down(): void
    {
        //
    }
}

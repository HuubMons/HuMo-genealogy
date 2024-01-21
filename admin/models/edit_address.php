<?php
class EditAddressModel
{
    private $address_id;

    public function set_address_id()
    {
        if (isset($_POST['address_id']) and is_numeric(($_POST['address_id']))) {
            $this->address_id = $_POST['address_id'];
        }
    }
    public function get_address_id()
    {
        return $this->address_id;
    }

    public function update_address($dbh, $tree_id, $db_functions, $editor_cls)
    {
        //$userid = false;
        //if (is_numeric($_SESSION['user_id_admin'])) $userid = $_SESSION['user_id_admin'];
        $username = $_SESSION['user_name_admin'];
        $gedcom_date = strtoupper(date("d M Y"));
        $gedcom_time = date("H:i:s");

        if (isset($_POST['address_add'])) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'R' . $db_functions->generate_gedcomnr($tree_id, 'address');

            //address_date='".safe_text_db($_POST['address_date'])."',
            $sql = "INSERT INTO humo_addresses SET
                address_tree_id='" . $tree_id . "',
                address_gedcomnr='" . $new_gedcomnumber . "',
                address_shared='1',
                address_address='" . $editor_cls->text_process($_POST['address_address']) . "',
                address_zip='" . safe_text_db($_POST['address_zip']) . "',
                address_place='" . $editor_cls->text_process($_POST['address_place']) . "',
                address_phone='" . safe_text_db($_POST['address_phone']) . "',
                address_text='" . $editor_cls->text_process($_POST['address_text']) . "',
                address_new_user='" . $username . "',
                address_new_date='" . $gedcom_date . "',
                address_new_time='" . $gedcom_time . "'";
            $dbh->query($sql);

            $this->address_id = $dbh->lastInsertId();
        }

        if (isset($_POST['address_change'])) {
            // *** Date by address is processed in connection table ***
            //address_date='".$editor_cls->date_process('address_date')."',
            $sql = "UPDATE humo_addresses SET
                address_address='" . $editor_cls->text_process($_POST['address_address']) . "',
                address_zip='" . safe_text_db($_POST['address_zip']) . "',
                address_place='" . $editor_cls->text_process($_POST['address_place']) . "',
                address_phone='" . safe_text_db($_POST['address_phone']) . "',
                address_text='" . $editor_cls->text_process($_POST['address_text'], true) . "',
                address_changed_user='" . $username . "',
                address_changed_date='" . $gedcom_date . "',
                address_changed_time='" . $gedcom_time . "'
                WHERE address_id='" . $this->address_id . "'";
            $dbh->query($sql);
        }

        if (isset($_POST['address_remove2'])) {
            // *** Remove sources by this address from connection table ***
            $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $tree_id . "'
                AND connect_kind='address' AND connect_connect_id='" . $this->address_id . "'";
            $dbh->query($sql);

            // *** Delete connections to address, and re-order remaining address connections ***
            $connect_sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $tree_id . "'
                AND connect_sub_kind='person_address' AND connect_item_id='" . safe_text_db($_POST["address_gedcomnr"]) . "'";
            $connect_qry = $dbh->query($connect_sql);
            while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
                // *** Delete source connections ***
                $sql = "DELETE FROM humo_connections WHERE connect_id='" . $connectDb->connect_id . "'";
                $dbh->query($sql);

                // *** Re-order remaining source connections ***
                $event_order = 1;
                $event_sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $tree_id . "'
                    AND connect_kind='" . $connectDb->connect_kind . "' AND connect_sub_kind='" . $connectDb->connect_sub_kind . "'
                    AND connect_connect_id='" . $connectDb->connect_connect_id . "' ORDER BY connect_order";
                $event_qry = $dbh->query($event_sql);
                while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                    $sql = "UPDATE humo_connections SET connect_order='" . $event_order . "' WHERE connect_id='" . $eventDb->connect_id . "'";
                    $dbh->query($sql);
                    $event_order++;
                }
            }

            // *** Delete address ***
            $sql = "DELETE FROM humo_addresses WHERE address_id='" . $this->address_id . "'";
            $dbh->query($sql);

            // *** Reset selected address ***
            $this->address_id = NULL;
        }
    }
}

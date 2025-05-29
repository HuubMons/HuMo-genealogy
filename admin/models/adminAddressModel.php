<?php
class AdminAddressModel extends AdminBaseModel
{
    private $address_id;

    public function set_address_id(): void
    {
        if (isset($_POST['address_id']) && is_numeric(($_POST['address_id']))) {
            $this->address_id = $_POST['address_id'];
        }
    }

    public function get_address_id()
    {
        return $this->address_id;
    }

    public function get_addresses()
    {
        $editAddress['search_gedcomnr'] = '';
        if (isset($_POST['address_search_gedcomnr'])) {
            $editAddress['search_gedcomnr'] = safe_text_db($_POST['address_search_gedcomnr']);
        }
        $editAddress['search_text'] = '';
        if (isset($_POST['address_search'])) {
            $editAddress['search_text'] = safe_text_db($_POST['address_search']);
        }

        $qry = "SELECT * FROM humo_addresses WHERE address_tree_id='" . $this->tree_id . "' AND address_shared='1'";
        if ($editAddress['search_gedcomnr']) {
            $qry .= " AND address_gedcomnr LIKE '%" . safe_text_db($editAddress['search_gedcomnr']) . "%'";
        }
        if ($editAddress['search_text']) {
            $qry .= " AND ( address_address LIKE '%" . safe_text_db($editAddress['search_text']) . "%' OR address_place LIKE '%" . safe_text_db($editAddress['search_text']) . "%')";
        }
        $qry .= " ORDER BY address_place, address_address LIMIT 0,200";

        $address_qry = $this->dbh->query($qry);
        while ($addressDb = $address_qry->fetch(PDO::FETCH_OBJ)) {
            $editAddress['addresses_id'][] = $addressDb->address_id;

            $editAddress['addresses_gedcomnr'][$addressDb->address_id] = $addressDb->address_gedcomnr;
            $editAddress['addresses_place'][$addressDb->address_id] = $addressDb->address_place;
            $editAddress['addresses_address'][$addressDb->address_id] = $addressDb->address_address;

            if ($addressDb->address_text) {
                $address_text = ' ' . substr($addressDb->address_text, 0, 40);
                if (strlen($addressDb->address_text) > 40) {
                    $address_text .= '...';
                }
                $editAddress['addresses_text'][$addressDb->address_id] = $address_text;
            } else {
                $editAddress['addresses_text'][$addressDb->address_id] = '';
            }
        }
        return $editAddress;
    }

    public function update_address($editor_cls): void
    {
        $userid = false;
        if (is_numeric($_SESSION['user_id_admin'])) {
            $userid = $_SESSION['user_id_admin'];
        }

        if (isset($_POST['address_add'])) {
            // *** Generate new GEDCOM number ***
            $new_gedcomnumber = 'R' . $this->db_functions->generate_gedcomnr($this->tree_id, 'address');

            //address_date='".safe_text_db($_POST['address_date'])."',
            $sql = "INSERT INTO humo_addresses SET
                address_tree_id='" . $this->tree_id . "',
                address_gedcomnr='" . $new_gedcomnumber . "',
                address_shared='1',
                address_address='" . $editor_cls->text_process($_POST['address_address']) . "',
                address_zip='" . safe_text_db($_POST['address_zip']) . "',
                address_place='" . $editor_cls->text_process($_POST['address_place']) . "',
                address_phone='" . safe_text_db($_POST['address_phone']) . "',
                address_text='" . $editor_cls->text_process($_POST['address_text']) . "',
                address_new_user_id='" . $userid . "'";
            $this->dbh->query($sql);

            $this->address_id = $this->dbh->lastInsertId();
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
                address_changed_user_id='" . $userid . "'
                WHERE address_id='" . $this->address_id . "'";
            $this->dbh->query($sql);
        }

        if (isset($_POST['address_remove2'])) {
            // *** Remove sources by this address from connection table ***
            $sql = "DELETE FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "'
                AND connect_kind='address' AND connect_connect_id='" . $this->address_id . "'";
            $this->dbh->query($sql);

            // *** Delete connections to address, and re-order remaining address connections ***
            $connect_sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "'
                AND connect_sub_kind='person_address' AND connect_item_id='" . safe_text_db($_POST["address_gedcomnr"]) . "'";
            $connect_qry = $this->dbh->query($connect_sql);
            while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
                // *** Delete source connections ***
                $sql = "DELETE FROM humo_connections WHERE connect_id='" . $connectDb->connect_id . "'";
                $this->dbh->query($sql);

                // *** Re-order remaining source connections ***
                $event_order = 1;
                $event_sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "'
                    AND connect_kind='" . $connectDb->connect_kind . "' AND connect_sub_kind='" . $connectDb->connect_sub_kind . "'
                    AND connect_connect_id='" . $connectDb->connect_connect_id . "' ORDER BY connect_order";
                $event_qry = $this->dbh->query($event_sql);
                while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                    $sql = "UPDATE humo_connections SET connect_order='" . $event_order . "' WHERE connect_id='" . $eventDb->connect_id . "'";
                    $this->dbh->query($sql);
                    $event_order++;
                }
            }

            // *** Delete connections to address, and re-order remaining address connections ***
            $connect_sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "'
                AND connect_sub_kind='family_address' AND connect_item_id='" . safe_text_db($_POST["address_gedcomnr"]) . "'";
            $connect_qry = $this->dbh->query($connect_sql);
            while ($connectDb = $connect_qry->fetch(PDO::FETCH_OBJ)) {
                // *** Delete source connections ***
                $sql = "DELETE FROM humo_connections WHERE connect_id='" . $connectDb->connect_id . "'";
                $this->dbh->query($sql);

                // *** Re-order remaining source connections ***
                $event_order = 1;
                $event_sql = "SELECT * FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "'
                    AND connect_kind='" . $connectDb->connect_kind . "' AND connect_sub_kind='" . $connectDb->connect_sub_kind . "'
                    AND connect_connect_id='" . $connectDb->connect_connect_id . "' ORDER BY connect_order";
                $event_qry = $this->dbh->query($event_sql);
                while ($eventDb = $event_qry->fetch(PDO::FETCH_OBJ)) {
                    $sql = "UPDATE humo_connections SET connect_order='" . $event_order . "' WHERE connect_id='" . $eventDb->connect_id . "'";
                    $this->dbh->query($sql);
                    $event_order++;
                }
            }

            // *** Delete address ***
            $sql = "DELETE FROM humo_addresses WHERE address_id='" . $this->address_id . "'";
            $this->dbh->query($sql);

            // *** Reset selected address ***
            $this->address_id = NULL;
        }
    }
}

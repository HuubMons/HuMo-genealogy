<?php
class RenamePlaceModel extends AdminBaseModel
{

    function update_place($editor_cls): void
    {
        if (isset($_POST['place_change'])) {
            $sql = "UPDATE humo_persons SET pers_birth_place = :place_new
                    WHERE pers_tree_id = :tree_id AND pers_birth_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_persons SET pers_bapt_place = :place_new
                    WHERE pers_tree_id = :tree_id AND pers_bapt_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_persons SET pers_death_place = :place_new
                WHERE pers_tree_id = :tree_id AND pers_death_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_persons SET pers_buried_place = :place_new
                    WHERE pers_tree_id = :tree_id AND pers_buried_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_families SET fam_relation_place = :place_new
                    WHERE fam_tree_id = :tree_id AND fam_relation_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_families SET fam_marr_notice_place = :place_new
                    WHERE fam_tree_id = :tree_id AND fam_marr_notice_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_families SET fam_marr_place = :place_new
                    WHERE fam_tree_id = :tree_id AND fam_marr_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_families SET fam_marr_church_notice_place = :place_new
                    WHERE fam_tree_id = :tree_id AND fam_marr_church_notice_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_families SET fam_marr_church_place = :place_new
                    WHERE fam_tree_id = :tree_id AND fam_marr_church_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_families SET fam_div_place = :place_new
                    WHERE fam_tree_id = :tree_id AND fam_div_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_addresses SET address_place = :place_new
                    WHERE address_tree_id = :tree_id AND address_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_events SET event_place = :place_new
                    WHERE event_tree_id = :tree_id AND event_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_sources SET source_place = :place_new
                    WHERE source_tree_id = :tree_id AND source_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_connections SET connect_place = :place_new
                    WHERE connect_tree_id = :tree_id AND connect_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $editor_cls->text_process($_POST['place_new']),
                ':tree_id'   => $this->tree_id,
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_location SET location_location = :place_new
                    WHERE location_location = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $_POST['place_new'],
                ':place_old' => $_POST['place_old']
            ]);

            // *** Show changed place again ***
            $_POST["place_select"] = $_POST['place_new'];
        }
    }

    function get_query()
    {
        $place_qry = "(SELECT pers_birth_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_birth_place)
            UNION (SELECT pers_bapt_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_bapt_place)
            UNION (SELECT pers_death_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_death_place)
            UNION (SELECT pers_buried_place as place_edit FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_buried_place)";
        $place_qry .= " UNION (SELECT fam_relation_place as place_edit FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' GROUP BY fam_relation_place)
            UNION (SELECT fam_marr_notice_place as place_edit FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' GROUP BY fam_marr_notice_place)
            UNION (SELECT fam_marr_place as place_edit FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' GROUP BY fam_marr_place)
            UNION (SELECT fam_marr_church_notice_place as place_edit FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' GROUP BY fam_marr_church_notice_place)
            UNION (SELECT fam_div_place as place_edit FROM humo_families WHERE fam_tree_id='" . $this->tree_id . "' GROUP BY fam_div_place)";
        $place_qry .= "UNION (SELECT address_place as place_edit FROM humo_addresses WHERE address_tree_id='" . $this->tree_id . "' GROUP BY address_place)
            UNION (SELECT event_place as place_edit FROM humo_events WHERE event_tree_id='" . $this->tree_id . "' GROUP BY event_place)
            UNION (SELECT source_place as place_edit FROM humo_sources WHERE source_tree_id='" . $this->tree_id . "' GROUP BY source_place)
            UNION (SELECT connect_place as place_edit FROM humo_connections WHERE connect_tree_id='" . $this->tree_id . "' GROUP BY connect_place)";
        $place_qry .= ' ORDER BY place_edit';
        return $this->dbh->query($place_qry);
    }

    function get_place_select(): string
    {
        $place_select = '';
        if (isset($_POST["place_select"]) && $_POST["place_select"]) {
            $place_select = $_POST["place_select"];
        }
        return $place_select;
    }
}

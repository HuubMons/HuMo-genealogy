<?php

namespace Genealogy\Admin\Models;

use Genealogy\Admin\Models\AdminBaseModel;

class RenamePlaceModel extends AdminBaseModel
{

    function update_place(): void
    {
        if (isset($_POST['place_change'])) {
            $sql = "UPDATE humo_addresses SET address_place = :place_new WHERE address_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $_POST['place_new'],
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_sources SET source_place = :place_new WHERE source_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $_POST['place_new'],
                ':place_old' => $_POST["place_old"]
            ]);

            $sql = "UPDATE humo_connections SET connect_place = :place_new WHERE connect_place = :place_old";
            $stmt = $this->dbh->prepare($sql);
            $stmt->execute([
                ':place_new' => $_POST['place_new'],
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
        $place_qry = "(
            SELECT location_location as place_edit FROM humo_location GROUP BY location_location
            )
            UNION (
            SELECT address_place as place_edit FROM humo_addresses GROUP BY address_place
            )
            ORDER BY place_edit";

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

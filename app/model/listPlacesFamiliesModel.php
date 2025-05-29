<?php
class ListPlacesFamiliesModel extends BaseModel
{
    public function getSelection(): array
    {
        // *** For index places ***
        $data["place_name"] = '';
        $data["select_marriage_notice"] = '0';
        $data["select_marriage"] = '0';
        $data["select_marriage_notice_religious"] = '0';
        $data["select_marriage_religious"] = '0';
        if (isset($_POST['place_name'])) {
            $data["place_name"] = $_POST['place_name'];
            //$data["place_name"]=htmlentities($_POST['place_name'],ENT_QUOTES,'UTF-8');
            $_SESSION["save_place_name"] = $data["place_name"];

            if (isset($_POST['select_marriage_notice'])) {
                $data["select_marriage_notice"] = '1';
                $_SESSION["save_select_marriage_notice"] = '1';
            } else {
                $_SESSION["save_select_marriage_notice"] = '0';
            }
            if (isset($_POST['select_marriage'])) {
                $data["select_marriage"] = '1';
                $_SESSION["save_select_marriage"] = '1';
            } else {
                $_SESSION["save_select_marriage"] = '0';
            }
            if (isset($_POST['select_marriage_notice_religious'])) {
                $data["select_marriage_notice_religious"] = '1';
                $_SESSION["save_select_marriage_notice_religious"] = '1';
            } else {
                $_SESSION["save_select_marriage_notice_religious"] = '0';
            }
            if (isset($_POST['select_marriage_religious'])) {
                $data["select_marriage_religious"] = '1';
                $_SESSION["save_select_marriage_religious"] = '1';
            } else {
                $_SESSION["save_select_marriage_religious"] = '0';
            }
        }
        $data["part_place_name"] = '';
        if (isset($_POST['part_place_name'])) {
            $data["part_place_name"] = $_POST['part_place_name'];
            $_SESSION["save_part_place_name"] = $data["part_place_name"];
        }

        // *** Search for places in birth-baptise-died places etc. ***
        if (isset($_SESSION["save_place_name"])) {
            $data["place_name"] = $_SESSION["save_place_name"];
        }
        if (isset($_SESSION["save_part_place_name"])) {
            $data["part_place_name"] = $_SESSION["save_part_place_name"];
        }

        // *** Enable select boxes ***
        if (isset($_GET['reset'])) {
            $data["select_marriage_notice"] = '1';
            $_SESSION["save_select_marriage_notice"] = '1';
            $data["select_marriage"] = '1';
            $_SESSION["save_select_marriage"] = '1';
            $data["select_marriage_notice_religious"] = '1';
            $_SESSION["save_select_marriage_notice_religious"] = '1';
            $data["select_marriage_religious"] = '1';
            $_SESSION["save_select_marriage_religious"] = '1';
        } else {
            // *** Read and set select boxes for multiple pages ***
            if (isset($_SESSION["save_select_marriage_notice"])) {
                $data["select_marriage_notice"] = $_SESSION["save_select_marriage_notice"];
            }
            if (isset($_SESSION["save_select_marriage"])) {
                $data["select_marriage"] = $_SESSION["save_select_marriage"];
            }
            if (isset($_SESSION["save_select_marriage_notice_religious"])) {
                $data["select_marriage_notice_religious"] = $_SESSION["save_select_marriage_notice_religious"];
            }
            if (isset($_SESSION["save_select_marriage_religious"])) {
                $data["select_marriage_religious"] = $_SESSION["save_select_marriage_religious"];
            }
        }
        return $data;
    }

    // *** Search for (part of) first or lastname ***
    // TODO this function is also used in script list.
    private function name_qry($search_name, $search_part): string
    {
        $text = "LIKE '%" . safe_text_db($search_name) . "%'"; // *** Default value: "contains" ***
        if ($search_part == 'equals') {
            $text = "='" . safe_text_db($search_name) . "'";
        }
        if ($search_part == 'starts_with') {
            $text = "LIKE '" . safe_text_db($search_name) . "%'";
        }
        return $text;
    }

    public function build_query(): string
    {
        $query = '';

        $data = $this->getSelection();

        //*** Places index ***
        // *** EXAMPLE of a UNION querie ***
        //$qry = "(SELECT * FROM humo1_person ".$query.') ';
        //$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
        //$qry.= " ORDER BY pers_lastname, pers_firstname";

        $query = '';
        $start = false;

        // *** Search marriage place ***
        if ($data["select_marriage"] == '1') {
            $query = "(SELECT SQL_CALC_FOUND_ROWS *, fam_marr_place as place_order FROM humo_families";
            if ($data["place_name"]) {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND fam_marr_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
            } else {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND fam_marr_place LIKE '_%'";
            }
            $query .= ')';
            $start = true;
        }

        // *** Search marriage church place ***
        if ($data["select_marriage_religious"] == '1') {
            if ($start == true) {
                $query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }
            $query .= "(SELECT " . $calc . "*, fam_marr_church_place as place_order FROM humo_families";
            if ($data["place_name"]) {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND fam_marr_church_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
            } else {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND fam_marr_church_place LIKE '_%'";
            }
            $query .= ')';
            $start = true;
        }

        // *** Search marriage notice place ***
        if ($data["select_marriage_notice"] == '1') {
            if ($start == true) {
                $query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }
            $query .= "(SELECT " . $calc . "*, fam_marr_notice_place as place_order FROM humo_families";
            if ($data["place_name"]) {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND fam_marr_notice_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
            } else {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND fam_marr_notice_place LIKE '_%'";
            }
            $query .= ')';
            $start = true;
        }

        // *** Search marriage notice place ***
        if ($data["select_marriage_notice_religious"] == '1') {
            if ($start == true) {
                $query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }
            $query .= "(SELECT " . $calc . "*, fam_marr_church_notice_place as place_order FROM humo_families";
            if ($data["place_name"]) {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND fam_marr_church_notice_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
            } else {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND fam_marr_church_notice_place LIKE '_%'";
            }
            $query .= ')';
            $start = true;
        }

        // *** Order by place and marriage date ***
        $query .= ' ORDER BY place_order, substring(fam_marr_date,-4)';
        return $query;
    }
}

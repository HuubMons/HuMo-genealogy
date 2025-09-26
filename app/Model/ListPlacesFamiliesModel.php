<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use Genealogy\Include\BuildCondition;

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

    public function build_query(): string
    {
        $buildCondition = new BuildCondition();

        $query = '';

        $data = $this->getSelection();

        //*** Places index ***
        // *** EXAMPLE of a UNION querie ***
        //$qry = "(SELECT * FROM humo1_person ".$query.') ';
        //$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
        //$qry.= " ORDER BY pers_lastname, pers_firstname";

        $query = '';
        $start = false;

        $base_query = "
            marr_notice_location.location_location AS fam_marr_notice_place,
            marr_notice.event_date AS fam_marr_notice_date,

            marriage_location.location_location AS fam_marr_place,
            marriage.event_date AS fam_marr_date,

            marr_church_notice_location.location_location AS fam_marr_church_notice_place,
            marr_church_notice.event_date AS fam_marr_church_notice_date,

            marr_church_location.location_location AS fam_marr_church_place,
            marr_church.event_date AS fam_marr_church_date

            FROM humo_families

            LEFT JOIN humo_events AS marr_notice
            ON humo_families.fam_id = marr_notice.event_relation_id AND marr_notice.event_kind = 'marriage_notice'
            LEFT JOIN humo_location AS marr_notice_location
            ON marr_notice.event_place_id = marr_notice_location.location_id

            LEFT JOIN humo_events AS marriage
            ON humo_families.fam_id = marriage.event_relation_id AND marriage.event_kind = 'marriage'
            LEFT JOIN humo_location AS marriage_location
            ON marriage.event_place_id = marriage_location.location_id

            LEFT JOIN humo_events AS marr_church_notice
            ON humo_families.fam_id = marr_church_notice.event_relation_id AND marr_church_notice.event_kind = 'marr_church_notice'
            LEFT JOIN humo_location AS marr_church_notice_location
            ON marr_church_notice.event_place_id = marr_church_notice_location.location_id

            LEFT JOIN humo_events AS marr_church
            ON humo_families.fam_id = marr_church.event_relation_id AND marr_church.event_kind = 'marr_church'
            LEFT JOIN humo_location AS marr_church_location
            ON marr_church.event_place_id = marr_church_location.location_id
        ";

        // *** Search marriage place ***
        if ($data["select_marriage"] == '1') {
            //$query = "(SELECT SQL_CALC_FOUND_ROWS *, fam_marr_place as place_order FROM humo_families";

            $query = "(SELECT SQL_CALC_FOUND_ROWS *, marriage_location.location_location as place_order,";
            $query .= $base_query;

            if ($data["place_name"]) {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND marriage_location.location_location " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND marriage_location.location_location LIKE '_%'";
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
            //$query .= "(SELECT " . $calc . "*, fam_marr_church_place as place_order FROM humo_families";

            $query .= "(SELECT " . $calc . "*, marr_church_location.location_location as place_order,";
            $query .= $base_query;

            if ($data["place_name"]) {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND marr_church_location.location_location " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND marr_church_location.location_location LIKE '_%'";
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

            //$query .= "(SELECT " . $calc . "*, fam_marr_notice_place as place_order FROM humo_families";

            $query .= "(SELECT " . $calc . "*, marr_notice_location.location_location as place_order,";
            $query .= $base_query;

            if ($data["place_name"]) {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND marr_notice_location.location_location " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND marr_notice_location.location_location LIKE '_%'";
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

            //$query .= "(SELECT " . $calc . "*, fam_marr_church_notice_place as place_order FROM humo_families";

            $query .= "(SELECT " . $calc . "*, marr_church_notice_location.location_location as place_order,";
            $query .= $base_query;

            if ($data["place_name"]) {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND marr_church_notice_location.location_location " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                $query .= " WHERE fam_tree_id='" . $this->tree_id . "' AND marr_church_notice_location.location_location LIKE '_%'";
            }
            $query .= ')';
            $start = true;
        }

        // *** Order by place and marriage date ***
        //$query .= ' ORDER BY place_order, substring(fam_marr_date,-4)';
        $query .= ' ORDER BY place_order';
        return $query;
    }
}

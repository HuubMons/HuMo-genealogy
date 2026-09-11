<?php

/*
 * Aug. 2026. Improved performance by using STRAIGHT_JOIN and FORCE INDEX to optimize the query execution plan.
 * This change aims to reduce the time taken for complex queries involving multiple joins and conditions,
 * especially when filtering by place names in marriage-related events.
 * The use of STRAIGHT_JOIN ensures that the join order is preserved as specified,
 * while FORCE INDEX directs the database to use specific indexes, improving retrieval speed for large datasets.
 */

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

        $start = false;

        /*
         * Search marriage place
         */
        if ($data["select_marriage"] == '1') {

            $query = "(SELECT SQL_CALC_FOUND_ROWS *,
            marriage_location.location_location AS place_order,

            marr_notice_location.location_location AS fam_marr_notice_place,
            marr_notice.event_date AS fam_marr_notice_date,

            marriage_location.location_location AS fam_marr_place,
            marriage.event_date AS fam_marr_date,

            marr_church_notice_location.location_location AS fam_marr_church_notice_place,
            marr_church_notice.event_date AS fam_marr_church_notice_date,

            marr_church_location.location_location AS fam_marr_church_place,
            marr_church.event_date AS fam_marr_church_date,

            man_rel.person_id AS partner1_id,
            man_rel.person_gedcomnumber AS partner1_gedcomnumber,
            woman_rel.person_id AS partner2_id,
            woman_rel.person_gedcomnumber AS partner2_gedcomnumber

            FROM humo_events AS marriage FORCE INDEX (idx_kind_relation_place)

            STRAIGHT_JOIN humo_location AS marriage_location FORCE INDEX (PRIMARY)
                ON marriage.place_id = marriage_location.location_id

            STRAIGHT_JOIN humo_families
                ON humo_families.fam_id = marriage.relation_id
                AND humo_families.fam_tree_id = '" . $this->tree_id . "'

            LEFT JOIN humo_events AS marr_notice
                ON humo_families.fam_id = marr_notice.relation_id
                AND marr_notice.event_kind = 'marriage_notice'

            LEFT JOIN humo_location AS marr_notice_location FORCE INDEX (PRIMARY)
                ON marr_notice.place_id = marr_notice_location.location_id

            LEFT JOIN humo_events AS marr_church_notice
                ON humo_families.fam_id = marr_church_notice.relation_id
                AND marr_church_notice.event_kind = 'marr_church_notice'

            LEFT JOIN humo_location AS marr_church_notice_location FORCE INDEX (PRIMARY)
                ON marr_church_notice.place_id = marr_church_notice_location.location_id

            LEFT JOIN humo_events AS marr_church
                ON humo_families.fam_id = marr_church.relation_id
                AND marr_church.event_kind = 'marr_church'

            LEFT JOIN humo_location AS marr_church_location FORCE INDEX (PRIMARY)
                ON marr_church.place_id = marr_church_location.location_id

            LEFT JOIN humo_relations_persons AS man_rel
                ON man_rel.relation_id = humo_families.fam_id
                AND man_rel.relation_type = 'partner'
                AND man_rel.partner_order = 1

            LEFT JOIN humo_relations_persons AS woman_rel
                ON woman_rel.relation_id = humo_families.fam_id
                AND woman_rel.relation_type = 'partner'
                AND woman_rel.partner_order = 2

            WHERE marriage.event_kind = 'marriage'";

            if ($data["place_name"]) {
                $query .= " AND marriage_location.location_location " .
                    $buildCondition->build(
                        $data["place_name"],
                        $data["part_place_name"]
                    );
            } else {
                $query .= " AND marriage_location.location_location LIKE '_%'";
            }

            $query .= ')';
            $start = true;
        }

        /*
         * Search religious marriage place
         */
        if ($data["select_marriage_religious"] == '1') {

            if ($start) {
                $query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }

            $query .= "(SELECT " . $calc . "*,
            marr_church_location.location_location AS place_order,

            marr_notice_location.location_location AS fam_marr_notice_place,
            marr_notice.event_date AS fam_marr_notice_date,

            marriage_location.location_location AS fam_marr_place,
            marriage.event_date AS fam_marr_date,

            marr_church_notice_location.location_location AS fam_marr_church_notice_place,
            marr_church_notice.event_date AS fam_marr_church_notice_date,

            marr_church_location.location_location AS fam_marr_church_place,
            marr_church.event_date AS fam_marr_church_date,

            man_rel.person_id AS partner1_id,
            man_rel.person_gedcomnumber AS partner1_gedcomnumber,
            woman_rel.person_id AS partner2_id,
            woman_rel.person_gedcomnumber AS partner2_gedcomnumber

            FROM humo_events AS marr_church FORCE INDEX (idx_kind_relation_place)

            STRAIGHT_JOIN humo_location AS marr_church_location FORCE INDEX (PRIMARY)
                ON marr_church.place_id = marr_church_location.location_id

            STRAIGHT_JOIN humo_families
                ON humo_families.fam_id = marr_church.relation_id
                AND humo_families.fam_tree_id = '" . $this->tree_id . "'

            LEFT JOIN humo_events AS marr_notice
                ON humo_families.fam_id = marr_notice.relation_id
                AND marr_notice.event_kind = 'marriage_notice'

            LEFT JOIN humo_location AS marr_notice_location FORCE INDEX (PRIMARY)
                ON marr_notice.place_id = marr_notice_location.location_id

            LEFT JOIN humo_events AS marriage
                ON humo_families.fam_id = marriage.relation_id
                AND marriage.event_kind = 'marriage'

            LEFT JOIN humo_location AS marriage_location FORCE INDEX (PRIMARY)
                ON marriage.place_id = marriage_location.location_id

            LEFT JOIN humo_events AS marr_church_notice
                ON humo_families.fam_id = marr_church_notice.relation_id
                AND marr_church_notice.event_kind = 'marr_church_notice'

            LEFT JOIN humo_location AS marr_church_notice_location FORCE INDEX (PRIMARY)
                ON marr_church_notice.place_id = marr_church_notice_location.location_id

            LEFT JOIN humo_relations_persons AS man_rel
                ON man_rel.relation_id = humo_families.fam_id
                AND man_rel.relation_type = 'partner'
                AND man_rel.partner_order = 1

            LEFT JOIN humo_relations_persons AS woman_rel
                ON woman_rel.relation_id = humo_families.fam_id
                AND woman_rel.relation_type = 'partner'
                AND woman_rel.partner_order = 2

            WHERE marr_church.event_kind = 'marr_church'";

            if ($data["place_name"]) {
                $query .= " AND marr_church_location.location_location " .
                    $buildCondition->build(
                        $data["place_name"],
                        $data["part_place_name"]
                    );
            } else {
                $query .= " AND marr_church_location.location_location LIKE '_%'";
            }

            $query .= ')';
            $start = true;
        }

        /*
         * Search marriage notice place
         */
        if ($data["select_marriage_notice"] == '1') {

            if ($start) {
                $query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }

            $query .= "(SELECT " . $calc . "*,
            marr_notice_location.location_location AS place_order,

            marr_notice_location.location_location AS fam_marr_notice_place,
            marr_notice.event_date AS fam_marr_notice_date,

            marriage_location.location_location AS fam_marr_place,
            marriage.event_date AS fam_marr_date,

            marr_church_notice_location.location_location AS fam_marr_church_notice_place,
            marr_church_notice.event_date AS fam_marr_church_notice_date,

            marr_church_location.location_location AS fam_marr_church_place,
            marr_church.event_date AS fam_marr_church_date,

            man_rel.person_id AS partner1_id,
            man_rel.person_gedcomnumber AS partner1_gedcomnumber,
            woman_rel.person_id AS partner2_id,
            woman_rel.person_gedcomnumber AS partner2_gedcomnumber

            FROM humo_events AS marr_notice FORCE INDEX (idx_kind_relation_place)

            STRAIGHT_JOIN humo_location AS marr_notice_location FORCE INDEX (PRIMARY)
                ON marr_notice.place_id = marr_notice_location.location_id

            STRAIGHT_JOIN humo_families
                ON humo_families.fam_id = marr_notice.relation_id
                AND humo_families.fam_tree_id = '" . $this->tree_id . "'

            LEFT JOIN humo_events AS marriage
                ON humo_families.fam_id = marriage.relation_id
                AND marriage.event_kind = 'marriage'

            LEFT JOIN humo_location AS marriage_location FORCE INDEX (PRIMARY)
                ON marriage.place_id = marriage_location.location_id

            LEFT JOIN humo_events AS marr_church_notice
                ON humo_families.fam_id = marr_church_notice.relation_id
                AND marr_church_notice.event_kind = 'marr_church_notice'

            LEFT JOIN humo_location AS marr_church_notice_location FORCE INDEX (PRIMARY)
                ON marr_church_notice.place_id = marr_church_notice_location.location_id

            LEFT JOIN humo_events AS marr_church
                ON humo_families.fam_id = marr_church.relation_id
                AND marr_church.event_kind = 'marr_church'

            LEFT JOIN humo_location AS marr_church_location FORCE INDEX (PRIMARY)
                ON marr_church.place_id = marr_church_location.location_id

            LEFT JOIN humo_relations_persons AS man_rel
                ON man_rel.relation_id = humo_families.fam_id
                AND man_rel.relation_type = 'partner'
                AND man_rel.partner_order = 1

            LEFT JOIN humo_relations_persons AS woman_rel
                ON woman_rel.relation_id = humo_families.fam_id
                AND woman_rel.relation_type = 'partner'
                AND woman_rel.partner_order = 2

            WHERE marr_notice.event_kind = 'marriage_notice'";

            if ($data["place_name"]) {
                $query .= " AND marr_notice_location.location_location " .
                    $buildCondition->build(
                        $data["place_name"],
                        $data["part_place_name"]
                    );
            } else {
                $query .= " AND marr_notice_location.location_location LIKE '_%'";
            }

            $query .= ')';
            $start = true;
        }

        /*
         * Search religious marriage notice place
         */
        if ($data["select_marriage_notice_religious"] == '1') {

            if ($start) {
                $query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }

            $query .= "(SELECT " . $calc . "*,
            marr_church_notice_location.location_location AS place_order,

            marr_notice_location.location_location AS fam_marr_notice_place,
            marr_notice.event_date AS fam_marr_notice_date,

            marriage_location.location_location AS fam_marr_place,
            marriage.event_date AS fam_marr_date,

            marr_church_notice_location.location_location AS fam_marr_church_notice_place,
            marr_church_notice.event_date AS fam_marr_church_notice_date,

            marr_church_location.location_location AS fam_marr_church_place,
            marr_church.event_date AS fam_marr_church_date,

            man_rel.person_id AS partner1_id,
            man_rel.person_gedcomnumber AS partner1_gedcomnumber,
            woman_rel.person_id AS partner2_id,
            woman_rel.person_gedcomnumber AS partner2_gedcomnumber

            FROM humo_events AS marr_church_notice FORCE INDEX (idx_kind_relation_place)

            STRAIGHT_JOIN humo_location AS marr_church_notice_location FORCE INDEX (PRIMARY)
                ON marr_church_notice.place_id = marr_church_notice_location.location_id

            STRAIGHT_JOIN humo_families
                ON humo_families.fam_id = marr_church_notice.relation_id
                AND humo_families.fam_tree_id = '" . $this->tree_id . "'

            LEFT JOIN humo_events AS marr_notice
                ON humo_families.fam_id = marr_notice.relation_id
                AND marr_notice.event_kind = 'marriage_notice'

            LEFT JOIN humo_location AS marr_notice_location FORCE INDEX (PRIMARY)
                ON marr_notice.place_id = marr_notice_location.location_id

            LEFT JOIN humo_events AS marriage
                ON humo_families.fam_id = marriage.relation_id
                AND marriage.event_kind = 'marriage'

            LEFT JOIN humo_location AS marriage_location FORCE INDEX (PRIMARY)
                ON marriage.place_id = marriage_location.location_id

            LEFT JOIN humo_events AS marr_church
                ON humo_families.fam_id = marr_church.relation_id
                AND marr_church.event_kind = 'marr_church'

            LEFT JOIN humo_location AS marr_church_location FORCE INDEX (PRIMARY)
                ON marr_church.place_id = marr_church_location.location_id

            LEFT JOIN humo_relations_persons AS man_rel
                ON man_rel.relation_id = humo_families.fam_id
                AND man_rel.relation_type = 'partner'
                AND man_rel.partner_order = 1

            LEFT JOIN humo_relations_persons AS woman_rel
                ON woman_rel.relation_id = humo_families.fam_id
                AND woman_rel.relation_type = 'partner'
                AND woman_rel.partner_order = 2

            WHERE marr_church_notice.event_kind = 'marr_church_notice'";

            if ($data["place_name"]) {
                $query .= " AND marr_church_notice_location.location_location " .
                    $buildCondition->build(
                        $data["place_name"],
                        $data["part_place_name"]
                    );
            } else {
                $query .= " AND marr_church_notice_location.location_location LIKE '_%'";
            }

            $query .= ')';
            $start = true;
        }

        $query .= ' ORDER BY place_order';

        return $query;
    }
}

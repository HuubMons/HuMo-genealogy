<?php

/**
 * Use Ajax to order children and media.
 */

session_start();

//if (!defined('ADMIN_PAGE')){ exit; }

if (isset($_SESSION['admin_tree_id'])) {
    $ADMIN = TRUE; // *** Override "no database" message for admin ***
    include_once(__DIR__ . "/../../include/db_login.php"); // *** Database login ***
    include_once(__DIR__ . "/../../include/safe.php");

    $gedcom_date = strtoupper(date("d M Y"));
    $gedcom_time = date("H:i:s");
    $drag_kind = safe_text_db($_GET["drag_kind"]);

    if ($drag_kind == "children") {
        $chldstring = safe_text_db($_GET['chldstring']);
        $sql = "UPDATE humo_families SET
            fam_children='" . $chldstring . "',
            fam_changed_date='" . $gedcom_date . "',
            fam_changed_time='" . $gedcom_time . "'
            WHERE fam_id='" . safe_text_db($_GET["family_id"]) . "'";
        $result = $dbh->query($sql);
    }

    if ($drag_kind == "media") {
        $mediastring = safe_text_db($_GET['mediastring']);
        $media_arr = explode(";", $mediastring);
        for ($x = 0; $x < count($media_arr); $x++) {
            $sql = "UPDATE humo_events SET
                event_order='" . ($x + 1) . "',
                event_changed_date='" . $gedcom_date . "',
                event_changed_time='" . $gedcom_time . "'
                WHERE event_id='" . $media_arr[$x] . "'";
            $result = $dbh->query($sql);
        }
    }

    if ($drag_kind == "sources") {
        $mediastring = safe_text_db($_GET['sourcestring']);
        $media_arr = explode(";", $mediastring);
        for ($x = 0; $x < count($media_arr); $x++) {
            $sql = "UPDATE humo_connections SET
                connect_order='" . ($x + 1) . "',
                connect_changed_date='" . $gedcom_date . "',
                connect_changed_time='" . $gedcom_time . "'
                WHERE connect_id='" . $media_arr[$x] . "'";
            $result = $dbh->query($sql);
        }
    }
}

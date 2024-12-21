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

    $drag_kind = safe_text_db($_GET["drag_kind"]);

    if ($drag_kind == "children" && is_numeric($_GET["family_id"])) {
        $chldstring = safe_text_db($_GET['chldstring']);
        $result = $dbh->query("UPDATE humo_families SET fam_children='" . $chldstring . "' WHERE fam_id='" . $_GET["family_id"] . "'");
    }

    if ($drag_kind == "media") {
        $mediastring = safe_text_db($_GET['mediastring']);
        $media_arr = explode(";", $mediastring);
        $counter = count($media_arr);
        for ($x = 0; $x < $counter; $x++) {
            if (is_numeric($media_arr[$x])) {
                $result = $dbh->query("UPDATE humo_events SET event_order='" . ($x + 1) . "' WHERE event_id='" . $media_arr[$x] . "'");
            }
        }
    }

    if ($drag_kind == "sources") {
        $mediastring = safe_text_db($_GET['sourcestring']);
        $media_arr = explode(";", $mediastring);
        $counter = count($media_arr);
        for ($x = 0; $x < $counter; $x++) {
            if (is_numeric($media_arr[$x])) {
                $result = $dbh->query("UPDATE humo_connections SET connect_order='" . ($x + 1) . "' WHERE connect_id='" . $media_arr[$x] . "'");
            }
        }
    }

    if ($drag_kind == "trees") {
        $mediastring = safe_text_db($_GET['order']);
        $media_arr = explode(";", $mediastring);
        $counter = count($media_arr);
        for ($x = 0; $x < $counter; $x++) {
            if (is_numeric($media_arr[$x])) {
                $result = $dbh->query("UPDATE humo_trees SET tree_order='" . ($x + 1) . "' WHERE tree_id='" . $media_arr[$x] . "'");
            }
        }
    }

    if ($drag_kind == "homepage_modules") {
        $mediastring = safe_text_db($_GET['order']);
        $media_arr = explode(";", $mediastring);
        $counter = count($media_arr);
        for ($x = 0; $x < $counter; $x++) {
            if (is_numeric($media_arr[$x])) {
                $result = $dbh->query("UPDATE humo_settings SET setting_order='" . ($x + 1) . "' WHERE setting_id='" . $media_arr[$x] . "'");
            }
        }
    }
}

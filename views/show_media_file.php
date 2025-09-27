<?php
// *** Show media file using secured folder ***
// this checks if this is special url query for giving the file - it gives the file if user is authorized to get it
if (isset($_GET['page']) && $_GET['page'] == 'show_media_file' && isset($_GET['media_dir']) && isset($_GET['media_filename'])) {
    $personPrivacy = new \Genealogy\Include\PersonPrivacy();

    if (isset($_GET['media_filename']) && $_GET['media_filename']) {
        $media_filename = $_GET['media_filename'];
    }
    if (isset($_GET['media_dir']) && $_GET['media_dir']) {
        $media_dir = $_GET['media_dir'];
    }
    // we must check if file has category directory prefix from existing prefixes so we must preserve directory and concatenate with original filename (removing thumb only)
    // does photocat_prefix has any dependance to tree_id??
    $datasql = $dbh->query("SELECT * FROM humo_photocat WHERE photocat_prefix!='none'");
    $rowCount = $datasql->rowCount();
    $prefixes = [];
    for ($i = 0; $i < $rowCount; $i++) {
        $photocat_db = $datasql->fetch(PDO::FETCH_OBJ);
        $photocat_prefix = $photocat_db->photocat_prefix;
        if (!in_array($photocat_prefix, $prefixes)) $prefixes[] = $photocat_prefix;
    }

    $matching_prefix = '';

    foreach ($prefixes as $key => $prefix) {
        if (strpos($media_filename, $prefix . DIRECTORY_SEPARATOR) === 0) {
            $prefix_slash = $prefix . DIRECTORY_SEPARATOR;
            // we make the filename without dir origin filename prefix and slash
            $media_filename_with_prefix_dir =  substr($media_filename, strlen($prefix_slash));
            $matching_prefix = $prefix_slash;
        }
    }

    if (isset($media_filename_with_prefix_dir)) {
        $media_filename_for_thumb_check = $media_filename_with_prefix_dir;
    } else {
        $media_filename_for_thumb_check = $media_filename;
    }
    // we are checking if this is thumb - if it is we need to check privacy for origin file, not thumb
    // exception will be situation where user puts jpg file with "thumb_" begining in it's name - now this exception is not solved
    if (strpos($media_filename_for_thumb_check, 'thumb_') === 0) {
        // we make the thumbname origin filename
        $original_media_filename = substr($media_filename_for_thumb_check, 6, -4);
        $original_media_filename = $matching_prefix . $original_media_filename;
    } else {
        $original_media_filename = $media_filename;
    }

    $qry = "SELECT * FROM humo_events
        WHERE event_tree_id='" . $tree_id . "' 
        AND (event_connect_kind='person' OR event_connect_kind='family') 
        AND event_connect_id NOT LIKE '' AND event_event='" . $original_media_filename . "'";
    $media_qry = $dbh->query($qry);
    $media_qryDb = $media_qry->fetch(PDO::FETCH_OBJ);

    $file_allowed = false;

    if ($media_qryDb && $media_qryDb->event_connect_kind === 'person') {
        $personmnDb = $db_functions->get_person($media_qryDb->event_connect_id);
        $man_privacy = $personPrivacy->get_privacy($personmnDb);
        if ($personmnDb && !$man_privacy) {
            $file_allowed = true;
        } else {
            $file_allowed = false;
        }
    } elseif ($media_qryDb && $media_qryDb->event_connect_kind === 'family') {
        $qry2 = "SELECT * FROM humo_families WHERE fam_gedcomnumber='" . $media_qryDb->event_connect_id . "'";
        $family_qry = $dbh->query($qry2);
        $family_qryDb2 = $family_qry->fetch(PDO::FETCH_OBJ);

        $personmnDb = $db_functions->get_person($family_qryDb2->fam_man);
        $man_privacy = $personPrivacy->get_privacy($personmnDb);

        $personwmnDb = $db_functions->get_person($family_qryDb2->fam_woman);
        $woman_privacy = $personPrivacy->get_privacy($personwmnDb);

        // *** Only use this picture if both man and woman have disabled privacy options ***
        if ($man_privacy == '' && $woman_privacy == '') {
            $file_allowed = true;
        } else {
            $file_allowed = false;
        }
    } elseif (isset($_SESSION['group_id_admin'])) {
        $groepsql = $dbh->query("SELECT * FROM humo_groups WHERE group_id='" . $_SESSION['group_id_admin'] . "'");
        $groepDb = $groepsql->fetch(PDO::FETCH_OBJ);
        if ($groepDb->group_admin === 'j') {
            $file_allowed = true;
        } else {
            $file_allowed = false;
        }
    }

    // In this if we make exception for favicon.ico, logo.png and logo.jpg which must be served always
    if ($file_allowed || ($media_filename == 'logo.png' || $media_filename == 'logo.jpg' || $media_filename == 'favicon.ico')) {
        if (file_exists($media_dir . $media_filename)) {
            // We check what content type is file to put header
            $content_type_header = mime_content_type($media_dir . $media_filename);
            header('Content-Type: ' . $content_type_header);
            header('Content-Disposition: inline; filename="' . $media_filename . '"');
            header('Cache-Control: private, max-age=3600');
            header('Pragma:');
            header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + (3600))); // 3600s cache
            readfile($media_dir . $media_filename);
        } else {
            echo 'file not exists';
        }
    } else {
        echo 'You are non authorized to get this file';
    }
}

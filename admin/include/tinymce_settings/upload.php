<?php

/*******************************************************
 * Only these origins will be allowed to upload images *
 ******************************************************/
//$accepted_origins = array("http://localhost", "http://192.168.1.1", "http://example.com");

session_start();

// *** Check if admin is logged in (session) ***
if (!isset($_SESSION["user_name_admin"])) {
    exit;
}

// *** Get path to pictures ***
//TODO use __DIR__
include_once('../../../include/db_login.php'); // *** Database login ***
include_once('../../../include/settings_global.php');

/*********************************************
 * Change this line to set the upload folder *
 *********************************************/
//$imageFolder = "images/";
$imageFolder = $humo_option["cms_images_path"] . '/';
if (substr($imageFolder, 0, 1) == '|') $imageFolder = '../../../media/cms/';

reset($_FILES);
$temp = current($_FILES);
if (is_uploaded_file($temp['tmp_name'])) {
    /*
    if (isset($_SERVER['HTTP_ORIGIN'])) {
        // same-origin requests won't set an origin. If the origin is set, it must be valid.
        if (in_array($_SERVER['HTTP_ORIGIN'], $accepted_origins)) {
            header('Access-Control-Allow-Origin: ' . $_SERVER['HTTP_ORIGIN']);
        } else {
            header("HTTP/1.1 403 Origin Denied");
            return;
        }
    }
    */

    /*
      If your script needs to receive cookies, set images_upload_credentials : true in
      the configuration and enable the following two headers.
    */
    // header('Access-Control-Allow-Credentials: true');
    // header('P3P: CP="There is no P3P policy."');

    // Sanitize input
    if (preg_match("/([^\w\s\d\-_~,;:\[\]\(\).])|([\.]{2,})/", $temp['name'])) {
        header("HTTP/1.1 400 Invalid file name.");
        return;
    }

    // Verify extension
    if (!in_array(strtolower(pathinfo($temp['name'], PATHINFO_EXTENSION)), array("gif", "jpg", "png"))) {
        header("HTTP/1.1 400 Invalid extension.");
        return;
    }

    // Accept upload if there was no origin, or if it is an accepted origin
    $filetowrite = $imageFolder . $temp['name'];
    move_uploaded_file($temp['tmp_name'], $filetowrite);

    // Respond to the successful upload with JSON.
    // Use a location key to specify the path to the saved image resource.
    // { location : '/your/uploaded/image/file'}
    //echo json_encode(array('location' => $filetowrite));


    // Determine the base URL 
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on' ? "https://" : "http://";
    $baseurl = $protocol . $_SERVER["HTTP_HOST"] . rtrim(dirname($_SERVER['REQUEST_URI']), "/") . "/";

    // Change:
    // http://localhost/humo-genealogy/admin/include/tinymce/../../../media/cms/Mastodon.png
    // Into:
    // http://localhost/humo-genealogy/media/cms/Mastodon.png
    $return_url = $baseurl . $filetowrite;
    $return_url = str_replace('/admin/include/tinymce/../../..', '', $return_url);

    //echo json_encode(array('location' => $baseurl . $filetowrite));
    echo json_encode(array('location' => $return_url));
} else {
    // Notify editor that the upload failed
    header("HTTP/1.1 500 Server Error");
}

<?php
session_start();

// *** Seperate file for PDF scripts. Copy of layout.php ***

// TODO check if PDF exports can be generated using index.php, then use this file as PDF view file.

// TODO CREATE GENERAL AUTOLOAD FUNCTION
// COPY FROM INDEX.PHP (except extra ../)
/**
 *  Dec. 2024: Added autoload.
 *  Name of class = SomethingClass
 *  Name of script: somethingClass.php ***
 */
// TODO add autoload in gendex.php, sitemap.php, editor_ajax.php, namesearch.php, layout_pdf.php.
function custom_autoload($class_name)
{
    // Examples of autoload files:
    // app/model/ All scripts are autoloading.
    // app/model/adresModel.php

    // controller/ All scripts are autoloading.
    // controller/addressController.php

    // include/dbFunctions.php
    // include/marriage_cls
    // include/personCls.php
    // include/calculateDates.php
    // include/processLinks.php
    // include/validateDate.php

    // languages/languageCls.php

    $include = array(
        'CalculateDates',
        'DbFunctions',
        'GeneralSettings',
        'MarriageCls',
        'PersonCls',
        'ProcessLinks',
        'ValidateDate',
        'Config'
    );

    if ($class_name == 'LanguageCls') {
        require __DIR__ . '/../languages/languageCls.php';
    } elseif (substr($class_name, -10) == 'Controller') {
        require __DIR__ . '/../app/controller/' . lcfirst($class_name) . '.php';
    } elseif (substr($class_name, -5) == 'Model') {
        require __DIR__ . '/../app/model/' . lcfirst($class_name) . '.php';
    } elseif (in_array($class_name, $include)) {
        require __DIR__ . '/../include/' . lcfirst($class_name) . '.php';
    }
}
spl_autoload_register('custom_autoload');

include_once(__DIR__ . "/../include/db_login.php"); //Inloggen database.
include_once(__DIR__ . '/../include/show_tree_text.php');
$db_functions = new DbFunctions($dbh);

include_once(__DIR__ . "/../include/safe.php");

$GeneralSettings = new GeneralSettings();
$user = $GeneralSettings->get_user_settings($dbh);
$humo_option = $GeneralSettings->get_humo_option($dbh);

include_once(__DIR__ . "/../include/get_visitor_ip.php");
$visitor_ip = visitorIP();

// *** Added in nov 2023 (used in outline_report_pdf.php) ***
$tree_id = 0;
if (isset($_POST['tree_id']) && is_numeric($_POST['tree_id'])) {
    $tree_id = $_POST['tree_id'];
}

// TODO this is a copy from INDEX.PHP
$config = array(
    "dbh" => $dbh,
    "db_functions" => $db_functions,
    "tree_id" => $tree_id,
    "user" => $user,
    "humo_option" => $humo_option
);

// *** Debug HuMo-genealogy front pages ***
if ($humo_option["debug_front_pages"] == 'y') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

// *** Check if visitor is allowed access to website ***
if (!$db_functions->check_visitor($visitor_ip, 'partial')) {
    echo 'Access to website is blocked.';
    exit;
}

// *** Set timezone ***
include_once(__DIR__ . "/../include/timezone.php"); // set timezone 
timezone();
// *** TIMEZONE TEST ***
//echo date("Y-m-d H:i");

// *** Check if visitor is a bot or crawler ***
$bot_visit = preg_match('/bot|spider|crawler|curl|Yahoo|Google|^$/i', $_SERVER['HTTP_USER_AGENT']);
// *** Line for bot test! ***
//$bot_visit=true;

// *** Language processing after header("..") lines. *** 
include_once(__DIR__ . "/../languages/language.php");

// *** Process LTR and RTL variables ***
$dirmark1 = "&#x200E;";  //ltr marker
$dirmark2 = "&#x200F;";  //rtl marker
$rtlmarker = "ltr";
$alignmarker = "left";
// *** Switch direction markers if language is RTL ***
if ($language["dir"] == "rtl") {
    $dirmark1 = "&#x200F;";  //rtl marker
    $dirmark2 = "&#x200E;";  //ltr marker
    $rtlmarker = "rtl";
    $alignmarker = "right";
}
if (isset($screen_mode) && $screen_mode == "PDF") {
    $dirmark1 = '';
    $dirmark2 = '';
}

// *** Default values
$page = 'index';
$index['main_admin'] = $humo_option["database_name"]; // TODO check variable. Not used here?
$tmp_path = '';


// *** Generate BASE HREF for use in url_rewrite ***
// SERVER_NAME   127.0.0.1
//     PHP_SELF: /url_test/index/1abcd2345/
// OF: PHP_SELF: /url_test/index.php
// REQUEST_URI: /url_test/index/1abcd2345/
// REQUEST_URI: /url_test/index.php?variabele=1
$base_href = ''; // TODO: $base_href probably not needed for PDF.
if ($humo_option["url_rewrite"] == "j" && $tmp_path) {
    // *** url_rewrite ***
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] != 'off') {
        $uri_path = 'https://' . $_SERVER['SERVER_NAME'] . $tmp_path;
    } else {
        $uri_path = 'http://' . $_SERVER['SERVER_NAME'] . $tmp_path;
    }
    $base_href = $uri_path;
} else {
    // *** Use standard uri ***
    $position = strrpos($_SERVER['PHP_SELF'], '/');
    $uri_path = substr($_SERVER['PHP_SELF'], 0, $position) . '/';
}

// *** To be used to show links in several pages ***
//include_once(__DIR__ . '/../include/processLinks.php');
$link_cls = new ProcessLinks($uri_path);

// *** For PDF reports: remove html tags en decode ' characters ***
function pdf_convert($text)
{
    //$text=@iconv("UTF-8","cp1252//IGNORE//TRANSLIT",$text);	// Only needed if FPDF is used. We now use TFPDF.
    return html_entity_decode(strip_tags($text), ENT_QUOTES);
}

// *** Set default PDF font ***
$pdf_font = 'DejaVu';

// *** june 2022: FPDF supports romanian and greek characters ***
//define('FPDF_FONTPATH',"include/fpdf16//font/unifont");
require(__DIR__ . '/../include/tfpdf/tfpdf.php');
require(__DIR__ . '/../include/tfpdf/tfpdfextend.php');
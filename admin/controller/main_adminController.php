<?php
require_once __DIR__ . "/../models/main_admin.php";

// *** Only logoff admin ***
if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name_admin']);
    unset($_SESSION['user_id_admin']);
    unset($_SESSION['group_id_admin']);
}

$ADMIN = TRUE; // *** Override "no database" message for admin ***
include_once(__DIR__ . "/../../include/db_login.php"); // *** Database login ***

include_once(__DIR__ . "/../../include/safe.php"); // Variables

// *** Function to show family tree texts ***
include_once(__DIR__ . '/../../include/show_tree_text.php');

if (isset($dbh)) {
    $db_functions = new Db_functions_cls($dbh);
}

// *** Added october 2023: generate links to frontsite ***
$link_cls = new Link_cls();

include_once(__DIR__ . "/../../include/get_visitor_ip.php");
$visitor_ip = visitorIP();

class Main_adminController
{
    public function detail()
    {
        $main_adminModel = new Main_adminModel();
        //$main_admin['abc'] = $main_adminModel->get_abc();

        //return $main_admin;
    }
}

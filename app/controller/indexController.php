<?php
require_once  __DIR__ . "/../model/index.php";

if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_group_id']);
    unset($_SESSION['tree_prefix']);
    session_destroy();
}

include_once(__DIR__ . '/../../include/show_tree_text.php');
include_once(__DIR__ . "/../../include/db_functions_cls.php");
include_once(__DIR__ . "/../../include/safe.php");
include_once(__DIR__ . "/../../include/settings_global.php"); // System variables
include_once(__DIR__ . "/../../include/settings_user.php"); // User variables
include_once(__DIR__ . "/../../include/get_visitor_ip.php");

// TODO dec. 2023 now included this in index.php. Check other includes...
include_once(__DIR__ . "/../../include/person_cls.php");

include_once(__DIR__ . "/../../include/timezone.php");
include(__DIR__ . '/../../languages/language_cls.php');


class IndexController
{
    /*
    private $db_functions, $user;

    public function __construct($db_functions, $user)
    {
        $this->db_functions = $db_functions;
        $this->user = $user;
    }
    */

    public function detail($dbh, $humo_option)
    {
        $indexModel = new IndexModel();

        $index['db_functions'] = new db_functions($dbh);

        $index['visitor_ip'] = visitorIP();

        $index['person_cls'] = new person_cls;

        // *** Debug HuMo-genealogy front pages ***
        if ($humo_option["debug_front_pages"] == 'y') {
            error_reporting(E_ALL);
            ini_set('display_errors', 1);
        }

        // *** Check if visitor is allowed access to website ***
        if (!$index['db_functions']->check_visitor($index['visitor_ip'], 'partial')) {
            echo 'Access to website is blocked.';
            exit;
        }

        timezone();
        // *** TIMEZONE TEST ***
        //echo date("Y-m-d H:i");

        // *** Check if visitor is a bot or crawler ***
        $index['bot_visit'] = preg_match('/bot|spider|crawler|curl|Yahoo|Google|^$/i', $_SERVER['HTTP_USER_AGENT']);
        // *** Line for bot test! ***
        //$index['bot_visit'] = true;

        // *** Get ordered list of languages ***
        $language_cls = new Language_cls;
        $index['language_file'] = $language_cls->get_languages();

        $login = $indexModel->login($dbh, $index['db_functions'], $index['visitor_ip']);
        $index = array_merge($index, $login);

        return $index;
    }
}

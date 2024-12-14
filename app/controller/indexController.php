<?php
if (isset($_GET['log_off'])) {
    unset($_SESSION['user_name']);
    unset($_SESSION['user_id']);
    unset($_SESSION['user_group_id']);
    unset($_SESSION['tree_prefix']);
    session_destroy();
}

include_once(__DIR__ . "/../../include/db_login.php"); // Connect to database
include_once(__DIR__ . "/../../include/show_tree_text.php");
include_once(__DIR__ . "/../../include/safe.php");
include_once(__DIR__ . "/../../include/settings_global.php"); // System variables
include_once(__DIR__ . "/../../include/settings_user.php"); // User variables
include_once(__DIR__ . "/../../include/get_visitor_ip.php"); // Statistics and option to block certain IP addresses.

include_once(__DIR__ . "/../../include/timezone.php");
include(__DIR__ . "/../../languages/language_cls.php");

include_once(__DIR__ . '/../routing/router.php'); // Page routing.

class IndexController
{
    public function detail($dbh, $humo_option, $user)
    {
        $indexModel = new IndexModel();

        $index['db_functions'] = new Db_functions_cls($dbh);

        $index['visitor_ip'] = visitorIP();

        $index['person_cls'] = new Person_cls;

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

        // *** Language processing after header("..") lines. *** 
        include_once(__DIR__ . "/../../languages/language.php"); //Taal
        $index['language'] = $language; // $language = array.
        $index['selected_language'] = $selected_language;

        $login = $indexModel->login($dbh, $index['db_functions'], $index['visitor_ip']);
        $index = array_merge($index, $login);

        $ltr_rtl = $indexModel->process_ltr_rtl($index['language']);
        $index = array_merge($index, $ltr_rtl);

        $route = $indexModel->get_route($humo_option);
        $index = array_merge($index, $route);

        $family_tree = $indexModel->get_family_tree($dbh, $index['db_functions'], $user); // Get tree_id, tree_prefix.
        $index = array_merge($index, $family_tree);

        return $index;
    }
}

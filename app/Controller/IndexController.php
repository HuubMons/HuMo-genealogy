<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\IndexModel;
use Genealogy\Include\GetVisitorIP;
use Genealogy\Include\SetTimezone;
use Genealogy\Include\DbFunctions;
use Genealogy\Languages\LanguageCls;

class IndexController
{
    public function detail($dbh, $humo_option, $user): array
    {
        $indexModel = new IndexModel();
        $getVisitorIP = new GetVisitorIP();
        $setTimezone = new SetTimezone();

        // TODO check if these variables can be used in multiple scripts. Only use in index page?
        $index['db_functions'] = new DbFunctions($dbh);
        $index['visitor_ip'] = $getVisitorIP->visitorIP();

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

        $setTimezone->timezone();
        // *** TIMEZONE TEST ***
        //echo date("Y-m-d H:i");

        // *** Check if visitor is a bot or crawler ***
        $index['bot_visit'] = preg_match('/bot|spider|crawler|curl|Yahoo|Google|^$/i', $_SERVER['HTTP_USER_AGENT']);
        // *** Line for bot test! ***
        //$index['bot_visit'] = true;

        // *** Get ordered list of languages ***
        $language_cls = new LanguageCls;
        $index['language_file'] = $language_cls->get_languages();

        // *** Language processing after header("..") lines. *** 
        include_once(__DIR__ . "/../../languages/language.php");
        $index['language'] = $language; // $language = array.
        $index['selected_language'] = $selected_language;

        $login = $indexModel->login($dbh, $index['db_functions'], $index['visitor_ip']);
        $index = array_merge($index, $login);

        $ltr_rtl = $indexModel->process_ltr_rtl($index['language']);
        $index = array_merge($index, $ltr_rtl);

        $route = $indexModel->get_model_route($humo_option);
        $index = array_merge($index, $route);

        // *** Get tree_id, tree_prefix ***
        $family_tree = $indexModel->get_family_tree($dbh, $index['db_functions'], $user);
        $index = array_merge($index, $family_tree);

        /* TODO create $index['screen_mode'] variable
        //$index['page'] == 'ancestor_report_pdf'
        if (substr($index['page'], -3) == 'pdf') {
            $screen_mode = "PDF";

            // *** PDF page, so no direction markers needed ***
            $index['dirmark1'] = '';
            $index['dirmark2'] = '';
        }
        elseif (substr($index['page'], -3) == 'rtf') {
            $screen_mode = "RTF";
        }
        */

        $index['page404'] = $indexModel->get_page404();
        $index['page301'] = $indexModel->get_page301();

        return $index;
    }
}

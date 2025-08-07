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

        // *** Language items ***
        $language_cls = new LanguageCls;
        $index['language_file'] = $language_cls->get_languages();
        $index['selected_language'] = $language_cls->get_selected_language($humo_option);
        $index['language'] = $language_cls->get_language_data($index['selected_language']);
        // *** .mo language text files ***
        include_once(__DIR__ . "/../../languages/gettext.php");
        // *** Load ***
        Load_default_textdomain();

        $login = $indexModel->login($dbh, $index['db_functions'], $index['visitor_ip']);
        $index = array_merge($index, $login);

        $route = $indexModel->get_model_route($humo_option);
        $index = array_merge($index, $route);

        // *** Get tree_id, tree_prefix ***
        $family_tree = $indexModel->get_family_tree($dbh, $index['db_functions'], $user);
        $index = array_merge($index, $family_tree);

        $index['page404'] = $indexModel->get_page404();
        $index['page301'] = $indexModel->get_page301();

        return $index;
    }
}

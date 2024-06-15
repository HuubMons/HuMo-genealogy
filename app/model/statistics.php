<?php
class StatisticsModel
{
    /*
    private $db_functions;

    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }
    */

    public function get_menu_tab()
    {
        // *** Tab menu ***
        $menu_tab = 'stats_tree';
        if (isset($_GET['menu_tab']) && $_GET['menu_tab'] == 'stats_tree') {
            $menu_tab = 'stats_tree';
        }
        if (isset($_GET['menu_tab']) && $_GET['menu_tab'] == 'stats_persons') {
            $menu_tab = 'stats_persons';
        }
        if (isset($_GET['menu_tab']) && $_GET['menu_tab'] == 'stats_surnames') {
            $menu_tab = 'stats_surnames';
        }
        if (isset($_GET['menu_tab']) && $_GET['menu_tab'] == 'stats_firstnames') {
            $menu_tab = 'stats_firstnames';
        }
        return $menu_tab;
    }
}

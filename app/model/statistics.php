<?php
class StatisticsModel
{
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

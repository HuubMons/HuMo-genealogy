<?php
class LogModel
{
    public function get_menu_tab()
    {
        $menu_tab = 'log_users';
        if (isset($_POST['menu_admin'])) {
            $menu_tab = $_POST['menu_admin'];
        }
        if (isset($_GET['menu_admin'])) {
            $menu_tab = $_GET['menu_admin'];
        }
        return $menu_tab;
    }
}

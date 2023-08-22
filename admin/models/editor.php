<?php

/**
 * July 2023: refactor editor to MVC
 */

class Editor
{
    private $Connection;

    public function __construct($Connection)
    {
        $this->Connection = $Connection;

        include_once(__DIR__ . "/../../include/language_date.php");
        include_once(__DIR__ . "/../../include/date_place.php");
        include_once(__DIR__ . "/../../include/language_event.php");
    }

    public function getMenuAdmin()
    {
        $menu_admin = 'person';
        if (isset($_GET["menu_admin"])) {
            $menu_admin = $_GET['menu_admin'];
            $_SESSION['admin_menu_admin'] = $menu_admin;
        }
        if (isset($_SESSION['admin_menu_admin'])) {
            $menu_admin = $_SESSION['admin_menu_admin'];
        }
        return $menu_admin;
    }
}

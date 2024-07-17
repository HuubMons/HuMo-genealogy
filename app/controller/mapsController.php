<?php
require_once  __DIR__ . "/../model/maps.php";

include_once(__DIR__ . "/../../include/person_cls.php");
include_once(__DIR__ . "/../../include/marriage_cls.php");
include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");

class MapsController
{
    /*
    private $db_functions, $user;

    public function __construct($db_functions, $user)
    {
        $this->db_functions = $db_functions;
        $this->user = $user;
    }
    */

    public function detail($humo_option)
    {
        $mapsModel = new MapsModel();

        $select_world_map = $mapsModel->select_world_map($humo_option);

        return array(
            "select_world_map" => $select_world_map
        );
    }
}

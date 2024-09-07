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

    public function detail($humo_option, $dbh, $tree_id)
    {
        $mapsModel = new MapsModel();

        $maps['select_world_map'] = $mapsModel->select_world_map($humo_option);

        // *** Variables: $maps['display_birth'] and $maps['display_death'] ***
        $maps_array = $mapsModel->get_maps_type();
        $maps = array_merge($maps, $maps_array);

        if ($maps['select_world_map'] == 'OpenStreetMap') {
            $maps_locations = $mapsModel->get_locations($dbh, $tree_id, $maps);
            $maps = array_merge($maps, $maps_locations);
        }

        return $maps;
    }
}

<?php
class MapsModel
{
    /*
    private $db_functions;

    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }
    */

    public function select_world_map($humo_option)
    {
        $select = 'Google';
        if (isset($humo_option["use_world_map"]) && $humo_option["use_world_map"] == 'OpenStreetMap') {
            $select = 'OpenStreetMap';
        }
        return $select;
    }
}

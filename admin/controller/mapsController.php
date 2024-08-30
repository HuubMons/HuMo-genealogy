<?php
require_once __DIR__ . "/../models/maps.php";

class MapsController
{
    /*
    private $editor_cls;

    public function __construct()
    {
        $this->editor_cls = new editor_cls;
    }
    */

    public function detail($dbh, $db_functions)
    {
        $mapsModel = new MapsModel();

        // *** July 2024: disabled some variables. Probably not needed anymore. ***
        $maps['use_world_map'] = $mapsModel->get_use_world_map($dbh);
        $maps['google_api1'] = $mapsModel->get_google_api1($dbh);
        //$maps['google_api2'] = $mapsModel->get_google_api2($dbh);
        $maps['geokeo_api'] = $mapsModel->get_geokeo_api($dbh);
        //$maps['default_zoom'] = $mapsModel->get_default_zoom($dbh, $db_functions);
        //$maps['map_type'] = $mapsModel->get_map_type($dbh, $db_functions);
        $maps['slider'] = $mapsModel->get_slider($dbh, $db_functions);

        $maps['geo_tree_id'] = $mapsModel->get_geo_tree_id($dbh);

        return $maps;
    }
}

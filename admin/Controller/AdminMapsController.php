<?php

namespace Genealogy\Admin\Controller;

use Genealogy\Admin\Models\AdminMapsModel;

class AdminMapsController
{
    protected $admin_config;

    public function __construct($admin_config)
    {
        $this->admin_config = $admin_config;
    }

    public function detail(): array
    {
        $mapsModel = new AdminMapsModel($this->admin_config);

        // *** July 2024: disabled some variables. Probably not needed anymore. ***
        $maps['use_world_map'] = $mapsModel->get_use_world_map();
        $maps['google_api1'] = $mapsModel->get_google_api1();
        //$maps['google_api2'] = $mapsModel->get_google_api2();
        $maps['geokeo_api'] = $mapsModel->get_geokeo_api();
        //$maps['default_zoom'] = $mapsModel->get_default_zoom();
        //$maps['map_type'] = $mapsModel->get_map_type();
        $maps['slider'] = $mapsModel->get_slider();

        $maps['geo_tree_id'] = $mapsModel->get_geo_tree_id();

        return $maps;
    }
}

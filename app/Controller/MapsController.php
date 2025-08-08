<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\MapsModel;

class MapsController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function detail($tree_prefix_quoted): array
    {
        $mapsModel = new MapsModel($this->config);

        $maps['select_world_map'] = $mapsModel->select_world_map();

        $maps['family_names'] = $mapsModel->get_family_names();
        $maps['show_family_names'] = $mapsModel->show_family_names($maps['family_names']);

        // *** Get slider settings ***
        if ($maps['select_world_map'] == 'Google') {
            $glider_settings = $mapsModel->get_slider_settings($tree_prefix_quoted);
            $maps = array_merge($maps, $glider_settings);
        }

        // *** Get desc array and chosen name ***
        if ($maps['select_world_map'] == 'Google') {
            $desc_array = $mapsModel->get_maps_descendants();
            $maps = array_merge($maps, $desc_array);

            $anc_array = $mapsModel->get_maps_ancestors();
            $maps = array_merge($maps, $anc_array);
        }

        // *** Variables: $maps['display_birth'] and $maps['display_death'] ***
        $maps_array = $mapsModel->get_maps_type();
        $maps = array_merge($maps, $maps_array);

        if ($maps['select_world_map'] == 'OpenStreetMap') {
            $maps_locations = $mapsModel->get_locations($maps);
            $maps = array_merge($maps, $maps_locations);
        }

        if ($maps['select_world_map'] == 'Google') {
            $maps['locarray'] = $mapsModel->get_locations_google($maps);
        }

        return $maps;
    }
}

<?php
require_once  __DIR__ . "/../model/maps.php";

include_once(__DIR__ . "/../../include/person_cls.php");
include_once(__DIR__ . "/../../include/marriage_cls.php");
include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");


include_once(__DIR__ . "/../../include/ancestors_descendants.php");


class MapsController
{
    public function detail($humo_option, $dbh, $tree_id, $tree_prefix_quoted)
    {
        $mapsModel = new MapsModel();

        $maps['select_world_map'] = $mapsModel->select_world_map($humo_option);

        $maps['family_names'] = $mapsModel->get_family_names();
        $maps['show_family_names'] = $mapsModel->show_family_names($maps['family_names']);

        // *** Get slider settings ***
        if ($maps['select_world_map'] == 'Google') {
            $glider_settings = $mapsModel->get_slider_settings($dbh, $tree_prefix_quoted);
            $maps = array_merge($maps, $glider_settings);
        }

        // *** Get desc array and chosen name ***
        if ($maps['select_world_map'] == 'Google') {
            $desc_array = $mapsModel->get_maps_descendants($dbh, $tree_id);
            $maps = array_merge($maps, $desc_array);

            $anc_array = $mapsModel->get_maps_ancestors($dbh, $tree_id);
            $maps = array_merge($maps, $anc_array);
        }

        // *** Variables: $maps['display_birth'] and $maps['display_death'] ***
        $maps_array = $mapsModel->get_maps_type();
        $maps = array_merge($maps, $maps_array);

        if ($maps['select_world_map'] == 'OpenStreetMap') {
            $maps_locations = $mapsModel->get_locations($dbh, $tree_id, $maps);
            $maps = array_merge($maps, $maps_locations);
        }

        if ($maps['select_world_map'] == 'Google') {
            //$maps['locarray'] = $mapsModel->get_locations_google($dbh);
            $maps['locarray'] = $mapsModel->get_locations_google($dbh, $tree_id, $maps);
        }

        return $maps;
    }
}

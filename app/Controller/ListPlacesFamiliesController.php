<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\ListPlacesFamiliesModel;

class ListPlacesFamiliesController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list_places_names(): array
    {
        $listPlacesFamiliesModel = new ListPlacesFamiliesModel($this->config);

        $get_data = $listPlacesFamiliesModel->getSelection();
        $query = $listPlacesFamiliesModel->build_query();

        return array(
            "place_name" => $get_data["place_name"],
            "select_marriage_notice" => $get_data["select_marriage_notice"],
            "select_marriage" => $get_data["select_marriage"],
            "select_marriage_notice_religious" => $get_data["select_marriage_notice_religious"],
            "select_marriage_religious" => $get_data["select_marriage_religious"],
            "part_place_name" => $get_data["part_place_name"],
            "query" => $query
        );
    }
}

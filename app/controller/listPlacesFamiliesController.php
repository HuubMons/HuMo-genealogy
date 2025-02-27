<?php
class ListPlacesFamiliesController
{

    //public function list_places_names($dbh, $tree_id, $user, $humo_option)
    public function list_places_names($tree_id)
    {
        $listPlacesFamiliesModel = new ListPlacesFamiliesModel();

        $get_data = $listPlacesFamiliesModel->getSelection();
        $query = $listPlacesFamiliesModel->build_query($tree_id);

        return array(
            "place_name" => $get_data["place_name"],
            "select_marriage_notice" => $get_data["select_marriage_notice"],
            "select_marriage" => $get_data["select_marriage"],
            "select_marriage_notice_religious" => $get_data["select_marriage_notice_religious"],
            "select_marriage_religious" => $get_data["select_marriage_religious"],
            "part_place_name" => $get_data["part_place_name"],

            "query" => $query
        );

        // TODO use array merge
        // *** Add array $person_data:
        //$data = array_merge($data, $person_data);  
    }
}

<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\ListModel;

class ListController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list_names(): array
    {
        $listModel = new ListModel($this->config);

        // *** Only used in Advanced search. A standard reset HTML button doesn't work if search is allready done! ***
        if (isset($_POST['reset_all'])) {
            $_POST = array();

            // *** Show advanced search ***
            $_GET['adv_search'] = '1';
        }

        if (isset($_POST['pers_firstname']) || isset($_GET['pers_lastname']) || isset($_GET['pers_firstname']) || isset($_GET['reset']) || isset($_POST['quicksearch'])) {
            unset($_SESSION["save_select_trees"]);
            unset($_SESSION["save_adv_search"]);

            // *** Array containing multiple search values ***
            unset($_SESSION["save_selection"]);
        }

        $index_list = $listModel->getIndexList();
        $order = $listModel->getOrder();
        $desc_asc = $listModel->getDescAsc($order);
        $order_select = $listModel->getOrderSelect();

        $get_orderby = $listModel->getQueryOrderBy($index_list, $desc_asc, $order_select);

        $select_trees = $listModel->getSelectTrees();
        $selection = $listModel->getSelection();

        $quicksearch = $listModel->getQuickSearch();
        $adv_search = $listModel->getAdvSearch($selection);

        $get_data = $listModel->getIndexPlaces($index_list);


        $person_result = $listModel->build_query();
        return array(
            "index_list" => $index_list,
            "order" => $order,
            "desc_asc" => $desc_asc,
            "order_select" => $order_select,

            "orderby" => $get_orderby["orderby"],
            "make_date" => $get_orderby["make_date"],

            "select_trees" => $select_trees,
            "selection" => $selection,

            "quicksearch" => $quicksearch,
            "adv_search" => $adv_search,

            "place_name" => $get_data["place_name"],
            "select_birth" => $get_data["select_birth"],
            "select_bapt" => $get_data["select_bapt"],
            "select_place" => $get_data["select_place"],
            "select_death" => $get_data["select_death"],
            "select_buried" => $get_data["select_buried"],
            "select_event" => $get_data["select_event"],
            "part_place_name" => $get_data["part_place_name"],

            "person_result" => $person_result["person_result"],
            "start" => $person_result["start"],
            "nr_persons" => $person_result["nr_persons"],
            "count_persons" => $person_result["count_persons"],
            "item" => $person_result["item"],
        );
    }
}

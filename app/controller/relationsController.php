<?php
require_once  __DIR__ . "/../model/relations.php";

include_once(__DIR__ . "/../../include/marriage_cls.php");
include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");

class RelationsController
{
    public function getRelations($db_functions, $person_cls)
    {
        $RelationsModel = new RelationsModel();

        $RelationsModel->resetValues();
        $RelationsModel->checkInput();

        $get_persons = $RelationsModel->getSelectedPersons($db_functions, $person_cls);
        $get_names = $RelationsModel->getNames();
        $get_GEDCOMnumbers = $RelationsModel->getGEDCOMnumbers();

        //$relation = array(
        //    "title" => __('Relationship calculator')
        //);

        // *** Add array $person_data:
        //$relation = array_merge($relation, $get_persons);
        $relation = $get_persons;
        $relation = array_merge($relation, $get_names);
        $relation = array_merge($relation, $get_GEDCOMnumbers);

        $switch_persons = $RelationsModel->switchPersons($relation);
        if (isset($switch_persons)) {
            $relation = array_merge($relation, $switch_persons);
        }

        return $relation;
    }
}

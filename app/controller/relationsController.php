<?php
require_once  __DIR__ . "/../model/relations.php";

include_once(__DIR__ . "/../../include/marriage_cls.php");
include_once(__DIR__ . "/../../include/language_date.php");
include_once(__DIR__ . "/../../include/date_place.php");

class RelationsController
{
    //private $db_functions, $user;

    /*
    public function __construct($db_functions, $user)
    {
        $this->db_functions = $db_functions;
        $this->user = $user;
    }
    */

    public function getRelations()
    {
        $RelationsModel = new RelationsModel();

        $RelationsModel->resetValues();
        $RelationsModel->checkInput();

        $get_persons = $RelationsModel->getSelectedPersons();
        $get_names = $RelationsModel->getNames();
        $get_GEDCOMnumbers = $RelationsModel->getGEDCOMnumbers();

        //$data = array(
        //    "title" => __('Relationship calculator')
        //);

        // *** Add array $person_data:
        //$data = array_merge($data, $get_persons);
        $data = $get_persons;
        $data = array_merge($data, $get_names);
        $data = array_merge($data, $get_GEDCOMnumbers);

        $switch_persons = $RelationsModel->switchPersons($data);
        if (isset($switch_persons)) $data = array_merge($data, $switch_persons);

        return $data;
    }
}

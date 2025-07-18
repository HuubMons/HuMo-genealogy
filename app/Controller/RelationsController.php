<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\RelationsModel;

class RelationsController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getRelations($selected_language): array
    {
        $RelationsModel = new RelationsModel($this->config, $selected_language);

        $RelationsModel->resetValues();
        $RelationsModel->checkInput();
        $RelationsModel->getSelectedPersons();
        $RelationsModel->getNames();
        $RelationsModel->getGEDCOMnumbers();
        $RelationsModel->switchPersons();
        $RelationsModel->set_control_variables();

        // *** Extended search ***
        if (isset($_POST["extended"]) || isset($_POST["next_path"])) {
            // TODO check variable. Is session needed?
            if (!isset($_POST["next_path"])) {
                $_SESSION['next_path'] = "";
            }
        }

        // *** Process standard calculation ***
        $RelationsModel->process_standard_calculation();

        // *** Process extended calculation ***
        if (isset($_POST["extended"]) || isset($_POST["next_path"])) {
            $RelationsModel->process_extended_calculation();
        }

        // TODO this could probably be improved.
        // Second array needed for marriage relationship. Some variables were used for processing marriage relationship.
        // $standard_extend is first array needed for standard and extended calculation.
        $standard_extended = $RelationsModel->get_variables_standard_extended();

        // *** Process marriage relationship. This function will use same variables ***
        $RelationsModel->process_marriage_relationship();

        $relation = $RelationsModel->get_variables();
        $relation = array_merge($standard_extended, $relation);

        $links = $RelationsModel->get_links();
        $relation = array_merge($relation, $links);

        return $relation;
    }
}

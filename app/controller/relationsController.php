<?php
class RelationsController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getRelations($person_cls, $link_cls, $uri_path, $selected_language): array
    {
        $RelationsModel = new RelationsModel($this->config, $selected_language);

        $RelationsModel->resetValues();
        $RelationsModel->checkInput();
        $RelationsModel->getSelectedPersons($person_cls);
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
        $RelationsModel->process_standard_calculation($link_cls, $uri_path);

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

        // http://localhost/HuMo-genealogy/family/3/F116?main_person=I202
        $relation['fam_path'] = $link_cls->get_link($uri_path, 'family', $this->config['tree_id'], true);
        $relation['rel_path'] = $link_cls->get_link($uri_path, 'relations', $this->config['tree_id']);

        return $relation;
    }
}

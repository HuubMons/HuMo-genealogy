<?php
class RelationsController
{
    public function getRelations($db_functions, $person_cls, $link_cls, $uri_path, $tree_id, $selected_language)
    {
        $RelationsModel = new RelationsModel($db_functions, $selected_language);

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
        $RelationsModel->process_standard_calculation($tree_id, $link_cls, $uri_path);

        if (isset($_POST["extended"]) || isset($_POST["next_path"])) {
            $RelationsModel->process_extended_calculation();
        }

        $relation = $RelationsModel->get_variables();

        // http://localhost/HuMo-genealogy/family/3/F116?main_person=I202
        $relation['fam_path'] = $link_cls->get_link($uri_path, 'family', $tree_id, true);
        $relation['rel_path'] = $link_cls->get_link($uri_path, 'relations', $tree_id);

        return $relation;
    }
}

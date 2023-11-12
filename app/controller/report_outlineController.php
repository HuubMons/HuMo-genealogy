<?php
// For now same model...
require_once  __DIR__ . "/../model/family.php";

class report_outlineController
{
    //private $db_functions, $user;

    //public function __construct($db_functions, $user)
    //{
    //    $this->db_functions = $db_functions;
    //    $this->user = $user;
    //}

    public function getFamily($dbh, $tree_id)
    {
        $familyModel = new FamilyModel($dbh);
        $family_id = $familyModel->getFamilyId();
        $main_person = $familyModel->getMainPerson();
        $family_expanded =  $familyModel->getFamilyExpanded();
        $source_presentation =  $familyModel->getSourcePresentation();
        $picture_presentation =  $familyModel->getPicturePresentation();
        $text_presentation =  $familyModel->getTextPresentation();
        $maps_presentation = $familyModel->getMapsPresentation();
        $number_roman = $familyModel->getNumberRoman();
        $number_generation = $familyModel->getNumberGeneration();
        $descendant_report = $familyModel->getDescendantReport();
        $descendant_header = $familyModel->getDescendantHeader('Outline report', $tree_id, $family_id, $main_person);

        $data = array(
            "family_id" => $family_id,
            "main_person" => $main_person,
            "family_expanded" => $family_expanded,
            "source_presentation" => $source_presentation,
            "picture_presentation" => $picture_presentation,
            "text_presentation" => $text_presentation,
            "maps_presentation" => $maps_presentation,
            "number_roman" => $number_roman,
            "number_generation" => $number_generation,
            "descendant_report" => $descendant_report,
            "descendant_header" => $descendant_header,
            "title" => __('Family')
        );
        return $data;
    }
}

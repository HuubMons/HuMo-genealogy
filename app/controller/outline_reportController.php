<?php
require_once  __DIR__ . "/../model/family.php";
require_once  __DIR__ . "/../model/outline_report.php";

class Outline_reportController
{
    //private $db_functions, $user;

    //public function __construct($db_functions, $user)
    //{
    //    $this->db_functions = $db_functions;
    //    $this->user = $user;
    //}

    public function getOutlineReport($dbh, $tree_id, $humo_option)
    {
        $OutlineReportModel = new OutlineReportModel($dbh);
        $family_id = $OutlineReportModel->getFamilyId();
        $main_person = $OutlineReportModel->getMainPerson();

        //$family_expanded =  $OutlineReportModel->getFamilyExpanded();
        // *** Use compact view in outline report ***
        $family_expanded = 'compact';

        $source_presentation =  $OutlineReportModel->getSourcePresentation();
        $picture_presentation =  $OutlineReportModel->getPicturePresentation();
        $text_presentation =  $OutlineReportModel->getTextPresentation();
        $maps_presentation = $OutlineReportModel->getMapsPresentation();
        $number_roman = $OutlineReportModel->getNumberRoman();
        $number_generation = $OutlineReportModel->getNumberGeneration();
        $descendant_report = $OutlineReportModel->getDescendantReport();
        $descendant_header = $OutlineReportModel->getDescendantHeader('Outline report', $tree_id, $family_id, $main_person);

        $show_details = $OutlineReportModel->getShowDetails();
        $show_date = $OutlineReportModel->getShowDate();
        $dates_behind_names = $OutlineReportModel->getDatesBehindNames();
        $nr_generations = $OutlineReportModel->getNrGenerations($humo_option);

        return array(
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

            "show_details" => $show_details,
            "show_date" => $show_date,
            "dates_behind_names" => $dates_behind_names,
            "nr_generations" => $nr_generations,

            "title" => __('Family')
        );

        // TODO use array merge
        // *** Add array $person_data:
        //$data = array_merge($data, $person_data);
    }
}

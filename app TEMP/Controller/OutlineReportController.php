<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\OutlineReportModel;

class OutlineReportController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getOutlineReport(): array
    {
        $OutlineReportModel = new OutlineReportModel($this->config);

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
        $descendant_header = $OutlineReportModel->getDescendantHeader('Outline report', $family_id, $main_person);

        $show_details = $OutlineReportModel->getShowDetails();
        $show_date = $OutlineReportModel->getShowDate();
        $dates_behind_names = $OutlineReportModel->getDatesBehindNames();
        $nr_generations = $OutlineReportModel->getNrGenerations();

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
    }
}

<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\FamilyModel;

class FamilyController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getFamily(): array
    {
        $familyModel = new FamilyModel($this->config);

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
        $descendant_header = $familyModel->getDescendantHeader('Descendant report', $family_id, $main_person);

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

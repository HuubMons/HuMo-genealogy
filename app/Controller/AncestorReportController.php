<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\AncestorModel;

class AncestorReportController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list($id): array
    {
        $get_ancestorModel = new AncestorModel($this->config);

        $main_person = $get_ancestorModel->getMainPerson2($id);
        $rom_nr = $get_ancestorModel->getNumberRoman();
        $ancestor_header = $get_ancestorModel->getAncestorHeader('Ancestor report', $main_person);

        // TODO for now using extended class.
        $text_presentation =  $get_ancestorModel->getTextPresentation();
        $family_expanded =  $get_ancestorModel->getFamilyExpanded();
        $picture_presentation =  $get_ancestorModel->getPicturePresentation();
        // Source presentation is saved in session.
        //$source_presentation =  $familyModel->getSourcePresentation();

        $data = array(
            "main_person" => $main_person,
            "rom_nr" => $rom_nr,
            "ancestor_header" => $ancestor_header,
            "text_presentation" => $text_presentation,
            "family_expanded" => $family_expanded,
            "picture_presentation" => $picture_presentation,
            "title" => __('Ancestor report')
        );

        return $data;
    }
}

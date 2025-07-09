<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\DescendantModel;
use Genealogy\App\Model\AncestorModel;

class HourglassController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getHourglass(): array
    {
        $descendantModel = new DescendantModel($this->config);

        // *** Sets hourglass to true. Hourglass uses descendant chart and ancestor chart ***
        $descendantModel->setHourglass(true);

        $family_id = $descendantModel->getFamilyId();
        $main_person = $descendantModel->getMainPerson();
        //$family_expanded =  $descendantModel->getFamilyExpanded();
        $family_expanded = 'compact';
        //$source_presentation =  $descendantModel->getSourcePresentation();
        $picture_presentation =  $descendantModel->getPicturePresentation();
        $text_presentation =  $descendantModel->getTextPresentation();
        //$maps_presentation = $descendantModel->getMapsPresentation();
        //$number_roman = $descendantModel->getNumberRoman();
        //$number_generation = $descendantModel->getNumberGeneration();
        //$descendant_report = $descendantModel->getDescendantReport();
        $descendant_report = true;
        $descendant_header = $descendantModel->getDescendantHeader('Descendant chart', $family_id, $main_person);

        // Also add these variables for hourglass in descendant_chart.php
        $dna = $descendantModel->getDNA();
        $chosengen = $descendantModel->GetChosengen($dna);
        $chosengenanc = $descendantModel->getChosengenanc();
        $size = $descendantModel->getSize($dna);
        //$direction = $descendantModel->getDirection();
        $direction = 1;


        //TODO remove temp. global.
        global $data;
        $genarray = $descendantModel->Prepare_genarray($data);
        $genarray = $descendantModel->generate($genarray);

        $hsize = $descendantModel->getHsize();
        $vdist = $descendantModel->getVdist();
        $vsize = $descendantModel->getVsize();
        $hdist = $descendantModel->getHdist();

        $data = array(
            "family_id" => $family_id,
            "main_person" => $main_person,
            "family_expanded" => $family_expanded,
            "picture_presentation" => $picture_presentation,
            "text_presentation" => $text_presentation,
            "descendant_report" => $descendant_report,
            "descendant_header" => $descendant_header,
            "direction" => $direction,

            "dna" => $dna,
            "chosengen" => $chosengen,
            "chosengenanc" => $chosengenanc,
            "size" => $size,

            "hsize" => $hsize,
            "vdist" => $vdist,
            "vsize" => $vsize,
            "hdist" => $hdist,

            "genarray" => $genarray,

            "title" => __('Family')
        );

        // *** Ancestors ***
        $get_ancestorModel = new AncestorModel($this->config);
        $get_ancestors = $get_ancestorModel->get_ancestors2($main_person);
        $data = array_merge($data, $get_ancestors);

        return $data;
    }
}

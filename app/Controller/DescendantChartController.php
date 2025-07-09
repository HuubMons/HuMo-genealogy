<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\DescendantModel;

class DescendantChartController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function getFamily(): array
    {
        $descendantModel = new DescendantModel($this->config);

        $descendantModel->setHourglass(false);

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
        $direction = $descendantModel->getDirection();

        //TODO remove temp. global.
        //global $db_functions, $data, $user;
        global $data;
        $genarray = $descendantModel->Prepare_genarray($data);
        $genarray = $descendantModel->generate($genarray);

        $hsize = $descendantModel->getHsize();
        $vdist = $descendantModel->getVdist();
        $vsize = $descendantModel->getVsize();
        $hdist = $descendantModel->getHdist();

        // TODO check these variables
        /*
        $base_person_famc = $descendantModel->getBasepersonfamc($dna);
        $base_person_sexe = $descendantModel->getBasepersonsexe($dna);
        $base_person_name = $descendantModel->getBasepersonname($dna);
        $base_person_gednr = $descendantModel->getBasepersongednr($dna);
        */

        $base_person = $descendantModel->getBasePerson($main_person);

        //"base_person_famc" => $base_person_famc,
        //"base_person_sexe" => $base_person_sexe,
        //"base_person_name" => $base_person_name,
        //"base_person_gednr" => $base_person_gednr,

        $data = array(
            "family_id" => $family_id,
            "main_person" => $main_person,
            "family_expanded" => $family_expanded,
            "picture_presentation" => $picture_presentation,
            "text_presentation" => $text_presentation,
            "descendant_report" => $descendant_report,
            "descendant_header" => $descendant_header,

            "dna" => $dna,
            "chosengen" => $chosengen,
            "chosengenanc" => $chosengenanc,
            "size" => $size,
            "direction" => $direction,

            "hsize" => $hsize,
            "vdist" => $vdist,
            "vsize" => $vsize,
            "hdist" => $hdist,

            "genarray" => $genarray,

            "base_person_famc" => $base_person["famc"],
            "base_person_sexe" => $base_person["sexe"],
            "base_person_name" => $base_person["name"],
            "base_person_gednr" => $base_person["gednr"],

            "title" => __('Family')
        );
        return $data;
    }
}

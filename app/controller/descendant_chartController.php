<?php
require_once  __DIR__ . "/../model/family.php";
require_once  __DIR__ . "/../model/descendant.php";

class Descendant_chartController
{
    //private $db_functions, $user;

    //public function __construct($db_functions, $user)
    //{
    //    $this->db_functions = $db_functions;
    //    $this->user = $user;
    //}

    public function getFamily($dbh, $tree_id)
    {
        $descendantModel = new DescendantModel($dbh);

        $descendantModel->setHourglass(false);

        $family_id = $descendantModel->getFamilyId();
        $main_person = $descendantModel->getMainPerson();
        //$family_expanded =  $descendantModel->getFamilyExpanded();
        $family_expanded = false;
        //$source_presentation =  $descendantModel->getSourcePresentation();
        $picture_presentation =  $descendantModel->getPicturePresentation();
        $text_presentation =  $descendantModel->getTextPresentation();
        //$maps_presentation = $descendantModel->getMapsPresentation();
        //$number_roman = $descendantModel->getNumberRoman();
        //$number_generation = $descendantModel->getNumberGeneration();

        //$descendant_report = $descendantModel->getDescendantReport();
        $descendant_report = true;

        $descendant_header = $descendantModel->getDescendantHeader('Descendant chart', $tree_id, $family_id, $main_person);

        // Also add these variables for hourglass in descendant_chart.php
        $dna = $descendantModel->getDNA();
        $chosengen = $descendantModel->GetChosengen($dna);
        $chosengenanc = $descendantModel->getChosengenanc();
        $size = $descendantModel->getSize($dna);
        $direction = $descendantModel->getDirection();

        //TODO remove temp. global.
        global $db_functions, $data, $user;
        $genarray = $descendantModel->Prepare_genarray($db_functions, $data, $user);

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

        $base_person = $descendantModel->getBasePerson($db_functions, $main_person);

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

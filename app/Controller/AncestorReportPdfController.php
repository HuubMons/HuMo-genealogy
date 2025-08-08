<?php

namespace Genealogy\App\Controller;

class AncestorReportPdfController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function list($id): array
    {
        $get_ancestor = new \Genealogy\App\Model\AncestorModel($this->config);
        $data["main_person"] = $get_ancestor->getMainPerson2('', __FILE__);

        $data["rom_nr"] = $get_ancestor->getNumberRoman();

        $data["text_presentation"] =  $get_ancestor->getTextPresentation();
        $data["family_expanded"] =  $get_ancestor->getFamilyExpanded();
        $data["picture_presentation"] =  $get_ancestor->getPicturePresentation();
        // source_presentation is saved in session.

        return $data;
    }
}

/*
use Genealogy\App\Controller\AncestorReportController;

class AncestorReportPdfController extends AncestorReportController
{
    public function __construct($config)
    {
        parent::__construct($config);
    }

    public function list($id): array
    {
        $data = parent::list($id);

        // Additional logic for PDF generation can be added here if needed.
        // For now, we just return the data prepared by the parent class.
        
        return $data;
    }
}
*/

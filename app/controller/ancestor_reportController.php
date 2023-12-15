<?php
require_once  __DIR__ . "/../model/ancestor.php";

class Ancestor_reportController
{
    private $dbh;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function list($tree_id)
    {
        $get_ancestorModel = new AncestorModel($this->dbh);

        $main_person = $get_ancestorModel->getMainPerson();
        $rom_nr = $get_ancestorModel->getNumberRoman();
        $ancestor_header = $get_ancestorModel->getAncestorHeader('Ancestor report', $tree_id, $main_person);

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

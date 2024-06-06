<?php
require_once  __DIR__ . "/../model/ancestor.php";

class Ancestor_sheetController
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
        // Not needed in ancestor sheet:
        //$rom_nr = $get_ancestorModel->getNumberRoman();
        $ancestor_header = $get_ancestorModel->getAncestorHeader('Ancestor sheet', $tree_id, $main_person);

        // Not needed for ancestor_sheet.
        // TODO for now using extended class.
        //$text_presentation = $get_ancestorModel->getTextPresentation();
        //$family_expanded = $get_ancestorModel->getFamilyExpanded();
        //$picture_presentation = $get_ancestorModel->getPicturePresentation();

        $data = array(
            "main_person" => $main_person,
            "ancestor_header" => $ancestor_header,
            "title" => __('Ancestor sheet')
        );
        
        return $data;
    }
}

<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\AncestorModel;

class AncestorSheetController
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
        // Not needed in ancestor sheet:
        //$rom_nr = $get_ancestorModel->getNumberRoman();
        $ancestor_header = $get_ancestorModel->getAncestorHeader('Ancestor sheet', $main_person);

        $get_ancestors = $get_ancestorModel->get_ancestors2($main_person);

        $data = array(
            "main_person" => $main_person,
            "ancestor_header" => $ancestor_header,
            "title" => __('Ancestor sheet')
        );

        return array_merge($data, $get_ancestors);
    }
}

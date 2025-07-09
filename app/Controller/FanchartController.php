<?php

namespace Genealogy\App\Controller;

use Genealogy\App\Model\FanchartModel;

class FanchartController
{
    private $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function detail($id): array
    {
        $get_fanchartModel = new FanchartModel($this->config);

        $main_person = $get_fanchartModel->getMainPerson2($id);
        $ancestor_header = $get_fanchartModel->getAncestorHeader('Fanchart', $main_person);

        $chosengen = $get_fanchartModel->get_chosengen();
        $fontsize = $get_fanchartModel->get_fontsize();
        $date_display = $get_fanchartModel->get_date_display();
        $printing = $get_fanchartModel->get_printing();
        $fan_style = $get_fanchartModel->get_fan_style();
        $fan_width = $get_fanchartModel->get_fan_width();
        $real_width = $get_fanchartModel->get_real_width($fan_width);

        // Doesn't work yet.
        //$fanchart_item = $get_fanchartModel->generate_fanchart_item_array($chosengen);

        //"fanchart_item" => $fanchart_item
        $data = array(
            "main_person" => $main_person,
            "ancestor_header" => $ancestor_header,
            "chosengen" => $chosengen,
            "fontsize" => $fontsize,
            "date_display" => $date_display,
            "printing" => $printing,
            "fan_style" => $fan_style,
            "fan_width" => $fan_width,
            "real_width" => $real_width
        );

        //$data = array_merge($data, $fanchart_item);

        return $data;
    }
}

<?php
require_once  __DIR__ . "/../model/anniversary.php";

include_once(__DIR__ . "/../../include/person_cls.php");
include_once(__DIR__ . "/../../include/language_date.php");

class AnniversaryController
{
    public function anniversary()
    {
        $anniversaryModel = new AnniversaryModel();

        $get_month = $anniversaryModel->getMonth();
        $get_present_date = $anniversaryModel->getPresentDate();
        $get_ann_choice = $anniversaryModel->getAnnChoice();

        $get_civil = $anniversaryModel->getCivil();
        $get_relig = $anniversaryModel->getRelig();
        if (!$get_civil && !$get_relig) {
            $get_civil = true;
        }

        $url_end = $anniversaryModel->getUrlend($get_ann_choice, $get_civil, $get_relig);

        $data = array(
            "today" => $get_present_date,
            "ann_choice" => $get_ann_choice,
            "civil" => $get_civil,
            "relig" => $get_relig,
            "url_end" => $url_end
        );

        return array_merge($data, $get_month);
    }
}

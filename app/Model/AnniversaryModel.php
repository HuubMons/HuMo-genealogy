<?php

namespace Genealogy\App\Model;

use Genealogy\Include\LanguageDate;

class AnniversaryModel
{

    private $languageDate;

    public function __construct()
    {
        $this->languageDate = new LanguageDate();
    }

    public function getMonth()
    {
        // *** Month to show ***
        $data["month"] = strtolower(date("M"));
        if (isset($_GET['month']) && strlen($_GET['month']) == '3') {
            $month_check = $_GET['month'];
            if ($month_check == 'jan') {
                $data["month"] = 'jan';
            }
            if ($month_check == 'feb') {
                $data["month"] = 'feb';
            }
            if ($month_check == 'mar') {
                $data["month"] = 'mar';
            }
            if ($month_check == 'apr') {
                $data["month"] = 'apr';
            }
            if ($month_check == 'may') {
                $data["month"] = 'may';
            }
            if ($month_check == 'jun') {
                $data["month"] = 'jun';
            }
            if ($month_check == 'jul') {
                $data["month"] = 'jul';
            }
            if ($month_check == 'aug') {
                $data["month"] = 'aug';
            }
            if ($month_check == 'sep') {
                $data["month"] = 'sep';
            }
            if ($month_check == 'oct') {
                $data["month"] = 'oct';
            }
            if ($month_check == 'nov') {
                $data["month"] = 'nov';
            }
            if ($month_check == 'dec') {
                $data["month"] = 'dec';
            }
        }
        $data["show_month"] = $this->languageDate->language_date($data["month"]);

        $months_map = [
            'jan' => 1, 'feb' => 2, 'mar' => 3, 'apr' => 4,
            'may' => 5, 'jun' => 6, 'jul' => 7, 'aug' => 8,
            'sep' => 9, 'oct' => 10, 'nov' => 11, 'dec' => 12,
        ];
        $data["month_int"] = isset($months_map[$data["month"]]) ? $months_map[$data["month"]] : (int) date('n');

        return $data;
    }

    public function getPresentDate()
    {
        // *** Calculate present date, month and year ***
        $today = date('j') . ' ' . date('M');
        return strtolower($today);
    }

    public function getAnnChoice()
    {
        $ann_choice = 'birthdays';
        if (isset($_POST['ann_choice']) && $_POST['ann_choice'] == 'wedding') {
            $ann_choice = 'wedding';
        }
        if (isset($_GET['ann_choice']) && $_GET['ann_choice'] == 'wedding') {
            $ann_choice = 'wedding';
        }
        return $ann_choice;
    }

    public function getCivil()
    {
        $civil = false;
        if (isset($_POST['civil'])) {
            $civil = true;
        }
        if (isset($_GET['civil'])) {
            $civil = true;
        }
        return $civil;
    }

    public function getRelig()
    {
        $relig = false;
        if (isset($_POST['relig'])) {
            $relig = true;
        }
        if (isset($_GET['relig'])) {
            $relig = true;
        }
        return $relig;
    }

    public function getUrlend($ann_choice, $civil, $relig)
    {
        $url_end = '';
        if ($ann_choice == 'wedding') {
            $url_end = '&ann_choice=wedding';
            if ($civil) {
                $url_end .= '&civil=civil';
            }
            if ($relig) {
                $url_end .= '&relig=relig';
            }
        }
        return $url_end;
    }
}

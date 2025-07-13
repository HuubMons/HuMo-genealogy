<?php

/**
 * July 2023: refactor ancestor.php to MVC
 * 
 * This model is used by multiple ancestor reports (report/ chart/ sheet)
 */

namespace Genealogy\App\Model;

use Genealogy\Include\ProcessLinks;
use Genealogy\App\Model\FamilyModel;

class AncestorModel extends FamilyModel
{
    public function getMainPerson2($id): string
    {
        $main_person = 'I1'; // *** Mainperson of a family ***

        //if (isset($_GET["main_person"])) {
        //    $main_person = $_GET["main_person"];
        //}
        //if (isset($_POST["main_person"])) {
        //    $main_person = $_POST["main_person"];
        //}
        if (isset($id)) {
            // $id=last variable in link: http://127.0.0.1/humo-genealogy/ancestor_report/3/I1180
            $main_person = $id;
        }
        if (isset($_GET["id"])) {
            $main_person = $_GET["id"];
        }
        if (isset($_POST["id"])) {
            $main_person = $_POST["id"];
        }

        return $main_person;
    }

    // TODO: use general get_ancestor function.
    // The following is used for ancestor chart, ancestor sheet and ancestor sheet PDF (ASPDF)
    public function get_ancestors2($pers_gedcomnumber): array
    {
        // person 01
        $personDb = $this->db_functions->get_person($pers_gedcomnumber);
        $data["gedcomnumber"][1] = $personDb->pers_gedcomnumber;
        $pers_famc[1] = $personDb->pers_famc;
        $data["sexe"][1] = $personDb->pers_sexe;
        $parent_array[2] = '';
        $parent_array[3] = '';
        if ($pers_famc[1]) {
            $parentDb = $this->db_functions->get_family($pers_famc[1]);
            $parent_array[2] = $parentDb->fam_man;
            $parent_array[3] = $parentDb->fam_woman;
            $data["marr_date"][2] = $parentDb->fam_marr_date;
            $data["marr_place"][2] = $parentDb->fam_marr_place;
        }

        // Loop to find person data
        $count_max = 64;
        // *** hourglass report ***
        if (isset($hourglass) && $hourglass === true) {
            $count_max = pow(2, $data["chosengenanc"]);
        }

        for ($counter = 2; $counter < $count_max; $counter++) {
            $data["gedcomnumber"][$counter] = '';
            $pers_famc[$counter] = '';
            $data["sexe"][$counter] = '';
            if ($parent_array[$counter]) {
                $personDb = $this->db_functions->get_person($parent_array[$counter]);
                $data["gedcomnumber"][$counter] = $personDb->pers_gedcomnumber;
                $pers_famc[$counter] = $personDb->pers_famc;
                $data["sexe"][$counter] = $personDb->pers_sexe;
            }

            $Mcounter = $counter * 2;
            $Fcounter = $Mcounter + 1;
            $parent_array[$Mcounter] = '';
            $parent_array[$Fcounter] = '';
            $data["marr_date"][$Mcounter] = '';
            $data["marr_place"][$Mcounter] = '';
            if ($pers_famc[$counter]) {
                $parentDb = $this->db_functions->get_family($pers_famc[$counter]);
                $parent_array[$Mcounter] = $parentDb->fam_man;
                $parent_array[$Fcounter] = $parentDb->fam_woman;
                $data["marr_date"][$Mcounter] = $parentDb->fam_marr_date;
                $data["marr_place"][$Mcounter] = $parentDb->fam_marr_place;
            }
        }
        return $data;
    }

    function getAncestorHeader($name, $main_person): string
    {
        $processLinks = new ProcessLinks();

        $data['header_active'] = array();
        $data['header_link'] = array();
        $data['header_text'] = array();

        $vars['id'] = $main_person;
        $link = $processLinks->get_link($this->uri_path, 'ancestor_report', $this->tree_id, false, $vars);
        //$link .= 'screen_mode=ancestor_chart';

        $data['header_link'][] = $link;
        $data['header_active'][] = $name == 'Ancestor report' ? 'active' : '';
        $data['header_text'][] = __('Ancestor report');

        // TODO improve paths and variables.
        if ($this->humo_option["url_rewrite"] == 'j') {
            $path = 'ancestor_chart/' . $this->tree_id . '/' . $main_person;
        } else {
            $path = 'index.php?page=ancestor_chart?tree_id=' . $this->tree_id . '&amp;id=' . $main_person;
        }
        $data['header_link'][] = $path;
        $data['header_active'][] = $name == 'Ancestor chart' ? 'active' : '';
        $data['header_text'][] = __('Ancestor chart');

        if ($this->humo_option["url_rewrite"] == 'j') {
            $path = 'ancestor_sheet/' . $this->tree_id . '/' . $main_person;
        } else {
            $path = 'index.php?page=ancestor_sheet&amp;tree_id=' . $this->tree_id . '&amp;id=' . $main_person;
        }
        $data['header_link'][] = $path;
        $data['header_active'][] = $name == 'Ancestor sheet' ? 'active' : '';
        $data['header_text'][] = __('Ancestor sheet');

        // *** Fanchart ***
        $vars['id'] = $main_person;
        $path = $processLinks->get_link($this->uri_path, 'fanchart', $this->tree_id, false, $vars);

        $data['header_link'][] = $path;
        $data['header_active'][] = $name == 'Fanchart' ? 'active' : '';
        $data['header_text'][] = __('Fanchart');

        // TODO Move to view (is used multiple times)?
        // *** Tab menu ***
        $text = '
        <h1>' . __('Ancestors') . '</h1>
        <ul class="nav nav-tabs d-print-none">
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['header_active'][0] . '" href="' . $data['header_link'][0] . '" rel="nofollow">' . $data['header_text'][0] . '</a>
            </li>
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['header_active'][1] . '" href="' . $data['header_link'][1] . '" rel="nofollow">' . $data['header_text'][1] . '</a>
            </li>
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['header_active'][2] . '" href="' . $data['header_link'][2] . '" rel="nofollow">' . $data['header_text'][2] . '</a>
            </li>
            <li class="nav-item me-1">
                <a class="nav-link genealogy_nav-link ' . $data['header_active'][3] . '" href="' . $data['header_link'][3] . '" rel="nofollow">' . $data['header_text'][3] . '</a>
            </li>
        </ul>
        <!-- Align content to the left -->
        <!-- <div style="float: left; background-color:white; height:500px; padding:10px;"> -->
        <div style="float: left; background-color:white; padding:10px;">';

        return $text;
    }
}

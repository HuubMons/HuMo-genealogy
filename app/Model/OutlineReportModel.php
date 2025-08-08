<?php

/**
 * OutlineReportModel.php
 * 
 * Jul. 2025 Huub: changed <div> into <ul> in function outline_report_html.
 */

namespace Genealogy\App\Model;

use Genealogy\App\Model\FamilyModel;

class OutlineReportModel extends FamilyModel
{
    //private $generation_number = 0;
    private $nr_generations;
    private $show_details;
    private $show_date;
    private $dates_behind_names;
    private string $html_output = '';

    public function getShowDetails(): bool
    {
        $show_details = false;
        if (isset($_GET["show_details"]) && is_numeric(($_GET["show_details"]))) {
            $show_details = $_GET["show_details"];
        }
        if (isset($_POST["show_details"]) && is_numeric($_POST["show_details"])) {
            $show_details = $_POST["show_details"];
        }
        $this->show_details = $show_details;
        return $show_details;
    }

    public function getShowDate(): bool
    {
        $show_date = true;
        if (isset($_GET["show_date"]) && is_numeric($_GET["show_date"])) {
            $show_date = $_GET["show_date"];
        }
        if (isset($_POST["show_date"]) && is_numeric($_POST["show_date"])) {
            $show_date = $_POST["show_date"];
        }
        $this->show_date = $show_date;
        return $show_date;
    }

    public function getDatesBehindNames(): bool
    {
        $dates_behind_names = true;
        if (isset($_GET["dates_behind_names"]) && is_numeric($_GET["dates_behind_names"])) {
            $dates_behind_names = $_GET["dates_behind_names"];
        }
        if (isset($_POST["dates_behind_names"]) && is_numeric($_POST["dates_behind_names"])) {
            $dates_behind_names = $_POST["dates_behind_names"];
        }
        $this->dates_behind_names = $dates_behind_names;
        return $dates_behind_names;
    }

    public function getNrGenerations(): int
    {
        $nr_generations = ($this->humo_option["descendant_generations"] - 1);
        if (isset($_GET["nr_generations"]) && is_numeric($_GET["nr_generations"])) {
            $nr_generations = $_GET["nr_generations"];
        }
        if (isset($_POST["nr_generations"]) && is_numeric($_POST["nr_generations"])) {
            $nr_generations = $_POST["nr_generations"];
        }
        $this->nr_generations = $nr_generations;
        return $nr_generations;
    }

    /**
     * Recursive function outline
     */
    public function outline_report_html($outline_family_id, $outline_main_person, $generation_number = 0)
    {
        global $language, $screen_mode;

        $directionMarkers = new \Genealogy\Include\DirectionMarkers($language["dir"], $screen_mode);
        $personPrivacy = new \Genealogy\Include\PersonPrivacy;
        $personName_extended = new \Genealogy\Include\PersonNameExtended('compact');
        $personData = new \Genealogy\Include\PersonData('compact');
        $languageDate = new \Genealogy\Include\LanguageDate;
        $totallyFilterPerson = new \Genealogy\Include\TotallyFilterPerson;

        $family_nr = 1; //*** Process multiple families ***

        $show_privacy_text = false;

        if ($this->nr_generations < $generation_number) {
            return;
        }
        $generation_number++;

        // *** Count marriages of man ***
        // *** YB: if needed show woman as main_person ***
        $familyDb = $this->db_functions->get_family($outline_family_id, 'man-woman');
        $parent1 = '';
        $parent2 = '';
        $swap_parent1_parent2 = false;

        // *** Standard main_person is the father ***
        if ($familyDb->fam_man) {
            $parent1 = $familyDb->fam_man;
        }
        // *** If mother is selected, mother will be main_person ***
        if ($familyDb->fam_woman == $outline_main_person) {
            $parent1 = $familyDb->fam_woman;
            $swap_parent1_parent2 = true;
        }

        // *** Check family with parent1: N.N. ***
        if ($parent1) {
            // *** Save man's families in array ***
            $personDb = $this->db_functions->get_person($parent1, 'famc-fams');
            $marriage_array = explode(";", $personDb->pers_fams);
            $nr_families = substr_count($personDb->pers_fams, ";");
        } else {
            $marriage_array[0] = $outline_family_id;
            $nr_families = "0";
        }

        // *** Loop multiple marriages of main_person ***
        for ($parent1_marr = 0; $parent1_marr <= $nr_families; $parent1_marr++) {
            $familyDb = $this->db_functions->get_family($marriage_array[$parent1_marr]);

            // *** Privacy filter man and woman ***
            $person_manDb = $this->db_functions->get_person($familyDb->fam_man);
            $privacy_man = $personPrivacy->get_privacy($person_manDb);

            $person_womanDb = $this->db_functions->get_person($familyDb->fam_woman);
            $privacy_woman = $personPrivacy->get_privacy($person_womanDb);

            $marriage_cls = new \Genealogy\Include\MarriageCls($familyDb, $privacy_man, $privacy_woman);
            $family_privacy = $marriage_cls->get_privacy();

            $this->html_output .= '<ul class="outline-tree">';

            /**
             * Show parent1 (normally the father)
             */
            if ($familyDb->fam_kind != 'PRO-GEN') {
                //onecht kind, vrouw zonder man
                if ($family_nr == 1) {
                    // *** Show data of man ***
                    $this->html_output .= '<li class="generation">';
                    $this->html_output .= '<span class="generation-number">' . $generation_number . '</span>';

                    if ($swap_parent1_parent2 == true) {
                        $this->html_output .= $personName_extended->name_extended($person_womanDb, $privacy_woman, "outline");
                        if ($this->show_details && !$privacy_woman) {
                            $this->html_output .= $personData->person_data($person_womanDb, $privacy_woman, "outline", $familyDb->fam_gedcomnumber);
                        }

                        if ($this->show_date == "1" && !$privacy_woman && !$this->show_details) {
                            $this->html_output .= $directionMarkers->dirmark1 . ',';
                            if ($this->dates_behind_names == false) {
                                $this->html_output .= '<br>';
                            }
                            $this->html_output .= ' &nbsp; (' . $languageDate->language_date($person_womanDb->pers_birth_date) . ' - ' . $languageDate->language_date($person_womanDb->pers_death_date) . ')';
                        }
                    } else {
                        $this->html_output .= $personName_extended->name_extended($person_manDb, $privacy_man, "outline");
                        if ($this->show_details && !$privacy_man) {
                            $this->html_output .= $personData->person_data($person_manDb, $privacy_man, "outline", $familyDb->fam_gedcomnumber);
                        }

                        if ($this->show_date == "1" && !$privacy_man && !$this->show_details) {
                            $this->html_output .= $directionMarkers->dirmark1 . ',';
                            if ($this->dates_behind_names == false) {
                                $this->html_output .= '<br>';
                            }
                            $this->html_output .= ' &nbsp; (' . $languageDate->language_date($person_manDb->pers_birth_date) . ' - ' . $languageDate->language_date($person_manDb->pers_death_date) . ')';
                        }
                    }
                    $this->html_output .= '</li>';
                } else {
                    // empty: no second show of data of main_person in outline report
                }
                $family_nr++;
            }

            /**
             * Show parent2 (normally the mother)
             */

            // *** Totally hide parent2 if setting is active ***
            $show_parent2 = true;
            if ($swap_parent1_parent2) {
                if ($totallyFilterPerson->isTotallyFiltered($this->user, $person_manDb)) {
                    $show_privacy_text = true;
                    $family_privacy = true;
                    $show_parent2 = false;
                }
            } else {
                if ($totallyFilterPerson->isTotallyFiltered($this->user, $person_womanDb)) {
                    $show_privacy_text = true;
                    $family_privacy = true;
                    $show_parent2 = false;
                }
            }

            // TODO improve this script and use $parent1Db and $parent2Db.
            // Needed for marriageCls.php. Workaround to solve bug.
            global $parent1Db, $parent2Db;
            if ($swap_parent1_parent2) {
                $parent1Db = $person_womanDb;
                $parent2Db = $person_manDb;
            } else {
                $parent1Db = $person_manDb;
                $parent2Db = $person_womanDb;
            }

            $this->html_output .= '<li class="generation">';

            if (!$this->show_details) {
                $this->html_output .= ' x ' . $directionMarkers->dirmark1;
            } else {
                $this->html_output .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
                if ($parent1_marr == 0) {
                    if ($family_privacy) {
                        $this->html_output .= $marriage_cls->marriage_data($familyDb, '', 'short') . "<br>";
                    } else {
                        $this->html_output .= $marriage_cls->marriage_data() . "<br>";
                        //$this->html_output.= $marriage_cls->marriage_data($familyDb) . "<br>";
                    }
                } else {
                    $this->html_output .= $marriage_cls->marriage_data($familyDb, $parent1_marr + 1, 'shorter') . ' <br>';
                }
            }

            if ($show_parent2 && $swap_parent1_parent2) {
                $this->html_output .= $personName_extended->name_extended($person_manDb, $privacy_man, "outline");
                if ($this->show_details && !$privacy_man) {
                    $this->html_output .= $personData->person_data($person_manDb, $privacy_man, "outline", $familyDb->fam_gedcomnumber);
                }

                if ($this->show_date == "1" && !$privacy_man && !$this->show_details) {
                    $this->html_output .= $directionMarkers->dirmark1 . ',';
                    if ($this->dates_behind_names == false) {
                        $this->html_output .= '<br>';
                    }
                    $this->html_output .= ' &nbsp; (' . @$languageDate->language_date($person_manDb->pers_birth_date) . ' - ' . @$languageDate->language_date($person_manDb->pers_death_date) . ')';
                }
            } elseif ($show_parent2) {
                if ($this->show_details) {
                    $this->html_output .= "&nbsp;&nbsp;&nbsp;&nbsp;";
                }
                $this->html_output .= $personName_extended->name_extended($person_womanDb, $privacy_woman, "outline");
                if ($this->show_details && !$privacy_woman) {
                    $this->html_output .= $personData->person_data($person_womanDb, $privacy_woman, "outline", $familyDb->fam_gedcomnumber);
                }

                if ($this->show_date == "1" && !$privacy_woman && !$this->show_details) {
                    $this->html_output .= $directionMarkers->dirmark1 . ',';
                    if ($this->dates_behind_names == false) {
                        $this->html_output .= '<br>';
                    }
                    $this->html_output .= ' &nbsp; (' . @$languageDate->language_date($person_womanDb->pers_birth_date) . ' - ' . @$languageDate->language_date($person_womanDb->pers_death_date) . ')';
                }
            } else {
                // *** No permission to show parent2 ***
                $this->html_output .= __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
            }
            $this->html_output .= '</li>';

            /**
             * Show children
             */
            if ($familyDb->fam_children) {
                $childnr = 1;
                $child_array = explode(";", $familyDb->fam_children);
                foreach ($child_array as $i => $value) {
                    $childDb = $this->db_functions->get_person($child_array[$i]);

                    // *** Totally hide children if setting is active ***
                    if ($totallyFilterPerson->isTotallyFiltered($this->user, $childDb)) {
                        if (!$show_privacy_text) {
                            $this->html_output .= __('*** Privacy filter is active, one or more items are filtered. Please login to see all items ***') . '<br>';
                            $show_privacy_text = true;
                        }
                        continue;
                    }

                    $child_privacy = $personPrivacy->get_privacy($childDb);

                    // *** Build descendant_report ***
                    if ($childDb->pers_fams) {
                        // *** 1st family of child ***
                        $child_family = explode(";", $childDb->pers_fams);
                        $child1stfam = $child_family[0];
                        $this->outline_report_html($child1stfam, $childDb->pers_gedcomnumber, $generation_number);  // recursive
                    } else {
                        // Child without own family
                        if ($this->nr_generations >= $generation_number) {
                            $childgn = $generation_number + 1;
                            $this->html_output .= '<ul class="outline-tree">';
                            $this->html_output .= '<li class="generation">';

                            $this->html_output .= '<span class="generation-number">' . $childgn . '</span>';
                            $this->html_output .= $personName_extended->name_extended($childDb, $child_privacy, "outline");
                            if ($this->show_details and !$child_privacy) {
                                $this->html_output .= $personData->person_data($childDb, $child_privacy, "outline", "");
                            }

                            if ($this->show_date == "1" and !$child_privacy and !$this->show_details) {
                                $this->html_output .= $directionMarkers->dirmark1 . ',';
                                if ($this->dates_behind_names == false) {
                                    $this->html_output .= '<br>';
                                }
                                $this->html_output .= ' &nbsp; (' . $languageDate->language_date($childDb->pers_birth_date) . ' - ' . $languageDate->language_date($childDb->pers_death_date) . ')';
                            }
                            $this->html_output .= '</li>';
                            $this->html_output .= '</ul>';
                        }
                    }
                    $this->html_output .= "\n";
                    $childnr++;
                }
            }

            $this->html_output .= '</ul>';
        } // Show  multiple marriages
    }

    public function getHtmlOutput(): string
    {
        return $this->html_output;
    }
}

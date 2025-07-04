<?php

/**
 * First version made by Yossi Beck.
 * $generation_number = generation number to process.
 * $nr_generations = maximum number of generations.
 *
 *  Descendant array:
 *  person                  descendant[1]
 *  child1                  descendant[2]
 *  child2                  descendant[3]
 *
 *  children of child1:
 *  child1                  descendant[4]
 *  child2                  descendant[5]
 *
 * April 2015 Huub Mons: created a general ancestors - descendants functions script.
 * Jan. 2024: at this moment this script is only used in editorModel.php to add a colour for descendants.
 * Sept. 2024: also used in maps script.
 * 
 * TODO: use this function for multiple scripts.
 */

class Descendants
{
    private $generation_number = 0;
    private $descendant_id = 0;
    private $descendant_array = [];

    public function get_descendant_id()
    {
        return $this->descendant_id;
    }

    public function get_descendants($family_id, $main_person, $nr_generations)
    {
        global $db_functions;

        // *** Selected person ***
        $this->descendant_id++;
        $this->descendant_array[$this->descendant_id] = $main_person;
        $this->generation_number++;
        if ($nr_generations < $this->generation_number) {
            return;
        }

        // TODO check this function. Could be improved.

        // *** Count marriages of main person (man) ***
        $familyDb = $db_functions->get_family($family_id, 'man-woman');
        $parent1 = '';
        // *** Standard main_person is the father ***
        if ($familyDb->fam_man) {
            $parent1 = $familyDb->fam_man;
        }
        // *** If mother is selected, mother will be main_person ***
        if ($familyDb->fam_woman == $main_person) {
            $parent1 = $familyDb->fam_woman;
        }

        // *** Check family with parent1: N.N. ***
        $nr_families = 0;
        if ($parent1) {
            // *** Save family of person in array ***
            $personDb = $db_functions->get_person($parent1, 'famc-fams');
            $marriage_array = explode(";", $personDb->pers_fams);
            $nr_families = substr_count($personDb->pers_fams, ";");
        }

        // *** Loop multiple marriages of main_person ***
        for ($parent1_marr = 0; $parent1_marr <= $nr_families; $parent1_marr++) {
            $familyDb = $db_functions->get_family($marriage_array[$parent1_marr]);
            // *** Progen: onecht kind, vrouw zonder man ***
            //if ($familyDb->fam_kind!='PRO-GEN'){
            //  $family_nr++;
            //}

            /**
             * Children
             */
            if ($familyDb->fam_children) {
                $child_array = explode(";", $familyDb->fam_children);
                foreach ($child_array as $i => $value) {
                    $childDb = $db_functions->get_person($child_array[$i], 'famc-fams');
                    if ($childDb->pers_fams) {
                        // *** 1st family of child ***
                        $child_family = explode(";", $childDb->pers_fams);
                        $child1stfam = $child_family[0];
                        // *** Recursive, process ancestors of child ***
                        $this->get_descendants($child1stfam, $child_array[$i], $nr_generations);
                    } else {
                        // *** Child without own family ***
                        $this->descendant_id++;
                        //$this->descendant_array[$this->descendant_id] = $childDb->pers_gedcomnumber;
                        $this->descendant_array[$this->descendant_id] = $child_array[$i];
                        //if($nr_generations>=$this->generation_number) {
                        //	$childgn=$this->generation_number+1;
                        //}
                    }
                }
            }
        }
        return $this->descendant_array;
    }
}

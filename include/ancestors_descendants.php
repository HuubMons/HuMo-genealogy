<?php

/*
/* Functions get_ancestors and get_descendants.
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
 * Jan. 2024: at this moment these scripts are only used in editorModel.php to add a colour for ancestors/ descendants.
 * Sept. 2024: also used in maps script.
 * TODO: use these functions for multiple descendant/ ascendant scripts.
 */

$generation_number = 0;
$descendant_id = 0;
function get_descendants($family_id, $main_person, $generation_number, $nr_generations)
{
    global $dbh, $db_functions, $descendant_id, $descendant_array;

    // *** Selected person ***
    $descendant_id++;
    $descendant_array[$descendant_id] = $main_person;
    $generation_number++;
    if ($nr_generations < $generation_number) {
        return;
    }

    // TODO check this function. Could be improved.

    // *** Count marriages of main person (man) ***
    $familyDb = $db_functions->get_family($family_id, 'man-woman');
    $parent1 = '';
    //$parent2 = '';
    //$swap_parent1_parent2 = false;
    // *** Standard main_person is the father ***
    if ($familyDb->fam_man) {
        $parent1 = $familyDb->fam_man;
    }
    // *** If mother is selected, mother will be main_person ***
    if ($familyDb->fam_woman == $main_person) {
        $parent1 = $familyDb->fam_woman;
        //$swap_parent1_parent2 = true;
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
        //if ($familyDb->fam_kind!='PRO-GEN') $family_nr++;

        // *************************************************************
        // *** Children                                              ***
        // *************************************************************
        if ($familyDb->fam_children) {
            $child_array = explode(";", $familyDb->fam_children);
            foreach ($child_array as $i => $value) {
                //$childDb = $db_functions->get_person($child_array[$i]);
                $childDb = $db_functions->get_person($child_array[$i], 'famc-fams');
                if ($childDb->pers_fams) {
                    // *** 1st family of child ***
                    $child_family = explode(";", $childDb->pers_fams);
                    $child1stfam = $child_family[0];
                    // *** Recursive, process ancestors of child ***
                    //get_descendants($child1stfam, $childDb->pers_gedcomnumber, $generation_number, $nr_generations);
                    get_descendants($child1stfam, $child_array[$i], $generation_number, $nr_generations);
                } else {    // *** Child without own family ***
                    $descendant_id++;
                    //$descendant_array[$descendant_id] = $childDb->pers_gedcomnumber;
                    $descendant_array[$descendant_id] = $child_array[$i];
                    //if($nr_generations>=$generation_number) {
                    //	$childgn=$generation_number+1;
                    //}
                }
            }
        }
    }
}

/*
 * Original function used for ancestor sheet, made by Yossi.
 * April 2015 Huub Mons: added this function in general ancestor/ descendant function script.
 * Jan. 2024 Huub Mons: improved function.
 * 
 * father4	mother5		father6	mother7		ancestor[4]		ancestor[5]		ancestor[6]		ancestor[7]
 * father2				mother3				ancestor[2]						ancestor[3]
 * person									ancestor[1]
 *
*/
function get_ancestors($db_functions, $main_person)
{
    $ancestor_array = array();

    // *** person 1 ***
    $personDb = $db_functions->get_person($main_person, 'famc-fams');
    // *** Get parents ***
    if ($personDb->pers_famc) {
        $parentDb = $db_functions->get_family($personDb->pers_famc, 'man-woman');
        $ancestor_array[2] = $parentDb->fam_man;
        $ancestor_array[3] = $parentDb->fam_woman;
    }
    // end of person 1

    // Loop to find person data
    $count_max = 4; // *** Start with value 4, can be raised in loop ***

    for ($counter = 2; $counter < $count_max; $counter++) {
        if (isset($ancestor_array[$counter])) {
            $personDb = $db_functions->get_person($ancestor_array[$counter], 'famc-fams');
            // *** Get parents ***
            if (isset($personDb->pers_famc) && $personDb->pers_famc) {
                $father_counter = $counter * 2;
                $mother_counter = $father_counter + 1;
                $parentDb = $db_functions->get_family($personDb->pers_famc, 'man-woman');

                // *** Check if man is in array allready ***
                if (!in_array($parentDb->fam_man, $ancestor_array)) {
                    $ancestor_array[$father_counter] = $parentDb->fam_man;
                    if ($father_counter > $count_max) {
                        $count_max = $father_counter;
                    }
                }

                // *** Check if woman is in array allready ***
                if (!in_array($parentDb->fam_woman, $ancestor_array)) {
                    $ancestor_array[$mother_counter] = $parentDb->fam_woman;
                    if ($mother_counter > $count_max) {
                        $count_max = $mother_counter;
                    }
                }
            }
        }
    }
    return $ancestor_array;
}

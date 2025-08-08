<?php

/**
 * Original function used for ancestor sheet, made by Yossi.
 * April 2015 Huub Mons: added this function in general ancestor function script.
 * Jan. 2024 Huub Mons: improved function.
 *
 * ancestor[4] = father4   ancestor[5] = mother5   ancestor[6] = father6   ancestor[7] = mother7
 *                         ancestor[2] = father2   ancestor[3] = mother3
 *                                      ancestor[1] = person
 *
 * TODO: use this function for multiple scripts.
 */

namespace Genealogy\Include;

class Ancestors
{
    public function get_ancestors($db_functions, $main_person): array
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
}

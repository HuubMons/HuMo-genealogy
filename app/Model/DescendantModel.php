<?php

/**
 * Descendant model needs some parts of family model.
 */

namespace Genealogy\App\Model;

use Genealogy\App\Model\FamilyModel;
use Genealogy\Include\PersonPrivacy;
use Genealogy\Include\PersonName;
use Genealogy\Include\MarriageCls;
use Genealogy\Include\ShowSourcesFootnotes;

class DescendantModel extends FamilyModel
{
    private $hsize, $vdist, $vsize, $hdist, $hourglass;

    public function getDNA(): string
    {
        $dna = "none"; // DNA setting
        if (isset($_GET["dnachart"])) {
            $dna = $_GET["dnachart"];
        }
        if (isset($_POST["dnachart"])) {
            $dna = $_POST["dnachart"];
        }
        return $dna;
    }

    public function getChosengen($dna): string
    {
        $chosengen = 4;
        if ($dna != "none") {
            $chosengen = "All";
        } // in DNA chart by default show all generations
        if (isset($_GET["chosengen"])) {
            $chosengen = $_GET["chosengen"];
        }
        if (isset($_POST["chosengen"])) {
            $chosengen = $_POST["chosengen"];
        }
        return $chosengen;
    }

    public function getChosengenanc(): int
    {
        $chosengenanc = 4;  // for hourglass -- no. of generations of ancestors
        if (isset($_GET["chosengenanc"])) {
            $chosengenanc = $_GET["chosengenanc"];
        }
        if (isset($_POST["chosengenanc"])) {
            $chosengenanc = $_POST["chosengenanc"];
        }
        return $chosengenanc;
    }

    public function getSize($dna): int
    {
        if (isset($_SESSION['chartsize'])) {
            $size = $_SESSION['chartsize'];
        } else {
            $size = 50;
            // in DNA chart by default zoom position 4
            if ($dna != "none") {
                $size = 25;
            }
        }
        if (isset($_GET["chosensize"])) {
            $size = $_GET["chosensize"];
        }
        if (isset($_POST["chosensize"])) {
            $size = $_POST["chosensize"];
        }
        $_SESSION['chartsize'] = $size;
        return $size;
    }

    public function getDirection(): int
    {
        $direction = 0; // vertical
        if (isset($_GET["direction"])) {
            $direction = $_GET["direction"];
        }
        if (isset($_POST["direction"])) {
            $direction = $_POST["direction"];
        }

        // *** Change direction for hourglass ***
        if ($this->hourglass) {
            $direction = 1;
        }

        return $direction;
    }

    public function getHsize()
    {
        return $this->hsize;
    }

    public function getVdist()
    {
        return $this->vdist;
    }

    public function getVsize()
    {
        return $this->vsize;
    }

    public function getHdist()
    {
        return $this->hdist;
    }

    public function setHourglass($hourglass): void
    {
        $this->hourglass = $hourglass;
    }

    public function getBasepersonfamc($dna)
    {
        $base_person_famc = '';
        if ($dna != "none") {
            if (isset($_GET["bf"])) {
                $base_person_famc = $_GET["bf"];
            }
            if (isset($_POST["bf"])) {
                $base_person_famc = $_POST["bf"];
            }
            return $base_person_famc;
        }
        return null;
    }

    public function getBasepersonsexe($dna)
    {
        $base_person_sexe = '';
        if ($dna != "none") {
            if (isset($_GET["bs"])) {
                $base_person_sexe = $_GET["bs"];
            }
            if (isset($_POST["bs"])) {
                $base_person_sexe = $_POST["bs"];
            }
            return $base_person_sexe;
        }
        return null;
    }

    public function getBasepersonname($dna)
    {
        $base_person_name = '';
        if ($dna != "none") {
            if (isset($_GET["bn"])) {
                $base_person_name = $_GET["bn"];
            }
            if (isset($_POST["bn"])) {
                $base_person_name = $_POST["bn"];
            }
            return $base_person_name;
        }
        return null;
    }

    public function getBasepersongednr($dna)
    {
        $base_person_gednr = '';
        if ($dna != "none") {
            if (isset($_GET["bg"])) {
                $base_person_gednr = $_GET["bg"];
            }
            if (isset($_POST["bg"])) {
                $base_person_gednr = $_POST["bg"];
            }
            return $base_person_gednr;
        }
        return null;
    }

    public function getBasePerson($main_person)
    {
        $dnaDb = $this->db_functions->get_person($main_person);

        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();

        $privacy = $personPrivacy->get_privacy($dnaDb);
        $dnaname = $personName->get_person_name($dnaDb, $privacy);

        // need these 4 in report_descendant
        $base_person["name"] =  $dnaname["standard_name"];
        $base_person["sexe"] = $dnaDb->pers_sexe;
        $base_person["famc"] = $dnaDb->pers_famc;
        $base_person["gednr"] = $dnaDb->pers_gedcomnumber;
        return $base_person;
    }

    function Prepare_genarray($data)
    {
        // At this moment these globals are needed to process personData and marriage_cls.
        global $data, $parent1Db;

        $personName = new PersonName();
        $personPrivacy = new PersonPrivacy();
        $showSourcesFootnotes = new ShowSourcesFootnotes();

        $family_nr = 1;  // *** process multiple families ***
        $dna = $this->getDNA();
        $data["main_person"] = $this->getMainPerson();
        $data["family_id"] = $this->getFamilyId();
        $chosengen = $this->getChosengen($dna);

        //$descendant_report = $this->getDescendantReport();
        $descendant_report = true;

        $data["text_presentation"] = $this->getTextPresentation();
        $data["picture_presentation"] = $this->getPicturePresentation();

        // *** Check if family gedcomnumber is valid ***
        $this->db_functions->check_family($data["family_id"]);

        // *** Check if person gedcomnumber is valid ***
        $this->db_functions->check_person($data["main_person"]);

        $genarray = array();

        // DNA chart -> change base person to earliest father-line (Y-DNA) or mother-line (Mt-DNA) ancestor
        $max_generation = 100;

        $dnaDb = $this->db_functions->get_person($data["main_person"]);
        /*
        $privacy = $personPrivacy->get_privacy($dnaDb);
        $dnaname = $personName->get_person_name($dnaDb, $privacy);
        $base_person_name =  $dnaname["standard_name"];    // need these 4 in report_descendant
        $base_person_sexe = $dnaDb->pers_sexe;
        $base_person_famc = $dnaDb->pers_famc;
        $base_person_gednr = $dnaDb->pers_gedcomnumber;
        */

        if ($dna == "ydna" || $dna == "ydnamark") {
            while (isset($dnaDb->pers_famc) && $dnaDb->pers_famc != "") {
                $dnaparDb = $this->db_functions->get_family($dnaDb->pers_famc);
                if ($dnaparDb->fam_man == "") {
                    break;
                } else {
                    $data["main_person"] = $dnaparDb->fam_man;
                    $data["family_id"]  = $dnaDb->pers_famc;
                    $dnaDb = $this->db_functions->get_person($dnaparDb->fam_man);
                }
            }
        }
        if ($dna == "mtdna" || $dna == "mtdnamark") {
            while (isset($dnaDb->pers_famc) && $dnaDb->pers_famc != "") {
                $dnaparDb = $this->db_functions->get_family($dnaDb->pers_famc);
                if ($dnaparDb->fam_woman == "") {
                    break;
                } else {
                    $data["main_person"] = $dnaparDb->fam_woman;
                    $data["family_id"]  = $dnaDb->pers_famc;
                    $dnaDb = $this->db_functions->get_person($dnaparDb->fam_woman);
                }
            }
        }

        // *******************
        // *** Show family ***
        // *******************
        if ($data["family_id"]) {
            $descendant_family_id2[] = $data["family_id"];
            $descendant_main_person2[] = $data["main_person"];

            $arraynr = 0;

            // *** Nr. of generations ***
            $max_generation = $chosengen != "All" ? $chosengen - 2 : 100; // any impossibly high number, will anyway stop at last generation

            for ($descendant_loop = 0; $descendant_loop <= $max_generation; $descendant_loop++) {
                $descendant_family_id2[] = 0;
                $descendant_main_person2[] = 0;
                if (!isset($descendant_family_id2[1])) {
                    break;
                }

                // TEST code (only works with family, will give error in descendant report and DNA reports:
                // if (!isset($descendant_family_id2[0])){
                //  break;
                // }

                // *** Copy array ***
                unset($descendant_family_id);
                $descendant_family_id = $descendant_family_id2;
                unset($descendant_family_id2);

                unset($descendant_main_person);
                $descendant_main_person = $descendant_main_person2;
                unset($descendant_main_person2);

                if ($descendant_loop != 0) {
                    if (isset($genarray[$arraynr])) {
                        $temppar = $genarray[$arraynr]["par"];
                    }
                    while (isset($genarray[$temppar]["gen"]) && $genarray[$temppar]["gen"] === $descendant_loop - 1) {
                        //$lst_in_array += $genarray[$temppar]["nrc"];
                        if (isset($lst_in_array)) {
                            $lst_in_array += $genarray[$temppar]["nrc"];
                        }
                        $temppar++;
                    }
                }
                $nrchldingen = 0;

                // *** Nr of families in one generation ***
                $nr_families = count($descendant_family_id);
                for ($descendant_loop2 = 0; $descendant_loop2 < $nr_families; $descendant_loop2++) {
                    while (
                        isset($genarray[$arraynr]["non"]) && $genarray[$arraynr]["non"] == 1 && isset($genarray[$arraynr]["gen"]) && $genarray[$arraynr]["gen"] === $descendant_loop
                    ) {
                        $genarray[$arraynr]["nrc"] = 0;
                        $arraynr++;
                    }

                    // Original code:
                    //if ($descendant_family_id[$descendant_loop2]==''){ break; }
                    if ($descendant_family_id[$descendant_loop2] == '0') {
                        break;
                    }

                    $family_id_loop = $descendant_family_id[$descendant_loop2];
                    $data["main_person"] = $descendant_main_person[$descendant_loop2];
                    $family_nr = 1;

                    // *** Count marriages of man ***
                    $familyDb = $this->db_functions->get_family($family_id_loop);
                    $parent1 = '';
                    $parent2 = '';
                    $swap_parent1_parent2 = false;
                    // *** Standard main person is the father ***
                    if ($familyDb->fam_man) {
                        $parent1 = $familyDb->fam_man;
                    }
                    // *** After clicking the mother, the mother is main person ***
                    if ($familyDb->fam_woman == $data["main_person"]) {
                        $parent1 = $familyDb->fam_woman;
                        $swap_parent1_parent2 = true;
                    }

                    // *** Check for parent1: N.N. ***
                    if ($parent1) {
                        // *** Save parent1 families in array ***
                        $personDb = $this->db_functions->get_person($parent1);
                        $marriage_array = explode(";", $personDb->pers_fams);
                        $count_marr = substr_count($personDb->pers_fams, ";");
                    } else {
                        $marriage_array[0] = $family_id_loop;
                        $count_marr = "0";
                    }

                    // *** Loop multiple marriages of main_person ***
                    for ($parent1_marr = 0; $parent1_marr <= $count_marr; $parent1_marr++) {
                        $id = $marriage_array[$parent1_marr];
                        $familyDb = $this->db_functions->get_family($id);

                        // Oct. 2021 New method:
                        if ($swap_parent1_parent2 == true) {
                            $parent1 = $familyDb->fam_woman;
                            $parent2 = $familyDb->fam_man;
                        } else {
                            $parent1 = $familyDb->fam_man;
                            $parent2 = $familyDb->fam_woman;
                        }
                        $parent1Db = $this->db_functions->get_person($parent1);
                        $parent1_privacy = $personPrivacy->get_privacy($parent1Db);

                        $parent2Db = $this->db_functions->get_person($parent2);
                        $parent2_privacy = $personPrivacy->get_privacy($parent2Db);

                        $marriage_cls = new MarriageCls($familyDb, $parent1_privacy, $parent2_privacy);
                        $family_privacy = $marriage_cls->get_privacy();


                        /**
                         * Parent1 (normally the father)
                         */
                        if ($familyDb->fam_kind != 'PRO-GEN') {  //onecht kind, woman without man
                            if ($family_nr == 1) {
                                //*** Show data of parent1 ***
                                if ($descendant_loop == 0) {
                                    $privacy = $personPrivacy->get_privacy($parent1Db);
                                    $name = $personName->get_person_name($parent1Db, $privacy);
                                    $genarray[$arraynr]["nam"] = $name["standard_name"];
                                    if (isset($name["colour_mark"])) {
                                        $genarray[$arraynr]["nam"] .= $name["colour_mark"];
                                    }
                                    $genarray[$arraynr]["init"] = $name["initials"];
                                    $genarray[$arraynr]["short"] = $name["short_firstname"];
                                    $genarray[$arraynr]["fams"] = $id;
                                    if (isset($parent1Db->pers_gedcomnumber)) {
                                        $genarray[$arraynr]["gednr"] = $parent1Db->pers_gedcomnumber;
                                    }
                                    $genarray[$arraynr]["2nd"] = 0;

                                    if ($swap_parent1_parent2 == true) {
                                        $genarray[$arraynr]["sex"] = "v";
                                        $genarray[$arraynr]["dna"] = $dna == "mtdnamark" || $dna == "mtdna" ? 1 : "no";
                                    } else {
                                        $genarray[$arraynr]["sex"] = "m";
                                        $genarray[$arraynr]["dna"] = $dna == "ydnamark" || $dna == "ydna" || $dna == "mtdnamark" || $dna == "mtdna" ? 1 : "no";
                                    }
                                }
                                //$family_nr++;
                            } else {
                                // *** Show standard marriage text and name in 2nd, 3rd, etc. marriage ***
                                if ($descendant_loop == 0) {
                                    $genarray[$arraynr] = $genarray[$arraynr - 1];
                                    $genarray[$arraynr]["2nd"] = 1;
                                    //$genarray[$arraynr]["fams"]=$id;
                                }
                                $genarray[$arraynr]["huw"] = $marriage_cls->marriage_data($familyDb, $family_nr, 'shorter');
                                $genarray[$arraynr]["fams"] = $id;
                            }
                            $family_nr++;
                        }


                        /**
                         * Marriage
                         */
                        if ($familyDb->fam_kind != 'PRO-GEN') {  // onecht kind, wife without man
                            // *** Check if marriage data must be hidden (also hidden if privacy filter is active) ***
                            if (
                                $this->user["group_pers_hide_totally_act"] == 'j' && isset($parent1Db->pers_own_code) && strpos(' ' . $parent1Db->pers_own_code, $this->user["group_pers_hide_totally"]) > 0
                            ) {
                                $family_privacy = true;
                            }
                            if (
                                $this->user["group_pers_hide_totally_act"] == 'j' && isset($parent2Db->pers_own_code) && strpos(' ' . $parent2Db->pers_own_code, $this->user["group_pers_hide_totally"]) > 0
                            ) {
                                $family_privacy = true;
                            }

                            if ($family_privacy) {
                                $genarray[$arraynr]["htx"] = $marriage_cls->marriage_data($familyDb, '', 'short');
                            } else {
                                $genarray[$arraynr]["htx"] = $marriage_cls->marriage_data();
                            }
                        }

                        /**
                         * Parent2 (normally the mother)
                         */
                        if ($parent2Db) {
                            $privacy = $personPrivacy->get_privacy($parent2Db);
                            $name = $personName->get_person_name($parent2Db, $privacy);
                            $genarray[$arraynr]["sps"] = $name["standard_name"];
                            $genarray[$arraynr]["spgednr"] = $parent2Db->pers_gedcomnumber;
                        } else {
                            $genarray[$arraynr]["sps"] = __('Unknown');
                            $genarray[$arraynr]["spgednr"] = ''; // this is a non existing NN spouse!
                        }
                        $genarray[$arraynr]["spfams"] = $id;


                        /**
                         * Marriagetext
                         */
                        $temp = '';

                        if ($descendant_loop == 0) {
                            $lst_in_array = $count_marr;
                            $genarray[$arraynr]["gen"] = 0;
                            $genarray[$arraynr]["par"] = -1;
                            $genarray[$arraynr]["chd"] = $arraynr + 1;
                            $genarray[$arraynr]["non"] = 0;
                        }

                        /**
                         * Children
                         */
                        if (!$familyDb->fam_children) {
                            $genarray[$arraynr]["nrc"] = 0;
                        }

                        if ($familyDb->fam_children) {
                            $childnr = 1;
                            $child_array = explode(";", $familyDb->fam_children);

                            $genarray[$arraynr]["nrc"] = count($child_array);
                            // dna -> count only man or women
                            if ($dna == "ydna" || $dna == "mtdna") {
                                $countdna = 0;
                                foreach ($child_array as $i => $value) {
                                    $childDb = $this->db_functions->get_person($child_array[$i]);
                                    if ($dna == "ydna" and $childDb->pers_sexe == "M" and $genarray[$arraynr]["sex"] == "m" and $genarray[$arraynr]["dna"] == 1) $countdna++;
                                    elseif ($dna == "mtdna" and $genarray[$arraynr]["sex"] == "v" and $genarray[$arraynr]["dna"] == 1) $countdna++;
                                }
                                $genarray[$arraynr]["nrc"] = $countdna;
                            }

                            $show_privacy_text = false;
                            foreach ($child_array as $i => $value) {
                                $childDb = $this->db_functions->get_person($child_array[$i]);

                                $chdn_in_gen = $nrchldingen + $childnr;
                                $place = $lst_in_array + $chdn_in_gen;

                                //if (isset($genarray[$arraynr]["sex"]) AND isset($genarray[$arraynr]["dna"] )){
                                if (($dna == "ydnamark" || $dna == "ydna") && $childDb->pers_sexe == "M" && $genarray[$arraynr]["sex"] == "m" && $genarray[$arraynr]["dna"] == 1
                                ) {
                                    $genarray[$place]["dna"] = 1;
                                } elseif (($dna == "mtdnamark" || $dna == "mtdna") && $genarray[$arraynr]["sex"] == "v" && $genarray[$arraynr]["dna"] == 1) {
                                    $genarray[$place]["dna"] = 1;
                                } elseif ($dna == "ydna" || $dna == "mtdna") {
                                    continue;
                                } else {
                                    $genarray[$place]["dna"] = "no";
                                }
                                //}

                                $genarray[$place]["gen"] = $descendant_loop + 1;
                                $genarray[$place]["par"] = $arraynr;
                                $genarray[$place]["chd"] = $childnr;
                                $genarray[$place]["non"] = 0;
                                $genarray[$place]["nrc"] = 0;
                                $genarray[$place]["2nd"] = 0;

                                $privacy = $personPrivacy->get_privacy($childDb);
                                $name = $personName->get_person_name($childDb, $privacy);
                                $genarray[$place]["nam"] = $name["standard_name"] . $name["colour_mark"];

                                $genarray[$place]["init"] = $name["initials"];
                                $genarray[$place]["short"] = $name["short_firstname"];
                                $genarray[$place]["gednr"] = $childDb->pers_gedcomnumber;
                                if ($childDb->pers_fams) {
                                    $childfam = explode(";", $childDb->pers_fams);
                                    $genarray[$place]["fams"] = $childfam[0];
                                } else {
                                    $genarray[$place]["fams"] = $childDb->pers_famc;
                                }
                                $genarray[$place]["sex"] = $childDb->pers_sexe == "F" ? "v" : "m";

                                // *** Build descendant_report ***
                                if ($descendant_report && $childDb->pers_fams && $descendant_loop < $max_generation) {
                                    // *** 1st family of child ***
                                    $child_family = explode(";", $childDb->pers_fams);

                                    // *** Check for double families in descendant report (if a person relates or marries another person in the same family) ***
                                    if (isset($check_double) && in_array($child_family[0], $check_double)) {
                                        // *** Don't show this family, double... ***
                                    } else {
                                        $descendant_family_id2[] = $child_family[0];
                                    }

                                    if (count($child_family) > 1) {
                                        $counter = count($child_family);
                                        for ($k = 1; $k < $counter; $k++) {
                                            $childnr++;
                                            $thisplace = $place + $k;
                                            $genarray[$thisplace] = $genarray[$place];
                                            $genarray[$thisplace]["chd"] = $childnr;
                                            $genarray[$thisplace]["2nd"] = 1;
                                            $genarray[$arraynr]["nrc"] += 1;
                                        }
                                    }

                                    // *** YB: show children first in descendant_report ***
                                    $descendant_main_person2[] = $childDb->pers_gedcomnumber;
                                } else {
                                    $genarray[$place]["non"] = 1;
                                }

                                $childnr++;
                            }
                            $nrchldingen += ($childnr - 1);
                        }

                        $arraynr++;
                    } // Show multiple marriages

                } // Multiple families in 1 generation

            } // nr. of generations
        } // End of single person

        // *** If source footnotes are selected, show them here ***
        if (isset($_SESSION['save_source_presentation']) && $_SESSION['save_source_presentation'] == 'footnote') {
            echo $showSourcesFootnotes->show_sources_footnotes();
        }

        return $genarray;
    }





    /**
     *	-------------------------------------------------------------------------
     *	|   REPORT_DESCENDANT                                                   |
     *	|   for use with the $genarray generated in HuMo-genealogy              |
     *	|   Original starfield plotting code by Yossi Beck - Feb-March 2010     |
     *	|   Copyright GPL_GNU licence                                           |
     *	-------------------------------------------------------------------------
     *
     *	meaning of $genarray members:
     *	"par" = array nr of parent
     *	"nrc" = nr of children (children with multiple marriages are counted as additional children for plotting's sake
     *	"gen" = nr of the generation
     *	"posx" = the x position of top left corner of a person's square
     *	"posy" = the y position of top left corner of a person's square
     *	"fst" = the x position of first (lefmost) child
     *	"lst" = the x position of last (rightmost) child, unless this is a second marriage of this child,
     *	        in which case the first marriage of the last child is entered into "lst"
     *	"chd" = the number of the child in the family (additional marriages have subsequent numbers)
     *	"2nd" = indicates this person is in fact a second or following instance of the previous person with additional marriage
     *	"htx" = wedding text ("married on 13 mar 1930 to:")
     *	"huw" = mentioning of additional marriage ("2nd marriage")
     *	"sex" = sex of the person
     *	"nam" = name of the person
     *	"sps" = name of spouse
     *	"fams"  = GEDCOM family number (F345)
     *	"gednr" = GEDCOM person number (I143)
     *	"non" = person with no own family (i.e. only child status)
     */

    // *** This script is also used in hourglass ***

    /**
     * 1st Part:  CODE TO GENERATE THE STARFIELD CHART FROM $GENARRAY
     */
    function generate($genarray)
    {
        global $data;

        $dna = $this->getDNA();
        $size = $this->getSize($dna);
        $direction = $this->getDirection();

        $chosengenanc = $this->getChosengenanc();

        if ($direction == 0) { // if vertical
            if ($size == 50) {   // full size box with name and details
                $this->hsize = 150;
                $this->vsize = 75;
                $this->vdist = 80;
            } elseif ($size == 45) { // smaller box with name + popup
                $this->hsize = 100;
                $this->vsize = 45;
                $this->vdist = 60;
            } else {             // re-sizable box with no name, only popup
                $this->hsize = $size;
                $this->vsize = $size;
                $this->vdist = $size * 2;
            }

            $vbasesize = $this->vsize + $this->vdist;
            $inbetween = 10;   // horizontal distance between two persons in a family. Between fams is double $inbetween

            $movepar = 0;
            // flags the need to move parent box. 1 means: call move() function
            $counter = count($genarray);  // flags the need to move parent box. 1 means: call move() function

            for ($i = 0; $i < $counter; $i++) {
                if (!isset($genarray[$i])) {
                    break;
                }

                //$distance = 0;

                // *** last number seems to be height from previous div. ***
                $genarray[$i]["posy"] = ($genarray[$i]["gen"] * ($vbasesize)) + 40;
                $par = $genarray[$i]["par"];
                if ($genarray[$i]["chd"] == 1) {   // the first child in this fam
                    if ($genarray[$i]["gen"] == 0) {  // this is base person - put in left most position
                        $genarray[$i]["posx"] = 0;
                    } else { // first child in fam in 2nd or following generation
                        $exponent = $genarray[$par]["nrc"] - 1; // exponent is number of additional children
                        //if (isset($genarray[$i]["posx"]))
                        $genarray[$i]["posx"] = $genarray[$par]["posx"] - (($exponent * ($this->hsize + $inbetween)) / 2); // place in proper spot under parent
                        //else
                        //$genarray[$i]["posx"] = (($exponent*($this->hsize+$inbetween))/2); // place in proper spot under parent

                        if ($genarray[$i]["gen"] == $genarray[$i - 1]["gen"]) { // is first child in fam but not in generation

                            if ($genarray[$i]["posx"] < $genarray[$i - 1]["posx"] + ($this->hsize + $inbetween * 2)) {
                                $genarray[$i]["posx"] = $genarray[$i - 1]["posx"] + ($this->hsize + $inbetween * 2);
                                $movepar = 1;
                            }
                        } else {  // is first child in generation. If it was set to minus 0, move it to 0 and call "move parents" function move()
                            //if (isset($genarray[$i]["posx"])){
                            if ($genarray[$i]["posx"] < 0) {
                                $genarray[$i]["posx"] = 0;
                                $movepar = 1;
                            }
                            //}
                        }
                        //if (isset($genarray[$i]["posx"]))
                        $genarray[$par]["fst"] = $genarray[$i]["posx"];    // x of first child in fam
                    }
                } else {
                    //if (isset($genarray[$i]["posx"]))
                    $genarray[$i]["posx"] = $genarray[$i - 1]["posx"] + ($this->hsize + $inbetween);
                }

                $z = $i;
                if ($genarray[$z]["gen"] != 0 && $genarray[$z]["chd"] == $genarray[$par]["nrc"]) {

                    while ($genarray[$z]["2nd"] == 1) {
                        $z--;
                    }

                    $genarray[$par]["lst"] = $genarray[$z]["posx"];
                    if ($genarray[$z]["gen"] > $genarray[$z - 1]["gen"] && $genarray[$par]["lst"] == $genarray[$par]["fst"]) {
                        // this person is first in generation and is only child - move directly under parent
                        $genarray[$z]["posx"] = $genarray[$par]["posx"];
                        while (isset($genarray[$z + 1]) && $genarray[$z + 1]["2nd"] == 1) {
                            $genarray[$z + 1]["posx"] = $genarray[$z]["posx"] + $this->hsize + $inbetween;
                            $z++;
                        }
                        $genarray[$par]["fst"] = $genarray[$par]["posx"];
                    } elseif ($movepar == 1) {
                        $movepar = 0;
                        //move($par);
                        $genarray = $this->move($par, $genarray);
                    }
                }
            }    // end for loop

        } // end if vertical

        else {  // horizontal
            if ($size == 50) {   // full size box with name and details
                $this->hsize = 150;
                if ($this->hourglass === true) {
                    $this->hsize = 170;
                }
                $this->vsize = 75;
                $this->hdist = 60;
                if ($this->hourglass === true) {
                    $this->hdist = 30;
                }
            } elseif ($size == 45) { // smaller box with name + popup
                $this->hsize = 100;
                $this->vsize = 45;
                $this->hdist = 50;
            } else {             // re-sizable box with no name, first 4 with initials + popup, rest only popup
                $this->hsize = $size;
                $this->vsize = $size;
                $this->hdist = $size;
                if ($size < 15) {
                    $this->hdist = 15;
                } // shorter than this doesn't look good
            }

            $hbasesize = $this->hsize + $this->hdist;
            $vinbetween = 10;   // vertical distance between two persons in a family. Between fams is double $inbetween

            $movepar = 0;
            // flags the need to move parent box. 1 means: call move() function
            $counter = count($genarray);  // flags the need to move parent box. 1 means: call move() function

            for ($i = 0; $i < $counter; $i++) {
                if (!isset($genarray[$i])) {
                    break;
                }

                //$distance = 0;

                $genarray[$i]["posx"] = ($genarray[$i]["gen"] * $hbasesize) + 1;

                if ($this->hourglass === true) {
                    // *** Calculate left position for hourglass (depends on number of ancestor generations chosen) ***
                    if ($size == 50) {
                        $thissize = 170;
                    } elseif ($size == 45) {
                        $thissize = 100;
                    } else {
                        $thissize = $size;
                    }

                    $left = 30 + $thissize; // default when 2 generations only
                    if ($chosengenanc == 3 && $size == 50 && $genarray[1]["2nd"] == 1) {
                        // prevent parent overlap by 2nd marr of base person in 3 gen display
                        $left = 10 + (2 * (20 + $thissize)) + (($chosengenanc - 3) * (($thissize / 2) + 20));
                    } elseif ($chosengenanc > 2) {
                        if ($size == 50) {
                            $left = 10 + (2 * $thissize) + (($chosengenanc - 3) * (($thissize / 2) + 20));
                        } elseif ($size == 45) {
                            $left = 10 + (2 * (20 + $thissize)) + (($chosengenanc - 3) * (($thissize / 2) + 20));
                        } elseif ($size < 45) {
                            $left = 10 + (($chosengenanc - 1) * ($size + 20));
                        }
                    }

                    $genarray[$i]["posx"] = ($genarray[$i]["gen"] * $hbasesize) + $left;
                }
                $par = $genarray[$i]["par"];
                if ($genarray[$i]["chd"] == 1) {
                    if ($genarray[$i]["gen"] == 0) {
                        $genarray[$i]["posy"] = 40;
                    } else {
                        $exponent = $genarray[$par]["nrc"] - 1;

                        $genarray[$i]["posy"] = $genarray[$par]["posy"] -  (($exponent * ($this->vsize + $vinbetween)) / 2);

                        if ($genarray[$i]["gen"] == $genarray[$i - 1]["gen"]) {
                            if ($genarray[$i]["posy"] < $genarray[$i - 1]["posy"] + ($this->vsize + $vinbetween * 2)) {
                                $genarray[$i]["posy"] = $genarray[$i - 1]["posy"] + ($this->vsize + $vinbetween * 2);
                                $movepar = 1;
                            }
                        } elseif ($genarray[$i]["posy"] < 40) {
                            $genarray[$i]["posy"] = 40;
                            $movepar = 1;
                        }
                        $genarray[$par]["fst"] = $genarray[$i]["posy"];       // y of first child in fam
                    }
                } else {
                    $genarray[$i]["posy"] = $genarray[$i - 1]["posy"] + ($this->vsize + $vinbetween);
                }

                $z = $i;
                if ($genarray[$z]["gen"] != 0 && $genarray[$z]["chd"] == $genarray[$par]["nrc"]) {
                    while ($genarray[$z]["2nd"] == 1) {
                        $z--;
                    }

                    $genarray[$par]["lst"] = $genarray[$z]["posy"];
                    if ($genarray[$z]["gen"] > $genarray[$z - 1]["gen"] && $genarray[$par]["lst"] == $genarray[$par]["fst"]) {
                        // this person is first in generation and is only child - move directly under parent
                        $genarray[$z]["posy"] = $genarray[$par]["posy"];
                        // make this into while loop
                        while (isset($genarray[$z + 1]) && $genarray[$z + 1]["2nd"] == 1) {
                            $genarray[$z + 1]["posy"] = $genarray[$z]["posy"] + $this->vsize + $vinbetween;
                            $z++;
                        }
                        $genarray[$par]["fst"] = $genarray[$par]["posy"];
                    } elseif ($movepar == 1) {
                        $movepar = 0;
                        //move($par);
                        $genarray = $this->move($par, $genarray);
                    }
                }
            }    // end for loop

        }  // end if horizontal

        return $genarray;
    }

    /**
     * 2nd Part: RECURSIVE FUNCTION TO MOVE PART OF THE CHART WHEN NEW ITEMS ARE ADDED
     */
    function move($i, $genarray)
    {
        $direction = $this->getDirection();

        if ($direction == 0) { // if vertical
            $par = $genarray[$i]["par"];
            $tempx = $genarray[$i]["posx"];
            //if (isset($genarray[$i]["lst"]))
            $genarray[$i]["posx"] = ($genarray[$i]["fst"] + $genarray[$i]["lst"]) / 2;

            if ($genarray[$i]["gen"] != 0) {
                $q = $i;
                if ($genarray[$q]["chd"] == 1) {
                    $genarray[$par]["fst"] = $genarray[$q]["posx"];
                }
                if ($genarray[$q]["chd"] == $genarray[$par]["nrc"]) {
                    while ($genarray[$q]["2nd"] == 1) {
                        $q--;
                    }
                    $genarray[$par]["lst"] = $genarray[$q]["posx"];
                }
            }
            $distance = $genarray[$i]["posx"] - $tempx;

            $n = $i + 1;
            while ($genarray[$n]["gen"] == $genarray[$n - 1]["gen"]) {
                //		while(isset($genarray[$n]["gen"]) AND $genarray[$n]["gen"] == $genarray[$n-1]["gen"]) {
                if (isset($genarray[$n]["fst"]) && isset($genarray[$n]["lst"])) {
                    $tempx = $genarray[$n]["posx"];
                    $genarray[$n]["posx"] = ($genarray[$n]["fst"] + $genarray[$n]["lst"]) / 2;
                    $distance = $genarray[$n]["posx"] - $tempx;
                } else {
                    //if (isset($genarray[$n]["posx"]))
                    $genarray[$n]["posx"] += $distance;
                    //else
                    //    $genarray[$n]["posx"] = $distance;
                }
                if ($genarray[$n]["gen"] != 0) {
                    $c = $n;
                    $par = $genarray[$c]["par"];
                    if ($genarray[$c]["chd"] == 1) {
                        $genarray[$par]["fst"] = $genarray[$c]["posx"];
                    }
                    if ($genarray[$c]["chd"] == $genarray[$par]["nrc"]) {

                        while ($genarray[$c]["2nd"] == 1) {
                            // $c++;
                            $c--;
                        }

                        $genarray[$par]["lst"] = $genarray[$c]["posx"];
                    }
                }
                $n++;
            }
            if ($genarray[$i]["gen"] > 0) {
                $par = $genarray[$i]["par"];
                //move($par);
                $genarray = $this->move($par, $genarray);
            }
        } // end if vertical

        else { // if horizontal
            $par = $genarray[$i]["par"];
            $tempx = $genarray[$i]["posy"];
            $genarray[$i]["posy"] = ($genarray[$i]["fst"] + $genarray[$i]["lst"]) / 2;

            if ($genarray[$i]["gen"] != 0) {
                $q = $i;
                if ($genarray[$q]["chd"] == 1) {
                    $genarray[$par]["fst"] = $genarray[$q]["posy"];
                }
                if ($genarray[$q]["chd"] == $genarray[$par]["nrc"]) {
                    while ($genarray[$q]["2nd"] == 1) {
                        $q--;
                    }
                    $genarray[$par]["lst"] = $genarray[$q]["posy"];
                }
            }
            $distance = $genarray[$i]["posy"] - $tempx;

            $n = $i + 1;
            while ($genarray[$n]["gen"] == $genarray[$n - 1]["gen"]) {
                if (isset($genarray[$n]["fst"]) && isset($genarray[$n]["lst"])) {
                    $tempx = $genarray[$n]["posy"];
                    $genarray[$n]["posy"] = ($genarray[$n]["fst"] + $genarray[$n]["lst"]) / 2;
                    $distance = $genarray[$n]["posy"] - $tempx;
                } else {
                    $genarray[$n]["posy"] += $distance;
                }
                if ($genarray[$n]["gen"] != 0) {
                    $c = $n;
                    $par = $genarray[$c]["par"];
                    if ($genarray[$c]["chd"] == 1) {
                        $genarray[$par]["fst"] = $genarray[$c]["posy"];
                    }
                    if ($genarray[$c]["chd"] == $genarray[$par]["nrc"]) {

                        while ($genarray[$c]["2nd"] == 1) {
                            $c--;
                        }

                        $genarray[$par]["lst"] = $genarray[$c]["posy"];
                    }
                }
                $n++;
            }
            if ($genarray[$i]["gen"] > 0) {
                $par = $genarray[$i]["par"];
                //move($par);
                $genarray = $this->move($par, $genarray);
            }
        }  // end if horizontal
        return $genarray;
    }
}

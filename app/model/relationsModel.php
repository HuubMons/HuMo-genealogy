<?php
class RelationsModel extends BaseModel
{
    private $selected_language;

    private $search_name1 = '', $search_name2 = '';
    private $search_gednr1 = '', $search_gednr2 = '';
    private $start_calculation = false, $search_results = false;

    private $link1, $link2;
    private $language_is;
    private $bloodrel = false;
    private $bloodreltext = '';
    private $rel_arrayX, $rel_arrayY;
    private $rel_arrayspouseX, $rel_arrayspouseY;
    private $fams1_array, $fams2_array;

    // *** Extended search ***
    private $globaltrack, $globaltrack2;
    private $totalpath, $show_extended_message;

    private $count = 0;

    private $relation = [
        'person1' => '',
        'person2' => '',
        'name1' => '',
        'name2' => '',
        'gednr1' => '',
        'gednr2' => '',
        'sexe1' => '',
        'sexe2' => '',
        'fams1' => '',
        'fams2' => '',
        'family_id1' => '',
        'family_id2' => '',
        'foundX_nr' => '',
        'foundY_nr' => '',
        'foundX_gen' => '',
        'foundY_gen' => '',
        'foundX_match' => '',
        'foundY_match' => '',
        'spouse' => '',
        'famspouseX' => '',
        'famspouseY' => '',
        'spousenameX' => '',
        'spousenameY' => '',
        'double_spouse' => '0',
        'sexe1' => '',
        'sexe2' => '',
        'rel_text' => '',
        'rel_text_nor_dan' => '',
        'rel_text_nor_dan2' => '',
        'relation_type' => '',
        'special_spouseX' => '',
        'special_spouseY' => '',
        'dutch_text' => ''
    ];

    public function __construct($config, $selected_language)
    {
        parent::__construct($config);
        $this->selected_language = $selected_language;
    }

    public function resetValues(): void
    {
        // *** Reset values ***
        //if ( !isset($_POST["search1"]) && !isset($_POST["search2"]) && !isset($_POST["calculator"]) && !isset($_POST["switch"]) && !isset($_POST["extended"]) && !isset($_POST["next_path"]) && !isset($_GET['pers_id']) && !isset($_POST["search_id1"]) && !isset($_POST["search_id2"])) {
        if (!isset($_POST["calculator"]) && !isset($_POST["switch"]) && !isset($_POST["extended"]) && !isset($_POST["next_path"]) && !isset($_GET['pers_id']) && !isset($_POST["search_id1"]) && !isset($_POST["search_id2"])) {
            // No button pressed: this is a fresh entry from frontpage link: start clean search form
            //$_SESSION["search1"] = '';
            //$_SESSION["search2"] = '';
            $_SESSION['rel_search_name'] = '';
            $_SESSION['rel_search_name2'] = '';
            $_SESSION['rel_search_gednr'] = '';
            $_SESSION['rel_search_gednr2'] = '';
            unset($_SESSION["search_pers_id"]);
            unset($_SESSION["search_pers_id2"]);
        }
    }

    public function get_variables(): array
    {
        $relation = $this->relation;

        $relation['search_name1'] = $this->search_name1;
        $relation['search_name2'] = $this->search_name2;

        $relation["search_gednr1"] = $this->search_gednr1;
        $relation["search_gednr2"] = $this->search_gednr2;

        $relation['start_calculation'] = $this->start_calculation;
        $relation['search_results'] = $this->search_results;

        $relation['link1'] = $this->link1;
        $relation['link2'] = $this->link2;
        $relation['language_is'] = $this->language_is;
        $relation['bloodrel'] = $this->bloodrel;
        $relation['bloodreltext'] = $this->bloodreltext;
        $relation['rel_arrayX'] = $this->rel_arrayX;
        $relation['rel_arrayY'] = $this->rel_arrayY;

        $relation['fams1_array'] = $this->fams1_array;
        $relation['fams2_array'] = $this->fams2_array;

        // *** Extended search ***
        $relation['globaltrack'] = $this->globaltrack;
        $relation['globaltrack2'] = $this->globaltrack2;
        $relation['totalpath'] = $this->totalpath;
        $relation['show_extended_message'] = $this->show_extended_message;

        $relation['rel_arrayspouseX'] = $this->rel_arrayspouseX;
        $relation['rel_arrayspouseY'] = $this->rel_arrayspouseY;

        return $relation;
    }

    // *** Several variables used double. For standard and marital relationship calculator. ***
    public function get_variables_standard_extended(): array
    {
        // TODO jan. 2025 for now just add all variables to "standard_extended" array. Must be refactored.
        $relation['standard_extended'] = $this->relation;

        $relation['standard_extended']['search_name1'] = $this->search_name1;
        $relation['standard_extended']['search_name2'] = $this->search_name2;

        $relation['standard_extended']["search_gednr1"] = $this->search_gednr1;
        $relation['standard_extended']["search_gednr2"] = $this->search_gednr2;

        $relation['standard_extended']['start_calculation'] = $this->start_calculation;
        $relation['standard_extended']['search_results'] = $this->search_results;

        $relation['standard_extended']['link1'] = $this->link1;
        $relation['standard_extended']['link2'] = $this->link2;
        $relation['standard_extended']['language_is'] = $this->language_is;
        $relation['standard_extended']['bloodrel'] = $this->bloodrel;
        $relation['standard_extended']['bloodreltext'] = $this->bloodreltext;
        $relation['standard_extended']['rel_arrayX'] = $this->rel_arrayX;
        $relation['standard_extended']['rel_arrayY'] = $this->rel_arrayY;

        $relation['standard_extended']['fams1_array'] = $this->fams1_array;
        $relation['standard_extended']['fams2_array'] = $this->fams2_array;

        // *** Extended search ***
        $relation['standard_extended']['globaltrack'] = $this->globaltrack;
        $relation['standard_extended']['globaltrack2'] = $this->globaltrack2;
        $relation['standard_extended']['totalpath'] = $this->totalpath;
        $relation['standard_extended']['show_extended_message'] = $this->show_extended_message;

        $relation['standard_extended']['rel_arrayspouseX'] = $this->rel_arrayspouseX;
        $relation['standard_extended']['rel_arrayspouseY'] = $this->rel_arrayspouseY;

        return $relation;
    }

    public function set_control_variables(): void
    {
        //$this->start_calculation = isset($_POST["calculator"]) || isset($_POST["switch"]); // Jan. 2025: doesn't work properly.
        $this->start_calculation = isset($_POST["calculator"]);
        $this->search_results = $this->relation["person1"] == '' || $this->relation["person2"] == '' ? false : true;
    }

    public function checkInput(): void
    {
        $safeTextDb = new SafeTextDb();

        if (isset($_POST["button_search_name1"]) || isset($_POST["button_search_id1"])) {
            $_SESSION["button_search_name1"] = 1;
        }
        if (isset($_POST["button_search_name2"]) || isset($_POST["button_search_id2"])) {
            $_SESSION["button_search_name2"] = 1;
        }

        // *** Link from person pop-up menu ***
        if (isset($_GET['pers_id'])) {
            $_SESSION["button_search_name1"] = 1;
            $_SESSION["search_pers_id"] = $safeTextDb->safe_text_db($_GET['pers_id']);
            unset($_SESSION["search_pers_id2"]);
            $_SESSION['rel_search_name'] = '';
        }
    }

    public function getSelectedPersons(): void
    {
        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();
        $validateGedcomber = new ValidateGedcomnumber();

        // *** GEDCOM number: must be pattern like: Ixxxx ***
        if (isset($_POST["person1"]) && $validateGedcomber->validate($_POST["person1"])) {
            $this->relation["person1"] = $_POST['person1'];
        }

        if (isset($_POST["person2"]) && $validateGedcomber->validate($_POST["person2"])) {
            $this->relation["person2"] = $_POST['person2'];
        }

        // calculate or switch button is pressed
        if ((isset($_POST["calculator"]) || isset($_POST["switch"])) && $this->relation["person1"] && $this->relation["person2"]) {
            $searchDb = $this->db_functions->get_person($this->relation["person1"]);
            if (isset($searchDb)) {
                $this->relation['gednr1'] = $searchDb->pers_gedcomnumber;
                $privacy = $personPrivacy->get_privacy($searchDb);
                $name = $personName->get_person_name($searchDb, $privacy);
                $this->relation['name1'] = $name["name"];
                //$this->relation['sexe1'] = $searchDb->pers_sexe == 'M' ? 'm' : 'f';
                $this->relation['sexe1'] = $searchDb->pers_sexe;
            }
            if ($searchDb->pers_fams) {
                $this->relation['fams1'] = $searchDb->pers_fams;
                $this->fams1_array = explode(";", $this->relation['fams1']);
                $this->relation['family_id1'] = $this->fams1_array[0];
            } else {
                $this->relation['family_id1'] = $searchDb->pers_famc;
            }
            //$vars['pers_family'] = $this->relation['family_id1'];
            //$relation['link1'] = $processLinks->get_link($this->uri_path, 'family', $tree_id, true, $vars);

            $searchDb2 = $this->db_functions->get_person($this->relation["person2"]);
            if (isset($searchDb2)) {
                $this->relation['gednr2'] = $searchDb2->pers_gedcomnumber;
                $privacy = $personPrivacy->get_privacy($searchDb2);
                $name = $personName->get_person_name($searchDb2, $privacy);
                $this->relation['name2'] = $name["name"];
                //$this->relation['sexe2'] = $searchDb2->pers_sexe == 'M' ? 'm' : 'f';
                $this->relation['sexe2'] = $searchDb2->pers_sexe;
            }
            if ($searchDb2->pers_fams) {
                $this->relation['fams2'] = $searchDb2->pers_fams;
                $this->fams2_array = explode(";", $this->relation['fams2']);
                $this->relation['family_id2'] = $this->fams2_array[0];
            } else {
                $this->relation['family_id2'] = $searchDb2->pers_famc;
            }
            //$vars['pers_family'] = $this->relation['family_id2'];
            //$relation['link2'] = $processLinks->get_link($this->uri_path, 'family', $tree_id, true, $vars);
        }
    }

    public function getNames(): void
    {
        $safeTextDb = new SafeTextDb();

        // *** Person 1 ***
        if (isset($_POST["search_name"]) && !isset($_POST["switch"])) {
            $this->search_name1 = $safeTextDb->safe_text_db($_POST['search_name']);
            $_SESSION['rel_search_name'] = $this->search_name1;
        }
        if (isset($_SESSION['rel_search_name'])) {
            $this->search_name1 = $_SESSION['rel_search_name'];
        }
        if (isset($_POST["button_search_id1"])) {
            $this->search_name1 = '';
        }

        // *** Person 2 ***
        if (isset($_POST["search_name2"]) && !isset($_POST["switch"])) {
            $this->search_name2 = $safeTextDb->safe_text_db($_POST['search_name2']);
            $_SESSION['rel_search_name2'] = $this->search_name2;
        }
        if (isset($_SESSION['rel_search_name2'])) {
            $this->search_name2 = $_SESSION['rel_search_name2'];
        }
        if (isset($_POST["button_search_id2"])) {
            $this->search_name2 = '';
        }
    }

    public function getGEDCOMnumbers(): void
    {
        $safeTextDb = new SafeTextDb();

        if (isset($_POST["search_gednr"]) && !isset($_POST["switch"])) {
            $this->search_gednr1 = strtoupper($safeTextDb->safe_text_db($_POST['search_gednr']));
            $_SESSION['rel_search_gednr'] = $this->search_gednr1;
        }
        if (isset($_SESSION['rel_search_gednr'])) {
            $this->search_gednr1 = $_SESSION['rel_search_gednr'];
        }
        if (isset($_POST["button_search_name1"])) {
            $this->search_gednr1 = '';
        }

        if (isset($_POST["search_gednr2"]) && !isset($_POST["switch"])) {
            $this->search_gednr2 = strtoupper($safeTextDb->safe_text_db($_POST['search_gednr2']));
            $_SESSION['rel_search_gednr2'] = $this->search_gednr2;
        }
        if (isset($_SESSION['rel_search_gednr2'])) {
            $this->search_gednr2 = $_SESSION['rel_search_gednr2'];
        }
        if (isset($_POST["button_search_name2"])) {
            $this->search_gednr2 = '';
        }
    }

    public function switchPersons(): void
    {
        // *** Switch person 1 and 2 ***
        if (isset($_POST["switch"])) {
            $temp = $this->search_name1;
            $this->search_name1 = $this->search_name2;
            $_SESSION['rel_search_name'] = $this->search_name1;
            $this->search_name2 = $temp;
            $_SESSION['rel_search_name2'] = $this->search_name2;

            $temp = $this->search_gednr1;
            $this->search_gednr1 = $this->search_gednr2;
            $_SESSION['rel_search_gednr'] = $this->search_gednr1;
            $this->search_gednr2 = $temp;
            $_SESSION['rel_search_gednr2'] = $this->search_gednr2;

            $temp = $this->relation["person1"];
            $this->relation["person1"] = $this->relation["person2"];
            $this->relation["person2"] = $temp;

            // *** Link from person pop-up menu ***
            if (isset($_SESSION["search_pers_id"]) && isset($_SESSION["search_pers_id2"])) {
                $temp = $_SESSION["search_pers_id2"];
                $_SESSION["search_pers_id2"] = $_SESSION["search_pers_id"];
                $_SESSION["search_pers_id"] = $temp;
            }
        }
    }

    public function process_standard_calculation(): void
    {
        if ($this->start_calculation && $this->search_results) {
            $processLinks = new ProcessLinks();

            $vars['pers_family'] = $this->relation['family_id1'];
            $this->link1 = $processLinks->get_link($this->uri_path, 'family', $this->tree_id, true, $vars);

            $vars['pers_family'] = $this->relation['family_id2'];
            $this->link2 = $processLinks->get_link($this->uri_path, 'family', $this->tree_id, true, $vars);

            // *** Used in sentence: Firstname Lastname IS 2nd cousin 4 times removed of husband of Firstname Lastname ***
            $this->language_is = ' ' . __('is') . ' ';
            if ($this->selected_language == "he") {
                $this->language_is = $this->relation['sexe1'] == "M" ? ' הוא ' : ' היא ';
            } elseif ($this->selected_language == "cn") {
                $this->language_is = '的';
            }

            $this->rel_arrayX = $this->create_rel_array($this->relation['person1']); // === GEDCOM nr of person X ===
            $this->rel_arrayY = $this->create_rel_array($this->relation['person2']); // === GEDCOM nr of person Y ===    

            if (isset($this->rel_arrayX) && isset($this->rel_arrayY)) {
                $this->compare_rel_array($this->rel_arrayX, $this->rel_arrayY, 0);
            }

            $this->calculate_rel();
        }
    }

    public function process_extended_calculation(): void
    {
        $firstcall1 = array();
        $firstcall1[0] = $this->relation["person1"] . "@fst@fst@fst" . $this->relation["person1"];

        $firstcall2 = array();
        $firstcall2[0] = $this->relation["person2"] . "@fst@fst@fst" . $this->relation["person2"];

        //$total_arr = array();

        if (isset($_POST["extended"]) && !isset($_POST["next_path"])) {
            $_SESSION["couple"] = '';
            // session[couple] flags that persons A & B are a couple. consequences: 
            // 1. don't display that (has already been done in regular calculator)
            // 2. in the extended_calculator function don't search thru the fam of the couple, since this gives errors.
            $pers1Db = $this->db_functions->get_person($this->relation["person1"]);
            $pers2Db = $this->db_functions->get_person($this->relation["person2"]);
            if (isset($pers1Db->pers_fams) && isset($pers2Db->pers_fams)) {
                $fam1 = explode(";", $pers1Db->pers_fams);
                $fam2 = explode(";", $pers2Db->pers_fams);
                foreach ($fam1 as $value1) {
                    foreach ($fam2 as $value2) {
                        if ($value1 === $value2) {
                            $_SESSION["couple"] = $value1;
                        }
                    }
                }
            }
        }
        //$relation['global_array'] = array();

        $this->extended_calculator($firstcall1, $firstcall2);
    }

    public function process_marriage_relationship(): void
    {
        /**
         * Marital relationship
         *
         * This part shows for example this relationship: Uncle <-> Wife of nephew.
         * Relation types
         * 3 = uncle - nephew
         * 4 = nephew - uncle
         * 5 = cousin
         * 6 = siblings
         */
        if ($this->start_calculation && $this->search_results && $this->relation['relation_type'] != 1 && $this->relation['relation_type'] != 2 && $this->relation['relation_type'] != 7) {
            $this->relation['foundX_nr'] = '';
            $this->relation['foundY_nr'] = '';
            $this->relation['foundX_gen'] = '';
            $this->relation['foundY_gen'] = '';
            $this->relation['foundX_match'] = '';
            $this->relation['foundY_match'] = '';
            $this->relation['relation_type'] = '';
            $this->relation['rel_text'] = '';
            $this->relation['spouse'] = '';

            $this->search_marital(); // Will return a new $relation['rel_text'].
        }
    }

    // TODO: use general ancestor script. Or: check query, get_person will get all items of person. Not needed here.
    public function create_rel_array($gedcomnumber)
    {
        // Creates array of ancestors of person with GEDCOM nr. $this->relation['gednr1']
        $ancestor_id2[] = $gedcomnumber;
        $ancestor_number2[] = 1;
        $marriage_number2[] = 0;
        $generation = 1;
        $genarray_count = 0;
        $trackfamc = array();

        // *** Loop ancestor report ***
        while (isset($ancestor_id2[0])) {
            unset($ancestor_id);
            $ancestor_id = $ancestor_id2;
            unset($ancestor_id2);

            unset($ancestor_number);
            $ancestor_number = $ancestor_number2;
            unset($ancestor_number2);

            unset($marriage_number);
            $marriage_number = $marriage_number2;
            unset($marriage_number2);

            // *** Loop per generation ***
            $kwcount = count($ancestor_id);
            for ($i = 0; $i < $kwcount; $i++) {
                if ($ancestor_id[$i] != '0') {
                    $person_manDb = $this->db_functions->get_person($ancestor_id[$i], 'famc-fams');
                    /*
                    $personPrivacy = new PersonPrivacy();
                    $man_privacy=$personPrivacy->get_privacy($person_manDb);
                    if (strtolower($person_manDb->pers_sexe)=='m' && $ancestor_number[$i]>1){
                        $familyDb=$this->db_functions->get_family($marriage_number[$i]);

                        // *** Use privacy filter of woman ***
                        $person_womanDb=$this->db_functions->get_person();
                        $woman_privacy=$personPrivacy->get_privacy($familyDb->fam_woman);

                        $marriage_cls = new MarriageCls($familyDb, $man_privacy, $woman_privacy);
                        $family_privacy=$marriage_cls->get_privacy();
                    }
                    */

                    //*** Show person data ***
                    $genarray[$genarray_count][0] = $ancestor_id[$i];
                    $genarray[$genarray_count][1] = $generation - 1;
                    $genarray_count++; // increase by one

                    // *** Check for parents ***
                    if ($person_manDb->pers_famc && !in_array($person_manDb->pers_famc, $trackfamc)) {
                        $trackfamc[] = $person_manDb->pers_famc;

                        $familyDb = $this->db_functions->get_family($person_manDb->pers_famc, 'man-woman');
                        if ($familyDb->fam_man) {
                            $ancestor_id2[] = $familyDb->fam_man;
                            $ancestor_number2[] = (2 * $ancestor_number[$i]);
                            $marriage_number2[] = $person_manDb->pers_famc;
                            $genarray[][2] = $genarray_count - 1;
                            // save array nr of child in parent array so we can build up ancestral line later
                        }

                        if ($familyDb->fam_woman) {
                            $ancestor_id2[] = $familyDb->fam_woman;
                            $ancestor_number2[] = (2 * $ancestor_number[$i] + 1);
                            $marriage_number2[] = $person_manDb->pers_famc;
                            $genarray[][2] = $genarray_count - 1;
                            // save array nr of child in parent array so we can build up ancestral line later
                        }
                    }
                }
            }
            $generation++;
        }

        return $genarray;
    }

    private function compare_rel_array($arrX, $arrY, $spouse_flag)
    {
        foreach ($arrX as $keyx => $valx) {
            foreach ($arrY as $keyy => $valy) {
                if ($arrX[$keyx][0] == $arrY[$keyy][0]) {
                    $this->relation['foundX_match'] = $keyx;  // saves the array nr of common ancestor in ancestor array of X
                    $this->relation['foundY_match'] = $keyy;  // saves the array nr of common ancestor in ancestor array of Y
                    // saves the array nr of the child leading to X
                    if (isset($arrX[$keyx][2])) {
                        $this->relation['foundX_nr'] = $arrX[$keyx][2];
                    }
                    // saves the array nr of the child leading to Y
                    if (isset($arrY[$keyy][2])) {
                        $this->relation['foundY_nr'] = $arrY[$keyy][2];
                    }
                    // saves the nr of generations common ancestor is removed from X
                    if (isset($arrX[$keyx][1])) {
                        $this->relation['foundX_gen'] = $arrX[$keyx][1];
                    }
                    // saves the nr of generations common ancestor is removed from Y
                    if (isset($arrY[$keyy][1])) {
                        $this->relation['foundY_gen'] = $arrY[$keyy][1];
                    }
                    $this->relation['spouse'] = $spouse_flag; // saves global variable flagging if we're comparing X - Y or spouse combination
                    return;
                }
            }
        }
    }

    private function calculate_rel()
    {
        // calculates the relationship found: "X is 2nd cousin once removed of Y"
        if ($this->relation['foundX_match'] == '') {
            return;
        }

        $this->relation['double_spouse'] = 0;
        if ($this->relation['foundX_match'] == 0 && $this->relation['foundY_match'] == 0) {  // self
            $this->relation['rel_text'] = __(' identical to ');
            if ($this->relation['spouse'] == 1 || $this->relation['spouse'] == 2) {
                $this->relation['rel_text'] = " ";
            }
            if ($this->relation['spouse'] == 3) {
                $this->relation['double_spouse'] = 1;
            }
            // it's the spouse itself so text should be "X is spouse of Y", not "X is spouse of is identical to Y" !!
            $this->relation['relation_type'] = 7;
        } elseif ($this->relation['foundX_match'] == 0 && $this->relation['foundY_match'] > 0) {
            // x is ancestor of y
            $this->relation['relation_type'] = 1;
            $this->calculate_ancestor($this->relation['foundY_gen']);
        } elseif ($this->relation['foundY_match'] == 0 && $this->relation['foundX_match'] > 0) {
            // x is descendant of y
            $this->relation['relation_type'] = 2;
            $this->calculate_descendant($this->relation['foundX_gen']);
        } elseif ($this->relation['foundX_gen'] == 1 && $this->relation['foundY_gen'] == 1) {
            // x is brother of y
            /*
            elder brother's wife 嫂
            younger brother's wife 弟妇
            elder sister's husband 姊夫
            younger sister's husband 妹夫
            */
            $this->relation['relation_type'] = 6;
            if ($this->relation['sexe1'] == 'M') {
                $this->relation['rel_text'] = __('brother of ');
                //***Greek ***
                /**In the Greek language, the gender of the second person plays a role in expressing the blood relationship that exists between two people.
                 * For example, father:
                 * If it is a boy we say (father ΤΟΥ John).
                 * If it's a girl, (father ΤΗΣ Helen).
                 * The code for the Greek language was modified by Dimitris Fasoulas, for the website www.remen.gr
                 */

                /** Στην ελληνική γλώσσα για την διατύπωση της συγγένειας αίματος  που υπάρχει μεταξύ δύο ατόμων παίζει ρόλο το γένος του δεύτερου προσώπου.
                 * Για παράδειγμα, πατέρας:
                 * Αν είναι αγόρι λέμε (πατέρας ΤΟΥ Γιάννη).
                 * Αν είναι κορίτσι, πατέρας ΤΗΣ Ελένης.
                 * Ο κώδικας για την ελληνική γλώσσα τροποποιήθηκε από τον Δημητρη Φασούλα, για τον ιστότοπο www.remen.gr
                 */

                // *** Ελληνικά αδελφός***
                if ($this->selected_language == "gr" && $this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = 'αδελφός του ';
                    } else {
                        $this->relation['rel_text'] = 'αδελφός της ';
                    }
                }
                // *** Ελληνικά τέλος***
                // *** Greek end***
                if ($this->selected_language == "cn") {
                    if ($this->relation['sexe2'] == "M") {
                        // "A's brother is B"
                        $this->relation['rel_text'] = '兄弟是';
                    } else {
                        // "A's sister is B"
                        $this->relation['rel_text'] = '姊妹是';
                    }
                }
                if ($this->relation['spouse'] == 1) {
                    $this->relation['rel_text'] = __('sister-in-law of ');
                    $this->relation['special_spouseX'] = 1;  //comparing spouse of X with Y
                    // *** Greek***
                    // *** Ελληνικά κουνιάδα***
                    if ($this->selected_language == "gr" && $this->relation['sexe1'] == "M") {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = 'κουνιάδα του ';
                        } else {
                            $this->relation['rel_text'] = 'κουνιάδα της ';
                        }
                    }
                    // *** Ελληνικά τέλος***
                    // *** Greek end*** 
                    if ($this->selected_language == "cn") {
                        if ($this->relation['sexe2'] == "M") {
                            // "A's brother-in-law is B" (husband's brother)
                            $this->relation['rel_text'] = '大爷(小叔)是';
                        } else {
                            // "A's sister-in-law is B" (husband's sister)
                            $this->relation['rel_text'] = '大姑(小姑)是';
                        }
                    }
                }
                if ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3) {
                    $this->relation['rel_text'] =  __('brother-in-law of ');
                    $this->relation['special_spouseY'] = 1;
                    //comparing X with spouse of Y or comparing 2 spouses
                    //$this->relation['special_spouseX'] flags not to enter "spouse of" for X in display function
                    //$this->relation['special_spouseY'] flags not to enter "spouse of" for Y in display function
                    // *** Greek***
                    // *** Ελληνικά κουνιάδος***
                    if ($this->selected_language == "gr" && $this->relation['spouse'] == 2) {
                        if ($this->relation['sexe2'] == "M") {
                            $this->relation['rel_text'] = 'κουνιάδος του ';
                        } else {
                            $this->relation['rel_text'] = 'κουνιάδος της ';
                        }
                    }
                    // *** Ελληνικά τέλος***
                    // *** Greek end***
                    if ($this->selected_language == "cn" && $this->relation['spouse'] == 2) {
                        if ($this->relation['sexe2'] == "M") {
                            // "A's brother-in-law is B" (sister's husband) 
                            $this->relation['rel_text'] = '姊夫(妹夫)是';
                        } else {
                            // "A's sister-in-law is B" (brother's wife) 
                            $this->relation['rel_text'] = '嫂(弟妇)是';
                        }
                    }
                    //***Greek ***
                    // *** Ελληνικά κουνιάδος***
                    if ($this->selected_language == "gr" && $this->relation['spouse'] == 3) {
                        if ($this->relation['sexe2'] == "M") {
                            $this->relation['rel_text'] = 'κουνιάδος του ';
                        } else {
                            $this->relation['rel_text'] = 'κουνιάδος της ';
                        }
                    }
                    // *** Ελληνικά τέλος***
                    // *** Greek end*** 
                    if ($this->selected_language == "cn" && $this->relation['spouse'] == 3) {
                        if ($this->relation['sexe2'] == "M") {
                            // "A's brother-in-law is B" (husband's sister's husband) 
                            $this->relation['rel_text'] = '大姑丈(小姑丈)是';
                        } else {
                            // "A's sister-in-law is B" (husband's brother's wife)
                            $this->relation['rel_text'] = '大嫂(小嫂)是';
                        }
                    }
                }
            } else {
                $this->relation['rel_text'] = __('sister of ');
                // *** Greek***
                // *** Ελληνικά αδελφή***
                if ($this->selected_language == "gr") {
                    if ($this->relation['sexe2'] == "M") {
                        $this->relation['rel_text'] = 'αδελφή του ';
                    } else {
                        $this->relation['rel_text'] = 'αδελφή της ';
                    }
                }
                // *** Ελληνικά τέλος***
                // *** Greek end***             
                if ($this->selected_language == "cn") {
                    if ($this->relation['sexe2'] == "M") {
                        // "A's brother is B"
                        $this->relation['rel_text'] = '兄弟是';
                    } else {
                        // "A's sister is B"
                        $this->relation['rel_text'] = '姊妹是';
                    }
                }
                if ($this->relation['spouse'] == 1) {
                    $this->relation['rel_text'] =  __('brother-in-law of ');
                    $this->relation['special_spouseX'] = 1;  //comparing spouse of X with Y
                    // *** Greek***
                    // *** Ελληνικά κουνιάδος***
                    if ($this->selected_language == "gr") {
                        if ($this->relation['sexe2'] == "M") {
                            $this->relation['rel_text'] = 'κουνιάδος του ';
                        } else {
                            $this->relation['rel_text'] = 'κουνιάδος της ';
                        }
                    }
                    // *** Ελληνικά τέλος***
                    // *** Greek end***
                    if ($this->selected_language == "cn") {
                        if ($this->relation['sexe2'] == "M") {
                            // "A's brother-in-law is B" (wife's brother)
                            $this->relation['rel_text'] = '大舅(小舅)是';
                        } else {
                            // "A's sister-in-law is B" (wife's sister)
                            $this->relation['rel_text'] = '大姨子(小姨)是';
                        }
                    }
                }
                if ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3) {
                    $this->relation['rel_text'] =  __('sister-in-law of ');
                    $this->relation['special_spouseY'] = 1; //comparing X with spouse of Y or comparing 2 spouses
                    //$this->relation['special_spouseX'] flags not to enter "spouse of" for X in display function
                    //$this->relation['special_spouseY'] flags not to enter "spouse of" for Y in display function
                    // *** Greek***
                    // *** Ελληνικά κουνιάδα***
                    if ($this->selected_language == "gr" && $this->relation['spouse'] == 2) {
                        if ($this->relation['sexe2'] == "M") {
                            $this->relation['rel_text'] = 'κουνιάδα του ';
                        } else {
                            $this->relation['rel_text'] = 'κουνιάδα της ';
                        }
                    }
                    // *** Ελληνικά τέλος***
                    // *** Greek end*** 
                    if ($this->selected_language == "cn" && $this->relation['spouse'] == 2) {
                        if ($this->relation['sexe2'] == "M") {
                            // "A's brother-in-law is B"  (sister's husband)
                            $this->relation['rel_text'] = '姊夫(妹夫)是';
                        } else {
                            // "A's sister-in-law is B" (brother's wife)
                            $this->relation['rel_text'] = '嫂(弟妇)是';
                        }
                    }
                    if ($this->selected_language == "cn" && $this->relation['spouse'] == 3) {
                        if ($this->relation['sexe2'] == "M") {
                            // "A's brother-in-law is B" (wife's sister's husband) 
                            $this->relation['rel_text'] = '姐夫(妹夫)是';
                        } else {
                            // "A's sister-in-law is B" (wife's brother's wife)
                            $this->relation['rel_text'] = '表嫂(表嫂)是';
                        }
                    }
                }
            }
        } elseif ($this->relation['foundX_gen'] == 1 && $this->relation['foundY_gen'] > 1) {
            // x is uncle, great-uncle etc of y
            $this->relation['relation_type'] = 3;
            $this->calculate_uncles($this->relation['foundY_gen']);
        } elseif ($this->relation['foundX_gen'] > 1 && $this->relation['foundY_gen'] == 1) {
            // x is nephew, great-nephew etc of y
            $this->relation['relation_type'] = 4;
            $this->calculate_nephews($this->relation['foundX_gen']);
        } else {
            // x and y are cousins of any number (2nd, 3rd etc) and any distance removed (once removed, twice removed etc)
            $this->relation['relation_type'] = 5;
            $this->calculate_cousins($this->relation['foundX_gen'], $this->relation['foundY_gen']);
        }
    }

    private function spanish_degrees($pers): string
    {
        $spantext = '';
        //if ($pers == 2) {
        //
        //}
        if ($pers == 3) {
            $spantext = 'bis';
        }
        if ($pers == 4) {
            $spantext = 'tris';
        }
        if ($pers == 5) {
            $spantext = 'tetra';
        }
        if ($pers == 6) {
            $spantext = 'penta';
        }
        if ($pers == 7) {
            $spantext = 'hexa';
        }
        if ($pers == 8) {
            $spantext = 'hepta';
        }
        if ($pers == 9) {
            $spantext = 'octa';
        }
        if ($pers == 10) {
            $spantext = 'nona';
        }
        if ($pers == 11) {
            $spantext = 'deca';
        }
        if ($pers == 12) {
            $spantext = 'undeca';
        }
        if ($pers == 13) {
            $spantext = 'dodeca';
        }
        if ($pers == 14) {
            $spantext = 'trideca';
        }
        if ($pers == 15) {
            $spantext = 'tetradeca';
        }
        if ($pers == 16) {
            $spantext = 'pentadeca';
        }
        if ($pers == 17) {
            $spantext = 'hexadeca';
        }
        if ($pers == 18) {
            $spantext = 'heptadeca';
        }
        if ($pers == 19) {
            $spantext = 'octadeca';
        }
        if ($pers == 20) {
            $spantext = 'nonadeca';
        }
        if ($pers == 21) {
            $spantext = 'icosa';
        }
        if ($pers == 22) {
            $spantext = 'unicosa';
        }
        if ($pers == 23) {
            $spantext = 'doicosa';
        }
        if ($pers == 24) {
            $spantext = 'tricosa';
        }
        if ($pers == 25) {
            $spantext = 'tetricosa';
        }
        if ($pers == 26) {
            $spantext = 'penticosa';
        }
        return $spantext;
    }

    private function calculate_ancestor($pers): void
    {
        $ancestortext = '';
        $parent = $this->relation['sexe1'] == 'M' ? __('father') : __('mother');

        // *** Greek***
        // *** Ελληνικά πατέρας μητέρα***
        if ($this->selected_language == "gr") {
            if ($this->relation['sexe1'] == 'M') {
                if ($this->relation['sexe2'] == 'M') {
                    $parent = 'πατέρας του ';
                } else {
                    $parent = 'πατέρας της  ';
                }
            } else {
                if ($this->relation['sexe2'] == 'M') {
                    $parent = 'μητέρα του ';
                } else {
                    $parent = 'μητέρα της  ';
                }
            }
        }
        // *** Ελληνικά τέλος***
        // *** Greek end*** 

        if ($this->selected_language == "cn") {
            // chinese instead of A is father of B we say: A's son is B
            // therefore we need sex of B instead of A and use son/daughter instead of father/mother
            if ($this->relation['sexe2'] == 'M') {
                $parent = '儿子';  // son
            } else {
                $parent = '女儿';  //daughter
            }
        }

        if ($pers == 1) {
            if ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3) {
                $this->relation['special_spouseY'] = 1; // prevents "spouse of Y" in output
                // TODO improve code.
                $parent = $parent == __('father') ? __('father-in-law') : __('mother-in-law');
                // *** Greek***
                // *** Ελληνικά πεθερός πεθερά***  
                if ($this->selected_language == "gr") {
                    if ($this->relation['sexe1'] == "M") {
                        if ($this->relation['sexe2'] == "M") {
                            $parent = 'πεθερός του ';
                        } else {
                            $parent = 'πεθερός της ';
                        }
                    } else {
                        if ($this->relation['sexe2'] == "M") {
                            $parent = 'πεθερά του ';
                        } else {
                            $parent = 'πεθερά της ';
                        }
                    }
                }
                // *** Ελληνικά τέλος***
                // *** Greek end*** 
                if ($this->selected_language == "cn") {
                    if ($this->relation['sexe2'] == "M") {
                        // son-in-law
                        $parent = '女婿';
                    } else {
                        // daughter-in-law
                        $parent = '儿媳';
                    }
                }
            }
            if ($this->selected_language == "gr") {
                $this->relation['rel_text'] = $parent . ' ';
            } else {
                $this->relation['rel_text'] = $parent . __(' of ');
            }
            if ($this->selected_language == "da") {
                $this->relation['rel_text'] = $parent . ' til ';
            }
            if ($this->selected_language == "cn") {
                $this->relation['rel_text'] = $parent . '是';
            }
        } else {
            if ($this->selected_language == "nl") {
                $ancestortext = $this->dutch_ancestors($pers);
                $this->relation['rel_text'] = $ancestortext . $parent . __(' of ');
                if ($pers > 4) {
                    $gennr = $pers - 2;
                    $this->relation['dutch_text'] =  "(" . $ancestortext . $parent . " = " . $gennr . __('th') . ' ' . __('great-grand') . $parent . ")";
                }
                // *** Greek***
                // *** Ελληνικά παππούς γιαγιά***
            } elseif ($this->selected_language == "gr") {
                if ($parent == __('father')) {
                    $grparent = 'παππούς';
                    $grgrparent = 'προπάππος';
                    $gr_postfix = "oς";
                } else {
                    $grparent = 'γιαγιά';
                    $grgrparent = 'προγιαγιά';
                    $gr_postfix = "η";
                }

                $gennr = $pers - 1;
                $degree = $gennr . $gr_postfix;
                if ($pers == 2) {
                    $this->relation['rel_text'] = $this->relation['sexe2'] == 'M' ? ' του ' : ' της ';
                } elseif ($pers > 2 && $pers < 6) {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $grgrparent . " (" . $degree . " " . $grparent . ') του ';
                    } else {
                        $this->relation['rel_text'] = $grgrparent . " (" . $degree . " " . $grparent . ') της ';
                    }
                }
                // *** Ελληνικά τέλος***
                // *** Greek end*** 
            } elseif ($this->selected_language == "es") {
                // TODO improve code
                if ($parent == __('father')) {
                    $grparent = 'abuelo';
                    $spanishnumber = "o";
                } else {
                    $grparent = 'abuela';
                    $spanishnumber = "a";
                }
                $gennr = $pers - 1;
                $degree = $gennr . $spanishnumber . " " . $grparent;
                if ($pers == 2) {
                    $this->relation['rel_text'] = $grparent . __(' of ');
                } elseif ($pers > 2 && $pers < 27) {
                    $this->relation['rel_text'] = $this->spanish_degrees($pers) . $grparent . " (" . $degree . ")" . __(' of ');
                } else {
                    $this->relation['rel_text'] = $degree . __(' of ');
                }
            } elseif ($this->selected_language == "he") {
                //TODO improve code
                if ($parent == __('father')) {
                    $grparent = __('grand');
                    $grgrparent = __('great-grand');
                } else {
                    $grparent = __('grand');
                    $grgrparent = __('great-grand');
                }
                $gennr = $pers - 2;
                if ($pers == 2) {
                    $this->relation['rel_text'] = $grparent . __(' of ');
                } elseif ($pers > 2) {
                    $degree = '';
                    if ($pers > 3) {
                        $degree = ' דרגה ';
                        $degree .= $gennr;
                    }
                    $this->relation['rel_text'] = $grgrparent . $degree . __(' of ');
                }
            } elseif ($this->selected_language == "fi") {
                if ($pers == 2) {
                    $this->relation['rel_text'] = __('grand') . $parent . __(' of ');
                }
                $gennr = $pers - 1;
                if ($pers >  2) {
                    $this->relation['rel_text'] = $gennr . '. ' . __('grand') . $parent . __(' of ');
                }
            } elseif ($this->selected_language == "no") {
                if ($pers == 2) {
                    $this->relation['rel_text'] = __('grand') . $parent . __(' of ');
                }
                if ($pers == 3) {
                    $this->relation['rel_text'] = __('great-grand') . $parent . __(' of ');
                }
                if ($pers == 4) {
                    $this->relation['rel_text'] = 'tippolde' . $parent . __(' of ');
                }
                if ($pers == 5) {
                    $this->relation['rel_text'] = 'tipp-tippolde' . $parent . __(' of ');
                }
                $gennr = $pers - 3;
                if ($pers >  5) {
                    $this->relation['rel_text'] = $gennr . "x " . 'tippolde' . $parent . __(' of ');
                }
            } elseif ($this->selected_language == "da") {
                // right person is spouse of Y, not Y
                if ($this->relation['spouse'] == "2" || $this->relation['spouse'] == "3") {
                    $relarr = $this->rel_arrayspouseY;
                } else {
                    $relarr = $this->rel_arrayY;
                }
                if ($pers == 2) {
                    // grandfather
                    $arrnum = 0;
                    $ancsarr = array();
                    $count = $this->relation['foundY_nr'];
                    while ($count != 0) {
                        $parnumber = $count;
                        $ancsarr[$arrnum] = $parnumber;
                        $arrnum++;
                        $count = $relarr[$count][2];
                    }
                    $persidDb = $this->db_functions->get_person($relarr[$parnumber][0]);
                    $parsexe = $persidDb->pers_sexe;
                    if ($parsexe == 'M') {
                        $this->relation['rel_text'] = 'far' . $parent . ' til ';
                    } else {
                        $this->relation['rel_text'] = 'mor' . $parent . ' til ';
                    }
                }
                if ($pers == 3) {
                    $this->relation['rel_text'] = "olde" . $parent . ' til ';
                }
                if ($pers == 4) {
                    $this->relation['rel_text'] = "tip olde" . $parent . ' til ';
                }
                if ($pers == 5) {
                    $this->relation['rel_text'] = "tip tip olde" . $parent . ' til ';
                }
                if ($pers == 6) {
                    $this->relation['rel_text'] = "tip tip tip olde" . $parent . ' til ';
                }
                $gennr = $pers - 3;
                if ($pers >  6) {
                    $this->relation['rel_text'] = $gennr . ' gange tip olde' . $parent . ' til ';
                }
            }

            // Swedish needs to know if grandparent is related through mother or father - different names there
            // also for great-grandparent and 2nd great-grandparent!!!
            elseif ($this->selected_language == "sv") {
                if ($this->relation['spouse'] == "2" || $this->relation['spouse'] == "3") {
                    // right person is spouse of Y, not Y
                    $relarr = $this->rel_arrayspouseY;
                } else {
                    $relarr = $this->rel_arrayY;
                }

                if ($pers > 1) {
                    // grandfather
                    $arrnum = 0;
                    //reset($ancsarr);
                    $count = $this->relation['foundY_nr'];
                    while ($count != 0) {
                        $parnumber = $count;
                        $ancsarr[$arrnum] = $parnumber;
                        $arrnum++;
                        $count = $relarr[$count][2];
                    }
                    $persidDb = $this->db_functions->get_person($relarr[$parnumber][0]);
                    $parsexe = $persidDb->pers_sexe;
                    if ($parsexe == 'M') {
                        $se_grandpar = 'far' . $parent;
                        $direct_par = 'far';
                    } else {
                        $se_grandpar = 'mor' . $parent;
                        $direct_par = 'mor';
                    }
                }

                if ($pers > 2) {
                    // great-grandfather
                    $persidDb2 = $this->db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
                    $parsexe2 = $persidDb2->pers_sexe;

                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") {
                            $se_gr_grandpar = 'farfars ' . $parent;
                        } else {
                            $se_gr_grandpar = 'morfars ' . $parent;
                        }
                    } else {
                        if ($parsexe == "M") {
                            $se_gr_grandpar = 'farmors ' . $parent;
                        } else {
                            $se_gr_grandpar = 'mormors ' . $parent;
                        }
                    }
                }

                if ($pers > 3) {
                    // 2nd great-grandfather
                    $persidDb3 = $this->db_functions->get_person($relarr[$ancsarr[$arrnum - 3]][0]);
                    $parsexe3 = $persidDb3->pers_sexe;
                    if ($parsexe3 == "M") {
                        if ($parsexe2 == "M") {
                            if ($parsexe == "M") {
                                $se_2ndgr_grandpar = 'farfars far' . $parent;
                            } else {
                                $se_2ndgr_grandpar = 'morfars far' . $parent;
                            }
                        } else {
                            if ($parsexe == "M") {
                                $se_2ndgr_grandpar = 'farmors far' . $parent;
                            } else {
                                $se_2ndgr_grandpar = 'mormors far' . $parent;
                            }
                        }
                    } else {
                        if ($parsexe2 == "M") {
                            if ($parsexe == "M") {
                                $se_2ndgr_grandpar = 'farfars mor' . $parent;
                            } else {
                                $se_2ndgr_grandpar = 'morfars mor' . $parent;
                            }
                        } else {
                            if ($parsexe == "M") {
                                $se_2ndgr_grandpar = 'farmors mor' . $parent;
                            } else {
                                $se_2ndgr_grandpar = 'mormors mor' . $parent;
                            }
                        }
                    }
                }

                if ($pers == 2) {
                    $this->relation['rel_text'] = $se_grandpar . __(' of ');
                }
                if ($pers == 3) {
                    $this->relation['rel_text'] = $se_gr_grandpar . __(' of ');
                }
                if ($pers == 4) {
                    $this->relation['rel_text'] = $se_2ndgr_grandpar . __(' of ');
                }
                $gennr = $pers;
                if ($pers >  4) {
                    $this->relation['rel_text'] = $gennr . ':e generations ana på ' . $direct_par . 's sida' . __(' of ');
                }
            } elseif ($this->selected_language == "cn") {
                if (($this->relation['sexe2'] == 'M' && $this->relation['spouse'] != 2 && $this->relation['spouse'] != 3) || ($this->relation['sexe2'] == 'F' && ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3))) {
                    //if($this->relation['sexe2']=="m") { // kwan gives: grandson, great-grandson etc 曾內孫仔  孫子 ???
                    if ($pers == 2) {
                        $this->relation['rel_text'] = '孙子';
                    }
                    if ($pers == 3) {
                        $this->relation['rel_text'] = '曾孙';
                    }
                    if ($pers == 4) {
                        $this->relation['rel_text'] = '玄孙';
                    }
                    if ($pers > 4) {
                        $this->relation['rel_text'] = 'notext';
                    }
                    // in Chinese don't display text after 2nd great grandson
                } else {
                    // granddaughter etc (kwan gives: 曾孫女 曾內孫女  玄孫 ???)
                    if ($pers == 2) {
                        $this->relation['rel_text'] = '孙女';
                    }
                    if ($pers == 3) {
                        $this->relation['rel_text'] = '曾孙女';
                    }
                    if ($pers == 4) {
                        $this->relation['rel_text'] = '玄孙女';
                    }
                    if ($pers > 4) {
                        $this->relation['rel_text'] = 'notext';
                    }
                    // in Chinese don't display text after 2nd great granddaughter
                }
                $this->relation['rel_text'] .= '是';
            } elseif ($this->selected_language == "fr") {
                if ($pers == 2) {
                    $this->relation['rel_text'] = 'grand-' . $parent . __(' of ');
                }
                if ($pers == 3) {
                    $this->relation['rel_text'] = 'arrière-grand-' . $parent . __(' of ');
                }
                if ($pers == 4) {
                    $this->relation['rel_text'] = 'arrière-arrière-grand-' . $parent . __(' of ');
                }
                if ($pers == 5) {
                    $this->relation['rel_text'] = 'arrière-arrière-arrière-grand-' . $parent . __(' of ');
                }
                if ($pers == 6) {
                    $this->relation['rel_text'] = 'arrière-arrière-arrière-arrière-grand-' . $parent . __(' of ');
                }
                $gennr = $pers + 1;
                if ($pers >  6) {
                    $this->relation['rel_text'] = 'ancêtre ' . $gennr . 'ème génération' . __(' of ');
                }
            } elseif ($this->selected_language == "ro") {
                if ($pers == 2) {
                    if ($this->relation['sexe1'] == 'M') {
                        $this->relation['rel_text'] = 'bunicul' . __(' of ');
                    } else {
                        $this->relation['rel_text'] = 'bunica' . __(' of ');
                    }
                }
                if ($pers == 3) {
                    if ($this->relation['sexe1'] == 'M') {
                        $this->relation['rel_text'] = 'străbunicul' . __(' of ');
                    } else {
                        $this->relation['rel_text'] = 'străbunica' . __(' of ');
                    }
                }

                // Example:
                // stră-străbunicul, stra-străbunica, stră-stră-străbunicul, stră-stră-străbunica, stră-stră-stră-străbunicul, stră-stră-stră-străbunica
                // stră-străbunica, stră-stră-străbunica, stră-stră-stră-străbunica, stră-stră-stră-stră-străbunica, stră-stră-stră-stră-stră-străbunica
                if ($pers > 3) {
                    $this->relation['rel_text'] = '';
                    for ($i = 4; $i <= $pers; $i++) {
                        $this->relation['rel_text'] .= 'stră-';
                    }
                    if ($this->relation['sexe1'] == 'M') {
                        $this->relation['rel_text'] .= 'străbunicul' . __(' of ');
                    } else {
                        $this->relation['rel_text'] .= 'străbunica' . __(' of ');
                    }
                }
            } else {
                // *** Other languages ***
                if ($pers == 2) {
                    $this->relation['rel_text'] = __('grand') . $parent . __(' of ');
                }
                if ($pers == 3) {
                    $this->relation['rel_text'] = __('great-grand') . $parent . __(' of ');
                }
                if ($pers == 4) {
                    $this->relation['rel_text'] = __('2nd') . ' ' . __('great-grand') . $parent . __(' of ');
                }
                if ($pers == 5) {
                    $this->relation['rel_text'] = __('3rd') . ' ' . __('great-grand') . $parent . __(' of ');
                }
                $gennr = $pers - 2;
                if ($pers >  5) {
                    $this->relation['rel_text'] = $gennr . __('th') . ' ' . __('great-grand') . $parent . __(' of ');
                }
            }
        }
    }

    private function dutch_ancestors($gennr): string
    {
        $ancestortext = '';
        $rest = '';

        if ($gennr > 512) {
            $ancestortext = " Neanthertaler ancestor of ";    //  ;-)
        } else {
            if ($gennr > 256) {
                $ancestortext = "hoog-";
                $gennr -= 256;
                $this->dutch_ancestors($gennr);
            } elseif ($gennr > 128) {
                $ancestortext = "opper-";
                $gennr -= 128;
                $this->dutch_ancestors($gennr);
            } elseif ($gennr > 64) {
                $ancestortext = "aarts-";
                $gennr -= 64;
                $this->dutch_ancestors($gennr);
            } elseif ($gennr > 32) {
                $ancestortext = "voor-";
                $gennr -= 32;
                $this->dutch_ancestors($gennr);
            } elseif ($gennr > 16) {
                $ancestortext = "edel-";
                $gennr -= 16;
                $this->dutch_ancestors($gennr);
            } elseif ($gennr > 8) {
                $ancestortext = "stam-";
                $gennr -= 8;
                $this->dutch_ancestors($gennr);
            } elseif ($gennr > 4) {
                $ancestortext = "oud";
                $gennr -= 4;
                $this->dutch_ancestors($gennr);
            } else {
                if ($gennr == 4) {
                    $rest = 'betovergroot';
                }
                if ($gennr == 3) {
                    $rest = 'overgroot';
                }
                if ($gennr == 2) {
                    $rest = 'groot';
                }
                if ($gennr == 1) {
                    $rest = '';
                }
            }
        }
        return $ancestortext . $rest;
    }

    private function calculate_descendant($pers): void
    {
        $child = $this->relation['sexe1'] == 'M' ? __('son') : __('daughter');

        // *** Greek***
        // *** Ελληνικά γιος κόρη***  
        if ($this->selected_language == "gr") {
            if ($this->relation['sexe1'] == 'M') {
                if ($this->relation['sexe2'] == 'M') {
                    $child = 'γιος του ';
                } else {
                    $child = 'γιος της ';
                }
            } elseif ($this->relation['sexe2'] == 'M') {
                if ($this->relation['sexe2'] == 'M') {
                    $child = 'κόρη του ';
                } else {
                    $child = 'κόρη της ';
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end***
        }

        if ($this->selected_language == "cn") {
            // chinese instead of A is son of B we say: A's father is B
            // therefore we need sex of B instead of A and use father/ mother instead of son/ daughter
            if ($this->relation['sexe2'] == 'M') {
                $child = '父亲';  // father
            } else {
                $child = '母亲';  // mother
            }
        }

        if ($pers == 1) {
            if ($this->relation['spouse'] == 1) {
                $child = $child == __('son') ? __('son-in-law') : __('daughter-in-law');
                $this->relation['special_spouseX'] = 1;

                // *** Greek***
                // *** Ελληνικά νύφη γαμπρός***
                if ($this->selected_language == "gr") {
                    if ($this->relation['sexe1'] == "M") {
                        if ($this->relation['sexe2'] == "M") {
                            $child = 'νύφη του ';
                        } else {
                            $child = 'νύφη της';
                        }
                    } else {
                        if ($this->relation['sexe2'] == "M") {
                            $child = 'γαμπρός του ';
                        } else {
                            $child = 'γαμπρός της ';
                        }
                    }
                }
                // *** Ελληνικά τέλος***
                // *** Greek end*** 

                if ($this->selected_language == "cn") {  // A's father/mother-in-law is B (instead of A is son/daughter-in-law of B)
                    if ($this->relation['sexe2'] == "M") {
                        if ($this->relation['sexe1'] == "F") {
                            // father-in-law called by daughter-in-law  
                            $child = '公公';
                        } else {
                            // father-in-law called by son-in-law
                            $child = '岳父';
                        }
                    } else {
                        if ($this->relation['sexe1'] == "F") {
                            // mother-in-law called by daughter-in-law
                            $child = '婆婆';
                        } else {
                            // mother-in-law called by son-in-law
                            $child = '岳母';
                        }
                    }
                }
            }

            if ($this->selected_language == "gr") {
                $this->relation['rel_text'] = $child . '  ';
            } else {
                $this->relation['rel_text'] = $child . __(' of ');
            }
            if ($this->selected_language == "cn") {
                $this->relation['rel_text'] = $child . '是';
            }
            // *** Greek***
            // *** Ελληνικά εγγονός***
        } elseif ($this->selected_language == "gr") {
            if ($child == __('son')) {
                $grchild = 'εγγονός';
                $grgrchild = 'δισέγγονος';
                $gr_postfix = "ος";
            } else {
                $grchild = 'εγγονή';
                $grgrchild = 'δισέγγονη';
                $gr_postfix = "η";
            }
            $gennr = $pers - 1;
            $degree = $gennr . $gr_postfix . " " . $grchild;
            if ($pers == 2) {
                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $grchild . ' του ';
                    } else {
                        $this->relation['rel_text'] = $grchild . ' της ';
                    }
                } else {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $grchild . ' του ';
                    } else {
                        $this->relation['rel_text'] = $grchild . ' της ';
                    }
                }
            } elseif ($pers > 2) {
                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $grgrchild . " (" . $degree . ' ) του ';
                    } else {
                        $this->relation['rel_text'] = $grgrchild . " (" . $degree . ' ) της ';
                    }
                } else {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $grgrchild . " (" . $degree . ' ) του ';
                    } else {
                        $this->relation['rel_text'] =  $grgrchild . " (" . $degree . ' ) της ';
                    }
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end*** 
        } elseif ($this->selected_language == "es") {
            if ($child == __('son')) {
                $grchild = 'nieto';
                $spanishnumber = "o";
            } else {
                $grchild = 'nieta';
                $spanishnumber = "a";
            }
            $gennr = $pers - 1;
            $degree = $gennr . $spanishnumber . " " . $grchild;
            if ($pers == 2) {
                $this->relation['rel_text'] = $grchild . __(' of ');
            } elseif ($pers > 2 && $pers < 27) {
                $this->relation['rel_text'] = $this->spanish_degrees($pers) . $grchild . " (" . $degree . ")" . __(' of ');
            } else {
                $this->relation['rel_text'] = $degree . __(' of ');
            }
        } elseif ($this->selected_language == "he") {
            if ($child == __('son')) {
                $grchild = 'נכד ';
                $grgrchild = 'נין ';
            } else {
                $grchild = 'נכדה ';
                $grgrchild = 'נינה ';
            }
            $gennr = $pers - 2;
            if ($pers == 2) {
                $this->relation['rel_text'] = $grchild . __(' of ');
            } elseif ($pers > 2) {
                $degree = '';
                if ($pers > 3) {
                    $degree = 'דרגה ' . $gennr;
                }
                $this->relation['rel_text'] = $grgrchild . $degree . __(' of ');
            }
        } elseif ($this->selected_language == "fi") {
            if ($pers == 2) {
                $this->relation['rel_text'] = __('grandchild') . __(' of ');
            }
            $gennr = $pers - 1;
            if ($pers >  2) {
                $this->relation['rel_text'] = $gennr . '. ' . __('grandchild') . __(' of ');
            }
        } elseif ($this->selected_language == "no") {
            $child = 'barnet'; // barn
            if ($pers == 2) {
                // barnebarn
                $this->relation['rel_text'] = 'barnebarnet ' . __(' of ');
            }
            if ($pers == 3) {
                // olde + barn
                $this->relation['rel_text'] = __('great-grand') . $child . __(' of ');
            }
            if ($pers == 4) {
                // tippolde + barn
                $this->relation['rel_text'] = 'tippolde' . $child . __(' of ');
            }
            if ($pers == 5) {
                // tipp-tippolde + barn
                $this->relation['rel_text'] = 'tipp-tippolde' . $child . __(' of ');
            }
            $gennr = $pers - 3;
            if ($pers >  5) {
                $this->relation['rel_text'] = $gennr . 'x tipp-tippolde' . $child . __(' of ');
            }
        } elseif ($this->selected_language == "da") {
            // right person is spouse of Y, not Y
            if ($this->relation['spouse'] == "1" || $this->relation['spouse'] == "3") {
                $relarr = $this->rel_arrayspouseX;
            } else {
                $relarr = $this->rel_arrayX;
            }

            if ($pers == 2) {
                // grandchild
                $arrnum = 0;
                $ancsarr = array();
                $count = $this->relation['foundX_nr'];
                while ($count != 0) {
                    $parnumber = $count;
                    $ancsarr[$arrnum] = $parnumber;
                    $arrnum++;
                    $count = $relarr[$count][2];
                }
                $persidDb = $this->db_functions->get_person($relarr[$this->relation['foundX_nr']][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == 'M') {
                    $this->relation['rel_text'] = 'sønne' . $child . __(' of ');
                } else {
                    $this->relation['rel_text'] = 'datter' . $child . __(' of ');
                }
            }

            if ($pers == 3) {
                // oldeson oldedatter
                $this->relation['rel_text'] = 'olde' . $child . __(' of ');
            }
            if ($pers == 4) {
                // tip oldeson
                $this->relation['rel_text'] = 'tip olde' . $child . __(' of ');
            }
            if ($pers == 5) {
                // tip tip oldeson
                $this->relation['rel_text'] = 'tip tip olde' . $child . __(' of ');
            }
            if ($pers == 6) {
                // tip tip tip oldeson
                $this->relation['rel_text'] = 'tip tip tip olde' . $child . __(' of ');
            }
            $gennr = $pers - 3;
            if ($pers >  6) {
                $this->relation['rel_text'] = $gennr . ' gange tip olde' . $child . __(' of ');
            }
        }
        // Swedish needs to know if grandchild is related through son or daughter - different names there
        // also for great-grandchild and 2nd great-grandchild!!!
        elseif ($this->selected_language == "sv") {
            if ($this->relation['spouse'] == "1" || $this->relation['spouse'] == "3") { // right person is spouse of Y, not Y
                $relarr = $this->rel_arrayspouseX;
            } else {
                $relarr = $this->rel_arrayX;
            }

            if ($pers > 1) {
                // grandchild
                $arrnum = 0;
                //reset($ancsarr);
                $count = $this->relation['foundX_nr'];
                while ($count != 0) {
                    $parnumber = $count;
                    $ancsarr[$arrnum] = $parnumber;
                    $arrnum++;
                    //$count=$this->rel_arrayX[$count][2];
                    $count = $relarr[$count][2];
                }
                $persidDb = $this->db_functions->get_person($relarr[$this->relation['foundX_nr']][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == 'M') {
                    $se_grandch = 'son' . $child;
                    //$direct_ch = 'son';
                } else {
                    $se_grandch = 'dotter' . $child;
                    //$direct_ch = 'dotter';
                }
            }

            if ($pers > 2) {
                // great-grandchild
                $persidDb2 = $this->db_functions->get_person($relarr[$ancsarr[1]][0]);
                $parsexe2 = $persidDb2->pers_sexe;

                if ($parsexe2 == "M") {
                    if ($parsexe == "M") {
                        $se_gr_grandch = 'sonsons ' . $child;
                    } else {
                        $se_gr_grandch = 'dottersons ' . $child;
                    }
                } else {
                    if ($parsexe == "M") {
                        $se_gr_grandch = 'sondotters ' . $child;
                    } else {
                        $se_gr_grandch = 'dotterdotters ' . $child;
                    }
                }
            }

            if ($pers > 3) {
                // 2nd great-grandchild
                $persidDb3 = $this->db_functions->get_person($relarr[$ancsarr[2]][0]);
                $parsexe3 = $persidDb3->pers_sexe;
                if ($parsexe3 == "M") {
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") {
                            $se_2ndgr_grandch = 'sonsons son' . $child;
                        } else {
                            $se_2ndgr_grandch = 'dottersons son' . $child;
                        }
                    } else {
                        if ($parsexe == "M") {
                            $se_2ndgr_grandch = 'sondotters son' . $child;
                        } else {
                            $se_2ndgr_grandch = 'dotterdotters son' . $child;
                        }
                    }
                } else {
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") {
                            $se_2ndgr_grandch = 'sonsons dotter' . $child;
                        } else {
                            $se_2ndgr_grandch = 'dottersons dotter' . $child;
                        }
                    } else {
                        if ($parsexe == "M") {
                            $se_2ndgr_grandch = 'sondotters dotter' . $child;
                        } else {
                            $se_2ndgr_grandch = 'dotterdotters dotter' . $child;
                        }
                    }
                }
            }

            if ($pers == 2) {
                $this->relation['rel_text'] = $se_grandch . __(' of ');
            }
            if ($pers == 3) {
                $this->relation['rel_text'] = $se_gr_grandch . __(' of ');
            }
            if ($pers == 4) {
                $this->relation['rel_text'] = $se_2ndgr_grandch . __(' of ');
            }
            $gennr = $pers;
            if ($pers >  4) {
                $this->relation['rel_text'] = $gennr . ':e generations barn' . __(' of ');
            }
        } elseif ($this->selected_language == "cn") {
            // instead of A is grandson of B we say: A's grandfather is B
            if ($this->relation['sexe2'] == 'M' && $this->relation['spouse'] != 2 && $this->relation['spouse'] != 3 || $this->relation['sexe2'] == 'F' && ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3)) {
                // grandfather, great-grandfather etc
                //if($this->relation['sexe2']=="m") {
                if ($pers == 2) {
                    $this->relation['rel_text'] = '祖父';
                }
                if ($pers == 3) {
                    $this->relation['rel_text'] = '曾祖父';
                }
                if ($pers == 4) {
                    $this->relation['rel_text'] = '高祖父';
                }
                if ($pers > 4) {
                    $this->relation['rel_text'] = 'notext';
                }
                // in Chinese don't display text after 2nd great grandfather
            } else {
                // grandmother etc
                if ($pers == 2) {
                    $this->relation['rel_text'] = '祖母';
                }
                if ($pers == 3) {
                    $this->relation['rel_text'] = '曾祖母';
                }
                if ($pers == 4) {
                    $this->relation['rel_text'] = '高祖母';
                }
                if ($pers > 4) {
                    $this->relation['rel_text'] = 'notext';
                }
                // in Chinese don't display text after 2nd great grandmother
            }
            $this->relation['rel_text'] .= '是';
        } elseif ($this->selected_language == "fr") {
            if ($this->relation['sexe1'] == 'M') {
                $gend = '';
            } else {
                $gend = "e";
            }
            if ($pers == 2) {
                $this->relation['rel_text'] = 'petit' . $gend . '-' . $child . __(' of ');
            }
            if ($pers == 3) {
                $this->relation['rel_text'] = 'arrière-petit' . $gend . '-' . $child . __(' of ');
            }
            if ($pers == 4) {
                $this->relation['rel_text'] = 'arrière-arrière-petit' . $gend . '-' . $child . __(' of ');
            }
            if ($pers == 5) {
                $this->relation['rel_text'] = 'arrière-arrière-arrière-petit' . $gend . '-' . $child . __(' of ');
            }
            $gennr = $pers - 2;
            if ($pers >  5) {
                $this->relation['rel_text'] = 'arrière (' . ($pers - 2) . ' fois) petit' . $gend . '-' . $child . __(' of ');
            }
        } elseif ($this->selected_language == "ro") {
            if ($pers == 2) {
                if ($this->relation['sexe1'] == 'M') {
                    $this->relation['rel_text'] = 'nepotul' . __(' of ');
                } else {
                    $this->relation['rel_text'] = 'nepoata' . __(' of ');
                }
            }
            if ($pers == 3) {
                if ($this->relation['sexe1'] == 'M') {
                    $this->relation['rel_text'] = 'strănepotul' . __(' of ');
                } else {
                    $this->relation['rel_text'] = 'strănepoata' . __(' of ');
                }
            }

            // Example:
            // stră-străpotul, stra-străpoata, stră-stră-străpotul, stră-stră-străpoata, stră-stră-stră-străpotul, stră-stră-stră-străpoata
            // stră-străpoata, stră-stră-străpoata, stră-stră-stră-străpoata, stră-stră-stră-stră-străpoata, stră-stră-stră-stră-stră-străpoata
            if ($pers > 3) {
                $this->relation['rel_text'] = '';
                for ($i = 4; $i <= $pers; $i++) {
                    $this->relation['rel_text'] .= 'stră-';
                }
                if ($this->relation['sexe1'] == 'M') {
                    $this->relation['rel_text'] .= 'strănepotul' . __(' of ');
                } else {
                    $this->relation['rel_text'] .= 'strănepoata' . __(' of ');
                }
            }
        } else {
            if ($pers == 2) {
                $this->relation['rel_text'] = __('grand') . $child . __(' of ');
            }
            if ($pers == 3) {
                $this->relation['rel_text'] = __('great-grand') . $child . __(' of ');
            }
            if ($pers == 4) {
                $this->relation['rel_text'] = __('2nd') . ' ' . __('great-grand') . $child . __(' of ');
            }
            if ($pers == 5) {
                $this->relation['rel_text'] = __('3rd') . ' ' . __('great-grand') . $child . __(' of ');
            }
            $gennr = $pers - 2;
            if ($pers >  5) {
                $this->relation['rel_text'] = $gennr . __('th') . ' ' . __('great-grand') . $child . __(' of ');
            }
        }
    }

    private function calculate_nephews($generX): void
    {
        // handed generations x is removed from common ancestor
        // *** Greek***
        // *** Ελληνικά***
        if ($this->selected_language == "gr") {
            if ($this->relation['sexe1'] == "M") {
                $neph = 'ανιψιος';
                $gr_postfix = "ος ";
                $grson = 'εγγονός';
                $grgrson = 'δισέγγονος';
            } else {
                $neph = 'ανιψιά';
                $gr_postfix = "η ";
                $grson = 'εγγονή';
                $grgrson = 'δισέγγονη';
            }
            $gendiff = $generX - 1;
            $gennr = $gendiff - 1;
            $degree = $grson . " " . $gennr . $gr_postfix;
            if ($gendiff == 1) {
                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $neph . ' του ';
                    } else {
                        $this->relation['rel_text'] = $neph . ' της ';
                    }
                } else {
                    if ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = $neph . ' του ';
                        } else {
                            $this->relation['rel_text'] = $neph . ' της ';
                        }
                    }
                }
            } elseif ($gendiff == 2) {
                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $neph . " " . $grson . ' του ';
                    } else {
                        $this->relation['rel_text'] = $neph . " " . $grson . ' της ';
                    }
                } else {
                    if ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = $neph . " " . $grson . ' του ';
                        } else {
                            $this->relation['rel_text'] = $neph . " " . $grson . ' της ';
                        }
                    }
                }
            } else {
                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $neph . " " . $grgrson . ' του ';
                    } else {
                        $this->relation['rel_text'] = $neph . " " . $grgrson . ' της ';
                    }
                } else {
                    if ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = $neph . " " . $grgrson . ' του ';
                        } else {
                            $this->relation['rel_text'] = $neph . " " . $grgrson . ' της ';
                        }
                    }
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end*** 
        } elseif ($this->selected_language == "es") {
            if ($this->relation['sexe1'] == "M") {
                $neph = __('nephew');
                $span_postfix = "o ";
                $grson = 'nieto';
            } else {
                $neph = __('niece');
                $span_postfix = "a ";
                $grson = 'nieta';
            }
            $gendiff = $generX - 1;
            $gennr = $gendiff - 1;
            $degree = $grson . " " . $gennr . $span_postfix;
            if ($gendiff == 1) {
                $this->relation['rel_text'] = $neph . __(' of ');
            } elseif ($gendiff > 1 && $gendiff < 27) {
                $this->relation['rel_text'] = $neph . " " . $this->spanish_degrees($gendiff) . $grson . __(' of ');
            } else {
                $this->relation['rel_text'] = $neph . " " . $degree;
            }
        } elseif ($this->selected_language == "he") {
            $nephniece = $this->relation['sexe1'] == 'M' ? __('nephew') : __('niece');
            $gendiff = $generX - 1;
            if ($gendiff == 1) {
                $this->relation['rel_text'] = $nephniece . __(' of ');
            } elseif ($gendiff > 1) {
                $degree = ' דרגה ' . $gendiff;
                $this->relation['rel_text'] = $nephniece . $degree . __(' of ');
            }
        } elseif ($this->selected_language == "fi") {
            $nephniece = $this->relation['sexe1'] == 'M' ? __('nephew') : __('niece');
            if ($generX == 2) {
                $this->relation['rel_text'] = $nephniece . __(' of ');
            }
            if ($generX == 3) {
                $this->relation['rel_text'] = __('grand') . $nephniece . __(' of ');
            }
            $gennr = $generX - 2;
            if ($generX >  3) {
                $this->relation['rel_text'] = $gennr . '. ' . __('grand') . $nephniece . __(' of ');
            }
        } elseif ($this->selected_language == "no") {
            $nephniece = $this->relation['sexe1'] == 'M' ? __('nephew') : __('niece');
            $this->relation['rel_text_nor_dan'] = '';
            $this->relation['rel_text_nor_dan2'] = '';
            if ($generX > 3) {
                // for: A er oldebarnet av Bs søsken
                $this->relation['rel_text_nor_dan'] = "s " . substr('søskenet', 0, -2);
                // for: A er oldebarnet av søskenet av mannen til B
                $this->relation['rel_text_nor_dan2'] = 'søskenet' . __(' of ');
            }
            if ($generX == 2) {
                $this->relation['rel_text'] = $nephniece . __(' of ');
            }
            if ($generX == 3) {
                $this->relation['rel_text'] = 'grand' . $nephniece . __(' of ');
            }
            if ($generX == 4) {
                $this->relation['rel_text'] = __('great-grand') . ' barnet' . __(' of ');
            }
            if ($generX == 5) {
                $this->relation['rel_text'] = 'tippolde barnet' . __(' of ');
            }
            if ($generX == 6) {
                $this->relation['rel_text'] = 'tipp-tippolde barnet' . __(' of ');
            }
            $gennr = $generX - 4;
            if ($generX >  6) {
                $this->relation['rel_text'] = $gennr . 'x tippolde barnet' . __(' of ');
            }
        } elseif ($this->selected_language == "da") {
            $nephniece = $this->relation['sexe1'] == 'M' ? __('nephew') : __('niece');
            $this->relation['rel_text_nor_dan'] = '';
            $this->relation['rel_text_nor_dan2'] = '';
            if ($generX > 3) {
                // for: A er oldebarn af Bs søskende
                $this->relation['rel_text_nor_dan'] = "s søskende";
                // for: A er oldebarn af søskende af ..... til B
                $this->relation['rel_text_nor_dan2'] = 'søskende' . __(' of ');
            }
            if ($generX == 2) {
                $this->relation['rel_text'] = $nephniece . __(' of ');
            }
            if ($generX == 3) {
                $this->relation['rel_text'] = 'grand' . $nephniece . __(' of ');
            }
            if ($generX == 4) {
                $this->relation['rel_text'] = 'oldebarn' . __(' of ');
            }
            if ($generX == 5) {
                $this->relation['rel_text'] = 'tip oldebarn' . __(' of ');
            }
            if ($generX == 6) {
                $this->relation['rel_text'] = 'tip tip oldebarn' . __(' of ');
            }
            if ($generX == 7) {
                $this->relation['rel_text'] = 'tip tip tip oldebarn' . __(' of ');
            }
            $gennr = $generX - 4;
            if ($generX >  7) {
                $this->relation['rel_text'] = $gennr . ' gange tip oldebarn' . __(' of ');
            }
        } elseif ($this->selected_language == "nl") {
            $nephniece = $this->relation['sexe1'] == 'M' ? __('nephew') : __('niece');
            // in Dutch we use the __('3rd [COUSIN]') variables, that works for nephews as well
            if ($generX == 2) {
                $this->relation['rel_text'] = $nephniece . __(' of ');
            }
            if ($generX == 3) {
                $this->relation['rel_text'] = __('2nd [COUSIN]') . $nephniece . __(' of ');
            }
            if ($generX == 4) {
                $this->relation['rel_text'] = __('3rd [COUSIN]') . $nephniece . __(' of ');
            }
            if ($generX == 5) {
                $this->relation['rel_text'] = __('2nd') . ' ' . __('3rd [COUSIN]') . $nephniece . __(' of ');
            }
            if ($generX == 6) {
                $this->relation['rel_text'] = __('3rd') . ' ' . __('3rd [COUSIN]') . $nephniece . __(' of ');
            }
            $gennr = $generX - 3;
            if ($generX >  6) {
                $this->relation['rel_text'] = $gennr . __('th ') . __('3rd [COUSIN]') . $nephniece . __(' of ');
            }
        } elseif ($this->selected_language == "sv") {
            // Swedish needs to know if nephew/niece is related through brother or sister - different names there
            // also for grandnephew!!!
            // right person is spouse of Y, not Y
            if ($this->relation['spouse'] == "1" || $this->relation['spouse'] == "3") {
                $relarr = $this->rel_arrayspouseX;
            } else {
                $relarr = $this->rel_arrayX;
            }

            if ($this->relation['sexe1'] == 'M') {
                $nephniece = "son";
            } else {
                $nephniece = "dotter";
            }
            if ($generX > 1) {
                // niece/nephew
                $arrnum = 0;
                //reset($ancsarr);
                $count = $this->relation['foundX_nr'];
                while ($count != 0) {
                    $parnumber = $count;
                    $ancsarr[$arrnum] = $parnumber;
                    $arrnum++;
                    $count = $relarr[$count][2];
                }
                $persidDb = $this->db_functions->get_person($relarr[$parnumber][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == 'M') {
                    $se_nephniece = 'bror' . $nephniece;
                } else {
                    $se_nephniece = 'syster' . $nephniece;
                }
            }
            if ($generX == 3) {
                // grandniece/nephew
                $persidDb2 = $this->db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
                $parsexe2 = $persidDb2->pers_sexe;
                if ($parsexe2 == "M") {
                    if ($parsexe == "M") {
                        $se_gr_nephniece = 'brors son' . $nephniece;
                    } else {
                        $se_gr_nephniece = 'brors dotter' . $nephniece;
                    }
                } else {
                    if ($parsexe == "M") {
                        $se_gr_nephniece = 'systers son' . $nephniece;
                    } else {
                        $se_gr_nephniece = 'systers dotter' . $nephniece;
                    }
                }
            }
            if ($generX == 2) {
                $this->relation['rel_text'] = $se_nephniece . __(' of ');
            }
            if ($generX == 3) {
                $this->relation['rel_text'] = $se_gr_nephniece . __(' of ');
            }
            $gennr = $generX - 1;
            if ($generX >  3) {
                $persidDb = $this->db_functions->get_person($this->rel_arrayX[$this->relation['foundX_nr']][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == 'M') {
                    $se_sib = "bror";
                } else {
                    $se_sib = "syster";
                }
                $this->relation['rel_text'] = $se_sib . 's ' . $gennr . ':e generations barn' . __(' of ');
            }
        } elseif ($this->selected_language == "cn") {
            // Used: http://www.kwanfamily.info/culture/familytitles_table.php
            if ($this->relation['spouse'] == "1") { // left person is spouse of X, not X
                $relarrX = $this->rel_arrayspouseX;
            } else {
                $relarrX = $this->rel_arrayX;
            }
            $arrnumX = 0;
            if (isset($ancsarrX)) {
                reset($ancsarrX);
            }
            $count = $this->relation['foundX_nr'];
            while ($count != 0) {
                $parnumberX = $count;
                $ancsarrX[$arrnumX] = $parnumberX;
                $arrnumX++;
                $count = $relarrX[$count][2];
            }
            $persidDbX = $this->db_functions->get_person($relarrX[$parnumberX][0]);
            $parsexeX = $persidDbX->pers_sexe;
            if ($parsexeX == 'M') {
                // uncle/aunt from father's side
                if (($this->relation['sexe2'] == "M" && $this->relation['spouse'] != 2 && $this->relation['spouse'] != 3) || ($this->relation['sexe2'] == "F" && ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3))) {
                    $this->relation['rel_text'] = '伯父(叔父)是';  // uncle - brother of father
                } else {
                    $this->relation['rel_text'] = '姑母是';  // aunt - sister of father
                }
            } else {
                // uncle/aunt from mother's side
                if (($this->relation['sexe2'] == "M" && $this->relation['spouse'] != 2 && $this->relation['spouse'] != 3) || ($this->relation['sexe2'] == "F" && ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3))) {
                    $this->relation['rel_text'] = '舅父是';  // uncle - brother of mother
                } else {
                    $this->relation['rel_text'] = '姨母(姨)是';  // aunt - sister of mother
                }
            }

            /*
            if(($this->relation['sexe2']=='m' && $this->relation['spouse']!=2 && $this->relation['spouse']!=3) || ($this->relation['sexe2'] == "F" && ($this->relation['spouse']==2 || $this->relation['spouse']==3))) {  
                $nephniece = '叔伯是';  // A's uncle is B
            }
            else {
                $nephniece = '婶娘是';  //  A's aunt is B
            }
            */
            if ($generX == 2) {
            }
            if ($generX > 2) {
                // suppress text - "granduncle" etc is not (yet) supported in Chinese
                $this->relation['rel_text'] = "notext";
            }
        } elseif ($this->selected_language == "fr") {
            if ($this->relation['sexe1'] == 'M') {
                $nephniece = __('nephew');
                $gend = '';
            } else {
                $nephniece = __('niece');
                $gend = "e";
            }
            if ($generX == 2) {
                $this->relation['rel_text'] = $nephniece . __(' of ');
            }
            if ($generX == 3) {
                $this->relation['rel_text'] = 'petit' . $gend . '-' . $nephniece . __(' of ');
            }
            if ($generX == 4) {
                $this->relation['rel_text'] = 'arrière-petit' . $gend . '-' . $nephniece . __(' of ');
            }
            if ($generX == 5) {
                $this->relation['rel_text'] = 'arrière-arrière-petit' . $gend . '-' . $nephniece . __(' of ');
            }
            if ($generX == 6) {
                $this->relation['rel_text'] = 'arrière-arrière-arrière-petit' . $gend . '-' . $nephniece . __(' of ');
            }
            $gennr = $generX - 3;
            if ($generX >  6) {
                $this->relation['rel_text'] = 'arrière (' . $gennr . ' fois) petit' . $gend . '-' . $nephniece . __(' of ');
            }
        } elseif ($this->selected_language == "ro") {
            // Example: vărul de-al 2-lea a / verișoara de-a 2-a a
            if ($generX > 1) {
                if ($this->relation['sexe1'] == 'M') {
                    $this->relation['rel_text'] = 'vărul de-al ' . $generX . '-lea a';
                } else {
                    $this->relation['rel_text'] = 'verișoara de-a ' . $generX . '-a a';
                }
            }
        } else {
            $nephniece = $this->relation['sexe1'] == 'M' ? __('nephew') : __('niece');
            if ($generX == 2) {
                $this->relation['rel_text'] = $nephniece . __(' of ');
            }
            if ($generX == 3) {
                $this->relation['rel_text'] = __('grand') . $nephniece . __(' of ');
            }
            if ($generX == 4) {
                $this->relation['rel_text'] = __('great-grand') . $nephniece . __(' of ');
            }
            if ($generX == 5) {
                $this->relation['rel_text'] = __('2nd') . ' ' . __('great-grand') . $nephniece . __(' of ');
            }
            if ($generX == 6) {
                $this->relation['rel_text'] = __('3rd') . ' ' . __('great-grand') . $nephniece . __(' of ');
            }
            $gennr = $generX - 3;
            if ($generX >  6) {
                $this->relation['rel_text'] = $gennr . __('th ') . __('great-grand') . $nephniece . __(' of ');
            }
        }
    }

    // handed generations y is removed from common ancestor
    private function calculate_uncles($generY): void
    {
        if ($this->relation['sexe1'] == 'M') {
            $uncleaunt = __('uncle');
            if ($this->selected_language == "cn") {
                // A's nephew/niece is B
                // Used: http://www.kwanfamily.info/culture/familytitles_table.php
                // Other translations (not used):  dongshan: nephew: 侄子是  niece 侄女是
                // right person is spouse of Y, not Y
                if ($this->relation['spouse'] == "2" || $this->relation['spouse'] == "3") {
                    $relarrY = $this->rel_arrayspouseY;
                } else {
                    $relarrY = $this->rel_arrayY;
                }
                $arrnumY = 0;
                if (isset($ancsarrY)) {
                    reset($ancsarrY);
                }
                $count = $this->relation['foundY_nr'];
                while ($count != 0) {
                    $parnumberY = $count;
                    $ancsarrY[$arrnumY] = $parnumberY;
                    $arrnumY++;
                    $count = $relarrY[$count][2];
                }
                $persidDbY = $this->db_functions->get_person($relarrY[$parnumberY][0]);
                $parsexeY = $persidDbY->pers_sexe;
                if ($parsexeY == "M") { // is child of brother
                    if (($this->relation['sexe2'] == 'M' && $this->relation['spouse'] != 2 && $this->relation['spouse'] != 3) || ($this->relation['sexe2'] == 'F' && ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3))) {
                        $uncleaunt = '姪子是';
                    } else {
                        $uncleaunt = '姪女是';
                    }
                } else {
                    // is child of sister - term depends also on sex of A
                    if (($this->relation['sexe2'] == 'M' && $this->relation['spouse'] != 2 && $this->relation['spouse'] != 3) || ($this->relation['sexe2'] == 'F' && ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3))) {
                        if ($this->relation['sexe1'] == "M") {
                            // son of sister (A is male)
                            $uncleaunt = '外甥是';
                        } else {
                            // son of sister (A is female)
                            $uncleaunt = '姨甥是';
                        }
                    } else {
                        if ($this->relation['sexe1'] == "M") {
                            // daughter of sister (A is male)
                            $uncleaunt = '外甥女是';
                        } else {
                            // daughter of sister (A is female)
                            $uncleaunt = '姨甥女是';
                        }
                    }
                }
            }

            // Finnish needs to know if uncle is related through mother or father - different names there
            if ($this->selected_language == "fi") {
                $count = $this->relation['foundY_nr'];
                while ($count != 0) {
                    $parnumber = $count;
                    $count = $this->rel_arrayY[$count][2];
                }
                $persidDb = $this->db_functions->get_person($this->rel_arrayY[$parnumber][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == 'M') {
                    $uncleaunt = 'setä';
                } else {
                    $uncleaunt = 'eno';
                }
            }

            // Swedish needs to know if uncle is related through mother or father - different names there
            // also for granduncle and great-granduncle!!!
            if ($this->selected_language == "sv") {
                if ($this->relation['spouse'] == "2" || $this->relation['spouse'] == "3") { // right person is spouse of Y, not Y
                    $relarr = $this->rel_arrayspouseY;
                } else {
                    $relarr = $this->rel_arrayY;
                }

                $se_sibling = "bror"; // used for gr_gr_granduncle and more "4:e gen anas bror"
                // uncle
                $arrnum = 0;
                //reset($ancsarr);
                $count = $this->relation['foundY_nr'];
                while ($count != 0) {
                    $parnumber = $count;
                    $ancsarr[$arrnum] = $parnumber;
                    $arrnum++;
                    $count = $relarr[$count][2];
                }
                $persidDb = $this->db_functions->get_person($relarr[$parnumber][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == 'M') {
                    $uncleaunt = 'farbror';
                } else {
                    $uncleaunt = 'morbror';
                }

                if ($generY > 2) {
                    // granduncle
                    $persidDb2 = $this->db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
                    $parsexe2 = $persidDb2->pers_sexe;
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") {
                            $se_granduncleaunt = 'fars farbror';
                        } else {
                            $se_granduncleaunt = 'mors farbror';
                        }
                    } else {
                        if ($parsexe == "M") {
                            $se_granduncleaunt = 'fars morbror';
                        } else {
                            $se_granduncleaunt = 'mors morbror';
                        }
                    }
                }

                if ($generY > 3) {
                    // great-granduncle
                    $persidDb3 = $this->db_functions->get_person($relarr[$ancsarr[$arrnum - 3]][0]);
                    $parsexe3 = $persidDb3->pers_sexe;
                    if ($parsexe3 == "M") {
                        if ($parsexe2 == "M") {
                            if ($parsexe == "M") {
                                $se_gr_granduncleaunt = 'farfars farbror';
                            } else {
                                $se_gr_granduncleaunt = 'morfars farbror';
                            }
                        } else {
                            if ($parsexe == "M") {
                                $se_gr_granduncleaunt = 'farmors farbror';
                            } else {
                                $se_gr_granduncleaunt = 'mormors farbror';
                            }
                        }
                    } else {
                        if ($parsexe2 == "M") {
                            if ($parsexe == "M") {
                                $se_gr_granduncleaunt = 'farfars morbror';
                            } else {
                                $se_gr_granduncleaunt = 'morfars morbror';
                            }
                        } else {
                            if ($parsexe == "M") {
                                $se_gr_granduncleaunt = 'farmors morbror';
                            } else {
                                $se_gr_granduncleaunt = 'mormors morbror';
                            }
                        }
                    }
                }
            }
        } else {
            $uncleaunt = __('aunt');
            if ($this->selected_language == "cn") {
                if ($this->relation['sexe2'] == "M") {
                    // "A's nephew is B"
                    $uncleaunt = '侄子是';
                } else {
                    // "A's niece is B"
                    $uncleaunt = '侄女是';
                }
            }

            // Swedish needs to know if aunt is related through mother or father - different names there
            // also for grandaunt and great-grandaunt!!!
            if ($this->selected_language == "sv") {
                if ($this->relation['spouse'] == "2" || $this->relation['spouse'] == "3") { // right person is spouse of Y, not Y
                    $relarr = $this->rel_arrayspouseY;
                } else {
                    $relarr = $this->rel_arrayY;
                }

                $se_sibling = "syster"; // used for gr_gr_grandaunt and more "4:e gen anas syster"
                // aunt
                $arrnum = 0;
                //reset($ancsarr);
                $count = $this->relation['foundY_nr'];
                while ($count != 0) {
                    $parnumber = $count;
                    $ancsarr[$arrnum] = $parnumber;
                    $arrnum++;
                    $count = $relarr[$count][2];
                }
                $persidDb = $this->db_functions->get_person($relarr[$parnumber][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == 'M') {
                    $uncleaunt = 'faster';
                } else {
                    $uncleaunt = 'moster';
                }

                if ($generY > 2) {
                    // grandaunt
                    $persidDb2 = $this->db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
                    $parsexe2 = $persidDb2->pers_sexe;
                    if ($parsexe2 == "M") {
                        if ($parsexe == "M") {
                            $se_granduncleaunt = 'fars faster';
                        } else {
                            $se_granduncleaunt = 'mors faster';
                        }
                    } else {
                        if ($parsexe == "M") {
                            $se_granduncleaunt = 'fars moster';
                        } else {
                            $se_granduncleaunt = 'mors moster';
                        }
                    }
                }

                if ($generY > 3) {
                    // great-grandaunt
                    $persidDb3 = $this->db_functions->get_person($relarr[$ancsarr[$arrnum - 3]][0]);
                    $parsexe3 = $persidDb3->pers_sexe;
                    if ($parsexe3 == "M") {
                        if ($parsexe2 == "M") {
                            if ($parsexe == "M") {
                                $se_gr_granduncleaunt = 'farfars faster';
                            } else {
                                $se_gr_granduncleaunt = 'morfars faster';
                            }
                        } else {
                            if ($parsexe == "M") {
                                $se_gr_granduncleaunt = 'farmors faster';
                            } else {
                                $se_gr_granduncleaunt = 'mormors faster';
                            }
                        }
                    } else {
                        if ($parsexe2 == "M") {
                            if ($parsexe == "M") {
                                $se_gr_granduncleaunt = 'farfars moster';
                            } else {
                                $se_gr_granduncleaunt = 'morfars moster';
                            }
                        } else {
                            if ($parsexe == "M") {
                                $se_gr_granduncleaunt = 'farmors moster';
                            } else {
                                $se_gr_granduncleaunt = 'mormors moster';
                            }
                        }
                    }
                }
            }
        }

        if ($this->selected_language == "nl") {
            $ancestortext = $this->dutch_ancestors($generY - 1);
            $this->relation['rel_text'] = $ancestortext . $uncleaunt . __(' of ');
            if ($generY > 4) {
                $gennr = $generY - 3;
                $this->relation['dutch_text'] =  "(" . $ancestortext . $uncleaunt . " = " . $gennr . __('th') . ' ' . __('great-grand') . $uncleaunt . ")";
            }
            // *** Greek***
            // *** Ελληνικά θείος***
        } elseif ($this->selected_language == "gr") {
            // TODO improve code
            if ($this->relation['sexe1'] == "M") {
                $uncle = 'θείος';
                $gr_postfix = "ος ";
                $gran = 'παππούς';
                $grgrparent = 'προπάππος';
            } else {
                $uncle = 'θεία';
                $gr_postfix = "η ";
                $gran = 'γιαγιά';
                $grgrparent = 'προγιαγιά';
            }
            $gendiff = $generY - 1;
            $gennr = $gendiff - 1;
            $degree = $gran . " " . $gennr . $gr_postfix;
            if ($gendiff == 1) {
                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $uncle . ' του ';
                    } else {
                        $this->relation['rel_text'] = $uncle . ' της ';
                    }
                } else {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $uncle . ' του ';
                    } else {
                        $this->relation['rel_text'] =  $uncle . ' της ';
                    }
                }
            } elseif ($gendiff == 2) {
                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $uncle . " " . $gran . ' του ';
                    } else {
                        $this->relation['rel_text'] = $uncle . " " . $gran . ' της ';
                    }
                } else {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $uncle . " " . $gran . ' του ';
                    } else {
                        $this->relation['rel_text'] = $uncle . " " . $gran . ' της ';
                    }
                }
            } elseif ($gendiff > 2) {

                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $uncle . " " . $grgrparent . ' του ';
                    } else {
                        $this->relation['rel_text'] = $uncle . " " . $grgrparent . ' της ';
                    }
                } else {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $uncle . " " . $grgrparent . ' του ';
                    } else {
                        $this->relation['rel_text'] = $uncle . " " . $grgrparent . ' της ';
                    }
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end*** 
        } elseif ($this->selected_language == "es") {
            if ($this->relation['sexe1'] == "M") {
                $uncle = __('uncle');
                $span_postfix = "o ";
                $gran = 'abuelo';
            } else {
                $uncle = __('aunt');
                $span_postfix = "a ";
                $gran = 'abuela';
            }
            $gendiff = $generY - 1;
            $gennr = $gendiff - 1;
            $degree = $gran . " " . $gennr . $span_postfix;
            if ($gendiff == 1) {
                $this->relation['rel_text'] = $uncle . __(' of ');
            } elseif ($gendiff > 1 && $gendiff < 27) {
                $this->relation['rel_text'] = $uncle . " " . $this->spanish_degrees($gendiff) . $gran . __(' of ');
            } else {
                $this->relation['rel_text'] = $uncle . " " . $degree;
            }
        } elseif ($this->selected_language == "he") {
            $gendiff = $generY - 1;
            if ($gendiff == 1) {
                $this->relation['rel_text'] = $uncleaunt . __(' of ');
            } elseif ($gendiff > 1) {
                $degree = ' דרגה ' . $gendiff;
                $this->relation['rel_text'] = $uncleaunt . $degree . __(' of ');
            }
        } elseif ($this->selected_language == "fi") {
            if ($generY == 2) {
                $this->relation['rel_text'] = $uncleaunt . __(' of ');
            }
            if ($generY == 3) {
                $this->relation['rel_text'] = __('grand') . $uncleaunt . __(' of ');
            }
            $gennr = $generY - 2;
            if ($generY >  3) {
                $this->relation['rel_text'] = $gennr . __('th') . ' ' . __('grand') . $uncleaunt . __(' of ');
            }
        } elseif ($this->selected_language == "sv") {
            if ($generY == 2) {
                $this->relation['rel_text'] = $uncleaunt . __(' of ');
            }
            if ($generY == 3) {
                $this->relation['rel_text'] = $se_granduncleaunt . __(' of ');
            }
            if ($generY == 4) {
                $this->relation['rel_text'] = $se_gr_granduncleaunt . __(' of ');
            }
            $gennr = $generY - 1;
            if ($generY >  4) {
                $this->relation['rel_text'] = $gennr . ':e gen anas ' . $se_sibling . __(' of ');
            }
        } elseif ($this->selected_language == "no") {
            $temptext = '';
            $this->relation['rel_text_nor_dan'] = '';
            $this->relation['rel_text_nor_dan2'] = '';
            if ($generY == 2) {
                $this->relation['rel_text'] = $uncleaunt . __(' of ');
            }
            if ($generY == 3) {
                $this->relation['rel_text'] = 'grand' . $uncleaunt . __(' of ');
            }
            if ($generY > 3) {
                if ($uncleaunt == __('uncle')) {
                    $this->relation['rel_text'] = __('brother of ');
                } else {
                    $this->relation['rel_text'] = __('sister of ');
                }
            }
            if ($generY == 4) {
                $temptext = 'oldeforelderen';
            }
            if ($generY == 5) {
                $temptext = 'tippoldeforelderen';
            }
            if ($generY == 6) {
                $temptext = 'tipp-tippoldeforelderen';
            }
            $gennr = $generY - 4;
            if ($generY >  6) {
                $temptext = $gennr . 'x tippoldeforelderen';
            }
            if ($temptext !== '') {
                $this->relation['rel_text_nor_dan'] = "s " . substr($temptext, 0, -2);
                $this->relation['rel_text_nor_dan2'] = $temptext . __(' of ');
            }
        } elseif ($this->selected_language == "da") {
            $temptext = '';
            $this->relation['rel_text_nor_dan'] = '';
            $this->relation['rel_text_nor_dan2'] = '';
            if ($generY == 2) {
                $this->relation['rel_text'] = $uncleaunt . ' til ';
            }
            if ($generY == 3) {
                $this->relation['rel_text'] = 'grand' . $uncleaunt . ' til ';
            }
            if ($generY > 3) {
                if ($uncleaunt == __('uncle')) {
                    $this->relation['rel_text'] = __('brother of ');
                } else {
                    $this->relation['rel_text'] = __('sister of ');
                }
            }
            if ($generY == 4) {
                $temptext = 'oldeforældre';
            }
            if ($generY == 5) {
                $temptext = 'tip oldeforældre';
            }
            if ($generY == 6) {
                $temptext = 'tip tip oldeforældre';
            }
            if ($generY == 7) {
                $temptext = 'tip tip tip oldeforældre';
            }
            $gennr = $generY - 4;
            if ($generY >  7) {
                $temptext = $gennr . ' gange tip oldeforældre';
            }
            if ($temptext !== '') {
                $this->relation['rel_text_nor_dan'] = "s " . $temptext;
                $this->relation['rel_text_nor_dan2'] = $temptext . ' til ';
            }
        } elseif ($this->selected_language == "cn") {
            if ($generY == 2) {
                $this->relation['rel_text'] = $uncleaunt;
            }
            if ($generY > 2) {
                $this->relation['rel_text'] = "notext";
            }
        } elseif ($this->selected_language == "fr") {
            if ($generY == 2) {
                $this->relation['rel_text'] = $uncleaunt . __(' of ');
            }
            if ($generY == 3) {
                $this->relation['rel_text'] = 'grand-' . $uncleaunt . __(' of ');
            }
            if ($generY == 4) {
                $this->relation['rel_text'] = 'arrière-grand-' . $uncleaunt . __(' of ');
            }
            if ($generY == 5) {
                $this->relation['rel_text'] = 'arrière-arrière-grand-' . $uncleaunt . __(' of ');
            }
            if ($generY == 6) {
                $this->relation['rel_text'] = 'arrière-arrière-arrière-grand-' . $uncleaunt . __(' of ');
            }
            if ($generY == 7) {
                $this->relation['rel_text'] = 'arrière-arrière-arrière-arrière-grand-' . $uncleaunt . __(' of ');
            }
            $gennr = $generY - 3;
            if ($generY >  7) {
                $this->relation['rel_text'] = 'arrière (' . $gennr . ' fois) grand-' . $uncleaunt . __(' of ');
            }
        } else {
            if ($generY == 2) {
                $this->relation['rel_text'] = $uncleaunt . __(' of ');
            }
            if ($generY == 3) {
                $this->relation['rel_text'] = __('grand') . $uncleaunt . __(' of ');
            }
            if ($generY == 4) {
                $this->relation['rel_text'] = __('great-grand') . $uncleaunt . __(' of ');
            }
            if ($generY == 5) {
                $this->relation['rel_text'] = __('2nd') . ' ' . __('great-grand') . $uncleaunt . __(' of ');
            }
            if ($generY == 6) {
                $this->relation['rel_text'] = __('3rd') . ' ' . __('great-grand') . $uncleaunt . __(' of ');
            }
            $gennr = $generY - 3;
            if ($generY >  6) {
                $this->relation['rel_text'] = $gennr . __('th') . ' ' . __('great-grand') . $uncleaunt . __(' of ');
            }
        }
    }

    private function calculate_cousins($generX, $generY): void
    {
        if ($this->selected_language == "es") {
            $gendiff = abs($generX - $generY);

            if ($gendiff == 0) {
                if ($this->relation['sexe1'] == "M") {
                    $cousin = __('cousin.male');
                    $span_postfix = "o ";
                    $sibling = __('1st [COUSIN]');
                } else {
                    $cousin = __('cousin.female');
                    $span_postfix = "a ";
                    $sibling = 'hermana';
                }
                if ($generX == 2) {
                    $this->relation['rel_text'] = $cousin . " " . $sibling . __(' of ');
                } elseif ($generX > 2) {
                    $degree = $generX - 1;
                    $this->relation['rel_text'] = $cousin . " " . $degree . $span_postfix . __(' of ');
                }
            } elseif ($generX < $generY) {
                if ($this->relation['sexe1'] == "M") {
                    $uncle = __('uncle');
                    $span_postfix = "o ";
                    $gran = 'abuelo';
                } else {
                    $uncle = __('aunt');
                    $span_postfix = "a ";
                    $gran = 'abuela';
                }

                if ($gendiff == 1) {
                    $relname = $uncle;
                } elseif ($gendiff > 1 && $gendiff < 27) {
                    $relname = $uncle . " " . $this->spanish_degrees($gendiff) . $gran;
                } else {
                }
                $this->relation['rel_text'] = $relname . " " . $generX . $span_postfix . __(' of ');
            } else {
                if ($this->relation['sexe1'] == "M") {
                    $nephew = __('nephew');
                    $span_postfix = "o ";
                    $grson = 'nieto';
                } else {
                    $nephew = __('niece');
                    $span_postfix = "a ";
                    $grson = 'nieta';
                }

                if ($gendiff == 1) {
                    $relname = $nephew;
                } else {
                    $relname = $nephew . " " . $this->spanish_degrees($gendiff) . $grson;
                }
                $this->relation['rel_text'] = $relname . " " . $generY . $span_postfix . __(' of ');
            }
            // *** Greek***
            // *** Ελληνικά ξαδέλφια***
        } elseif ($this->selected_language == "gr") {
            // TODO improve code
            $gendiff = abs($generX - $generY);

            if ($gendiff == 0) {
                if ($this->relation['sexe1'] == "M") {
                    $cousin = __('cousin.male');
                    $gr_postfix = "ος ";
                    $sibling = __('1st [COUSIN]');
                } else {
                    $cousin = __('cousin.female');
                    $gr_postfix = "η ";
                    $sibling = __('1st [COUSIN]');
                }
                if ($generX == 2) {
                    if ($this->relation['sexe1'] == 'M') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = $sibling . $gr_postfix . $cousin . '  του ';
                        } else {
                            $this->relation['rel_text'] = $sibling . $gr_postfix . $cousin . ' της ';
                        }
                    } elseif ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = $sibling . $gr_postfix . $cousin . ' του ';
                        } else {
                            $this->relation['rel_text'] = $sibling . $gr_postfix . $cousin . ' της ';
                        }
                    }
                } elseif ($generX > 2) {
                    $degree = $generX - 1;
                    if ($this->relation['sexe1'] == 'M') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] =  $degree . $gr_postfix . $cousin . ' του ';
                        } else {
                            $this->relation['rel_text'] =  $degree . $gr_postfix . $cousin . ' της ';
                        }
                    } elseif ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] =  $degree . $gr_postfix . $cousin . ' του ';
                        } else {
                            $this->relation['rel_text'] =  $degree . $gr_postfix . $cousin . ' της ';
                        }
                    }
                }
            } elseif ($generX < $generY) {
                if ($this->relation['sexe1'] == "M") {
                    $uncle = __('uncle');
                    $gr_postfix = "ος ";
                    $gran = 'παππούς';
                } else {
                    $uncle = __('aunt');
                    $gr_postfix = "η ";
                    $gran = 'γιαγιά';
                }
                if ($gendiff == 1) {
                    if ($this->relation['sexe1'] == 'M') {
                        if ($this->relation['sexe2'] == 'M') {
                            $relname = $uncle . ' του ';
                        } else {
                            $relname = $uncle . ' της ';
                        }
                    } elseif ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $relname = $uncle . ' του ';
                        } else {
                            $relname = $uncle . ' της ';
                        }
                    }
                } else {

                    if ($this->relation['sexe1'] == 'M') {
                        if ($this->relation['sexe2'] == 'M') {
                            $relname = $uncle . ' του ';
                        } else {
                            $relname = $uncle . ' του ';
                        }
                    } elseif ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $relname = $uncle . ' του ';
                        } else {
                            $relname = $uncle . ' του ';
                        }
                    }
                }
                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $uncle . " " . $generX . $gr_postfix . ' του';
                    } else {
                        $this->relation['rel_text'] = $uncle . " " . $generX . $gr_postfix . ' της ';
                    }
                } elseif ($this->relation['sexe1'] == 'F') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $uncle . " " . $generX . $gr_postfix . ' του ';
                    } else {
                        $this->relation['rel_text'] = $uncle . " " . $generX . $gr_postfix . ' της ';
                    }
                }
                if ($gendiff == 2) {
                    if ($this->relation['sexe1'] == 'M') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = $uncle . " " . $gran . ' του';
                        } else {
                            $this->relation['rel_text'] = $uncle . " " . $gran . ' της ';
                        }
                    } elseif ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = $uncle . " " . $gran . ' του ';
                        } else {
                            $this->relation['rel_text'] = $uncle . " " . $gran . ' της ';
                        }
                    }
                }
            } else {
                if ($this->relation['sexe1'] == "M") {
                    $nephew = 'ανιψιος';
                    $gr_postfix = "ος ";
                    $grson = 'εγγονός';
                } else {
                    $nephew = 'ανιψιά';
                    $gr_postfix = "η ";
                    $grson = 'εγγονή';
                }
                if ($gendiff == 1) {
                    if ($this->relation['sexe1'] == 'M') {
                        if ($this->relation['sexe2'] == 'M') {
                            $relname = $nephew . ' του ';
                        } else {
                            $relname = $nephew . ' του ';
                        }
                    } elseif ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $relname = $nephew . ' του ';
                        } else {
                            $relname = $nephew . ' του ';
                        }
                    }
                }
                if ($this->relation['sexe1'] == 'M') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $nephew . " " . $generY . $gr_postfix . ' του';
                    } else {
                        $this->relation['rel_text'] = $nephew . " " . $generY . $gr_postfix . ' της ';
                    }
                } elseif ($this->relation['sexe1'] == 'F') {
                    if ($this->relation['sexe2'] == 'M') {
                        $this->relation['rel_text'] = $nephew . " " . $generY . $gr_postfix . ' του ';
                    } else {
                        $this->relation['rel_text'] = $nephew . " " . $generY . $gr_postfix . ' της ';
                    }
                }
                if ($gendiff == 2) {
                    if ($this->relation['sexe1'] == 'M') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = $nephew . " " . $grson . ' του';
                        } else {
                            $this->relation['rel_text'] = $nephew . " " . $grson . ' της ';
                        }
                    } elseif ($this->relation['sexe1'] == 'F') {
                        if ($this->relation['sexe2'] == 'M') {
                            $this->relation['rel_text'] = $nephew . " " . $grson . ' του ';
                        } else {
                            $this->relation['rel_text'] = $nephew . " " . $grson . ' της ';
                        }
                    }
                }
            }
            // *** Ελληνικά τέλος***
            // *** Greek end***    
        } elseif ($this->selected_language == "he") {
            if ($this->relation['sexe1'] == 'M') {
                $cousin = __('COUSIN_MALE');
            } else {
                $cousin = __('COUSIN_FEMALE');
            }
            $gendiff = abs($generX - $generY);
            if ($gendiff == 0) {
                $removenr = '';
            } elseif ($gendiff == 1) {
                $removenr = 'בהפרש ' . __('once removed');
            } else {
                $removenr = 'בהפרש ' . $gendiff . " " . __('times removed');
            }
            $degree = '';
            $degreediff = min($generX, $generY);
            if ($degreediff > 2) {
                $degree = 'דרגה ' . ($degreediff - 1) . " ";
            }
            $this->relation['rel_text'] = $cousin . $degree . $removenr . __(' of ');
        } elseif ($this->selected_language == "no") {
            $this->relation['rel_text_nor_dan'] = '';
            $this->relation['rel_text_nor_dan2'] = '';
            $degreediff = min($generX, $generY);
            if ($degreediff == 2) {
                $nor_cousin = __('1st [COUSIN]'); // 1st cousin
            } elseif ($degreediff == 3) {
                $nor_cousin = __('2nd [COUSIN]'); // 2nd cousin
            } elseif ($degreediff == 4) {
                $nor_cousin = __('3rd [COUSIN]'); // 3rd cousin
            } elseif ($degreediff == 5) {
                $nor_cousin = __('4th [COUSIN]'); // 4th cousin
            } elseif ($degreediff > 5) {
                $gennr = $degreediff - 3;
                $nor_cousin = $degreediff . "-menningen";
            }

            $gendiff = abs($generX - $generY);
            if ($gendiff == 0) { // A and B are cousins of same generation
                $this->relation['rel_text'] = $nor_cousin . __(' of ');
            } elseif ($generX > $generY) {  // A is the "younger" cousin  (A er barnebarnet av Bs tremenning)
                if ($this->relation['sexe1'] == 'M') {
                    // only for 1st generation
                    $child = __('son');
                } else {
                    $child = __('daughter');
                }
                if ($gendiff == 1) {
                    // sønnen/datteren til
                    $this->relation['rel_text'] = $child . __(' of ');
                }
                if ($gendiff == 2) {
                    // barnebarnet til
                    $this->relation['rel_text'] = 'barnebarnet ' . __(' of ');
                }
                if ($gendiff == 3) {
                    //olde+barnet
                    $this->relation['rel_text'] = __('great-grand') . ' barnet' . __(' of ');
                }
                if ($gendiff == 4) {
                    $this->relation['rel_text'] = 'tippolde barnet' . __(' of ');
                }
                if ($gendiff == 5) {
                    $this->relation['rel_text'] = 'tipp-tippolde barnet' . __(' of ');
                }
                $gennr = $gendiff - 3;
                if ($gendiff >  5) {
                    $this->relation['rel_text'] = $gennr . 'x tippolde barnet' . __(' of ');
                }
                $this->relation['rel_text_nor_dan'] = "s " . substr($nor_cousin, 0, -2);
                $this->relation['rel_text_nor_dan2'] = $nor_cousin . __(' of ');
            } elseif ($generX < $generY) {  // A is the "older" cousin (A er timenning av Bs tipp-tippoldefar)
                if ($gendiff == 1) {
                    $temptext = 'forelderen';
                }
                if ($gendiff == 2) {
                    $temptext = __('grand') . 'forelderen';
                }
                if ($gendiff == 3) {
                    $temptext = __('great-grand') . 'forelderen';
                }
                if ($gendiff == 4) {
                    $temptext = 'tippoldeforelderen';
                }
                if ($gendiff == 5) {
                    $temptext = 'tipp-tippoldeforelderen';
                }
                $gennr = $gendiff - 3;
                if ($gendiff >  5) {
                    $temptext = $gennr . 'x tippoldeforelderen';
                }
                $this->relation['rel_text'] = $nor_cousin . __(' of ');
                $this->relation['rel_text_nor_dan'] = "s " . substr($temptext, 0, -2);
                $this->relation['rel_text_nor_dan2'] = $temptext . __(' of ');

                /* following is the alternative way of notation for cousins when X is the older one
                // (A er barnebarn av Bs tipp-tippolefars sosken)
                // at the moment we use the previous method that is shorter and approved by our Norwegian user
                // but we'll leave this here, just in case....
                $this->relation['rel_text'] = $nor_removed;
                if ($generX == 2) {
                    $X_removed = 'barnet'."barn";
                }
                if ($generX == 3) {
                    $X_removed = __('great-grand')."barn";
                }
                if ($generX == 4) {
                    $X_removed = 'tippolde'."barn";
                }
                if ($generX == 5) {
                    $X_removed = 'tipp-tippolde'."barn";
                }
                if ($generX >  5) {
                    $gennr = $generX-3;
                    $X_removed = $gennr.'x tippolde'."barn";
                }

                if ($generY == 3) {
                    $Y_removed = __('great-grand')."barn";
                }
                if ($generY == 4) {
                    $Y_removed = 'tippolde '."barn";
                }
                if ($generY == 5) {
                    $Y_removed = 'tipp-tippolde '."barn";
                }
                if ($generY >  5) {
                    $gennr = $generY-3;
                    $Y_removed = $gennr.'x tippolde'."barn";
                }
                $this->relation['rel_text'] = $X_removed.__(' of ');
                $this->relation['rel_text_nor_dan'] = "s ".$Y_removed."s ".'søskenet';
                $this->relation['rel_text_nor_dan2'] = $Y_removed.__(' of ');
                */
            }
        } elseif ($this->selected_language == "sv") {
            $degreediff = min($generX, $generY);
            if ($degreediff == 2) {
                // 1st cousin
                $se_cousin = "kusin";
            } elseif ($degreediff == 3) {
                // 2nd cousin
                $se_cousin = "tremänning";
            } elseif ($degreediff == 4) {
                // 3rd cousin
                $se_cousin = "fyrmänning";
            } elseif ($degreediff == 5) {
                // 4th cousin
                $se_cousin = "femmänning";
            } elseif ($degreediff == 6) {
                // 5th cousin
                $se_cousin = "sexmänning";
            } elseif ($degreediff == 7) {
                // 6th cousin
                $se_cousin = "sjumänning";
            } elseif ($degreediff == 8) {
                // 7th cousin
                $se_cousin = "åttamänning";
            } elseif ($degreediff == 9) {
                // 8th cousin
                $se_cousin = "niomänning";
            } elseif ($degreediff == 10) {
                // 9th cousin
                $se_cousin = "tiomänning";
            } elseif ($degreediff == 11) {
                // 10th cousin
                $se_cousin = "elvammänning";
            } elseif ($degreediff == 12) {
                // 11nd cousin
                $se_cousin = "tolvmänning";
            } elseif ($degreediff == 13) {
                // 12th cousin
                $se_cousin = "trettonmänning";
            } elseif ($degreediff == 14) {
                // 13th cousin
                $se_cousin = "fjortonmänning";
            } elseif ($degreediff == 15) {
                // 14th cousin
                $se_cousin = "femtonmänning";
            } elseif ($degreediff == 16) {
                // 15th cousin
                $se_cousin = "sextonmänning";
            } elseif ($degreediff == 17) {
                // 16th cousin
                $se_cousin = "sjuttonmänning";
            } elseif ($degreediff == 18) {
                // 17th cousin
                $se_cousin = "artonmänning";
            } elseif ($degreediff == 19) {
                // 18th cousin
                $se_cousin = "nittonmänning";
            } elseif ($degreediff == 20) {
                // 19th cousin
                $se_cousin = "tjugomänning";
            } elseif ($degreediff > 20) {
                $gennr = $degreediff - 3;
                $se_cousin = $degreediff . "-männing";
            }

            $gendiff = abs($generX - $generY); // generation gap between A and B
            if ($gendiff == 0) { // A and B are cousins of same generation
                $this->relation['rel_text'] = $se_cousin . __(' of ');
            } elseif ($generX > $generY) {  // A is the "younger" cousin  (example A är tremannings barnbarn för B)
                if ($gendiff == 1)
                    if ($se_cousin == "kusin") {
                        $this->relation['rel_text'] = 'kusinbarn' . __(' of ');
                    } else {
                        $this->relation['rel_text'] = $se_cousin . 's barn' . __(' of ');
                    }
                if ($gendiff == 2) {
                    $this->relation['rel_text'] = $se_cousin . 's barnbarn' . __(' of ');
                }
                $gennr = $gendiff;
                if ($gendiff >  2) {
                    $this->relation['rel_text'] = $se_cousin . 's ' . $gennr . ':e generations barn' . __(' of ');
                }
            } elseif ($generX < $generY) {  // A is the "older" cousin (A är farfars tremanning för B)

                if ($this->relation['spouse'] == "2" || $this->relation['spouse'] == "3") { // right person is spouse of Y, not Y
                    $relarr = $this->rel_arrayspouseY;
                } else {
                    $relarr = $this->rel_arrayY;
                }

                $arrnum = 0;
                reset($ancsarr);
                $count = $this->relation['foundY_nr'];
                while ($count != 0) {
                    $parnumber = $count;
                    $ancsarr[$arrnum] = $parnumber;
                    $arrnum++;
                    $count = $relarr[$count][2];
                }

                // parent
                $persidDb = $this->db_functions->get_person($relarr[$parnumber][0]);
                $parsexe = $persidDb->pers_sexe;
                if ($parsexe == "M") {
                    $se_par = "far";
                } else {
                    $se_par = "mor";
                }

                //grandparent
                if ($gendiff > 1) {
                    $persidDb2 = $this->db_functions->get_person($relarr[$ancsarr[$arrnum - 2]][0]);
                    $parsexe2 = $persidDb2->pers_sexe;
                    if ($parsexe2 == "M") {
                        $se_grpar = "fars";
                    } else {
                        $se_grpar = "mors";
                    }
                }
                if ($gendiff == 1) {
                    $this->relation['rel_text'] = $se_par . 's ' . $se_cousin . __(' of ');
                }
                if ($gendiff == 2) {
                    $this->relation['rel_text'] = $se_par . $se_grpar . ' ' . $se_cousin . __(' of ');
                }
                $gennr = $gendiff;
                if ($gendiff >  2) {
                    $this->relation['rel_text'] = $gennr . ':e generation anas ' . $se_cousin . __(' of ');
                }
            }
        } elseif ($this->selected_language == "cn") {    // cousin biao
            // Followed guidelines of: http://www.kwanfamily.info/culture/familytitles_table.php
            // paternal male cousin -	father's brother's son	堂兄弟
            // paternal female cousin-	father's brother's daughters	堂姊妹
            // paternal male cousin - father's sisters's son	表兄弟
            // maternal male cousin	mother's siblings' son 表兄弟
            // paternal female cousin father's sister's daughters	表姊妹
            // maternal female cousin	mother's siblings' daughters 表姊妹
            // Other translations for cousins that I saw: (not used)
            // dongshan: 叔伯, 叔伯公, 曾叔伯公
            // 表姐 cousin jie
            // 表妹 cousin mei
            // 表姐妹 cousin jie-mei
            // 表亲 cousin qin

            $gendiff = abs($generX - $generY);
            $degreediff = min($generX, $generY);
            if ($gendiff == 0 && $degreediff == 2) {
                // deals with first cousins not removed only.
                // Unfortunately we miss the Chinese terminology for 2nd, 3rd cousins and "removed" sequence...
                if ($this->relation['spouse'] == "1") { // left person is spouse of X, not X
                    $relarrX = $this->rel_arrayspouseX;
                } else {
                    $relarrX = $this->rel_arrayX;
                }
                $arrnumX = 0;
                if (isset($ancsarrX)) {
                    reset($ancsarrX);
                }
                $count = $this->relation['foundX_nr'];
                while ($count != 0) {
                    $parnumberX = $count;
                    $ancsarrX[$arrnumX] = $parnumberX;
                    $arrnumX++;
                    $count = $relarrX[$count][2];
                }
                $persidDbX = $this->db_functions->get_person($relarrX[$parnumberX][0]);
                $parsexeX = $persidDbX->pers_sexe;
                if ($parsexeX == 'F') {
                    // the easier part: with siblings of mother doesn't matter from her brothers or sisters
                    if (($this->relation['sexe2'] == "M" && $this->relation['spouse'] != 2 && $this->relation['spouse'] != 3) || ($this->relation['sexe2'] == "F" && ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3))) {
                        $this->relation['rel_text'] = '表兄弟是';  // male cousin from mother's side
                    } else {
                        $this->relation['rel_text'] = '表姊妹是';  // female cousin from mother's side
                    }
                } else {
                    // difficult part: it matters whether cousins thru father's brothers of father's sister!
                    if ($this->relation['spouse'] == "2" || $this->relation['spouse'] == "3") { // right person is spouse of Y, not Y
                        $relarrY = $this->rel_arrayspouseY;
                    } else {
                        $relarrY = $this->rel_arrayY;
                    }
                    $arrnumY = 0;
                    if (isset($ancsarrY)) {
                        reset($ancsarrY);
                    }
                    $count = $this->relation['foundY_nr'];
                    while ($count != 0) {
                        $parnumberY = $count;
                        $ancsarrY[$arrnumY] = $parnumberY;
                        $arrnumY++;
                        $count = $relarrY[$count][2];
                    }
                    $persidDbY = $this->db_functions->get_person($relarrY[$parnumberY][0]);
                    $parsexeY = $persidDbY->pers_sexe;
                    if ($parsexeY == "M") { // child of father's brother
                        if (($this->relation['sexe2'] == "M" && $this->relation['spouse'] != 2 && $this->relation['spouse'] != 3) || ($this->relation['sexe2'] == "F" && ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3))) {
                            $this->relation['rel_text'] = '堂兄弟是';
                        } else {
                            $this->relation['rel_text'] = '堂姊妹是';
                        }
                    } else { // child of father's sister
                        if (($this->relation['sexe2'] == "M" && $this->relation['spouse'] != 2 && $this->relation['spouse'] != 3) || ($this->relation['sexe2'] == "F" && ($this->relation['spouse'] == 2 || $this->relation['spouse'] == 3))) {
                            $this->relation['rel_text'] = '表兄弟是';
                        } else {
                            $this->relation['rel_text'] = '表姊妹是';
                        }
                    }
                }
            } else {
                $this->relation['rel_text'] = "notext";
            }
        } elseif ($this->selected_language == "da") {
            $gendiff = abs($generX - $generY);
            $degreediff = min($generX, $generY);

            if ($degreediff == 2) {
                $nor_cousin = 'kusine'; // 1st cousin
            } elseif ($degreediff == 3) {
                $nor_cousin = 'halvkusine'; // 2nd cousin
            } elseif ($degreediff > 3) {
                $gennr = $degreediff - 1;
                $nor_cousin = $gennr . ". kusine";  // 3. kusine
            }

            if ($degreediff == 2 && $gendiff == 0) {
                // first cousins
                $this->relation['rel_text'] = __('COUSIN_MALE') . __(' of ');
            } elseif ($degreediff == 2 && $gendiff == 1 && $generX < $generY) {   // first cousins once removed - X older
                if ($this->relation['sexe1'] == "M") {
                    $this->relation['rel_text'] =  'halvonkel' . __(' of ');
                } else {
                    $this->relation['rel_text'] =  'halvtante' . __(' of ');
                }
            } elseif ($degreediff == 2 && $gendiff == 1 && $generX > $generY) {   // first cousins once removed - Y older
                if ($this->relation['sexe1'] == "M") {
                    $this->relation['rel_text'] =  'halvnevø' . __(' of ');
                } else {
                    $this->relation['rel_text'] =  'halvniece' . __(' of ');
                }
            } elseif ($degreediff == 3 && $gendiff == 0) {
                // second cousins
                $this->relation['rel_text'] = 'halvkusine' . __(' of ');
            } elseif ($generX > $generY) {
                // A is the "younger" cousin  (A er barnebarn af Bs tremenning)
                if ($this->relation['sexe1'] == 'M') {
                    // only for 1st generation
                    $child = __('son');
                } else {
                    $child = __('daughter');
                }
                if ($gendiff == 1) {
                    // søn/datter af
                    $this->relation['rel_text'] = $child . __(' of ');
                }
                if ($gendiff == 2) {
                    // barnebarn af
                    $this->relation['rel_text'] = 'barnebarn ' . __(' of ');
                }
                if ($gendiff == 3) {
                    $this->relation['rel_text'] = 'oldebarn' . __(' of ');
                }
                if ($gendiff == 4) {
                    $this->relation['rel_text'] = 'tip oldebarn' . __(' of ');
                }
                if ($gendiff == 5) {
                    $this->relation['rel_text'] = 'tip tip oldebarn' . __(' of ');
                }
                if ($gendiff == 6) {
                    $this->relation['rel_text'] = 'tip tip tip oldebarn' . __(' of ');
                }
                $gennr = $gendiff - 3;
                if ($gendiff >  6) {
                    $this->relation['rel_text'] = $gennr . ' gange tip oldebarn' . __(' of ');
                }
                $this->relation['rel_text_nor_dan'] = "s " . $nor_cousin;
                $this->relation['rel_text_nor_dan2'] = $nor_cousin . __(' of ');
            } elseif ($generX < $generY) {
                // A is the "older" cousin (A er timenning af Bs tiptipoldeforældre)
                if ($gendiff == 1) {
                    $temptext = 'forældre';
                }
                if ($gendiff == 2) {
                    $temptext = 'bedsteforældre';
                }
                if ($gendiff == 3) {
                    $temptext = 'oldeforældre';
                }
                if ($gendiff == 4) {
                    $temptext = 'tip oldeforældre';
                }
                if ($gendiff == 5) {
                    $temptext = 'tip tip oldeforældre';
                }
                if ($gendiff == 6) {
                    $temptext = 'tip tip tip oldeforældre';
                }
                $gennr = $gendiff - 3;
                if ($gendiff >  7) {
                    $temptext = $gennr . ' gange tip oldeforældre';
                }
                $this->relation['rel_text'] = $nor_cousin . ' til ';
                $this->relation['rel_text_nor_dan'] = "s " . $temptext;
                $this->relation['rel_text_nor_dan2'] = $temptext . ' til ';
            }
        } elseif ($this->selected_language == "fr") {  // french
            if ($this->relation['sexe1'] == 'M') {
                $cousin = __('cousin.male');
                $gend = '';
            } else {
                $cousin = __('cousin.female');
                $gend = 'e';
            }
            // 1st cousin, 2nd cousin etc
            $degreediff = min($generX, $generY);

            if ($degreediff == 2) {
                $cousin .= ' germain' . $gend;
            }
            if ($degreediff == 3) {
                $cousin .= ' issu' . $gend . ' de germains ';
            }
            if ($degreediff == 4) {
                $cousin = ' petit' . $gend . '-' . $cousin;
            }
            if ($degreediff == 5) {
                $cousin = 'arrière-petit' . $gend . '-' . $cousin;
            }
            if ($degreediff == 6) {
                $cousin = 'arrière-arrière-petit' . $gend . '-' . $cousin;
            }
            if ($degreediff == 7) {
                $cousin = 'arrière-arrière-arrière-petit' . $gend . '-' . $cousin;
            } elseif ($degreediff > 7) {
                $cousin = 'arrière (' . ($degreediff - 4) . ' fois) petit' . $gend . '-' . $cousin;
            }

            // once/twice etc removed
            $gendiff = abs($generX - $generY);
            if ($gendiff == 1) {
                $cousin .= " éloigné" . $gend . " au 1er degré";
            } elseif ($gendiff == 2) {
                $cousin .= " éloigné" . $gend . " au 2ème degré";
            } elseif ($gendiff == 3) {
                $cousin .= " éloigné" . $gend . " au 3ème degré";
            } elseif ($gendiff == 4) {
                $cousin .= " éloigné" . $gend . " au 4ème degré";
            } elseif ($gendiff == 5) {
                $cousin .= " éloigné" . $gend . " au 5ème degré";
            } elseif ($gendiff > 5) {
                $cousin .= " éloigné" . $gend . " au " . $gendiff . "ème degré ";
            }

            $this->relation['rel_text'] = $cousin . __(' of ');
        } else {
            $gendiff = abs($generX - $generY);
            if ($gendiff == 0) {
                $removenr = '';
            } elseif ($gendiff == 1) {
                $removenr = ' ' . __('once removed');
            } elseif ($gendiff == 2) {
                $removenr = ' ' . __('twice removed');
            } elseif ($gendiff > 2) {
                $removenr = $gendiff . ' ' . __('times removed');
            }

            $degreediff = min($generX, $generY);
            if ($degreediff == 2) {
                $degree = __('1st [COUSIN]');
            }
            if ($degreediff == 3) {
                $degree = __('2nd [COUSIN]');
            }
            if ($degreediff == 4) {
                $degree = __('3rd [COUSIN]');
            }

            $cousin = $this->relation['sexe1'] == 'M' ? __('cousin.male') : __('cousin.female');

            if ($degreediff > 4) {
                $degreediff -= 1;
                $degree = $degreediff . __('th') . ' ';
                if ($this->selected_language == "nl") {
                    $degreediff--;  // 5th cousin is in dutch "4de achterneef"
                    $degree = $degreediff . __('th') . ' ' . __('2nd [COUSIN]'); // in Dutch cousins are counted with 2nd cousin as base
                }
            }
            if (($this->selected_language == "fi" && $degreediff == 3) || ($this->selected_language == "nl" && $degreediff >= 3)) {
                // no space here (FI): pikkuserkku
                // no space here (NL): achterneef, achter-achternicht, 3de achterneef
                $this->relation['rel_text'] = $degree . $cousin . ' ' . $removenr . __(' of ');
            } else {
                $this->relation['rel_text'] = $degree . ' ' . $cousin . ' ' . $removenr . __(' of ');
            }
        }
    }

    // TODO function used once
    private function search_marital(): void
    {
        $personName = new PersonName();
        $privacy = new PersonPrivacy();

        if ($this->relation['fams1'] != '') {
            $marrcount = count($this->fams1_array);
            for ($x = 0; $x < $marrcount; $x++) {
                $familyDb = $this->db_functions->get_family($this->fams1_array[$x], 'man-woman');
                $thespouse = $this->relation['sexe1'] == 'F' ? $familyDb->fam_man : $familyDb->fam_woman;

                $this->rel_arrayspouseX = $this->create_rel_array($thespouse);

                if (isset($this->rel_arrayspouseX)) {
                    $this->compare_rel_array($this->rel_arrayspouseX, $this->rel_arrayY, 1); // "1" flags comparison with "spouse of X"
                }

                if ($this->relation['foundX_match'] !== '') {
                    $this->relation['famspouseX'] = $this->fams1_array[$x];

                    $this->relation['sexe1'] = $this->relation['sexe1'] == 'M' ? "f" : "m"; // we have to switch sex since the spouse is the relative!
                    $this->calculate_rel();

                    $spouseidDb = $this->db_functions->get_person($thespouse);
                    $privacy = $privacy->get_privacy($spouseidDb);
                    $name = $personName->get_person_name($spouseidDb, $privacy);
                    $this->relation['spousenameX'] = $name["name"];

                    break;
                }
            }
        }

        if ($this->relation['foundX_match'] === '' && $this->relation['fams2'] != '') {  // no match found between "spouse of X" && "Y", let's try "X" with "spouse of "Y"
            $ymarrcount = count($this->fams2_array);
            for ($x = 0; $x < $ymarrcount; $x++) {
                $familyDb = $this->db_functions->get_family($this->fams2_array[$x], 'man-woman');
                $thespouse2 = $this->relation['sexe2'] == 'F' ? $familyDb->fam_man : $familyDb->fam_woman;

                $this->rel_arrayspouseY = $this->create_rel_array($thespouse2);

                if (isset($this->rel_arrayspouseY)) {
                    $this->compare_rel_array($this->rel_arrayX, $this->rel_arrayspouseY, 2); // "2" flags comparison with "spouse of Y"
                }
                if ($this->relation['foundX_match'] !== '') {
                    $this->relation['famspouseY'] = $this->fams2_array[$x];
                    $this->calculate_rel();
                    $spouseidDb = $this->db_functions->get_person($thespouse2);
                    $privacy = $privacy->get_privacy($spouseidDb);
                    $name = $personName->get_person_name($spouseidDb, $privacy);
                    $this->relation['spousenameY'] = $name["name"];
                    break;
                }
            }
        }

        if ($this->relation['foundX_match'] === '' && $this->relation['fams1'] != '' && $this->relation['fams2'] != '') { // still no matches, let's try comparison of "spouse of X" with "spouse of Y"
            $xmarrcount = count($this->fams1_array);
            $ymarrcount = count($this->fams2_array);
            for ($x = 0; $x < $xmarrcount; $x++) {
                for ($y = 0; $y < $ymarrcount; $y++) {
                    $familyDb = $this->db_functions->get_family($this->fams1_array[$x], 'man-woman');
                    $thespouse = $this->relation['sexe1'] == 'F' ? $familyDb->fam_man : $familyDb->fam_woman;

                    $this->rel_arrayspouseX = $this->create_rel_array($thespouse);
                    $familyDb = $this->db_functions->get_family($this->fams2_array[$y], 'man-woman');
                    $thespouse2 = $this->relation['sexe2'] == 'F' ? $familyDb->fam_man : $familyDb->fam_woman;

                    $this->rel_arrayspouseY = $this->create_rel_array($thespouse2);

                    if (isset($this->rel_arrayspouseX) && isset($this->rel_arrayspouseY)) {
                        $this->compare_rel_array($this->rel_arrayspouseX, $this->rel_arrayspouseY, 3); //"3" flags comparison "spouse of X" with "spouse of Y"
                    }
                    if ($this->relation['foundX_match'] !== '') {
                        // we have to switch sex since the spouse is the relative!
                        $this->relation['sexe1'] = $this->relation['sexe1'] == 'M' ? "f" : "m";
                        $this->calculate_rel();

                        $spouseidDb = $this->db_functions->get_person($thespouse);
                        $privacy_spouse = $privacy->get_privacy($spouseidDb);
                        $name = $personName->get_person_name($spouseidDb, $privacy_spouse);
                        $this->relation['spousenameX'] = $name["name"];

                        $spouseidDb = $this->db_functions->get_person($thespouse2);
                        $privacy_spouse2 = $privacy->get_privacy($spouseidDb);
                        $name = $personName->get_person_name($spouseidDb, $privacy_spouse2);
                        $this->relation['spousenameY'] = $name["name"];

                        $this->relation['famspouseX'] = $this->fams1_array[$x];
                        $this->relation['famspouseY'] = $this->fams2_array[$y];

                        break;
                    }
                }
                if ($this->relation['foundX_match'] !== '') {
                    break;
                }
            }
        }
    }

    /* the extended marital calculator computation */
    public function extended_calculator($pers_array, $pers_array2)
    {
        // in first loop $pers_array and $pers_array2 hold persons A and B
        // in the next loop it will contain the parents, children and spouses of persons A and B, where they exist etc
        // the algorithm starts simultaneously from person A and person B in expanding circles until a common person is found (= connection found)
        // or until either person A or B runs out of persons (= no connection exists)

        $this->count++;
        if ($this->count > 400000) {
            $this->show_extended_message = "Database too large!";
            exit;
        }

        $work_array = array();
        $work_array2 = array();

        // build closest circle around person A (parents, children, spouse(s))
        foreach ($pers_array as $value) {   // each array item has 4 parts, separated by "@": I124@par@I15@I54;I46;I326;I123;I15
            $params = explode("@", $value);
            $persged = $params[0]; // the gedcomnumber of this person
            $refer = $params[1];   // the referrer type: par (parent), spo (spouse), chd (child) - this means who was the previous person that called this one
            $callged = $params[2]; // the gedcomnumber of the referrer (in case referrer is child: gedcomnumber;famc gedcomnumber)
            $pathway = $params[3]; // the path from person A to this person (gedcomnumbers separated by semi-colon)

            if ($refer === "chd") {
                $callarray = explode(";", $callged);    // [0] = gedcomnumber of referring child, [1] = famc gedcomnumber of referring child
            } else {
                $callarray[0] = $callged;
            }

            $persDb = $this->db_functions->get_person($persged);
            if ($persDb == false) {
                //echo __('No such person') . ':ref=' . $refer . ' persged=' . $persged . ' callged=' . $callged . '$$';
                $this->show_extended_message = __('No such person') . ':ref=' . $refer . ' persged=' . $persged . ' callged=' . $callged . '$$';
                return (false);
            }

            if ($refer === "fst") {
                $this->globaltrack .= $persDb->pers_gedcomnumber . "@";
            }
            // find parents
            if (isset($persDb->pers_famc) && $persDb->pers_famc != "" && $refer !== "par") {
                $famcDb = $this->db_functions->get_family($persDb->pers_famc);
                if ($famcDb == false) {
                    //echo __('No such family');
                    $this->show_extended_message = __('No such family');
                    return;
                }

                if (isset($famcDb->fam_man) && $famcDb->fam_man != "" && $famcDb->fam_man != "0" && strpos($this->globaltrack, $famcDb->fam_man . "@") === false) {
                    if (strpos($_SESSION['next_path'], $famcDb->fam_man . "@") === false) {
                        $work_array[] = $famcDb->fam_man . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_man;
                        $this->relation['global_array'][] = $famcDb->fam_man . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_man;
                    }
                    $this->count++;
                    $this->globaltrack .= $famcDb->fam_man . "@";
                }
                if (isset($famcDb->fam_woman) && $famcDb->fam_woman != "" && $famcDb->fam_woman != "0" && strpos($this->globaltrack, $famcDb->fam_woman . "@") === false) {
                    if (strpos($_SESSION['next_path'], $famcDb->fam_woman . "@") === false) {
                        $work_array[] = $famcDb->fam_woman . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_woman;
                        $this->relation['global_array'][] = $famcDb->fam_woman . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_woman;
                    }
                    $this->count++;
                    $this->globaltrack .= $famcDb->fam_woman . "@";
                }
            }

            if (isset($persDb->pers_fams) && $persDb->pers_fams != "") {
                $famsarray = explode(";", $persDb->pers_fams);

                foreach ($famsarray as $value) {
                    if ($refer === "spo" && $value === $callged) {
                        continue;
                    }
                    if ($refer === "fst" && $_SESSION['couple'] == $value) {
                        continue;
                    }
                    $famsDb = $this->db_functions->get_family($value);
                    if ($refer === "chd" && $famsDb->fam_woman == $persDb->pers_gedcomnumber && isset($famsDb->fam_man) && $famsDb->fam_man != "" && $famsDb->fam_gedcomnumber == $callarray[1]) {
                        continue;
                    }
                    // find children
                    if (isset($famsDb->fam_children) && $famsDb->fam_children != "") {
                        $childarray = explode(";", $famsDb->fam_children);
                        foreach ($childarray as $value) {
                            if ($refer === "chd" && $callarray[0] === $value) {
                                continue;
                            }
                            if (strpos($this->globaltrack, $value . "@") === false) {
                                if (strpos($_SESSION['next_path'], $value . "@") === false) {
                                    $work_array[] = $value . "@par@" . $persged . "@" . $pathway . ";" . "par" . $value;
                                    $this->relation['global_array'][] = $value . "@par@" . $persged . "@" . $pathway . ";" . "par" . $value;
                                }
                                $this->count++;
                                $this->globaltrack .= $value . "@";
                            }
                        }
                    }
                }
                // find spouses
                foreach ($famsarray as $value) {
                    if ($refer === "chd" && $value === $callarray[1]) {
                        continue;
                    }
                    if ($refer === "spo" && $value === $callged) {
                        continue;
                    }
                    if ($refer === "fst" && $_SESSION['couple'] == $value) {
                        continue;
                    }
                    $famsDb = $this->db_functions->get_family($value);
                    if ($famsDb->fam_man == $persDb->pers_gedcomnumber) {
                        if (isset($famsDb->fam_woman) && $famsDb->fam_woman != "" && $famsDb->fam_woman != "0" && strpos($this->globaltrack, $famsDb->fam_woman . "@") === false) {
                            if (strpos($_SESSION['next_path'], $famsDb->fam_woman . "@") === false) {
                                $work_array[] = $famsDb->fam_woman . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_woman;
                                $this->relation['global_array'][] = $famsDb->fam_woman . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_woman;
                            }
                            $this->count++;
                            $this->globaltrack .= $famsDb->fam_woman . "@";
                        }
                    } else {
                        if (isset($famsDb->fam_man) && $famsDb->fam_man != "" && $famsDb->fam_man != "0" && strpos($this->globaltrack, $famsDb->fam_man . "@") === false) {
                            if (strpos($_SESSION['next_path'], $famsDb->fam_man . "@") === false) {
                                $work_array[] = $famsDb->fam_man . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_man;
                                $this->relation['global_array'][] = $famsDb->fam_man . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_man;
                            }
                            $this->count++;
                            $this->globaltrack .= $famsDb->fam_man . "@";
                        }
                    }
                }
            }
        }

        // build closest circle around person B (parents, children, spouse(s))
        foreach ($pers_array2 as $value) {
            $params = explode("@", $value);
            $persged = $params[0];
            $refer = $params[1];
            $callged = $params[2];
            $pathway = $params[3];

            if ($refer === "chd") {
                $callarray = explode(";", $callged);
            } else {
                $callarray[0] = $callged;
            }

            $persDb = $this->db_functions->get_person($persged);
            if ($persDb == false) {
                /*
                ?>
                    <?= __('No such person'); ?>:ref=<?= $refer; ?> persged=<?= $persged; ?> callged=<?= $callged; ?>$$
                <?php
                */
                $this->show_extended_message = __('No such person') . ":ref=" . $refer . "persged=" . $persged . "callged=" . $callged . "$$";
                return (false);
            }

            if ($refer === "fst") {
                $this->globaltrack2 .= $persDb->pers_gedcomnumber . "@";
            }

            if (isset($persDb->pers_famc) && $persDb->pers_famc != "" && $refer !== "par") {
                $famcDb = $this->db_functions->get_family($persDb->pers_famc);
                if ($famcDb == false) {
                    //echo __('No such family');
                    $this->show_extended_message = __('No such family');
                    return;
                }
                if (isset($famcDb->fam_man) && $famcDb->fam_man != "" && $famcDb->fam_man != "0") {
                    $var1 = strpos($_SESSION['next_path'], $famcDb->fam_man . "@");
                    if (strpos($this->globaltrack, $famcDb->fam_man . "@") !== false && $var1 === false) {
                        $this->totalpath = $this->ext_calc_join_path($this->relation['global_array'], $pathway, $famcDb->fam_man, "chd");
                        $_SESSION['next_path'] .= $famcDb->fam_man . "@";
                        //ext_calc_display_result($this->totalpath, $this->db_functions, $this->relation);
                        // TODO: return isn't used?
                        return ($famcDb->fam_man);
                    }
                    if (strpos($this->globaltrack2, $famcDb->fam_man . "@") === false) {
                        if ($var1 === false) {
                            $work_array2[] = $famcDb->fam_man . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_man;
                        }
                        $this->count++;
                        $this->globaltrack2 .= $famcDb->fam_man . "@";
                    }
                }
                if (isset($famcDb->fam_woman) && $famcDb->fam_woman != "" && $famcDb->fam_woman != "0") {
                    $var2 = strpos($_SESSION['next_path'], $famcDb->fam_woman . "@");
                    if (strpos($this->globaltrack, $famcDb->fam_woman . "@") !== false && $var2 === false) {
                        $this->totalpath = $this->ext_calc_join_path($this->relation['global_array'], $pathway, $famcDb->fam_woman, "chd");
                        $_SESSION['next_path'] .= $famcDb->fam_woman . "@";
                        //ext_calc_display_result($this->totalpath, $this->db_functions, $this->relation);
                        // TODO: return isn't used?
                        return ($famcDb->fam_woman);
                    }
                    if (strpos($this->globaltrack2, $famcDb->fam_woman . "@") === false) {
                        if ($var2 === false) {
                            $work_array2[] = $famcDb->fam_woman . "@chd@" . $persged . ";" . $persDb->pers_famc . "@" . $pathway . ";" . "chd" . $famcDb->fam_woman;
                        }
                        $this->count++;
                        $this->globaltrack2 .= $famcDb->fam_woman . "@";
                    }
                }
            }

            if (isset($persDb->pers_fams) && $persDb->pers_fams != "") {
                $famsarray = explode(";", $persDb->pers_fams);
                foreach ($famsarray as $value) {
                    if ($refer === "spo" && $value === $callged) {
                        continue;
                    }
                    if ($refer === "fst" && $_SESSION['couple'] == $value) {
                        continue;
                    }
                    $famsDb = $this->db_functions->get_family($value);
                    if ($refer === "chd" && $famsDb->fam_woman == $persDb->pers_gedcomnumber && isset($famsDb->fam_man) && $famsDb->fam_man != "" && $famsDb->fam_gedcomnumber == $callarray[1]) {
                        continue;
                    }
                    if (isset($famsDb->fam_children) && $famsDb->fam_children != "") {
                        $childarray = explode(";", $famsDb->fam_children);
                        foreach ($childarray as $value) {
                            if ($refer === "chd" && $callarray[0] === $value) {
                                continue;
                            }
                            $var3 = strpos($_SESSION['next_path'], $value . "@");
                            if (strpos($this->globaltrack, $value . "@") !== false && $var3 === false) {
                                $this->totalpath = $this->ext_calc_join_path($this->relation['global_array'], $pathway, $value, "par");
                                $_SESSION['next_path'] .= $value . "@";
                                //ext_calc_display_result($this->totalpath, $this->db_functions, $this->relation);
                                return ($value);
                            }
                            if (strpos($this->globaltrack2, $value . "@") === false) {
                                if ($var3 === false) {
                                    $work_array2[] = $value . "@par@" . $persged . "@" . $pathway . ";" . "par" . $value;
                                }
                                $this->count++;
                                $this->globaltrack2 .= $value . "@";
                            }
                        }
                    }
                }
                foreach ($famsarray as $value) {
                    if ($refer === "chd" && $value === $callarray[1]) {
                        continue;
                    }
                    if ($refer === "spo" && $value === $callged) {
                        continue;
                    }
                    if ($refer === "fst" && $_SESSION['couple'] == $value) {
                        continue;
                    }
                    $famsDb = $this->db_functions->get_family($value);
                    if ($famsDb->fam_man == $persDb->pers_gedcomnumber) {
                        if (isset($famsDb->fam_woman) && $famsDb->fam_woman != "" && $famsDb->fam_woman != "0") {
                            $var4 = strpos($_SESSION['next_path'], $famsDb->fam_woman . "@");
                            if (strpos($this->globaltrack, $famsDb->fam_woman . "@") !== false && $var4 === false) {
                                $this->totalpath = $this->ext_calc_join_path($this->relation['global_array'], $pathway, $famsDb->fam_woman, "spo");
                                $_SESSION['next_path'] .= $famsDb->fam_woman . "@";
                                //ext_calc_display_result($this->totalpath, $this->db_functions, $this->relation);
                                // TODO: return isn't used?
                                return ($famsDb->fam_woman);
                            }
                            if (strpos($this->globaltrack2, $famsDb->fam_woman . "@") === false) {
                                if ($var4 === false) {
                                    $work_array2[] = $famsDb->fam_woman . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_woman;
                                }
                                $this->count++;
                                $this->globaltrack2 .= $famsDb->fam_woman . "@";
                            }
                        }
                    } elseif ($famsDb->fam_woman == $persDb->pers_gedcomnumber) {
                        if (isset($famsDb->fam_man) && $famsDb->fam_man != "" && $famsDb->fam_man != "0") {
                            $var5 = strpos($_SESSION['next_path'], $famsDb->fam_man . "@");
                            if (strpos($this->globaltrack, $famsDb->fam_man . "@") !== false && $var5 === false) {
                                $this->totalpath = $this->ext_calc_join_path($this->relation['global_array'], $pathway, $famsDb->fam_man, "spo");
                                $_SESSION['next_path'] .= $famsDb->fam_man . "@";
                                //ext_calc_display_result($this->totalpath, $this->db_functions, $this->relation);
                                // TODO: return isn't used?
                                return ($famsDb->fam_man);
                            }
                            if (strpos($this->globaltrack2, $famsDb->fam_man . "@") === false) {
                                if ($var5 === false) {
                                    $work_array2[] = $famsDb->fam_man . "@spo@" . $value . "@" . $pathway . ";" . "spo" . $famsDb->fam_man;
                                }
                                $this->count++;
                                $this->globaltrack2 .= $famsDb->fam_man . "@";
                            }
                        }
                    }
                }
            }
        }

        if (isset($work_array[0]) && isset($work_array2[0])) {
            // no common person was found but both A and B still have a wider circle to expand -> call this function again
            $this->extended_calculator($work_array, $work_array2);
        } elseif (!isset($_SESSION['next_path'])) {
            $this->show_extended_message = __("These persons are not related in any way.");
        } else {
            $this->show_extended_message = __("No further paths found.");
        }
    }

    private function ext_calc_join_path($workarr, $path2, $pers2, $ref): string
    {
        // we have two trails. one from person A to the common person and one from person B to the common person (A ---> common <---- B)
        // we have to create one trail from A to B
        // since the second trail is reverse (from B to the common person) it first has to be turned around, including changing the relation to previous and next person

        // $workarr is the array with all trails from person A 
        // we have to find the trail that contains the common person ($pers2)
        foreach ($workarr as $value) {
            if (strpos($value . ";", $pers2 . ";") === false) {
                continue;
            }
            $path1 = substr($value, strrpos($value, "@") + 1);  // found the right trail
        }
        $fstcommon = substr($path1, strpos($path1 . ";", $pers2 . ";") - 3, 3); // find the common person as appears in the trail from person A ("parI3120")

        // now turn around the second trail and adjust par, chd, spo values accordingly
        $secpath = explode(";", $path2);
        $new_path2 = '';
        $changepath = array();
        $commonpers = ";" . $fstcommon . $pers2;
        if ($ref == "par" && $fstcommon == "par") {
            // the common person is a child of both sides - discard child and make right person spouse of left!
            $changepath[count($secpath) - 1] = "spo" . substr($secpath[count($secpath) - 1], 3);
            $commonpers = '';
            $_SESSION['next_path'] .= substr($secpath[count($secpath) - 1], 3) . "@"; // add parent from side B to ignore string for next path
            $par1str = substr($path1, 0, strrpos($path1, ";"));  // first take off last (=common) person
            $_SESSION['next_path'] .= substr($par1str, strrpos($par1str, ";") + 4) . "@";     // add parent from side A to ignore string for next path
        } elseif ($ref == "par") {
            $changepath[count($secpath) - 1] = "chd" . substr($secpath[count($secpath) - 1], 3);
        } elseif ($ref == "chd") {
            $changepath[count($secpath) - 1] = "par" . substr($secpath[count($secpath) - 1], 3);
        } else {
            $changepath[count($secpath) - 1] = "spo" . substr($secpath[count($secpath) - 1], 3);
        }
        for ($w = count($secpath) - 1; $w > 0; $w--) {
            if (substr($secpath[$w], 0, 3) === "par") {
                $changepath[$w - 1] = "chd" . substr($secpath[$w - 1], 3);
            } elseif (substr($secpath[$w], 0, 3) === "chd") {
                $changepath[$w - 1] = "par" . substr($secpath[$w - 1], 3);
            } else {
                $changepath[$w - 1] = "spo" . substr($secpath[$w - 1], 3);
            }
        }
        for ($w = count($changepath) - 1; $w >= 0; $w--) {
            $new_path2 .= ";" . $changepath[$w];
        }  // the entire trail from person A to B
        return (substr($path1, 0, strpos($path1, $pers2) - 4) . $commonpers . $new_path2);
    }

    public function get_links()
    {
        $processLinks = new ProcessLinks();
        // http://localhost/HuMo-genealogy/family/3/F116?main_person=I202
        $relation['fam_path'] = $processLinks->get_link($this->uri_path, 'family', $this->tree_id, true);
        $relation['rel_path'] = $processLinks->get_link($this->uri_path, 'relations', $this->tree_id);

        return $relation;
    }
}

<?php
class RelationsModel
{
    public function resetValues(): void
    {
        // *** Reset values ***
        if (
            !isset($_POST["search1"]) && !isset($_POST["search2"]) && !isset($_POST["calculator"]) && !isset($_POST["switch"]) && !isset($_POST["extended"]) && !isset($_POST["next_path"]) && !isset($_GET['pers_id']) && !isset($_POST["search_id1"]) && !isset($_POST["search_id2"])
        ) {
            // no button pressed: this is a fresh entry from frontpage link: start clean search form
            $_SESSION["search1"] = '';
            $_SESSION["search2"] = '';
            $_SESSION['rel_search_name'] = '';
            $_SESSION['rel_search_name2'] = '';
            $_SESSION['rel_search_gednr'] = '';
            $_SESSION['rel_search_gednr2'] = '';
            unset($_SESSION["search_pers_id"]);
            unset($_SESSION["search_pers_id2"]);
        }
    }

    public function checkInput(): void
    {
        if (isset($_POST["button_search_name1"]) || isset($_POST["button_search_id1"])) {
            $_SESSION["button_search_name1"] = 1;
        }
        if (isset($_POST["button_search_name2"]) || isset($_POST["button_search_id2"])) {
            $_SESSION["button_search_name2"] = 1;
        }

        // *** Link from person pop-up menu ***
        if (isset($_GET['pers_id'])) {
            $_SESSION["button_search_name1"] = 1;
            $_SESSION["search_pers_id"] = safe_text_db($_GET['pers_id']);
            unset($_SESSION["search_pers_id2"]);
            $_SESSION['rel_search_name'] = '';
        }
    }

    public function getSelectedPersons($db_functions, $person_cls)
    {
        // *** GEDCOM number: must be pattern like: Ixxxx ***
        $pattern = '/^^[a-z,A-Z][0-9]{1,}$/';

        $relation["person1"] = '';
        if (isset($_POST["person1"]) && preg_match($pattern, $_POST["person1"])) {
            $relation["person1"] = $_POST['person1'];
        }

        $relation["person2"] = '';
        if (isset($_POST["person2"]) && preg_match($pattern, $_POST["person2"])) {
            $relation["person2"] = $_POST['person2'];
        }

        // calculate or switch button is pressed
        if ((isset($_POST["calculator"]) || isset($_POST["switch"])) && $relation["person1"] && $relation["person2"]) {
            $searchDb = $db_functions->get_person($relation["person1"]);
            $relation['name1'] = '';
            $relation['sexe1'] = '';
            if (isset($searchDb)) {
                $relation['gednr1'] = $searchDb->pers_gedcomnumber;
                //$name = $pers_cls->person_name($searchDb);
                $name = $person_cls->person_name($searchDb);
                $relation['name1'] = $name["name"];
                //$relation['sexe1'] = $searchDb->pers_sexe == 'M' ? 'm' : 'f';
                $relation['sexe1'] = $searchDb->pers_sexe;
            }
            $relation['fams1'] = '';
            if ($searchDb->pers_fams) {
                $relation['fams1'] = $searchDb->pers_fams;
                $relation['fams1_array'] = explode(";", $relation['fams1']);
                $relation['family_id1'] = $relation['fams1_array'][0];
            } else {
                $relation['family_id1'] = $searchDb->pers_famc;
            }
            //$vars['pers_family'] = $relation['family_id1'];
            //$relation['link1'] = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);

            $searchDb2 = $db_functions->get_person($relation["person2"]);
            $relation['name2'] = '';
            $relation['sexe2'] = '';
            if (isset($searchDb2)) {
                $relation['gednr2'] = $searchDb2->pers_gedcomnumber;
                $name = $person_cls->person_name($searchDb2);
                $relation['name2'] = $name["name"];
                //$relation['sexe2'] = $searchDb2->pers_sexe == 'M' ? 'm' : 'f';
                $relation['sexe2'] = $searchDb2->pers_sexe;
            }
            $relation['fams2'] = '';
            if ($searchDb2->pers_fams) {
                $relation['fams2'] = $searchDb2->pers_fams;
                $relation['fams2_array'] = explode(";", $relation['fams2']);
                $relation['family_id2'] = $relation['fams2_array'][0];
            } else {
                $relation['family_id2'] = $searchDb2->pers_famc;
            }
            //$vars['pers_family'] = $relation['family_id2'];
            //$relation['link2'] = $link_cls->get_link($uri_path, 'family', $tree_id, true, $vars);
        }

        return $relation;
    }

    public function getNames()
    {
        // *** Person 1 ***
        $relation["search_name1"] = '';
        if (isset($_POST["search_name"]) && !isset($_POST["switch"])) {
            $relation["search_name1"] = safe_text_db($_POST['search_name']);
            $_SESSION['rel_search_name'] = $relation["search_name1"];
        }
        if (isset($_SESSION['rel_search_name'])) {
            $relation["search_name1"] = $_SESSION['rel_search_name'];
        }
        if (isset($_POST["button_search_id1"])) {
            $relation["search_name1"] = '';
        }

        // *** Person 2 ***
        $relation["search_name2"] = '';
        if (isset($_POST["search_name2"]) && !isset($_POST["switch"])) {
            $relation["search_name2"] = safe_text_db($_POST['search_name2']);
            $_SESSION['rel_search_name2'] = $relation["search_name2"];
        }
        if (isset($_SESSION['rel_search_name2'])) {
            $relation["search_name2"] = $_SESSION['rel_search_name2'];
        }
        if (isset($_POST["button_search_id2"])) {
            $relation["search_name2"] = '';
        }
        return $relation;
    }

    public function getGEDCOMnumbers()
    {
        $relation["search_gednr1"] = '';
        if (isset($_POST["search_gednr"]) && !isset($_POST["switch"])) {
            $relation["search_gednr1"] = strtoupper(safe_text_db($_POST['search_gednr']));
            $_SESSION['rel_search_gednr'] = $relation["search_gednr1"];
        }
        if (isset($_SESSION['rel_search_gednr'])) {
            $relation["search_gednr1"] = $_SESSION['rel_search_gednr'];
        }
        if (isset($_POST["button_search_name1"])) {
            $relation["search_gednr1"] = '';
        }

        $relation["search_gednr2"] = '';
        if (isset($_POST["search_gednr2"]) && !isset($_POST["switch"])) {
            $relation["search_gednr2"] = strtoupper(safe_text_db($_POST['search_gednr2']));
            $_SESSION['rel_search_gednr2'] = $relation["search_gednr2"];
        }
        if (isset($_SESSION['rel_search_gednr2'])) {
            $relation["search_gednr2"] = $_SESSION['rel_search_gednr2'];
        }
        if (isset($_POST["button_search_name2"])) {
            $relation["search_gednr2"] = '';
        }
        return $relation;
    }

    public function switchPersons($relation)
    {
        // *** Switch person 1 and 2 ***
        if (isset($_POST["switch"])) {
            $temp = $relation["search_name1"];
            $relation["search_name1"] = $relation["search_name2"];
            $_SESSION['rel_search_name'] = $relation["search_name1"];
            $relation["search_name2"] = $temp;
            $_SESSION['rel_search_name2'] = $relation["search_name2"];

            $temp = $relation["search_gednr1"];
            $relation["search_gednr1"] = $relation["search_gednr2"];
            $_SESSION['rel_search_gednr'] = $relation["search_gednr1"];
            $relation["search_gednr2"] = $temp;
            $_SESSION['rel_search_gednr2'] = $relation["search_gednr2"];

            $temp = $relation["person1"];
            $relation["person1"] = $relation["person2"];
            $relation["person2"] = $temp;

            // TODO: check code.
            /*
            if (isset($button_search_name1)) {
                $temp = $button_search_name1;
                $button_search_name1 = $button_search_name2;
                $_SESSION['button_search_name1'] = $button_search_name1;
                $button_search_name2 = $temp;
                $_SESSION['button_search_name2'] = $button_search_name2;
            }
            */

            // *** Link from person pop-up menu ***
            if (isset($_SESSION["search_pers_id"])) {
                $_SESSION["search_pers_id2"] = $_SESSION["search_pers_id"];
                unset($_SESSION["search_pers_id"]);
            }
            // *** Link from person pop-up menu ***
            elseif (isset($_SESSION["search_pers_id2"])) {
                $_SESSION["search_pers_id"] = $_SESSION["search_pers_id2"];
                unset($_SESSION["search_pers_id2"]);
            }
        }
        if (isset($relation)) {
            return $relation;
        }
        return null;
    }
}

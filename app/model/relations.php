<?php
class RelationsModel
{
    //private $db_functions;

    /*
    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }
    */

    public function resetValues()
    {
        // *** Reset values ***
        if (
            !isset($_POST["search1"]) and !isset($_POST["search2"]) and !isset($_POST["calculator"])
            and !isset($_POST["switch"]) and !isset($_POST["extended"]) and !isset($_POST["next_path"]) and !isset($_GET['pers_id'])
            and !isset($_POST["search_id1"]) and !isset($_POST["search_id2"])
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

    public function checkInput()
    {
        if (isset($_POST["button_search_name1"]) or isset($_POST["button_search_id1"])) {
            $_SESSION["button_search_name1"] = 1;
        }
        if (isset($_POST["button_search_name2"]) or isset($_POST["button_search_id2"])) {
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

    public function getSelectedPersons()
    {
        $data["person1"] = '';
        if (isset($_POST["person1"])) {
            $data["person1"] = $_POST['person1'];
        }

        $data["person2"] = '';
        if (isset($_POST["person2"])) {
            $data["person2"] = $_POST['person2'];
        }

        return $data;
    }

    public function getNames()
    {
        // *** Person 1 ***
        $data["search_name1"] = '';
        if (isset($_POST["search_name"]) and !isset($_POST["switch"])) {
            $data["search_name1"] = safe_text_db($_POST['search_name']);
            $_SESSION['rel_search_name'] = $data["search_name1"];
        }
        if (isset($_SESSION['rel_search_name'])) {
            $data["search_name1"] = $_SESSION['rel_search_name'];
        }
        if (isset($_POST["button_search_id1"])) {
            $data["search_name1"] = '';
        }

        // *** Person 2 ***
        $data["search_name2"] = '';
        if (isset($_POST["search_name2"]) and !isset($_POST["switch"])) {
            $data["search_name2"] = safe_text_db($_POST['search_name2']);
            $_SESSION['rel_search_name2'] = $data["search_name2"];
        }
        if (isset($_SESSION['rel_search_name2'])) {
            $data["search_name2"] = $_SESSION['rel_search_name2'];
        }
        if (isset($_POST["button_search_id2"])) {
            $data["search_name2"] = '';
        }
        return $data;
    }

    public function getGEDCOMnumbers()
    {
        $data["search_gednr1"] = '';
        if (isset($_POST["search_gednr"]) and !isset($_POST["switch"])) {
            $data["search_gednr1"] = strtoupper(safe_text_db($_POST['search_gednr']));
            $_SESSION['rel_search_gednr'] = $data["search_gednr1"];
        }
        if (isset($_SESSION['rel_search_gednr'])) {
            $data["search_gednr1"] = $_SESSION['rel_search_gednr'];
        }
        if (isset($_POST["button_search_name1"])) {
            $data["search_gednr1"] = '';
        }

        $data["search_gednr2"] = '';
        if (isset($_POST["search_gednr2"]) and !isset($_POST["switch"])) {
            $data["search_gednr2"] = strtoupper(safe_text_db($_POST['search_gednr2']));
            $_SESSION['rel_search_gednr2'] = $data["search_gednr2"];
        }
        if (isset($_SESSION['rel_search_gednr2'])) {
            $data["search_gednr2"] = $_SESSION['rel_search_gednr2'];
        }
        if (isset($_POST["button_search_name2"])) {
            $data["search_gednr2"] = '';
        }
        return $data;
    }

    public function switchPersons($data)
    {
        // *** Switch person 1 and 2 ***
        if (isset($_POST["switch"])) {
            $temp = $data["search_name1"];
            $data["search_name1"] = $data["search_name2"];
            $_SESSION['rel_search_name'] = $data["search_name1"];
            $data["search_name2"] = $temp;
            $_SESSION['rel_search_name2'] = $data["search_name2"];

            $temp = $data["search_gednr1"];
            $data["search_gednr1"] = $data["search_gednr2"];
            $_SESSION['rel_search_gednr'] = $data["search_gednr1"];
            $data["search_gednr2"] = $temp;
            $_SESSION['rel_search_gednr2'] = $data["search_gednr2"];

            $temp = $data["person1"];
            $data["person1"] = $data["person2"];
            $data["person2"] = $temp;

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
        if (isset($data)) return $data;
    }
}

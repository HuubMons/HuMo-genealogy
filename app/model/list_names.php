<?php
class list_namesModel
{
    //private $db_functions;

    /*
    public function __construct($db_functions)
    {
        $this->db_functions = $db_functions;
    }
    */

    public function getAlphabetArray($dbh, $tree_id, $user)
    {
        $person_qry = "SELECT UPPER(substring(pers_lastname,1,1)) as first_character
        FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY first_character ORDER BY first_character";

        // *** Search pers_prefix for names like: "van Mons" ***
        if ($user['group_kindindex'] == "j") {
            $person_qry = "SELECT UPPER(substring(CONCAT(pers_prefix,pers_lastname),1,1)) as first_character
            FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' GROUP BY first_character ORDER BY first_character";
        }
        $person_result = $dbh->query($person_qry);
        $alphabet = [];
        while ($personDb = $person_result->fetch(PDO::FETCH_OBJ)) {
            $alphabet[] = $personDb->first_character;
        }
        return $alphabet;
    }

    public function getMaxCols()
    {
        $maxcols = 2; // number of name & nr colums in table. For example 3 means 3x name col + nr col
        if (isset($_POST['maxcols']) && is_numeric($_POST['maxcols'])) {
            $maxcols = $_POST['maxcols'];
            $_SESSION["save_maxcols"] = $maxcols;
        }
        if (isset($_SESSION["save_maxcols"])) {
            $maxcols = $_SESSION["save_maxcols"];
        }
        return $maxcols;
    }

    public function getMaxNames()
    {
        $maxnames = 100;
        if (isset($_POST['freqsurnames']) && is_numeric($_POST['freqsurnames'])) {
            $maxnames = $_POST['freqsurnames'];
            $_SESSION["save_maxnames"] = $maxnames;
        }
        if (isset($_SESSION["save_maxnames"])) {
            $maxnames = $_SESSION["save_maxnames"];
        }
        return $maxnames;
    }
}

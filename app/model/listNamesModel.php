<?php
class listNamesModel extends BaseModel
{
    public function getAlphabetArray(): array
    {
        $person_qry = "SELECT UPPER(substring(pers_lastname,1,1)) as first_character FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY first_character ORDER BY first_character";

        // *** Search pers_prefix for names like: "van Mons" ***
        if ($this->user['group_kindindex'] == "j") {
            $person_qry = "SELECT UPPER(substring(CONCAT(pers_prefix,pers_lastname),1,1)) as first_character FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY first_character ORDER BY first_character";
        }
        $person_result = $this->dbh->query($person_qry);
        $alphabet = [];
        while ($personDb = $person_result->fetch(PDO::FETCH_OBJ)) {
            $alphabet[] = $personDb->first_character;
        }
        return $alphabet;
    }

    public function getMaxCols(): int
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

    public function get_last_name($last_name): string
    {
        if (!isset($last_name)) {
            $last_name = 'a'; // *** Default first_character ***
        }
        if (isset($_GET['last_name']) && $_GET['last_name'] && is_string($_GET['last_name'])) {
            $last_name = safe_text_db($_GET['last_name']);
        }
        return $last_name;
    }

    public function get_item(): int
    {
        $item = 0;
        if (isset($_GET['item'])) {
            $item = $_GET['item'];
        }
        return $item;
    }

    public function get_start(): int
    {
        $start = 0;
        if (isset($_GET["start"])) {
            $start = $_GET["start"];
        }
        return $start;
    }

    public function get_names($list_names): array
    {
        // *** Get names from database ***
        $list_names['number_high'] = 0;

        // Mons, van or: van Mons
        if ($this->user['group_kindindex'] == "j") {
            // *** Order names as: van Mons
            // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
            $personqry = "SELECT pers_prefix, pers_lastname, count(pers_lastname) as count_last_names
                FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND CONCAT(pers_prefix,pers_lastname) LIKE '" . $list_names["last_name"] . "%'
                GROUP BY pers_prefix, pers_lastname ORDER BY CONCAT(pers_prefix, pers_lastname)";

            // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
            $count_qry = "SELECT pers_lastname, pers_prefix
                FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND CONCAT(pers_prefix,pers_lastname) LIKE '" . $list_names["last_name"] . "%'
                GROUP BY pers_prefix, pers_lastname";

            if ($list_names["last_name"] == 'all') {
                // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
                $personqry = "SELECT pers_prefix, pers_lastname, count(pers_lastname) as count_last_names
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_prefix, pers_lastname ORDER BY CONCAT(pers_prefix, pers_lastname)";

                // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
                $count_qry = "SELECT pers_prefix, pers_lastname FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_prefix, pers_lastname";
            }
        } else {
            // Order names as: Mons, van
            // *** Select alphabet first_character ***
            // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
            $personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
                FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_lastname LIKE '" . $list_names["last_name"] . "%'
                GROUP BY pers_lastname, pers_prefix ORDER BY CONCAT(pers_lastname, pers_prefix)";

            // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
            $count_qry = "SELECT pers_lastname, pers_prefix
                FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_lastname LIKE '" . $list_names["last_name"] . "%'
                GROUP BY pers_lastname, pers_prefix";

            if ($list_names["last_name"] == 'all') {
                // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
                $personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_lastname, pers_prefix ORDER BY CONCAT(pers_lastname, pers_prefix)";

                // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
                $count_qry = "SELECT pers_lastname, pers_prefix FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_lastname, pers_prefix";
            }
        }

        // *** Add limit to query (results per page) ***
        if ($list_names["max_names"] != '999') {
            $personqry .= " LIMIT " . $list_names["item"] . "," . $list_names["max_names"];
        }

        //$list_names['show_name'] = [];
        $person = $this->dbh->query($personqry);
        while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
            if ($personDb->pers_lastname == '') {
                $personDb->pers_lastname = '...';
            }

            if ($this->user['group_kindindex'] == "j") {
                $show_name = '';
                if ($personDb->pers_prefix) {
                    $show_name = str_replace("_", " ", $personDb->pers_prefix);
                }
                $list_names['show_name'][] = $show_name . $personDb->pers_lastname;

                // *** Replace & by | for URL ***
                $list_names['link_name'][] = str_replace("_", " ", $personDb->pers_prefix) . str_replace("&", "|", $personDb->pers_lastname);
            } else {
                $show_name = $personDb->pers_lastname;
                if ($personDb->pers_prefix) {
                    $show_name .= ', ' . str_replace("_", " ", $personDb->pers_prefix);
                }
                $list_names['show_name'][] = $show_name;

                // *** Replace & by | for URL ***
                $link_name = str_replace("&", "|", $personDb->pers_lastname);
                if ($personDb->pers_prefix) {
                    $list_names['link_name'][] = $link_name . '&amp;pers_prefix=' . $personDb->pers_prefix;
                } else {
                    $list_names['link_name'][] = $link_name . '&amp;pers_prefix=EMPTY';
                }
            }

            $list_names['freq_count_last_names'][] = $personDb->count_last_names;
            if ($personDb->count_last_names > $list_names['number_high']) {
                $list_names['number_high'] = $personDb->count_last_names;
            }
        }

        if (isset($list_names['show_name'])) {
            $list_names['row'] = ceil(count($list_names['show_name']) / $list_names["max_cols"]);
        }

        // *** Total number of persons for multiple pages ***
        $result = $this->dbh->query($count_qry);
        $list_names['count_persons'] = $result->rowCount();

        // *** If number of displayed surnames is "ALL" change value into number of surnames ***
        $list_names['nr_persons'] = $list_names["max_names"];
        if ($list_names['nr_persons'] == 'ALL') {
            $list_names['nr_persons'] = $list_names['count_persons'];
        }

        $list_names['person'] = $person->rowCount();

        return $list_names;
    }

    function get_pagination($uri_path, $list_names): array
    {
        //*** Show number of persons and pages ***
        $list_names['show_pagination'] = false;
        // *** Check for search results ***
        //if ($list_names['person']->rowCount() > 0) {

        if ($list_names["person"] > 0) {
            if ($this->humo_option["url_rewrite"] == "j") {
                $uri_path_string = $uri_path . 'list_names/' . $this->tree_id . '/' . $list_names["last_name"] . '?';
            } else {
                $uri_path_string = 'index.php?page=list_names&amp;last_name=' . $list_names["last_name"] . '&amp;';
            }

            // "<="
            $list_names["previous_link"] = '';
            $list_names["previous_status"] = '';
            if ($list_names["start"] > 1) {
                $list_names['show_pagination'] = true;
                $calculated = ($list_names["start"] - 2) * $list_names['nr_persons'];
                $list_names["previous_link"] = $uri_path_string . "start=" . ($list_names["start"] - 20) . "&amp;item=" . $calculated;
            }
            if ($list_names["start"] <= 0) {
                $list_names["start"] = 1;
            }
            if ($list_names["start"] == '1') {
                $list_names["previous_status"] = 'disabled';
            }

            // 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
            for ($i = $list_names["start"]; $i <= $list_names["start"] + 19; $i++) {
                $calculated = ($i - 1) * $list_names['nr_persons'];
                if ($calculated < $list_names['count_persons']) {
                    $list_names["page_nr"][] = $i;
                    if ($list_names["item"] == $calculated) {
                        $list_names["page_status"][$i] = 'active';
                    } else {
                        $list_names['show_pagination'] = true;
                        $list_names["page_status"][$i] = '';
                    }
                    $list_names["page_link"][$i] = $uri_path_string . "start=" . $list_names["start"] . "&amp;item=" . $calculated;
                }
            }

            // "=>"
            $list_names["next_link"] = '';
            $list_names["next_status"] = '';
            $calculated = ($i - 1) * $list_names['nr_persons'];
            if ($calculated < $list_names['count_persons']) {
                $list_names['show_pagination'] = true;
                $list_names["next_link"] = $uri_path_string . "start=" . $i . "&amp;item=" . $calculated;
            } else {
                $list_names["next_status"] = 'disabled';
            }
        }
        return $list_names;
    }
}

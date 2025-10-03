<?php

/**
 * List and search persons.
 * 
 * Advanced search added by Yossi Beck. Translated and integrated in person search page by Huub.
 */

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use Genealogy\Include\BuildCondition;
use Genealogy\Include\SafeTextDb;
use PDO;

class ListModel extends BaseModel
{
    private $selection = [];
    private $query, $count_query;
    private $orderby, $make_date;
    private $index_list;

    public function getIndexList(): string
    {
        $this->index_list = 'quicksearch';
        // *** Reset if necessary ***
        if (isset($_POST['pers_firstname']) || isset($_GET['pers_lastname']) || isset($_GET['pers_firstname']) || isset($_GET['reset']) || isset($_POST['quicksearch'])) {
            $this->index_list = 'search';
        }
        if (isset($_POST["index_list"])) {
            $this->index_list = $_POST['index_list'];
        }
        if (isset($_GET["index_list"])) {
            $this->index_list = $_GET['index_list'];
        }
        return $this->index_list;
    }

    public function getOrder(): int
    {
        $order = 0;
        if (isset($_SESSION['sort_desc'])) {
            $order = $_SESSION['sort_desc'] == 1 ? 1 : 0;
        }
        if (isset($_GET['sort_desc'])) {
            if ($_GET['sort_desc'] == 1) {
                $order = 1;
                $_SESSION['sort_desc'] = 1;
            } else {
                $order = 0;
                $_SESSION['sort_desc'] = 0;
            }
        }
        return $order;
    }

    public function getDescAsc($order): string
    {
        return $order == 1 ? " DESC " : " ASC ";
    }

    public function getOrderSelect(): string
    {
        $selectsort = '';
        if (isset($_SESSION['sort']) && !isset($_GET['sort'])) {
            $selectsort = $_SESSION['sort'];
        }

        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == "sort_lastname") {
                $selectsort = "sort_lastname";
                $_SESSION['sort'] = $selectsort;
            }
            if ($_GET['sort'] == "sort_firstname") {
                $selectsort = "sort_firstname";
                $_SESSION['sort'] = $selectsort;
            }
            if ($_GET['sort'] == "sort_birthdate") {
                $selectsort = "sort_birthdate";
                $_SESSION['sort'] = $selectsort;
            }
            if ($_GET['sort'] == "sort_birthplace") {
                $selectsort = "sort_birthplace";
                $_SESSION['sort'] = $selectsort;
            }
            //if($_GET['sort']=="sort_baptdate") {
            //  $selectsort="sort_baptdate"; $_SESSION['sort']=$selectsort;
            //}
            if ($_GET['sort'] == "sort_deathdate") {
                $selectsort = "sort_deathdate";
                $_SESSION['sort'] = $selectsort;
            }
            if ($_GET['sort'] == "sort_deathplace") {
                $selectsort = "sort_deathplace";
                $_SESSION['sort'] = $selectsort;
            }
            //if($_GET['sort']=="sort_burieddate") {
            //  $selectsort="sort_burieddate"; $_SESSION['sort']=$selectsort;
            //}
        }
        return $selectsort;
    }

    public function getSelectTrees(): string
    {
        // *** Search in 1 or more family trees ***
        $select_trees = 'tree_selected';
        if (isset($_POST['select_trees'])) {
            $select_trees = $_POST['select_trees'];
            $_SESSION["save_select_trees"] = $select_trees;
        }
        if (isset($_GET["select_trees"])) {
            $select_trees = $_GET['select_trees'];
            $_SESSION["save_select_trees"] = $select_trees;
        }
        if (isset($this->humo_option['one_name_study']) && $this->humo_option['one_name_study'] == 'y') {
            $select_trees = "all_trees";
            $_SESSION["save_select_trees"] = $select_trees;
        }
        // *** Read session for multiple pages ***
        if (isset($_GET['item']) && isset($_SESSION["save_select_trees"])) {
            $select_trees = $_SESSION["save_select_trees"];
        }
        return $select_trees;
    }

    public function getSelection(): array
    {
        $change = false;

        $this->selection['pers_firstname'] = '';

        // Test to remember old extended search values (session is unset in ListController.php).
        //if (isset($_SESSION["save_selection"])) {
        //    $this->selection = $_SESSION["save_selection"];
        //}

        if (isset($_POST['pers_firstname'])) {
            $this->selection['pers_firstname'] = $_POST['pers_firstname'];
            //$this->selection['pers_firstname']=htmlentities($_POST['pers_firstname'],ENT_QUOTES,'UTF-8');
            $change = true;
        }
        // *** Used for frequent firstnames in statistics page ***
        if (isset($_GET['pers_firstname'])) {
            $this->selection['pers_firstname'] = $_GET['pers_firstname'];
            $_GET['adv_search'] = '1';
            $change = true;
        }

        $this->selection['part_firstname'] = '';
        if (isset($_POST['part_firstname'])) {
            $this->selection['part_firstname'] = $_POST['part_firstname'];
            $change = true;
        }
        if (isset($_GET['part_firstname'])) {
            $this->selection['part_firstname'] = $_GET['part_firstname'];
            $change = true;
        }

        // *** Prefix (names list and most frequent names in main menu.) ***
        $this->selection['pers_prefix'] = '';
        if (isset($_POST['pers_prefix'])) {
            $this->selection['pers_prefix'] = $_POST['pers_prefix'];
            $change = true;
        }
        if (isset($_GET['pers_prefix'])) {
            $this->selection['pers_prefix'] = $_GET['pers_prefix'];
            //$this->selection['pers_prefix']=htmlentities($_GET['pers_prefix'],ENT_QUOTES,'UTF-8');
            $change = true;
        }

        // *** Enable / disable pers_prefix search. Only use option if advanced search page is started/ used ***
        $this->selection['use_pers_prefix'] = 'USED';
        if (isset($_POST['part_lastname']) && !isset($_POST['use_pers_prefix'])) {
            $this->selection['pers_prefix'] = 'EMPTY';
            $this->selection['use_pers_prefix'] = 'EMPTY';
        }
        // *** Page is called from menu bar or direct link from main menu. Option should be enabled then ***
        if (isset($_GET['adv_search']) && $_GET['adv_search'] == '1') {
            $this->selection['use_pers_prefix'] = 'USED';
        }
        // *** Page is called from names list. Option should be disabled then ***
        if (isset($_GET['pers_prefix']) && $_GET['pers_prefix'] == 'EMPTY') {
            $this->selection['pers_prefix'] = 'EMPTY';
            $this->selection['use_pers_prefix'] = 'EMPTY';
        }

        // *** Lastname ***
        $this->selection['pers_lastname'] = '';
        if (isset($_POST['pers_lastname'])) {
            $this->selection['pers_lastname'] = $_POST['pers_lastname'];
            //$this->selection['pers_lastname']=htmlentities($_POST['pers_lastname'],ENT_QUOTES,'UTF-8');
            $change = true;
        }
        if ((isset($this->humo_option['one_name_study']) and $this->humo_option['one_name_study'] == 'y') && (isset($_GET['adv_search']) && $_GET['adv_search'] == 1 || isset($_GET['index_list']) && $_GET['index_list'] == 'search' || isset($_GET['reset']) && $_GET['reset'] == 1)) {
            $this->selection['pers_lastname'] = $this->humo_option['one_name_thename'];
            $change = true;
        }
        if (isset($_GET["pers_lastname"])) {
            $this->selection['pers_lastname'] = $_GET['pers_lastname'];
            //$this->selection['pers_lastname']=htmlentities($_GET['pers_lastname'],ENT_QUOTES,'UTF-8');
            $this->selection['pers_lastname'] = str_replace("|", "&", $this->selection['pers_lastname']);  // Don't use a & character in a GET link
            $change = true;
        }

        $this->selection['part_lastname'] = '';
        if (isset($_POST['part_lastname'])) {
            $this->selection['part_lastname'] = $_POST['part_lastname'];
            $change = true;
        }
        // *** Used for clicking in the names list ***
        if (isset($_GET['part_lastname'])) {
            $this->selection['part_lastname'] = $_GET['part_lastname'];
            $change = true;
        }

        $this->selection['birth_place'] = '';
        if (isset($_POST['birth_place'])) {
            $this->selection['birth_place'] = $_POST['birth_place'];
            $change = true;
        }
        $this->selection['part_birth_place'] = '';
        if (isset($_POST['part_birth_place'])) {
            $this->selection['part_birth_place'] = $_POST['part_birth_place'];
            $change = true;
        }

        $this->selection['death_place'] = '';
        if (isset($_POST['death_place'])) {
            $this->selection['death_place'] = $_POST['death_place'];
            $change = true;
        }
        $this->selection['part_death_place'] = '';
        if (isset($_POST['part_death_place'])) {
            $this->selection['part_death_place'] = $_POST['part_death_place'];
            $change = true;
        }

        // TODO check for numeric
        $this->selection['birth_year'] = '';
        if (isset($_POST['birth_year'])) {
            $this->selection['birth_year'] = $_POST['birth_year'];
            $change = true;
        }
        $this->selection['birth_year_end'] = '';
        if (isset($_POST['birth_year_end'])) {
            $this->selection['birth_year_end'] = $_POST['birth_year_end'];
            $change = true;
        }

        $this->selection['death_year'] = '';
        if (isset($_POST['death_year'])) {
            $this->selection['death_year'] = $_POST['death_year'];
            $change = true;
        }
        $this->selection['death_year_end'] = '';
        if (isset($_POST['death_year_end'])) {
            $this->selection['death_year_end'] = $_POST['death_year_end'];
            $change = true;
        }

        $this->selection['spouse_firstname'] = '';
        if (isset($_POST['spouse_firstname'])) {
            $this->selection['spouse_firstname'] = $_POST['spouse_firstname'];
            //$this->selection['spouse_firstname']=htmlentities($_POST['spouse_firstname'],ENT_QUOTES,'UTF-8');
            $change = true;
        }
        $this->selection['part_spouse_firstname'] = '';
        if (isset($_POST['part_spouse_firstname'])) {
            $this->selection['part_spouse_firstname'] = $_POST['part_spouse_firstname'];
            $change = true;
        }

        $this->selection['spouse_lastname'] = '';
        if (isset($_POST['spouse_lastname'])) {
            $this->selection['spouse_lastname'] = $_POST['spouse_lastname'];
            //$this->selection['spouse_lastname']=htmlentities($_POST['spouse_lastname'],ENT_QUOTES,'UTF-8');
            $change = true;
        }
        $this->selection['part_spouse_lastname'] = '';
        if (isset($_POST['part_spouse_lastname'])) {
            $this->selection['part_spouse_lastname'] = $_POST['part_spouse_lastname'];
            $change = true;
        }

        $this->selection['sexe'] = '';
        if (isset($_POST['sexe'])) {
            $this->selection['sexe'] = $_POST['sexe'];
            $change = true;
        } elseif (isset($_GET['sexe'])) {
            $this->selection['sexe'] = $_GET['sexe'];
            $change = true;
        }

        // *** Own Code ***
        $this->selection['own_code'] = '';
        if (isset($_POST['own_code'])) {
            $this->selection['own_code'] = $_POST['own_code'];
            $change = true;
        }
        $this->selection['part_own_code'] = '';
        if (isset($_POST['part_own_code'])) {
            $this->selection['part_own_code'] = $_POST['part_own_code'];
            $change = true;
        }

        // *** Gedcomnumber ***
        $this->selection['gednr'] = '';
        if (isset($_POST['gednr'])) {
            $this->selection['gednr'] = $_POST['gednr'];
            $change = true;
        }
        $this->selection['part_gednr'] = '';
        if (isset($_POST['part_gednr'])) {
            $this->selection['part_gednr'] = $_POST['part_gednr'];
            $change = true;
        }

        // *** Profession ***
        $this->selection['pers_profession'] = '';
        if (isset($_POST['pers_profession'])) {
            $this->selection['pers_profession'] = $_POST['pers_profession'];
            $change = true;
        }
        $this->selection['part_profession'] = '';
        if (isset($_POST['part_profession'])) {
            $this->selection['part_profession'] = $_POST['part_profession'];
            $change = true;
        }

        // *** Text ***
        $this->selection['text'] = '';
        if (isset($_POST['text'])) {
            $this->selection['text'] = $_POST['text'];
            $change = true;
        }
        $this->selection['part_text'] = '';
        if (isset($_POST['part_text'])) {
            $this->selection['part_text'] = $_POST['part_text'];
            $change = true;
        }

        // *** Place ***
        $this->selection['pers_place'] = '';
        if (isset($_POST['pers_place'])) {
            $this->selection['pers_place'] = $_POST['pers_place'];
            $change = true;
        }
        $this->selection['part_place'] = '';
        if (isset($_POST['part_place'])) {
            $this->selection['part_place'] = $_POST['part_place'];
            $change = true;
        }

        // *** Zip code ***
        $this->selection['zip_code'] = '';
        if (isset($_POST['zip_code'])) {
            $this->selection['zip_code'] = $_POST['zip_code'];
            $change = true;
        }
        $this->selection['part_zip_code'] = '';
        if (isset($_POST['part_zip_code'])) {
            $this->selection['part_zip_code'] = $_POST['part_zip_code'];
            $change = true;
        }

        // *** Research status ***
        $this->selection['parent_status'] = '';
        if (!isset($_POST['quicksearch']) && isset($_POST['parent_status'])) {
            $this->selection['parent_status'] = $_POST['parent_status'];
            $change = true;
        }

        // *** Witness ***
        $this->selection['witness'] = '';
        if (isset($_POST['witness'])) {
            $this->selection['witness'] = $_POST['witness'];
            $change = true;
        }
        $this->selection['part_witness'] = '';
        if (isset($_POST['part_witness'])) {
            $this->selection['part_witness'] = $_POST['part_witness'];
            $change = true;
        }
        // *** Store selection if an item is changed ***
        if ($change == true) {
            $_SESSION["save_selection"] = $this->selection;
        }

        // *** Read session for multiple pages ***
        // *** Multiple search values ***
        if (isset($_GET['item']) && isset($_SESSION["save_selection"])) {
            $this->selection = $_SESSION["save_selection"];
        }

        return $this->selection;
    }

    public function getQueryOrderBy($desc_asc, $order_select)
    {
        // *** SOME DEFAULTS ***
        $last_or_patronym = " pers_lastname ";
        if ($this->index_list == 'patronym') {
            $last_or_patronym = " pers_patronym ";
        }

        //REMARK: at this moment also used to select birth/baptise or death/buried place...
        $this->make_date = ''; // we only need this when sorting by date

        $this->orderby = $last_or_patronym . $desc_asc . ", pers_firstname " . $desc_asc;
        if ($this->user['group_kindindex'] == "j" && $this->index_list != 'patronym') {
            $this->orderby = " concat_name " . $desc_asc;
        }

        $selectsort = $order_select;
        if ($selectsort) {
            if ($selectsort == "sort_lastname") {
                $this->orderby = $last_or_patronym . $desc_asc . ", pers_firstname " . $desc_asc;
                if ($this->user['group_kindindex'] == "j" && $this->index_list != 'patronym') {
                    $this->orderby = " concat_name " . $desc_asc;
                }
            }
            if ($selectsort == "sort_firstname") {
                $this->orderby = " pers_firstname " . $desc_asc . "," . $last_or_patronym . $desc_asc;
            }

            if ($selectsort == "sort_birthdate") {
                $this->make_date = ",
                    COALESCE(
                        birth.date_year,
                        bapt.date_year
                    ) AS order_year,
                    COALESCE(
                        birth.date_month,
                        bapt.date_month
                    ) AS order_month,
                    COALESCE(
                        birth.date_day,
                        bapt.date_day
                    ) AS order_day
                ";

                $this->orderby = " order_year $desc_asc, order_month $desc_asc, order_day $desc_asc, $last_or_patronym ASC, pers_firstname ASC";
            }
            if ($selectsort == "sort_birthplace") {
                $this->make_date = ",
                    COALESCE(
                        birth_location.location_location,
                        bapt_location.location_location
                    ) AS place
                ";

                $this->orderby = " place" . $desc_asc . ", " . $last_or_patronym . $desc_asc;
            }

            if ($selectsort == "sort_deathdate") {
                $this->make_date = ",
                    COALESCE(
                        death.date_year,
                        buried.date_year
                    ) AS order_year,
                    COALESCE(
                        death.date_month,
                        buried.date_month
                    ) AS order_month,
                    COALESCE(
                        death.date_day,
                        buried.date_day
                    ) AS order_day
                ";

                $this->orderby = " order_year $desc_asc, order_month $desc_asc, order_day $desc_asc, $last_or_patronym ASC, pers_firstname ASC";
            }
            if ($selectsort == "sort_deathplace") {
                $this->make_date = ",
                    COALESCE(
                        death_location.location_location,
                        buried_location.location_location
                    ) AS place
                ";

                $this->orderby = " place" . $desc_asc . ", " . $last_or_patronym . $desc_asc;
            }
        }

        $data["orderby"] = $this->orderby;
        $data["make_date"] = $this->make_date;
        return $data;
    }

    public function getQuickSearch(): string
    {
        $quicksearch = '';
        if (isset($_POST['quicksearch'])) {
            //$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
            $quicksearch = $_POST['quicksearch'];
            $_SESSION["save_quicksearch"] = $quicksearch;
        }
        // *** Switch from advanced search to standard search (now quick search) ***
        if ((isset($_GET['adv_search']) and $_GET['adv_search'] == '0') && isset($_SESSION["save_quicksearch"])) {
            $quicksearch = $_SESSION["save_quicksearch"];
        }
        // *** Read session for multiple pages ***
        if (isset($_GET['item']) && isset($_SESSION["save_quicksearch"])) {
            $quicksearch = $_SESSION["save_quicksearch"];
        }
        return $quicksearch;
    }

    public function getAdvSearch(): bool
    {
        $adv_search = false;
        // *** Link from "names" list, automatically uses advanced search ***
        if (isset($_GET['part_lastname'])) {
            $_GET['adv_search'] = '1';
            $_SESSION["save_selection"] = $this->selection;
        }
        if (isset($_GET['adv_search'])) {
            if ($_GET['adv_search'] == '1') {
                $adv_search = true;
            }
            $_SESSION["save_adv_search"] = $adv_search;
        }
        if (isset($_POST['adv_search'])) {
            if ($_POST['adv_search'] == '1') {
                $adv_search = true;
            }
            $_SESSION["save_adv_search"] = $adv_search;
        }
        // *** Read session for multiple pages ***
        if (isset($_GET['item']) && isset($_SESSION["save_adv_search"])) {
            $adv_search = $_SESSION["save_adv_search"];
        }
        return $adv_search;
    }

    public function getIndexPlaces(): array
    {
        // *** For index places ***
        $data["place_name"] = '';
        $data["select_birth"] = '0';
        $data["select_bapt"] = '0';
        $data["select_place"] = '0';
        $data["select_death"] = '0';
        $data["select_buried"] = '0';
        $data["select_event"] = '0';
        if (isset($_POST['place_name'])) {
            $data["place_name"] = $_POST['place_name'];
            //$data["place_name"]=htmlentities($_POST['place_name'],ENT_QUOTES,'UTF-8');
            $_SESSION["save_place_name"] = $data["place_name"];

            if (isset($_POST['select_birth'])) {
                $data["select_birth"] = '1';
                $_SESSION["save_select_birth"] = '1';
            } else {
                $_SESSION["save_select_birth"] = '0';
            }
            if (isset($_POST['select_bapt'])) {
                $data["select_bapt"] = '1';
                $_SESSION["save_select_bapt"] = '1';
            } else {
                $_SESSION["save_select_bapt"] = '0';
            }
            if (isset($_POST['select_place'])) {
                $data["select_place"] = '1';
                $_SESSION["save_select_place"] = '1';
            } else {
                $_SESSION["save_select_place"] = '0';
            }
            if (isset($_POST['select_death'])) {
                $data["select_death"] = '1';
                $_SESSION["save_select_death"] = '1';
            } else {
                $_SESSION["save_select_death"] = '0';
            }
            if (isset($_POST['select_buried'])) {
                $data["select_buried"] = '1';
                $_SESSION["save_select_buried"] = '1';
            } else {
                $_SESSION["save_select_buried"] = '0';
            }
            if (isset($_POST['select_event'])) {
                $data["select_event"] = '1';
                $_SESSION["save_select_event"] = '1';
            } else {
                $_SESSION["save_select_event"] = '0';
            }
        }

        $data["part_place_name"] = '';
        if (isset($_POST['part_place_name'])) {
            $data["part_place_name"] = $_POST['part_place_name'];
            $_SESSION["save_part_place_name"] = $data["part_place_name"];
        }

        // *** Search for places in birth-baptise-died places etc. ***
        if ($this->index_list == 'places') {
            if (isset($_SESSION["save_place_name"])) {
                $data["place_name"] = $_SESSION["save_place_name"];
            }
            if (isset($_SESSION["save_part_place_name"])) {
                $data["part_place_name"] = $_SESSION["save_part_place_name"];
            }

            // *** Enable select boxes ***
            if (isset($_GET['reset'])) {
                $data["select_birth"] = '1';
                $_SESSION["save_select_birth"] = '1';
                $data["select_bapt"] = '1';
                $_SESSION["save_select_bapt"] = '1';
                $data["select_place"] = '1';
                $_SESSION["save_select_place"] = '1';
                $data["select_death"] = '1';
                $_SESSION["save_select_death"] = '1';
                $data["select_buried"] = '1';
                $_SESSION["save_select_buried"] = '1';
                $data["select_event"] = '1';
                $_SESSION["save_select_event"] = '1';
            } else {
                // *** Read and set select boxes for multiple pages ***
                if (isset($_SESSION["save_select_birth"])) {
                    $data["select_birth"] = $_SESSION["save_select_birth"];
                }
                if (isset($_SESSION["save_select_bapt"])) {
                    $data["select_bapt"] = $_SESSION["save_select_bapt"];
                }
                if (isset($_SESSION["save_select_place"])) {
                    $data["select_place"] = $_SESSION["save_select_place"];
                }
                if (isset($_SESSION["save_select_death"])) {
                    $data["select_death"] = $_SESSION["save_select_death"];
                }
                if (isset($_SESSION["save_select_buried"])) {
                    $data["select_buried"] = $_SESSION["save_select_buried"];
                }
                if (isset($_SESSION["save_select_event"])) {
                    $data["select_event"] = $_SESSION["save_select_event"];
                }
            }
        }
        return $data;
    }

    public function qry_advanced_search()
    {
        $safeTextDb = new SafeTextDb();
        $buildCondition = new BuildCondition();

        $this->query = '';
        $this->count_query = '';

        // TODO improve processing of variables (use $this->xxxxx).
        //$this->selection = $this->getSelection();
        $select_trees = $this->getSelectTrees($this->humo_option);
        $order = $this->getOrder();
        $desc_asc = $this->getDescAsc($order);
        $order_select = $this->getOrderSelect();

        $this->getQueryOrderBy($desc_asc, $order_select);

        //*** Results of searchform in mainmenu ***
        //*** Or: search in lastnames ***
        if (
            $this->selection['pers_firstname'] || $this->selection['pers_prefix'] || $this->selection['pers_lastname'] || $this->selection['birth_place'] || $this->selection['death_place'] || $this->selection['birth_year'] || $this->selection['death_year'] || $this->selection['sexe'] && $this->selection['sexe'] != 'both' || $this->selection['own_code'] || $this->selection['gednr'] || $this->selection['pers_profession'] || $this->selection['pers_place'] || $this->selection['text'] || $this->selection['zip_code'] || $this->selection['witness'] || $this->selection['parent_status'] != ""
        ) {

            // *** Build query ***
            //$and=" ";
            $and = " AND ";

            $add_address_qry = false;
            $add_event_qry = false;
            $add_text_qry = false;

            if ($this->selection['pers_lastname']) {
                if ($this->selection['pers_lastname'] == __('...')) {
                    $this->query .= $and . " pers_lastname=''";
                    $and = " AND ";
                } elseif ($this->user['group_kindindex'] == "j") {
                    $this->query .= $and . " CONCAT( REPLACE(pers_prefix,'_',' ') ,pers_lastname) " . $buildCondition->build($this->selection['pers_lastname'], $this->selection['part_lastname']);
                    $and = " AND ";
                } else {
                    $this->query .= $and . " pers_lastname " . $buildCondition->build($this->selection['pers_lastname'], $this->selection['part_lastname']);
                    $and = " AND ";
                }
            }
            // TODO Use a parameterized query to prevent SQL injection
            //$this->query .= $and . "pers_lastname = :pers_lastname";
            //$this->queryParams[':pers_lastname'] = $this->selection['pers_lastname'];

            // *** Namelist: search persons without pers_prefix ***
            if ($this->selection['pers_prefix'] == 'EMPTY') {
                $this->query .= $and . "pers_prefix=''";
                $and = " AND ";
            } elseif ($this->selection['pers_prefix']) {
                // *** Search results for: "van", "van " and "van_" ***
                $pers_prefix = $safeTextDb->safe_text_db(str_replace(' ', '_', $this->selection['pers_prefix']));
                $this->query .= $and . "(pers_prefix='" . $pers_prefix . "' OR pers_prefix ='" . $pers_prefix . '_' . "')";
                $and = " AND ";
            }
            // TODO Use a parameterized query to prevent SQL injection
            //$this->query .= $and . "pers_prefix = :pers_prefix";
            //$this->queryParams[':pers_prefix'] = $this->selection['pers_prefix'];

            if ($this->selection['pers_firstname']) {
                $this->query .= $and . "(pers_firstname " . $buildCondition->build($this->selection['pers_firstname'], $this->selection['part_firstname']);
                //$this->query .= " OR (event_kind='name' AND event_event " . $buildCondition->build($this->selection['pers_firstname'], $this->selection['part_firstname']) . ') )';
                $this->query .= " OR (events.event_kind='name' AND events.event_event " . $buildCondition->build($this->selection['pers_firstname'], $this->selection['part_firstname']) . ') )';

                $and = " AND ";
                $add_event_qry = true;
            }
            // TODO Use a parameterized query to prevent SQL injection
            //$this->query .= $and . "pers_firstname = :pers_firstname";
            //$this->queryParams[':pers_firstname'] = $this->selection['pers_firstname'];

            // *** Search for born AND baptised place ***
            if ($this->selection['birth_place']) {
                //$this->query .= $and . "(pers_birth_place " . $buildCondition->build($this->selection['birth_place'], $this->selection['part_birth_place']);
                //$and = " AND ";
                //$this->query .= " OR pers_bapt_place " . $buildCondition->build($this->selection['birth_place'], $this->selection['part_birth_place']) . ')';
                //$and = " AND ";

                // Get birth and baptism place from events table.
                $this->query .= $and . "(
                    (pers_id IN (
                        SELECT person_id
                        FROM humo_events
                        WHERE event_kind = 'birth'
                        AND birth_location.location_location " . $buildCondition->build($this->selection['birth_place'], $this->selection['part_birth_place']) . "
                    ))
                    OR
                    (pers_id IN (
                        SELECT person_id
                        FROM humo_events
                        WHERE event_kind = 'baptism'
                        AND bapt_location.location_location " . $buildCondition->build($this->selection['birth_place'], $this->selection['part_birth_place']) . "
                    ))
                )";
                $and = " AND ";
            }
            // TODO Use a parameterized query to prevent SQL injection
            //$this->query .= $and . "pers_birth_place = :pers_birth_place";
            //$this->queryParams[':pers_birth_place'] = $this->selection['pers_birth_place'];

            // *** Search for death AND buried place ***
            if ($this->selection['death_place']) {
                /*
                $this->query .= $and . "(pers_death_place " . $buildCondition->build($this->selection['death_place'], $this->selection['part_death_place']);
                $and = " AND ";
                $this->query .= " OR pers_buried_place " . $buildCondition->build($this->selection['death_place'], $this->selection['part_death_place']) . ')';
                $and = " AND ";
                */

                // Use event table for death and buried place search
                $this->query .= $and . "(
                    (pers_id IN (
                        SELECT person_id
                        FROM humo_events
                        WHERE event_kind = 'death'
                        AND death_location.location_location " . $buildCondition->build($this->selection['death_place'], $this->selection['part_death_place']) . "
                    ))
                    OR
                    (pers_id IN (
                        SELECT person_id
                        FROM humo_events
                        WHERE event_kind = 'burial'
                        AND buried_location.location_location " . $buildCondition->build($this->selection['death_place'], $this->selection['part_death_place']) . "
                    ))
                )";
                $and = " AND ";
            }

            if ($this->selection['birth_year']) {
                if (!$this->selection['birth_year_end']) {
                    // filled in one year: exact date
                    // Search birth and baptism year using event table
                    $this->query .= $and . "(
                        (pers_id IN (
                            SELECT person_id
                            FROM humo_events
                            WHERE event_kind = 'birth'
                            AND date_year = '" . $safeTextDb->safe_text_db($this->selection['birth_year']) . "'
                        ))
                        OR
                        (pers_id IN (
                            SELECT person_id
                            FROM humo_events
                            WHERE event_kind = 'baptism'
                            AND date_year = '" . $safeTextDb->safe_text_db($this->selection['birth_year']) . "'
                        ))
                    )";
                    $and = " AND ";
                } else {
                    // Search birth and baptism year range using event table
                    $this->query .= $and . "(
                        (pers_id IN (
                            SELECT person_id
                            FROM humo_events
                            WHERE event_kind = 'birth'
                            AND date_year BETWEEN '" . $safeTextDb->safe_text_db($this->selection['birth_year']) . "' AND '" . $safeTextDb->safe_text_db($this->selection['birth_year_end']) . "'
                        ))
                        OR
                        (pers_id IN (
                            SELECT person_id
                            FROM humo_events
                            WHERE event_kind = 'baptism'
                            AND date_year BETWEEN '" . $safeTextDb->safe_text_db($this->selection['birth_year']) . "' AND '" . $safeTextDb->safe_text_db($this->selection['birth_year_end']) . "'
                        ))
                    )";

                    $and = " AND ";
                }
            }

            if ($this->selection['death_year']) {
                if (!$this->selection['death_year_end']) {
                    // filled in one year: exact date
                    // Search death and burial year using event table
                    $this->query .= $and . "(
                        (pers_id IN (
                            SELECT person_id
                            FROM humo_events
                            WHERE event_kind = 'death'
                            AND date_year = '" . $safeTextDb->safe_text_db($this->selection['death_year']) . "'
                        ))
                        OR
                        (pers_id IN (
                            SELECT person_id
                            FROM humo_events
                            WHERE event_kind = 'burial'
                            AND date_year = '" . $safeTextDb->safe_text_db($this->selection['death_year']) . "'
                        ))
                    )";
                    $and = " AND ";
                } else {
                    // Search death and burial year range using event table
                    $this->query .= $and . "(
                        (pers_id IN (
                            SELECT person_id
                            FROM humo_events
                            WHERE event_kind = 'death'
                            AND date_year BETWEEN '" . $safeTextDb->safe_text_db($this->selection['death_year']) . "' AND '" . $safeTextDb->safe_text_db($this->selection['death_year_end']) . "'
                        ))
                        OR
                        (pers_id IN (
                            SELECT person_id
                            FROM humo_events
                            WHERE event_kind = 'burial'
                            AND date_year BETWEEN '" . $safeTextDb->safe_text_db($this->selection['death_year']) . "' AND '" . $safeTextDb->safe_text_db($this->selection['death_year_end']) . "'
                        ))
                    )";
                    $and = " AND ";
                }
            }

            if ($this->selection['sexe'] == "M" || $this->selection['sexe'] == "F") {
                $this->query .= $and . "pers_sexe='" . $this->selection['sexe'] . "'";
                $and = " AND ";
            }
            if ($this->selection['sexe'] == "Unknown") {
                $this->query .= $and . "(pers_sexe!='M' AND pers_sexe!='F')";
                $and = " AND ";
            }
            // TODO Use a parameterized query to prevent SQL injection
            //$this->query .= $and . "pers_sexe = :pers_sexe";
            //$this->queryParams[':pers_sexe'] = $this->selection['sexe'];

            if ($this->selection['own_code']) {
                $this->query .= $and . "pers_own_code " . $buildCondition->build($this->selection['own_code'], $this->selection['part_own_code']);
                $and = " AND ";
            }

            if ($this->selection['gednr']) {
                if (strtoupper(substr($_POST['gednr'], 0, 1)) !== 'I') {
                    $this->selection['gednr'] = 'I' . $_POST['gednr']; // if only number was entered - add "I" before
                } else {
                    $this->selection['gednr'] = strtoupper($_POST['gednr']); // in case lowercase "i" was entered before number, make it "I"
                }
                $this->query .= $and . "pers_gedcomnumber " . $buildCondition->build($this->selection['gednr'], $this->selection['part_gednr']);
                $and = " AND ";
            }

            if ($this->selection['pers_profession']) {
                $this->query .= $and . " (events.event_kind='profession' AND events.event_event " . $buildCondition->build($this->selection['pers_profession'], $this->selection['part_profession']) . ')';
                $and = " AND ";
                $add_event_qry = true;
            }

            if ($this->selection['text']) {
                // *** Search in person and family text ***
                $this->query .= $and . " (pers_text " . $buildCondition->build($this->selection['text'], $this->selection['part_text']) . "
                    OR fam_text " . $buildCondition->build($this->selection['text'], $this->selection['part_text']) . ")";
                $and = " AND ";

                $add_text_qry = true;
            }

            if ($this->selection['pers_place']) {
                $this->query .= $and . " address_place " . $buildCondition->build($this->selection['pers_place'], $this->selection['part_place']);
                $and = " AND ";
                $add_address_qry = true;
            }

            if ($this->selection['zip_code']) {
                $this->query .= $and . " address_zip " . $buildCondition->build($this->selection['zip_code'], $this->selection['part_zip_code']);
                $and = " AND ";
                $add_address_qry = true;
            }

            if ($this->selection['witness']) {
                //$this->query .= $and . " ( RIGHT(events.event_kind,7)='witness' AND events.event_event " . $buildCondition->build($this->selection['witness'], $this->selection['part_witness']) . ')';
                $this->query .= $and . " ( events.event_kind='ASSO' AND events.event_event " . $buildCondition->build($this->selection['witness'], $this->selection['part_witness']) . ')';
                $and = " AND ";
                $add_event_qry = true;
            }

            if ($this->selection['parent_status'] && $this->selection['parent_status'] == "noparents") {
                $this->query .= $and . " (pers_famc = '') ";
                $and = " AND ";
                $add_event_qry = true;
            }

            // *** Change query if searched for spouse ***
            if ($this->selection['spouse_firstname'] || $this->selection['spouse_lastname']) {
                $this->query .= $and . "pers_fams!=''";
                $and = " AND ";
            }


            // *** Build SELECT part of query. Search with option "ALL family trees" or "All but selected" ***
            if ($select_trees == 'all_trees' || $select_trees == 'all_but_this') {
                $query_part = $this->query;

                $counter = 0;
                $multi_tree = '';
                foreach ($this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order") as $datapdo) {
                    if ($select_trees == "all_but_this" && $datapdo['tree_id'] == $this->tree_id) {
                        continue;
                    }

                    // *** Check is family tree is shown or hidden for user group ***
                    $hide_tree_array = explode(";", $this->user['group_hide_trees']);
                    if (!in_array($datapdo['tree_id'], $hide_tree_array)) {
                        if ($counter > 0) {
                            $multi_tree .= ' OR ';
                        }
                        $multi_tree .= 'pers_tree_id=' . $datapdo['tree_id'];
                        $counter++;
                    }
                }
            } else {
                // *** Start building query, search in 1 database ***
                $multi_tree = " pers_tree_id='" . $this->tree_id . "'";
            }

            // *** Build query, only add events and addresses tables if necessary ***
            // *** April 2023: simplified query, and added search in fam_text ***
            // *** Aug. 2017: renewed querie because of > MySQL 5.7 ***
            $query_select = "SELECT SQL_CALC_FOUND_ROWS humo_persons.*";

            if ($add_event_qry) {
                //$query_select .= ", event_event, event_kind";
                $query_select .= ", events.event_event, events.event_kind";
            }
            if ($add_address_qry) {
                $query_select .= ", address_place, address_zip";
            }
            // Text isn't needed in results
            //	if ($add_text_qry) $query_select .= ", fam_text";

            if ($this->user['group_kindindex'] == "j") {
                // *** Change ordering of index, using concat name ***
                $query_select .= ", CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name ";
            }

            //NIEUW
            // TODO birth.date_year, birth.date_month, birth.date_day. Maybe not needed here?
            $query_select .= ",
                birth.event_date AS pers_birth_date,
                birth.date_year, birth.date_month, birth.date_day,
                birth_location.location_location AS pers_birth_place,
                bapt.event_date AS pers_bapt_date,
                bapt.date_year, bapt.date_month, bapt.date_day,
                bapt_location.location_location AS pers_bapt_place,
                death.event_date AS pers_death_date,
                death.date_year, death.date_month, death.date_day,
                death_location.location_location AS pers_death_place,
                buried.event_date AS pers_buried_date,
                buried.date_year, buried.date_month, buried.date_day,
                buried_location.location_location AS pers_buried_place";
            $query_select .= $this->make_date . " FROM humo_persons";

            if ($add_event_qry) {
                $query_select .= " LEFT JOIN humo_events as events
                    ON events.event_tree_id=pers_tree_id
                    AND events.event_connect_id=pers_gedcomnumber
                    AND events.event_kind NOT IN ('birth', 'baptism', 'burial', 'death')";
            }

            if ($add_address_qry) {
                // Check query. There is AND and OR. Maybe () needed...
                $query_select .= " LEFT JOIN humo_connections
                    ON connect_tree_id=pers_tree_id
                    AND connect_connect_id=pers_gedcomnumber
                    AND connect_sub_kind='person_address'
                    LEFT JOIN humo_addresses
                    ON address_connect_id=pers_gedcomnumber
                    AND address_connect_sub_kind='person'
                    AND address_tree_id=pers_tree_id

                    OR address_gedcomnr=connect_item_id
                    AND address_tree_id=connect_tree_id

                    AND connect_connect_id=pers_gedcomnumber";
            }

            if ($add_text_qry) {
                // *** This query is extremely SLOW. Because of combination fam_man/ fam_woman=pers_gedcomnumber! ***
                //AND fam_text LIKE '_%'
                //$query_select .= " LEFT JOIN humo_families
                //ON fam_tree_id=pers_tree_id
                //AND (fam_man=pers_gedcomnumber OR fam_woman=pers_gedcomnumber)";

                $query_select .= " LEFT JOIN(
                    SELECT fam_tree_id,fam_text,fam_man as find_person FROM humo_families WHERE fam_text LIKE '_%'
                    UNION
                    SELECT fam_tree_id,fam_text,fam_woman as find_person FROM humo_families WHERE fam_text LIKE '_%'
                    ) as humo_families
                    ON fam_tree_id=pers_tree_id
                    AND find_person=pers_gedcomnumber
                ";

                /*
                // TODO test query
                $query_select .= " LEFT JOIN (
                    SELECT fam_tree_id, fam_text, fam_man as find_person FROM humo_families WHERE fam_text LIKE '_%'
                    UNION ALL
                    SELECT fam_tree_id, fam_text, fam_woman as find_person FROM humo_families WHERE fam_text LIKE '_%'
                ) AS humo_families
                ON humo_families.fam_tree_id = pers_tree_id
                AND humo_families.find_person = pers_gedcomnumber
                */
            }

            $query_select .= " LEFT JOIN humo_events AS birth
                    ON birth.person_id = humo_persons.pers_id AND birth.event_kind = 'birth'
                LEFT JOIN humo_location AS birth_location
                    ON birth.place_id = birth_location.location_id";

            $query_select .= " LEFT JOIN humo_events AS bapt
                    ON bapt.person_id = humo_persons.pers_id AND bapt.event_kind = 'baptism'
                LEFT JOIN humo_location AS bapt_location
                    ON bapt.place_id = bapt_location.location_id";

            $query_select .= " LEFT JOIN humo_events AS death
                    ON death.person_id = humo_persons.pers_id AND death.event_kind = 'death'
                LEFT JOIN humo_location AS death_location
                    ON death.place_id = death_location.location_id";

            $query_select .= " LEFT JOIN humo_events AS buried
                    ON buried.person_id = humo_persons.pers_id AND buried.event_kind = 'burial'
                LEFT JOIN humo_location AS buried_location
                    ON buried.place_id = buried_location.location_id";

            // *** GROUP BY is needed to prevent double results if searched for events ***
            $query_select .= " WHERE (" . $multi_tree . ") " . $this->query . " GROUP BY pers_id";

            $query_select .= " ORDER BY " . $this->orderby;
            $this->query = $query_select;
        }
    }


    public function qry_quicksearch()
    {
        $safeTextDb = new SafeTextDb();
        $quicksearch = $this->getQuickSearch();
        $select_trees = $this->getSelectTrees($this->humo_option);

        $this->query = '';

        // *** Replace space by % to find first AND lastname in one search "Huub Mons" ***
        $quicksearch = str_replace(' ', '%', $quicksearch);
        if ($this->humo_option['one_name_study'] == 'y') {
            $quicksearch .= '%' . $this->humo_option['one_name_thename'];
        }
        // *** In case someone entered "Mons, Huub" using a comma ***
        $quicksearch = str_replace(',', '', $quicksearch);

        // One can enter "Huub Mons", "Mons Huub", "Huub van Mons", "van Mons, Huub", "Mons, Huub van" and even "Mons van, Huub"

        // *** Build SELECT part of query. Search in ALL family trees ***
        if ($select_trees == 'all_trees' || $select_trees == 'all_but_this') {
            //$this->query = '';
            $counter = 0;
            $multi_tree = '';

            /*
            // TODO test code.
            // Cache the list of trees in a class property to avoid repeated queries
                if (!isset($this->treeListCache)) {
                    $this->treeListCache = [];
                    foreach ($this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order") as $treeRow) {
                        $this->treeListCache[] = $treeRow;
                    }
                }
                foreach ($this->treeListCache as $pdoresult) {
            */

            foreach ($this->dbh->query("SELECT * FROM humo_trees WHERE tree_prefix!='EMPTY' ORDER BY tree_order") as $pdoresult) {
                if ($select_trees == "all_but_this" && $pdoresult['tree_id'] == $this->tree_id) {
                    continue;
                }
                // *** Check if family tree is shown or hidden for user group ***
                $hide_tree_array = explode(";", $this->user['group_hide_trees']);
                if (!in_array($pdoresult['tree_id'], $hide_tree_array)) {
                    if ($counter > 0) {
                        $multi_tree .= ' OR ';
                    }
                    $multi_tree .= 'pers_tree_id=' . $pdoresult['tree_id'];
                    $counter++;
                }
            }
        } else {
            // *** Start building query, search in 1 database ***
            $multi_tree = "pers_tree_id='" . $this->tree_id . "'";
        }

        /**
         * Quicksearch query
         * Feb 2016: added search for patronym
         * Aug 2017: changed for MySQL > 5.7.
         * Nov. 2022: changed first patronymic line
         * April 2023: added pers_firstname, event_event. To find "firstname eventname" (event could be a kind of lastname too).
         */

        // TODO remove birth.place_id AS pers_birth_place_id ????
        // TODO date_year, date_month, etc nazien.
        $this->query .= "SELECT SQL_CALC_FOUND_ROWS
            CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,
            humo_persons2.*, 
            humo_persons1.pers_id, 
            humo_persons1.event_event, 
            humo_persons1.event_kind,
            birth_location.location_location AS pers_birth_place,
            birth.event_date AS pers_birth_date,
            birth.date_year, birth.date_month, birth.date_day,
            bapt_location.location_location AS pers_bapt_place,
            bapt.event_date AS pers_bapt_date,
            bapt.date_year, bapt.date_month, bapt.date_day,
            death_location.location_location AS pers_death_place,
            death.event_date AS pers_death_date,
            death.date_year, death.date_month, death.date_day,
            buried_location.location_location AS pers_buried_place,
            buried.event_date AS pers_buried_date,
            buried.date_year, buried.date_month, buried.date_day
            " . $this->make_date . "
            FROM humo_persons as humo_persons2
            RIGHT JOIN 
            (
                SELECT pers_id, events.event_event, events.event_kind
                FROM humo_persons
                LEFT JOIN humo_events AS events
                    ON events.event_connect_id=pers_gedcomnumber
                    AND events.event_kind='name' AND events.event_tree_id=pers_tree_id
                WHERE (" . $multi_tree . ")
                    AND 
                    ( CONCAT(pers_firstname,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . $safeTextDb->safe_text_db($quicksearch) . "%'
                    OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%" . $safeTextDb->safe_text_db($quicksearch) . "%' 
                    OR CONCAT(pers_patronym,pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%" . $safeTextDb->safe_text_db($quicksearch) . "%' 
                    OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%" . $safeTextDb->safe_text_db($quicksearch) . "%'
                    OR CONCAT(events.event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . $safeTextDb->safe_text_db($quicksearch) . "%'
                    OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),events.event_event) LIKE '%" . $safeTextDb->safe_text_db($quicksearch) . "%' 
                    OR CONCAT(pers_patronym,pers_lastname,events.event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%" . $safeTextDb->safe_text_db($quicksearch) . "%' 
                    OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,events.event_event) LIKE '%" . $safeTextDb->safe_text_db($quicksearch) . "%'
                    OR CONCAT(pers_firstname,events.event_event) LIKE '%" . $safeTextDb->safe_text_db($quicksearch) . "%'
                    )
                GROUP BY pers_id, events.event_event, events.event_kind
            ) as humo_persons1
            ON humo_persons1.pers_id = humo_persons2.pers_id
            LEFT JOIN humo_events AS birth
                ON birth.person_id = humo_persons2.pers_id
                AND birth.event_kind = 'birth'
            LEFT JOIN humo_location AS birth_location
                ON birth.place_id = birth_location.location_id
            LEFT JOIN humo_events AS bapt
                ON bapt.person_id = humo_persons2.pers_id
                AND bapt.event_kind = 'baptism'
            LEFT JOIN humo_location AS bapt_location
                ON bapt.place_id = bapt_location.location_id
            LEFT JOIN humo_events AS death
                ON death.person_id = humo_persons2.pers_id
                AND death.event_kind = 'death'
            LEFT JOIN humo_location AS death_location
                ON death.place_id = death_location.location_id
            LEFT JOIN humo_events AS buried
                ON buried.person_id = humo_persons2.pers_id
                AND buried.event_kind = 'burial'
            LEFT JOIN humo_location AS buried_location
                ON buried.place_id = buried_location.location_id";

        // *** Prevent double results (if there are multiple nick names) ***
        // *** 31-03-2023 BE AWARE: disabled option ONLY_GROUP_BY in header script ***
        $this->query .= " GROUP BY humo_persons1.pers_id";
        // *** Added event_event and event_kind for some PHP/MySQL providers... ***
        // IF USED THERE ARE DOUBLE RESULTS IN SEARCH LIST:
        //$this->query.=" GROUP BY humo_persons1.pers_id, event_event, event_kind";
        $this->query .= " ORDER BY " . $this->orderby;
    }

    public function qry_places()
    {
        // *** EXAMPLE of a UNION querie ***
        //$qry = "(SELECT * FROM humo1_person ".$this->query.') ';
        //$qry.= " UNION (SELECT * FROM humo2_person ".$this->query.')';
        //$qry.= " UNION (SELECT * FROM humo3_person ".$this->query.')';
        //$qry.= " ORDER BY pers_lastname, pers_firstname";

        $buildCondition = new BuildCondition();

        $data = $this->getIndexPlaces();

        $this->query = '';
        $start = false;

        $base_query = '';
        if ($this->user['group_kindindex'] == "j") {
            $base_query .= "CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,";
        }

        // TODO pers_address is added, but doesn't work yet.
        // Maybe just use previous query, and add NULL at some places?
        $base_query = "
            birth_location.location_location as pers_birth_place,
            birth.event_date AS pers_birth_date,
            bapt_location.location_location as pers_bapt_place,
            bapt.event_date AS pers_bapt_date,
            death_location.location_location as pers_death_place,
            death.event_date AS pers_death_date,
            buried_location.location_location as pers_buried_place,
            buried.event_date AS pers_buried_date,
            address_location.location_location AS pers_address
            FROM humo_persons
            LEFT JOIN humo_events AS birth
            ON birth.person_id = humo_persons.pers_id AND birth.event_kind = 'birth'
            LEFT JOIN humo_location AS birth_location
            ON birth.place_id = birth_location.location_id
            LEFT JOIN humo_events AS bapt
            ON bapt.person_id = humo_persons.pers_id AND bapt.event_kind = 'baptism'
            LEFT JOIN humo_location AS bapt_location
            ON bapt.place_id = bapt_location.location_id
            LEFT JOIN humo_events AS death
            ON death.person_id = humo_persons.pers_id AND death.event_kind = 'death'
            LEFT JOIN humo_location AS death_location
            ON death.place_id = death_location.location_id
            LEFT JOIN humo_events AS buried
            ON buried.person_id = humo_persons.pers_id AND buried.event_kind = 'burial'
            LEFT JOIN humo_location AS buried_location
            ON buried.place_id = buried_location.location_id

            LEFT JOIN humo_connections
            ON humo_connections.connect_connect_id = humo_persons.pers_gedcomnumber
            AND humo_connections.connect_tree_id = humo_persons.pers_tree_id
            AND humo_connections.connect_sub_kind = 'person_address'
            LEFT JOIN humo_location AS address_location
            ON humo_connections.connect_item_id = address_location.location_id

            WHERE humo_persons.pers_tree_id='" . $this->tree_id . "'";

        // *** Search birth place ***
        if ($data["select_birth"] == '1') {
            /*
            if ($this->user['group_kindindex'] == "j") {
                $this->query = "(SELECT SQL_CALC_FOUND_ROWS *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_birth_place as place_order
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
            } else {
                $this->query = "(SELECT SQL_CALC_FOUND_ROWS *, pers_birth_place as place_order
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
            }
            */

            $this->query = "(SELECT SQL_CALC_FOUND_ROWS humo_persons.*,";
            //$this->query .= "birth_location.location_location as place_order,
            //    NULL AS pers_address,
            //    NULL AS event_place,";
            $this->query .= "birth_location.location_location as place_order,
                NULL AS event_place,";
            $this->query .= $base_query;

            if ($data["place_name"]) {
                $this->query .= " AND birth_location.location_location " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                $this->query .= " AND birth_location.location_location LIKE '_%'";
            }

            $this->query .= ')';
            $start = true;
        }

        // *** Search baptise place ***
        if ($data["select_bapt"] == '1') {
            if ($start == true) {
                $this->query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }
            /*
            if ($this->user['group_kindindex'] == "j") {
                $this->query .= "(SELECT " . $calc . "*,CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_bapt_place as place_order
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
            } else {
                $this->query .= "(SELECT " . $calc . "*, pers_bapt_place as place_order FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
            }
            */

            $this->query .= "(SELECT " . $calc . "humo_persons.*,";
            //$this->query .= "bapt_location.location_location as place_order,
            //    NULL AS pers_address,
            //    NULL AS event_place,";
            $this->query .= "bapt_location.location_location as place_order,
                NULL AS event_place,";
            $this->query .= $base_query;

            if ($data["place_name"]) {
                $this->query .= " AND bapt_location.location_location " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                $this->query .= " AND bapt_location.location_location LIKE '_%'";
            }
            $this->query .= ')';
            $start = true;
        }


        // TODO: see also code in view file.
        // TODO: doesn't work yet
        // *** Search residence ***
        /*
        if ($data["select_place"] == '1') {
            if ($start == true) {
                $this->query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }

            $this->query .= "(SELECT " . $calc . "humo_persons.*,";
            $this->query .= "address_location.location_location as place_order,
                NULL AS event_place,";
            $this->query .= $base_query;

            if ($data["place_name"]) {
                $this->query .= " AND address_location.location_location " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                $this->query .= " AND address_location.location_location LIKE '_%'";
            }
            $this->query .= ')';
            $start = true;
        }
        */


        // *** Search death place ***
        if ($data["select_death"] == '1') {
            if ($start == true) {
                $this->query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }

            /*
            if ($this->user['group_kindindex'] == "j") {
                $this->query .= "(SELECT " . $calc . "*, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_death_place as place_order
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
            } else {
                $this->query .= "(SELECT " . $calc . "*, pers_death_place as place_order
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
            }
            */

            $this->query .= "(SELECT " . $calc . "humo_persons.*,";
            if ($this->user['group_kindindex'] == "j") {
                $this->query .= "CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,";
            }
            //$this->query .= "
            //    death_location.location_location as place_order,
            //    NULL AS pers_address,
            //    NULL AS event_place,";
            $this->query .= "
                death_location.location_location as place_order,
                NULL AS event_place,";
            $this->query .= $base_query;

            if ($data["place_name"]) {
                $this->query .= " AND death_location.location_location " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                $this->query .= " AND death_location.location_location LIKE '_%'";
            }
            $this->query .= ')';
            $start = true;
        }

        // *** Search buried place ***
        if ($data["select_buried"] == '1') {
            if ($start == true) {
                $this->query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }
            /*
            if ($this->user['group_kindindex'] == "j") {
                $this->query .= "(SELECT " . $calc . "*,CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,pers_buried_place as place_order
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
            } else {
                $this->query .= "(SELECT " . $calc . "*, pers_buried_place as place_order
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
            }
            */

            $this->query .= "(SELECT " . $calc . "humo_persons.*,";
            if ($this->user['group_kindindex'] == "j") {
                $this->query .= "CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,";
            }
            //$this->query .= "buried_location.location_location as place_order,
            //    NULL AS pers_address,
            //    NULL AS event_place,";
            $this->query .= "buried_location.location_location as place_order,
                NULL AS event_place,";
            $this->query .= $base_query;

            if ($data["place_name"]) {
                $this->query .= " AND buried_location.location_location " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                $this->query .= " AND buried_location.location_location LIKE '_%'";
            }
            $this->query .= ')';
            $start = true;
        }



        // TODO nazien
        // *** NEW oct. 2021: Search for place in events like occupation ***
        //if ($data["select_place"] == '1') {
        if ($data["select_place"] == 'UITGESCHAKELD') {
            if ($start == true) {
                $this->query .= ' UNION ';
                $calc = '';
            } else {
                $calc = 'SQL_CALC_FOUND_ROWS ';
            }
            /*
            if ($this->user['group_kindindex'] == "j") {
                $this->query .= "(SELECT " . $calc . "humo_persons.*,
                    CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,
                    humo_location.location_location as place_order,
                    humo_location.location_location as pers_place
                    FROM humo_persons
                    JOIN humo_events
                        ON humo_events.event_connect_id = humo_persons.pers_gedcomnumber
                        AND humo_events.event_tree_id = humo_persons.pers_tree_id
                    LEFT JOIN humo_location
                        ON humo_events.place_id = humo_location.location_id
                    WHERE humo_persons.pers_tree_id='" . $this->tree_id . "'";
            } else {
                $this->query .= "(SELECT " . $calc . "humo_persons.*,
                    humo_events.event_place as place_order
                    FROM humo_persons, humo_events
                    WHERE event_connect_id=pers_gedcomnumber
                    AND event_tree_id=pers_tree_id
                    AND pers_tree_id='" . $this->tree_id . "'";
            }
            */

            $this->query .= "(SELECT " . $calc . "humo_persons.*,";
            if ($this->user['group_kindindex'] == "j") {
                $this->query .= "CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,";
            }
            $this->query .= "
                humo_location.location_location as place_order,
                humo_location.location_location as pers_place,
                birth_location.location_location as pers_birth_place,
                birth.event_date AS pers_birth_date,
                bapt_location.location_location as pers_bapt_place,
                bapt.event_date AS pers_bapt_date,
                death_location.location_location as pers_death_place,
                death.event_date AS pers_death_date,
                buried_location.location_location as pers_buried_place,
                buried.event_date AS pers_buried_date,
                NULL AS event_place
                FROM humo_persons
                JOIN humo_events
                    ON humo_events.event_connect_id = humo_persons.pers_gedcomnumber
                    AND humo_events.event_tree_id = humo_persons.pers_tree_id
                LEFT JOIN humo_location
                    ON humo_events.place_id = humo_location.location_id
                LEFT JOIN humo_events AS birth
                    ON birth.person_id = humo_persons.pers_id AND birth.event_kind = 'birth'
                LEFT JOIN humo_location AS birth_location
                    ON birth.place_id = birth_location.location_id
                LEFT JOIN humo_events AS bapt
                    ON bapt.person_id = humo_persons.pers_id AND bapt.event_kind = 'baptism'
                LEFT JOIN humo_location AS bapt_location
                    ON bapt.place_id = bapt_location.location_id
                LEFT JOIN humo_events AS death
                    ON death.person_id = humo_persons.pers_id AND death.event_kind = 'death'
                LEFT JOIN humo_location AS death_location
                    ON death.place_id = death_location.location_id
                LEFT JOIN humo_events AS buried
                    ON buried.person_id = humo_persons.pers_id AND buried.event_kind = 'burial'
                LEFT JOIN humo_location AS buried_location
                    ON buried.place_id = buried_location.location_id
                WHERE humo_persons.pers_tree_id='" . $this->tree_id . "'";

            if ($data["place_name"]) {
                //$this->query .= " AND event_place " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                //$this->query .= " AND event_place LIKE '_%'";
            }
            $this->query .= ')';
            $start = true;
        }


        //echo $this->query . '<br>';


        // TODO TEST without UNION. Probably not possible without changing the person place page.
        // One large query? Use SELECT for birth_place, etc? How to use place order?
        /*
        $this->query = "SELECT SQL_CALC_FOUND_ROWS humo_persons.*,
            birth_location.location_location as place_order,
            birth_location.location_location as pers_birth_place, 
            birth.event_date AS pers_birth_date,
            bapt.event_date AS pers_bapt_date,
            bapt_location.location_location as pers_bapt_place,
            death.event_date AS pers_death_date,
            death_location.location_location as pers_death_place,
            buried.event_date AS pers_buried_date,
            buried_location.location_location as pers_buried_place,

            events.event_date AS event_date,
            events_location.location_location AS event_place

            FROM humo_persons

            LEFT JOIN humo_events AS birth
            ON birth.person_id = humo_persons.pers_id AND birth.event_kind = 'birth'
            LEFT JOIN humo_location AS birth_location
            ON birth.place_id = birth_location.location_id
            
            LEFT JOIN humo_events AS bapt
            ON bapt.person_id = humo_persons.pers_id AND bapt.event_kind = 'baptism'
            LEFT JOIN humo_location AS bapt_location
            ON bapt.place_id = bapt_location.location_id

            LEFT JOIN humo_events AS death
            ON death.person_id = humo_persons.pers_id AND death.event_kind = 'death'
            LEFT JOIN humo_location AS death_location
            ON death.place_id = death_location.location_id

            LEFT JOIN humo_events AS buried
            ON buried.person_id = humo_persons.pers_id AND buried.event_kind = 'burial'
            LEFT JOIN humo_location AS buried_location
            ON buried.place_id = buried_location.location_id

            LEFT JOIN humo_events AS events
            ON events.person_id = humo_persons.pers_id
            LEFT JOIN humo_location AS events_location
            ON events.place_id = events_location.location_id

            WHERE humo_persons.pers_tree_id='" . $this->tree_id . "'";

            if ($data["place_name"]) {
                //$this->query .= " AND event_place " . $buildCondition->build($data["place_name"], $data["part_place_name"]);
            } else {
                //$this->query .= " AND event_place LIKE '_%'";
            }

        */


        // *** Order by place and name: "Mons, van" or: "van Mons" ***
        if ($this->user['group_kindindex'] == "j") {
            $this->query .= ' ORDER BY place_order, concat_name';
        } else {
            $this->query .= ' ORDER BY place_order, pers_lastname, pers_firstname';
        }


        //echo $this->query.'<br>';

    }

    public function qry_patronym()
    {
        // *** Only in pers_patronym index if there is no pers_lastname! ***
        //$this->query = "SELECT SQL_CALC_FOUND_ROWS * " . $this->make_date . " FROM humo_persons
        //    WHERE pers_tree_id='" . $this->tree_id . "' AND pers_patronym LIKE '_%' AND pers_lastname='' ORDER BY " . $this->orderby;
        $this->query = "SELECT SQL_CALC_FOUND_ROWS humo_persons.*, 
            birth.event_date AS pers_birth_date,
            birth.date_year, birth.date_month, birth.date_day,
            birth_location.location_location AS pers_birth_place,
            bapt.event_date AS pers_bapt_date,
            bapt.date_year, bapt.date_month, bapt.date_day,
            bapt_location.location_location AS pers_bapt_place,
            death.event_date AS pers_death_date,
            death.date_year, death.date_month, death.date_day,
            death_location.location_location AS pers_death_place,
            buried.event_date AS pers_buried_date,
            buried.date_year, buried.date_month, buried.date_day,
            buried_location.location_location AS pers_buried_place
            " . $this->make_date . "
            FROM humo_persons
            LEFT JOIN humo_events AS birth ON birth.person_id = humo_persons.pers_id AND birth.event_kind = 'birth'
            LEFT JOIN humo_location AS birth_location ON birth.place_id = birth_location.location_id
            LEFT JOIN humo_events AS bapt ON bapt.person_id = humo_persons.pers_id AND bapt.event_kind = 'baptism'
            LEFT JOIN humo_location AS bapt_location ON bapt.place_id = bapt_location.location_id
            LEFT JOIN humo_events AS death ON death.person_id = humo_persons.pers_id AND death.event_kind = 'death'
            LEFT JOIN humo_location AS death_location ON death.place_id = death_location.location_id
            LEFT JOIN humo_events AS buried ON buried.person_id = humo_persons.pers_id AND buried.event_kind = 'burial'
            LEFT JOIN humo_location AS buried_location ON buried.place_id = buried_location.location_id
            WHERE humo_persons.pers_tree_id='" . $this->tree_id . "' 
            AND humo_persons.pers_patronym LIKE '_%' 
            AND humo_persons.pers_lastname='' 
            ORDER BY " . $this->orderby;
    }

    // *** Check if there is a query, otherwise generate standard query ***
    public function qry_standard()
    {
        // *** Standard index ***
        if ($this->query == '' or $this->index_list == 'standard') {
            //$this->query = "SELECT * " . $this->make_date . " FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' ORDER BY " . $this->orderby;
            $this->query = "SELECT humo_persons.*, 
                birth.event_date AS pers_birth_date,
                birth.date_year, birth.date_month, birth.date_day,
                birth_location.location_location AS pers_birth_place,
                bapt.event_date AS pers_bapt_date,
                bapt.date_year, bapt.date_month, bapt.date_day,
                bapt_location.location_location AS pers_bapt_place,
                death.event_date AS pers_death_date,
                death.date_year, death.date_month, death.date_day,
                death_location.location_location AS pers_death_place,
                buried.event_date AS pers_buried_date,
                buried.date_year, buried.date_month, buried.date_day,
                buried_location.location_location AS pers_buried_place,
                buried.date_year, buried.date_month, buried.date_day
                " . $this->make_date . "
                FROM humo_persons
                LEFT JOIN humo_events AS birth
                    ON birth.person_id = humo_persons.pers_id
                    AND birth.event_kind = 'birth'
                LEFT JOIN humo_location AS birth_location
                    ON birth.place_id = birth_location.location_id
                LEFT JOIN humo_events AS bapt
                    ON bapt.person_id = humo_persons.pers_id
                    AND bapt.event_kind = 'baptism'
                LEFT JOIN humo_location AS bapt_location
                    ON bapt.place_id = bapt_location.location_id
                LEFT JOIN humo_events AS death
                    ON death.person_id = humo_persons.pers_id
                    AND death.event_kind = 'death'
                LEFT JOIN humo_location AS death_location
                    ON death.place_id = death_location.location_id
                LEFT JOIN humo_events AS buried
                    ON buried.person_id = humo_persons.pers_id
                    AND buried.event_kind = 'burial'
                LEFT JOIN humo_location AS buried_location
                    ON buried.place_id = buried_location.location_id
                WHERE humo_persons.pers_tree_id='" . $this->tree_id . "' 
                GROUP BY humo_persons.pers_id
                ORDER BY " . $this->orderby;

            // Mons, van or: van Mons
            if ($this->user['group_kindindex'] == "j") {
                $this->query = "SELECT *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name " . $this->make_date . "
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' ORDER BY " . $this->orderby;
            }

            //$this->count_query = "SELECT COUNT(*) as teller ".$this->make_date." FROM humo_persons WHERE pers_tree_id='".$this->tree_id."'";
            // *** 31-03-2023 GROUP BY option is needed for COUNT: added GROUP BY and removed $this->make_date (not necessary) ***
            $this->count_query = "SELECT COUNT(pers_tree_id) as teller, pers_tree_id FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_tree_id";
        }
    }


    public function build_query()
    {
        // *** DEBUG/ TEST: SHOW QUERY ***
        //echo $this->query.'<br>';

        //*** Show number of persons and pages ***
        $item = 0;
        if (isset($_GET['item']) && is_numeric($_GET['item'])) {
            $item = $_GET['item'];
        }
        $start = 0;
        if (isset($_GET["start"]) && is_numeric($_GET["start"])) {
            $start = $_GET["start"];
        }
        $nr_persons = $this->humo_option['show_persons'];

        if (!$this->selection['spouse_firstname'] && !$this->selection['spouse_lastname'] && $this->selection['parent_status'] != "motheronly" && $this->selection['parent_status'] != "fatheronly") {

            //TEST
            //echo nl2br($this->query) . " LIMIT " . $item . "," . $nr_persons . '!!!!<br>';

            $person_result = $this->dbh->query($this->query . " LIMIT " . $item . "," . $nr_persons);

            //TEST 
            //$person_result->closeCursor();

            if ($this->count_query) {
                // *** Use MySQL COUNT command to calculate nr. of persons in simple queries (faster than php num_rows and in simple queries faster than SQL_CAL_FOUND_ROWS) ***
                $result = $this->dbh->query($this->count_query);
                $resultDb = $result->fetch(PDO::FETCH_OBJ);
                if ($resultDb) {
                    $count_persons = $resultDb->teller;
                }
            } else {
                // *** USE SQL_CALC_FOUND_ROWS for complex queries (faster than mysql count) ***
                $result = $this->dbh->query("SELECT FOUND_ROWS() AS 'found_rows'");
                $rows = $result->fetch();
                $count_persons = $rows['found_rows'];
            }
        } else {

            //TEST
            //echo nl2br($this->query) . "!!!???<br>";

            $person_result = $this->dbh->query($this->query);
            $count_persons = 0; // Isn't used if search is done for spouse or for people with only known mother or only known father...
        }

        $data["person_result"] = $person_result;
        $data["start"] = $start;
        $data["nr_persons"] = $nr_persons;
        $data["count_persons"] = $count_persons;
        $data["item"] = $item;
        return $data;
    }


    // *** NOT IN USE YET ***
    // $query_select .= $this->getEventJoins('humo_persons');
    /*
    private function getEventJoins($personAlias = 'humo_persons')
    {
        return "
        LEFT JOIN humo_events AS birth
            ON birth.person_id = {$personAlias}.pers_id AND birth.event_kind = 'birth'
        LEFT JOIN humo_location AS birth_location
            ON birth.place_id = birth_location.location_id

        LEFT JOIN humo_events AS bapt
            ON bapt.person_id = {$personAlias}.pers_id AND bapt.event_kind = 'baptism'
        LEFT JOIN humo_location AS bapt_location
            ON bapt.place_id = bapt_location.location_id

        LEFT JOIN humo_events AS death
            ON death.person_id = {$personAlias}.pers_id AND death.event_kind = 'death'
        LEFT JOIN humo_location AS death_location
            ON death.place_id = death_location.location_id

        LEFT JOIN humo_events AS buried
            ON buried.person_id = {$personAlias}.pers_id AND buried.event_kind = 'burial'
        LEFT JOIN humo_location AS buried_location
            ON buried.place_id = buried_location.location_id
    ";
    }
    */
}

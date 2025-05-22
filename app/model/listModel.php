<?php
class ListModel extends BaseModel
{
    public function getIndexList(): string
    {
        $index_list = 'quicksearch';
        // *** Reset if necessary ***
        if (isset($_POST['pers_firstname']) || isset($_GET['pers_lastname']) || isset($_GET['pers_firstname']) || isset($_GET['reset']) || isset($_POST['quicksearch'])) {
            $index_list = 'search';
        }
        if (isset($_POST["index_list"])) {
            $index_list = $_POST['index_list'];
        }
        if (isset($_GET["index_list"])) {
            $index_list = $_GET['index_list'];
        }
        return $index_list;
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
            //if($_GET['sort']=="sort_baptdate") { $selectsort="sort_baptdate"; $_SESSION['sort']=$selectsort; }
            if ($_GET['sort'] == "sort_deathdate") {
                $selectsort = "sort_deathdate";
                $_SESSION['sort'] = $selectsort;
            }
            if ($_GET['sort'] == "sort_deathplace") {
                $selectsort = "sort_deathplace";
                $_SESSION['sort'] = $selectsort;
            }
            //if($_GET['sort']=="sort_burieddate") { $selectsort="sort_burieddate"; $_SESSION['sort']=$selectsort; }
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

        $selection['pers_firstname'] = '';
        if (isset($_POST['pers_firstname'])) {
            $selection['pers_firstname'] = $_POST['pers_firstname'];
            //$selection['pers_firstname']=htmlentities($_POST['pers_firstname'],ENT_QUOTES,'UTF-8');
            $change = true;
        }
        // *** Used for frequent firstnames in statistics page ***
        if (isset($_GET['pers_firstname'])) {
            $selection['pers_firstname'] = $_GET['pers_firstname'];
            $_GET['adv_search'] = '1';
            $change = true;
        }

        $selection['part_firstname'] = '';
        if (isset($_POST['part_firstname'])) {
            $selection['part_firstname'] = $_POST['part_firstname'];
            $change = true;
        }
        if (isset($_GET['part_firstname'])) {
            $selection['part_firstname'] = $_GET['part_firstname'];
            $change = true;
        }

        // *** Prefix (names list and most frequent names in main menu.) ***
        $selection['pers_prefix'] = '';
        if (isset($_POST['pers_prefix'])) {
            $selection['pers_prefix'] = $_POST['pers_prefix'];
            $change = true;
        }
        if (isset($_GET['pers_prefix'])) {
            $selection['pers_prefix'] = $_GET['pers_prefix'];
            //$selection['pers_prefix']=htmlentities($_GET['pers_prefix'],ENT_QUOTES,'UTF-8');
            $change = true;
        }

        // *** Enable / disable pers_prefix search. Only use option if advanced search page is started/ used ***
        $selection['use_pers_prefix'] = 'USED';
        if (isset($_POST['part_lastname']) && !isset($_POST['use_pers_prefix'])) {
            $selection['pers_prefix'] = 'EMPTY';
            $selection['use_pers_prefix'] = 'EMPTY';
        }
        // *** Page is called from menu bar or direct link from main menu. Option should be enabled then ***
        if (isset($_GET['adv_search']) && $_GET['adv_search'] == '1') {
            $selection['use_pers_prefix'] = 'USED';
        }
        // *** Page is called from names list. Option should be disabled then ***
        if (isset($_GET['pers_prefix']) && $_GET['pers_prefix'] == 'EMPTY') {
            $selection['pers_prefix'] = 'EMPTY';
            $selection['use_pers_prefix'] = 'EMPTY';
        }

        // *** Lastname ***
        $selection['pers_lastname'] = '';
        if (isset($_POST['pers_lastname'])) {
            $selection['pers_lastname'] = $_POST['pers_lastname'];
            //$selection['pers_lastname']=htmlentities($_POST['pers_lastname'],ENT_QUOTES,'UTF-8');
            $change = true;
        }
        if ((isset($this->humo_option['one_name_study']) and $this->humo_option['one_name_study'] == 'y') && (isset($_GET['adv_search']) && $_GET['adv_search'] == 1 || isset($_GET['index_list']) && $_GET['index_list'] == 'search' || isset($_GET['reset']) && $_GET['reset'] == 1)) {
            $selection['pers_lastname'] = $this->humo_option['one_name_thename'];
            $change = true;
        }
        if (isset($_GET["pers_lastname"])) {
            $selection['pers_lastname'] = $_GET['pers_lastname'];
            //$selection['pers_lastname']=htmlentities($_GET['pers_lastname'],ENT_QUOTES,'UTF-8');
            $selection['pers_lastname'] = str_replace("|", "&", $selection['pers_lastname']);  // Don't use a & character in a GET link
            $change = true;
        }

        $selection['part_lastname'] = '';
        if (isset($_POST['part_lastname'])) {
            $selection['part_lastname'] = $_POST['part_lastname'];
            $change = true;
        }
        // *** Used for clicking in the names list ***
        if (isset($_GET['part_lastname'])) {
            $selection['part_lastname'] = $_GET['part_lastname'];
            $change = true;
        }

        // ***  ADVANCED SEARCH added by Yossi Beck, translated and integrated in person search screen by Huub. *** //
        $selection['birth_place'] = '';
        if (isset($_POST['birth_place'])) {
            $selection['birth_place'] = $_POST['birth_place'];
            $change = true;
        }
        $selection['part_birth_place'] = '';
        if (isset($_POST['part_birth_place'])) {
            $selection['part_birth_place'] = $_POST['part_birth_place'];
            $change = true;
        }

        $selection['death_place'] = '';
        if (isset($_POST['death_place'])) {
            $selection['death_place'] = $_POST['death_place'];
            $change = true;
        }
        $selection['part_death_place'] = '';
        if (isset($_POST['part_death_place'])) {
            $selection['part_death_place'] = $_POST['part_death_place'];
            $change = true;
        }

        $selection['birth_year'] = '';
        if (isset($_POST['birth_year'])) {
            $selection['birth_year'] = $_POST['birth_year'];
            $change = true;
        }
        $selection['birth_year_end'] = '';
        if (isset($_POST['birth_year_end'])) {
            $selection['birth_year_end'] = $_POST['birth_year_end'];
            $change = true;
        }

        $selection['death_year'] = '';
        if (isset($_POST['death_year'])) {
            $selection['death_year'] = $_POST['death_year'];
            $change = true;
        }
        $selection['death_year_end'] = '';
        if (isset($_POST['death_year_end'])) {
            $selection['death_year_end'] = $_POST['death_year_end'];
            $change = true;
        }

        $selection['spouse_firstname'] = '';
        if (isset($_POST['spouse_firstname'])) {
            $selection['spouse_firstname'] = $_POST['spouse_firstname'];
            //$selection['spouse_firstname']=htmlentities($_POST['spouse_firstname'],ENT_QUOTES,'UTF-8');
            $change = true;
        }
        $selection['part_spouse_firstname'] = '';
        if (isset($_POST['part_spouse_firstname'])) {
            $selection['part_spouse_firstname'] = $_POST['part_spouse_firstname'];
            $change = true;
        }

        $selection['spouse_lastname'] = '';
        if (isset($_POST['spouse_lastname'])) {
            $selection['spouse_lastname'] = $_POST['spouse_lastname'];
            //$selection['spouse_lastname']=htmlentities($_POST['spouse_lastname'],ENT_QUOTES,'UTF-8');
            $change = true;
        }
        $selection['part_spouse_lastname'] = '';
        if (isset($_POST['part_spouse_lastname'])) {
            $selection['part_spouse_lastname'] = $_POST['part_spouse_lastname'];
            $change = true;
        }

        $selection['sexe'] = '';
        if (isset($_POST['sexe'])) {
            $selection['sexe'] = $_POST['sexe'];
            $change = true;
        } elseif (isset($_GET['sexe'])) {
            $selection['sexe'] = $_GET['sexe'];
            $change = true;
        }

        // *** Own Code ***
        $selection['own_code'] = '';
        if (isset($_POST['own_code'])) {
            $selection['own_code'] = $_POST['own_code'];
            $change = true;
        }
        $selection['part_own_code'] = '';
        if (isset($_POST['part_own_code'])) {
            $selection['part_own_code'] = $_POST['part_own_code'];
            $change = true;
        }

        // *** Gedcomnumber ***
        $selection['gednr'] = '';
        if (isset($_POST['gednr'])) {
            $selection['gednr'] = $_POST['gednr'];
            $change = true;
        }
        $selection['part_gednr'] = '';
        if (isset($_POST['part_gednr'])) {
            $selection['part_gednr'] = $_POST['part_gednr'];
            $change = true;
        }

        // *** Profession ***
        $selection['pers_profession'] = '';
        if (isset($_POST['pers_profession'])) {
            $selection['pers_profession'] = $_POST['pers_profession'];
            $change = true;
        }
        $selection['part_profession'] = '';
        if (isset($_POST['part_profession'])) {
            $selection['part_profession'] = $_POST['part_profession'];
            $change = true;
        }

        // *** Text ***
        $selection['text'] = '';
        if (isset($_POST['text'])) {
            $selection['text'] = $_POST['text'];
            $change = true;
        }
        $selection['part_text'] = '';
        if (isset($_POST['part_text'])) {
            $selection['part_text'] = $_POST['part_text'];
            $change = true;
        }

        // *** Place ***
        $selection['pers_place'] = '';
        if (isset($_POST['pers_place'])) {
            $selection['pers_place'] = $_POST['pers_place'];
            $change = true;
        }
        $selection['part_place'] = '';
        if (isset($_POST['part_place'])) {
            $selection['part_place'] = $_POST['part_place'];
            $change = true;
        }

        // *** Zip code ***
        $selection['zip_code'] = '';
        if (isset($_POST['zip_code'])) {
            $selection['zip_code'] = $_POST['zip_code'];
            $change = true;
        }
        $selection['part_zip_code'] = '';
        if (isset($_POST['part_zip_code'])) {
            $selection['part_zip_code'] = $_POST['part_zip_code'];
            $change = true;
        }

        // *** Research status ***
        $selection['parent_status'] = '';
        if (!isset($_POST['quicksearch']) && isset($_POST['parent_status'])) {
            $selection['parent_status'] = $_POST['parent_status'];
            $change = true;
        }

        // *** Witness ***
        $selection['witness'] = '';
        if (isset($_POST['witness'])) {
            $selection['witness'] = $_POST['witness'];
            $change = true;
        }
        $selection['part_witness'] = '';
        if (isset($_POST['part_witness'])) {
            $selection['part_witness'] = $_POST['part_witness'];
            $change = true;
        }

        // *** Store selection if an item is changed ***
        if ($change == true) {
            $_SESSION["save_selection"] = $selection;
        }

        // *** Read session for multiple pages ***
        // *** Multiple search values ***
        if (isset($_GET['item']) && isset($_SESSION["save_selection"])) {
            $selection = $_SESSION["save_selection"];
        }

        return $selection;
    }

    public function getQueryOrderBy($index_list, $desc_asc, $order_select): array
    {
        // *** SOME DEFAULTS ***
        $last_or_patronym = " pers_lastname ";
        if ($index_list == 'patronym') {
            $last_or_patronym = " pers_patronym ";
        }

        //REMARK: at this moment also used to select birth/baptise or death/buried place...
        $make_date = ''; // we only need this when sorting by date

        $orderby = $last_or_patronym . $desc_asc . ", pers_firstname " . $desc_asc;
        if ($this->user['group_kindindex'] == "j" && $index_list != 'patronym') {
            $orderby = " concat_name " . $desc_asc;
        }

        $selectsort = $order_select;
        if ($selectsort) {
            if ($selectsort == "sort_lastname") {
                $orderby = $last_or_patronym . $desc_asc . ", pers_firstname " . $desc_asc;
                if ($this->user['group_kindindex'] == "j" && $index_list != 'patronym') {
                    $orderby = " concat_name " . $desc_asc;
                }
            }
            if ($selectsort == "sort_firstname") {
                $orderby = " pers_firstname " . $desc_asc . "," . $last_or_patronym . $desc_asc;
            }

            if ($selectsort == "sort_birthdate") {
                // *** Replace ABT, AFT, BEF, EST, CAL and BET...AND items and sort by birth or baptise date ***
                $make_date = ", CASE
                    WHEN pers_birth_date = '' AND SUBSTR(CONCAT(' ',pers_bapt_date),-4,1)= ' ' THEN 
                    replace(
                        replace(
                            replace(
                                replace(
                                    replace(
                                        replace(
                                            UPPER(
                                                CONVERT(
                                                    CONCAT(
                                                        SUBSTR(pers_bapt_date,1,LENGTH(pers_bapt_date)-3),'0',SUBSTR(pers_bapt_date,-3)
                                                    ) USING latin1
                                                )
                                            ),
                                        'ABT ',''),
                                    'AFT ',''),
                                'BEF ',''),
                            'EST ',''),
                        'CAL ',''),
                    'AND ','       ')
                    WHEN pers_birth_date = '' AND SUBSTR(CONCAT(' ',pers_bapt_date),-4,1)!= ' ' THEN
                        replace(
                            replace(
                                replace(
                                    replace(
                                        replace(
                                            replace(
                                                UPPER(
                                                    CONVERT(pers_bapt_date USING latin1)
                                                ),
                                            'ABT ',''),
                                        'AFT ',''),
                                    'BEF ',''),
                                'EST ',''),
                            'CAL ',''),
                        'AND ','       ')
                    WHEN pers_birth_date != '' AND SUBSTR(CONCAT(' ',pers_birth_date),-4,1)= ' ' THEN
                        replace(
                            replace(
                                replace(
                                    replace(
                                        replace(
                                            replace(
                                                UPPER(
                                                    CONVERT(
                                                        CONCAT(SUBSTR(pers_birth_date,1,LENGTH(pers_birth_date)-3),'0',SUBSTR(pers_birth_date,-3))
                                                    USING latin1)
                                                ),
                                            'ABT ',''),
                                        'AFT ',''),
                                    'BEF ',''),
                                'EST ',''),
                            'CAL ',''),
                        'AND ','       ')
                    WHEN pers_birth_date != '' AND SUBSTR(CONCAT(' ',pers_birth_date),-4,1)!= ' ' THEN
                        replace(
                            replace(
                                replace(
                                    replace(
                                        replace(
                                            replace(
                                                UPPER(
                                                    CONVERT(pers_birth_date USING latin1)
                                                ),
                                            'ABT ',''),
                                        'AFT ',''),
                                    'BEF ',''),
                                'EST ',''),
                            'CAL ',''),
                        'AND ','       ')
                        END AS order_date";

                // DOESN'T WORK:
                // Use a sort of ucfirst by month? Should be: Jan, Feb, etc.
                // Something like: LOWER(SUBSTRING(name,2)))
                $orderby = " CONCAT( substring(order_date,-4),
                    date_format( str_to_date( substring(order_date,-8,3),'%b' ) ,'%m'),
                    date_format( str_to_date( substring(order_date,-11,2),'%d' ),'%d')
                    ) " . $desc_asc . ", " . $last_or_patronym . " ASC , pers_firstname ASC";
                // DOESN'T WORK AT WEBSITE:
                //$orderby = " CONCAT( substring(order_date,-4),
                //  date_format( str_to_date(
                //      LOWER(SUBSTRING(
                //          substring(order_date,-8,3)
                //      ,2))
                //  ,'%b' ) ,'%m'),
                //  date_format( str_to_date( substring(order_date,-11,2),'%d' ),'%d')
                //  ) ".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";
            }
            if ($selectsort == "sort_birthplace") {
                //$orderby = " pers_birth_place ".$desc_asc.",".$last_or_patronym.$desc_asc;
                $make_date = ", CASE
                    WHEN pers_birth_place = '' THEN pers_bapt_place ELSE pers_birth_place
                    END AS place";
                $orderby = " place" . $desc_asc . ", " . $last_or_patronym . $desc_asc;
            }

            if ($selectsort == "sort_deathdate") {
                //$make_date = ", right(pers_death_date,4) as year,
                //date_format( str_to_date( substring(pers_death_date,-8,3),'%b' ),'%m') as month,
                //date_format( str_to_date( left(pers_death_date,2),'%d' ),'%d') as day";
                //$orderby = " year".$desc_asc.", month".$desc_asc.", day".$desc_asc.", ".$last_or_patronym." ASC , pers_firstname ASC";

                // *** Replace ABT, AFT, BEF items and sort by death or buried date ***
                $make_date = ", CASE
                    WHEN pers_death_date = '' AND SUBSTR(CONCAT(' ',pers_buried_date),-4,1)= ' ' THEN replace(
                        replace(
                            replace(
                                replace(
                                    replace(
                                        UPPER(
                                            CONVERT(
                                                CONCAT(
                                                    SUBSTR(pers_buried_date,1,LENGTH(pers_buried_date)-3),'0',SUBSTR(pers_buried_date,-3)) USING latin1)
                                        ),'ABT ',''
                                    ),'AFT ',''
                                ),'BEF ',''
                            ),'EST ',''
                        ),'AND ','       '
                    )
                    WHEN pers_death_date = '' AND SUBSTR(CONCAT(' ',pers_buried_date),-4,1)!= ' ' THEN replace(
                        replace(
                            replace(
                                replace(
                                    replace(
                                        UPPER(
                                            CONVERT(pers_buried_date USING latin1)
                                        ),'ABT ',''
                                    ),'AFT ',''
                                ),'BEF ',''
                            ),'EST ',''
                        ),'AND ','       '
                    )
                    WHEN pers_death_date != '' AND SUBSTR(CONCAT(' ',pers_death_date),-4,1)= ' ' THEN replace(
                        replace(
                            replace(
                                replace(
                                    replace(
                                        UPPER(
                                            CONVERT(CONCAT(SUBSTR(pers_death_date,1,LENGTH(pers_death_date)-3),'0',SUBSTR(pers_death_date,-3)) USING latin1)
                                        ),'ABT ',''
                                    ),'AFT ',''
                                ),'BEF ',''
                            ),'EST ',''
                        ),'AND ','       '
                    )
                    WHEN pers_death_date != '' AND SUBSTR(CONCAT(' ',pers_death_date),-4,1)!= ' ' THEN 	replace(
                        replace(
                            replace(
                                replace(
                                    replace(
                                        UPPER(
                                            CONVERT(pers_death_date USING latin1)),'ABT ',''),'AFT ',''
                                ),'BEF ',''
                            ),'EST ',''
                        ),'AND ','       '
                    )
                    END AS order_date";

                $orderby = " CONCAT( right(order_date,4),
                    date_format( str_to_date( substring(order_date,-8,3),'%b' ) ,'%m'),
                    date_format( str_to_date( substring(order_date,-11,2),'%d' ),'%d')
                    )" . $desc_asc . ", " . $last_or_patronym . " ASC , pers_firstname ASC";
            }
            if ($selectsort == "sort_deathplace") {
                $make_date = ", CASE
                    WHEN pers_death_place = '' THEN pers_buried_place ELSE pers_death_place
                    END AS place";
                $orderby = " place" . $desc_asc . ", " . $last_or_patronym . $desc_asc;
            }
        }

        $data["orderby"] = $orderby;
        $data["make_date"] = $make_date;
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

    public function getAdvSearch($selection): bool
    {
        $adv_search = false;
        // *** Link from "names" list, automatically use advanced search ***
        if (isset($_GET['part_lastname'])) {
            $_GET['adv_search'] = '1';
            $_SESSION["save_selection"] = $selection;
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

    public function getIndexPlaces($index_list): array
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
        if ($index_list == 'places') {
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


    // *** Search for (part of) first or lastname ***
    private function name_qry($search_name, $search_part): string
    {
        $text = "LIKE '%" . safe_text_db($search_name) . "%'"; // *** Default value: "contains" ***
        if ($search_part == 'equals') {
            $text = "='" . safe_text_db($search_name) . "'";
        }
        if ($search_part == 'starts_with') {
            $text = "LIKE '" . safe_text_db($search_name) . "%'";
        }
        return $text;
    }

    public function build_query(): array
    {
        // *******************
        // *** BUILD QUERY ***
        // *******************

        $query = '';
        $count_qry = '';

        $selection = $this->getSelection();
        $select_trees = $this->getSelectTrees($this->humo_option);
        $index_list = $this->getIndexList();
        $order = $this->getOrder();
        $desc_asc = $this->getDescAsc($order);
        $order_select = $this->getOrderSelect();

        $get_orderby = $this->getQueryOrderBy($index_list, $desc_asc, $order_select);
        $orderby = $get_orderby["orderby"];
        $make_date = $get_orderby["make_date"];
        $quicksearch = $this->getQuickSearch();
        $data = $this->getIndexPlaces($index_list);

        //*** Results of searchform in mainmenu ***
        //*** Or: search in lastnames ***
        if (
            $selection['pers_firstname'] || $selection['pers_prefix'] || $selection['pers_lastname'] || $selection['birth_place'] || $selection['death_place'] || $selection['birth_year'] || $selection['death_year'] || $selection['sexe'] && $selection['sexe'] != 'both' || $selection['own_code'] || $selection['gednr'] || $selection['pers_profession'] || $selection['pers_place'] || $selection['text'] || $selection['zip_code'] || $selection['witness'] || $selection['parent_status'] != ""
        ) {

            // *** Build query ***
            //$and=" ";
            $and = " AND ";

            $add_address_qry = false;
            $add_event_qry = false;
            $add_text_qry = false;

            if ($selection['pers_lastname']) {
                if ($selection['pers_lastname'] == __('...')) {
                    $query .= $and . " pers_lastname=''";
                    $and = " AND ";
                } elseif ($this->user['group_kindindex'] == "j") {
                    $query .= $and . " CONCAT( REPLACE(pers_prefix,'_',' ') ,pers_lastname) " .
                        $this->name_qry($selection['pers_lastname'], $selection['part_lastname']);
                    $and = " AND ";
                } else {
                    $query .= $and . " pers_lastname " . $this->name_qry($selection['pers_lastname'], $selection['part_lastname']);
                    $and = " AND ";
                }
            }
            // *** Namelist: search persons without pers_prefix ***
            if ($selection['pers_prefix'] == 'EMPTY') {
                $query .= $and . "pers_prefix=''";
                $and = " AND ";
            } elseif ($selection['pers_prefix']) {
                // *** Search results for: "van", "van " and "van_" ***
                $pers_prefix = safe_text_db(str_replace(' ', '_', $selection['pers_prefix']));
                $query .= $and . "(pers_prefix='" . $pers_prefix . "' OR pers_prefix ='" . $pers_prefix . '_' . "')";
                $and = " AND ";
            }

            if ($selection['pers_firstname']) {
                $query .= $and . "(pers_firstname " . $this->name_qry($selection['pers_firstname'], $selection['part_firstname']);
                $query .= " OR (event_kind='name' AND event_event " . $this->name_qry($selection['pers_firstname'], $selection['part_firstname']) . ') )';

                $and = " AND ";
                $add_event_qry = true;
            }

            // *** Search for born AND baptised place ***
            if ($selection['birth_place']) {
                $query .= $and . "(pers_birth_place " . $this->name_qry($selection['birth_place'], $selection['part_birth_place']);
                $and = " AND ";
                $query .= " OR pers_bapt_place " . $this->name_qry($selection['birth_place'], $selection['part_birth_place']) . ')';
                $and = " AND ";
            }

            // *** Search for death AND buried place ***
            if ($selection['death_place']) {
                $query .= $and . "(pers_death_place " . $this->name_qry($selection['death_place'], $selection['part_death_place']);
                $and = " AND ";
                $query .= " OR pers_buried_place " . $this->name_qry($selection['death_place'], $selection['part_death_place']) . ')';
                $and = " AND ";
            }

            if ($selection['birth_year']) {
                if (!$selection['birth_year_end']) {   // filled in one year: exact date
                    // *** Also search for baptise ***
                    $query .= $and . "(pers_birth_date LIKE '%" . safe_text_db($selection['birth_year']) . "%'";
                    $and = " AND ";
                    $query .= " OR pers_bapt_date LIKE '%" . safe_text_db($selection['birth_year']) . "%')";
                    $and = " AND ";
                } else {
                    // *** Also search for baptise ***
                    $query .= $and . "(RIGHT(pers_birth_date, 4)>='" . safe_text_db($selection['birth_year']) . "' AND RIGHT(pers_birth_date, 4)<='" . safe_text_db($selection['birth_year_end']) . "'";
                    $and = " AND ";
                    $query .= " OR RIGHT(pers_bapt_date, 4)>='" . safe_text_db($selection['birth_year']) . "' AND RIGHT(pers_bapt_date, 4)<='" . safe_text_db($selection['birth_year_end']) . "')";
                    $and = " AND ";
                }
            }

            if ($selection['death_year']) {
                if (!$selection['death_year_end']) {      // filled in one year: exact date
                    // ** Also search for buried date ***
                    $query .= $and . "(pers_death_date LIKE '%" . safe_text_db($selection['death_year']) . "%'";
                    $and = " AND ";
                    $query .= "OR pers_buried_date LIKE '%" . safe_text_db($selection['death_year']) . "%')";
                    $and = " AND ";
                } else {
                    // ** Also search for buried date ***
                    $query .= $and . "(RIGHT(pers_death_date, 4)>='" . safe_text_db($selection['death_year']) . "' AND RIGHT(pers_death_date, 4)<='" . safe_text_db($selection['death_year_end']) . "'";
                    $and = " AND ";
                    $query .= " OR RIGHT(pers_buried_date, 4)>='" . safe_text_db($selection['death_year']) . "' AND RIGHT(pers_buried_date, 4)<='" . safe_text_db($selection['death_year_end']) . "')";
                    $and = " AND ";
                }
            }

            if ($selection['sexe'] == "M" || $selection['sexe'] == "F") {
                $query .= $and . "pers_sexe='" . $selection['sexe'] . "'";
                $and = " AND ";
            }
            if ($selection['sexe'] == "Unknown") {
                $query .= $and . "(pers_sexe!='M' AND pers_sexe!='F')";
                $and = " AND ";
            }

            if ($selection['own_code']) {
                $query .= $and . "pers_own_code " . $this->name_qry($selection['own_code'], $selection['part_own_code']);
                $and = " AND ";
            }

            if ($selection['gednr']) {
                if (strtoupper(substr($_POST['gednr'], 0, 1)) !== 'I') {
                    $selection['gednr'] = 'I' . $_POST['gednr']; // if only number was entered - add "I" before
                } else {
                    $selection['gednr'] = strtoupper($_POST['gednr']); // in case lowercase "i" was entered before number, make it "I"
                }
                $query .= $and . "pers_gedcomnumber " . $this->name_qry($selection['gednr'], $selection['part_gednr']);
                $and = " AND ";
            }

            if ($selection['pers_profession']) {
                $query .= $and . " (event_kind='profession' AND event_event " . $this->name_qry($selection['pers_profession'], $selection['part_profession']) . ')';
                $and = " AND ";
                $add_event_qry = true;
            }

            if ($selection['text']) {
                // *** Search in person and family text ***
                $query .= $and . " (pers_text " . $this->name_qry($selection['text'], $selection['part_text']) . "
                    OR fam_text " . $this->name_qry($selection['text'], $selection['part_text']) . ")";
                $and = " AND ";

                $add_text_qry = true;
            }

            if ($selection['pers_place']) {
                $query .= $and . " address_place " . $this->name_qry($selection['pers_place'], $selection['part_place']);
                $and = " AND ";
                $add_address_qry = true;
            }

            if ($selection['zip_code']) {
                $query .= $and . " address_zip " . $this->name_qry($selection['zip_code'], $selection['part_zip_code']);
                $and = " AND ";
                $add_address_qry = true;
            }

            if ($selection['witness']) {
                $query .= $and . " ( RIGHT(event_kind,7)='witness' AND event_event " . $this->name_qry($selection['witness'], $selection['part_witness']) . ')';
                $and = " AND ";
                $add_event_qry = true;
            }

            if ($selection['parent_status'] && $selection['parent_status'] == "noparents") {
                $query .= $and . " (pers_famc = '') ";
                $and = " AND ";
                $add_event_qry = true;
            }

            // *** Change query if searched for spouse ***
            if ($selection['spouse_firstname'] || $selection['spouse_lastname']) {
                $query .= $and . "pers_fams!=''";
                $and = " AND ";
            }


            // *** Build SELECT part of query. Search with option "ALL family trees" or "All but selected" ***
            if ($select_trees == 'all_trees' || $select_trees == 'all_but_this') {
                $query_part = $query;

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
                $query_select .= ", event_event, event_kind";
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

            $query_select .= $make_date . " FROM humo_persons";

            if ($add_event_qry) {
                $query_select .= " LEFT JOIN humo_events
                    ON event_tree_id=pers_tree_id
                    AND event_connect_id=pers_gedcomnumber";
                // *** If event_kind='name' is used, search for name will work, but other events are hidden! ***
                //AND event_kind='name'";
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
            }

            // *** GROUP BY is needed to prevent double results if searched for events ***
            $query_select .= " WHERE (" . $multi_tree . ") " . $query . " GROUP BY pers_id";

            $query_select .= " ORDER BY " . $orderby;
            $query = $query_select;
        }

        // *** Menu quicksearch ***
        if ($index_list == 'quicksearch') {
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
                $query = '';
                $counter = 0;
                $multi_tree = '';
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

            /*	******************************************
            *** QUICKSEARCH QUERY ***
            Aug 2017: changed for MySQL > 5.7.
            Feb 2016: added search for patronym
            ******************************************
            */
            // *** April 2023: added pers_firstname, event_event. To find "firstname eventname" (event could be a kind of lastname too). ***
            // *** Nov. 2022: changed first patronymic line ***
            $query .= "SELECT SQL_CALC_FOUND_ROWS CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,
                humo_persons2.*, humo_persons1.pers_id, event_event, event_kind
                " . $make_date . "
                FROM humo_persons as humo_persons2
                RIGHT JOIN 
                (
                    SELECT pers_id, event_event, event_kind
                    FROM humo_persons
                    LEFT JOIN humo_events ON event_connect_id=pers_gedcomnumber AND event_kind='name' AND event_tree_id=pers_tree_id
                    WHERE (" . $multi_tree . ")
                        AND 
                        ( CONCAT(pers_firstname,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . safe_text_db($quicksearch) . "%'
                        OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),pers_firstname) LIKE '%" . safe_text_db($quicksearch) . "%' 
                        OR CONCAT(pers_patronym,pers_lastname,pers_firstname,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($quicksearch) . "%' 
                        OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,pers_firstname) LIKE '%" . safe_text_db($quicksearch) . "%'
                        OR CONCAT(event_event,pers_patronym,REPLACE(pers_prefix,'_',' '),pers_lastname) LIKE '%" . safe_text_db($quicksearch) . "%'
                        OR CONCAT(pers_patronym,pers_lastname,REPLACE(pers_prefix,'_',' '),event_event) LIKE '%" . safe_text_db($quicksearch) . "%' 
                        OR CONCAT(pers_patronym,pers_lastname,event_event,REPLACE(pers_prefix,'_',' ')) LIKE '%" . safe_text_db($quicksearch) . "%' 
                        OR CONCAT(pers_patronym,REPLACE(pers_prefix,'_',' '), pers_lastname,event_event) LIKE '%" . safe_text_db($quicksearch) . "%'

                        OR CONCAT(pers_firstname,event_event) LIKE '%" . safe_text_db($quicksearch) . "%'
                        )
                    GROUP BY pers_id, event_event, event_kind
                ) as humo_persons1
                ON humo_persons1.pers_id = humo_persons2.pers_id
            ";
            // *** Prevent double results (if there are multiple nick names) ***
            // *** 31-03-2023 BE AWARE: disabled option ONLY_GROUP_BY in header script ***
            $query .= " GROUP BY humo_persons1.pers_id";
            // *** Added event_event and event_kind for some PHP/MySQL providers... ***
            // IF USED THERE ARE DOUBLE RESULTS IN SEARCH LIST:
            //$query.=" GROUP BY humo_persons1.pers_id, event_event, event_kind";
            $query .= " ORDER BY " . $orderby;
        }

        //*** Places index ***
        if ($index_list == 'places') {
            // *** EXAMPLE of a UNION querie ***
            //$qry = "(SELECT * FROM humo1_person ".$query.') ';
            //$qry.= " UNION (SELECT * FROM humo2_person ".$query.')';
            //$qry.= " UNION (SELECT * FROM humo3_person ".$query.')';
            //$qry.= " ORDER BY pers_lastname, pers_firstname";

            $query = '';
            $start = false;

            // *** Search birth place ***
            if ($data["select_birth"] == '1') {
                if ($this->user['group_kindindex'] == "j") {
                    $query = "(SELECT SQL_CALC_FOUND_ROWS *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_birth_place as place_order
                        FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
                } else {
                    $query = "(SELECT SQL_CALC_FOUND_ROWS *, pers_birth_place as place_order
                        FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
                }

                if ($data["place_name"]) {
                    $query .= " AND pers_birth_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
                } else {
                    $query .= " AND pers_birth_place LIKE '_%'";
                }
                $query .= ')';
                $start = true;
            }

            // *** Search baptise place ***
            if ($data["select_bapt"] == '1') {
                if ($start == true) {
                    $query .= ' UNION ';
                    $calc = '';
                } else {
                    $calc = 'SQL_CALC_FOUND_ROWS ';
                }
                if ($this->user['group_kindindex'] == "j") {
                    $query .= "(SELECT " . $calc . "*,CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_bapt_place as place_order
                        FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
                } else {
                    $query .= "(SELECT " . $calc . "*, pers_bapt_place as place_order FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
                }
                if ($data["place_name"]) {
                    $query .= " AND pers_bapt_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
                } else {
                    $query .= " AND pers_bapt_place LIKE '_%'";
                }
                $query .= ')';
                $start = true;
            }

            // *** Search residence ***
            if ($data["select_place"] == '1') {
                if ($start == true) {
                    $query .= ' UNION ';
                    $calc = '';
                } else {
                    $calc = 'SQL_CALC_FOUND_ROWS ';
                }

                if ($this->user['group_kindindex'] == "j") {
                    //$query.= "(SELECT ".$calc."*, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_place_index as place_order
                    //FROM humo_persons WHERE pers_tree_id='".$this->tree_id."'";

                    $query .= "(SELECT " . $calc . "humo_persons.*, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, humo_addresses.address_place as place_order
                        FROM humo_persons, humo_connections, humo_addresses
                        WHERE connect_connect_id=pers_gedcomnumber
                        AND connect_tree_id=pers_tree_id
                        AND address_gedcomnr=connect_item_id AND address_tree_id=pers_tree_id
                        AND pers_tree_id='" . $this->tree_id . "'";
                } else {
                    //$query.= "(SELECT ".$calc."*, pers_place_index as place_order 
                    //	FROM humo_persons WHERE pers_tree_id='".$this->tree_id."'";

                    $query .= "(SELECT " . $calc . "humo_persons.*, humo_addresses.address_place as place_order
                        FROM humo_persons, humo_connections, humo_addresses
                        WHERE connect_connect_id=pers_gedcomnumber
                        AND connect_tree_id=pers_tree_id
                        AND address_gedcomnr=connect_item_id AND address_tree_id=pers_tree_id
                        AND pers_tree_id='" . $this->tree_id . "'";
                }

                if ($data["place_name"]) {
                    //$query.= " AND pers_place_index ".$this->name_qry($data["place_name"],$data["part_place_name"]);
                    $query .= " AND address_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
                } else {
                    //$query .= " AND pers_place_index LIKE '_%'";
                    $query .= " AND address_place LIKE '_%'";
                }
                $query .= ')';
                $start = true;
            }

            // *** Search death place ***
            if ($data["select_death"] == '1') {
                if ($start == true) {
                    $query .= ' UNION ';
                    $calc = '';
                } else {
                    $calc = 'SQL_CALC_FOUND_ROWS ';
                }
                if ($this->user['group_kindindex'] == "j") {
                    $query .= "(SELECT " . $calc . "*, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, pers_death_place as place_order
                        FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
                } else {
                    $query .= "(SELECT " . $calc . "*, pers_death_place as place_order
                        FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
                }
                if ($data["place_name"]) {
                    $query .= " AND pers_death_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
                } else {
                    $query .= " AND pers_death_place LIKE '_%'";
                }
                $query .= ')';
                $start = true;
            }

            // *** Search buried place ***
            if ($data["select_buried"] == '1') {
                if ($start == true) {
                    $query .= ' UNION ';
                    $calc = '';
                } else {
                    $calc = 'SQL_CALC_FOUND_ROWS ';
                }
                if ($this->user['group_kindindex'] == "j") {
                    $query .= "(SELECT " . $calc . "*,CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name,pers_buried_place as place_order
                        FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
                } else {
                    $query .= "(SELECT " . $calc . "*, pers_buried_place as place_order
                        FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "'";
                }
                if ($data["place_name"]) {
                    $query .= " AND pers_buried_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
                } else {
                    $query .= " AND pers_buried_place LIKE '_%'";
                }
                $query .= ')';
                $start = true;
            }

            // *** NEW oct. 2021: Search for place in events like occupation ***
            if ($data["select_place"] == '1') {
                if ($start == true) {
                    $query .= ' UNION ';
                    $calc = '';
                } else {
                    $calc = 'SQL_CALC_FOUND_ROWS ';
                }
                if ($this->user['group_kindindex'] == "j") {
                    $query .= "(SELECT " . $calc . "humo_persons.*, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name, humo_events.event_place as place_order
                        FROM humo_persons, humo_events
                        WHERE event_connect_id=pers_gedcomnumber
                        AND event_tree_id=pers_tree_id
                        AND pers_tree_id='" . $this->tree_id . "'";
                } else {
                    $query .= "(SELECT " . $calc . "humo_persons.*, humo_events.event_place as place_order
                        FROM humo_persons, humo_events
                        WHERE event_connect_id=pers_gedcomnumber
                        AND event_tree_id=pers_tree_id
                        AND pers_tree_id='" . $this->tree_id . "'";
                }

                if ($data["place_name"]) {
                    $query .= " AND event_place " . $this->name_qry($data["place_name"], $data["part_place_name"]);
                } else {
                    $query .= " AND event_place LIKE '_%'";
                }
                $query .= ')';
                $start = true;
            }


            // *** Order by place and name: "Mons, van" or: "van Mons" ***
            if ($this->user['group_kindindex'] == "j") {
                $query .= ' ORDER BY place_order, concat_name';
            } else {
                $query .= ' ORDER BY place_order, pers_lastname, pers_firstname';
            }
        }


        // Test line to show query.
        //echo $query.'!!';
        //end;


        //*** Patronym list ***
        if ($index_list == 'patronym') {
            // *** Only in pers_patronym index if there is no pers_lastname! ***
            $query = "SELECT SQL_CALC_FOUND_ROWS * " . $make_date . " FROM humo_persons
                WHERE pers_tree_id='" . $this->tree_id . "' AND pers_patronym LIKE '_%' AND pers_lastname='' ORDER BY " . $orderby;
        }

        // **************************
        // *** Generate indexlist ***
        // **************************

        // *** Standard index ***
        if ($query == '' or $index_list == 'standard') {
            $query = "SELECT * " . $make_date . " FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' ORDER BY " . $orderby;

            // Mons, van or: van Mons
            if ($this->user['group_kindindex'] == "j") {
                $query = "SELECT *, CONCAT(pers_prefix,pers_lastname,pers_firstname) as concat_name " . $make_date . "
                    FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' ORDER BY " . $orderby;
            }

            //$count_qry = "SELECT COUNT(*) as teller ".$make_date." FROM humo_persons WHERE pers_tree_id='".$this->tree_id."'";
            // *** 31-03-2023 GROUP BY option is needed for COUNT: added GROUP BY and removed $make_date (not necessary) ***
            $count_qry = "SELECT COUNT(pers_tree_id) as teller, pers_tree_id FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' GROUP BY pers_tree_id";
        }

        // *** DEBUG/ TEST: SHOW QUERY ***
        //echo $query.'<br>';

        //*** Show number of persons and pages *****************************************
        $item = 0;
        if (isset($_GET['item']) && is_numeric($_GET['item'])) {
            $item = $_GET['item'];
        }
        $start = 0;
        if (isset($_GET["start"]) && is_numeric($_GET["start"])) {
            $start = $_GET["start"];
        }
        $nr_persons = $this->humo_option['show_persons'];

        if (!$selection['spouse_firstname'] && !$selection['spouse_lastname'] && $selection['parent_status'] != "motheronly" && $selection['parent_status'] != "fatheronly") {
            $person_result = $this->dbh->query($query . " LIMIT " . $item . "," . $nr_persons);

            if ($count_qry) {
                // *** Use MySQL COUNT command to calculate nr. of persons in simple queries (faster than php num_rows and in simple queries faster than SQL_CAL_FOUND_ROWS) ***
                $result = $this->dbh->query($count_qry);
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
            $person_result = $this->dbh->query($query);
            $count_persons = 0; // Isn't used if search is done for spouse or for people with only known mother or only known father...
        }

        $data["person_result"] = $person_result;
        $data["start"] = $start;
        $data["nr_persons"] = $nr_persons;
        $data["count_persons"] = $count_persons;
        $data["item"] = $item;
        return $data;
    }
}

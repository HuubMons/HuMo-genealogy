<?php
include_once(__DIR__ . '/../../include/language_date.php');
include_once(__DIR__ . '/../../include/date_place.php');
include_once(__DIR__ . "/../../include/show_tree_date.php");

class TreeIndexModel
{
    // Can't be used in all functions yet. Refactor is needed.
    private $dbh, $humo_option;

    public function __construct($dbh, $humo_option)
    {
        $this->dbh = $dbh;
        $this->humo_option = $humo_option;
    }

    public function show_tree_index()
    {
        global $dbh, $tree_id, $tree_prefix_quoted, $dataDb, $selected_language, $treetext_name, $dirmark2, $bot_visit, $humo_option, $db_functions;
        global $link_cls, $uri_path;

        // *** Option to only index CMS page for bots ***
        if ($bot_visit && $humo_option["searchengine_cms_only"] == 'y') {
            $item_array[0]['position'] = 'center';
            $item_array[0]['header'] = '';
            $item_array[0]['item'] = $this->selected_family_tree();

            $item_array[1]['position'] = 'right';
            $item_array[1]['header'] = '';
            $item_array[1]['item'] = '';
        }
        // *** Check visitor/ user permissions ***
        elseif ($tree_prefix_quoted == '' || $tree_prefix_quoted == 'EMPTY') {
            $temp = $this->selected_family_tree();

            $path_tmp = $link_cls->get_link($uri_path, 'login');
            $temp .= '<h2><a href="' . $path_tmp . '">' . __('Select another family tree, or login for the selected family tree.') . '</a></h2>';

            $item_array[0]['position'] = 'center';
            $item_array[0]['header'] = '';
            $item_array[0]['item'] = $temp;

            $item_array[1]['position'] = 'right';
            $item_array[1]['header'] = '';
            $item_array[1]['item'] = '';
        }
        // *** One name study page ***
        elseif ($humo_option["one_name_study"] != 'n') {
            $item_array[0]['position'] = 'center';
            $item_array[0]['header'] = __('One Name Study of the name');
            $item_array[0]['item'] = '<span style="font-weight:bold;font-size:150%">' . $humo_option["one_name_thename"] . '</span>';

            // *** Right column: search module ***
            $item_array[1]['position'] = 'right';
            $item_array[1]['header'] = __('Search');
            $item_array[1]['item'] = $this->search_box();
        }
        // *** Standard family tree template page ***
        else {
            $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
            while ($data2Db = $datasql->fetch(PDO::FETCH_OBJ)) {
                $item = explode("|", $data2Db->setting_value);
                if ($item[0] === 'active') {
                    $module_column[] = $item[1];
                    $module_item[] = $item[2];

                    $module_option_1[] = isset($item[3]) ? $item[3] : '';

                    $module_option_2[] = isset($item[4]) ? $item[4] : '';

                    $module_order[] = $data2Db->setting_order;
                }
            }

            $nr_modules = 0;
            if (isset($module_order)) {
                $nr_modules = count($module_order);
            }
            $nr_modules--;


            $count = 0;

            for ($i = 0; $i <= $nr_modules; $i++) {
                $temp = '';
                $header = '';

                // *** Select family tree ***
                if ($module_item[$i] == 'select_family_tree') {
                    //move these 2 rows at top of template script?
                    $data2sql = $dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
                    $num_rows = $data2sql->rowCount();
                    if ($num_rows > 1) {
                        $header = __('Select a family tree');
                        // *** List of family trees ***
                        $temp .= $this->tree_list($data2sql);
                    } else {
                        // *** Don't show selection of family trees if there is only 1 tree ***
                        $module_column[$i] = '';
                    }
                }

                // *** Homepage favourites ***
                if ($module_item[$i] == 'favourites') {
                    $header = __('Favourites');
                    $temp .= $this->extra_links();
                }

                // *** Just for sure, probably not necessary here: re-get selected family tree data ***
                $dataDb = $db_functions->get_tree($tree_prefix_quoted);
                //*** Today in history ***
                if ($module_item[$i] == 'history') {
                    $header = __('Today in history');
                    $temp .= $this->today_in_history($module_option_1[$i]);
                }

                // *** Alphabet line ***
                if ($module_item[$i] == 'alphabet') {
                    //*** Find first first_character of last name ***
                    $header = __('Surnames Index');
                    $temp .= $this->alphabet() . $dirmark2;
                }

                //*** Most frequent names ***
                if ($module_item[$i] == 'names') {
                    $header = __('Names');
                    $temp .= $this->last_names($module_option_1[$i], $module_option_2[$i]);
                }

                // *** Show name of selected family tree ***
                if ($module_item[$i] == 'selected_family_tree') {
                    $header = $this->selected_family_tree();

                    // use seperate modules for these items?
                    // *** Date and number of persons/ families ***
                    $temp .= ' <i>' . $this->tree_data() . '</i><br>';
                    if ($this->tree_data() != "") {
                        $temp .= $dirmark2;
                    }

                    // *** Owner genealogy ***
                    $temp .= $this->owner();

                    // *** Prepare mainmenu text and source ***
                    $treetext = show_tree_text($dataDb->tree_id, $selected_language);

                    // *** Show mainmenu text ***
                    $mainmenu_text = $treetext['mainmenu_text'];
                    if ($mainmenu_text != '') {
                        $temp .= '<br><br>' . nl2br($mainmenu_text) . $dirmark2;
                    }

                    // *** Show mainmenu source ***
                    $mainmenu_source = $treetext['mainmenu_source'];
                    if ($mainmenu_source != '') {
                        $temp .= '<br><br>' . nl2br($mainmenu_source) . $dirmark2;
                    }
                }

                // *** Search ***
                if ($module_item[$i] == 'search') {
                    $header = __('Search');
                    if (!$bot_visit) {
                        $temp .= $this->search_box();
                    }
                }

                // *** Random photo ***
                if ($module_item[$i] == 'random_photo') {
                    $header = __('Random photo');
                    if (!$bot_visit) {
                        $temp .= $this->random_photo();
                    }
                }

                // *** Text ***
                if ($module_item[$i] == 'text') {
                    if ($module_option_1[$i]) {
                        $header = $module_option_1[$i];
                    }
                    $temp .= $module_option_2[$i];
                }

                // *** CMS page ***
                if ($module_item[$i] == 'cms_page') {
                    $page_qry = $dbh->query("SELECT * FROM humo_cms_pages WHERE page_id='" . $module_option_1[$i] . "' AND page_status!=''");
                    $cms_pagesDb = $page_qry->fetch(PDO::FETCH_OBJ);

                    if ($cms_pagesDb->page_title) {
                        $header = $cms_pagesDb->page_title;
                    }
                    $temp .= $cms_pagesDb->page_text;
                }

                // *** Own script ***
                if ($module_item[$i] == 'own_script' && strpos($module_option_2[$i], $_SERVER['HTTP_HOST'])) {
                    if ($module_option_1[$i]) {
                        $header = __($module_option_1[$i]);
                    }
                    $codefile = $module_option_2[$i];
                    $temp .= file_get_contents($codefile . '?language=' . $selected_language . '&treeid=' . $tree_id);
                }

                // *** Empty line ***
                if ($module_item[$i] == 'empty_line') {
                    $temp .= '<br>';
                }

                if ($module_column[$i] == 'left') {
                    $item_array[$count]['position'] = 'left';
                    $item_array[$count]['item'] = $temp;
                    $item_array[$count]['header'] = $header;
                    $count++;
                }
                if ($module_column[$i] == 'center') {
                    $item_array[$count]['position'] = 'center';
                    $item_array[$count]['item'] = $temp;
                    $item_array[$count]['header'] = $header;
                    $count++;
                }
                if ($module_column[$i] == 'right') {
                    $item_array[$count]['position'] = 'right';
                    $item_array[$count]['item'] = $temp;
                    $item_array[$count]['header'] = $header;
                    $count++;
                }
            }
        } // *** End of user permission check ***

        return $item_array;
    }

    // *** Show name of selected family tree ***
    public function selected_family_tree()
    {
        global $dbh, $num_rows, $selected_language;
        $text = '';
        if ($num_rows > 1) {
            $text .= __('Selected family tree') . ': ';
        }
        // *** Variable $treetext_name used from menu.php ***
        $treetext = show_tree_text($_SESSION['tree_id'], $selected_language);
        return $text . $treetext['name'];
    }

    // *** List family trees ***
    public function tree_list($datasql)
    {
        global $dbh, $humo_option, $uri_path, $user, $language, $selected_language, $link_cls;
        $text = '';
        while ($dataDb = $datasql->fetch(PDO::FETCH_OBJ)) {
            // *** Check is family tree is shown or hidden for user group ***
            $hide_tree_array = explode(";", $user['group_hide_trees']);
            if (!in_array($dataDb->tree_id, $hide_tree_array)) {
                $treetext = show_tree_text($dataDb->tree_id, $selected_language);
                $treetext_name = $treetext['name'];

                // *** Name family tree ***
                if ($dataDb->tree_prefix == 'EMPTY') {
                    // *** Show empty line ***
                    $tree_name = '';
                } elseif (isset($_SESSION['tree_prefix']) && $_SESSION['tree_prefix'] == $dataDb->tree_prefix) {
                    $tree_name = '<span class="tree_link">' . $treetext_name . '</span>';
                } else {
                    $path_tmp = $link_cls->get_link($uri_path, 'tree_index', $dataDb->tree_id);
                    $tree_name = '<span class="tree_link"><a href="' . $path_tmp . '">' . $treetext_name . '</a></span>';
                }
                if ($text !== '') {
                    $text .= '<br>';
                }
                $text .= $tree_name;
            }    // end of family tree check
        }

        // *** Use scroll scrollbar for long list of family trees ***
        //$text='<div style="max-height:240px; overflow-x: auto;">'.$text.'</div>';

        return $text;
    }

    // *** Family tree data ***
    public function tree_data()
    {
        global $dataDb;
        return __('Latest update:') . ' ' . show_tree_date($dataDb->tree_date, true) . ', ' . $dataDb->tree_persons . ' ' . __('persons') . ', ' . $dataDb->tree_families . ' ' . __('families');
    }

    // *** Owner family tree ***
    public function owner()
    {
        global $dataDb, $humo_option;
        $tree_owner = '';

        if (isset($dataDb->tree_owner) && $dataDb->tree_owner) {
            $tree_owner = __('Owner family tree:') . ' ';
            // *** Show owner e-mail address ***
            if ($dataDb->tree_email) {
                $path_tmp = $humo_option["url_rewrite"] == "j" ? 'mailform.php' : 'index.php?page=mailform';
                $tree_owner .= '<a href="' . $path_tmp . '">' . $dataDb->tree_owner . "</a>\n";
            } else {
                $tree_owner .= $dataDb->tree_owner . "\n";
            }
        }
        return $tree_owner;
    }

    //*** Most frequent names ***
    public function last_names($columns, $rows)
    {
        global $dbh, $dataDb, $tree_id, $language, $user, $humo_option, $uri_path, $maxcols, $text;

        // MAIN SETTINGS
        $maxcols = 2; // number of name&nr colums in table. For example 3 means 3x name col + nr col
        if ($columns) {
            $maxcols = $columns;
        }

        $maxnames = 8;
        if ($rows) {
            $maxnames = $rows * $maxcols;
        }

        $text = '';

        if (!function_exists('tablerow')) {
            function tablerow($nr, $lastcol = false)
            {
                // displays one set of name & nr column items in the row
                // $nr is the array number of the name set created in function last_names
                // if $lastcol is set to true, the last right border of the number column will not be made thicker (as the other ones are to distinguish between the name&nr sets)
                global $user, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $text, $link_cls, $uri_path, $tree_id;
                $path_tmp = $link_cls->get_link($uri_path, 'list', $tree_id, true);
                $text .= '<td class="namelst">';
                if (isset($freq_last_names[$nr])) {
                    $top_pers_lastname = '';
                    if ($freq_pers_prefix[$nr]) {
                        $top_pers_lastname = str_replace("_", " ", $freq_pers_prefix[$nr]);
                    }
                    $top_pers_lastname .= $freq_last_names[$nr];
                    if ($user['group_kindindex'] == "j") {
                        $text .= '<a href="' . $path_tmp . 'pers_lastname=' . str_replace("_", " ", $freq_pers_prefix[$nr]) . str_replace("&", "|", $freq_last_names[$nr]);
                    } else {
                        $top_pers_lastname = $freq_last_names[$nr];
                        if ($freq_pers_prefix[$nr]) {
                            $top_pers_lastname .= ', ' . str_replace("_", " ", $freq_pers_prefix[$nr]);
                        }
                        $text .= '<a href="' . $path_tmp . 'pers_lastname=' . str_replace("&", "|", $freq_last_names[$nr]);
                        if ($freq_pers_prefix[$nr]) {
                            $text .= '&amp;pers_prefix=' . $freq_pers_prefix[$nr];
                        } else {
                            $text .= '&amp;pers_prefix=EMPTY';
                        }
                    }
                    $text .= '&amp;part_lastname=equals">' . $top_pers_lastname . "</a>";
                } else {
                    $text .= '~';
                }
                $text .= '</td>';

                if ($lastcol == false) {
                    $text .= '<td class="namenr" style="text-align:center;border-right-width:3px">';
                } else {
                    $text .= '<td class="namenr" style="text-align:center">';
                } // no thick border

                if (isset($freq_last_names[$nr])) {
                    $text .= $freq_count_last_names[$nr];
                } else {
                    $text .= '~';
                }
                $text .= '</td>';
            }
        }

        if (!function_exists('last_names')) {
            function last_names($max)
            {
                global $dbh, $dataDb, $tree_id, $language, $user, $humo_option, $uri_path, $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $maxcols, $text;

                // *** Read cache (only used in large family trees) ***
                $cache = '';
                $cache_count = 0;
                $cache_exists = false;
                $cache_check = false; // *** Use cache for large family trees ***
                $cacheqry = $dbh->query("SELECT * FROM humo_settings
                WHERE setting_variable='cache_surnames' AND setting_tree_id='" . $tree_id . "'");
                $cacheDb = $cacheqry->fetch(PDO::FETCH_OBJ);
                if ($cacheDb) {
                    $cache_exists = true;
                    $cache_array = explode("|", $cacheDb->setting_value);
                    foreach ($cache_array as $cache_line) {
                        $cacheDb = json_decode(unserialize($cache_line));

                        $cache_check = true;
                        $test_time = time() - 7200; // *** 86400 = 1 day, 7200 = 2 hours ***
                        // TEST LINE
                        //$test_time=time()-20; // *** 86400 = 1 day, 7200 = 2 hours ***
                        if ($cacheDb->time < $test_time) {
                            $cache_check = false;
                        } else {
                            $freq_last_names[] = $cacheDb->pers_lastname;
                            $freq_pers_prefix[] = $cacheDb->pers_prefix;
                            $freq_count_last_names[] = $cacheDb->count_last_names;
                        }
                    }
                }

                if ($cache_check == false) {
                    // TEST LINE
                    //echo 'NO CACHE';
                    /*
                    $personqry="SELECT pers_lastname, pers_prefix,
                        CONCAT(pers_prefix,pers_lastname) as long_name, count(pers_lastname) as count_last_names
                        FROM humo_persons
                        WHERE pers_tree_id='".$tree_id."' AND pers_lastname NOT LIKE ''
                        GROUP BY long_name ORDER BY count_last_names DESC LIMIT 0,".$max;
                    */
                    // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
                    $personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
                        FROM humo_persons
                        WHERE pers_tree_id='" . $tree_id . "' AND pers_lastname NOT LIKE ''
                        GROUP BY pers_lastname, pers_prefix ORDER BY count_last_names DESC LIMIT 0," . $max;
                    $person = $dbh->query($personqry);

                    while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
                        // *** Cache: only use cache if there are > 5.000 persons in database ***
                        if (isset($dataDb->tree_persons) && $dataDb->tree_persons > 5000) {
                            $personDb->time = time(); // *** Add linux time to array ***
                            if ($cache !== '' && $cache !== '0') {
                                $cache .= '|';
                            }
                            $cache .= serialize(json_encode($personDb));
                            $cache_count++;
                        }

                        $freq_last_names[] = $personDb->pers_lastname;
                        $freq_pers_prefix[] = $personDb->pers_prefix;
                        $freq_count_last_names[] = $personDb->count_last_names;
                    }

                    // *** Add or renew cache in database (only if cache_count is valid) ***
                    if ($cache && $cache_count == $max) {
                        if ($cache_exists) {
                            // *** Update existing cache item ***
                            $sql = "UPDATE humo_settings SET
                                setting_variable='cache_surnames', setting_value='" . safe_text_db($cache) . "'
                                WHERE setting_tree_id='" . safe_text_db($tree_id) . "'";
                            $dbh->query($sql);
                        } else {
                            // *** Add new cache item ***
                            $sql = "INSERT INTO humo_settings SET
                                setting_variable='cache_surnames', setting_value='" . safe_text_db($cache) . "',
                                setting_tree_id='" . safe_text_db($tree_id) . "'";
                            $dbh->query($sql);
                        }
                    }
                } // *** End of cache ***

                $row = 0;
                if ($freq_last_names) {
                    $row = round(count($freq_last_names) / $maxcols);
                }

                for ($i = 0; $i < $row; $i++) {
                    $text .= '<tr>';
                    for ($n = 0; $n < $maxcols; $n++) {
                        if ($n == $maxcols - 1) {
                            tablerow($i + ($row * $n), true); // last col
                        } else {
                            tablerow($i + ($row * $n)); // other cols
                        }
                    }
                    $text .= '</tr>';
                }
                if (isset($freq_count_last_names)) {
                    return $freq_count_last_names[0];
                }
                return null;
            }
        }

        // *** nametbl = used for javascript to show graphical lightgray bar to show number of persons ***
        $text .= '<table class="table table-sm nametbl">';

        $text .= '<thead class="table-primary">';
        $text .= '<tr>';
        $col_width = ((round(100 / $maxcols)) - 6) . "%";
        for ($x = 1; $x < $maxcols; $x++) {
            $text .= '<th style="width:' . $col_width . ';">' . __('Surname') . '</th><th>' . __('Total') . '</th>';
        }
        $text .= '<th style="width:' . $col_width . ';">' . __('Surname') . '</th><th>' . __('Total') . '</th>';
        $text .= '</tr>';
        $text .= '</thead>';

        $baseperc = last_names($maxnames);   // displays the table and sets the $baseperc (= the name with highest frequency that will be 100%)

        $path = $humo_option["url_rewrite"] == "j" ? 'statistics' : 'index.php?page=statistics';
        $text .= '<tr><td colspan="' . ($maxcols * 2) . '" class="table-active"><a href="' . $path . '">' . __('More statistics') . '</a></td></tr>';
        $text .= '</table>';

        // *** Show light gray background bar, that graphical shows number of persons ***
        $text .= '
        <script>
        var tbl = document.getElementsByClassName("nametbl")[0];
        var rws = tbl.rows; var baseperc = ' . $baseperc . ';
        for(var i = 0; i < rws.length; i ++) {
            var tbs =  rws[i].getElementsByClassName("namenr");
            var nms = rws[i].getElementsByClassName("namelst");
            for(var x = 0; x < tbs.length; x ++) {
                var percentage = parseInt(tbs[x].innerHTML, 10);
                percentage = (percentage * 100)/baseperc;
                if(percentage > 0.1) {
                    nms[x].style.backgroundImage= "url(images/lightgray.png)"; 
                    nms[x].style.backgroundSize = percentage + "%" + " 100%";
                    nms[x].style.backgroundRepeat = "no-repeat";
                    nms[x].style.color = "rgb(0, 140, 200)";
                }
            }
        }
        </script>';

        return $text;
    }

    // *** Search field ***
    public function search_box()
    {
        global $language, $dbh, $humo_option, $link_cls, $uri_path, $tree_id;
        $text = '';

        // *** Reset search field if a new genealogy is selected ***
        $reset_search = false;
        if (isset($_SESSION["save_search_tree_prefix"]) && $_SESSION["save_search_tree_prefix"] != $_SESSION['tree_prefix']) {
            $reset_search = true;
        }
        if ($reset_search) {
            unset($_SESSION["save_firstname"]);
            unset($_SESSION["save_lastname"]);
            unset($_SESSION["save_part_firstname"]);
            unset($_SESSION["save_part_lastname"]);
            unset($_SESSION["save_search_database"]);
        }
        //*** Search screen ***
        $pers_firstname = '';
        if (isset($_SESSION["save_firstname"])) {
            $pers_firstname = $_SESSION["save_firstname"];
        }
        $part_firstname = '';
        if (isset($_SESSION["save_part_firstname"])) {
            $part_firstname = $_SESSION["save_part_firstname"];
        }
        $pers_lastname = '';
        if (isset($_SESSION["save_lastname"])) {
            $pers_lastname = $_SESSION["save_lastname"];
        }
        $part_lastname = '';
        if (isset($_SESSION["save_part_lastname"])) {
            $part_lastname = $_SESSION["save_part_lastname"];
        }
        $search_database = 'tree_selected';
        //if (isset($_SESSION["save_search_database"])) {
        //    $search_database = $_SESSION["save_search_database"];
        //}

        $path_tmp = $link_cls->get_link($uri_path, 'list', $tree_id, false);
        $text .= '<form method="post" action="' . $path_tmp . '">';

        $text .= '<p>';
        if ($humo_option['one_name_study'] == 'n') {
            $text .= __('Enter name or part of name') . '<br>';
        } else {
            $text .= __('Enter private name') . '<br>';
        }
        //$text.='<span style="font-size:10px;">"John Jones", "Jones John", "John of Jones", "of Jones, John", "Jones, John of", "Jones of, John"</span>';

        $text .= '<input type="hidden" name="index_list" value="quicksearch">';
        $quicksearch = '';
        if (isset($_POST['quicksearch'])) {
            //$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
            $quicksearch = safe_text_show($_POST['quicksearch']);
            $_SESSION["save_quicksearch"] = $quicksearch;
        }
        if (isset($_SESSION["save_quicksearch"])) {
            $quicksearch = $_SESSION["save_quicksearch"];
        }
        $text .= '<input type="text" class="form-control form-control-sm" name="quicksearch" placeholder="' . __('Name') . '" value="' . $quicksearch . '" size="30" pattern=".{3,}" title="' . __('Minimum: 3 characters.') . '"></p>';

        // Check if there are multiple family trees.
        $datasql2 = $dbh->query("SELECT * FROM humo_trees");
        $num_rows2 = $datasql2->rowCount();
        if ($num_rows2 > 1 && $humo_option['one_name_study'] == 'n') {
            /*
            $checked = '';
            if ($search_database == "tree_selected") {
                $checked = 'checked';
            }
            $text .= '<input type="radio" class="form-check-input" name="search_database" value="tree_selected" ' . $checked . '> ' . __('Selected family tree') . '<br>';
            //$checked=''; if ($search_database=="all_databases"){ $checked='checked'; }
            $checked = '';
            if ($search_database == "all_trees") {
                $checked = 'checked';
            }
            $text .= '<input type="radio" class="form-check-input" name="search_database" value="all_trees" ' . $checked . '> ' . __('All family trees') . '<br>';
            $checked = '';
            if ($search_database == "all_but_this") {
                $checked = 'checked';
            }
            $text .= '<input type="radio" class="form-check-input" name="search_database" value="all_but_this" ' . $checked . '> ' . __('All but selected tree') . '<br>';
            */

            $text .= '<select name="select_trees" class="form-select form-select-sm">';
            $text .= '<option value="tree_selected"';
            $text .= ' selected';
            $text .= '>' . __('Selected family tree') . '</option>';

            $text .= '<option value="all_trees"';
            if ($search_database === "all_trees") {
                $text .= ' selected';
            }
            $text .= '>' . __('All family trees') . '</option>';

            $text .= '<option value="all_but_this"';
            if ($search_database === "all_but_this") {
                $text .= ' selected';
            }
            $text .= '>' . __('All but selected tree') . '</option>';
            $text .= '</select>';
        }
        if ($num_rows2 > 1 && $humo_option['one_name_study'] == 'y') {
            $text .= '<input type="hidden" name="search_database" value="all_trees">';
        }
        $text .= '<p><button type="submit" class="btn btn-success btn-sm my-2">' . __('Search') . '</button></p>';
        $path_tmp = $link_cls->get_link($uri_path, 'list', $tree_id, true);
        $path_tmp .= 'adv_search=1&index_list=search';
        $text .= '<a href="' . $path_tmp . '"><img src="images/advanced-search.jpg" width="25"> ' . __('Advanced search') . '</a>';
        return $text . "</form>";
    }

    // *** Random photo ***
    public function random_photo()
    {
        global $dataDb, $tree_id, $dbh, $db_functions, $humo_option;
        // adding static table for displayed photos storage
        static $temp_pic_names_table = [];
        $text = '';
        // characters limit for rounding text below photo 100 looks good at photos inline, 200 should be good for lightbox desc
        //this for text without lightbox
        $char_limit = 100;
        //this for lightbox
        $char_limit2 = 400;

        $tree_pict_path = $dataDb->tree_pict_path;
        if (substr($tree_pict_path, 0, 1) === '|') {
            $tree_pict_path = 'media/';
        }

        // *** Loop through pictures and find first available picture without privacy filter ***
        // i added also family kind photos
        $qry = "SELECT * FROM humo_events
            WHERE event_tree_id='" . $tree_id . "' AND event_kind='picture' AND (event_connect_kind='person' OR event_connect_kind='family')  AND event_connect_id NOT LIKE ''
            ORDER BY RAND()";
        $picqry = $dbh->query($qry);
        // We will go unique-random aproach than only randomness
        // 'ORDER BY RAND' is pseudorandom. It's still not random - now im implementing uniqueness
        // first we count number of rows with this query
        // $rowCount = $picqry->rowCount();
        // then we skip some rows if sum of rows is enough to skip - thats why we count
        // $skipCount = random_int(0, $rowCount - 5);
        // for ($i = 0; $i < $skipCount; $i++) {
        //     if (!$picqry->fetch(PDO::FETCH_OBJ)) {
        //         return null;
        //     }
        // }

        while ($picqryDb = $picqry->fetch(PDO::FETCH_OBJ)) {
            $picname = $picqryDb->event_event;
            // adding new var to store kind of connection - will be useful to use different approach for person photos and family photos
            $pic_conn_kind = $picqryDb->event_connect_kind;
            // this code was taking extension from name and was not working with 4 letter extensions: $check_file = strtolower(substr($picname, -3, 3));
            // im now using dedicated function to determine extension - it get 3 letter extensions and 4 letter for jpeg which i'm adding too below
            $check_file = pathinfo($picname, PATHINFO_EXTENSION);

            // im adding jpeg also and adding uniqueness 
            if (($check_file === 'png' || $check_file === 'gif' || $check_file === 'jpg' || $check_file === 'jpeg') && file_exists($tree_pict_path . $picname) && !in_array($picname, $temp_pic_names_table)) {

                $is_privacy = true;

                if ($pic_conn_kind == 'person') {
                    $personmnDb = $db_functions->get_person($picqryDb->event_connect_id);
                    $man_cls = new PersonCls($personmnDb);
                    if ($man_cls->privacy == '') {
                        $is_privacy = false;

                        $name = $man_cls->person_name($personmnDb);
                        $link_text = $name["standard_name"];

                        $url = $man_cls->person_url2($personmnDb->pers_tree_id, $personmnDb->pers_famc, $personmnDb->pers_fams, $personmnDb->pers_gedcomnumber);
                    }
                } elseif ($pic_conn_kind == 'family') {
                    $qry2 = "SELECT * FROM humo_families WHERE fam_gedcomnumber='" . $picqryDb->event_connect_id . "'";
                    $picqry2 = $dbh->query($qry2);
                    $picqryDb2 = $picqry2->fetch(PDO::FETCH_OBJ);

                    $personmnDb2 = $db_functions->get_person($picqryDb2->fam_man);
                    $man_cls = new PersonCls($personmnDb2);

                    $personmnDb3 = $db_functions->get_person($picqryDb2->fam_woman);
                    $woman_cls = new PersonCls($personmnDb3);

                    // *** Only use this picture if both man and woman have disabled privacy options ***
                    if ($man_cls->privacy == '' && $woman_cls->privacy == '') {
                        $is_privacy = false;

                        $name = $man_cls->person_name($personmnDb2);
                        $man_name = $name["standard_name"];

                        $name = $woman_cls->person_name($personmnDb3);
                        $woman_name =  $name["standard_name"];

                        $link_text = __('Family') . ': ' . $man_name . ' &amp; ' . $woman_name;

                        if ($humo_option["url_rewrite"] == "j") {
                            $url = 'family/' . $picqryDb->event_tree_id . '/' . $picqryDb->event_connect_id;
                        } else {
                            $url = 'index.php?page=family&tree_id=' . $picqryDb->event_tree_id . '&id=' . $picqryDb->event_connect_id;
                        }
                        // TODO use function to build link:
                        //$vars['pers_family'] = $familyDb->stat_gedcom_fam;
                        //$link = $link_cls->get_link('../', 'family', $familyDb->tree_id, false, $vars);
                        //echo '<a href="' . $link . '">' . __('Family') . ': </a>';
                    }
                }

                if (!$is_privacy) {
                    $date_place = '';
                    if ($picqryDb->event_date || $picqryDb->event_place) {
                        $date_place = date_place($picqryDb->event_date, $picqryDb->event_place) . '<br>';
                    }
                    include_once('./include/give_media_path.php');
                    $picture_path = give_media_path($tree_pict_path, $picname);

                    // u can delete this variables if there are some global variables for protocol and omain combined
                    // Get the protocol (HTTP or HTTPS)
                    $text .= '<div style="text-align: center;">';

                    // *** Show picture using GLightbox ***
                    $desc_for_lightbox =  $date_place . str_replace("&", "&amp;", $picqryDb->event_text);
                    $desc_for_lightbox = (mb_strlen($desc_for_lightbox, "UTF-8") > $char_limit2) ? mb_substr($desc_for_lightbox, 0, $char_limit2, "UTF-8") . '...' : $desc_for_lightbox;

                    $text .= '<a href="' . $picture_path . '" class="glightbox" data-glightbox="description: ' . $desc_for_lightbox . '"><img src="' . $picture_path .
                        '" width="90%" style="border-radius: 5px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);"></a><br>';

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $text .= '<a href="' . $url . '">' . $link_text . '</a>';
                    if ($picqryDb->event_text !== '' or $date_place !== '') {
                        // this code shortens event text below photos to 50 chars and adds '...' if its above 50 chars. Photos with long texts added looks bad...
                        $shortEventText = (mb_strlen($picqryDb->event_text, "UTF-8") > $char_limit) ? mb_substr($picqryDb->event_text, 0, $char_limit, "UTF-8") . '...' : $picqryDb->event_text;
                        $text .= '<br>' . $date_place . $shortEventText;
                    }
                    $text .= '</div>';
                    // add displayed photo to table for checking uniqueness
                    $temp_pic_names_table[] = $picname;
                    // *** Show first available picture without privacy restrictions ***
                    break;
                }
            }
        }
        return $text;
    }

    // *** Favourites ***
    public function extra_links()
    {
        global $dbh, $tree_id, $humo_option, $uri_path;
        $text = '';

        // *** Check if there are extra links ***
        $datasql = $dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
        $num_rows = $datasql->rowCount();
        if ($num_rows > 0) {
            while ($data2Db = $datasql->fetch(PDO::FETCH_OBJ)) {
                $item = explode("|", $data2Db->setting_value);
                $pers_own_code[] = $item[0];
                $link_text[] = $item[1];
                $link_order[] = $data2Db->setting_order;
            }
            $person = $dbh->query("SELECT * FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_own_code NOT LIKE ''");
            while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
                if (in_array($personDb->pers_own_code, $pers_own_code)) {
                    $person_cls = new PersonCls;
                    //$person_cls = new PersonCls($personDb);

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $path_tmp = $person_cls->person_url2($personDb->pers_tree_id, $personDb->pers_famc, $personDb->pers_fams, $personDb->pers_gedcomnumber);
                    $name = $person_cls->person_name($personDb);
                    $text_nr = array_search($personDb->pers_own_code, $pers_own_code);
                    $link_order2 = $link_order[$text_nr];
                    // *** Only needed for PJCS, can't be used in other installations ***
                    //$link_text2[$link_order2] = '<a href="' . $path_tmp . '">' . $name["standard_name"] . '</a> ' . __($link_text[$text_nr]);
                    $link_text2[$link_order2] = '<a href="' . $path_tmp . '">' . $name["standard_name"] . '</a> ' . $link_text[$text_nr];
                }
            }

            // *** Show links ***
            if (isset($link_text2)) {
                for ($i = 1; $i <= $num_rows; $i++) {
                    if (isset($link_text2[$i])) {
                        $text .= $link_text2[$i] . "<br>\n";
                    }
                }
            }
        }
        return $text;
    }

    // *** Alphabet line ***
    public function alphabet()
    {
        global $dbh, $dataDb, $tree_id, $language, $user, $humo_option, $uri_path, $link_cls;
        $text = '';

        // *** Read cache (only used in large family trees) ***
        $cache = '';
        $cache_count = 0;
        $cache_exists = false;
        $cache_check = false; // *** Use cache for large family trees ***
        $cacheqry = $dbh->query("SELECT * FROM humo_settings
            WHERE setting_variable='cache_alphabet' AND setting_tree_id='" . $tree_id . "'");
        $cacheDb = $cacheqry->fetch(PDO::FETCH_OBJ);
        if ($cacheDb) {
            $cache_exists = true;
            $cache_array = explode("|", $cacheDb->setting_value);
            foreach ($cache_array as $cache_line) {
                $cacheDb = json_decode(unserialize($cache_line));

                $cache_check = true;
                $test_time = time() - 10800; // *** 86400 = 1 day, 7200 = 2 hours, 10800 = 3 hours ***
                // TEST LINE
                //$test_time=time()-20; // *** 86400 = 1 day, 7200 = 2 hours, 10800 = 3 hours ***
                if ($cacheDb->time < $test_time) {
                    $cache_check = false;
                } else {
                    $first_character[] = $cacheDb->first_character;
                }
            }
        }

        if ($cache_check == false) {
            $personqry = "SELECT UPPER(LEFT(pers_lastname,1)) as first_character FROM humo_persons
                WHERE pers_tree_id='" . $tree_id . "' AND LEFT(CONCAT(pers_prefix,pers_lastname),1)!=''
                GROUP BY first_character ORDER BY first_character";
            // *** If "van Mons" is selected, also check pers_prefix ***
            if ($user['group_kindindex'] == "j") {
                $personqry = "SELECT UPPER(LEFT(CONCAT(pers_prefix,pers_lastname),1)) as first_character FROM humo_persons
                    WHERE pers_tree_id='" . $tree_id . "' AND LEFT(CONCAT(pers_prefix,pers_lastname),1)!=''
                    GROUP BY first_character ORDER BY first_character";
            }

            $person = $dbh->query($personqry);
            $count_first_character = $person->rowCount();
            while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
                // *** Cache: only use cache if there are > 5.000 persons in database ***
                if (isset($dataDb->tree_persons) && $dataDb->tree_persons > 5000) {
                    $personDb->time = time(); // *** Add linux time to array ***
                    if ($cache !== '' && $cache !== '0') {
                        $cache .= '|';
                    }
                    $cache .= serialize(json_encode($personDb));
                    $cache_count++;
                }

                $first_character[] = $personDb->first_character;
            }

            // *** Add or renew cache in database (only if cache_count is valid) ***
            if ($cache && $cache_count == $count_first_character) {
                if ($cache_exists) {
                    $sql = "UPDATE humo_settings SET
                        setting_variable='cache_alphabet', setting_value='" . safe_text_db($cache) . "'
                        WHERE setting_tree_id='" . safe_text_db($tree_id) . "'";
                    $dbh->query($sql);
                } else {
                    $sql = "INSERT INTO humo_settings SET
                        setting_variable='cache_alphabet', setting_value='" . safe_text_db($cache) . "',
                        setting_tree_id='" . safe_text_db($tree_id) . "'";
                    $dbh->query($sql);
                }
            }
        }

        // *** Show character line ***
        if (isset($first_character)) {
            $counter = count($first_character);
            for ($i = 0; $i < $counter; $i++) {
                // TODO use function
                if ($humo_option["url_rewrite"] == "j") {
                    // *** url_rewrite ***
                    // *** $uri_path is generated in header script ***
                    //$path_tmp = $uri_path . 'list_names/' . $tree_id . '/' . $first_character[$i] . '/';
                    $path_tmp = $uri_path . 'list_names/' . $tree_id . '/' . $first_character[$i];
                } else {
                    $path_tmp = 'index.php?page=list_names.php&amp;tree_id=' . $tree_id . '&amp;last_name=' . $first_character[$i];
                }
                $text .= ' <a href="' . $path_tmp . '">' . $first_character[$i] . '</a>';
            }
        }

        $person = "SELECT pers_patronym FROM humo_persons WHERE pers_tree_id='" . $tree_id . "' AND pers_patronym LIKE '_%' AND pers_lastname ='' LIMIT 0,1";
        $personDb = $dbh->query($person);
        if ($personDb->rowCount() > 0) {
            $path_tmp = $link_cls->get_link($uri_path, 'list', $tree_id, true);
            $path_tmp .= 'index_list=patronym';
            $text .= ' <a href="' . $path_tmp . '">' . __('Patronyms') . '</a>';
        }

        return $text;
    }

    public function today_in_history($view = 'with_table')
    {
        global $dbh, $dataDb;
        // *** Backwards compatible, value is empty ***
        if ($view == '') {
            $view = 'with_table';
        }

        $today = date('j') . ' ' . strtoupper(date("M"));
        $today2 = '0' . date('j') . ' ' . strtoupper(date("M"));
        $count_privacy = 0;
        $text = '';

        // *** Check user group is restricted sources can be shown ***
        // *** Calculate present date, month and year ***
        $sql = "SELECT * FROM humo_persons WHERE pers_tree_id = :tree_id
            AND (
                substring( pers_birth_date,1,6) = :today OR substring( pers_birth_date, 1,6 ) = :today2
                OR substring( pers_bapt_date,1,6) = :today OR substring( pers_bapt_date, 1,6 ) = :today2
                OR substring( pers_death_date,1,6) = :today OR substring( pers_death_date, 1,6 ) = :today2
            )
            ORDER BY substring(pers_birth_date,-4) DESC
            LIMIT 0,30
        ";
        try {
            $birth_qry = $dbh->prepare($sql);
            $birth_qry->bindValue(':tree_id', $dataDb->tree_id, PDO::PARAM_STR);
            $birth_qry->bindValue(':today', $today, PDO::PARAM_STR);
            $birth_qry->bindValue(':today2', $today2, PDO::PARAM_STR);
            $birth_qry->execute();
        } catch (PDOException $e) {
            //echo $e->getMessage() . "<br/>";
        }

        // *** Save results in an array, so it's possible to order the results by date ***
        while ($record = $birth_qry->fetch(PDO::FETCH_OBJ)) {
            $person_cls = new PersonCls($record);
            $name = $person_cls->person_name($record);
            if (!$person_cls->privacy) {
                if (trim(substr($record->pers_birth_date, 0, 6)) === $today || substr($record->pers_birth_date, 0, 6) === $today2) {
                    //$history['order'][]=substr($record->pers_birth_date,-4);
                    // *** First order birth, using C ***
                    $history['order'][] = 'C' . substr($record->pers_birth_date, -4);
                    if ($view == 'with_table') {
                        $history['date'][] = '<td>' . date_place($record->pers_birth_date, '') . '</td><td>' . __('born') . '</td>';
                    } else {
                        $history['item'][] = __('born');
                        $history['date'][] = date_place($record->pers_birth_date, '');
                    }
                } elseif (trim(substr($record->pers_bapt_date, 0, 6)) === $today || substr($record->pers_bapt_date, 0, 6) === $today2) {
                    // *** Second order baptise, using B ***
                    $history['order'][] = 'B' . substr($record->pers_bapt_date, -4);
                    if ($view == 'with_table') {
                        $history['date'][] = '<td>' . date_place($record->pers_bapt_date, '') . '</td><td>' . __('baptised') . '</td>';
                    } else {
                        $history['item'][] = __('baptised');
                        $history['date'][] = date_place($record->pers_bapt_date, '');
                    }
                } else {
                    // *** Third order death, using A ***
                    $history['order'][] = 'A' . substr($record->pers_death_date, -4);
                    if ($view == 'with_table') {
                        $history['date'][] = '<td>' . date_place($record->pers_death_date, '') . '</td><td>' . __('died') . '</td>';
                    } else {
                        $history['item'][] = __('died');
                        $history['date'][] = date_place($record->pers_death_date, '');
                    }
                }

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                $url = $person_cls->person_url2($record->pers_tree_id, $record->pers_famc, $record->pers_fams, $record->pers_gedcomnumber);

                $history['name'][] = '<td><a href="' . $url . '">' . $name["standard_name"] . '</a></td>';
            } else {
                $count_privacy++;
            }
        }

        // *** Use scrollbar for long list ***
        $text .= '<div style="max-height:200px; overflow-x: auto;">';
        if ($view == 'with_table') {

            // test Bootstrap responsive
            //$text.='<div class="table-responsive">';

            $text .= '<table class="table table-sm nametbl">';

            $text .= '<thead class="table-primary">';
            $text .= '<tr>';
            $text .= '<th>' . __('Date') . '</th><th>' . __('Event') . '</th><th>' . __('Name') . '</th>';
            $text .= '</tr>';
            $text .= '</thead>';

            if (isset($history['date'])) {
                array_multisort($history['order'], SORT_DESC, $history['date'], $history['name']);

                for ($i = 0; $i <= count($history['date']) - 1; $i++) {
                    $text .= '<tr>';
                    $text .= $history['date'][$i];
                    $text .= $history['name'][$i];
                    $text .= '</tr>';
                }
            }

            if ($count_privacy !== 0) {
                $text .= '<tr><td colspan="3">' . $count_privacy . __(' persons are not shown due to privacy settings') . '</td></tr>';
            }
            $text .= '</table>';

            //test bootstrap
            //$text.='</div>';
        } else {
            // *** Show history list without table ***
            if (isset($history['date'])) {
                array_multisort($history['order'], SORT_DESC, $history['date'], $history['name'], $history['item']);
                $item = '';
                for ($i = 0; $i <= count($history['date']) - 1; $i++) {
                    if ($item == '') {
                        $item = $history['item'][$i];
                        $text .= '<b>' . ucfirst($history['item'][$i]) . ' ' . substr($history['date'][$i], 0, -4) . '</b><br>';
                    }
                    if ($item != $history['item'][$i]) {
                        $item = $history['item'][$i];
                        $text .= '<b>' . ucfirst($history['item'][$i]) . ' ' . substr($history['date'][$i], 0, -4) . '</b><br>';
                    }

                    //$text.=$history['date'][$i].' ';
                    $text .= substr($history['date'][$i], -4) . ' ';
                    $text .= $history['name'][$i] . '<br>';
                }
            }
            if ($count_privacy !== 0) {
                $text .= $count_privacy . __(' persons are not shown due to privacy settings');
            }
        }
        return $text . '</div>';
    }

    // *** Show bootstrap slideshow ***
    public function show_slideshow(): void
    {
?>

        <div id="carouselExampleAutoplaying" class="carousel slide" data-bs-ride="carousel" style="margin-top: -10px;">
            <div class="carousel-inner">
                <?php
                for ($i = 1; $i <= 4; $i++) {
                    $slideshow = explode('|', $this->humo_option["slideshow_0" . $i]);
                    if ($slideshow[0] && file_exists($slideshow[0])) {
                ?>
                        <div class="carousel-item <?= $i == 1 ? 'active' : ''; ?>">
                            <img src="<?= $slideshow[0]; ?>" class="d-block w-100" alt="<?= $slideshow[1]; ?>">

                            <div class="carousel-caption d-none d-md-block pb-0">
                                <h5 style="background: rgba(0, 0, 0, 0.4); color: #fff;"><?= $slideshow[1]; ?></h5>
                            </div>
                        </div>
                    <?php } else { ?>
                        <div class="carousel-item <?= $i == 1 ? 'active' : ''; ?>">
                            <img src="images/missing-image_large.jpg" height="174" width="946" class="d-block w-100" alt="<?= 'Missing image ' . $i; ?>">

                            <div class="carousel-caption d-none d-md-block">
                                <h5 style="background: rgba(0, 0, 0, 0.4); color: #fff;"><?= 'Missing image ' . $i; ?></h5>
                            </div>
                        </div>
                <?php
                    }
                }
                ?>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="prev">
                <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Previous</span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#carouselExampleAutoplaying" data-bs-slide="next">
                <span class="carousel-control-next-icon" aria-hidden="true"></span>
                <span class="visually-hidden">Next</span>
            </button>
        </div>

<?php
    }
}

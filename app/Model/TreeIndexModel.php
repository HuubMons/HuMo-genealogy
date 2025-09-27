<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use Genealogy\Include\BotDetector;
use Genealogy\Include\DatePlace;
use Genealogy\Include\MediaPath;
use Genealogy\Include\PersonLink;
use Genealogy\Include\PersonName;
use Genealogy\Include\PersonPrivacy;
use Genealogy\Include\ProcessLinks;
use Genealogy\Include\SafeTextShow;
use Genealogy\Include\ShowTreeDate;
use Genealogy\Include\ShowTreeText;
use PDO;
use PDOException;

class TreeIndexModel extends BaseModel
{
    private $personLink;
    private $processLinks;

    // This class could be called from tree_index.php.
    public function __construct($config)
    {
        parent::__construct($config);

        $this->personLink = new PersonLink();
        $this->processLinks = new ProcessLinks();
    }

    public function show_tree_index()
    {
        global $tree_prefix_quoted, $selected_language, $language;

        $botDetector = new BotDetector();
        $directionMarkers = new \Genealogy\Include\DirectionMarkers($language["dir"]);
        $showTreeText = new ShowTreeText();

        // *** Option to only index CMS page for bots ***
        if ($botDetector->isBot() && $this->humo_option["searchengine_cms_only"] == 'y') {
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

            $path_tmp = $this->processLinks->get_link($this->uri_path, 'login');
            $temp .= '<h2><a href="' . $path_tmp . '">' . __('Select another family tree, or login for the selected family tree.') . '</a></h2>';

            $item_array[0]['position'] = 'center';
            $item_array[0]['header'] = '';
            $item_array[0]['item'] = $temp;

            $item_array[1]['position'] = 'right';
            $item_array[1]['header'] = '';
            $item_array[1]['item'] = '';
        }
        // *** One name study page ***
        elseif ($this->humo_option["one_name_study"] != 'n') {
            $item_array[0]['position'] = 'center';
            $item_array[0]['header'] = __('One Name Study of the name');
            $item_array[0]['item'] = '<span style="font-weight:bold;font-size:150%">' . $this->humo_option["one_name_thename"] . '</span>';

            // *** Right column: search module ***
            $item_array[1]['position'] = 'right';
            $item_array[1]['header'] = __('Search');
            $item_array[1]['item'] = $this->search_box();
        }
        // *** Standard family tree template page ***
        else {
            $datasql = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='template_homepage' ORDER BY setting_order");
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
                    $data2sql = $this->dbh->query("SELECT * FROM humo_trees ORDER BY tree_order");
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

                //*** Today in history ***
                if ($module_item[$i] == 'history') {
                    $header = __('Today in history');
                    $temp .= $this->today_in_history($module_option_1[$i]);
                }

                // *** Alphabet line ***
                if ($module_item[$i] == 'alphabet') {
                    //*** Find first first_character of last name ***
                    $header = __('Surnames Index');
                    $temp .= $this->alphabet() . $directionMarkers->dirmark2;
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
                        $temp .= $directionMarkers->dirmark2;
                    }

                    // *** Owner genealogy ***
                    $temp .= $this->owner();

                    // *** Prepare mainmenu text and source ***
                    $treetext = $showTreeText->show_tree_text($this->selectedFamilyTree->tree_id, $selected_language);

                    // *** Show mainmenu text ***
                    $mainmenu_text = $treetext['mainmenu_text'];
                    if ($mainmenu_text != '') {
                        $temp .= '<br><br>' . nl2br($mainmenu_text) . $directionMarkers->dirmark2;
                    }

                    // *** Show mainmenu source ***
                    $mainmenu_source = $treetext['mainmenu_source'];
                    if ($mainmenu_source != '') {
                        $temp .= '<br><br>' . nl2br($mainmenu_source) . $directionMarkers->dirmark2;
                    }
                }

                // *** Search ***
                if ($module_item[$i] == 'search') {
                    $header = __('Search');
                    if (!$botDetector->isBot()) {
                        $temp .= $this->search_box();
                    }
                }

                // *** Random photo ***
                if ($module_item[$i] == 'random_photo') {
                    $header = __('Random photo');
                    if (!$botDetector->isBot()) {
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
                    $page_qry = $this->dbh->query("SELECT * FROM humo_cms_pages WHERE page_id='" . $module_option_1[$i] . "' AND page_status!=''");
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
                    $temp .= file_get_contents($codefile . '?language=' . $selected_language . '&treeid=' . $this->tree_id);
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
    public function selected_family_tree(): string
    {
        global $num_rows, $selected_language;

        $showTreeText = new ShowTreeText();

        $text = '';
        if ($num_rows > 1) {
            $text .= __('Selected family tree') . ': ';
        }
        // *** Variable $treetext_name used from menu.php ***
        $treetext = $showTreeText->show_tree_text($_SESSION['tree_id'], $selected_language);
        return $text . $treetext['name'];
    }

    // *** List family trees ***
    public function tree_list($datasql): string
    {
        global $selected_language;

        $showTreeText = new ShowTreeText();

        $text = '';
        while ($familytree = $datasql->fetch(PDO::FETCH_OBJ)) {
            // *** Check is family tree is shown or hidden for user group ***
            $hide_tree_array = explode(";", $this->user['group_hide_trees']);
            if (!in_array($familytree->tree_id, $hide_tree_array)) {
                $treetext = $showTreeText->show_tree_text($familytree->tree_id, $selected_language);
                $treetext_name = $treetext['name'];

                // *** Name family tree ***
                if ($familytree->tree_prefix == 'EMPTY') {
                    // *** Show empty line ***
                    $tree_name = '';
                } elseif (isset($_SESSION['tree_id']) && $_SESSION['tree_id'] == $familytree->tree_id) {
                    $tree_name = '<span class="tree_link">' . $treetext_name . '</span>';
                } else {
                    $path_tmp = $this->processLinks->get_link($this->uri_path, 'tree_index', $familytree->tree_id);
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
    public function tree_data(): string
    {
        $showTreeDate = new ShowTreeDate();

        return __('Latest update:') . ' ' . $showTreeDate->show_tree_date($this->selectedFamilyTree->tree_date, true) . ', ' . $this->selectedFamilyTree->tree_persons . ' ' . __('persons') . ', ' . $this->selectedFamilyTree->tree_families . ' ' . __('families');
    }

    // *** Owner family tree ***
    public function owner(): string
    {
        $tree_owner = '';

        if (isset($this->selectedFamilyTree->tree_owner) && $this->selectedFamilyTree->tree_owner) {
            $tree_owner = __('Owner family tree:') . ' ';
            // *** Show owner e-mail address ***
            if ($this->selectedFamilyTree->tree_email) {
                $path_tmp = $this->humo_option["url_rewrite"] == "j" ? 'mailform.php' : 'index.php?page=mailform';
                $tree_owner .= '<a href="' . $path_tmp . '">' . $this->selectedFamilyTree->tree_owner . "</a>\n";
            } else {
                $tree_owner .= $this->selectedFamilyTree->tree_owner . "\n";
            }
        }
        return $tree_owner;
    }

    //*** Most frequent names ***
    public function last_names($columns, $rows): string
    {
        global $maxcols, $text;

        // MAIN SETTINGS
        $maxcols = 2; // number of name&nr colums in table. For example 3 means 3x name col + nr col
        if ($columns) {
            $maxcols = $columns;
        }

        $maxnames = 8;
        if ($rows) {
            $maxnames = $rows * $maxcols;
        }

        // *** nametbl = used for javascript to show graphical lightgray bar to show number of persons ***
        $text = '<table class="table table-sm nametbl">';

        $text .= '<thead class="table-primary">';
        $text .= '<tr>';
        $col_width = ((round(100 / $maxcols)) - 6) . "%";
        for ($x = 1; $x < $maxcols; $x++) {
            $text .= '<th style="width:' . $col_width . ';">' . __('Surname') . '</th><th>' . __('Total') . '</th>';
        }
        $text .= '<th style="width:' . $col_width . ';">' . __('Surname') . '</th><th>' . __('Total') . '</th>';
        $text .= '</tr>';
        $text .= '</thead>';

        $baseperc = $this->last_names_array($maxnames);   // displays the table and sets the $baseperc (= the name with highest frequency that will be 100%)

        $path = $this->humo_option["url_rewrite"] == "j" ? 'statistics' : 'index.php?page=statistics';
        $text .= '<tr><td colspan="' . ($maxcols * 2) . '" class="table-active"><a href="' . $path . '">' . __('More statistics') . '</a></td></tr>';
        $text .= '</table>';

        // Show gray bar in name box. Graphical indication of number of names.
        $text .= '
        <script>var baseperc = ' . $baseperc . ';</script>
        <script src="assets/js/stats_graphical_bar.js"></script>';

        return $text;
    }

    function last_names_array($max)
    {
        global $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $maxcols, $text;

        // *** Read cache (only used in large family trees) ***
        $cache = '';
        $cache_count = 0;
        $cache_exists = false;
        $cache_check = false; // *** Use cache for large family trees ***
        $cacheqry = $this->dbh->query("SELECT * FROM humo_settings
            WHERE setting_variable='cache_surnames' AND setting_tree_id='" . $this->tree_id . "'");
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
            // *** Renewed query because of ONLY_FULL_GROUP_BY setting in MySQL 5.7 (otherwise query will stop) ***
            $personqry = "SELECT pers_lastname, pers_prefix, count(pers_lastname) as count_last_names
                FROM humo_persons
                WHERE pers_tree_id='" . $this->tree_id . "' AND pers_lastname NOT LIKE ''
                GROUP BY pers_lastname, pers_prefix ORDER BY count_last_names DESC LIMIT 0," . $max;
            $person = $this->dbh->query($personqry);

            while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
                // *** Cache: only use cache if there are > 5.000 persons in database ***
                if (isset($this->selectedFamilyTree->tree_persons) && $this->selectedFamilyTree->tree_persons > 5000) {
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
                        setting_variable = :setting_variable,
                        setting_value = :setting_value
                        WHERE setting_tree_id = :setting_tree_id";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->execute([
                        ':setting_variable' => 'cache_surnames',
                        ':setting_value' => $cache,
                        ':setting_tree_id' => $this->tree_id
                    ]);
                } else {
                    // *** Add new cache item ***
                    $sql = "INSERT INTO humo_settings (setting_variable, setting_value, setting_tree_id)
                        VALUES (:setting_variable, :setting_value, :setting_tree_id)";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->execute([
                        ':setting_variable' => 'cache_surnames',
                        ':setting_value' => $cache,
                        ':setting_tree_id' => $this->tree_id
                    ]);
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
                    $this->last_names_tablerow($i + ($row * $n), true); // last col
                } else {
                    $this->last_names_tablerow($i + ($row * $n)); // other cols
                }
            }
            $text .= '</tr>';
        }
        if (isset($freq_count_last_names)) {
            return $freq_count_last_names[0];
        }
        return null;
    }

    function last_names_tablerow($nr, $lastcol = false)
    {
        // displays one set of name & nr column items in the row
        // $nr is the array number of the name set created in function last_names
        // if $lastcol is set to true, the last right border of the number column will not be made thicker (as the other ones are to distinguish between the name&nr sets)
        global $freq_last_names, $freq_pers_prefix, $freq_count_last_names, $text;

        $processLinks = new ProcessLinks($this->uri_path);
        $path_tmp = $processLinks->get_link($this->uri_path, 'list', $this->tree_id, true);
        $text .= '<td class="namelst">';
        if (isset($freq_last_names[$nr])) {
            $top_pers_lastname = '';
            if ($freq_pers_prefix[$nr]) {
                $top_pers_lastname = str_replace("_", " ", $freq_pers_prefix[$nr]);
            }
            $top_pers_lastname .= $freq_last_names[$nr];
            if ($this->user['group_kindindex'] == "j") {
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
            // no thick border
            $text .= '<td class="namenr" style="text-align:center">';
        }

        if (isset($freq_last_names[$nr])) {
            $text .= $freq_count_last_names[$nr];
        } else {
            $text .= '~';
        }
        $text .= '</td>';
    }

    // *** Search field ***
    public function search_box(): string
    {
        $safeTextShow = new SafeTextShow();

        $text = '';

        // *** Reset search field if a new genealogy is selected ***
        /*
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
        */
        /*
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
        */
        $search_database = 'tree_selected';
        //if (isset($_SESSION["save_search_database"])) {
        //    $search_database = $_SESSION["save_search_database"];
        //}

        $path_tmp = $this->processLinks->get_link($this->uri_path, 'list', $this->tree_id, false);
        $text .= '<form method="post" action="' . $path_tmp . '">';

        $text .= '<p>';
        if ($this->humo_option['one_name_study'] == 'n') {
            $text .= __('Enter name or part of name') . '<br>';
        } else {
            $text .= __('Enter private name') . '<br>';
        }
        //$text.='<span style="font-size:10px;">"John Jones", "Jones John", "John of Jones", "of Jones, John", "Jones, John of", "Jones of, John"</span>';

        $text .= '<input type="hidden" name="index_list" value="quicksearch">';
        $quicksearch = '';
        if (isset($_POST['quicksearch'])) {
            //$quicksearch=htmlentities($_POST['quicksearch'],ENT_QUOTES,'UTF-8');
            $quicksearch = $safeTextShow->safe_text_show($_POST['quicksearch']);
            $_SESSION["save_quicksearch"] = $quicksearch;
        }
        if (isset($_SESSION["save_quicksearch"])) {
            $quicksearch = $_SESSION["save_quicksearch"];
        }
        $text .= '<input type="text" class="form-control form-control-sm" name="quicksearch" placeholder="' . __('Name') . '" value="' . $quicksearch . '" size="30" pattern=".{3,}" title="' . __('Minimum: 3 characters.') . '"></p>';

        // Check if there are multiple family trees.
        $datasql2 = $this->dbh->query("SELECT * FROM humo_trees");
        $num_rows2 = $datasql2->rowCount();
        if ($num_rows2 > 1 && $this->humo_option['one_name_study'] == 'n') {
            $text .= '<select name="select_trees" aria-label="' . __('Select family tree') . '" class="form-select form-select-sm">';
            $text .= '<option value="tree_selected">' . __('Selected family tree') . '</option>';

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
        if ($num_rows2 > 1 && $this->humo_option['one_name_study'] == 'y') {
            $text .= '<input type="hidden" name="search_database" value="all_trees">';
        }
        $text .= '<p><button type="submit" class="btn btn-success btn-sm my-2">' . __('Search') . '</button></p>';
        $path_tmp = $this->processLinks->get_link($this->uri_path, 'list', $this->tree_id, true);
        $path_tmp .= 'adv_search=1&index_list=search';
        $text .= '<a href="' . $path_tmp . '"><img src="images/advanced-search.jpg" width="25" alt="' . __('Advanced search') . '" title="' . __('Advanced search') . '"> ' . __('Advanced search') . '</a>';
        return $text . "</form>";
    }

    // *** Random photo ***
    public function random_photo(): string
    {
        $personName = new PersonName();
        $personPrivacy = new PersonPrivacy();
        $datePlace = new DatePlace();
        $mediaPath = new MediaPath;

        // adding static table for displayed photos storage
        static $temp_pic_names_table = [];

        $text = '';
        // characters limit for rounding text below photo 100 looks good at photos inline, 200 should be good for lightbox desc
        //this for text without lightbox
        $char_limit = 100;
        //this for lightbox
        $char_limit2 = 400;

        $tree_pict_path = $this->selectedFamilyTree->tree_pict_path;
        if (substr($tree_pict_path, 0, 1) === '|') {
            $tree_pict_path = 'media/';
        }

        // *** Loop through pictures and find first available picture without privacy filter ***
        // i added also family kind photos
        $qry = "SELECT e.*, l.location_location AS event_place
            FROM humo_events e
            LEFT JOIN humo_location l ON e.event_place_id = l.location_id
            WHERE e.event_tree_id='" . $this->tree_id . "' 
            AND e.event_kind='picture' 
            AND (e.event_connect_kind='person' OR e.event_connect_kind='family')  
            AND e.event_connect_id NOT LIKE ''
            ORDER BY RAND()";
        $picqry = $this->dbh->query($qry);

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
                    $personmnDb = $this->db_functions->get_person_with_id($picqryDb->event_person_id);
                    $man_privacy = $personPrivacy->get_privacy($personmnDb);
                    if (!$man_privacy) {
                        $is_privacy = false;

                        $name = $personName->get_person_name($personmnDb, $man_privacy);
                        $link_text = $name["standard_name"];

                        $url = $this->personLink->get_person_link($personmnDb);
                    }
                } elseif ($pic_conn_kind == 'family') {
                    $picqryDb2 = $this->db_functions->get_family_with_id($picqryDb->event_relation_id);

                    $personmnDb2 = $this->db_functions->get_person($picqryDb2->fam_man);
                    $man_privacy = $personPrivacy->get_privacy($personmnDb2);

                    $personmnDb3 = $this->db_functions->get_person($picqryDb2->fam_woman);
                    $woman_privacy = $personPrivacy->get_privacy($personmnDb3);

                    // *** Only use this picture if both man and woman have disabled privacy options ***
                    if (!$man_privacy && !$woman_privacy) {
                        $is_privacy = false;

                        $name = $personName->get_person_name($personmnDb2, $man_privacy);
                        $man_name = $name["standard_name"];

                        $name = $personName->get_person_name($personmnDb3, $woman_privacy);
                        $woman_name =  $name["standard_name"];

                        $link_text = __('Family') . ': ' . $man_name . ' &amp; ' . $woman_name;

                        if ($this->humo_option["url_rewrite"] == "j") {
                            $url = 'family/' . $picqryDb->event_tree_id . '/' . $picqryDb->event_connect_id;
                        } else {
                            $url = 'index.php?page=family&tree_id=' . $picqryDb->event_tree_id . '&id=' . $picqryDb->event_connect_id;
                        }
                        // TODO use function to build link:
                        //$vars['pers_family'] = $familyDb->stat_gedcom_fam;
                        //$link = $this->processLinks->get_link('../', 'family', $familyDb->tree_id, false, $vars);
                        //echo '<a href="' . $link . '">' . __('Family') . ': </a>';
                    }
                }

                if (!$is_privacy) {
                    $dateplace = '';
                    if ($picqryDb->event_date || $picqryDb->event_place) {
                        $dateplace = $datePlace->date_place($picqryDb->event_date, $picqryDb->event_place) . '<br>';
                    }
                    $picture_path = $mediaPath->give_media_path($tree_pict_path, $picname);

                    // u can delete this variables if there are some general variables for protocol and domain combined
                    // Get the protocol (HTTP or HTTPS)
                    $text .= '<div style="text-align: center;">';

                    // *** Show picture using GLightbox ***
                    $desc_for_lightbox =  $dateplace . str_replace("&", "&amp;", $picqryDb->event_text);
                    $desc_for_lightbox = (mb_strlen($desc_for_lightbox, "UTF-8") > $char_limit2) ? mb_substr($desc_for_lightbox, 0, $char_limit2, "UTF-8") . '...' : $desc_for_lightbox;

                    $text .= '<a href="' . $picture_path . '" class="glightbox" data-glightbox="description: ' . $desc_for_lightbox . '"><img src="' . $picture_path .
                        '" width="90%" style="border-radius: 5px; box-shadow: 0 4px 8px 0 rgba(0, 0, 0, 0.2), 0 6px 20px 0 rgba(0, 0, 0, 0.19);"></a><br>';

                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $text .= '<a href="' . $url . '">' . $link_text . '</a>';
                    if ($picqryDb->event_text !== '' or $dateplace !== '') {
                        // this code shortens event text below photos to 50 chars and adds '...' if its above 50 chars. Photos with long texts added looks bad...
                        $shortEventText = (mb_strlen($picqryDb->event_text, "UTF-8") > $char_limit) ? mb_substr($picqryDb->event_text, 0, $char_limit, "UTF-8") . '...' : $picqryDb->event_text;
                        $text .= '<br>' . $dateplace . $shortEventText;
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
    public function extra_links(): string
    {
        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();
        $text = '';

        // *** Check if there are extra links ***
        $datasql = $this->dbh->query("SELECT * FROM humo_settings WHERE setting_variable='link'");
        $num_rows = $datasql->rowCount();
        if ($num_rows > 0) {
            while ($data2Db = $datasql->fetch(PDO::FETCH_OBJ)) {
                $item = explode("|", $data2Db->setting_value);
                $pers_own_code[] = $item[0];
                $link_text[] = $item[1];
                $link_order[] = $data2Db->setting_order;
            }
            $person = $this->dbh->query("SELECT pers_id FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_own_code NOT LIKE ''");
            while ($person2Db = $person->fetch(PDO::FETCH_OBJ)) {
                // *** Get person with ID to have all fields available (for privacy check) ***
                $personDb = $this->db_functions->get_person_with_id($person2Db->pers_id);
                if (in_array($personDb->pers_own_code, $pers_own_code)) {
                    // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                    $path_tmp = $this->personLink->get_person_link($personDb);
                    $privacy = $personPrivacy->get_privacy($personDb);
                    $name = $personName->get_person_name($personDb, $privacy);
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
    public function alphabet(): string
    {
        $text = '';

        // *** Read cache (only used in large family trees) ***
        $cache = '';
        $cache_count = 0;
        $cache_exists = false;
        $cache_check = false; // *** Use cache for large family trees ***
        $cacheqry = $this->dbh->query("SELECT * FROM humo_settings
            WHERE setting_variable='cache_alphabet' AND setting_tree_id='" . $this->tree_id . "'");
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
                WHERE pers_tree_id='" . $this->tree_id . "' AND LEFT(CONCAT(pers_prefix,pers_lastname),1)!=''
                GROUP BY first_character ORDER BY first_character";
            // *** If "van Mons" is selected, also check pers_prefix ***
            if ($this->user['group_kindindex'] == "j") {
                $personqry = "SELECT UPPER(LEFT(CONCAT(pers_prefix,pers_lastname),1)) as first_character FROM humo_persons
                    WHERE pers_tree_id='" . $this->tree_id . "' AND LEFT(CONCAT(pers_prefix,pers_lastname),1)!=''
                    GROUP BY first_character ORDER BY first_character";
            }

            $person = $this->dbh->query($personqry);
            $count_first_character = $person->rowCount();
            while ($personDb = $person->fetch(PDO::FETCH_OBJ)) {
                // *** Cache: only use cache if there are > 5.000 persons in database ***
                if (isset($this->selectedFamilyTree->tree_persons) && $this->selectedFamilyTree->tree_persons > 5000) {
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
                        setting_variable = :setting_variable,
                        setting_value = :setting_value
                        WHERE setting_tree_id = :setting_tree_id";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->execute([
                        ':setting_variable' => 'cache_alphabet',
                        ':setting_value' => $cache,
                        ':setting_tree_id' => $this->tree_id
                    ]);
                } else {
                    $sql = "INSERT INTO humo_settings (setting_variable, setting_value, setting_tree_id)
                        VALUES (:setting_variable, :setting_value, :setting_tree_id)";
                    $stmt = $this->dbh->prepare($sql);
                    $stmt->execute([
                        ':setting_variable' => 'cache_alphabet',
                        ':setting_value' => $cache,
                        ':setting_tree_id' => $this->tree_id
                    ]);
                }
            }
        }

        // *** Show character line ***
        if (isset($first_character)) {
            $counter = count($first_character);
            for ($i = 0; $i < $counter; $i++) {
                // TODO use function
                if ($this->humo_option["url_rewrite"] == "j") {
                    // *** url_rewrite ***
                    // *** $this->uri_path is generated in header script ***
                    //$path_tmp = $this->uri_path . 'list_names/' . $this->tree_id . '/' . $first_character[$i] . '/';
                    $path_tmp = $this->uri_path . 'list_names/' . $this->tree_id . '/' . $first_character[$i];
                } else {
                    $path_tmp = 'index.php?page=list_names.php&amp;tree_id=' . $this->tree_id . '&amp;last_name=' . $first_character[$i];
                }
                $text .= ' <a href="' . $path_tmp . '">' . $first_character[$i] . '</a>';
            }
        }

        $person = "SELECT pers_patronym FROM humo_persons WHERE pers_tree_id='" . $this->tree_id . "' AND pers_patronym LIKE '_%' AND pers_lastname ='' LIMIT 0,1";
        $personDb = $this->dbh->query($person);
        if ($personDb->rowCount() > 0) {
            $path_tmp = $this->processLinks->get_link($this->uri_path, 'list', $this->tree_id, true);
            $path_tmp .= 'index_list=patronym';
            $text .= ' <a href="' . $path_tmp . '">' . __('Patronyms') . '</a>';
        }

        return $text;
    }

    public function today_in_history($view = 'with_table'): string
    {
        $personPrivacy = new PersonPrivacy();
        $personName = new PersonName();
        $datePlace = new DatePlace();

        // *** Backwards compatible, value is empty ***
        if ($view == '') {
            $view = 'with_table';
        }

        $today = date('j') . ' ' . strtoupper(date("M"));
        $today2 = '0' . date('j') . ' ' . strtoupper(date("M"));
        $count_privacy = 0;
        $text = '';

        $sql = "SELECT p.*, e.event_kind, e.event_date_day, e.event_date_year, e.event_date_month
            FROM humo_persons p
            LEFT JOIN humo_events e ON p.pers_id = e.event_person_id
            AND e.event_tree_id = p.pers_tree_id
            AND e.event_connect_kind = 'person'
            WHERE p.pers_tree_id = :tree_id
            AND (
                (e.event_kind = 'birth' AND e.event_date_month = :month AND e.event_date_day = :day)
                OR
                (e.event_kind = 'baptism' AND e.event_date_month = :month AND e.event_date_day = :day)
                OR
                (e.event_kind = 'death' AND e.event_date_month = :month AND e.event_date_day = :day)
            )
            ORDER BY e.event_date_year DESC
            LIMIT 0,30
        ";
        try {
            $birth_qry = $this->dbh->prepare($sql);
            $birth_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_INT);
            $birth_qry->bindValue(':month', date('n'), PDO::PARAM_INT);
            $birth_qry->bindValue(':day', date('j'), PDO::PARAM_INT);
            $birth_qry->execute();
        } catch (PDOException $e) {
            //echo $e->getMessage() . "<br/>";
        }

        // *** Save results in an array, so it's possible to order the results by date ***
        while ($record2 = $birth_qry->fetch(PDO::FETCH_OBJ)) {
            // *** Get all data from person ***
            $record = $this->db_functions->get_person_with_id($record2->pers_id);

            //echo $record2->event_kind.' '.$record2->event_date_day.' '.$record2->event_date_month.' '.$record2->event_date_year.'<br><br><br>';

            $privacy = $personPrivacy->get_privacy($record);
            $name = $personName->get_person_name($record, $privacy);
            //echo $record->pers_id.'!!!<br><br><br>';
            if (!$privacy) {
                if (trim(substr($record->pers_birth_date, 0, 6)) === $today || substr($record->pers_birth_date, 0, 6) === $today2) {
                    // *** First order birth, using C ***
                    $history['order'][] = 'C' . substr($record->pers_birth_date, -4);
                    if ($view == 'with_table') {
                        $history['date'][] = '<td>' . $datePlace->date_place($record->pers_birth_date, '') . '</td><td>' . __('born') . '</td>';
                    } else {
                        $history['item'][] = __('born');
                        $history['date'][] = $datePlace->date_place($record->pers_birth_date, '');
                    }
                } elseif (trim(substr($record->pers_bapt_date, 0, 6)) === $today || substr($record->pers_bapt_date, 0, 6) === $today2) {
                    // *** Second order baptise, using B ***
                    $history['order'][] = 'B' . substr($record->pers_bapt_date, -4);
                    if ($view == 'with_table') {
                        $history['date'][] = '<td>' . $datePlace->date_place($record->pers_bapt_date, '') . '</td><td>' . __('baptised') . '</td>';
                    } else {
                        $history['item'][] = __('baptised');
                        $history['date'][] = $datePlace->date_place($record->pers_bapt_date, '');
                    }
                } else {
                    // *** Third order death, using A ***
                    $history['order'][] = 'A' . substr($record->pers_death_date, -4);
                    if ($view == 'with_table') {
                        $history['date'][] = '<td>' . $datePlace->date_place($record->pers_death_date, '') . '</td><td>' . __('died') . '</td>';
                    } else {
                        $history['item'][] = __('died');
                        $history['date'][] = $datePlace->date_place($record->pers_death_date, '');
                    }
                }

                // *** Person url example (optional: "main_person=I23"): http://localhost/humo-genealogy/family/2/F10?main_person=I23/ ***
                $url = $this->personLink->get_person_link($record);
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

<?php
class SourcesModel
{
    private $dbh;
    private $count_sources, $all_sources, $item, $source_search, $sort_desc, $order_sources;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function get_source_search()
    {
        return $this->source_search;
    }

    public function get_sort_desc()
    {
        return $this->sort_desc;
    }

    public function get_order_sources()
    {
        return $this->order_sources;
    }

    public function listSources($dbh, $tree_id, $user, $humo_option)
    {
        // *** Search ***
        $this->source_search = '';
        if (isset($_POST['source_search'])) {
            $this->source_search = safe_text_db($_POST['source_search']);
        }
        if (isset($_GET['source_search'])) {
            $this->source_search = safe_text_db($_GET['source_search']);
        }

        $desc_asc = " ASC ";
        $this->sort_desc = 0;
        if (isset($_GET['sort_desc'])) {
            $desc_asc = " ASC ";
            $this->sort_desc = 0;
            if ($_GET['sort_desc'] == 1) {
                $desc_asc = " DESC ";
                $this->sort_desc = 1;
            }
        }

        $this->order_sources = 'title';
        if (isset($_GET['order_sources'])) {
            if ($_GET['order_sources'] == 'title') $this->order_sources = 'title';
            if ($_GET['order_sources'] == 'date') $this->order_sources = 'date';
            if ($_GET['order_sources'] == 'place') $this->order_sources = 'place';
        }
        if ($this->order_sources == "title") {
            // *** Default querie: order by title ***
            $querie = "SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "'";
            // *** Check user group is restricted sources can be shown ***
            if ($user['group_show_restricted_source'] == 'n') {
                $querie .= " AND (source_status!='restricted' OR source_status IS NULL)";
            }

            //	if ($this->source_search!=''){ $querie.=" AND (source_title LIKE '%".safe_text_db($this->source_search)."%')"; }
            // *** Only search in source_text if source_title isn't used ***
            if ($this->source_search != '') {
                $querie .= " AND (source_title LIKE '%" . safe_text_db($this->source_search) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($this->source_search) . "%') )";
            }

            $querie .= " ORDER BY IF (source_title!='',source_title,source_text)" . $desc_asc; // *** Order by title if exists, else use text ***
        }
        if ($this->order_sources == "date") {
            // *** Check user group is restricted sources can be shown ***
            $querie = "SELECT source_status, source_id, source_gedcomnr, source_title, source_text, source_date, source_place,
                CONCAT(right(source_date,4),
                    date_format( str_to_date( substring(source_date,-8,3),'%b' ) ,'%m'),
                    date_format( str_to_date( substring(source_date,-11,2),'%d' ) ,'%d'))
                    as year
                FROM humo_sources WHERE source_tree_id='" . $tree_id . "'";
            if ($user['group_show_restricted_source'] == 'n') {
                $querie .= " AND (source_status!='restricted' OR source_status IS NULL)";
            }

            if ($this->source_search != '') {
                $querie .= " AND (source_title LIKE '%" . safe_text_db($this->source_search) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($this->source_search) . "%') )";
            }

            $querie .= " ORDER BY year" . $desc_asc;
        }
        if ($this->order_sources == "place") {
            $querie = "SELECT * FROM humo_sources WHERE source_tree_id='" . $tree_id . "'";
            // *** Check user group is restricted sources can be shown ***
            if ($user['group_show_restricted_source'] == 'n') {
                $querie .= " AND (source_status!='restricted' OR source_status IS NULL)";
            }

            if ($this->source_search != '') {
                $querie .= " AND (source_title LIKE '%" . safe_text_db($this->source_search) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($this->source_search) . "%') )";
            }

            $querie .= " ORDER BY source_place" . $desc_asc;
        }

        // *** Pages ***
        $this->item = 0;
        if (isset($_GET['item']) and is_numeric($_GET['item'])) $this->item = $_GET['item'];
        $this->count_sources = $humo_option['show_persons'];    // *** Number of lines to show ***

        // *** All sources query ***
        $this->all_sources = $dbh->query($querie);
        $source = $dbh->query($querie . " LIMIT " . safe_text_db($this->item) . "," . $this->count_sources);

        $sources = $source->fetchAll(PDO::FETCH_OBJ);

        return $sources;
    }

    function line_pages($tree_id, $link_cls, $uri_path)
    {
        $path = $link_cls->get_link($uri_path, 'sources', $tree_id, true);
        $line_pages = __('Page');

        $start = 0;
        if (isset($_GET["start"]) and is_numeric($_GET["start"])) $start = $_GET["start"];

        // "<="
        if ($start > 1) {
            $start2 = $start - 20;
            $calculated = ($start - 2) * $this->count_sources;
            $line_pages .= '<a href="' . $path . 'start=' . $start2 . '&amp;item=' . $calculated;
            if (isset($_GET['order_sources'])) {
                $line_pages .=  '&amp;order_sources=' . $_GET['order_sources'] . '&sort_desc=' . $this->sort_desc;
            }
            if ($this->source_search != '') {
                $line_pages .=  '&amp;source_search=' . $this->source_search;
            }
            $line_pages .=  '">&lt;= </a>';
        }
        if ($start <= 0) {
            $start = 1;
        }

        // 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
        for ($i = $start; $i <= $start + 19; $i++) {
            $calculated = ($i - 1) * $this->count_sources;
            if ($calculated < $this->all_sources->rowCount()) {
                if ($this->item == $calculated) {
                    $line_pages .=  " <b>$i</b>";
                } else {
                    $line_pages .=  ' <a href="' . $path . 'item=' . $calculated . '&amp;start=' . $start;
                    if (isset($_GET['order_sources'])) $line_pages .= '&amp;order_sources=' . $_GET['order_sources'] . '&sort_desc=' . $this->sort_desc;
                    if ($this->source_search != '') {
                        $line_pages .=  '&amp;source_search=' . $this->source_search;
                    }
                    $line_pages .=  '">' . $i . '</a>';
                }
            }
        }

        // "=>"
        $calculated = ($i - 1) * $this->count_sources;
        if ($calculated < $this->all_sources->rowCount()) {
            $line_pages .=  '<a href="' . $path . 'start=' . $i . '&amp;item=' . $calculated;
            if (isset($_GET['order_sources'])) {
                $line_pages .=  '&amp;order_sources=' . $_GET['order_sources'] . '&sort_desc=' . $this->sort_desc;
            }
            if ($this->source_search != '') {
                $line_pages .=  '&amp;source_search=' . $this->source_search;
            }
            $line_pages .=  '"> =&gt;</a>';
        }

        return $line_pages;
    }
}

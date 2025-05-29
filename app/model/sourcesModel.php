<?php
class SourcesModel extends BaseModel
{
    private $count_sources, $all_sources, $item, $source_search, $sort_desc, $order_sources;

    // *** Added feb 2025 ***
    public function process_variables(): void
    {
        // *** Search ***
        $this->source_search = '';
        if (isset($_POST['source_search'])) {
            $this->source_search = safe_text_db($_POST['source_search']);
        }
        if (isset($_GET['source_search'])) {
            $this->source_search = safe_text_db($_GET['source_search']);
        }

        $this->order_sources = 'title';
        if (isset($_GET['order_sources'])) {
            if ($_GET['order_sources'] == 'title') {
                $this->order_sources = 'title';
            }
            if ($_GET['order_sources'] == 'date') {
                $this->order_sources = 'date';
            }
            if ($_GET['order_sources'] == 'place') {
                $this->order_sources = 'place';
            }
        }

        // *** Pages ***
        $this->item = 0;
        if (isset($_GET['item']) && is_numeric($_GET['item'])) {
            $this->item = $_GET['item'];
        }
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

    public function listSources()
    {
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

        if ($this->order_sources === "title") {
            // *** Default querie: order by title ***
            $querie = "SELECT * FROM humo_sources WHERE source_tree_id='" . $this->tree_id . "'";
            // *** Check user group is restricted sources can be shown ***
            if ($this->user['group_show_restricted_source'] == 'n') {
                $querie .= " AND (source_status!='restricted' OR source_status IS NULL)";
            }

            //	if ($this->source_search!=''){ $querie.=" AND (source_title LIKE '%".safe_text_db($this->source_search)."%')"; }
            // *** Only search in source_text if source_title isn't used ***
            if ($this->source_search != '') {
                $querie .= " AND (source_title LIKE '%" . safe_text_db($this->source_search) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($this->source_search) . "%') )";
            }

            $querie .= " ORDER BY IF (source_title!='',source_title,source_text)" . $desc_asc; // *** Order by title if exists, else use text ***
        }
        if ($this->order_sources === "date") {
            // *** Check user group is restricted sources can be shown ***
            $querie = "SELECT source_status, source_id, source_gedcomnr, source_title, source_text, source_date, source_place,
                CONCAT(right(source_date,4),
                    date_format( str_to_date( substring(source_date,-8,3),'%b' ) ,'%m'),
                    date_format( str_to_date( substring(source_date,-11,2),'%d' ) ,'%d'))
                    as year
                FROM humo_sources WHERE source_tree_id='" . $this->tree_id . "'";
            if ($this->user['group_show_restricted_source'] == 'n') {
                $querie .= " AND (source_status!='restricted' OR source_status IS NULL)";
            }

            if ($this->source_search != '') {
                $querie .= " AND (source_title LIKE '%" . safe_text_db($this->source_search) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($this->source_search) . "%') )";
            }

            $querie .= " ORDER BY year" . $desc_asc;
        }
        if ($this->order_sources === "place") {
            $querie = "SELECT * FROM humo_sources WHERE source_tree_id='" . $this->tree_id . "'";
            // *** Check user group is restricted sources can be shown ***
            if ($this->user['group_show_restricted_source'] == 'n') {
                $querie .= " AND (source_status!='restricted' OR source_status IS NULL)";
            }

            if ($this->source_search != '') {
                $querie .= " AND (source_title LIKE '%" . safe_text_db($this->source_search) . "%' OR (source_title='' AND source_text LIKE '%" . safe_text_db($this->source_search) . "%') )";
            }

            $querie .= " ORDER BY source_place" . $desc_asc;
        }

        // *** Pages ***
        $this->count_sources = $this->humo_option['show_persons'];    // *** Number of lines to show ***

        // *** All sources query ***
        $this->all_sources = $this->dbh->query($querie);

        $source = $this->dbh->query($querie . " LIMIT " . safe_text_db($this->item) . "," . $this->count_sources);
        return $source->fetchAll(PDO::FETCH_OBJ);
    }

    // TODO also used in addressesModel.php
    private function process_link(): string
    {
        $link = '';
        if ($this->order_sources != '') {
            $link .=  '&amp;order_sources=' . $this->order_sources . '&sort_desc=' . $this->sort_desc;
        }
        if ($this->source_search != '') {
            $link .=  '&amp;source_search=' . $this->source_search;
        }
        return $link;
    }

    public function line_pages($link_cls, $uri_path): array
    {
        $path = $link_cls->get_link($uri_path, 'sources', $this->tree_id, true);

        $start = 0;
        if (isset($_GET["start"]) && is_numeric($_GET["start"])) {
            $start = $_GET["start"];
        }

        // "<="
        $data["previous_link"] = '';
        $data["previous_status"] = '';
        if ($start > 1) {
            $start2 = $start - 20;
            $calculated = ($start - 2) * $this->count_sources;
            $data["previous_link"] .= $path . 'start=' . $start2 . '&amp;item=' . $calculated;
            $data["previous_link"] .=  $this->process_link();
        }
        if ($start <= 0) {
            $start = 1;
        }
        if ($start == 1) {
            $data["previous_status"] = 'disabled';
        }

        // 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
        for ($i = $start; $i <= $start + 19; $i++) {
            $calculated = ($i - 1) * $this->count_sources;
            if ($calculated < $this->all_sources->rowCount()) {
                $data["page_nr"][] = $i;

                if ($this->item == $calculated) {
                    $data["page_status"][$i] = 'active';
                } else {
                    $data["page_status"][$i] = '';
                }
                $data["page_link"][$i] =  $path . 'start=' . $start . '&amp;item=' . $calculated;
                $data["page_link"][$i] .=  $this->process_link();
            }
        }

        // "=>"
        $data["next_link"] = '';
        $data["next_status"] = '';
        $calculated = ($i - 1) * $this->count_sources;
        if ($calculated < $this->all_sources->rowCount()) {
            $data["next_link"] .=  $path . 'start=' . $i . '&amp;item=' . $calculated;
            $data["next_link"] .=  $this->process_link();
        } else {
            $data["next_status"] = 'disabled';
        }

        return $data;
    }
}

<?php

namespace Genealogy\App\Model;

use Genealogy\App\Model\BaseModel;
use Genealogy\Include\ProcessLinks;
use PDO;

class SourcesModel extends BaseModel
{
    private $count_sources, $all_sources, $item, $source_search, $sort_desc, $order_sources;

    // *** Added feb 2025 ***
    public function process_variables(): void
    {
        // *** Search ***
        $this->source_search = '';
        if (isset($_POST['source_search'])) {
            $this->source_search = $_POST['source_search'];
        }
        if (isset($_GET['source_search'])) {
            $this->source_search = $_GET['source_search'];
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

        $params = [];
        $where = " WHERE source_tree_id = :tree_id";
        $params[':tree_id'] = $this->tree_id;

        // Restrict sources if needed
        if ($this->user['group_show_restricted_source'] == 'n') {
            $where .= " AND (source_status != 'restricted' OR source_status IS NULL)";
        }

        // Search filter
        if ($this->source_search != '') {
            $where .= " AND (source_title LIKE :search OR (source_title = '' AND source_text LIKE :search))";
            $params[':search'] = '%' . $this->source_search . '%';
        }

        // Order by
        if ($this->order_sources === "title") {
            $order = " ORDER BY IF(source_title != '', source_title, source_text)" . $desc_asc;
            $select = "SELECT * FROM humo_sources";
        } elseif ($this->order_sources === "date") {
            $order = " ORDER BY year" . $desc_asc;
            $select = "SELECT source_status, source_id, source_gedcomnr, source_title, source_text, source_date, source_place,
            CONCAT(
                RIGHT(source_date, 4),
                DATE_FORMAT(STR_TO_DATE(SUBSTRING(source_date, -8, 3), '%b'), '%m'),
                DATE_FORMAT(STR_TO_DATE(SUBSTRING(source_date, -11, 2), '%d'), '%d')
            ) AS year
            FROM humo_sources";
        } elseif ($this->order_sources === "place") {
            $order = " ORDER BY source_place" . $desc_asc;
            $select = "SELECT * FROM humo_sources";
        } else {
            $order = " ORDER BY IF(source_title != '', source_title, source_text)" . $desc_asc;
            $select = "SELECT * FROM humo_sources";
        }

        // Pagination
        $this->count_sources = $this->humo_option['show_persons'];
        $limit = " LIMIT :item, :count";
        $params[':item'] = (int)$this->item;
        $params[':count'] = (int)$this->count_sources;

        // All sources query (for rowCount)
        $all_query = $select . $where . $order;
        $all_stmt = $this->dbh->prepare($all_query);
        foreach ($params as $key => $val) {
            if ($key === ':item' || $key === ':count') continue;
            $all_stmt->bindValue($key, $val);
        }
        $all_stmt->execute();
        $this->all_sources = $all_stmt;

        // Paged query
        $paged_query = $select . $where . $order . $limit;
        $paged_stmt = $this->dbh->prepare($paged_query);
        foreach ($params as $key => $val) {
            if ($key === ':item' || $key === ':count') {
                $paged_stmt->bindValue($key, $val, PDO::PARAM_INT);
            } else {
                $paged_stmt->bindValue($key, $val);
            }
        }
        $paged_stmt->execute();
        return $paged_stmt->fetchAll(PDO::FETCH_OBJ);
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

    public function line_pages(): array
    {
        $processLinks = new ProcessLinks();
        $path = $processLinks->get_link($this->uri_path, 'sources', $this->tree_id, true);

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

<?php

namespace Genealogy\App\Model;

use Genealogy\Include\SafeTextDb;
use Genealogy\Include\SafeTextShow;
use Genealogy\Include\ProcessLinks;
use Genealogy\App\Model\BaseModel;
use PDO;

class AddressesModel extends BaseModel
{
    private $count_addresses, $all_addresses, $item, $selectsort, $sort_desc, $adr_place, $adr_address;
    private $safeTextShow;

    public function __construct($config)
    {
        parent::__construct($config);

        $this->safeTextShow = new SafeTextShow();
    }

    public function getAddressAuthorised(): string
    {
        $authorised = '';
        if ($this->user['group_addresses'] != 'j') {
            $authorised = __('You are not authorised to see this page.');
        }
        return $authorised;
    }

    // *** Added feb 2025 ***
    public function process_variables(): void
    {
        // *** Search data ***
        $this->adr_place = '';
        if (isset($_POST['adr_place']) && $_POST['adr_place'] != '') {
            $this->adr_place = $_POST['adr_place'];
        }
        if (isset($_GET['adr_place']) && $_GET['adr_place'] != '') {
            $this->adr_place = $_GET['adr_place'];
        }

        $this->adr_address = '';
        if (isset($_POST['adr_address']) && $_POST['adr_address'] != '') {
            $this->adr_address = $_POST['adr_address'];
        }
        if (isset($_GET['adr_address']) && $_GET['adr_address'] != '') {
            $this->adr_address = $_GET['adr_address'];
        }

        // *** Pages ***
        $this->item = 0;
        if (isset($_GET['item']) && is_numeric($_GET['item'])) {
            $this->item = $_GET['item'];
        }
    }

    public function get_adr_place(): string
    {
        return $this->adr_place;
    }

    public function get_adr_address(): string
    {
        return $this->adr_address;
    }

    public function listAddresses()
    {
        $safeTextDb = new SafeTextDb();

        // *** Order data ***
        $desc_asc = " ASC ";
        $this->sort_desc = 0;
        if (isset($_SESSION['sort_desc'])) {
            if ($_SESSION['sort_desc'] == 1) {
                $desc_asc = " DESC ";
                $this->sort_desc = 1;
            } else {
                $desc_asc = " ASC ";
                $this->sort_desc = 0;
            }
        }
        if (isset($_GET['sort_desc'])) {
            if ($_GET['sort_desc'] == 1) {
                $desc_asc = " DESC ";
                $this->sort_desc = 1;
                $_SESSION['sort_desc'] = 1;
            } else {
                $desc_asc = " ASC ";
                $this->sort_desc = 0;
                $_SESSION['sort_desc'] = 0;
            }
        }

        $this->selectsort = '';
        if (isset($_SESSION['sort']) && !isset($_GET['sort'])) {
            $this->selectsort = $_SESSION['sort'];
        }
        if (isset($_GET['sort'])) {
            if ($_GET['sort'] == "sort_place") {
                $this->selectsort = "sort_place";
                $_SESSION['sort'] = $this->selectsort;
            }
            if ($_GET['sort'] == "sort_address") {
                $this->selectsort = "sort_address";
                $_SESSION['sort'] = $this->selectsort;
            }
        }

        $orderby = " address_place" . $desc_asc . ", address_address" . $desc_asc;
        if ($this->selectsort) {
            if ($this->selectsort == "sort_place") {
                $orderby = " address_place " . $desc_asc . ", address_address" . $desc_asc;
            }
            if ($this->selectsort == "sort_address") {
                $orderby = " address_address " . $desc_asc;
            }
        }

        $where = '';
        if ($this->adr_place || $this->adr_address) {
            if ($this->adr_place != '') {
                $where .= " AND address_place LIKE '%" . $safeTextDb->safe_text_db($this->adr_place) . "%' ";
            }
            if ($this->adr_address != '') {
                $where .= " AND address_address LIKE '%" . $safeTextDb->safe_text_db($this->adr_address) . "%' ";
            }
        }

        // *** Count all addresses, needed for pagination ***
        $sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:tree_id AND address_shared='1'" . $where;
        $address_qry = $this->dbh->prepare($sql);
        $address_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_INT);
        $address_qry->execute();
        $this->all_addresses = $address_qry->rowCount();

        // *** Pages ***
        $this->count_addresses = $this->humo_option['show_persons'];    // *** Number of lines to show ***

        // *** Get addresses ***
        $sql = "SELECT * FROM humo_addresses
            WHERE address_tree_id=:tree_id AND address_shared='1'" . $where . "
            ORDER BY " . $orderby . " LIMIT :item, :count_addresses";
        $address_qry = $this->dbh->prepare($sql);
        $address_qry->bindValue(':tree_id', $this->tree_id, PDO::PARAM_INT);
        $address_qry->bindValue(':item', (int)$this->item, PDO::PARAM_INT);
        $address_qry->bindValue(':count_addresses', (int)$this->count_addresses, PDO::PARAM_INT);
        $address_qry->execute();

        return $address_qry->fetchAll(PDO::FETCH_OBJ);
    }

    // *** Added feb. 2025 (copy from sources scripts) ***
    private function process_link(): string
    {
        $link = '';

        if ($this->sort_desc != '') {
            $link .=  '&amp;sort_desc=' . $this->sort_desc;
        }

        if ($this->selectsort != '') {
            $link .=  '&amp;sort=' . $this->selectsort;
        }

        if ($this->adr_place != '') {
            $link .=  '&amp;adr_place=' . $this->adr_place;
        }

        if ($this->adr_address != '') {
            $link .=  '&amp;adr_address=' . $this->adr_address;
        }

        return $link;
    }

    public function line_pages(): array
    {
        $processLinks = new ProcessLinks();
        $path = $processLinks->get_link($this->uri_path, 'addresses', $this->tree_id, true);

        $start = 0;
        if (isset($_GET["start"]) && is_numeric($_GET["start"])) {
            $start = $_GET["start"];
        }

        // "<="
        $data["previous_link"] = '';
        $data["previous_status"] = '';
        if ($start > 1) {
            $start2 = $start - 20;
            $calculated = ($start - 2) * $this->count_addresses;
            $data["previous_link"] .= $path . 'start=' . $start2 . '&amp;item=' . $calculated;
            $data["previous_link"] .= $this->process_link();
        }
        if ($start <= 0) {
            $start = 1;
        }
        if ($start == 1) {
            $data["previous_status"] = 'disabled';
        }

        // 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19
        for ($i = $start; $i <= $start + 19; $i++) {
            $calculated = ($i - 1) * $this->count_addresses;
            if ($calculated < $this->all_addresses) {
                $data["page_nr"][] = $i;

                if ($this->item == $calculated) {
                    $data["page_status"][$i] = 'active';
                } else {
                    $data["page_status"][$i] = '';
                }

                $data["page_link"][$i] =  $path . 'start=' . $start . '&amp;item=' . $calculated;
                $data["page_link"][$i] .= $this->process_link();
            }
        }

        // "=>"
        $data["next_link"] = '';
        $data["next_status"] = '';
        $calculated = ($i - 1) * $this->count_addresses;
        if ($calculated < $this->all_addresses) {
            $data["next_link"] .=  $path . 'start=' . $i . '&amp;item=' . $calculated;
            $data["next_link"] .=  $this->process_link();
        } else {
            $data["next_status"] = 'disabled';
        }

        return $data;
    }

    public function getPlaceLink(): string
    {
        $place_sort_reverse = $this->sort_desc;
        if ($this->selectsort == "sort_place") {
            $place_sort_reverse = '1';
            if ($this->sort_desc == '1') {
                $place_sort_reverse = '0';
            }
        }
        return 'adr_place=' . $this->safeTextShow->safe_text_show($this->adr_place) . '&adr_address=' . $this->safeTextShow->safe_text_show($this->adr_address) . '&sort=sort_place&sort_desc=' . $place_sort_reverse;
    }

    public function getPlaceImage(): string
    {
        $image = 'images/button3.png';
        if ($this->selectsort == "sort_place" && $this->sort_desc == '1') {
            $image = 'images/button3up.png';
        }
        return $image;
    }

    public function getAddressLink(): string
    {
        $address_sort_reverse = $this->sort_desc;
        if ($this->selectsort == "sort_address") {
            $address_sort_reverse = '1';
            if ($this->sort_desc == '1') {
                $address_sort_reverse = '0';
            }
        }
        return 'adr_place=' . $this->safeTextShow->safe_text_show($this->adr_place) . '&adr_address=' . $this->safeTextShow->safe_text_show($this->adr_address) . '&sort=sort_address&sort_desc=' . $address_sort_reverse;
    }

    public function getAddressImage(): string
    {
        $image = 'images/button3.png';
        if ($this->selectsort == "sort_address" && $this->sort_desc == '1') {
            $image = 'images/button3up.png';
        }
        return $image;
    }

    public function getSelectSort(): string
    {
        return $this->selectsort;
    }
}

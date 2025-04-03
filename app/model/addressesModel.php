<?php
class AddressesModel
{
    private $dbh;

    private $count_addresses, $all_addresses, $item, $selectsort, $sort_desc, $adr_place, $adr_address;

    public function __construct($dbh)
    {
        $this->dbh = $dbh;
    }

    public function getAddressAuthorised($user)
    {
        $authorised = '';
        if ($user['group_addresses'] != 'j') {
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

    public function get_adr_place()
    {
        return $this->adr_place;
    }

    public function get_adr_address()
    {
        return $this->adr_address;
    }

    public function listAddresses($dbh, $tree_id, $humo_option)
    {
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
                $where .= " AND address_place LIKE '%" . safe_text_db($this->adr_place) . "%' ";
            }
            if ($this->adr_address != '') {
                $where .= " AND address_address LIKE '%" . safe_text_db($this->adr_address) . "%' ";
            }
        }

        // *** Count all addresses, needed for pagination ***
        $sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:tree_id AND address_shared='1'" . $where;
        $address_qry = $dbh->prepare($sql);
        $address_qry->bindValue(':tree_id', $tree_id, PDO::PARAM_INT);
        $address_qry->execute();
        $this->all_addresses = $address_qry->rowCount();

        // *** Pages ***
        $this->count_addresses = $humo_option['show_persons'];    // *** Number of lines to show ***

        // *** Get addresses ***
        $sql = "SELECT * FROM humo_addresses WHERE address_tree_id=:tree_id AND address_shared='1'" . $where . "
            ORDER BY " . $orderby . " LIMIT " . safe_text_db($this->item) . "," . $this->count_addresses;
        $address_qry = $dbh->prepare($sql);
        $address_qry->bindValue(':tree_id', $tree_id, PDO::PARAM_INT);
        $address_qry->execute();

        return $address_qry->fetchAll(PDO::FETCH_OBJ);
    }

    // *** Added feb. 2025 (copy from sources scripts) ***
    private function process_link()
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
    function line_pages($tree_id, $link_cls, $uri_path)
    {
        $path = $link_cls->get_link($uri_path, 'addresses', $tree_id, true);

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

    function getPlaceLink()
    {
        $place_sort_reverse = $this->sort_desc;
        if ($this->selectsort == "sort_place") {
            $place_sort_reverse = '1';
            if ($this->sort_desc == '1') {
                $place_sort_reverse = '0';
            }
        }
        return 'adr_place=' . safe_text_show($this->adr_place) . '&adr_address=' . safe_text_show($this->adr_address) . '&sort=sort_place&sort_desc=' . $place_sort_reverse;
    }

    function getPlaceImage()
    {
        $image = 'images/button3.png';
        if ($this->selectsort == "sort_place" && $this->sort_desc == '1') {
            $image = 'images/button3up.png';
        }
        return $image;
    }

    function getAddressLink()
    {
        $address_sort_reverse = $this->sort_desc;
        if ($this->selectsort == "sort_address") {
            $address_sort_reverse = '1';
            if ($this->sort_desc == '1') {
                $address_sort_reverse = '0';
            }
        }
        return 'adr_place=' . safe_text_show($this->adr_place) . '&adr_address=' . safe_text_show($this->adr_address) . '&sort=sort_address&sort_desc=' . $address_sort_reverse;
    }

    function getAddressImage()
    {
        $image = 'images/button3.png';
        if ($this->selectsort == "sort_address" && $this->sort_desc == '1') {
            $image = 'images/button3up.png';
        }
        return $image;
    }

    function getSelectSort()
    {
        return $this->selectsort;
    }
}

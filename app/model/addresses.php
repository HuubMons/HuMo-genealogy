<?php
class AddressesModel
{
    private $dbh;

    private $selectsort, $sort_desc, $adr_place, $adr_address;

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

    public function getAll($dbh, $tree_id)
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
        if (isset($_SESSION['sort']) and !isset($_GET['sort'])) {
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

        // *** Search data ***
        $this->adr_place = '';
        if (isset($_POST['adr_place']) and $_POST['adr_place'] != '') {
            $this->adr_place = $_POST['adr_place'];
        }
        if (isset($_GET['adr_place']) and $_GET['adr_place'] != '') {
            $this->adr_place = $_GET['adr_place'];
        }

        $this->adr_address = '';
        if (isset($_POST['adr_address']) and $_POST['adr_address'] != '') {
            $this->adr_address = $_POST['adr_address'];
        }
        if (isset($_GET['adr_address']) and $_GET['adr_address'] != '') {
            $this->adr_address = $_GET['adr_address'];
        }

        $where = '';
        if ($this->adr_place or $this->adr_address) {
            if ($this->adr_place != '') {
                $where .= " AND address_place LIKE '%" . safe_text_db($this->adr_place) . "%' ";
            }
            if ($this->adr_address != '') {
                $where .= " AND address_address LIKE '%" . safe_text_db($this->adr_address) . "%' ";
            }
        }

        $sql = "SELECT * FROM humo_addresses
            WHERE address_tree_id=:tree_id
            AND address_shared='1'" . $where . " ORDER BY " . $orderby;
        $address_qry = $dbh->prepare($sql);
        $address_qry->bindValue(':tree_id', $tree_id, PDO::PARAM_INT);
        $address_qry->execute();
        $addresses = $address_qry->fetchAll(PDO::FETCH_OBJ);

        return $addresses;
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
        $link = 'adr_place=' . safe_text_show($this->adr_place) . '&adr_address=' . safe_text_show($this->adr_address) . '&sort=sort_place&sort_desc=' . $place_sort_reverse;
        return $link;
    }

    function getPlaceImage()
    {
        $image = 'images/button3.png';
        if ($this->selectsort == "sort_place" and  $this->sort_desc == '1') {
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
        $link = 'adr_place=' . safe_text_show($this->adr_place) . '&adr_address=' . safe_text_show($this->adr_address) . '&sort=sort_address&sort_desc=' . $address_sort_reverse;
        return $link;
    }

    function getAddressImage()
    {
        $image = 'images/button3.png';
        if ($this->selectsort == "sort_address" and $this->sort_desc == '1') {
            $image = 'images/button3up.png';
        }
        return $image;
    }

    function getSelectSort()
    {
        return $this->selectsort;
    }
}
